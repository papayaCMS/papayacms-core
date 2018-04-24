<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUtilStringXmlTest extends PapayaTestCase {

  /**
  * @covers PapayaUtilStringXml::escape
  * @dataProvider escapeDataProvider
  */
  public function testEscape($string, $expected) {
    $this->assertEquals(
      $expected,
      PapayaUtilStringXml::escape($string)
    );
  }

  /**
  * @covers PapayaUtilStringXml::unescape
  * @dataProvider escapeDataProvider
  */
  public function testUnescape($expected, $string) {
    $this->assertEquals(
      $expected,
      PapayaUtilStringXml::unescape($string)
    );
  }

  /**
  * @covers PapayaUtilStringXml::escapeAttribute
  * @dataProvider escapeAttributeDataProvider
  */
  public function testEscapeAttribute($string, $expected) {
    $this->assertEquals(
      $expected,
      PapayaUtilStringXml::escapeAttribute($string)
    );
  }

  /**
   * @covers PapayaUtilStringXml::repairEntities
   * @backupGlobals disabled
   * @backupStaticAttributes disabled
   * @preserveGlobalState disabled
   * @runInSeparateProcess
   */
  public function testRepairEntitiesInitalizesTranslationTable() {
    $this->assertEquals(
      'ä',
      PapayaUtilStringXml::repairEntities('&auml;')
    );
  }

  /**
   * @covers PapayaUtilStringXml::repairEntities
   * @dataProvider getXhtmlDataToRepair
   */
  public function testRepairEntities($expected, $string) {
    $this->assertEquals(
      $expected,
      PapayaUtilStringXml::repairEntities($string)
    );
  }

  /**
  * @covers PapayaUtilStringXml::serializeArray
  * @covers PapayaUtilStringXml::_serializeSubArray
  * @dataProvider provideSerializerArrayAndXml
  */
  public function testSerializeArray($expected, $array) {
    $this->assertEquals($expected, PapayaUtilStringXml::serializeArray($array));
  }

  /**
  * @covers PapayaUtilStringXml::serializeArray
  * @covers PapayaUtilStringXml::_serializeSubArray
  */
  public function testSerializeArrayWithName() {
    $this->assertEquals(
      '<sample version="2"><sample-element name="foo">bar</sample-element></sample>',
      PapayaUtilStringXml::serializeArray(array('foo' => 'bar'), 'sample')
    );
  }

  /**
  * @covers PapayaUtilStringXml::unserializeArray
  * @covers PapayaUtilStringXml::_unserializeArrayFromNode
  * @dataProvider provideSerializerArrayAndXml
  */
  public function testUnserializeArray($xml, $expected) {
    $this->assertEquals($expected, PapayaUtilStringXml::unserializeArray($xml));
  }

  /**
  * @covers PapayaUtilStringXml::unserializeArray
  */
  public function testUnserializeArrayWithEmptyString() {
    $this->assertEquals(array(), PapayaUtilStringXml::unserializeArray(''));
  }

  /**
  * @covers PapayaUtilStringXml::unserializeArray
  * @covers PapayaUtilStringXml::decodeOldEntitiesToUtf8
  * @dataProvider provideOldEncodedEntities
  */
  public function testUnserializeWithOldEntities($expected, $entities) {
    $this->assertEquals(
      array('foo' => $expected),
      PapayaUtilStringXml::unserializeArray(
        '<sample><sample-element name="foo">'.$entities.'</sample-element></sample>'
      )
    );
  }

  /**
  * @covers PapayaUtilStringXml::unserializeArray
  * @covers PapayaUtilStringXml::_unserializeArrayFromNode
  */
  public function testUnserializeWithOldEscapingAndDoubleEscapedData() {
    $this->assertEquals(
      array('foo' => '"<br/>'),
      PapayaUtilStringXml::unserializeArray(
        '<sample><sample-element name="foo">&amp;quot;&lt;br/&gt;</sample-element></sample>'
      )
    );
  }

  /**
  * @covers PapayaUtilStringXml::unserializeArray
  * @covers PapayaUtilStringXml::_unserializeArrayFromNode
  */
  public function testUnserializeListWithOldEscapingAndDoubleEscapedData() {
    $this->assertEquals(
      array('bar' => array('foo' => '"<br/>')),
      PapayaUtilStringXml::unserializeArray(
        '<sample>
          <sample-list name="bar">
            <sample-element name="foo">&amp;quot;&lt;br/&gt;</sample-element>
          </sample-list>
        </sample>'
      )
    );
  }

  /**
  * @covers PapayaUtilStringXml::truncate
  * @covers PapayaUtilStringXml::_truncateChildNodes
  * @covers PapayaUtilStringXml::_copyElement
  * @dataProvider provideTruncateXml
  */
  public function testTruncate($expected, $xml, $length) {
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadXml($xml);
    $node = PapayaUtilStringXml::truncate($dom->documentElement, $length);
    $this->assertEquals(
      $expected, $node->ownerDocument->saveXml($node)
    );
  }

  /**
   * @covers PapayaUtilStringXml::isQName
   * @dataProvider provideValidQualifiedNames
   */
  public function testIsQName($qualifiedName) {
    $this->assertTrue(PapayaUtilStringXml::isQName($qualifiedName));
  }

  /**
   * @covers PapayaUtilStringXml::isQName
   */
  public function testIsQNameWithEmptyNameExpectingException() {
    $this->setExpectedException(UnexpectedValueException::class);
    PapayaUtilStringXml::isQName('');
  }

  /**
   * @covers PapayaUtilStringXml::isNcName
   * @dataProvider provideValidNcNames
   */
  public function testIsNcName($tagName, $offset, $length) {
    $this->assertTrue(PapayaUtilStringXml::isNcName($tagName, $offset, $length));
  }

  /**
   * @covers PapayaUtilStringXml::isNcName
   */
  public function testIsNcNameWithEmptyTagnameExpectingException() {
    $this->setExpectedException(
      'UnexpectedValueException',
      'Invalid QName "nc:": Missing QName part.'
    );
    PapayaUtilStringXml::isNcName('nc:', 3);
  }

  /**
   * @covers PapayaUtilStringXml::isNcName
   */
  public function testIsNcNameWithInvalidTagnameCharExpectingException() {
    $this->setExpectedException(
      'UnexpectedValueException',
      'Invalid QName "nc:ta<g>": Invalid character at index 5.'
    );
    PapayaUtilStringXml::isNcName('nc:ta<g>', 3);
  }

  /**
   * @covers PapayaUtilStringXml::isNcName
   */
  public function testIsNcNameWithInvalidTagnameStartingCharExpectingException() {
    $this->setExpectedException(
      'UnexpectedValueException',
      'Invalid QName "nc:1tag": Invalid character at index 3.'
    );
    PapayaUtilStringXml::isNcName('nc:1tag', 3);
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
      array('<sample>', '&lt;sample&gt;'),
      array('"sample"', '&quot;sample&quot;'),
      array("'sample'", '&#039;sample&#039;'),
    );
  }

  public static function escapeAttributeDataProvider() {
    return array(
      array('<sample>', '&lt;sample&gt;'),
      array('"sample"', '&quot;sample&quot;'),
      array("'sample'", '&#039;sample&#039;'),
      array("\r\n", '&#13;&#10;'),
    );
  }

  public static function provideSerializerArrayAndXml() {
    return array(
      array('<data version="2"/>', array()),
      array(
        '<data version="2"><data-element name="test">value</data-element></data>',
        array('test' => 'value')
      ),
      array(
        '<data version="2">'.
        '<data-list name="list">'.
        '<data-element name="0">one</data-element>'.
        '<data-element name="1">two</data-element>'.
        '</data-list>'.
        '</data>',
        array('list' => array('one', 'two'))
      ),
      array(
        '<data version="2"><data-element name="test">&lt;tag/&gt;</data-element></data>',
        array('test' => '<tag/>')
      ),
      array(
        '<data version="2">'.
          '<data-element name="test">&lt;tag attr="&amp;quot;"/&gt;</data-element>'.
        '</data>',
        array('test' => '<tag attr="&quot;"/>')
      ),
    );
  }

  public static function provideTruncateXml() {
    return array(
      'full copy' => array(
        '<sample><child>TEST 1</child><child>TEST 2</child></sample>',
        '<sample><child>TEST 1</child><child>TEST 2</child></sample>',
        100
      ),
      'first' => array(
        '<sample><child>TEST 1</child></sample>',
        '<sample><child>TEST 1</child><child>TEST 2</child></sample>',
        7
      ),
      'first two' => array(
        '<sample><child>TEST 1</child><child>TEST 2</child></sample>',
        '<sample><child>TEST 1</child><child>TEST 2</child><child>TEST 3</child></sample>',
        13
      ),
      'first and part two' => array(
        '<sample><child>TEST 1</child><child>TEST</child></sample>',
        '<sample><child>TEST 1</child><child>TEST 2</child><child>TEST 3</child></sample>',
        11
      ),
      'first and part child' => array(
        '<sample><child>TEST 1<child foo="bar">TEST</child></child></sample>',
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
      array('<div/>', '<div/>'),
      array('<div data-value="&quot;foo&quot;"/>', '<div data-value="&quot;foo&quot;"/>'),
      array('<div>"foo"</div>', '<div>"foo"</div>'),
      array('<div>&quot;foo&quot;</div>', '<div>&quot;foo&quot;</div>'),
    );
  }
}
