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

namespace Papaya\File\System\Action {

  require_once __DIR__.'/../../../../../bootstrap.php';

  class ScriptTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\File\System\Action\Script::__construct
     */
    public function testConstructor() {
      $action = new Script('/local/script');
      $this->assertEquals(
        '/local/script', (string)$action
      );
    }

    /**
     * @covers \Papaya\File\System\Action\Script::execute
     */
    public function testExecute() {
      $action = new Script_TestProxy('/local/script');
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

  class Script_TestProxy extends Script {

    public $commandCall = array();

    protected function executeCommand($command, $arguments) {
      $this->commandCall = func_get_args();
    }
  }
}
