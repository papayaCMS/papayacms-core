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

namespace Papaya\Administration\Pages;

/**
 * Display anchestors of the current page.
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Ancestors extends \Papaya\Ui\Control {

  /**
   * Member variable for pages subobject
   *
   * @var \Papaya\Content\Pages
   */
  private $_pages = NULL;

  /**
   * Member variable for hierarchy menu subobject
   *
   * @var \Papaya\Ui\Hierarchy\Menu
   */
  private $_menu = NULL;

  /**
   * Append ancestor menu xml to parent element, this will do nothing until ids are set.
   *
   * @param \Papaya\Xml\Element $parent
   * @return NULL|\Papaya\Xml\Element
   */
  public function appendTo(\Papaya\Xml\Element $parent) {
    return $this->menu()->appendTo($parent);
  }

  /**
   * Load data for the given page ids and create items in the menu for them.
   *
   * @param array $pageIds
   */
  public function setIds(array $pageIds) {
    $this->pages()->load(
      array(
        'id' => $pageIds,
        'language_id' => $this->papaya()->administrationLanguage->getCurrent()->id
      )
    );
    $this->menu()->items->clear();
    $this->menu()->items->limit = 10;
    foreach ($pageIds as $id) {
      if ($this->pages()->offsetExists($id)) {
        $data = $this->pages()->offsetGet($id);
        $this->menu()->items[] = $item = new \Papaya\Ui\Hierarchy\Item($data['title']);
        $item->reference->setParameters(array('page_id' => $id), 'tt');
      }
    }
  }

  /**
   * Content object, to load page informations
   *
   * @param \Papaya\Content\Pages $pages
   * @return \Papaya\Content\Pages
   */
  public function pages(\Papaya\Content\Pages $pages = NULL) {
    if (isset($pages)) {
      $this->_pages = $pages;
    } elseif (is_null($this->_pages)) {
      $this->_pages = new \Papaya\Content\Pages();
      $this->_pages->papaya($this->papaya());
    }
    return $this->_pages;
  }

  /**
   * Menu object used to generate xml with page items
   *
   * @param \Papaya\Ui\Hierarchy\Menu $menu
   * @return \Papaya\Ui\Hierarchy\Menu
   */
  public function menu(\Papaya\Ui\Hierarchy\Menu $menu = NULL) {
    if (isset($menu)) {
      $this->_menu = $menu;
    } elseif (is_null($this->_menu)) {
      $this->_menu = new \Papaya\Ui\Hierarchy\Menu();
      $this->_menu->papaya($this->papaya());
    }
    return $this->_menu;
  }
}
