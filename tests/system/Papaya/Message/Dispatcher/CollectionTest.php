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

namespace Papaya\Message\Dispatcher {

  use InvalidArgumentException;
  use Papaya\Message\Dispatcher;
  use Papaya\TestFramework\TestCase;
  use Papaya\Message;

  /**
   * @covers \Papaya\Message\Dispatcher\Collection
   */
  class CollectionTest extends TestCase {

    public function testDispatchesMessageToEach() {
      $message = $this->createMock(Message::class);

      $one = $this->createMock(Dispatcher::class);
      $one
        ->expects($this->once())
        ->method('dispatch')
        ->with($message);
      $two = $this->createMock(Dispatcher::class);
      $two
        ->expects($this->once())
        ->method('dispatch')
        ->with($message);

      $group = new Collection();
      $group->add($one)->add($two);
      $group->dispatch($message);
    }

    public function testAddInvalidDispatcherExpectingException() {
      $group = new Collection();
      $this->expectException(InvalidArgumentException::class);
      $group->add(new \stdClass());
    }
  }
}
