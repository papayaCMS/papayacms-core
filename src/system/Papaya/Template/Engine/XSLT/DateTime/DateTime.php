<?php

namespace Papaya\Template\Engine\XSLT\DateTime {

  use Papaya\Template\Engine\XSLT\Context;
  use Papaya\Template\Engine\XSLT\XpathError;
  use DateTimeImmutable as PHPDateTime;
  use DateTimeZone as PHPDateTimeZone;

  class DateTime {

    private $_internal;
    /**
     * @var Offset
     */
    private $_offset;
    private $_withTimezone = FALSE;

    /**
     * @param string $dateTime
     * @param TimezoneDuration|NULL $timezone
     * @throws XpathError
     */
    public function __construct($dateTime = 'now', TimezoneDuration $timezone = NULL) {
      if (preg_match('((?:Z|[+-]\\d\\d:\\d\\d)$)', $dateTime, $matches)) {
        $this->_offset = new Offset($matches[0]);
        $this->_withTimezone = TRUE;
      } elseif (NULL !== $timezone) {
        $this->_offset = $timezone->asOffset();
        $this->_withTimezone = TRUE;
      } else {
        $this->_offset = (new TimezoneDuration(Context::implicitTimezone()))->asOffset();
        $this->_withTimezone = FALSE;
      }
      $this->_internal = new PHPDateTime($dateTime, new PHPDateTimeZone($this->_offset));
    }

    public function format(string $template): string {
      return $this->_internal->format($template);
    }

    public function adjustTimezone(TimezoneDuration $targetTimezone = NULL): self {
      if (NULL === $targetTimezone) {
        $targetTimezone = new TimezoneDuration(Context::implicitTimezone());
      }
      $targetOffset = $targetTimezone->asOffset();
      if ($this->_withTimezone) {
        $adjusted = clone $this;
        $adjusted->_internal = $this->_internal->setTimezone(
          new PHPDateTimeZone($targetOffset)
        );
        $adjusted->_withTimezone = TRUE;
        $adjusted->_offset = $targetOffset;
      } else {
        $adjusted = new self($this.$targetOffset);
      }
      return $adjusted;
    }

    public function __toString():string {
      $result = str_replace('.000', '', $this->format('Y-m-d\\TH:i:s.v'));
      if (
        $this->_withTimezone ||
        (
          $this->_offset !== NULL &&
          (string)($this->_offset->asDuration()) !== Context::implicitTimezone()
        )
      ) {
        $result .= $this->_offset;
      }
      return (string)$result;
    }
  }
}
