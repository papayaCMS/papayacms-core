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

class PapayaUiDialogButtonTest extends PapayaTestCase {

  /**
  * @covers \PapayaUiDialogButton::__construct
  */
  public function testConstructor() {
    $button = new \PapayaUiDialogButton_TestProxy();
    $this->assertAttributeEquals(
      \PapayaUiDialogButton::ALIGN_RIGHT,
      '_align',
      $button
    );
  }

  /**
  * @covers \PapayaUiDialogButton::__construct
  */
  public function testConstructorWithAlign() {
    $button = new \PapayaUiDialogButton_TestProxy(PapayaUiDialogButton::ALIGN_LEFT);
    $this->assertAttributeEquals(
      \PapayaUiDialogButton::ALIGN_LEFT,
      '_align',
      $button
    );
  }

  /**
  * @covers \PapayaUiDialogButton::setAlign
  */
  public function testSetAlign() {
    $button = new \PapayaUiDialogButton_TestProxy();
    $button->setAlign(PapayaUiDialogButton::ALIGN_LEFT);
    $this->assertAttributeEquals(
      \PapayaUiDialogButton::ALIGN_LEFT,
      '_align',
      $button
    );
  }
}

class PapayaUiDialogButton_TestProxy extends PapayaUiDialogButton {

  public function appendTo(PapayaXmlElement $parent) {
  }
}
