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

class PapayaTemplateValueTest extends \PapayaTestCase {

  /**
  * @covers \PapayaTemplateValue::__construct
  */
  public function testConstructorWithDocument() {
    $document = new \PapayaXmlDocument();
    $value = new \PapayaTemplateValue($document);
    $this->assertAttributeSame(
      $document, '_node', $value
    );
  }

  /**
  * @covers \PapayaTemplateValue::__construct
  */
  public function testConstructorWithDomnode() {
    $document = new \PapayaXmlDocument();
    $node = $document->createElement('node');
    $value = new \PapayaTemplateValue($node);
    $this->assertAttributeSame(
      $node, '_node', $value
    );
  }

  /**
  * @covers \PapayaTemplateValue::node
  */
  public function testNode() {
    $document = new \PapayaXmlDocument();
    $value = new \PapayaTemplateValue($document);
    $this->assertSame(
      $document, $value->node()
    );
  }

  /**
  * @covers \PapayaTemplateValue::node
  */
  public function testNodeWithArgument() {
    $document = new \PapayaXmlDocument();
    $value = new \PapayaTemplateValue($document->appendElement('dom'));
    $this->assertSame(
      $document,
      $value->node($document)
    );
    $this->assertAttributeSame(
      $document, '_node', $value
    );
  }

  /**
  * @covers \PapayaTemplateValue::node
  */
  public function testNodeWithInvalidArgumentExpectingException() {
    $document = new \PapayaXmlDocument();
    $value = new \PapayaTemplateValue($document->appendElement('dom'));
    $this->expectException(InvalidArgumentException::class);
    $value->node(new stdClass());
  }

  /**
  * @covers \PapayaTemplateValue::appendTo
  */
  public function testAppendTo() {
    $document = new \PapayaXmlDocument();
    $document->appendChild($document->createElement('test'));
    $node = $document->createElement('node');
    $value = new \PapayaTemplateValue($node);
    $this->assertSame(
      $value,
      $value->appendTo($document->documentElement)
    );
    $this->assertAttributeSame(
      $document->documentElement->firstChild,
      '_node',
      $value
    );
  }

  /**
  * @covers \PapayaTemplateValue::append
  * @covers \PapayaTemplateValue::_getDocument
  */
  public function testAppendWithString() {
    $document = new \PapayaXmlDocument();
    $value = new \PapayaTemplateValue($document);
    $newValue = $value->append('node', array('sample' => 'yes'), 'content');
    $this->assertEquals(
      /** @lang XML */'<node sample="yes">content</node>',
      $document->saveXML($this->readAttribute($newValue, '_node'))
    );
  }

  /**
  * @covers \PapayaTemplateValue::append
  * @covers \PapayaTemplateValue::_getDocument
  */
  public function testAppendWithDomElement() {
    $document = new \PapayaXmlDocument();
    $node = $document->createElement('node');
    $value = new \PapayaTemplateValue($document);
    $newValue = $value->append($node, array('sample' => 'yes'), 'content');
    $this->assertEquals(
      /** @lang XML */'<node sample="yes">content</node>',
      $document->saveXML($this->readAttribute($newValue, '_node'))
    );
  }

  /**
  * @covers \PapayaTemplateValue::append
  * @covers \PapayaTemplateValue::_getDocument
  */
  public function testAppendWithDomDocument() {
    $document = new \PapayaXmlDocument();
    $document->appendChild($node = $document->createElement('node'));
    $value = new \PapayaTemplateValue($document);
    $newValue = $value->append($document, array('sample' => 'yes'), 'content');
    $this->assertEquals(
      /** @lang XML */'<node sample="yes">content</node>',
      $document->saveXML($this->readAttribute($newValue, '_node'))
    );
  }

  /**
  * @covers \PapayaTemplateValue::append
  * @covers \PapayaTemplateValue::_getDocument
  */
  public function testAppendOnDOMElement() {
    $document = new \PapayaXmlDocument();
    $document->appendChild($node = $document->createElement('node'));
    $value = new \PapayaTemplateValue($node);
    $newValue = $value->append('child');
    $this->assertEquals(
      /** @lang XML */'<node><child/></node>',
      $document->saveXML($this->readAttribute($value, '_node'))
    );
    $this->assertEquals(
      /** @lang XML */'<child/>',
      $document->saveXML($this->readAttribute($newValue, '_node'))
    );
  }

  /**
  * @covers \PapayaTemplateValue::append
  */
  public function testAppendWithPapayaXmlAppendable() {
    $appendable = $this->createMock(\PapayaXmlAppendable::class);
    $appendable
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\PapayaXmlElement::class));

    $document = new \PapayaXmlDocument();
    $document->appendChild($node = $document->createElement('node'));
    $value = new \PapayaTemplateValue($node);
    $value->append($appendable);
  }

  /**
  * @covers \PapayaTemplateValue::append
  * @covers \PapayaTemplateValue::_getDocument
  */
  public function testAppendWithInvalidElement() {
    $document = new \PapayaXmlDocument();
    $value = new \PapayaTemplateValue($document);
    $this->expectException(InvalidArgumentException::class);
    $value->append(5);
  }

  /**
  * @covers \PapayaTemplateValue::append
  * @covers \PapayaTemplateValue::_getDocument
  */
  public function testAppendWithEmptyDocument() {
    $document = new \PapayaXmlDocument();
    $value = new \PapayaTemplateValue($document);
    $this->expectException(InvalidArgumentException::class);
    $value->append($document);
  }

  /**
  * @covers \PapayaTemplateValue::appendXml
  */
  public function testAppendXml() {
    $document = new \PapayaXmlDocument();
    $value = new \PapayaTemplateValue($document);
    $newValue = $value->appendXml(/** @lang XML */'<child/>');
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */'<?xml version="1.0" encoding="UTF-8"?><child/>',
      $document->saveXML($this->readAttribute($newValue, '_node'))
    );
  }

  /**
  * @covers \PapayaTemplateValue::xml
  */
  public function testXmlWithoutArgument() {
    $document = new \PapayaXmlDocument();
    $document->loadXml(/** @lang XML */'<sample><child/>test</sample>');
    $value = new \PapayaTemplateValue($document->documentElement);
    $this->assertEquals(
      // language=XML prefix=<fragment> suffix=</fragment>
      '<child/>test',
      $value->xml()
    );
  }

  /**
  * @covers \PapayaTemplateValue::xml
  */
  public function testXmlWithEmptyArgumentRemovingElements() {
    $document = new \PapayaXmlDocument();
    $document->loadXml(/** @lang XML */'<sample><child/>test</sample>');
    $value = new \PapayaTemplateValue($document->documentElement);
    $this->assertEquals(
      '',
      $value->xml('')
    );
  }

  /**
  * @covers \PapayaTemplateValue::xml
  */
  public function testXmlWithXmlFragment() {
    $document = new \PapayaXmlDocument();
    $document->loadXml(/** @lang XML */'<sample/>');
    $value = new \PapayaTemplateValue($document->documentElement);
    $this->assertEquals(
      // language=XML prefix=<fragment> suffix=</fragment>
      '<child/>test',
      $value->xml('<child/>test')
    );
  }

  /**
  * @covers \PapayaTemplateValue::xml
  */
  public function testXmlWithDomnode() {
    $document = new \PapayaXmlDocument();
    $document->loadXml(/** @lang XML */'<sample/>');
    $value = new \PapayaTemplateValue($document->documentElement);
    $this->assertEquals(
      /** @lang XML */'<child/>',
      $value->xml($document->createElement('child'))
    );
  }

  /**
  * @covers \PapayaTemplateValue::xml
  */
  public function testXmlWithArrayOfDomnodes() {
    $document = new \PapayaXmlDocument();
    $document->loadXml(/** @lang XML */'<sample/>');
    $value = new \PapayaTemplateValue($document->documentElement);
    $this->assertEquals(
      // language=XML prefix=<fragment> suffix=</fragment>
    '<child/>text',
      $value->xml(
        array(
          $document->createElement('child'),
          $document->createTextNode('text')
        )
      )
    );
  }

  /**
  * @covers \PapayaTemplateValue::xml
  */
  public function testXmlWithInvalidArgumentExpectingException() {
    $document = new \PapayaXmlDocument();
    $document->loadXml(/** @lang XML */'<sample/>');
    $value = new \PapayaTemplateValue($document->documentElement);
    $this->expectException(InvalidArgumentException::class);
    $value->xml(1);
  }

  /**
  * @covers \PapayaTemplateValue::xml
  */
  public function testXmlWithInvalidArrayExpectingException() {
    $document = new \PapayaXmlDocument();
    $document->loadXml(/** @lang XML */'<sample/>');
    $value = new \PapayaTemplateValue($document->documentElement);
    $this->expectException(InvalidArgumentException::class);
    $value->xml(array('child'));
  }

}
