<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaMessageContextGroupTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageContextGroup::append
  */
  public function testAppend() {
    $element = $this->getMock('PapayaMessageContextInterface');
    $group = new PapayaMessageContextGroup();
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
  * @covers PapayaMessageContextGroup::current
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
  * @covers PapayaMessageContextGroup::next
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
  * @covers PapayaMessageContextGroup::key
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
  * @covers PapayaMessageContextGroup::rewind
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
  * @covers PapayaMessageContextGroup::rewind
  */
  public function testValidExpectingTrue() {
    $group = $this->getContextGroupFixture();
    $this->assertTrue(
      $group->valid()
    );
  }

  /**
  * @covers PapayaMessageContextGroup::valid
  */
  public function testValidExpectingFalse() {
    $group = new PapayaMessageContextGroup();
    $this->assertFalse(
      $group->valid()
    );
  }

  /**
  * @covers PapayaMessageContextGroup::count
  */
  public function testCount() {
    $group = $this->getContextGroupFixture();
    $this->assertEquals(
      3,
      $group->count()
    );
  }

  /**
  * @covers PapayaMessageContextGroup::asString
  */
  public function testAsString() {
    $group = $this->getContextGroupFixture();
    $this->assertEquals(
      'Universe'."\n\n".'Hello <World>'."\n\n".'Hello World',
      $group->asString()
    );
  }

  /**
  * @covers PapayaMessageContextGroup::asXhtml
  */
  public function testAsXhtml() {
    $group = $this->getContextGroupFixture();
    $this->assertEquals(
      '<div class="group"><h3>Universe</h3></div>'.
      '<div class="group">Hello &lt;World&gt;</div>'.
      '<div class="group">Hello <b>World</b></div>',
      $group->asXhtml()
    );
  }

  public function getContextGroupFixture() {
    $group = new PapayaMessageContextGroup();
    $elementLabeled = $this->getMock('PapayaMessageContextInterfaceLabeled');
    $elementLabeled
      ->expects($this->any())
      ->method('getLabel')
      ->will($this->returnValue('Universe'));
    $elementString = $this->getMock('PapayaMessageContextInterfaceString');
    $elementString
      ->expects($this->any())
      ->method('asString')
      ->will($this->returnValue('Hello <World>'));
    $elementXhtml = $this->getMock('PapayaMessageContextInterfaceXhtml');
    $elementXhtml
      ->expects($this->any())
      ->method('asXhtml')
      ->will($this->returnValue('Hello <b>World</b>'));
    $group
      ->append($elementLabeled)
      ->append($elementString)
      ->append($elementXhtml);
    return $group;
  }
}