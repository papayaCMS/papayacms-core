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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterFactoryTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testGetIterator() {
    $factory = new \Papaya\Filter\Factory();
    $this->assertContains('isEmail', $factory);
    $this->assertContains('isText', $factory);
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testHasProfile() {
    $factory = new \Papaya\Filter\Factory();
    $this->assertTrue($factory->hasProfile('isEmail'));
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testHasProfileWithLowercaseName() {
    $factory = new \Papaya\Filter\Factory();
    $this->assertTrue($factory->hasProfile('isemail'));
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testHasProfileExpectingFalse() {
    $factory = new \Papaya\Filter\Factory();
    $this->assertFalse($factory->hasProfile('invalidValidationFilter'));
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testGetProfile() {
    $factory = new \Papaya\Filter\Factory();
    $this->assertInstanceOf(\Papaya\Filter\Factory\Profile::class, $factory->getProfile('isEmail'));
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testGetProfileExpectingException() {
    $factory = new \Papaya\Filter\Factory();
    $this->expectException(\Papaya\Filter\Factory\Exception\InvalidProfile::class);
    $factory->getProfile('SomeInvalidProfileName');
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testGetFilter() {
    $profile = $this->createMock(\Papaya\Filter\Factory\Profile::class);
    $profile
      ->expects($this->never())
      ->method('options');
    $profile
      ->expects($this->once())
      ->method('getFilter')
      ->will($this->returnValue($this->createMock(\Papaya\Filter::class)));
    $factory = new \Papaya\Filter\Factory();
    $filter = $factory->getFilter($profile);
    $this->assertInstanceOf(\Papaya\Filter::class, $filter);
    $this->assertNotInstanceOf(\Papaya\Filter\LogicalOr::class, $filter);
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testGetFilterNotMandatory() {
    $profile = $this->createMock(\Papaya\Filter\Factory\Profile::class);
    $profile
      ->expects($this->never())
      ->method('options');
    $profile
      ->expects($this->once())
      ->method('getFilter')
      ->will($this->returnValue($this->createMock(\Papaya\Filter::class)));
    $factory = new \Papaya\Filter\Factory();
    $filter = $factory->getFilter($profile, FALSE);
    $this->assertInstanceOf(\Papaya\Filter\LogicalOr::class, $filter);
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testGetFilterNotMandatoryWithOptions() {
    $profile = $this->createMock(\Papaya\Filter\Factory\Profile::class);
    $profile
      ->expects($this->once())
      ->method('options')
      ->with('data');
    $profile
      ->expects($this->once())
      ->method('getFilter')
      ->will($this->returnValue($this->createMock(\Papaya\Filter::class)));
    $factory = new \Papaya\Filter\Factory();
    $filter = $factory->getFilter($profile, TRUE, 'data');
    $this->assertInstanceOf(\Papaya\Filter::class, $filter);
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testGetFilterWithNamedProfile() {
    $factory = new \Papaya\Filter\Factory();
    $this->assertInstanceOf(\Papaya\Filter::class, $factory->getFilter(\Papaya\Filter::IS_EMAIL));
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testValidateWithProfileNameExpectingTrue() {
    $this->assertTrue(
      \Papaya\Filter\Factory::validate('foo@bar.tld', \Papaya\Filter::IS_EMAIL)
    );
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testValidateWithEmptyValueExpectingFalse() {
    $this->assertFalse(
      \Papaya\Filter\Factory::validate('', \Papaya\Filter::IS_EMAIL)
    );
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testValidateWithEmptyValueNotMandatoryExpectingTrue() {
    $this->assertTrue(
      \Papaya\Filter\Factory::validate('', \Papaya\Filter::IS_EMAIL, FALSE)
    );
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testMatches() {
    $this->assertTrue(
      \Papaya\Filter\Factory::matches('foo', '(^[a-z]+$)')
    );
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testMatchesExpectingFalse() {
    $this->assertFalse(
      \Papaya\Filter\Factory::matches('', '(^[a-z]+$)')
    );
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testMatchesNotMandatory() {
    $this->assertTrue(
      \Papaya\Filter\Factory::matches('', '(^[a-z]+$)', FALSE)
    );
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testFilterCastsValue() {
    $this->assertSame(
      42,
      \Papaya\Filter\Factory::filter('42', \Papaya\Filter::IS_INTEGER)
    );
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testValidateUsingCallStaticMagicMethod() {
    $this->assertTrue(
      \Papaya\Filter\Factory::isEmail('foo@bar.tld')
    );
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testValidateUsingCallStaticMagicMethodExpectingFalse() {
    $this->assertFalse(
      \Papaya\Filter\Factory::isEmail('')
    );
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testValidateUsingCallStaticMagicMethodNotMandatory() {
    $this->assertTrue(
      \Papaya\Filter\Factory::isEmail('', FALSE)
    );
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testValidateUsingCallStaticMagicMethodWithoutArguments() {
    $this->expectException(\InvalidArgumentException::class);
    /** @noinspection PhpParamsInspection */
    \Papaya\Filter\Factory::isEmail();
  }

  /**
   * @covers \Papaya\Filter\Factory
   */
  public function testCallUnknownFunctionExpectingException() {
    $this->expectException(\LogicException::class);
    /** @noinspection PhpUndefinedMethodInspection */
    \Papaya\Filter\Factory::someUnknownFunction();
  }
}
