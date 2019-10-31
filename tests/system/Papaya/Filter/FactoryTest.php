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

namespace Papaya\Filter {

  use Papaya\TestCase;

  require_once __DIR__.'/../../../bootstrap.php';

  /**
   * @covers \Papaya\Filter\Factory
   */
  class FactoryTest extends TestCase {

    public function testGetIterator() {
      $factory = new Factory();
      $this->assertContains('isEmail', $factory);
      $this->assertContains('isText', $factory);
    }

    public function testHasProfile() {
      $factory = new Factory();
      $this->assertTrue($factory->hasProfile('isXml'));
    }

    public function testHasProfileWithLowercaseName() {
      $factory = new Factory();
      $this->assertTrue($factory->hasProfile('isemail'));
    }

    public function testHasProfileExpectingFalse() {
      $factory = new Factory();
      $this->assertFalse($factory->hasProfile('invalidValidationFilter'));
    }

    public function testGetProfile() {
      $factory = new Factory();
      $this->assertInstanceOf(Factory\Profile::class, $factory->getProfile('isEmail'));
    }

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
        ->willReturn($this->createMock(\Papaya\Filter::class));
      $factory = new Factory();
      $filter = $factory->getFilter($profile);
      $this->assertInstanceOf(\Papaya\Filter::class, $filter);
      $this->assertNotInstanceOf(LogicalOr::class, $filter);
    }

    public function testGetFilterNotMandatory() {
      $profile = $this->createMock(Factory\Profile::class);
      $profile
        ->expects($this->never())
        ->method('options');
      $profile
        ->expects($this->once())
        ->method('getFilter')
        ->willReturn($this->createMock(\Papaya\Filter::class));
      $factory = new Factory();
      $filter = $factory->getFilter($profile, FALSE);
      $this->assertInstanceOf(LogicalOr::class, $filter);
    }

    public function testGetFilterNotMandatoryWithOptions() {
      $profile = $this->createMock(Factory\Profile::class);
      $profile
        ->expects($this->once())
        ->method('options')
        ->with('data');
      $profile
        ->expects($this->once())
        ->method('getFilter')
        ->willReturn($this->createMock(\Papaya\Filter::class));
      $factory = new Factory();
      $filter = $factory->getFilter($profile, TRUE, 'data');
      $this->assertInstanceOf(\Papaya\Filter::class, $filter);
    }

    public function testGetFilterWithNamedProfile() {
      $factory = new Factory();
      $this->assertInstanceOf(\Papaya\Filter::class, $factory->getFilter(\Papaya\Filter::IS_EMAIL));
    }

    public function testValidateWithProfileNameExpectingTrue() {
      $this->assertTrue(
        Factory::validate('foo@bar.tld', \Papaya\Filter::IS_EMAIL)
      );
    }

    public function testValidateWithEmptyValueExpectingFalse() {
      $this->assertFalse(
        Factory::validate('', \Papaya\Filter::IS_EMAIL)
      );
    }

    public function testValidateWithEmptyValueNotMandatoryExpectingTrue() {
      $this->assertTrue(
        Factory::validate('', \Papaya\Filter::IS_EMAIL, FALSE)
      );
    }

    public function testMatches() {
      $this->assertTrue(
        Factory::matches('foo', '(^[a-z]+$)')
      );
    }

    public function testMatchesExpectingFalse() {
      $this->assertFalse(
        Factory::matches('', '(^[a-z]+$)')
      );
    }

    public function testMatchesNotMandatory() {
      $this->assertTrue(
        Factory::matches('', '(^[a-z]+$)', FALSE)
      );
    }

    public function testFilterCastsValue() {
      $this->assertSame(
        42,
        Factory::filter('42', \Papaya\Filter::IS_INTEGER)
      );
    }

    public function testValidateUsingCallStaticMagicMethod() {
      $this->assertTrue(
        Factory::isEmail('foo@bar.tld')
      );
    }

    public function testValidateUsingCallStaticMagicMethodExpectingFalse() {
      $this->assertFalse(
        Factory::isEmail('')
      );
    }

    public function testValidateUsingCallStaticMagicMethodNotMandatory() {
      $this->assertTrue(
        Factory::isEmail('', FALSE)
      );
    }

    public function testValidateUsingCallStaticMagicMethodWithoutArguments() {
      $this->expectException(\InvalidArgumentException::class);
      /** @noinspection PhpParamsInspection */
      Factory::isEmail();
    }

    public function testCallUnknownFunctionExpectingException() {
      $this->expectException(\LogicException::class);
      /** @noinspection PhpUndefinedMethodInspection */
      Factory::someUnknownFunction();
    }
  }
}
