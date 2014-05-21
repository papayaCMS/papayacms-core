<?php
/**
* Papaya filter class for using a guid - a 16byte hexadecimal string
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
* @version $Id: Guid.php 37513 2012-09-07 14:13:56Z weinert $
*/

/**
* Papaya filter class for using a guid - a 16byte hexadecimal string
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterGuid extends PapayaFilterPcre {

  public function __construct() {
    parent::__construct('(^[a-f-A-F\d]{32}$)D');
  }

}
