<?php
/**
* Papaya filter class validating a url host name
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
* @version $Id: Host.php 37430 2012-08-16 18:42:22Z weinert $
*/

/**
* Papaya filter class validating a url host name
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterUrlHost extends PapayaFilterPcre {

  /**
   * set pattern in superclass constructor
   */
  public function __construct() {
    parent::__construct('(^([\pL\d_-]+\.)*([\pL\d-]{2,})(\.[a-z]{2,6})?(:\d{1,5})?$)Du');
  }
}
