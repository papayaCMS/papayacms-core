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

namespace Papaya\Ui\Dialog\Field\Factory\Profile;
/**
 * Field factory profile for a "captcha" field.
 *
 * @package Papaya-Library
 * @subpackage Ui
 */
class Captcha extends \Papaya\Ui\Dialog\Field\Factory\Profile {

  /**
   * Create a color input field
   *
   * @see \Papaya\Ui\Dialog\Field\Input\Color
   * @see \Papaya\Ui\Dialog\Field\Factory\Profile::getField()
   * @throws \Papaya\Ui\Dialog\Field\Factory\Exception\InvalidOption
   */
  public function getField() {
    $field = new \Papaya\Ui\Dialog\Field\Input\Captcha(
      $this->options()->caption,
      $this->options()->name,
      empty($this->options()->parameters) ? 'captcha' : $this->options()->parameters
    );
    return $field;
  }
}
