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

namespace Papaya\Message\Context;
require_once __DIR__.'/../../../../bootstrap.php';


/**
 * @covers \Papaya\Message\Context\Table
 */
class TableTest extends \Papaya\TestCase {

  public function testGetLabel() {
    $context = new Table('Sample');
    $this->assertEquals(
      'Sample', $context->getLabel()
    );
  }

  public function testSetColumns() {
    $context = new Table('');
    $context->setColumns(array('sample' => 'Sample'));
    $this->assertEquals(
      array('sample' => 'Sample'), $context->getColumns()
    );
    $this->assertEquals(
      array('sample'), $context->getFields()
    );
    $this->assertEquals(
      array(), $context->asArray()
    );
  }

  public function testSetColumnsWithEmptyArrayExpectingException() {
    $context = new Table('');
    $this->expectException(\InvalidArgumentException::class);
    $context->setColumns(array());
  }

  public function testSetColumnsResetRows() {
    $context = new Table('');
    $context->addRow(array('foo' => 'bar'));
    $context->setColumns(array('sample' => 'Sample'));
    $this->assertEquals(
      array(), $context->asArray()
    );
  }

  public function testGetColumns() {
    $context = new Table('');
    $context->setColumns(array('sample' => 'Sample'));
    $this->assertEquals(
      array('sample' => 'Sample'), $context->getColumns()
    );
  }

  public function testAddRow() {
    $context = new Table('');
    $context->addRow(array('fieldOne' => 'Content One'));
    $this->assertEquals(
      array('fieldOne'), $context->getFields()
    );
    $this->assertEquals(
      array('Content One '), $context->asArray()
    );
  }

  public function testAddRowMergingFieldIdentifiers() {
    $context = new Table('');
    $context->addRow(array('fieldOne' => 'Content One'));
    $context->addRow(array('fieldOne' => 'Content One', 'fieldTwo' => 'Content Two'));
    $this->assertEquals(
      array(
        'Content One |  ',
        'Content One | Content Two '
      ),
      $context->asArray()
    );
    $this->assertEquals(
      array('fieldOne', 'fieldTwo'),
      $context->getFields()
    );
  }

  public function testGetRow() {
    $context = new Table('');
    $context->addRow(array('fieldOne' => 'Content One'));
    $this->assertEquals(
      array('fieldOne' => 'Content One'), $context->getRow(0)
    );
  }

  public function testGetRowMergedFields() {
    $context = new Table('');
    $context->addRow(array('fieldOne' => 'Content One'));
    $context->addRow(array('fieldOne' => 'Content One', 'fieldTwo' => 'Content Two'));
    $this->assertEquals(
      array('fieldOne' => 'Content One', 'fieldTwo' => NULL), $context->getRow(0)
    );
  }

  public function testGetRowColumnsDefined() {
    $context = new Table('');
    $context->setColumns(array('fieldOne' => 'Field One'));
    $context->addRow(array('fieldOne' => 'Content One'));
    $context->addRow(array('fieldOne' => 'Content One', 'fieldTwo' => 'Content Two'));
    $this->assertEquals(
      array('fieldOne' => 'Content One'), $context->getRow(1)
    );
  }

  public function testGetRowCountExpectingZero() {
    $context = new Table('');
    $this->assertEquals(
      0, $context->getRowCount()
    );
  }

  public function testGetRowCountExpectingOne() {
    $context = new Table('');
    $context->addRow(array('fieldOne' => 'Content One'));
    $this->assertEquals(
      1, $context->getRowCount()
    );
  }

  public function testAsStringWithoutContent() {
    $context = new Table('');
    $this->assertEquals(
      '', $context->asString()
    );
  }

  public function testAsStringWithColumns() {
    $context = new Table('');
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

  public function testAsStringWithoutColumns() {
    $context = new Table('');
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

  public function testAsArrayWithoutContent() {
    $context = new Table('');
    $this->assertEquals(
      array(), $context->asArray()
    );
  }

  public function testAsArrayWithColumns() {
    $context = new Table('');
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

  public function testAsArrayWithoutColumns() {
    $context = new Table('');
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

  public function testAsXhtmlWithoutContent() {
    $context = new Table('');
    $this->assertEquals(
      '', $context->asXhtml()
    );
  }

  public function testAsXhtmlWithColumns() {
    $context = new Table('');
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

  public function testAsXhtmlWithoutColumns() {
    $context = new Table('');
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
