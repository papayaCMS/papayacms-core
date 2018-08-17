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
\Papaya\TestCase::defineConstantDefaults(
  'PAPAYA_DB_TBL_MODULES'
);

class PluginsTest extends \Papaya\TestCase {

  /**
   * @covers Plugins::createObject
   */
  public function testCreateObject() {
    $profile = new Plugins();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Application\Access $plugins */
    $plugins = $profile->createObject($application = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      \Papaya\Plugin\Loader::class,
      $plugins
    );
    $this->assertSame($application, $plugins->papaya());
  }
}
