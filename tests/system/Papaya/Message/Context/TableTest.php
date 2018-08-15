<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaMessageContextTableTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Message\Context\Table::__construct
  */
  public function testConstructor() {
    $context = new \Papaya\Message\Context\Table('Sample');
    $this->assertAttributeEquals(
      'Sample', '_label', $context
    );
  }

  /**
  * @covers \Papaya\Message\Context\Table::getLabel
  */
  public function testGetLabel() {
    $context = new \Papaya\Message\Context\Table('Sample');
    $this->assertEquals(
      'Sample', $context->getLabel()
    );
  }

  /**
  * @covers \Papaya\Message\Context\Table::setColumns
  */
  public function testSetColumns() {
    $context = new \Papaya\Message\Context\Table('');
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
  * @covers \Papaya\Message\Context\Table::setColumns
  */
  public function testSetColumnsWithEmptyArrayExpectingException() {
    $context = new \Papaya\Message\Context\Table('');
    $this->expectException(\InvalidArgumentException::class);
    $context->setColumns(array());
  }

  /**
  * @covers \Papaya\Message\Context\Table::setColumns
  */
  public function testSetColumnsResetRows() {
    $context = new \Papaya\Message\Context\Table('');
    $context->addRow(array('foo' => 'bar'));
    $context->setColumns(array('sample' => 'Sample'));
    $this->assertAttributeEquals(
      array(), '_rows', $context
    );
  }

  /**
  * @covers \Papaya\Message\Context\Table::getColumns
  */
  public function testGetColumns() {
    $context = new \Papaya\Message\Context\Table('');
    $context->setColumns(array('sample' => 'Sample'));
    $this->assertEquals(
      array('sample' => 'Sample'), $context->getColumns()
    );
  }

  /**
  * @covers \Papaya\Message\Context\Table::addRow
  */
  public function testAddRow() {
    $context = new \Papaya\Message\Context\Table('');
    $context->addRow(array('fieldOne' => 'Content One'));
    $this->assertAttributeEquals(
      array(array('fieldOne' => 'Content One')), '_rows', $context
    );
  }

  /**
  * @covers \Papaya\Message\Context\Table::addRow
  */
  public function testAddRowMergingFieldIdentifiers() {
    $context = new \Papaya\Message\Context\Table('');
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
  * @covers \Papaya\Message\Context\Table::getRow
  */
  public function testGetRow() {
    $context = new \Papaya\Message\Context\Table('');
    $context->addRow(array('fieldOne' => 'Content One'));
    $this->assertEquals(
      array('fieldOne' => 'Content One'), $context->getRow(0)
    );
  }

  /**
  * @covers \Papaya\Message\Context\Table::getRow
  */
  public function testGetRowMergedFields() {
    $context = new \Papaya\Message\Context\Table('');
    $context->addRow(array('fieldOne' => 'Content One'));
    $context->addRow(array('fieldOne' => 'Content One', 'fieldTwo' => 'Content Two'));
    $this->assertEquals(
      array('fieldOne' => 'Content One', 'fieldTwo' => NULL), $context->getRow(0)
    );
  }

  /**
  * @covers \Papaya\Message\Context\Table::getRow
  */
  public function testGetRowColumnsDefined() {
    $context = new \Papaya\Message\Context\Table('');
    $context->setColumns(array('fieldOne' => 'Field One'));
    $context->addRow(array('fieldOne' => 'Content One'));
    $context->addRow(array('fieldOne' => 'Content One', 'fieldTwo' => 'Content Two'));
    $this->assertEquals(
      array('fieldOne' => 'Content One'), $context->getRow(1)
    );
  }

  /**
  * @covers \Papaya\Message\Context\Table::getRowCount
  */
  public function testGetRowCountExpectingZero() {
    $context = new \Papaya\Message\Context\Table('');
    $this->assertEquals(
      0, $context->getRowCount()
    );
  }

  /**
  * @covers \Papaya\Message\Context\Table::getRowCount
  */
  public function testGetRowCountExpectingOne() {
    $context = new \Papaya\Message\Context\Table('');
    $context->addRow(array('fieldOne' => 'Content One'));
    $this->assertEquals(
      1, $context->getRowCount()
    );
  }

  /**
  * @covers \Papaya\Message\Context\Table::asString
  */
  public function testAsStringWithoutContent() {
    $context = new \Papaya\Message\Context\Table('');
    $this->assertEquals(
      '', $context->asString()
    );
  }

  /**
  * @covers \Papaya\Message\Context\Table::asString
  */
  public function testAsStringWithColumns() {
    $context = new \Papaya\Message\Context\Table('');
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
  * @covers \Papaya\Message\Context\Table::asString
  */
  public function testAsStringWithoutColumns() {
    $context = new \Papaya\Message\Context\Table('');
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
  * @covers \Papaya\Message\Context\Table::asArray
  */
  public function testAsArrayWithoutContent() {
    $context = new \Papaya\Message\Context\Table('');
    $this->assertEquals(
      array(), $context->asArray()
    );
  }

  /**
  * @covers \Papaya\Message\Context\Table::asArray
  */
  public function testAsArrayWithColumns() {
    $context = new \Papaya\Message\Context\Table('');
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
  * @covers \Papaya\Message\Context\Table::asArray
  */
  public function testAsArrayWithoutColumns() {
    $context = new \Papaya\Message\Context\Table('');
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
  * @covers \Papaya\Message\Context\Table::asXhtml
  */
  public function testAsXhtmlWithoutContent() {
    $context = new \Papaya\Message\Context\Table('');
    $this->assertEquals(
      '', $context->asXhtml()
    );
  }

  /**
  * @covers \Papaya\Message\Context\Table::asXhtml
  */
  public function testAsXhtmlWithColumns() {
    $context = new \Papaya\Message\Context\Table('');
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
  * @covers \Papaya\Message\Context\Table::asXhtml
  */
  public function testAsXhtmlWithoutColumns() {
    $context = new \Papaya\Message\Context\Table('');
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
