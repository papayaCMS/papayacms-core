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

namespace Papaya\XML {
  require_once __DIR__.'/../../../bootstrap.php';

  class ElementTest extends \Papaya\TestCase {

    /**
     * @covers \Papaya\XML\Element::append
     */
    public function testAppend() {
      $document = new Document();
      $element = $document->appendElement('sample');
      $element->append(new XMLAppendable_TestImplementation());
      $this->assertEquals(
      /** @lang XML */
        '<sample><success/></sample>',
        $document->saveXML($element)
      );
    }

    /**
     * @covers \Papaya\XML\Element::appendElement
     */
    public function testAppendElement() {
      $document = new Document();
      $element = $document->createElement('sample');
      $document->appendChild($element);
      $element->appendElement('test', array('attribute' => 42), 'content');
      $this->assertEquals(
      /** @lang XML */
        '<sample><test attribute="42">content</test></sample>',
        $document->saveXML($element)
      );
    }

    /**
     * @covers \Papaya\XML\Element::appendText
     */
    public function testAppendText() {
      $document = new Document();
      $element = $document->createElement('sample');
      $document->appendChild($element);
      $element->appendText('content');
      $this->assertEquals(
      /** @lang XML */
        '<sample>content</sample>',
        $document->saveXML($element)
      );
    }

    /**
     * @covers \Papaya\XML\Element::appendTo
     */
    public function testAppendToWithDocumentTarget() {
      $target = new \DOMDocument('1.0', 'UTF-8');
      $document = new Document();
      $element = $document->createElement('sample');
      $element->appendTo($target);
      $this->assertEquals(
      /** @lang XML */
        '<sample/>',
        $target->saveXML($target->documentElement)
      );
    }

    /**
     * @covers \Papaya\XML\Element::appendTo
     */
    public function testAppendToWithElementTarget() {
      $document = new Document();
      $target = $document->createElement('sample');
      $document->appendChild($target);
      $element = $document->createElement('test');
      $element->appendTo($target);
      $this->assertEquals(
      /** @lang XML */
        '<sample><test/></sample>',
        $document->saveXML($target)
      );
    }

    /**
     * @covers \Papaya\XML\Element::appendTo
     */
    public function testAppendToWithNodeTargetExpectingException() {
      $document = new Document();
      $element = $document->createElement('test');
      $this->expectException(\InvalidArgumentException::class);
      $element->appendTo($document->createTextNode('_'));
    }

    /**
     * @covers \Papaya\XML\Element::appendXML
     */
    public function testAppendXml() {
      $document = new Document();
      $target = $document->createElement('sample');
      $document->appendChild($target);
      $target->appendXML(
      // language=XML prefix=<fragment> suffix=</fragment>
        '<element/>text<element attribute="value"/>'
      );
      $this->assertEquals(
      /** @lang XML */
        '<sample><element/>text<element attribute="value"/></sample>',
        $document->saveXML($target)
      );
    }

    /**
     * @covers \Papaya\XML\Element::saveXML
     */
    public function testSaveXml() {
      $document = new Document();
      $document->appendChild($document->createElement('sample'));
      $target = $document->createElement('test');
      $document->documentElement->appendChild($target);
      $this->assertEquals(
      /** @lang XML */
        '<test/>',
        $target->saveXML()
      );
    }

    /**
     * @covers \Papaya\XML\Element::saveFragment
     */
    public function testSaveFragment() {
      $document = new Document();
      $target = $document->appendElement('test');
      $target->appendElement('element', array('attribute' => 42));
      $target->appendText('text');
      $this->assertEquals(
      // language=XML prefix=<fragment> suffix=</fragment>
        '<element attribute="42"/>text',
        $target->saveFragment()
      );
    }

    /**
     * @covers       \Papaya\XML\Element::setAttribute
     * @dataProvider provideAttributeValues
     * @param string $expected
     * @param mixed $value
     */
    public function testSetAttribute($expected, $value) {
      $document = new Document();
      $target = $document->appendElement('test');
      $target->setAttribute('attribute', $value);
      $this->assertXmlStringEqualsXmlString(
        $expected,
        $target->saveXML()
      );
    }

    /**
     * @covers \Papaya\XML\Element::setAttribute
     */
    public function testSetAttributeWithEmptyValueExpectingNoAttribute() {
      $document = new Document();
      $target = $document->appendElement('test');
      $target->setAttribute('attribute', '');
      $this->assertEquals(
      /** @lang XML */
        '<test/>',
        $target->saveXML()
      );
    }

    /**
     * @covers \Papaya\XML\Element::setAttribute
     */
    public function testSetAttributeWithNamespaceAttribute() {
      $document = new Document();
      $target = $document->appendElement('test');
      $target->setAttribute('xmlns:a', 'urn:a');
      /** @noinspection UnknownInspectionInspection */
      /** @noinspection XmlUnusedNamespaceDeclaration */
      $this->assertEquals(
      /** @lang XML */
        '<test xmlns:a="urn:a"/>',
        $target->saveXML()
      );
    }

    /**
     * @covers \Papaya\XML\Element::setAttribute
     */
    public function testSetAttributeWithXmlIdAttribute() {
      $document = new Document();
      $target = $document->appendElement('test');
      $target->setAttribute('xml:id', 'idOne');
      $this->assertEquals(
      /** @lang XML */
        '<test xml:id="idOne"/>',
        $target->saveXML()
      );
    }

    public static function provideAttributeValues() {
      return array(
        array(/** @lang XML */
          '<test attribute="42"/>', 42),
        array(/** @lang XML */
          '<test attribute="value"/>', 'value'),
        array(/** @lang XML */
          '<test attribute=" "/>', ' '),
        array(/** @lang XML */
          '<test attribute="0"/>', 0),
        array(/** @lang XML */
          '<test attribute=""/>', FALSE)
      );
    }

    public static function provideAttributeIgnoredValues() {
      return array(
        array(NULL),
        array('')
      );
    }
  }

  class XMLAppendable_TestImplementation implements Appendable {
    public function appendTo(Element $parent) {
      $parent->appendElement('success');
    }
  }
}
