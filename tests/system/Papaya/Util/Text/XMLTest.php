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

namespace Papaya\Utility\Text;

require_once __DIR__.'/../../../../bootstrap.php';

class XMLTest extends \PapayaTestCase {

  /**
   * @covers       \Papaya\Utility\Text\XML::escape
   * @dataProvider escapeDataProvider
   * @param string $string
   * @param string $expected
   */
  public function testEscape($string, $expected) {
    $this->assertEquals(
      $expected,
      XML::escape($string)
    );
  }

  /**
   * @covers       \Papaya\Utility\Text\XML::unescape
   * @dataProvider escapeDataProvider
   * @param string $string
   * @param string $expected
   */
  public function testUnescape($expected, $string) {
    $this->assertEquals(
      $expected,
      XML::unescape($string)
    );
  }

  /**
   * @covers       \Papaya\Utility\Text\XML::escapeAttribute
   * @dataProvider escapeAttributeDataProvider
   * @param string $string
   * @param string $expected
   */
  public function testEscapeAttribute($string, $expected) {
    $this->assertEquals(
      $expected,
      XML::escapeAttribute($string)
    );
  }

  /**
   * @covers \Papaya\Utility\Text\XML::repairEntities
   * @backupGlobals disabled
   * @backupStaticAttributes disabled
   * @preserveGlobalState disabled
   * @runInSeparateProcess
   */
  public function testRepairEntitiesInitialisesTranslationTable() {
    $this->assertEquals(
      'ä',
      XML::repairEntities('&auml;')
    );
  }

  /**
   * @covers       \Papaya\Utility\Text\XML::repairEntities
   * @dataProvider getXhtmlDataToRepair
   * @param string $string
   * @param string $expected
   */
  public function testRepairEntities($expected, $string) {
    $this->assertEquals(
      $expected,
      XML::repairEntities($string)
    );
  }

  /**
   * @covers       \Papaya\Utility\Text\XML::serializeArray
   * @covers       \Papaya\Utility\Text\XML::_serializeSubArray
   * @dataProvider provideSerializerArrayAndXml
   * @param string $expected
   * @param array $array
   */
  public function testSerializeArray($expected, $array) {
    $this->assertXmlStringEqualsXmlString($expected, XML::serializeArray($array));
  }

  /**
   * @covers \Papaya\Utility\Text\XML::serializeArray
   * @covers \Papaya\Utility\Text\XML::_serializeSubArray
   */
  public function testSerializeArrayWithName() {
    $this->assertEquals(
    /** @lang XML */
      '<sample version="2"><sample-element name="foo">bar</sample-element></sample>',
      XML::serializeArray(array('foo' => 'bar'), 'sample')
    );
  }

  /**
   * @covers       \Papaya\Utility\Text\XML::unserializeArray
   * @covers       \Papaya\Utility\Text\XML::_unserializeArrayFromNode
   * @dataProvider provideSerializerArrayAndXml
   * @param string $xml
   * @param string $expected
   */
  public function testUnserializeArray($xml, $expected) {
    $this->assertEquals($expected, XML::unserializeArray($xml));
  }

  /**
   * @covers \Papaya\Utility\Text\XML::unserializeArray
   */
  public function testDeserializeArrayWithEmptyString() {
    $this->assertEquals(array(), XML::unserializeArray(''));
  }

  /**
   * @covers       \Papaya\Utility\Text\XML::unserializeArray
   * @covers       \Papaya\Utility\Text\XML::decodeOldEntitiesToUtf8
   * @dataProvider provideOldEncodedEntities
   * @param array $expected
   * @param string $entities
   */
  public function testUnserializeWithOldEntities($expected, $entities) {
    $this->assertEquals(
      array('foo' => $expected),
      XML::unserializeArray(
      /** @lang XML */
        "<sample><sample-element name='foo'>$entities</sample-element></sample>"
      )
    );
  }

  /**
   * @covers \Papaya\Utility\Text\XML::unserializeArray
   * @covers \Papaya\Utility\Text\XML::_unserializeArrayFromNode
   */
  public function testDeserializeWithOldEscapingAndDoubleEscapedData() {
    $this->assertEquals(
      array('foo' => '"<br/>'),
      XML::unserializeArray(
      /** @lang XML */
        '<sample><sample-element name="foo">&amp;quot;&lt;br/&gt;</sample-element></sample>'
      )
    );
  }

  /**
   * @covers \Papaya\Utility\Text\XML::unserializeArray
   * @covers \Papaya\Utility\Text\XML::_unserializeArrayFromNode
   */
  public function testDeserializeListWithOldEscapingAndDoubleEscapedData() {
    $this->assertEquals(
      array('bar' => array('foo' => '"<br/>')),
      XML::unserializeArray(
      /** @lang XML */
        '<sample>
          <sample-list name="bar">
            <sample-element name="foo">&amp;quot;&lt;br/&gt;</sample-element>
          </sample-list>
        </sample>'
      )
    );
  }

  /**
   * @covers       \Papaya\Utility\Text\XML::truncate
   * @covers       \Papaya\Utility\Text\XML::_truncateChildNodes
   * @covers       \Papaya\Utility\Text\XML::_copyElement
   * @dataProvider provideTruncateXml
   * @param string $expected
   * @param string $xml
   * @param int $length
   */
  public function testTruncate($expected, $xml, $length) {
    $document = new \DOMDocument('1.0', 'UTF-8');
    $document->loadXML($xml);
    $node = XML::truncate($document->documentElement, $length);
    $this->assertXmlStringEqualsXmlString(
      $expected, $node->ownerDocument->saveXML($node)
    );
  }

  /**
   * @covers       \Papaya\Utility\Text\XML::isQName
   * @dataProvider provideValidQualifiedNames
   * @param string $qualifiedName
   */
  public function testIsQName($qualifiedName) {
    $this->assertTrue(XML::isQName($qualifiedName));
  }

  /**
   * @covers \Papaya\Utility\Text\XML::isQName
   */
  public function testIsQNameWithEmptyNameExpectingException() {
    $this->expectException(\UnexpectedValueException::class);
    XML::isQName('');
  }

  /**
   * @covers       \Papaya\Utility\Text\XML::isNcName
   * @dataProvider provideValidNcNames
   * @param string $tagName
   * @param int $offset
   * @param int $length
   */
  public function testIsNcName($tagName, $offset, $length) {
    $this->assertTrue(XML::isNcName($tagName, $offset, $length));
  }

  /**
   * @covers \Papaya\Utility\Text\XML::isNcName
   */
  public function testIsNcNameWithEmptyTagnameExpectingException() {
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('Invalid QName "nc:": Missing QName part.');
    XML::isNcName('nc:', 3);
  }

  /**
   * @covers \Papaya\Utility\Text\XML::isNcName
   */
  public function testIsNcNameWithInvalidTagnameCharExpectingException() {
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('Invalid QName "nc:ta<g>": Invalid character at index 5.');
    XML::isNcName('nc:ta<g>', 3);
  }

  /**
   * @covers \Papaya\Utility\Text\XML::isNcName
   */
  public function testIsNcNameWithInvalidTagnameStartingCharExpectingException() {
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('Invalid QName "nc:1tag": Invalid character at index 3.');
    XML::isNcName('nc:1tag', 3);
  }

  /*********************************
   * Data Provider
   *********************************/

  public static function provideOldEncodedEntities() {
    return array(
      'Umlauts' => array('oöüu', 'o&#195;&#182;&#195;&#188;u'),
      'Typographic Quotes' => array('„test“', '&#226;&#128;&#158;test&#226;&#128;&#156;')
    );
  }

  public static function escapeDataProvider() {
    return array(
      array(/** @lang Text */
        '<sample>', '&lt;sample&gt;'),
      array('"sample"', '&quot;sample&quot;'),
      array("'sample'", '&#039;sample&#039;'),
    );
  }

  public static function escapeAttributeDataProvider() {
    return array(
      array(/** @lang Text */
        '<sample>', '&lt;sample&gt;'),
      array('"sample"', '&quot;sample&quot;'),
      array("'sample'", '&#039;sample&#039;'),
      array("\r\n", '&#13;&#10;'),
    );
  }

  public static function provideSerializerArrayAndXml() {
    return array(
      array(/** @lang XML */
        '<data version="2"/>', array()),
      array(
        /** @lang XML */
        '<data version="2"><data-element name="test">value</data-element></data>',
        array('test' => 'value')
      ),
      array(
        /** @lang XML */
        '<data version="2">
        <data-list name="list">
        <data-element name="0">one</data-element>
        <data-element name="1">two</data-element>
        </data-list>
        </data>',
        array('list' => array('one', 'two'))
      ),
      array(
        /** @lang XML */
        '<data version="2"><data-element name="test">&lt;tag/&gt;</data-element></data>',
        array('test' => /** @lang XML */
          '<tag/>')
      ),
      array(
        /** @lang XML */
        '<data version="2">
          <data-element name="test">&lt;tag attr="&amp;quot;"/&gt;</data-element>
        </data>',
        array('test' => /** @lang XML */
          '<tag attr="&quot;"/>')
      ),
    );
  }

  public static function provideTruncateXml() {
    return array(
      'full copy' => array(
        /** @lang XML */
        '<sample><child>TEST 1</child><child>TEST 2</child></sample>',
        /** @lang XML */
        '<sample><child>TEST 1</child><child>TEST 2</child></sample>',
        100
      ),
      'first' => array(
        /** @lang XML */
        '<sample><child>TEST 1</child></sample>',
        /** @lang XML */
        '<sample><child>TEST 1</child><child>TEST 2</child></sample>',
        7
      ),
      'first two' => array(
        /** @lang XML */
        '<sample><child>TEST 1</child><child>TEST 2</child></sample>',
        /** @lang XML */
        '<sample><child>TEST 1</child><child>TEST 2</child><child>TEST 3</child></sample>',
        13
      ),
      'first and part two' => array(
        /** @lang XML */
        '<sample><child>TEST 1</child><child>TEST</child></sample>',
        /** @lang XML */
        '<sample><child>TEST 1</child><child>TEST 2</child><child>TEST 3</child></sample>',
        11
      ),
      'first and part child' => array(
        /** @lang XML */
        '<sample><child>TEST 1<child foo="bar">TEST</child></child></sample>',
        /** @lang XML */
        '<sample><child>TEST 1<child foo="bar">TEST 2</child></child></sample>',
        11
      )
    );
  }

  public static function provideValidQualifiedNames() {
    return array(
      array('tag'),
      array('namespace:tag'),
      array('_:_'),
      array('_-_'),
      array('_')
    );
  }

  public static function provideValidNcNames() {
    return array(
      array('html', 0, 0),
      array('tag23', 0, 0),
      array('sample-tag', 0, 0),
      array('sampleTag', 0, 0),
      array('ns:tag', 3, 0),
      array('ns:tag', 0, 2)
    );
  }

  public static function getXhtmlDataToRepair() {
    return array(
      array('&amp;', '&'),
      array('&amp;', '&amp;'),
      array('ö', '&ouml;'),
      array(/** @lang XML */
        '<div/>', /** @lang XML */
        '<div/>'),
      array(/** @lang XML */
        '<div data-value="&quot;foo&quot;"/>', /** @lang XML */
        '<div data-value="&quot;foo&quot;"/>'),
      array(/** @lang XML */
        '<div>"foo"</div>', /** @lang XML */
        '<div>"foo"</div>'),
      array(/** @lang XML */
        '<div>&quot;foo&quot;</div>', /** @lang XML */
        '<div>&quot;foo&quot;</div>'),
    );
  }
}
