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
* Papaya Request Handling
*
* @package Papaya-Library
* @subpackage Request
*
* @property PapayaContentLanguage $language
* @property PapayaContentViewMode $mode
* @property-read PapayaUrl $url
* @property-read string $method
* @property-read boolean $allowCompression
* @property-read integer $pageId
* @property-read integer $categoryId
* @property-read integer $languageId
* @property-read string $languageIdentifier
* @property-read integer $modeId
* @property-read boolean $isPreview
* @property-read PapayaRequestContent $content
* @property-read int $contentLength
*/
class PapayaRequest
  extends PapayaObject
  implements PapayaObjectInterfaceProperties {

  /**
  * Paramter source type: url path
  * @var integer
  */
  const SOURCE_PATH = 1;
  /**
  * Paramter source type: query string
  * @var integer
  */
  const SOURCE_QUERY = 2;
  /**
  * Paramter source type: request body ($_POST)
  * @var integer
  */
  const SOURCE_BODY = 4;

  /**
  * Paramter source group: body, query, path (in this priority)
  * @var integer
  */
  const SOURCE_ALL = 7;

  /**
  * Paramter source type: cookie (not included in SOURCE_ALL)
  * @var integer
  */
  const SOURCE_COOKIE = 8;

  /**
  * allowed request methods
  */
  private static $_allowedMethods = array(
    'get', 'post', 'put', 'delete'
  );

  /**
  * separator for query string parameter groups
  * @var string
  */
  private $_separator = ':';
  /**
  * cms installation path
  * @var string
  */
  private $_installationPath = '/';

  /**
  * Request parsers list
  * @var array
  */
  private $_parsers = array();
  /**
  * Request url object
  * @var PapayaUrl
  */
  private $_url = NULL;
  /**
  * Request url object
  * @var PapayaUrl
  */
  private $_language = NULL;

  private $_mode;
  /**
  * Request path parameter data
  * @var array
  */
  private $_pathData = array();

  /**
  * internal cache for parameter objects
  * @var array
  */
  private $_parameterCache = array();

  /**
  * Does the client that sent the request allow gzip compression of the response.
  *
  * @var boolean|NULL
  */
  private $_allowCompression = NULL;

  /**
   * Access to the raw request content
   *
   * @var PapayaRequestContent
   */
  private $_content = NULL;

  /**
  * Create object and set options if given.
  *
  * @param PapayaConfiguration $options
  */
  public function __construct($options = NULL) {
    if (isset($options)) {
      $this->setConfiguration($options);
    }
  }

  /**
   * Allow to read request data as properties
   *
   * @param string $name
   * @throws LogicException
   * @return mixed
   */
  public function __get($name) {
    $name = PapayaUtilStringIdentifier::toCamelCase($name);
    switch ($name) {
    case 'url' :
      return $this->getUrl();
    case 'language' :
      return $this->language();
    case 'method' :
      return $this->getMethod();
    case 'allowCompression' :
      return $this->allowCompression();
    case 'pageId' :
      return $this->getParameter(
        'page_id',
        $this->papaya()->options->get('PAPAYA_PAGEID_DEFAULT', 0),
        NULL,
        self::SOURCE_PATH
      );
    case 'categoryId' :
      return $this->getParameter(
        'category_id',
        0,
        NULL,
        self::SOURCE_PATH
      );
    case 'languageId' :
      return (int)$this->language->id;
    case 'languageIdentifier' :
      return $this->language->identifier;
    case 'mode' :
      return $this->mode();
    case 'modeId' :
      return $this->mode()->id;
    case 'isPreview' :
      return $this->getParameter(
        'preview', FALSE, NULL, PapayaRequest::SOURCE_PATH
      );
    case 'isAdministration' :
      return defined('PAPAYA_ADMIN_PAGE') && constant('PAPAYA_ADMIN_PAGE');
    case 'content' :
      return $this->content();
    case 'contentLength' :
      return $this->content()->length();
    }
    throw new LogicException(
      sprintf(
        'Property %s::$%s can not be changed', get_class($this), $name
      )
    );
  }

  /**
   * Allow to set request subobjects as properties, block other changes
   *
   * @param string $name
   * @param mixed $value
   * @throws LogicException
   */
  public function __set($name, $value) {
    $name = PapayaUtilStringIdentifier::toCamelCase($name);
    switch ($name) {
    case 'language' :
      $this->language($value);
      return;
    case 'mode' :
      $this->mode($value);
      return;
    }
    throw new LogicException(
      sprintf(
        'Property %s::$%s can not be changed', get_class($this), $name
      )
    );
  }

  /**
  * Initialize object configuration
  * @param PapayaConfiguration $options
  */
  public function setConfiguration($options) {
    $this->_separator = $options->get('PAPAYA_URL_LEVEL_SEPARATOR', '[]');
    $this->_installationPath = $options->get('PAPAYA_PATH_WEB', '/');
  }

  /**
  * get the attached url object
  * @return PapayaUrl|NULL
  */
  public function getUrl() {
    if (is_null($this->_url)) {
      $this->load(new PapayaUrlCurrent());
    }
    return $this->_url;
  }

  /**
   * Getter/Setter for the request language
   *
   * @param PapayaContentLanguage $language
   * @return PapayaContentLanguage
   */
  public function language(PapayaContentLanguage $language = NULL) {
    if (isset($language)) {
      $this->_language = $language;
    } elseif (NULL == $this->_language) {
      $this->_language = new PapayaContentLanguage($language);
      $this->_language->papaya($this->papaya());
      if ($identifier = $this->getParameter('language', '', NULL, PapayaRequest::SOURCE_PATH)) {
        $this->_language->activateLazyLoad(
          array('identifier' => $identifier)
        );
      } elseif ($id = $this->papaya()->options->get('PAPAYA_CONTENT_LANGUAGE', 0)) {
        $this->_language->activateLazyLoad(
          array('id' => $id)
        );
      }
    }
    return $this->_language;
  }

  /**
   * Getter/Setter for view mode object
   *
   * @param PapayaContentViewMode $mode
   * @return \PapayaContentViewMode
   */
  public function mode(PapayaContentViewMode $mode = NULL) {
    if (isset($mode)) {
      $this->_mode = $mode;
    } elseif (NULL == $this->_mode) {
      $this->_mode = new PapayaContentViewMode();
      $this->_mode->papaya($this->papaya());
      $extension = $this->getParameter(
        'output_mode', 'html', NULL, PapayaRequest::SOURCE_PATH
      );
      switch ($extension) {
      case 'xml' :
        $this->_mode->assign(
          array(
            'id' => -1,
            'extension' => 'xml',
            'type' => 'page',
            'charset' => 'utf-8',
            'content_type' => 'application/xml'
          )
        );
        break;
      default :
        $this->_mode->activateLazyLoad(
          array('extension' => $extension)
        );
        break;
      }
    }
    return $this->_mode;
  }

  /**
  * Get parameter group separator
  * @return string
  */
  public function getParameterGroupSeparator() {
    return $this->_separator;
  }

  /**
   * Set parameter group separator if valid
   *
   * @param string $separator
   * @throws InvalidArgumentException
   * @return string
   */
  public function setParameterGroupSeparator($separator) {
    if ($separator == '') {
      $this->_separator = '[]';
    } elseif (in_array($separator, array('[]', ',', ':', '/', '*', '!'))) {
      $this->_separator = $separator;
    } else {
      throw new InvalidArgumentException(
        'Invalid parameter level separator: '.$separator
      );
    }
    return $this;
  }


  /**
  * get base web path (without file name)
  * @return string
  */
  public function getBasePath() {
    if ($session = $this->getParameter('session', '', NULL, PapayaRequest::SOURCE_PATH)) {
      return '/'.$session.$this->_installationPath;
    } else {
      return $this->_installationPath;
    }
  }

  /**
  * Initialize request parsers if not already done
  * @return void
  */
  private function _initParsers() {
    if (empty($this->_parsers)) {
      $this->_parsers = array(
        new PapayaRequestParserSession(),
        new PapayaRequestParserFile(),
        new PapayaRequestParserSystem(),
        new PapayaRequestParserPage(),
        new PapayaRequestParserThumbnail(),
        new PapayaRequestParserMedia(),
        new PapayaRequestParserImage(),
        new PapayaRequestParserWrapper(),
        new PapayaRequestParserStart()
      );
      /** @var PapayaRequestParser $parser */
      foreach ($this->_parsers as $parser) {
        $parser->papaya($this->papaya());
      }
    }
  }

  /**
  * Set request parsers
  * @param array $parsers
  * @return void
  */
  public function setParsers($parsers) {
    $this->_parsers = $parsers;
  }

  /**
  * Load and parse request
  * @param PapayaUrl $url
  * @return boolean
  */
  public function load(PapayaUrl $url) {
    $this->_url = $url;
    $this->_initParsers();
    $this->_pathData = array();
    foreach ($this->_parsers as $parser) {
      /** @var PapayaRequestParser $parser */
      if ($requestData = $parser->parse($url)) {
        $this->_pathData = PapayaUtilArray::merge(
          $this->_pathData,
          $requestData
        );
        if ($parser->isLast()) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
  * return current magic quotes status
  * @return boolean
  */
  public function getMagicQuotesStatus() {
    return (get_magic_quotes_gpc() || get_magic_quotes_runtime());
  }

  /**
  * Initialize and cache parameter for the specified source
  *
  * @param Integer $source
   * @return \PapayaRequestParameters
   */
  private function _loadParametersForSource($source) {
    if (isset($this->_parameterCache[$source]) &&
        $this->_parameterCache[$source] instanceof PapayaRequestParameters) {
      return $this->_parameterCache[$source];
    }
    $parameters = new PapayaRequestParameters();
    switch ($source) {
    case PapayaRequest::SOURCE_PATH :
      $parameters->merge(
        $parameters->prepareParameter($this->_pathData)
      );
      break;
    case PapayaRequest::SOURCE_QUERY :
      if (isset($this->_url)) {
        $query = new PapayaRequestParametersQuery($this->_separator);
        $parameters->merge(
          $query->setString($this->_url->getQuery())->values()
        );
      }
      break;
    case PapayaRequest::SOURCE_BODY :
      $parameters->merge(
        $parameters->prepareParameter(
          $_POST,
          $this->getMagicQuotesStatus()
        )
      );
      break;
    case PapayaRequest::SOURCE_COOKIE :
      $parameters->merge(
        $parameters->prepareParameter(
          $_COOKIE,
          $this->getMagicQuotesStatus()
        )
      );
      break;
    }
    $this->_parameterCache[$source] = $parameters;
    return $parameters;
  }

  /**
  * Load parameters into PapayaRequestParameters object and return it.
  *
  * Merges parameter data from different sources and uses an object cache
  *
  * @param $sources
  * @return PapayaRequestParameters
  */
  public function loadParameters($sources = PapayaRequest::SOURCE_ALL) {
    if (!isset($this->_parameterCache[$sources])) {
      $parameters = new PapayaRequestParameters();
      if ($sources == PapayaRequest::SOURCE_COOKIE) {
        return $this->_loadParametersForSource(PapayaRequest::SOURCE_COOKIE);
      }
      if ($sources & PapayaRequest::SOURCE_PATH) {
        $parameters->merge(
          $this->_loadParametersForSource(PapayaRequest::SOURCE_PATH)
        );
      }
      if ($sources & PapayaRequest::SOURCE_QUERY) {
        $parameters->merge(
          $this->_loadParametersForSource(PapayaRequest::SOURCE_QUERY)
        );
      }
      if ($sources & PapayaRequest::SOURCE_BODY) {
        $parameters->merge(
          $this->_loadParametersForSource(PapayaRequest::SOURCE_BODY)
        );
      }
      if (!isset($this->_parameterCache[$sources])) {
        $this->_parameterCache[$sources] = $parameters;
      }
      return $parameters;
    }
    return $this->_parameterCache[$sources];
  }

  /**
  * Get a parameters object containing all parameters from the given sources
  * @param integer $sources
  * @return PapayaRequestParameters
  */
  public function getParameters($sources = PapayaRequest::SOURCE_ALL) {
    return $this->loadParameters($sources);
  }

  /**
  * Get a request parameter value
  * @param string $name
  * @param mixed $defaultValue
  * @param PapayaFilter $filter
  * @param integer $sources
  * @return mixed
  */
  public function getParameter(
    $name, $defaultValue = NULL, $filter = NULL, $sources = PapayaRequest::SOURCE_ALL
  ) {
    $parameters = $this->loadParameters($sources);
    return $parameters->get($name, $defaultValue, $filter);
  }

  /**
  * Get a group
  * @param string $name
  * @param integer $sources
  * @return PapayaRequestParameters
   */
  public function getParameterGroup($name, $sources = PapayaRequest::SOURCE_ALL) {
    $parameters = $this->loadParameters($sources);
    return $parameters->getGroup($name);
  }

  /**
   * Set parameters object for a source. This resets all merged parameter caches
   * @param integer $source
   * @param PapayaRequestParameters $parameters
   * @throws InvalidArgumentException
   * @return void
   */
  public function setParameters($source, $parameters) {
    $validSources = array(
      PapayaRequest::SOURCE_PATH,
      PapayaRequest::SOURCE_QUERY,
      PapayaRequest::SOURCE_BODY,
      PapayaRequest::SOURCE_COOKIE
    );
    if (in_array($source, $validSources) &&
        $parameters instanceof PapayaRequestParameters) {
      $this->_parameterCache[$source] = $parameters;
      foreach ($this->_parameterCache as $cachedSource => $cachedParameters) {
        if (!in_array($cachedSource, $validSources)) {
          unset($this->_parameterCache[$cachedSource]);
        }
      }
    } else {
      throw new InvalidArgumentException();
    }
  }

  /**
  * return the request method
  * @return string
  */
  public function getMethod() {
    $method = empty($_SERVER['REQUEST_METHOD']) ? '' : strtolower($_SERVER['REQUEST_METHOD']);
    if (in_array($method, self::$_allowedMethods)) {
      return $method;
    } else {
      return 'get';
    }
  }

  /**
  * Check if the client that send the request allows gzip compression of the response.
  *
  * The value will be cached into $_allowCompression for optimization.
  *
  * @return boolean
  */
  public function allowCompression() {
    if (is_null($this->_allowCompression)) {
      $this->_allowCompression = FALSE;
      if (!function_exists('gzencode') ||
          (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.0') ||
          ini_get('zlib.output_compression')) {
        return $this->_allowCompression;
      }
      if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
        $encodings = preg_split('(\s*,\s*)', strtolower(trim($_SERVER['HTTP_ACCEPT_ENCODING'])));
        if (in_array('gzip', $encodings) || in_array('x-gzip', $encodings)) {
          $this->_allowCompression = TRUE;
        }
      }
    }
    return $this->_allowCompression;
  }

  /**
   * Check for an X_PAPAYA_ESI http header. If it is here allow Edge Side Includes.
   *
   * @return bool
   */
  public function allowEsi() {
    $headerNames = array('X_PAPAYA_ESI', 'HTTP_X_PAPAYA_ESI');
    $header = NULL;
    foreach ($headerNames as $name) {
      if (isset($_SERVER[$name])) {
        $header = $_SERVER[$name];
      }
    }
    return (isset($header) && strtolower($header) === 'yes');
  }

  /**
   * Check if the browser cache is valid using HTTP headers.
   *
   * @param string $cacheId
   * @param $lastModified
   * @return bool
   */
  public function validateBrowserCache($cacheId, $lastModified) {
    if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
      if ($cacheId == $_SERVER['HTTP_IF_NONE_MATCH'] ||
          '"'.$cacheId.'"' == $_SERVER['HTTP_IF_NONE_MATCH']) {
        $modifiedSince = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
        if ($lastModified > 0 &&
            $lastModified <= $modifiedSince) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Returns the content of the request (as available in php://input) as an object that
   * takes it castable to a string.
   *
   * @param PapayaRequestContent $content
   * @return PapayaRequestContent
   */
  public function content(PapayaRequestContent $content = NULL) {
    if (isset($content)) {
      $this->_content = $content;
    } elseif (NULL === $this->_content) {
      $this->_content = new PapayaRequestContent();
    }
    return $this->_content;
  }
}

