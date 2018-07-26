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
* Papaya Interface Reference (Hyperlink Reference)
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiReference extends \Papaya\Application\BaseObject {

  /**
  * Url group separator
  * @var string
  */
  private $_parameterGroupSeparator = NULL;

  /**
  * parameters list
  * @var \PapayaRequestParameters
  */
  private $_parametersObject = NULL;

  /**
  * Internal url object
  *
  * @var \Papaya\Url
  */
  private $_url = NULL;

  /**
  * Base web path
  * @var string
  */
  protected $_basePath = '/';

  /**
  * Reference status
  * @var string
  */
  private $_valid = TRUE;

  /**
  * create object and load url if provided.
  *
  * @param \Papaya\Url $url
  */
  public function __construct(\Papaya\Url $url = NULL) {
    if (isset($url)) {
      $this->url($url);
    }
  }

  /**
  * Other object can mark an reference as valid or invalid after testing it. An invalid reference
  * will return an empty string as url (get() and getRelative()).
  *
  * @param boolean $isValid
  * @return boolean
  */
  public function valid($isValid = NULL) {
    if (isset($isValid)) {
      $this->_valid = $isValid;
    }
    return $this->_valid;
  }

  /**
  * Static create function to allow fluent calls.
  *
  * @param \Papaya\Url $url
  * @return \PapayaUiReference
  */
  public static function create(\Papaya\Url $url = NULL) {
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
    if (!isset($this->_url)) {
      /** @noinspection PhpParamsInspection */
      $this->load(
        $this->papaya()->request
      );
    }
  }

  /**
   * Get the reference string relative to the current request url
   *
   * @param \Papaya\Url|NULL $currentUrl
   * @param bool $includeQueryString
   * @return string
   */
  public function getRelative($currentUrl = NULL, $includeQueryString = TRUE) {
    if (!$this->valid()) {
      return '';
    }
    $this->url()->setUrl($this->get());
    $transformer = new \PapayaUrlTransformerRelative();
    if (!$includeQueryString) {
      $this->url()->setQuery('');
    }
    $relative = $transformer->transform(
      isset($currentUrl) ? $currentUrl : new \PapayaUrlCurrent(),
      $this->url()
    );
    return is_null($relative) ? $this->get() : $relative;
  }

  /**
  * Use an relative url string to change the reference
  *
  * @param string $relativeUrl
  */
  public function setRelative($relativeUrl) {
    $transformer = new \PapayaUrlTransformerAbsolute();
    $absoluteUrl = $transformer->transform($this->url(), $relativeUrl);
    $this->url()->setUrl($absoluteUrl);
    $this->getParameters()->setQueryString($this->url()->getQuery());
  }

  /**
   * Get reference string
   * @param bool $forPublic URL is for public use (do not include the session id)
   * @return string
   */
  public function get($forPublic = FALSE) {
    if (!$this->valid()) {
      return '';
    }
    return $this->cleanupPath($this->url()->getPathUrl(), $forPublic).$this->getQueryString($forPublic).$this->getFragment();
  }

  /**
   * @param $path
   * @param bool $forPublic URL is for public use (do not include the session id)
   * @return string
   */
  protected function cleanupPath($path, $forPublic = FALSE) {
    $sessionParameterName = isset($this->papaya()->session) ? $this->papaya()->session->name : 'sid';
    if ($forPublic && $sessionParameterName !== '') {
      return preg_replace('(/'.preg_quote($sessionParameterName, '(').'[^/?#]+)', '', $path);
    }
    return $path;
  }

  /**
   * Set/Get attached url object or use the request to load one.
   *
   * @param \Papaya\Url $url
   * @return \Papaya\Url
   */
  public function url(\Papaya\Url $url = NULL) {
    if (isset($url)) {
      $this->_url = $url;
    }
    $this->prepare();
    return $this->_url;
  }

  /**
  * load request data to reference
  *
  * @param \Papaya\Request $request
  * @return \PapayaUiReference
  */
  public function load(\Papaya\Request $request) {
    $url = $request->getUrl();
    $this->_url = clone (($url instanceof \Papaya\Url) ? $url : new \Papaya\Url);
    if (is_null($this->_parameterGroupSeparator)) {
      $this->setParameterGroupSeparator($request->getParameterGroupSeparator());
    }
    $this->setBasePath($request->getBasePath());
    return $this;
  }

  /**
   * Specifiy a custom parameter group separator
   * @param string $separator Allowed values: '[]', ',', ':', '/', '*', '!'
   * @throws \InvalidArgumentException
   * @return \PapayaUiReference
   */
  public function setParameterGroupSeparator($separator) {
    if ($separator == '') {
      $this->_parameterGroupSeparator = '[]';
    } elseif (in_array($separator, array('[]', ',', ':', '/', '*', '!'))) {
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
    if (is_null($this->_parameterGroupSeparator)) {
      $this->url();
    }
    return empty($this->_parameterGroupSeparator) ? '[]' : $this->_parameterGroupSeparator;
  }

  /**
  * Set several parameters at once
  * @param array|\PapayaRequestParameters $parameters
  * @param string|NULL $parameterGroup
  * @return \PapayaUiReference
  */
  public function setParameters($parameters, $parameterGroup = NULL) {
    if (NULL === $this->_parametersObject) {
      $this->_parametersObject = new \PapayaRequestParameters();
    }
    if (
      is_array($parameters) ||
      $parameters instanceof \PapayaRequestParameters
    ) {
      if (NULL !== $parameterGroup && '' !== trim($parameterGroup)) {
        $this->_parametersObject->merge(
          array(
            $parameterGroup => $parameters instanceof \PapayaRequestParameters
              ? $parameters->toArray() : $parameters
          )
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
  * @return \PapayaRequestParameters $parameters
  */
  public function getParameters() {
    if (!isset($this->_parametersObject)) {
      $this->_parametersObject = new \PapayaRequestParameters();
    }
    return $this->_parametersObject;
  }

  /**
   * Get reference query string prefixed by "?"
   * @param bool $forPublic remove session id parameter for public urls
   * @return string
   */
  public function getQueryString($forPublic = FALSE) {
    if (isset($this->_parametersObject)) {
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
  * @return \PapayaUiReferencePage
  */
  public function setFragment($fragment) {
    if (substr($fragment, 0, 1) == '#') {
      $fragment = substr($fragment, 1);
    }
    $this->url()->setFragment($fragment);
    return $this;
  }

  /**
  * Get reference fragment string prefixed by "#"
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
    if (isset($this->_parametersObject)) {
      return $this->_parametersObject->getList($this->_parameterGroupSeparator);
    }
    return array();
  }

  /**
  * Set web base path
  *
  * @param string $path
  * @access public
  * @return \PapayaUiReference
  */
  public function setBasePath($path) {
    if (substr($path, 0, 1) != '/') {
      $path = '/'.$path;
    }
    if (substr($path, -1) != '/') {
      $path .= '/';
    }
    $this->_basePath = $path;
    return $this;
  }

  /**
  * If subobjects were created, clone then, too.
  */
  public function __clone() {
    if (isset($this->_url)) {
      $this->_url = clone $this->_url;
    }
    if (isset($this->_parametersObject)) {
      $this->_parametersObject = clone $this->_parametersObject;
    }
  }
}
