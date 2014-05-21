<?php
/**
* A range exception is thrown if a value is not a valid xml fragment.
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
* @version $Id: Xml.php 37434 2012-08-16 20:37:26Z weinert $
*/

/**
* A range exception is thrown if a value is not a valid xml fragment.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterExceptionXml extends PapayaFilterException {

  /**
   * @param PapayaXmlException $e
   */
  public function __construct(PapayaXmlException $e) {
    parent::__construct($e->getMessage());
  }

}