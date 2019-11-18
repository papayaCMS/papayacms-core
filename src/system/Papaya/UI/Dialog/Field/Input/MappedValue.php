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
namespace Papaya\UI\Dialog\Field\Input {

  use Papaya\UI;
  use Papaya\XML\Element as XMLElement;

  /**
   * A single line input for that displays the maximum character left to input using javascript.
   *
   * @package Papaya-Library
   * @subpackage UI
   */
  class MappedValue extends UI\Dialog\Field\Input {

    /**
     * @var MappedValue\Callbacks
     */
    private $_callbacks;

    public function callbacks(MappedValue\Callbacks $callbacks = NULL) {
      if (NULL !== $callbacks) {
        $this->_callbacks = $callbacks;
      } elseif (NULL === $this->_callbacks) {
        $this->_callbacks = new MappedValue\Callbacks();
      }
      return $this->_callbacks;
    }

    public function getCurrentValue() {
      $name = $this->getName();
      if (!empty($name) && ($dialog = $this->getDialog())) {
        if (!$this->getDisabled() && $dialog->parameters()->has($name)) {
          $value = $dialog->parameters()->get($name);
          if (isset($this->callbacks()->mapFromDisplay)) {
            return $this->callbacks()->mapFromDisplay($value);
          }
          return $value;
        }
        if ($dialog->data()->has($name) && NULL !== ($value = $dialog->data()->get($name))) {
          return $value;
        }
      }
      return $this->getDefaultValue();
    }

    public function appendTo(XMLElement $parent) {
      $value = $this->getCurrentValue();
      if (isset($this->callbacks()->mapToDisplay)) {
        $value = $this->callbacks()->mapToDisplay($value);
      }
      $field = $this->_appendFieldTo($parent);
      $field->appendElement(
        'input',
        [
          'type' => $this->getType(),
          'name' => $this->_getParameterName($this->getName()),
          'maxlength' => $this->_maximumLength
        ],
        $value
      );
    }
  }
}
