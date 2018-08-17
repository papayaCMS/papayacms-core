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

namespace Papaya\UI\Listview {

  require_once __DIR__.'/../../../../bootstrap.php';

  class SubitemTest extends \PapayaTestCase {

    /**
     * @covers \Papaya\UI\Listview\Subitem::getAlign
     * @covers \Papaya\UI\Listview\Subitem::setAlign
     */
    public function testGetAlignAfterSetAlign() {
      $subitem = new Subitem_TestProxy();
      $subitem->setAlign(\Papaya\UI\Option\Align::RIGHT);
      $this->assertEquals(
        \Papaya\UI\Option\Align::RIGHT, $subitem->getAlign()
      );
    }

    /**
     * @covers \Papaya\UI\Listview\Subitem::getAlign
     */
    public function testGetAlignFetchFromColumn() {
      $column = $this
        ->getMockBuilder(Column::class)
        ->setConstructorArgs(array(''))
        ->getMock();
      $column
        ->expects($this->once())
        ->method('getAlign')
        ->will($this->returnValue(\Papaya\UI\Option\Align::CENTER));
      $listview = $this->createMock(\Papaya\UI\Listview::class);
      $columns = $this
        ->getMockBuilder(Columns::class)
        ->setConstructorArgs(array($listview))
        ->getMock();
      $columns
        ->expects($this->once())
        ->method('has')
        ->with($this->equalTo(1))
        ->will($this->returnValue(TRUE));
      $columns
        ->expects($this->once())
        ->method('get')
        ->with($this->equalTo(1))
        ->will($this->returnValue($column));
      $listview
        ->expects($this->atLeastOnce())
        ->method('columns')
        ->will($this->returnValue($columns));
      $subitems = $this
        ->getMockBuilder(Subitems::class)
        ->setConstructorArgs(
          array(
            $this
              ->getMockBuilder(Item::class)
              ->setConstructorArgs(array('', ''))
              ->getMock()
          )
        )
        ->getMock();
      $subitems
        ->expects($this->atLeastOnce())
        ->method('getListview')
        ->will($this->returnValue($listview));
      $subitem = new Subitem_TestProxy();
      $subitem->collection($subitems);
      $this->assertEquals(
        \Papaya\UI\Option\Align::CENTER, $subitem->getAlign()
      );
    }

    /**
     * @covers \Papaya\UI\Listview\Subitem::getAlign
     */
    public function testGetAlignUseDefaultValue() {
      $listview = $this->createMock(\Papaya\UI\Listview::class);
      $columns = $this
        ->getMockBuilder(Columns::class)
        ->setConstructorArgs(array($listview))
        ->getMock();
      $columns
        ->expects($this->once())
        ->method('has')
        ->with($this->equalTo(1))
        ->will($this->returnValue(FALSE));
      $listview
        ->expects($this->atLeastOnce())
        ->method('columns')
        ->will($this->returnValue($columns));
      $subitems = $this
        ->getMockBuilder(Subitems::class)
        ->setConstructorArgs(
          array(
            $this
              ->getMockBuilder(Item::class)
              ->setConstructorArgs(array('', ''))
              ->getMock()
          )
        )
        ->getMock();
      $subitems
        ->expects($this->atLeastOnce())
        ->method('getListview')
        ->will($this->returnValue($listview));
      $subitem = new Subitem_TestProxy();
      $subitem->collection($subitems);
      $this->assertEquals(
        \Papaya\UI\Option\Align::LEFT, $subitem->getAlign()
      );
    }

    /**
     * @covers \Papaya\UI\Listview\Subitem::setActionParameters
     */
    public function testSetActionParameters() {
      $subitem = new Subitem_TestProxy();
      $subitem->setActionParameters(array('foo'));
      $this->assertAttributeEquals(
        array('foo'), '_actionParameters', $subitem
      );
    }
  }

  class Subitem_TestProxy extends Subitem {

    public function appendTo(\Papaya\XML\Element $parent) {
    }
  }
}
