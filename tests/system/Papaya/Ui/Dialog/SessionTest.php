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

namespace Papaya\UI\Dialog {

  require_once __DIR__.'/../../../../bootstrap.php';

  class SessionTest extends \PapayaTestCase {

    /**
     * @covers \Papaya\UI\Dialog\Session::__construct
     */
    public function testConstructor() {
      $dialog = new Session();
      $this->assertAttributeSame(
        $dialog, '_sessionIdentifier', $dialog
      );
    }

    /**
     * @covers \Papaya\UI\Dialog\Session::__construct
     */
    public function testConstructorWithSessionIdentifier() {
      $dialog = new Session('sample_name');
      $this->assertAttributeSame(
        'sample_name', '_sessionIdentifier', $dialog
      );
    }

    /**
     * @covers \Papaya\UI\Dialog\Session::execute
     */
    public function testExecuteSetSessionVariableExpectingTrue() {
      $session = $this->createMock(\Papaya\Session::class);
      $session
        ->expects($this->once())
        ->method('getValue')
        ->with('session_identifier')
        ->will($this->returnValue(array('session' => 'value')));
      $session
        ->expects($this->once())
        ->method('setValue')
        ->with('session_identifier', array('session' => 'value'));

      $dialog = new Session_TestProxy('session_identifier');
      $dialog->papaya(
        $this->mockPapaya()->application(
          array('session' => $session)
        )
      );
      $this->assertTrue($dialog->execute());
    }

    /**
     * @covers \Papaya\UI\Dialog\Session::execute
     */
    public function testExecuteSetSessionVariableExpectingFalseWithoutData() {
      $session = $this->createMock(\Papaya\Session::class);
      $session
        ->expects($this->once())
        ->method('getValue')
        ->with('session_identifier')
        ->will($this->returnValue(FALSE));
      $session
        ->expects($this->never())
        ->method('setValue');

      $dialog = new Session_TestProxy('session_identifier');
      $dialog->_isSubmittedResult = FALSE;
      $dialog->papaya(
        $this->mockPapaya()->application(
          array('session' => $session)
        )
      );
      $this->assertFalse($dialog->execute());
    }

    /**
     * @covers \Papaya\UI\Dialog\Session::reset
     */
    public function testReset() {
      $session = $this->createMock(\Papaya\Session::class);
      $session
        ->expects($this->once())
        ->method('setValue')
        ->with('session_identifier', NULL);

      $dialog = new Session('session_identifier');
      $dialog->papaya(
        $this->mockPapaya()->application(
          array('session' => $session)
        )
      );
      $dialog->reset();
    }
  }

  class Session_TestProxy extends Session {
    public $_isSubmittedResult = TRUE;
  }
}
