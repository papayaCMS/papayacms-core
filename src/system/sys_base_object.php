<?php
/**
* Implementation super class
*
* Provides basic functionality for all other classes
*
* What you will want to use (see the actual methods documentation for details):
* <pre>
*   debug                   <- we just love it!
*   getAbsoluteURL          <- get an absolute URL with the complete protocol and host stuff
*   getLink                 <- get a link with parameters in the backend
*   getWebLink              <- get a link with parameters to frontend pages
*   getWebMediaLink         <- get a link to a media file
*   getXHTMLString          <- to make a string xhtml compatible
*   _gt                     <- get a translation for a string (phrases)
*   _gtf                    <- get a translation and fill in the vars (sprintf-like)
*   initializeParams        <- loads params from _GET/_POST for this module (paramName)
*   initializeSessionParam  <- registers a parameter to the session and resets others
*   get-/setSessionValue    <- if you want to store data in the session or read it
*                              make pretty sure the sessionParamName is not in use!
*   logMsg                  <- messages show up in the system protocol for info or error tracking
*   parseRequestURI         <- in some cases you may need information about the URI, nicely prepared
* </pre>
* ...anything else?
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
* @subpackage Core
* @version $Id: sys_base_object.php 39728 2014-04-07 19:51:21Z weinert $
*/

/**
* log type for user messages (login/logout)
*/
define('PAPAYA_LOGTYPE_USER', PapayaMessageLogable::GROUP_USER);
/**
* log type for page messages (published)
*/
define('PAPAYA_LOGTYPE_PAGES', PapayaMessageLogable::GROUP_CONTENT);
/**
* log type for database messages (errors)
*/
define('PAPAYA_LOGTYPE_DATABASE', PapayaMessageLogable::GROUP_DATABASE);
/**
* log type for calendar messages
*/
define('PAPAYA_LOGTYPE_CALENDAR', 4);
/**
* log type for cronjob messages
*/
define('PAPAYA_LOGTYPE_CRONJOBS', PapayaMessageLogable::GROUP_CRONJOBS);
/**
* log type for surfer/community messages
*/
define('PAPAYA_LOGTYPE_SURFER', PapayaMessageLogable::GROUP_COMMUNITY);
/**
* log type for system messages
*/
define('PAPAYA_LOGTYPE_SYSTEM', PapayaMessageLogable::GROUP_SYSTEM);
/**
* log type for system messages
*/
define('PAPAYA_LOGTYPE_MODULES', PapayaMessageLogable::GROUP_MODULES);

/**
* line break string constant
*/
define('LF', "\n");

/**
* message type info
*/
define('MSG_INFO', PapayaMessage::SEVERITY_INFO);

/**
* message type warning
*/
define('MSG_WARNING', PapayaMessage::SEVERITY_WARNING);

/**
* message type error
*/
define('MSG_ERROR', PapayaMessage::SEVERITY_ERROR);

/**
* Implementation super class
*
* Provides basic functionality for all other classes
*
* @package Papaya
* @subpackage Core
*/
class base_object extends PapayaObject implements PapayaRequestParametersInterface {
  /**
  * Error messages, not used any more
  * @deprecated
  * @var object $msgs
  */
  var $msgs = NULL;

  /**
  * Images -> use $this->papaya()->images
  * @deprecated
  * @var array|PapayaUiImages $images
  */
  var $images = NULL;

  /**
  * Parameters
  * @var array $params
  */
  var $params = array();
  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName = 'p';
  /**
  * Request method
  * @var string $requestMethod
  */
  var $requestMethod = 'get';
  /**
  * Session parameter name
  * @var string $sessionParamName
  */
  var $sessionParamName = NULL;
  /**
  * Session parameters
  * @var array $sessionParams
  */
  var $sessionParams = array();

  /**
  * allowed url level separators
  * @access private
  * @var array
  */
  var $urlLevelSeparators = array(',', ':', '*', '!', "'", "/");

  /**
  * Application object
  * @var PapayaApplication
  */
  var $_applicationObject = NULL;
  public $baseLink;

  /**
   * @var PapayaRequestParameters
   */
  private $_parameters = NULL;

  /**
  * Constructor
  *
  * @access public
  */
  function __construct() {
    //empty implementation for all child classes
  }

  /**
  * Get session value
  *
  * @param mixed $name
  * @access public
  * @return mixed session value or NULL
  */
  function getSessionValue($name) {
    return $this->papaya()->session->values[$name];
  }

  /**
   * Set session value
   *
   * @param mixed $name
   * @param mixed $value session value
   * @return mixed
   * @access public
   */
  function setSessionValue($name, $value) {
    return $this->papaya()->session->values[$name] = $value;
  }

  /**
  * Phrasetranslator - Fetch translation for all phrase
  *
  * @param string $phrase Phrase
  * @param mixed $module Modul optional, default value NULL
  * @access public
  * @return string
  */
  function _gt($phrase, $module = NULL) {
    if ($this->papaya()->hasObject('Phrases') &&
        trim($phrase) != '') {
      return $this->papaya()->phrases->getText($phrase, $module);
    }
    return $phrase;
  }

  /**
  * Phrasetranslator - Fetch translation of one phrase and insert variable
  *
  * @param string $phrase Phrase
  * @param array $vals Parameter
  * @param mixed $module Modul
  * @access public
  * @return string
  */
  function _gtf($phrase, $vals, $module = NULL) {
    if (trim($phrase) != '' && isset($this->papaya()->phrases)) {
      return $this->papaya()->phrases->getTextFmt(
        $phrase, $vals, $module
      );
    }
    return vsprintf($phrase, $vals);
  }

  /**
  * Phrases - Locate files
  *
  * @param string $fileName filename
  * @access public
  * @return string file content
  */
  function _gtfile($fileName) {
    $administrationUser = $this->papaya()->administrationUser;
    if (!empty($administrationUser->options['PAPAYA_UI_LANGUAGE'])) {
      $lng = $administrationUser->options['PAPAYA_UI_LANGUAGE'];
    } else {
      $lng = $this->papaya()->options['PAPAYA_UI_LANGUAGE'];
      if (empty($lng)) {
        $lng = 'en-US';
      }
    }
    $fileName = PapayaUtilFilePath::cleanup(
      PapayaUtilFilePath::getDocumentRoot($this->papaya()->options).
      $this->papaya()->options->get('PAPAYA_PATHWEB_ADMIN', '/papaya/').
      '/data/'.$lng.'/'
    ).$fileName;
    if (file_exists($fileName) && is_readable($fileName)) {
      if ($fh = @fopen($fileName, 'r')) {
        $data = fread($fh, filesize($fileName));
        fclose($fh);
        return PapayaUtilStringUtf8::ensure($data);
      }
    }
    return '';
  }

  /**
  * Log events
  *
  * The strings $short and $long may not be translated and must be in english.
  *
  * @param integer $type message priority, {@see PapayaMessage}
  * @param integer $group message group (@see PapayaMessageLogable)
  * @param string $short message short (for lists)
  * @param string $long message detailed
  * @param boolean $addBacktrace
  * @param integer $backtraceOffset
  * @access public
  */
  function logMsg($type, $group, $short, $long = '', $addBacktrace = FALSE, $backtraceOffset = 1) {
    $logMessage = new PapayaMessageLog($group, $type, $short);
    if (!empty($long)) {
      $logMessage->context()->append(
        new PapayaMessageContextText($long)
      );
    }
    if ($addBacktrace) {
      $logMessage->context()->append(
        new PapayaMessageContextBacktrace($backtraceOffset)
      );
    }
    $this->papaya()->messages->dispatch(
      $logMessage
    );
  }

  /**
  * Log a variable dump
  *
  * @param integer $level message priority, {@see sys_error.php}
  * @param integer $group message group (groups)
  * @param string $title
  * @param mixed $variable
  * @param boolean $addBacktrace
  * @param integer $backtraceOffset
  * @access public
  */
  function logVariable(
    $level, $group, $title, $variable, $addBacktrace = FALSE, $backtraceOffset = 3
  ) {
    $logMessage = new PapayaMessageLog($group, $level, $title);
    $logMessage->context()->append(
      new PapayaMessageContextVariable($variable)
    );
    if ($addBacktrace) {
      $logMessage->context()->append(
        new PapayaMessageContextBacktrace($backtraceOffset)
      );
    }
    $this->papaya()->messages->dispatch(
      $logMessage
    );
  }

  /**
   * Adds a message in the message object.
   * All messages will be displayed in programm run
   *
   * @param integer $level message priority
   * @param string $text message
   * @param bool $log
   * @param integer $group
   */
  public function addMsg($level, $text, $log = FALSE, $group = PAPAYA_LOGTYPE_SYSTEM) {
    $this->papaya()->messages->dispatch(
      new PapayaMessageDisplay($level, $text)
    );
    if ($log) {
      $this->papaya()->messages->dispatch(
        new PapayaMessageLog($group, $level, $text)
      );
    }
  }

  /**
  * Parameter initialisation
  *
  * All global parameters for the object will be read out of the global
  * namespace and put in $this->params. The current will script filename
  * is stored in the $this->baseLink.
  * If here is a parameter $sessionParamName or a property $this->sessionParamName,
  * the value will be registered in the session object.
  *
  * @param mixed $sessionParamName optional, default value NULL
  * @access public
  */
  function initializeParams($sessionParamName = NULL) {
    $application = $this->papaya();
    $request = $application->request;
    $this->requestMethod = $request->getMethod();
    if (isset($this->paramName)) {
      $this->params = $request->getParameter(
        $this->paramName,
        array(),
        NULL,
        PapayaRequest::SOURCE_QUERY | PapayaRequest::SOURCE_BODY
      );
    } else {
      $this->params = array();
    }
    if (isset($sessionParamName) && trim($sessionParamName) != '') {
      $this->sessionParamName = $sessionParamName;
    }
    $this->baseLink = $this->getBaseLink();
  }

  /**
  * get a parameter array from superglobal arrays,
  * remove magic quotes from the parameter values
  * and make that they are all utf-8
  *
  * the parameter $modes specifies the superglobals and the order
  *  Examples:
  *    GP = $_GET and $_POST, $_POST values override $_GET values
  *    CGP = $_COOKIE, $_GET and $_POST, $_POST overrides both, $_GET overrides $_COOKIE
  *
  * @param string $paramName
  * @param string $modes optional, default value 'GP'
  * @access public
  * @return array | NULL
  */
  function getRequestParameters($paramName, $modes = 'GP') {
    $application = $this->papaya();
    $request = $application->getObject('Request');
    $sourceCount = strlen($modes);
    $result = NULL;
    $source = NULL;
    $parameters = NULL;
    for ($i = 0; $i < $sourceCount; ++$i) {
      switch ($modes[$i]) {
      case 'C' :
        $parameters = $request->getParameter(
          $paramName, NULL, NULL, PapayaRequest::SOURCE_COOKIE
        );
        break;
      case 'G' :
        $parameters = $request->getParameter(
          $paramName, NULL, NULL, PapayaRequest::SOURCE_QUERY
        );
        break;
      case 'P' :
        $parameters = $request->getParameter(
          $paramName, NULL, NULL, PapayaRequest::SOURCE_BODY
        );
        break;
      }
      if (!empty($parameters)) {
        break;
      }
    }
    return $parameters;
  }

  /**
  * Initialisation of session-parameter
  *
  * If the parameter this->params[name] is set all variables within $resetParams get reset.
  * Otherwise if $this->sessionParams[$name] is set the parameter will replace
  * this->params[$name]. If there is no parameter set with name $name, this->params set to NULL
  *
  * @param string $name name of the parameter
  * @param array $resetParams array parameter optional, default NULL
  * @access public
  */
  function initializeSessionParam($name, $resetParams = NULL) {
    if (isset($this->params[$name])) {
      if (isset($resetParams) &&
          is_array($resetParams) &&
          (
            !isset($this->sessionParams[$name]) ||
            $this->params[$name] != $this->sessionParams[$name]
          )) {
        foreach ($resetParams as $paramName) {
          if (isset($this->params[$paramName])) {
            unset($this->params[$paramName]);
          }
          if (isset($this->sessionParams[$paramName])) {
            unset($this->sessionParams[$paramName]);
          }
        }
      }
      $this->sessionParams[$name] = $this->params[$name];
    } elseif (isset($this->sessionParams[$name])) {
      $this->params[$name] = $this->sessionParams[$name];
    } else {
      $this->params[$name] = NULL;
    }
  }

  /**
  * Get base link
  *
  * @param integer $pageId
  * @param integer $categId
  * @access public
  * @return string $baseLink URL
  */
  function getBaseLink($pageId = 0, $categId = 0) {
    $data = $this->parseRequestURI();
    if (isset($data['output']) &&
        ($data['output'] == 'media' || $data['output'] == 'image')) {
      return $data['filename'];
    } elseif ($pageId > 0) {
      $pId = (int)$pageId;
    } elseif ($data['page_id'] > 0) {
      $pId = (int)$data['page_id'];
    } elseif (defined('PAPAYA_ADMIN_PAGE') && PAPAYA_ADMIN_PAGE) {
      $pId = 0;
    } elseif (isset($GLOBALS['PAPAYA_PAGE']) &&
              is_object($GLOBALS['PAPAYA_PAGE']) &&
              is_a($GLOBALS['PAPAYA_PAGE'], 'papaya_page')) {
      $pId = $GLOBALS['PAPAYA_PAGE']->topicId;
    } elseif (isset($_GET['p_id']) && $_GET['p_id'] > 0) {
      $pId = (int)$_GET['p_id'];
    } else {
      $pId = 0;
    }
    if ($categId > 0) {
      $cId = $categId;
    } elseif (isset($data['categ_id']) && $data['categ_id'] > 0) {
      $cId = (int)$data['categ_id'];
    } else {
      $cId = 0;
    }
    $baseLink = $data['filename'];
    if ($pId > 0 && $cId > 0) {
      $baseLink .= '.'.$cId;
    }
    if ($pId > 0) {
      $baseLink .= '.'.$pId;
    }
    if (isset($data['language']) && $data['language'] != '') {
      $baseLink .= '.'.$data['language'];
    }
    $baseLink .= '.'.$data['ext'];
    if ($data['preview']) {
      if ($data['datetime']) {
        $baseLink .= '.'.(int)$data['datetime'];
      }
      $baseLink .= '.preview';
    }
    return $baseLink;
  }

  /**
  * get base path to current script
  *
  * @param boolean $withDocumentRoot optional, default value FALSE
  * @access public
  * @return string
  */
  function getBasePath($withDocumentRoot = FALSE) {
    return PapayaUtilFilePath::getBasePath($withDocumentRoot);
  }

  /**
   * Parse request URI to filter data out of it
   *
   * @param null $url
   * @return array
   */
  public function parseRequestURI($url = NULL) {
    $application = $this->papaya();
    if (empty($url)) {
      $request = $application->getObject('Request');
    } else {
      $request = new PapayaRequest($application->options);
      if (is_object($url)) {
        $request->load($url);
      } else {
        $request->load(new PapayaUrl($url));
      }
    }
    $fileTitle = 'index';
    $ext = 'php';
    if (defined('PAPAYA_ADMIN_PAGE') && PAPAYA_ADMIN_PAGE) {
      $fileNamePattern = '~([a-z_\.-]+)\.([a-z_-]+)(\?|$)~';
      if (preg_match($fileNamePattern, $_SERVER['SCRIPT_FILENAME'], $regs)) {
        $fileTitle = $regs[1];
        $ext = $regs[2];
      }
    } else {
      $ext = $this->papaya()->options->get('PAPAYA_URL_EXTENSION', 'html');
    }
    $mode = $request->getParameter(
      'mode', 'page', NULL, PapayaRequest::SOURCE_PATH
    );
    $specialModes = array(
      'media', 'download', 'thumb', 'thumbnail', 'image', 'status', 'urls'
    );
    if (!in_array($mode, $specialModes)) {
      $mode = $request->getParameter(
        'output_mode', $ext, NULL, PapayaRequest::SOURCE_PATH
      );
    }
    $result = array(
      'sid' => $request->getParameter(
        'session', '', NULL, PapayaRequest::SOURCE_PATH
      ),
      'path' => $request->getParameter(
        'file_path', '/', NULL, PapayaRequest::SOURCE_PATH
      ),
      'filename' => $request->getParameter(
        'file_title', $fileTitle, NULL, PapayaRequest::SOURCE_PATH
      ),
      'page_id' => $request->getParameter(
        'page_id', 0, NULL, PapayaRequest::SOURCE_PATH
      ),
      'categ_id' => $request->getParameter(
        'category_id', 0, NULL, PapayaRequest::SOURCE_PATH
      ),
      'language' => $request->getParameter(
        'language', '', NULL, PapayaRequest::SOURCE_PATH
      ),
      'preview' => $request->getParameter(
        'preview', FALSE, NULL, PapayaRequest::SOURCE_PATH
      ),
      'datetime' => $request->getParameter(
        'preview_time', 0, NULL, PapayaRequest::SOURCE_PATH
      ),
      'media_id' => $request->getParameter(
        'media_uri', '', NULL, PapayaRequest::SOURCE_PATH
      ),
      'ext' => $mode,
      'output' => $mode
    );
    return $result;
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
  function getWebLink(
    $pageId = NULL,
    $lng = NULL,
    $mode = NULL,
    $params = NULL,
    $paramName = NULL,
    $text = '',
    $categId = NULL
  ) {
    $application = $this->papaya();
    $request = $application->request;
    $reference = new PapayaUiReferencePage();
    $reference->load($request);
    if (isset($pageId)) {
      $reference->setPageId($pageId);
    }
    if (isset($categId)) {
      $reference->setCategoryId($categId);
    }
    if (isset($mode) && strpos($mode, '.preview') !== FALSE) {
      $mode = substr($mode, 0, -8);
      $preview = TRUE;
    } elseif ((isset($GLOBALS['PAPAYA_PAGE']) && $GLOBALS['PAPAYA_PAGE']->public == FALSE) ||
              $mode == 'preview') {
      $preview = TRUE;
    } else {
      $preview = FALSE;
    }
    if ($preview) {
      if (isset($GLOBALS['PAPAYA_PAGE']) &&
          $GLOBALS['PAPAYA_PAGE']->versionDateTime > 0) {
        $reference->setPreview(TRUE, $GLOBALS['PAPAYA_PAGE']->versionDateTime);
      } else {
        $reference->setPreview(TRUE);
      }
    } else {
      $reference->setPreview(FALSE);
    }
    if (empty($lng)) {
      if (isset($GLOBALS['PAPAYA_PAGE']) &&
          isset($GLOBALS['PAPAYA_PAGE']->contentLanguage) &&
          isset($GLOBALS['PAPAYA_PAGE']->contentLanguage['lng_ident'])) {
        $lng = $GLOBALS['PAPAYA_PAGE']->contentLanguage['lng_ident'];
      }
      $lng = $request->getParameter(
        'language',
        $lng,
        NULL,
        PapayaRequest::SOURCE_ALL
      );
    }
    $reference->setPageLanguage($lng);
    if (!($reference->getPageId() > 0) && $reference->getPageTitle() != 'index') {
      if (!empty($GLOBALS['PAPAYA_PAGE']->topicId)) {
        $reference->setPageId($GLOBALS['PAPAYA_PAGE']->topicId);
      }
    }
    if (!empty($text)) {
      $reference->setPageTitle(
        $this->escapeForFilename($text, $reference->getPageTitle(), $lng)
      );
    }
    $reference->setOutputMode($this->_getWebLinkPageModeExtension($mode));
    $reference->setParameters($params, $paramName);

    $transformer = new PapayaUrlTransformerRelative();
    $absolute = $reference->get();
    $relative = $transformer->transform(
      $request->getUrl(),
      new PapayaUrl($absolute)
    );
    return is_null($relative) ? $absolute : $relative;
  }

  /**
  * Get extension for a given page mode, use fallbacks for invalid or empty argument
  * @param string $mode
  * @return string
  */
  function _getWebLinkPageModeExtension($mode) {
    $defaultExtension = $this->papaya()->options->get('PAPAYA_URL_EXTENSION', 'html');
    $pageModes = array(
      'page' => $defaultExtension,
      'preview' => $defaultExtension
    );
    if (isset($mode) && isset($pageModes[$mode])) {
      $ext = $pageModes[$mode];
    } elseif (isset($mode) && preg_match('~^[a-z]+$~', $mode)) {
      $ext = $mode;
    } elseif (isset($GLOBALS['PAPAYA_PAGE']) &&
              is_object($GLOBALS['PAPAYA_PAGE']) &&
              isset($GLOBALS['PAPAYA_PAGE']->mode)) {
      $ext = $GLOBALS['PAPAYA_PAGE']->mode;
    } else {
      $ext = $defaultExtension;
    }
    return $ext;
  }

  /**
  * Encode query string
  *
  * @param array $params array of params
  * @param string $paramName optional param name
  * @param integer $maxDepth optional default 5
  * @access public
  * @return string
  */
  function encodeQueryString($params, $paramName = NULL, $maxDepth = 5) {
    if (isset($params) && is_array($params)) {
      $parameters = new PapayaRequestParameters();
      if (empty($paramName)) {
        $parameters->merge($params);
      } else {
        $parameters->set($paramName, $params);
      }
      $parameters->remove(array('p_id', 'p_pagemode', 'preview'));
      $previewDate = $parameters->get('preview_date');
      if (empty($previewDate)) {
        $parameters->remove('preview_date');
      }
      $queryString = $parameters->getQueryString(
        $this->papaya()->options->get('PAPAYA_URL_LEVEL_SEPARATOR')
      );
      if (!empty($queryString)) {
        return '?'.$queryString;
      }
    }
    return '';
  }

  /**
   * Recode query string
   *
   * @param string $queryString
   * @param array $newQueryParams
   * @return string
   */
  public function recodeQueryString($queryString, $newQueryParams = array()) {
    $query = new PapayaRequestParametersQuery(
      $this->papaya()->options->get('PAPAYA_URL_LEVEL_SEPARATOR')
    );
    $query->setString($queryString);
    $query->values()->merge($newQueryParams);
    $queryString = $query->getString();
    if (!empty($queryString)) {
      return '?'.$queryString;
    }
    return '';
  }

  /**
  * Escape chars in a string to use it in a filename
  *
  * @param string $str
  * @param string $default returned if str is empty
  * @param string $language transliteration language optional, default value 0
  * @access public
  * @return string
  */
  function escapeForFilename($str, $default = 'index', $language = NULL) {
    $str = PapayaUtilFile::normalizeName(
      $str,
      $this->papaya()->options->get('PAPAYA_URL_NAMELENGTH', 50),
      $language
    );
    return ($str != '') ? strtolower($str) : $default;
  }

  /**
  * Get web media link
  *
  * @param string $mid GUID
  * @param string $mode optional, default value 'media'
  * @param string $text optional, default value ''
  * @param string $ext optional, default value ''
  * @return mixed
  */
  function getWebMediaLink($mid, $mode = 'media', $text = '', $ext = '') {
    $application = $this->papaya();
    $request = $application->request;
    $thumbsFileType = $this->papaya()->options->get('PAPAYA_THUMBS_FILETYPE', IMAGETYPE_PNG);
    if (in_array($mode, array('thumb', 'thumbs', 'thumbnail'))) {
      $reference = new PapayaUiReferenceThumbnail();
      $storageGroup = 'thumbs';
      $mode = 'thumbnail';
      $extensions = array(
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_GIF => 'gif'
      );
      $ext = $extensions[$thumbsFileType];
    } else {
      $reference = new PapayaUiReferenceMedia();
      $reference->setMode($mode);
      $storageGroup = 'files';
    }
    $reference->load($request);
    $reference->setMediaUri($mid);
    $options = $this->papaya()->options;
    $mimeType = '';
    $isPublic = isset($GLOBALS['PAPAYA_PAGE']) && $GLOBALS['PAPAYA_PAGE']->public;
    if (empty($text) || empty ($ext)) {
      if (strlen($mid) > 0 && strpos($mid, '.') === FALSE) {
        if ($storageGroup == 'thumbs') {
          $mimeType = image_type_to_mime_type($thumbsFileType);
        } else {
          $mediaDb = base_mediadb::getInstance();
          $mediaFile = $mediaDb->getFile($mid);
          if ($mediaFile) {
            if (!empty($mediaFile['mimetype_ext'])) {
              $ext = $mediaFile['mimetype_ext'];
            }
            $text = empty($mediaFile['file_name']) ? $text : $mediaFile['file_name'];
            $mimeType = (isset($mediaFile['mimetype'])) ? $mediaFile['mimetype'] : '';
            if ($storageGroup == 'files') {
              $versionSuffix = (isset($mediaFile['current_version_id'])) ?
                'v'.$mediaFile['current_version_id'] : '';
            }
          }
          unset($mediaFile);
          unset($mediaDb);
        }
      }
      if ($isPublic) {
        $storage = PapayaMediaStorage::getService(
          $options->get('PAPAYA_MEDIA_STORAGE_SERVICE', ''),
          $options
        );
        if ($storage->isPublic($storageGroup, $mid, $mimeType)) {
          return $storage->getUrl($storageGroup, $mid, $mimeType);
        } elseif (!empty($versionSuffix) &&
                  $storage->isPublic($storageGroup, $mid.$versionSuffix, $mimeType)) {
          return $storage->getUrl($storageGroup, $mid.$versionSuffix, $mimeType);
        }
      }
    }
    $reference->setPreview(!$isPublic);
    if (!empty($ext)) {
      $reference->setExtension($ext);
    } elseif (preg_match('(\.([a-zA-Z\d]{1,10}$))', $text, $matches) && $mode != 'thumb') {
      $reference->setExtension($matches[1]);
    }
    $reference->setTitle(
      $this->escapeForFilename(
        preg_replace('(\.[a-zA-Z\d]{1,10}$)', '', $text),
        'index'
      )
    );
    $transformer = new PapayaUrlTransformerRelative();
    $absolute = $reference->get();
    $relative = $transformer
      ->transform(
        $request->getUrl(),
        new PapayaUrl($absolute)
      );
    return is_null($relative) ? $absolute : $relative;
  }

  /**
   * Return absolute URL
   *
   * @param string $url
   * @param string $text optional, default value ''
   * @param boolean $sid optional, default value TRUE
   * @param null $protocol
   * @access public
   * @return string URL
   */
  function getAbsoluteURL($url, $text = '', $sid = TRUE, $protocol = NULL) {
    $urlObject = $this->papaya()->request->getUrl();
    if (empty($protocol)) {
      if (!($protocol = $urlObject->getScheme())) {
        $protocol = 'http';
      }
    }

    $hostPort = $urlObject->getHost();
    $port = $urlObject->getPort();
    if (!empty($port)) {
      $hostPort .= ':'.$port;
    }
    if (!($hostPort)) {
      $hostPort = $this->papaya()->options->get('PAPAYA_DEFAULT_HOST', 'localhost');
    }
    $baseHref = $protocol."://".$hostPort;

    $session = $this->papaya()->session;
    if ($session->isActive() &&
        $session->id()->existsIn(PapayaSessionId::SOURCE_PARAMETER) &&
        $sid) {
      $sidStr = '/'.$session->name.$session->id;
    } else {
      $sidStr = '';
    }

    if (FALSE !== ($pos = strpos($url, '?'))) {
      $urlAppend = substr($url, $pos);
      $url = substr($url, 0, $pos);
    } elseif (FALSE !== ($pos = strpos($url, '#'))) {
      $urlAppend = substr($url, $pos);
      $url = substr($url, 0, $pos);
    } else {
      $urlAppend = '';
    }

    $pathWeb = $this->papaya()->options->get('PAPAYA_PATH_WEB', '/');
    if ($url == '/') {
      $href = $baseHref.$pathWeb;
    } elseif (preg_match("(^(?:(\d+)\.)?(\d+)$)", trim($url), $regs)) {
      $href = $this->getWebLink(
        PapayaUtilArray::get($regs, 2, 0),
        NULL,
        NULL,
        NULL,
        NULL,
        $text,
        PapayaUtilArray::get($regs, 1, 0)
      );
      if (!preg_match('(^\w+://)', $href)) {
        $href = $baseHref.$sidStr.$pathWeb.$href;
      }
    } elseif (PapayaFilterFactory::isUrl($url)) {
      $href = $url;
    } elseif (preg_match("#^/#", $url)) {
      $href = $baseHref.$sidStr.$url;
    } else {
      $iUrl = empty($_SERVER['REQUEST_URI']) ? '' : $_SERVER['REQUEST_URI'];
      if (FALSE === ($pos = strpos($iUrl, '?'))) {
        $pos = strpos($iUrl, '#');
      }
      if (FALSE !== $pos) {
        $iUrl = substr($iUrl, 0, $pos);
      }
      $iUrl = preg_replace('([^/]+$)', '', $iUrl);
      $href = $baseHref.$iUrl.$url;
    }
    $pathAdmin = $this->papaya()->options->get('PAPAYA_PATH_ADMIN', '/');
    $href = preg_replace(
      '('.preg_quote($pathAdmin).'/../)', '/', $href
    );
    $transformer = new PapayaUrlTransformerCleanup();
    return $transformer->transform($href.$urlAppend);
  }

  /**
  * Get link
  *
  * @param mixed $params optional, default value NULL (no Query String)
  * @param mixed $paramName optional, default value NULL ($this->paramName)
  * @param string $fileName
  * @param integer $pageId
  * @access public
  * @return string
  */
  function getLink($params = NULL, $paramName = NULL, $fileName = '', $pageId = NULL) {
    if (isset($fileName) && trim($fileName) != '') {
      $link = $fileName;
    } else {
      $link = $this->getBaseLink(0);
    }
    if ((!isset($paramName)) && isset($this->paramName)) {
      $queryString = $this->encodeQueryString($params, $this->paramName);
    } elseif ($paramName == '') {
      $queryString = $this->encodeQueryString($params, NULL);
    } else {
      $queryString = $this->encodeQueryString($params, $paramName);
    }
    if (isset($pageId) && $pageId > 0) {
      if (trim($queryString) != '') {
        return $link.$queryString.'&p_id='.(int)$pageId;
      } else {
        return $link.'?p_id='.(int)$pageId;
      }
    } else {
      return $link.$queryString;
    }
  }

  /**
   * Show the debug of the variable $var
   *
   * @param mixed,... [$var] auszugebende Variable
   * @access public
   */
  public function debug() {
    $debugMessage = new PapayaMessageDebug(
      PapayaMessageLogable::GROUP_DEBUG, 'Variables'
    );
    foreach (func_get_args() as $variable) {
      $debugMessage->context()->append(
        new PapayaMessageContextVariable($variable)
      );
    }
    $this->papaya()->messages->dispatch($debugMessage);
  }

  /**
   * log a debug message
   *
   * @param string $message
   */
  function debugMsg($message = '') {
    $this->papaya()->messages->dispatch(
      new PapayaMessageDebug(PapayaMessageLogable::GROUP_DEBUG, $message)
    );
  }

  /**
  * prepends another directory to include path (if it doesn`t exist yet)
  * but leaves the current directory ('.') at position one
  *
  * @param string $newPath the new include directory to be added
  * @return boolean $wasModified whether the include path has been modified or not
  */
  function addIncludePath($newPath) {
    if (is_string($newPath) && trim($newPath) != '') {
      $completePath = get_include_path();
      if (substr(PHP_OS, 0, 3) == 'WIN') {
        $sep = ';';
      } else {
        $sep = ':';
      }
      $paths = explode($sep, $completePath);
      if (is_array($paths) && count($paths) > 0) {
        if (in_array($newPath, $paths)) {
          return FALSE;
        } else {
          if ($paths[0] == '.') {
            array_shift($paths);
            $paths = array_merge(array('.', $newPath), $paths);
          } else {
            $paths = array_merge(array($newPath), $paths);
          }
        }
      } else {
        $paths = $newPath;
      }
      if (set_include_path(implode($sep, $paths))) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Create XHTML-compliant string.
  *
  * @param string $str
  * @param boolean $nlToBr Convert line break in XHTML
  * @return string
  */
  public function getXHTMLString($str, $nlToBr = FALSE) {
    $iStr = papaya_strings::entityToXML($str);
    $iStr = papaya_strings::escapeHTMLTags($iStr, $nlToBr);
    if (trim($iStr) == '') {
      return '';
    } else {
      $errorStatus = libxml_use_internal_errors(TRUE);
      libxml_clear_errors();
      $dom = new DOMDocument('1.0', 'UTF-8');
      $fragment = $dom->createDocumentFragment();
      if (!$fragment->appendXml($iStr)) {
        /** @var PapayaApplicationCms $application */
        $application = PapayaApplication::getInstance();
        $showErrors = $application->options->get(
          'PAPAYA_DBG_XML_USERINPUT', FALSE
        );
        if ($showErrors) {
          $errors = libxml_get_errors();
          $errorOutput = '<div class="xmlError">'.LF;
          $errorOutput .= '<ul class="xmlErrorMessages">'.LF;
          foreach ($errors as $error) {
            if (in_array($error->level, array(LIBXML_ERR_ERROR, LIBXML_ERR_FATAL))) {
              $errorOutput .= sprintf(
                '<li>%d: %s in line %d at char %d</li>'.LF,
                (int)$error->code,
                PapayaUtilStringXml::escape($error->message),
                (int)$error->line,
                (int)$error->column
              );
            }
          }
          $errorOutput .= '</ul>'.LF;
          $errorOutput .= '<ol class="xmlBrokenFragment">'.LF;
          $lines = preg_split("(\r\n|\n\r|[\r\n])", $iStr);
          foreach ($lines as $line) {
            $errorOutput .= sprintf('<li>%s</li>'.LF, PapayaUtilStringXml::escape($line));
          }
          $errorOutput .= '</ol>'.LF;
          $errorOutput .= '</div>'.LF;
          $iStr = $errorOutput;
        } else {
          $iStr = PapayaUtilStringXml::escape($iStr);
        }
      }
      libxml_clear_errors();
      libxml_use_internal_errors($errorStatus);
    }
    return $iStr;
  }

  /**
   * Get/Set parameter handling method. For the base_object this is always "Mixed Post" and
   * can not be changed.
   *
   * @param integer $method
   * @throws LogicException
   * @return integer
   */
  public function parameterMethod($method = NULL) {
    if (isset($method)) {
      throw new LogicException('Can not change parameter method of %s', __CLASS__);
    }
    return self::METHOD_MIXED_POST;
  }

  /**
  * Get/Set the parameter group name.
  *
  * This puts all field parameters (except the hidden fields) into a parameter group.
  *
  * @param string|NULL $groupName
  * @return string|NULL
  */
  public function parameterGroup($groupName = NULL) {
    if (isset($groupName)) {
      $this->paramName = $groupName;
    }
    return $this->paramName;
  }

  /**
  * Access request parameters
  *
  * This method gives you access to request parameters.
  *
  * @param PapayaRequestParameters $parameters
  * @return PapayaRequestParameters
  */
  public function parameters(PapayaRequestParameters $parameters = NULL) {
    if (isset($parameters)) {
      $this->_parameters = $parameters;
      $this->params = $parameters->toArray();
    } elseif ($this->_parameters === NULL) {
      $this->_parameters = new PapayaRequestParameters($this->params);
    }
    return $this->_parameters;
  }
}

