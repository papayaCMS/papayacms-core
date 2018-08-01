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

class PapayaXmlDocumentTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Xml\Document::__construct
  */
  public function testConstructor() {
    $document = new \Papaya\Xml\Document();
    $this->assertInstanceOf(
      \Papaya\Xml\Element::class, $document->createElement('test')
    );
  }

  /**
  * @covers \Papaya\Xml\Document::xpath
  */
  public function testGetXpath() {
    $document = new \Papaya\Xml\Document();
    $document->loadXml(/** @lang XML */'<element attribute="value">text</element>');
    $this->assertInstanceOf('DOMXpath', $document->xpath());
  }

  /**
  * @covers \Papaya\Xml\Document::xpath
  */
  public function testGetXpathRefreshesOnLoad() {
    $document = new \Papaya\Xml\Document();
    $xpathOne = $document->xpath();
    $document->loadXml(/** @lang XML */'<element attribute="value">text</element>');
    $xpathTwo = $document->xpath();
    $this->assertNotSame($xpathOne, $xpathTwo);
  }

  /**
  * @covers \Papaya\Xml\Document::registerNamespaces
  * @covers \Papaya\Xml\Document::xpath
  */
  public function testRegisterNamespacesOnXpathLazyRegistration() {
    $document = new \Papaya\Xml\Document();
    $document->registerNamespaces(array('a' => 'urn:a'));
    $document->loadXml(/** @lang XML */'<element xmlns="urn:a" attribute="success">text</element>');
    $this->assertEquals('success', $document->xpath()->evaluate('string(/a:element/@attribute)'));
  }

  /**
  * @covers \Papaya\Xml\Document::registerNamespaces
  * @covers \Papaya\Xml\Document::xpath
  */
  public function testRegisterNamespacesOnXpathDirectRegistration() {
    $document = new \Papaya\Xml\Document();
    $document->loadXml(/** @lang XML */'<element xmlns="urn:a" attribute="success">text</element>');
    $document->xpath();
    $document->registerNamespaces(array('a' => 'urn:a'));
    $this->assertEquals('success', $document->xpath()->evaluate('string(/a:element/@attribute)'));
  }

  /**
  * @covers \Papaya\Xml\Document::getNamespace
  */
  public function testGetNamespaceFromTagName() {
    $document = new \Papaya\Xml\Document();
    $document->registerNamespaces(array('a' => 'urn:a'));
    $this->assertEquals('urn:a', $document->getNamespace('a:element'));
  }

  /**
  * @covers \Papaya\Xml\Document::getNamespace
  */
  public function testGetNamespaceFromPrefix() {
    $document = new \Papaya\Xml\Document();
    $document->registerNamespaces(array('a' => 'urn:a'));
    $this->assertEquals('urn:a', $document->getNamespace('a'));
  }

  /**
  * @covers \Papaya\Xml\Document::getNamespace
  */
  public function testGetNamespaceExpectingException() {
    $document = new \Papaya\Xml\Document();
    $this->expectException(UnexpectedValueException::class);
    $this->assertEquals('urn:a', $document->getNamespace('a'));
  }

  /**
  * @covers \Papaya\Xml\Document::appendXml
  */
  public function testAppendXml() {
    $document = new \Papaya\Xml\Document();
    $document->appendXml(/** @lang XML */'<element attribute="value">text</element>');
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */'<element attribute="value">text</element>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \Papaya\Xml\Document::appendXml
  */
  public function testAppendXmlWithTarget() {
    $document = new \Papaya\Xml\Document();
    $target = $document->appendElement('test');
    $document->appendXml(/** @lang XML */'<element attribute="value">text</element>', $target);
    $this->assertEquals(
    /** @lang XML */'<test><element attribute="value">text</element></test>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \Papaya\Xml\Document::appendXml
  */
  public function testAppendXmlToDocumentIgnoredAdditionalElements() {
    $document = new \Papaya\Xml\Document();
    $document->appendXml(
      // language=XML prefix=<fragment> suffix=</fragment>
      '<one/><two/>'
    );
    $this->assertEquals(
      /** @lang XML */'<one/>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \Papaya\Xml\Document::appendXml
  */
  public function testAppendXmlToDocumentWithoutElements() {
    $document = new \Papaya\Xml\Document();
    $document->appendXml('');
    $this->assertEquals(
      '<?xml version="1.0" encoding="UTF-8"?>'."\n",
      $document->saveXML()
    );
  }

  /**
  * @covers \Papaya\Xml\Document::appendXml
  */
  public function testAppendXmlWithInvalidChars() {
    $document = new \Papaya\Xml\Document();
    $ansiiUmlauts = utf8_decode('äöü');
    $document->appendXml(/** @lang XML */"<element>$ansiiUmlauts</element>");
    $this->assertEquals(
      /** @lang XML */'<element>äöü</element>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \Papaya\Xml\Document::appendElement
  * @covers \Papaya\Xml\Document::createElement
  */
  public function testAppendElement() {
    $document = new \Papaya\Xml\Document();
    $document->appendElement('sample', array('attribute' => 42), 'content');
    $this->assertEquals(
      /** @lang XML */'<sample attribute="42">content</sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \Papaya\Xml\Document::appendElement
  * @covers \Papaya\Xml\Document::createElement
  * @covers \Papaya\Xml\Document::createAttribute
  * @covers \Papaya\Xml\Document::getNamespace
  */
  public function testAppendElementWithNamespace() {
    $document = new \Papaya\Xml\Document();
    $document->registerNamespaces(array('a' => 'urn:a'));
    $document->appendElement('a:sample', array('attribute' => 42), 'content');
    $this->assertEquals(
      /** @lang XML */'<a:sample xmlns:a="urn:a" attribute="42">content</a:sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \Papaya\Xml\Document::appendElement
  * @covers \Papaya\Xml\Document::createElement
  * @covers \Papaya\Xml\Document::createAttribute
  * @covers \Papaya\Xml\Document::getNamespace
  */
  public function testAppendElementWithAttributeUsingNamespace() {
    $document = new \Papaya\Xml\Document();
    $document->registerNamespaces(array('a' => 'urn:a'));
    $document->appendElement('sample', array('a:attribute' => 42), 'content');
    $this->assertEquals(
      /** @lang XML */'<sample xmlns:a="urn:a" a:attribute="42">content</sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \Papaya\Xml\Document::createElement
  */
  public function testCreateElement() {
    $document = new \Papaya\Xml\Document();
    $node = $document->createElement('sample');
    $this->assertEquals(
      /** @lang XML */'<sample/>',
      $node->saveXml()
    );
  }

  /**
  * @covers \Papaya\Xml\Document::createElement
  */
  public function testCreateElementWithContent() {
    $document = new \Papaya\Xml\Document();
    $node = $document->createElement('sample', 'content');
    $this->assertEquals(
    /** @lang XML */'<sample>content</sample>',
      $node->saveXml()
    );
  }

  /**
  * @covers \Papaya\Xml\Document::createElement
  * @covers \Papaya\Xml\Document::getNamespace
  */
  public function testCreateElementWithNamespace() {
    $document = new \Papaya\Xml\Document();
    $document->registerNamespaces(array('a' => 'urn:a'));
    $node = $document->createElement('a:sample');
    $this->assertEquals(
      '<a:sample xmlns:a="urn:a"/>',
      $node->saveXml()
    );
  }

  /**
  * @covers \Papaya\Xml\Document::activateEntityLoader
  */
  public function testActivateEntityLoaderGetAfterSet() {
    $document = new \Papaya\Xml\Document();
    $document->activateEntityLoader(TRUE);
    $this->assertTrue($document->activateEntityLoader());
  }

  /**
  * @covers \Papaya\Xml\Document::activateEntityLoader
  */
  public function testActivateEntityLoaderGetWithoutSet() {
    $document = new \Papaya\Xml\Document();
    $this->assertFalse($document->activateEntityLoader());
  }

  /**
  * @covers \Papaya\Xml\Document::load
  */
  public function testLoadXml() {
    $document = new \Papaya\Xml\Document();
    $document->loadXml(file_get_contents(__DIR__.'/TestData/xmlWithoutEntity.xml'));
    $this->assertEquals('foo', $document->xpath()->evaluate('string(/sample)'));
  }

  /**
  * @covers \Papaya\Xml\Document::loadXml
  */
  public function testLoadXmlWithExternalEntity() {
    $document = new \Papaya\Xml\Document();
    $document->activateEntityLoader(TRUE);
    $document->loadXml(file_get_contents(__DIR__.'/TestData/xmlWithEntity.xml'), LIBXML_NOENT);
    $this->assertEquals('ENTITY_STRING', $document->xpath()->evaluate('string(/sample)'));
  }

  /**
  * @covers \Papaya\Xml\Document::createFromXml
  */
  public function testCreateFromXml() {
    $document = \Papaya\Xml\Document::createFromXml(/** @lang XML */'<foo/>');
    $this->assertEquals(/** @lang XML */'<foo/>', $document->documentElement->saveXml());
  }

  /**
  * @covers \Papaya\Xml\Document::createFromXml
  */
  public function testCreateFromWithInvalidXmlButSilentExpectingNull() {
    $document = \Papaya\Xml\Document::createFromXml('abc', TRUE);
    $this->assertNull($document);
  }
}
