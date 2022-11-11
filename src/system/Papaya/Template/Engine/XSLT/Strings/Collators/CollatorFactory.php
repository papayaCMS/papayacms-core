<?php
declare(strict_types=1);

namespace Papaya\Template\Engine\XSLT\Strings\Collators {

  use Papaya\Template\Engine\XSLT\Strings\XpathCollator;

  abstract class CollatorFactory {

    private static $_defaultCollation = UnicodeCodepointCollator::URI;

    private static $_collationURIs = [
      UnicodeCodepointCollator::URI => UnicodeCodepointCollator::class,
      CaseInsensitiveASCIICollator::URI => CaseInsensitiveASCIICollator::class,
      ParameterizedCollator::URI => ParameterizedCollator::class,
    ];

    private static $_collators = [];

    public static function getByURI(string $uri): XpathCollator {
      if ('' === $uri) {
        $uri = self::getDefaultCollation();
      }
      if (isset(self::$_collators[$uri])) {
        return self::$_collators[$uri];
      }
      $collatorClass = self::getCollatorClass($uri);
      if (
        class_exists($collatorClass) &&
        ($collator = new $collatorClass($uri)) &&
        $collator instanceof XpathCollator
      ) {
        return self::$_collators[$uri] = $collator;
      }
      throw new \InvalidArgumentException('Unknown collation URI: '.$uri);
    }

    public static function reset(): void {
      self::$_collators = [];
      self::$_defaultCollation = UnicodeCodepointCollator::URI;
    }

    public static function setDefaultCollation(string $uri): void {
      if (NULL === self::getCollatorClass($uri)) {
        throw new \InvalidArgumentException('Can not set default collation to unknown URI: '.$uri);
      }
      self::$_defaultCollation = $uri;
    }

    public static function getDefaultCollation(): string {
      return self::$_defaultCollation;
    }

    /**
     * @param string $uri
     * @return NULL|string
     */
    private static function getCollatorClass(string $uri): ?string {
      $collatorClass = NULL;
      if (isset(self::$_collationURIs[$uri])) {
        $collatorClass = self::$_collationURIs[$uri];
      } else {
        $baseURI = preg_replace('([?#].*$)s', '', $uri);
        if (isset(self::$_collationURIs[$baseURI])) {
          $collatorClass = self::$_collationURIs[$baseURI];
        }
      }
      return $collatorClass;
    }
  }
}
