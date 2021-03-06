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
namespace Papaya\Message\Dispatcher {

  use Papaya\BaseObject;
  use Papaya\Message;

  /**
   * Papaya Message Dispatcher PSR-3 implements the PHP-FIG interface for logging
   * defined in PSR-3.
   *
   * @package Papaya-Library
   * @subpackage Messages
   */
  class Collection
    extends BaseObject\Collection
    implements Message\Dispatcher {
    public function __construct() {
      parent::__construct(Message\Dispatcher::class);
    }

    /**
     * @param Message $message
     *
     * @return bool|void
     */
    public function dispatch(Message $message) {
      /** @var Message\Dispatcher $dispatcher */
      foreach ($this as $dispatcher) {
        $dispatcher->dispatch($message);
      }
    }
  }
}
