<?php
declare(strict_types=1);

namespace Papaya\Template\Engine\XSLT\MapsAndArrays {

  use Papaya\Template\Engine\XSLT\Namespaces;
  use Papaya\Template\Engine\XSLT\Sequences\External;
  use Papaya\Template\Engine\XSLT\XpathError;
  use JsonException;

  abstract class JSON {

    public static function jsonDoc(string $href): ?\DOMElement {
      return self::jsonToXML(External::unparsedText($href))->documentElement;
    }

    public static function jsonToXML(string $jsonData): \DOMDocument {
      $document = new \DOMDocument('1.0', 'UTF-8');
      try {
        $json = json_decode($jsonData, FALSE, 512, JSON_THROW_ON_ERROR);
        self::appendJSONToXDM($document, $json);
      } catch (JsonException $e) {
        throw new XpathError(Namespaces::XMLNS_ERR.'#FOJS0001', 'JSON syntax error.');
      }
      return $document;
    }

    public static function xmlToJSON($node, \DOMNode $options = NULL): string {
      if (is_array($node)) {
        $node = $node[0];
      }
      if ($node instanceof \DOMDocument) {
        $node = $node->documentElement;
      }
      if ($node instanceof \DOMElement) {
        return json_encode(self::convertXDMToJSON($node), JSON_PRETTY_PRINT);
      }
      throw new XpathError(Namespaces::XMLNS_ERR.'#FOJS0006', 'Invalid XML representation of JSON');
    }

    private static function appendJSONToXDM(\DOMNode $parent, $value, string $key = NULL): void {
      $document = $parent instanceof \DOMDocument ? $parent : $parent->ownerDocument;
      if (
        $document instanceof \DOMDocument &&
        (
          $parent instanceof \DOMDocument || $parent instanceof \DOMElement
        )
      ) {
        $child = NULL;
        if ($value instanceof \stdClass) {
          $parent->appendChild(
            $child = $document->createElementNS(Namespaces::XMLNS_FN, 'map')
          );
          foreach (get_object_vars($value) as $childKey => $childValue) {
            self::appendJSONToXDM($child, $childValue, $childKey);
          }
        } elseif (is_array($value)) {
          $parent->appendChild(
            $child = $document->createElementNS(Namespaces::XMLNS_FN, 'array')
          );
          foreach ($value as $childValue) {
            self::appendJSONToXDM($child, $childValue);
          }
        } elseif (NULL === $value) {
          $parent->appendChild(
            $child = $document->createElementNS(Namespaces::XMLNS_FN, 'null')
          );
        } elseif (is_bool($value)) {
          $parent->appendChild(
            $child = $document->createElementNS(Namespaces::XMLNS_FN, 'boolean')
          );
          $child->textContent = $value ? 'true' : 'false';
        } elseif (is_int($value) || is_float($value)) {
          $parent->appendChild(
            $child = $document->createElementNS(Namespaces::XMLNS_FN, 'number')
          );
          $child->textContent = $value;
        } elseif (is_string($value)) {
          $parent->appendChild(
            $child = $document->createElementNS(Namespaces::XMLNS_FN, 'string')
          );
          $child->textContent = $value;
        }
        if ($child && $key) {
          $child->setAttribute('key', $key);
        }
      }
    }

    private static function convertXDMToJSON(\DOMElement $node) {
      $document = $node instanceof \DOMDocument ? $node : $node->ownerDocument;
      $xpath = new \DOMXPath($document);
      switch ($node->localName) {
      case 'array':
        $result = [];
        foreach ($xpath->evaluate('*', $node) as $childNode) {
          $result[] = self::convertXDMToJSON($childNode);
        }
        break;
      case 'map':
        $result = new \stdClass();
        foreach ($xpath->evaluate('*[string(@key) != ""]', $node) as $childNode) {
          /** @var \DOMElement $childNode */
          $result->{$childNode->getAttribute('key')} = self::convertXDMToJSON($childNode);
        }
        break;
      case 'boolean':
        return trim($node->textContent) === 'true';
      case 'null':
        return NULL;
      case 'number':
        return (float)$node->textContent;
      case 'string':
        return $node->textContent;
      default:
        throw new XpathError(Namespaces::XMLNS_ERR.'#FOJS0006', 'Invalid XML representation of JSON');
      }
      return $result;
    }
  }
}
