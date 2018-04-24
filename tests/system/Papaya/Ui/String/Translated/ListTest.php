<?php
require_once __DIR__.'/../../../../../bootstrap.php';

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
    $phrases = $this
      ->getMockBuilder('PapayaPhrases')
      ->disableOriginalConstructor()
      ->getMock();
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
    $application = $this->createMock(PapayaApplication::class);
    $this->assertSame($application, $list->papaya($application));
  }
}

