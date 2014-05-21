<?php
/**
* Base class of parser plugins
*
* image plugins must be inherited from this superclass
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
* @subpackage Modules
* @version $Id: base_parsermodule.php 39734 2014-04-08 19:01:37Z weinert $
*/

/**
* Base class of parser plugins
*
* image  plugins must be inherited from this superclass
*
* @package Papaya
* @subpackage Modules
*/
class base_parsermodule extends base_plugin {

  /**
  * Parser Object Reference
  * @var object papaya_parser $parserObj
  */
  var $parserObj = NULL;

  /**
  * Override constrcutor to avoid unnessesary initialization
  *
  * @param $aOwner
  */
  function __construct($aOwner = NULL) {
    $this->parentObj = $aOwner;
  }

  /**
   * Initialize Plugin
   *
   * @param papaya_parser $parser
   * @return bool|void
   */
  public function initialize($parser) {
    $this->parserObj = $parser;
  }

  /**
  * compile data for added tags
  *
  * @param array &$papayaTags
  * @access public
  */
  function compileData(&$papayaTags) {

  }

  /**
  * Expand tag for richtext editor
  *
  * @param array &$papayaTag
  * @param array &$original
  * @param boolean $stripDynamicAttributes optional, default value FALSE
  * @access public
  * @return string
  */
  function expandTag(&$papayaTag, &$original, $stripDynamicAttributes = FALSE) {
    return '';
  }

  /**
  * create output tag
  *
  * @param array &$papayaTag
  * @access public
  * @return string
  */
  function createTag($papayaTag) {
    return '';
  }

  /**
  * get additonal data (xml)
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    return '';
  }

  /**
   * Get web link
   *
   * @param mixed $pageId optional, page id, default value NULL
   * @param integer $lng optional, language id, default value NULL
   * @param string $mode optional, default value 'page'
   * @param mixed $params optional, default value NULL
   * @param mixed $paramName optional, default value NULL
   * @param string $text optional, default value empty string
   * @param integer $categId optional, default value NULL
   * @access public
   * @return string Weblink
   */
  public function getWebLink(
    $pageId = NULL,
    $lng = NULL,
    $mode = NULL,
    $params = NULL,
    $paramName = NULL,
    $text = '',
    $categId = NULL
  ) {
    $href = parent::getWebLink($pageId, $lng, $mode, $params, $paramName, $text, $categId);
    if (isset($this->parserObj->linkModeAbsolute) && $this->parserObj->linkModeAbsolute) {
      return $this->getAbsoluteURL($href, $text, FALSE);
    } else {
      return $href;
    }
  }
}