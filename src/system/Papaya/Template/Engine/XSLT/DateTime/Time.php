<?php

namespace Papaya\Template\Engine\XSLT\DateTime {

  class Time {

    private const PATTERN = '(
      ^
      (?<hour>\\d+)
      :
      (?<minute>\\d+)
      :
      (?<second>\\d+(?:\\.\\d+)?)
      (?<offset>(?:Z|[+-]\\d{2}:\\d{2}))?
      $
    )x';

    private $_hour = 0;
    private $_minute = 0;
    private $_second = 1;
    private $_offset;

    public function __construct(string $time) {
      if (preg_match(self::PATTERN, $time, $matches)) {
        $this->_hour = (int)($matches['hour'] ?? 0);
        if ($this->_hour === 24) {
          $this->_hour = 0;
        }
        $this->_minute = (int)($matches['minute'] ?? 0);
        $this->_second = (float)($matches['second'] ?? 0);
        if (isset($matches['offset'])) {
          $this->_offset = new Offset($matches['offset']);
        }
      }
    }

    public function __toString(): string {
      if (abs($this->_second - floor($this->_second)) < 0.001) {
        $result = sprintf(
          '%1$02d:%2$02d:%3$02d', $this->_hour, $this->_minute, floor($this->_second)
        );
      } else {
        $result = sprintf(
          '%1$02d:%2$02d:%3$02.3f', $this->_hour, $this->_minute, $this->_second
        );
      }
      if (NULL !== $this->_offset) {
        $result .= $this->_offset;
      }
      return $result;
    }

    public function getHour(): int {
      return $this->_hour;
    }

    public function getMinute(): int {
      return $this->_minute;
    }

    public function getSecond(): float {
      return $this->_second;
    }

    public function getOffset(): ?Offset {
      return $this->_offset;
    }

    public function withoutOffset(): self {
      $time = clone $this;
      $time->_offset = NULL;
      return $time;
    }
  }
}
