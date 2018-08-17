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

namespace Papaya\Filter\Locale\Germany;
require_once __DIR__.'/../../../../../bootstrap.php';

class ZipTest extends \Papaya\TestCase {

  /**
   * @covers       \Papaya\Filter\Locale\Germany\Zip::__construct
   * @dataProvider providerConstructor
   * @param bool|NULL $value
   */
  public function testConstruct($value) {
    $filter = new Zip($value);
    $this->assertAttributeEquals($value, '_allowCountryPrefix', $filter);
  }

  /**
   * @covers \Papaya\Filter\Locale\Germany\Zip::__construct
   */
  public function testConstructWithoutArgument() {
    $filter = new Zip();
    $this->assertAttributeEquals(NULL, '_allowCountryPrefix', $filter);
  }

  /**
   * @covers \Papaya\Filter\Locale\Germany\Zip::validate
   */
  public function testValidate() {
    $filter = new Zip();
    $this->assertTrue($filter->validate('12345'));
  }

  /**
   * @covers \Papaya\Filter\Locale\Germany\Zip::validate
   */
  public function testValidateExpectCharacterInvalidException() {
    $filter = new Zip(TRUE);
    $this->expectException(\Papaya\Filter\Exception\InvalidCharacter::class);
    $filter->validate('11235');
  }

  /**
   * @covers \Papaya\Filter\Locale\Germany\Zip::validate
   */
  public function testValidateExpectLengthMinimumException() {
    $filter = new Zip();
    $this->expectException(\Papaya\Filter\Exception\InvalidLength\ToShort::class);
    $filter->validate('123');
  }

  /**
   * @covers \Papaya\Filter\Locale\Germany\Zip::validate
   */
  public function testValidateExpectLengthMaximumException() {
    $filter = new Zip();
    $this->expectException(\Papaya\Filter\Exception\InvalidLength\ToLong::class);
    $filter->validate('342423432424');
  }

  /**
   * @covers \Papaya\Filter\Locale\Germany\Zip::validate
   */
  public function testValidateExpectCharacterInvalidExceptionInPostalcode() {
    $filter = new Zip();
    $this->expectException(\Papaya\Filter\Exception\InvalidCharacter::class);
    $filter->validate('23a91');
  }

  /**
   * @covers \Papaya\Filter\Locale\Germany\Zip::filter
   */
  public function testFilter() {
    $filter = new Zip();
    $this->assertEquals('12345', $filter->filter('12345'));
  }

  /**
   * @covers \Papaya\Filter\Locale\Germany\Zip::filter
   */
  public function testFilterExpectsFilterException() {
    $filter = new Zip();
    $this->assertNull($filter->filter('78asdblnnlnltest'));
  }

  /************************
   * Data Provider
   ************************/

  public static function providerConstructor() {
    return array(
      array(NULL),
      array(TRUE),
      array(FALSE)
    );
  }

}
