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

class PapayaUiToolbarComposedTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Toolbar\Composed::__construct
  * @covers \Papaya\Ui\Toolbar\Composed::setNames
  */
  public function testConstructor() {
    $composed = new \Papaya\Ui\Toolbar\Composed(
      array('first', 'second')
    );
    $this->assertTrue(isset($composed->first));
    $this->assertTrue(isset($composed->second));
  }

  /**
  * @covers \Papaya\Ui\Toolbar\Composed::__construct
  * @covers \Papaya\Ui\Toolbar\Composed::setNames
  */
  public function testConstructorWithEmptySetList() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('No sets defined');
    new \Papaya\Ui\Toolbar\Composed(array());
  }

  /**
  * @covers \Papaya\Ui\Toolbar\Composed::__construct
  * @covers \Papaya\Ui\Toolbar\Composed::setNames
  */
  public function testConstructorWithInvalidSetName() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid set name "" in index "0".');
    new \Papaya\Ui\Toolbar\Composed(array(''));
  }

  /**
  * @covers \Papaya\Ui\Toolbar\Composed::appendTo
  */
  public function testAppendTo() {
    $set = $this->createMock(\Papaya\Ui\Toolbar\Collection::class);
    $elements = $this->createMock(\Papaya\Ui\Toolbar\Elements::class);
    $elements
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf(\Papaya\Ui\Toolbar\Collection::class));
    $toolbar = $this->createMock(\Papaya\Ui\Toolbar::class);
    $toolbar
      ->expects($this->any())
      ->method('__get')
      ->with('elements')
      ->will($this->returnValue($elements));
    $toolbar
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $composed = new \Papaya\Ui\Toolbar\Composed(array('first', 'second'));
    $composed->toolbar($toolbar);
    /** @noinspection PhpUndefinedFieldInspection */
    $composed->first = $set;
    $composed->getXml();
  }

  /**
  * @covers \Papaya\Ui\Toolbar\Composed::toolbar
  */
  public function testToolbarGetAfterSet() {
    $toolbar = $this->createMock(\Papaya\Ui\Toolbar::class);
    $composed = new \Papaya\Ui\Toolbar\Composed(array('first', 'second'));
    $composed->toolbar($toolbar);
    $this->assertSame($toolbar, $composed->toolbar());
  }

  /**
  * @covers \Papaya\Ui\Toolbar\Composed::toolbar
  */
  public function testToolbarGetImplicitCreate() {
    $composed = new \Papaya\Ui\Toolbar\Composed(array('first', 'second'));
    $this->assertInstanceOf(\Papaya\Ui\Toolbar::class, $toolbar = $composed->toolbar());
  }

  /**
  * @covers \Papaya\Ui\Toolbar\Composed::__isset
  */
  public function testIssetExpectingTrue() {
    $composed = new \Papaya\Ui\Toolbar\Composed(array('someSet'));
    $this->assertTrue(isset($composed->someSet));
  }

  /**
  * @covers \Papaya\Ui\Toolbar\Composed::__isset
  */
  public function testIssetExpectingFalse() {
    $composed = new \Papaya\Ui\Toolbar\Composed(array('someSet'));
    $this->assertFalse(isset($composed->unknownSet));
  }

  /**
  * @covers \Papaya\Ui\Toolbar\Composed::__set
  * @covers \Papaya\Ui\Toolbar\Composed::__get
  */
  public function testGetAfterSet() {
    $set = $this->createMock(\Papaya\Ui\Toolbar\Collection::class);
    $composed = new \Papaya\Ui\Toolbar\Composed(array('someSet'));
    /** @noinspection PhpUndefinedFieldInspection */
    $composed->someSet = $set;
    /** @noinspection PhpUndefinedFieldInspection */
    $this->assertSame($set, $composed->someSet);
  }

  /**
  * @covers \Papaya\Ui\Toolbar\Composed::__get
  */
  public function testGetImplicitCreate() {
    $composed = new \Papaya\Ui\Toolbar\Composed(array('someSet'));
    /** @noinspection PhpUndefinedFieldInspection */
    $this->assertInstanceOf(\Papaya\Ui\Toolbar\Collection::class, $set = $composed->someSet);
  }

  /**
  * @covers \Papaya\Ui\Toolbar\Composed::__get
  */
  public function testGetWithUndefinedNameExpectingException() {
    $composed = new \Papaya\Ui\Toolbar\Composed(array('someSet'));
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('Invalid toolbar set requested.');
    /** @noinspection PhpUndefinedFieldInspection */
    $composed->unknownSet;
  }

  /**
  * @covers \Papaya\Ui\Toolbar\Composed::__set
  */
  public function testSetWithUndefinedNameExpectingException() {
    $composed = new \Papaya\Ui\Toolbar\Composed(array('someSet'));
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('Invalid toolbar set requested.');
    /** @noinspection PhpUndefinedFieldInspection */
    $composed->unknownSet = $this->createMock(\Papaya\Ui\Toolbar\Collection::class);
  }
}
