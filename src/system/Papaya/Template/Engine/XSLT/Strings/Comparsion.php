<?php
declare(strict_types=1);

namespace Papaya\Template\Engine\XSLT\Strings {

  use Papaya\Template\Engine\XSLT\Strings\Collators\CollatorFactory;

  abstract class Comparsion {

    /**
     * @param string $a
     * @param string $b
     * @param string $collationURI
     * @return int
     */
    public static function compare(string $a, string $b, string $collationURI = ''): int {
      return self::getCollator($collationURI)->compare($a, $b);
    }

    public static function collationKey(string $input, string $collationURI): string {
      return self::getCollator($collationURI)->getSortKey($input);
    }

    public static function containsToken(string $input, string $token, string $collationURI): bool {
      $token = preg_replace('(^\s*|\s*$)u', '', $token);
      $collator = self::getCollator($collationURI);
      foreach (preg_split('(\\s+)u', $input) as $tokenString) {
        if ($tokenString === $token || 0 === $collator->compare($token, $tokenString)) {
          return TRUE;
        }
      }
      return FALSE;
    }

    private static function getCollator(string $collationURI): XpathCollator {
      return CollatorFactory::getByURI($collationURI);
    }
  }
}
