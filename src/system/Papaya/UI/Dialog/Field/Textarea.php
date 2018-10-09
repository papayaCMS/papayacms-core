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
namespace Papaya\UI\Dialog\Field;

use Papaya\Filter;
use Papaya\UI;
use Papaya\Utility;
use Papaya\XML;

/**
 * A simple textarea (multiple line input) field
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Textarea extends UI\Dialog\Field {
  /**
   * Field lines
   *
   * @var int
   */
  protected $_lineCount = 0;

  /**
   * Initialize object, set caption, field name and maximum length
   *
   * @param string|\Papaya\UI\Text $caption
   * @param string $name
   * @param int $lines
   * @param mixed $default
   * @param Filter|null $filter
   */
  public function __construct(
    $caption, $name, $lines = 10, $default = NULL, Filter $filter = NULL
  ) {
    $this->setCaption($caption);
    $this->setName($name);
    $this->setLineCount($lines);
    $this->setDefaultValue($default);
    if (NULL !== $filter) {
      $this->setFilter($filter);
    }
  }

  /**
   * Set the line count of this element.
   *
   * @param int $lineCount
   */
  public function setLineCount($lineCount) {
    Utility\Constraints::assertInteger($lineCount);
    $this->_lineCount = $lineCount;
  }

  /**
   * Append field and textarea output to DOM
   *
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
    $field = $this->_appendFieldTo($parent);
    $field->appendElement(
      'textarea',
      [
        'type' => 'text',
        'name' => $this->_getParameterName($this->getName()),
        'lines' => $this->_lineCount
      ],
      (string)$this->getCurrentValue()
    );
  }
}
