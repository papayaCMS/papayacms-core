<?php
require_once __DIR__.'/../../../../../../../../bootstrap.php';

class PapayaUiDialogFieldFactoryProfileSelectFileTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileSelectFile
   */
  public function testGetField() {
    $context = $this->getMock('PapayaObjectInterface');
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
    $profile = new PapayaUiDialogFieldFactoryProfileSelectFile();
    $profile->fileSystem($this->getFileSystemFixture(array('sample.txt')));
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldSelect', $field = $profile->getField());
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileSelectFile
   */
  public function testGetFieldGetPathFromContext() {
    $context = $this->getMock('PapayaUiDialogFieldFactoryProfileSelectFile_TestContext');
    $context
      ->expects($this->once())
      ->method('getPath')
      ->will($this->returnValue('/sample/'));
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'fileselect',
        'caption' => 'File',
        'default' => '',
        'parameters' => array('callback:getPath'),
        'context' => $context
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileSelectFile();
    $profile->fileSystem($this->getFileSystemFixture(array('sample.txt')));
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldSelect', $field = $profile->getField());
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileSelectFile
   */
  public function testGetFieldWithFilter() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'fileselect',
        'caption' => 'File',
        'default' => '',
        'parameters' => array('/', '(pattern)', '/sample/')
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileSelectFile();
    $profile->fileSystem($this->getFileSystemFixture(array('sample.txt'), '(pattern)'));
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldSelect', $field = $profile->getField());
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileSelectFile
   */
  public function testGetFieldWithInvalidDirectoryExpectingMessageField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'fileselect',
        'caption' => 'File',
        'default' => '',
        'parameters' => array('/sample/')
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileSelectFile();
    $profile->fileSystem($this->getFileSystemFixture());
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldMessage', $field = $profile->getField());
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileSelectFile
   */
  public function testGetFieldGroupsValues() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'fileselect',
        'caption' => 'File',
        'mandatory' => TRUE,
        'default' => '',
        'parameters' => array('/sample/')
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileSelectFile();
    $profile->fileSystem(
      $this->getFileSystemFixture(
        array(
          'group_sample1.txt' => 'group_sample1.txt',
          'group_sample2.txt' => 'group_sample2.txt'
        )
      )
    );
    $profile->options($options);
    $this->assertXmlStringEqualsXmlString(
      '<field caption="File" class="DialogFieldSelect" error="yes" mandatory="yes">'.
        '<select name="fileselect" type="dropdown">'.
          '<group caption="group">'.
            '<option value="group_sample1.txt">group_sample1.txt</option>'.
            '<option value="group_sample2.txt">group_sample2.txt</option>'.
          '</group>'.
        '</select>'.
      '</field>',
      $profile->getField()->getXml()
    );
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileSelectFile
   */
  public function testGetFieldNotMandatoryExpectingEmptyElement() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'fileselect',
        'caption' => 'File',
        'mandatory' => FALSE,
        'default' => '',
        'parameters' => array('/sample/')
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileSelectFile();
    $profile->fileSystem(
      $this->getFileSystemFixture(
        array(
          'group_sample1.txt' => 'group_sample1.txt',
          'group_sample2.txt' => 'group_sample2.txt'
        )
      )
    );
    $profile->options($options);
    $this->assertXmlStringEqualsXmlString(
      '<field caption="File" class="DialogFieldSelect" error="no">'.
        '<select name="fileselect" type="dropdown">'.
          '<option selected="selected">none</option>'.
          '<group caption="group">'.
            '<option value="group_sample1.txt">group_sample1.txt</option>'.
            '<option value="group_sample2.txt">group_sample2.txt</option>'.
          '</group>'.
        '</select>'.
      '</field>',
      $profile->getField()->getXml()
    );
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileSelectFile::fileSystem
   */
  public function testFileSystemGetAfterSet() {
    $profile = new PapayaUiDialogFieldFactoryProfileSelectFile();
    $profile->fileSystem($fileSystem = $this->getMock('PapayaFileSystemFactory'));
    $this->assertSame($fileSystem, $profile->fileSystem());
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileSelectFile::fileSystem
   */
  public function testFileSystemGetImplicitCreate() {
    $profile = new PapayaUiDialogFieldFactoryProfileSelectFile();
    $this->assertInstanceOf('PapayaFileSystemFactory', $profile->fileSystem());
  }

  private function getFileSystemFixture(array $files = NULL, $filter = '') {
    $directory = $this
      ->getMockBuilder('PapayaFileSystemDirectory')
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
        ->with($filter, PapayaFileSystemDirectory::FETCH_FILES)
        ->will($this->returnValue(new ArrayIterator($files)));
    }
    $fileSystem = $this->getMock('PapayaFileSystemFactory');
    $fileSystem
      ->expects($this->once())
      ->method('getDirectory')
      ->with('/sample/')
      ->will($this->returnValue($directory));
    return $fileSystem;
  }
}

abstract class PapayaUiDialogFieldFactoryProfileSelectFile_TestContext extends PapayaObject {

  abstract public function getPath();
}
