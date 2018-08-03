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

class PapayaUiPanelTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Panel::appendTo
  */
  public function testAppendTo() {
    $document = new \Papaya\Xml\Document();
    $document->appendElement('sample');
    $panel = new \PapayaUiPanel_TestProxy();
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<panel/>',
      $panel->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Panel::appendTo
  * @covers \Papaya\Ui\Panel::setCaption
  */
  public function testAppendToWithCaption() {
    $document = new \Papaya\Xml\Document();
    $document->appendElement('sample');
    $panel = new \PapayaUiPanel_TestProxy();
    $panel->setCaption('sample caption');
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<panel title="sample caption"/>',
      $panel->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Panel::toolbars
  */
  public function testToolbarsGetAfterSet() {
    $panel = new \PapayaUiPanel_TestProxy();
    $toolbars = $this->createMock(\Papaya\Ui\Toolbars::class);
    $this->assertSame($toolbars, $panel->toolbars($toolbars));
  }

  /**
  * @covers \Papaya\Ui\Panel::toolbars
  */
  public function testToolbarsGetImplicitCreate() {
    $panel = new \PapayaUiPanel_TestProxy();
    $toolbars = $panel->toolbars();
    $this->assertInstanceOf(\Papaya\Ui\Toolbars::class, $toolbars);
  }
}

class PapayaUiPanel_TestProxy extends \Papaya\Ui\Panel {

}
