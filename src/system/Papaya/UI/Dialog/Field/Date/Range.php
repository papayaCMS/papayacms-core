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
namespace Papaya\UI\Dialog\Field\Date;

use Papaya\BaseObject\Interfaces\StringCastable;
use Papaya\Filter;
use Papaya\UI;
use Papaya\Utility;
use Papaya\XML;

class Range extends UI\Dialog\Field {
  /**
   * @var int
   */
  private $_includeTime;

  /**
   * @var \Traversable additional labels for the field
   */
  private $_labels;

  /**
   * Creates dialog field for date range, two inputs for a start and an end value
   *
   * @param string|StringCastable $caption
   * @param string $name
   * @param bool $mandatory
   * @param int $includeTime
   *
   * @throws \InvalidArgumentException
   */
  public function __construct(
    $caption,
    $name,
    $mandatory = FALSE,
    $includeTime = Filter\Date::DATE_NO_TIME
  ) {
    if (
      Filter\Date::DATE_NO_TIME !== $includeTime &&
      Filter\Date::DATE_OPTIONAL_TIME !== $includeTime &&
      Filter\Date::DATE_MANDATORY_TIME !== $includeTime
    ) {
      throw new \InvalidArgumentException(
        \sprintf(
          'Argument must be a %s::DATE_* constant.', Filter\Date::class
        )
      );
    }
    $this->_includeTime = (int)$includeTime;
    $this->setCaption($caption);
    $this->setName($name);
    $this->setFilter(
      new Filter\AssociativeArray(
        [
          'start' => new Filter\LogicalOr(
            new Filter\EmptyValue(),
            new Filter\Date($this->_includeTime)
          ),
          'end' => new Filter\LogicalOr(
            new Filter\EmptyValue(),
            new Filter\Date($this->_includeTime)
          ),
          'mode' => new Filter\LogicalOr(
            new Filter\EmptyValue(),
            new Filter\ArrayElement(['fromTo', 'in', 'from', 'to'])
          )
        ]
      )
    );
    $this->setMandatory($mandatory);
  }

  /**
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
    $field = $this->_appendFieldTo($parent);
    $field->setAttribute(
      'data-include-time',
      (Filter\Date::DATE_NO_TIME === $this->_includeTime) ? 'false' : 'true'
    );
    $fieldName = $this->getName();
    $values = $this->getCurrentValue();
    $start = '';
    $end = '';
    if (!empty($values['start'])) {
      $start = Utility\Date::stringToTimestamp($values['start']);
    }
    if (!empty($values['end'])) {
      $end = Utility\Date::stringToTimestamp($values['end']);
    }
    $group = $field->appendElement('group');
    $labels = $group->appendElement('labels');
    foreach ($this->labels() as $id => $label) {
      $labels->appendElement('label', ['for' => $id], $label);
    }
    $group->setAttribute(
      'data-selected-page',
      empty($values['mode']) ? 'fromTo' : $values['mode']
    );
    $group->appendElement(
      'input',
      [
        'type' => (Filter\Date::DATE_NO_TIME === $this->_includeTime) ? 'date' : 'datetime',
        'name' => $this->_getParameterName($fieldName.'/start'),
        'value' => $start
      ],
      $this->formatDateTime(
        $start, Filter\Date::DATE_NO_TIME !== $this->_includeTime
      )
    );
    $group->appendElement(
      'input',
      [
        'type' => (Filter\Date::DATE_NO_TIME === $this->_includeTime) ? 'date' : 'datetime',
        'name' => $this->_getParameterName($fieldName.'/end'),
        'value' => $end
      ],
      $this->formatDateTime(
        $end, Filter\Date::DATE_NO_TIME !== $this->_includeTime
      )
    );
  }

  /**
   * @param \Traversable|null $labels
   * @return \EmptyIterator|UI\Text\Translated\Collection|\Traversable
   */
  public function labels(\Traversable $labels = NULL) {
    if (NULL !== $labels) {
      $this->_labels = $labels;
    } elseif (NULL === $this->_labels) {
      if ($this->papaya()->request->isAdministration) {
        $this->_labels = new UI\Text\Translated\Collection(
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
   * @param int $timestamp
   * @param bool $includeTime
   *
   * @return string
   */
  private function formatDateTime($timestamp, $includeTime = TRUE) {
    if (0 === (int)$timestamp) {
      return '';
    }
    if ($includeTime) {
      return \date('Y-m-d H:i:s', $timestamp);
    }
    return \date('Y-m-d', $timestamp);
  }

  /**
   * If not mandatory allow the whole value as empty or each sub value.
   *
   * @return null|\Papaya\Filter
   */
  public function getFilter() {
    $filter = parent::getFilter();
    if ($this->getMandatory()) {
      return $filter;
    }
    return new Filter\LogicalOr(
      new Filter\AssociativeArray(
        [
          'start' => new Filter\EmptyValue(),
          'end' => new Filter\EmptyValue(),
          'mode' => new Filter\EmptyValue()
        ]
      ),
      $filter
    );
  }
}
