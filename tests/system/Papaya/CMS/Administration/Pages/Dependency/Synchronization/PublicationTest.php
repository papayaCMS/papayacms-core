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

namespace Papaya\CMS\Administration\Pages\Dependency\Synchronization;

require_once __DIR__.'/../../../../../../../bootstrap.php';

class PublicationTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Publication::synchronize
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Publication::getVersionData
   */
  public function testSynchronize() {
    $action = new Publication();
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
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Publication::synchronize
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Publication::getVersionData
   */
  public function testSynchronizeVersionDataLoadFailedExpectingFalse() {
    $action = new Publication();
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
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Publication::publication
   */
  public function testPublicationGetAfterSet() {
    $publication = $this->createMock(\Papaya\CMS\Content\Page\Publication::class);
    $action = new Publication();
    $this->assertSame(
      $publication, $action->publication($publication)
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Publication::publication
   */
  public function testPublicationGetImplicitCreate() {
    $action = new Publication();
    $this->assertInstanceOf(
      \Papaya\CMS\Content\Page\Publication::class, $action->publication()
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Publication::page
   */
  public function testPageGetAfterSet() {
    $page = $this->createMock(\Papaya\CMS\Content\Page\Work::class);
    $action = new Publication();
    $this->assertSame(
      $page, $action->page($page)
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Publication::page
   */
  public function testPageGetImplicitCreate() {
    $action = new Publication();
    $this->assertInstanceOf(
      \Papaya\CMS\Content\Page\Work::class, $action->page()
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Publication::version
   */
  public function testVersionGetAfterSet() {
    $version = $this->createMock(\Papaya\CMS\Content\Page\Version::class);
    $action = new Publication();
    $this->assertSame(
      $version, $action->version($version)
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Publication::version
   */
  public function testVersionGetImplicitCreate() {
    $action = new Publication();
    $this->assertInstanceOf(
      \Papaya\CMS\Content\Page\Version::class, $action->version()
    );
  }

  /******************************
   * Fixtures
   ******************************/

  /**
   * @param array $publicationData
   * @param array $latestVersionData
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Content\Page\Publication
   */
  private function getPublicationFixture($publicationData = NULL, $latestVersionData = NULL) {
    $publication = $this->createMock(\Papaya\CMS\Content\Page\Publication::class);
    $publication
      ->expects($this->once())
      ->method('load')
      ->with(23)
      ->will($this->returnValue(NULL !== $publicationData));
    $publication
      ->expects($this->any())
      ->method('__get')
      ->willReturnCallback(
        function ($name) use ($publicationData) {
          return $publicationData[$name];
        }
      );
    if (NULL !== $latestVersionData) {
      $databaseResult = NULL;
      if ($latestVersionData) {
        $databaseResult = $this->createMock(\Papaya\Database\Result::class);
        $databaseResult
          ->expects($this->any())
          ->method('fetchRow')
          ->with(\Papaya\Database\Result::FETCH_ASSOC)
          ->will(
            $this->onConsecutiveCalls($latestVersionData, FALSE)
          );
      }
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->will($this->returnValue($databaseResult));
      $publication
        ->expects($this->once())
        ->method('getDatabaseAccess')
        ->will($this->returnValue($databaseAccess));
    }
    return $publication;
  }

  /**
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Content\Page\Work
   */
  private function getPageFixture() {
    $page = $this->createMock(\Papaya\CMS\Content\Page\Work::class);
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

  /**
   * @param $versionData
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Content\Page\Version
   */
  private function getVersionFixture($versionData) {
    $version = $this->createMock(\Papaya\CMS\Content\Page\Version::class);
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
