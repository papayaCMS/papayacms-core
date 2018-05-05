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

/**
* Display anchestors of the current page.
*
* @package Papaya-Library
* @subpackage Administration
*/
class PapayaAdministrationPagesAnchestors extends \PapayaUiControl {

  /**
  * Member variable for pages subobject
  *
  * @var PapayaContentPages
  */
  private $_pages = NULL;

  /**
  * Member variable for hierarchy menu subobject
  *
  * @var PapayaUiHierarchyMenu
  */
  private $_menu = NULL;

  /**
   * Append anchestor menu xml to parent element, this will do nothing until ids are set.
   *
   * @param \PapayaXmlElement $parent
   * @return NULL|\PapayaXmlElement
   */
  public function appendTo(\PapayaXmlElement $parent) {
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
        $this->menu()->items[] = $item = new \PapayaUiHierarchyItem($data['title']);
        $item->reference->setParameters(array('page_id' => $id), 'tt');
      }
    }
  }

  /**
  * Content object, to load page informations
  *
  * @param \PapayaContentPages $pages
  * @return \PapayaContentPages
  */
  public function pages(\PapayaContentPages $pages = NULL) {
    if (isset($pages)) {
      $this->_pages = $pages;
    } elseif (is_null($this->_pages)) {
      $this->_pages = new \PapayaContentPages();
      $this->_pages->papaya($this->papaya());
    }
    return $this->_pages;
  }

  /**
  * Menu object used to generate xml with page items
  *
  * @param \PapayaUiHierarchyMenu $menu
  * @return \PapayaUiHierarchyMenu
  */
  public function menu(\PapayaUiHierarchyMenu $menu = NULL) {
    if (isset($menu)) {
      $this->_menu = $menu;
    } elseif (is_null($this->_menu)) {
      $this->_menu = new \PapayaUiHierarchyMenu();
      $this->_menu->papaya($this->papaya());
    }
    return $this->_menu;
  }
}
