<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaAdministrationPagesDependencySynchronizationTagsTest extends PapayaTestCase {

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationTags::synchronize
  * @covers PapayaAdministrationPagesDependencySynchronizationTags::synchronizeTags
  */
  public function testSynchronize() {
    $tags = $this->getMock('PapayaContentPageTags');
    $tags
      ->expects($this->once())
      ->method('load')
      ->with(23, 0)
      ->will($this->returnValue(TRUE));
    $tags
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new ArrayIterator(
            array(
              1 => array(
                'id' => 1,
                'pageId' => 23
              ),
              2 => array(
                'id' => 2,
                'pageId' => 23
              )
            )
          )
        )
      );
    $tags
      ->expects($this->exactly(2))
      ->method('clear')
      ->with($this->logicalOr(21, 42))
      ->will($this->returnValue(TRUE));
    $tags
      ->expects($this->exactly(2))
      ->method('insert')
      ->with($this->logicalOr(21, 42), array(1, 2))
      ->will($this->returnValue(TRUE));

    $action = new PapayaAdministrationPagesDependencySynchronizationTags();
    $action->tags($tags);
    $this->assertTrue($action->synchronize(array(21, 42), 23));
  }


  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationTags::synchronize
  * @covers PapayaAdministrationPagesDependencySynchronizationTags::synchronizeTags
  */
  public function testSynchronizeLoadFailed() {
    $tags = $this->getMock('PapayaContentPageTags');
    $tags
      ->expects($this->once())
      ->method('load')
      ->with(23, 0)
      ->will($this->returnValue(FALSE));

    $action = new PapayaAdministrationPagesDependencySynchronizationTags();
    $action->tags($tags);
    $this->assertFalse($action->synchronize(array(21, 42), 23));
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationTags::synchronize
  * @covers PapayaAdministrationPagesDependencySynchronizationTags::synchronizeTags
  */
  public function testSynchronizeClearOnly() {
    $tags = $this->getMock('PapayaContentPageTags');
    $tags
      ->expects($this->once())
      ->method('load')
      ->with(23, 0)
      ->will($this->returnValue(TRUE));
    $tags
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(new ArrayIterator(array()))
      );
    $tags
      ->expects($this->exactly(2))
      ->method('clear')
      ->with($this->logicalOr(21, 42))
      ->will($this->returnValue(TRUE));

    $action = new PapayaAdministrationPagesDependencySynchronizationTags();
    $action->tags($tags);
    $this->assertTrue($action->synchronize(array(21, 42), 23));
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationTags::synchronize
  * @covers PapayaAdministrationPagesDependencySynchronizationTags::synchronizeTags
  */
  public function testSynchronizeClearFailedExpectingFalse() {
    $tags = $this->getMock('PapayaContentPageTags');
    $tags
      ->expects($this->once())
      ->method('load')
      ->with(23, 0)
      ->will($this->returnValue(TRUE));
    $tags
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(new ArrayIterator(array()))
      );
    $tags
      ->expects($this->once())
      ->method('clear')
      ->with(21)
      ->will($this->returnValue(FALSE));

    $action = new PapayaAdministrationPagesDependencySynchronizationTags();
    $action->tags($tags);
    $this->assertFalse($action->synchronize(array(21, 42), 23));
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationTags::tags
  */
  public function testTagsGetAfterSet() {
    $tags = $this->getMock('PapayaContentPageTags');
    $action = new PapayaAdministrationPagesDependencySynchronizationTags();
    $this->assertSame(
      $tags, $action->tags($tags)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationTags::tags
  */
  public function testTagsGetImplicitCreate() {
    $action = new PapayaAdministrationPagesDependencySynchronizationTags();
    $this->assertInstanceOf(
      'PapayaContentPageTags', $action->tags()
    );
  }
}
