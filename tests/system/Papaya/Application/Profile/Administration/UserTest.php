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

namespace Papaya\Application\Profile\Administration;

require_once __DIR__.'/../../../../../bootstrap.php';
\Papaya\TestCase::defineConstantDefaults(
  'PAPAYA_DB_TBL_AUTHOPTIONS',
  'PAPAYA_DB_TBL_AUTHUSER',
  'PAPAYA_DB_TBL_AUTHGROUPS',
  'PAPAYA_DB_TBL_AUTHLINK',
  'PAPAYA_DB_TBL_AUTHPERM',
  'PAPAYA_DB_TBL_AUTHMODPERMS',
  'PAPAYA_DB_TBL_AUTHMODPERMLINKS',
  'PAPAYA_DB_TBL_SURFER'
);

class UserTest extends \Papaya\TestCase {

  /**
  * @covers \Papaya\Application\Profile\Administration\User::createObject
  */
  public function testCreateObject() {
    $options = $this->createMock(\Papaya\Configuration\Cms::class);
    $options
      ->expects($this->once())
      ->method('defineDatabaseTables');
    $application = $this
      ->mockPapaya()
      ->application(
        array('options' => $options)
      );
    $profile = new User();
    $options = $profile->createObject($application);
    $this->assertInstanceOf(
      'base_auth',
      $options
    );
  }
}
