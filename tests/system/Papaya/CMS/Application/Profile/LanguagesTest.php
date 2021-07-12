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

namespace Papaya\CMS\Application\Profile;

require_once __DIR__.'/../../../../../bootstrap.php';

class LanguagesTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\CMS\Application\Profile\Languages::createObject
   */
  public function testCreateObject() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->will($this->returnValue(FALSE));
    $databaseManager = $this->createMock(\Papaya\Database\Manager::class);
    $databaseManager
      ->expects($this->once())
      ->method('createDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $application = $this
      ->mockPapaya()
      ->application(
        array(
          'database' => $databaseManager
        )
      );
    $profile = new Languages();
    $request = $profile->createObject($application);
    $this->assertInstanceOf(
      \Papaya\CMS\Content\Languages::class,
      $request
    );
  }
}
