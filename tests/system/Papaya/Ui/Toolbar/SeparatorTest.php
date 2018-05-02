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

class PapayaUiToolbarSeparatorTest extends PapayaTestCase {

  /**
  * @covers PapayaUiToolbarSeparator::appendTo
  */
  public function testAppendTo() {
    $document = new PapayaXmlDocument();
    $document->appendElement('sample');
    $collection = $this->createMock(PapayaUiControlCollection::class);
    $collection
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(44));
    $collection
      ->expects($this->once())
      ->method('get')
      ->with(41)
      ->will($this->returnValue($this->createMock(PapayaUiToolbarButton::class)));
    $separator = new PapayaUiToolbarSeparator_TestProxy();
    $separator->collection($collection);
    $separator->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      '<sample><separator/></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers PapayaUiToolbarSeparator::appendTo
  */
  public function testAppendToSeparatorNotDisplayed() {
    $document = new PapayaXmlDocument();
    $document->appendElement('sample');
    $separator = new PapayaUiToolbarSeparator();
    $separator->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString('<sample/>', $document->saveXML($document->documentElement));
  }

  /**
  * @covers PapayaUiToolbarSeparator::isDisplayed
  */
  public function testIsDisplayedExpectingTrue() {
    $collection = $this->createMock(PapayaUiControlCollection::class);
    $collection
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(44));
    $collection
      ->expects($this->once())
      ->method('get')
      ->with(41)
      ->will($this->returnValue($this->createMock(PapayaUiToolbarButton::class)));
    $separator = new PapayaUiToolbarSeparator_TestProxy();
    $separator->collection($collection);
    $this->assertTrue($separator->isDisplayed());
  }

  /**
  * @covers PapayaUiToolbarSeparator::isDisplayed
  */
  public function testIsDisplayedWhileFirstElementExpectingFalse() {
    $separator = new PapayaUiToolbarSeparator();
    $this->assertFalse($separator->isDisplayed());
  }

  /**
  * @covers PapayaUiToolbarSeparator::isDisplayed
  */
  public function testIsDisplayedWhileLastElementExpectingFalse() {
    $collection = $this->createMock(PapayaUiControlCollection::class);
    $collection
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(43));
    $separator = new PapayaUiToolbarSeparator_TestProxy();
    $separator->collection($collection);
    $this->assertFalse($separator->isDisplayed());
  }

  /**
  * @covers PapayaUiToolbarSeparator::isDisplayed
  */
  public function testIsDisplayedPreviousElementIsSeparatorExpectingFalse() {
    $collection = $this->createMock(PapayaUiControlCollection::class);
    $collection
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(44));
    $collection
      ->expects($this->once())
      ->method('get')
      ->with(41)
      ->will($this->returnValue($this->createMock(PapayaUiToolbarSeparator::class)));
    $separator = new PapayaUiToolbarSeparator_TestProxy();
    $separator->collection($collection);
    $this->assertFalse($separator->isDisplayed());
  }
}

class PapayaUiToolbarSeparator_TestProxy extends PapayaUiToolbarSeparator {
  public function index($index = NULL) {
    return 42;
  }
}
