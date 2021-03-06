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
namespace Papaya\UI;

use Papaya\BaseObject\Interfaces\Properties;
use Papaya\Utility;
use Papaya\XML;

/**
 * A list view can have up to four toolbars, at the different corners. This class provides
 * access to them. The toolbars can be access using dynamic properties e.g. "$toolbars->topLeft".
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property Toolbar $topLeft
 * @property Toolbar $topRight
 * @property Toolbar $bottomLeft
 * @property Toolbar $bottomRight
 */
class Toolbars extends Control implements Properties {
  /**
   * The internal toolbar list
   *
   * @var Toolbar[]
   */
  private $_toolbars = [];

  /**
   * String representation of the positions
   *
   * @var array(string=>string,...)
   */
  protected $_positions = [
    'topLeft' => 'top left',
    'topRight' => 'top right',
    'bottomLeft' => 'bottom left',
    'bottomRight' => 'bottom right'
  ];

  /**
   * Append the existing toolbar to the parent xml element and set the position attribute.
   * Toolbars without elements will not be added.
   *
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
    /** @var Toolbar $toolbar */
    foreach ($this->_toolbars as $position => $toolbar) {
      if (NULL !== $toolbar) {
        $node = $toolbar->appendTo($parent);
        if ($node instanceof XML\Element) {
          $node->setAttribute(
            'position', $this->_positions[$position]
          );
        }
      }
    }
  }

  /**
   * Return the toolbar for the given position. If the position name is invalid an exception is
   * thrown.
   *
   * @throws \UnexpectedValueException
   *
   * @param string $name
   *
   * @return Toolbar
   */
  public function __get($name) {
    if (\array_key_exists($name, $this->_positions)) {
      if (!isset($this->_toolbars[$name])) {
        $this->_toolbars[$name] = $toolbar = new Toolbar();
        $toolbar->papaya($this->papaya());
      }
      return $this->_toolbars[$name];
    }
    throw new \UnexpectedValueException(
      'UnexpectedValueException: Invalid toolbar position requested.'
    );
  }

  /**
   * @param string $name
   * @return bool
   */
  public function __isset($name) {
    return \array_key_exists($name, $this->_positions);
  }

  /**
   * Set the toolbar defined by the position name.  If the position name is invalid an excpetion is
   * thrown.
   *
   * @throws \UnexpectedValueException
   *
   * @param string $name
   * @param Toolbar $value
   */
  public function __set($name, $value) {
    Utility\Constraints::assertInstanceOf(Toolbar::class, $value);
    if (\array_key_exists($name, $this->_positions)) {
      $this->_toolbars[$name] = $value;
    } else {
      throw new \UnexpectedValueException(
        'UnexpectedValueException: Invalid toolbar position requested.'
      );
    }
  }
}
