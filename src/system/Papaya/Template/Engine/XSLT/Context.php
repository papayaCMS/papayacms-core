<?php
declare(strict_types=1);

namespace Papaya\Template\Engine\XSLT {

  use Papaya\Template\Engine\XSLT\DateTime\TimezoneDuration;
  use Papaya\Template\Engine\XSLT\Duration\Duration;
  use Papaya\Template\Engine\XSLT\Strings\Collators\CollatorFactory;
  use DateTime;
  use Locale;

  abstract class Context {

    /**
     * @var Duration
     */
    private static $_implicitTimezone;

    public static function currentDateTime() : string {
      return (new DateTime())->format(DateTime::RFC3339_EXTENDED);
    }

    public static function currentDate() : string {
      return (new DateTime())->format('Y-m-d');
    }

    public static function currentTime() : string {
      return (new DateTime())->format('H:i:s.vP');
    }

    /**
     * @return string
     * @throws XpathError
     */
    public static function implicitTimezone() : string {
      if (NULL === self::$_implicitTimezone) {
        self::$_implicitTimezone = new TimezoneDuration(
            preg_replace(
            '(^(?:\\+|([+-]))0?(\\d?\\d):0?(\\d?\\d)$)',
            '$1PT$2H$3M',
            (new DateTime())->format('P')
          )
        );
      }
      return (string)self::$_implicitTimezone;
    }

    public static function setImplicitTimezone(TimezoneDuration $duration): void {
      self::$_implicitTimezone = $duration;
    }

    public static function defaultCollation(): string {
      return CollatorFactory::getDefaultCollation();
    }

    public static function setDefaultCollation(string $collation): void {
      CollatorFactory::setDefaultCollation($collation);
    }

    public static function defaultLanguage(): string {
      return Locale::getDefault();
    }

    public static function reset(): void {
      CollatorFactory::reset();
      self::$_implicitTimezone = NULL;
    }
  }
}
