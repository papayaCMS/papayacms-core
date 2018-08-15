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

class PapayaUiDialogFieldXhtmlTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\UI\Dialog\Field\Xhtml::__construct
  */
  public function testConstructor() {
    $xhtml = new \Papaya\UI\Dialog\Field\Xhtml('<strong>Test</strong>');
    $this->assertEquals(
      /** @lang XML */'<xhtml><strong>Test</strong></xhtml>',
      $xhtml->content()->saveXML()
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Xhtml::content
  */
  public function testContentGetAfterSet() {
    $document = new \Papaya\XML\Document();
    $content = $document->appendElement('html');
    $xhtml = new \Papaya\UI\Dialog\Field\Xhtml();
    $this->assertSame($content, $xhtml->content($content));
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Xhtml::content
  */
  public function testContentGetImplicitCreate() {
    $xhtml = new \Papaya\UI\Dialog\Field\Xhtml();
    $this->assertInstanceOf(\Papaya\XML\Element::class, $xhtml->content('<strong>Test</strong>'));
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Xhtml::content
  */
  public function testContentGetExpectingInvalidArgumentException() {
    $xhtml = new \Papaya\UI\Dialog\Field\Xhtml();
    $this->expectException(InvalidArgumentException::class);
    $xhtml->content(new \stdClass());
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Xhtml::appendTo
  */
  public function testAppendTo() {
    $xhtml = new \Papaya\UI\Dialog\Field\Xhtml('<strong>Test</strong>');
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field class="DialogFieldXhtml" error="no">
        <xhtml><strong>Test</strong></xhtml>
      </field>',
      $xhtml->getXML()
    );
  }

}
