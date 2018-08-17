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

namespace Papaya\UI;
require_once __DIR__.'/../../../bootstrap.php';

class SheetTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Panel::appendTo
   */
  public function testAppendTo() {
    $sheet = new Sheet();
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sheet><text/></sheet>',
      $sheet->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Panel::appendTo
   */
  public function testAppendToWithTitle() {
    $sheet = new Sheet();
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
      $sheet->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Panel::appendTo
   */
  public function testAppendToWithSubtitle() {
    $sheet = new Sheet();
    $sheet->subtitles()->add(new Sheet\Subtitle('Sample Title'));
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<?xml version="1.0"?>
       <sheet>
         <header>
           <subtitle>Sample Title</subtitle>
         </header>
         <text/>
       </sheet>',
      $sheet->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Panel::appendTo
   */
  public function testAppendToWithContent() {
    $sheet = new Sheet();
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
      $sheet->getXML()
    );
  }
}
