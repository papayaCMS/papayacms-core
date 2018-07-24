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

use Papaya\Content\Pages\Publications;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentPagesPublicationsTest extends PapayaTestCase {

  /**
  * @covers Publications::__construct
  * @covers Publications::load
  * @covers Publications::_compileCondition
  */
  public function testLoadWithTranslationNeeded() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will($this->returnValue(FALSE));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->logicalAnd(
          $this->isType('string'),
          $this->stringContains(
            "((t.published_from <= '123456789' AND t.published_to >= '123456789')"
          ),
          $this->stringContains('OR t.published_to <= t.published_from)')
        ),
        array(
          'table_'.PapayaContentTables::PAGE_PUBLICATIONS,
          'table_'.PapayaContentTables::PAGE_PUBLICATION_TRANSLATIONS,
          1,
          'table_'.PapayaContentTables::PAGE_PUBLICATIONS,
          'table_'.PapayaContentTables::VIEWS,
          'table_'.PapayaContentTables::VIEW_CONFIGURATIONS,
          0,
          'table_'.PapayaContentTables::AUTHENTICATION_USERS
        )
      )
      ->will($this->returnValue($databaseResult));
    $pages = new Publications(TRUE);
    $pages->setDatabaseAccess($databaseAccess);
    $this->assertTrue($pages->load(array('time' => 123456789, 'language_id' => 1)));
  }

  /**
  * @covers Publications
  */
  public function testIsPublicExpectingTrue() {
    $pages = new Publications();
    $this->assertTrue($pages->isPublic());
  }
}
