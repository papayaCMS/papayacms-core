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

namespace Papaya\Message\PHP;
/**
 * Papaya Message Hook Exception, capture exceptions and handle them
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
class Exception
  extends \Papaya\Message\PHP {

  /**
   * Create object and set values from erorr exception object
   *
   * @param \ErrorException $exception
   * @param \Papaya\Message\Context\Backtrace $trace
   */
  public function __construct(
    \ErrorException $exception,
    \Papaya\Message\Context\Backtrace $trace = NULL
  ) {
    parent::__construct();
    $this->setSeverity($exception->getSeverity());
    $this->_message = $exception->getMessage();
    $this
      ->_context
      ->append(
        is_null($trace)
          ? new \Papaya\Message\Context\Backtrace(0, $exception->getTrace())
          : $trace
      );
  }
}