<?php
/**
* Papaya database connection exception, thrown if an error occurs during connect
*
* @copyright 2002-2011 by papaya Software GmbH - All rights reserved.
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
* @subpackage Database
* @version $Id: Connect.php 36089 2011-08-15 09:13:41Z weinert $
*/

/**
* Papaya database connection exception, thrown if an error occurs during connect
*
* @package Papaya-Library
* @subpackage Database
*/
class PapayaDatabaseExceptionConnect extends PapayaDatabaseException {

  /**
  * Create exception and store values
  *
  * @param string $message
  * @param integer $code
  */
  public function __construct($message, $code = 0) {
    parent::__construct($message, $code, PapayaDatabaseException::SEVERITY_ERROR);
  }
}