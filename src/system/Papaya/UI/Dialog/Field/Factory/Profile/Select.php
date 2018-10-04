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
 * Field factory profiles for a generic select field.
 *
 * Each profile defines how a field {@see \Papaya\UI\Dialog\Field} is created for a specified
 * type. Here is an options subobject to provide data for the field configuration.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Select extends UI\Dialog\Field\Factory\Profile {
  /**
   * @see \Papaya\UI\Dialog\Field\Factory\Profile::getField()
   *
   * @return UI\Dialog\Field\Select
   *
   * @throws \Papaya\UI\Dialog\Field\Factory\Exception\InvalidOption
   */
  public function getField() {
    if (\is_array($this->options()->parameters) ||
      $this->options()->parameters instanceof \Traversable) {
      $elements = $this->options()->parameters;
    } else {
      $elements = [];
    }
    $field = $this->createField($elements);
    $field->setDefaultValue($this->options()->default);
    if ($hint = $this->options()->hint) {
      $field->setHint($hint);
    }
    return $field;
  }

  /**
   * Create field, own function so that child class can redefine the creation
   *
   * @param array|\Traversable $elements
   *
   * @return UI\Dialog\Field\Select
   *
   * @throws \Papaya\UI\Dialog\Field\Factory\Exception\InvalidOption
   */
  protected function createField($elements) {
    return new UI\Dialog\Field\Select(
      $this->options()->caption,
      $this->options()->name,
      $elements
    );
  }
}
