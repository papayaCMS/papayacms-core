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

namespace Papaya\Message\Dispatcher\Wildfire;
require_once __DIR__.'/../../../../../bootstrap.php';

/**
 * @covers \Papaya\Message\Dispatcher\Wildfire\Handler
 */
class HandlerTest extends \Papaya\TestCase {

  private $_collectedHeaders = array();

  public function testConstructorWithInvalidCallbackExpectingException() {
    $this->expectException(\InvalidArgumentException::class);
    new Handler(NULL);
  }

  public function testSendInitialization() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new Handler($callback);
    $handler->resetCounters();
    $handler->sendInitialization(Handler::HEADER_MAIN);
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3'
      ),
      $this->_collectedHeaders
    );
    $this->assertEquals(
      array(
        Handler::HEADER_MAIN => 1,
        Handler::HEADER_CONSOLE => 0,
        Handler::HEADER_DUMP => 0,
        Handler::HEADER_DATA => 0
      ),
      $handler->getCounters()
    );
  }

  public function testSendMessage() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new Handler($callback);
    $handler->resetCounters();
    $handler->sendMessage('Hallo Welt');
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1',
        'X-Wf-1-1-1-1: 29|[{"Type":"LOG"},"Hallo Welt"]|'
      ),
      $this->_collectedHeaders
    );
  }

  public function testSendMessageWithTypeParameter() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new Handler($callback);
    $handler->resetCounters();
    $handler->sendMessage('Hallo Welt', 'INFO');
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1',
        'X-Wf-1-1-1-1: 30|[{"Type":"INFO"},"Hallo Welt"]|'
      ),
      $this->_collectedHeaders
    );
  }

  public function testSendMessageWithLabelParameter() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new Handler($callback);
    $handler->resetCounters();
    $handler->sendMessage('Hallo Welt', 'INFO', 'Sample');
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1',
        'X-Wf-1-1-1-1: 47|[{"Type":"INFO","Label":"Sample"},"Hallo Welt"]|'
      ),
      $this->_collectedHeaders
    );
  }

  public function testSendDump() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new Handler($callback);
    $handler->resetCounters();
    $handler->sendDump(array());
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1',
        'X-Wf-1-1-1-1: 19|[{"Type":"LOG"},[]]|'
      ),
      $this->_collectedHeaders
    );
  }

  public function testStartGroup() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new Handler($callback);
    $handler->resetCounters();
    $handler->startGroup();
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1',
        'X-Wf-1-1-1-1: 41|[{"Type":"GROUP_START","Label":" "},null]|'
      ),
      $this->_collectedHeaders
    );
  }

  public function testStartGroupWithParameters() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new Handler($callback);
    $handler->resetCounters();
    $handler->startGroup('Sample');
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1',
        'X-Wf-1-1-1-1: 46|[{"Type":"GROUP_START","Label":"Sample"},null]|'
      ),
      $this->_collectedHeaders
    );
  }

  public function testEndGroup() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new Handler($callback);
    $handler->resetCounters();
    $handler->endGroup();
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1',
        'X-Wf-1-1-1-1: 27|[{"Type":"GROUP_END"},null]|'
      ),
      $this->_collectedHeaders
    );
  }

  public function testSendData() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new Handler($callback);
    $handler->resetCounters();
    $handler->sendData(
      Handler::HEADER_CONSOLE,
      array(
        'Type' => 'INFO'
      ),
      NULL
    );
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1',
        'X-Wf-1-1-1-1: 22|[{"Type":"INFO"},null]|'
      ),
      $this->_collectedHeaders
    );
  }

  public function testSendDataTwoTimes() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new Handler($callback);
    $handler->resetCounters();
    $handler->sendData(
      Handler::HEADER_CONSOLE,
      array(
        'Type' => 'INFO'
      ),
      'Info'
    );
    $handler->sendData(
      Handler::HEADER_CONSOLE,
      array(
        'Type' => 'WARN'
      ),
      'Warning'
    );
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1',
        'X-Wf-1-1-1-1: 24|[{"Type":"INFO"},"Info"]|',
        'X-Wf-1-1-1-2: 27|[{"Type":"WARN"},"Warning"]|'
      ),
      $this->_collectedHeaders
    );
  }

  public function testSendDataWithLargeContent() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new Handler($callback);
    $handler->resetCounters();
    Handler::$lengthLimit = 30;
    $handler->sendData(
      Handler::HEADER_CONSOLE,
      array(
        'Type' => 'INFO'
      ),
      'Not really large but large enough'
    );
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1',
        'X-Wf-1-1-1-1: 53|[{"Type":"INFO"},"Not really l|\\',
        'X-Wf-1-1-1-2: |arge but large enough"]|'
      ),
      $this->_collectedHeaders
    );
  }

  /**
   * @covers \Papaya\Message\Dispatcher\Wildfire\Handler::sendData
   */
  public function testSendDumpData() {
    $callback = array($this, 'callbackCollectHeaders');
    $handler = new Handler($callback);
    $handler->resetCounters();
    $handler->sendData(
      Handler::HEADER_DUMP,
      NULL,
      array('Hello' => 'World')
    );
    $this->assertEquals(
      array(
        'X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2',
        'X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3',
        'X-Wf-1-Structure-2: http://meta.firephp.org/Wildfire/Structure/FirePHP/Dump/0.1',
        'X-Wf-1-2-1-1: 17|{"Hello":"World"}|'
      ),
      $this->_collectedHeaders
    );
  }

  public function callbackCollectHeaders($header) {
    $this->_collectedHeaders[] = $header;
  }
}
