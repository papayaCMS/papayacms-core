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

class PapayaUiIconListTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Icon\Collection::offsetExists
  */
  public function testOffsetExists() {
    $list = new \Papaya\Ui\Icon\Collection();
    $list['sample'] = new \Papaya\Ui\Icon('sample.png');
    $this->assertTrue(isset($list['sample']));
  }

  /**
  * @covers \Papaya\Ui\Icon\Collection::offsetGet
  */
  public function testOffsetGet() {
    $list = new \Papaya\Ui\Icon\Collection();
    $list['sample'] = $icon = new \Papaya\Ui\Icon('sample.png');
    $this->assertSame($icon, $list['sample']);
  }

  /**
  * @covers \Papaya\Ui\Icon\Collection::offsetSet
  */
  public function testOffsetSet() {
    $list = new \Papaya\Ui\Icon\Collection();
    $list['sample'] = $icon = new \Papaya\Ui\Icon('sample.png');
    $this->assertAttributeSame(array('sample' => $icon), '_icons', $list);
  }

  /**
  * @covers \Papaya\Ui\Icon\Collection::offsetSet
  */
  public function testOffsetSetWithoutIndexExpectingException() {
    $list = new \Papaya\Ui\Icon\Collection();
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: Please provide a valid offset for the icon.');
    $list[] = new \Papaya\Ui\Icon('sample.png');
  }

  /**
  * @covers \Papaya\Ui\Icon\Collection::offsetSet
  */
  public function testOffsetSetWithInvalidIconExpectingException() {
    $list = new \Papaya\Ui\Icon\Collection();
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: Please provide an instance of Papaya\Ui\PapayaUiIcon.');
    $list['sample'] = 'X';
  }

  /**
  * @covers \Papaya\Ui\Icon\Collection::offsetUnset
  */
  public function testOffsetUnset() {
    $list = new \Papaya\Ui\Icon\Collection();
    $list['sample'] = new \Papaya\Ui\Icon('sample.png');
    unset($list['sample']);
    $this->assertFalse(isset($list['sample']));
  }

  /**
  * @covers \Papaya\Ui\Icon\Collection::count
  */
  public function testCountExpectingZero() {
    $list = new \Papaya\Ui\Icon\Collection();
    $this->assertCount(0, $list);
  }

  /**
  * @covers \Papaya\Ui\Icon\Collection::count
  */
  public function testCountExpectingTwo() {
    $list = new \Papaya\Ui\Icon\Collection();
    $list['one'] = $icon = new \Papaya\Ui\Icon('one.png');
    $list['two'] = $icon = new \Papaya\Ui\Icon('two.png');
    $this->assertCount(2, $list);
  }

  /**
  * @covers \Papaya\Ui\Icon\Collection::getIterator
  */
  public function testGetIterator() {
    $list = new \Papaya\Ui\Icon\Collection();
    $list['sample'] = $icon = new \Papaya\Ui\Icon('sample.png');
    $this->assertSame(array('sample' => $icon), $list->getIterator()->getArrayCopy());
  }
}
