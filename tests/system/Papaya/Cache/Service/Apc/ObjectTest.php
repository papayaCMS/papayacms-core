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

use Papaya\Cache\Service\Apc\Wrapper;

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaCacheServiceApcObjectTest extends PapayaTestCase {

  public function skipIfApcIsAvailable() {
    if (extension_loaded('apc')) {
      $this->markTestSkipped('Apc is available');
    }
  }

  public function skipIfApcIsNotAvailable() {
    if (!extension_loaded('apc')) {
      $this->markTestSkipped('Apc is not available');
    }
  }

  /**
  * @covers Wrapper::available
  */
  public function testAvailableExpectingTrue() {
    $this->skipIfApcIsNotAvailable();
    $apc = new Wrapper();
    $this->assertTrue($apc->available());
  }

  /**
  * @covers Wrapper::available
  */
  public function testAvailableExpectingFalse() {
    $this->skipIfApcIsAvailable();
    $apc = new Wrapper();
    $this->assertFalse($apc->available());
  }

  /**
  * @covers Wrapper::store
  */
  public function testStore() {
    $this->skipIfApcIsNotAvailable();
    $apc = new Wrapper();
    $this->assertInternalType('boolean', $apc->store('SAMPLE', 'DATA', 5));
  }


  /**
  * @covers Wrapper::fetch
  */
  public function testFetch() {
    $this->skipIfApcIsNotAvailable();
    $apc = new Wrapper();
    $this->assertNull($apc->fetch('SAMPLE'));
  }

  /**
  * @covers Wrapper::clearCache
  */
  public function testClearCache() {
    $this->skipIfApcIsNotAvailable();
    $apc = new Wrapper();
    $this->assertInternalType('boolean', $apc->clearCache('user'));
  }
}
