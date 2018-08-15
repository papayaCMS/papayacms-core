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

namespace Papaya\Content\Page;

require_once __DIR__.'/../../../../bootstrap.php';

class VersionTest extends \PapayaTestCase {

  /**
   * @covers Version::save
   */
  public function testSaveBlockingUpdateExpectingException() {
    $version = new Version();
    /** @noinspection Annotator */
    $version->id = 42;
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('LogicException: Page versions can not be changed.');
    $version->save();
  }

  /**
   * @covers Version::save
   */
  public function testSaveInsertWhileMissingValuesExpectingException() {
    $version = new Version();
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('UnexpectedValueException: page id, owner or message are missing.');
    $version->save();
  }

  /**
   * @covers Version::save
   * @covers Version::create
   */
  public function testSave() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->any())
      ->method('lastInsertId')
      ->will($this->returnValue(42));
    $databaseAccess
      ->expects($this->exactly(2))
      ->method('queryFmtWrite')
      ->with(
        $this->isType('string'),
        $this->logicalOr(
          array('table_topic_versions', 123, 'sample user id', 'test message', 1, 'table_topic', 21),
          array('table_topic_versions_trans', 42, 'table_topic_trans', 21)
        )
      )
      ->will(
        $this->onConsecutiveCalls(1, 2)
      );

    $version = new Version();
    $version->assign(
      array(
        'page_id' => 21,
        'owner' => 'sample user id',
        'message' => 'test message',
        'created' => 123,
        'level' => 1,
      )
    );
    $version->setDatabaseAccess($databaseAccess);
    $this->assertEquals(
      42, $version->save()
    );
  }

  /**
   * @covers Version::save
   * @covers Version::create
   */
  public function testSaveWithDatabaseErrorInFirstQueryExpectingFalse() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmtWrite')
      ->with(
        $this->isType('string'),
        array('table_topic_versions', 123, 'sample user id', 'test message', 1, 'table_topic', 21)
      )
      ->will($this->returnValue(FALSE));

    $version = new Version();
    $version->assign(
      array(
        'page_id' => 21,
        'owner' => 'sample user id',
        'message' => 'test message',
        'created' => 123,
        'level' => 1,
      )
    );
    $version->setDatabaseAccess($databaseAccess);
    $this->assertFalse(
      $version->save()
    );
  }

  /**
   * @covers Version::translations
   */
  public function testTranslationsGetAfterSet() {
    $translations = $this->createMock(Version\Translations::class);
    $version = new Version();
    $this->assertSame($translations, $version->translations($translations));
  }

  /**
   * @covers Version::translations
   */
  public function testTranslationsGetWithImplicitCreate() {
    $version = new Version();
    $this->assertInstanceOf(
      Version\Translations::class,
      $version->translations()
    );
  }
}
