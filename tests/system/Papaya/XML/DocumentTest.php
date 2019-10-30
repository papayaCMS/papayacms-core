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

  use InvalidArgumentException;
  use Papaya\TestCase;

  require_once __DIR__.'/../../../bootstrap.php';

  /**
   * @covers \Papaya\XML\Document
   */
  class DocumentTest extends TestCase {

    public function testConstructor() {
      $document = new Document();
      $this->assertInstanceOf(
        Element::class, $document->createElement('test')
      );
    }

    public function testGetXpath() {
      $document = new Document();
      $document->loadXML(
      /** @lang XML */
        '<element attribute="value">text</element>'
      );
      $this->assertInstanceOf('DOMXpath', $document->xpath());
    }

    public function testGetXpathRefreshesOnLoad() {
      $document = new Document();
      $xpathOne = $document->xpath();
      $document->loadXML(
      /** @lang XML */
        '<element attribute="value">text</element>'
      );
      $xpathTwo = $document->xpath();
      $this->assertNotSame($xpathOne, $xpathTwo);
    }

    public function testRegisterNamespacesOnXpathLazyRegistration() {
      $document = new Document();
      $document->registerNamespaces(['a' => 'urn:a']);
      $document->loadXML(
      /** @lang XML */
        '<element xmlns="urn:a" attribute="success">text</element>'
      );
      $this->assertEquals('success', $document->xpath()->evaluate('string(/a:element/@attribute)'));
    }

    public function testRegisterNamespacesOnXpathDirectRegistration() {
      $document = new Document();
      $document->loadXML(
      /** @lang XML */
        '<element xmlns="urn:a" attribute="success">text</element>'
      );
      $document->xpath();
      $document->registerNamespaces(['a' => 'urn:a']);
      $this->assertEquals('success', $document->xpath()->evaluate('string(/a:element/@attribute)'));
    }

    public function testRegisterNamespacesForReservedPrefixExpectingException() {
      $document = new Document();
      $this->expectException(InvalidArgumentException::class);
      $document->registerNamespace('xmlns', 'urn:a');
    }

    public function testGetNamespaceFromTagName() {
      $document = new Document();
      $document->registerNamespaces(['a' => 'urn:a']);
      $this->assertEquals('urn:a', $document->getNamespace('a:element'));
    }

    public function testGetNamespaceFromPrefix() {
      $document = new Document();
      $document->registerNamespaces(['a' => 'urn:a']);
      $this->assertEquals('urn:a', $document->getNamespace('a'));
    }

    public function testGetNamespaceFromPrefixForReservedNamespace() {
      $document = new Document();
      $this->assertEquals('http://www.w3.org/2000/xmlns/', $document->getNamespace('xmlns'));
    }

    public function testGetNamespaceExpectingException() {
      $document = new Document();
      $this->expectException(\UnexpectedValueException::class);
      $this->assertEquals('urn:a', $document->getNamespace('a'));
    }

    public function testAppendXml() {
      $document = new Document();
      $document->appendXML(
      /** @lang XML */
        '<element attribute="value">text</element>'
      );
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<element attribute="value">text</element>',
        $document->saveXML($document->documentElement)
      );
    }

    public function testAppendXmlWithTarget() {
      $document = new Document();
      $target = $document->appendElement('test');
      $document->appendXML(
      /** @lang XML */
        '<element attribute="value">text</element>', $target
      );
      $this->assertEquals(
      /** @lang XML */
        '<test><element attribute="value">text</element></test>',
        $document->saveXML($document->documentElement)
      );
    }

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

    public function testAppendXmlToDocumentWithoutElements() {
      $document = new Document();
      $document->appendXML('');
      $this->assertEquals(
        '<?xml version="1.0" encoding="UTF-8"?>'."\n",
        $document->saveXML()
      );
    }

    public function testAppendXmlWithInvalidChars() {
      $document = new Document();
      $ansiiUmlauts = utf8_decode('äöü');
      $document->appendXML(
      /** @lang XML */
        "<element>$ansiiUmlauts</element>"
      );
      $this->assertEquals(
      /** @lang XML */
        '<element>äöü</element>',
        $document->saveXML($document->documentElement)
      );
    }

    public function testAppendElement() {
      $document = new Document();
      $document->appendElement('sample', ['attribute' => 42], 'content');
      $this->assertEquals(
      /** @lang XML */
        '<sample attribute="42">content</sample>',
        $document->saveXML($document->documentElement)
      );
    }

    public function testAppendElementWithNamespace() {
      $document = new Document();
      $document->registerNamespaces(['a' => 'urn:a']);
      $document->appendElement('a:sample', ['attribute' => 42], 'content');
      $this->assertEquals(
      /** @lang XML */
        '<a:sample xmlns:a="urn:a" attribute="42">content</a:sample>',
        $document->saveXML($document->documentElement)
      );
    }

    public function testAppendElementWithAttributeUsingNamespace() {
      $document = new Document();
      $document->registerNamespaces(['a' => 'urn:a']);
      $document->appendElement('sample', ['a:attribute' => 42], 'content');
      $this->assertEquals(
      /** @lang XML */
        '<sample xmlns:a="urn:a" a:attribute="42">content</sample>',
        $document->saveXML($document->documentElement)
      );
    }

    public function testCreateElement() {
      $document = new Document();
      $node = $document->createElement('sample');
      $this->assertEquals(
      /** @lang XML */
        '<sample/>',
        $node->saveXML()
      );
    }

    public function testCreateElementWithContent() {
      $document = new Document();
      $node = $document->createElement('sample', 'content');
      $this->assertEquals(
      /** @lang XML */
        '<sample>content</sample>',
        $node->saveXML()
      );
    }

    public function testCreateElementWithNamespace() {
      $document = new Document();
      $document->registerNamespaces(['a' => 'urn:a']);
      $node = $document->createElement('a:sample');
      $this->assertEquals(
        '<a:sample xmlns:a="urn:a"/>',
        $node->saveXML()
      );
    }

    public function testCreateElementWithNodesFromOtherDocument() {
      $source = Document::createFromXML('<sample>content</sample>');
      $document = new Document();
      $node = $document->createElement('sample', $source->documentElement);
      $this->assertEquals(
      /** @lang XML */
        '<sample><sample>content</sample></sample>',
        $node->saveXML()
      );
    }

    public function testCreateElementWithXMLAppendable() {
      $appendable = $this->createMock(Appendable::class);
      $appendable
        ->expects($this->once())
        ->method('appendTo')
        ->willReturnCallback(
          static function(Element $parent) {
            $parent->appendElement('appendable');
          }
        );
      $document = new Document();
      $node = $document->createElement('sample', $appendable);
      $this->assertEquals(
      /** @lang XML */
        '<sample><appendable/></sample>',
        $node->saveXML()
      );
    }

    public function testCreateElementNodeWithNamespace() {
      $document = new Document();
      $document->registerNamespaces(['a' => 'urn:a']);
      $node = Document::createElementNode($document, 'a:sample', ['a:attribute' => 'value'], 'content');
      $this->assertEquals(
        '<a:sample xmlns:a="urn:a" a:attribute="value">content</a:sample>',
        $node->saveXML()
      );
    }

    public function testCreateAttribute() {
      $document = new Document();
      $document->appendElement(
        'sample',
        $document->createAttribute('attribute', 'content')
      );
      $this->assertEquals(
      /** @lang XML */
        '<sample attribute="content"/>',
        $document->documentElement->saveXML()
      );
    }

    public function testCreateAttributeWithNamespace() {
      $document = new Document();
      $document->registerNamespaces(['a' => 'urn:a']);
      $document
        ->appendElement('sample')
        ->appendChild(
          $document->createAttribute('a:attribute', 'content')
        );
      $this->assertEquals(
      /** @lang XML */
        '<sample xmlns:a="urn:a" a:attribute="content"/>',
        $document->documentElement->saveXML()
      );
    }

    public function testActivateEntityLoaderGetAfterSet() {
      $document = new Document();
      $document->activateEntityLoader(TRUE);
      $this->assertTrue($document->activateEntityLoader());
    }

    public function testActivateEntityLoaderGetWithoutSet() {
      $document = new Document();
      $this->assertFalse($document->activateEntityLoader());
    }

    public function testLoadXml() {
      $document = new Document();
      $document->loadXML(file_get_contents(__DIR__.'/TestData/xmlWithoutEntity.xml'));
      $this->assertEquals('foo', $document->xpath()->evaluate('string(/sample)'));
    }

    public function testLoadXmlWithExternalEntity() {
      $document = new Document();
      $document->activateEntityLoader(TRUE);
      $document->loadXML(file_get_contents(__DIR__.'/TestData/xmlWithEntity.xml'), LIBXML_NOENT);
      $this->assertEquals('ENTITY_STRING', $document->xpath()->evaluate('string(/sample)'));
    }

    public function testCreateFromXml() {
      $document = Document::createFromXML(
      /** @lang XML */
        '<foo/>'
      );
      $this->assertEquals(
      /** @lang XML */
        '<foo/>', $document->documentElement->saveXML()
      );
    }

    public function testCreateFromWithInvalidXmlButSilentExpectingNull() {
      $document = Document::createFromXML('abc', TRUE);
      $this->assertNull($document);
    }
  }
}
