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
namespace Papaya\Utility\Text;

use Papaya\Utility;
use Papaya\XML\Errors as XMLErrors;

/**
 * Papaya Utilities - XML functions
 *
 * @package Papaya-Library
 * @subpackage Util
 */
class XML {
  /**
   * Escape XML meta chars in string
   *
   * @param string $string
   *
   * @return string
   */
  public static function escape($string) {
    return \htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
  }

  /**
   * Unescape XML meta chars in string
   *
   * @param string $string
   *
   * @return string
   */
  public static function unescape($string) {
    return \html_entity_decode($string, ENT_QUOTES, 'UTF-8');
  }

  /**
   * Escape XML meta chars and linebreaks in string
   *
   * @param string $string
   *
   * @return string
   */
  public static function escapeAttribute($string) {
    return \str_replace(
      ["\r", "\n"],
      ['&#13;', '&#10;'],
      \htmlspecialchars($string, ENT_QUOTES, 'UTF-8')
    );
  }

  /**
   * Try to repair anf fix entities (unencaped and from html) to ensure valid xml.
   *
   * @param string $string
   *
   * @return string
   */
  public static function repairEntities($string) {
    static $translations = NULL;
    if (NULL === $translations) {
      $translations = \array_flip(
        \version_compare(PHP_VERSION, '5.2', '>')
          ? \get_html_translation_table(HTML_ENTITIES, ENT_COMPAT, 'UTF-8')
          : \get_html_translation_table(HTML_ENTITIES)
      );
    }
    $result = $string;
    $result = \preg_replace(
      '(&((amp)|(quot)|([gl]t)|(#((\d+)|(x[a-fA-F\d]{2,4}))));)i',
      '#||\\1||#',
      $result
    );
    $result = \strtr($result, \is_array($translations) ? $translations : []);
    $result = \str_replace('&', '&amp;', $result);
    $result = \preg_replace('(#\|\|([a-z\d#]+)\|\|#)i', '&\\1;', $result);
    $result = \str_replace('&amp;amp;', '&amp;', $result);
    return UTF8::ensure($result);
  }

  /**
   * Serialize an php array (including array elements) into a xml string
   *
   * @param array $array
   * @param string $tagName
   *
   * @return string
   */
  public static function serializeArray($array, $tagName = 'data') {
    $document = new \DOMDocument('1.0', 'UTF-8');
    $root = $document->createElement($tagName);
    $root->setAttribute('version', '2');
    $document->appendChild($root);
    if (\is_array($array)) {
      self::_serializeSubArray($document->documentElement, $tagName, $array);
    }
    return $document->saveXML($document->documentElement);
  }

  /**
   * Serialize a php array into child nodes - called recursive for array elements
   *
   * @param \DOMElement $parent
   * @param string $tagName
   * @param array $array
   */
  private static function _serializeSubArray($parent, $tagName, $array) {
    foreach ($array as $name => $value) {
      if ('' !== \trim($name)) {
        if (NULL !== $value && \is_array($value)) {
          $childNode = $parent->ownerDocument->createElement($tagName.'-list');
          $childNode->setAttribute('name', UTF8::ensure($name));
          self::_serializeSubArray($childNode, $tagName, $value);
        } else {
          $childNode = $parent->ownerDocument->createElement($tagName.'-element');
          $childNode->setAttribute('name', UTF8::ensure($name));
          $dataNode = $parent->ownerDocument->createTextNode(
            UTF8::ensure($value)
          );
          $childNode->appendChild($dataNode);
        }
        $parent->appendChild($childNode);
      }
    }
  }

  /**
   * Unserialize a php array from xml
   *
   * @param string $xml
   *
   * @return array
   */
  public static function unserializeArray($xml) {
    $result = [];
    if (empty($xml)) {
      return $result;
    }
    if (FALSE === \strpos($xml, ' version="2">')) {
      $xml = UTF8::ensure(
        \preg_replace_callback(
          /** @lang PHPRegExp */
          '(&\\#(
            (?:1(?:2[6-9]|[3-9]\\d))
            |
            (?:2(?:[01]\\d|2[0-7]))
           );)x',
          static function($match) {
            return isset($match[1]) ? \chr($match[1]) : $match[0];
          },
          $xml
        )
      );
    }
    $dom = new \DOMDocument('1.0', 'UTF-8');
    $errorUsage = \libxml_use_internal_errors(TRUE);
    if ($dom->loadXML($xml)) {
      $version = $dom->documentElement->getAttribute('version');
      if (\version_compare($version, '2', '>=')) {
        self::_unserializeArrayFromNode(
          $dom->documentElement->nodeName, $dom->documentElement, $result
        );
      } else {
        self::_unserializeArrayFromNode(
          $dom->documentElement->nodeName,
          $dom->documentElement,
          $result,
          static function($value) {
            return self::unescape($value);
          }
        );
      }
    }
    \libxml_clear_errors();
    \libxml_use_internal_errors($errorUsage);
    return $result;
  }

  /**
   * Unserialize array data from a node, this function is called recursive.
   *
   * @param string $tagName
   * @param \DOMElement $parentNode
   * @param array $array
   * @param callable|null $valueCallback
   */
  private static function _unserializeArrayFromNode(
    $tagName, \DOMElement $parentNode, &$array, callable $valueCallback = NULL
  ) {
    if ($parentNode->hasChildNodes()) {
      foreach ($parentNode->childNodes as $childNode) {
        if (
          $childNode instanceof \DOMElement &&
          $childNode->hasAttribute('name')
        ) {
          $name = $childNode->getAttribute('name');
          if ($childNode->localName === $tagName.'-list') {
            $array[$name] = [];
            self::_unserializeArrayFromNode(
              $tagName, $childNode, $array[$name], $valueCallback
            );
          } elseif (NULL !== $valueCallback) {
            $array[$name] = $valueCallback($childNode->textContent);
          } else {
            $array[$name] = $childNode->nodeValue;
          }
        }
      }
    }
  }

  /**
   * Truncate the text content in a DOMElement.
   *
   * Empty elements like img or br get deleted.
   *
   * @param \DOMElement $sourceNode
   * @param int $length
   *
   * @return \DOMElement
   */
  public static function truncate(\DOMElement $sourceNode, $length) {
    $document = new \DOMDocument('1.0', 'UTF-8');
    $targetNode = self::_copyElement($sourceNode, $document);
    $document->appendChild($targetNode);
    self::_truncateChildNodes($sourceNode, $targetNode, $length);
    return $targetNode;
  }

  /**
   * Append child nodes of a parent element until the text content whould be larger than the
   * specified length. The last text node content ist truncated at the whitespace.
   *
   * @param \DOMElement $sourceNode
   * @param \DOMElement $targetNode
   * @param int $length
   *
   * @return int
   */
  private static function _truncateChildNodes(
    \DOMElement $sourceNode, \DOMElement $targetNode, $length
  ) {
    foreach ($sourceNode->childNodes as $childNode) {
      if ($length <= 0) {
        break;
      }
      switch ($childNode->nodeType) {
        case XML_ELEMENT_NODE :
          $copy = self::_copyElement($childNode, $targetNode);
          $length = self::_truncateChildNodes($childNode, $copy, $length);
        break;
        case XML_CDATA_SECTION_NODE :
        case XML_TEXT_NODE :
          $nodeText = $childNode->textContent;
          $nodeLength = \strlen($nodeText);
          if ($nodeLength <= $length) {
            $targetNode->appendChild(
              $targetNode->ownerDocument->createTextNode($nodeText)
            );
            $length -= $nodeLength;
          } else {
            $nodeText = Utility\Text::truncate($nodeText, $length, FALSE);
            if ('' !== $nodeText) {
              $targetNode->appendChild(
                $targetNode->ownerDocument->createTextNode($nodeText)
              );
            }
            $length = 0;
          }
        break;
      }
    }
    if (!$targetNode->hasChildNodes()) {
      $targetNode->parentNode->removeChild($targetNode);
    }
    return $length;
  }

  /**
   * Copy given element wiht its parameters but without its child nodes into the target.
   *
   * @param \DOMElement $sourceNode
   * @param \DOMNode $targetParent
   *
   * @return \DOMElement Imported node
   */
  private static function _copyElement(\DOMElement $sourceNode, \DOMNode $targetParent) {
    if ($targetParent instanceof \DOMDocument) {
      $targetNode = $targetParent->importNode($sourceNode, FALSE);
    } else {
      $targetNode = $targetParent->ownerDocument->importNode($sourceNode, FALSE);
    }
    /* @var \DOMElement $targetNode */
    foreach ($sourceNode->attributes as $attribute) {
      $targetNode->setAttribute($attribute->name, $attribute->value);
    }
    $targetParent->appendChild($targetNode);
    return $targetNode;
  }

  /**
   * Validate if the given string is a qualified element name (tag name)
   *
   * @param string $name
   *
   * @throws \UnexpectedValueException
   *
   * @return true
   */
  public static function isQName($name) {
    if (empty($name)) {
      throw new \UnexpectedValueException('Invalid QName: QName is empty.');
    }
    if (
      FALSE !== ($position = \strpos($name, ':'))
    ) {
      self::isNCName($name, 0, $position);
      self::isNCName($name, $position + 1);
      return TRUE;
    }
    self::isNCName($name);
    return TRUE;
  }

  /**
   * Validate if the given string is a valid nc name (namespace or element name)
   *
   * @param string $name
   * @param int $offset Offset of NCName part in QName
   * @param int $length Length of NCName part in QName
   *
   * @return bool
   *
   * @throws \UnexpectedValueException
   */
  public static function isNCName($name, $offset = 0, $length = 0) {
    $nameStartChar =
      'A-Z_a-z'.
      '\\x{C0}-\\x{D6}\\x{D8}-\\x{F6}\\x{F8}-\\x{2FF}\\x{370}-\\x{37D}'.
      '\\x{37F}-\\x{1FFF}\\x{200C}-\\x{200D}\\x{2070}-\\x{218F}'.
      '\\x{2C00}-\\x{2FEF}\\x{3001}-\\x{D7FF}\\x{F900}-\\x{FDCF}'.
      '\\x{FDF0}-\\x{FFFD}\\x{10000}-\\x{EFFFF}';
    $nameChar =
      $nameStartChar.
      '\\.\\d\\x{B7}\\x{300}-\\x{36F}\\x{203F}-\\x{2040}';
    if ($length > 0) {
      $namePart = \substr($name, $offset, $length);
    } elseif ($offset > 0) {
      $namePart = \substr($name, $offset);
    } else {
      $namePart = $name;
    }
    if (empty($namePart)) {
      throw new \UnexpectedValueException(
        'Invalid QName "'.$name.'": Missing QName part.'
      );
    }
    if (\preg_match('([^'.$nameChar.'-])u', $namePart, $match, PREG_OFFSET_CAPTURE)) {
      //invalid bytes and whitespaces
      $position = (int)$match[0][1];
      throw new \UnexpectedValueException(
        'Invalid QName "'.$name.'": Invalid character at index '.($offset + $position).'.'
      );
    }
    if (\preg_match('(^[^'.$nameStartChar.'])u', $namePart)) {
      //first char is a little more limited
      throw new \UnexpectedValueException(
        'Invalid QName "'.$name.'": Invalid character at index '.$offset.'.'
      );
    }
    return TRUE;
  }

  /**
   * Removes control characters (invalid in PCDATA in XML) from an string.
   *
   * @param $string
   *
   * @return string
   */
  public static function removeControlCharacters($string) {
    $result = \preg_replace(
      '([^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+)u',
      '',
      $string
    );
    return (is_string($result)) ? $result : '';
  }

  /**
   * @param string $string
   * @return string
   */
  public static function stripTags($string) {
    $errors = new XMLErrors();
    return (string)$errors->encapsulate(
      static function() use ($string) {
        $document = new \DOMDocument();
        if (0 !== strpos($string, '<?xml')) {
          $string = '<?xml version="1.0" encoding="utf-8"?>'.$string;
        }
        $document->loadHTML($string, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        return $document->textContent;
      },
      [],
      FALSE
    );
  }
}
