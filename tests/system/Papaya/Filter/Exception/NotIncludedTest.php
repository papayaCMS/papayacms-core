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

namespace Papaya\Filter\Exception;

require_once __DIR__.'/../../../../bootstrap.php';

class NotIncludedTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Filter\Exception\NotIncluded::__construct
   */
  public function testConstructor() {
    $e = new NotIncluded(42);
    $this->assertEquals(
      'Value is to not enclosed in list of valid elements. Got "42".',
      $e->getMessage()
    );
  }
}
