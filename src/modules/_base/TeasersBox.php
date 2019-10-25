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
  use Papaya\XML\Element as XMLElement;

  class TeasersBox implements ApplicationAccess, AppendablePlugin, EditablePlugin, Partials\Teasers {

    use EditableContent\Aggregation;
    use Partials\TeasersAggregation;

    const FIELD_PAGE_ID = 'category-page-id';

    const _DEFAULTS = [
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
      $editor = new EditorDialog($content);
      $editor->papaya($this->papaya());
      $dialog = $editor->dialog();
      $dialog->fields[] = new DialogField\Input(
        new TranslatedText('Page Id'), self::FIELD_PAGE_ID, 255, self::_DEFAULTS[self::FIELD_PAGE_ID]
      );
      $this->appendTeaserFieldsToDialog($dialog, $content);
      return $editor;
    }

    /**
     * @param XMLElement $parent
     */
    public function appendTo(XMLElement $parent) {
      $content = $this->content()->withDefaults(self::_DEFAULTS);
      $pageId = $content[self::FIELD_PAGE_ID];
      if ($pageId !== '') {
        $teasers = $this->teaserFactory()->byParent(
          $pageId,
          $content[self::FIELD_TEASER_ORDER],
          $content[self::FIELD_TEASER_LIMIT]
        );
        $parent->append($teasers);
      }
    }
  }
}
