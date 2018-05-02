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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaUiSheetTest extends PapayaTestCase {

  /**
  * @covers PapayaUiPanel::appendTo
  */
  public function testAppendTo() {
    $sheet = new PapayaUiSheet();
    $this->assertXmlStringEqualsXmlString(
      '<sheet><text/></sheet>',
      $sheet->getXml()
    );
  }

  /**
  * @covers PapayaUiPanel::appendTo
  */
  public function testAppendToWithTitle() {
    $sheet = new PapayaUiSheet();
    $sheet->title('Sample Title');
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<?xml version="1.0"?>
       <sheet>
         <header>
           <title>Sample Title</title>
         </header>
         <text/>
       </sheet>',
      $sheet->getXml()
    );
  }

  /**
  * @covers PapayaUiPanel::appendTo
  */
  public function testAppendToWithSubtitle() {
    $sheet = new PapayaUiSheet();
    $sheet->subtitles()->add(new PapayaUiSheetSubtitle('Sample Title'));
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<?xml version="1.0"?>
       <sheet>
         <header>
           <subtitle>Sample Title</subtitle>
         </header>
         <text/>
       </sheet>',
      $sheet->getXml()
    );
  }

  /**
  * @covers PapayaUiPanel::appendTo
  */
  public function testAppendToWithContent() {
    $sheet = new PapayaUiSheet();
    $sheet
      ->content()
      ->appendElement('div', array('class' => 'simple'), 'Content');
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<?xml version="1.0"?>
       <sheet>
         <text>
           <div class="simple">Content</div>
         </text>
       </sheet>',
      $sheet->getXml()
    );
  }
}
