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
/**
 * A textarea (multiline input) field, that will use the filter on each line
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Lines extends \Papaya\UI\Dialog\Field\Textarea {

  protected $_type = 'lines';

  public function __construct(
    $caption, $name, $lines = 10, $default = NULL, \Papaya\Filter $filter = NULL
  ) {
    parent::__construct(
      $caption,
      $name,
      $lines,
      $default,
      new \Papaya\Filter\Lines($filter)
    );
  }

  /**
   * Append field and textarea output to DOM
   *
   * @param \Papaya\XML\Element $parent
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    $field = $this->_appendFieldTo($parent);
    $field->appendElement(
      'textarea',
      array(
        'type' => $this->_type,
        'name' => $this->_getParameterName($this->getName()),
        'lines' => $this->_lineCount
      ),
      (string)$this->getCurrentValue()
    );
  }
}
