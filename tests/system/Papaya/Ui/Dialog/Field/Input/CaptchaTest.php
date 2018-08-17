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

namespace Papaya\UI\Dialog\Field\Input {

  require_once __DIR__.'/../../../../../../bootstrap.php';

  class CaptchaTest extends \PapayaTestCase {

    /**
     * @covers \Papaya\UI\Dialog\Field\Input\Captcha::__construct
     */
    public function testConstructor() {
      $field = new Captcha('Caption', 'name');
      $this->assertEquals('Caption', $field->getCaption());
      $this->assertEquals('name', $field->getName());
    }

    /**
     * @covers \Papaya\UI\Dialog\Field\Input\Captcha::__construct
     * @covers \Papaya\UI\Dialog\Field\Input\Captcha::getCaptchaImage
     */
    public function testConstructorWithAllParameters() {
      $field = new Captcha('Caption', 'name', 'captchaname');
      $this->assertEquals('captchaname', $field->getCaptchaImage());
    }

    /**
     * @covers \Papaya\UI\Dialog\Field\Input\Captcha::appendTo
     */
    public function testAppendTo() {
      $field = new Captcha_TestProxy('Caption', 'name', 'somecaptcha');
      $field->papaya($this->mockPapaya()->application());
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<field caption="Caption" class="DialogFieldInputCaptcha_TestProxy" error="no"
         mandatory="yes">
        <input type="captcha" name="name[foo]"/>
        <image src="http://www.test.tld/somecaptcha.image.jpg?img[identifier]=foo"/>
      </field>',
        $field->getXML()
      );
    }

    /**
     * @covers \Papaya\UI\Dialog\Field\Input\Captcha::getCurrentValue
     * @covers \Papaya\UI\Dialog\Field\Input\Captcha::validateCaptcha
     */
    public function testGetCurrentValueForUnattachedFieldExpectingTrue() {
      $field = new Captcha_TestProxy('Caption', 'name', 'somecaptcha');
      $this->assertTrue($field->getCurrentValue());
    }

    /**
     * @covers \Papaya\UI\Dialog\Field\Input\Captcha::getCurrentValue
     * @covers \Papaya\UI\Dialog\Field\Input\Captcha::validateCaptcha
     */
    public function testGetCurrentValueAttachedFieldNoTokenExpectingFalse() {
      $field = new Captcha_TestProxy('Caption', 'name', 'somecaptcha');
      $dialog = $this->createMock(\Papaya\UI\Dialog::class);
      $dialog
        ->expects($this->once())
        ->method('parameters')
        ->will($this->returnValue(new \Papaya\Request\Parameters()));
      $collection = $this->createMock(\Papaya\UI\Dialog\Fields::class);
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
     * @covers \Papaya\UI\Dialog\Field\Input\Captcha::getCurrentValue
     * @covers \Papaya\UI\Dialog\Field\Input\Captcha::validateCaptcha
     */
    public function testGetCurrentValueAttachedFieldInvalidTokenExpectingFalse() {
      $field = new Captcha_TestProxy('Caption', 'somecaptcha', 'somecaptcha');
      $dialog = $this->createMock(\Papaya\UI\Dialog::class);
      $dialog
        ->expects($this->once())
        ->method('parameters')
        ->will(
          $this->returnValue(
            new \Papaya\Request\Parameters(array('somecaptcha' => array('someident' => 'somevalue')))
          )
        );
      $collection = $this->createMock(\Papaya\UI\Dialog\Fields::class);
      $collection
        ->expects($this->once())
        ->method('hasOwner')
        ->will($this->returnValue(TRUE));
      $collection
        ->expects($this->once())
        ->method('owner')
        ->will($this->returnValue($dialog));
      $session = $this->createMock(\Papaya\Session::class);
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
     * @covers \Papaya\UI\Dialog\Field\Input\Captcha::getCurrentValue
     * @covers \Papaya\UI\Dialog\Field\Input\Captcha::validateCaptcha
     */
    public function testGetCurrentValueTwoTimeExpectingOnlyOnFetch() {
      $field = new Captcha_TestProxy('Caption', 'somecaptcha', 'somecaptcha');
      $dialog = $this->createMock(\Papaya\UI\Dialog::class);
      $dialog
        ->expects($this->once())
        ->method('parameters')
        ->will(
          $this->returnValue(
            new \Papaya\Request\Parameters(array('somecaptcha' => array('someident' => 'somevalue')))
          )
        );
      $collection = $this->createMock(\Papaya\UI\Dialog\Fields::class);
      $collection
        ->expects($this->any())
        ->method('hasOwner')
        ->will($this->returnValue(TRUE));
      $collection
        ->expects($this->once())
        ->method('owner')
        ->will($this->returnValue($dialog));
      $session = $this->createMock(\Papaya\Session::class);
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
     * @covers \Papaya\UI\Dialog\Field\Input\Captcha::getCurrentValue
     * @covers \Papaya\UI\Dialog\Field\Input\Captcha::validateCaptcha
     */
    public function testGetCurrentValueAttachedFieldValidTokenExpectingTrue() {
      $field = new Captcha_TestProxy('Caption', 'somecaptcha', 'somecaptcha');
      $dialog = $this->createMock(\Papaya\UI\Dialog::class);
      $dialog
        ->expects($this->once())
        ->method('parameters')
        ->will(
          $this->returnValue(
            new \Papaya\Request\Parameters(array('somecaptcha' => array('someident' => 'somevalue')))
          )
        );
      $collection = $this->createMock(\Papaya\UI\Dialog\Fields::class);
      $collection
        ->expects($this->once())
        ->method('hasOwner')
        ->will($this->returnValue(TRUE));
      $collection
        ->expects($this->once())
        ->method('owner')
        ->will($this->returnValue($dialog));
      $session = $this->createMock(\Papaya\Session::class);
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
     * @covers \Papaya\UI\Dialog\Field\Input\Captcha::createCaptchaIdentifier
     */
    public function testCreateCaptchaIdentifier() {
      $field = new Captcha('Caption', 'name');
      $this->assertRegExp('(^[a-z\d]{32}$)D', $field->createCaptchaIdentifier());
    }
  }

  class Captcha_TestProxy extends Captcha {

    public function createCaptchaIdentifier() {
      return 'foo';
    }
  }
}
