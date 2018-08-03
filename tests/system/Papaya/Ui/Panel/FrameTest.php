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

class PapayaUiPanelFrameTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\UI\Panel\Frame::__construct
  */
  public function testConstructor() {
    $frame = new \Papaya\UI\Panel\Frame('Sample Caption', 'sample_frame');
    $this->assertEquals(
      'Sample Caption', $frame->caption
    );
    $this->assertEquals(
      'sample_frame', $frame->name
    );
  }

  /**
  * @covers \Papaya\UI\Panel\Frame::__construct
  */
  public function testConstructorWithAllParameters() {
    $frame = new \Papaya\UI\Panel\Frame('Sample Caption', 'sample_frame', '100%');
    $this->assertEquals(
      '100%', $frame->height
    );
  }

  /**
  * @covers \Papaya\UI\Panel\Frame::appendTo
  */
  public function testAppendTo() {
    $document = new \Papaya\Xml\Document();
    $document->appendElement('sample');
    $frame = new \Papaya\UI\Panel\Frame('Sample Caption', 'sample_frame');
    $frame->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<panel title="Sample Caption">
        <iframe id="sample_frame" src="http://www.test.tld/test.html" height="400"/>
      </panel>',
      $frame->getXml()
    );
  }

  /**
  * @covers \Papaya\UI\Panel\Frame::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->createMock(\Papaya\UI\Reference::class);
    $frame = new \Papaya\UI\Panel\Frame('Sample Caption', 'sample_frame');
    $this->assertSame(
      $reference, $frame->reference($reference)
    );
  }

  /**
  * @covers \Papaya\UI\Panel\Frame::reference
  */
  public function testReferenceGetImplicitCreate() {
    $frame = new \Papaya\UI\Panel\Frame('Sample Caption', 'sample_frame');
    $this->assertInstanceOf(
      \Papaya\UI\Reference::class, $frame->reference
    );
  }
}
