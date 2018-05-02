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

class PapayaUiToolbarSetTest extends PapayaTestCase {

  /**
  * @covers PapayaUiToolbarSet::elements
  */
  public function testElementsGetAfterSet() {
    $group = new PapayaUiToolbarSet();
    $elements = $this
      ->getMockBuilder(PapayaUiToolbarElements::class)
      ->setConstructorArgs(array($group))
      ->getMock();
    $elements
      ->expects($this->once())
      ->method('owner')
      ->with($this->isInstanceOf(PapayaUiToolbarSet::class));
    $this->assertSame(
      $elements, $group->elements($elements)
    );
  }

  /**
  * @covers PapayaUiToolbarSet::elements
  */
  public function testElementsImplicitCreate() {
    $group = new PapayaUiToolbarSet();
    $this->assertInstanceOf(
      PapayaUiToolbarElements::class, $group->elements()
    );
    $this->assertSame(
      $group, $group->elements()->owner()
    );
  }

  /**
  * @covers PapayaUiToolbarSet::appendTo
  */
  public function testAppendTo() {
    $group = new PapayaUiToolbarSet();
    $elements = $this
      ->getMockBuilder(PapayaUiToolbarElements::class)
      ->setConstructorArgs(array($group))
      ->getMock();
    $elements
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXMlElement::class));
    $group->elements($elements);
    $this->assertEquals(
      '',
      $group->getXml()
    );
  }
}
