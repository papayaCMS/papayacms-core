<?php
/**
* Profile creating pcre filter using the options as an string containing the pattern
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
* @version $Id: Regex.php 37371 2012-08-06 15:12:26Z weinert $
*/

/**
* Profile creating pcre filter using the options as an string containing the pattern
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterFactoryProfileRegex extends PapayaFilterFactoryProfile {

  /**
   * @see PapayaFilterFactoryProfile::getFilter()
   */
  public function getFilter() {
    return new PapayaFilterPcre((string)$this->options());
  }
}

