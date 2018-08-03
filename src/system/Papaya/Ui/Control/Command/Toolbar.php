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

namespace Papaya\Ui\Control\Command;
/**
 * A command that adds elements to a provided toolbar, this will not add elements to the DOM but
 * the papayaUI toolbar obkject.
 *
 * @package Papaya-Library
 * @subpackage Ui
 */
abstract class Toolbar extends \Papaya\Ui\Control\Command {

  /**
   * @var \Papaya\Ui\Toolbar\Elements
   */
  private $_elements;

  /**
   * Append the elements to the provided toolbar, buttons, dropdowns, ...
   */
  abstract public function appendToolbarElements();

  /**
   * @param \Papaya\Ui\Toolbar\Elements $elements
   */
  public function __construct(\Papaya\Ui\Toolbar\Elements $elements) {
    $this->elements($elements);
  }

  /**
   * Getter/Setter for the toolbar elements
   *
   * @param \Papaya\Ui\Toolbar\Elements $elements
   * @return \Papaya\Ui\Toolbar\Elements
   */
  public function elements(\Papaya\Ui\Toolbar\Elements $elements = NULL) {
    if (NULL !== $elements) {
      $this->_elements = $elements;
    }
    return $this->_elements;
  }

  /**
   * appendTo is used as an trigger only - it actually does not modify the dom.
   *
   * @param \Papaya\Xml\Element $parent
   * @return \Papaya\Xml\Element
   */
  public function appendTo(\Papaya\Xml\Element $parent) {
    $this->appendToolbarElements();
    return $parent;
  }
}
