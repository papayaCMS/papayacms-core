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
namespace Papaya {

  use Papaya\Request\Content as RequestContent;
  use Papaya\Request\Parameters as RequestParameters;
  use Papaya\Request\Parser as RequestParser;

  /**
   * Papaya Request Handling
   *
   * @package Papaya-Library
   * @subpackage Request
   *
   * @property-read URL $url
   * @property-read string $method
   * @property-read bool $allowCompression
   * @property-read RequestContent $content
   * @property-read int $contentLength
   */
  class Request
    implements Application\Access, BaseObject\Interfaces\Properties {
    use Application\Access\Aggregation;

    /**
     * Parameter source type: url path
     *
     * @var int
     */
    public const SOURCE_PATH = 1;

    /**
     * Parameter source type: query string
     *
     * @var int
     */
    public const SOURCE_QUERY = 2;

    /**
     * Parameter source type: request body ($_POST)
     *
     * @var int
     */
    public const SOURCE_BODY = 4;

    /**
     * Parameter source group: body, query, path (in this priority)
     *
     * @var int
     */
    public const SOURCE_ALL = 7;

    /**
     * Parameter source type: cookie (not included in SOURCE_ALL)
     *
     * @var int
     */
    public const SOURCE_COOKIE = 8;

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
    private $_basePath = '/';

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
     * @var RequestContent
     */
    private $_content;

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name) {
      $name = Utility\Text\Identifier::toCamelCase($name);
      switch ($name) {
      case 'url' :
      case 'method' :
      case 'allowCompression' :
      case 'content' :
      case 'contentLength' :
        return TRUE;
      }
      return FALSE;
    }

    /**
     * Allow to read request data as properties
     *
     * @param string $name
     *
     * @return mixed
     * @throws \LogicException
     *
     */
    public function __get($name) {
      $name = Utility\Text\Identifier::toCamelCase($name);
      switch ($name) {
      case 'url' :
        return $this->getURL();
      case 'method' :
        return $this->getMethod();
      case 'allowCompression' :
        return $this->allowCompression();
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
     * Allow to set request sub objects as properties, block other changes
     *
     * @param string $name
     * @param mixed $value
     *
     * @throws \LogicException
     */
    public function __set($name, $value) {
      throw new \LogicException(
        \sprintf(
          'Property %s::$%s can not be changed', \get_class($this), $name
        )
      );
    }

    /**
     * @param $name
     */
    public function __unset($name) {
      $name = Utility\Text\Identifier::toCamelCase($name);
      throw new \LogicException(
        \sprintf(
          'Property %s::$%s can not be unset', \get_class($this), $name
        )
      );
    }

    /**
     * get the attached url object
     *
     * @return URL
     */
    public function getURL() {
      if (NULL === $this->_url) {
        $this->load(new URL\Current());
      }
      return $this->_url;
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
     *
     * @return string
     * @throws \InvalidArgumentException
     *
     */
    public function setParameterGroupSeparator($separator) {
      if ('' === (string)$separator) {
        $this->_separator = RequestParameters\GroupSeparator::ARRAY_SYNTAX;
      } elseif (RequestParameters\GroupSeparator::validate($separator, TRUE)) {
        $this->_separator = $separator;
      } else {
        throw new \InvalidArgumentException(
          'Invalid parameter group separator: '.$separator
        );
      }
      return $this;
    }

    public function setBasePath(string $basePath) {
      return $this->_basePath = $basePath ?: '/';
    }

    /**
     * get base web path (without file name)
     *
     * @return string
     */
    public function getBasePath() {
      if ($session = $this->getParameter('session', '', NULL, self::SOURCE_PATH)) {
        return '/'.$session.$this->_basePath;
      }
      return $this->_basePath;
    }

    /**
     * Initialize request parsers if not already done and
     * return them.
     */
    public function getParsers() {
      if (empty($this->_parsers)) {
        $this->_parsers = [
          new Request\Parser\Session()
        ];
        /** @var RequestParser $parser */
        foreach ($this->_parsers as $parser) {
          $parser->papaya($this->papaya());
        }
      }
      return $this->_parsers;
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
     *
     * @return bool
     */
    public function load(URL $url) {
      $this->_url = $url;
      $this->_pathData = [];
      foreach ($this->getParsers() as $parser) {
        /** @var RequestParser $parser */
        if ($requestData = $parser->parse($url)) {
          $this->_pathData = Utility\Arrays::merge(
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
      /** @noinspection PhpDeprecationInspection */
      return is_callable('get_magic_quotes_gpc') ? @(\get_magic_quotes_gpc() || \get_magic_quotes_runtime()) : FALSE;
    }

    /**
     * Initialize and cache parameter for the specified source
     *
     * @param int $source
     * @return RequestParameters
     */
    private function _loadParametersForSource($source) {
      if (
        isset($this->_parameterCache[$source]) &&
        $this->_parameterCache[$source] instanceof Request\Parameters
      ) {
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
        if (NULL !== $this->_url) {
          $query = new Request\Parameters\QueryString($this->_separator);
          $parameters->merge(
            $query->setString($this->_url->getQuery())->values()
          );
        }
        break;
      case self::SOURCE_BODY :
        $parameters->merge(
          $parameters->prepareParameter($_POST, $this->getMagicQuotesStatus())
        );
        break;
      case self::SOURCE_COOKIE :
        $parameters->merge(
          $parameters->prepareParameter($_COOKIE, $this->getMagicQuotesStatus())
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
     *
     * @return RequestParameters
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
        /** @noinspection NotOptimalIfConditionsInspection */
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
     *
     * @return RequestParameters
     */
    public function getParameters($sources = self::SOURCE_ALL) {
      return $this->loadParameters($sources);
    }

    /**
     * Get a request parameter value
     *
     * @param string $name
     * @param mixed $defaultValue
     * @param Filter $filter
     * @param int $sources
     *
     * @return mixed
     */
    public function getParameter(
      $name, $defaultValue = NULL, Filter $filter = NULL, $sources = self::SOURCE_ALL
    ) {
      $parameters = $this->loadParameters($sources);
      return $parameters->get($name, $defaultValue, $filter);
    }

    /**
     * Get a group
     *
     * @param string $name
     * @param int $sources
     *
     * @return RequestParameters
     */
    public function getParameterGroup($name, $sources = self::SOURCE_ALL) {
      $parameters = $this->loadParameters($sources);
      return $parameters->getGroup($name);
    }

    /**
     * Set parameters object for a source. This resets all merged parameter caches
     *
     * @param int $source
     * @param RequestParameters $parameters
     *
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
      return \in_array($method, self::$_allowedMethods, TRUE) ? $method : 'get';
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
      return (NULL !== $header && 'yes' === \strtolower($header));
    }

    /**
     * Check if the browser cache is valid using HTTP headers.
     *
     * @param string $cacheId
     * @param $lastModified
     *
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
     * @param RequestContent $content
     *
     * @return RequestContent
     */
    public function content(Request\Content $content = NULL) {
      if (NULL !== $content) {
        $this->_content = $content;
      } elseif (NULL === $this->_content) {
        $this->_content = new Request\Content();
      }
      return $this->_content;
    }
  }
}
