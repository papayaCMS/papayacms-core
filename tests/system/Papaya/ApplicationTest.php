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

namespace Papaya;

require_once __DIR__.'/../../bootstrap.php';

class ApplicationTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Application::getInstance
   */
  public function testGetInstanceOneInstance() {
    $app1 = Application::getInstance();
    $app2 = Application::getInstance();
    $this->assertInstanceOf(
      Application::class,
      $app1
    );
    $this->assertSame(
      $app1, $app2
    );
  }

  /**
   * @covers \Papaya\Application::getInstance
   */
  public function testGetInstanceTwoInstances() {
    $app1 = Application::getInstance();
    $app2 = Application::getInstance(TRUE);
    $this->assertInstanceOf(
      Application::class,
      $app1
    );
    $this->assertNotSame(
      $app1, $app2
    );
  }

  /**
   * @covers \Papaya\Application::registerProfiles
   */
  public function testRegisterProfiles() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profile */
    $profile = $this->createMock(Application\Profile::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profiles $profiles */
    $profiles = $this->createMock(Application\Profiles::class);
    $profiles
      ->expects($this->once())
      ->method('getProfiles')
      ->will($this->returnValue(array('SampleClass' => $profile)));
    $app = new Application();
    $app->registerProfiles($profiles);
    $this->assertSame(
      array('sampleclass' => $profile),
      $this->readAttribute($app, '_profiles')
    );
  }

  /**
   * @covers \Papaya\Application::registerProfile
   */
  public function testRegisterProfile() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profile */
    $profile = $this->createMock(Application\Profile::class);
    $app = new Application();
    $app->registerProfile('SampleClass', $profile);
    $this->assertSame(
      array('sampleclass' => $profile),
      $this->readAttribute($app, '_profiles')
    );
  }

  /**
   * @covers \Papaya\Application::registerProfile
   */
  public function testRegisterProfileUsingCallable() {
    $app = new Application();
    $app->registerProfile('SampleClass', $profile = array($this, 'callbackCreateObject'));
    $this->assertSame(
      array('sampleclass' => $profile),
      $this->readAttribute($app, '_profiles')
    );
  }

  public function callbackCreateObject() {
    return new \stdClass();
  }

  /**
   * @covers \Papaya\Application::registerProfile
   */
  public function testRegisterProfileWithInvalidProfileExpectingException() {
    $app = new Application();
    $this->expectException(\InvalidArgumentException::class);
    /** @noinspection PhpParamsInspection */
    $app->registerProfile('SampleClass', new \stdClass());
  }

  /**
   * @covers \Papaya\Application::registerProfile
   */
  public function testRegisterProfileDuplicateIgnore() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profileOne */
    $profileOne = $this->createMock(Application\Profile::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profileTwo */
    $profileTwo = $this->createMock(Application\Profile::class);
    $app = new Application();
    $app->registerProfile('SampleClass', $profileOne);
    $app->registerProfile('SampleClass', $profileTwo, Application::DUPLICATE_IGNORE);
    $this->assertSame(
      array('sampleclass' => $profileOne),
      $this->readAttribute($app, '_profiles')
    );
  }

  /**
   * @covers \Papaya\Application::registerProfile
   */
  public function testRegisterProfileDuplicateOverwrite() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profileOne */
    $profileOne = $this->createMock(Application\Profile::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profileTwo */
    $profileTwo = $this->createMock(Application\Profile::class);
    $app = new Application();
    $app->registerProfile('SampleClass', $profileOne);
    $app->registerProfile('SampleClass', $profileTwo, Application::DUPLICATE_OVERWRITE);
    $this->assertSame(
      array('sampleclass' => $profileTwo),
      $this->readAttribute($app, '_profiles')
    );
  }

  /**
   * @covers \Papaya\Application::registerProfile
   */
  public function testRegisterProfileDuplicateError() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profileOne */
    $profileOne = $this->createMock(Application\Profile::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profileTwo */
    $profileTwo = $this->createMock(Application\Profile::class);
    $app = new Application();
    $app->registerProfile('SampleClass', $profileOne);
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Duplicate application object profile:');
    $app->registerProfile('SampleClass', $profileTwo, Application::DUPLICATE_ERROR);
  }

  /**
   * @covers \Papaya\Application::getObject
   */
  public function testGetObjectAfterSet() {
    $object = new \stdClass();
    $app = new Application();
    $app->setObject('SAMPLE', $object);
    $this->assertSame(
      $object,
      $app->getObject('SAMPLE')
    );
  }

  /**
   * @covers \Papaya\Application::getObject
   */
  public function testGetObjectWithoutSetExpectingError() {
    $app = new Application();
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Unknown profile identifier:');
    $app->getObject('SAMPLE');
  }

  /**
   * @covers \Papaya\Application::getObject
   */
  public function testGetObjectWithProfile() {
    $object = new \stdClass();
    /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profile */
    $profile = $this->createMock(Application\Profile::class);
    $profile
      ->expects($this->once())
      ->method('createObject')
      ->will($this->returnValue($object));
    $app = new Application();
    $app->registerProfile('SampleClass', $profile);
    $this->assertSame(
      $object,
      $app->getObject('SampleClass')
    );
  }

  /**
   * @covers \Papaya\Application::getObject
   */
  public function testGetObjectWithCallable() {
    $app = new Application();
    $app->registerProfile('SampleClass', array($this, 'callbackCreateObject'));
    $this->assertInstanceOf(
      \stdClass::class,
      $app->getObject('SampleClass')
    );
  }

  /**
   * @covers \Papaya\Application::setObject
   */
  public function testSetObject() {
    $object = new \stdClass();
    $app = new Application();
    $app->setObject(\stdClass::class, $object);
    $this->assertSame(
      array('stdclass' => $object),
      $this->readAttribute($app, '_objects')
    );
  }

  /**
   * @covers \Papaya\Application::setObject
   */
  public function testSetObjectDuplicateError() {
    $app = new Application();
    $app->setObject('SampleClass', new \stdClass());
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Application object does already exists:');
    $app->setObject('SampleClass', new \stdClass());
  }

  /**
   * @covers \Papaya\Application::setObject
   */
  public function testSetObjectDuplicateIgnore() {
    $objectOne = new \stdClass();
    $app = new Application();
    $app->setObject('SampleClass', $objectOne);
    $app->setObject('SampleClass', new \stdClass(), Application::DUPLICATE_IGNORE);
    $this->assertSame(
      $objectOne,
      $app->getObject('SampleClass')
    );
  }

  /**
   * @covers \Papaya\Application::setObject
   */
  public function testSetObjectDuplicateOverwrite() {
    $objectTwo = new \stdClass();
    $app = new Application();
    $app->setObject('SampleClass', new \stdClass());
    $app->setObject('SampleClass', $objectTwo, Application::DUPLICATE_OVERWRITE);
    $this->assertSame(
      $objectTwo,
      $app->getObject('SampleClass')
    );
  }

  /**
   * @covers \Papaya\Application::hasObject
   */
  public function testHasObjectExpectingFalse() {
    $app = new Application();
    $this->assertFalse(
      $app->hasObject('SampleClass')
    );
  }

  /**
   * @covers \Papaya\Application::hasObject
   */
  public function testHasObjectExpectingTrue() {
    $app = new Application();
    $app->setObject('SampleClass', new \stdClass());
    $this->assertTrue(
      $app->hasObject('SampleClass')
    );
  }

  /**
   * @covers \Papaya\Application::hasObject
   */
  public function testHasObjectWithProfileExpectingTrue() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profile */
    $profile = $this->createMock(Application\Profile::class);
    $app = new Application();
    $app->registerProfile('SampleClass', $profile);
    $this->assertTrue(
      $app->hasObject('SampleClass')
    );
  }

  /**
   * @covers \Papaya\Application::hasObject
   */
  public function testHasObjectWithProfileExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profile */
    $profile = $this->createMock(Application\Profile::class);
    $app = new Application();
    $app->registerProfile('SampleClass', $profile);
    $this->assertFalse(
      $app->hasObject('SampleClass', FALSE)
    );
  }

  /**
   * @covers \Papaya\Application::removeObject
   */
  public function testRemoveObjectKeepsProfile() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profile */
    $profile = $this->createMock(Application\Profile::class);
    $app = new Application();
    $app->registerProfile('SampleClass', $profile);
    /** @noinspection PhpUndefinedFieldInspection */
    $app->sampleClass = new \stdClass();
    $app->removeObject('SampleClass');
    $this->assertTrue(
      $app->hasObject('SampleClass', TRUE)
    );
    $this->assertFalse(
      $app->hasObject('SampleClass', FALSE)
    );
  }

  /**
   * @covers \Papaya\Application::removeObject
   */
  public function testRemoveObject() {
    $app = new Application();
    /** @noinspection PhpUndefinedFieldInspection */
    $app->sampleClass = new \stdClass();
    $app->removeObject('SampleClass');
    $this->assertFalse($app->hasObject('SampleClass'));
  }

  /**
   * @covers \Papaya\Application::removeObject
   */
  public function testRemoveObjectWhileOnlyProfileExists() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profile */
    $profile = $this->createMock(Application\Profile::class);
    $app = new Application();
    $app->registerProfile('SampleClass', $profile);
    $app->removeObject('SampleClass');
    $this->assertTrue($app->hasObject('SampleClass'));
  }

  /**
   * @covers \Papaya\Application::removeObject
   */
  public function testRemoveObjectUnknownExpectingException() {
    $app = new Application();
    $this->expectException(\InvalidArgumentException::class);
    $app->removeObject('SampleClass');
  }

  /**
   * @covers \Papaya\Application::__get
   */
  public function testMagicMethodGetWithProfile() {
    $object = new \stdClass();
    /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profile */
    $profile = $this->createMock(Application\Profile::class);
    $profile
      ->expects($this->once())
      ->method('createObject')
      ->will($this->returnValue($object));
    $app = new Application();
    $app->registerProfile('SampleClass', $profile);
    /** @noinspection PhpUndefinedFieldInspection */
    $this->assertSame(
      $object,
      $app->SampleClass
    );
  }

  /**
   * @covers \Papaya\Application::__set
   */
  public function testMagicMethodSet() {
    $object = new \stdClass();
    $app = new Application();
    /** @noinspection PhpUndefinedFieldInspection */
    $app->stdClass = $object;
    $this->assertSame(
      array('stdclass' => $object),
      $this->readAttribute($app, '_objects')
    );
  }

  /**
   * @covers \Papaya\Application::__set
   */
  public function testMagicMethodSetWithInvalidValueExpectingException() {
    $app = new Application();
    $this->expectException(\UnexpectedValueException::class);
    /** @noinspection PhpUndefinedFieldInspection */
    $app->propertyName = 'INVALID_VALUE';
  }

  /**
   * @covers \Papaya\Application::__isset
   */
  public function testMagicMethodIssetWithProfileExpectingTrue() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profile */
    $profile = $this->createMock(Application\Profile::class);
    $app = new Application();
    $app->registerProfile('SampleClass', $profile);
    $this->assertTrue(
      isset($app->SampleClass)
    );
  }

  /**
   * @covers \Papaya\Application::__call
   */
  public function testMagicMethodCall() {
    $app = new Application();
    /** @noinspection PhpUndefinedMethodInspection */
    $app->sampleClass($sample = new \stdClass());
    /** @noinspection PhpUndefinedMethodInspection */
    $this->assertSame($sample, $app->sampleClass());
  }

  /**
   * @covers \Papaya\Application::offsetExists
   */
  public function testOffsetExistsExpectingTrue() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profile */
    $profile = $this->createMock(Application\Profile::class);
    $app = new Application();
    $app->registerProfile('SampleClass', $profile);
    $this->assertTrue(
      isset($app['SampleClass'])
    );
  }

  /**
   * @covers \Papaya\Application::offsetExists
   */
  public function testOffsetExistsExpectingFalse() {
    $app = new Application();
    $this->assertFalse(
      isset($app['SampleClass'])
    );
  }

  /**
   * @covers \Papaya\Application::offsetSet
   * @covers \Papaya\Application::offsetGet
   */
  public function testOffsetGetAfterSet() {
    $app = new Application();
    $app['SampleClass'] = $object = new \stdClass();
    $this->assertSame(
      $object, $app['SampleClass']
    );
  }

  /**
   * @covers \Papaya\Application::offsetUnset
   */
  public function testOffsetUnset() {
    $app = new Application();
    $app['SampleClass'] = new \stdClass();
    unset($app['SampleClass']);
    $this->assertFalse(
      isset($app['SampleClass'])
    );
  }
}
