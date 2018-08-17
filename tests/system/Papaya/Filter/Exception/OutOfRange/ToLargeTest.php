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

namespace Papaya\Filter\Exception\OutOfRange;

require_once __DIR__.'/../../../../../bootstrap.php';

class ToLargeTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Filter\Exception\OutOfRange\ToLarge::__construct
   */
  public function testConstructor() {
    $e = new ToLarge(21, 42);
    $this->assertEquals(
      'Value is to large. Expecting a maximum of "21", got "42".',
      $e->getMessage()
    );
  }
}
