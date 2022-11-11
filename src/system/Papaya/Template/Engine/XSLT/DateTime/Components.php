<?php
declare(strict_types=1);

namespace Papaya\Template\Engine\XSLT\DateTime {

  use Papaya\Template\Engine\XSLT\Namespaces;
  use Papaya\Template\Engine\XSLT\XpathError;

  abstract class Components {

    public static function dateTime(string $dateString, string $timeString): string {
      $date = new Date($dateString);
      $time = new Time($timeString);
      $dateOffset = $date->getOffset();
      $timeOffset = $time->getOffset();
      if ($dateOffset !== NULL && $timeOffset !== NULL) {
        if ($dateOffset->compareWith($timeOffset) !== 0) {
          throw new XpathError(
            Namespaces::XMLNS_ERR.'#FORG0008',
            'The two arguments to fn:dateTime have inconsistent timezones.'
          );
        }
        $timezone = $dateOffset->asTimezoneDuration();
      } elseif ($dateOffset !== NULL) {
        $timezone = $dateOffset->asTimezoneDuration();
      } elseif ($timeOffset !== NULL) {
        $timezone = $timeOffset->asTimezoneDuration();
      } else {
        $timezone = NULL;
      }
      $dateTime = new DateTime(
        $date->withoutOffset().'T'.$time->withoutOffset(), $timezone
      );
      return (string)$dateTime;
    }

    public static function yearFromDateTime(string $dateTime): int {
      return (int)(new DateTime($dateTime))->format('Y');
    }

    public static function monthFromDateTime(string $dateTime): int {
      return (int)(new DateTime($dateTime))->format('m');
    }

    public static function dayFromDateTime(string $dateTime): int {
      return (int)(new DateTime($dateTime))->format('d');
    }

    public static function hoursFromDateTime(string $dateTime): int {
      return (int)(new DateTime($dateTime))->format('H');
    }

    public static function minutesFromDateTime(string $dateTime): int {
      return (int)(new DateTime($dateTime))->format('i');
    }

    public static function secondsFromDateTime(string $dateTime): float {
      return (float)(new DateTime($dateTime))->format('s.v');
    }

    public static function timezoneFromDateTime(string $dateTime): ?string {
      return self::timezoneFromStringEnd($dateTime);
    }

    public static function yearFromDate(string $date): int {
      return (new Date($date))->getYear();
    }

    public static function monthFromDate(string $date): int {
      return (new Date($date))->getMonth();
    }

    public static function dayFromDate(string $date): int {
      return (new Date($date))->getDay();
    }

    public static function hoursFromTime(string $time): int {
      return (new Time($time))->getHour();
    }

    public static function minutesFromTime(string $time): int {
      return (new Time($time))->getMinute();
    }

    public static function secondsFromTime(string $time): float {
      return (new Time($time))->getSecond();
    }

    public static function timezoneFromTime(string $time): ?string {
      return self::timezoneFromStringEnd($time);
    }

    private static function timezoneFromStringEnd(string $input): ?string {
      if (preg_match('((?:Z|[+-]\\d{2}:\\d{2})$)', $input, $matches)) {
        $offset = new Offset($matches[0]);
        return (string)$offset->asDuration();
      }
      return NULL;
    }
  }
}
