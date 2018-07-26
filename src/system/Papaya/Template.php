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
 * Papaya Template, abstract superclass for \Papaya Template objects.
 *
 * It contains all the handling for data (in a DOM document) and parameters. It defines
 * a method parse() for the actual processing.
 *
 * @package Papaya-Library
 * @subpackage Template
 *
 * @method bool addNavigation($xml, $encodeInvalidEntities = TRUE)
 * @method bool addInformation($xml, $encodeInvalidEntities = TRUE)
 * @method bool addContent($xml, $encodeInvalidEntities = TRUE)
 * @method bool addMenu($xml, $encodeInvalidEntities = TRUE)
 * @method bool addScript($xml, $encodeInvalidEntities = TRUE)
 */
abstract class Template extends Application\BaseObject {

  /**
   * Strip the XML processing instruction <?xml ...?>
   */
  const STRIP_XML_PI = 1;
  /**
   * Strip empty XML namespaces xmlns:*=""
   */
  const STRIP_XML_EMPTY_NAMESPACE = 2;

  /**
   * Strip default XML namespaces xmlns="*"
   */
  const STRIP_XML_DEFAULT_NAMESPACE = 4;


  const STRIP_ALL = 7;

  /**
   * @var \PapayaTemplateValues
   */
  private $_values = NULL;

  /**
   * @var \PapayaTemplateParameters
   */
  private $_parameters = NULL;

  /**
   * @var \PapayaXmlErrors
   */
  private $_errors = NULL;

  /**
   * Map method names to value paths
   *
   * @var array
   */
  private $_addMethods = array(
    'navigation' => 'leftcol',
    'information' => 'rightcol',
    'content' => 'centercol',
    'menu' => 'menus',
    'script' => 'scripts'
  );

  abstract function parse($options = self::STRIP_XML_EMPTY_NAMESPACE);

  /**
   * Combined getter/setter for the template values object
   *
   * @param \PapayaTemplateValues $values
   * @return \PapayaTemplateValues
   */
  public function values(\PapayaTemplateValues $values = NULL) {
    if (isset($values)) {
      $this->_values = $values;
    } elseif (is_null($this->_values)) {
      $this->_values = new \PapayaTemplateValues();
    }
    return $this->_values;
  }

  /**
   * Set template values from xml string
   *
   * @param string $xml
   * @return boolean
   */
  public function setXml($xml) {
    return $this->errors()->encapsulate(
      array($this->values()->document(), 'loadXml'),
      array($xml)
    );
  }

  /**
   * Get XML values as string
   *
   * @access public
   * @return string
   */
  function getXml() {
    return $this->values()->document()->saveXml();
  }

  /**
   * @param array|\Traversable $parameters
   * @return \PapayaTemplateParameters
   */
  public function parameters($parameters = NULL) {
    if (isset($parameters)) {
      if ($parameters instanceof \PapayaTemplateParameters) {
        $this->_parameters = $parameters;
      } else {
        $this->_parameters = new \PapayaTemplateParameters($parameters);
      }
    } elseif (NULL === $this->_parameters) {
      $this->_parameters = new \PapayaTemplateParameters();
    }
    return $this->_parameters;
  }

  /**
   * Combined getter/setter for the libxml errors
   *
   * @param \PapayaXmlErrors $errors
   * @return \PapayaXmlErrors
   */
  public function errors(\PapayaXmlErrors $errors = NULL) {
    if (isset($errors)) {
      $this->_errors = $errors;
    } elseif (is_null($this->_errors)) {
      $this->_errors = new \PapayaXmlErrors();
      $this->_errors->papaya($this->papaya());
    }
    return $this->_errors;
  }

  /**
   * Clean the result from the template processing.
   *
   * @param string|FALSE $xml
   * @param int $options
   * @return bool|mixed
   */
  protected function clean($xml, $options) {
    if (FALSE !== $xml) {
      $replace = array(
        '(<([\w:-]+)\s\s*>)s'
      );
      $with = array('<$1>', '');
      if (\PapayaUtilBitwise::inBitmask(self::STRIP_XML_PI, $options)) {
        $replace[] = '(<\?xml[^>]+\?>)';
        $with [] = '';
      }
      if (\PapayaUtilBitwise::inBitmask(self::STRIP_XML_EMPTY_NAMESPACE, $options)) {
        $replace[] = '(\s*xmlns(:[a-zA-Z]+)?="\s*")';
        $with [] = '';
      }
      if (\PapayaUtilBitwise::inBitmask(self::STRIP_XML_DEFAULT_NAMESPACE, $options)) {
        $replace[] = '(\s*xmlns="[^"]*")';
        $with [] = '';
      }
      return preg_replace($replace, $with, $xml);
    }
    return FALSE;
  }


  /**
   * Add content to the Xml document. The content will be added to the 'page' root
   * element. If you do not provide an path 'centercol' will be used. A path
   * can be a simple element name or a sequence of element names separated by '/'.
   * If an element in the path does not exists, it will be created.
   *
   * @param string|\PapayaXmlAppendable|\DOMNode $xml data
   * @param string $path optional, default value 'centercol' the element path relative to '/page'
   * @param boolean $encodeInvalidEntities encode invalid entities like &
   * @return mixed
   */
  public function add($xml, $path = NULL, $encodeInvalidEntities = TRUE) {
    if (!isset($path)) {
      $path = '/page/centercol';
    } else {
      $path = '/page/'.$path;
    }
    if ($xml instanceof \PapayaXmlAppendable || $xml instanceof \DOMNode) {
      return $this->errors()->encapsulate(
        array(
          $this->values()->getValueByPath($path),
          'append'
        ),
        array($xml)
      );
    } else {
      return $this->errors()->encapsulate(
        array(
          $this->values()->getValueByPath($path),
          'appendXml'
        ),
        array(
          $encodeInvalidEntities ? $this->encodeInvalidEntities($xml) : $xml
        )
      );
    }
  }

  /**
   * Try to repair and XML input if it contais invlaid utf-8 characters,
   * named entities or '&'.
   *
   * @param string $xml
   * @return string
   */
  private function encodeInvalidEntities($xml) {
    $result = \PapayaUtilStringUtf8::ensure($xml);
    $result = \PapayaUtilStringHtml::decodeNamedEntities($result);
    $result = str_replace('&', '&amp;', $result);
    $result = preg_replace(
      '(&amp;(gt|lt|quot|apos|amp|#\d{1,6}|#x[a-fA-F\d]{1,4});)',
      '&$1;',
      $result
    );
    return $result;
  }

  /**
   * Capture add* methods, and call add() with the defined target.
   *
   * @param string $method
   * @param array $arguments
   * @return mixed
   * @throws \LogicException
   */
  public function __call($method, $arguments) {
    if (0 === strpos($method, 'add')) {
      $target = strtolower(substr($method, 3));
      if (!isset($this->_addMethods[$target])) {
        throw new \LogicException(
          sprintf(
            'Invalid add method %s::%s(), can not find target.',
            get_class($this),
            $method
          )
        );
      } elseif (!isset($arguments[0])) {
        throw new \LogicException(
          sprintf(
            'Invalid $xml argument for add method "%s:%s()".',
            get_class($this),
            $method
          )
        );
      } else {
        return call_user_func(
          array($this, 'add'),
          $arguments[0],
          $this->_addMethods[$target],
          isset($arguments[1]) ? (bool)$arguments[1] : TRUE
        );
      }
    }
    throw new \LogicException(
      sprintf(
        'Can not call nonexisting method "%s:%s()"',
        get_class($this),
        $method
      )
    );
  }

  /**
   * Transform XML with XSL to HTML
   *
   * @param int $options
   * @return string
   */
  public function getOutput($options = self::STRIP_XML_EMPTY_NAMESPACE) {
    $debugXml = $this->papaya()->request->getParameter(
      'XML', FALSE, NULL, \Papaya\Request::SOURCE_QUERY
    );
    if ($debugXml && $this->papaya()->administrationUser->isLoggedIn()) {
      /**
       * @var \Papaya\Response $response
       */
      $response = $this->papaya()->response;
      $response->setContentType('text/xml', 'utf-8');
      $response->content(new \PapayaResponseContentString($this->getXml()));
      $response->send(TRUE);
    } elseif ($result = $this->parse($options)) {
      return $result;
    }
    return FALSE;
  }

  /****************************
   * Backwards compatiblity
   ***************************/

  /**
   * Alias for add()
   *
   * @param string|\DOMElement $xml data
   * @param string $path optional, default value 'centercol' the element path relative to '/page'
   * @param boolean $encode encode special characters ? optional, default value TRUE
   * @return mixed
   */
  public function addData($xml, $path = NULL, $encode = TRUE) {
    $this->add($xml, $path, $encode);
  }

  /**
   * Alias for addNavigation()
   *
   * @param string|\DOMElement $xml data
   * @param boolean $encode encode special characters ? optional, default value TRUE
   * @return mixed
   */
  public function addLeft($xml, $encode = TRUE) {
    $this->addNavigation($xml, $encode);
  }

  /**
   * Alias for addContent()
   *
   * @param string|\DOMElement $xml data
   * @param boolean $encode encode special characters ? optional, default value TRUE
   * @return mixed
   */
  public function addCenter($xml, $encode = TRUE) {
    $this->addContent($xml, $encode);
  }

  /**
   * Alias for addInformation()
   *
   * @param string|\DOMElement $xml data
   * @param boolean $encode encode special characters ? optional, default value TRUE
   * @return mixed
   */
  public function addRight($xml, $encode = TRUE) {
    $this->addInformation($xml, $encode);
  }

  /**
   * @deprecated
   * @param string $name
   * @param string $value
   */
  public function setParam($name, $value) {
    $this->parameters()->$name = $value;
  }

  /**
   * Alias for getOutput()
   *
   * @deprecated
   * @param int $options
   * @return FALSE|string
   */
  public function xhtml($options = 0) {
    return $this->getOutput($options);
  }

  /**
   * Alias for getXml()
   *
   * @deprecated
   * @return string
   */
  public function xml() {
    return $this->getXml();
  }
}
