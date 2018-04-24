<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaMediaDatabaseItemTest extends PapayaTestCase {

  var $_mockRecordData = array();

  /**
  * @covers PapayaMediaDatabaseItem::__construct
  */
  public function testConstructor() {
    $service = $this->createMock(PapayaMediaStorageService::class);
    $item = new PapayaMediaDatabaseItem($service);
    $this->assertAttributeSame(
      $service, '_storage', $item
    );
  }

  /**
  * @covers PapayaMediaDatabaseItem::__set
  */
  public function testMagicMethodSetWithInvalidName() {
    $item = new PapayaMediaDatabaseItem($this->createMock(PapayaMediaStorageService::class));
    $this->expectException(BadMethodCallException::class);
    /** @noinspection PhpUndefinedFieldInspection */
    $item->invalidPropertyName = '';
  }

  /**
  * @covers PapayaMediaDatabaseItem::__set
  * @covers PapayaMediaDatabaseItem::_setName
  * @covers PapayaMediaDatabaseItem::_setAttributeTrimString
  */
  public function testMagicMethodSetName() {
    $item = new PapayaMediaDatabaseItem($this->createMock(PapayaMediaStorageService::class));
    $item->name = 'SAMPLE_NAME';
    $attributes = $this->readAttribute($item, '_attributes');
    $this->assertSame(
      'SAMPLE_NAME', $attributes['name']
    );
  }

  /**
  * @covers PapayaMediaDatabaseItem::__set
  * @covers PapayaMediaDatabaseItem::_setName
  * @covers PapayaMediaDatabaseItem::_setAttributeTrimString
  */
  public function testMagicMethodSetNameWithInvalidValue() {
    $item = new PapayaMediaDatabaseItem($this->createMock(PapayaMediaStorageService::class));
    $this->expectException(BadMethodCallException::class);
    $item->name = '';
  }

  /**
  * @covers PapayaMediaDatabaseItem::__set
  * @covers PapayaMediaDatabaseItem::_setMediaId
  */
  public function testMagicMethodSetMediaId() {
    $item = new PapayaMediaDatabaseItem($this->createMock(PapayaMediaStorageService::class));
    $item->mediaId = '123456789012345678901234567890ab';
    $this->assertAttributeSame(
      '123456789012345678901234567890ab', '_mediaId', $item
    );
  }

  /**
  * @covers PapayaMediaDatabaseItem::__set
  * @covers PapayaMediaDatabaseItem::_setMediaId
  */
  public function testMagicMethodSetMediaIdWithInvalidValue() {
    $item = new PapayaMediaDatabaseItem($this->createMock(PapayaMediaStorageService::class));
    $this->expectException(BadMethodCallException::class);
    $item->mediaId = 'abc';
  }

  /**
  * @covers PapayaMediaDatabaseItem::__set
  * @covers PapayaMediaDatabaseItem::_setVersionId
  */
  public function testMagicMethodSetVersionId() {
    $item = new PapayaMediaDatabaseItem($this->createMock(PapayaMediaStorageService::class));
    $item->versionId = '3';
    $this->assertAttributeSame(
      3, '_versionId', $item
    );
  }

  /**
  * @covers PapayaMediaDatabaseItem::__set
  * @covers PapayaMediaDatabaseItem::_setVersionId
  */
  public function testMagicMethodSetVersionIdWithInvalidValue() {
    $item = new PapayaMediaDatabaseItem($this->createMock(PapayaMediaStorageService::class));
    $this->expectException(BadMethodCallException::class);
    $item->versionId = 'a';
  }

  /**
  * @covers PapayaMediaDatabaseItem::__set
  */
  public function testMagicMethodSetMimeType() {
    $item = new PapayaMediaDatabaseItem($this->createMock(PapayaMediaStorageService::class));
    $item->mimeType = 'image/gif';
    $attributes = $this->readAttribute($item, '_attributes');
    $this->assertSame(
      'image/gif', $attributes['mimeType']
    );
  }

  /**
  * @covers PapayaMediaDatabaseItem::__get
  */
  public function testMagicMethodGet() {
    $item = new PapayaMediaDatabaseItem($this->createMock(PapayaMediaStorageService::class));
    $item->name = 'sample.png';
    $this->assertSame(
      'sample.png', $item->name
    );
  }

  /**
  * @covers PapayaMediaDatabaseItem::__get
  */
  public function testMagicMethodGetWithInvalidName() {
    $item = new PapayaMediaDatabaseItem($this->createMock(PapayaMediaStorageService::class));
    $this->expectException(BadMethodCallException::class);
    $dummy = $item->invalidPropertyName;
  }

  /**
  * @covers PapayaMediaDatabaseItem::__get
  */
  public function testMagicMethodGetMediaId() {
    $item = new PapayaMediaDatabaseItem($this->createMock(PapayaMediaStorageService::class));
    $item->mediaId = '123456789012345678901234567890ab';
    $this->assertSame(
      '123456789012345678901234567890ab', $item->mediaId
    );
  }

  /**
  * @covers PapayaMediaDatabaseItem::__get
  */
  public function testMagicMethodGetVersionId() {
    $item = new PapayaMediaDatabaseItem($this->createMock(PapayaMediaStorageService::class));
    $item->versionId = 23;
    $this->assertSame(
      23, $item->versionId
    );
  }

  /**
  * @covers PapayaMediaDatabaseItem::setDatabaseAccessObject
  */
  function testSetDatabaseAccessObject() {
    $item = new PapayaMediaDatabaseItem($this->createMock(PapayaMediaStorageService::class));
    $record = $this->createMock(PapayaMediaDatabaseItemRecord::class);
    $item->setDatabaseAccessObject($record);
    $this->assertAttributeSame(
      $record, '_databaseAccessObject', $item
    );
  }

  /**
  * @covers PapayaMediaDatabaseItem::getDatabaseAccessObject
  */
  function testGetDatabaseAccessObject() {
    $item = new PapayaMediaDatabaseItem($this->createMock(PapayaMediaStorageService::class));
    $record = $this->createMock(PapayaMediaDatabaseItemRecord::class);
    $item->setDatabaseAccessObject($record);
    $this->assertSame(
      $record,
      $item->getDatabaseAccessObject()
    );
  }

  /**
  * @covers PapayaMediaDatabaseItem::getDatabaseAccessObject
  */
  function testGetDatabaseAccessObjectImplicitCreate() {
    $item = new PapayaMediaDatabaseItem($this->createMock(PapayaMediaStorageService::class));
    $this->assertInstanceOf(
      PapayaMediaDatabaseItemRecord::class,
      $item->getDatabaseAccessObject()
    );
  }

  /**
  * @covers PapayaMediaDatabaseItem::load
  */
  public function testLoad() {
    $item = new PapayaMediaDatabaseItem($this->createMock(PapayaMediaStorageService::class));
    $item->setDatabaseAccessObject(
      $this->getMockRecordLoadFixture(
        array(
          'file_name' => 'sample.png',
          'current_version_id' => '1',
          'mimetype' => 'image/gif',
        )
      )
    );
    $this->assertTrue($item->load('123456789012345678901234567890ab'));
    $this->assertAttributeEquals(
      '123456789012345678901234567890ab', '_mediaId', $item
    );
    $this->assertAttributeEquals(
      1, '_versionId', $item
    );
    $this->assertAttributeEquals(
      array(
        'name' => 'sample.png',
        'mimeType' => 'image/gif',
      ),
      '_attributes',
      $item
    );
  }

  /**
  * @covers PapayaMediaDatabaseItem::load
  */
  public function testLoadWithInvalidArgument() {
    $item = new PapayaMediaDatabaseItem($this->createMock(PapayaMediaStorageService::class));
    $record = $this->createMock(PapayaMediaDatabaseItemRecord::class);
    $record
      ->expects($this->once())
      ->method('load')
      ->will($this->returnValue(FALSE));
    $item->setDatabaseAccessObject($record);
    $this->expectException(InvalidArgumentException::class);
    $item->load(NULL);
  }

  /**
  * @covers PapayaMediaDatabaseItem::getUrl
  */
  public function testGetUrl() {
    $storage = $this->createMock(PapayaMediaStorageService::class);
    $storage
      ->expects($this->once())
      ->method('getUrl')
      ->with(
        $this->equalTo('files'),
        $this->equalTo('123456789012345678901234567890abv1')
      )
      ->will(
        $this->returnValue(
          'http://cdn.sample.tld/files/123456789012345678901234567890abv1'
        )
      );
    $item = new PapayaMediaDatabaseItem($storage);
    $item->mediaId = '123456789012345678901234567890ab';
    $item->versionId = 1;
    $this->assertEquals(
      'http://cdn.sample.tld/files/123456789012345678901234567890abv1',
      $item->getUrl()
    );
  }

  /************************
  * Fixtures
  ************************/

  public function getMockRecordLoadFixture($data) {
    $this->_mockRecordData = $data;
    $record = $this->createMock(PapayaMediaDatabaseItemRecord::class);
    $record
      ->expects($this->once())
      ->method('load')
      ->will($this->returnValue(TRUE));
    $record
      ->expects($this->any())
      ->method('offsetGet')
      ->will(
        $this->returnCallBack(
          array($this, 'callbackMockRecordLoadData')
        )
      );
    return $record;
  }

  /**
   * @param $name
   * @return mixed
   */
  public function callbackMockRecordLoadData($name) {
    if (isset($this->_mockRecordData[$name])) {
      return $this->_mockRecordData[$name];
    } else {
      /** @noinspection PhpVoidFunctionResultUsedInspection */
      return $this->fail(sprintf('Unknown field in record "%s"', $name));
    }
  }
}

