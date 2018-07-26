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
* Switch richtext editor in UI
*
* @package Papaya
* @subpackage Administration
*/
class base_switch_richtext extends base_object {

  /**
  * Parameter name
  * @var string
  */
  var $paramName = 'rtswi';


  /**
  * use richtext editor
  * @var boolean $useRichtext
  */
  var $useRichtext = TRUE;

  /**
   * @var boolean
   */
  public $showRichtext = TRUE;

  /**
  * Get instance of base_language_select
  *
  * @access public
  */
  public static function getInstance() {
    static $rtSwitch;
    if (isset($rtSwitch) && is_object($rtSwitch) &&
        is_a($rtSwitch, 'base_switch_richtext')) {
      return $rtSwitch;
    } else {
      $rtSwitch = new base_switch_richtext();
      return $rtSwitch;
    }
  }

  /**
  * PHP5 constructor
  *
  * @access public
  */
  function __construct() {
    $this->initialize();
  }

  /**
  * Initialization
  *
  * @access public
  */
  function initialize() {
    $this->initializeParams();
    $this->sessionParamName = 'PAPAYA_SESS_richtext_'.$this->paramName;
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);

    if (isset($this->sessionParams['use_richtext']) &&
        in_array($this->sessionParams['use_richtext'], array(0, 1))) {
      $this->useRichtext = (int)$this->sessionParams['use_richtext'];
    } elseif (isset($GLOBALS['PAPAYA_USER']) &&
              isset($GLOBALS['PAPAYA_USER']->options['PAPAYA_USE_RICHTEXT']) &&
              (int)$GLOBALS['PAPAYA_USER']->options['PAPAYA_USE_RICHTEXT'] > 0) {
      $this->useRichtext =
        (int)$GLOBALS['PAPAYA_USER']->options['PAPAYA_USE_RICHTEXT'];
    } elseif (defined('PAPAYA_USE_RICHTEXT') &&
              in_array(PAPAYA_USE_RICHTEXT, array(0, 1))) {
      $this->useRichtext = (int)PAPAYA_USE_RICHTEXT;
    } else {
      $this->useRichtext = 0;
    }

    if (isset($GLOBALS['PAPAYA_USER']) &&
              isset($GLOBALS['PAPAYA_USER']->options['PAPAYA_USE_RICHTEXT'])) {
      $this->showRichtext =
        (bool)$GLOBALS['PAPAYA_USER']->options['PAPAYA_USE_RICHTEXT'];
    } elseif (defined('PAPAYA_USE_RICHTEXT')) {
      $this->showRichtext = (bool)PAPAYA_USE_RICHTEXT;
    } else {
      $this->showRichtext = FALSE;
    }

    $reloadPage = FALSE;
    if (isset($this->params['switch_richtext']) &&
        $this->useRichtext != $this->params['switch_richtext'] &&
        in_array($this->params['switch_richtext'], array(0, 1))) {
      $this->useRichtext = (int)$this->params['switch_richtext'];
      $reloadPage = TRUE;
    }

    $this->setSessionValue('PAPAYA_SESS_USE_RICHTEXT', (bool)$this->useRichtext);
    $this->sessionParams['use_richtext'] = (bool)$this->useRichtext;
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);

    if ($reloadPage) {
      $protocol = \PapayaUtilServerProtocol::get();
      $toUrl = $protocol."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
      if (!(defined('PAPAYA_DISABLE_XHEADERS') && PAPAYA_DISABLE_XHEADERS)) {
        header('X-Papaya-Status: switching richtext editor');
      }
      header('Location: '.str_replace(array('\r', '\n'), '', $toUrl));
      exit;
    }
  }

  /**
  * Get XML for switch richtext editor links
  * @return string
  */
  function getSwitchRichtextLinksXML() {
    if ($this->showRichtext) {
      $result = sprintf(
        '<links title="%s" align="right">',
        papaya_strings::escapeHTMLChars($this->_gt('Richtext Editor'))
      );
      $options = array(
        1 => $this->_gt('On'),
        0 => $this->_gt('Off')
      );
      $currentValue = (isset($this->useRichtext) && $this->useRichtext == 1) ? 1 : 0;
      foreach ($options as $optionValue => $optionTitle) {
        $selected = ($currentValue == $optionValue) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<link href="%s" title="%s" %s/>',
          papaya_strings::escapeHTMLChars(
            $this->getLink(array('switch_richtext' => $optionValue))
          ),
          papaya_strings::escapeHTMLChars($optionTitle),
          $selected
        );
      }
      $result .= '</links>';
      return $result;
    }
    return '';
  }

}
