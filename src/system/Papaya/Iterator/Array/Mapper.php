<?php
/**
* This iterator allows convert array elements into scalars by fetching one specified subelement
* from the array.
*
* @copyright 2013 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Iterator
* @version $Id: Mapper.php 39409 2014-02-27 16:36:19Z weinert $
*/

/**
* This iterator allows convert array elements into scalars by fetching one specified subelement
* from the array.
*
* @package Papaya-Library
* @subpackage Iterator
*/
class PapayaIteratorArrayMapper extends PapayaIteratorCallback {

  private $_elementName;

  /**
   * Create object, store iterator data and element name.
   *
   * The element name can be an array. In this case the first found element is used.
   *
   * @param array|Traversable $iterator
   * @param mixed $elementName
   */
  public function __construct($iterator, $elementName) {
    parent::__construct($iterator, array($this, 'callbackMapElement'), self::MODIFY_VALUES);
    $this->_elementName = $elementName;
  }

  /**
   * Callback used to map the array elements to scalars
   *
   * @param array $element
   * @return mixed
   */
  public function callbackMapElement($element) {
    return PapayaUtilArray::get($element, $this->_elementName, NULL);
  }
}
