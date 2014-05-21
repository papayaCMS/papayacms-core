<?php
/**
* Papaya Request Parser superclass
*
* @copyright 2009 by papaya Software GmbH - All rights reserved.
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
* @subpackage Request
* @version $Id: Parser.php 35317 2011-01-14 10:02:45Z weinert $
*/

/**
* Papaya Request Parser super class
* @package Papaya-Library
* @subpackage Request
*/
abstract class PapayaRequestParser {

  /**
  * parse request
  * @param PapayaUrl $url
  * @return FALSE|array
  */
  abstract public function parse($url);

  /**
  * If a parser hast the "last" property, the loop is finished if it matches.
  * @return boolean
  */
  public function isLast() {
    return TRUE;
  }
}