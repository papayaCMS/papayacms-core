<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaTemplateValueTest extends PapayaTestCase {

  /**
  * @covers PapayaTemplateValue::__construct
  */
  public function testConstructorWithDocument() {
    $dom = new PapayaXmlDocument();
    $value = new PapayaTemplateValue($dom);
    $this->assertAttributeSame(
      $dom, '_node', $value
    );
  }

  /**
  * @covers PapayaTemplateValue::__construct
  */
  public function testConstructorWithDomnode() {
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('node');
    $value = new PapayaTemplateValue($node);
    $this->assertAttributeSame(
      $node, '_node', $value
    );
  }

  /**
  * @covers PapayaTemplateValue::node
  */
  public function testNode() {
    $dom = new PapayaXmlDocument();
    $value = new PapayaTemplateValue($dom);
    $this->assertSame(
      $dom, $value->node()
    );
  }

  /**
  * @covers PapayaTemplateValue::node
  */
  public function testNodeWithArgument() {
    $dom = new PapayaXmlDocument();
    $value = new PapayaTemplateValue($dom->appendElement('dom'));
    $this->assertSame(
      $dom,
      $value->node($dom)
    );
    $this->assertAttributeSame(
      $dom, '_node', $value
    );
  }

  /**
  * @covers PapayaTemplateValue::node
  */
  public function testNodeWithInvalidArgumentExpectingException() {
    $dom = new PapayaXmlDocument();
    $value = new PapayaTemplateValue($dom->appendElement('dom'));
    $this->setExpectedException('InvalidArgumentException');
    $value->node(new stdClass());
  }

  /**
  * @covers PapayaTemplateValue::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendChild($dom->createElement('test'));
    $node = $dom->createElement('node');
    $value = new PapayaTemplateValue($node);
    $this->assertSame(
      $value,
      $value->appendTo($dom->documentElement)
    );
    $this->assertAttributeSame(
      $dom->documentElement->firstChild,
      '_node',
      $value
    );
  }

  /**
  * @covers PapayaTemplateValue::append
  * @covers PapayaTemplateValue::_getDocument
  */
  public function testAppendWithString() {
    $dom = new PapayaXmlDocument();
    $value = new PapayaTemplateValue($dom);
    $newValue = $value->append('node', array('sample' => 'yes'), 'content');
    $this->assertEquals(
      '<node sample="yes">content</node>',
      $dom->saveXml($this->readAttribute($newValue, '_node'))
    );
  }

  /**
  * @covers PapayaTemplateValue::append
  * @covers PapayaTemplateValue::_getDocument
  */
  public function testAppendWithDomElement() {
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('node');
    $value = new PapayaTemplateValue($dom);
    $newValue = $value->append($node, array('sample' => 'yes'), 'content');
    $this->assertEquals(
      '<node sample="yes">content</node>',
      $dom->saveXml($this->readAttribute($newValue, '_node'))
    );
  }

  /**
  * @covers PapayaTemplateValue::append
  * @covers PapayaTemplateValue::_getDocument
  */
  public function testAppendWithDomDocument() {
    $dom = new PapayaXmlDocument();
    $dom->appendChild($node = $dom->createElement('node'));
    $value = new PapayaTemplateValue($dom);
    $newValue = $value->append($dom, array('sample' => 'yes'), 'content');
    $this->assertEquals(
      '<node sample="yes">content</node>',
      $dom->saveXml($this->readAttribute($newValue, '_node'))
    );
  }

  /**
  * @covers PapayaTemplateValue::append
  * @covers PapayaTemplateValue::_getDocument
  */
  public function testAppendOnDomelement() {
    $dom = new PapayaXmlDocument();
    $dom->appendChild($node = $dom->createElement('node'));
    $value = new PapayaTemplateValue($node);
    $newValue = $value->append('child');
    $this->assertEquals(
      '<node><child/></node>',
      $dom->saveXml($this->readAttribute($value, '_node'))
    );
    $this->assertEquals(
      '<child/>',
      $dom->saveXml($this->readAttribute($newValue, '_node'))
    );
  }

  /**
  * @covers PapayaTemplateValue::append
  */
  public function testAppendWithPapayaXmlAppendable() {
    $appendable = $this->createMock(PapayaXmlAppendable::class);
    $appendable
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));

    $dom = new PapayaXmlDocument();
    $dom->appendChild($node = $dom->createElement('node'));
    $value = new PapayaTemplateValue($node);
    $value->append($appendable);
  }

  /**
  * @covers PapayaTemplateValue::append
  * @covers PapayaTemplateValue::_getDocument
  */
  public function testAppendWithInvalidElement() {
    $dom = new PapayaXmlDocument();
    $value = new PapayaTemplateValue($dom);
    $this->setExpectedException('InvalidArgumentException');
    $value->append(5);
  }

  /**
  * @covers PapayaTemplateValue::append
  * @covers PapayaTemplateValue::_getDocument
  */
  public function testAppendWithEmptyDocument() {
    $dom = new PapayaXmlDocument();
    $value = new PapayaTemplateValue($dom);
    $this->setExpectedException('InvalidArgumentException');
    $value->append($dom);
  }

  /**
  * @covers PapayaTemplateValue::appendXml
  */
  public function testAppendXml() {
    $dom = new PapayaXmlDocument();
    $value = new PapayaTemplateValue($dom);
    $newValue = $value->appendXml('<child/>');
    $this->assertEquals(
      '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<child/>'."\n",
      $dom->saveXml($this->readAttribute($newValue, '_node'))
    );
  }

  /**
  * @covers PapayaTemplateValue::xml
  */
  public function testXmlWithoutArgument() {
    $dom = new PapayaXmlDocument();
    $dom->loadXml('<sample><child/>test</sample>');
    $value = new PapayaTemplateValue($dom->documentElement);
    $this->assertEquals(
      '<child/>test',
      $value->xml()
    );
  }

  /**
  * @covers PapayaTemplateValue::xml
  */
  public function testXmlWithEmptyArgumentRemovingElements() {
    $dom = new PapayaXmlDocument();
    $dom->loadXml('<sample><child/>test</sample>');
    $value = new PapayaTemplateValue($dom->documentElement);
    $this->assertEquals(
      '',
      $value->xml('')
    );
  }

  /**
  * @covers PapayaTemplateValue::xml
  */
  public function testXmlWithXmlFragment() {
    $dom = new PapayaXmlDocument();
    $dom->loadXml('<sample/>');
    $value = new PapayaTemplateValue($dom->documentElement);
    $this->assertEquals(
      '<child/>test',
      $value->xml('<child/>test')
    );
  }

  /**
  * @covers PapayaTemplateValue::xml
  */
  public function testXmlWithDomnode() {
    $dom = new PapayaXmlDocument();
    $dom->loadXml('<sample/>');
    $value = new PapayaTemplateValue($dom->documentElement);
    $this->assertEquals(
      '<child/>',
      $value->xml($dom->createElement('child'))
    );
  }

  /**
  * @covers PapayaTemplateValue::xml
  */
  public function testXmlWithArrayOfDomnodes() {
    $dom = new PapayaXmlDocument();
    $dom->loadXml('<sample/>');
    $value = new PapayaTemplateValue($dom->documentElement);
    $this->assertEquals(
      '<child/>text',
      $value->xml(
        array(
          $dom->createElement('child'),
          $dom->createTextNode('text')
        )
      )
    );
  }

  /**
  * @covers PapayaTemplateValue::xml
  */
  public function testXmlWithInvalidArgumentExpectingException() {
    $dom = new PapayaXmlDocument();
    $dom->loadXml('<sample/>');
    $value = new PapayaTemplateValue($dom->documentElement);
    $this->setExpectedException('InvalidArgumentException');
    $value->xml(1);
  }

  /**
  * @covers PapayaTemplateValue::xml
  */
  public function testXmlWithInvalidArrayExpectingException() {
    $dom = new PapayaXmlDocument();
    $dom->loadXml('<sample/>');
    $value = new PapayaTemplateValue($dom->documentElement);
    $this->setExpectedException('InvalidArgumentException');
    $value->xml(array('child'));
  }

}
