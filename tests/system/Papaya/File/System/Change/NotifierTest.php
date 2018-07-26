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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaFileSystemChangeNotifierTest extends \PapayaTestCase {

  /**
   * @covers \PapayaFileSystemChangeNotifier::__construct
   * @covers \PapayaFileSystemChangeNotifier::setTarget
   * @covers \PapayaFileSystemChangeNotifier::action
   */
  public function testConstructorWithScript() {
    $notifier = new \PapayaFileSystemChangeNotifier('/sample/script.php');
    $this->assertInstanceOf(
      \Papaya\File\System\Action\Script::class, $notifier->action()
    );
  }

  /**
   * @covers \PapayaFileSystemChangeNotifier::__construct
   * @covers \PapayaFileSystemChangeNotifier::setTarget
   * @covers \PapayaFileSystemChangeNotifier::action
   */
  public function testConstructorWithUrl() {
    $notifier = new \PapayaFileSystemChangeNotifier('http://example.tld/sample/script.php');
    $this->assertInstanceOf(
      \Papaya\File\System\Action\Url::class, $notifier->action()
    );
  }

  /**
   * @covers \PapayaFileSystemChangeNotifier::__construct
   * @covers \PapayaFileSystemChangeNotifier::setTarget
   * @covers \PapayaFileSystemChangeNotifier::action
   */
  public function testConstructorWithEmptyString() {
    $notifier = new \PapayaFileSystemChangeNotifier('');
    $this->assertNull(
      $notifier->action()
    );
  }

  /**
   * @covers \PapayaFileSystemChangeNotifier::action
   */
  public function testActionGetAfterSet() {
    $notifier = new \PapayaFileSystemChangeNotifier('');
    $notifier->action($action = $this->createMock(\PapayaFileSystemAction::class));
    $this->assertSame($action, $notifier->action());
  }

  public function testNotify() {
    $action = $this->createMock(\PapayaFileSystemAction::class);
    $action
      ->expects($this->once())
      ->method('execute')
      ->with(array('action' => 'A', 'file' => '/sample/file.png', 'path' => '/sample/'));
    $notifier = new \PapayaFileSystemChangeNotifier('');
    $notifier->action($action);
    $notifier->notify(\PapayaFileSystemChangeNotifier::ACTION_ADD, '/sample/file.png', '/sample/');
  }
}
