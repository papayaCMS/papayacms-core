<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentStructureNodeTest extends PapayaTestCase {

  /**
   * @covers PapayaContentStructureNode
   */
  public function testIssetWithValidPropertyExpectingTrue() {
    $node = new PapayaContentStructureNode_TestProxy();
    $this->assertTrue(isset($node->name));
  }

  /**
   * @covers PapayaContentStructureNode
   */
  public function testIssetWithInvalidPropertyExpectingFalse() {
    $node = new PapayaContentStructureNode_TestProxy();
    $this->assertFalse(isset($node->INVALID));
  }


  /**
   * @covers PapayaContentStructureNode
   * @dataProvider providePropertyValues
   */
  public function testGetAfterSet($expected, $name, $value) {
    $node = new PapayaContentStructureNode_TestProxy();
    $node->$name = $value;
    $this->assertEquals($expected, $node->$name);
  }

  /**
   * @covers PapayaContentStructureNode
   */
  public function testSetInvalidPropertyExpectingException() {
    $node = new PapayaContentStructureNode_TestProxy();
    $this->setExpectedException(UnexpectedValueException::class);
    $node->INVALID = 'foo';
  }

  /**
   * @covers PapayaContentStructureNode
   */
  public function testGetInvalidPropertyExpectingException() {
    $node = new PapayaContentStructureNode_TestProxy();
    $this->setExpectedException(UnexpectedValueException::class);
    $result = $node->INVALID;
  }

  /**
   * @covers PapayaContentStructureNode
   */
  public function testSetInvalidPropertyNameExpectingException() {
    $node = new PapayaContentStructureNode_TestProxy();
    $this->setExpectedException(UnexpectedValueException::class);
    $node->name = ':';
  }

  public static function providePropertyValues() {
    return array(
      array('success', 'name', 'success'),
      array('success', 'getter', ''),
      array('success', 'setter', ''),
      array('success', 'property', 'success')
    );
  }
}

class PapayaContentStructureNode_TestProxy extends PapayaContentStructureNode {

  public function __construct() {
    parent::__construct(
      array(
        'name' => 'test',
        'getter' => '',
        'setter' => '',
        'property' => ''
      )
    );
  }

  public function getGetter() {
    return 'success';
  }

  public function setSetter($value) {
    $this->setValue('setter', 'success');
  }

}
