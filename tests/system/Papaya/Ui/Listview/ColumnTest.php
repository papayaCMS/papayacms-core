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

class PapayaUiListviewColumnTest extends \PapayaTestCase {

  /**
  * @covers \PapayaUiListviewColumn::__construct
  */
  public function testConstructor() {
    $column = new \PapayaUiListviewColumn('test title');
    $this->assertAttributeEquals(
      'test title', '_caption', $column
    );
  }

  /**
  * @covers \PapayaUiListviewColumn::__construct
  * @covers \PapayaUiListviewColumn::setAlign
  */
  public function testConstructorWithAllParameters() {
    $column = new \PapayaUiListviewColumn(
      'test title', \PapayaUiOptionAlign::CENTER
    );
    $this->assertAttributeEquals(
      \PapayaUiOptionAlign::CENTER, '_align', $column
    );
  }

  /**
  * @covers \PapayaUiListviewColumn::getAlign
  * @covers \PapayaUiListviewColumn::setAlign
  */
  public function testGetAlignAfterSetAlign() {
    $column = new \PapayaUiListviewColumn('test title');
    $column->setAlign(\PapayaUiOptionAlign::RIGHT);
    $this->assertEquals(
      \PapayaUiOptionAlign::RIGHT, $column->getAlign()
    );
  }

  /**
  * @covers \PapayaUiListviewColumn::appendTo
  */
  public function testAppendTo() {
    $document = new \Papaya\Xml\Document();
    $document->appendChild($document->createElement('sample'));
    $column = new \PapayaUiListviewColumn('test title');
    $column->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample><col align="left">test title</col></sample>',
      $document->saveXML($document->documentElement)
    );
  }

}
