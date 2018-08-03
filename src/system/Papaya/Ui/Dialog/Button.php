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

namespace Papaya\Ui\Dialog;
/**
 * Superclass for dialog buttons
 *
 * A button can be a simple text button or an image.
 *
 * @package Papaya-Library
 * @subpackage Ui
 */
abstract class Button extends \Papaya\Ui\Dialog\Element {

  /**
   * Button alignment constant: right (default)
   */
  const ALIGN_RIGHT = 0;

  /**
   * Button alignment constant: left
   */
  const ALIGN_LEFT = 1;

  /**
   * Button alignment
   *
   * @var integer
   */
  protected $_align = self::ALIGN_RIGHT;

  /**
   * Initialize button object and set alignment
   *
   * @param integer $align
   */
  public function __construct($align = self::ALIGN_RIGHT) {
    $this->setAlign($align);
  }

  /**
   * Set button alignment
   *
   * @param integer $align
   */
  public function setAlign($align) {
    $this->_align = $align;
  }
}
