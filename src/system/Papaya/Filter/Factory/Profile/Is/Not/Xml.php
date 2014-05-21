<?php
/**
* Profile creating a filter for string that contains an char that triggers a
* status change in xml (<, > ")
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
* @version $Id: Xml.php 37427 2012-08-16 16:43:40Z weinert $
*/

/**
* Profile creating a filter for string that contains an char that triggers a
* status change in xml (<, > ") and whould need escaping
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterFactoryProfileIsNotXml extends PapayaFilterFactoryProfile {

  /**
   * @see PapayaFilterFactoryProfile::getFilter()
   */
  public function getFilter() {
    return new PapayaFilterPcre('(^[^<>&]+$)Du');
  }
}
