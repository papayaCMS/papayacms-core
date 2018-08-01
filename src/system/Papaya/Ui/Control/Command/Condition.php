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

namespace Papaya\Ui\Control\Command;
/**
 * Abstract superclass for ui command condition, allow to specify conditions that hav to
 * be fullfilled to execute the command.
 *
 * @package Papaya-Library
 * @subpackage Ui
 */
abstract class Condition extends \Papaya\Application\BaseObject {

  /**
   * The command of the condition.
   *
   * @param \Papaya\Ui\Control\Command
   */
  private $_command;

  /**
   * Validate needs to be implemented in chzild classes.
   *
   * @return boolean
   */
  abstract public function validate();

  /**
   * Assign a owner command to the condition
   *
   * If the owner is emtpy and exception is thrown.
   *
   *
   * @param \Papaya\Ui\Control\Command $command
   * @throws \LogicException
   * @return \Papaya\Ui\Control\Command
   */
  public function command(\Papaya\Ui\Control\Command $command = NULL) {
    if (NULL !== $command) {
      $this->_command = $command;
      $this->papaya($command->papaya());
    } elseif (NULL === $this->_command) {
      throw new \LogicException(
        sprintf(
          'LogicException: Instance of "%s" has no command assigned.',
          get_class($this)
        )
      );
    }
    return $this->_command;
  }

  /**
   * Validate if an command object is assigned
   *
   * @return boolean
   */
  public function hasCommand() {
    return NULL !== $this->_command;
  }
}
