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

class PapayaFileSystemActionUrlTest extends PapayaTestCase {

  /**
   * @covers \Papaya\File\System\Action\Url::__construct
   */
  public function testConstructor() {
    $action = new \Papaya\File\System\Action\Url('http://www.sample.tld/success');
    $this->assertAttributeEquals(
      'http://www.sample.tld/success', '_url', $action
    );
  }

  /**
   * @covers \Papaya\File\System\Action\Url::execute
   */
  public function testExecute() {
    $action = new \PapayaFileSystemActionUrl_TestProxy('http://test.tld/remote.php');
    $action->execute(array('foo' => 'bar'));
    $this->assertEquals(
      array(
        'http://test.tld/remote.php?foo=bar'
      ),
      $action->fetchCall
    );
  }
}

class PapayaFileSystemActionUrl_TestProxy extends \Papaya\File\System\Action\Url {

  public $fetchCall = array();

  protected function fetch($url) {
    $this->fetchCall = func_get_args();
  }
}
