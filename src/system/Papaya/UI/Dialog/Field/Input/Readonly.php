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
namespace Papaya\UI\Dialog\Field\Input;

use Papaya\UI;
use Papaya\XML;

/**
 * A simple single line readonly input field with a caption.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Readonly extends UI\Dialog\Field\Input {
  /**
   * Initialize object, set caption and field name
   *
   * @param string|\Papaya\UI\Text $caption
   * @param string $name
   * @param mixed $default
   */
  public function __construct($caption, $name, $default = NULL) {
    parent::__construct($caption, $name, 0, $default);
  }

  /**
   * Append field and input output to DOM
   *
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
    $field = $this->_appendFieldTo($parent);
    $field->appendElement(
      'input',
      [
        'type' => $this->_type,
        'name' => $this->_getParameterName($this->getName()),
        'readonly' => 'yes'
      ],
      (string)$this->getCurrentValue()
    );
  }

  /**
   * Always return the provided default value
   *
   * @return mixed|null
   */
  public function getCurrentValue() {
    return $this->getDefaultValue();
  }
}
