<?php
declare(strict_types=1);

namespace Papaya\Template\Engine\XSLT\Duration {

  abstract class Components {

    public static function yearsFromDuration(string $duration): int {
      return (new Duration($duration))->normalize()->getYears();
    }

    public static function monthsFromDuration(string $duration): int {
      return (new Duration($duration))->normalize()->getMonths();
    }

    public static function daysFromDuration(string $duration): int {
      return (new Duration($duration))->normalize()->getDays();
    }

    public static function hoursFromDuration(string $duration): int {
      return (new Duration($duration))->normalize()->getHours();
    }

    public static function minutesFromDuration(string $duration): int {
      return (new Duration($duration))->normalize()->getMinutes();
    }

    public static function secondsFromDuration(string $duration): float {
      return (new Duration($duration))->normalize()->getSeconds();
    }
  }
}
