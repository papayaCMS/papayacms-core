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

namespace Papaya\Template\Engine\Values {

  use Papaya\Test\TestCase;

  /**
   * @covers \Papaya\Template\Engine\Values\ArrayLoader
   */
  class ArrayLoaderTest extends TestCase {

    public function testLoadArrayIntoDocument() {
      $loader = new ArrayLoader();
      $node = $loader->load(['foo' => ['bar' => 42]]);
      $this->assertXmlStringEqualsXmlString(
        '<_>
          <foo>
            <bar>42</bar>
          </foo>
        </_>',
        $node->saveXML()
      );
    }

    public function testLoadArrayInvalidValueKeyGetsIgnored() {
      $loader = new ArrayLoader();
      $node = $loader->load(['foo' => ['bar' => 42, '' => 'fail']]);
      $this->assertXmlStringEqualsXmlString(
        '<_>
          <foo>
            <bar>42</bar>
          </foo>
        </_>',
        $node->saveXML()
      );
    }
  }

}
