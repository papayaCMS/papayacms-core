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

namespace Papaya\Application\Profile;

require_once __DIR__.'/../../../../bootstrap.php';
\PapayaTestCase::defineConstantDefaults(
  array(
    'PAPAYA_DB_TBL_SURFER',
    'PAPAYA_DB_TBL_SURFERGROUPS',
    'PAPAYA_DB_TBL_SURFERPERM',
    'PAPAYA_DB_TBL_SURFERACTIVITY',
    'PAPAYA_DB_TBL_SURFERPERMLINK',
    'PAPAYA_DB_TBL_SURFERCHANGEREQUESTS',
    'PAPAYA_DB_TBL_TOPICS'
  )
);

class SurferTest extends \PapayaTestCase {

  /**
   * @covers Surfer::createObject
   */
  public function testCreateObject() {
    $profile = new Surfer();
    $surferOne = \base_surfer::getInstance(FALSE);
    $surferTwo = $profile->createObject($application = NULL);
    $this->assertSame(
      $surferOne,
      $surferTwo
    );
  }
}
