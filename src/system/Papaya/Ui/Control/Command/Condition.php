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

/**
* Abstract superclass for ui command condition, allow to specify conditions that hav to
* be fullfilled to execute the command.
*
* @package Papaya-Library
* @subpackage Ui
*/
abstract class PapayaUiControlCommandCondition extends \PapayaObject {

  /**
  * The command of the condition.
  *
  * @param \PapayaUiControlCommand
  */
  private $_command = NULL;

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
   * @param \PapayaUiControlCommand $command
   * @throws \LogicException
   * @return \PapayaUiControlCommand
   */
  public function command(\PapayaUiControlCommand $command = NULL) {
    if (isset($command)) {
      $this->_command = $command;
      $this->papaya($command->papaya());
    } elseif (is_null($this->_command)) {
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
    return isset($this->_command);
  }
}
