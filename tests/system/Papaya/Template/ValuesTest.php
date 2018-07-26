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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaTemplateValuesTest extends PapayaTestCase {

  /**
  * @covers \PapayaTemplateValues::__construct
  */
  public function testConstructor() {
    $values = new \PapayaTemplateValues();
    $this->assertAttributeInstanceOf(
      PapayaXmlDocument::class, '_document', $values
    );
  }

  /**
  * @covers \PapayaTemplateValues::__construct
  */
  public function testConstructorWithDocument() {
    $document = new \PapayaXmlDocument();
    $values = new \PapayaTemplateValues($document);
    $this->assertAttributeSame(
      $document, '_document', $values
    );
  }

  /**
  * @covers \PapayaTemplateValues::document
  */
  public function testDocument() {
    $document = new \PapayaXmlDocument();
    $values = new \PapayaTemplateValues($document);
    $this->assertSame(
      $document, $values->document()
    );
  }

  /**
  * @covers \PapayaTemplateValues::document
  */
  public function testDocumentWithArgument() {
    $document = new \PapayaXmlDocument();
    $values = new \PapayaTemplateValues();
    $this->assertSame(
      $document, $values->document($document)
    );
    $this->assertAttributeSame(
      $document, '_document', $values
    );
  }

  /**
  * @covers \PapayaTemplateValues::getXpath
  */
  public function testGetXpath() {
    $values = new \PapayaTemplateValues();
    $this->assertInstanceOf(
      'DOMXPath', $values->getXpath()
    );
  }

  /**
  * @covers \PapayaTemplateValues::getValueByPath
  */
  public function testGetValueByPathImplizitCreate() {
    $values = new \PapayaTemplateValues();
    $value = $values->getValueByPath('sample/child');
    $this->assertEquals(
      'child',
      $value->node()->tagName
    );
    $this->assertEquals(
      /** @lang XML */'<sample><child/></sample>',
      $values->document()->saveXML($values->document()->documentElement)
    );
  }

  /**
  * @covers \PapayaTemplateValues::getValueByPath
  */
  public function testGetValueByPathUsingContext() {
    $values = new \PapayaTemplateValues();
    $value = $values->getValueByPath('child', $values->getValueByPath('sample')->node());
    $this->assertEquals(
      'child',
      $value->node()->tagName
    );
    $this->assertEquals(
      /** @lang XML */'<sample><child/></sample>',
      $values->document()->saveXML($values->document()->documentElement)
    );
  }

  /**
  * @covers \PapayaTemplateValues::getValueByPath
  */
  public function testGetValueByPathIgnoringContext() {
    $values = new \PapayaTemplateValues();
    $value = $values->getValueByPath('/sample/child', $values->getValueByPath('sample')->node());
    $this->assertEquals(
      'child',
      $value->node()->tagName
    );
    $this->assertEquals(
      /** @lang XML */'<sample><child/></sample>',
      $values->document()->saveXML($values->document()->documentElement)
    );
  }

  /**
  * @covers \PapayaTemplateValues::getValueByPath
  */
  public function testGetValueByPathWithInvalidPathExpectingException() {
    $values = new \PapayaTemplateValues();
    $this->expectException(InvalidArgumentException::class);
    $values->getValueByPath('');
  }

  /**
  * @covers \PapayaTemplateValues::getValueByPath
  */
  public function testGetValueByPathWithoutImplicitCreateExpectingFalse() {
    $values = new \PapayaTemplateValues();
    $this->assertFalse(
      $values->getValueByPath('sample', NULL, FALSE)
    );
  }

  /**
  * @covers \PapayaTemplateValues::getValue
  */
  public function testGetValueWithPath() {
    $values = new \PapayaTemplateValues();
    $value = $values->getValue('/sample/child');
    $this->assertEquals(
      'child',
      $value->node()->tagName
    );
  }

  /**
  * @covers \PapayaTemplateValues::getValue
  */
  public function testGetValueWithNull() {
    $values = new \PapayaTemplateValues();
    $this->assertInstanceOf(
      PapayaXmlDocument::class, $values->getValue()->node()
    );
  }

  /**
  * @covers \PapayaTemplateValues::getValue
  */
  public function testGetValueWithDomelement() {
    $document = new \PapayaXmlDocument();
    $document->appendChild($node = $document->createElement('sample'));
    $values = new \PapayaTemplateValues($document);
    $this->assertSame(
      'sample', $values->getValue($node)->node()->tagName
    );
  }

  /**
  * @covers \PapayaTemplateValues::getValue
  */
  public function testGetValueWithInvalidElementExpectingException() {
    $values = new \PapayaTemplateValues();
    $this->expectException(InvalidArgumentException::class);
    $values->getValue(23);
  }

  /**
  * @covers \PapayaTemplateValues::append
  */
  public function testAppend() {
    $values = new \PapayaTemplateValues();
    $value = $values->append('sample', 'child', array('added' => 'yes'), 'content');
    $this->assertEquals(
      'child',
      $value->node()->tagName
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */'<sample><child added="yes">content</child></sample>',
      $values->document()->saveXML($values->document()->documentElement)
    );
  }

  /**
  * @covers \PapayaTemplateValues::appendXml
  */
  public function testAppendXml() {
    $values = new \PapayaTemplateValues();
    $value = $values->appendXml('sample', /** @lang XML */'<child added="yes">content</child>');
    $this->assertEquals(
      'sample',
      $value->node()->tagName
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */'<sample><child added="yes">content</child></sample>',
      $values->document()->saveXML($values->document()->documentElement)
    );
  }
}
