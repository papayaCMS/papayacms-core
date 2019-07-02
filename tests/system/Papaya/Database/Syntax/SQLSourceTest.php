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

namespace Papaya\Database\Syntax {

  /**
   * @covers \Papaya\Database\Syntax\SQLSource
   */
  class SQLSourceTest extends \PHPUnit_Framework_TestCase {

    /**
     * @param string $expected
     * @param mixed $sqlString
     * @testWith
     *   ["foo", "foo"]
     *   ["42", 42]
     *   ["", null]
     *   ["LOWER(foo)", "LOWER(foo)"]
     */
    public function testCreateAndCastToString($expected, $sqlString) {
      $sqlSource = new SQLSource($sqlString);
      $this->assertSame($expected, (string)$sqlSource);
    }

  }

}
