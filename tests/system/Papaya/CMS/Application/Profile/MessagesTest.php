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

namespace Papaya\CMS\Application\Profile {

  require_once __DIR__.'/../../../../../bootstrap.php';

  use Papaya\Database\Connection as DatabaseConnection;
  use Papaya\Database\Connector as DatabaseConnector;
  use Papaya\Database\Exception\ConnectionFailed;
  use Papaya\Database\Manager as DatabaseManager;
  use Papaya\Message\Dispatcher;
  use Papaya\Message\Manager as MessageManager;
  use Papaya\CMS\Plugin\Loader as PluginLoader;
  use Papaya\Plugin\LoggerFactory;
  use Papaya\CMS\Plugin\Types as PluginTypes;
  use Papaya\TestFramework\TestCase;

  /**
   * @covers \Papaya\CMS\Application\Profile\Messages
   */
  class MessagesTest extends TestCase {

    public function testCreateObject() {
      $profile = new Messages();
      $messages = $profile->createObject($this->mockPapaya()->application());
      $this->assertInstanceOf(
        MessageManager::class, $messages
      );
      $dispatchers = iterator_to_array($messages);
      $this->assertInstanceOf(
        Dispatcher\Template::class, $dispatchers[0]
      );
      $this->assertInstanceOf(
        Dispatcher\Database::class, $dispatchers[1]
      );
      $this->assertInstanceOf(
        Dispatcher\Wildfire::class, $dispatchers[2]
      );
      $this->assertInstanceOf(
        Dispatcher\XHTML::class, $dispatchers[3]
      );
    }

    public function testCreateObjectCapturingDatabaseConnectionError() {
      $databaseConnector = $this->createMock(DatabaseConnector::class);
      $databaseConnector
        ->expects($this->once())
        ->method('connect')
        ->willThrowException($this->createMock(ConnectionFailed::class));
      $database = $this->createMock(DatabaseManager::class);
      $database
        ->method('getConnector')
        ->willReturn($databaseConnector);

      $profile = new Messages();
      $messages = $profile->createObject(
        $this->mockPapaya()->application(
          [
            'options' => $this->mockPapaya()->options(['PAPAYA_LOG_ENABLE_EXTERNAL' => TRUE]),
            'database' => $database
          ]
        )
      );
      $this->assertInstanceOf(
        MessageManager::class, $messages
      );
      $dispatchers = iterator_to_array($messages);
      $this->assertCount(4, $dispatchers);
    }

    public function testCreateObjectWithDispatcherPlugins() {
      $databaseConnector = $this->createMock(DatabaseConnector::class);
      $databaseConnector
        ->expects($this->once())
        ->method('connect')
        ->willReturn($this->createMock(DatabaseConnection::class));
      $database = $this->createMock(DatabaseManager::class);
      $database
        ->method('getConnector')
        ->willReturn($databaseConnector);

      $loggerFactory = $this->createMock(LoggerFactory::class);
      $loggerFactory
        ->method('createLogger')
        ->willReturn($loggerPlugin = $this->createMock(Dispatcher::class));
      $plugins = $this->createMock(PluginLoader::class);
      $plugins
        ->expects($this->once())
        ->method('withType')
        ->with(PluginTypes::LOGGER)
        ->willReturn([$loggerFactory]);

      $profile = new Messages();
      $messageManager = $profile->createObject(
        $this->mockPapaya()->application(
          [
            'options' => $this->mockPapaya()->options(['PAPAYA_LOG_ENABLE_EXTERNAL' => TRUE]),
            'database' => $database,
            'plugins' => $plugins
          ]
        )
      );
      $this->assertInstanceOf(
        MessageManager::class, $messageManager
      );
      $dispatchers = iterator_to_array($messageManager);
      $this->assertSame(
        $loggerPlugin, $dispatchers[4]
      );
    }
  }
}
