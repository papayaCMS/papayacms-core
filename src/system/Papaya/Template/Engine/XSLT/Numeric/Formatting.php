<?php
declare(strict_types=1);

namespace Papaya\Template\Engine\XSLT\Numeric {

  use Papaya\Template\Engine\XSLT\Namespaces;
  use Papaya\Template\Engine\XSLT\XpathError;
  use NumberFormatter;

  abstract class Formatting {

    /**
     * @param float $input
     * @param string $picture
     * @param string $language
     * @return string
     * @throws XpathError
     */
    public static function formatInteger(float $input, string $picture, string $language): string {
      return self::createNumberFormatter($language, $picture)->format((int)$input);
    }

    /**
     * @param string $language
     * @param string $pattern
     * @return NumberFormatter
     * @throws XpathError
     */
    private static function createNumberFormatter(string $language, string $pattern): NumberFormatter {
      /* @todo Add some real parsing for the pattern */
      if ($pattern === 'w') {
        return NumberFormatter::create($language, NumberFormatter::SPELLOUT);
      }
      $formatter = NumberFormatter::create($language, NumberFormatter::DECIMAL);
      if (!$formatter->setPattern($pattern)) {
        throw new XpathError(Namespaces::XMLNS_ERR.'#FODF1310', 'Invalid decimal format picture string.');
      }
      return $formatter;
    }
  }
}
