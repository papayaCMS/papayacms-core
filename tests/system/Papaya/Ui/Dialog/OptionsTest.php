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

class PapayaUiDialogOptionsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogOptions::appendTo
  * @covers PapayaUiDialogOptions::_valueToString
  */
  public function testAppendTo() {
    $document = new PapayaXmlDocument();
    $document->appendChild($document->createElement('sample'));
    $options = new PapayaUiDialogOptions();
    $options->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<options>
        <option name="USE_CONFIRMATION" value="yes"/>
        <option name="USE_TOKEN" value="yes"/>
        <option name="PROTECT_CHANGES" value="yes"/>
        <option name="CAPTION_STYLE" value="1"/>
        <option name="DIALOG_WIDTH" value="m"/>
        <option name="TOP_BUTTONS" value="no"/>
        <option name="BOTTOM_BUTTONS" value="yes"/>
        </options>',
      $document->saveXML($document->documentElement->firstChild)
    );
  }
}
