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

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldInputCaptchaTest extends \PapayaTestCase {

  /**
   * @covers \PapayaUiDialogFieldInputCaptcha::__construct
   */
  public function testConstructor() {
    $field = new \PapayaUiDialogFieldInputCaptcha('Caption', 'name');
    $this->assertEquals('Caption', $field->getCaption());
    $this->assertEquals('name', $field->getName());
  }

  /**
   * @covers \PapayaUiDialogFieldInputCaptcha::__construct
   * @covers \PapayaUiDialogFieldInputCaptcha::getCaptchaImage
   */
  public function testConstructorWithAllParameters() {
    $field = new \PapayaUiDialogFieldInputCaptcha('Caption', 'name', 'captchaname');
    $this->assertEquals('captchaname', $field->getCaptchaImage());
  }

  /**
   * @covers \PapayaUiDialogFieldInputCaptcha::appendTo
   */
  public function testAppendTo() {
    $field = new \PapayaUiDialogFieldInputCaptcha_TestProxy('Caption', 'name', 'somecaptcha');
    $field->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldInputCaptcha_TestProxy" error="no"
         mandatory="yes">
        <input type="captcha" name="name[foo]"/>
        <image src="http://www.test.tld/somecaptcha.image.jpg?img[identifier]=foo"/>
      </field>',
      $field->getXml()
    );
  }

  /**
   * @covers \PapayaUiDialogFieldInputCaptcha::getCurrentValue
   * @covers \PapayaUiDialogFieldInputCaptcha::validateCaptcha
   */
  public function testGetCurrentValueForUnattachedFieldExpectingTrue() {
    $field = new \PapayaUiDialogFieldInputCaptcha_TestProxy('Caption', 'name', 'somecaptcha');
    $this->assertTrue($field->getCurrentValue());
  }

  /**
   * @covers \PapayaUiDialogFieldInputCaptcha::getCurrentValue
   * @covers \PapayaUiDialogFieldInputCaptcha::validateCaptcha
   */
  public function testGetCurrentValueAttachedFieldNoTokenExpectingFalse() {
    $field = new \PapayaUiDialogFieldInputCaptcha_TestProxy('Caption', 'name', 'somecaptcha');
    $dialog = $this->createMock(\PapayaUiDialog::class);
    $dialog
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new \PapayaRequestParameters()));
    $collection = $this->createMock(\PapayaUiDialogFields::class);
    $collection
      ->expects($this->once())
      ->method('hasOwner')
      ->will($this->returnValue(TRUE));
    $collection
      ->expects($this->once())
      ->method('owner')
      ->will($this->returnValue($dialog));
    $field->collection($collection);
    $this->assertFalse($field->getCurrentValue());
  }

  /**
   * @covers \PapayaUiDialogFieldInputCaptcha::getCurrentValue
   * @covers \PapayaUiDialogFieldInputCaptcha::validateCaptcha
   */
  public function testGetCurrentValueAttachedFieldInvalidTokenExpectingFalse() {
    $field = new \PapayaUiDialogFieldInputCaptcha_TestProxy('Caption', 'somecaptcha', 'somecaptcha');
    $dialog = $this->createMock(\PapayaUiDialog::class);
    $dialog
      ->expects($this->once())
      ->method('parameters')
      ->will(
        $this->returnValue(
          new \PapayaRequestParameters(array('somecaptcha' => array('someident' => 'somevalue')))
        )
      );
    $collection = $this->createMock(\PapayaUiDialogFields::class);
    $collection
      ->expects($this->once())
      ->method('hasOwner')
      ->will($this->returnValue(TRUE));
    $collection
      ->expects($this->once())
      ->method('owner')
      ->will($this->returnValue($dialog));
    $session = $this->createMock(\PapayaSession::class);
    $session
      ->expects($this->once())
      ->method('getValue')
      ->with('PAPAYA_SESS_CAPTCHA', array())
      ->will($this->returnValue(array()));
    $field->collection($collection);
    $field->papaya($this->mockPapaya()->application(array('session' => $session)));

    $this->assertFalse($field->getCurrentValue());
  }

  /**
   * @covers \PapayaUiDialogFieldInputCaptcha::getCurrentValue
   * @covers \PapayaUiDialogFieldInputCaptcha::validateCaptcha
   */
  public function testGetCurrentValueTwoTimeExpectingOnlyOnFetch() {
    $field = new \PapayaUiDialogFieldInputCaptcha_TestProxy('Caption', 'somecaptcha', 'somecaptcha');
    $dialog = $this->createMock(\PapayaUiDialog::class);
    $dialog
      ->expects($this->once())
      ->method('parameters')
      ->will(
        $this->returnValue(
          new \PapayaRequestParameters(array('somecaptcha' => array('someident' => 'somevalue')))
        )
      );
    $collection = $this->createMock(\PapayaUiDialogFields::class);
    $collection
      ->expects($this->any())
      ->method('hasOwner')
      ->will($this->returnValue(TRUE));
    $collection
      ->expects($this->once())
      ->method('owner')
      ->will($this->returnValue($dialog));
    $session = $this->createMock(\PapayaSession::class);
    $session
      ->expects($this->once())
      ->method('getValue')
      ->with('PAPAYA_SESS_CAPTCHA', array())
      ->will($this->returnValue(array()));
    $field->collection($collection);
    $field->papaya($this->mockPapaya()->application(array('session' => $session)));

    $field->getCurrentValue();
    $this->assertFalse($field->getCurrentValue());
  }

  /**
   * @covers \PapayaUiDialogFieldInputCaptcha::getCurrentValue
   * @covers \PapayaUiDialogFieldInputCaptcha::validateCaptcha
   */
  public function testGetCurrentValueAttachedFieldValidTokenExpectingTrue() {
    $field = new \PapayaUiDialogFieldInputCaptcha_TestProxy('Caption', 'somecaptcha', 'somecaptcha');
    $dialog = $this->createMock(\PapayaUiDialog::class);
    $dialog
      ->expects($this->once())
      ->method('parameters')
      ->will(
        $this->returnValue(
          new \PapayaRequestParameters(array('somecaptcha' => array('someident' => 'somevalue')))
        )
      );
    $collection = $this->createMock(\PapayaUiDialogFields::class);
    $collection
      ->expects($this->once())
      ->method('hasOwner')
      ->will($this->returnValue(TRUE));
    $collection
      ->expects($this->once())
      ->method('owner')
      ->will($this->returnValue($dialog));
    $session = $this->createMock(\PapayaSession::class);
    $session
      ->expects($this->once())
      ->method('getValue')
      ->with('PAPAYA_SESS_CAPTCHA', array())
      ->will($this->returnValue(array('someident' => 'somevalue', 'otherident' => 'othervalue')));
    $session
      ->expects($this->once())
      ->method('setValue')
      ->with('PAPAYA_SESS_CAPTCHA', array('otherident' => 'othervalue'));
    $field->collection($collection);
    $field->papaya($this->mockPapaya()->application(array('session' => $session)));

    $this->assertTrue($field->getCurrentValue());
  }

  /**
   * @covers \PapayaUiDialogFieldInputCaptcha::createCaptchaIdentifier
   */
  public function testCreateCaptchaIdentifier() {
    $field = new \PapayaUiDialogFieldInputCaptcha('Caption', 'name');
    $this->assertRegExp('(^[a-z\d]{32}$)D', $field->createCaptchaIdentifier());
  }
}

class PapayaUiDialogFieldInputCaptcha_TestProxy extends \PapayaUiDialogFieldInputCaptcha {

  public function createCaptchaIdentifier() {
    return 'foo';
  }
}
