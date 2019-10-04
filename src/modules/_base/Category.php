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

  use Papaya\Plugin\Cacheable as CacheablePlugin;
  use Papaya\Plugin\Configurable\Context as ContextAwarePlugin;
  use Papaya\Plugin\Editable as EditablePlugin;
  use Papaya\Plugin\Editor as PluginEditor;
  use Papaya\Plugin\Filter as PluginFilter;
  use Papaya\UI\Content\Teasers\Factory as PageTeaserFactory;
  use Papaya\XML\Element as XMLElement;

  class Category extends Article implements Partials\Teasers {

    use ContextAwarePlugin\Aggregation;
    use EditablePlugin\Content\Aggregation;
    use CacheablePlugin\Aggregation;
    use PluginFilter\Aggregation;
    use Partials\TeasersAggregation;

    const _DEFAULTS = [
      self::FIELD_TITLE => '',
      self::FIELD_SUBTITLE => '',
      self::FIELD_OVERLINE => '',
      self::FIELD_IMAGE => '',
      self::FIELD_TEASER => '',
      self::FIELD_TEXT => ''
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
      $content = $this->content()->withDefaults(self::_TEASER_DEFAULTS);
      $pageId = $this->_page->getPageId();
      if ($pageId !== '') {
        $teasers = $this->teaserFactory()->byParent(
          $pageId,
          $content[self::FIELD_TEASER_ORDER],
          $content[self::FIELD_TEASER_LIMIT]
        );
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
      $this->appendTeaserFieldsToDialog($editor->dialog(), $content);
      return $editor;
    }
  }
}
