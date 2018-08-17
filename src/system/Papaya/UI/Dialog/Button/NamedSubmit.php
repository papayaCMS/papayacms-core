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

namespace Papaya\UI\Dialog\Button;
/**
 * A named submit button sets a value in the dialog data if it was "clicked".
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class NamedSubmit extends Submit {

  /**
   * Button name
   *
   * @var string
   */
  protected $_name = '';

  /**
   * Button value
   *
   * @var string
   */
  protected $_value = '';

  /**
   * Initialize object, set caption and alignment
   *
   * @param string|\Papaya\UI\Text $caption
   * @param string $name
   * @param int|float|boolean|string $value
   * @param int $align
   */
  public function __construct(
    $caption, $name, $value = '1', $align = \Papaya\UI\Dialog\Button::ALIGN_RIGHT
  ) {
    parent::__construct($caption, $align);
    \Papaya\Utility\Constraints::assertString($name);
    \Papaya\Utility\Constraints::assertNotEmpty($name);
    \Papaya\Utility\Constraints::assertNotEmpty($value);
    $this->_name = $name;
    $this->_value = $value;
  }

  /**
   * Append button ouptut to DOM
   *
   * @param \Papaya\XML\Element $parent
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    $parent->appendElement(
      'button',
      array(
        'type' => 'submit',
        'align' => ($this->_align == \Papaya\UI\Dialog\Button::ALIGN_LEFT) ? 'left' : 'right',
        'name' => $this->_getParameterName(array($this->_name, $this->_value))
      ),
      (string)$this->_caption
    );
  }

  /**
   * If the button was clicked, put the value in the dialog data.
   *
   * The function checks for a existing parameter 'name_value'. The value of the parameter is not
   * used because it is the caption.
   *
   * @return boolean
   */
  public function collect() {
    if (parent::collect()) {
      $parameterName = $this->_getParameterName(array($this->_name, $this->_value), FALSE);
      if ($this->collection()->owner()->parameters()->has($parameterName)) {
        $this->collection()->owner()->data()->set($this->_name, $this->_value);
        return TRUE;
      }
    }
    return FALSE;
  }
}
