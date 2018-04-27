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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaMediaDatabaseItemTest extends PapayaTestCase {

  /**
  * @covers PapayaMediaDatabaseItem::__construct
  */
  public function testConstructor() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMediaStorageService $service */
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMediaStorageService $service */
    $service = $this->createMock(PapayaMediaStorageService::class);
    $item = new PapayaMediaDatabaseItem($service);
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMediaStorageService $service */
    $service = $this->createMock(PapayaMediaStorageService::class);
    $item = new PapayaMediaDatabaseItem($service);
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMediaStorageService $service */
    $service = $this->createMock(PapayaMediaStorageService::class);
    $item = new PapayaMediaDatabaseItem($service);
    $this->expectException(BadMethodCallException::class);
    $item->name = '';
  }

  /**
  * @covers PapayaMediaDatabaseItem::__set
  * @covers PapayaMediaDatabaseItem::_setMediaId
  */
  public function testMagicMethodSetMediaId() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMediaStorageService $service */
    $service = $this->createMock(PapayaMediaStorageService::class);
    $item = new PapayaMediaDatabaseItem($service);
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMediaStorageService $service */
    $service = $this->createMock(PapayaMediaStorageService::class);
    $item = new PapayaMediaDatabaseItem($service);
    $this->expectException(BadMethodCallException::class);
    $item->mediaId = 'abc';
  }

  /**
  * @covers PapayaMediaDatabaseItem::__set
  * @covers PapayaMediaDatabaseItem::_setVersionId
  */
  public function testMagicMethodSetVersionId() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMediaStorageService $service */
    $service = $this->createMock(PapayaMediaStorageService::class);
    $item = new PapayaMediaDatabaseItem($service);
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMediaStorageService $service */
    $service = $this->createMock(PapayaMediaStorageService::class);
    $item = new PapayaMediaDatabaseItem($service);
    $this->expectException(BadMethodCallException::class);
    $item->versionId = 'a';
  }

  /**
  * @covers PapayaMediaDatabaseItem::__set
  */
  public function testMagicMethodSetMimeType() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMediaStorageService $service */
    $service = $this->createMock(PapayaMediaStorageService::class);
    $item = new PapayaMediaDatabaseItem($service);
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMediaStorageService $service */
    $service = $this->createMock(PapayaMediaStorageService::class);
    $item = new PapayaMediaDatabaseItem($service);
    $item->name = 'sample.png';
    $this->assertSame(
      'sample.png', $item->name
    );
  }

  /**
  * @covers PapayaMediaDatabaseItem::__get
  */
  public function testMagicMethodGetWithInvalidName() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMediaStorageService $service */
    $service = $this->createMock(PapayaMediaStorageService::class);
    $item = new PapayaMediaDatabaseItem($service);
    $this->expectException(BadMethodCallException::class);
    /** @noinspection PhpUndefinedFieldInspection */
    $item->invalidPropertyName;
  }

  /**
  * @covers PapayaMediaDatabaseItem::__get
  */
  public function testMagicMethodGetMediaId() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMediaStorageService $service */
    $service = $this->createMock(PapayaMediaStorageService::class);
    $item = new PapayaMediaDatabaseItem($service);
    $item->mediaId = '123456789012345678901234567890ab';
    $this->assertSame(
      '123456789012345678901234567890ab', $item->mediaId
    );
  }

  /**
  * @covers PapayaMediaDatabaseItem::__get
  */
  public function testMagicMethodGetVersionId() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMediaStorageService $service */
    $service = $this->createMock(PapayaMediaStorageService::class);
    $item = new PapayaMediaDatabaseItem($service);
    $item->versionId = 23;
    $this->assertSame(
      23, $item->versionId
    );
  }

  /**
  * @covers PapayaMediaDatabaseItem::setDatabaseAccessObject
  */
  public function testSetDatabaseAccessObject() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMediaStorageService $service */
    $service = $this->createMock(PapayaMediaStorageService::class);
    $item = new PapayaMediaDatabaseItem($service);
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMediaDatabaseItemRecord $record */
    $record = $this->createMock(PapayaMediaDatabaseItemRecord::class);
    $item->setDatabaseAccessObject($record);
    $this->assertAttributeSame(
      $record, '_databaseAccessObject', $item
    );
  }

  /**
  * @covers PapayaMediaDatabaseItem::getDatabaseAccessObject
  */
  public function testGetDatabaseAccessObject() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMediaStorageService $service */
    $service = $this->createMock(PapayaMediaStorageService::class);
    $item = new PapayaMediaDatabaseItem($service);
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMediaDatabaseItemRecord $record */
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
  public function testGetDatabaseAccessObjectImplicitCreate() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMediaStorageService $service */
    $service = $this->createMock(PapayaMediaStorageService::class);
    $item = new PapayaMediaDatabaseItem($service);
    $this->assertInstanceOf(
      PapayaMediaDatabaseItemRecord::class,
      $item->getDatabaseAccessObject()
    );
  }

  /**
  * @covers PapayaMediaDatabaseItem::load
  */
  public function testLoad() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMediaStorageService $service */
    $service = $this->createMock(PapayaMediaStorageService::class);
    $item = new PapayaMediaDatabaseItem($service);
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMediaStorageService $service */
    $service = $this->createMock(PapayaMediaStorageService::class);
    $item = new PapayaMediaDatabaseItem($service);
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMediaDatabaseItemRecord $record */
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaMediaStorageService $storage */
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
   ***********************/

  /**
   * @param array $data
   * @return PHPUnit_Framework_MockObject_MockObject|PapayaMediaDatabaseItemRecord
   */
  public function getMockRecordLoadFixture($data) {
    $record = $this->createMock(PapayaMediaDatabaseItemRecord::class);
    $record
      ->expects($this->once())
      ->method('load')
      ->will($this->returnValue(TRUE));
    $record
      ->expects($this->any())
      ->method('offsetGet')
      ->willReturnCallback(
        function($name) use ($data) {
          if (isset($data[$name])) {
            return $data[$name];
          }
          /** @noinspection PhpVoidFunctionResultUsedInspection */
          return $this->fail(sprintf('Unknown field in record "%s"', $name));
        }
      );
    return $record;
  }
}

