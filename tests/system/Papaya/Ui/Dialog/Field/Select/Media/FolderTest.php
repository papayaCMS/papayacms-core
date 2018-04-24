<?php
require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaUiDialogFieldSelectMediaFolderTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldSelectMediaFolder::__construct
  */
  public function testConstructor() {
    $select = new PapayaUiDialogFieldSelectMediaFolder(
      'Caption', 'name'
    );
    $this->assertEquals(
      'Caption', $select->getCaption()
    );
    $this->assertEquals(
      'name', $select->getName()
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelectMediaFolder::mediaFolders
  */
  public function testMediaFoldersGetAfterSet() {
    $select = new PapayaUiDialogFieldSelectMediaFolder(
      'Caption', 'name'
    );
    $select->mediaFolders(
      $mediaFolders = $this->createMock(PapayaContentMediaFolders::class)
    );
    $this->assertSame($mediaFolders, $select->mediaFolders());
  }

  /**
  * @covers PapayaUiDialogFieldSelectMediaFolder::mediaFolders
  */
  public function testMediaFoldersGetImplicitCreate() {
    $select = new PapayaUiDialogFieldSelectMediaFolder(
      'Caption', 'name'
    );
    $this->assertInstanceOf('PapayaContentMediaFolders', $select->mediaFolders());
  }

  /**
  * @covers PapayaUiDialogFieldSelectMediaFolder::appendTo
  */
  public function testAppendTo() {
    $select = new PapayaUiDialogFieldSelectMediaFolder(
      'Caption', 'name'
    );
    $select->mediaFolders($this->getMediaFoldersFixture());
    $select->papaya($this->mockPapaya()->application());
    $select->setDefaultValue(42);
    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldSelectMediaFolder" error="no">'.
        '<select name="name" type="dropdown">'.
          '<option value="21">Folder 21</option>'.
          '<option value="42" selected="selected">-&gt;Folder 42</option>'.
          '<option value="84">  -&gt;Folder 84</option>'.
        '</select>'.
      '</field>',
      $select->getXml()
    );
  }

  private function getMediaFoldersFixture() {
    $folders = new PapayaIteratorTreeChildren(
      array(
        '21' => array('id' => 21, 'title' => 'Folder 21'),
        '42' => array('id' => 42, 'title' => 'Folder 42'),
        '84' => array('id' => 84, 'title' => 'Folder 84')
      ),
      array(
        0 => array(21),
        21 => array(42),
        42 => array(84)
      )
    );

    $mediaFolders = $this->createMock(PapayaContentMediaFolders::class);
    $mediaFolders
      ->expects($this->once())#
      ->method('getIterator')
      ->will($this->returnValue($folders));
    $mediaFolders
      ->expects($this->any())#
      ->method('offsetExists')
      ->with(42)
      ->will($this->returnValue(TRUE));
    return $mediaFolders;
  }
}
