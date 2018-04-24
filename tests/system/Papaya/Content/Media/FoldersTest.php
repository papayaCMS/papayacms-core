<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentMediaFoldersTest extends PapayaTestCase {

  /**
   * @covers PapayaContentMediaFolders::_createMapping
   */
  public function testCreateMapping() {
    $records = new PapayaContentMediaFolders();
    $mapping = $records->mapping();
    $this->assertTrue(isset($mapping->callbacks()->onMapValueFromFieldToProperty));
    $this->assertTrue(isset($mapping->callbacks()->onGetFieldForProperty));
  }

  /**
   * @covers PapayaContentMediaFolders::callbackMapValueFromFieldToProperty
   */
  public function testCallbackMapValueFromFieldToProperty() {
    $records = new PapayaContentMediaFolders();
    $this->assertEquals(
      23,
      $records->callbackMapValueFromFieldToProperty(
        new stdClass(), 'id', 'folder', '23'
      )
    );
  }

  /**
   * @covers PapayaContentMediaFolders::callbackMapValueFromFieldToProperty
   */
  public function testCallbackMapValueFromFieldToPropertyDecodesAncestors() {
    $records = new PapayaContentMediaFolders();
    $this->assertEquals(
      array(21, 42),
      $records->callbackMapValueFromFieldToProperty(
        new stdClass(), 'ancestors', 'parent_path', ';21;42;'
      )
    );
  }

  /**
   * @covers PapayaContentMediaFolders::callbackGetFieldForProperty
   */
  public function testCallbackGetFieldForPropertyUnknownPropertyExpectingNull() {
    $records = new PapayaContentMediaFolders();
    $this->assertNull(
      $records->callbackGetFieldForProperty(
        new stdClass(), 'unknown_property_name'
      )
    );
  }

  /**
   * @covers PapayaContentMediaFolders::callbackGetFieldForProperty
   * @dataProvider providePropertyToFieldValues
   */
  public function testCallbackGetFieldForProperty($expected, $property) {
    $records = new PapayaContentMediaFolders();
    $this->assertEquals(
      $expected, $records->callbackGetFieldForProperty(new stdClass, $property, '')
    );
  }

  public static function providePropertyToFieldValues() {
    return array(
      array('f.folder_id', 'id'),
      array('f.parent_id', 'parent_id'),
      array('f.parent_path', 'ancestors'),
      array('ft.lng_id', 'language_id'),
      array('ft.folder_name', 'title')
    );
  }

  /**
   * @covers PapayaContentMediaFolders::load
   */
  public function testLoad() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'folder_id' => 1,
            'parent_id' => 0,
            'parent_path' => ';0;',
            'lng_id' => 1,
            'folder_name' => 'One'
          ),
          array(
            'folder_id' => 2,
            'parent_id' => 0,
            'parent_path' => ';0;',
            'lng_id' => 1,
            'folder_name' => 'Two'
          ),
          array(
            'folder_id' => 3,
            'parent_id' => 1,
            'parent_path' => ';1;',
            'lng_id' => 1,
            'folder_name' => 'Tree'
          )
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
        array('mediadb_folders', 'mediadb_folders_trans', 1)
      )
      ->will($this->returnValue($databaseResult));

    $records = new PapayaContentMediaFolders();
    $records->setDatabaseAccess($databaseAccess);
    $this->assertTrue($records->load(array('language_id' => 1)));

    $this->assertEquals(
      array(
        1 => array(
          'id' => 1,
          'parent_id' => 0,
          'ancestors' => array(0),
          'language_id' => 1,
          'title' => 'One'
        ),
        3 => array(
          'id' => 3,
          'parent_id' => 1,
          'ancestors' => array(1),
          'language_id' => 1,
          'title' => 'Tree'
        ),
        2 => array(
          'id' => 2,
          'parent_id' => 0,
          'ancestors' => array(0),
          'language_id' => 1,
          'title' => 'Two'
        )
      ),
      iterator_to_array(
        new RecursiveIteratorIterator($records, RecursiveIteratorIterator::SELF_FIRST)
      )
    );
  }


  /**
   * @covers PapayaContentMediaFolders::load
   */
  public function testLoadwithoutLanguageIdExpectingNoTranslations() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'folder_id' => 1,
            'parent_id' => 0,
            'parent_path' => ';0;',
            'lng_id' => NULL,
            'folder_name' => NULL
          )
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
        array('mediadb_folders', 'mediadb_folders_trans', 0)
      )
      ->will($this->returnValue($databaseResult));

    $records = new PapayaContentMediaFolders();
    $records->setDatabaseAccess($databaseAccess);
    $this->assertTrue($records->load());

    $this->assertEquals(
      array(
        1 => array(
          'id' => 1,
          'parent_id' => 0,
          'ancestors' => array(0),
          'language_id' =>  NULL,
          'title' => NULL
        )
      ),
      iterator_to_array(
        new RecursiveIteratorIterator($records, RecursiveIteratorIterator::SELF_FIRST)
      )
    );
  }
}
