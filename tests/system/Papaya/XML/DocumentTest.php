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

namespace Papaya\XML;
require_once __DIR__.'/../../../bootstrap.php';

class DocumentTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\XML\Document::__construct
   */
  public function testConstructor() {
    $document = new Document();
    $this->assertInstanceOf(
      Element::class, $document->createElement('test')
    );
  }

  /**
   * @covers \Papaya\XML\Document::xpath
   */
  public function testGetXpath() {
    $document = new Document();
    $document->loadXML(/** @lang XML */
      '<element attribute="value">text</element>');
    $this->assertInstanceOf('DOMXpath', $document->xpath());
  }

  /**
   * @covers \Papaya\XML\Document::xpath
   */
  public function testGetXpathRefreshesOnLoad() {
    $document = new Document();
    $xpathOne = $document->xpath();
    $document->loadXML(/** @lang XML */
      '<element attribute="value">text</element>');
    $xpathTwo = $document->xpath();
    $this->assertNotSame($xpathOne, $xpathTwo);
  }

  /**
   * @covers \Papaya\XML\Document::registerNamespaces
   * @covers \Papaya\XML\Document::xpath
   */
  public function testRegisterNamespacesOnXpathLazyRegistration() {
    $document = new Document();
    $document->registerNamespaces(array('a' => 'urn:a'));
    $document->loadXML(/** @lang XML */
      '<element xmlns="urn:a" attribute="success">text</element>');
    $this->assertEquals('success', $document->xpath()->evaluate('string(/a:element/@attribute)'));
  }

  /**
   * @covers \Papaya\XML\Document::registerNamespaces
   * @covers \Papaya\XML\Document::xpath
   */
  public function testRegisterNamespacesOnXpathDirectRegistration() {
    $document = new Document();
    $document->loadXML(/** @lang XML */
      '<element xmlns="urn:a" attribute="success">text</element>');
    $document->xpath();
    $document->registerNamespaces(array('a' => 'urn:a'));
    $this->assertEquals('success', $document->xpath()->evaluate('string(/a:element/@attribute)'));
  }

  /**
   * @covers \Papaya\XML\Document::getNamespace
   */
  public function testGetNamespaceFromTagName() {
    $document = new Document();
    $document->registerNamespaces(array('a' => 'urn:a'));
    $this->assertEquals('urn:a', $document->getNamespace('a:element'));
  }

  /**
   * @covers \Papaya\XML\Document::getNamespace
   */
  public function testGetNamespaceFromPrefix() {
    $document = new Document();
    $document->registerNamespaces(array('a' => 'urn:a'));
    $this->assertEquals('urn:a', $document->getNamespace('a'));
  }

  /**
   * @covers \Papaya\XML\Document::getNamespace
   */
  public function testGetNamespaceExpectingException() {
    $document = new Document();
    $this->expectException(\UnexpectedValueException::class);
    $this->assertEquals('urn:a', $document->getNamespace('a'));
  }

  /**
   * @covers \Papaya\XML\Document::appendXML
   */
  public function testAppendXml() {
    $document = new Document();
    $document->appendXML(/** @lang XML */
      '<element attribute="value">text</element>');
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<element attribute="value">text</element>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
   * @covers \Papaya\XML\Document::appendXML
   */
  public function testAppendXmlWithTarget() {
    $document = new Document();
    $target = $document->appendElement('test');
    $document->appendXML(/** @lang XML */
      '<element attribute="value">text</element>', $target);
    $this->assertEquals(
    /** @lang XML */
      '<test><element attribute="value">text</element></test>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
   * @covers \Papaya\XML\Document::appendXML
   */
  public function testAppendXmlToDocumentIgnoredAdditionalElements() {
    $document = new Document();
    $document->appendXML(
    // language=XML prefix=<fragment> suffix=</fragment>
      '<one/><two/>'
    );
    $this->assertEquals(
    /** @lang XML */
      '<one/>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
   * @covers \Papaya\XML\Document::appendXML
   */
  public function testAppendXmlToDocumentWithoutElements() {
    $document = new Document();
    $document->appendXML('');
    $this->assertEquals(
      '<?xml version="1.0" encoding="UTF-8"?>'."\n",
      $document->saveXML()
    );
  }

  /**
   * @covers \Papaya\XML\Document::appendXML
   */
  public function testAppendXmlWithInvalidChars() {
    $document = new Document();
    $ansiiUmlauts = utf8_decode('äöü');
    $document->appendXML(/** @lang XML */
      "<element>$ansiiUmlauts</element>");
    $this->assertEquals(
    /** @lang XML */
      '<element>äöü</element>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
   * @covers \Papaya\XML\Document::appendElement
   * @covers \Papaya\XML\Document::createElement
   */
  public function testAppendElement() {
    $document = new Document();
    $document->appendElement('sample', array('attribute' => 42), 'content');
    $this->assertEquals(
    /** @lang XML */
      '<sample attribute="42">content</sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
   * @covers \Papaya\XML\Document::appendElement
   * @covers \Papaya\XML\Document::createElement
   * @covers \Papaya\XML\Document::createAttribute
   * @covers \Papaya\XML\Document::getNamespace
   */
  public function testAppendElementWithNamespace() {
    $document = new Document();
    $document->registerNamespaces(array('a' => 'urn:a'));
    $document->appendElement('a:sample', array('attribute' => 42), 'content');
    $this->assertEquals(
    /** @lang XML */
      '<a:sample xmlns:a="urn:a" attribute="42">content</a:sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
   * @covers \Papaya\XML\Document::appendElement
   * @covers \Papaya\XML\Document::createElement
   * @covers \Papaya\XML\Document::createAttribute
   * @covers \Papaya\XML\Document::getNamespace
   */
  public function testAppendElementWithAttributeUsingNamespace() {
    $document = new Document();
    $document->registerNamespaces(array('a' => 'urn:a'));
    $document->appendElement('sample', array('a:attribute' => 42), 'content');
    $this->assertEquals(
    /** @lang XML */
      '<sample xmlns:a="urn:a" a:attribute="42">content</sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
   * @covers \Papaya\XML\Document::createElement
   */
  public function testCreateElement() {
    $document = new Document();
    $node = $document->createElement('sample');
    $this->assertEquals(
    /** @lang XML */
      '<sample/>',
      $node->saveXML()
    );
  }

  /**
   * @covers \Papaya\XML\Document::createElement
   */
  public function testCreateElementWithContent() {
    $document = new Document();
    $node = $document->createElement('sample', 'content');
    $this->assertEquals(
    /** @lang XML */
      '<sample>content</sample>',
      $node->saveXML()
    );
  }

  /**
   * @covers \Papaya\XML\Document::createElement
   * @covers \Papaya\XML\Document::getNamespace
   */
  public function testCreateElementWithNamespace() {
    $document = new Document();
    $document->registerNamespaces(array('a' => 'urn:a'));
    $node = $document->createElement('a:sample');
    $this->assertEquals(
      '<a:sample xmlns:a="urn:a"/>',
      $node->saveXML()
    );
  }

  /**
   * @covers \Papaya\XML\Document::activateEntityLoader
   */
  public function testActivateEntityLoaderGetAfterSet() {
    $document = new Document();
    $document->activateEntityLoader(TRUE);
    $this->assertTrue($document->activateEntityLoader());
  }

  /**
   * @covers \Papaya\XML\Document::activateEntityLoader
   */
  public function testActivateEntityLoaderGetWithoutSet() {
    $document = new Document();
    $this->assertFalse($document->activateEntityLoader());
  }

  /**
   * @covers \Papaya\XML\Document::load
   */
  public function testLoadXml() {
    $document = new Document();
    $document->loadXML(file_get_contents(__DIR__.'/TestData/xmlWithoutEntity.xml'));
    $this->assertEquals('foo', $document->xpath()->evaluate('string(/sample)'));
  }

  /**
   * @covers \Papaya\XML\Document::loadXml
   */
  public function testLoadXmlWithExternalEntity() {
    $document = new Document();
    $document->activateEntityLoader(TRUE);
    $document->loadXML(file_get_contents(__DIR__.'/TestData/xmlWithEntity.xml'), LIBXML_NOENT);
    $this->assertEquals('ENTITY_STRING', $document->xpath()->evaluate('string(/sample)'));
  }

  /**
   * @covers \Papaya\XML\Document::createFromXML
   */
  public function testCreateFromXml() {
    $document = Document::createFromXML(/** @lang XML */
      '<foo/>');
    $this->assertEquals(/** @lang XML */
      '<foo/>', $document->documentElement->saveXML());
  }

  /**
   * @covers \Papaya\XML\Document::createFromXML
   */
  public function testCreateFromWithInvalidXmlButSilentExpectingNull() {
    $document = Document::createFromXML('abc', TRUE);
    $this->assertNull($document);
  }
}
