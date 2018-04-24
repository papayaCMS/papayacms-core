<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaContentViewModeTypesTest extends PapayaTestCase {

  /**
   * @covers PapayaContentViewModeTypes::exists
   */
  public function testExistsExpectingTrue() {
    $this->assertTrue(PapayaContentViewModeTypes::exists(PapayaContentViewModeTypes::PAGE));
  }

  /**
   * @covers PapayaContentViewModeTypes::exists
   */
  public function testExistsExpectingFalse() {
    $this->assertFalse(PapayaContentViewModeTypes::exists(-23));
  }

  /**
   * @covers PapayaContentViewModeTypes::offsetExists
   */
  public function testArrayAccessExistsExpectingTrue() {
    $types = new PapayaContentViewModeTypes();
    $this->assertTrue(isset($types[PapayaContentViewModeTypes::PAGE]));
  }

  /**
   * @covers PapayaContentViewModeTypes::offsetExists
   */
  public function testArrayAccessExistsExpectingFalse() {
    $types = new PapayaContentViewModeTypes();
    $this->assertFalse(isset($types[-23]));
  }

  /**
   * @covers PapayaContentViewModeTypes::offsetGet
   */
  public function testArrayAccessGet() {
    $types = new PapayaContentViewModeTypes();
    $this->assertEquals('Feed', $types[PapayaContentViewModeTypes::FEED]);
  }

  /**
   * @covers PapayaContentViewModeTypes::offsetGet
   */
  public function testArrayAccessGetwithInvalidType() {
    $types = new PapayaContentViewModeTypes();
    $this->assertEquals('Page', $types[-23]);
  }

  /**
   * @covers PapayaContentViewModeTypes::offsetSet
   */
  public function testArrayAccessBlockedSet() {
    $types = new PapayaContentViewModeTypes();
    $this->setExpectedException(LogicException::class);
    $types[PapayaContentViewModeTypes::FEED] = 'invalid';
  }

  /**
   * @covers PapayaContentViewModeTypes::offsetUnset
   */
  public function testArrayAccessBlockedUnset() {
    $types = new PapayaContentViewModeTypes();
    $this->setExpectedException(LogicException::class);
    unset($types[PapayaContentViewModeTypes::FEED]);
  }

  /**
   * @covers PapayaContentViewModeTypes::getIterator
   */
  public function testIterator() {
    $types = new PapayaContentViewModeTypes();
    $this->assertEquals(
      array(
        PapayaContentViewModeTypes::PAGE => 'Page',
        PapayaContentViewModeTypes::FEED => 'Feed',
        PapayaContentViewModeTypes::HIDDEN => 'Hidden'
      ),
      iterator_to_array($types)
    );
  }
}
