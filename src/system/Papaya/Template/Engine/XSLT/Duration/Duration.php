<?php
declare(strict_types=1);

namespace Papaya\Template\Engine\XSLT\Duration {

  class Duration {

    private const PATTERN = '(
      ^
      (?<negative>-)?
      P
      (
        (?:(?<years>\d+)Y)?
        (?:(?<months>\d+)M)?
        (?:(?<days>\d+)D)?
      )?
      (T
        (?:(?<hours>\d+)H)?
        (?:(?<minutes>\d+)M)?
        (?:(?<seconds>\d+(?:\.\d+)?)S)?
      )?
      $
    )x';

    private $_isNegative = false;
    private $_years = 0;
    private $_months = 0;
    private $_days = 0;
    private $_hours = 0;
    private $_minutes = 0;
    private $_seconds = 0;

    public function __construct(string $duration) {
      if (preg_match(self::PATTERN, $duration, $matches)) {
        $this->_isNegative = ($matches['negative'] ?? '') === '-';
        $this->_years = (int)($matches['years'] ?? 0);
        $this->_months = (int)($matches['months'] ?? 0);
        $this->_days = (int)($matches['days'] ?? 0);
        $this->_hours = (int)($matches['hours'] ?? 0);
        $this->_minutes = (int)($matches['minutes'] ?? 0);
        $this->_seconds = (float)($matches['seconds'] ?? 0.0);
      }
    }

    public function __toString(): string {
      $dateMap = [
        '_years' => 'Y',
        '_months' => 'M',
        '_days' => 'D'
      ];
      $timeMap = [
        '_hours' => 'H',
        '_minutes' => 'M',
      ];
      $date = '';
      foreach ($dateMap as $property => $unit) {
        $date .= ($this->{$property} > 0) ? $this->{$property}.$unit : '';
      }
      $time = '';
      foreach ($timeMap as $property => $unit) {
        $time .= ($this->{$property} > 0) ? $this->{$property}.$unit : '';
      }
      if ($this->_seconds > 0) {
        $time .= str_replace(
          '.000',
          '',
          number_format($this->_seconds, 3, '.', '')
        );
      }
      if ($date === '' && $time === '') {
        return 'PT0S';
      }
      return ($this->_isNegative ? '-P' : 'P').$date.'T'.$time;
    }

    public function isNegative(): bool {
      return $this->_isNegative;
    }

    public function getYears(): int {
      return $this->_years * ($this->_isNegative ? -1 : 1);
    }

    public function getMonths(): int {
      return $this->_months * ($this->_isNegative ? -1 : 1);
    }

    public function getDays(): int {
      return $this->_days * ($this->_isNegative ? -1 : 1);
    }

    public function getHours(): int {
      return $this->_hours * ($this->_isNegative ? -1 : 1);
    }

    public function getMinutes(): int {
      return $this->_minutes * ($this->_isNegative ? -1 : 1);
    }

    public function getSeconds(): float {
      return $this->_seconds * ($this->_isNegative ? -1 : 1);
    }

    public function normalize(): self {
      $duration = clone $this;
      if ($duration->_seconds >= 60) {
        $duration->_minutes += ($overflow = (int)floor($duration->_seconds / 60));
        $duration->_seconds -= $overflow * 60;
      }
      if ($duration->_minutes >= 60) {
        $duration->_hours += (int)floor($duration->_minutes / 60);
        $duration->_minutes %= 60;
      }
      if ($duration->_hours >= 24) {
        $duration->_days += (int)floor($duration->_hours / 24);
        $duration->_hours %= 24;
      }
      if ($duration->_months >= 12) {
        $duration->_years += (int)floor($duration->_months / 12);
        $duration->_months %= 12;
      }
      return $duration;
    }

    public function compareWith(Duration $duration): int {
      if ($this->_isNegative !== $duration->_isNegative) {
        return $this->_isNegative ? -1 : 1;
      }
      $properties = ['_years', '_months', '_days', '_hours', '_minutes', '_seconds'];
      foreach ($properties as $property) {
        if ($this->{$property} !== $duration->{$property}) {
          if ($this->_isNegative) {
            return ($this->{$property} < $duration->{$property}) ? 1 : -1;
          }
          return ($this->{$property} < $duration->{$property}) ? -1 : 1;
        }
      }
      return 0;
    }
  }
}
