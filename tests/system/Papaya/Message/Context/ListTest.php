<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaMessageContextListTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageContextList::__construct
  */
  public function testConstructor() {
    $context = new PapayaMessageContextList('List Sample', array('Hello', 'World'));
    $this->assertAttributeEquals(
      'List Sample', '_label', $context
    );
    $this->assertAttributeEquals(
      array('Hello', 'World'), '_items', $context
    );
  }

  /**
  * @covers PapayaMessageContextList::getLabel
  */
  public function testGetLabel() {
    $context = new PapayaMessageContextList('List Sample', array('Hello', 'World'));
    $this->assertEquals(
      'List Sample', $context->getLabel()
    );
  }

  /**
  * @covers PapayaMessageContextList::asArray
  */
  public function testAsArray() {
    $context = new PapayaMessageContextList('', array('Hello', 'World'));
    $this->assertEquals(
      array('Hello', 'World'),
      $context->asArray()
    );
  }

  /**
  * @covers PapayaMessageContextList::asXhtml
  */
  public function testAsXhtml() {
    $context = new PapayaMessageContextList('', array('Hello', 'World'));
    $this->assertEquals(
      '<ol><li>Hello</li><li>World</li></ol>',
      $context->asXhtml()
    );
  }

  /**
  * @covers PapayaMessageContextList::asXhtml
  */
  public function testAsXhtmlWithEmptyList() {
    $context = new PapayaMessageContextList('', array());
    $this->assertEquals(
      '',
      $context->asXhtml()
    );
  }

  /**
  * @covers PapayaMessageContextList::asString
  */
  public function testAsString() {
    $context = new PapayaMessageContextList('', array('Hello', 'World'));
    $this->assertEquals(
      'Hello'."\n".'World',
      $context->asString()
    );
  }
}