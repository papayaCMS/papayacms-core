<?php
/**
* Profile creating a filter for a german date string
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
* @version $Id: Date.php 37401 2012-08-15 14:17:24Z weinert $
*/

/**
* Profile creating a filter for a german date string
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterFactoryProfileIsGermanDate extends PapayaFilterFactoryProfile {

  /**
   * @see PapayaFilterFactoryProfile::getFilter()
   */
  public function getFilter() {
    return new PapayaFilterPcre('(^\d{1,2}\.\d{1,2}\.\d{2,4}$)Du');
  }
}
