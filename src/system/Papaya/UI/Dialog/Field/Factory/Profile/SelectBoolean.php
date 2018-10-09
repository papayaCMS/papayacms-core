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

use Papaya\UI;

/**
 * Field factory profiles for a field with two radio boxes displaying "yes" and "no"
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class SelectBoolean
  extends Select {
  /**
   * Create a select field with two elements displayed as radio boxes
   *
   * @param array|\Traversable $elements
   *
   * @return UI\Dialog\Field\Select
   *
   * @throws UI\Dialog\Field\Factory\Exception\InvalidOption
   */
  protected function createField($elements) {
    return new UI\Dialog\Field\Select\Radio(
      $this->options()->caption,
      $this->options()->name,
      new UI\Text\Translated\Collection(['no', 'yes'])
    );
  }
}
