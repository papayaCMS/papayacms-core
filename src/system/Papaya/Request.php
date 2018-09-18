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

namespace Papaya;

/**
 * Papaya Request Handling
 *
 * @package Papaya-Library
 * @subpackage Request
 *
 * @property \Papaya\Content\Language $language
 * @property \Papaya\Content\View\Mode $mode
 * @property-read URL $url
 * @property-read string $method
 * @property-read bool $allowCompression
 * @property-read int $pageId
 * @property-read int $languageId
 * @property-read string $languageIdentifier
 * @property-read int $modeId
 * @property-read bool $isPreview
 * @property-read \Papaya\Request\Content $content
 * @property-read int $contentLength
 */
class Request
  extends Application\BaseObject
  implements BaseObject\Interfaces\Properties {
  /**
   * Paramter source type: url path
   *
   * @var int
   */
  const SOURCE_PATH = 1;

  /**
   * Paramter source type: query string
   *
   * @var int
   */
  const SOURCE_QUERY = 2;

  /**
   * Paramter source type: request body ($_POST)
   *
   * @var int
   */
  const SOURCE_BODY = 4;

  /**
   * Paramter source group: body, query, path (in this priority)
   *
   * @var int
   */
  const SOURCE_ALL = 7;

  /**
   * Paramter source type: cookie (not included in SOURCE_ALL)
   *
   * @var int
   */
  const SOURCE_COOKIE = 8;

  /**
   * allowed request methods
   */
  private static $_allowedMethods = [
    'get', 'post', 'put', 'delete'
  ];

  /**
   * separator for query string parameter groups
   *
   * @var string
   */
  private $_separator = ':';

  /**
   * cms installation path
   *
   * @var string
   */
  private $_installationPath = '/';

  /**
   * Request parsers list
   *
   * @var array
   */
  private $_parsers = [];

  /**
   * Request url object
   *
   * @var URL
   */
  private $_url;

  /**
   * Request url object
   *
   * @var URL
   */
  private $_language;

  private $_mode;

  /**
   * Request path parameter data
   *
   * @var array
   */
  private $_pathData = [];

  /**
   * internal cache for parameter objects
   *
   * @var array
   */
  private $_parameterCache = [];

  /**
   * Does the client that sent the request allow gzip compression of the response.
   *
   * @var bool|null
   */
  private $_allowCompression;

  /**
   * Access to the raw request content
   *
   * @var \Papaya\Request\Content
   */
  private $_content;

  /**
   * Create object and set options if given.
   *
   * @param \Papaya\Configuration $options
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
   * @throws \LogicException
   * @return mixed
   */
  public function __get($name) {
    $name = Utility\Text\Identifier::toCamelCase($name);
    switch ($name) {
      case 'url' :
        return $this->getURL();
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
          'preview', FALSE, NULL, self::SOURCE_PATH
        );
      case 'isAdministration' :
        return \defined('PAPAYA_ADMIN_PAGE') && \constant('PAPAYA_ADMIN_PAGE');
      case 'content' :
        return $this->content();
      case 'contentLength' :
        return $this->content()->length();
    }
    throw new \LogicException(
      \sprintf(
        'Property %s::$%s can not be changed', \get_class($this), $name
      )
    );
  }

  /**
   * Allow to set request subobjects as properties, block other changes
   *
   * @param string $name
   * @param mixed $value
   * @throws \LogicException
   */
  public function __set($name, $value) {
    $name = Utility\Text\Identifier::toCamelCase($name);
    switch ($name) {
      case 'language' :
        $this->language($value);
        return;
      case 'mode' :
        $this->mode($value);
        return;
    }
    throw new \LogicException(
      \sprintf(
        'Property %s::$%s can not be changed', \get_class($this), $name
      )
    );
  }

  /**
   * Initialize object configuration
   *
   * @param \Papaya\Configuration $options
   */
  public function setConfiguration($options) {
    $this->_separator = $options->get('PAPAYA_URL_LEVEL_SEPARATOR', '[]');
    $this->_installationPath = $options->get('PAPAYA_PATH_WEB', '/');
  }

  /**
   * get the attached url object
   *
   * @return URL|null
   */
  public function getURL() {
    if (NULL === $this->_url) {
      $this->load(new URL\Current());
    }
    return $this->_url;
  }

  /**
   * Getter/Setter for the request language
   *
   * @param \Papaya\Content\Language $language
   * @return \Papaya\Content\Language
   */
  public function language(\Papaya\Content\Language $language = NULL) {
    if (NULL !== $language) {
      $this->_language = $language;
    } elseif (NULL === $this->_language) {
      $this->_language = new \Papaya\Content\Language();
      $this->_language->papaya($this->papaya());
      if ($identifier = $this->getParameter('language', '', NULL, self::SOURCE_PATH)) {
        $this->_language->activateLazyLoad(
          ['identifier' => $identifier]
        );
      } elseif ($id = $this->papaya()->options->get('PAPAYA_CONTENT_LANGUAGE', 0)) {
        $this->_language->activateLazyLoad(
          ['id' => $id]
        );
      }
    }
    return $this->_language;
  }

  /**
   * Getter/Setter for view mode object
   *
   * @param \Papaya\Content\View\Mode $mode
   * @return \Papaya\Content\View\Mode
   */
  public function mode(\Papaya\Content\View\Mode $mode = NULL) {
    if (isset($mode)) {
      $this->_mode = $mode;
    } elseif (NULL == $this->_mode) {
      $this->_mode = new \Papaya\Content\View\Mode();
      $this->_mode->papaya($this->papaya());
      $extension = $this->getParameter(
        'output_mode', 'html', NULL, self::SOURCE_PATH
      );
      if ('xml' === $extension) {
        $this->_mode->assign(
          [
            'id' => -1,
            'extension' => 'xml',
            'type' => 'page',
            'charset' => 'utf-8',
            'content_type' => 'application/xml'
          ]
        );
      } else {
        $this->_mode->activateLazyLoad(
          ['extension' => $extension]
        );
      }
    }
    return $this->_mode;
  }

  /**
   * Get parameter group separator
   *
   * @return string
   */
  public function getParameterGroupSeparator() {
    return $this->_separator;
  }

  /**
   * Set parameter group separator if valid
   *
   * @param string $separator
   * @throws \InvalidArgumentException
   * @return string
   */
  public function setParameterGroupSeparator($separator) {
    if ('' == $separator) {
      $this->_separator = '[]';
    } elseif (\in_array($separator, ['[]', ',', ':', '/', '*', '!'])) {
      $this->_separator = $separator;
    } else {
      throw new \InvalidArgumentException(
        'Invalid parameter level separator: '.$separator
      );
    }
    return $this;
  }

  /**
   * get base web path (without file name)
   *
   * @return string
   */
  public function getBasePath() {
    if ($session = $this->getParameter('session', '', NULL, self::SOURCE_PATH)) {
      return '/'.$session.$this->_installationPath;
    }
    return $this->_installationPath;
  }

  /**
   * Initialize request parsers if not already done
   */
  private function _initParsers() {
    if (empty($this->_parsers)) {
      $this->_parsers = [
        new Request\Parser\Session(),
        new Request\Parser\File(),
        new Request\Parser\System(),
        new Request\Parser\Page(),
        new Request\Parser\Thumbnail(),
        new Request\Parser\Media(),
        new Request\Parser\Image(),
        new Request\Parser\Wrapper(),
        new Request\Parser\Start()
      ];
      /** @var \Papaya\Request\Parser $parser */
      foreach ($this->_parsers as $parser) {
        $parser->papaya($this->papaya());
      }
    }
  }

  /**
   * Set request parsers
   *
   * @param array $parsers
   */
  public function setParsers($parsers) {
    $this->_parsers = $parsers;
  }

  /**
   * Load and parse request
   *
   * @param URL $url
   * @return bool
   */
  public function load(URL $url) {
    $this->_url = $url;
    $this->_initParsers();
    $this->_pathData = [];
    foreach ($this->_parsers as $parser) {
      /** @var \Papaya\Request\Parser $parser */
      if ($requestData = $parser->parse($url)) {
        $this->_pathData = \Papaya\Utility\Arrays::merge(
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
   *
   * @return bool
   */
  public function getMagicQuotesStatus() {
    return (\get_magic_quotes_gpc() || \get_magic_quotes_runtime());
  }

  /**
   * Initialize and cache parameter for the specified source
   *
   * @param \Integer $source
   * @return \Papaya\Request\Parameters
   */
  private function _loadParametersForSource($source) {
    if (isset($this->_parameterCache[$source]) &&
      $this->_parameterCache[$source] instanceof Request\Parameters) {
      return $this->_parameterCache[$source];
    }
    $parameters = new Request\Parameters();
    switch ($source) {
      case self::SOURCE_PATH :
        $parameters->merge(
          $parameters->prepareParameter($this->_pathData)
        );
      break;
      case self::SOURCE_QUERY :
        if (isset($this->_url)) {
          $query = new Request\Parameters\QueryString($this->_separator);
          $parameters->merge(
            $query->setString($this->_url->getQuery())->values()
          );
        }
      break;
      case self::SOURCE_BODY :
        $parameters->merge(
          $parameters->prepareParameter(
            $_POST,
            $this->getMagicQuotesStatus()
          )
        );
      break;
      case self::SOURCE_COOKIE :
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
   * Load parameters into \Papaya\Request\Parameters object and return it.
   *
   * Merges parameter data from different sources and uses an object cache
   *
   * @param $sources
   * @return \Papaya\Request\Parameters
   */
  public function loadParameters($sources = self::SOURCE_ALL) {
    if (!isset($this->_parameterCache[$sources])) {
      $parameters = new Request\Parameters();
      if (self::SOURCE_COOKIE === $sources) {
        return $this->_loadParametersForSource(self::SOURCE_COOKIE);
      }
      if (self::SOURCE_PATH & $sources) {
        $parameters->merge(
          $this->_loadParametersForSource(self::SOURCE_PATH)
        );
      }
      if ($sources & self::SOURCE_QUERY) {
        $parameters->merge(
          $this->_loadParametersForSource(self::SOURCE_QUERY)
        );
      }
      if ($sources & self::SOURCE_BODY) {
        $parameters->merge(
          $this->_loadParametersForSource(self::SOURCE_BODY)
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
   *
   * @param int $sources
   * @return \Papaya\Request\Parameters
   */
  public function getParameters($sources = self::SOURCE_ALL) {
    return $this->loadParameters($sources);
  }

  /**
   * Get a request parameter value
   *
   * @param string $name
   * @param mixed $defaultValue
   * @param \Papaya\Filter $filter
   * @param int $sources
   * @return mixed
   */
  public function getParameter(
    $name, $defaultValue = NULL, $filter = NULL, $sources = self::SOURCE_ALL
  ) {
    $parameters = $this->loadParameters($sources);
    return $parameters->get($name, $defaultValue, $filter);
  }

  /**
   * Get a group
   *
   * @param string $name
   * @param int $sources
   * @return \Papaya\Request\Parameters
   */
  public function getParameterGroup($name, $sources = self::SOURCE_ALL) {
    $parameters = $this->loadParameters($sources);
    return $parameters->getGroup($name);
  }

  /**
   * Set parameters object for a source. This resets all merged parameter caches
   *
   * @param int $source
   * @param \Papaya\Request\Parameters $parameters
   * @throws \InvalidArgumentException
   */
  public function setParameters($source, $parameters) {
    $validSources = [
      self::SOURCE_PATH,
      self::SOURCE_QUERY,
      self::SOURCE_BODY,
      self::SOURCE_COOKIE
    ];
    if (
      $parameters instanceof Request\Parameters &&
      \in_array($source, $validSources, TRUE)
    ) {
      $this->_parameterCache[$source] = $parameters;
      foreach ($this->_parameterCache as $cachedSource => $cachedParameters) {
        if (!\in_array($cachedSource, $validSources, TRUE)) {
          unset($this->_parameterCache[$cachedSource]);
        }
      }
    } else {
      throw new \InvalidArgumentException(
        \sprintf(
          'Only %1$s::SOURCE_* constants allowed.', __CLASS__
        )
      );
    }
  }

  /**
   * return the request method
   *
   * @return string
   */
  public function getMethod() {
    $method = empty($_SERVER['REQUEST_METHOD']) ? '' : \strtolower($_SERVER['REQUEST_METHOD']);
    if (\in_array($method, self::$_allowedMethods)) {
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
   * @return bool
   */
  public function allowCompression() {
    if (NULL === $this->_allowCompression) {
      $this->_allowCompression = FALSE;
      if (
        (isset($_SERVER['SERVER_PROTOCOL']) && 'HTTP/1.0' === $_SERVER['SERVER_PROTOCOL']) ||
        !\function_exists('gzencode') ||
        \ini_get('zlib.output_compression')
      ) {
        return $this->_allowCompression;
      }
      if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
        $encodings = \preg_split('(\s*,\s*)', \strtolower(\trim($_SERVER['HTTP_ACCEPT_ENCODING'])));
        if (\in_array('gzip', $encodings, TRUE) || \in_array('x-gzip', $encodings, TRUE)) {
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
    $headerNames = ['X_PAPAYA_ESI', 'HTTP_X_PAPAYA_ESI'];
    $header = NULL;
    foreach ($headerNames as $name) {
      if (isset($_SERVER[$name])) {
        $header = $_SERVER[$name];
      }
    }
    return (isset($header) && 'yes' === \strtolower($header));
  }

  /**
   * Check if the browser cache is valid using HTTP headers.
   *
   * @param string $cacheId
   * @param $lastModified
   * @return bool
   */
  public function validateBrowserCache($cacheId, $lastModified) {
    if (
      isset($_SERVER['HTTP_IF_NONE_MATCH'], $_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
      (
        $cacheId === $_SERVER['HTTP_IF_NONE_MATCH'] ||
        '"'.$cacheId.'"' === $_SERVER['HTTP_IF_NONE_MATCH']
      )
    ) {
      $modifiedSince = \strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
      if ($lastModified > 0 && $lastModified <= $modifiedSince) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Returns the content of the request (as available in php://input) as an object that
   * takes it castable to a string.
   *
   * @param \Papaya\Request\Content $content
   * @return \Papaya\Request\Content
   */
  public function content(Request\Content $content = NULL) {
    if (isset($content)) {
      $this->_content = $content;
    } elseif (NULL === $this->_content) {
      $this->_content = new Request\Content();
    }
    return $this->_content;
  }
}
