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
 * A textarea (multiline input) field, that will use the filter on each line
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldTextareaLines extends \PapayaUiDialogFieldTextarea {

  protected $_type = 'lines';

  public function __construct(
    $caption, $name, $lines = 10, $default = NULL, Papaya\Filter $filter = NULL
  ) {
    parent::__construct(
      $caption,
      $name,
      $lines,
      $default,
      new \PapayaFilterLines($filter)
    );
  }

  /**
  * Append field and textarea output to DOM
  *
  * @param \PapayaXmlElement $parent
  */
  public function appendTo(\PapayaXmlElement $parent) {
    $field = $this->_appendFieldTo($parent);
    $field->appendElement(
      'textarea',
      array(
        'type' => $this->_type,
        'name' => $this->_getParameterName($this->getName()),
        'lines' => $this->_lineCount
      ),
      (string)$this->getCurrentValue()
    );
  }
}
