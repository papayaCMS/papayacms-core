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

  use Papaya\CMS\Administration\Plugin\Editor\Dialog as EditorDialog;
  use Papaya\Application\Access as ApplicationAccess;
  use Papaya\Plugin\Appendable as AppendablePlugin;
  use Papaya\Plugin\Editable as EditablePlugin;
  use Papaya\Plugin\Editable\Content as EditableContent;
  use Papaya\Plugin\Editor as PluginEditor;
  use Papaya\CMS\Output\Teasers\Factory as PageTeaserFactory;
  use Papaya\UI\Dialog\Field as DialogField;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\XML\Element as XMLElement;

  class TeasersBox implements ApplicationAccess, AppendablePlugin, EditablePlugin, Partials\Teasers {

    use EditableContent\Aggregation;
    use Partials\TeasersAggregation;

    private const FIELD_PAGE_ID = 'category-page-id';

    private const _TEASERS_BOX_DEFAULTS = [
      self::FIELD_PAGE_ID => 0
    ];

    /**
     * @var PageTeaserFactory
     */
    private $_contentTeasers;

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
      $dialog->fields[] = new DialogField\Input(
        new TranslatedText('Page Id'), self::FIELD_PAGE_ID, 255, $defaults[self::FIELD_PAGE_ID]
      );
      $this->appendTeasersFieldsToDialog($dialog, $content);
      return $editor;
    }

    /**
     * @param XMLElement $parent
     */
    public function appendTo(XMLElement $parent) {
      $content = $this->content()->withDefaults($this->getDefaultContent());
      $pageId = $content[self::FIELD_PAGE_ID];
      if ($pageId !== '' && $this->teaserFactory()) {
        $teasers = $this->teaserFactory()->byParent(
          $pageId,
          $content[self::FIELD_TEASERS_ORDER],
          $content[self::FIELD_TEASERS_LIMIT]
        );
        $parent->append($teasers);
      }
    }

    protected function getDefaultContent() {
      return self::_TEASERS_BOX_DEFAULTS;
    }
  }
}

