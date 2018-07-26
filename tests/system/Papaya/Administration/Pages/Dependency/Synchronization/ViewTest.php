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

use Papaya\Administration\Pages\Dependency\Synchronization\View;
use Papaya\Content\Page\Translation;
use Papaya\Content\Page\Translations;
use Papaya\Content\Tables;
use Papaya\Database\Access;
use Papaya\Database\Result;

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaAdministrationPagesDependencySynchronizationViewTest extends PapayaTestCase {

  /**
  * @covers View::updateTranslations
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
          'viewId' => 33,
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
          'view_id' => 33,
          'topic_trans_modified' => 123
        ),
        array(
          'lng_id' => 1,
          'topic_id' => array(21)
        )
      )
      ->will($this->returnValue(TRUE));
    $action = new View();
    $action->translations($translations);
    $this->assertTrue($action->synchronize(array(21), 42));
  }

  /********************************
   * Fixtures
   *******************************/

  /**
   * @param array $targetRecords
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Result
   */
  private function getDatabaseAccessFixture(array $targetRecords = array()) {
    $databaseResult = $this->createMock(Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(Result::FETCH_ASSOC)
      ->will(
        call_user_func_array(
          array($this, 'onConsecutiveCalls'), $targetRecords
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('table_'.Tables::PAGE_TRANSLATIONS))
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
   * @param \Papaya\Database\Access|PHPUnit_Framework_MockObject_MockObject $databaseAccess
   * @param array $translations
   * @param Translation|PHPUnit_Framework_MockObject_MockObject|NULL $translation
   * @return PHPUnit_Framework_MockObject_MockObject|Translations
   */
  private function getTranslationsFixture(
    Access $databaseAccess,
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
            $this->getTranslationFixture(array('pageId' => 0, 'languageId' => 0))
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
}
