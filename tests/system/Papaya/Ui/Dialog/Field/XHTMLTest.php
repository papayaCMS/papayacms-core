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

namespace Papaya\UI\Dialog\Field;
require_once __DIR__.'/../../../../../bootstrap.php';

class XHTMLTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Dialog\Field\XHTML::__construct
   */
  public function testConstructor() {
    $xhtml = new XHTML('<strong>Test</strong>');
    $this->assertEquals(
    /** @lang XML */
      '<xhtml><strong>Test</strong></xhtml>',
      $xhtml->content()->saveXML()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\XHTML::content
   */
  public function testContentGetAfterSet() {
    $document = new \Papaya\XML\Document();
    $content = $document->appendElement('html');
    $xhtml = new XHTML();
    $this->assertSame($content, $xhtml->content($content));
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\XHTML::content
   */
  public function testContentGetImplicitCreate() {
    $xhtml = new XHTML();
    $this->assertInstanceOf(\Papaya\XML\Element::class, $xhtml->content('<strong>Test</strong>'));
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\XHTML::content
   */
  public function testContentGetExpectingInvalidArgumentException() {
    $xhtml = new XHTML();
    $this->expectException(\InvalidArgumentException::class);
    $xhtml->content(new \stdClass());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\XHTML::appendTo
   */
  public function testAppendTo() {
    $xhtml = new XHTML('<strong>Test</strong>');
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<field class="DialogFieldXHTML" error="no">
        <xhtml><strong>Test</strong></xhtml>
      </field>',
      $xhtml->getXML()
    );
  }

}
