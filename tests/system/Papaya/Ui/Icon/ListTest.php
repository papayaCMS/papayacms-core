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

class PapayaUiIconListTest extends PapayaTestCase {

  /**
  * @covers \PapayaUiIconList::offsetExists
  */
  public function testOffsetExists() {
    $list = new \PapayaUiIconList();
    $list['sample'] = new \PapayaUiIcon('sample.png');
    $this->assertTrue(isset($list['sample']));
  }

  /**
  * @covers \PapayaUiIconList::offsetGet
  */
  public function testOffsetGet() {
    $list = new \PapayaUiIconList();
    $list['sample'] = $icon = new \PapayaUiIcon('sample.png');
    $this->assertSame($icon, $list['sample']);
  }

  /**
  * @covers \PapayaUiIconList::offsetSet
  */
  public function testOffsetSet() {
    $list = new \PapayaUiIconList();
    $list['sample'] = $icon = new \PapayaUiIcon('sample.png');
    $this->assertAttributeSame(array('sample' => $icon), '_icons', $list);
  }

  /**
  * @covers \PapayaUiIconList::offsetSet
  */
  public function testOffsetSetWithoutIndexExpectingException() {
    $list = new \PapayaUiIconList();
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: Please provide a valid offset for the icon.');
    $list[] = new \PapayaUiIcon('sample.png');
  }

  /**
  * @covers \PapayaUiIconList::offsetSet
  */
  public function testOffsetSetWithInvalidIconExpectingException() {
    $list = new \PapayaUiIconList();
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: Please provide an instance of PapayaUiIcon.');
    $list['sample'] = 'X';
  }

  /**
  * @covers \PapayaUiIconList::offsetUnset
  */
  public function testOffsetUnset() {
    $list = new \PapayaUiIconList();
    $list['sample'] = new \PapayaUiIcon('sample.png');
    unset($list['sample']);
    $this->assertFalse(isset($list['sample']));
  }

  /**
  * @covers \PapayaUiIconList::count
  */
  public function testCountExpectingZero() {
    $list = new \PapayaUiIconList();
    $this->assertCount(0, $list);
  }

  /**
  * @covers \PapayaUiIconList::count
  */
  public function testCountExpectingTwo() {
    $list = new \PapayaUiIconList();
    $list['one'] = $icon = new \PapayaUiIcon('one.png');
    $list['two'] = $icon = new \PapayaUiIcon('two.png');
    $this->assertCount(2, $list);
  }

  /**
  * @covers \PapayaUiIconList::getIterator
  */
  public function testGetIterator() {
    $list = new \PapayaUiIconList();
    $list['sample'] = $icon = new \PapayaUiIcon('sample.png');
    $this->assertSame(array('sample' => $icon), $list->getIterator()->getArrayCopy());
  }
}
