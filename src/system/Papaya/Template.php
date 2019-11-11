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

  use Papaya\Template\Parameters as TemplateParameters;
  use Papaya\Template\Values as TemplateValues;
  use Papaya\XML\Appendable as XMLAppendable;
  use Papaya\XML\Element;
  use Papaya\XML\Errors as XMLErrors;

  /**
   * Papaya Template, abstract superclass for \Papaya Template objects.
   *
   * It contains all the handling for data (in a DOM document) and parameters. It defines
   * a method parse() for the actual processing.
   *
   * @package Papaya-Library
   * @subpackage Template
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

    const PATH_NAVIGATION = 'leftcol';
    const PATH_INFORMATION = 'rightcol';
    const PATH_CONTENT = 'centercol';
    const PATH_MENUS = 'menus';
    const PATH_SCRIPTS = 'scripts';

    /**
     * @var TemplateValues
     */
    private $_values;

    /**
     * @var TemplateParameters
     */
    private $_parameters;

    /**
     * @var XMLErrors
     */
    private $_errors;

    abstract public function parse($options = self::STRIP_XML_EMPTY_NAMESPACE);

    /**
     * Combined getter/setter for the template values object
     *
     * @param TemplateValues $values
     *
     * @return TemplateValues
     */
    public function values(TemplateValues $values = NULL) {
      if (NULL !== $values) {
        $this->_values = $values;
      } elseif (NULL === $this->_values) {
        $this->_values = new TemplateValues();
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
      if (!($this->values()->document()->documentElement instanceof Element)) {
        $this->values()->append(NULL, 'page');
      }
      return $this->values()->document()->saveXML();
    }

    /**
     * @param array|\Traversable $parameters
     *
     * @return TemplateParameters
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
     * @param XMLErrors $errors
     *
     * @return XMLErrors
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
        $replace = ['(<(/)?([\w:\-]+)\s\s*(\s/)?>)s'];
        $with = ['<$1$2$3>'];
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
     * element. If you do not provide an path self::PATH_CONTENT will be used. A path
     * can be a simple element name or a sequence of element names separated by '/'.
     * If an element in the path does not exists, it will be created.
     *
     * @param string|XMLAppendable|\DOMNode $xml data
     * @param string $path optional, default value self::PATH_CONTENT the element path relative to '/page'
     * @param bool $encodeInvalidEntities encode invalid entities like &
     *
     * @return mixed
     */
    public function add($xml, $path = NULL, $encodeInvalidEntities = TRUE) {
      $path = '/page/'.(NULL === $path ? self::PATH_CONTENT : $path);
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
     * @param string|XMLAppendable|\DOMNode $xml
     * @param bool $encodeInvalidEntities
     * @return mixed
     */
    public function addNavigation($xml, $encodeInvalidEntities = TRUE) {
      return $this->add($xml, self::PATH_NAVIGATION, $encodeInvalidEntities);
    }

    /**
     * @param string|XMLAppendable|\DOMNode $xml
     * @param bool $encodeInvalidEntities
     * @return mixed
     */
    public function addInformation($xml, $encodeInvalidEntities = TRUE) {
      return $this->add($xml, self::PATH_INFORMATION, $encodeInvalidEntities);
    }

    /**
     * @param string|XMLAppendable|\DOMNode $xml
     * @param bool $encodeInvalidEntities
     * @return mixed
     */
    public function addContent($xml, $encodeInvalidEntities = TRUE) {
      return $this->add($xml, self::PATH_CONTENT, $encodeInvalidEntities);
    }

    /**
     * @param string|XMLAppendable|\DOMNode $xml
     * @param bool $encodeInvalidEntities
     * @return mixed
     */
    public function addMenu($xml, $encodeInvalidEntities = TRUE) {
      return $this->add($xml, self::PATH_MENUS, $encodeInvalidEntities);
    }

    /**
     * @param string|XMLAppendable|\DOMNode $xml
     * @param bool $encodeInvalidEntities
     * @return mixed
     */
    public function addScript($xml, $encodeInvalidEntities = TRUE) {
      return $this->add($xml, self::PATH_SCRIPTS, $encodeInvalidEntities);
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
     * Generate Output. If here is a parameter XML=1 in the current
     * query string and a administration user logging in send the
     * XML as a response.
     *
     * @param int $options
     * @return string
     */
    public function getOutput($options = self::STRIP_XML_EMPTY_NAMESPACE) {
      $debugXML = $this->papaya()->request->getParameter(
        'XML', FALSE, NULL, Request::SOURCE_QUERY
      );
      if ($debugXML && $this->papaya()->administrationUser->isLoggedIn()) {
        /**
         * @var Response $response
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
     * @param string $name
     * @param string $value
     * @deprecated
     *
     */
    public function setParam($name, $value) {
      $this->parameters()->$name = $value;
    }

    /**
     * Alias for getOutput()
     *
     * @param int $options
     * @return false|string
     * @deprecated
     */
    public function xhtml($options = 0) {
      return $this->getOutput($options);
    }

    /**
     * Alias for getXML()
     *
     * @return string
     * @deprecated
     */
    public function xml() {
      return $this->getXML();
    }
  }
}
