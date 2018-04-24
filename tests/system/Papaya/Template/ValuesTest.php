<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaTemplateValuesTest extends PapayaTestCase {

  /**
  * @covers PapayaTemplateValues::__construct
  */
  public function testConstructor() {
    $values = new PapayaTemplateValues();
    $this->assertAttributeInstanceOf(
      PapayaXmlDocument::class, '_document', $values
    );
  }

  /**
  * @covers PapayaTemplateValues::__construct
  */
  public function testConstructorWithDocument() {
    $dom = new PapayaXmlDocument();
    $values = new PapayaTemplateValues($dom);
    $this->assertAttributeSame(
      $dom, '_document', $values
    );
  }

  /**
  * @covers PapayaTemplateValues::document
  */
  public function testDocument() {
    $dom = new PapayaXmlDocument();
    $values = new PapayaTemplateValues($dom);
    $this->assertSame(
      $dom, $values->document()
    );
  }

  /**
  * @covers PapayaTemplateValues::document
  */
  public function testDocumentWithArgument() {
    $dom = new PapayaXmlDocument();
    $values = new PapayaTemplateValues();
    $this->assertSame(
      $dom, $values->document($dom)
    );
    $this->assertAttributeSame(
      $dom, '_document', $values
    );
  }

  /**
  * @covers PapayaTemplateValues::getXpath
  */
  public function testGetXpath() {
    $values = new PapayaTemplateValues();
    $this->assertInstanceOf(
      'DOMXPath', $values->getXpath()
    );
  }

  /**
  * @covers PapayaTemplateValues::getValueByPath
  */
  public function testGetValueByPathImplizitCreate() {
    $values = new PapayaTemplateValues();
    $value = $values->getValueByPath('sample/child');
    $this->assertEquals(
      'child',
      $value->node()->tagName
    );
    $this->assertEquals(
      '<sample><child/></sample>',
      $values->document()->saveXml($values->document()->documentElement)
    );
  }

  /**
  * @covers PapayaTemplateValues::getValueByPath
  */
  public function testGetValueByPathUsingContext() {
    $values = new PapayaTemplateValues();
    $value = $values->getValueByPath('child', $values->getValueByPath('sample')->node());
    $this->assertEquals(
      'child',
      $value->node()->tagName
    );
    $this->assertEquals(
      '<sample><child/></sample>',
      $values->document()->saveXml($values->document()->documentElement)
    );
  }

  /**
  * @covers PapayaTemplateValues::getValueByPath
  */
  public function testGetValueByPathIgnoringContext() {
    $values = new PapayaTemplateValues();
    $value = $values->getValueByPath('/sample/child', $values->getValueByPath('sample')->node());
    $this->assertEquals(
      'child',
      $value->node()->tagName
    );
    $this->assertEquals(
      '<sample><child/></sample>',
      $values->document()->saveXml($values->document()->documentElement)
    );
  }

  /**
  * @covers PapayaTemplateValues::getValueByPath
  */
  public function testGetValueByPathWithInvalidPathExpectingException() {
    $values = new PapayaTemplateValues();
    $this->setExpectedException(InvalidArgumentException::class);
    $value = $values->getValueByPath('');
  }

  /**
  * @covers PapayaTemplateValues::getValueByPath
  */
  public function testGetValueByPathWihtoutImplizitCreateExpectingFalse() {
    $values = new PapayaTemplateValues();
    $this->assertFalse(
      $values->getValueByPath('sample', NULL, FALSE)
    );
  }

  /**
  * @covers PapayaTemplateValues::getValue
  */
  public function testGetValueWithPath() {
    $values = new PapayaTemplateValues();
    $value = $values->getValue('/sample/child');
    $this->assertEquals(
      'child',
      $value->node()->tagName
    );
  }

  /**
  * @covers PapayaTemplateValues::getValue
  */
  public function testGetValueWithNull() {
    $values = new PapayaTemplateValues();
    $this->assertInstanceOf(
      PapayaXmlDocument::class, $values->getValue()->node()
    );
  }

  /**
  * @covers PapayaTemplateValues::getValue
  */
  public function testGetValueWithDomelement() {
    $dom = new PapayaXmlDocument();
    $dom->appendChild($node = $dom->createElement('sample'));
    $values = new PapayaTemplateValues($dom);
    $this->assertSame(
      'sample', $values->getValue($node)->node()->tagName
    );
  }

  /**
  * @covers PapayaTemplateValues::getValue
  */
  public function testGetValueWithInvalidElementExpectingException() {
    $values = new PapayaTemplateValues();
    $this->setExpectedException(InvalidArgumentException::class);
    $value = $values->getValue(23);
  }

  /**
  * @covers PapayaTemplateValues::append
  */
  public function testAppend() {
    $values = new PapayaTemplateValues();
    $value = $values->append('sample', 'child', array('added' => 'yes'), 'content');
    $this->assertEquals(
      'child',
      $value->node()->tagName
    );
    $this->assertEquals(
      '<sample><child added="yes">content</child></sample>',
      $values->document()->saveXml($values->document()->documentElement)
    );
  }

  /**
  * @covers PapayaTemplateValues::appendXml
  */
  public function testAppendXml() {
    $values = new PapayaTemplateValues();
    $value = $values->appendXml('sample', '<child added="yes">content</child>');
    $this->assertEquals(
      'sample',
      $value->node()->tagName
    );
    $this->assertEquals(
      '<sample><child added="yes">content</child></sample>',
      $values->document()->saveXml($values->document()->documentElement)
    );
  }
}
