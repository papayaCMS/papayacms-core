<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaContentDomainsTest extends PapayaTestCase {

  /**
  * @covers PapayaContentDomains::load
  */
  public function testLoad() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'domain_id' => 21,
            'domain_hostname' => 'www.sample.tld',
            'domain_protocol' => 0,
            'domain_mode' => 4
          ),
          FALSE
        )
      );
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array(PapayaContentTables::DOMAINS)
      )
      ->will($this->returnValue($databaseResult));
    $pages = new PapayaContentDomains();
    $pages->setDatabaseAccess($databaseAccess);
    $this->assertTrue($pages->load());
    $this->assertEquals(
      array(
        21 => array('id' => 21, 'host' => 'www.sample.tld', 'scheme' => 0, 'mode' => 4)
      ),
      $pages->toArray()
    );
  }

  /**
  * @covers PapayaContentDomains::load
  */
  public function testLoadWithFilter() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'domain_id' => 21,
            'domain_hostname' => 'www.sample.tld',
            'domain_protocol' => 0,
            'domain_mode' => 4
          ),
          FALSE
        )
      );
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('getSqlCondition', 'queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('domain_mode' => 4))
      ->will($this->returnValue(" domain_mode = '4'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array(PapayaContentTables::DOMAINS)
      )
      ->will($this->returnValue($databaseResult));
    $pages = new PapayaContentDomains();
    $pages->setDatabaseAccess($databaseAccess);
    $this->assertTrue($pages->load(array('mode' => 4)));
    $this->assertEquals(
      array(
        21 => array('id' => 21, 'host' => 'www.sample.tld', 'scheme' => 0, 'mode' => 4)
      ),
      $pages->toArray()
    );
  }
}