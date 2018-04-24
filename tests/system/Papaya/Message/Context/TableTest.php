<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaMessageContextTableTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageContextTable::__construct
  */
  public function testConstructor() {
    $context = new PapayaMessageContextTable('Sample');
    $this->assertAttributeEquals(
      'Sample', '_label', $context
    );
  }

  /**
  * @covers PapayaMessageContextTable::getLabel
  */
  public function testGetLabel() {
    $context = new PapayaMessageContextTable('Sample');
    $this->assertEquals(
      'Sample', $context->getLabel()
    );
  }

  /**
  * @covers PapayaMessageContextTable::setColumns
  */
  public function testSetColumns() {
    $context = new PapayaMessageContextTable('');
    $context->setColumns(array('sample' => 'Sample'));
    $this->assertAttributeEquals(
      array('sample' => 'Sample'), '_captions', $context
    );
    $this->assertAttributeEquals(
      array('sample'), '_fields', $context
    );
    $this->assertAttributeEquals(
      array(), '_rows', $context
    );
  }

  /**
  * @covers PapayaMessageContextTable::setColumns
  */
  public function testSetColumnsWithEmptyArrayExpectingException() {
    $context = new PapayaMessageContextTable('');
    $this->setExpectedException('InvalidArgumentException');
    $context->setColumns(array());
  }

  /**
  * @covers PapayaMessageContextTable::setColumns
  */
  public function testSetColumnsResetRows() {
    $context = new PapayaMessageContextTable('');
    $context->addRow(array('foo' => 'bar'));
    $context->setColumns(array('sample' => 'Sample'));
    $this->assertAttributeEquals(
      array(), '_rows', $context
    );
  }

  /**
  * @covers PapayaMessageContextTable::getColumns
  */
  public function testGetColumns() {
    $context = new PapayaMessageContextTable('');
    $context->setColumns(array('sample' => 'Sample'));
    $this->assertEquals(
      array('sample' => 'Sample'), $context->getColumns()
    );
  }

  /**
  * @covers PapayaMessageContextTable::addRow
  */
  public function testAddRow() {
    $context = new PapayaMessageContextTable('');
    $context->addRow(array('fieldOne' => 'Content One'));
    $this->assertAttributeEquals(
      array(array('fieldOne' => 'Content One')), '_rows', $context
    );
  }

  /**
  * @covers PapayaMessageContextTable::addRow
  */
  public function testAddRowMergingFieldIdentifiers() {
    $context = new PapayaMessageContextTable('');
    $context->addRow(array('fieldOne' => 'Content One'));
    $context->addRow(array('fieldOne' => 'Content One', 'fieldTwo' => 'Content Two'));
    $this->assertAttributeEquals(
      array(
        array('fieldOne' => 'Content One'),
        array('fieldOne' => 'Content One', 'fieldTwo' => 'Content Two')
      ),
      '_rows',
      $context
    );
    $this->assertAttributeEquals(
      array('fieldOne', 'fieldTwo'),
      '_fields',
      $context
    );
  }

  /**
  * @covers PapayaMessageContextTable::getRow
  */
  public function testGetRow() {
    $context = new PapayaMessageContextTable('');
    $context->addRow(array('fieldOne' => 'Content One'));
    $this->assertEquals(
      array('fieldOne' => 'Content One'), $context->getRow(0)
    );
  }

  /**
  * @covers PapayaMessageContextTable::getRow
  */
  public function testGetRowMergedFields() {
    $context = new PapayaMessageContextTable('');
    $context->addRow(array('fieldOne' => 'Content One'));
    $context->addRow(array('fieldOne' => 'Content One', 'fieldTwo' => 'Content Two'));
    $this->assertEquals(
      array('fieldOne' => 'Content One', 'fieldTwo' => NULL), $context->getRow(0)
    );
  }

  /**
  * @covers PapayaMessageContextTable::getRow
  */
  public function testGetRowColumnsDefined() {
    $context = new PapayaMessageContextTable('');
    $context->setColumns(array('fieldOne' => 'Field One'));
    $context->addRow(array('fieldOne' => 'Content One'));
    $context->addRow(array('fieldOne' => 'Content One', 'fieldTwo' => 'Content Two'));
    $this->assertEquals(
      array('fieldOne' => 'Content One'), $context->getRow(1)
    );
  }

  /**
  * @covers PapayaMessageContextTable::getRowCount
  */
  public function testGetRowCountExpectingZero() {
    $context = new PapayaMessageContextTable('');
    $this->assertEquals(
      0, $context->getRowCount()
    );
  }

  /**
  * @covers PapayaMessageContextTable::getRowCount
  */
  public function testGetRowCountExpectingOne() {
    $context = new PapayaMessageContextTable('');
    $context->addRow(array('fieldOne' => 'Content One'));
    $this->assertEquals(
      1, $context->getRowCount()
    );
  }

  /**
  * @covers PapayaMessageContextTable::asString
  */
  public function testAsStringWithoutContent() {
    $context = new PapayaMessageContextTable('');
    $this->assertEquals(
      '', $context->asString()
    );
  }

  /**
  * @covers PapayaMessageContextTable::asString
  */
  public function testAsStringWithColumns() {
    $context = new PapayaMessageContextTable('');
    $context->setColumns(array('fieldOne' => 'Caption One', 'fieldTwo' => 'Caption Two'));
    $context->addRow(array('fieldOne' => '1.1'));
    $context->addRow(array('fieldOne' => '2.1', 'fieldTwo' => '2.2'));
    $this->assertEquals(
      "Caption One: 1.1\n\n".
      "Caption One: 2.1\n".
      "Caption Two: 2.2\n\n",
      $context->asString()
    );
  }

  /**
  * @covers PapayaMessageContextTable::asString
  */
  public function testAsStringWithoutColumns() {
    $context = new PapayaMessageContextTable('');
    $context->addRow(array('fieldOne' => '1.1'));
    $context->addRow(array('fieldOne' => '2.1', 'fieldTwo' => '2.2'));
    $this->assertEquals(
      "- 1.1\n".
      "- \n\n".
      "- 2.1\n".
      "- 2.2\n\n",
      $context->asString()
    );
  }

  /**
  * @covers PapayaMessageContextTable::asArray
  */
  public function testAsArrayWithoutContent() {
    $context = new PapayaMessageContextTable('');
    $this->assertEquals(
      array(), $context->asArray()
    );
  }

  /**
  * @covers PapayaMessageContextTable::asArray
  */
  public function testAsArrayWithColumns() {
    $context = new PapayaMessageContextTable('');
    $context->setColumns(array('fieldOne' => 'Caption One', 'fieldTwo' => 'Caption Two'));
    $context->addRow(array('fieldOne' => '1.1'));
    $context->addRow(array('fieldOne' => '2.1', 'fieldTwo' => '2.2'));
    $this->assertEquals(
      array(
       'Caption One: 1.1',
       'Caption One: 2.1, Caption Two: 2.2'
      ),
      $context->asArray()
    );
  }

  /**
  * @covers PapayaMessageContextTable::asArray
  */
  public function testAsArrayWithoutColumns() {
    $context = new PapayaMessageContextTable('');
    $context->addRow(array('fieldOne' => '1.1'));
    $context->addRow(array('fieldOne' => '2.1', 'fieldTwo' => '2.2'));
    $this->assertEquals(
      array(
       '1.1 |  ',
       '2.1 | 2.2 '
      ),
      $context->asArray()
    );
  }

  /**
  * @covers PapayaMessageContextTable::asXhtml
  */
  public function testAsXhtmlWithoutContent() {
    $context = new PapayaMessageContextTable('');
    $this->assertEquals(
      '', $context->asXhtml()
    );
  }

  /**
  * @covers PapayaMessageContextTable::asXhtml
  */
  public function testAsXhtmlWithColumns() {
    $context = new PapayaMessageContextTable('');
    $context->setColumns(array('fieldOne' => 'Caption One', 'fieldTwo' => 'Caption Two'));
    $context->addRow(array('fieldOne' => '1.1'));
    $context->addRow(array('fieldOne' => '2.1', 'fieldTwo' => '2.2'));
    $this->assertEquals(
      '<table class="logContext" summary="">'.
      '<thead><tr><th>Caption One</th><th>Caption Two</th></tr></thead>'.
      '<tbody><tr><td>1.1</td><td></td></tr><tr><td>2.1</td><td>2.2</td></tr></tbody>'.
      '</table>',
      $context->asXhtml()
    );
  }

  /**
  * @covers PapayaMessageContextTable::asXhtml
  */
  public function testAsXhtmlWithoutColumns() {
    $context = new PapayaMessageContextTable('');
    $context->addRow(array('fieldOne' => '1.1'));
    $context->addRow(array('fieldOne' => '2.1', 'fieldTwo' => '2.2'));
    $this->assertEquals(
      '<table class="logContext" summary="">'.
      '<tbody><tr><td>1.1</td><td></td></tr><tr><td>2.1</td><td>2.2</td></tr></tbody>'.
      '</table>',
      $context->asXhtml()
    );
  }
}
