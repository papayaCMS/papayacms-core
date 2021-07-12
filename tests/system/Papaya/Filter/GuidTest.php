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

class GuidTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Filter\Guid
   */
  public function testValidateExpectingTrue() {
    $filter = new Guid();
    $this->assertTrue(
      $filter->validate('123456789012345678901234567890ab')
    );
  }

  /**
   * @covers \Papaya\Filter\Guid
   */
  public function testValidateExpectingException() {
    $filter = new Guid();
    $this->expectException(Exception::class);
    $filter->validate('foo');
  }
}
