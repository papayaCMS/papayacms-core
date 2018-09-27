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
namespace Papaya\Message;

use Papaya\Application;

/**
 * Papaya Message Exception, message object representing a php exception. This allows to convert
 * any exception into an error log message.
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
class Sandbox
  implements Application\Access {
  use Application\Access\Aggregation;

  /**
   * @var callable
   */
  private $_callback;

  /**
   * Create object and set values from exception object
   *
   * @param \Callable $callback
   */
  public function __construct(callable $callback) {
    $this->_callback = $callback;
  }

  /**
   * invoke the callback, return the result. If an exception occurs, dispatch it as an
   * message and return NULL.
   *
   * @param mixed,... $argument
   *
   * @return mixed
   */
  public function __invoke(...$arguments) {
    $result = NULL;
    try {
      $callback = $this->_callback;
      $result = $callback(...$arguments);
    } /** @noinspection PhpRedundantCatchClauseInspection */ catch (
      \ErrorException $e
    ) {
      $this->papaya()->messages->dispatch(new PHP\Exception($e));
    } catch (\Exception $e) {
      $this->papaya()->messages->dispatch(new Exception($e));
    }
    return $result;
  }
}
