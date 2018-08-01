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
* A menu element group. This is a sublist of menu elements like buttons with an group caption.
*
* @package Papaya-Library
* @subpackage Ui
*
* @property string|\PapayaUiString $caption
* @property \PapayaUiToolbarElements $elements
*/
class PapayaUiToolbarGroup
  extends \PapayaUiToolbarSet {

  /**
  * A caption for the group
  *
  * @var string|\PapayaUiString
  */
  protected $_caption = '';

  /**
  * Declare properties
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'caption' => array('_caption', '_caption'),
    'elements' => array('elements', 'elements')
  );

  /**
  * Create object and store group caption
  *
  * @param string|\PapayaUiString $caption
  */
  public function __construct($caption) {
    $this->_caption = $caption;
  }

  /**
  * Append group and elements to the output xml.
  *
  * @param \Papaya\Xml\Element $parent
  * @return \Papaya\Xml\Element|NULL
   */
  public function appendTo(\Papaya\Xml\Element $parent) {
    if (count($this->elements()) > 0) {
      $group = $parent->appendElement(
        'group',
        array(
          'title' => (string)$this->_caption
        )
      );
      $this->elements()->appendTo($group);
      return $group;
    }
    return NULL;
  }
}
