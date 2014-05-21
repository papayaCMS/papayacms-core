<?php
/**
* This exception is thrown if a value is not considered as float.
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
* @version $Id: Float.php 36047 2011-08-05 16:06:05Z bphilipp $
*/

/**
* This exception is thrown if a value is not considered as float.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterExceptionNotFloat extends PapayaFilterException {
  public function __construct($value) {
    parent::__construct("Value is not a float: $value", $this->code);
  }
}