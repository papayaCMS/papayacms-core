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

namespace Papaya\Message\Context {
  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\Message\Context\Group
   */
  class GroupTest extends \Papaya\TestFramework\TestCase {

    public function testAppend() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Data $element */
      $element = $this->createMock(Data::class);
      $group = new Group();
      $this->assertSame(
        $group,
        $group->append($element)
      );
      $this->assertSame(
        [$element],
        iterator_to_array($group)
      );
    }

    public function testCount() {
      $group = $this->getContextGroupFixture();
      $this->assertEquals(
        3,
        $group->count()
      );
    }

    public function testAsString() {
      $group = $this->getContextGroupFixture();
      $this->assertEquals(
      /** @lang Text */
        "Universe\n\nHello <World>\n\nHello World",
        $group->asString()
      );
    }

    public function testAsXhtml() {
      $group = $this->getContextGroupFixture();
      $this->assertXmlFragmentEqualsXmlFragment(
      // language=XML prefix=<fragment> suffix=</fragment>
        '<div class="group"><h3>Universe</h3></div>
      <div class="group">Hello &lt;World&gt;</div>
      <div class="group">Hello <b>World</b></div>',
        $group->asXhtml()
      );
    }

    public function getContextGroupFixture() {
      $group = new Group();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Data $elementLabeled */
      $elementLabeled = $this->createMock(Interfaces\Labeled::class);
      $elementLabeled
        ->method('getLabel')
        ->willReturn('Universe');
      /** @var \PHPUnit_Framework_MockObject_MockObject|Data $elementString */
      $elementString = $this->createMock(Interfaces\Text::class);
      $elementString
        ->method('asString')
        ->willReturn(
        /** @lang Text */
          'Hello <World>'
        );
      /** @var \PHPUnit_Framework_MockObject_MockObject|Data $elementXhtml */
      $elementXhtml = $this->createMock(Interfaces\XHTML::class);
      $elementXhtml
        ->method('asXhtml')
        ->willReturn(
        // language=XML prefix=<fragment> suffix=</fragment>
          'Hello <b>World</b>'
        );
      $group
        ->append($elementLabeled)
        ->append($elementString)
        ->append($elementXhtml);
      return $group;
    }
  }
}
