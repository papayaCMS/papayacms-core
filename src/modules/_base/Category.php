<?php
/**
 * A simple CMS box for loading a page teaser
 *
 * @copyright  2013 by papaya Software GmbH - All rights reserved.
 * @link       http://www.papaya-cms.com/
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 * You can redistribute and/or modify this script under the terms of the GNU General Public
 * License (GPL) version 2, provided that the copyright and license notes, including these
 * lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.
 *
 * @package    Papaya-Library
 * @subpackage Modules-Standard
 * @version    $Id: Box.php 39795 2014-05-06 15:35:52Z weinert $
 */

namespace Papaya\Modules\Core {

  /*
  * A simple CMS box for loading a page teaser
  *
  * @package Papaya-Library
  * @subpackage Modules-Standard
  */

  use Papaya\Filter\IntegerValue;
  use Papaya\Plugin\Cacheable as CacheablePlugin;
  use Papaya\Plugin\Configurable\Context as ContextAwarePlugin;
  use Papaya\Plugin\Editable as EditablePlugin;
  use Papaya\Plugin\Editor as PluginEditor;
  use Papaya\Plugin\Filter as PluginFilter;
  use Papaya\UI\Content\Teasers\Factory as PageTeaserFactory;
  use Papaya\UI\Dialog\Field\Textarea\Richtext;
  use Papaya\XML\Element as XMLElement;

  class Category extends Article implements Partials\Teasers {

    use ContextAwarePlugin\Aggregation;
    use EditablePlugin\Content\Aggregation;
    use CacheablePlugin\Aggregation;
    use PluginFilter\Aggregation;
    use Partials\TeasersAggregation;

    const PARAMETER_NAME_PAGING = 'page';

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
      $offsetPage = $this->papaya()->request->getParameter(
        self::PARAMETER_NAME_PAGING, 1, new IntegerValue(1)
      );
      $pageId = $this->_page->getPageId();
      if ($pageId !== '' && $this->teaserFactory()) {
        $teasers = $this->teaserFactory()->byParent(
          $pageId,
          $content[self::FIELD_TEASERS_ORDER],
          $content[self::FIELD_TEASERS_LIMIT] + 1,
          ($offsetPage - 1) * $content[self::FIELD_TEASERS_LIMIT]
        );
        $teasers->definePaging(self::PARAMETER_NAME_PAGING, $content[self::FIELD_TEASERS_LIMIT]);
        $parent->append($teasers);
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
      $editor = parent::createEditor($content);
      $this->appendTeasersFieldsToDialog($editor->dialog(), $content);
      return $editor;
    }

    protected function getDefaultContent() {
      return array_merge(
        parent::getDefaultContent(),
        $this->getTeasersDefaultContent()
      );
    }
  }
}
