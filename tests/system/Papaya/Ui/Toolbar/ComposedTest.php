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

class PapayaUiToolbarComposedTest extends PapayaTestCase {

  /**
  * @covers \PapayaUiToolbarComposed::__construct
  * @covers \PapayaUiToolbarComposed::setNames
  */
  public function testConstructor() {
    $composed = new \PapayaUiToolbarComposed(
      array('first', 'second')
    );
    $this->assertTrue(isset($composed->first));
    $this->assertTrue(isset($composed->second));
  }

  /**
  * @covers \PapayaUiToolbarComposed::__construct
  * @covers \PapayaUiToolbarComposed::setNames
  */
  public function testConstructorWithEmptySetList() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('No sets defined');
    new \PapayaUiToolbarComposed(array());
  }

  /**
  * @covers \PapayaUiToolbarComposed::__construct
  * @covers \PapayaUiToolbarComposed::setNames
  */
  public function testConstructorWithInvalidSetName() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid set name "" in index "0".');
    new \PapayaUiToolbarComposed(array(''));
  }

  /**
  * @covers \PapayaUiToolbarComposed::appendTo
  */
  public function testAppendTo() {
    $set = $this->createMock(PapayaUiToolbarSet::class);
    $elements = $this->createMock(PapayaUiToolbarElements::class);
    $elements
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf(PapayaUiToolbarSet::class));
    $toolbar = $this->createMock(PapayaUiToolbar::class);
    $toolbar
      ->expects($this->any())
      ->method('__get')
      ->with('elements')
      ->will($this->returnValue($elements));
    $toolbar
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));
    $composed = new \PapayaUiToolbarComposed(array('first', 'second'));
    $composed->toolbar($toolbar);
    /** @noinspection PhpUndefinedFieldInspection */
    $composed->first = $set;
    $composed->getXml();
  }

  /**
  * @covers \PapayaUiToolbarComposed::toolbar
  */
  public function testToolbarGetAfterSet() {
    $toolbar = $this->createMock(PapayaUiToolbar::class);
    $composed = new \PapayaUiToolbarComposed(array('first', 'second'));
    $composed->toolbar($toolbar);
    $this->assertSame($toolbar, $composed->toolbar());
  }

  /**
  * @covers \PapayaUiToolbarComposed::toolbar
  */
  public function testToolbarGetImplicitCreate() {
    $composed = new \PapayaUiToolbarComposed(array('first', 'second'));
    $this->assertInstanceOf(PapayaUiToolbar::class, $toolbar = $composed->toolbar());
  }

  /**
  * @covers \PapayaUiToolbarComposed::__isset
  */
  public function testIssetExpectingTrue() {
    $composed = new \PapayaUiToolbarComposed(array('someSet'));
    $this->assertTrue(isset($composed->someSet));
  }

  /**
  * @covers \PapayaUiToolbarComposed::__isset
  */
  public function testIssetExpectingFalse() {
    $composed = new \PapayaUiToolbarComposed(array('someSet'));
    $this->assertFalse(isset($composed->unknownSet));
  }

  /**
  * @covers \PapayaUiToolbarComposed::__set
  * @covers \PapayaUiToolbarComposed::__get
  */
  public function testGetAfterSet() {
    $set = $this->createMock(PapayaUiToolbarSet::class);
    $composed = new \PapayaUiToolbarComposed(array('someSet'));
    /** @noinspection PhpUndefinedFieldInspection */
    $composed->someSet = $set;
    /** @noinspection PhpUndefinedFieldInspection */
    $this->assertSame($set, $composed->someSet);
  }

  /**
  * @covers \PapayaUiToolbarComposed::__get
  */
  public function testGetImplicitCreate() {
    $composed = new \PapayaUiToolbarComposed(array('someSet'));
    /** @noinspection PhpUndefinedFieldInspection */
    $this->assertInstanceOf(PapayaUiToolbarSet::class, $set = $composed->someSet);
  }

  /**
  * @covers \PapayaUiToolbarComposed::__get
  */
  public function testGetWithUndefinedNameExpectingException() {
    $composed = new \PapayaUiToolbarComposed(array('someSet'));
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('Invalid toolbar set requested.');
    /** @noinspection PhpUndefinedFieldInspection */
    $composed->unknownSet;
  }

  /**
  * @covers \PapayaUiToolbarComposed::__set
  */
  public function testSetWithUndefinedNameExpectingException() {
    $composed = new \PapayaUiToolbarComposed(array('someSet'));
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('Invalid toolbar set requested.');
    /** @noinspection PhpUndefinedFieldInspection */
    $composed->unknownSet = $this->createMock(PapayaUiToolbarSet::class);
  }
}
