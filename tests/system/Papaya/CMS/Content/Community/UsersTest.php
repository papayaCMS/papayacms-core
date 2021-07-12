<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\CMS\Content\Community;

require_once __DIR__.'/../../../../../bootstrap.php';

class UsersTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\CMS\Content\Community\Users::_compileCondition
   * @dataProvider provideFilterArrays
   * @param string $expected
   * @param array $filter
   */
  public function testCompileCondition($expected, array $filter) {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
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

    $users = new Users();
    $users->setDatabaseAccess($databaseAccess);
    $this->assertEquals(
      $expected,
      $users->_compileCondition($filter)
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Community\Users::_createMapping
   */
  public function testCreateMapping() {
    $users = new Users();
    /** @var \Papaya\Database\Interfaces\Mapping $mapping */
    $mapping = $users->mapping();
    $this->assertTrue(isset($mapping->callbacks()->onAfterMappingFieldsToProperties));
  }

  /**
   * @covers \Papaya\CMS\Content\Community\Users::callbackAfterMappingFieldsToProperties
   * @dataProvider provideRecordsForMapping
   * @param $expected
   * @param $values
   */
  public function testCallbackAfterMappingFieldsToProperties(array $expected, array $values) {
    $users = new Users();
    $this->assertEquals(
      $expected, $users->callbackAfterMappingFieldsToProperties(new \stdClass, $values)
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
