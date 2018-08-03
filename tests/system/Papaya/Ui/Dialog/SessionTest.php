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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiDialogSessionTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Dialog\Session::__construct
  */
  public function testConstructor() {
    $dialog = new \Papaya\Ui\Dialog\Session();
    $this->assertAttributeSame(
      $dialog, '_sessionIdentifier', $dialog
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Session::__construct
  */
  public function testConstructorWithSessionIdentifier() {
    $dialog = new \Papaya\Ui\Dialog\Session('sample_name');
    $this->assertAttributeSame(
      'sample_name', '_sessionIdentifier', $dialog
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Session::execute
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

    $dialog = new \PapayaUiDialogSession_TestProxy('session_identifier');
    $dialog->papaya(
      $this->mockPapaya()->application(
        array('session' => $session)
      )
    );
    $this->assertTrue($dialog->execute());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Session::execute
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

    $dialog = new \PapayaUiDialogSession_TestProxy('session_identifier');
    $dialog->_isSubmittedResult = FALSE;
    $dialog->papaya(
      $this->mockPapaya()->application(
        array('session' => $session)
      )
    );
    $this->assertFalse($dialog->execute());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Session::reset
  */
  public function testReset() {
    $session = $this->createMock(\Papaya\Session::class);
    $session
      ->expects($this->once())
      ->method('setValue')
      ->with('session_identifier', NULL);

    $dialog = new \Papaya\Ui\Dialog\Session('session_identifier');
    $dialog->papaya(
      $this->mockPapaya()->application(
        array('session' => $session)
      )
    );
    $dialog->reset();
  }
}

class PapayaUiDialogSession_TestProxy extends \Papaya\Ui\Dialog\Session {
  public $_isSubmittedResult = TRUE;
}
