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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiDialogFieldFactoryTest extends PapayaTestCase {

  /**
   * @covers \PapayaUiDialogFieldFactory::getProfile
   * @covers \PapayaUiDialogFieldFactory::getProfileClass
   */
  public function testGetProfile() {
    $factory = new \PapayaUiDialogFieldFactory();
    $factory->registerProfiles(
      array(
        'dummy' => PapayaUiDialogFieldFactoryProfile_TestDummy::class
      )
    );
    $profile = $factory->getProfile('dummy');
    $this->assertInstanceOf(PapayaUiDialogFieldFactoryProfile::class, $profile);
  }

  /**
   * @covers \PapayaUiDialogFieldFactory::getProfile
   * @covers \PapayaUiDialogFieldFactory::getProfileClass
   */
  public function testGetProfileWihtEmptyNameReturingInputField() {
    $factory = new \PapayaUiDialogFieldFactory();
    $profile = $factory->getProfile('');
    $this->assertInstanceOf(PapayaUiDialogFieldFactoryProfileInput::class, $profile);
  }

  /**
   * @covers \PapayaUiDialogFieldFactory::getProfile
   * @covers \PapayaUiDialogFieldFactory::getProfileClass
   */
  public function testGetProfileAutomaticNameMapping() {
    $factory = new \PapayaUiDialogFieldFactory();
    $profile = $factory->getProfile('color');
    $this->assertInstanceOf(PapayaUiDialogFieldFactoryProfile::class, $profile);
  }

  /**
   * @covers \PapayaUiDialogFieldFactory::getProfile
   * @covers \PapayaUiDialogFieldFactory::getProfileClass
   */
  public function testGetProfileExpectingException() {
    $factory = new \PapayaUiDialogFieldFactory();
    $this->expectException(PapayaUiDialogFieldFactoryExceptionInvalidProfile::class);
    $factory->getProfile('INVALID_PROFILE_CLASS_NAME');
  }

  /**
   * @covers \PapayaUiDialogFieldFactory::getField
   */
  public function testGetFieldWithProfile() {
    $profile = $this->createMock(PapayaUiDialogFieldFactoryProfile::class);
    $profile
      ->expects($this->once())
      ->method('getField')
      ->will($this->returnValue($this->createMock(PapayaUiDialogField::class)));
    $factory = new \PapayaUiDialogFieldFactory();
    $this->assertInstanceOf(PapayaUiDialogField::class, $factory->getField($profile));
  }

  /**
   * @covers \PapayaUiDialogFieldFactory::getField
   */
  public function testGetFieldWithProfileAndOptions() {
    $options = $this->createMock(PapayaUiDialogFieldFactoryOptions::class);
    $profile = $this->createMock(PapayaUiDialogFieldFactoryProfile::class);
    $profile
      ->expects($this->once())
      ->method('options')
      ->with($this->isInstanceOf(PapayaUiDialogFieldFactoryOptions::class));
    $profile
      ->expects($this->once())
      ->method('getField')
      ->will($this->returnValue($this->createMock(PapayaUiDialogField::class)));
    $factory = new \PapayaUiDialogFieldFactory();
    $this->assertInstanceOf(PapayaUiDialogField::class, $factory->getField($profile, $options));
  }

  /**
   * @covers \PapayaUiDialogFieldFactory::getField
   */
  public function testGetFieldWithProfileName() {
    $factory = new \PapayaUiDialogFieldFactory();
    $factory->registerProfiles(
      array(
        'profileSample' => PapayaUiDialogFieldFactoryProfile_TestDummy::class
      )
    );
    $this->assertInstanceOf(PapayaUiDialogField::class, $factory->getField('profileSample'));
  }

  /**
   * @covers \PapayaUiDialogFieldFactory::registerProfiles
   */
  public function testRegisterProfiles() {
    $factory = new \PapayaUiDialogFieldFactory();
    $factory->registerProfiles(
      array(
        'foo' => 'SampleOne',
        'BAR' => 'SampleTwo',
        'foo_bar' => 'SampleTree',
        'BarFoo' => 'SampleFour'
      )
    );
    $this->assertAttributeEquals(
      array(
        'Foo' => 'SampleOne',
        'Bar' => 'SampleTwo',
        'FooBar' => 'SampleTree',
        'BarFoo' => 'SampleFour'
      ),
      '_profiles',
      $factory
    );
  }
}

class PapayaUiDialogFieldFactoryProfile_TestDummy extends PapayaUiDialogFieldFactoryProfile {

  public function getField() {
    return new \PapayaUiDialogFieldInput('Sample', 'sample');
  }
}

