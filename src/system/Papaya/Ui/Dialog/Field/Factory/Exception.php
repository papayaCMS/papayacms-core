<?php
/**
* Abstract superclass for field factory exceptions.
*
* @copyright 2012 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Exception.php 37350 2012-08-03 12:50:02Z weinert $
*/

/**
* Abstract superclass for field factory exceptions.
*
* A child class is used if something unexpected happend while creating a field using the factory
*
* @package Papaya-Library
* @subpackage Ui
*/
abstract class PapayaUiDialogFieldFactoryException extends PapayaException {

}