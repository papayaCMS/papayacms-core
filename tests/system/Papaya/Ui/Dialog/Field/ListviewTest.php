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

namespace Papaya\UI\Dialog\Field;
require_once __DIR__.'/../../../../../bootstrap.php';

class ListviewTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Dialog\Field\Listview::__construct
   * @covers \Papaya\UI\Dialog\Field\Listview::listview
   */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Listview $listview */
    $listview = $this->createMock(\Papaya\UI\Listview::class);
    $field = new Listview($listview);
    $this->assertSame(
      $listview, $field->listview()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Listview::appendTo
   */
  public function testAppendTo() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Listview $listview */
    $listview = $this->createMock(\Papaya\UI\Listview::class);
    $listview
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    $field = new Listview($listview);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<field class="DialogFieldListview" error="no"/>',
      $field->getXML()
    );
  }
}
