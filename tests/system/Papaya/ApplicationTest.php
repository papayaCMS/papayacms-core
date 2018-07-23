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

use Papaya\Application\Profile;
use Papaya\Application\Profiles;

require_once __DIR__.'/../../bootstrap.php';

class PapayaApplicationTest extends PapayaTestCase {

  /**
  * @covers PapayaApplication::getInstance
  */
  public function testGetInstanceOneInstance() {
    $app1 = PapayaApplication::getInstance();
    $app2 = PapayaApplication::getInstance();
    $this->assertInstanceOf(
      PapayaApplication::class,
      $app1
    );
    $this->assertSame(
      $app1, $app2
    );
  }
  /**
  * @covers PapayaApplication::getInstance
  */
  public function testGetInstanceTwoInstances() {
    $app1 = PapayaApplication::getInstance();
    $app2 = PapayaApplication::getInstance(TRUE);
    $this->assertInstanceOf(
      PapayaApplication::class,
      $app1
    );
    $this->assertNotSame(
      $app1, $app2
    );
  }

  /**
  * @covers PapayaApplication::registerProfiles
  */
  public function testRegisterProfiles() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Profile $profile */
    $profile = $this->createMock(Profile::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|Profiles $profiles */
    $profiles = $this->createMock(Profiles::class);
    $profiles
      ->expects($this->once())
      ->method('getProfiles')
      ->will($this->returnValue(array('SampleClass' => $profile)));
    $app = new PapayaApplication();
    $app->registerProfiles($profiles);
    $this->assertSame(
      array('sampleclass' => $profile),
      $this->readAttribute($app, '_profiles')
    );
  }

  /**
  * @covers PapayaApplication::registerProfile
  */
  public function testRegisterProfile() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Profile $profile */
    $profile = $this->createMock(Profile::class);
    $app = new PapayaApplication();
    $app->registerProfile('SampleClass', $profile);
    $this->assertSame(
      array('sampleclass' => $profile),
      $this->readAttribute($app, '_profiles')
    );
  }

  /**
  * @covers PapayaApplication::registerProfile
  */
  public function testRegisterProfileUsingCallable() {
    $app = new PapayaApplication();
    $app->registerProfile('SampleClass', $profile = array($this, 'callbackCreateObject'));
    $this->assertSame(
      array('sampleclass' => $profile),
      $this->readAttribute($app, '_profiles')
    );
  }

  public function callbackCreateObject() {
    return new stdClass();
  }

  /**
  * @covers PapayaApplication::registerProfile
  */
  public function testRegisterProfileWithInvalidProfileExpectingException() {
    $app = new PapayaApplication();
    $this->expectException(InvalidArgumentException::class);
    /** @noinspection PhpParamsInspection */
    $app->registerProfile('SampleClass', new stdClass());
  }

  /**
  * @covers PapayaApplication::registerProfile
  */
  public function testRegisterProfileDuplicateIgnore() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Profile $profileOne */
    $profileOne = $this->createMock(Profile::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|Profile $profileTwo */
    $profileTwo = $this->createMock(Profile::class);
    $app = new PapayaApplication();
    $app->registerProfile('SampleClass', $profileOne);
    $app->registerProfile('SampleClass', $profileTwo, PapayaApplication::DUPLICATE_IGNORE);
    $this->assertSame(
      array('sampleclass' => $profileOne),
      $this->readAttribute($app, '_profiles')
    );
  }

  /**
  * @covers PapayaApplication::registerProfile
  */
  public function testRegisterProfileDuplicateOverwrite() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Profile $profileOne */
    $profileOne = $this->createMock(Profile::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|Profile $profileTwo */
    $profileTwo = $this->createMock(Profile::class);
    $app = new PapayaApplication();
    $app->registerProfile('SampleClass', $profileOne);
    $app->registerProfile('SampleClass', $profileTwo, PapayaApplication::DUPLICATE_OVERWRITE);
    $this->assertSame(
      array('sampleclass' => $profileTwo),
      $this->readAttribute($app, '_profiles')
    );
  }

  /**
  * @covers PapayaApplication::registerProfile
  */
  public function testRegisterProfileDuplicateError() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Profile $profileOne */
    $profileOne = $this->createMock(Profile::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|Profile $profileTwo */
    $profileTwo = $this->createMock(Profile::class);
    $app = new PapayaApplication();
    $app->registerProfile('SampleClass', $profileOne);
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Duplicate application object profile:');
    $app->registerProfile('SampleClass', $profileTwo, PapayaApplication::DUPLICATE_ERROR);
  }

  /**
  * @covers PapayaApplication::getObject
  */
  public function testGetObjectAfterSet() {
    $object = new stdClass();
    $app = new PapayaApplication();
    $app->setObject('SAMPLE', $object);
    $this->assertSame(
      $object,
      $app->getObject('SAMPLE')
    );
  }

  /**
  * @covers PapayaApplication::getObject
  */
  public function testGetObjectWithoutSetExpectingError() {
    $app = new PapayaApplication();
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Unknown profile identifier:');
    $app->getObject('SAMPLE');
  }

  /**
  * @covers PapayaApplication::getObject
  */
  public function testGetObjectWithProfile() {
    $object = new stdClass();
    /** @var PHPUnit_Framework_MockObject_MockObject|Profile $profile */
    $profile = $this->createMock(Profile::class);
    $profile
      ->expects($this->once())
      ->method('createObject')
      ->will($this->returnValue($object));
    $app = new PapayaApplication();
    $app->registerProfile('SampleClass', $profile);
    $this->assertSame(
      $object,
      $app->getObject('SampleClass')
    );
  }

  /**
  * @covers PapayaApplication::getObject
  */
  public function testGetObjectWithCallable() {
    $app = new PapayaApplication();
    $app->registerProfile('SampleClass', array($this, 'callbackCreateObject'));
    $this->assertInstanceOf(
      stdClass::class,
      $app->getObject('SampleClass')
    );
  }

  /**
  * @covers PapayaApplication::setObject
  */
  public function testSetObject() {
    $object = new stdClass();
    $app = new PapayaApplication();
    $app->setObject(stdClass::class, $object);
    $this->assertSame(
      array('stdclass' => $object),
      $this->readAttribute($app, '_objects')
    );
  }

  /**
  * @covers PapayaApplication::setObject
  */
  public function testSetObjectDuplicateError() {
    $app = new PapayaApplication();
    $app->setObject('SampleClass', new stdClass());
    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('Application object does already exists:');
    $app->setObject('SampleClass', new stdClass());
  }

  /**
  * @covers PapayaApplication::setObject
  */
  public function testSetObjectDuplicateIgnore() {
    $objectOne = new stdClass();
    $app = new PapayaApplication();
    $app->setObject('SampleClass', $objectOne);
    $app->setObject('SampleClass', new stdClass(), PapayaApplication::DUPLICATE_IGNORE);
    $this->assertSame(
      $objectOne,
      $app->getObject('SampleClass')
    );
  }

  /**
  * @covers PapayaApplication::setObject
  */
  public function testSetObjectDuplicateOverwrite() {
    $objectTwo = new stdClass();
    $app = new PapayaApplication();
    $app->setObject('SampleClass', new stdClass());
    $app->setObject('SampleClass', $objectTwo, PapayaApplication::DUPLICATE_OVERWRITE);
    $this->assertSame(
      $objectTwo,
      $app->getObject('SampleClass')
    );
  }

  /**
  * @covers PapayaApplication::hasObject
  */
  public function testHasObjectExpectingFalse() {
    $app = new PapayaApplication();
    $this->assertFalse(
      $app->hasObject('SampleClass')
    );
  }

  /**
  * @covers PapayaApplication::hasObject
  */
  public function testHasObjectExpectingTrue() {
    $app = new PapayaApplication();
    $app->setObject('SampleClass', new stdClass());
    $this->assertTrue(
      $app->hasObject('SampleClass')
    );
  }

  /**
  * @covers PapayaApplication::hasObject
  */
  public function testHasObjectWithProfileExpectingTrue() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Profile $profile */
    $profile = $this->createMock(Profile::class);
    $app = new PapayaApplication();
    $app->registerProfile('SampleClass', $profile);
    $this->assertTrue(
      $app->hasObject('SampleClass')
    );
  }

  /**
  * @covers PapayaApplication::hasObject
  */
  public function testHasObjectWithProfileExpectingFalse() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Profile $profile */
    $profile = $this->createMock(Profile::class);
    $app = new PapayaApplication();
    $app->registerProfile('SampleClass', $profile);
    $this->assertFalse(
      $app->hasObject('SampleClass', FALSE)
    );
  }

  /**
  * @covers PapayaApplication::removeObject
  */
  public function testRemoveObjectKeepsProfile() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Profile $profile */
    $profile = $this->createMock(Profile::class);
    $app = new PapayaApplication();
    $app->registerProfile('SampleClass', $profile);
    /** @noinspection PhpUndefinedFieldInspection */
    $app->sampleClass = new stdClass();
    $app->removeObject('SampleClass');
    $this->assertTrue(
      $app->hasObject('SampleClass', TRUE)
    );
    $this->assertFalse(
      $app->hasObject('SampleClass', FALSE)
    );
  }

  /**
  * @covers PapayaApplication::removeObject
  */
  public function testRemoveObject() {
    $app = new PapayaApplication();
    /** @noinspection PhpUndefinedFieldInspection */
    $app->sampleClass = new stdClass();
    $app->removeObject('SampleClass');
    $this->assertFalse($app->hasObject('SampleClass'));
  }

  /**
  * @covers PapayaApplication::removeObject
  */
  public function testRemoveObjectWhileOnlyProfileExists() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Profile $profile */
    $profile = $this->createMock(Profile::class);
    $app = new PapayaApplication();
    $app->registerProfile('SampleClass', $profile);
    $app->removeObject('SampleClass');
    $this->assertTrue($app->hasObject('SampleClass'));
  }

  /**
  * @covers PapayaApplication::removeObject
  */
  public function testRemoveObjectUnknownExpectingException() {
    $app = new PapayaApplication();
    $this->expectException(InvalidArgumentException::class);
    $app->removeObject('SampleClass');
  }

  /**
  * @covers PapayaApplication::__get
  */
  public function testMagicMethodGetWithProfile() {
    $object = new stdClass();
    /** @var PHPUnit_Framework_MockObject_MockObject|Profile $profile */
    $profile = $this->createMock(Profile::class);
    $profile
      ->expects($this->once())
      ->method('createObject')
      ->will($this->returnValue($object));
    $app = new PapayaApplication();
    $app->registerProfile('SampleClass', $profile);
    /** @noinspection PhpUndefinedFieldInspection */
    $this->assertSame(
      $object,
      $app->SampleClass
    );
  }

  /**
  * @covers PapayaApplication::__set
  */
  public function testMagicMethodSet() {
    $object = new stdClass();
    $app = new PapayaApplication();
    /** @noinspection PhpUndefinedFieldInspection */
    $app->stdClass = $object;
    $this->assertSame(
      array('stdclass' => $object),
      $this->readAttribute($app, '_objects')
    );
  }

  /**
  * @covers PapayaApplication::__set
  */
  public function testMagicMethodSetWithInvalidValueExpectingException() {
    $app = new PapayaApplication();
    $this->expectException(UnexpectedValueException::class);
    /** @noinspection PhpUndefinedFieldInspection */
    $app->propertyName = 'INVALID_VALUE';
  }

  /**
  * @covers PapayaApplication::__isset
  */
  public function testMagicMethodIssetWithProfileExpectingTrue() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Profile $profile */
    $profile = $this->createMock(Profile::class);
    $app = new PapayaApplication();
    $app->registerProfile('SampleClass', $profile);
    $this->assertTrue(
      isset($app->SampleClass)
    );
  }

  /**
  * @covers PapayaApplication::__call
  */
  public function testMagicMethodCall() {
    $app = new PapayaApplication();
    /** @noinspection PhpUndefinedMethodInspection */
    $app->sampleClass($sample = new stdClass());
    /** @noinspection PhpUndefinedMethodInspection */
    $this->assertSame($sample, $app->sampleClass());
  }

  /**
  * @covers PapayaApplication::offsetExists
  */
  public function testOffsetExistsExpectingTrue() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Profile $profile */
    $profile = $this->createMock(Profile::class);
    $app = new PapayaApplication();
    $app->registerProfile('SampleClass', $profile);
    $this->assertTrue(
      isset($app['SampleClass'])
    );
  }

  /**
  * @covers PapayaApplication::offsetExists
  */
  public function testOffsetExistsExpectingFalse() {
    $app = new PapayaApplication();
    $this->assertFalse(
      isset($app['SampleClass'])
    );
  }

  /**
  * @covers PapayaApplication::offsetSet
  * @covers PapayaApplication::offsetGet
  */
  public function testOffsetGetAfterSet() {
    $app = new PapayaApplication();
    $app['SampleClass'] = $object = new stdClass();
    $this->assertSame(
      $object, $app['SampleClass']
    );
  }

  /**
  * @covers PapayaApplication::offsetUnset
  */
  public function testOffsetUnset() {
    $app = new PapayaApplication();
    $app['SampleClass'] = new stdClass();
    unset($app['SampleClass']);
    $this->assertFalse(
      isset($app['SampleClass'])
    );
  }
}
