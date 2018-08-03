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
 * A simple submit button with a caption and without a name.
 *
 * Usage:
 *   $dialog->buttons()->add(new \Papaya\UI\Dialog\Button\Submit('Save'));
 *
 *   $dialog->buttons()->add(
 *     new \Papaya\UI\Dialog\Button\Submit(
 *       new \Papaya\UI\Text\Translated('Save')
 *     ),
 *     \Papaya\UI\Dialog\Button::ALIGN_LEFT
 *   );
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Submit extends \Papaya\UI\Dialog\Button {

  /**
   * Button caption
   *
   * @var string|\Papaya\UI\Text
   */
  protected $_caption = 'Submit';

  /**
   * Initialize object, set caption and alignment
   *
   * @param string|\Papaya\UI\Text $caption
   * @param integer $align
   */
  public function __construct($caption, $align = \Papaya\UI\Dialog\Button::ALIGN_RIGHT) {
    parent::__construct($align);
    $this->_caption = $caption;
  }

  /**
   * Append button output to DOM
   *
   * @param \Papaya\XML\Element $parent
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    $parent->appendElement(
      'button',
      array(
        'type' => 'submit',
        'align' => ($this->_align == \Papaya\UI\Dialog\Button::ALIGN_LEFT) ? 'left' : 'right'
      ),
      (string)$this->_caption
    );
  }
}
