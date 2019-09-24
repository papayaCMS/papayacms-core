<?php

namespace dimensional\WebSite {

  use Papaya\Administration\Plugin\Editor\Dialog as PluginDialog;
  use Papaya\Plugin\Appendable as AppendablePlugin;
  use Papaya\Plugin\Editable\Content as PluginContent;
  use Papaya\Plugin\Editor as PluginEditor;
  use Papaya\Plugin\Quoteable as QuotablePlugin;
  use Papaya\Plugin\Editable as EditablePlugin;
  use Papaya\UI\Dialog\Field as DialogField;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\XML\Element as XMLElement;
  use Papaya\Template\Tag\Image as ImageTag;

  class Article implements AppendablePlugin, EditablePlugin, QuotablePlugin {

    use EditablePlugin\Aggregation;

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
     * @param PluginContent $content
     *
     * @return PluginEditor
     */
    public function createEditor(PluginContent $content) {
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
      $parent->appendElement('title', $content[self::FIELD_TITLE]);
      $parent->appendElement('subtitle', $content[self::FIELD_TITLE]);
      $parent->appendElement('overline', $content[self::FIELD_OVERLINE]);
      $parent->appendElement('teaser')->appendXML($content[self::FIELD_TEASER]);
      $parent->appendElement('text')->appendXML($content[self::FIELD_TEXT]);
      $parent->appendElement('image')->append(new ImageTag($content[self::FIELD_IMAGE]));
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
  }
}
