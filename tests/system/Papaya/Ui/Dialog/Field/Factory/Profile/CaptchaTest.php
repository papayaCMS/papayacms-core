<?php
require_once(dirname(__FILE__).'/../../../../../../../bootstrap.php');

class PapayaUiDialogFieldFactoryProfileCaptchaTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileCaptcha
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'captcha',
        'caption' => 'Captcha'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileCaptcha();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldInputCaptcha', $field = $profile->getField());
  }
}