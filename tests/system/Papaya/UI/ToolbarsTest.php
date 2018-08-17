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

namespace Papaya\UI {

  class ToolbarsTest extends \Papaya\TestCase {

    /**
     * @covers \Papaya\UI\Toolbars::appendTo
     */
    public function testAppendTo() {
      $document = new \Papaya\XML\Document();
      $document->appendElement('sample');
      $toolbars = new Toolbars();
      $toolbars->topLeft = new Toolbar_Mock();
      $toolbars->appendTo($document->documentElement);
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<sample><toolbar position="top left"/></sample>',
        $document->saveXML($document->documentElement)
      );
    }

    /**
     * @covers       \Papaya\UI\Toolbars::__set
     * @covers       \Papaya\UI\Toolbars::__get
     * @dataProvider provideToolbarPositions
     * @param string $position
     */
    public function testGetAfterSet($position) {
      $toolbars = new Toolbars();
      $toolbars->$position = $toolbar = $this->createMock(Toolbar::class);
      $this->assertSame(
        $toolbar, $toolbars->$position
      );
    }

    /**
     * @covers \Papaya\UI\Toolbars::__get
     */
    public function testGetWithImplicitCreate() {
      $toolbars = new Toolbars();
      $this->assertInstanceOf(Toolbar::class, $toolbar = $toolbars->topLeft);
      $this->assertSame($toolbar, $toolbars->topLeft);
    }

    /**
     * @covers \Papaya\UI\Toolbars::__set
     */
    public function testSetWithInvalidPositionExpectionExcpetion() {
      $toolbars = new Toolbars();
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('UnexpectedValueException: Invalid toolbar position requested.');
      /** @noinspection PhpUndefinedFieldInspection */
      $toolbars->invalidPosition = $this->createMock(Toolbar::class);
    }

    /**
     * @covers \Papaya\UI\Toolbars::__get
     */
    public function testGetWithInvalidPositionExpectionExcpetion() {
      $toolbars = new Toolbars();
      $this->expectException(\UnexpectedValueException::class);
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

  class Toolbar_Mock extends Toolbar {
    public function appendTo(\Papaya\XML\Element $parent) {
      return $parent->appendElement('toolbar');
    }
  }
}
