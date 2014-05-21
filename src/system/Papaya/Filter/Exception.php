<?php
/**
* Abstract superclass for filter exceptions
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
* @version $Id: Exception.php 37339 2012-08-02 13:46:19Z weinert $
*/

/**
* Abstract superclass for filter exceptions
*
* Child classes of this class are used to throw exceptions for validation errors
*
* @package Papaya-Library
* @subpackage Filter
*/
abstract class PapayaFilterException extends PapayaException {

}