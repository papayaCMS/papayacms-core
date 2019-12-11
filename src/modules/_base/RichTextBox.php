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

  use Papaya\Administration\Plugin\Editor\Dialog as EditorDialog;
  use Papaya\Application\Access as ApplicationAccess;
  use Papaya\Plugin\Appendable as AppendablePlugin;
  use Papaya\Plugin\Editable as EditablePlugin;
  use Papaya\Plugin\Editable\Content as EditableContent;
  use Papaya\Plugin\Editor as PluginEditor;
  use Papaya\UI\Dialog\Field as DialogField;
  use Papaya\UI\Dialog\Options as DialogOptions;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\XML\Element as XMLElement;

  class RichTextBox implements ApplicationAccess, AppendablePlugin, EditablePlugin {

    use EditableContent\Aggregation;

    const FIELD_TEXT = 'text';

    const _RICHTEXT_DEFAULTS = [
      self::FIELD_TEXT => ''
    ];

    /**
     * @param EditableContent $content
     *
     * @return PluginEditor
     */
    public function createEditor(EditableContent $content) {
      $defaults = $this->getDefaultContent();
      $editor = new EditorDialog($content);
      $editor->papaya($this->papaya());
      $dialog = $editor->dialog();
      $dialog->options->captionStyle = DialogOptions::CAPTION_NONE;
      $dialog->fields[] = new DialogField\Textarea\Richtext(
        new TranslatedText('Text'),
        self::FIELD_TEXT,
        20,
        $defaults[self::FIELD_TEXT],
        NULL,
        DialogField\Textarea\Richtext::RTE_SIMPLE
      );
      return $editor;
    }

    /**
     * @param XMLElement $parent
     */
    public function appendTo(XMLElement $parent) {
      $content = $this->content()->withDefaults($this->getDefaultContent());
      $parent->appendElement('text')->appendXML($content[self::FIELD_TEXT]);
    }

    /**
     * @return array
     */
    protected function getDefaultContent() {
      return self::_RICHTEXT_DEFAULTS;
    }
  }
}

