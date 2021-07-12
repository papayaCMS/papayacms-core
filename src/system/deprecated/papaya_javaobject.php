<?php
/**
* papaya java object class
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
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
* @subpackage Core
* @version $Id: papaya_javaobject.php 39260 2014-02-18 17:13:06Z weinert $
*/

/**
* papaya java object class
*
* @package Papaya
* @subpackage Core
*/
class papaya_javaobject extends base_object {

  var $_noJavaMessage = 'Sorry, but you don\'t have Java installed.';
  var $_appletParams = array();
  var $_javaParams = array();
  var $_internalParams = array(
    'align', 'alt', 'archive', 'code', 'codebase',
    'height', 'width', 'vspace', 'hspace',
    'name', 'mayscript',
    'class', 'style', 'title'
  );

  /**
  *
  *
  * @param string $appletURL URL of applet class http://www.domain.tld/path/acrive.jar/applet.class
  * @param string $width
  * @param string $height
  * @param string | NULL $align optional, default value NULL
  * @access public
  * @return string
  */
  function getXHTML($appletURL, $width, $height, $align = NULL) {
    $result = '';
    if (preg_match('~^(.*?/)?(?:([\w\d-]+\.jar)/)([\w\d-]+\.class)$~', $appletURL, $match)) {
      if (!empty($match[1])) {
        $appletCodeBase = $match[1];
      } else {
        $appletCodeBase = NULL;
      }
      $appletArchive = $match[2];
      $appletFile = $match[3];
    } elseif (preg_match('~^(.*?/)([\w\d-]+\.class)$~', $appletURL, $match)) {
      $appletCodeBase = $match[1];
      $appletArchive = NULL;
      $appletFile = $match[2];
    } else {
      $appletCodeBase = NULL;
      $appletArchive = NULL;
      $appletFile = $appletURL;
    }
    $result .= sprintf(
      '<applet code="%s" width="%s" height="%s"%s%s%s%s>'.LF,
      papaya_strings::escapeHTMLChars($appletFile),
      papaya_strings::escapeHTMLChars($width),
      papaya_strings::escapeHTMLChars($height),
      (isset($align) && in_array($align, array('left', 'right', 'center')))
        ? ' align="'.papaya_strings::escapeHTMLChars($align).'"' : '',
      (isset($appletCodeBase))
        ? ' codebase="'.papaya_strings::escapeHTMLChars($appletCodeBase).'"' : '',
      (isset($this->_javaParams['alt']))
        ? ' codebase="'.papaya_strings::escapeHTMLChars($this->_javaParams['alt']).'"' : '',
      (isset($this->_javaParams['mayscript']) && $this->_javaParams['mayscript'])
        ? ' mayscript="mayscript"' : '',
      (isset($this->_javaParams['vspace']))
        ? ' vspace="'.papaya_strings::escapeHTMLChars($this->_javaParams['vspace']).'"' : '',
      (isset($this->_javaParams['hspace']))
        ? ' hspace="'.papaya_strings::escapeHTMLChars($this->_javaParams['hspace']).'"' : ''
    );
    if (isset($appletArchive)) {
      $result .= sprintf(
        '<param name="archive" value="%s" />'.LF,
        papaya_strings::escapeHTMLChars($appletArchive)
      );
    }
    foreach ($this->_appletParams as $paramName => $paramValue) {
      $result .= sprintf(
        '<param name="%s" value="%s" />'.LF,
        papaya_strings::escapeHTMLChars($paramName),
        papaya_strings::escapeHTMLChars($paramValue)
      );
    }
    $result .= $this->getXHTMLString($this->_gt($this->_noJavaMessage)).LF;
    $result .= '</applet>'.LF;
    return $result;
  }

  /**
  * set applet parameters
  *
  * @param array $appletParams
  * @access public
  * @return boolean false if tried to set java vm parameter
  */
  function setAppletParams($appletParams) {
    $result = TRUE;
    $this->_appletParams = array();
    foreach ($appletParams as $paramName => $paramValue) {
      $paramName = strtolower($paramName);
      if (!in_array($paramName, $this->_internalParams)) {
        $this->_appletParams[$paramName] = $paramValue;
      } else {
        $result = FALSE;
      }
    }
    return $result;
  }

  /**
  * set jvm parameters
  *
  * @param string $paramName
  * @param string $paramValue
  * @access public
  * @return void
  */
  function setJavaParam($paramName, $paramValue) {
    $paramName = strtolower($paramName);
    switch ($paramName) {
    case 'alt' :
      if (!empty($paramValue)) {
        $this->_javaParams[$paramName] = $paramValue;
      } else {
        $this->_javaParams[$paramName] = '';
      }
      break;
    case 'mayscript' :
      if ($paramValue) {
        $this->_javaParams[$paramName] = TRUE;
      } else {
        $this->_javaParams[$paramName] = FALSE;
      }
      break;
    case 'vspace' :
    case 'hspace' :
      if (preg_match('~^([1-9]\d+)|(100%|[1-9]\d?%)$~', $paramValue)) {
        $this->_javaParams[$paramName] = $paramValue;
      } elseif (isset($this->_javaParams[$paramName])) {
        unset($this->_javaParams[$paramName]);
      }
      break;
    }
  }
}

