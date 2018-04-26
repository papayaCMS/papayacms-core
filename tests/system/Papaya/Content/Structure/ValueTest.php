<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentStructureValueTest extends PapayaTestCase {

  /**
   * @covers PapayaContentStructureValue::__construct
   */
  public function testConstructor() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaContentStructureGroup $group */
    $group = $this
      ->getMockBuilder(PapayaContentStructureGroup::class)
      ->disableOriginalConstructor()
      ->getMock();
    $value = new PapayaContentStructureValue($group);
    $this->assertAttributeSame($group, '_group', $value);
  }

  /**
   * @covers PapayaContentStructureValue::getIdentifier
   */
  public function testGetIdentifier() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaContentStructureGroup $group */
    $group = $this
      ->getMockBuilder(PapayaContentStructureGroup::class)
      ->disableOriginalConstructor()
      ->getMock();
    $group
      ->expects($this->once())
      ->method('getIdentifier')
      ->will($this->returnValue('PAGE/GROUP'));
    $value = new PapayaContentStructureValue($group);
    $value->name = 'VALUE';
    $this->assertEquals(
      'PAGE/GROUP/VALUE', $value->getIdentifier()
    );
  }
}
