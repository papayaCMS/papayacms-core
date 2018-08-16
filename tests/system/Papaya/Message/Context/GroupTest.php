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

namespace Papaya\Message\Context;
require_once __DIR__.'/../../../../bootstrap.php';

class GroupTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Message\Context\Group::append
   */
  public function testAppend() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Data $element */
    $element = $this->createMock(Data::class);
    $group = new Group();
    $this->assertSame(
      $group,
      $group->append($element)
    );
    $this->assertAttributeSame(
      array($element),
      '_elements',
      $group
    );
  }

  /**
   * @covers \Papaya\Message\Context\Group::current
   */
  public function testCurrent() {
    $group = $this->getContextGroupFixture();
    $elements = $this->readAttribute($group, '_elements');
    $this->assertSame(
      $elements[0],
      $group->current()
    );
  }

  /**
   * @covers \Papaya\Message\Context\Group::next
   */
  public function testNext() {
    $group = $this->getContextGroupFixture();
    $elements = $this->readAttribute($group, '_elements');
    $group->next();
    $this->assertSame(
      $elements[1],
      $group->current()
    );
  }

  /**
   * @covers \Papaya\Message\Context\Group::key
   */
  public function testKey() {
    $group = $this->getContextGroupFixture();
    $group->next();
    $this->assertSame(
      1,
      $group->key()
    );
  }

  /**
   * @covers \Papaya\Message\Context\Group::rewind
   */
  public function testRewind() {
    $group = $this->getContextGroupFixture();
    $elements = $this->readAttribute($group, '_elements');
    $group->next();
    $group->rewind();
    $this->assertSame(
      $elements[0],
      $group->current()
    );
  }

  /**
   * @covers \Papaya\Message\Context\Group::rewind
   */
  public function testValidExpectingTrue() {
    $group = $this->getContextGroupFixture();
    $this->assertTrue(
      $group->valid()
    );
  }

  /**
   * @covers \Papaya\Message\Context\Group::valid
   */
  public function testValidExpectingFalse() {
    $group = new Group();
    $this->assertFalse(
      $group->valid()
    );
  }

  /**
   * @covers \Papaya\Message\Context\Group::count
   */
  public function testCount() {
    $group = $this->getContextGroupFixture();
    $this->assertEquals(
      3,
      $group->count()
    );
  }

  /**
   * @covers \Papaya\Message\Context\Group::asString
   */
  public function testAsString() {
    $group = $this->getContextGroupFixture();
    $this->assertEquals(
    /** @lang Text */
      "Universe\n\nHello <World>\n\nHello World",
      $group->asString()
    );
  }

  /**
   * @covers \Papaya\Message\Context\Group::asXhtml
   */
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
      ->expects($this->any())
      ->method('getLabel')
      ->will($this->returnValue('Universe'));
    /** @var \PHPUnit_Framework_MockObject_MockObject|Data $elementString */
    $elementString = $this->createMock(Interfaces\Text::class);
    $elementString
      ->expects($this->any())
      ->method('asString')
      ->willReturn(/** @lang Text */
        'Hello <World>');
    /** @var \PHPUnit_Framework_MockObject_MockObject|Data $elementXhtml */
    $elementXhtml = $this->createMock(Interfaces\Xhtml::class);
    $elementXhtml
      ->expects($this->any())
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
