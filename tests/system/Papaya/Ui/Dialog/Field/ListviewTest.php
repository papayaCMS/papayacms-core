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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiDialogFieldListviewTest extends \PapayaTestCase {

  /**
  * @covers \PapayaUiDialogFieldListview::__construct
  * @covers \PapayaUiDialogFieldListview::listview
  */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaUiListview $listview */
    $listview = $this->createMock(\PapayaUiListview::class);
    $field = new \PapayaUiDialogFieldListview($listview);
    $this->assertSame(
      $listview, $field->listview()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldListview::appendTo
  */
  public function testAppendTo() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaUiListview $listview */
    $listview = $this->createMock(\PapayaUiListview::class);
    $listview
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\PapayaXmlElement::class));
    $field = new \PapayaUiDialogFieldListview($listview);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field class="DialogFieldListview" error="no"/>',
      $field->getXml()
    );
  }
}
