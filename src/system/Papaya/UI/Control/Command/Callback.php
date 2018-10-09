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
use Papaya\Utility;
use Papaya\XML;

/**
 * A command that uses a callback to append elements to the DOM. Allows for direct implementation
 * of simple commands
 *
 * The first argument of the callback is the parent xml element.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Callback extends UI\Control\Command {
  /**
   * @var callable
   */
  private $_callback;

  /**
   * @param \Callable $callback
   */
  public function __construct($callback) {
    Utility\Constraints::assertCallable($callback);
    $this->_callback = $callback;
  }

  /**
   * appendTo is used as an trigger only - it actually does not modify the dom.
   *
   * @param XML\Element $parent
   *
   * @return XML\Element
   */
  public function appendTo(XML\Element $parent) {
    $callback = $this->_callback;
    return $callback($parent);
  }
}
