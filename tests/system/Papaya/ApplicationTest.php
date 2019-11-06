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

namespace Papaya {

  require_once __DIR__.'/../../bootstrap.php';

  /**
   * @covers \Papaya\Application
   */
  class ApplicationTest extends TestCase {

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

    public function testRegisterProfiles() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profile */
      $profile = $this->createMock(Application\Profile::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profiles $profiles */
      $profiles = $this->createMock(Application\Profiles::class);
      $profiles
        ->expects($this->once())
        ->method('getProfiles')
        ->willReturn(['SampleClass' => $profile]);
      $app = new Application();
      $app->registerProfiles($profiles);
      $this->assertSame(
        ['sampleclass' => $profile],
        $app->getProfiles()
      );
    }

    public function testRegisterProfile() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profile */
      $profile = $this->createMock(Application\Profile::class);
      $app = new Application();
      $app->registerProfile('SampleClass', $profile);
      $this->assertSame(
        ['sampleclass' => $profile],
        $app->getProfiles()
      );
    }

    public function testRegisterProfileUsingCallable() {
      $app = new Application();
      $app->registerProfile('SampleClass', $profile = [$this, 'callbackCreateObject']);
      $this->assertSame(
        ['sampleclass' => $profile],
        $app->getProfiles()
      );
    }

    public function callbackCreateObject() {
      return new \stdClass();
    }

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
        ['sampleclass' => $profileOne],
        $app->getProfiles()
      );
    }

    public function testRegisterProfileDuplicateOverwrite() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profileOne */
      $profileOne = $this->createMock(Application\Profile::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profileTwo */
      $profileTwo = $this->createMock(Application\Profile::class);
      $app = new Application();
      $app->registerProfile('SampleClass', $profileOne);
      $app->registerProfile('SampleClass', $profileTwo, Application::DUPLICATE_OVERWRITE);
      $this->assertSame(
        ['sampleclass' => $profileTwo],
        $app->getProfiles()
      );
    }

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

    public function testGetObjectAfterSet() {
      $object = new \stdClass();
      $app = new Application();
      $app->setObject('SAMPLE', $object);
      $this->assertSame(
        $object,
        $app->getObject('SAMPLE')
      );
    }

    public function testGetObjectWithoutSetExpectingException() {
      $app = new Application();
      $this->expectException(\InvalidArgumentException::class);
      $this->expectExceptionMessage('Unknown profile identifier:');
      $app->getObject('SAMPLE');
    }

    public function testGetObjectWithoutSetExpectingFalse() {
      $app = new Application();
      $this->assertNull($app->getObject('SAMPLE', TRUE));
    }

    public function testGetObjectWithProfile() {
      $object = new \stdClass();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profile */
      $profile = $this->createMock(Application\Profile::class);
      $profile
        ->expects($this->once())
        ->method('createObject')
        ->willReturn($object);
      $app = new Application();
      $app->registerProfile('SampleClass', $profile);
      $this->assertSame(
        $object,
        $app->getObject('SampleClass')
      );
    }

    public function testGetObjectWithCallable() {
      $app = new Application();
      $app->registerProfile('SampleClass', [$this, 'callbackCreateObject']);
      $this->assertInstanceOf(
        \stdClass::class,
        $app->getObject('SampleClass')
      );
    }

    public function testSetObject() {
      $object = new \stdClass();
      $app = new Application();
      $app->setObject(\stdClass::class, $object);
      $this->assertSame(
        ['stdclass' => $object],
        iterator_to_array($app)
      );
    }

    public function testSetObjectDuplicateError() {
      $app = new Application();
      $app->setObject('SampleClass', new \stdClass());
      $this->expectException(\LogicException::class);
      $this->expectExceptionMessage('Application object does already exists:');
      $app->setObject('SampleClass', new \stdClass());
    }

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

    public function testHasObjectExpectingFalse() {
      $app = new Application();
      $this->assertFalse(
        $app->hasObject('SampleClass')
      );
    }

    public function testHasObjectExpectingTrue() {
      $app = new Application();
      $app->setObject('SampleClass', new \stdClass());
      $this->assertTrue(
        $app->hasObject('SampleClass')
      );
    }

    public function testHasObjectWithProfileExpectingTrue() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profile */
      $profile = $this->createMock(Application\Profile::class);
      $app = new Application();
      $app->registerProfile('SampleClass', $profile);
      $this->assertTrue(
        $app->hasObject('SampleClass')
      );
    }

    public function testHasObjectWithProfileExpectingFalse() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profile */
      $profile = $this->createMock(Application\Profile::class);
      $app = new Application();
      $app->registerProfile('SampleClass', $profile);
      $this->assertFalse(
        $app->hasObject('SampleClass', FALSE)
      );
    }

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

    public function testRemoveObject() {
      $app = new Application();
      /** @noinspection PhpUndefinedFieldInspection */
      $app->sampleClass = new \stdClass();
      $app->removeObject('SampleClass');
      $this->assertFalse($app->hasObject('SampleClass'));
    }

    public function testRemoveObjectWhileOnlyProfileExists() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profile */
      $profile = $this->createMock(Application\Profile::class);
      $app = new Application();
      $app->registerProfile('SampleClass', $profile);
      $app->removeObject('SampleClass');
      $this->assertTrue($app->hasObject('SampleClass'));
    }

    public function testRemoveObjectUnknownExpectingException() {
      $app = new Application();
      $this->expectException(\InvalidArgumentException::class);
      $app->removeObject('SampleClass');
    }

    public function testMagicMethodGetWithProfile() {
      $object = new \stdClass();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profile */
      $profile = $this->createMock(Application\Profile::class);
      $profile
        ->expects($this->once())
        ->method('createObject')
        ->willReturn($object);
      $app = new Application();
      $app->registerProfile('SampleClass', $profile);
      /** @noinspection PhpUndefinedFieldInspection */
      $this->assertSame(
        $object,
        $app->SampleClass
      );
    }

    public function testMagicMethodSet() {
      $object = new \stdClass();
      $app = new Application();
      /** @noinspection PhpUndefinedFieldInspection */
      $app->stdClass = $object;
      $this->assertSame(
        ['stdclass' => $object],
        iterator_to_array($app)
      );
    }

    public function testMagicMethodSetWithInvalidValueExpectingException() {
      $app = new Application();
      $this->expectException(\UnexpectedValueException::class);
      /** @noinspection PhpUndefinedFieldInspection */
      $app->propertyName = 'INVALID_VALUE';
    }

    public function testMagicMethodIssetWithProfileExpectingTrue() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profile */
      $profile = $this->createMock(Application\Profile::class);
      $app = new Application();
      $app->registerProfile('SampleClass', $profile);
      $this->assertTrue(
        isset($app->SampleClass)
      );
    }

    public function testMagicMethodUnset() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profile */
      $app = new Application();
      $app->sample = new \stdClass;
      $this->assertTrue(isset($app->sample));
      unset($app->sample);
      $this->assertFalse(isset($app->sample));
    }

    public function testMagicMethodCall() {
      $app = new Application();
      /** @noinspection PhpUndefinedMethodInspection */
      $app->sampleClass($sample = new \stdClass());
      /** @noinspection PhpUndefinedMethodInspection */
      $this->assertSame($sample, $app->sampleClass());
    }

    public function testOffsetExistsExpectingTrue() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Application\Profile $profile */
      $profile = $this->createMock(Application\Profile::class);
      $app = new Application();
      $app->registerProfile('SampleClass', $profile);
      $this->assertTrue(
        isset($app['SampleClass'])
      );
    }

    public function testOffsetExistsExpectingFalse() {
      $app = new Application();
      $this->assertFalse(
        isset($app['SampleClass'])
      );
    }

    public function testOffsetGetAfterSet() {
      $app = new Application();
      $app['SampleClass'] = $object = new \stdClass();
      $this->assertSame(
        $object, $app['SampleClass']
      );
    }

    public function testOffsetUnset() {
      $app = new Application();
      $app['SampleClass'] = new \stdClass();
      unset($app['SampleClass']);
      $this->assertFalse(
        isset($app['SampleClass'])
      );
    }
  }
}
