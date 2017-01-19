<?php

class PapayaUiDialogFieldDateRange extends PapayaUiDialogField {

  private $_includeTime = FALSE;

  /**
   * Creates dialog field for date range, two inputs for a start and an end value
   *
   * @param string|PapayaUiString $caption
   * @param string $name
   * @param boolean $mandatory
   * @param int $includeTime
   * @throws InvalidArgumentException
   */
  public function __construct(
    $caption,
    $name,
    $mandatory = FALSE,
    $includeTime = PapayaFilterDate::DATE_NO_TIME
  ) {
    if (
      $includeTime != PapayaFilterDate::DATE_NO_TIME &&
      $includeTime != PapayaFilterDate::DATE_OPTIONAL_TIME &&
      $includeTime != PapayaFilterDate::DATE_MANDATORY_TIME
    ) {
      throw new InvalidArgumentException(
        'Argument must be PapayaFilterDate::DATE_* constant.'
      );
    }
    $this->_includeTime = (int)$includeTime;
    $this->setCaption($caption);
    $this->setName($name);
    $this->setFilter(
      new PapayaFilterArrayAssociative(
        [
          'start' => new PapayaFilterDate($this->_includeTime),
          'end' => new PapayaFilterDate($this->_includeTime)
        ]
      )
    );
    $this->setMandatory($mandatory);
  }

  public function appendTo(PapayaXmlElement $parent) {
    $field = $this->_appendFieldTo($parent);
    $field->setAttribute(
      'data-include-time', ($this->_includeTime == PapayaFilterDate::DATE_NO_TIME) ? 'false' : 'true'
    );
    $fieldName = $this->getName();
    $start = '';
    $end = '';
    if ($values = $this->getCurrentValue()) {
      $start = PapayaUtilDate::stringToTimestamp($values['start']);
      $end = PapayaUtilDate::stringToTimestamp($values['end']);
    }
    $group = $field->appendElement('group');
    $group->appendElement(
      'input',
      [
        'type' => ($this->_includeTime == PapayaFilterDate::DATE_NO_TIME) ? 'date' : 'datetime',
        'name' => $this->_getParameterName($fieldName.'/start')
      ],
      $this->formatDateTime(
        $start, $this->_includeTime != PapayaFilterDate::DATE_NO_TIME
      )
    );
    $group->appendElement(
      'input',
      [
        'type' => ($this->_includeTime == PapayaFilterDate::DATE_NO_TIME) ? 'date' : 'datetime',
        'name' => $this->_getParameterName($fieldName.'/end'),
        'value' => $end
      ],
      $this->formatDateTime(
        $end, $this->_includeTime != PapayaFilterDate::DATE_NO_TIME
      )
    );
  }

  /**
   * Convert timestamp into a string
   *
   * @param integer $timestamp
   * @param boolean $includeTime
   * @return string
   */
  private function formatDateTime($timestamp, $includeTime = TRUE) {
    if ($timestamp == 0) {
      return '';
    } elseif ($includeTime) {
      return date('Y-m-d H:i:s', $timestamp);
    } else {
      return date('Y-m-d', $timestamp);
    }
  }
}