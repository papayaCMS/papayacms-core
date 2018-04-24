<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaCacheServiceFileTest extends PapayaTestCase {

  public function tearDown() {
    $this->removeTemporaryDirectory();
  }

  public function getServiceObjectFixture($createSampleFile = FALSE) {
    $this->createTemporaryDirectory();
    $configuration = new PapayaCacheConfiguration();
    $configuration['FILESYSTEM_PATH'] = $this->_temporaryDirectory;
    if ($createSampleFile) {
      $oldMask = umask(0);
      mkdir($this->_temporaryDirectory.'/GROUP/ELEMENT/', 0777, TRUE);
      umask($oldMask);
      file_put_contents(
        $this->_temporaryDirectory.'/GROUP/ELEMENT/PARAMETERS',
        'DATA'
      );
    }
    return new PapayaCacheServiceFile($configuration);
  }

  /**
  * @covers PapayaCacheServiceFile::setConfiguration
  */
  public function testSetConfiguration() {
    $service = $this->getServiceObjectFixture();
    $this->assertAttributeSame(
      $this->_temporaryDirectory, '_cacheDirectory', $service
    );
  }

  /**
  * @covers PapayaCacheServiceFile::setConfiguration
  */
  public function testSetConfigurationWithNotifier() {
    $configuration = new PapayaCacheConfiguration();
    $configuration['FILESYSTEM_PATH'] = '/tmp';
    $configuration['FILESYSTEM_NOTIFIER_SCRIPT'] = '/foo/bar.php';
    $service = new PapayaCacheServiceFile($configuration);
    $this->assertAttributeSame(
      '/foo/bar.php', '_notifierScript', $service
    );
  }

  /**
  * @covers PapayaCacheServiceFile::verify
  */
  public function testVerifyExpectingTrue() {
    $service = $this->getServiceObjectFixture();
    $this->assertTrue($service->verify());
  }

  /**
  * @covers PapayaCacheServiceFile::verify
  */
  public function testVerifyExpectingFalse() {
    $configuration = new PapayaCacheConfiguration();
    $configuration['FILESYSTEM_PATH'] = '';
    $service = new PapayaCacheServiceFile($configuration);
    $this->assertFalse($service->verify());
  }

  /**
  * @covers PapayaCacheServiceFile::verify
  */
  public function testVerifyExpectingError() {
    $configuration = new PapayaCacheConfiguration();
    $configuration['FILESYSTEM_PATH'] = '/foo';
    $service = new PapayaCacheServiceFile($configuration);
    $this->setExpectedException('LogicException');
    $this->assertFalse($service->verify(FALSE));
  }

  /**
  * @covers PapayaCacheServiceFile::write
  * @covers PapayaCacheServiceFile::notify
  * @covers PapayaCacheServiceFile::_ensureLocalDirectory
  */
  public function testWrite() {
    $service = $this->getServiceObjectFixture();
    $this->assertSame(
      'GROUP/ELEMENT/PARAMETERS',
      $service->write('GROUP', 'ELEMENT', 'PARAMETERS', 'DATA', 30)
    );
    $this->assertFileExists($this->_temporaryDirectory.'/GROUP/ELEMENT/PARAMETERS');
  }

  /**
  * @covers PapayaCacheServiceFile::write
  * @covers PapayaCacheServiceFile::notify
  * @covers PapayaCacheServiceFile::_ensureLocalDirectory
  */
  public function testWriteTriggersNotifier() {
    $service = $this->getServiceObjectFixture();

    $path = str_replace('\\', '/', $this->_temporaryDirectory);
    $notifier = $this
      ->getMockBuilder('PapayaFileSystemChangeNotifier')
      ->disableOriginalConstructor()
      ->getMock();
    $notifier
      ->expects($this->at(0))
      ->method('notify')
      ->with(
        PapayaFileSystemChangeNotifier::ACTION_ADD,
        NULL,
        $path.'/GROUP'
      );
    $notifier
      ->expects($this->at(1))
      ->method('notify')
      ->with(
        PapayaFileSystemChangeNotifier::ACTION_ADD,
        NULL,
        $path.'/GROUP/ELEMENT'
      );
    $notifier
      ->expects($this->at(2))
      ->method('notify')
      ->with(
        PapayaFileSystemChangeNotifier::ACTION_MODIFIED,
        $path.'/GROUP/ELEMENT/PARAMETERS'
      );
    $service->notifier($notifier);

    $service->write('GROUP', 'ELEMENT', 'PARAMETERS', 'DATA', 30);
  }

  /**
  * @covers PapayaCacheServiceFile::write
  * @covers PapayaCacheServiceFile::_ensureLocalDirectory
  */
  public function testWriteOverExistingFile() {
    $service = $this->getServiceObjectFixture(TRUE);
    $this->assertSame(
      'GROUP/ELEMENT/PARAMETERS',
      $service->write('GROUP', 'ELEMENT', 'PARAMETERS', 'DATA', 30)
    );
    $this->assertFileExists($this->_temporaryDirectory.'/GROUP/ELEMENT/PARAMETERS');
  }

  /**
  * @covers PapayaCacheServiceFile::write
  * @covers PapayaCacheServiceFile::_ensureLocalDirectory
  */
  public function testWriteExpectingFailure() {
    $service = new PapayaCacheServiceFile();
    $this->assertFalse(
      $service->write('GROUP', 'ELEMENT', 'PARAMETERS', 'DATA', 30)
    );
  }

  /**
  * @covers PapayaCacheServiceFile::read
  * @covers PapayaCacheServiceFile::exists
  */
  public function testRead() {
    $service = $this->getServiceObjectFixture(TRUE);
    $this->assertSame(
      'DATA',
      $service->read('GROUP', 'ELEMENT', 'PARAMETERS', 86400)
    );
  }

  /**
  * @covers PapayaCacheServiceFile::read
  * @covers PapayaCacheServiceFile::exists
  */
  public function testReadWithInvalidConfigurationExpectingFalse() {
    $service = new PapayaCacheServiceFile();
    $this->assertFalse(
      $service->read('GROUP', 'ELEMENT', 'PARAMETERS', 1800)
    );
  }

  /**
  * @covers PapayaCacheServiceFile::exists
  */
  public function testExistsWithInvalidFile() {
    $service = $this->getServiceObjectFixture();
    $this->assertFalse(
      $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 1800)
    );
  }

  /**
  * @covers PapayaCacheServiceFile::exists
  */
  public function testExistsWithExpiredFile() {
    $service = $this->getServiceObjectFixture(TRUE);
    $yesterday = time() - 86400;
    touch(
      $this->_temporaryDirectory.'/GROUP/ELEMENT/PARAMETERS',
      $yesterday
    );
    $this->assertFalse(
      $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 1800)
    );
  }

  /**
  * @covers PapayaCacheServiceFile::exists
  */
  public function testExistsWithDeprecatedFile() {
    $service = $this->getServiceObjectFixture(TRUE);
    $yesterday = time() - 86400;
    $lastHour = time() - 3600;
    $threeMinutesAgo = time() - 180;
    touch(
      $this->_temporaryDirectory.'/GROUP/ELEMENT/PARAMETERS',
      $lastHour
    );
    $this->assertFalse(
      $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 86400, $threeMinutesAgo)
    );
  }

  /**
  * @covers PapayaCacheServiceFile::created
  */
  public function testCreated() {
    $service = $this->getServiceObjectFixture(TRUE);
    $yesterday = time() - 86400;
    $lastHour = time() - 3600;
    touch(
      $this->_temporaryDirectory.'/GROUP/ELEMENT/PARAMETERS',
      $lastHour
    );
    $this->assertEquals(
      $lastHour,
      $service->created('GROUP', 'ELEMENT', 'PARAMETERS', 86400)
    );
  }

  /**
  * @covers PapayaCacheServiceFile::created
  */
  public function testCreatedWithExpiredExpectingFalse() {
    $service = $this->getServiceObjectFixture(TRUE);
    $yesterday = time() - 86400;
    $lastHour = time() - 3600;
    $threeMinutesAgo = time() - 180;
    touch(
      $this->_temporaryDirectory.'/GROUP/ELEMENT/PARAMETERS',
      $lastHour
    );
    $this->assertFalse(
      $service->created('GROUP', 'ELEMENT', 'PARAMETERS', 86400, $threeMinutesAgo)
    );
  }


  /**
  * @covers PapayaCacheServiceFile::delete
  * @dataProvider deleteArgumentsDataProvider
  */
  public function testDelete($group, $element, $parameters) {
    $service = $this->getServiceObjectFixture(TRUE);
    $this->assertEquals(1, $service->delete($group, $element, $parameters));
  }

  /**
  * @covers PapayaCacheServiceFile::delete
  */
  public function testDeleteFileTriggersNotifier() {
    $service = $this->getServiceObjectFixture(TRUE);

    $path = str_replace('\\', '/', $this->_temporaryDirectory);
    $notifier = $this
      ->getMockBuilder('PapayaFileSystemChangeNotifier')
      ->disableOriginalConstructor()
      ->getMock();
    $notifier
      ->expects($this->once())
      ->method('notify')
      ->with(
        PapayaFileSystemChangeNotifier::ACTION_DELETED,
        $path.'/GROUP/ELEMENT/PARAMETERS'
      );
    $service->notifier($notifier);

    $this->assertEquals(1, $service->delete('GROUP', 'ELEMENT', 'PARAMETERS'));
  }

  /**
  * @covers PapayaCacheServiceFile::delete
  */
  public function testDeleteDirectoryTriggersNotifier() {
    $service = $this->getServiceObjectFixture(TRUE);

    $path = str_replace('\\', '/', $this->_temporaryDirectory);
    $notifier = $this
      ->getMockBuilder('PapayaFileSystemChangeNotifier')
      ->disableOriginalConstructor()
      ->getMock();
    $notifier
      ->expects($this->once())
      ->method('notify')
      ->with(
        PapayaFileSystemChangeNotifier::ACTION_CLEARED,
        NULL,
        $path.'/GROUP/ELEMENT/'
      );
    $service->notifier($notifier);

    $this->assertEquals(1, $service->delete('GROUP', 'ELEMENT'));
  }

  /**
  * @covers PapayaCacheServiceFile::delete
  */
  public function testInvalidateDirectoryTriggersNotifier() {
    $this->createTemporaryDirectory();
    $configuration = new PapayaCacheConfiguration();
    $configuration['FILESYSTEM_PATH'] = $this->_temporaryDirectory;
    $configuration['FILESYSTEM_DISABLE_CLEAR'] = TRUE;
    $oldMask = umask(0);
    mkdir($this->_temporaryDirectory.'/GROUP/ELEMENT/', 0777, TRUE);
    umask($oldMask);
    file_put_contents(
      $this->_temporaryDirectory.'/GROUP/ELEMENT/PARAMETERS',
      'DATA'
    );
    $service = new PapayaCacheServiceFile($configuration);

    $path = str_replace('\\', '/', $this->_temporaryDirectory);
    $notifier = $this
      ->getMockBuilder('PapayaFileSystemChangeNotifier')
      ->disableOriginalConstructor()
      ->getMock();
    $notifier
      ->expects($this->once())
      ->method('notify')
      ->with(
        PapayaFileSystemChangeNotifier::ACTION_INVALIDATED,
        NULL,
        $path.'/GROUP/ELEMENT/'
      );
    $service->notifier($notifier);

    $this->assertTrue($service->delete('GROUP', 'ELEMENT'));
    $this->assertFileExists($this->_temporaryDirectory.'/GROUP/ELEMENT/PARAMETERS');
  }

  /**
  * @covers PapayaCacheServiceFile::delete
  */
  public function testDeleteNonexistingElement() {
    $service = $this->getServiceObjectFixture(TRUE);
    $this->assertTrue($service->delete('NONEXISTING_GROUP'));
  }

  /**
  * @covers PapayaCacheServiceFile::delete
  */
  public function testDeleteWithInvalidConfiguration() {
    $service = new PapayaCacheServiceFile();
    $this->assertFalse($service->delete());
  }

  /**
  * @covers PapayaCacheServiceFile::_getCacheIdentification
  * @dataProvider getCacheIdentificationDataProvider
  */
  public function testGetCacheIdentification($group, $identifier, $parameters, $expected) {
    $service = new PapayaCacheServiceFile_TestProxy();
    $this->assertSame(
      $expected,
      $service->_getCacheIdentification($group, $identifier, $parameters)
    );
  }

  /**
  * @covers PapayaCacheServiceFile::_getCacheIdentification
  * @dataProvider getInvalidCacheIdentificationDataProvider
  */
  public function testGetCacheIdentificationExpectingError($group, $identifier, $parameters) {
    $service = new PapayaCacheServiceFile_TestProxy();
    $this->setExpectedException('InvalidArgumentException');
    $service->_getCacheIdentification($group, $identifier, $parameters);
  }

  /**
  * @covers PapayaCacheServiceFile::notifier
  */
  public function testNofifierGetAfterSet() {
    $notifier = $this
      ->getMockBuilder('PapayaFileSystemChangeNotifier')
      ->disableOriginalConstructor()
      ->getMock();

    $service = new PapayaCacheServiceFile();
    $service->notifier($notifier);
    $this->assertSame($notifier, $service->notifier());
  }

  /**
  * @covers PapayaCacheServiceFile::notifier
  */
  public function testNofifierGetImplicitCreate() {
    $configuration = new PapayaCacheConfiguration();
    $configuration['FILESYSTEM_PATH'] = '/tmp';
    $configuration['FILESYSTEM_NOTIFIER_SCRIPT'] = '/foo/bar.php';

    $service = new PapayaCacheServiceFile($configuration);
    $this->assertInstanceOf('PapayaFileSystemChangeNotifier', $service->notifier());
  }

  /**
  * @covers PapayaCacheServiceFile::notifier
  */
  public function testNofifierGetImplicitCreateWithoutNotifierScriptExpectingFalse() {
    $service = new PapayaCacheServiceFile();
    $this->assertFalse($service->notifier());
  }

  /**************************************
  * Data Providers
  **************************************/

  public static function getCacheIdentificationDataProvider() {
    return array(
      array(
        'GROUP',
        'ELEMENT',
        'PARAMETERS',
        array(
          'group' => '/GROUP',
          'element' => '/GROUP/ELEMENT',
          'file' => '/GROUP/ELEMENT/PARAMETERS',
          'identifier' => 'GROUP/ELEMENT/PARAMETERS'
        )
      ),
      array(
        'GROUP',
        'ELEMENT',
        array('PARAMETER_1', 'PARAMETER_2'),
        array(
          'group' => '/GROUP',
          'element' => '/GROUP/ELEMENT',
          'file' => '/GROUP/ELEMENT/91dc48c3332977db0b09e40ef18a9246',
          'identifier' => 'GROUP/ELEMENT/91dc48c3332977db0b09e40ef18a9246'
        )
      ),
      array(
        'GROUP',
        'ELEMENT',
        new stdClass(),
        array(
          'group' => '/GROUP',
          'element' => '/GROUP/ELEMENT',
          'file' => '/GROUP/ELEMENT/f7827bf44040a444ac855cd67adfb502',
          'identifier' => 'GROUP/ELEMENT/f7827bf44040a444ac855cd67adfb502'
        )
      )
    );
  }

  public static function getInvalidCacheIdentificationDataProvider() {
    return array(
      array(
        '',
        '',
        ''
      ),
      array(
        'GROUP',
        '',
        ''
      ),
      array(
        'GROUP',
        'ELEMENT',
        ''
      ),
      array(
        'GROUP',
        'ELEMENT',
        str_repeat('X', 256)
      )
    );
  }

  public static function decodeIdentifierDataProvider() {
    return array(
      array(
        'GROUP/ELEMENT/PARAMETERS',
        array(
          'group' => 'GROUP',
          'element' => 'ELEMENT',
          'parameters' => 'PARAMETERS'
        )
      ),
      array(
        '',
        array(
          'group' => NULL,
          'element' => NULL,
          'parameters' => NULL
        )
      )
    );
  }

  public static function deleteArgumentsDataProvider() {
    return array(
      'all' => array(NULL, NULL, NULL),
      'group' => array('GROUP', NULL, NULL),
      'element' => array('GROUP', 'ELEMENT', NULL),
      'variant string' => array('GROUP', 'ELEMENT', 'PARAMETERS')
    );
  }

  public static function ensureTrailingSlashDataProvider() {
    return array(
      array('/foo', '/foo/'),
      array('/foo\\', '/foo/'),
      array('/foo/', '/foo/')
    );
  }
}

class PapayaCacheServiceFile_TestProxy extends PapayaCacheServiceFile {

  public function _getCacheIdentification($group, $element, $parameters) {
    return parent::_getCacheIdentification($group, $element, $parameters);
  }
}
