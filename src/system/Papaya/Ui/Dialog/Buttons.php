<?php
/**
* A list of dialog buttons
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
* @subpackage Ui
* @version $Id: Buttons.php 35573 2011-03-29 10:48:18Z weinert $
*/

/**
* A list of dialog buttons
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogButtons extends PapayaUiDialogElements {

  /**
  * Only PapayaUiDialogButton objects are allows in this list
  * @var string
  */
  protected $_itemClass = 'PapayaUiDialogButton';
}