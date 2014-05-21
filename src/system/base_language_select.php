<?php
/**
* Language managment UI and Content
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya
* @subpackage Administration
* @version $Id: base_language_select.php 39731 2014-04-08 10:08:07Z weinert $
*/

/**
 * Language managment UI and Content
 *
 * @deprecated
 *
 * @property-read integer $currentLanguageId
 * @property-read array $currentLanguage
 * @property-read array $languages
 * @property-read string languageShort
 *
 * @package Papaya
 * @subpackage Administration
 */
class base_language_select extends base_db {

  /**
  * Parameter name
  * @var string
  */
  var $paramName = 'lngsel';

  /**
  * Languages
  * @var array $languages
  */
  private $_languages = NULL;
  /**
  * Language short
  * @var array $languageShort
  */
  private $_languageShort = NULL;

  /**
  * Get instance of base_language_select
  *
  * @access public
  */
  public static function getInstance() {
    static $lngSelect;
    if (isset($lngSelect) &&
        is_object($lngSelect) &&
        is_a($lngSelect, 'base_language_select')) {
      return $lngSelect;
    } else {
      /** @noinspection PhpDeprecationInspection */
      $lngSelect = new base_language_select;
      return $lngSelect;
    }
  }

  public function __isset($name) {
    switch ($name) {
    case 'currentLanguageId' :
    case 'currentLanguage' :
    case 'languages' :
    case 'languageShort' :
      return TRUE;
    }
    return FALSE;
  }

  /**
   * @return PapayaAdministrationLanguagesSwitch
   */
  private function getSwitch() {
    return $this->papaya()->administrationLanguage;
  }

  /**
   * Provide some property access for data from the switch
   *
   * @param $name
   * @throws BadMethodCallException
   * @return mixed
   */
  public function __get($name) {
    switch ($name) {
    case 'currentLanguageId' :
      return $this->getSwitch()->getCurrent()->id;
    case 'currentLanguage' :
      return $this->getCurrentLanguage();
    case 'languages' :
      if (is_null($this->_languages)) {
        $this->loadLanguages();
      }
      return $this->_languages;
    case 'languageShort' :
      if (is_null($this->_languageShort)) {
        $this->loadLanguages();
      }
      return $this->_languageShort;
    }
    throw new BadMethodCallException(
      sprintf(
        'Unknown property %s::$%s.', get_class($this), $name
      )
    );
  }

  /**
  * Load languages from database
  *
  * @access public
  * @return boolean
  */
  public function loadLanguages() {
    $this->_languages = array();
    $this->_languageShort = array();
    foreach ($this->getSwitch()->languages() as $language) {
      $this->_languages[$language['id']] = array(
        'lng_id' => $language['id'],
        'lng_ident' => $language['identifier'],
        'lng_short' => $language['code'],
        'lng_title' => $language['title'],
        'lng_glyph' => $language['image'],
      );
      $this->_languageShort[$language['id']] = &$this->_languages[$language['id']];
    }
    return TRUE;
  }

  /**
  * Get information about the current set language
  *
  * @return array
  */
  public function getCurrentLanguage() {
    $language = $this->getSwitch()->getCurrent();
    return array(
      'lng_id' => $language['id'],
      'lng_ident' => $language['identifier'],
      'lng_short' => $language['code'],
      'lng_title' => $language['title'],
      'lng_glyph' => $language['image'],
    );
  }

  /**
   * Get language combo
   *
    * @param string $paramName
    * @param string $name
    * @param array $element
    * @param mixed $data
    * @param mixed $emptyElement optional, default NULL
    * @param boolean $nameOnly otpional, default FALSE
    * @access public
    * @return string $result XML
    */
  public function getContentLanguageCombo(
    $paramName, $name, $element, $data, $emptyElement = NULL, $nameOnly = FALSE
  ) {
    $result = '';
    $languages = $this->getSwitch()->languages();
    if (count($languages) > 0) {
      $result .= sprintf(
        '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
        papaya_strings::escapeHTMLChars($paramName),
        papaya_strings::escapeHTMLChars($name)
      );
      if (isset($emptyElement)) {
        $result .= sprintf(
          '<option value="0">%s</option>'.LF,
          papaya_strings::escapeHTMLChars($emptyElement)
        );
      }
      foreach ($languages as $language) {
        $selected = ($language['id'] == $data) ? ' selected="selected"' : '';
        if ($nameOnly) {
          $result .= sprintf(
            '<option value="%d"%s>%s</option>'.LF,
            papaya_strings::escapeHTMLChars($language['id']),
            $selected,
            papaya_strings::escapeHTMLChars($language['title'])
          );
        } else {
          $result .= sprintf(
            '<option value="%d"%s>%s (%s)</option>'.LF,
            papaya_strings::escapeHTMLChars($language['id']),
            $selected,
            papaya_strings::escapeHTMLChars($language['title']),
            papaya_strings::escapeHTMLChars($language['code'])
          );
        }
      }
      $result .= '</select>'.LF;
    } else {
      $result = sprintf(
        '<input type="text" disabled="disabled" value="%s" class="dialogInput dialogScale"/>',
        papaya_strings::escapeHTMLChars($this->_gt('No language found'))
      );
    }
    return $result;
  }

  /**
   * Get XML for content language switch
   *
   * @return string
   */
  public function getContentLanguageLinksXML() {
    return $this->getSwitch()->getXml();
  }

  /**
   * Get current launguage icon
   *
   * @access public
   * @return string
   */
  public function getCurrentLanguageIcon() {
    $image = $this->getSwitch()->getCurrent()->image;
    if (empty($image)) {
      return '';
    } else {
      return './pics/language/'.$image;
    }
  }

  /**
   * Get the current language title
   *
   * @param string $separator optional, default value ' - '
   * @access public
   * @return string
   */
  public function getCurrentLanguageTitle($separator = ' - ') {
    $title = $this->getSwitch()->getCurrent()->title;
    if (empty($title)) {
      return '';
    } else {
      return $title.$separator;
    }
  }
}