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

namespace Papaya\Ui\Dialog\Field;
/**
 * A simple single line input field with a caption.
 *
 * @package Papaya-Library
 * @subpackage Ui
 */
class Input extends \Papaya\Ui\Dialog\Field {

  /**
   * Field maximum input length
   *
   * @var integer
   */
  protected $_maximumLength = 0;

  /**
   * An input field is always an single line text input field.
   *
   * However here are variants and not all of them require special php logic. The
   * type is included in the xml so the xslt template can access it and add special handling like
   * css classes for defensive javascript.
   *
   * @var string
   */
  protected $_type = 'text';

  /**
   * Initialize object, set caption, field name and maximum length
   *
   * @param string|\Papaya\Ui\Text $caption
   * @param string $name
   * @param integer $length
   * @param mixed $default
   * @param \Papaya\Filter|NULL $filter
   */
  public function __construct(
    $caption,
    $name,
    $length = 1024,
    $default = NULL,
    \Papaya\Filter $filter = NULL
  ) {
    $this->setCaption($caption);
    $this->setName($name);
    $this->setMaximumLength($length);
    $this->setDefaultValue($default);
    if (NULL !== $filter) {
      $this->setFilter($filter);
    }
  }

  /**
   * Set the maximum field length of this element.
   *
   * @param integer $maximumLength
   */
  public function setMaximumLength($maximumLength) {
    \Papaya\Utility\Constraints::assertInteger($maximumLength);
    if ($maximumLength > 0) {
      $this->_maximumLength = $maximumLength;
    } else {
      $this->_maximumLength = -1;
    }
  }

  /**
   * Set the type of this input field.
   *
   * An input field is always an single line text input field. However here are variants and
   * not all of them require special php logic. The type is included in the xml so the xslt template
   * can access it and add special handling like css classes for defensive javascript.
   *
   * The method can uses by descendant classes, too.
   *
   * @param string $type
   */
  public function setType($type) {
    \Papaya\Utility\Constraints::assertString($type);
    \Papaya\Utility\Constraints::assertNotEmpty($type);
    $this->_type = $type;
  }

  /**
   * Read the type of this input field.
   *
   * @return string
   */
  public function getType() {
    return $this->_type;
  }

  /**
   * Append field and input ouptut to DOM
   *
   * @param \Papaya\Xml\Element $parent
   */
  public function appendTo(\Papaya\Xml\Element $parent) {
    $field = $this->_appendFieldTo($parent);
    $field->appendElement(
      'input',
      array(
        'type' => $this->getType(),
        'name' => $this->_getParameterName($this->getName()),
        'maxlength' => $this->_maximumLength
      ),
      $this->getCurrentValue()
    );
  }
}
