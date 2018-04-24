<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentStructureValuesTest extends PapayaTestCase {

  /**
   * @covers PapayaContentStructureValues::__construct
   */
  public function testConstructor() {
    $group = $this
      ->getMockBuilder('PapayaContentStructureGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $values = new PapayaContentStructureValues($group);
    $this->assertEquals('PapayaContentStructureValue', $values->getItemClass());
  }

  /**
   * @covers PapayaContentStructureValues::load
   */
  public function testLoad() {
    $group = $this
      ->getMockBuilder('PapayaContentStructureGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $dom = new PapayaXmlDocument();
    $dom->load(__DIR__.'/../TestData/structure.xml');
    $values = new PapayaContentStructureValues($group);
    $values->load($dom->xpath()->evaluate('//page[1]/group[1]')->item(0));
    $this->assertCount(1, $values);
    $this->assertEquals('Font color', $values[0]->title);
    $this->assertEquals('COLOR', $values[0]->name);
    $this->assertEquals('text', $values[0]->type);
    $this->assertEquals('color', $values[0]->fieldType);
    $this->assertEquals('#FF0000', $values[0]->default);
    $this->assertEquals('main font color', $values[0]->hint);
  }

  /**
   * @covers PapayaContentStructureValues::load
   */
  public function testLoadValueWithMultipleParameters() {
    $group = $this
      ->getMockBuilder('PapayaContentStructureGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $dom = new PapayaXmlDocument();
    $dom->load(__DIR__.'/../TestData/structure.xml');
    $values = new PapayaContentStructureValues($group);
    $values->load($dom->xpath()->evaluate('//page[1]/group[2]')->item(0));
    $this->assertCount(1, $values);
    $this->assertEquals(
      array(
        'justify' => 'Justify',
        'left' => 'Left',
        'right' => 'Right'
      ),
      $values[0]->fieldParameters
    );
    $this->assertEquals('Text Alignment', $values[0]->hint);
  }

  /**
   * @covers PapayaContentStructureValues::load
   */
  public function testLoadValueWithSinpleParameterAsAttribute() {
    $group = $this
      ->getMockBuilder('PapayaContentStructureGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $dom = new PapayaXmlDocument();
    $dom->load(__DIR__.'/../TestData/structure.xml');
    $values = new PapayaContentStructureValues($group);
    $values->load($dom->xpath()->evaluate('//page[1]/group[3]')->item(0));
    $this->assertCount(2, $values);
    $this->assertEquals('200', $values[0]->fieldParameters);
  }

  /**
   * @covers PapayaContentStructureValues::load
   */
  public function testLoadValueWithParametersList() {
    $group = $this
      ->getMockBuilder('PapayaContentStructureGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $dom = new PapayaXmlDocument();
    $dom->load(__DIR__.'/../TestData/structure.xml');
    $values = new PapayaContentStructureValues($group);
    $values->load($dom->xpath()->evaluate('//page[1]/group[3]')->item(0));
    $this->assertCount(2, $values);
    $this->assertEquals(
      array(
        'foo.png' => 'foo.png',
        'bar.png' => 'bar.png'
      ),
      $values[1]->fieldParameters
    );
  }
}
