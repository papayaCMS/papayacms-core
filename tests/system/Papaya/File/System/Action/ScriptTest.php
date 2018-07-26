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

class PapayaFileSystemActionScriptTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\File\System\Action\Script::__construct
   */
  public function testConstructor() {
    $action = new \Papaya\File\System\Action\Script('/local/script');
    $this->assertAttributeEquals(
      '/local/script', '_script', $action
    );
  }

  /**
   * @covers \Papaya\File\System\Action\Script::execute
   */
  public function testExecute() {
    $action = new \PapayaFileSystemActionScript_TestProxy('/local/script');
    $action->execute(array('foo' => 'bar'));
    $this->assertEquals(
      array(
        '/local/script',
        array('--foo' => 'bar')
      ),
      $action->commandCall
    );
  }
}

class PapayaFileSystemActionScript_TestProxy extends \Papaya\File\System\Action\Script {

  public $commandCall = array();

  protected function executeCommand($command, $arguments) {
    $this->commandCall = func_get_args();
  }
}
