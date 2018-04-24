<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiToolbarSeparatorTest extends PapayaTestCase {

  /**
  * @covers PapayaUiToolbarSeparator::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');
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
    $separator->appendTo($dom->documentElement);
    $this->assertEquals('<sample><separator/></sample>', $dom->saveXml($dom->documentElement));
  }

  /**
  * @covers PapayaUiToolbarSeparator::appendTo
  */
  public function testAppendToSeparatorNotDisplayed() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');
    $separator = new PapayaUiToolbarSeparator();
    $separator->appendTo($dom->documentElement);
    $this->assertEquals('<sample/>', $dom->saveXml($dom->documentElement));
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
  public function testIsDisplayedPrevisousElementIsSeparatorExpectingFalse() {
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
