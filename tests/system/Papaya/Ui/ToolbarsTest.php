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

class PapayaUiToolbarsTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Toolbars::appendTo
  */
  public function testAppendTo() {
    $document = new \Papaya\Xml\Document();
    $document->appendElement('sample');
    $toolbars = new \Papaya\Ui\Toolbars();
    $toolbars->topLeft = new \PapayaUiToolbarsToolbar_Mock();
    $toolbars->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample><toolbar position="top left"/></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
   * @covers \Papaya\Ui\Toolbars::__set
   * @covers \Papaya\Ui\Toolbars::__get
   * @dataProvider provideToolbarPositions
   * @param string $position
   */
  public function testGetAfterSet($position) {
    $toolbars = new \Papaya\Ui\Toolbars();
    $toolbars->$position = $toolbar = $this->createMock(\Papaya\Ui\Toolbar::class);
    $this->assertSame(
      $toolbar, $toolbars->$position
    );
  }

  /**
  * @covers \Papaya\Ui\Toolbars::__get
  */
  public function testGetWithImplicitCreate() {
    $toolbars = new \Papaya\Ui\Toolbars();
    $this->assertInstanceOf(\Papaya\Ui\Toolbar::class, $toolbar = $toolbars->topLeft);
    $this->assertSame($toolbar, $toolbars->topLeft);
  }

  /**
  * @covers \Papaya\Ui\Toolbars::__set
  */
  public function testSetWithInvalidPositionExpectionExcpetion() {
    $toolbars = new \Papaya\Ui\Toolbars();
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('UnexpectedValueException: Invalid toolbar position requested.');
    /** @noinspection PhpUndefinedFieldInspection */
    $toolbars->invalidPosition = $this->createMock(\Papaya\Ui\Toolbar::class);
  }

  /**
  * @covers \Papaya\Ui\Toolbars::__get
  */
  public function testGetWithInvalidPositionExpectionExcpetion() {
    $toolbars = new \Papaya\Ui\Toolbars();
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('UnexpectedValueException: Invalid toolbar position requested.');
    /** @noinspection PhpUndefinedFieldInspection */
    /** @noinspection PhpUnusedLocalVariableInspection */
    $toolbar = $toolbars->invalidPosition;
  }

  public static function provideToolbarPositions() {
    return array(
      array('topLeft'),
      array('topRight'),
      array('bottomLeft'),
      array('bottomRight')
    );
  }
}

class PapayaUiToolbarsToolbar_Mock extends \Papaya\Ui\Toolbar {
  public function appendTo(\Papaya\Xml\Element $parent) {
    return $parent->appendElement('toolbar');
  }
}
