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
* Replacement for the DOMDocument adding some shortcuts for easier use
*
* @package Papaya-Library
* @subpackage Xml
*
* @property \PapayaXmlElement $documentElement
*/
class PapayaXmlDocument
  extends \DOMDocument
  implements \PapayaXmlNodeInterface {

  /**
   * @var \PapayaXmlXpath
   */
  private $_xpath = NULL;

  /**
   * @var array
   */
  private $_namespaces = array();

  /**
   * Namespace prefixes starting with the letters 'xml' are reserved by the w3c and
   * used for defined namespaces
   *
   * @var array
   */
  private $_reservedNamespaces = array(
    'xml' => 'http://www.w3.org/XML/1998/namespace',
    'xmlns' => 'http://www.w3.org/2000/xmlns/'
  );

  /**
   * @var bool
   */
  private $_activateEntityLoader = FALSE;

  /**
   * @var bool
   */
  private $_canDisableEntityLoader = TRUE;

  /**
   * Initialize document object and register own nodeclass(es)
   *
   * @param string $version
   * @param string $encoding
   * @return \PapayaXmlDocument
   */
  public function __construct($version = '1.0', $encoding = 'UTF-8') {
    parent::__construct($version, $encoding);
    $this->registerNodeClass(\DOMElement::class, \PapayaXmlElement::class);
    $this->_canDisableEntityLoader = function_exists('libxml_disable_entity_loader');
  }

  /**
   * Get an Xpath object for the current document instance, refresh it if the internal document
   * id changes (document loading), register namespaces on the xpath object.
   *
   * @return \DOMXpath
   */
  public function xpath() {
    if (is_null($this->_xpath) || $this->_xpath->document != $this) {
      $this->_xpath = new \PapayaXmlXpath($this);
      foreach ($this->_namespaces as $prefix => $namespace) {
        $this->_xpath->registerNamespace($prefix, $namespace);
      }
    }
    return $this->_xpath;
  }

  /**
   * Register Namespaces for the document and an attaches Xpath instance.
   *
   * @param array $namespaces
   * @param bool $registerOnXpath
   */
  public function registerNamespaces(array $namespaces, $registerOnXpath = TRUE) {
    $registerOnXpath = $registerOnXpath && isset($this->_xpath);
    foreach ($namespaces as $prefix => $namespace) {
      $this->registerNamespace($prefix, $namespace, $registerOnXpath);
    }
  }

  /**
   * Register a single namespace for the document and an attached Xpath instance.
   *
   * @param string $prefix
   * @param string $namespace
   * @param bool $registerOnXpath
   * @throws \InvalidArgumentException
   */
  public function registerNamespace($prefix, $namespace, $registerOnXpath = TRUE) {
    if (
      isset($this->_reservedNamespaces[$prefix]) &&
      !$this->_reservedNamespaces[$prefix] == $namespace) {
      throw new \InvalidArgumentException(
        'Xml prefix "%s" is reserved for the namespace "%s".'
      );
    }
    $this->_namespaces[$prefix] = $namespace;
    if ($registerOnXpath && isset($this->_xpath)) {
      $this->_xpath->registerNamespace($prefix, $namespace);
    }
  }

  /**
   * Get the namespace for an prefix. If the $prefix contains a ':' only the part before that
   * character will be used.
   *
   * @param string $prefix
   * @throws \UnexpectedValueException
   * @return string
   */
  public function getNamespace($prefix) {
    if (FALSE !== ($position = strpos($prefix, ':'))) {
      $prefix = substr($prefix, 0, $position);
    }
    if (isset($this->_reservedNamespaces[$prefix])) {
      return $this->_reservedNamespaces[$prefix];
    }
    if (isset($this->_namespaces[$prefix])) {
      return $this->_namespaces[$prefix];
    }
    throw new \UnexpectedValueException('Unknown namespace prefix: '.$prefix);
  }

  /**
  * Append an xml element with attributes and content
  *
  * @param string $name
  * @param array $attributes
  * @param string $content
  * @return \PapayaXmlElement new element
  */
  public function appendElement($name, array $attributes = array(), $content = NULL) {
    return $this->appendChild($this->createElement($name, $content, $attributes));
  }

  /**
  * Append a xml fragment into document.
  *
  * This will fail if the document already has an element
  * or the document fragment does not contain one.
  *
  * If a target is provided, it will append the xml to the target node.
  *
  * @param string $content
  * @param \PapayaXmlElement $target
  * @return \PapayaXmlElement|\PapayaXmlDocument $target
  */
  public function appendXml($content, \PapayaXmlElement $target = NULL) {
    if (NULL === $target) {
      $target = $this;
    }
    $fragment = $this->createDocumentFragment();
    $content = sprintf(
      '<papaya:content xmlns:papaya="http://www.papaya-cms.com/ns/papayacms">%s</papaya:content>',
      \Papaya\Utility\Text\Xml::removeControlCharacters(\Papaya\Utility\Text\Utf8::ensure($content))
    );
    $fragment->appendXML($content);
    if ($fragment->firstChild) {
      if ($target->ownerDocument instanceof self) {
        foreach ($fragment->firstChild->childNodes as $node) {
          /** @var DOMNode $node */
          $target->appendChild($node->cloneNode(TRUE));
        }
      } else {
        if ($fragment->firstChild->firstChild) {
          $target->appendChild($fragment->firstChild->firstChild->cloneNode(TRUE));
        }
      }
    }
    return $target;
  }

  /**
   * Overload createDocument(), to look for an namespace prefix in the element name and create
   * an element in this namespace. The namespace needs to be registered on the document object.
   * Fix the excaping bug for the $value argument, by creating the text node.
   * Allow to provide attributes.
   *
   * @see \DOMDocument::createElement()
   * @param string $name
   * @param string|NULL $value
   * @param array|NULL $attributes
   * @return \PapayaXmlElement
   */
  public function createElement($name, $value = NULL, array $attributes = NULL) {
    if (FALSE !== strpos($name, ':')) {
      $node = $this->createElementNS($this->getNamespace($name), $name);
    } else {
      $node = parent::createElement($name);
    }
    if (!is_null($value)) {
      $node->appendChild($this->createTextNode($value));
    }
    if (!empty($attributes)) {
      foreach ($attributes as $attributeName => $attributeValue) {
        $node->setAttribute($attributeName, $attributeValue);
      }
    }
    return $node;
  }

  /**
   * Overload createAttribute(), to look for an namespace prefix in the element name and create
   * an attribute in this namespace. The namespace needs to be registered on the document object.
   *
   * Allow to provide the attribute value directly.
   *
   * @see \DOMDocument::createElement()
   * @param string $name
   * @param string|NULL $value
   * @return \DOMAttribute
   */
  public function createAttribute($name, $value = NULL) {
    if (FALSE !== strpos($name, ':')) {
      $node = $this->createAttributeNS($this->getNamespace($name), $name);
    } else {
      $node = parent::createAttribute($name);
    }
    if (!is_null($value)) {
      $node->value = $value;
    }
    return $node;
  }

  /**
  * Create an new element node for a given document
  *
  * @param \PapayaXmlDocument $document
  * @param string $name
  * @param array $attributes
  * @param string $content
  * @deprecated
  * @return \PapayaXmlElement new node
  */
  public static function createElementNode(
    \PapayaXmlDocument $document, $name, array $attributes = array(), $content = NULL
  ) {
    return $document->createElement($name, $content, $attributes);
  }

  /**
   * Get/set the entry loader status
   *
   * @param boolean $status
   * @return bool|null
   */
  public function activateEntityLoader($status = NULL) {
    if (NULL !== $status) {
      $this->_activateEntityLoader = $status;
    }
    return $this->_activateEntityLoader;
  }

  /**
   * Load an xml string, but allow to disable the entitiy loader.
   *
   * @see \DOMDocument::load()
   */
  public function loadXml($source, $options = 0) {
    $status = ($this->_canDisableEntityLoader)
      ? libxml_disable_entity_loader(!$this->_activateEntityLoader) : FALSE;
    $result = parent::loadXML($source, $options);
    if ($this->_canDisableEntityLoader) {
      libxml_disable_entity_loader($status);
    }
    return $result;
  }

  /**
   * create a DOM from an xml document, capture errors
   */
  public static function createFromXml($xmlString, $silent = FALSE) {
    $errors = new \PapayaXmlErrors();
    $dom = new \PapayaXmlDocument();
    $success = $errors->encapsulate(
      array($dom, 'loadXml'), array($xmlString), !$silent
    );
    return ($success) ? $dom : NULL;
  }

  public function createTextNode($content) {
    return parent::createTextNode(
      \Papaya\Utility\Text\Xml::removeControlCharacters($content) ?: ''
    );
  }
}
