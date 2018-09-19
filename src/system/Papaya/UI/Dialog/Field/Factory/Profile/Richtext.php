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
namespace Papaya\UI\Dialog\Field\Factory\Profile;

/**
 * Field factory profiles for a default rte field.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Richtext extends \Papaya\UI\Dialog\Field\Factory\Profile {
  /**
   * @see \Papaya\UI\Dialog\Field\Factory\Profile::getField()
   *
   * @return \Papaya\UI\Dialog\Field\Textarea\Richtext
   *
   * @throws \Papaya\UI\Dialog\Field\Factory\Exception\InvalidOption
   */
  public function getField() {
    $field = new \Papaya\UI\Dialog\Field\Textarea\Richtext(
      $this->options()->caption,
      $this->options()->name,
      (int)$this->options()->parameters,
      $this->options()->default,
      $this->options()->validation
    );
    if ($hint = $this->options()->hint) {
      $field->setHint($hint);
    }
    return $field;
  }
}
