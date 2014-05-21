<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaTemplateSimpleAstNodeValueTest extends PapayaTestCase {

  /**
   * @covers PapayaTemplateSimpleAstNodeValue::__construct
   */
  public function testConstructorAndPropertyAccess() {
    $node = new PapayaTemplateSimpleAstNodeValue('foo', 'bar');
    $this->assertEquals('foo', $node->name);
    $this->assertEquals('bar', $node->default);
  }

}