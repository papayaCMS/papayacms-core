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

class PapayaUiIconTest extends PapayaTestCase {

  /**
  * @covers PapayaUiIcon::__construct
  */
  public function testContructor() {
    $icon = new PapayaUiIcon('sample');
    $this->assertAttributeEquals(
      'sample', '_image', $icon
    );
  }

  /**
  * @covers PapayaUiIcon::__construct
  */
  public function testContructorWithAllArguments() {
    $icon = new PapayaUiIcon('sample', 'caption', 'hint', array('foo' => 'bar'));
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
  * @covers PapayaUiIcon::__toString
  */
  public function testMagicMethodToString() {
    $icon = new PapayaUiIcon('sample');
    $icon->papaya(
      $this->mockPapaya()->application(array('Images' => array('sample' => 'sample.png')))
    );
    $this->assertEquals(
      'sample.png', (string)$icon
    );
  }

  /**
  * @covers PapayaUiIcon::appendTo
  */
  public function testAppendTo() {
    $document = new PapayaXmlDocument();
    $icon = new PapayaUiIcon('sample');
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
  * @covers PapayaUiIcon::appendTo
  */
  public function testAppendToWithHiddenIcon() {
    $document = new PapayaXmlDocument();
    $icon = new PapayaUiIcon('sample');
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
  * @covers PapayaUiIcon::appendTo
  */
  public function testAppendToWithLink() {
    $document = new PapayaXmlDocument();
    $icon = new PapayaUiIcon('sample', 'caption', 'hint', array('foo' => 'bar'));
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
  * @covers PapayaUiIcon::getImageUrl
  */
  public function testGetImageUrl() {
    $icon = new PapayaUiIcon('sample');
    $icon->papaya(
      $this->mockPapaya()->application(array('Images' => array('sample' => 'sample.png')))
    );
    $this->assertEquals(
      'sample.png', $icon->getImageUrl()
    );
  }

  /**
  * @covers PapayaUiIcon::getUrl
  */
  public function testGetUrl() {
    $icon = new PapayaUiIcon('sample');
    $icon->papaya(
      $this->mockPapaya()->application()
    );
    $this->assertNull($icon->getUrl());
  }

  /**
  * @covers PapayaUiIcon::getUrl
  */
  public function testGetUrlWithActionParameters() {
    $icon = new PapayaUiIcon('sample', 'caption', 'hint', array('foo' => 'bar'));
    $icon->papaya(
      $this->mockPapaya()->application()
    );
    $this->assertEquals(
      'http://www.test.tld/test.html?foo=bar', $icon->getUrl()
    );
  }

  /**
  * @covers PapayaUiIcon::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->createMock(PapayaUiReference::class);
    $icon = new PapayaUiIcon('sample');
    $this->assertSame(
      $reference, $icon->reference($reference)
    );
  }

  /**
  * @covers PapayaUiIcon::reference
  */
  public function testReferenceGetImplicitCreate() {
    $icon = new PapayaUiIcon('sample');
    $icon->papaya(
      $this->mockPapaya()->application()
    );
    $this->assertInstanceOf(
      PapayaUiReference::class, $icon->reference()
    );
  }
}
