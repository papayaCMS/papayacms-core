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
use Papaya\XML;

/**
 * A hidden dialog field, this will be part of the dialog but not visible. The main
 * difference to the hiddenFields property of the dialog object is that this field changes the data
 * property.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Hidden extends UI\Dialog\Field {
  /**
   * Initialize object, field name, default value and filter
   *
   * @param string $name
   * @param int $default
   * @param Filter|null $filter
   */
  public function __construct($name, $default, Filter $filter = NULL) {
    $this->setName($name);
    $this->setDefaultValue($default);
    if (NULL !== $filter) {
      $this->setFilter($filter);
    }
  }

  /**
   * Append field and input output to DOM
   *
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
    $field = $parent->appendElement(
      'field',
      [
        'class' => $this->_getFieldClass()
      ]
    );
    if ('' !== $this->getId()) {
      $field->setAttribute('id', $this->getId());
    }
    $field->appendElement(
      'input',
      [
        'type' => 'hidden',
        'name' => $this->_getParameterName($this->getName())
      ],
      $this->getCurrentValue()
    );
  }
}
