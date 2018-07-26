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

use Papaya\Application\Profile\Messages;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaApplicationProfileMessagesTest extends PapayaTestCase {

  /**
  * @covers Messages::createObject
  */
  public function testCreateObject() {
    $profile = new Messages();
    $messages = $profile->createObject($this->mockPapaya()->application());
    $this->assertInstanceOf(
      \PapayaMessageManager::class, $messages
    );
    $dispatchers = $this->readAttribute($messages, '_dispatchers');
    $this->assertInstanceOf(
      \PapayaMessageDispatcherTemplate::class, $dispatchers[0]
    );
    $this->assertInstanceOf(
      \PapayaMessageDispatcherDatabase::class, $dispatchers[1]
    );
    $this->assertInstanceOf(
      \PapayaMessageDispatcherWildfire::class, $dispatchers[2]
    );
    $this->assertInstanceOf(
      \PapayaMessageDispatcherXhtml::class, $dispatchers[3]
    );
  }
}
