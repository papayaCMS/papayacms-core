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

namespace Papaya\UI\ListView\SubItem;
require_once __DIR__.'/../../../../../bootstrap.php';

class DateTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\UI\ListView\SubItem\Date::__construct
   */
  public function testConstructor() {
    $now = time();
    $subitem = new Date($now);
    $this->assertEquals(
      $now, $subitem->timestamp
    );
  }

  /**
   * @covers \Papaya\UI\ListView\SubItem\Date::appendTo
   */
  public function testAppendTo() {
    $document = new \Papaya\XML\Document();
    $document->appendElement('test');
    $subitem = new Date(strtotime('2011-05-18 12:13:45'));
    $subitem->align = \Papaya\UI\Option\Align::CENTER;
    $subitem->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<test><subitem align="center">2011-05-18 12:13</subitem></test>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
   * @covers \Papaya\UI\ListView\SubItem\Date::appendTo
   */
  public function testAppendToDateOnly() {
    $document = new \Papaya\XML\Document();
    $document->appendElement('test');
    $subitem = new Date(
      strtotime('2011-05-18 12:13:45'),
      Date::SHOW_DATE
    );
    $subitem->align = \Papaya\UI\Option\Align::CENTER;
    $subitem->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<test><subitem align="center">2011-05-18</subitem></test>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
   * @covers \Papaya\UI\ListView\SubItem\Date::appendTo
   */
  public function testAppendToWithSeconds() {
    $document = new \Papaya\XML\Document();
    $document->appendElement('test');
    $subitem = new Date(
      strtotime('2011-05-18 12:13:45'),
      Date::SHOW_TIME | Date::SHOW_SECONDS
    );
    $subitem->align = \Papaya\UI\Option\Align::CENTER;
    $subitem->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<test><subitem align="center">2011-05-18 12:13:45</subitem></test>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
   * @covers \Papaya\UI\ListView\SubItem\Date::appendTo
   */
  public function testAppendToHidesZero() {
    $document = new \Papaya\XML\Document();
    $document->appendElement('test');
    $subitem = new Date(0);
    $subitem->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<test><subitem align="left"/></test>',
      $document->saveXML($document->documentElement)
    );
  }

}
