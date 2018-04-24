<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaThemeListTest extends PapayaTestCase {

  /**
   * @covers PapayaThemeList::getIterator
   * @covers PapayaThemeList::callbackGetName
   */
  public function testGetIterator() {
    $handler = $this->createMock(PapayaThemeHandler::class);
    $handler
      ->expects($this->once())
      ->method('getLocalPath')
      ->will($this->returnValue(__DIR__.'/TestDataList/'));
    $list = new PapayaThemeList();
    $list->handler($handler);
    $this->assertEquals(
      array(
        'theme-sample'
      ),
      iterator_to_array($list)
    );
  }

  /**
   * @covers PapayaThemeList::getDefinition
   */
  public function testGetDefinition() {
    $handler = $this->createMock(PapayaThemeHandler::class);
    $handler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('theme-sample')
      ->will($this->returnValue(new PapayaContentStructure()));
    $list = new PapayaThemeList();
    $list->handler($handler);
    $this->assertInstanceOf(
      'PapayaContentStructure',
      $list->getDefinition('theme-sample')
    );
  }

  /**
   * @covers PapayaThemeList::handler
   */
  public function testHandlerGetAfterSet() {
    $list = new PapayaThemeList();
    $list->handler($handler =  $this->createMock(PapayaThemeHandler::class));
    $this->assertSame($handler, $list->handler());
  }

  /**
   * @covers PapayaThemeList::handler
   */
  public function testHandlerGetImplicitCreate() {
    $list = new PapayaThemeList();
    $this->assertInstanceOf('PapayaThemeHandler', $list->handler());
  }
}

