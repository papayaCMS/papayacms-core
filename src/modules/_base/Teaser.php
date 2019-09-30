<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Modules\Core {

  use Papaya\Administration\Plugin\Editor\Dialog as PluginDialog;
  use Papaya\Plugin\Editable as EditablePlugin;
  use Papaya\Plugin\PageModule;
  use Papaya\Plugin\Quoteable as QuotablePlugin;
  use Papaya\UI\Dialog\Field as DialogField;
  use Papaya\Plugin\Editor as PluginEditor;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\XML\Element as XMLElement;

  abstract class Teaser implements PageModule, EditablePlugin, QuotablePlugin {

    use EditablePlugin\Aggregation;

    const FIELD_TITLE = 'title';
    const FIELD_SUBTITLE = 'subtitle';
    const FIELD_OVERLINE = 'overline';
    const FIELD_IMAGE = 'image';
    const FIELD_TEASER = 'teaser';

    const _DEFAULTS = [
      self::FIELD_TITLE => '',
      self::FIELD_SUBTITLE => '',
      self::FIELD_OVERLINE => '',
      self::FIELD_IMAGE => '',
      self::FIELD_TEASER => ''
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
      return $editor;
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
