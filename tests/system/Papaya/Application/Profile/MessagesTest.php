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

namespace Papaya\Application\Profile;

require_once __DIR__.'/../../../../bootstrap.php';

class MessagesTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Application\Profile\Messages::createObject
   */
  public function testCreateObject() {
    $profile = new Messages();
    $messages = $profile->createObject($this->mockPapaya()->application());
    $this->assertInstanceOf(
      \Papaya\Message\Manager::class, $messages
    );
    $dispatchers = $this->readAttribute($messages, '_dispatchers');
    $this->assertInstanceOf(
      \Papaya\Message\Dispatcher\Template::class, $dispatchers[0]
    );
    $this->assertInstanceOf(
      \Papaya\Message\Dispatcher\Database::class, $dispatchers[1]
    );
    $this->assertInstanceOf(
      \Papaya\Message\Dispatcher\Wildfire::class, $dispatchers[2]
    );
    $this->assertInstanceOf(
      \Papaya\Message\Dispatcher\XHTML::class, $dispatchers[3]
    );
  }
}
