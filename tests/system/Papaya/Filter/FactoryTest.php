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

class PapayaFilterFactoryTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactory
   */
  public function testGetIterator() {
    $factory = new PapayaFilterFactory();
    $this->assertContains('isEmail', $factory);
    $this->assertContains('isText', $factory);
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testHasProfile() {
    $factory = new PapayaFilterFactory();
    $this->assertTrue($factory->hasProfile('isEmail'));
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testHasProfileWithLowercaseName() {
    $factory = new PapayaFilterFactory();
    $this->assertTrue($factory->hasProfile('isemail'));
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testHasProfileExpectingFalse() {
    $factory = new PapayaFilterFactory();
    $this->assertFalse($factory->hasProfile('invalidValidationFilter'));
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testGetProfile() {
    $factory = new PapayaFilterFactory();
    $this->assertInstanceOf(PapayaFilterFactoryProfile::class, $factory->getProfile('isEmail'));
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testGetProfileExpectingException() {
    $factory = new PapayaFilterFactory();
    $this->expectException(PapayaFilterFactoryExceptionInvalidProfile::class);
    $factory->getProfile('SomeInvalidProfileName');
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testGetFilter() {
    $profile = $this->createMock(PapayaFilterFactoryProfile::class);
    $profile
      ->expects($this->never())
      ->method('options');
    $profile
      ->expects($this->once())
      ->method('getFilter')
      ->will($this->returnValue($this->createMock(PapayaFilter::class)));
    $factory = new PapayaFilterFactory();
    $filter = $factory->getFilter($profile);
    $this->assertInstanceOf(PapayaFilter::class, $filter);
    $this->assertNotInstanceOf(PapayaFilterLogicalOr::class, $filter);
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testGetFilterNotMandatory() {
    $profile = $this->createMock(PapayaFilterFactoryProfile::class);
    $profile
      ->expects($this->never())
      ->method('options');
    $profile
      ->expects($this->once())
      ->method('getFilter')
      ->will($this->returnValue($this->createMock(PapayaFilter::class)));
    $factory = new PapayaFilterFactory();
    $filter = $factory->getFilter($profile, FALSE);
    $this->assertInstanceOf(PapayaFilterLogicalOr::class, $filter);
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testGetFilterNotMandatoryWithOptions() {
    $profile = $this->createMock(PapayaFilterFactoryProfile::class);
    $profile
      ->expects($this->once())
      ->method('options')
      ->with('data');
    $profile
      ->expects($this->once())
      ->method('getFilter')
      ->will($this->returnValue($this->createMock(PapayaFilter::class)));
    $factory = new PapayaFilterFactory();
    $filter = $factory->getFilter($profile, TRUE, 'data');
    $this->assertInstanceOf(PapayaFilter::class, $filter);
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testGetFilterWithNamedProfile() {
    $factory = new PapayaFilterFactory();
    $this->assertInstanceOf(PapayaFilter::class, $factory->getFilter(PapayaFilter::IS_EMAIL));
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testValidateWithProfileNameExpectingTrue() {
    $this->assertTrue(
      PapayaFilterFactory::validate('foo@bar.tld', PapayaFilter::IS_EMAIL)
    );
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testValidateWithEmptyValueExpectingFalse() {
    $this->assertFalse(
      PapayaFilterFactory::validate('', PapayaFilter::IS_EMAIL)
    );
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testValidateWithEmptyValueNotMandatoryExpectingTrue() {
    $this->assertTrue(
      PapayaFilterFactory::validate('', PapayaFilter::IS_EMAIL, FALSE)
    );
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testMatches() {
    $this->assertTrue(
      PapayaFilterFactory::matches('foo', '(^[a-z]+$)')
    );
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testMatchesExpectingFalse() {
    $this->assertFalse(
      PapayaFilterFactory::matches('', '(^[a-z]+$)')
    );
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testMatchesNotMandatory() {
    $this->assertTrue(
      PapayaFilterFactory::matches('', '(^[a-z]+$)', FALSE)
    );
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testFilterCastsValue() {
    $this->assertSame(
      42,
      PapayaFilterFactory::filter('42', PapayaFilter::IS_INTEGER)
    );
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testValidateUsingCallStaticMagicMethod() {
    $this->assertTrue(
      PapayaFilterFactory::isEmail('foo@bar.tld')
    );
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testValidateUsingCallStaticMagicMethodExpectingFalse() {
    $this->assertFalse(
      PapayaFilterFactory::isEmail('')
    );
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testValidateUsingCallStaticMagicMethodNotMandatory() {
    $this->assertTrue(
      PapayaFilterFactory::isEmail('', FALSE)
    );
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testValidateUsingCallStaticMagicMethodWithoutArguments() {
    $this->expectException(InvalidArgumentException::class);
    /** @noinspection PhpParamsInspection */
    PapayaFilterFactory::isEmail();
  }

  /**
   * @covers PapayaFilterFactory
   */
  public function testCallUnknownFunctionExpectingException() {
    $this->expectException(LogicException::class);
    /** @noinspection PhpUndefinedMethodInspection */
    PapayaFilterFactory::someUnknownFunction();
  }
}
