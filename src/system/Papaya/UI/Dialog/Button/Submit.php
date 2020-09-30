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

use Papaya\BaseObject\Interfaces\StringCastable;
use Papaya\UI;
use Papaya\XML;
use Papaya\XML\Element as XMLElement;

/**
 * A simple submit button with a caption and without a name.
 *
 * Usage:
 *   $dialog->buttons()->add(new \Papaya\UI\Dialog\Button\Submit('Save'));
 *
 *   $dialog->buttons()->add(
 *     new \Papaya\UI\Dialog\Button\Submit(
 *       new StringCastable\Translated('Save')
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
   * @var string|StringCastable
   */
  protected $_caption = 'Submit';
  /**
   * Button hint
   *
   * @var string|StringCastable
   */
  private $_hint = '';

  /**
   * @var string
   */
  private $_image = '';


  /**
   * Initialize object, set caption and alignment
   *
   * @param string|StringCastable $caption
   * @param int $align
   */
  public function __construct($caption, $align = UI\Dialog\Button::ALIGN_RIGHT) {
    parent::__construct($align);
    $this->_caption = $caption;
  }

  /**
   * Append button output to DOM
   *
   * @param XMLElement $parent
   */
  public function appendTo(XMLElement $parent) {
    $image = (string)$this->getImage();
    $parent->appendElement(
      'button',
      [
        'type' => 'submit',
        'align' => (UI\Dialog\Button::ALIGN_LEFT === $this->_align) ? 'left' : 'right',
        'hint' => $this->getHint(),
        'image' => $image !== '' ? $image : NULL
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

  /**
   * @param string|object $image
   */
  public function setImage($image) {
    \Papaya\Utility\Constraints::assertStringCastable($image);
    $this->_image = $image;
  }

  /**
   * @return string $image
   */
  public function getImage() {
    return (string)$this->_image;
  }
}
