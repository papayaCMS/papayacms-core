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

class PapayaUiDialogFieldFactoryTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Ui\Dialog\Field\Factory::getProfile
   * @covers \Papaya\Ui\Dialog\Field\Factory::getProfileClass
   */
  public function testGetProfile() {
    $factory = new \Papaya\Ui\Dialog\Field\Factory();
    $factory->registerProfiles(
      array(
        'dummy' => \PapayaUiDialogFieldFactoryProfile_TestDummy::class
      )
    );
    $profile = $factory->getProfile('dummy');
    $this->assertInstanceOf(\Papaya\Ui\Dialog\Field\Factory\Profile::class, $profile);
  }

  /**
   * @covers \Papaya\Ui\Dialog\Field\Factory::getProfile
   * @covers \Papaya\Ui\Dialog\Field\Factory::getProfileClass
   */
  public function testGetProfileWihtEmptyNameReturingInputField() {
    $factory = new \Papaya\Ui\Dialog\Field\Factory();
    $profile = $factory->getProfile('');
    $this->assertInstanceOf(\Papaya\Ui\Dialog\Field\Factory\Profile\Input::class, $profile);
  }

  /**
   * @covers \Papaya\Ui\Dialog\Field\Factory::getProfile
   * @covers \Papaya\Ui\Dialog\Field\Factory::getProfileClass
   */
  public function testGetProfileAutomaticNameMapping() {
    $factory = new \Papaya\Ui\Dialog\Field\Factory();
    $profile = $factory->getProfile('color');
    $this->assertInstanceOf(\Papaya\Ui\Dialog\Field\Factory\Profile::class, $profile);
  }

  /**
   * @covers \Papaya\Ui\Dialog\Field\Factory::getProfile
   * @covers \Papaya\Ui\Dialog\Field\Factory::getProfileClass
   */
  public function testGetProfileExpectingException() {
    $factory = new \Papaya\Ui\Dialog\Field\Factory();
    $this->expectException(\Papaya\Ui\Dialog\Field\Factory\Exception\InvalidProfile::class);
    $factory->getProfile('INVALID_PROFILE_CLASS_NAME');
  }

  /**
   * @covers \Papaya\Ui\Dialog\Field\Factory::getField
   */
  public function testGetFieldWithProfile() {
    $profile = $this->createMock(\Papaya\Ui\Dialog\Field\Factory\Profile::class);
    $profile
      ->expects($this->once())
      ->method('getField')
      ->will($this->returnValue($this->createMock(\Papaya\Ui\Dialog\Field::class)));
    $factory = new \Papaya\Ui\Dialog\Field\Factory();
    $this->assertInstanceOf(\Papaya\Ui\Dialog\Field::class, $factory->getField($profile));
  }

  /**
   * @covers \Papaya\Ui\Dialog\Field\Factory::getField
   */
  public function testGetFieldWithProfileAndOptions() {
    $options = $this->createMock(\Papaya\Ui\Dialog\Field\Factory\Options::class);
    $profile = $this->createMock(\Papaya\Ui\Dialog\Field\Factory\Profile::class);
    $profile
      ->expects($this->once())
      ->method('options')
      ->with($this->isInstanceOf(\Papaya\Ui\Dialog\Field\Factory\Options::class));
    $profile
      ->expects($this->once())
      ->method('getField')
      ->will($this->returnValue($this->createMock(\Papaya\Ui\Dialog\Field::class)));
    $factory = new \Papaya\Ui\Dialog\Field\Factory();
    $this->assertInstanceOf(\Papaya\Ui\Dialog\Field::class, $factory->getField($profile, $options));
  }

  /**
   * @covers \Papaya\Ui\Dialog\Field\Factory::getField
   */
  public function testGetFieldWithProfileName() {
    $factory = new \Papaya\Ui\Dialog\Field\Factory();
    $factory->registerProfiles(
      array(
        'profileSample' => \PapayaUiDialogFieldFactoryProfile_TestDummy::class
      )
    );
    $this->assertInstanceOf(\Papaya\Ui\Dialog\Field::class, $factory->getField('profileSample'));
  }

  /**
   * @covers \Papaya\Ui\Dialog\Field\Factory::registerProfiles
   */
  public function testRegisterProfiles() {
    $factory = new \Papaya\Ui\Dialog\Field\Factory();
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

class PapayaUiDialogFieldFactoryProfile_TestDummy extends \Papaya\Ui\Dialog\Field\Factory\Profile {

  public function getField() {
    return new \Papaya\Ui\Dialog\Field\Input('Sample', 'sample');
  }
}

