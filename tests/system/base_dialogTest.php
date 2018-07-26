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

require_once __DIR__.'/../bootstrap.php';

class base_dialogTest extends \PapayaTestCase {

  /**
   * @covers base_dialog::checkDialogInput
   */
  public function testCheckDialogInput() {
    $dialog = new base_dialog(NULL, NULL, array());
    $dialog->useToken = FALSE;
    $this->assertTrue($dialog->checkDialogInput());
  }

  /**
   * @covers base_dialog::checkDialogInput
   */
  public function testCheckDialogInputWithCaptcha() {
    $fields = array(
      'captcha' => array(
        'caption',
        'isNoHTML',
        FALSE,
        'captcha',
        'captcha_type'
      )
    );
    $dialog = new base_dialog(NULL, NULL, $fields);
    $dialog->useToken = FALSE;

    $messages = $this->createMock(\PapayaMessageManager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\PapayaMessageDisplay::class));

    $session = $this->createMock(\Papaya\Session::class);
    $values = $this
      ->getMockBuilder(\PapayaSessionValues::class)
      ->setConstructorArgs(array($session))
      ->getMock();
    $values
      ->expects($this->once())
      ->method('offsetGet')
      ->with($this->equalTo('PAPAYA_SESS_CAPTCHA'))
      ->will($this->returnValue(array('identifier' => 'answer')));
    $values
      ->expects($this->once())
      ->method('offsetSet')
      ->with($this->equalTo('PAPAYA_SESS_CAPTCHA'), $this->equalTo(array()));
    $session
      ->expects($this->any())
      ->method('__get')
      ->with($this->equalTo('values'))
      ->will($this->returnValue($values));

    $dialog->papaya(
      $this->mockPapaya()->application(
        array(
          'session' => $session,
          'messages' => $messages,
        )
      )
    );

    $this->assertFalse($dialog->checkDialogInput());
    $this->assertSame(1, $dialog->inputErrors['captcha']);
  }
}
