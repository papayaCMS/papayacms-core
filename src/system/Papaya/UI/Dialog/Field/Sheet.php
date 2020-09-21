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
namespace Papaya\UI\Dialog\Field {

  use Papaya\UI;
  use Papaya\XML;

  /**
   * A field containing a sheet control.
   */
  class Sheet extends UI\Dialog\Field {
    /**
     * @var UI\ListView
     */
    private $_sheet;

    /**
     * @param UI\Sheet $sheet
     */
    public function __construct(UI\Sheet $sheet = NULL) {
      if (isset($sheet)) {
        $this->_sheet = $sheet;
      }
    }

    /**
     * @param UI\Sheet $sheet
     * @return UI\Sheet
     */
    public function sheet(UI\Sheet $sheet = NULL) {
      if (NULL !== $sheet) {
        $this->_sheet = $sheet;
      } elseif (NULL === $this->_sheet) {
        $this->_sheet = new UI\Sheet();
        $this->_sheet->papaya($this->papaya());
      }
      return $this->_sheet;
    }

    /**
     * Append field to dialog xml element.
     *
     * @param XML\Element $parent
     * @return XML\Element
     */
    public function appendTo(XML\Element $parent) {
      $field = $this->_appendFieldTo($parent);
      $field->append($this->sheet());
      return $field;
    }
  }
}
