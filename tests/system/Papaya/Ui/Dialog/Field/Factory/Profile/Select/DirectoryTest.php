<?php
require_once __DIR__.'/../../../../../../../../bootstrap.php';

class PapayaUiDialogFieldFactoryProfileSelectDirectoryTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileSelectDirectory
   */
  public function testGetField() {
    $context = $this->createMock(PapayaObjectInterface::class);
    $context
      ->expects($this->once())
      ->method('papaya')
      ->will($this->returnValue($this->mockPapaya()->application()));
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'fileselect',
        'caption' => 'File',
        'default' => '',
        'parameters' => array('/sample/'),
        'context' => $context
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileSelectDirectory();
    $profile->fileSystem($this->getFileSystemFixture(array('sample.txt')));
    $profile->options($options);
    $this->assertInstanceOf(PapayaUiDialogFieldSelect::class, $field = $profile->getField());
  }

  private function getFileSystemFixture(array $files = NULL, $filter = '') {
    $directory = $this
      ->getMockBuilder(PapayaFileSystemDirectory::class)
      ->disableOriginalConstructor()
      ->getMock();
    $directory
      ->expects($this->once())
      ->method('isReadable')
      ->will($this->returnValue(isset($files)));
    if (isset($files)) {
      $directory
        ->expects($this->once())
        ->method('getEntries')
        ->with($filter, PapayaFileSystemDirectory::FETCH_DIRECTORIES)
        ->will($this->returnValue(new ArrayIterator($files)));
    }
    $fileSystem = $this->createMock(PapayaFileSystemFactory::class);
    $fileSystem
      ->expects($this->once())
      ->method('getDirectory')
      ->with('/sample/')
      ->will($this->returnValue($directory));
    return $fileSystem;
  }
}
