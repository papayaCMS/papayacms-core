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

  use Papaya\Test\TestCase;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\Database\Syntax\Placeholder
   */
  class PlaceholderTest extends TestCase {
    /**
     * @param string $expected
     * @param string $name
     * @testWith
     *   ["?", ""]
     *   [":foo", "foo"]
     *   [":bar", "bar"]
     *   [":foo123", "foo123"]
     */
    public function testConstructor($expected, $name) {
      $identifier = new Placeholder($name);
      $this->assertEquals($expected, (string)$identifier);
    }

    /**
     * @param string $name
     * @testWith
     *   ["12"]
     *   ["foo.12"]
     *   ["foo(bar)"]
     *   [" foo"]
     *   ["foo foo"]
     */
    public function testConstructorWithInvalidValuesExpectingException($name) {
      $this->expectException(\InvalidArgumentException::class);
      new Placeholder($name);
    }
  }

}
