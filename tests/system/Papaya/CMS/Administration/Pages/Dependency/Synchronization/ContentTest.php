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

class ContentTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::translations
   */
  public function testTranslationsGetAfterSet() {
    $translations = $this->createMock(\Papaya\CMS\Content\Page\Translations::class);
    $action = new Content();
    $this->assertSame(
      $translations, $action->translations($translations)
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::translations
   */
  public function testTranslationsGetImplicitCreate() {
    $action = new Content();
    $this->assertInstanceOf(
      \Papaya\CMS\Content\Page\Translations::class, $action->translations()
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::synchronize
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::getExistingTargetTranslations
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::getMissingTargetTranslations
   */
  public function testSynchronizeWithoutAnyTranslations() {
    $action = new Content();
    $action->translations($this->getTranslationsFixture($this->getDatabaseAccessFixture()));
    $this->assertTrue($action->synchronize(array(21), 42, array(1)));
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::synchronize
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::getExistingTargetTranslations
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::getMissingTargetTranslations
   */
  public function testSynchronizeFetchLanguagesFromTranslations() {
    $action = new Content();
    $action->translations(
      $this->getTranslationsFixture(
        $this->getDatabaseAccessFixture(),
        array(
          1 => array('languageId' => 1)
        )
      )
    );
    $this->assertTrue($action->synchronize(array(21), 42));
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::synchronize
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::getExistingTargetTranslations
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::getMissingTargetTranslations
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::synchronizeTranslations
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::updateTranslations
   */
  public function testSynchronizeUpdateOneTranslation() {
    $translations = $this->getTranslationsFixture(
      $databaseAccess = $this->getDatabaseAccessFixture(
        array(
          array('topic_id' => 21, 'lng_id' => 1)
        )
      ),
      array(
        1 => array('languageId' => 1)
      ),
      $this->getTranslationFixture(
        array(
          'id' => 42,
          'languageId' => 1,
          'content' => array(),
          'modified' => 123
        )
      )
    );
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->with(
        'table_topic_trans',
        array(
          'topic_content' =>
          /** @lang XML */
            '<data version="2"/>',
          'topic_trans_modified' => 123
        ),
        array(
          'lng_id' => 1,
          'topic_id' => array(21)
        )
      )
      ->will($this->returnValue(TRUE));
    $action = new Content();
    $action->translations($translations);
    $this->assertTrue($action->synchronize(array(21), 42));
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::synchronize
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::getExistingTargetTranslations
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::getMissingTargetTranslations
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::synchronizeTranslations
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::updateTranslations
   */
  public function testSynchronizeUpdateFailed() {
    $translations = $this->getTranslationsFixture(
      $databaseAccess = $this->getDatabaseAccessFixture(
        array(
          array('topic_id' => 21, 'lng_id' => 1)
        )
      ),
      array(
        1 => array('languageId' => 1)
      ),
      $this->getTranslationFixture(
        array(
          'id' => 42,
          'languageId' => 1,
          'content' => array(),
          'modified' => 123
        )
      )
    );
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->with(
        'table_topic_trans',
        array(
          'topic_content' =>
          /** @lang XML */
            '<data version="2"/>',
          'topic_trans_modified' => 123
        ),
        array(
          'lng_id' => 1,
          'topic_id' => array(21)
        )
      )
      ->will($this->returnValue(FALSE));
    $action = new Content();
    $action->translations($translations);
    $this->assertFalse($action->synchronize(array(21), 42));
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::synchronize
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::getExistingTargetTranslations
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::getMissingTargetTranslations
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::synchronizeTranslations
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::deleteTranslations
   */
  public function testSynchronizeDeleteOneTranslation() {
    $translations = $this->getTranslationsFixture(
      $databaseAccess = $this->getDatabaseAccessFixture(
        array(
          array('topic_id' => 21, 'lng_id' => 1)
        )
      ),
      array(
        1 => array('languageId' => 1)
      ),
      $this->getTranslationFixture(
        array(
          'id' => 0,
          'languageId' => 0,
          'content' => array(),
          'modified' => 0
        )
      )
    );
    $databaseAccess
      ->expects($this->once())
      ->method('deleteRecord')
      ->with(
        'table_topic_trans',
        array(
          'lng_id' => 1,
          'topic_id' => array(21)
        )
      )
      ->will($this->returnValue(TRUE));
    $action = new Content();
    $action->translations($translations);
    $this->assertTrue($action->synchronize(array(21), 42));
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::synchronize
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::getExistingTargetTranslations
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::getMissingTargetTranslations
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::synchronizeTranslations
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::insertTranslations
   */
  public function testSynchronizeInsertOneTranslation() {
    $key = $this->createMock(\Papaya\Database\Interfaces\Key::class);
    $key
      ->expects($this->once())
      ->method('clear');
    $translations = $this->getTranslationsFixture(
      $databaseAccess = $this->getDatabaseAccessFixture(),
      array(
        1 => array('languageId' => 1)
      ),
      $translation = $this->getTranslationFixture(
        array(
          'id' => 42,
          'languageId' => 1,
          'content' => array(),
          'modified' => 123
        )
      )
    );
    $translation
      ->expects($this->once())
      ->method('__set')
      ->with('id', '21');
    $translation
      ->expects($this->once())
      ->method('key')
      ->will($this->returnValue($key));
    $translation
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(TRUE));
    $action = new Content();
    $action->translations($translations);
    $this->assertTrue($action->synchronize(array(21), 42));
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::synchronize
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::getExistingTargetTranslations
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::getMissingTargetTranslations
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::synchronizeTranslations
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Synchronization\Content::insertTranslations
   */
  public function testSynchronizeInsertOneTranslationInsertFailed() {
    $key = $this->createMock(\Papaya\Database\Interfaces\Key::class);
    $key
      ->expects($this->once())
      ->method('clear');
    $translations = $this->getTranslationsFixture(
      $databaseAccess = $this->getDatabaseAccessFixture(),
      array(
        1 => array('languageId' => 1)
      ),
      $translation = $this->getTranslationFixture(
        array(
          'id' => 42,
          'languageId' => 1,
          'content' => array(),
          'modified' => 123
        )
      )
    );
    $translation
      ->expects($this->once())
      ->method('__set')
      ->with('id', '21');
    $translation
      ->expects($this->once())
      ->method('key')
      ->will($this->returnValue($key));
    $translation
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(FALSE));
    $action = new Content();
    $action->translations($translations);
    $this->assertFalse($action->synchronize(array(21), 42));
  }

  /********************************
   * Fixtures
   *******************************/

  /**
   * @param array $targetRecords
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Access
   */
  private function getDatabaseAccessFixture(array $targetRecords = array()) {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(\Papaya\Database\Result::FETCH_ASSOC)
      ->will(
        call_user_func_array(
          array($this, 'onConsecutiveCalls'), $targetRecords
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('table_'.\Papaya\CMS\Content\Tables::PAGE_TRANSLATIONS))
      ->will($this->returnValue($databaseResult));
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(
        array(
          'topic_id' => array(21),
          'lng_id' => array(1)
        )
      )
      ->will($this->returnValue('__FILTER__'));
    return $databaseAccess;
  }

  /**
   * @param \Papaya\Database\Access|\PHPUnit_Framework_MockObject_MockObject $databaseAccess
   * @param array $translations
   * @param \Papaya\CMS\Content\Page\Translation|\PHPUnit_Framework_MockObject_MockObject $translation
   * @return \PHPUnit_Framework_MockObject_MockObject
   */
  private function getTranslationsFixture(
    \Papaya\Database\Access $databaseAccess,
    array $translations = array(),
    \Papaya\CMS\Content\Page\Translation $translation = NULL
  ) {
    $result = $this->createMock(\Papaya\CMS\Content\Page\Translations::class);
    $result
      ->expects($this->once())
      ->method('load')
      ->with(42)
      ->will($this->returnValue(TRUE));
    $result
      ->expects($this->any())
      ->method('getIterator')
      ->will($this->returnValue(new \ArrayIterator($translations)));
    $result
      ->expects($this->any())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    if (NULL !== $translation) {
      $result
        ->expects($this->once())
        ->method('getTranslation')
        ->with(42, 1)
        ->will($this->returnValue($translation));
      $translation
        ->expects($this->any())
        ->method('getDatabaseAccess')
        ->will($this->returnValue($databaseAccess));
    } else {
      $result
        ->expects($this->any())
        ->method('getTranslation')
        ->withAnyParameters()
        ->will(
          $this->returnValue(
            $this->getTranslationFixture(array('id' => 0, 'languageId' => 0))
          )
        );
    }
    return $result;
  }

  /**
   * @param array $data
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Content\Page\Translation
   */
  private function getTranslationFixture(array $data = array()) {
    $translation = $this->createMock(\Papaya\CMS\Content\Page\Translation::class);
    $translation
      ->expects($this->any())
      ->method('__get')
      ->willReturnCallback(
        function ($name) use ($data) {
          return $data[$name];
        }
      );
    return $translation;
  }
}
