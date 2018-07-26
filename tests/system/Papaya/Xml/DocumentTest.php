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
  * @covers \PapayaXmlDocument::__construct
  */
  public function testConstructor() {
    $document = new \PapayaXmlDocument();
    $this->assertInstanceOf(
      \PapayaXmlElement::class, $document->createElement('test')
    );
  }

  /**
  * @covers \PapayaXmlDocument::xpath
  */
  public function testGetXpath() {
    $document = new \PapayaXmlDocument();
    $document->loadXml(/** @lang XML */'<element attribute="value">text</element>');
    $this->assertInstanceOf('DOMXpath', $document->xpath());
  }

  /**
  * @covers \PapayaXmlDocument::xpath
  */
  public function testGetXpathRefreshesOnLoad() {
    $document = new \PapayaXmlDocument();
    $xpathOne = $document->xpath();
    $document->loadXml(/** @lang XML */'<element attribute="value">text</element>');
    $xpathTwo = $document->xpath();
    $this->assertNotSame($xpathOne, $xpathTwo);
  }

  /**
  * @covers \PapayaXmlDocument::registerNamespaces
  * @covers \PapayaXmlDocument::xpath
  */
  public function testRegisterNamespacesOnXpathLazyRegistration() {
    $document = new \PapayaXmlDocument();
    $document->registerNamespaces(array('a' => 'urn:a'));
    $document->loadXml(/** @lang XML */'<element xmlns="urn:a" attribute="success">text</element>');
    $this->assertEquals('success', $document->xpath()->evaluate('string(/a:element/@attribute)'));
  }

  /**
  * @covers \PapayaXmlDocument::registerNamespaces
  * @covers \PapayaXmlDocument::xpath
  */
  public function testRegisterNamespacesOnXpathDirectRegistration() {
    $document = new \PapayaXmlDocument();
    $document->loadXml(/** @lang XML */'<element xmlns="urn:a" attribute="success">text</element>');
    $document->xpath();
    $document->registerNamespaces(array('a' => 'urn:a'));
    $this->assertEquals('success', $document->xpath()->evaluate('string(/a:element/@attribute)'));
  }

  /**
  * @covers \PapayaXmlDocument::getNamespace
  */
  public function testGetNamespaceFromTagName() {
    $document = new \PapayaXmlDocument();
    $document->registerNamespaces(array('a' => 'urn:a'));
    $this->assertEquals('urn:a', $document->getNamespace('a:element'));
  }

  /**
  * @covers \PapayaXmlDocument::getNamespace
  */
  public function testGetNamespaceFromPrefix() {
    $document = new \PapayaXmlDocument();
    $document->registerNamespaces(array('a' => 'urn:a'));
    $this->assertEquals('urn:a', $document->getNamespace('a'));
  }

  /**
  * @covers \PapayaXmlDocument::getNamespace
  */
  public function testGetNamespaceExpectingException() {
    $document = new \PapayaXmlDocument();
    $this->expectException(UnexpectedValueException::class);
    $this->assertEquals('urn:a', $document->getNamespace('a'));
  }

  /**
  * @covers \PapayaXmlDocument::appendXml
  */
  public function testAppendXml() {
    $document = new \PapayaXmlDocument();
    $document->appendXml(/** @lang XML */'<element attribute="value">text</element>');
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */'<element attribute="value">text</element>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \PapayaXmlDocument::appendXml
  */
  public function testAppendXmlWithTarget() {
    $document = new \PapayaXmlDocument();
    $target = $document->appendElement('test');
    $document->appendXml(/** @lang XML */'<element attribute="value">text</element>', $target);
    $this->assertEquals(
    /** @lang XML */'<test><element attribute="value">text</element></test>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \PapayaXmlDocument::appendXml
  */
  public function testAppendXmlToDocumentIgnoredAdditionalElements() {
    $document = new \PapayaXmlDocument();
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
  * @covers \PapayaXmlDocument::appendXml
  */
  public function testAppendXmlToDocumentWithoutElements() {
    $document = new \PapayaXmlDocument();
    $document->appendXml('');
    $this->assertEquals(
      '<?xml version="1.0" encoding="UTF-8"?>'."\n",
      $document->saveXML()
    );
  }

  /**
  * @covers \PapayaXmlDocument::appendXml
  */
  public function testAppendXmlWithInvalidChars() {
    $document = new \PapayaXmlDocument();
    $ansiiUmlauts = utf8_decode('äöü');
    $document->appendXml(/** @lang XML */"<element>$ansiiUmlauts</element>");
    $this->assertEquals(
      /** @lang XML */'<element>äöü</element>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \PapayaXmlDocument::appendElement
  * @covers \PapayaXmlDocument::createElement
  */
  public function testAppendElement() {
    $document = new \PapayaXmlDocument();
    $document->appendElement('sample', array('attribute' => 42), 'content');
    $this->assertEquals(
      /** @lang XML */'<sample attribute="42">content</sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \PapayaXmlDocument::appendElement
  * @covers \PapayaXmlDocument::createElement
  * @covers \PapayaXmlDocument::createAttribute
  * @covers \PapayaXmlDocument::getNamespace
  */
  public function testAppendElementWithNamespace() {
    $document = new \PapayaXmlDocument();
    $document->registerNamespaces(array('a' => 'urn:a'));
    $document->appendElement('a:sample', array('attribute' => 42), 'content');
    $this->assertEquals(
      /** @lang XML */'<a:sample xmlns:a="urn:a" attribute="42">content</a:sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \PapayaXmlDocument::appendElement
  * @covers \PapayaXmlDocument::createElement
  * @covers \PapayaXmlDocument::createAttribute
  * @covers \PapayaXmlDocument::getNamespace
  */
  public function testAppendElementWithAttributeUsingNamespace() {
    $document = new \PapayaXmlDocument();
    $document->registerNamespaces(array('a' => 'urn:a'));
    $document->appendElement('sample', array('a:attribute' => 42), 'content');
    $this->assertEquals(
      /** @lang XML */'<sample xmlns:a="urn:a" a:attribute="42">content</sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \PapayaXmlDocument::createElement
  */
  public function testCreateElement() {
    $document = new \PapayaXmlDocument();
    $node = $document->createElement('sample');
    $this->assertEquals(
      /** @lang XML */'<sample/>',
      $node->saveXml()
    );
  }

  /**
  * @covers \PapayaXmlDocument::createElement
  */
  public function testCreateElementWithContent() {
    $document = new \PapayaXmlDocument();
    $node = $document->createElement('sample', 'content');
    $this->assertEquals(
    /** @lang XML */'<sample>content</sample>',
      $node->saveXml()
    );
  }

  /**
  * @covers \PapayaXmlDocument::createElement
  * @covers \PapayaXmlDocument::getNamespace
  */
  public function testCreateElementWithNamespace() {
    $document = new \PapayaXmlDocument();
    $document->registerNamespaces(array('a' => 'urn:a'));
    $node = $document->createElement('a:sample');
    $this->assertEquals(
      '<a:sample xmlns:a="urn:a"/>',
      $node->saveXml()
    );
  }

  /**
  * @covers \PapayaXmlDocument::activateEntityLoader
  */
  public function testActivateEntityLoaderGetAfterSet() {
    $document = new \PapayaXmlDocument();
    $document->activateEntityLoader(TRUE);
    $this->assertTrue($document->activateEntityLoader());
  }

  /**
  * @covers \PapayaXmlDocument::activateEntityLoader
  */
  public function testActivateEntityLoaderGetWithoutSet() {
    $document = new \PapayaXmlDocument();
    $this->assertFalse($document->activateEntityLoader());
  }

  /**
  * @covers \PapayaXmlDocument::load
  */
  public function testLoadXml() {
    $document = new \PapayaXmlDocument();
    $document->loadXml(file_get_contents(__DIR__.'/TestData/xmlWithoutEntity.xml'));
    $this->assertEquals('foo', $document->xpath()->evaluate('string(/sample)'));
  }

  /**
  * @covers \PapayaXmlDocument::loadXml
  */
  public function testLoadXmlWithExternalEntity() {
    $document = new \PapayaXmlDocument();
    $document->activateEntityLoader(TRUE);
    $document->loadXml(file_get_contents(__DIR__.'/TestData/xmlWithEntity.xml'), LIBXML_NOENT);
    $this->assertEquals('ENTITY_STRING', $document->xpath()->evaluate('string(/sample)'));
  }

  /**
  * @covers \PapayaXmlDocument::createFromXml
  */
  public function testCreateFromXml() {
    $document = \PapayaXmlDocument::createFromXml(/** @lang XML */'<foo/>');
    $this->assertEquals(/** @lang XML */'<foo/>', $document->documentElement->saveXml());
  }

  /**
  * @covers \PapayaXmlDocument::createFromXml
  */
  public function testCreateFromWithInvalidXmlButSilentExpectingNull() {
    $document = \PapayaXmlDocument::createFromXml('abc', TRUE);
    $this->assertNull($document);
  }
}
