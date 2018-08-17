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

namespace Papaya\Controller {

  require_once __DIR__.'/../../../bootstrap.php';
  \Papaya\TestCase::defineConstantDefaults(
    'PAPAYA_DB_TBL_MEDIADB_FILES',
    'PAPAYA_DB_TBL_MEDIADB_FILES_DERIVATIONS',
    'PAPAYA_DB_TBL_MEDIADB_FILES_TRANS',
    'PAPAYA_DB_TBL_MEDIADB_FILES_VERSIONS',
    'PAPAYA_DB_TBL_MEDIADB_FOLDERS',
    'PAPAYA_DB_TBL_MEDIADB_FOLDERS_TRANS',
    'PAPAYA_DB_TBL_MEDIADB_FOLDERS_PERMISSIONS',
    'PAPAYA_DB_TBL_MEDIADB_MIMEGROUPS',
    'PAPAYA_DB_TBL_MEDIADB_MIMEGROUPS_TRANS',
    'PAPAYA_DB_TBL_MEDIADB_MIMETYPES',
    'PAPAYA_DB_TBL_MEDIADB_MIMETYPES_EXTENSIONS',
    'PAPAYA_DB_TBL_TAG_LINKS',
    'PAPAYA_DB_TBL_SURFER',
    'PAPAYA_PATH_MEDIAFILES',
    'PAPAYA_PATH_THUMBFILES',
    'PAPAYA_MEDIADB_SUBDIRECTORIES'
  );

  class PapayaControllerMediaTest extends \Papaya\TestCase {

    /**
     * @covers Media::execute
     */
    public function testExecuteNoMediaFound() {
      $application = $this->mockPapaya()->application();
      $request = $this->mockPapaya()->request();
      $response = $this->mockPapaya()->response();
      $controller = new Media();
      $this->assertInstanceOf(
        Error::class,
        $controller->execute($application, $request, $response)
      );
    }

    /**
     * @covers Media::execute
     */
    public function testExecuteNonExistentMediaFile() {
      $application = $this->mockPapaya()->application();
      $request = $this->mockPapaya()->request(
        array(
          'media_id' => 'sample'
        )
      );
      $response = $this->mockPapaya()->response();

      /** @var \PHPUnit_Framework_MockObject_MockObject|\base_mediadb $generator */
      $generator = $this->createMock(\base_mediadb::class);
      $generator
        ->expects($this->once())
        ->method('getFile')
        ->will($this->returnValue(FALSE));

      $controller = new Media();
      $controller->setMediaDatabase($generator);

      $this->assertInstanceOf(
        Error::class,
        $controller->execute($application, $request, $response)
      );
    }

    /**
     * @dataProvider trueFalseDataProvider
     * @covers       Media::execute
     * @param bool $enablePreview
     */
    public function testExecute($enablePreview) {
      $application = $this->mockPapaya()->application();
      $request = $this->mockPapaya()->request(
        array(
          'preview' => $enablePreview,
          'media_id' => 'sample'
        )
      );
      $response = $this->mockPapaya()->response();

      /** @var \PHPUnit_Framework_MockObject_MockObject|\base_mediadb $generator */
      $generator = $this->createMock(\base_mediadb::class);
      $generator
        ->expects($this->once())
        ->method('getFile')
        ->will($this->returnValue(TRUE));

      $controller = new Media_TestProxy();
      $controller->setMediaDatabase($generator);
      $this->assertTrue(
        $controller->execute($application, $request, $response)
      );
    }

    /**
     * @covers Media::setMediaDatabase
     */
    public function testSetMediaDatabase() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|\base_mediadb $generator */
      $generator = $this->createMock(\base_mediadb::class);
      $controller = new Media();
      $controller->setMediaDatabase($generator);
      $this->assertAttributeSame(
        $generator, '_mediaDatabase', $controller
      );
    }

    /**
     * @covers Media::getMediaDatabase
     */
    public function testGetMediaDatabase() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|\base_mediadb $generator */
      $generator = $this->createMock(\base_mediadb::class);
      $controller = new Media();
      $controller->setMediaDatabase($generator);
      $this->assertSame(
        $generator,
        $controller->getMediaDatabase()
      );
    }

    /**
     * @covers Media::getMediaDatabase
     */
    public function testGetMediaDatabaseImplicitCreate() {
      $controller = new Media();
      $this->assertInstanceOf(
        \base_mediadb::class,
        $controller->getMediaDatabase()
      );
    }

    /**
     * @covers Media::_outputPublicFile
     */
    public function testOutputPublicFileWithFolderPermissions() {
      $surfer = $this->getMockBuilder(\base_surfer::class)->getMock();
      $surfer
        ->expects($this->once())
        ->method('hasOnePermOf')
        ->will($this->returnValue(TRUE));

      $application = $this->mockPapaya()->application(array('Surfer' => $surfer));

      /** @var \PHPUnit_Framework_MockObject_MockObject|\base_mediadb $generator */
      $generator = $this->createMock(\base_mediadb::class);
      $generator
        ->expects($this->once())
        ->method('getFolderPermissions')
        ->will(
          $this->returnValue(
            array(
              'surfer_view' => array(),
              'surfer_edit' => array(),
            )
          )
        );

      $controller = new Files_TestProxy;
      $controller->papaya($application);
      $controller->setMediaDatabase($generator);

      $controller->_outputPublicFile(array('folder_id' => 123));
    }

    /**
     * @covers Media::_outputPublicFile
     */
    public function testOutputPublicFile() {
      $surfer = $this->getMockBuilder(\base_surfer::class)->getMock();
      $surfer
        ->expects($this->once())
        ->method('hasOnePermOf')
        ->will($this->returnValue(FALSE));

      $application = $this->mockPapaya()->application(array('Surfer' => $surfer));

      /** @var \PHPUnit_Framework_MockObject_MockObject|\base_mediadb $generator */
      $generator = $this->createMock(\base_mediadb::class);
      $generator
        ->expects($this->once())
        ->method('getFolderPermissions')
        ->will(
          $this->returnValue(
            array(
              'surfer_view' => array(),
              'surfer_edit' => array(),
            )
          )
        );

      $controller = new Files_TestProxy;
      $controller->papaya($application);
      $controller->setMediaDatabase($generator);

      $this->assertFalse($controller->_outputPublicFile(array('folder_id' => 123)));
    }

    /**
     * @covers Media::_outputPublicFile
     */
    public function testOutputPublicFileWithoutFolderPermissions() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|\base_mediadb $generator */
      $generator = $this->createMock(\base_mediadb::class);
      $generator
        ->expects($this->once())
        ->method('getFolderPermissions')
        ->will($this->returnValue(array()));

      $controller = new Files_TestProxy;
      $controller->setMediaDatabase($generator);

      $this->assertTrue($controller->_outputPublicFile(array('folder_id' => 123)));
    }

    /**
     * @dataProvider trueFalseDataProvider
     * @covers       Media::_outputPreviewFile
     * @param bool $userValid
     */
    public function testOutputPreviewFile($userValid) {
      $controller = new Files_TestProxy;
      $controller->papaya(
        $this->mockPapaya()->application(
          array(
            'AdministrationUser' => $this->mockPapaya()->user($userValid)
          )
        )
      );
      $this->assertEquals($userValid, $controller->_outputPreviewFile(array()));
    }

    /**
     * @covers Media::_outputFile
     */
    public function testOutputFile() {
      $this->markTestSkipped('Request on a static function not mockable.');
    }

    /***************************************************************************/
    /** Dataprovider                                                          **/
    /***************************************************************************/

    public static function trueFalseDataProvider() {
      return array(
        array(TRUE),
        array(FALSE),
      );
    }

  }

  class Media_TestProxy extends Media {
    public function _outputPreviewFile($file) {
      return TRUE;
    }

    public function _outputPublicFile($file) {
      return TRUE;
    }
  }

  class Files_TestProxy extends Media {
    public function _outputPublicFile($file) {
      return parent::_outputPublicFile($file);
    }

    public function _outputPreviewFile($file) {
      return parent::_outputPreviewFile($file);
    }

    public function _outputFile($file) {
      return TRUE;
    }
  }

  class File_TestProxy extends Media {
    public function _outputFile($file) {
      /** @noinspection PhpVoidFunctionResultUsedInspection */
      return parent::_outputFile($file);
    }
  }
}
