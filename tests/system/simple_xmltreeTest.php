<?php
require_once __DIR__.'/../bootstrap.php';

class simple_xmltreeTest extends PapayaTestCase {

  /**
   * @covers simple_xmltree::unserializeArrayFromXML
   */
  public function testUnserializeArrayFromXML() {
    $xmlStr = '<data><data-element name="PAPAYA_LAYOUT_THEME"><![CDATA[theme]]></data-element><data-element name="PAPAYA_LAYOUT_TEMPLATES"><![CDATA[tpl]]></data-element></data>';
    $expected = array('PAPAYA_LAYOUT_THEME' => 'theme', 'PAPAYA_LAYOUT_TEMPLATES' => 'tpl');
    $actual = null;
    simple_xmltree::unserializeArrayFromXML('', $actual, $xmlStr);
    $this->assertEquals($expected, $actual);
  }

}
