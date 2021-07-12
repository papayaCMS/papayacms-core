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

namespace Papaya\CMS\Content\Page;

require_once __DIR__.'/../../../../../bootstrap.php';

class PublicationsTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\CMS\Content\Page\Publications::__construct
   * @covers \Papaya\CMS\Content\Page\Publications::load
   * @covers \Papaya\CMS\Content\Page\Publications::_compileCondition
   */
  public function testLoadWithTranslationNeeded() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
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
          'table_'.\Papaya\CMS\Content\Tables::PAGE_PUBLICATIONS,
          'table_'.\Papaya\CMS\Content\Tables::PAGE_PUBLICATION_TRANSLATIONS,
          1,
          'table_'.\Papaya\CMS\Content\Tables::PAGE_PUBLICATIONS,
          'table_'.\Papaya\CMS\Content\Tables::VIEWS,
          'table_'.\Papaya\CMS\Content\Tables::VIEW_CONFIGURATIONS,
          0,
          'table_'.\Papaya\CMS\Content\Tables::AUTHENTICATION_USERS
        )
      )
      ->will($this->returnValue($databaseResult));
    $pages = new Publications(TRUE);
    $pages->setDatabaseAccess($databaseAccess);
    $this->assertTrue($pages->load(array('time' => 123456789, 'language_id' => 1)));
  }

  /**
   * @covers \Papaya\CMS\Content\Page\Publications
   */
  public function testIsPublicExpectingTrue() {
    $pages = new Publications();
    $this->assertTrue($pages->isPublic());
  }
}
