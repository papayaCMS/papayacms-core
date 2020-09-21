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
namespace Papaya\UI {

  use Papaya\Application;
  use Papaya\Request;
  use Papaya\Request\Parameters\GroupSeparator;
  use Papaya\URL;

  /**
   * Papaya Interface Reference (Hyperlink Reference)
   *
   * @package Papaya-Library
   * @subpackage UI
   */
  class Reference implements Application\Access {
    use Application\Access\Aggregation;

    /**
     * URL group separator
     *
     * @var string
     */
    private $_parameterGroupSeparator;

    /**
     * parameters list
     *
     * @var Request\Parameters
     */
    private $_parametersObject;

    /**
     * Internal url object
     *
     * @var URL
     */
    private $_url;

    /**
     * Base web path
     *
     * @var string
     */
    protected $_basePath = '/';

    /**
     * Reference status
     *
     * @var string
     */
    private $_valid = TRUE;

    /**
     * create object and load url if provided.
     *
     * @param URL $url
     */
    public function __construct(URL $url = NULL) {
      if (NULL !== $url) {
        $this->url($url);
      }
    }

    /**
     * Other object can mark an reference as valid or invalid after testing it. An invalid reference
     * will return an empty string as url (get() and getRelative()).
     *
     * @param bool $isValid
     *
     * @return bool
     */
    public function valid($isValid = NULL) {
      if (NULL !== $isValid) {
        $this->_valid = $isValid;
      }
      return $this->_valid;
    }

    /**
     * Static create function to allow fluent calls.
     *
     * @param URL $url
     *
     * @return self
     */
    public static function create(URL $url = NULL) {
      return new self($url);
    }

    /**
     * Get relative reference (url) as string
     */
    public function __toString() {
      return $this->getRelative();
    }

    /**
     * Prepare the object before changing it. This will load the url data from the request if
     * no other url was set before.
     */
    protected function prepare() {
      if (NULL === $this->_url) {
        /* @noinspection PhpParamsInspection */
        $this->load(
          $this->papaya()->request
        );
      }
    }

    /**
     * Get the reference string relative to the current request url
     *
     * @param URL|null $currentURL
     * @param bool $includeQueryString
     *
     * @return string
     */
    public function getRelative($currentURL = NULL, $includeQueryString = TRUE) {
      if (!$this->valid()) {
        return '';
      }
      $this->url()->setURLString($this->get());
      $transformer = new URL\Transformer\Relative();
      if (!$includeQueryString) {
        $this->url()->setQuery('');
      }
      $relative = $transformer->transform(
        NULL !== $currentURL ? $currentURL : new URL\Current(),
        $this->url()
      );
      return NULL === $relative ? $this->get() : $relative;
    }

    /**
     * Use an relative url string to change the reference
     *
     * @param string $relativeURL
     */
    public function setRelative($relativeURL) {
      $transformer = new URL\Transformer\Absolute();
      $absoluteURL = $transformer->transform($this->url(), $relativeURL);
      $this->url()->setURLString($absoluteURL);
      $this->getParameters()->setQueryString($this->url()->getQuery());
    }

    /**
     * Get reference string
     *
     * @param bool $forPublic URL is for public use (do not include the session id)
     *
     * @return string
     */
    public function get($forPublic = FALSE) {
      if (!$this->valid()) {
        return '';
      }
      return $this->cleanupPath(
          $this->url()
            ->getPathURL(), $forPublic
        ).$this->getQueryString($forPublic).$this->getFragment();
    }

    /**
     * @param $path
     * @param bool $forPublic URL is for public use (do not include the session id)
     *
     * @return string
     */
    protected function cleanupPath($path, $forPublic = FALSE) {
      $sessionParameterName = isset($this->papaya()->session) ? $this->papaya()->session->name : 'sid';
      if ($forPublic && '' !== $sessionParameterName) {
        return \preg_replace(
          '(/'.\preg_quote($sessionParameterName, '(').'[^/?#]+)',
          '',
          $path
        );
      }
      return $path;
    }

    /**
     * Set/Get attached url object or use the request to load one.
     *
     * @param URL $url
     *
     * @return URL
     */
    public function url(URL $url = NULL) {
      if (NULL !== $url) {
        $this->_url = $url;
      }
      $this->prepare();
      return $this->_url;
    }

    /**
     * load request data to reference
     *
     * @param Request $request
     *
     * @return self
     */
    public function load(Request $request) {
      $url = $request->getURL();
      $this->_url = clone(($url instanceof URL) ? $url : new URL());
      if (NULL === $this->_parameterGroupSeparator) {
        $this->setParameterGroupSeparator($request->getParameterGroupSeparator());
      }
      $this->setBasePath($request->getBasePath());
      return $this;
    }

    /**
     * Specify a custom parameter group separator
     *
     * @param string $separator
     * @return self
     * @throws \InvalidArgumentException
     *
     */
    public function setParameterGroupSeparator($separator) {
      if ('' === (string)$separator) {
        $this->_parameterGroupSeparator = Request\Parameters\GroupSeparator::ARRAY_SYNTAX;
      } elseif (Request\Parameters\GroupSeparator::validate($separator, TRUE)) {
        $this->_parameterGroupSeparator = $separator;
      } else {
        throw new \InvalidArgumentException(
          'Invalid parameter level separator: '.$separator
        );
      }
      return $this;
    }

    /**
     * Return the current group separator
     *
     * @return string
     */
    public function getParameterGroupSeparator() {
      if (NULL === $this->_parameterGroupSeparator) {
        $this->url();
      }
      return empty($this->_parameterGroupSeparator) ? GroupSeparator::ARRAY_SYNTAX : $this->_parameterGroupSeparator;
    }

    /**
     * Set several parameters at once
     *
     * @param array|Request\Parameters $parameters
     * @param string|null $parameterGroup
     *
     * @return self
     */
    public function setParameters($parameters, $parameterGroup = NULL) {
      if (NULL === $this->_parametersObject) {
        $this->_parametersObject = new Request\Parameters();
      }
      if (
        \is_array($parameters) ||
        $parameters instanceof Request\Parameters
      ) {
        if (NULL !== $parameterGroup && '' !== \trim($parameterGroup)) {
          $this->_parametersObject->merge(
            [
              $parameterGroup => $parameters instanceof Request\Parameters
                ? $parameters->toArray() : $parameters
            ]
          );
        } else {
          $this->_parametersObject->merge($parameters);
        }
      }
      return $this;
    }

    /**
     * Provides access to the parameters object of the reference
     *
     * @return Request\Parameters $parameters
     */
    public function getParameters() {
      if (NULL === $this->_parametersObject) {
        $this->_parametersObject = new Request\Parameters();
      }
      return $this->_parametersObject;
    }

    /**
     * Get reference query string prefixed by "?"
     *
     * @param bool $forPublic remove session id parameter for public urls
     *
     * @return string
     */
    public function getQueryString($forPublic = FALSE) {
      if (NULL !== $this->_parametersObject) {
        $sessionParameterName = isset($this->papaya()->session) ? $this->papaya()->session->name : 'sid';
        if ($forPublic && $this->_parametersObject->has($sessionParameterName)) {
          $parameters = clone $this->_parametersObject;
          unset($parameters[$sessionParameterName]);
        } else {
          $parameters = $this->_parametersObject;
        }
        $queryString = $parameters->getQueryString(
          $this->_parameterGroupSeparator
        );
        return empty($queryString) ? '' : '?'.$queryString;
      }
      return '';
    }

    /**
     * Set fragment
     *
     * @param string $fragment
     *
     * @return self
     */
    public function setFragment($fragment) {
      if (0 === \strpos($fragment, '#')) {
        $fragment = \substr($fragment, 1);
      }
      $this->url()->setFragment($fragment);
      return $this;
    }

    /**
     * Get reference fragment string prefixed by "#"
     *
     * @return string
     */
    public function getFragment() {
      $fragment = $this->url()->getFragment();
      return empty($fragment) ? '' : '#'.$fragment;
    }

    /**
     * Get Reference parameters as a plain/flat array (name => value)
     *
     * @return array
     */
    public function getParametersList() {
      if (NULL !== $this->_parametersObject) {
        return $this->_parametersObject->getList($this->_parameterGroupSeparator);
      }
      return [];
    }

    /**
     * Set web base path
     *
     * @param string $path
     *
     * @return self
     */
    public function setBasePath($path) {
      if (0 !== \strpos($path, '/')) {
        $path = '/'.$path;
      }
      if ('/' !== \substr($path, -1)) {
        $path .= '/';
      }
      $this->_basePath = $path;
      return $this;
    }

    /**
     * If subobjects were created, clone then, too.
     */
    public function __clone() {
      if (NULL !== $this->_url) {
        $this->_url = clone $this->_url;
      }
      if (NULL !== $this->_parametersObject) {
        $this->_parametersObject = clone $this->_parametersObject;
      }
    }
  }
}
