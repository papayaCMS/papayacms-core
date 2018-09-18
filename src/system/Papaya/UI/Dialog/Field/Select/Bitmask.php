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

namespace Papaya\UI\Dialog\Field\Select;

/**
 * A selection field displayed as checkboxes, mutiple values can be selected.
 *
 * The actual value is a bitmask, each checkbox represents on possible bit of the bitmask.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Bitmask extends \Papaya\UI\Dialog\Field\Select {
  /**
   * type of the select control, used in the xslt template
   *
   * @var string
   */
  protected $_type = 'checkboxes';

  /**
   * Determine if the option is selected using the current value and the option value.
   *
   * @param mixed $currentValue
   * @param string $optionValue
   * @return bool|int
   */
  protected function _isOptionSelected($currentValue, $optionValue) {
    return (int)$currentValue & (int)$optionValue;
  }

  /**
   * If the values are set, it is nessessary to create a filter based on the values.
   */
  protected function _createFilter() {
    $values = $this->getValues();
    if ($values instanceof \RecursiveIterator) {
      $values = \iterator_to_array(new \RecursiveIteratorIterator($values));
    } elseif ($values instanceof \Traversable) {
      $values = \iterator_to_array($values);
    }
    return new \Papaya\Filter\Bitmask(\array_keys($values));
  }

  /**
   * Always onvert the default value to integer
   *
   * @see \Papaya\UI\Dialog\Field::getDefaultValue()
   */
  public function getDefaultValue() {
    return (int)parent::getDefaultValue();
  }

  /**
   * Get the current field value.
   *
   * If the dialog object has a matching paremeter it is used. Otherwise the data object of the
   * dialog is checked and used.
   *
   * If neither dialog parameter or data is available, the default value is returned.
   *
   * @return mixed
   */
  public function getCurrentValue() {
    $name = $this->getName();
    if ($this->hasCollection() &&
      $this->collection()->hasOwner() &&
      !empty($name)) {
      if ($this->collection()->owner()->parameters()->has($name)) {
        $bits = $this->collection()->owner()->parameters()->get($name);
        $bitmask = 0;
        foreach ($bits as $bit) {
          if (\array_key_exists($bit, $this->_values)) {
            $bitmask |= (int)$bit;
          }
        }
        return $bitmask;
      } elseif ($this->collection()->owner()->isSubmitted()) {
        return 0;
      }
    }
    return parent::getCurrentValue();
  }
}
