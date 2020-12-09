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

  /*
  * A simple CMS box for loading a page teaser
  *
  * @package Papaya-Library
  * @subpackage Modules-Standard
  */
  use Papaya\Plugin\Cacheable as CacheablePlugin;
  use Papaya\Plugin\Configurable\Context as ContextAwarePlugin;
  use Papaya\Plugin\Editable as EditablePlugin;
  use Papaya\Plugin\Editor as PluginEditor;
  use Papaya\Plugin\Filter as PluginFilter;
  use Papaya\UI\Content\Teasers\Factory as PageTeaserFactory;
  use Papaya\UI\Dialog\Field as DialogField;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\UI\Text\Translated\Collection as TranslatedList;
  use Papaya\XML\Element as XMLElement;

  class CategoryGroup extends Article implements Partials\Teasers {

    use ContextAwarePlugin\Aggregation;
    use EditablePlugin\Content\Aggregation;
    use CacheablePlugin\Aggregation;
    use PluginFilter\Aggregation;
    use Partials\TeasersAggregation;

    const FIELD_CATEGORY_ORDER = 'category-order';
    const FIELD_CATEGORY_LIMIT = 'category-limit';

    const _CATEGORY_GROUP_DEFAULTS = [
      self::FIELD_CATEGORY_ORDER => PageTeaserFactory::ORDER_POSITION_ASCENDING,
      self::FIELD_CATEGORY_LIMIT => 10
    ];

    /**
     * @var PageTeaserFactory
     */
    private $_contentTeasers;

    /**
     * Append the page output xml to the DOM.
     *
     * @param XMLElement $parent
     * @see PapayaXmlAppendable::appendTo()
     */
    public function appendTo(XMLElement $parent) {
      parent::appendTo($parent);
      $content = $this->content()->withDefaults($this->getDefaultContent());
      $pageId = $this->_page->getPageId();
      if ($pageId !== '' && $this->teaserFactory()) {
        $groups = $this->teaserFactory()->byParent(
          $pageId,
          $content[self::FIELD_CATEGORY_ORDER],
          $content[self::FIELD_CATEGORY_LIMIT]
        );
        foreach ($groups->pages() as $page) {
          $teasers = $this->teaserFactory()->byParent(
            $page['id'],
            $content[self::FIELD_TEASERS_ORDER],
            $content[self::FIELD_TEASERS_LIMIT]
          );
          $group = $parent->appendElement(
            'category'
          );
          $groups->appendTeaserTo($group, $page);
          $group->append($teasers);
        }
      }
    }

    /**
     * The editor is used to change the stored data in the administration interface.
     *
     * In this case it the editor creates an dialog from a field definition.
     *
     * @param EditablePlugin\Content $content
     * @return PluginEditor
     * @see PapayaPluginEditableContent::editor()
     */
    public function createEditor(EditablePlugin\Content $content) {
      $defaults = $this->getDefaultContent();
      $pageOrderOptions = new TranslatedList(PageTeaserFactory::ORDER_POSSIBILITIES);
      $editor = parent::createEditor($content);
      $dialog = $editor->dialog();
      $dialog->fields[] = $group = new DialogField\Group(
        new TranslatedText('Categories')
      );
      $group->fields[] = $field = new DialogField\Select(
        new TranslatedText('Order'),
        self::FIELD_CATEGORY_ORDER,
        $pageOrderOptions,
        TRUE
      );
      $field->setDefaultValue($defaults[self::FIELD_CATEGORY_ORDER]);
      $group->fields[] = new DialogField\Input\Number(
        new TranslatedText('Limit'),
        self::FIELD_CATEGORY_LIMIT,
        $defaults[self::FIELD_CATEGORY_LIMIT]
      );
      $this->appendTeasersFieldsToDialog($editor->dialog(), $content);
      return $editor;
    }

    protected function getDefaultContent() {
      return array_merge(
        parent::getDefaultContent(),
        $this->getTeasersDefaultContent(),
        self::_CATEGORY_GROUP_DEFAULTS
      );
    }
  }
}
