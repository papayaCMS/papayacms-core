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
namespace Papaya\UI\Control\Command;

use Papaya\UI;
use Papaya\XML;

/**
 * A command that adds elements to a provided toolbar, this will not add elements to the DOM but
 * the papayaUI toolbar object.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
abstract class Toolbar extends UI\Control\Command {
  /**
   * @var UI\Toolbar\Elements
   */
  private $_elements;

  /**
   * Append the elements to the provided toolbar, buttons, ...
   */
  abstract public function appendToolbarElements();

  /**
   * @param UI\Toolbar\Elements $elements
   */
  public function __construct(UI\Toolbar\Elements $elements) {
    $this->elements($elements);
  }

  /**
   * Getter/Setter for the toolbar elements
   *
   * @param UI\Toolbar\Elements $elements
   *
   * @return UI\Toolbar\Elements
   */
  public function elements(UI\Toolbar\Elements $elements = NULL) {
    if (NULL !== $elements) {
      $this->_elements = $elements;
    }
    return $this->_elements;
  }

  /**
   * appendTo is used as an trigger only - it actually does not modify the dom.
   *
   * @param XML\Element $parent
   *
   * @return XML\Element
   */
  public function appendTo(XML\Element $parent) {
    $this->appendToolbarElements();
    return $parent;
  }
}
