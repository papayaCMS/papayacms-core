<?php
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
    $profile = $this->createMock(PapayaApplicationProfile::class);
    $profiles = $this->createMock(PapayaApplicationProfiles::class);
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
    $profile = $this->createMock(PapayaApplicationProfile::class);
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

  public function callbackCreateObject($application) {
    return new stdClass();
  }

  /**
  * @covers PapayaApplication::registerProfile
  */
  public function testRegisterProfileWithInvalidProfileExpectingException() {
    $app = new PapayaApplication();
    $this->setExpectedException(InvalidArgumentException::class);
    $app->registerProfile('SampleClass', new stdClass());
  }

  /**
  * @covers PapayaApplication::registerProfile
  */
  public function testRegisterProfileDuplicateIgnore() {
    $profileOne = $this->createMock(PapayaApplicationProfile::class);
    $profileTwo = $this->createMock(PapayaApplicationProfile::class);
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
    $profileOne = $this->createMock(PapayaApplicationProfile::class);
    $profileTwo = $this->createMock(PapayaApplicationProfile::class);
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
    $profileOne = $this->createMock(PapayaApplicationProfile::class);
    $profileTwo = $this->createMock(PapayaApplicationProfile::class);
    $app = new PapayaApplication();
    $app->registerProfile('SampleClass', $profileOne);
    $this->setExpectedException(
      'InvalidArgumentException',
      'Duplicate application object profile:'
    );
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
    $object = new stdClass();
    $app = new PapayaApplication();
    $this->setExpectedException(
      'InvalidArgumentException',
      'Unknown profile identifier:'
    );
    $app->getObject('SAMPLE');
  }

  /**
  * @covers PapayaApplication::getObject
  */
  public function testGetObjectWithProfile() {
    $object = new stdClass();
    $profile = $this->createMock(PapayaApplicationProfile::class);
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
      'stdClass',
      $app->getObject('SampleClass')
    );
  }

  /**
  * @covers PapayaApplication::setObject
  */
  public function testSetObject() {
    $object = new stdClass();
    $app = new PapayaApplication();
    $app->setObject('stdClass', $object);
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
    $this->setExpectedException(
      'LogicException',
      'Application object does already exists:'
    );
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
    $profile = $this->createMock(PapayaApplicationProfile::class);
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
    $profile = $this->createMock(PapayaApplicationProfile::class);
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
    $profile = $this->createMock(PapayaApplicationProfile::class);
    $app = new PapayaApplication();
    $app->registerProfile('SampleClass', $profile);
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
    $app->sampleClass = new stdClass();
    $app->removeObject('SampleClass');
    $this->assertFalse($app->hasObject('SampleClass'));
  }

  /**
  * @covers PapayaApplication::removeObject
  */
  public function testRemoveObjectWhileOnlyProfileExists() {
    $profile = $this->createMock(PapayaApplicationProfile::class);
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
    $this->setExpectedException(InvalidArgumentException::class);
    $app->removeObject('SampleClass');
  }

  /**
  * @covers PapayaApplication::__get
  */
  public function testMagicMethodGetWithProfile() {
    $object = new stdClass();
    $profile = $this->createMock(PapayaApplicationProfile::class);
    $profile
      ->expects($this->once())
      ->method('createObject')
      ->will($this->returnValue($object));
    $app = new PapayaApplication();
    $app->registerProfile('SampleClass', $profile);
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
    $this->setExpectedException(UnexpectedValueException::class);
    $app->propertyName = 'INVALID_VALUE';
  }

  /**
  * @covers PapayaApplication::__isset
  */
  public function testMagicMethodIssetWithProfileExpectingTrue() {
    $profile = $this->createMock(PapayaApplicationProfile::class);
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
    $app->sampleClass($sample = new stdClass());
    $this->assertSame($sample, $app->sampleClass());
  }

  /**
  * @covers PapayaApplication::offsetExists
  */
  public function testOffsetExistsExpectingTrue() {
    $profile = $this->createMock(PapayaApplicationProfile::class);
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
