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

class PapayaUiToolbarSetTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Toolbar\Collection::elements
  */
  public function testElementsGetAfterSet() {
    $group = new \Papaya\Ui\Toolbar\Collection();
    $elements = $this
      ->getMockBuilder(\Papaya\Ui\Toolbar\Elements::class)
      ->setConstructorArgs(array($group))
      ->getMock();
    $elements
      ->expects($this->once())
      ->method('owner')
      ->with($this->isInstanceOf(\Papaya\Ui\Toolbar\Collection::class));
    $this->assertSame(
      $elements, $group->elements($elements)
    );
  }

  /**
  * @covers \Papaya\Ui\Toolbar\Collection::elements
  */
  public function testElementsImplicitCreate() {
    $group = new \Papaya\Ui\Toolbar\Collection();
    $this->assertInstanceOf(
      \Papaya\Ui\Toolbar\Elements::class, $group->elements()
    );
    $this->assertSame(
      $group, $group->elements()->owner()
    );
  }

  /**
  * @covers \Papaya\Ui\Toolbar\Collection::appendTo
  */
  public function testAppendTo() {
    $group = new \Papaya\Ui\Toolbar\Collection();
    $elements = $this
      ->getMockBuilder(\Papaya\Ui\Toolbar\Elements::class)
      ->setConstructorArgs(array($group))
      ->getMock();
    $elements
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $group->elements($elements);
    $this->assertEquals(
      '',
      $group->getXml()
    );
  }
}
