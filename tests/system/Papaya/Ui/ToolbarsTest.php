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
  * @covers \PapayaUiToolbars::appendTo
  */
  public function testAppendTo() {
    $document = new \PapayaXmlDocument();
    $document->appendElement('sample');
    $toolbars = new \PapayaUiToolbars();
    $toolbars->topLeft = new \PapayaUiToolbarsToolbar_Mock();
    $toolbars->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample><toolbar position="top left"/></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
   * @covers \PapayaUiToolbars::__set
   * @covers \PapayaUiToolbars::__get
   * @dataProvider provideToolbarPositions
   * @param string $position
   */
  public function testGetAfterSet($position) {
    $toolbars = new \PapayaUiToolbars();
    $toolbars->$position = $toolbar = $this->createMock(\PapayaUiToolbar::class);
    $this->assertSame(
      $toolbar, $toolbars->$position
    );
  }

  /**
  * @covers \PapayaUiToolbars::__get
  */
  public function testGetWithImplicitCreate() {
    $toolbars = new \PapayaUiToolbars();
    $this->assertInstanceOf(\PapayaUiToolbar::class, $toolbar = $toolbars->topLeft);
    $this->assertSame($toolbar, $toolbars->topLeft);
  }

  /**
  * @covers \PapayaUiToolbars::__set
  */
  public function testSetWithInvalidPositionExpectionExcpetion() {
    $toolbars = new \PapayaUiToolbars();
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('UnexpectedValueException: Invalid toolbar position requested.');
    /** @noinspection PhpUndefinedFieldInspection */
    $toolbars->invalidPosition = $this->createMock(\PapayaUiToolbar::class);
  }

  /**
  * @covers \PapayaUiToolbars::__get
  */
  public function testGetWithInvalidPositionExpectionExcpetion() {
    $toolbars = new \PapayaUiToolbars();
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

class PapayaUiToolbarsToolbar_Mock extends \PapayaUiToolbar {
  public function appendTo(PapayaXmlElement $parent) {
    return $parent->appendElement('toolbar');
  }
}
