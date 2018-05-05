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
* A callback exception is thrown if an callback is not callable or returns FALSE
*
* This exception call is a abstract superclass for callback exceptions, It is not used directly.
*
* @package Papaya-Library
* @subpackage Filter
*/
abstract class PapayaFilterExceptionCallback extends \PapayaFilterException {

  /**
  * Private property containing the callback
  * @var Callback
  */
  protected $_callback;

  /**
  * Construct object an save callback variable
  *
  * @param string $message
  * @param \Callback $callback
  */
  public function __construct($message, $callback) {
    parent::__construct($message);
    $this->_callback = $callback;
  }

  /**
  * Return callback from private property
  *
  * @return \Callback
  */
  public function getCallback() {
    return $this->_callback;
  }

  /**
  * Convert a callback into a human readable string
  *
  * @param \Callback $callback
  * @return string
  */
  protected function callbackToString($callback) {
    if (is_array($callback)) {
      if (is_object($callback[0])) {
        return get_class($callback[0]).'->'.$callback[1];
      } else {
        return $callback[0].'::'.$callback[1];
      }
    } elseif (is_string($callback) &&
              FALSE === strpos($callback, '{') &&
              0 !== strpos($callback, "\x00")) {
      return $callback;
    } else {
      return 'function() {...}';
    }
  }
}
