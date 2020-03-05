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
  use Papaya\UI\Content\Teasers\Factory as PageTeaserFactory;
  use Papaya\UI\Dialog\Field as DialogField;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\Utility\Arrays as ArrayUtilities;
  use Papaya\XML\Element as XMLElement;

  class PassthroughBox implements AppendablePlugin, EditablePlugin {

    use EditableContent\Aggregation;

    const FIELD_PASSTHROUGH_CONTENT = 'passtrough-content';
    const FIELD_PASSTHROUGH_TYPE = 'passtrough-content-type';

    const PASSTHROUGH_TYPE_XHTML = 'xhtml';
    const PASSTHROUGH_TYPE_HTML = 'html';

    const _BOX_DEFAULTS = [
      self::FIELD_PASSTHROUGH_CONTENT => '',
      self::FIELD_PASSTHROUGH_TYPE => self::PASSTHROUGH_TYPE_XHTML
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
      $dialog->fields[] = $field = new DialogField\Select\Radio(
        new TranslatedText('Type'),
        self::FIELD_PASSTHROUGH_TYPE,
        [
          self::PASSTHROUGH_TYPE_XHTML => new TranslatedText('XHTML'),
          self::PASSTHROUGH_TYPE_HTML => new TranslatedText('HTML')
        ],
        TRUE
      );
      $field->setDefaultValue($defaults[self::FIELD_PASSTHROUGH_TYPE]);
      $dialog->fields[] = new DialogField\Textarea(
        new TranslatedText('Content'),
        self::FIELD_PASSTHROUGH_CONTENT,
        40,
        $defaults[self::FIELD_PASSTHROUGH_CONTENT]
      );
      return $editor;
    }

    /**
     * @param XMLElement $parent
     */
    public function appendTo(XMLElement $parent) {
      $values = $this->content()->withDefaults($this->getDefaultContent());
      $content = $values[self::FIELD_PASSTHROUGH_CONTENT];
      if ($content !== '') {
        switch ($values[self::FIELD_PASSTHROUGH_TYPE]) {
        case self::PASSTHROUGH_TYPE_HTML:
          $parent->appendElement('passthrough', ['type' => 'html'], $content);
          break;
        case self::PASSTHROUGH_TYPE_HTML:
        default:
          $parent->appendElement('passthrough', ['type' => 'xhtml'])->appendXML($content);
        }
      }
    }

    protected function getDefaultContent() {
      return self::_BOX_DEFAULTS;
    }
  }
}

