<?php
/*
 * papaya CMS
 *
 * @copyright 2000-2020 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\UI\ListView\Column {

  use Papaya\Filter\ArrayElement;
  use Papaya\Request;
  use Papaya\Request\Parameters\Name as ParameterName;
  use Papaya\UI;
  use Papaya\UI\ListView\Column;
  use Papaya\Utility\Arrays;
  use Papaya\XML\Element as XMLElement;

  class SortableColumn extends Column implements Request\Parameters\Access {

    use Request\Parameters\Access\Integration;

    const UNSORTED = 'unsorted';
    const SORTED_ASCENDING = 'asc';
    const SORTED_DESCENDING = 'desc';

    /**
     * @var UI\Reference
     */
    private $_reference;
    /**
     * @var ParameterName
     */
    private $_parameterName;
    /**
     * @var string
     */
    private $_columnName;
    /**
     * @var mixed
     */
    private $_parameterFilter;

    private $_defaultColumn = '0';

    private $_defaultSort = self::SORTED_ASCENDING;

    public function __construct($caption, $parameterName, $columnName = '', $align = UI\Option\Align::LEFT) {
      parent::__construct($caption, $align);
      $this->_parameterName = new ParameterName($parameterName);
      $this->_parameterFilter =  new ArrayElement([self::UNSORTED, self::SORTED_ASCENDING, self::SORTED_DESCENDING]);
      $this->_columnName = $columnName;
    }

    /**
     * Append column xml to parent node.
     *
     * @param XMLElement $parent
     */
    public function appendTo(XMLElement $parent) {
      $sortValues = [
        self::UNSORTED => 'none',
        self::SORTED_ASCENDING => 'asc',
        self::SORTED_DESCENDING => 'desc',
      ];
      $nextSort = $this->getSort() === self::SORTED_ASCENDING
        ? $sortValues[self::SORTED_DESCENDING] : $sortValues[self::SORTED_ASCENDING];
      $reference = $this->reference();
      if ('' !== ($columnName = $this->getColumnName())) {
        $parameters = $reference->getParameters();
        unset($parameters[$this->_parameterName]);
        $parameters[$this->_parameterName] =  [$columnName => $nextSort];
        $reference->setParameters($parameters);
      }
      $parent->appendElement(
        'col',
        [
          'align' => UI\Option\Align::getString($this->_align),
          'href' => $reference,
          'sort' => Arrays::get($sortValues, $this->getSort(), 'none')
        ],
        (string)$this->_caption
      );
    }

    private function getColumnName() {
      if (empty($this->_columnName) && $this->hasCollection()) {
        $this->_columnName = (string)$this->collection()->indexOf($this);
      }
      return $this->_columnName;
    }

    public function isSortColumn() {
      return (
        ('' !== ($columnName = $this->getColumnName())) &&
        $this->parameters()->getGroup($this->_parameterName)->has($columnName)
      );
    }

    /**
     * @return string
     */
    public function getSort() {
      if ('' !== ($columnName = $this->getColumnName())) {
        if ($this->parameters()->has($this->_parameterName)) {
          return $this->parameters()->getGroup($this->_parameterName)->get($columnName, self::UNSORTED, $this->_parameterFilter);
        }
        if ($columnName === $this->_defaultColumn) {
          return $this->_defaultSort;
        }
      }
      return self::UNSORTED;
    }

    public function setDefaultSort($columnName, $sort = self::SORTED_ASCENDING) {
       $this->_defaultColumn = $columnName;
       $this->_defaultSort = $sort;
    }

    /**
     * Getter/Setter for the owner listview
     *
     * @return object|UI\ListView
     */
    public function getListView() {
      return $this->collection()->owner();
    }

    /**
     * Getter/Setter for the reference subobject, if not explicit set. The reference from the collection
     * is cloned or a new one is created (if no collection is available).
     *
     * @param UI\Reference $reference
     *
     * @return UI\Reference
     */
    public function reference(UI\Reference $reference = NULL) {
      if (NULL !== $reference) {
        $this->_reference = $reference;
      } elseif (NULL === $this->_reference) {
        if ($this->hasCollection()) {
          $this->_reference = clone $this->getListView()->reference();
        } else {
          $this->_reference = new UI\Reference();
          $this->_reference->papaya($this->papaya());
        }
      }
      return $this->_reference;
    }
  }
}
