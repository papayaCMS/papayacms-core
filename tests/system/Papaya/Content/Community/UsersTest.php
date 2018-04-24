<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentCommunityUsersTest extends PapayaTestCase {

  /**
  * @covers PapayaContentCommunityUsers::_compileCondition
  * @dataProvider provideFilterArrays
  */
  public function testCompileCondition($expected, $filter) {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('getSqlCondition', 'escapeString'))
      ->getMock();
    $databaseAccess
      ->expects($this->any())
      ->method('getSqlCondition')
      ->withAnyParameters()
      ->will($this->returnValue('>>CONDITION<<'));
    $databaseAccess
      ->expects($this->any())
      ->method('escapeString')
      ->withAnyParameters()
      ->will($this->returnArgument(0));

    $users = new PapayaContentCommunityUsers_TestProxy();
    $users->setDatabaseAccess($databaseAccess);
    $this->assertEquals(
      $expected,
      $users->_compileCondition($filter)
    );
  }

  /**
  * @covers PapayaContentCommunityUsers::_createMapping
  */
  public function testCreateMapping() {
    $users = new PapayaContentCommunityUsers_TestProxy();
    $mapping = $users->mapping();
    $this->assertTrue(isset($mapping->callbacks()->onAfterMappingFieldsToProperties));
  }

  /**
  * @covers PapayaContentCommunityUsers::callbackAfterMappingFieldsToProperties
  * @dataProvider provideRecordsForMapping
  */
  public function testCallbackAfterMappingFieldsToProperties($expected, $values) {
    $users = new PapayaContentCommunityUsers_TestProxy();
    $this->assertEquals(
      $expected, $users->callbackAfterMappingFieldsToProperties(new stdClass, $values, array())
    );
  }

  /**************************
  * Data Provider
  **************************/

  public static function provideFilterArrays() {
    return array(
      'filter text and id' => array(
        " WHERE (surfer_givenname LIKE '%test%' OR surfer_surname LIKE '%test%' OR".
          " surfer_email LIKE '%test%') AND (>>CONDITION<<)",
        array('filter' => 'test', 'id' => 'sample_id')
      ),
      'id only' => array(
        ' WHERE (>>CONDITION<<)',
        array('id' => 'sample_id')
      ),
      'empy condition' => array(
        '',
        array()
      )
    );
  }

  public static function provideRecordsForMapping() {
    return array(
      'full name' => array(
        array(
          'id' => 1,
          'givenname' => 'first',
          'surname' => 'last',
          'email' => 'mail@test.tld',
          'caption' => 'last, first'
        ),
        array(
          'id' => 1,
          'givenname' => 'first',
          'surname' => 'last',
          'email' => 'mail@test.tld'
        )
      ),
      'surname' => array(
        array(
          'id' => 1,
          'givenname' => '',
          'surname' => 'last',
          'email' => 'mail@test.tld',
          'caption' => 'last, ?'
        ),
        array(
          'id' => 1,
          'givenname' => '',
          'surname' => 'last',
          'email' => 'mail@test.tld',
        )
      ),
      'email' => array(
        array(
          'id' => 1,
          'givenname' => '',
          'surname' => '',
          'email' => 'mail@test.tld',
          'caption' => 'mail@test.tld'
        ),
        array(
          'id' => 1,
          'givenname' => '',
          'surname' => '',
          'email' => 'mail@test.tld'
        )
      )
    );
  }
}

class PapayaContentCommunityUsers_TestProxy extends PapayaContentCommunityUsers {

  public function _compileCondition($filter, $prefix = " WHERE ") {
    return parent::_compileCondition($filter, $prefix);
  }
}
