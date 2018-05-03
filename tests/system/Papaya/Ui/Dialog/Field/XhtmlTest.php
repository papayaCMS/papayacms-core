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

class PapayaUiDialogFieldXhtmlTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldXhtml::__construct
  */
  public function testConstructor() {
    $xhtml = new PapayaUiDialogFieldXhtml('<strong>Test</strong>');
    $this->assertEquals(
      /** @lang XML */'<xhtml><strong>Test</strong></xhtml>',
      $xhtml->content()->saveXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldXhtml::content
  */
  public function testContentGetAfterSet() {
    $document = new PapayaXmlDocument();
    $content = $document->appendElement('html');
    $xhtml = new PapayaUiDialogFieldXhtml();
    $this->assertSame($content, $xhtml->content($content));
  }

  /**
  * @covers PapayaUiDialogFieldXhtml::content
  */
  public function testContentGetImplicitCreate() {
    $xhtml = new PapayaUiDialogFieldXhtml();
    $this->assertInstanceOf(PapayaXmlElement::class, $xhtml->content('<strong>Test</strong>'));
  }

  /**
  * @covers PapayaUiDialogFieldXhtml::content
  */
  public function testContentGetExpectingInvalidArgumentException() {
    $xhtml = new PapayaUiDialogFieldXhtml();
    $this->expectException(InvalidArgumentException::class);
    $xhtml->content(new stdClass());
  }

  /**
  * @covers PapayaUiDialogFieldXhtml::appendTo
  */
  public function testAppendTo() {
    $xhtml = new PapayaUiDialogFieldXhtml('<strong>Test</strong>');
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field class="DialogFieldXhtml" error="no">
        <xhtml><strong>Test</strong></xhtml>
      </field>',
      $xhtml->getXml()
    );
  }

}
