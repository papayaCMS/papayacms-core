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
namespace Papaya\XML;

use Papaya\Utility;

/**
 * Replacement for the DOMDocument adding some shortcuts for easier use
 *
 * @package Papaya-Library
 * @subpackage XML
 *
 * @property Element $documentElement
 */
class Document
  extends \DOMDocument
  implements Node {
  /**
   * Avoid loosing the overloaded class
   * @var self
   */
  private $_document;

  /**
   * @var Xpath
   */
  private $_xpath;

  /**
   * @var array
   */
  private $_namespaces = [];

  /**
   * Namespace prefixes starting with the letters 'xml' are reserved by the w3c and
   * used for defined namespaces
   *
   * @var array
   */
  private $_reservedNamespaces = [
    'xml' => 'http://www.w3.org/XML/1998/namespace',
    'xmlns' => 'http://www.w3.org/2000/xmlns/'
  ];

  /**
   * @var bool
   */
  private $_activateEntityLoader = FALSE;

  /**
   * @var bool
   */
  private $_canDisableEntityLoader;

  /**
   * Initialize document object and register own node class(es)
   *
   * @param string $version
   * @param string $encoding
   */
  public function __construct($version = '1.0', $encoding = 'UTF-8') {
    parent::__construct($version, $encoding);
    /** @noinspection UnusedConstructorDependenciesInspection */
    $this->_document = $this;
    $this->registerNodeClass(\DOMElement::class, Element::class);
    $this->_canDisableEntityLoader = \function_exists('libxml_disable_entity_loader');
  }

  /**
   * Get an Xpath object for the current document instance, refresh it if the internal document
   * id changes (document loading), register namespaces on the xpath object.
   *
   * @return \DOMXpath
   */
  public function xpath() {
    if (NULL === $this->_xpath || $this->_xpath->document !== $this) {
      $this->_xpath = new Xpath($this);
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
    $registerOnXpath = $registerOnXpath && NULL !== $this->_xpath;
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
   *
   * @throws \InvalidArgumentException
   */
  public function registerNamespace($prefix, $namespace, $registerOnXpath = TRUE) {
    if (
      isset($this->_reservedNamespaces[$prefix]) &&
      !$this->_reservedNamespaces[$prefix] === $namespace
    ) {
      throw new \InvalidArgumentException(
        \sprintf(
          'XML prefix "%s" is reserved for the namespace "%s".',
          $prefix,
          $this->_reservedNamespaces[$prefix]
        )
      );
    }
    $this->_namespaces[$prefix] = $namespace;
    if ($registerOnXpath && NULL !== $this->_xpath) {
      $this->_xpath->registerNamespace($prefix, $namespace);
    }
  }

  /**
   * Get the namespace for an prefix. If the $prefix contains a ':' only the part before that
   * character will be used.
   *
   * @param string $prefix
   *
   * @throws \UnexpectedValueException
   *
   * @return string
   */
  public function getNamespace($prefix) {
    if (FALSE !== ($position = \strpos($prefix, ':'))) {
      $prefix = \substr($prefix, 0, $position);
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
   * Append an xml element with attributes and content.
   * Strings will be appended as text nodes, arrays set as attributes, NULL will be ignored.
   *
   * @param string $name
   * @param string[]|array[]|Appendable[] $appendables
   * @return Element new element
   */
  public function appendElement($name, ...$appendables) {
    /** @noinspection PhpIncompatibleReturnTypeInspection */
    return $this->appendChild(
      $this->createElement($name, ...$appendables)
    );
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
   * @param Element|Document $target
   *
   * @return Element|self $target
   */
  public function appendXML($content, Element $target = NULL) {
    if (NULL === $target) {
      $target = $this;
    }
    $fragment = $this->createDocumentFragment();
    $content = \sprintf(
      '<papaya:content xmlns:papaya="http://www.papaya-cms.com/ns/papayacms">%s</papaya:content>',
      Utility\Text\XML::removeControlCharacters(Utility\Text\UTF8::ensure($content))
    );
    $fragment->appendXML($content);
    if ($fragment->firstChild) {
      if ($target->ownerDocument instanceof self) {
        foreach ($fragment->firstChild->childNodes as $node) {
          /* @var \DOMNode $node */
          $target->appendChild($node->cloneNode(TRUE));
        }
      } elseif ($fragment->firstChild->firstChild) {
        $target->appendChild($fragment->firstChild->firstChild->cloneNode(TRUE));
      }
    }
    return $target;
  }

  /**
   * Overload createDocument(), to look for an namespace prefix in the element name and create
   * an element in this namespace. The namespace needs to be registered on the document object.
   * Fix the escaping bug for the $value argument, by creating the text node.
   * Allow to provide attributes.
   *
   * @see \DOMDocument::createElement()
   *
   * @param string $name
   * @param null $content
   * @param array $appendables
   * @return Element
   */
  public function createElement($name, $content = NULL, ...$appendables) {
    /** @var Element $node */
    if (FALSE !== \strpos($name, ':')) {
      $node = $this->createElementNS($this->getNamespace($name), $name);
    } else {
      $node = parent::createElement($name);
    }
    if (NULL !== $content) {
      \array_unshift($appendables, $content);
    }
    foreach ($appendables as $appendable) {
      if ($appendable instanceof Appendable) {
        $appendable->appendTo($node);
      } elseif (\is_array($appendable)) {
        foreach ($appendable as $attributeName => $attributeValue) {
          $node->setAttribute($attributeName, $attributeValue);
        }
      } else {
        $node->appendChild($this->createTextNode($appendable));
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
   *
   * @param string $name
   * @param string|null $value
   *
   * @return \DOMAttr
   */
  public function createAttribute($name, $value = NULL) {
    if (FALSE !== \strpos($name, ':')) {
      $node = $this->createAttributeNS($this->getNamespace($name), $name);
    } else {
      $node = parent::createAttribute($name);
    }
    if (NULL !== $value) {
      $node->value = $value;
    }
    return $node;
  }

  /** @noinspection PhpDocSignatureInspection */

  /**
   * Create an new element node for a given document
   *
   * @param self $document
   * @param string $name
   * @param array $attributes
   * @param string $content
   *
   * @return Element new node
   * @deprecated
   */
  public static function createElementNode(
    self $document, $name, array $attributes = [], $content = NULL
  ) {
    return $document->createElement($name, $content, $attributes);
  }

  /**
   * Get/set the entry loader status
   *
   * @param bool $status
   *
   * @return bool|null
   */
  public function activateEntityLoader($status = NULL) {
    if (NULL !== $status) {
      $this->_activateEntityLoader = $status;
    }
    return $this->_activateEntityLoader;
  }

  /**
   * Load an xml string, but allow to disable the entity loader.
   *
   * @see \DOMDocument::load()
   * @param string $source
   * @param int $options
   * @return mixed
   */
  public function loadXML($source, $options = 0) {
    $status = $this->_canDisableEntityLoader
      ? \libxml_disable_entity_loader(!$this->_activateEntityLoader) : FALSE;
    $result = parent::loadXML($source, $options);
    if ($this->_canDisableEntityLoader) {
      \libxml_disable_entity_loader($status);
    }
    return $result;
  }

  /**
   * create a DOM from an xml document, capture errors
   *
   * @param $xmlString
   * @param bool $silent
   * @return null|\Papaya\XML\Document
   */
  public static function createFromXML($xmlString, $silent = FALSE) {
    $errors = new Errors();
    $document = new self();
    $success = $errors->encapsulate(
      function($source, $options = 0) use ($document) {
        return $document->loadXML($source, $options);
      },
      [$xmlString],
      !$silent
    );
    return $success ? $document : NULL;
  }

  /**
   * @param string $content
   * @return \DOMText
   */
  public function createTextNode($content) {
    return parent::createTextNode(
      Utility\Text\XML::removeControlCharacters($content)
    );
  }
}
