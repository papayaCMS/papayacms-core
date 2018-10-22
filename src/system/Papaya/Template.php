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
abstract class Template implements Application\Access {
  use Application\Access\Aggregation;

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
   * @var \Papaya\Template\Values
   */
  private $_values;

  /**
   * @var \Papaya\Template\Parameters
   */
  private $_parameters;

  /**
   * @var \Papaya\XML\Errors
   */
  private $_errors;

  /**
   * Map method names to value paths
   *
   * @var array
   */
  private $_addMethods = [
    'navigation' => 'leftcol',
    'information' => 'rightcol',
    'content' => 'centercol',
    'menu' => 'menus',
    'script' => 'scripts'
  ];

  abstract public function parse($options = self::STRIP_XML_EMPTY_NAMESPACE);

  /**
   * Combined getter/setter for the template values object
   *
   * @param \Papaya\Template\Values $values
   *
   * @return \Papaya\Template\Values
   */
  public function values(Template\Values $values = NULL) {
    if (NULL !== $values) {
      $this->_values = $values;
    } elseif (NULL === $this->_values) {
      $this->_values = new Template\Values();
    }
    return $this->_values;
  }

  /**
   * Set template values from xml string
   *
   * @param string $xml
   *
   * @return bool
   */
  public function setXML($xml) {
    return $this->errors()->encapsulate(
      [$this->values()->document(), 'loadXML'],
      [$xml]
    );
  }

  /**
   * Get XML values as string
   *
   * @return string
   */
  public function getXML() {
    return $this->values()->document()->saveXML();
  }

  /**
   * @param array|\Traversable $parameters
   *
   * @return \Papaya\Template\Parameters
   */
  public function parameters($parameters = NULL) {
    if (NULL !== $parameters) {
      if ($parameters instanceof Template\Parameters) {
        $this->_parameters = $parameters;
      } else {
        $this->_parameters = new Template\Parameters($parameters);
      }
    } elseif (NULL === $this->_parameters) {
      $this->_parameters = new Template\Parameters();
    }
    return $this->_parameters;
  }

  /**
   * Combined getter/setter for the libxml errors
   *
   * @param \Papaya\XML\Errors $errors
   *
   * @return \Papaya\XML\Errors
   */
  public function errors(XML\Errors $errors = NULL) {
    if (NULL !== $errors) {
      $this->_errors = $errors;
    } elseif (NULL === $this->_errors) {
      $this->_errors = new XML\Errors();
      $this->_errors->papaya($this->papaya());
    }
    return $this->_errors;
  }

  /**
   * Clean the result from the template processing.
   *
   * @param string|false $xml
   * @param int $options
   *
   * @return bool|mixed
   */
  protected function clean($xml, $options) {
    if (FALSE !== $xml) {
      $replace = [
        '(<([\w:-]+)\s\s*>)s'
      ];
      $with = ['<$1>', ''];
      if (Utility\Bitwise::inBitmask(self::STRIP_XML_PI, $options)) {
        $replace[] = '(<\?xml[^>]+\?>)';
        $with[] = '';
      }
      if (Utility\Bitwise::inBitmask(self::STRIP_XML_EMPTY_NAMESPACE, $options)) {
        $replace[] = '(\s*xmlns(:[a-zA-Z]+)?="\s*")';
        $with[] = '';
      }
      if (Utility\Bitwise::inBitmask(self::STRIP_XML_DEFAULT_NAMESPACE, $options)) {
        $replace[] = '(\s*xmlns="[^"]*")';
        $with[] = '';
      }
      return \preg_replace($replace, $with, $xml);
    }
    return FALSE;
  }

  /**
   * Add content to the XML document. The content will be added to the 'page' root
   * element. If you do not provide an path 'centercol' will be used. A path
   * can be a simple element name or a sequence of element names separated by '/'.
   * If an element in the path does not exists, it will be created.
   *
   * @param string|\Papaya\XML\Appendable|\DOMNode $xml data
   * @param string $path optional, default value 'centercol' the element path relative to '/page'
   * @param bool $encodeInvalidEntities encode invalid entities like &
   *
   * @return mixed
   */
  public function add($xml, $path = NULL, $encodeInvalidEntities = TRUE) {
    if (NULL === $path) {
      $path = '/page/centercol';
    } else {
      $path = '/page/'.$path;
    }
    if ($xml instanceof XML\Appendable || $xml instanceof \DOMNode) {
      return $this->errors()->encapsulate(
        [
          $this->values()->getValueByPath($path),
          'append'
        ],
        [$xml]
      );
    }
    return $this->errors()->encapsulate(
      [
        $this->values()->getValueByPath($path),
        'appendXML'
      ],
      [
        $encodeInvalidEntities ? $this->encodeInvalidEntities($xml) : $xml
      ]
    );
  }

  /**
   * Try to repair and XML input if it contains invalid utf-8 characters,
   * named entities or '&'.
   *
   * @param string $xml
   *
   * @return string
   */
  private function encodeInvalidEntities($xml) {
    $result = Utility\Text\UTF8::ensure($xml);
    $result = Utility\Text\HTML::decodeNamedEntities($result);
    $result = \str_replace('&', '&amp;', $result);
    $result = \preg_replace(
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
   *
   * @return mixed
   *
   * @throws \LogicException
   */
  public function __call($method, $arguments) {
    if (0 === \strpos($method, 'add')) {
      $target = \strtolower(\substr($method, 3));
      if (!isset($this->_addMethods[$target])) {
        throw new \LogicException(
          \sprintf(
            'Invalid add method %s::%s(), can not find target.',
            \get_class($this),
            $method
          )
        );
      }
      if (!isset($arguments[0])) {
        throw new \LogicException(
          \sprintf(
            'Invalid $xml argument for add method "%s:%s()".',
            \get_class($this),
            $method
          )
        );
      }
      return $this->add(
        $arguments[0],
        $this->_addMethods[$target],
        isset($arguments[1]) ? (bool)$arguments[1] : TRUE
      );
    }
    throw new \LogicException(
      \sprintf(
        'Can not call non-existing method "%s:%s()"',
        \get_class($this),
        $method
      )
    );
  }

  /**
   * Transform XML with XSL to HTML
   *
   * @param int $options
   *
   * @return string
   */
  public function getOutput($options = self::STRIP_XML_EMPTY_NAMESPACE) {
    $debugXML = $this->papaya()->request->getParameter(
      'XML', FALSE, NULL, Request::SOURCE_QUERY
    );
    if ($debugXML && $this->papaya()->administrationUser->isLoggedIn()) {
      /**
       * @var \Papaya\Response $response
       */
      $response = $this->papaya()->response;
      $response->setContentType('text/xml', 'utf-8');
      $response->content(new Response\Content\Text($this->getXML()));
      $response->send(TRUE);
    } elseif ($result = $this->parse($options)) {
      return $result;
    }
    return FALSE;
  }

  /****************************
   * Backwards compatibility
   ***************************/

  /**
   * Alias for add()
   *
   * @param string|\DOMElement $xml data
   * @param string $path optional, default value 'centercol' the element path relative to '/page'
   * @param bool $encode encode special characters ? optional, default value TRUE
   */
  public function addData($xml, $path = NULL, $encode = TRUE) {
    $this->add($xml, $path, $encode);
  }

  /**
   * Alias for addNavigation()
   *
   * @param string|\DOMElement $xml data
   * @param bool $encode encode special characters ? optional, default value TRUE
   */
  public function addLeft($xml, $encode = TRUE) {
    $this->addNavigation($xml, $encode);
  }

  /**
   * Alias for addContent()
   *
   * @param string|\DOMElement $xml data
   * @param bool $encode encode special characters ? optional, default value TRUE
   */
  public function addCenter($xml, $encode = TRUE) {
    $this->addContent($xml, $encode);
  }

  /**
   * Alias for addInformation()
   *
   * @param string|\DOMElement $xml data
   * @param bool $encode encode special characters ? optional, default value TRUE
   */
  public function addRight($xml, $encode = TRUE) {
    $this->addInformation($xml, $encode);
  }

  /**
   * @deprecated
   *
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
   *
   * @param int $options
   *
   * @return false|string
   */
  public function xhtml($options = 0) {
    return $this->getOutput($options);
  }

  /**
   * Alias for getXML()
   *
   * @deprecated
   *
   * @return string
   */
  public function xml() {
    return $this->getXML();
  }
}
