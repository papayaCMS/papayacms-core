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

namespace Papaya\UI\Dialog\Button {

  use Papaya\Test\TestCase;
  use Papaya\UI\Reference;

  /**
   * @covers \Papaya\UI\Dialog\Button\Link
   */
  class LinkTest extends TestCase {

    public function testGetReferenceAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Reference $reference */
      $reference = $this->createMock(Reference::class);
      $button = new Link('caption');
      $this->assertSame($reference, $button->reference($reference));
    }

    public function testGetReferenceImplicitCreate() {
      $button = new Link('caption');
      $button->papaya($papaya = $this->mockPapaya()->application());
      $this->assertSame($papaya, $button->reference()->papaya());
    }

    public function testAppendTo() {
      $button = new Link('caption');
      $button->reference($this->mockPapaya()->reference());
      $this->assertXmlStringEqualsXmlString(
        '<button align="right" href="http://www.example.html" type="link">caption</button>',
        $button->getXML()
      );
    }

  }

}
