<?php
require_once(dirname(__FILE__).'/../bootstrap.php');

class base_dialogTest extends PapayaTestCase {

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

    $messages = $this->getMock('PapayaMessageManager', array('dispatch'));
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageDisplay'));

    $session = $this->getMock('PapayaSession');
    $values = $this->getMock('PapayaSessionValues', array(), array($session));
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
