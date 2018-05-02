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

class PapayaXmlElementTest extends PapayaTestCase {

  /**
  * @covers PapayaXmlElement::append
  */
  public function testAppend() {
    $document = new PapayaXmlDocument();
    $element = $document->appendElement('sample');
    $element->append(new PapayaXmlAppendable_TestImplementation());
    $this->assertEquals(
      '<sample><success/></sample>',
      $document->saveXML($element)
    );
  }

  /**
  * @covers PapayaXmlElement::appendElement
  */
  public function testAppendElement() {
    $document = new PapayaXmlDocument();
    $element = $document->createElement('sample');
    $document->appendChild($element);
    $element->appendElement('test', array('attribute' => 42), 'content');
    $this->assertEquals(
      '<sample><test attribute="42">content</test></sample>',
      $document->saveXML($element)
    );
  }

  /**
  * @covers PapayaXmlElement::appendText
  */
  public function testAppendText() {
    $document = new PapayaXmlDocument();
    $element = $document->createElement('sample');
    $document->appendChild($element);
    $element->appendText('content');
    $this->assertEquals(
      '<sample>content</sample>',
      $document->saveXML($element)
    );
  }

  /**
  * @covers PapayaXmlElement::appendTo
  */
  public function testAppendToWithDocumentTarget() {
    $target = new DOMDocument('1.0', 'UTF-8');
    $document = new PapayaXmlDocument();
    $element = $document->createElement('sample');
    $element->appendTo($target);
    $this->assertEquals(
      '<sample/>',
      $target->saveXML($target->documentElement)
    );
  }

  /**
  * @covers PapayaXmlElement::appendTo
  */
  public function testAppendToWithElementTarget() {
    $document = new PapayaXmlDocument();
    $target = $document->createElement('sample');
    $document->appendChild($target);
    $element = $document->createElement('test');
    $element->appendTo($target);
    $this->assertEquals(
      '<sample><test/></sample>',
      $document->saveXML($target)
    );
  }

  /**
  * @covers PapayaXmlElement::appendTo
  */
  public function testAppendToWithNodeTargetExpectingException() {
    $document = new PapayaXmlDocument();
    $element = $document->createElement('test');
    $this->expectException(InvalidArgumentException::class);
    $element->appendTo($document->createTextNode('_'));
  }

  /**
  * @covers PapayaXmlElement::appendXml
  */
  public function testAppendXml() {
    $document = new PapayaXmlDocument();
    $target = $document->createElement('sample');
    $document->appendChild($target);
    $target->appendXml('<element/>text<element attribute="value"/>');
    $this->assertEquals(
      '<sample><element/>text<element attribute="value"/></sample>',
      $document->saveXML($target)
    );
  }

  /**
  * @covers PapayaXmlElement::saveXml
  */
  public function testSaveXml() {
    $document = new PapayaXmlDocument();
    $document->appendChild($document->createElement('sample'));
    $target = $document->createElement('test');
    $document->documentElement->appendChild($target);
    $this->assertEquals(
      '<test/>',
      $target->saveXml()
    );
  }

  /**
  * @covers PapayaXmlElement::saveFragment
  */
  public function testSaveFragment() {
    $document = new PapayaXmlDocument();
    $target = $document->appendElement('test');
    $target->appendElement('element', array('attribute' => 42));
    $target->appendText('text');
    $this->assertEquals(
      '<element attribute="42"/>text',
      $target->saveFragment()
    );
  }

  /**
   * @covers PapayaXmlElement::setAttribute
   * @dataProvider provideAttributeValues
   * @param string $expected
   * @param mixed $value
   */
  public function testSetAttribute($expected, $value) {
    $document = new PapayaXmlDocument();
    $target = $document->appendElement('test');
    $target->setAttribute('attribute', $value);
    $this->assertXmlStringEqualsXmlString(
      $expected,
      $target->saveXml()
    );
  }

  /**
  * @covers PapayaXmlElement::setAttribute
  */
  public function testSetAttributeWithEmptyValueExpectingNoAttribute() {
    $document = new PapayaXmlDocument();
    $target = $document->appendElement('test');
    $target->setAttribute('attribute', '');
    $this->assertEquals(
      '<test/>',
      $target->saveXml()
    );
  }

  /**
  * @covers PapayaXmlElement::setAttribute
  */
  public function testSetAttributeWithNamespaceAttribute() {
    $document = new PapayaXmlDocument();
    $target = $document->appendElement('test');
    $target->setAttribute('xmlns:a', 'urn:a');
    /** @noinspection UnknownInspectionInspection */
    /** @noinspection XmlUnusedNamespaceDeclaration */
    $this->assertEquals(
      '<test xmlns:a="urn:a"/>',
      $target->saveXml()
    );
  }

  /**
  * @covers PapayaXmlElement::setAttribute
  */
  public function testSetAttributeWithXmlIdAttribute() {
    $document = new PapayaXmlDocument();
    $target = $document->appendElement('test');
    $target->setAttribute('xml:id', 'idOne');
    $this->assertEquals(
      '<test xml:id="idOne"/>',
      $target->saveXml()
    );
  }

  public static function provideAttributeValues() {
    return array(
      array('<test attribute="42"/>', 42),
      array('<test attribute="value"/>', 'value'),
      array('<test attribute=" "/>', ' '),
      array('<test attribute="0"/>', 0),
      array('<test attribute=""/>', FALSE)
    );
  }

  public static function provideAttributeIgnoredValues() {
    return array(
      array(NULL),
      array('')
    );
  }
}

class PapayaXmlAppendable_TestImplementation implements PapayaXmlAppendable {
  public function appendTo(PapayaXmlElement $parent) {
    $parent->appendElement('success');
  }
}
