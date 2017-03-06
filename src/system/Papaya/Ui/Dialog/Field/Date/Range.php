<?php

class PapayaUiDialogFieldDateRange extends PapayaUiDialogField {

  private $_includeTime = FALSE;

  /**
   * @var Traversable additional labels for the field
   */
  private $_labels;

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
          'start' => new PapayaFilterLogicalOr(
            new PapayaFilterEmpty(),
            new PapayaFilterDate($this->_includeTime)
          ),
          'end' => new PapayaFilterLogicalOr(
            new PapayaFilterEmpty(),
            new PapayaFilterDate($this->_includeTime)
          ),
          'mode' => new PapayaFilterLogicalOr(
            new PapayaFilterEmpty(),
            new PapayaFilterList(['fromTo', 'in', 'from', 'to'])
          )
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
    $values = $this->getCurrentValue();
    $start = '';
    $end = '';
    if (!empty($values['start'])) {
      $start = PapayaUtilDate::stringToTimestamp($values['start']);
    }
    if (!empty($values['end'])) {
      $end = PapayaUtilDate::stringToTimestamp($values['end']);
    }
    $group = $field->appendElement('group');
    $labels = $group->appendElement('labels');
    foreach ($this->labels() as $id => $label) {
      $labels->appendElement('label', ['for' => $id ], $label);
    }
    $group->setAttribute(
      'data-selected-page',
      empty($values['mode']) ? 'fromTo' : $values['mode']
    );
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

  public function labels(Traversable $labels = NULL) {
    if (isset($labels)) {
      $this->_labels = $labels;
    } elseif (NULL === $this->_labels) {
      if ($this->papaya()->request->isAdministration) {
        $this->_labels = new PapayaUiStringTranslatedList(
          [
            'page-in' => 'In (Year: YYYY, Year-Month: YYYY-MM)',
            'page-fromto' => 'From/To',
            'page-from' => 'From',
            'page-to' => 'To'
          ]
        );
      } else {
        $this->_labels = new EmptyIterator();
      }
    }
    return $this->_labels;
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

  /**
   * If not mandatory allow the whole value as empty or each sub value.
   *
   * @return NULL|PapayaFilter
   */
  public function getFilter() {
    $filter = parent::getFilter();
    if ($this->getMandatory() && isset($filter)) {
      return $filter;
    } elseif (isset($filter)) {
      return new PapayaFilterLogicalOr(
        new PapayaFilterArrayAssociative(
          [
            'start' => new PapayaFilterEmpty(),
            'end' => new PapayaFilterEmpty(),
            'mode' => new PapayaFilterEmpty()
          ]
        ),
        $filter
      );
    } else {
      return NULL;
    }
  }
}