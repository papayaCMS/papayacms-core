<?php

namespace Papaya\Modules\Core {

  use Papaya\CMS\Administration\Plugin\Editor\Dialog as PluginDialog;
  use Papaya\Cache\Identifier\Definition\Page as PageCacheDefinition;
  use Papaya\Plugin;
  use Papaya\Plugin\Appendable as AppendablePlugin;
  use Papaya\Plugin\Cacheable as CacheablePlugin;
  use Papaya\Plugin\Configurable\Context as ContextAwarePlugin;
  use Papaya\Plugin\Configurable\Options as ConfigurableOptionsPlugin;
  use Papaya\Plugin\Editable as EditablePlugin;
  use Papaya\Template\Tag\Image as ImageTag;
  use Papaya\UI\Dialog\Field as DialogField;
  use Papaya\Plugin\Editor as PluginEditor;
  use Papaya\Plugin\Filter as PluginFilter;
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
    use EditablePlugin\Options\Aggregation;

    const FIELD_TEXT = 'text';
    const FIELD_CATCH_LINE_TITLE = 'catch-line-title';
    const FIELD_CATCH_LINE_TEXT = 'catch-line-text';

    const OPTION_CATCH_LINE_ENABLED = 'OPTION_CATCH_LINE_ENABLED';

    /**
     * @access private
     */
    const _ARTICLE_DEFAULTS = [
      self::FIELD_TEXT => '',
      self::FIELD_CATCH_LINE_TITLE => '',
      self::FIELD_CATCH_LINE_TEXT => ''
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
      }
      return $editor;
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
        $catchLine = $parent->appendElement('catch-line');
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
