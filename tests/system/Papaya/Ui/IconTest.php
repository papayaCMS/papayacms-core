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

namespace Papaya\UI;
require_once __DIR__.'/../../../bootstrap.php';

class IconTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Icon::__construct
   */
  public function testConstructor() {
    $icon = new Icon('sample');
    $this->assertAttributeEquals(
      'sample', '_image', $icon
    );
  }

  /**
   * @covers \Papaya\UI\Icon::__construct
   */
  public function testConstructorWithAllArguments() {
    $icon = new Icon('sample', 'caption', 'hint', array('foo' => 'bar'));
    $this->assertAttributeEquals(
      'caption', '_caption', $icon
    );
    $this->assertAttributeEquals(
      'hint', '_hint', $icon
    );
    $this->assertAttributeEquals(
      array('foo' => 'bar'), '_actionParameters', $icon
    );
  }

  /**
   * @covers \Papaya\UI\Icon::__toString
   */
  public function testMagicMethodToString() {
    $icon = new Icon('sample');
    $icon->papaya(
      $this->mockPapaya()->application(array('Images' => array('sample' => 'sample.png')))
    );
    $this->assertEquals(
      'sample.png', (string)$icon
    );
  }

  /**
   * @covers \Papaya\UI\Icon::appendTo
   */
  public function testAppendTo() {
    $document = new \Papaya\XML\Document();
    $icon = new Icon('sample');
    $icon->papaya(
      $this->mockPapaya()->application(array('Images' => array('sample' => 'sample.png')))
    );
    $icon->appendTo($document->appendElement('sample'));
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample><glyph src="sample.png"/></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
   * @covers \Papaya\UI\Icon::appendTo
   */
  public function testAppendToWithHiddenIcon() {
    $document = new \Papaya\XML\Document();
    $icon = new Icon('sample');
    $icon->papaya(
      $this->mockPapaya()->application(array('Images' => array('sample' => 'sample.png')))
    );
    $icon->visible = FALSE;
    $icon->appendTo($document->appendElement('sample'));
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample><glyph src="-"/></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
   * @covers \Papaya\UI\Icon::appendTo
   */
  public function testAppendToWithLink() {
    $document = new \Papaya\XML\Document();
    $icon = new Icon('sample', 'caption', 'hint', array('foo' => 'bar'));
    $icon->papaya(
      $this->mockPapaya()->application(array('Images' => array('sample' => 'sample.png')))
    );
    $icon->appendTo($document->appendElement('sample'));
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample>
        <glyph src="sample.png" caption="caption" hint="hint"
         href="http://www.test.tld/test.html?foo=bar"/>
      </sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
   * @covers \Papaya\UI\Icon::getImageURL
   */
  public function testGetImageUrl() {
    $icon = new Icon('sample');
    $icon->papaya(
      $this->mockPapaya()->application(array('Images' => array('sample' => 'sample.png')))
    );
    $this->assertEquals(
      'sample.png', $icon->getImageURL()
    );
  }

  /**
   * @covers \Papaya\UI\Icon::getURL
   */
  public function testGetUrl() {
    $icon = new Icon('sample');
    $icon->papaya(
      $this->mockPapaya()->application()
    );
    $this->assertNull($icon->getURL());
  }

  /**
   * @covers \Papaya\UI\Icon::getURL
   */
  public function testGetUrlWithActionParameters() {
    $icon = new Icon('sample', 'caption', 'hint', array('foo' => 'bar'));
    $icon->papaya(
      $this->mockPapaya()->application()
    );
    $this->assertEquals(
      'http://www.test.tld/test.html?foo=bar', $icon->getURL()
    );
  }

  /**
   * @covers \Papaya\UI\Icon::reference
   */
  public function testReferenceGetAfterSet() {
    $reference = $this->createMock(Reference::class);
    $icon = new Icon('sample');
    $this->assertSame(
      $reference, $icon->reference($reference)
    );
  }

  /**
   * @covers \Papaya\UI\Icon::reference
   */
  public function testReferenceGetImplicitCreate() {
    $icon = new Icon('sample');
    $icon->papaya(
      $this->mockPapaya()->application()
    );
    $this->assertInstanceOf(
      Reference::class, $icon->reference()
    );
  }
}
