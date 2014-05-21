<?php
require_once(dirname(__FILE__).'/../../../../../../../../../bootstrap.php');

class PapayaUiDialogFieldFactoryProfileSelectMediaFolderTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileSelectMediaFolder::createField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'mediafolder',
        'caption' => 'Folder'
      )
    );

    $profile = new PapayaUiDialogFieldFactoryProfileSelectMediaFolder();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldSelectMediaFolder', $profile->getField());
  }
}