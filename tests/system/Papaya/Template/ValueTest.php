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

namespace Papaya\Template;
require_once __DIR__.'/../../../bootstrap.php';

class ValueTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Template\Value::__construct
   */
  public function testConstructorWithDocument() {
    $document = new \Papaya\XML\Document();
    $value = new Value($document);
    $this->assertAttributeSame(
      $document, '_node', $value
    );
  }

  /**
   * @covers \Papaya\Template\Value::__construct
   */
  public function testConstructorWithDomnode() {
    $document = new \Papaya\XML\Document();
    $node = $document->createElement('node');
    $value = new Value($node);
    $this->assertAttributeSame(
      $node, '_node', $value
    );
  }

  /**
   * @covers \Papaya\Template\Value::node
   */
  public function testNode() {
    $document = new \Papaya\XML\Document();
    $value = new Value($document);
    $this->assertSame(
      $document, $value->node()
    );
  }

  /**
   * @covers \Papaya\Template\Value::node
   */
  public function testNodeWithArgument() {
    $document = new \Papaya\XML\Document();
    $value = new Value($document->appendElement('dom'));
    $this->assertSame(
      $document,
      $value->node($document)
    );
    $this->assertAttributeSame(
      $document, '_node', $value
    );
  }

  /**
   * @covers \Papaya\Template\Value::node
   */
  public function testNodeWithInvalidArgumentExpectingException() {
    $document = new \Papaya\XML\Document();
    $value = new Value($document->appendElement('dom'));
    $this->expectException(\InvalidArgumentException::class);
    $value->node(new \stdClass());
  }

  /**
   * @covers \Papaya\Template\Value::appendTo
   */
  public function testAppendTo() {
    $document = new \Papaya\XML\Document();
    $document->appendChild($document->createElement('test'));
    $node = $document->createElement('node');
    $value = new Value($node);
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
   * @covers \Papaya\Template\Value::append
   * @covers \Papaya\Template\Value::_getDocument
   */
  public function testAppendWithString() {
    $document = new \Papaya\XML\Document();
    $value = new Value($document);
    $newValue = $value->append('node', array('sample' => 'yes'), 'content');
    $this->assertEquals(
    /** @lang XML */
      '<node sample="yes">content</node>',
      $document->saveXML($this->readAttribute($newValue, '_node'))
    );
  }

  /**
   * @covers \Papaya\Template\Value::append
   * @covers \Papaya\Template\Value::_getDocument
   */
  public function testAppendWithDomElement() {
    $document = new \Papaya\XML\Document();
    $node = $document->createElement('node');
    $value = new Value($document);
    $newValue = $value->append($node, array('sample' => 'yes'), 'content');
    $this->assertEquals(
    /** @lang XML */
      '<node sample="yes">content</node>',
      $document->saveXML($this->readAttribute($newValue, '_node'))
    );
  }

  /**
   * @covers \Papaya\Template\Value::append
   * @covers \Papaya\Template\Value::_getDocument
   */
  public function testAppendWithDomDocument() {
    $document = new \Papaya\XML\Document();
    $document->appendChild($node = $document->createElement('node'));
    $value = new Value($document);
    $newValue = $value->append($document, array('sample' => 'yes'), 'content');
    $this->assertEquals(
    /** @lang XML */
      '<node sample="yes">content</node>',
      $document->saveXML($this->readAttribute($newValue, '_node'))
    );
  }

  /**
   * @covers \Papaya\Template\Value::append
   * @covers \Papaya\Template\Value::_getDocument
   */
  public function testAppendOnDOMElement() {
    $document = new \Papaya\XML\Document();
    $document->appendChild($node = $document->createElement('node'));
    $value = new Value($node);
    $newValue = $value->append('child');
    $this->assertEquals(
    /** @lang XML */
      '<node><child/></node>',
      $document->saveXML($this->readAttribute($value, '_node'))
    );
    $this->assertEquals(
    /** @lang XML */
      '<child/>',
      $document->saveXML($this->readAttribute($newValue, '_node'))
    );
  }

  /**
   * @covers \Papaya\Template\Value::append
   */
  public function testAppendWithPapayaXmlAppendable() {
    $appendable = $this->createMock(\Papaya\XML\Appendable::class);
    $appendable
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));

    $document = new \Papaya\XML\Document();
    $document->appendChild($node = $document->createElement('node'));
    $value = new Value($node);
    $value->append($appendable);
  }

  /**
   * @covers \Papaya\Template\Value::append
   * @covers \Papaya\Template\Value::_getDocument
   */
  public function testAppendWithInvalidElement() {
    $document = new \Papaya\XML\Document();
    $value = new Value($document);
    $this->expectException(\InvalidArgumentException::class);
    $value->append(5);
  }

  /**
   * @covers \Papaya\Template\Value::append
   * @covers \Papaya\Template\Value::_getDocument
   */
  public function testAppendWithEmptyDocument() {
    $document = new \Papaya\XML\Document();
    $value = new Value($document);
    $this->expectException(\InvalidArgumentException::class);
    $value->append($document);
  }

  /**
   * @covers \Papaya\Template\Value::appendXML
   */
  public function testAppendXml() {
    $document = new \Papaya\XML\Document();
    $value = new Value($document);
    $newValue = $value->appendXML(/** @lang XML */
      '<child/>');
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<?xml version="1.0" encoding="UTF-8"?><child/>',
      $document->saveXML($this->readAttribute($newValue, '_node'))
    );
  }

  /**
   * @covers \Papaya\Template\Value::xml
   */
  public function testXmlWithoutArgument() {
    $document = new \Papaya\XML\Document();
    $document->loadXml(/** @lang XML */
      '<sample><child/>test</sample>');
    $value = new Value($document->documentElement);
    $this->assertEquals(
    // language=XML prefix=<fragment> suffix=</fragment>
      '<child/>test',
      $value->xml()
    );
  }

  /**
   * @covers \Papaya\Template\Value::xml
   */
  public function testXmlWithEmptyArgumentRemovingElements() {
    $document = new \Papaya\XML\Document();
    $document->loadXml(/** @lang XML */
      '<sample><child/>test</sample>');
    $value = new Value($document->documentElement);
    $this->assertEquals(
      '',
      $value->xml('')
    );
  }

  /**
   * @covers \Papaya\Template\Value::xml
   */
  public function testXmlWithXmlFragment() {
    $document = new \Papaya\XML\Document();
    $document->loadXml(/** @lang XML */
      '<sample/>');
    $value = new Value($document->documentElement);
    $this->assertEquals(
    // language=XML prefix=<fragment> suffix=</fragment>
      '<child/>test',
      $value->xml('<child/>test')
    );
  }

  /**
   * @covers \Papaya\Template\Value::xml
   */
  public function testXmlWithDomnode() {
    $document = new \Papaya\XML\Document();
    $document->loadXml(/** @lang XML */
      '<sample/>');
    $value = new Value($document->documentElement);
    $this->assertEquals(
    /** @lang XML */
      '<child/>',
      $value->xml($document->createElement('child'))
    );
  }

  /**
   * @covers \Papaya\Template\Value::xml
   */
  public function testXmlWithArrayOfDomnodes() {
    $document = new \Papaya\XML\Document();
    $document->loadXml(/** @lang XML */
      '<sample/>');
    $value = new Value($document->documentElement);
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
   * @covers \Papaya\Template\Value::xml
   */
  public function testXmlWithInvalidArgumentExpectingException() {
    $document = new \Papaya\XML\Document();
    $document->loadXml(/** @lang XML */
      '<sample/>');
    $value = new Value($document->documentElement);
    $this->expectException(\InvalidArgumentException::class);
    $value->xml(1);
  }

  /**
   * @covers \Papaya\Template\Value::xml
   */
  public function testXmlWithInvalidArrayExpectingException() {
    $document = new \Papaya\XML\Document();
    $document->loadXml(/** @lang XML */
      '<sample/>');
    $value = new Value($document->documentElement);
    $this->expectException(\InvalidArgumentException::class);
    $value->xml(array('child'));
  }

}
