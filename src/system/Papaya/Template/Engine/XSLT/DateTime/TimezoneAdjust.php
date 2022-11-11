<?php
declare(strict_types=1);

namespace Papaya\Template\Engine\XSLT\DateTime {

  use Papaya\Template\Engine\XSLT\XpathError;

  abstract class TimezoneAdjust {

    /**
     * @param string $dateTime
     * @param string $timezone
     * @return string
     * @throws XpathError
     */
    public static function adjustDateTimeToTimezone(string $dateTime, string $timezone): string {
      return (string)(
        (new DateTime($dateTime))->adjustTimezone(
          ($timezone !== '') ? new TimezoneDuration($timezone) : NULL
        )
      );
    }

    /**
     * @param string $date
     * @param string $timezone
     * @return string
     * @throws XpathError
     */
    public static function adjustDateToTimezone(string $date, string $timezone): string {
      return preg_replace(
        '(T\\d+:\\d+:\\d+(\\.\\d+)?)',
        '',
        (new DateTime(Components::dateTime($date, '00:00:00')))->adjustTimezone(
          ($timezone !== '') ? new TimezoneDuration($timezone) : NULL
        )
      );
    }

    /**
     * @param string $time
     * @param string $timezone
     * @return string
     * @throws XpathError
     */
    public static function adjustTimeToTimezone(string $time, string $timezone): string {
      return preg_replace(
        '(\\d+-\\d+-\\d+T)',
        '',
        (new DateTime(Components::dateTime('1972-12-31', $time)))->adjustTimezone(
          ($timezone !== '') ? new TimezoneDuration($timezone) : NULL
        )
      );
    }
  }
}
