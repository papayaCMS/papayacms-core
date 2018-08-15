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

class PapayaTemplateValuesTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Template\Values::__construct
  */
  public function testConstructor() {
    $values = new \Papaya\Template\Values();
    $this->assertAttributeInstanceOf(
      \Papaya\XML\Document::class, '_document', $values
    );
  }

  /**
  * @covers \Papaya\Template\Values::__construct
  */
  public function testConstructorWithDocument() {
    $document = new \Papaya\XML\Document();
    $values = new \Papaya\Template\Values($document);
    $this->assertAttributeSame(
      $document, '_document', $values
    );
  }

  /**
  * @covers \Papaya\Template\Values::document
  */
  public function testDocument() {
    $document = new \Papaya\XML\Document();
    $values = new \Papaya\Template\Values($document);
    $this->assertSame(
      $document, $values->document()
    );
  }

  /**
  * @covers \Papaya\Template\Values::document
  */
  public function testDocumentWithArgument() {
    $document = new \Papaya\XML\Document();
    $values = new \Papaya\Template\Values();
    $this->assertSame(
      $document, $values->document($document)
    );
    $this->assertAttributeSame(
      $document, '_document', $values
    );
  }

  /**
  * @covers \Papaya\Template\Values::getXpath
  */
  public function testGetXpath() {
    $values = new \Papaya\Template\Values();
    $this->assertInstanceOf(
      'DOMXPath', $values->getXpath()
    );
  }

  /**
  * @covers \Papaya\Template\Values::getValueByPath
  */
  public function testGetValueByPathImplizitCreate() {
    $values = new \Papaya\Template\Values();
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
  * @covers \Papaya\Template\Values::getValueByPath
  */
  public function testGetValueByPathUsingContext() {
    $values = new \Papaya\Template\Values();
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
  * @covers \Papaya\Template\Values::getValueByPath
  */
  public function testGetValueByPathIgnoringContext() {
    $values = new \Papaya\Template\Values();
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
  * @covers \Papaya\Template\Values::getValueByPath
  */
  public function testGetValueByPathWithInvalidPathExpectingException() {
    $values = new \Papaya\Template\Values();
    $this->expectException(\InvalidArgumentException::class);
    $values->getValueByPath('');
  }

  /**
  * @covers \Papaya\Template\Values::getValueByPath
  */
  public function testGetValueByPathWithoutImplicitCreateExpectingFalse() {
    $values = new \Papaya\Template\Values();
    $this->assertFalse(
      $values->getValueByPath('sample', NULL, FALSE)
    );
  }

  /**
  * @covers \Papaya\Template\Values::getValue
  */
  public function testGetValueWithPath() {
    $values = new \Papaya\Template\Values();
    $value = $values->getValue('/sample/child');
    $this->assertEquals(
      'child',
      $value->node()->tagName
    );
  }

  /**
  * @covers \Papaya\Template\Values::getValue
  */
  public function testGetValueWithNull() {
    $values = new \Papaya\Template\Values();
    $this->assertInstanceOf(
      \Papaya\XML\Document::class, $values->getValue()->node()
    );
  }

  /**
  * @covers \Papaya\Template\Values::getValue
  */
  public function testGetValueWithDomelement() {
    $document = new \Papaya\XML\Document();
    $document->appendChild($node = $document->createElement('sample'));
    $values = new \Papaya\Template\Values($document);
    $this->assertSame(
      'sample', $values->getValue($node)->node()->tagName
    );
  }

  /**
  * @covers \Papaya\Template\Values::getValue
  */
  public function testGetValueWithInvalidElementExpectingException() {
    $values = new \Papaya\Template\Values();
    $this->expectException(\InvalidArgumentException::class);
    $values->getValue(23);
  }

  /**
  * @covers \Papaya\Template\Values::append
  */
  public function testAppend() {
    $values = new \Papaya\Template\Values();
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
  * @covers \Papaya\Template\Values::appendXML
  */
  public function testAppendXml() {
    $values = new \Papaya\Template\Values();
    $value = $values->appendXML('sample', /** @lang XML */'<child added="yes">content</child>');
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
