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

namespace Papaya\Template;
/**
 * Templates values are a handling object for a dom document of template values,
 * later convertet to an output using a template engine
 *
 * @package Papaya-Library
 * @subpackage Template
 */
class Values {

  /**
   * The Dom Document containg the actual values
   *
   * @var \Papaya\XML\Document
   */
  private $_document = NULL;

  /**
   * Construct object and initalize internal dom document.
   *
   * @param \Papaya\XML\Document $document
   * @return \PapayaTemplateValues
   */
  public function __construct(\Papaya\XML\Document $document = NULL) {
    $this->document(isset($document) ? $document : new \Papaya\XML\Document());
  }

  /**
   * Get/Set document property
   *
   * @param \DOMDocument $document
   * @return \DOMDocument
   * @internal param $node
   */
  public function document(\DOMDocument $document = NULL) {
    if (isset($document)) {
      $this->_document = $document;
    }
    return $this->_document;
  }

  /**
   * Get an Xpath object for the current document. Create it if it does not exist.
   *
   * @return \DOMXpath;
   */
  public function getXpath() {
    /** @noinspection PhpUndefinedMethodInspection */
    return $this->document()->xpath();
  }

  /**
   * Provides easy navigation in the response document
   *
   * The function expects a simple path like "/documentElement/element/element". It searches
   * for the first element matching this path and creates it if it is not found. If no context
   * is provides it starts with the document element matching the first part of the path.
   *
   * It returns FALSE if the element can not be found and throws an exception if it can not create
   * the element.
   *
   * @param string $path
   * @param \DOMElement $context
   * @param boolean $createIfNotExists
   * @throws \InvalidArgumentException
   * @return \Papaya\Template\Value|FALSE
   */
  public function getValueByPath($path, \DOMElement $context = NULL, $createIfNotExists = TRUE) {
    if (substr($path, 0, 1) == '/') {
      $context = NULL;
      $paths = explode('/', substr($path, 1));
    } else {
      $paths = explode('/', $path);
    }
    $node = FALSE;
    foreach ($paths as $name) {
      if (!preg_match('(^[a-z][a-z\d_-]*$)iD', $name)) {
        throw new \InvalidArgumentException('Invalid argument path: "'.$path.'"');
      }
      $nodeList = $this->getXpath()->evaluate(
        $name.'[1]', is_null($context) ? $this->_document : $context
      );
      if ($nodeList->length == 0) {
        if ($createIfNotExists) {
          $node = $this->_document->createElement($name);
          if (isset($context)) {
            $context->appendChild($node);
          } else {
            $this->_document->appendChild($node);
          }
        } else {
          return FALSE;
        }
      } else {
        $node = $nodeList->item(0);
      }
      $context = $node;
    }
    return ($node instanceof \Papaya\XML\Element)
      ? new \Papaya\Template\Value($node) : FALSE;
  }

  /**
   * Get a template value from the current tree
   *
   * The functions tries to get a template value element defined by the selector.
   *
   * If it is an string it {@see \Papaya\Template\PapayaTemplateValues::getPath()} will be used.
   *
   * If it is NULL, it will return a value containing the document itself.
   *
   * If it is an DOMElement a value containing this element will be returned.
   *
   * @throws \InvalidArgumentException
   * @param string|NULL|\DOMElement $selector
   * @return \Papaya\Template\Value
   */
  public function getValue($selector = NULL) {
    if (is_string($selector)) {
      return $this->getValueByPath($selector);
    } elseif (is_null($selector)) {
      return new \Papaya\Template\Value($this->_document);
    } elseif ($selector instanceof \Papaya\XML\Element) {
      return new \Papaya\Template\Value($selector);
    }
    throw new \InvalidArgumentException('Can not find specified template value');
  }

  /**
   * Append a new template element to a defined parent
   *
   * @param string|NULL|\DOMElement $parent
   * @param string $name
   * @param array $attributes
   * @param string $content
   * @return \Papaya\Template\Value
   */
  public function append($parent, $name, array $attributes = array(), $content = '') {
    return $this->getValue($parent)->append($name, $attributes, $content);
  }

  /**
   * Append a new xml fragment to a defined parent
   *
   * @param string|NULL|\DOMElement $parent
   * @param string $xml
   * @return \Papaya\Template\Value
   */
  public function appendXML($parent, $xml) {
    return $this->getValue($parent)->appendXML(\Papaya\Utility\Text\Utf8::ensure($xml));
  }
}
