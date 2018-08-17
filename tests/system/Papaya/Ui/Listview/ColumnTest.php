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

namespace Papaya\UI\Listview;
require_once __DIR__.'/../../../../bootstrap.php';

class ColumnTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\UI\Listview\Column::__construct
   */
  public function testConstructor() {
    $column = new Column('test title');
    $this->assertAttributeEquals(
      'test title', '_caption', $column
    );
  }

  /**
   * @covers \Papaya\UI\Listview\Column::__construct
   * @covers \Papaya\UI\Listview\Column::setAlign
   */
  public function testConstructorWithAllParameters() {
    $column = new Column(
      'test title', \Papaya\UI\Option\Align::CENTER
    );
    $this->assertAttributeEquals(
      \Papaya\UI\Option\Align::CENTER, '_align', $column
    );
  }

  /**
   * @covers \Papaya\UI\Listview\Column::getAlign
   * @covers \Papaya\UI\Listview\Column::setAlign
   */
  public function testGetAlignAfterSetAlign() {
    $column = new Column('test title');
    $column->setAlign(\Papaya\UI\Option\Align::RIGHT);
    $this->assertEquals(
      \Papaya\UI\Option\Align::RIGHT, $column->getAlign()
    );
  }

  /**
   * @covers \Papaya\UI\Listview\Column::appendTo
   */
  public function testAppendTo() {
    $document = new \Papaya\XML\Document();
    $document->appendChild($document->createElement('sample'));
    $column = new Column('test title');
    $column->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample><col align="left">test title</col></sample>',
      $document->saveXML($document->documentElement)
    );
  }

}
