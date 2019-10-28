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

namespace Papaya\UI\Dialog\Element {

  use Papaya\TestCase;

  /**
   * @covers \Papaya\UI\Dialog\Element\Description
   */
  class DescriptionTest extends TestCase {

    public function testDescriptionWithLink() {
      $description = new Description();
      $description->addLink($this->mockPapaya()->reference());

      $this->assertXmlStringEqualsXmlString(
        '<description>
          <link href="http://www.example.html"/>
        </description>',
        $description->getXML()
      );
    }

    public function testDescriptionWithProperty() {
      $description = new Description();
      $description->addProperty('foo', 'bar');

      $this->assertXmlStringEqualsXmlString(
        '<description>
          <property name="foo" value="bar"/>
        </description>',
        $description->getXML()
      );
    }

  }
}
