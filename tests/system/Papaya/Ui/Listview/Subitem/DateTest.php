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

class PapayaUiListviewSubitemDateTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\UI\Listview\Subitem\Date::__construct
  */
  public function testConstructor() {
    $now = time();
    $subitem = new \Papaya\UI\Listview\Subitem\Date($now);
    $this->assertEquals(
      $now, $subitem->timestamp
    );
  }

  /**
  * @covers \Papaya\UI\Listview\Subitem\Date::appendTo
  */
  public function testAppendTo() {
    $document = new \Papaya\Xml\Document();
    $document->appendElement('test');
    $subitem = new \Papaya\UI\Listview\Subitem\Date(strtotime('2011-05-18 12:13:45'));
    $subitem->align = \Papaya\UI\Option\Align::CENTER;
    $subitem->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<test><subitem align="center">2011-05-18 12:13</subitem></test>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \Papaya\UI\Listview\Subitem\Date::appendTo
  */
  public function testAppendToDateOnly() {
    $document = new \Papaya\Xml\Document();
    $document->appendElement('test');
    $subitem = new \Papaya\UI\Listview\Subitem\Date(
      strtotime('2011-05-18 12:13:45'),
      \Papaya\UI\Listview\Subitem\Date::SHOW_DATE
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
  * @covers \Papaya\UI\Listview\Subitem\Date::appendTo
  */
  public function testAppendToWithSeconds() {
    $document = new \Papaya\Xml\Document();
    $document->appendElement('test');
    $subitem = new \Papaya\UI\Listview\Subitem\Date(
      strtotime('2011-05-18 12:13:45'),
      \Papaya\UI\Listview\Subitem\Date::SHOW_TIME | \Papaya\UI\Listview\Subitem\Date::SHOW_SECONDS
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
  * @covers \Papaya\UI\Listview\Subitem\Date::appendTo
  */
  public function testAppendToHidesZero() {
    $document = new \Papaya\Xml\Document();
    $document->appendElement('test');
    $subitem = new \Papaya\UI\Listview\Subitem\Date(0);
    $subitem->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<test><subitem align="left"/></test>',
      $document->saveXML($document->documentElement)
    );
  }

}
