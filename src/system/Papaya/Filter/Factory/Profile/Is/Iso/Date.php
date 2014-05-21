<?php
/**
* Profile creating a filter for a  ISO 8601 date string
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
* @subpackage Filter
* @version $Id: Date.php 37405 2012-08-15 18:06:50Z weinert $
*/

/**
* Profile creating a filter for a  ISO 8601 date string
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterFactoryProfileIsIsoDate extends PapayaFilterFactoryProfile {

  /**
   * @see PapayaFilterFactoryProfile::getFilter()
   */
  public function getFilter() {
    return new PapayaFilterPcre(
      '(^([12]\d{3})-(\d|(0\d)|(1[0-2]))-(([012]?\d)|(3[01]))$)Du'
    );
  }
}
