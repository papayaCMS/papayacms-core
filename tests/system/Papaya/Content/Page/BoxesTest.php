<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentPageBoxesTest extends PapayaTestCase {

  /**
  * @covers PapayaContentPageBoxes::load
  */
  public function testLoad() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'box_id' => 1,
            'topic_id' => 21,
            'box_sort' => 1
          ),
          array(
            'box_id' => 2,
            'topic_id' => 21,
            'box_sort' => 2
          ),
          FALSE
        )
      );
    $databaseAccess = $this->getDatabaseAccessMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('boxlinks', 21))
      ->will($this->returnValue($databaseResult));
    $boxes = new PapayaContentPageBoxes();
    $boxes->setDatabaseAccess($databaseAccess);
    $this->assertTrue($boxes->load(21));
    $this->assertAttributeEquals(
      array(
        array(
          'box_id' => 1,
          'page_id' => 21,
          'position' => 1
        ),
        array(
          'box_id' => 2,
          'page_id' => 21,
          'position' => 2
        ),
      ),
      '_records',
      $boxes
    );
  }

  /**
  * @covers PapayaContentPageBoxes::delete
  */
  public function testDelete() {
    $databaseAccess = $this->getDatabaseAccessMock();
    $databaseAccess
      ->expects($this->once())
      ->method('deleteRecord')
      ->with($this->isType('string'), 'topic_id', array(21))
      ->will($this->returnValue(0));
    $boxes = new PapayaContentPageBoxes();
    $boxes->setDatabaseAccess($databaseAccess);
    $this->assertTrue($boxes->delete(21));
  }

  /**
  * @covers PapayaContentPageBoxes::copyTo
  */
  public function testCopyTo() {
    $databaseAccess = $this->getDatabaseAccessMock();
    $databaseAccess
      ->expects($this->once())
      ->method('deleteRecord')
      ->with($this->isType('string'), 'topic_id', array(42, 23))
      ->will($this->returnValue(0));
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecords')
      ->with(
        $this->isType('string'),
        array(
          array(
            'box_id' => 1,
            'topic_id' => 42,
            'box_sort' => 1
          ),
          array(
            'box_id' => 2,
            'topic_id' => 42,
            'box_sort' => 2
          ),
          array(
            'box_id' => 1,
            'topic_id' => 23,
            'box_sort' => 1
          ),
          array(
            'box_id' => 2,
            'topic_id' => 23,
            'box_sort' => 2
          )
        )
      )
      ->will($this->returnValue(0));
    $boxes = new PapayaContentPageBoxes();
    $boxes->setDatabaseAccess($databaseAccess);
    $boxes->assign(
      array(
        array(
          'box_id' => 1,
          'page_id' => 21,
          'position' => 1
        ),
        array(
          'box_id' => 2,
          'page_id' => 21,
          'position' => 2
        ),
      )
    );
    $this->assertTrue($boxes->copyTo(array(42, 23)));
  }

  /**
  * @covers PapayaContentPageBoxes::copyTo
  */
  public function testCopyToWithEmptySourceAndTargetExpectingTrue() {
    $boxes = new PapayaContentPageBoxes();
    $this->assertTrue($boxes->copyTo(array()));
  }

  /**
  * @covers PapayaContentPageBoxes::copyTo
  */
  public function testCopyToWhileDeleteFailedExpectingFalse() {
    $databaseAccess = $this->getDatabaseAccessMock();
    $databaseAccess
      ->expects($this->once())
      ->method('deleteRecord')
      ->with($this->isType('string'), 'topic_id', array(42, 23))
      ->will($this->returnValue(FALSE));
    $boxes = new PapayaContentPageBoxes();
    $boxes->setDatabaseAccess($databaseAccess);
    $boxes->assign(
      array(
        array(
          'box_id' => 1,
          'page_id' => 21,
          'position' => 1
        ),
        array(
          'box_id' => 2,
          'page_id' => 21,
          'position' => 2
        ),
      )
    );
    $this->assertFalse($boxes->copyTo(array(42, 23)));
  }

  /*****************
  * Fixtures
  *****************/

  private function getDatabaseAccessMock() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('getTableName', 'queryFmt', 'deleteRecord', 'insertRecords'))
      ->getMock();
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->withAnyParameters()
      ->will($this->returnArgument(0));
    return $databaseAccess;
  }
}
