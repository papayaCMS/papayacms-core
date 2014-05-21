<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaSpamFilterStatisticalReferenceTest extends PapayaTestCase {

  /**
  * @covers PapayaSpamFilterStatisticalReference::load
  * @covers PapayaSpamFilterStatisticalReference::loadTotals
  * @covers PapayaSpamFilterStatisticalReference::getHamCount
  * @covers PapayaSpamFilterStatisticalReference::getSpamCount
  */
  public function testLoad() {
    $totalsDatabaseResult = $this->getMock('PapayaDatabaseResult');
    $totalsDatabaseResult
       ->expects($this->any())
       ->method('fetchRow')
       ->with($this->equalTo(PapayaDatabaseResult::FETCH_ASSOC))
       ->will(
         $this->onConsecutiveCalls(
           array(
             'spamcategory_ident' => 'ham',
             'text_count' => '42'
           ),
           array(
             'spamcategory_ident' => 'spam',
             'text_count' => '2142'
           ),
           NULL
         )
       );
    $recordsDatabaseResult = $this->getMock('PapayaDatabaseResult');
    $recordsDatabaseResult
       ->expects($this->any())
       ->method('fetchRow')
       ->with($this->equalTo(PapayaDatabaseResult::FETCH_ASSOC))
       ->will(
         $this->onConsecutiveCalls(
           array(
             'spamword' => 'papaya',
             'spamword_count' => '3',
             'spamcategory_ident' => 'ham'
           ),
           array(
             'spamword' => 'papaya',
             'spamword_count' => '1',
             'spamcategory_ident' => 'spam'
           ),
           array(
             'spamword' => 'poker',
             'spamword_count' => '13',
             'spamcategory_ident' => 'spam'
           ),
           NULL
         )
       );
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess',
      array('getTableName', 'getSqlCondition', 'queryFmt'),
      array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with($this->equalTo('spamword'), $this->equalTo(array('foo', 'bar')))
      ->will($this->returnValue("spamword IN ('foo', 'bar')"));
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->with($this->isType('string'))
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->exactly(2))
      ->method('queryFmt')
      ->with($this->isType('string'), $this->isType('array'))
      ->will(
        $this->onConsecutiveCalls(
          $recordsDatabaseResult, $totalsDatabaseResult
        )
      );

    $reference = new PapayaSpamFilterStatisticalReference();
    $reference->setDatabaseAccess($databaseAccess);
    $this->assertTrue($reference->load(array('foo', 'bar'), 2));
    $this->assertAttributeEquals(
      array(
        'papaya' => array(
          'word' => 'papaya',
          'ham' => 3,
          'spam' => 1
        ),
        'poker' => array(
          'word' => 'poker',
          'ham' => 0,
          'spam' => 13
        )
      ),
      '_records',
      $reference
    );
    $this->assertEquals(42, $reference->getHamCount());
    $this->assertEquals(2142, $reference->getSpamCount());
  }

  /**
  * @covers PapayaSpamFilterStatisticalReference::load
  */
  public function testLoadWithEmptyWordList() {
    $reference = new PapayaSpamFilterStatisticalReference();
    $this->assertFalse($reference->load(array(), 2));
  }

  /**
  * @covers PapayaSpamFilterStatisticalReference::load
  */
  public function testLoadWithDatabaseError() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess',
      array('getTableName', 'getSqlCondition', 'queryFmt'),
      array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with($this->equalTo('spamword'), $this->equalTo(array('foo', 'bar')))
      ->will($this->returnValue("spamword IN ('foo', 'bar')"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), $this->isType('array'))
      ->will($this->returnValue(FALSE));

    $reference = new PapayaSpamFilterStatisticalReference();
    $reference->setDatabaseAccess($databaseAccess);
    $this->assertFalse($reference->load(array('foo', 'bar'), 2));
  }
}
