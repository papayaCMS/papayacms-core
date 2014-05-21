<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaDatabaseSequenceTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseSequence::__construct
  */
  public function testConstructor() {
    $sequence = new PapayaDatabaseSequence_TestProxy('table', 'field');
    $this->assertAttributeEquals(
      'table', '_table', $sequence
    );
    $this->assertAttributeEquals(
      'field', '_field', $sequence
    );
  }

  /**
  * @covers PapayaDatabaseSequence::__construct
  */
  public function testConstructorWithEmptyTableExpectingException() {
    $this->setExpectedException('InvalidArgumentException');
    $sequence = new PapayaDatabaseSequence_TestProxy('', 'field');
  }

  /**
  * @covers PapayaDatabaseSequence::__construct
  */
  public function testConstructorWithEmptyFieldExpectingException() {
    $this->setExpectedException('InvalidArgumentException');
    $sequence = new PapayaDatabaseSequence_TestProxy('table', '');
  }

  /**
  * @covers PapayaDatabaseSequence::next
  * @covers PapayaDatabaseSequence::createIdentifiers
  * @covers PapayaDatabaseSequence::checkIdentifiers
  */
  public function testNext() {
    $sequence = new PapayaDatabaseSequence_TestProxy('table', 'field');
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->exactly(3))
      ->method('fetchRow')
      ->will($this->onConsecutiveCalls(array(1), array(2), NULL));
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('getSqlCondition', 'queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with('field', array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10))
      ->will($this->returnValue("'1', '2', '3', '4', '5', '6', '7', '8', '9', '10'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('field', 'table'))
      ->will($this->returnValue($databaseResult));
    $sequence->setDatabaseAccess($databaseAccess);
    $this->assertEquals(
      3, $sequence->next()
    );
  }

  /**
  * @covers PapayaDatabaseSequence::next
  * @covers PapayaDatabaseSequence::createIdentifiers
  * @covers PapayaDatabaseSequence::checkIdentifiers
  */
  public function testNextAllInDatabaseFirstTime() {
    $sequence = new PapayaDatabaseSequence_TestProxy('table', 'field');
    $databaseResultOne = $this->getMock('PapayaDatabaseResult');
    $databaseResultOne
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(1),
          array(2),
          array(3),
          array(4),
          array(5),
          array(6),
          array(7),
          array(8),
          array(9),
          array(10),
          FALSE
        )
      );
    $databaseResultTwo = $this->getMock('PapayaDatabaseResult');
    $databaseResultTwo
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(11),
          array(12),
          array(13),
          FALSE
        )
      );
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('getSqlCondition', 'queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->any())
      ->method('getSqlCondition')
      ->with('field', $this->isType('array'))
      ->will(
        $this->onConsecutiveCalls(
          $this->returnValue("'1', '2', '3', '4', '5', '6', '7', '8', '9', '10'"),
          $this->returnValue("'11', '12', '13', '14', '15', '16', '17', '18', '19', '20'")
        )
      );
    $databaseAccess
      ->expects($this->any())
      ->method('queryFmt')
      ->with($this->isType('string'), array('field', 'table'))
      ->will(
        $this->onConsecutiveCalls(
          $this->returnValue($databaseResultOne),
          $this->returnValue($databaseResultTwo)
        )
      );
    $sequence->setDatabaseAccess($databaseAccess);
    $this->assertEquals(
      14, $sequence->next()
    );
  }

  /**
  * @covers PapayaDatabaseSequence::next
  * @covers PapayaDatabaseSequence::checkIdentifiers
  */
  public function testNextDatabaseQueryFailed() {
    $sequence = new PapayaDatabaseSequence_TestProxy('table', 'field');
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('getSqlCondition', 'queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with('field', array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10))
      ->will($this->returnValue("'1', '2', '3', '4', '5', '6', '7', '8', '9', '10'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('field', 'table'))
      ->will($this->returnValue(FALSE));
    $sequence->setDatabaseAccess($databaseAccess);
    $this->assertFalse(
      $sequence->next()
    );
  }

  /**
  * @covers PapayaDatabaseSequence::next
  * @covers PapayaDatabaseSequence::checkIdentifiers
  */
  public function testNextBrokenCreateMethod() {
    $sequence = new PapayaDatabaseSequence_TestProxyBroken('table', 'field');
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('getSqlCondition', 'queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with('field', array())
      ->will($this->returnValue(""));
    $sequence->setDatabaseAccess($databaseAccess);

    $this->setExpectedException('InvalidArgumentException');
    $sequence->next();
  }
}

class PapayaDatabaseSequence_TestProxy extends PapayaDatabaseSequence {

  public $idCounter = 1;

  public function create() {
    return $this->idCounter++;
  }

  public function checkIdentifiers($identifiers) {
    return parent::checkIdentifiers($identifiers);
  }

  public function createIdentifiers($count) {
    return parent::createIdentifiers($count);
  }
}

class PapayaDatabaseSequence_TestProxyBroken extends PapayaDatabaseSequence_TestProxy {

  public function create() {
    return NULL;
  }
}