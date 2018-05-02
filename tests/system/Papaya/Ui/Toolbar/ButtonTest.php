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

class PapayaUiToolbarButtonTest extends PapayaTestCase {

  /**
  * @covers PapayaUiToolbarButton::setAccessKey
  */
  public function testSetAccessKey() {
    $button = new PapayaUiToolbarButton();
    $button->accessKey = '1';
    $this->assertEquals(
      '1', $button->accessKey
    );
  }

  /**
  * @covers PapayaUiToolbarButton::setAccessKey
  */
  public function testSetAccessKeyWithInvalidKeyExpectingException() {
    $button = new PapayaUiToolbarButton();
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: Access key must be an single character.');
    $button->accessKey = 'foo';
  }

  /**
  * @covers PapayaUiToolbarButton::appendTo
  */
  public function testAppendTo() {
    $document = new PapayaXmlDocument;
    $document->appendElement('sample');
    $button = new PapayaUiToolbarButton();
    $button->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'sample.png')))
    );
    $button->caption = 'Test';
    $button->image = 'image';
    $button->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample>
        <button href="http://www.test.tld/test.html" target="_self"
           glyph="sample.png" title="Test"/>
        </sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers PapayaUiToolbarButton::appendTo
  */
  public function testAppendToWithAllProperties() {
    $document = new PapayaXmlDocument;
    $document->appendElement('sample');
    $button = new PapayaUiToolbarButton();
    $button->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'sample.png')))
    );
    $button->image = 'image';
    $button->caption = 'Test Caption';
    $button->hint = 'Test Hint';
    $button->selected = TRUE;
    $button->accessKey = 'T';
    $button->target = '_top';
    $button->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample>
        <button href="http://www.test.tld/test.html" target="_top" glyph="sample.png"
           title="Test Caption" accesskey="T" hint="Test Hint" down="down"/>
        </sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers PapayaUiToolbarButton::appendTo
  */
  public function testAppendToWithoutProperties() {
    $document = new PapayaXmlDocument;
    $document->appendElement('sample');
    $button = new PapayaUiToolbarButton();
    $button->papaya(
      $this->mockPapaya()->application(array('Images' => array('' => '')))
    );
    $button->appendTo($document->documentElement);
    $this->assertEquals(
      '<sample/>',
      $document->saveXML($document->documentElement)
    );
  }
}
