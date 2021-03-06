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
namespace Papaya\UI\Control;

use Papaya\Request;

/**
 * Abstract superclass for ui commands, like executing a dialog.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
abstract class Command extends Interactive {
  /**
   * A permission that is validated for the current administration user,
   * before executing the command
   *
   * @var null|array|int
   */
  private $_permission;

  /**
   * A condition that is validated, before executing the command
   *
   * @var null|true|Command\Condition
   */
  private $_condition;

  /**
   * The owner of the command. This is where the command gets it parameters from.
   *
   * @param Interactive
   */
  private $_owner;

  /**
   * Validate if the command has to be executed. Can return a boolean or throw an exception.
   *
   * @return bool
   */
  public function validateCondition() {
    $condition = $this->condition();
    return $condition->validate();
  }

  /**
   * Condition can be used to validate if an command can be executed.
   *
   * @param Command\Condition $condition
   *
   * @return Command\Condition
   */
  public function condition(Command\Condition $condition = NULL) {
    if (NULL !== $condition) {
      $this->_condition = $condition;
      $this->_condition->command($this);
    } elseif (NULL === $this->_condition) {
      $this->_condition = $this->createCondition();
    }
    return $this->_condition;
  }

  /**
   * The default condition is just the boolean value TRUE encapsulated in an object.
   *
   * @return Command\Condition
   */
  public function createCondition() {
    return new Command\Condition\Value(TRUE);
  }

  /**
   * Validate the assigned permission.
   *
   * @throws \UnexpectedValueException
   *
   * @return bool
   */
  public function validatePermission() {
    if ($permission = $this->permission()) {
      if (\is_array($permission) && 2 === \count($permission)) {
        $user = $this->papaya()->administrationUser;
        return $user->hasPerm($permission[1], $permission[0]);
      }
      if (\is_int($permission)) {
        $user = $this->papaya()->administrationUser;
        return $user->hasPerm($permission);
      }
      throw new \UnexpectedValueException(
        'UnexpectedValueException: Invalid permission value.'
      );
    }
    return TRUE;
  }

  /**
   * Getter/Setter for the permission
   *
   * @param int $permission
   *
   * @return null|array|int
   */
  public function permission($permission = NULL) {
    if (NULL !== $permission) {
      $this->_permission = $permission;
    }
    return $this->_permission;
  }

  /**
   * Assign a owner control to the command, the command reads parameters and the application
   * object from the owner.
   *
   * If the owner is emtpy and exception is thrown.
   *
   * @throws \LogicException
   *
   * @param Request\Parameters\Access|null $owner
   *
   * @return Request\Parameters\Access
   */
  public function owner(Request\Parameters\Access $owner = NULL) {
    if (NULL !== $owner) {
      $this->_owner = $owner;
      $this->papaya($owner->papaya());
    } elseif (NULL === $this->_owner) {
      throw new \LogicException(
        \sprintf(
          'LogicException: Instance of "%s" has no owner assigned.',
          \get_class($this)
        )
      );
    }
    return $this->_owner;
  }

  /**
   * Validate if an owner object is assigned
   *
   * @return bool
   */
  public function hasOwner() {
    return NULL !== $this->_owner;
  }

  /**
   * Get/Set parameter handling method. This will be used to define the parameter sources.
   *
   * If an owner is available, its parameterMethod function will be used.
   *
   * @param int $method
   *
   * @return int
   */
  public function parameterMethod($method = NULL) {
    if ($this->hasOwner()) {
      $method = $this->owner()->parameterMethod($method);
    }
    return parent::parameterMethod($method);
  }

  /**
   * Get/Set the parameter group name.
   *
   * This puts/expects all parameters into/in a parameter group.
   * If an owner is available, its parameterGroup function will be used.
   *
   * @param string|null $groupName
   *
   * @return string|null
   */
  public function parameterGroup($groupName = NULL) {
    if ($this->hasOwner()) {
      $groupName = $this->owner()->parameterGroup($groupName);
    }
    return parent::parameterGroup($groupName);
  }

  /**
   * Access request parameters
   *
   * This method gives you access to request parameters.
   * If an owner is available, its parameters function will be used.
   *
   * @param Request\Parameters $parameters
   *
   * @return Request\Parameters
   */
  public function parameters(Request\Parameters $parameters = NULL) {
    if ($this->hasOwner()) {
      $parameters = $this->owner()->parameters($parameters);
    }
    return parent::parameters($parameters);
  }
}
