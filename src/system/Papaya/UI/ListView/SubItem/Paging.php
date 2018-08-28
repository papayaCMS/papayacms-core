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

namespace Papaya\UI\ListView\SubItem {

  use Papaya\UI;
  use Papaya\UI\ListView;
  use Papaya\XML;

  /**
   * A listview subitem displaying paging buttons.
   *
   * @package Papaya-Library
   * @subpackage UI
   *
   * @property integer $align
   * @property \Papaya\UI\Icon\Collection $icons
   * @property string $selection
   * @property integer $selectionMode
   * @property array $actionParameters
   */
  class Paging extends ListView\SubItem {

    /**
     * @var UI\Paging\Count
     */
    private $_paging;
    private $_pagingParameterName;
    private $_itemsCount;

    public function __construct($parameterName, $itemsCount) {
      $this->_pagingParameterName = new \Papaya\Request\Parameters\Name($parameterName);
      $this->_itemsCount = $itemsCount;
    }

    public function appendTo(XML\Element $parent) {
      $subitem = $parent->appendElement(
        'subitem',
        array(
          'align' => UI\Option\Align::getString($this->getAlign())
        )
      );
      $subitem->append($this->paging());
    }

    /**
     * @param UI\Paging\Count|NULL $paging
     * @return UI\Paging\Count
     */
    public function paging(UI\Paging\Count $paging = NULL) {
      if (NULL !== $paging) {
        $this->_paging = $paging;
      } elseif (NULL === $this->_paging) {
        $this->_paging = new UI\Paging\Count(
          $this->_pagingParameterName,
          $this->papaya()->request->getParameter(
            (string)$this->_pagingParameterName,
            0
          ),
          $this->_itemsCount
        );
        $this->_paging->papaya($this->papaya());
      }
      return $this->_paging;
    }

    /**
     * @return int
     */
    public function getCurrentPage() {
      return $this->paging()->getCurrentPage();
    }

    /**
     * @return int
     */
    public function getCurrentOffset() {
      return $this->paging()->getCurrentOffset();
    }
  }
}
