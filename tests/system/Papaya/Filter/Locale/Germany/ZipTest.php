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

class PapayaFilterLocaleGermanyZipTest extends \PapayaTestCase {

  /**
   * @covers \PapayaFilterLocaleGermanyZip::__construct
   * @dataProvider providerConstructor
   * @param bool|NULL $value
   */
  public function testConstruct($value) {
    $filter = new \PapayaFilterLocaleGermanyZip($value);
    $this->assertAttributeEquals($value, '_allowCountryPrefix', $filter);
  }

  /**
  * @covers \PapayaFilterLocaleGermanyZip::__construct
  */
  public function testConstructWithoutArgument() {
    $filter = new \PapayaFilterLocaleGermanyZip();
    $this->assertAttributeEquals(NULL, '_allowCountryPrefix', $filter);
  }

  /**
  * @covers \PapayaFilterLocaleGermanyZip::validate
  */
  public function testValidate() {
    $filter = new \PapayaFilterLocaleGermanyZip();
    $this->assertTrue($filter->validate('12345'));
  }

  /**
  * @covers \PapayaFilterLocaleGermanyZip::validate
  */
  public function testValidateExpectCharacterInvalidException() {
    $filter = new \PapayaFilterLocaleGermanyZip(TRUE);
    $this->expectException(\Papaya\Filter\Exception\InvalidCharacter::class);
    $filter->validate('11235');
  }

  /**
  * @covers \PapayaFilterLocaleGermanyZip::validate
  */
  public function testValidateExpectLengthMinimumException() {
    $filter = new \PapayaFilterLocaleGermanyZip();
    $this->expectException(\Papaya\Filter\Exception\InvalidLength\ToShort::class);
    $filter->validate('123');
  }

  /**
  * @covers \PapayaFilterLocaleGermanyZip::validate
  */
  public function testValidateExpectLengthMaximumException() {
    $filter = new \PapayaFilterLocaleGermanyZip();
    $this->expectException(\Papaya\Filter\Exception\InvalidLength\ToLong::class);
    $filter->validate('342423432424');
  }

  /**
  * @covers \PapayaFilterLocaleGermanyZip::validate
  */
  public function testValidateExpectCharacterInvalidExceptionInPostalcode() {
    $filter = new \PapayaFilterLocaleGermanyZip();
    $this->expectException(\Papaya\Filter\Exception\InvalidCharacter::class);
    $filter->validate('23a91');
  }

  /**
  * @covers \PapayaFilterLocaleGermanyZip::filter
  */
  public function testFilter() {
    $filter = new \PapayaFilterLocaleGermanyZip();
    $this->assertEquals('12345', $filter->filter('12345'));
  }

  /**
  * @covers \PapayaFilterLocaleGermanyZip::filter
  */
  public function testFilterExpectsFilterException() {
    $filter = new \PapayaFilterLocaleGermanyZip();
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
