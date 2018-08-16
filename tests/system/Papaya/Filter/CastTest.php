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

class CastTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Filter\Cast::__construct
   */
  public function testConstructor() {
    $filter = new Cast('int');
    $this->assertAttributeEquals(
      'integer', '_type', $filter
    );
  }

  /**
   * @covers \Papaya\Filter\Cast::__construct
   */
  public function testConstructorExpectingException() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('"invalid_type_string" is not a valid type.');
    new Cast('invalid_type_string');
  }

  /**
   * @covers \Papaya\Filter\Cast::validate
   */
  public function testCheck() {
    $filter = new Cast('int');
    $this->assertTrue($filter->validate(NULL));
  }

  /**
   * @covers \Papaya\Filter\Cast::filter
   */
  public function testFilter() {
    $filter = new Cast('int');
    $this->assertSame(42, $filter->filter('42'));
  }
}
