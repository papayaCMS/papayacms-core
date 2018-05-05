<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

/**
* Language caption administration control. A string castable object that fetches
* the current language title from the language switch and puts if before the
* given string.
*
* @package Papaya-Library
* @subpackage Administration
*/
class PapayaAdministrationLanguagesCaption extends \PapayaObject {

  private $_suffix = '';
  private $_separator = ' - ';

  private $_string = NULL;

  /**
   * Create object and store arguments into variables
   *
   * @param string $string
   * @param string $separator
   */
  public function __construct($string = '', $separator = ' - ') {
    $this->_suffix = $string;
    $this->_separator = $separator;
  }

  /**
  * Prefix given string with administration lanugage title if available
  *
  * return string
  */
  public function __toString() {
    if (is_null($this->_string)) {
      $language = NULL;
      $suffix = (string)$this->_suffix;
      if (isset($this->papaya()->administrationLanguage)) {
        $language = $this->papaya()->administrationLanguage->getCurrent();
      }
      if (empty($language)) {
        $this->_string = $suffix;
      } elseif (empty($suffix)) {
        $this->_string = $language['title'];
      } else {
        $this->_string = $language['title'].$this->_separator.$suffix;
      }
    }
    return $this->_string;
  }

}
