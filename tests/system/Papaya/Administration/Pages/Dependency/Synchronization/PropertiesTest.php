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

use Papaya\Administration\Pages\Dependency\Synchronization\Properties;
use Papaya\Content\Page\Translation;
use Papaya\Content\Page\Translations;
use Papaya\Content\Page\Work;

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaAdministrationPagesDependencySynchronizationPropertiesTest extends PapayaTestCase {

  /**
  * @covers Properties::page
  */
  public function testPageGetAfterSet() {
    $page = $this->createMock(Work::class);
    $action = new Properties();
    $this->assertSame(
      $page, $action->page($page)
    );
  }

  /**
  * @covers Properties::page
  */
  public function testPageGetImplicitCreate() {
    $action = new Properties();
    $this->assertInstanceOf(
      Work::class, $action->page()
    );
  }

  /**
  * @covers Properties::synchronize
  * @covers Properties::updateTranslations
  * @covers Properties::updatePages
  */
  public function testSynchronizeUpdatePageAndOneTranslation() {
    $databaseAccess = $this->getDatabaseAccessFixture(
      array(
        array('topic_id' => 21, 'lng_id' => 1)
      )
    );
    $page = $this->getPageFixture(
      $databaseAccess,
      array(
        'pageId' => 42,
        'defaultLanguage' => 1,
        'linkType' => 23,
        'changeFrequency' => 50,
        'priority' => 99,
        'scheme' => 1,
        'cacheMode' => 2,
        'cacheTime' => 3600,
        'expiresMode' => 2,
        'expiresTime' => 3600
      )
    );
    $translations = $this->getTranslationsFixture(
      $databaseAccess,
      array(
        1 => array('languageId' => 1)
      ),
      $this->getTranslationFixture(
        array(
          'id' => 42,
          'languageId' => 1,
          'title' => 'Test Title',
          'metaTitle' => 'Test Meta Title',
          'metaDescription' => 'Test Meta Description',
          'metaKeywords' => 'Keyword1, Keyword2'
        )
      )
    );
    $databaseAccess
      ->expects($this->exactly(2))
      ->method('updateRecord')
      ->withConsecutive(
        array(
          'table_topic_trans',
          array(
            'meta_descr' => 'Test Meta Description',
            'meta_keywords' => 'Keyword1, Keyword2',
            'meta_title' => 'Test Meta Title',
            'topic_title' => 'Test Title'
          ),
          array(
            'lng_id' => 1,
            'topic_id' => array(21)
          ),
        ),
        array(
          'table_topic',
          array(
            'topic_mainlanguage' => 1,
            'topic_modified' => 84,
            'linktype_id' => 23,
            'topic_changefreq' => 50,
            'topic_priority' => 99,
            'topic_protocol' => 1,
            'topic_cachemode' => 2,
            'topic_cachetime' => 3600,
            'topic_expiresmode' => 2,
            'topic_expirestime' => 3600
          ),
          array(
            'topic_id' => array(21)
          )
        )
      )
      ->will($this->returnValue(TRUE));
    $action = new Properties();
    $action->page($page);
    $action->translations($translations);
    $this->assertTrue($action->synchronize(array(21), 42));
  }

  /**
  * @covers Properties::synchronize
  * @covers Properties::updateTranslations
  * @covers Properties::updatePages
  */
  public function testSynchronizeUpdatePageFailed() {
    $databaseAccess = $this->getDatabaseAccessFixture(
      array(
        array('topic_id' => 21, 'lng_id' => 1)
      )
    );
    $page = $this->getPageFixture(
      $databaseAccess,
      array(
        'id' => 42,
        'defaultLanguage' => 1,
        'linkType' => 23,
        'changeFrequency' => 50,
        'priority' => 99,
        'scheme' => 1,
        'cacheMode' => 2,
        'cacheTime' => 3600,
        'expiresMode' => 2,
        'expiresTime' => 3600
      )
    );
    $translations = $this->getTranslationsFixture(
      $databaseAccess,
      array(
        1 => array('languageId' => 1)
      ),
      $this->getTranslationFixture(
        array(
          'id' => 42,
          'languageId' => 1,
          'title' => 'Test Title',
          'metaTitle' => 'Test Meta Title',
          'metaDescription' => 'Test Meta Description',
          'metaKeywords' => 'Keyword1, Keyword2'
        )
      )
    );
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->with(
        'table_topic_trans',
        array(
          'meta_descr' => 'Test Meta Description',
          'meta_keywords' => 'Keyword1, Keyword2',
          'meta_title' => 'Test Meta Title',
          'topic_title' => 'Test Title'
        ),
        array(
          'lng_id' => 1,
          'topic_id' => array(21)
        )
      )
      ->will($this->returnValue(FALSE));
    $action = new Properties();
    $action->page($page);
    $action->translations($translations);
    $this->assertFalse($action->synchronize(array(21), 42));
  }

  /********************************
   * Fixtures
   *******************************/

  /**
   * @param array $targetRecords
   * @return PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseAccess
   */
  private function getDatabaseAccessFixture(array $targetRecords = array()) {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will(
        call_user_func_array(
          array($this, 'onConsecutiveCalls'), $targetRecords
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('table_'.PapayaContentTables::PAGE_TRANSLATIONS))
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
    $databaseAccess
      ->expects($this->any())
      ->method('getTimestamp')
      ->will($this->returnValue(84));
    return $databaseAccess;
  }

  /**
   * @param PapayaDatabaseAccess|PHPUnit_Framework_MockObject_MockObject $databaseAccess
   * @param array $translations
   * @param Translation|PHPUnit_Framework_MockObject_MockObject|NULL $translation
   * @return PHPUnit_Framework_MockObject_MockObject|Translations
   */
  private function getTranslationsFixture(
    PapayaDatabaseAccess $databaseAccess,
    array $translations = array(),
    Translation $translation = NULL
  ) {
    $result = $this->createMock(Translations::class);
    $result
      ->expects($this->once())
      ->method('load')
      ->with(42)
      ->will($this->returnValue(TRUE));
    $result
      ->expects($this->any())
      ->method('getIterator')
      ->will($this->returnValue(new ArrayIterator($translations)));
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
   * @return PHPUnit_Framework_MockObject_MockObject|Translation
   */
  private function getTranslationFixture(array $data = array()) {
    $translation = $this->createMock(Translation::class);
    $translation
      ->expects($this->any())
      ->method('__get')
      ->willReturnCallback(
        function($name) use ($data) {
          return $data[$name];
        }
      );
    return $translation;
  }

  /**
   * @param PapayaDatabaseAccess $databaseAccess
   * @param array $data
   * @return PHPUnit_Framework_MockObject_MockObject|Work
   */
  private function getPageFixture(PapayaDatabaseAccess $databaseAccess, array $data = array()) {
    $page = $this->createMock(Work::class);
    $page
      ->expects($this->any())
      ->method('load')
      ->will($this->returnValue(TRUE));
    $page
      ->expects($this->any())
      ->method('__get')
      ->willReturnCallback(
        function($name) use ($data) {
          return $data[$name];
        }
      );
    $page
      ->expects($this->any())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    return $page;
  }
}
