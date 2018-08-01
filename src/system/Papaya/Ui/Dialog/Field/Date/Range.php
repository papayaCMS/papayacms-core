<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

class PapayaUiDialogFieldDateRange extends \PapayaUiDialogField {

  private $_includeTime = FALSE;

  /**
   * @var Traversable additional labels for the field
   */
  private $_labels;

  /**
   * Creates dialog field for date range, two inputs for a start and an end value
   *
   * @param string|\PapayaUiString $caption
   * @param string $name
   * @param boolean $mandatory
   * @param int $includeTime
   * @throws \InvalidArgumentException
   */
  public function __construct(
    $caption,
    $name,
    $mandatory = FALSE,
    $includeTime = \Papaya\Filter\Date::DATE_NO_TIME
  ) {
    if (
      $includeTime != \Papaya\Filter\Date::DATE_NO_TIME &&
      $includeTime != \Papaya\Filter\Date::DATE_OPTIONAL_TIME &&
      $includeTime != \Papaya\Filter\Date::DATE_MANDATORY_TIME
    ) {
      throw new \InvalidArgumentException(
        'Argument must be \PapayaFilterDate::DATE_* constant.'
      );
    }
    $this->_includeTime = (int)$includeTime;
    $this->setCaption($caption);
    $this->setName($name);
    $this->setFilter(
      new \Papaya\Filter\AssociativeArray(
        [
          'start' => new \Papaya\Filter\LogicalOr(
            new \Papaya\Filter\EmptyValue(),
            new \Papaya\Filter\Date($this->_includeTime)
          ),
          'end' => new \Papaya\Filter\LogicalOr(
            new \Papaya\Filter\EmptyValue(),
            new \Papaya\Filter\Date($this->_includeTime)
          ),
          'mode' => new \Papaya\Filter\LogicalOr(
            new \Papaya\Filter\EmptyValue(),
            new \Papaya\Filter\ArrayElement(['fromTo', 'in', 'from', 'to'])
          )
        ]
      )
    );
    $this->setMandatory($mandatory);
  }

  public function appendTo(\Papaya\Xml\Element $parent) {
    $field = $this->_appendFieldTo($parent);
    $field->setAttribute(
      'data-include-time', ($this->_includeTime == \Papaya\Filter\Date::DATE_NO_TIME) ? 'false' : 'true'
    );
    $fieldName = $this->getName();
    $values = $this->getCurrentValue();
    $start = '';
    $end = '';
    if (!empty($values['start'])) {
      $start = \Papaya\Utility\Date::stringToTimestamp($values['start']);
    }
    if (!empty($values['end'])) {
      $end = \Papaya\Utility\Date::stringToTimestamp($values['end']);
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
        'type' => ($this->_includeTime == \Papaya\Filter\Date::DATE_NO_TIME) ? 'date' : 'datetime',
        'name' => $this->_getParameterName($fieldName.'/start')
      ],
      $this->formatDateTime(
        $start, $this->_includeTime != \Papaya\Filter\Date::DATE_NO_TIME
      )
    );
    $group->appendElement(
      'input',
      [
        'type' => ($this->_includeTime == \Papaya\Filter\Date::DATE_NO_TIME) ? 'date' : 'datetime',
        'name' => $this->_getParameterName($fieldName.'/end'),
        'value' => $end
      ],
      $this->formatDateTime(
        $end, $this->_includeTime != \Papaya\Filter\Date::DATE_NO_TIME
      )
    );
  }

  public function labels(\Traversable $labels = NULL) {
    if (isset($labels)) {
      $this->_labels = $labels;
    } elseif (NULL === $this->_labels) {
      if ($this->papaya()->request->isAdministration) {
        $this->_labels = new \PapayaUiStringTranslatedList(
          [
            'page-in' => 'In (Year, Year-Month)',
            'page-fromto' => 'Date Between',
            'page-from' => 'Date After',
            'page-to' => 'Date Before'
          ]
        );
      } else {
        $this->_labels = new \EmptyIterator();
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
   * @return NULL|\Papaya\Filter
   */
  public function getFilter() {
    $filter = parent::getFilter();
    if ($this->getMandatory() && isset($filter)) {
      return $filter;
    } elseif (isset($filter)) {
      return new \Papaya\Filter\LogicalOr(
        new \Papaya\Filter\AssociativeArray(
          [
            'start' => new \Papaya\Filter\EmptyValue(),
            'end' => new \Papaya\Filter\EmptyValue(),
            'mode' => new \Papaya\Filter\EmptyValue()
          ]
        ),
        $filter
      );
    } else {
      return NULL;
    }
  }
}
