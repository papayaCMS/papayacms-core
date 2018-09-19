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

/**
 * A field that output a message inside the dialog
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Information extends \Papaya\UI\Dialog\Field {
  /**
   * Information text
   *
   * @var string|\Papaya\UI\Text
   */
  protected $_text = '';

  /**
   * Message image
   *
   * @var string
   */
  protected $_image = '';

  /**
   * Create object and assign needed values
   *
   * @param string|\Papaya\UI\Text $text
   * @param string $image
   */
  public function __construct($text, $image = NULL) {
    $this->_text = $text;
    $this->_image = $image;
  }

  /**
   * Append message field to dialog xml dom
   *
   * @param \Papaya\XML\Element $parent
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    $field = $this->_appendFieldTo($parent);
    $message = $field->appendElement(
      'message', [], (string)$this->_text
    );
    $image = empty($this->_image) ? '' : $this->papaya()->images[$this->_image];
    if (!empty($image)) {
      $message->setAttribute('image', $image);
    }
  }
}
