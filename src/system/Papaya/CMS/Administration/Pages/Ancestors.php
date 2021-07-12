<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\CMS\Administration\Pages;

use Papaya\CMS\Content;
use Papaya\UI;

/**
 * Display anchestors of the current page.
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Ancestors extends UI\Control {
  /**
   * Member variable for pages subobject
   *
   * @var Content\Pages
   */
  private $_pages;

  /**
   * Member variable for hierarchy menu subobject
   *
   * @var UI\Hierarchy\Menu
   */
  private $_menu;

  /**
   * Append ancestor menu xml to parent element, this will do nothing until ids are set.
   *
   * @param \Papaya\XML\Element $parent
   *
   * @return null|\Papaya\XML\Element
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    return $this->menu()->appendTo($parent);
  }

  /**
   * Load data for the given page ids and create items in the menu for them.
   *
   * @param array $pageIds
   */
  public function setIds(array $pageIds) {
    $this->pages()->load(
      [
        'id' => $pageIds,
        'language_id' => $this->papaya()->administrationLanguage->getCurrent()->id
      ]
    );
    $this->menu()->items->clear();
    $this->menu()->items->limit = 10;
    foreach ($pageIds as $id) {
      if ($this->pages()->offsetExists($id)) {
        $data = $this->pages()->offsetGet($id);
        $this->menu()->items[] = $item = new UI\Hierarchy\Item($data['title']);
        $item->reference->setParameters(['page_id' => $id], 'tt');
      }
    }
  }

  /**
   * Content object, to load page informations
   *
   * @param Content\Pages $pages
   *
   * @return Content\Pages
   */
  public function pages(Content\Pages $pages = NULL) {
    if (NULL !== $pages) {
      $this->_pages = $pages;
    } elseif (NULL === $this->_pages) {
      $this->_pages = new Content\Pages();
      $this->_pages->papaya($this->papaya());
    }
    return $this->_pages;
  }

  /**
   * Menu object used to generate xml with page items
   *
   * @param UI\Hierarchy\Menu $menu
   *
   * @return UI\Hierarchy\Menu
   */
  public function menu(UI\Hierarchy\Menu $menu = NULL) {
    if (NULL !== $menu) {
      $this->_menu = $menu;
    } elseif (NULL === $this->_menu) {
      $this->_menu = new UI\Hierarchy\Menu();
      $this->_menu->papaya($this->papaya());
    }
    return $this->_menu;
  }
}
