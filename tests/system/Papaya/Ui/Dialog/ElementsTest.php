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

class PapayaUiDialogElementsTest extends \PapayaTestCase {

  /**
  * @covers \PapayaUiDialogElements::__construct
  */
  public function testConstructorWithOwner() {
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $elements = new \PapayaUiDialogElements_TestProxy($dialog);
    $this->assertSame(
      $dialog, $elements->owner()
    );
  }

  /**
  * @covers \PapayaUiDialogElements::appendTo
  */
  public function testAppendTo() {
    $document = new \PapayaXmlDocument();
    $node = $document->createElement('dummy');
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaUiDialogElement $element */
    $element = $this->createMock(\PapayaUiDialogElement::class);
    $element
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\PapayaXmlElement::class));
    $elements = new \PapayaUiDialogElements_TestProxy();
    $elements->add($element);
    $elements->appendTo($node);
  }

  /**
  * @covers \PapayaUiDialogElements::collect
  */
  public function testCollect() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaUiDialogElement $element */
    $element = $this->createMock(\PapayaUiDialogElement::class);
    $element
      ->expects($this->once())
      ->method('collect');
    $elements = new \PapayaUiDialogElements_TestProxy();
    $elements->add($element);
    $elements->collect();
  }
}

class PapayaUiDialogElements_TestProxy extends \PapayaUiDialogElements {
}
