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

namespace Papaya\UI\Dialog\Field {

  require_once __DIR__.'/../../../../../bootstrap.php';

  class FactoryTest extends \PapayaTestCase {

    /**
     * @covers \Papaya\UI\Dialog\Field\Factory::getProfile
     * @covers \Papaya\UI\Dialog\Field\Factory::getProfileClass
     */
    public function testGetProfile() {
      $factory = new Factory();
      $factory->registerProfiles(
        array(
          'dummy' => FactoryProfile_TestDummy::class
        )
      );
      $profile = $factory->getProfile('dummy');
      $this->assertInstanceOf(Factory\Profile::class, $profile);
    }

    /**
     * @covers \Papaya\UI\Dialog\Field\Factory::getProfile
     * @covers \Papaya\UI\Dialog\Field\Factory::getProfileClass
     */
    public function testGetProfileWithEmptyNameReturningInputField() {
      $factory = new Factory();
      $profile = $factory->getProfile('');
      $this->assertInstanceOf(Factory\Profile\Input::class, $profile);
    }

    /**
     * @covers \Papaya\UI\Dialog\Field\Factory::getProfile
     * @covers \Papaya\UI\Dialog\Field\Factory::getProfileClass
     */
    public function testGetProfileAutomaticNameMapping() {
      $factory = new Factory();
      $profile = $factory->getProfile('color');
      $this->assertInstanceOf(Factory\Profile::class, $profile);
    }

    /**
     * @covers \Papaya\UI\Dialog\Field\Factory::getProfile
     * @covers \Papaya\UI\Dialog\Field\Factory::getProfileClass
     */
    public function testGetProfileExpectingException() {
      $factory = new Factory();
      $this->expectException(Factory\Exception\InvalidProfile::class);
      $factory->getProfile('INVALID_PROFILE_CLASS_NAME');
    }

    /**
     * @covers \Papaya\UI\Dialog\Field\Factory::getField
     */
    public function testGetFieldWithProfile() {
      $profile = $this->createMock(Factory\Profile::class);
      $profile
        ->expects($this->once())
        ->method('getField')
        ->will($this->returnValue($this->createMock(\Papaya\UI\Dialog\Field::class)));
      $factory = new Factory();
      $this->assertInstanceOf(\Papaya\UI\Dialog\Field::class, $factory->getField($profile));
    }

    /**
     * @covers \Papaya\UI\Dialog\Field\Factory::getField
     */
    public function testGetFieldWithProfileAndOptions() {
      $options = $this->createMock(Factory\Options::class);
      $profile = $this->createMock(Factory\Profile::class);
      $profile
        ->expects($this->once())
        ->method('options')
        ->with($this->isInstanceOf(Factory\Options::class));
      $profile
        ->expects($this->once())
        ->method('getField')
        ->will($this->returnValue($this->createMock(\Papaya\UI\Dialog\Field::class)));
      $factory = new Factory();
      $this->assertInstanceOf(\Papaya\UI\Dialog\Field::class, $factory->getField($profile, $options));
    }

    /**
     * @covers \Papaya\UI\Dialog\Field\Factory::getField
     */
    public function testGetFieldWithProfileName() {
      $factory = new Factory();
      $factory->registerProfiles(
        array(
          'profileSample' => FactoryProfile_TestDummy::class
        )
      );
      $this->assertInstanceOf(\Papaya\UI\Dialog\Field::class, $factory->getField('profileSample'));
    }

    /**
     * @covers \Papaya\UI\Dialog\Field\Factory::registerProfiles
     */
    public function testRegisterProfiles() {
      $factory = new Factory();
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

  class FactoryProfile_TestDummy extends Factory\Profile {

    public function getField() {
      return new Input('Sample', 'sample');
    }
  }
}

