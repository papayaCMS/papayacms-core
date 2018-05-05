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
* A listview can have up to four toolbars, at the different corners. This class provides
* access to them. The toolbars can be access using dynamic properties e.g. "$toolbars->topLeft".
*
* @package Papaya-Library
* @subpackage Ui
*
* @property PapayaUiToolbar $topLeft
* @property PapayaUiToolbar $topRight
* @property PapayaUiToolbar $bottomLeft
* @property PapayaUiToolbar $bottomRight
*/
class PapayaUiToolbars extends \PapayaUiControl {

  /**
  * The internal toolbar list
  *
  * @var array(string=>PapayaUiToolbar,...)
  */
  private $_toolbars = array();

  /**
  * String representation of the positions
  *
  * @var array(string=>string,...)
  */
  protected $_positions = array(
    'topLeft' => 'top left',
    'topRight' => 'top right',
    'bottomLeft' => 'bottom left',
    'bottomRight' => 'bottom right'
  );

  /**
  * Append the existing toolbar to the parent xml eleemnt and set the position attribute.
  * Toolbars without elements will not be added.
  *
  * @param \PapayaXmlElement $parent
  */
  public function appendTo(\PapayaXmlElement $parent) {
    /** @var PapayaUiToolbar $toolbar */
    foreach ($this->_toolbars as $position => $toolbar) {
      if (isset($toolbar)) {
        $node = $toolbar->appendTo($parent);
        if ($node instanceof \PapayaXmlElement) {
          $node->setAttribute(
            'position', $this->_positions[$position]
          );
        }
      }
    }
  }

  /**
  * Return the toolbar for the given position. If the position name is invalid an excpetion is
  * thrown.
  *
  * @throws \UnexpectedValueException
  * @param string $name
  * @return \PapayaUiToolbar
  */
  public function __get($name) {
    if (array_key_exists($name, $this->_positions)) {
      if (!isset($this->_toolbars[$name])) {
        $this->_toolbars[$name] = $toolbar = new \PapayaUiToolbar();
        $toolbar->papaya($this->papaya());
      }
      return $this->_toolbars[$name];
    }
    throw new \UnexpectedValueException(
      'UnexpectedValueException: Invalid toolbar position requested.'
    );
  }

  /**
  * Set the toolbar defined by the position name.  If the position name is invalid an excpetion is
  * thrown.
  *
  * @throws \UnexpectedValueException
  * @param string $name
  * @param \PapayaUiToolbar $value
  */
  public function __set($name, $value) {
    \PapayaUtilConstraints::assertInstanceOf(\PapayaUiToolbar::class, $value);
    if (array_key_exists($name, $this->_positions)) {
      $this->_toolbars[$name] = $value;
    } else {
      throw new \UnexpectedValueException(
        'UnexpectedValueException: Invalid toolbar position requested.'
      );
    }
  }
}
