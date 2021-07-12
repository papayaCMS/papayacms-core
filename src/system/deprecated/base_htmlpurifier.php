<?php
/**
* HTML Purifier Wrapper
*
* HTML Purifier information
* current version 2.1.3-lite, LGPL v2.1+ (http://www.gnu.org/licenses/lgpl.html)
*
* <code>
* #for example, we create a white list of a,em,strong,p,div,media,span and add the youtube filter
* #definition id for this setup/config
* <?php
*     $htmlpurifier = new base_htmlpurifier();
*     $htmlpurifier->addFilters('YouTube');
*     $htmlpurifier->setUp(array(
*        'HTML:Allowed' => 'a[title|href],em,strong,p[class],div[class],
*            media[src|width|height|align|resize|tspace|rspace|
*            bspace|lspace|href|topic|popup],span[class]',
*        'HTML:DefinitionID' => 'papaya-richtext',
*        'HTML:DefinitionRev' => 1
*     ));
*
* #adding the media element
* <?php
*     $htmlpurifier->addElement('media', 'Inline', 'Empty', 'Custom', array(
*       'src*' => 'Text', // required
*       'width' => 'Number',
*       'height' => 'Number',
*       'align' => 'Enum#left|right|top|bottom',
*       'resize' => 'Enum#max|min|mincrop|abs',
*       'tspace' => 'Number',
*       'rspace' => 'Number',
*       'bspace' => 'Number',
*       'lspace' => 'Number',
*       'href' => 'URI',
*       'topic' => 'Number',
*       'popup' => 'Enum#1',
*     ));
*
* #now, image you have dirty html in $this->params['baduserinput']
* <?php
* $xhtml = $htmlpurifier->purifyInput($this->params['baduserinput']);
*
* </code>
*
* @see CMSConfiguration http://htmlpurifier.org/live/configdoc/plain.html
* @see enduse doc http://htmlpurifier.org/docs/
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
* @subpackage System
* @version $Id: base_htmlpurifier.php 39733 2014-04-08 18:10:55Z weinert $
*/

/**
* html purifier path
*/
define(
  'HTMLPURIFIER_INCLUDE_PATH',
  PAPAYA_INCLUDE_PATH.'external/htmlpurifier/library/'
);

/**
* HTML Purifier
*
* @package Papaya
* @subpackage System
*/
class base_htmlpurifier extends base_db {

  /**
  * Parameters
  * @var array $params
  */
  var $params = NULL;

  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName = 'p';

  /**
  * html purifier instance
  * @var HTMLPurifier
  */
  protected $_htmlPurifier = NULL;

  /**
  * An combined getter/setter for the HTMLPurifier
  *
  * @param HTMLPurifier $object
  * @return HTMLPurifier
  */
  public function htmlPurifier(HTMLPurifier $object = NULL) {
    if (isset($object)) {
      $this->_htmlPurifier = $object;
    }
    if (is_null($this->_htmlPurifier)) {
      if (!$this->checkRequiredVersion()) {
        return FALSE;
      }
      $this->addIncludePath(HTMLPURIFIER_INCLUDE_PATH);
      include_once(HTMLPURIFIER_INCLUDE_PATH.'HTMLPurifier.php');
      $this->_htmlPurifier = &HTMLPurifier::getInstance();
      $this->initialize();
    }
    return $this->_htmlPurifier;
  }

  public function checkRequiredVersion() {
    // html purifier requires php version 4.3.2 or greater
    if (version_compare(PHP_VERSION, '4.3.2', '>=')) {
      return TRUE;
    }
    $this->logMsg(
      MSG_ERROR,
      PAPAYA_LOGTYPE_MODULES,
      'Could not get HTML Purifier instance because this PHP version is too old.'
    );
    return FALSE;
  }

  /**
  * Initializes the current object to a state redy to be used.
  *
  */
  function initialize() {
    $config = &$this->htmlPurifier()->config;

    //default values
    $config->set('Core', 'Encoding', 'UTF-8');
    $config->set('HTML', 'Doctype', 'XHTML 1.0 Strict');

    // reset the cache to papaya-cache
    $cachePath = $this->papaya()->options['PAPAYA_PATH_CACHE'].'htmlpurifier';
    $config->set(
      'Cache', 'SerializerPath', $cachePath
    );
    $config->set('Core', 'EscapeInvalidTags', 'true');

    // try to build path, if not exist
    if (!is_dir($cachePath)) {
      @mkdir($cachePath);
    }
  }

  /**
  * optional setUp: overrides several default value settings
  *
  * currently supported:
  * * 'Core:Encoding' => 'UTF-8' or 'ISO-8859-1' or ...
  * * 'HTML:Doctype' => 'HTML 4.01 Strict' or 'HTML 4.01 Transitional' or 'XHTML 1.0 Strict'
  *                      or 'XHTML 1.0 Transitional' or 'XHTML 1.1'
  * * 'HTML:Allowed' =>  string of tags, separated with ','
  * * 'HTML:EnableIDPrefix' => set an id prefix for all elements (avoid conflicts)
  *
  * @param array $params
  */
  function setUp($params) {
    $config = &$this->htmlPurifier()->config;

    if (isset($params['Core:Encoding'])) {
      $config->set('Core', 'Encoding', $params['Core:Encoding']);
    }

    if (isset($params['HTML:Doctype'])) {
      $config->set('HTML', 'Doctype', $params['HTML:Doctype']);
    }

    if (isset($params['HTML:Allowed'])) {
      $config->set('HTML', 'Allowed', $params['HTML:Allowed']);
    }

    if (isset($params['HTML:DefinitionID'])) {
      $config->set('HTML', 'DefinitionID', $params['HTML:DefinitionID']);

      if (isset($params['HTML:DefinitionRev'])) {
        $config->set('HTML', 'DefinitionRev', $params['HTML:DefinitionRev']);
      }
    }

    if (isset($params['Core:EscapeInvalidTags'])) {
      $config->set('Core', 'EscapeInvalidTags', $params['Core:EscapeInvalidTags']);
    }

    if (isset($params['HTML:EnableIDPrefix'])) {
      $config->set('HTML', 'EnableAttrID', TRUE);
      $config->set('Attr', 'IDPrefix', $params['HTML:EnableIDPrefix']);
    }
  }

  /**
  * add an attribute definition
  *
  * @param string $element
  * @param string $name
  * @param string|object $attrDefinition see HTMLPurifier_AttrTypes for details
  */
  function addAttribute($element, $name, $attrDefinition) {
    $config = &$this->htmlPurifier()->config;
    $def =& $config->getHTMLDefinition(TRUE);
    $def->addAttribute($element, $name, $attrDefinition);
  }

  /**
  * return given $name if attr def found, otherwise it return "Text" as Fallback
  *
  * @param string $name e.g. 'Integer'
  * @param array $params values, e.g. for "Enum"
  * @return string|object
  */
  function getAttributeDefinition($name, $params = array()) {
    // recognize a name like 'CSS_Color' => AttrDef/CSS/Color.php & HTMLPurifier_AttrDef_CSS_Color
    $names = explode('_', $name, 2);
    if (count($names) == 2 && in_array($names[0], array('CSS', 'HTML','URI'))) {
      $names[0] = papaya_strings::normalizeString($names[0]);
      $names[1] = papaya_strings::normalizeString($names[1]);
      $fileName = 'HTMLPurifier/AttrDef/'.$names[0].'/' . $names[1].'.php';
      $className = 'HTMLPurifier_AttrDef_' . $name;
      $name = $names[0];
    } else {
      $name = papaya_strings::normalizeString($name);
      $fileName = 'HTMLPurifier/AttrDef/'.$name.'.php';
      $className = 'HTMLPurifier_AttrDef_' . $name;
    }

    switch ($name) {
    // standard defs by htmlpurifier
    case 'CSS':
    case 'URI':
    case 'Integer':
    case 'Lang':
    case 'Text':
      if (!class_exists($className, FALSE)) {
        @include_once(HTMLPURIFIER_INCLUDE_PATH.$fileName);
      }
      // dont recheck, because htmlpurifier system lib

      $obj = new $className;
      if ($obj && is_object($obj) && is_a($obj, $className)) {
        return $obj;
      }
      // no type, use fallback
      break;
    case 'Enum':
      if (!class_exists($className, FALSE)) {
        @include_once(
          sprintf(HTMLPURIFIER_INCLUDE_PATH.'HTMLPurifier/AttrDef/%s.php', $name)
        );
      }
      // dont recheck, because htmlpurifier system lib

      if ($params == NULL || (is_array($params) && count($params) == 0)) {
        // enum without params.. TODO senseless?
        $obj = new $className();
      } elseif (isset($params['values']) &&
                is_array($params['values']) && isset($params['case_sensitive'])) {
        // enum with parameters and case sensitive setting
        $obj = new $className($params['values'], $params['case_sensitive']);
      } else {
        // enum, only with values
        $obj = new $className($params);
      }
      if ($obj && is_object($obj) && is_a($obj, $className)) {
        return $obj;
      }
      // no type, use fallback
      break;
    default:
      if (!class_exists($className, FALSE)) {
        @include_once(
          sprintf(HTMLPURIFIER_INCLUDE_PATH.'../papaya/AttrDef/%s.php', $name)
        );
      }
      if (class_exists($className, FALSE)) {
        // use params, if defined
        if (isset($params)) {
          $obj = new $className($params);
        } else {
          $obj = new $className;
        }
        if ($obj && is_object($obj) && is_a($obj, $className)) {
          return $obj;
        }
      }
    }

    $this->debugMsg(
      'HTML Purifier could not load HTML Purifier Attribute Definition for '.$name
    );
    return 'Text'; // Fallback
  }

  /**
  * add an element definition
  *
  * example:
  * <code>
  * <?php
  * $base_htmlpurifier->addElement(
  *   'font', 'Inline', 'Optional: Inline', 'Common',array('color' => 'Color'));
  *
  * </code>
  *
  * @see HTMLModule
  * @param string $element Element name, ex. 'label'
  * @param string $type Content set to register in, ex. 'Inline' or 'Flow'
  * @param string $attrDefinition Description of allowed children
  * @param array $attr_collections Array (or string if only one) of attribute
  *              collection(s) to merge into the attributes array
  * @param array $attributes Array of attribute names to attribute
  *              definitions, much like the above-described attribute customization
  */
  function addElement(
    $element, $type, $attrDefinition, $attr_collections = array(), $attributes = array()
  ) {
    $config = &$this->htmlPurifier()->config;
    $def = &$config->getHTMLDefinition(TRUE);
    $def->addElement($element, $type, $attrDefinition, $attr_collections, $attributes);
  }

  /**
  * set a new config
  *
  * @param HTMLPurifier_Config $config config schema
  */
  function setNewConfig($config) {
    if ($config && is_object($config) && is_a($config, 'HTMLPurifier_Config')) {
      $this->htmlPurifier()->config = &$config;
    }
  }

  /**
  * add some special filter
  *
  * @param array|string $filters list of filternames
  * @param boolean $includeFromOwnLibrary use html purifier library (true) or papayas (false)
  * @param mixed $params optional params (constructor params)
  */
  function addFilters($filters, $includeFromOwnLibrary = TRUE, $params = NULL) {
    if (!is_array($filters)) {
      $filters = array($filters);
    }

    foreach ($filters as $filter) {
      // TODO sicher genug?
      $filter = papaya_strings::normalizeString($filter);
      $className = 'HTMLPurifier_Filter_'.$filter;
      if (!class_exists($className, FALSE)) {
        if ($includeFromOwnLibrary) {
          @include_once(
            sprintf(HTMLPURIFIER_INCLUDE_PATH.'HTMLPurifier/Filter/%s.php', $filter)
          );
        } else {
          @include_once(
            sprintf(HTMLPURIFIER_INCLUDE_PATH.'../papaya/Filter/%s.php', $filter)
          );
        }
      }
      if (class_exists($className, FALSE)) {
        if ($params == NULL) {
          $this->htmlPurifier()->addFilter(new $className());
        } else {
          $this->htmlPurifier()->addFilter(new $className($params));
        }
      }
    }
  }

  /**
  * purify the given html text and return it
  *
  * @param string $htmlContent html text input
  * @return string
  */
  function purifyInput($htmlContent) {
    $purifier = $this->htmlPurifier();
    if (!is_object($purifier)) {
      return FALSE;
    }

    return $purifier->purify($htmlContent);
  }

  /**
   * purify the given html text and return it
   *
   * @param array $arrayHtmlContents
   * @internal param string $htmlContent html text input
   * @return string
   */
  function purifyBatchInput($arrayHtmlContents) {
    $purifier = $this->htmlPurifier();
    if (!is_object($purifier)) {
      return FALSE;
    }

    return $purifier->purifyArray($arrayHtmlContents);
  }

  /**
  * static way of filter an input
  *
  * how to use, e.g.:
  * <code>
  * $filtered = base_htmlpurifier::filterInput('VideoTag', $yourHTMLWithTags, FALSE);
  * </code>
  *
  * FIXME
  * note: the filter sets $config and $context to NULL
  *
  * @static
  * @param string $filter name of a filter
  * @param string $input
  * @param boolean $includeFromOwnLibrary use html purifier library (true) or papayas (false)
  * @param mixed $params optional params (constructor params)
  * @return string|NULL NULL if filter not exist
  */
  function filterInput($filter, $input, $includeFromOwnLibrary = TRUE, $params = NULL) {
    $filter = papaya_strings::normalizeString($filter);
    $className = 'HTMLPurifier_Filter_'.$filter;
    if (!class_exists($className, FALSE)) {
      if ($includeFromOwnLibrary) {
        @include_once(
          sprintf(HTMLPURIFIER_INCLUDE_PATH.'HTMLPurifier/Filter/%s.php', $filter)
        );
      } else {
        @include_once(
          sprintf(HTMLPURIFIER_INCLUDE_PATH.'../papaya/Filter/%s.php', $filter)
        );
      }
    }

    if (!class_exists($className, FALSE)) {
      return NULL;
    }

    if ($params == NULL) {
      $obj = new $className();
    } else {
      $obj = new $className($params);
    }
    $input = $obj->preFilter($input, NULL, $obj);
    $input = $obj->postFilter($input, NULL, $obj);
    return $input;
  }
}

