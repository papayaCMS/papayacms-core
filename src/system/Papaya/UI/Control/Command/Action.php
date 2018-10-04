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
namespace Papaya\UI\Control\Command;

use Papaya\Request;
use Papaya\UI;
use Papaya\XML;

/**
 * A command that executes an action depending on a specific set of parameters
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Action extends UI\Control\Command {
  /**
   * Dialog object
   *
   * @var \Papaya\UI\Dialog
   */
  private $_data;

  /**
   * Dialog event callbacks
   *
   * @var \Papaya\UI\Control\Command\Dialog\Callbacks
   */
  private $_callbacks;

  /**
   * Execute command and append result to output xml
   *
   * @param XML\Element $parent
   *
   * @return XML\Element
   */
  public function appendTo(XML\Element $parent) {
    if ($this->data()->validate()) {
      $this->callbacks()->onValidationSuccessful($this, $parent);
    } else {
      $this->callbacks()->onValidationFailed($this, $parent);
    }
    return $parent;
  }

  /**
   * Getter/Setter to the validated parameters data subobject.
   *
   * @param Request\Parameters\Validator $data
   *
   * @return Request\Parameters\Validator|\Papaya\UI\Dialog
   */
  public function data(Request\Parameters\Validator $data = NULL) {
    if (NULL !== $data) {
      $this->_data = $data;
    } elseif (NULL === $this->_data) {
      $this->_data = $this->_createData();
    }
    return $this->_data;
  }

  /**
   * Create parameters validator using the "getDefintion()" callback
   *
   * @param array|null $definitions
   *
   * @return Request\Parameters\Validator
   */
  protected function _createData(array $definitions = NULL) {
    return new Request\Parameters\Validator(
      NULL !== $definitions ? $definitions : $this->callbacks()->getDefinition(),
      $this->parameters()
    );
  }

  /**
   * Getter/Setter for the callbacks object
   *
   * @param Action\Callbacks $callbacks
   *
   * @return Action\Callbacks
   */
  public function callbacks(Action\Callbacks $callbacks = NULL) {
    if (NULL !== $callbacks) {
      $this->_callbacks = $callbacks;
    } elseif (NULL === $this->_callbacks) {
      $this->_callbacks = new Action\Callbacks();
    }
    return $this->_callbacks;
  }
}
