<?php

namespace Papaya\Modules\Core {

  use Papaya\Administration\Plugin\Editor\Dialog;
  use Papaya\Administration\Plugin\Editor\Dialog as PluginDialog;
  use Papaya\Cache\Identifier\Definition\BooleanValue;
  use Papaya\Plugin\Appendable as AppendablePlugin;
  use Papaya\Plugin\Cacheable as CacheablePlugin;
  use Papaya\Plugin\Configurable\Context as ContextAwarePlugin;
  use Papaya\Plugin\Editable as EditablePlugin;
  use Papaya\Template\Tag\Image as ImageTag;
  use Papaya\UI\Dialog\Field as DialogField;
  use Papaya\Plugin\Editor as PluginEditor;
  use Papaya\Plugin\Filter as PluginFilter;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\XML\Element as XMLElement;

  class Article implements AppendablePlugin, EditablePlugin, ContextAwarePlugin, CacheablePlugin {

    use ContextAwarePlugin\Aggregation;
    use EditablePlugin\Aggregation;
    use CacheablePlugin\Aggregation;
    use PluginFilter\Aggregation;

    const FIELD_TITLE = 'title';
    const FIELD_SUBTITLE = 'subtitle';
    const FIELD_OVERLINE = 'overline';
    const FIELD_IMAGE = 'image';
    const FIELD_TEASER = 'teaser';
    const FIELD_TEXT = 'text';

    const _DEFAULTS = [
      self::FIELD_TITLE => '',
      self::FIELD_SUBTITLE => '',
      self::FIELD_OVERLINE => '',
      self::FIELD_IMAGE => '',
      self::FIELD_TEASER => '',
      self::FIELD_TEXT => '',
    ];

    /**
     * @param EditablePlugin\Content $content
     *
     * @return PluginEditor|PluginDialog
     */
    public function createEditor(EditablePlugin\Content $content) {
      $editor = new PluginDialog($content);
      $editor->papaya($this->papaya());
      $dialog = $editor->dialog();
      $dialog->fields[] = new DialogField\Input(
        new TranslatedText('Title'), self::FIELD_TITLE, 255, self::_DEFAULTS[self::FIELD_TITLE]
      );
      $dialog->fields[] = new DialogField\Input(
        new TranslatedText('Subtitle'),
        self::FIELD_SUBTITLE,
        255,
        self::_DEFAULTS[self::FIELD_SUBTITLE]
      );
      $dialog->fields[] = new DialogField\Input(
        new TranslatedText('Overline'),
        self::FIELD_OVERLINE,
        255,
        self::_DEFAULTS[self::FIELD_OVERLINE]
      );
      $dialog->fields[] = new DialogField\Input\Media\ImageResized(
        new TranslatedText('Image'), self::FIELD_IMAGE
      );
      $dialog->fields[] = new DialogField\Textarea\Richtext(
        new TranslatedText('Teaser'),
        self::FIELD_TEASER,
        8,
        self::_DEFAULTS[self::FIELD_TEASER],
        NULL,
        DialogField\Textarea\Richtext::RTE_SIMPLE
      );
      $dialog->fields[] = new DialogField\Textarea\Richtext(
        new TranslatedText('Text'),
        self::FIELD_TEXT,
        20,
        self::_DEFAULTS[self::FIELD_TEXT]
      );
      return $editor;
    }

    /**
     * Create dom node structure of the given object and append it to the given xml
     * element node.
     *
     * @param XMLElement $parent
     */
    public function appendTo(XMLElement $parent) {
      $content = $this->content()->withDefaults(self::_DEFAULTS);
      $filters = $this->filters();
      $filters->prepare(
        $content[self::FIELD_TEXT],
        $this->configuration()
      );
      $parent->appendElement('title', $content[self::FIELD_TITLE]);
      $parent->appendElement('subtitle', $content[self::FIELD_SUBTITLE]);
      $parent->appendElement('overline', $content[self::FIELD_OVERLINE]);
      $parent->appendElement('teaser')->appendXML($content[self::FIELD_TEASER]);
      $parent->appendElement('text')->appendXML(
        $filters->applyTo($content[self::FIELD_TEXT])
      );
      $parent->appendElement('image')->append(new ImageTag($content[self::FIELD_IMAGE]));
      $parent->append($filters);
    }

    /**
     * Append short content (aka "quote") to the parent xml element.
     *
     * @param XMLElement $parent
     *
     * @return XMLElement
     */
    public function appendQuoteTo(XMLElement $parent) {
      $content = $this->content()->withDefaults(self::_DEFAULTS);
      $parent->appendElement('title', $content[self::FIELD_TITLE]);
      $parent->appendElement('subtitle', $content[self::FIELD_TITLE]);
      $parent->appendElement('overline', $content[self::FIELD_OVERLINE]);
      $parent->appendElement('text')->appendXML($content[self::FIELD_TEASER]);
      return $parent;
    }

    public function createCacheDefinition() {
      return new BooleanValue(TRUE);
    }
  }
}
