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
use Papaya\Utility;
use Papaya\XML;

/**
 * A command that executes a list of other commands. This can be used to combine separate commands
 * into a single one.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Collection
  extends UI\Control\Command
  implements \ArrayAccess, \Countable, \IteratorAggregate {
  /**
   * List of commands
   *
   * @var array
   */
  private $_commands = [];

  /**
   * Create object, assign all arguments as commands to the internal list.
   *
   * @param \Papaya\UI\Control\Command ...$commands
   */
  public function __construct(UI\Control\Command ...$commands) {
    foreach ($commands as $command) {
      $this->offsetSet(NULL, $command);
    }
  }

  /**
   * Execute commands and append result to output xml
   *
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
    /** @var UI\Control\Command $command */
    foreach ($this->_commands as $command) {
      if ($command->validateCondition() &&
        $command->validatePermission()) {
        $command->appendTo($parent);
      }
    }
  }

  /**
   * Overload owner method to set owner on all commands, too.
   *
   * @param Request\Parameters\Access|UI\Control\Interactive $owner
   *
   * @return Request\Parameters\Access
   */
  public function owner(Request\Parameters\Access $owner = NULL) {
    Utility\Constraints::assertInstanceOf(UI\Control\Interactive::class, $owner);
    if (NULL !== $owner) {
      /** @var UI\Control\Command $command */
      foreach ($this->_commands as $command) {
        $command->owner($owner);
      }
    }
    return parent::owner($owner);
  }

  /**
   * ArrayAccess interface: validate if command with the offset is set.
   *
   * @param int $offset
   *
   * @return bool
   */
  public function offsetExists($offset): bool {
    return isset($this->_commands[$offset]);
  }

  /**
   * ArrayAccess interface: get command at given offset.
   *
   * @param int $offset
   *
   * @return UI\Control\Command
   */
  public function offsetGet($offset) {
    return $this->_commands[$offset];
  }

  /**
   * ArrayAccess interface: add/replace command
   *
   * @param int $offset
   * @param UI\Control\Command $command
   *
   * @throws \UnexpectedValueException
   */
  public function offsetSet($offset, $command) {
    if ($command instanceof UI\Control\Command) {
      $this->_commands[$offset] = $command;
      $this->_commands = \array_values($this->_commands);
    } else {
      throw new \UnexpectedValueException(
        \sprintf(
          'Expected instance of "Papaya\UI\Control\Command" but "%s" was given.',
          \is_object($command) ? \get_class($command) : \gettype($command)
        )
      );
    }
  }

  /**
   * ArrayAccess interface: remove command at given offset.
   *
   * @param int $offset
   */
  public function offsetUnset($offset) {
    unset($this->_commands[$offset]);
    $this->_commands = \array_values($this->_commands);
  }

  /**
   * Countable interface: get command count.
   *
   * @return int
   */
  public function count(): int {
    return \count($this->_commands);
  }

  /**
   * IteratorAggregate interface: create iterator for commands
   *
   * @return \ArrayIterator
   */
  public function getIterator(): \Traversable {
    return new \ArrayIterator($this->_commands);
  }
}
