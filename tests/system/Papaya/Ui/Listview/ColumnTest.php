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
  * @covers \Papaya\Ui\Listview\Column::__construct
  */
  public function testConstructor() {
    $column = new \Papaya\Ui\Listview\Column('test title');
    $this->assertAttributeEquals(
      'test title', '_caption', $column
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Column::__construct
  * @covers \Papaya\Ui\Listview\Column::setAlign
  */
  public function testConstructorWithAllParameters() {
    $column = new \Papaya\Ui\Listview\Column(
      'test title', \Papaya\Ui\Option\Align::CENTER
    );
    $this->assertAttributeEquals(
      \Papaya\Ui\Option\Align::CENTER, '_align', $column
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Column::getAlign
  * @covers \Papaya\Ui\Listview\Column::setAlign
  */
  public function testGetAlignAfterSetAlign() {
    $column = new \Papaya\Ui\Listview\Column('test title');
    $column->setAlign(\Papaya\Ui\Option\Align::RIGHT);
    $this->assertEquals(
      \Papaya\Ui\Option\Align::RIGHT, $column->getAlign()
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Column::appendTo
  */
  public function testAppendTo() {
    $document = new \Papaya\Xml\Document();
    $document->appendChild($document->createElement('sample'));
    $column = new \Papaya\Ui\Listview\Column('test title');
    $column->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample><col align="left">test title</col></sample>',
      $document->saveXML($document->documentElement)
    );
  }

}
