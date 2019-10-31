<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
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

  use Papaya\Filter;
  use Papaya\Test\TestCase;

  /**
   * @covers \Papaya\Filter\Lines
   */
  class LinesTest extends TestCase {

    public function testValidateExpectingTrue() {
      $lineFilter = $this->createMock(Filter::class);
      $lineFilter
        ->expects($this->exactly(2))
        ->method('validate')
        ->with('42')
        ->willReturn(TRUE);

      $filter = new Lines($lineFilter);
      $this->assertTrue($filter->validate("42\n42"));
    }

    public function testFilter() {
      $lineFilter = $this->createMock(Filter::class);
      $lineFilter
        ->expects($this->exactly(3))
        ->method('filter')
        ->with($this->logicalOr('42', '21'))
        ->willReturnMap(
          [
            ['42', '42'],
            ['21', NULL]
          ]
        );

      $filter = new Lines($lineFilter);
      $this->assertSame("42\n42", $filter->filter("42\n21\n42"));
    }

    public function testFilterWithEmptyString() {
      $lineFilter = $this->createMock(Filter::class);
      $lineFilter
        ->expects($this->never())
        ->method('filter');

      $filter = new Lines($lineFilter);
      $this->assertSame('', $filter->filter(''));
    }

  }

}
