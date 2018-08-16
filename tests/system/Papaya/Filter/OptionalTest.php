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

class OptionalTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Filter\Optional::__construct
   * @covers \Papaya\Filter\Optional::getInnerFilter
   */
  public function testConstructor() {
    $filter = new Optional($innerFilter = new IntegerValue(21, 42));
    $this->assertSame(
      $innerFilter, $filter->getInnerFilter()
    );
  }

  /**
   * @covers \Papaya\Filter\Optional::getFilter
   */
  public function testGetFilterCachesCreatedFilter() {
    $filter = new Optional($innerFilter = new IntegerValue(21, 42));
    $actualFilter = $filter->getFilter();
    $this->assertSame($actualFilter, $filter->getFilter());
  }

  /**
   * @covers \Papaya\Filter\Optional::getFilter
   * @covers \Papaya\Filter\Optional::validate
   */
  public function testValidateWithEmptyValue() {
    $filter = new Optional($innerFilter = new IntegerValue(21, 42));
    $this->assertTrue(
      $filter->validate('')
    );
  }

  /**
   * @covers \Papaya\Filter\Optional::getFilter
   * @covers \Papaya\Filter\Optional::validate
   */
  public function testValidateWithValidValue() {
    $filter = new Optional($innerFilter = new IntegerValue(21, 42));
    $this->assertTrue(
      $filter->validate('42')
    );
  }

  /**
   * @covers \Papaya\Filter\Optional::getFilter
   * @covers \Papaya\Filter\Optional::validate
   */
  public function testValidateWithInvalidValueExpectingException() {
    $filter = new Optional($innerFilter = new IntegerValue(21, 42));
    $this->expectException(Exception\OutOfRange\ToLarge::class);
    $this->assertTrue(
      $filter->validate('84')
    );
  }

  /**
   * @covers \Papaya\Filter\Optional::getFilter
   * @covers \Papaya\Filter\Optional::filter
   */
  public function testFilterWithEmptyValue() {
    $filter = new Optional($innerFilter = new IntegerValue(21, 42));
    $this->assertNull(
      $filter->filter('')
    );
  }

  /**
   * @covers \Papaya\Filter\Optional::getFilter
   * @covers \Papaya\Filter\Optional::validate
   */
  public function testFilterWithValidValue() {
    $filter = new Optional($innerFilter = new IntegerValue(21, 42));
    $this->assertSame(
      42, $filter->filter('42')
    );
  }

  /**
   * @covers \Papaya\Filter\Optional::getFilter
   * @covers \Papaya\Filter\Optional::filter
   */
  public function testFilterWithInvalidValueExpectingNull() {
    $filter = new Optional($innerFilter = new IntegerValue(21, 42));
    $this->assertNull(
      $filter->filter('84')
    );
  }
}
