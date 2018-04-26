<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseConditionRootTest extends PapayaTestCase {

  /**
   * @covers PapayaDatabaseConditionRoot
   */
  public function testCallAddingFirstElement() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
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
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $element = new PapayaDatabaseConditionRoot($databaseAccess);
    $element->isEqual('foo', 'bar');
    $this->expectException(LogicException::class);
    $element->isEqual('foo', 'bar');
  }

  /**
   * @covers PapayaDatabaseConditionRoot
   */
  public function testGetSqlWithoutElement() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $element = new PapayaDatabaseConditionRoot($databaseAccess);
    $this->assertEquals('', $element->getSql());
  }
}
