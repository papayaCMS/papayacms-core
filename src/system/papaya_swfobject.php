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
* papaya swf object class
*
* @package Papaya
* @subpackage Core
*/
class papaya_swfobject extends base_object {

  /**
  * possible swf params
  * name => array ( flash default, papaya default )
  * @var array
  */
  var $_swfParams = array(
    'play' => array(TRUE, TRUE),
    'loop' => array(TRUE, TRUE),
    'menu' => array(TRUE, TRUE),
    'quality' => array('', 'best'),
    'scale' => array('default', 'default'),
    'salign' => array('', ''),
    'wmode' => array('window', 'window'),
    'bgcolor' => array('', ''),
    'base' => array('', ''),
    'swliveconnect' => array(FALSE, FALSE),
    'flashvars' => array('', ''),
    'devicefont' => array(FALSE, FALSE),
    // http://www.adobe.com/cfusion/knowledgebase/index.cfm?id=tn_13331
    'allowscriptaccess' => array('samedomain', 'samedomain'),
    // http://www.adobe.com/cfusion/knowledgebase/index.cfm?id=tn_16494
    'seamlesstabbing' => array(TRUE, TRUE),
    // http://www.adobe.com/support/documentation/en/flashplayer/7/releasenotes.html
    'allowfullscreen' => array(FALSE, TRUE),
    // http://www.adobe.com/devnet/flashplayer/articles/full_screen_mode.html
    'allownetworking' => array('all', 'all')
    // http://livedocs.adobe.com/flash/9.0/main/00001079.html
  );

  /**
  * current swf param values if set
  * @var array
  */
  var $_swfParamValues = array();

  /**
  * flash tag id parameter
  * @var string
  */
  var $_flashObjectId = NULL;

  /**
  * minimium needed flash version
  * @var string
  */
  var $_flashVersion = NULL;

  /**
  * default minimum flash verson
  * @var string
  */
  var $_defaultFlashVersion = '9.0.28';

  /**
  * expressinstall url
  * @var string
  */
  var $_expressInstall = '';

  /**
  * Message if no flash is installed
  * @var string
  */
  var $_noFlashMessage = 'Sorry, but you don\'t have flash installed.';

  /**
  * Revision parameter for swf file to avoid browser caching
  * @var string
  */
  var $_playerRevision = '';

  /**
  * Get XHTML to embed flash object
  *
  * @param string $flashFile
  * @param string $width
  * @param string $height
  * @param string $align
  * @return string
  */
  function getXHTML($flashFile, $width, $height, $align = NULL) {
    if (empty($this->_flashVersion)) {
      if (defined('PAPAYA_FLASH_DEFAULT_VERSION') && PAPAYA_FLASH_DEFAULT_VERSION != '') {
        $this->_flashVersion = PAPAYA_FLASH_DEFAULT_VERSION;
      } else {
        $this->_flashVersion = $this->_defaultFlashVersion;
      }
    }
    if (defined('PAPAYA_FLASH_MIN_VERSION') && PAPAYA_FLASH_MIN_VERSION != '' &&
        version_compare($this->_flashVersion, PAPAYA_FLASH_MIN_VERSION, '<')) {
      $this->_flashVersion = PAPAYA_FLASH_MIN_VERSION;
    }

    $result = '';
    $swfParamStr = '';
    foreach (array_keys($this->_swfParams) as $swfParamName) {
      $swfParamStr .= $this->_getSWFParamXHTML($swfParamName);
    }
    if (empty($this->_flashObjectId)) {
      $this->_flashObjectId = 'flash'.md5(uniqid(rand()));
    }
    if (in_array($align, array('left', 'right', 'center'))) {
      $alignStr = ' align="'.$align.'"';
    } else {
      $alignStr = '';
    }
    if (!empty($this->_playerRevision)) {
      if (FALSE === strpos($flashFile, '?')) {
        $flashFile .= '?rev='.urlencode($this->_playerRevision);
      } else {
        $flashFile .= '&amp;rev='.urlencode($this->_playerRevision);
      }
    }

    if (empty($this->_expressInstall)) {
      if (defined('PAPAYA_ADMIN_PAGE') && PAPAYA_ADMIN_PAGE) {
        $this->_expressInstall = 'script/swfobject/expressInstall.swf';
      } else {
        $themeHandler = new \Papaya\Theme\Handler();
        $this->_expressInstall = $themeHandler->getUrl().'/papaya/swfobject/expressInstall.swf';
      }
    }
    $result .= sprintf(
      '<object data-swfobject="%8$s"'.
        ' id="%6$s" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"'.
        ' width="%2$s" height="%3$s"%7$s>
        <param name="movie" value="%1$s" />
        %4$s
        <!--[if !IE]>-->
        <object name="%6$s" type="application/x-shockwave-flash" data="%1$s"'.
          ' width="%2$s" height="%3$s"%7$s>
          %4$s
        <!--<![endif]-->
          <div class="noFlashMessage">%5$s</div>
        <!--[if !IE]>-->
        </object>
        <!--<![endif]-->
      </object>'.LF,
      papaya_strings::escapeHTMLChars($flashFile),
      papaya_strings::escapeHTMLChars($width),
      papaya_strings::escapeHTMLChars($height),
      $swfParamStr,
      $this->getXHTMLString($this->_gt($this->_noFlashMessage)),
      papaya_strings::escapeHTMLChars($this->_flashObjectId),
      $alignStr,
      \PapayaUtilStringXml::escapeAttribute(
        json_encode(
          array(
            'version' => $this->_flashVersion,
            'installer' => $this->_expressInstall
          )
        )
      )
    );
    return $result;
  }

  /**
  * Escape a string for javascript
  * @param string $str
  * @return string
  */
  function escapeForJS($str) {
    return str_replace(
      array("\n", "'", "\r", "\\"),
      array("\\n", "\\'", "", "\\\\"),
      $str
    );
  }

  /**
  * Get SWF parameter XHTML
  * @param string $swfParamName
  * @return string
  */
  function _getSWFParamXHTML($swfParamName) {
    if (isset($this->_swfParams[$swfParamName])) {
      if (isset($this->_swfParamValues[$swfParamName]) &&
        $this->_swfParams[$swfParamName][0] != $this->_swfParamValues[$swfParamName]) {
        //we have a user defined value that is different from default flash
        return sprintf(
          '<param name="%s" value="%s" />'.LF,
          papaya_strings::escapeHTMLChars($swfParamName),
          $this->_getSWFParamValueXHTML($this->_swfParamValues[$swfParamName])
        );
      } elseif (isset($this->_swfParamValues[$swfParamName]) &&
        $this->_swfParams[$swfParamName][0] == $this->_swfParamValues[$swfParamName]) {
        //we have a user defined value that is equal to the default flash
        return '';
      } elseif ($this->_swfParams[$swfParamName][0] != $this->_swfParams[$swfParamName][1]) {
        //we have a papaya defined value that is different from default flash
        return sprintf(
          '<param name="%s" value="%s" />'.LF,
          papaya_strings::escapeHTMLChars($swfParamName),
          $this->_getSWFParamValueXHTML($this->_swfParams[$swfParamName][1])
        );
      }
    }
    return '';
  }

  /**
  * Get SWF parameter value string
  * @param string|boolean $swfParamValue
  * @return string
  */
  function _getSWFParamValueXHTML($swfParamValue) {
    if (is_bool($swfParamValue)) {
      return ($swfParamValue) ? 'true' : 'false';
    } else {
      return papaya_strings::escapeHTMLChars($swfParamValue);
    }
  }

  /**
  * Set an SWF parameter value
  * @param string $swfParamName
  * @param boolean|string $value
  * @return void
  */
  function setSWFParam($swfParamName, $value = NULL) {
    if (isset($this->_swfParams[$swfParamName])) {
      if (isset($value)) {
        switch ($swfParamName) {
        case 'quality' :
          if (in_array($value, array('low', 'autolow', 'autohigh', 'medium', 'high', 'best'))) {
            $this->_swfParamValues[$swfParamName] = $value;
          } else {
            $this->resetSWFParam($swfParamName);
          }
          break;
        case 'scale' :
          if (in_array($value, array('default', 'noborder', 'extrafit'))) {
            $this->_swfParamValues[$swfParamName] = $value;
          } else {
            $this->resetSWFParam($swfParamName);
          }
          break;
        case 'salign' :
          if (in_array($value, array('', 'l', 't', 'b', 'r', 'tl', 'tr', 'bl', 'br'))) {
            $this->_swfParamValues[$swfParamName] = $value;
          } else {
            $this->resetSWFParam($swfParamName);
          }
          break;
        case 'wmode' :
          if (in_array($value, array('window', 'opaque', 'transparent'))) {
            $this->_swfParamValues[$swfParamName] = $value;
            if ($value == 'transparent') {
              $this->setSWFFlag('allowfullscreen', FALSE);
            }
          } else {
            $this->resetSWFParam($swfParamName);
          }
          break;
        case 'bgcolor' :
          if (preg_match('~#^[\da-fA-F]{6}$~D', $value)) {
            $this->_swfParamValues[$swfParamName] = $value;
          } else {
            $this->resetSWFParam($swfParamName);
          }
          break;
        case 'base' :
        case 'flashvars' :
          if (!empty($value)) {
            $this->_swfParamValues[$swfParamName] = $value;
          } else {
            $this->resetSWFParam($swfParamName);
          }
          break;
        case 'allowscriptaccess' :
          if (in_array($value, array('always', 'never'))) {
            $this->_swfParamValues[$swfParamName] = $value;
          } else {
            $this->resetSWFParam($swfParamName);
          }
          break;
        case 'allownetworking' :
          if (in_array($value, array('all', 'internal', 'none'))) {
            $this->_swfParamValues[$swfParamName] = $value;
          } else {
            $this->resetSWFParam($swfParamName);
          }
          break;
        default :
          $this->setSWFFlag($swfParamName, $value);
          break;
        }
      } else {
        $this->resetSWFParam($swfParamName);
      }
    }
  }

  /**
  * Set a boolean SWF parameter value
  * @param string $swfParamName
  * @param boolean $value
  * @return void
  */
  function setSWFFlag($swfParamName, $value) {
    if (isset($this->_swfParams[$swfParamName])) {
      if (isset($value)) {
        switch ($swfParamName) {
        case 'play' :
        case 'loop' :
        case 'menu' :
        case 'swliveconnect' :
        case 'devicefont' :
        case 'seamlesstabbing' :
          if ($this->_swfParams[$swfParamName][1] != (boolean)$value) {
            $this->_swfParamValues[$swfParamName] = (boolean)$value;
          } else {
            $this->resetSWFParam($swfParamName);
          }
          break;
        case 'allowfullscreen' :
          if ($this->_swfParams[$swfParamName][1] != (boolean)$value) {
            $this->_swfParamValues[$swfParamName] = (boolean)$value;
            if ((boolean)$value &&
                isset($this->_swfParamValues['wmode']) &&
                $this->_swfParamValues['wmode'] == 'transparent') {
              $this->_swfParamValues['wmode'] = 'window';
            }
          } else {
            $this->resetSWFParam($swfParamName);
            if ($this->_swfParams[$swfParamName][1] &&
                isset($this->_swfParamValues['wmode']) &&
                $this->_swfParamValues['wmode'] == 'transparent') {
              $this->_swfParamValues['wmode'] = 'window';
            }
          }
          break;
        }
      } else {
        $this->resetSWFParam($swfParamName);
      }
    }
  }

  /**
  * Reset a SWF parameter value
  * @param string $swfParamName
  * @return void
  */
  function resetSWFParam($swfParamName) {
    if (isset($this->_swfParamValues[$swfParamName])) {
      unset($this->_swfParamValues[$swfParamName]);
    }
  }

  /**
  * Set the "no flash" message
  * @param string $noFlashMessage
  */
  function setNoFlashMessage($noFlashMessage) {
    if (!empty($noFlashMessage)) {
      $this->_noFlashMessage = $noFlashMessage;
    }
  }

  /**
  * Set Flash variables
  * @param array $flashVars
  * @return void
  */
  function setFlashVars($flashVars) {
    if (is_array($flashVars) && count($flashVars) > 0) {
      $flashVarStr = '';
      foreach ($flashVars as $key => $val) {
        if (is_scalar($val)) {
          $flashVarStr .= '&'.rawurlencode($key).'='.rawurlencode($val);
        } elseif (is_array($val) && count($val) > 0) {
          foreach ($val as $subKey => $subVal) {
            if (is_scalar($val)) {
              $flashVarStr .= '&'.rawurlencode($key).'.'.rawurlencode($subKey).
                '='.rawurlencode($subVal);
            }
          }
        }
      }
      $this->_swfParamValues['flashvars'] = substr($flashVarStr, 1);
    } else {
      $this->resetSWFParam('flashvars');
    }
  }

  /**
  * Set Flash object id
  * @param string $idStr
  * @return void
  */
  function setObjectId($idStr) {
    $this->_flashObjectId = $idStr;
  }

  /**
  * Set Flash version
  * @param string $versionStr
  * @return boolean
  */
  function setFlashVersion($versionStr) {
    if (preg_match('~(?:[1-9]\d*)(?:\.\d+(?:\.\d+)?)?~', $versionStr)) {
      $this->_flashVersion = $versionStr;
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Set player revision string directly
  * @param string $revision
  * @return boolean
  */
  function setPlayerRevision($revision) {
    if (preg_match('~(^[a-z\d_-]+)~', $revision, $match)) {
      $this->_playerRevision = $match[0];
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Set player revision string using the name of a local file
  * @param string $revisionFileName
  * @return boolean
  */
  function setPlayerRevisionByFile($revisionFileName) {
    if (file_exists($revisionFileName) &&
        is_file($revisionFileName) &&
        is_readable($revisionFileName)) {
      if ($fh = fopen($revisionFileName, 'r')) {
        $line = trim(fgets($fh, 100));
        if (preg_match('(^[a-z\d_-]+)i', $line, $match)) {
          $this->_playerRevision = $match[0];
          fclose($fh);
          return TRUE;
        }
        fclose($fh);
      }
    }
    return FALSE;
  }

  /**
  * Set player revision string using the timestamp of a local file
  * @param string $playerFileName
  * @return boolean
  */
  function setPlayerRevisionByFileDate($playerFileName) {
    if (file_exists($playerFileName) &&
        is_file($playerFileName)) {
      $revisionTime = filectime($playerFileName);
      if ($revisionTime > 0) {
        $this->_playerRevision = date('YmdHis', $revisionTime);
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Set player revision string using a timestamp
  * @param integer $revisionTime
  * @return boolean
  */
  function setPlayerRevisionByDate($revisionTime) {
    if ($revisionTime > 0) {
      $this->_playerRevision = date('YmdHis', $revisionTime);
      return TRUE;
    }
    return FALSE;
  }
}

