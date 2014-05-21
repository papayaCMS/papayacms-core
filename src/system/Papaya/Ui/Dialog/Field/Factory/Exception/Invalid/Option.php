<?php
/**
* The option name is invalid, aka the option does not exist
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
* @version $Id: Option.php 38361 2013-04-04 12:09:41Z hapke $
*/

/**
* The option name is invalid, aka the option does not exist
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldFactoryExceptionInvalidOption
  extends PapayaUiDialogFieldFactoryException {

  /**
   * Create exception with compiled message
   *
   * @param string $optionName
   */
  public function __construct($optionName) {
    parent::__construct(
      sprintf('Invalid field factory option name "%s".', $optionName)
    );
  }
}
