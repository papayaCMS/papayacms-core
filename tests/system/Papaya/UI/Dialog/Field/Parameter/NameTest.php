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

namespace Papaya\UI\Dialog\Field\Parameter {

  use Papaya\Request\Parameters\Name as RequestParameterName;
  use Papaya\TestFramework\TestCase;
  use Papaya\UI\Dialog;

  /**
   * @covers \Papaya\UI\Dialog\Field\Parameter\Name
   */
  class NameTest extends TestCase {

    /**
     * @param string $expected
     * @param string $input
     * @testWith
     *   ["foo", "foo"]
     *   ["foo[bar]", "foo/bar"]
     */
    public function testGetNameAsString($expected, $input) {
      $name = new Name($input);
      $this->assertSame($expected, (string)$name);
    }

    public function testGetNameWithDialog() {
      $dialog = $this->createMock(Dialog::class);
      $dialog
        ->expects($this->once())
        ->method('getParameterName')
        ->with('field')
        ->willReturn(new RequestParameterName('field'));
      $dialog
        ->method('parameterGroup')
        ->willReturn('group');

      $name = new Name('field', $dialog);
      $this->assertSame('group[field]', (string)$name);
    }

  }

}
