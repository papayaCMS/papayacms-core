<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\UI\Dialog\Field\Input {

  use Papaya\UI;
  use Papaya\XML\Element as XMLElement;

  /**
   * A single line input for that displays the maximum character left to input using javascript.
   *
   * @package Papaya-Library
   * @subpackage UI
   */
  class MultipleValues extends UI\Dialog\Field\Input {

    private $_separator = ',';

    public function setSeparator($separator) {
      $this->_separator = $separator;
    }

    public function getCurrentValue() {
      $name = $this->getName();
      if (!empty($name) && ($dialog = $this->getDialog())) {
        if (!$this->getDisabled() && $dialog->parameters()->has($name)) {
          return array_filter(
            array_map(
              static function($value) {
                return trim($value);
              },
              explode(',', $dialog->parameters()->get($name, ''))
            ),
            static function($value) {
              return !empty($value);
            }
          );
        }
        if ($dialog->data()->has($name) && NULL !== ($value = $dialog->data()->get($name))) {
          return $value;
        }
      }
      return $this->getDefaultValue();
    }

    public function appendTo(XMLElement $parent) {
      $value = $this->getCurrentValue();
      if (!is_array($value)) {
        $value = empty($value) ? [] : [$value];
      }
      $field = $this->_appendFieldTo($parent);
      $field->appendElement(
        'input',
        [
          'type' => $this->getType(),
          'name' => $this->_getParameterName($this->getName()),
          'maxlength' => $this->_maximumLength
        ],
        implode($this->_separator, $value)
      );
    }
  }
}
