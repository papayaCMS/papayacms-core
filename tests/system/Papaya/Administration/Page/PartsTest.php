<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaAdministrationPagePartsTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationPageParts::__construct
   */
  public function testConstructor() {
    $parts = new PapayaAdministrationPageParts(
      $page = $this->getPageFixture()
    );
    $this->assertAttributeEquals(
      $page, '_page', $parts
    );
  }

  /**
   * @covers PapayaAdministrationPageParts::__get
   * @covers PapayaAdministrationPageParts::__set
   */
  public function testOffsetGetAfterOffsetSet() {
    $parts = new PapayaAdministrationPageParts(
      $page = $this->getPageFixture()
    );
    $parts->content = $part = $this->getMock('PapayaAdministrationPagePart');
    $this->assertSame($part, $parts->content);
  }

  /**
   * @covers PapayaAdministrationPageParts::get
   * @covers PapayaAdministrationPageParts::set
   */
  public function testGetAfterSet() {
    $parts = new PapayaAdministrationPageParts(
      $page = $this->getPageFixture()
    );
    $parts->set('content', $part = $this->getMock('PapayaAdministrationPagePart'));
    $this->assertSame($part, $parts->get('content'));
  }

  /**
   * @covers PapayaAdministrationPageParts::get
   * @covers PapayaAdministrationPageParts::create
   */
  public function testGetImplicitCreate() {
    $page = $this->getPageFixture();
    $page
      ->expects($this->once())
      ->method('createPart')
      ->with('content')
      ->will($this->returnValue($this->getMock('PapayaAdministrationPagePart')));
    $parts = new PapayaAdministrationPageParts($page);
    $this->assertInstanceOf('PapayaAdministrationPagePart', $parts->get('content'));
  }

  /**
   * @covers PapayaAdministrationPageParts::get
   * @covers PapayaAdministrationPageParts::create
   */
  public function testGetCreateReturnsFalse() {
    $page = $this->getPageFixture();
    $page
      ->expects($this->once())
      ->method('createPart')
      ->with('content')
      ->will($this->returnValue(FALSE));
    $parts = new PapayaAdministrationPageParts($page);
    $this->assertFalse($parts->get('content'));
  }

  /**
   * @covers PapayaAdministrationPageParts::get
   * @covers PapayaAdministrationPageParts::create
   */
  public function testGetWithInvalidNameExpectingException() {
    $parts = new PapayaAdministrationPageParts($this->getPageFixture());
    $this->setExpectedException('UnexpectedValueException');
    $parts->get('INVALID');
  }

  /**
   * @covers PapayaAdministrationPageParts::set
   */
  public function testSetWithInvalidNameExpectingException() {
    $parts = new PapayaAdministrationPageParts($this->getPageFixture());
    $this->setExpectedException('UnexpectedValueException');
    $parts->set('INVALID', $this->getMock('PapayaAdministrationPagePart'));
  }

  /**
   * @covers PapayaAdministrationPageParts::getTarget
   * @dataProvider providePartsAndTargets
   */
  public function testGetTarget($expected, $partName) {
    $parts = new PapayaAdministrationPageParts($this->getPageFixture());
    $this->assertEquals($expected, $parts->getTarget($partName));
  }

  public static function providePartsAndTargets() {
    return array(
      array('leftcol', 'navigation'),
      array('centercol', 'content'),
      array('rightcol', 'information')
    );
  }

  /**
   * @covers PapayaAdministrationPageParts::getTarget
   */
  public function testGetTargetWithInvalidNameExpectingException() {
    $parts = new PapayaAdministrationPageParts($this->getPageFixture());
    $this->setExpectedException('UnexpectedValueException');
    $parts->getTarget('INVALID');
  }

  /**
   * @covers PapayaAdministrationPageParts::toolbar
   */
  public function testToolbarGetAfterSet() {
    $parts = new PapayaAdministrationPageParts($this->getPageFixture());
    $parts->toolbar(
      $toolbar = $this
        ->getMockBuilder('PapayaUiToolbarComposed')
        ->disableOriginalConstructor()
        ->getMock()
    );
    $this->assertSame($toolbar, $parts->toolbar());
  }

  /**
   * @covers PapayaAdministrationPageParts::toolbar
   */
  public function testToolbarGetImplicitCreate() {
    $parts = new PapayaAdministrationPageParts($this->getPageFixture());
    $this->assertInstanceOf('PapayaUiToolbarComposed', $parts->toolbar());
  }

  /**
   * @covers PapayaAdministrationPageParts::rewind
   * @covers PapayaAdministrationPageParts::next
   * @covers PapayaAdministrationPageParts::current
   * @covers PapayaAdministrationPageParts::key
   * @covers PapayaAdministrationPageParts::valid
   */
  public function testIteration() {
    $parts = new PapayaAdministrationPageParts($this->getPageFixture());
    $parts->papaya($this->mockPapaya()->application());
    $parts->content = $partOne = $this->getPartFixture();
    $parts->navigation = $partTwo = $this->getPartFixture();
    $this->assertEquals(
      array(
        'content' => $partOne,
        'navigation' => $partTwo,
        'information' => FALSE
      ),
      iterator_to_array($parts)
    );
  }

  private function getPageFixture() {
    return $this
      ->getMockBuilder('PapayaAdministrationPage')
      ->disableOriginalConstructor()
      ->getMock();
  }

  private function getPartFixture() {
    $part = $this->getMock('PapayaAdministrationPagePart');
    $part
      ->expects($this->at(0))
      ->method('parameters')
      ->with($this->isInstanceOf('PapayaRequestParameters'));
    $part
      ->expects($this->at(1))
      ->method('parameters')
      ->will($this->returnValue($this->getMock('PapayaRequestParameters')));
    return $part;
  }
}