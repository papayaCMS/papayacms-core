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

namespace Papaya\Filter;
require_once __DIR__.'/../../../bootstrap.php';

class FactoryTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testGetIterator() {
    $factory = new Factory();
    $this->assertContains('isEmail', $factory);
    $this->assertContains('isText', $factory);
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testHasProfile() {
    $factory = new Factory();
    $this->assertTrue($factory->hasProfile('isEmail'));
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testHasProfileWithLowercaseName() {
    $factory = new Factory();
    $this->assertTrue($factory->hasProfile('isemail'));
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testHasProfileExpectingFalse() {
    $factory = new Factory();
    $this->assertFalse($factory->hasProfile('invalidValidationFilter'));
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testGetProfile() {
    $factory = new Factory();
    $this->assertInstanceOf(Factory\Profile::class, $factory->getProfile('isEmail'));
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testGetProfileExpectingException() {
    $factory = new Factory();
    $this->expectException(Factory\Exception\InvalidProfile::class);
    $factory->getProfile('SomeInvalidProfileName');
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testGetFilter() {
    $profile = $this->createMock(Factory\Profile::class);
    $profile
      ->expects($this->never())
      ->method('options');
    $profile
      ->expects($this->once())
      ->method('getFilter')
      ->will($this->returnValue($this->createMock(\Papaya\Filter::class)));
    $factory = new Factory();
    $filter = $factory->getFilter($profile);
    $this->assertInstanceOf(\Papaya\Filter::class, $filter);
    $this->assertNotInstanceOf(LogicalOr::class, $filter);
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testGetFilterNotMandatory() {
    $profile = $this->createMock(Factory\Profile::class);
    $profile
      ->expects($this->never())
      ->method('options');
    $profile
      ->expects($this->once())
      ->method('getFilter')
      ->will($this->returnValue($this->createMock(\Papaya\Filter::class)));
    $factory = new Factory();
    $filter = $factory->getFilter($profile, FALSE);
    $this->assertInstanceOf(LogicalOr::class, $filter);
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testGetFilterNotMandatoryWithOptions() {
    $profile = $this->createMock(Factory\Profile::class);
    $profile
      ->expects($this->once())
      ->method('options')
      ->with('data');
    $profile
      ->expects($this->once())
      ->method('getFilter')
      ->will($this->returnValue($this->createMock(\Papaya\Filter::class)));
    $factory = new Factory();
    $filter = $factory->getFilter($profile, TRUE, 'data');
    $this->assertInstanceOf(\Papaya\Filter::class, $filter);
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testGetFilterWithNamedProfile() {
    $factory = new Factory();
    $this->assertInstanceOf(\Papaya\Filter::class, $factory->getFilter(\Papaya\Filter::IS_EMAIL));
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testValidateWithProfileNameExpectingTrue() {
    $this->assertTrue(
      Factory::validate('foo@bar.tld', \Papaya\Filter::IS_EMAIL)
    );
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testValidateWithEmptyValueExpectingFalse() {
    $this->assertFalse(
      Factory::validate('', \Papaya\Filter::IS_EMAIL)
    );
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testValidateWithEmptyValueNotMandatoryExpectingTrue() {
    $this->assertTrue(
      Factory::validate('', \Papaya\Filter::IS_EMAIL, FALSE)
    );
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testMatches() {
    $this->assertTrue(
      Factory::matches('foo', '(^[a-z]+$)')
    );
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testMatchesExpectingFalse() {
    $this->assertFalse(
      Factory::matches('', '(^[a-z]+$)')
    );
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testMatchesNotMandatory() {
    $this->assertTrue(
      Factory::matches('', '(^[a-z]+$)', FALSE)
    );
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testFilterCastsValue() {
    $this->assertSame(
      42,
      Factory::filter('42', \Papaya\Filter::IS_INTEGER)
    );
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testValidateUsingCallStaticMagicMethod() {
    $this->assertTrue(
      Factory::isEmail('foo@bar.tld')
    );
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testValidateUsingCallStaticMagicMethodExpectingFalse() {
    $this->assertFalse(
      Factory::isEmail('')
    );
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testValidateUsingCallStaticMagicMethodNotMandatory() {
    $this->assertTrue(
      Factory::isEmail('', FALSE)
    );
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testValidateUsingCallStaticMagicMethodWithoutArguments() {
    $this->expectException(\InvalidArgumentException::class);
    /** @noinspection PhpParamsInspection */
    Factory::isEmail();
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testCallUnknownFunctionExpectingException() {
    $this->expectException(\LogicException::class);
    /** @noinspection PhpUndefinedMethodInspection */
    Factory::someUnknownFunction();
  }
}
