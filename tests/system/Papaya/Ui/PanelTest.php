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

  require_once __DIR__.'/../../../bootstrap.php';

  class PanelTest extends \PapayaTestCase {

    /**
     * @covers \Papaya\UI\Panel::appendTo
     */
    public function testAppendTo() {
      $document = new \Papaya\XML\Document();
      $document->appendElement('sample');
      $panel = new Panel_TestProxy();
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<panel/>',
        $panel->getXML()
      );
    }

    /**
     * @covers \Papaya\UI\Panel::appendTo
     * @covers \Papaya\UI\Panel::setCaption
     */
    public function testAppendToWithCaption() {
      $document = new \Papaya\XML\Document();
      $document->appendElement('sample');
      $panel = new Panel_TestProxy();
      $panel->setCaption('sample caption');
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<panel title="sample caption"/>',
        $panel->getXML()
      );
    }

    /**
     * @covers \Papaya\UI\Panel::toolbars
     */
    public function testToolbarsGetAfterSet() {
      $panel = new Panel_TestProxy();
      $toolbars = $this->createMock(Toolbars::class);
      $this->assertSame($toolbars, $panel->toolbars($toolbars));
    }

    /**
     * @covers \Papaya\UI\Panel::toolbars
     */
    public function testToolbarsGetImplicitCreate() {
      $panel = new Panel_TestProxy();
      $toolbars = $panel->toolbars();
      $this->assertInstanceOf(Toolbars::class, $toolbars);
    }
  }

  class Panel_TestProxy extends Panel {

  }
}
