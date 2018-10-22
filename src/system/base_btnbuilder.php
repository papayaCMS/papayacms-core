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
* Button bar delegation-class
*
* @package Papaya
* @subpackage Administration
*/
class base_btnbuilder extends base_object {

  /**
  * Toolbar array
  * @var array $toolbar
  */
  var $toolbar = NULL;
  /**
  * Images array
  * @var array $images
  */
  var $images = NULL;

  /**
  * Clear toolbar
  *
  * @access public
  */
  function clear() {
    unset($this->toolbar);
  }

  /**
  * Add a Button to button bar
  *
  * @param string $caption the caption of the button
  * @param string $href the link that is called on clicking the button
  * @param string $img the image displayed besides the button
  * @param string $hint a mouseover hint explaining the buttons function with more detail
  * @param boolean $down whether the button is impressed or not
  * @param boolean $noTranslation whether the caption should be auto-translated or not
  * @access public
  */
  function addButton(
    $caption, $href, $img = '', $hint = '', $down = FALSE, $noTranslation = FALSE
  ) {
    if (!$noTranslation) {
      $hint = $this->_gt($hint);
      $caption = $this->_gt($caption);
    }
    $downString = (($down) ? ' down="down"' : '');
    if (isset($this->images[$img])) {
      $glyph = ' glyph="'.papaya_strings::escapeHTMLChars($this->images[$img]).'"';
    } elseif (preg_match('~^module:([a-f\d]{32})/(.+)~', $img, $regs)) {
      $glyph = ' glyphscript="'.\Papaya\Administration\UI\Route::EXTENSIONS_IMAGE.'?module='.
        urlencode($regs[1]).'&amp;src='.urlencode($regs[2]).'"';
    } else {
      $glyph = ' glyph="'.papaya_strings::escapeHTMLChars($img).'"';
    }
    $button = sprintf(
      '<button title="%s" href="%s"%s hint="%s" target="_self"%s/>',
      papaya_strings::escapeHTMLChars($caption),
      papaya_strings::escapeHTMLChars($href),
      $glyph,
      papaya_strings::escapeHTMLChars($hint),
      $downString
    );
    $this->toolbar[] = array('btn', $button);
  }

  /**
  * Add combo
  *
  * @param string $caption list title
  * @param string $href url location
  * @param string $paramName group name for parameters
  * @param string $default default key to set a selected element
  * @param array $options optione keys and values
  * @access public
  */
  function addCombo($caption, $href, $paramName, $default, $options) {
    $combo = sprintf(
      '<combo title="%s" href="%s" name="%s">',
      papaya_strings::escapeHTMLChars($this->_gt($caption)),
      papaya_strings::escapeHTMLChars($href),
      papaya_strings::escapeHTMLChars($paramName)
    );
    foreach ($options as $key => $val) {
      if ($key == $default) {
        $selected = ' selected="selected"';
      } else {
        $selected = '';
      }
      $combo .= sprintf(
        '<option value="%s"%s>%s</option>',
        papaya_strings::escapeHTMLChars($key),
        $selected,
        papaya_strings::escapeHTMLChars($val)
      );
    }
    $combo .= '</combo>';
    $this->toolbar[] = array('combo', $combo);
  }

  /**
  * Add separator to toolbar
  *
  * @access public
  */
  function addSeparator() {
    if (is_array($this->toolbar) && count($this->toolbar) > 0) {
      $last = end($this->toolbar);
      if (isset($last) && is_array($last)) {
        if ($last[0] != '-') {
          $this->toolbar[] = array('-', '<seperator/>');
        }
      }
    }
  }

  /**
  * Add separator alias method
  *
  * @access public
  */
  function addSeperator() {
    $this->addSeparator();
  }

  /**
  * Output function
  *
  * @access public
  * @return string
  */
  function getXML() {
    $result = '';
    if (isset($this->toolbar) && is_array($this->toolbar)) {
      $last = end($this->toolbar);
      if ($last[0] == '-') {
        array_pop($this->toolbar);
      }
      foreach ($this->toolbar as $button) {
        $result .= $button[1].LF;
      }
    }
    return $result;
  }

}


