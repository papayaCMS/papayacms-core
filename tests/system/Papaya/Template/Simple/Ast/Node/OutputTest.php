<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaTemplateSimpleAstNodeOutputTest extends PapayaTestCase {

  /**
   * @covers PapayaTemplateSimpleAstNodeOutput::__construct
   */
  public function testConstructorAndPropertyAccess() {
    $node = new PapayaTemplateSimpleAstNodeOutput('success');
    $this->assertEquals('success', $node->text);
  }

  /**
   * @covers PapayaTemplateSimpleAstNodeOutput::append
   */
  public function testAppend() {
    $node = new PapayaTemplateSimpleAstNodeOutput('foo');
    $node->append('bar');
    $this->assertEquals('foobar', $node->text);
  }
}