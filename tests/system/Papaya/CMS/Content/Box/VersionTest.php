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

namespace Papaya\CMS\Content\Box;

require_once __DIR__.'/../../../../../bootstrap.php';

class VersionTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Content\Box\Version::save
   */
  public function testSaveBlockingUpdateExpectingException() {
    $version = new Version();
    /** @noinspection PhpUndefinedFieldInspection */
    $version->id = 42;
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('LogicException: Box versions can not be changed.');
    $version->save();
  }

  /**
   * @covers \Papaya\CMS\Content\Box\Version::save
   */
  public function testSaveInsertWhileMissingValuesExcpectingException() {
    $version = new Version();
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('UnexpectedValueException: box id, owner or message are missing.');
    $version->save();
  }

  /**
   * @covers \Papaya\CMS\Content\Box\Version::save
   * @covers \Papaya\CMS\Content\Box\Version::create
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
          array('table_box_versions', 123, 'sample user id', 'test message', 'table_box', 21),
          array('table_box_versions_trans', 42, 'table_box_trans', 21)
        )
      )
      ->will(
        $this->onConsecutiveCalls(42, TRUE)
      );

    $version = new Version();
    $version->assign(
      array(
        'box_id' => 21,
        'owner' => 'sample user id',
        'message' => 'test message',
        'created' => 123
      )
    );
    $version->setDatabaseAccess($databaseAccess);
    $this->assertEquals(
      42, $version->save()
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Box\Version::save
   * @covers \Papaya\CMS\Content\Box\Version::create
   */
  public function testSaveWithDatabaseErrorInFirstQueryExpectingFalse() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmtWrite')
      ->with(
        $this->isType('string'),
        $this->logicalOr(
          array('table_box_versions', 123, 'sample user id', 'test message', 'table_box', 21)
        )
      )
      ->will($this->returnValue(FALSE));

    $version = new Version();
    $version->assign(
      array(
        'box_id' => 21,
        'owner' => 'sample user id',
        'message' => 'test message',
        'created' => 123
      )
    );
    $version->setDatabaseAccess($databaseAccess);
    $this->assertFalse(
      $version->save()
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Box\Version::translations
   */
  public function testTranslationsGetAfterSet() {
    $translations = $this->createMock(Version\Translations::class);
    $version = new Version();
    $this->assertSame($translations, $version->translations($translations));
  }

  /**
   * @covers \Papaya\CMS\Content\Box\Version::translations
   */
  public function testTranslationsGetWithImplicitCreate() {
    $version = new Version();
    $this->assertInstanceOf(
      Version\Translations::class,
      $version->translations()
    );
  }
}
