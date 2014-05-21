<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaUiStringTranslatedListTest extends PapayaTestCase {

  /**
   * @covers PapayaUiStringTranslatedList::__construct
   */
  public function testConstructorWithArray() {
    $list = new PapayaUiStringTranslatedList(array('foo'));
    $this->assertInstanceOf('PapayaIteratorTraversable', $list->getInnerIterator());
  }

  /**
   * @covers PapayaUiStringTranslatedList
   */
  public function testIterationCallsTranslation() {
    /* PapayaPhraseManager will be the new implementation of the phrase translations,
       just mock it for now, so we dont have to handle the constant declarations in the
       current class */
    $phrases = $this->getMock('PapayaPhraseManager', array('getText'));
    $phrases
      ->expects($this->once())
      ->method('getText')
      ->with('foo')
      ->will($this->returnValue('bar'));
    $list = new PapayaUiStringTranslatedList(array('foo'));
    $list->papaya(
      $this->mockPapaya()->application(array('Phrases' => $phrases))
    );
    $this->assertEquals(
      array('bar'),
      iterator_to_array($list)
    );
  }

  /**
  * @covers PapayaUiStringTranslatedList::papaya
  */
  public function testPapayaGetUsingSingleton() {
    $list = new PapayaUiStringTranslatedList(array());
    $this->assertInstanceOf(
      'PapayaApplication', $list->papaya()
    );
  }

  /**
  * @covers PapayaUiStringTranslatedList::papaya
  */
  public function testPapayaGetAfterSet() {
    $list = new PapayaUiStringTranslatedList(array());
    $application = $this->getMock('PapayaApplication');
    $this->assertSame($application, $list->papaya($application));
  }
}

