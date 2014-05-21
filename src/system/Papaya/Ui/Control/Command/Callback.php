<?php
/**
* A command that uses a callback to append elements to the DOM. Allows for direct implementation
* of simple commands
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
* @subpackage Ui
* @version $Id: Callback.php 38904 2013-11-04 13:23:44Z weinert $
*/

/**
* A command that uses a callback to append elements to the DOM. Allows for direct implementation
* of simple commands
*
* The first argument of the callback is the parent xml element.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiControlCommandCallback extends PapayaUiControlCommand {

  /**
   * @var Callable
   */
  private $_callback = NULL;

  /**
   * @param Callable $callback
   */
  public function __construct($callback) {
    PapayaUtilConstraints::assertCallable($callback);
    $this->_callback = $callback;
  }

  /**
   * appendTo is used as an trigger only - it actually does not modify the dom.
   *
   * @param PapayaXmlElement $parent
   * @return PapayaXmlElement
   */
  public function appendTo(PapayaXmlElement $parent) {
    return call_user_func($this->_callback, $parent);
  }
}