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

use Papaya\Application\Profile\Plugins;

require_once __DIR__.'/../../../../bootstrap.php';
PapayaTestCase::defineConstantDefaults(
  'PAPAYA_DB_TBL_MODULES'
);

class PapayaApplicationProfilePluginsTest extends PapayaTestCase {

  /**
  * @covers Plugins::createObject
  */
  public function testCreateObject() {
    $profile = new Plugins();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaObjectInterface $plugins */
    $plugins = $profile->createObject($application = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      PapayaPluginLoader::class,
      $plugins
    );
    $this->assertSame($application, $plugins->papaya());
  }
}
