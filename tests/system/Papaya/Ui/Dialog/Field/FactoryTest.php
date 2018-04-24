<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiDialogFieldFactoryTest extends PapayaTestCase {

  /**
   * @param PapayaUiDialogFieldFactory::getProfile
   * @param PapayaUiDialogFieldFactory::getProfileClass
   */
  public function testGetProfile() {
    $factory = new PapayaUiDialogFieldFactory();
    $factory->registerProfiles(
      array(
        'dummy' => PapayaUiDialogFieldFactoryProfile_TestDummy::class
      )
    );
    $profile = $factory->getProfile('dummy');
    $this->assertInstanceOf(PapayaUiDialogFieldFactoryProfile::class, $profile);
  }

  /**
   * @param PapayaUiDialogFieldFactory::getProfile
   * @param PapayaUiDialogFieldFactory::getProfileClass
   */
  public function testGetProfileWihtEmptyNameReturingInputField() {
    $factory = new PapayaUiDialogFieldFactory();
    $profile = $factory->getProfile('');
    $this->assertInstanceOf(PapayaUiDialogFieldFactoryProfileInput::class, $profile);
  }

  /**
   * @param PapayaUiDialogFieldFactory::getProfile
   * @param PapayaUiDialogFieldFactory::getProfileClass
   */
  public function testGetProfileAutomaticNameMapping() {
    $factory = new PapayaUiDialogFieldFactory();
    $profile = $factory->getProfile('color');
    $this->assertInstanceOf(PapayaUiDialogFieldFactoryProfile::class, $profile);
  }

  /**
   * @param PapayaUiDialogFieldFactory::getProfile
   * @param PapayaUiDialogFieldFactory::getProfileClass
   */
  public function testGetProfileExpectingException() {
    $factory = new PapayaUiDialogFieldFactory();
    $this->setExpectedException(PapayaUiDialogFieldFactoryExceptionInvalidProfile::class);
    $profile = $factory->getProfile('INVALIDE_PROFILE_CLASSNAME');
  }

  /**
   * @param PapayaUiDialogFieldFactory::getField
   */
  public function testGetFieldWithProfile() {
    $profile = $this->createMock(PapayaUiDialogFieldFactoryProfile::class);
    $profile
      ->expects($this->once())
      ->method('getField')
      ->will($this->returnValue($this->createMock(PapayaUiDialogField::class)));
    $factory = new PapayaUiDialogFieldFactory();
    $this->assertInstanceOf(PapayaUiDialogField::class, $factory->getField($profile));
  }

  /**
   * @param PapayaUiDialogFieldFactory::getField
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
    $factory = new PapayaUiDialogFieldFactory();
    $this->assertInstanceOf(PapayaUiDialogField::class, $factory->getField($profile, $options));
  }

  /**
   * @param PapayaUiDialogFieldFactory::getField
   */
  public function testGetFieldWithProfileName() {
    $factory = new PapayaUiDialogFieldFactory();
    $factory->registerProfiles(
      array(
        'profileSample' => PapayaUiDialogFieldFactoryProfile_TestDummy::class
      )
    );
    $this->assertInstanceOf(PapayaUiDialogField::class, $factory->getField('profileSample'));
  }

  /**
   * @param PapayaUiDialogFieldFactory::registerProfiles
   */
  public function testRegisterProfiles() {
    $factory = new PapayaUiDialogFieldFactory();
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
    return new PapayaUiDialogFieldInput('Sample', 'sample');
  }
}

