<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaAdministrationPagesDependencySynchronizationPublicationTest extends PapayaTestCase {

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationPublication::synchronize
  * @covers PapayaAdministrationPagesDependencySynchronizationPublication::getVersionData
  */
  public function testSynchronize() {
    $action = new PapayaAdministrationPagesDependencySynchronizationPublication();
    $action->publication(
      $publication = $this->getPublicationFixture(
        array(
          'publishedFrom' => 123,
          'publishedTo' => 456,
        ),
        array(
          'version_author_id' => 'user_id',
          'version_message' => 'sync',
          'topic_change_level' => 0
        )
      )
    );
    $action->page($page = $this->getPageFixture());
    $action->version(
      $version = $this->getVersionFixture(
        array(
          'owner' => 'user_id',
          'message' => 'sync',
          'level' => 0
        )
      )
    );
    $this->assertTrue($action->synchronize(array(42), 23, array(1, 2)));
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationPublication::synchronize
  * @covers PapayaAdministrationPagesDependencySynchronizationPublication::getVersionData
  */
  public function testSynchronizeVersionDataLoadFailedExpectingFalse() {
    $action = new PapayaAdministrationPagesDependencySynchronizationPublication();
    $action->publication(
      $publication = $this->getPublicationFixture(
        array(
          'publishedFrom' => 123,
          'publishedTo' => 456,
        ),
        FALSE
      )
    );
    $this->assertFalse($action->synchronize(array(42), 23, array(1, 2)));
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationPublication::publication
  */
  public function testPublicationGetAfterSet() {
    $publication = $this->getMock('PapayaContentPagePublication');
    $action = new PapayaAdministrationPagesDependencySynchronizationPublication();
    $this->assertSame(
      $publication, $action->publication($publication)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationPublication::publication
  */
  public function testPublicationGetImplicitCreate() {
    $action = new PapayaAdministrationPagesDependencySynchronizationPublication();
    $this->assertInstanceOf(
      'PapayaContentPagePublication', $action->publication()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationPublication::page
  */
  public function testPageGetAfterSet() {
    $page = $this->getMock('PapayaContentPageWork');
    $action = new PapayaAdministrationPagesDependencySynchronizationPublication();
    $this->assertSame(
      $page, $action->page($page)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationPublication::page
  */
  public function testPageGetImplicitCreate() {
    $action = new PapayaAdministrationPagesDependencySynchronizationPublication();
    $this->assertInstanceOf(
      'PapayaContentPageWork', $action->page()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationPublication::version
  */
  public function testVersionGetAfterSet() {
    $version = $this->getMock('PapayaContentPageVersion');
    $action = new PapayaAdministrationPagesDependencySynchronizationPublication();
    $this->assertSame(
      $version, $action->version($version)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationPublication::version
  */
  public function testVersionGetImplicitCreate() {
    $action = new PapayaAdministrationPagesDependencySynchronizationPublication();
    $this->assertInstanceOf(
      'PapayaContentPageVersion', $action->version()
    );
  }

  /******************************
  * Fixtures
  ******************************/

  private function getPublicationFixture($publicationData = NULL, $latestVersionData = NULL) {
    $this->_mockData['publication'] = $publicationData;
    $publication = $this->getMock('PapayaContentPagePublication');
    $publication
      ->expects($this->once())
      ->method('load')
      ->with(23)
      ->will($this->returnValue(isset($publicationData)));
    $publication
      ->expects($this->any())
      ->method('__get')
      ->will($this->returnCallback(array($this, 'callbackPublicationData')));
    if (isset($latestVersionData)) {
      if ($latestVersionData) {
        $databaseResult = $this->getMock('PapayaDatabaseResult');
        $databaseResult
          ->expects($this->any())
          ->method('fetchRow')
          ->with(PapayaDatabaseResult::FETCH_ASSOC)
          ->will(
            $this->onConsecutiveCalls($latestVersionData, FALSE)
          );
      }
      $databaseAccess = $this
        ->getMockBuilder('PapayaDatabaseAccess')
        ->disableOriginalConstructor()
        ->setMethods(array('queryFmt'))
        ->getMock();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->will($this->returnValue(
          ($latestVersionData) ? $databaseResult : NULL)
        );
      $publication
        ->expects($this->once())
        ->method('getDatabaseAccess')
        ->will($this->returnValue($databaseAccess));
    }
    return $publication;
  }

  public function callbackPublicationData($offset) {
    return $this->_mockData['publication'][$offset];
  }

  private function getPageFixture() {
    $page = $this->getMock('PapayaContentPageWork');
    $page
      ->expects($this->once())
      ->method('load')
      ->with(42)
      ->will($this->returnValue(TRUE));
    $page
      ->expects($this->once())
      ->method('publish')
      ->with(array(1, 2), 123, 456)
      ->will($this->returnValue(TRUE));
    return $page;
  }

  private function getVersionFixture($versionData) {
    $version = $this->getMock('PapayaContentPageVersion');
    $version
      ->expects($this->exactly(2))
      ->method('__set')
      ->with($this->logicalOr('id', 'pageId'), $this->logicalOr(NULL, 42));
    $version
      ->expects($this->once())
      ->method('assign')
      ->with($versionData);
    $version
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(TRUE));
    return $version;
  }
}
