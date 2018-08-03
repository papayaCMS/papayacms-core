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

class PapayaUiToolbarSeparatorTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Toolbar\Separator::appendTo
  */
  public function testAppendTo() {
    $document = new \Papaya\Xml\Document();
    $document->appendElement('sample');
    $collection = $this->createMock(\Papaya\Ui\Control\Collection::class);
    $collection
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(44));
    $collection
      ->expects($this->once())
      ->method('get')
      ->with(41)
      ->will($this->returnValue($this->createMock(\Papaya\Ui\Toolbar\Button::class)));
    $separator = new \PapayaUiToolbarSeparator_TestProxy();
    $separator->collection($collection);
    $separator->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */'<sample><separator/></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \Papaya\Ui\Toolbar\Separator::appendTo
  */
  public function testAppendToSeparatorNotDisplayed() {
    $document = new \Papaya\Xml\Document();
    $document->appendElement('sample');
    $separator = new \Papaya\Ui\Toolbar\Separator();
    $separator->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(/** @lang XML */'<sample/>', $document->saveXML($document->documentElement));
  }

  /**
  * @covers \Papaya\Ui\Toolbar\Separator::isDisplayed
  */
  public function testIsDisplayedExpectingTrue() {
    $collection = $this->createMock(\Papaya\Ui\Control\Collection::class);
    $collection
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(44));
    $collection
      ->expects($this->once())
      ->method('get')
      ->with(41)
      ->will($this->returnValue($this->createMock(\Papaya\Ui\Toolbar\Button::class)));
    $separator = new \PapayaUiToolbarSeparator_TestProxy();
    $separator->collection($collection);
    $this->assertTrue($separator->isDisplayed());
  }

  /**
  * @covers \Papaya\Ui\Toolbar\Separator::isDisplayed
  */
  public function testIsDisplayedWhileFirstElementExpectingFalse() {
    $separator = new \Papaya\Ui\Toolbar\Separator();
    $this->assertFalse($separator->isDisplayed());
  }

  /**
  * @covers \Papaya\Ui\Toolbar\Separator::isDisplayed
  */
  public function testIsDisplayedWhileLastElementExpectingFalse() {
    $collection = $this->createMock(\Papaya\Ui\Control\Collection::class);
    $collection
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(43));
    $separator = new \PapayaUiToolbarSeparator_TestProxy();
    $separator->collection($collection);
    $this->assertFalse($separator->isDisplayed());
  }

  /**
  * @covers \Papaya\Ui\Toolbar\Separator::isDisplayed
  */
  public function testIsDisplayedPreviousElementIsSeparatorExpectingFalse() {
    $collection = $this->createMock(\Papaya\Ui\Control\Collection::class);
    $collection
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(44));
    $collection
      ->expects($this->once())
      ->method('get')
      ->with(41)
      ->will($this->returnValue($this->createMock(\Papaya\Ui\Toolbar\Separator::class)));
    $separator = new \PapayaUiToolbarSeparator_TestProxy();
    $separator->collection($collection);
    $this->assertFalse($separator->isDisplayed());
  }
}

class PapayaUiToolbarSeparator_TestProxy extends \Papaya\Ui\Toolbar\Separator {
  public function index($index = NULL) {
    return 42;
  }
}
