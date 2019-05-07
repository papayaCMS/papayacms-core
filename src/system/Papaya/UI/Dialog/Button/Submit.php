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

use Papaya\UI;
use Papaya\XML;

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
class Submit extends UI\Dialog\Button {
  /**
   * Button caption
   *
   * @var string|\Papaya\UI\Text
   */
  protected $_caption = 'Submit';
  /**
   * Button caption
   *
   * @var string|\Papaya\UI\Text
   */
  private $_hint = 'Submit';

  /**
   * Initialize object, set caption and alignment
   *
   * @param string|\Papaya\UI\Text $caption
   * @param int $align
   */
  public function __construct($caption, $align = UI\Dialog\Button::ALIGN_RIGHT) {
    parent::__construct($align);
    $this->_caption = $caption;
  }

  /**
   * Append button output to DOM
   *
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
    $parent->appendElement(
      'button',
      [
        'type' => 'submit',
        'align' => (UI\Dialog\Button::ALIGN_LEFT === $this->_align) ? 'left' : 'right',
        'hint' => $this->getHint()
      ],
      (string)$this->_caption
    );
  }

  /**
   * @param string|object $hint
   */
  public function setHint($hint) {
    \Papaya\Utility\Constraints::assertStringCastable($hint);
    $this->_hint = $hint;
  }

  /**
   * @return string $hint
   */
  public function getHint() {
    return (string)$this->_hint;
  }
}
