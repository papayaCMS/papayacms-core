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

class BeforeTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Filter\Before
   */
  public function testValidate() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Filter $before */
    $before = $this->createMock(\Papaya\Filter::class);
    $before
      ->expects($this->once())
      ->method('filter')
      ->with('foo')
      ->willReturn('success');

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Filter $after */
    $after = $this->createMock(\Papaya\Filter::class);
    $after
      ->expects($this->once())
      ->method('validate')
      ->with('success')
      ->willReturn(TRUE);

    $filter = new Before($before, $after);
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
   * @covers \Papaya\Filter\Before
   */
  public function testFilter() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Filter $before */
    $before = $this->createMock(\Papaya\Filter::class);
    $before
      ->expects($this->once())
      ->method('filter')
      ->with('foo')
      ->willReturn('success');

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Filter $after */
    $after = $this->createMock(\Papaya\Filter::class);
    $after
      ->expects($this->once())
      ->method('filter')
      ->with('success')
      ->willReturn(42);

    $filter = new Before($before, $after);
    $this->assertSame(
      42,
      $filter->filter('foo')
    );
  }
}
