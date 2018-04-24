<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseConditionRootTest extends PapayaTestCase {

  /**
   * @covers PapayaDatabaseConditionRoot
   */
  public function testCallAddingFirstElement() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->setMethods(array('getSqlCondition'))
      ->disableOriginalConstructor()
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->will(
        $this->returnValueMap(
          array(
            array(array('foo' => 'bar'), NULL, '=', "foo = 'bar'")
          )
        )
      );
    $element = new PapayaDatabaseConditionRoot($databaseAccess);
    $element->isEqual('foo', 'bar');
    $this->assertEquals("foo = 'bar'", $element->getSql());
  }

  /**
   * @covers PapayaDatabaseConditionRoot
   */
  public function testCallAddingSecondElementExpectingException() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->setMethods(array('getSqlCondition'))
      ->disableOriginalConstructor()
      ->getMock();
    $element = new PapayaDatabaseConditionRoot($databaseAccess);
    $element->isEqual('foo', 'bar');
    $this->setExpectedException('LogicException');
    $element->isEqual('foo', 'bar');
  }

  /**
   * @covers PapayaDatabaseConditionRoot
   */
  public function testGetSqlWithoutElement() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->setMethods(array('getSqlCondition'))
      ->disableOriginalConstructor()
      ->getMock();
    $element = new PapayaDatabaseConditionRoot($databaseAccess);
    $this->assertEquals('', $element->getSql());
  }
}
