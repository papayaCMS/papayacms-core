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

use Papaya\Cache\Configuration;
use Papaya\Cache\Service\File;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaCacheServiceFileTest extends \PapayaTestCase {

  public function tearDown() {
    $this->removeTemporaryDirectory();
  }

  public function getServiceObjectFixture($createSampleFile = FALSE) {
    $this->createTemporaryDirectory();
    $configuration = new Configuration();
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
    return new File($configuration);
  }

  /**
  * @covers File::setConfiguration
  */
  public function testSetConfiguration() {
    $service = $this->getServiceObjectFixture();
    $this->assertAttributeSame(
      $this->_temporaryDirectory, '_cacheDirectory', $service
    );
  }

  /**
  * @covers File::setConfiguration
  */
  public function testSetConfigurationWithNotifier() {
    $configuration = new Configuration();
    $configuration['FILESYSTEM_PATH'] = '/tmp';
    $configuration['FILESYSTEM_NOTIFIER_SCRIPT'] = '/foo/bar.php';
    $service = new File($configuration);
    $this->assertAttributeSame(
      '/foo/bar.php', '_notifierScript', $service
    );
  }

  /**
  * @covers File::verify
  */
  public function testVerifyExpectingTrue() {
    $service = $this->getServiceObjectFixture();
    $this->assertTrue($service->verify());
  }

  /**
  * @covers File::verify
  */
  public function testVerifyExpectingFalse() {
    $configuration = new Configuration();
    $configuration['FILESYSTEM_PATH'] = '';
    $service = new File($configuration);
    $this->assertFalse($service->verify());
  }

  /**
  * @covers File::verify
  */
  public function testVerifyExpectingError() {
    $configuration = new Configuration();
    $configuration['FILESYSTEM_PATH'] = '/foo';
    $service = new File($configuration);
    $this->expectException(LogicException::class);
    $this->assertFalse($service->verify(FALSE));
  }

  /**
  * @covers File::write
  * @covers File::notify
  * @covers File::_ensureLocalDirectory
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
  * @covers File::write
  * @covers File::notify
  * @covers File::_ensureLocalDirectory
  */
  public function testWriteTriggersNotifier() {
    $service = $this->getServiceObjectFixture();

    $path = str_replace('\\', '/', $this->_temporaryDirectory);
    $notifier = $this
      ->getMockBuilder(\PapayaFileSystemChangeNotifier::class)
      ->disableOriginalConstructor()
      ->getMock();
    $notifier
      ->expects($this->at(0))
      ->method('notify')
      ->with(
        \PapayaFileSystemChangeNotifier::ACTION_ADD,
        NULL,
        $path.'/GROUP'
      );
    $notifier
      ->expects($this->at(1))
      ->method('notify')
      ->with(
        \PapayaFileSystemChangeNotifier::ACTION_ADD,
        NULL,
        $path.'/GROUP/ELEMENT'
      );
    $notifier
      ->expects($this->at(2))
      ->method('notify')
      ->with(
        \PapayaFileSystemChangeNotifier::ACTION_MODIFIED,
        $path.'/GROUP/ELEMENT/PARAMETERS'
      );
    $service->notifier($notifier);

    $service->write('GROUP', 'ELEMENT', 'PARAMETERS', 'DATA', 30);
  }

  /**
  * @covers File::write
  * @covers File::_ensureLocalDirectory
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
  * @covers File::write
  * @covers File::_ensureLocalDirectory
  */
  public function testWriteExpectingFailure() {
    $service = new File();
    $this->assertFalse(
      $service->write('GROUP', 'ELEMENT', 'PARAMETERS', 'DATA', 30)
    );
  }

  /**
  * @covers File::read
  * @covers File::exists
  */
  public function testRead() {
    $service = $this->getServiceObjectFixture(TRUE);
    $this->assertSame(
      'DATA',
      $service->read('GROUP', 'ELEMENT', 'PARAMETERS', 86400)
    );
  }

  /**
  * @covers File::read
  * @covers File::exists
  */
  public function testReadWithInvalidConfigurationExpectingFalse() {
    $service = new File();
    $this->assertFalse(
      $service->read('GROUP', 'ELEMENT', 'PARAMETERS', 1800)
    );
  }

  /**
  * @covers File::exists
  */
  public function testExistsWithInvalidFile() {
    $service = $this->getServiceObjectFixture();
    $this->assertFalse(
      $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 1800)
    );
  }

  /**
  * @covers File::exists
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
  * @covers File::exists
  */
  public function testExistsWithDeprecatedFile() {
    $service = $this->getServiceObjectFixture(TRUE);
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
  * @covers File::created
  */
  public function testCreated() {
    $service = $this->getServiceObjectFixture(TRUE);
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
  * @covers File::created
  */
  public function testCreatedWithExpiredExpectingFalse() {
    $service = $this->getServiceObjectFixture(TRUE);
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
   * @covers File::delete
   * @dataProvider deleteArgumentsDataProvider
   * @param NULL|string $group
   * @param NULL|string $element
   * @param NULL|string $parameters
   */
  public function testDelete($group, $element, $parameters) {
    $service = $this->getServiceObjectFixture(TRUE);
    $this->assertEquals(1, $service->delete($group, $element, $parameters));
  }

  /**
  * @covers File::delete
  */
  public function testDeleteFileTriggersNotifier() {
    $service = $this->getServiceObjectFixture(TRUE);

    $path = str_replace('\\', '/', $this->_temporaryDirectory);
    $notifier = $this
      ->getMockBuilder(\PapayaFileSystemChangeNotifier::class)
      ->disableOriginalConstructor()
      ->getMock();
    $notifier
      ->expects($this->once())
      ->method('notify')
      ->with(
        \PapayaFileSystemChangeNotifier::ACTION_DELETED,
        $path.'/GROUP/ELEMENT/PARAMETERS'
      );
    $service->notifier($notifier);

    $this->assertEquals(1, $service->delete('GROUP', 'ELEMENT', 'PARAMETERS'));
  }

  /**
  * @covers File::delete
  */
  public function testDeleteDirectoryTriggersNotifier() {
    $service = $this->getServiceObjectFixture(TRUE);

    $path = str_replace('\\', '/', $this->_temporaryDirectory);
    $notifier = $this
      ->getMockBuilder(\PapayaFileSystemChangeNotifier::class)
      ->disableOriginalConstructor()
      ->getMock();
    $notifier
      ->expects($this->once())
      ->method('notify')
      ->with(
        \PapayaFileSystemChangeNotifier::ACTION_CLEARED,
        NULL,
        $path.'/GROUP/ELEMENT/'
      );
    $service->notifier($notifier);

    $this->assertEquals(1, $service->delete('GROUP', 'ELEMENT'));
  }

  /**
  * @covers File::delete
  */
  public function testInvalidateDirectoryTriggersNotifier() {
    $this->createTemporaryDirectory();
    $configuration = new Configuration();
    $configuration['FILESYSTEM_PATH'] = $this->_temporaryDirectory;
    $configuration['FILESYSTEM_DISABLE_CLEAR'] = TRUE;
    $oldMask = umask(0);
    mkdir($this->_temporaryDirectory.'/GROUP/ELEMENT/', 0777, TRUE);
    umask($oldMask);
    file_put_contents(
      $this->_temporaryDirectory.'/GROUP/ELEMENT/PARAMETERS',
      'DATA'
    );
    $service = new File($configuration);

    $path = str_replace('\\', '/', $this->_temporaryDirectory);
    $notifier = $this
      ->getMockBuilder(\PapayaFileSystemChangeNotifier::class)
      ->disableOriginalConstructor()
      ->getMock();
    $notifier
      ->expects($this->once())
      ->method('notify')
      ->with(
        \PapayaFileSystemChangeNotifier::ACTION_INVALIDATED,
        NULL,
        $path.'/GROUP/ELEMENT/'
      );
    $service->notifier($notifier);

    $this->assertTrue($service->delete('GROUP', 'ELEMENT'));
    $this->assertFileExists($this->_temporaryDirectory.'/GROUP/ELEMENT/PARAMETERS');
  }

  /**
  * @covers File::delete
  */
  public function testDeleteNonexistingElement() {
    $service = $this->getServiceObjectFixture(TRUE);
    $this->assertTrue($service->delete('NONEXISTING_GROUP'));
  }

  /**
  * @covers File::delete
  */
  public function testDeleteWithInvalidConfiguration() {
    $service = new File();
    $this->assertFalse($service->delete());
  }

  /**
   * @covers File::_getCacheIdentification
   * @dataProvider getCacheIdentificationDataProvider
   * @param string $group
   * @param string $identifier
   * @param mixed $parameters
   * @param array $expected
   */
  public function testGetCacheIdentification($group, $identifier, $parameters, $expected) {
    $service = new \PapayaCacheServiceFile_TestProxy();
    $this->assertSame(
      $expected,
      $service->_getCacheIdentification($group, $identifier, $parameters)
    );
  }

  /**
   * @covers File::_getCacheIdentification
   * @dataProvider getInvalidCacheIdentificationDataProvider
   * @param string $group
   * @param string $identifier
   * @param mixed $parameters
   */
  public function testGetCacheIdentificationExpectingError($group, $identifier, $parameters) {
    $service = new \PapayaCacheServiceFile_TestProxy();
    $this->expectException(InvalidArgumentException::class);
    $service->_getCacheIdentification($group, $identifier, $parameters);
  }

  /**
  * @covers File::notifier
  */
  public function testNotifierGetAfterSet() {
    $notifier = $this
      ->getMockBuilder(\PapayaFileSystemChangeNotifier::class)
      ->disableOriginalConstructor()
      ->getMock();

    $service = new File();
    $service->notifier($notifier);
    $this->assertSame($notifier, $service->notifier());
  }

  /**
  * @covers File::notifier
  */
  public function testNofifierGetImplicitCreate() {
    $configuration = new Configuration();
    $configuration['FILESYSTEM_PATH'] = '/tmp';
    $configuration['FILESYSTEM_NOTIFIER_SCRIPT'] = '/foo/bar.php';

    $service = new File($configuration);
    $this->assertInstanceOf(\PapayaFileSystemChangeNotifier::class, $service->notifier());
  }

  /**
  * @covers File::notifier
  */
  public function testNofifierGetImplicitCreateWithoutNotifierScriptExpectingFalse() {
    $service = new File();
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

class PapayaCacheServiceFile_TestProxy extends File {

  public function _getCacheIdentification($group, $element, $parameters) {
    return parent::_getCacheIdentification($group, $element, $parameters);
  }
}
