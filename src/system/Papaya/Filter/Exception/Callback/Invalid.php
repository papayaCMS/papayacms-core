<?php
/**
* This exception is thrown if a the callback is invalid.
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @subpackage Filter
* @version $Id: Invalid.php 34318 2010-06-04 14:35:27Z weinert $
*/

/**
* This exception is thrown if a the callback is invalid.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterExceptionCallbackInvalid extends PapayaFilterExceptionCallback {

  /**
  * Construct object with callback informations
  *
  * @param Callback $callback
  */
  public function __construct($callback) {
    parent::__construct(
      sprintf(
        'Invalid callback specified: "%s"',
        $this->callbackToString($callback)
      ),
      $callback
    );
  }
}