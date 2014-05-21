<?php
/**
* Profile creating an text (letters, punctuation, spaces) filter
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
* @version $Id: Numbers.php 37438 2012-08-20 12:10:38Z weinert $
*/

/**
* Profile creating an text (letters, punctuation, spaces) filter
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterFactoryProfileIsTextWithNumbers extends PapayaFilterFactoryProfile {

  /**
   * @see PapayaFilterFactoryProfile::getFilter()
   */
  public function getFilter() {
    return new PapayaFilterText(PapayaFilterText::ALLOW_SPACES | PapayaFilterText::ALLOW_DIGITS);
  }
}
