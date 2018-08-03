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
 * Field factory profiles for a select field that translates the elements of the given list.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class SelectTranslated
  extends Select {

  /**
   * Wrap elements in a string so they get translated
   *
   * @param array|\Traversable $elements
   * @return \Papaya\UI\Dialog\Field\Select
   * @throws \Papaya\UI\Dialog\Field\Factory\Exception\InvalidOption
   */
  protected function createField($elements) {
    return new \Papaya\UI\Dialog\Field\Select(
      $this->options()->caption,
      $this->options()->name,
      new \Papaya\UI\Text\Translated\Collection($elements)
    );
  }
}
