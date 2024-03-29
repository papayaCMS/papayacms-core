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

namespace Papaya\UI\Toolbar;
require_once __DIR__.'/../../../../bootstrap.php';

class ComposedTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\UI\Toolbar\Composed::__construct
   * @covers \Papaya\UI\Toolbar\Composed::setNames
   */
  public function testConstructor() {
    $composed = new Composed(
      array('first', 'second')
    );
    $this->assertTrue(isset($composed->first));
    $this->assertTrue(isset($composed->second));
  }

  /**
   * @covers \Papaya\UI\Toolbar\Composed::__construct
   * @covers \Papaya\UI\Toolbar\Composed::setNames
   */
  public function testConstructorWithEmptySetList() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('No sets defined');
    new Composed(array());
  }

  /**
   * @covers \Papaya\UI\Toolbar\Composed::__construct
   * @covers \Papaya\UI\Toolbar\Composed::setNames
   */
  public function testConstructorWithInvalidSetName() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid set name "" in index "0".');
    new Composed(array(''));
  }

  /**
   * @covers \Papaya\UI\Toolbar\Composed::appendTo
   */
  public function testAppendTo() {
    $set = $this->createMock(Collection::class);
    $elements = $this->createMock(Elements::class);
    $elements
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf(Collection::class));
    $toolbar = $this->createMock(\Papaya\UI\Toolbar::class);
    $toolbar
      ->expects($this->any())
      ->method('__get')
      ->with('elements')
      ->will($this->returnValue($elements));
    $toolbar
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    $composed = new Composed(array('first', 'second'));
    $composed->toolbar($toolbar);
    /** @noinspection PhpUndefinedFieldInspection */
    $composed->first = $set;
    $composed->getXML();
  }

  /**
   * @covers \Papaya\UI\Toolbar\Composed::toolbar
   */
  public function testToolbarGetAfterSet() {
    $toolbar = $this->createMock(\Papaya\UI\Toolbar::class);
    $composed = new Composed(array('first', 'second'));
    $composed->toolbar($toolbar);
    $this->assertSame($toolbar, $composed->toolbar());
  }

  /**
   * @covers \Papaya\UI\Toolbar\Composed::toolbar
   */
  public function testToolbarGetImplicitCreate() {
    $composed = new Composed(array('first', 'second'));
    $this->assertInstanceOf(\Papaya\UI\Toolbar::class, $toolbar = $composed->toolbar());
  }

  /**
   * @covers \Papaya\UI\Toolbar\Composed::__isset
   */
  public function testIssetExpectingTrue() {
    $composed = new Composed(array('someSet'));
    $this->assertTrue(isset($composed->someSet));
  }

  /**
   * @covers \Papaya\UI\Toolbar\Composed::__isset
   */
  public function testIssetExpectingFalse() {
    $composed = new Composed(array('someSet'));
    $this->assertFalse(isset($composed->unknownSet));
  }

  /**
   * @covers \Papaya\UI\Toolbar\Composed::__set
   * @covers \Papaya\UI\Toolbar\Composed::__get
   */
  public function testGetAfterSet() {
    $set = $this->createMock(Collection::class);
    $composed = new Composed(array('someSet'));
    /** @noinspection PhpUndefinedFieldInspection */
    $composed->someSet = $set;
    /** @noinspection PhpUndefinedFieldInspection */
    $this->assertSame($set, $composed->someSet);
  }

  /**
   * @covers \Papaya\UI\Toolbar\Composed::__get
   */
  public function testGetImplicitCreate() {
    $composed = new Composed(array('someSet'));
    /** @noinspection PhpUndefinedFieldInspection */
    $this->assertInstanceOf(Collection::class, $set = $composed->someSet);
  }

  /**
   * @covers \Papaya\UI\Toolbar\Composed::__get
   */
  public function testGetWithUndefinedNameExpectingException() {
    $composed = new Composed(array('someSet'));
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('Invalid toolbar set requested.');
    /** @noinspection PhpUndefinedFieldInspection */
    $composed->unknownSet;
  }

  /**
   * @covers \Papaya\UI\Toolbar\Composed::__set
   */
  public function testSetWithUndefinedNameExpectingException() {
    $composed = new Composed(array('someSet'));
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('Invalid toolbar set requested.');
    /** @noinspection PhpUndefinedFieldInspection */
    $composed->unknownSet = $this->createMock(Collection::class);
  }
}
