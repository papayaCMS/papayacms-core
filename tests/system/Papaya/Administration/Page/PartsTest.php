<?php
require_once __DIR__.'/../../../../bootstrap.php';

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
    $parts->content = $part = $this->createMock(PapayaAdministrationPagePart::class);
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
    $parts->set('content', $part = $this->createMock(PapayaAdministrationPagePart::class));
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
      ->will($this->returnValue($this->createMock(PapayaAdministrationPagePart::class)));
    $parts = new PapayaAdministrationPageParts($page);
    $this->assertInstanceOf(PapayaAdministrationPagePart::class, $parts->get('content'));
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
    $this->expectException(\UnexpectedValueException::class);
    $parts->get('INVALID');
  }

  /**
   * @covers PapayaAdministrationPageParts::set
   */
  public function testSetWithInvalidNameExpectingException() {
    $parts = new PapayaAdministrationPageParts($this->getPageFixture());
    $this->expectException(\UnexpectedValueException::class);
    $parts->set('INVALID', $this->createMock(PapayaAdministrationPagePart::class));
  }

  /**
   * @covers PapayaAdministrationPageParts::getTarget
   * @dataProvider providePartsAndTargets
   * @param string $expected
   * @param string $partName
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
    $this->expectException(\UnexpectedValueException::class);
    $parts->getTarget('INVALID');
  }

  /**
   * @covers PapayaAdministrationPageParts::toolbar
   */
  public function testToolbarGetAfterSet() {
    $parts = new PapayaAdministrationPageParts($this->getPageFixture());
    $parts->toolbar(
      $toolbar = $this
        ->getMockBuilder(PapayaUiToolbarComposed::class)
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
    $this->assertInstanceOf(PapayaUiToolbarComposed::class, $parts->toolbar());
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

  /**
   * @return PHPUnit_Framework_MockObject_MockObject|PapayaAdministrationPage
   */
  private function getPageFixture() {
    return $this
      ->getMockBuilder(PapayaAdministrationPage::class)
      ->disableOriginalConstructor()
      ->getMock();
  }

  private function getPartFixture() {
    $part = $this->createMock(PapayaAdministrationPagePart::class);
    $part
      ->expects($this->at(0))
      ->method('parameters')
      ->with($this->isInstanceOf(PapayaRequestParameters::class));
    $part
      ->expects($this->at(1))
      ->method('parameters')
      ->will($this->returnValue($this->createMock(PapayaRequestParameters::class)));
    return $part;
  }
}
