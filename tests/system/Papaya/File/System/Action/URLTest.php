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

  class URLTest extends \Papaya\TestCase {

    /**
     * @covers \Papaya\File\System\Action\URL::__construct
     */
    public function testConstructor() {
      $action = new URL('http://www.sample.tld/success');
      $this->assertEquals(
        'http://www.sample.tld/success', (string)$action
      );
    }

    /**
     * @covers \Papaya\File\System\Action\URL::execute
     */
    public function testExecute() {
      $action = new URL_TestProxy('http://test.tld/remote.php');
      $action->execute(array('foo' => 'bar'));
      $this->assertEquals(
        array(
          'http://test.tld/remote.php?foo=bar'
        ),
        $action->fetchCall
      );
    }
  }

  class URL_TestProxy extends URL {

    public $fetchCall = array();

    protected function fetch($url) {
      $this->fetchCall = func_get_args();
    }
  }
}
