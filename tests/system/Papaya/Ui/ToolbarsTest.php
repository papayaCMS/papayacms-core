<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaUiToolbarsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiToolbars::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');
    $toolbars = new PapayaUiToolbars();
    $toolbars->topLeft = new PapayaUiToolbarsToolbar_Mock();
    $toolbars->appendTo($dom->documentElement);
    $this->assertEquals(
      '<sample><toolbar position="top left"/></sample>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiToolbars::__set
  * @covers PapayaUiToolbars::__get
  * @dataProvider provideToolbarPositions
  */
  public function testGetAfterSet($position) {
    $toolbars = new PapayaUiToolbars();
    $toolbars->$position = $toolbar = $this->getMock('PapayaUiToolbar');
    $this->assertSame(
      $toolbar, $toolbars->$position
    );
  }

  /**
  * @covers PapayaUiToolbars::__get
  */
  public function testGetWithImplicitCreate() {
    $toolbars = new PapayaUiToolbars();
    $this->assertInstanceOf('PapayaUiToolbar', $toolbar = $toolbars->topLeft);
    $this->assertSame($toolbar, $toolbars->topLeft);
  }

  /**
  * @covers PapayaUiToolbars::__set
  */
  public function testSetWithInvalidPositionExpectionExcpetion() {
    $toolbars = new PapayaUiToolbars();
    $this->setExpectedException(
      'UnexpectedValueException',
      'UnexpectedValueException: Invalid toolbar position requested.'
    );
    /** @noinspection PhpUndefinedFieldInspection */
    $toolbars->invalidPosition = $this->getMock('PapayaUiToolbar');
  }

  /**
  * @covers PapayaUiToolbars::__get
  */
  public function testGetWithInvalidPositionExpectionExcpetion() {
    $toolbars = new PapayaUiToolbars();
    $this->setExpectedException(
      'UnexpectedValueException',
      'UnexpectedValueException: Invalid toolbar position requested.'
    );
    /** @noinspection PhpUndefinedFieldInspection */
    /** @noinspection PhpUnusedLocalVariableInspection */
    $toolbar = $toolbars->invalidPosition;
  }

  public static function provideToolbarPositions() {
    return array(
      array('topLeft'),
      array('topRight'),
      array('bottomLeft'),
      array('bottomRight')
    );
  }
}

class PapayaUiToolbarsToolbar_Mock extends PapayaUiToolbar {
  public function appendTo(PapayaXmlElement $parent) {
    return $parent->appendElement('toolbar');
  }
}