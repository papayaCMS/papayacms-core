<?php
/**
* This exception is thrown if a value is not defined (or NULL).
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
* @version $Id: Undefined.php 38361 2013-04-04 12:09:41Z hapke $
*/

/**
* This exception is thrown if a value is not defined (or NULL).
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterExceptionUndefined extends PapayaFilterException {

  /**
  * Construct object and set (static) message.
  */
  public function __construct() {
    parent::__construct('Value does not exist.');
  }
}