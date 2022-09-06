<?php

namespace Papaya\Modules\Core {

  use Papaya\CMS\Administration\Plugin\Editor\Dialog as PluginDialog;
  use Papaya\CMS\Cache\Identifier\Definition\Page as PageCacheDefinition;
  use Papaya\Plugin;
  use Papaya\Plugin\Appendable as AppendablePlugin;
  use Papaya\Plugin\Cacheable as CacheablePlugin;
  use Papaya\Plugin\Configurable\Context as ContextAwarePlugin;
  use Papaya\Plugin\Configurable\Options as ConfigurableOptionsPlugin;
  use Papaya\Plugin\Editable as EditablePlugin;
  use Papaya\Template\Tag\Image as ImageTag;
  use Papaya\UI\Dialog\Field as DialogField;
  use Papaya\Plugin\Editor as PluginEditor;
  use Papaya\CMS\Plugin\Options as PluginOptions;
  use Papaya\CMS\Plugin\Filter as PluginFilter;
  use Papaya\Filter as Filter;
  use Papaya\UI\Dialog\Field\Textarea\Richtext;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\UI\Text\Translated\Collection as TranslatedList;
  use Papaya\XML\Element as XMLElement;

  class Article
    extends Partials\Teaser
    implements AppendablePlugin, ContextAwarePlugin, CacheablePlugin, ConfigurableOptionsPlugin {

    use ContextAwarePlugin\Aggregation;
    use CacheablePlugin\Aggregation;
    use PluginFilter\Aggregation;
    use PluginOptions\Aggregation;

    private const FIELD_TEXT = 'text';
    private const FIELD_CATCH_LINE_OVERLINE = 'catch-line-overline';
    private const FIELD_CATCH_LINE_TITLE = 'catch-line-title';
    private const FIELD_CATCH_LINE_TEXT = 'catch-line-text';
    private const FIELD_CATCH_LINE_VARIANT = 'catch-line-variant';
    private const FIELD_PAGE_TITLE_MODE = 'page-title-mode';


    private const OPTION_CATCH_LINE_ENABLED = 'OPTION_CATCH_LINE_ENABLED';
    private const OPTION_CATCH_LINE_VARIANTS = 'OPTION_CATCH_LINE_VARIANTS';

    private const PAGE_TITLE_MODE_CONTENT_TITLE = 'content-title';
    private const PAGE_TITLE_MODE_CATCH_LINE_TITLE = 'catch-line-title';
    private const PAGE_TITLE_MODE_CATCH_LINE_OVERLINE = 'catch-line-overline';

    /**
     * @access private
     */
    private const _ARTICLE_DEFAULTS = [
      self::FIELD_TEXT => '',
      self::FIELD_CATCH_LINE_TITLE => '',
      self::FIELD_CATCH_LINE_TEXT => '',
      self::FIELD_CATCH_LINE_OVERLINE => '',
      self::FIELD_PAGE_TITLE_MODE => self::PAGE_TITLE_MODE_CONTENT_TITLE,
    ];

    /**
     * @param EditablePlugin\Content $content
     *
     * @return PluginEditor|PluginDialog
     */
    public function createEditor(EditablePlugin\Content $content) {
      $defaults = $this->getDefaultContent();
      $editor = parent::createEditor($content);
      $dialog = $editor->dialog();
      $dialog->fields[] = new Richtext(
        new TranslatedText('Text'),
        self::FIELD_TEXT,
        20,
        $defaults[self::FIELD_TEXT]
      );
      if ($this->options()->get(self::OPTION_CATCH_LINE_ENABLED, FALSE)) {
        $dialog->fields[] = $group = new DialogField\Group(new TranslatedText('Catch-Line'));
        $group->fields[] = new DialogField\Input(
          new TranslatedText('Overline'),
          self::FIELD_CATCH_LINE_OVERLINE,
          -1,
          $defaults[self::FIELD_CATCH_LINE_OVERLINE]
        );
        $group->fields[] = new DialogField\Input(
          new TranslatedText('Title'),
          self::FIELD_CATCH_LINE_TITLE,
          -1,
          $defaults[self::FIELD_CATCH_LINE_TITLE]
        );
        $group->fields[] = new DialogField\Input(
          new TranslatedText('Text'),
          self::FIELD_CATCH_LINE_TEXT,
          -1,
          $defaults[self::FIELD_CATCH_LINE_TEXT]
        );
        $group->fields[] = $field = new DialogField\Select\Radio(
          new TranslatedText('Page title element'),
          self::FIELD_PAGE_TITLE_MODE,
          new TranslatedList(
            [
              self::PAGE_TITLE_MODE_CONTENT_TITLE => 'Content title',
              self::PAGE_TITLE_MODE_CATCH_LINE_TITLE => 'Catch line title',
              self::PAGE_TITLE_MODE_CATCH_LINE_OVERLINE => 'Catch line overline'
            ]
          ),
          FALSE
        );
        if ($variants = $this->getCatchLineVariants()) {
          $group->fields[] = $field = new DialogField\Select\Radio(
            new TranslatedText('Variant'),
            self::FIELD_CATCH_LINE_VARIANT,
            $variants,
            FALSE
          );
        }
        $field->setDefaultValue(reset($variants) ?? '');
      }
      return $editor;
    }

    private function getCatchLineVariants() {
      $variants = preg_split(
        '([\r\n]+)',
        $this->options()->get(self::OPTION_CATCH_LINE_VARIANTS, '')
      );
      return array_combine($variants, $variants);
    }

    /**
     * Create dom node structure of the given object and append it to the given xml
     * element node.
     *
     * @param XMLElement $parent
     */
    public function appendTo(XMLElement $parent) {
      $content = $this->content()->withDefaults($this->getDefaultContent());
      $filters = $this->filters();
      $filters->prepare(
        $content[self::FIELD_TEXT],
        $this->configuration()
      );
      $parent->appendElement('overline', $content[self::FIELD_OVERLINE]);
      $parent->appendElement('title', $content[self::FIELD_TITLE]);
      $parent->appendElement('subtitle', $content[self::FIELD_SUBTITLE]);
      $parent->appendElement('teaser')->appendXML($content[self::FIELD_TEASER]);
      $parent->appendElement('image')->append(new ImageTag($content[self::FIELD_IMAGE]));
      if ($this->options()->get(self::OPTION_CATCH_LINE_ENABLED, FALSE)) {
        $catchLine = $parent->appendElement(
          'catch-line',
          [
            'title-mode' => $content[self::FIELD_PAGE_TITLE_MODE],
            'variant' => $content[self::FIELD_CATCH_LINE_VARIANT]
          ]
        );
        $catchLine->appendElement('overline', $content[self::FIELD_CATCH_LINE_OVERLINE]);
        $catchLine->appendElement('title', $content[self::FIELD_CATCH_LINE_TITLE]);
        $catchLine->appendElement('text', $content[self::FIELD_CATCH_LINE_TEXT]);
      }
      $parent->appendElement('text')->appendXML(
        $filters->applyTo($content[self::FIELD_TEXT])
      );
      $parent->append($filters);
    }

    public function createCacheDefinition() {
      return new PageCacheDefinition();
    }

    /**
     * @param EditablePlugin\Options $options
     *
     * @return PluginEditor
     */
    public function createOptionsEditor(Plugin\Editable\Options $options) {
      $editor = new PluginDialog($options);
      $dialog = $editor->dialog();
      $dialog->fields[] = $field = new DialogField\Select\Radio(
        new TranslatedText('Enable Catch-Line'),
        self::OPTION_CATCH_LINE_ENABLED,
          new TranslatedList([TRUE => 'Yes', FALSE => 'No']
        )
      );
      $dialog->fields[] = $field = new DialogField\Textarea\Lines(
        new TranslatedText('Catch-Line Variant Identifiers'),
        self::OPTION_CATCH_LINE_VARIANTS,
        10,
        [],
        new \Papaya\Filter\NotEmpty()
      );
      $field->setDefaultValue(FALSE);
      return $editor;
    }

    protected function getDefaultContent() {
      return array_merge(
        parent::getDefaultContent(),
        self::_ARTICLE_DEFAULTS
      );
    }
  }
}
