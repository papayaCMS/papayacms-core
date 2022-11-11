<?php
declare(strict_types=1);

namespace Papaya\Template\Engine\XSLT\MapsAndArrays {

  use Papaya\Template\Engine\XSLT\Namespaces;
  use Papaya\Template\Engine\XSLT\XpathError;
  use DOMElement;

  abstract class Maps {

    private const DUPLICATES_USE_FIRST = 'use-first';
    private const DUPLICATES_USE_LAST = 'use-last';
    private const DUPLICATES_USE_ANY = 'use-any';
    private const DUPLICATES_REJECT = 'reject';
    private const DUPLICATES_COMBINE = 'combine';

    private const DUPLICATES_MODES = [
      self::DUPLICATES_USE_FIRST,
      self::DUPLICATES_USE_LAST,
      self::DUPLICATES_USE_ANY,
      self::DUPLICATES_REJECT,
      self::DUPLICATES_COMBINE,
    ];

    private const ELEMENT_NAMES = [
      'array', 'boolean', 'map', 'number', 'null', 'string'
    ];

    public static function createMap(...$arguments): \DOMNode {
      $document = new \DOMDocument('1.0', 'UTF-8');
      $document->appendChild(
        $map = $document->createElementNS(Namespaces::XMLNS_FN, 'map')
      );
      foreach ($arguments as $argument) {
        $argument = self::getNodeFromArgument($argument);
        if (
          $argument instanceof DOMElement &&
          (string)$argument->getAttribute('key') !== '' &&
          in_array($argument->localName, self::ELEMENT_NAMES, TRUE)
        ) {
          $key = $argument->getAttribute('key');
          if (isset($added[$key])) {
            continue;
          }
          $added[$key] = TRUE;
          $map->appendChild(
            $entry = $document->createElementNS(Namespaces::XMLNS_FN, $argument->localName)
          );
          $entry->setAttribute('key', $key);
          foreach ($argument->childNodes as $childNode) {
            $entry->appendChild($document->importNode($childNode, TRUE));
          }
        }
      }
      return $document->documentElement;
    }

    public static function merge($maps, $options): \DOMNode {
      $values = [];
      $options = self::optionsToArray($options);
      $duplicates = self::DUPLICATES_USE_FIRST;
      if (
        isset($options['duplicates']) &&
        in_array($options['duplicates'], self::DUPLICATES_MODES, TRUE)
      ) {
        $duplicates = $options['duplicates'];
      }
      if (
        ($maps = self::getNodeFromArgument($maps)) &&
        $maps->localName === 'array'
      ) {
        $xpath = new \DOMXPath($maps->ownerDocument);
        foreach ($xpath->evaluate('*[local-name() = "map"]', $maps) as $map) {
          foreach ($xpath->evaluate('*[@key]', $map) as $entry) {
            /** @var DOMElement $entry */
            $key = $entry->getAttribute('key');
            if (isset($values[$key])) {
              switch ($duplicates) {
              case self::DUPLICATES_USE_LAST:
                $values[$key] = $entry;
                break;
              case self::DUPLICATES_REJECT:
                new XpathError(Namespaces::XMLNS_ERR.'#FOJS0003', 'JSON duplicate keys.');
                break;
              case self::DUPLICATES_COMBINE:
                throw new \LogicException('Not implemented yet.');
                break;
              }
            } else {
              $values[$key] = $entry;
            }
          }
        }
      }
      return self::createMap(...array_values($values));
    }

    private static function getNodeFromArgument($argument): ?DOMElement {
      if (is_array($argument) && isset($argument[0])) {
        $argument = $argument[0];
      }
      if ($argument instanceof \DOMDocument) {
        $argument = $argument->documentElement;
      }
      return ($argument instanceof DOMElement) ? $argument : NULL;
    }

    private static function optionsToArray($options): array {
      $result = [];
      if ($options = self::getNodeFromArgument($options)) {
        $xpath = new \DOMXPath($options->ownerDocument);
        foreach ($xpath->evaluate('*[@key]', $options) as $option) {
          /** @var DOMElement $option */
          switch ($options->localName) {
          case 'boolean':
            $result[$option->getAttribute('key')] = $option->textContent === 'true';
            break;
          case 'number':
            $result[$option->getAttribute('key')] = (float)$option->textContent;
            break;
          case 'null':
            $result[$option->getAttribute('key')] = null;
            break;
          case 'string':
            $result[$option->getAttribute('key')] = $option->textContent;
            break;
          }
        }
      }
      return $result;
    }
  }
}
