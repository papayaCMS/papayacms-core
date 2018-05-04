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
* Wrapper object for a DOMElement, defining a template value.
*
* @package Papaya-Library
* @subpackage Template
*
*/
class PapayaTemplateValue {

  /**
  * Wrapped DOM element
  *
  * @var PapayaXmlElement
  */
  private $_node = NULL;

  /**
  * Construct object from DOMNode
  *
  * @param PapayaXmlDocument|PapayaXmlElement $node
  */
  public function __construct($node) {
    $this->node($node);
  }

  /**
  * Get the document from the $node property
  *
  * @return DOMDocument
  */
  private function _getDocument() {
    if ($this->_node instanceof \PapayaXmlDocument) {
      return $this->_node;
    } else {
      return $this->_node->ownerDocument;
    }
  }

  /**
   * Get/Set node property
   *
   * @param PapayaXmlDocument|PapayaXmlElement $node
   * @throws InvalidArgumentException
   * @return \PapayaXmlElement|null
   */
  public function node($node = NULL) {
    if (isset($node)) {
      if ($node instanceof \PapayaXmlDocument ||
          $node instanceof \PapayaXmlElement) {
        $this->_node = $node;
      } else {
        throw new \InvalidArgumentException(
          sprintf(
            'PapayaXmlDocument or PapayaXmlElement expected, got %s',
            is_object($node) ? get_class($node) : gettype($node)
          )
        );
      }
    }
    return $this->_node;
  }

  /**
  * Append the node represented by this value to a parent node.
  *
  * @param PapayaXmlElement $parentNode
  * @return PapayaTemplateValue
  */
  public function appendTo(PapayaXmlElement $parentNode) {
    $parentNode->appendChild($this->_node);
    return $this;
  }

  /**
   * Append a new element to the value.
   *
   * If the first argument is a string a new element is created. If it is alread an DOMElement the
   * element is used directly.
   *
   * It sets all attributes defined by the second argument and the text content if not empty.
   * The element is append to the $_node property and a new instance of this object containing
   * the element is returned.
   *
   * @param string|DOMElement $element
   * @param array $attributes
   * @param string $textContent
   * @throws InvalidArgumentException
   * @return PapayaTemplateValue|NULL
   */
  public function append($element, array $attributes = array(), $textContent = '') {
    if (is_string($element)) {
      $element = $this->_getDocument()->createElement($element);
    } elseif ($element instanceof \PapayaXmlAppendable) {
      $element->appendTo($this->_node);
      return NULL;
    } elseif ($element instanceof \DOMDocument) {
      if (isset($element->documentElement)) {
        $element = $element->documentElement;
      } else {
        throw new \InvalidArgumentException(
          'Argument 1 is an empty dom document, nothing to append.'
        );
      }
    }
    if (!$element instanceof \DOMElement) {
      throw new \InvalidArgumentException(
        sprintf(
          'Argument 1 passed to %s must be a string or DOMElement, %s given.',
          __CLASS__.'::'.__METHOD__.'()',
          is_object($element) ? get_class($element) : gettype($element)
        )
      );
    }
    foreach ($attributes as $name => $value) {
      $element->setAttribute($name, $value);
    }
    if (!empty($textContent)) {
      $element->nodeValue = (string)$textContent;
    }
    $this->_node->appendChild($this->_getDocument()->importNode($element, TRUE));
    $class = get_class($this);
    return new $class($element);
  }

  /**
  * Appends a xml fragement to the node and returns the new element.
  *
  * This function creates an xml fragment and appends the first element in it.
  *
  * An instance of this class containing the appended element is returned.
  *
  * @param string $xml
  * @return PapayaTemplateValue
  */
  public function appendXml($xml) {
    $errors = new \PapayaXmlErrors();
    $errors->activate();
    $this->node()->appendXml($xml);
    $errors->emit();
    $errors->deactivate();
    return $this;
  }

  /**
   * Get and/or Set the content of a given template element
   *
   * If a content argument is provided the function remove all existing content in the element
   * and replaces it with the given content.
   *
   * If it is an string is will be threated as an xml fragment but you can provide a single DOMNode
   * or a list of DOMNodes as well.
   *
   * @param DOMNode|array|string $xml
   * @throws InvalidArgumentException
   * @return string
   */
  public function xml($xml = NULL) {
    if (isset($xml)) {
      for ($i = $this->_node->childNodes->length - 1; $i >= 0; $i--) {
        $this->_node->removeChild($this->_node->childNodes->item($i));
      }
      if (is_string($xml)) {
        $this->appendXml($xml);
      } elseif ($xml instanceof \DOMNode) {
        $this->_node->appendChild($xml);
      } elseif (is_array($xml)) {
        foreach ($xml as $index => $node) {
          if ($node instanceof \DOMNode) {
            $this->_node->appendChild($node);
          } else {
            throw new \InvalidArgumentException(
              sprintf(
                'Argument 1 passed to %s must be an array of DOMNodes, %s given at index "%s".',
                __CLASS__.'::'.__METHOD__.'()',
                is_object($node) ? get_class($node) : gettype($node),
                $index
              )
            );
          }
        }
      } else {
        throw new \InvalidArgumentException(
          sprintf(
            'Argument 1 passed to %s must be a string, array or DOMNode, %s given.',
            __CLASS__.'::'.__METHOD__.'()',
            is_object($xml) ? get_class($xml) : gettype($xml)
          )
        );
      }
    }
    $result = '';
    foreach ($this->_node->childNodes as $node) {
      $result .= $this->_getDocument()->saveXml($node);
    }
    return $result;
  }
}
