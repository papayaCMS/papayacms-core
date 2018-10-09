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
namespace Papaya\UI\Dialog\Field\Textarea;

use Papaya\Filter;
use Papaya\UI;
use Papaya\XML;

/**
 * A textarea (multiline input) field, that will be replaced with an RTE using JavaScript
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Richtext extends UI\Dialog\Field\Textarea {
  const RTE_DEFAULT = 'standard';

  const RTE_SIMPLE = 'simple';

  const RTE_INDIVIDUAL = 'individual';

  /**
   * @var string
   */
  private $_rteMode = self::RTE_DEFAULT;

  /**
   * Initialize object, set caption, field name and maximum length
   *
   * @param string|\Papaya\UI\Text $caption
   * @param string $name
   * @param int $lines
   * @param mixed $default
   * @param Filter|null $filter
   * @param int|string $rteMode
   */
  public function __construct(
    $caption,
    $name,
    $lines = 10,
    $default = NULL,
    Filter $filter = NULL,
    $rteMode = self::RTE_DEFAULT
  ) {
    parent::__construct($caption, $name, $lines, $default, $filter);
    $this->setRteMode($rteMode);
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
        'lines' => $this->_lineCount,
        'data-rte' => $this->_rteMode
      ],
      (string)$this->getCurrentValue()
    );
  }

  /**
   * The variant of the richtext editor is mostly defined by javascript
   * so we just need to store a mode and put it into the xml for further use.
   *
   * @param string $mode
   */
  public function setRteMode($mode) {
    $this->_rteMode = $mode;
  }

  /**
   * Read the rte mode used to define the js configuration.
   *
   * @return string
   */
  public function getRteMode() {
    return $this->_rteMode;
  }
}
