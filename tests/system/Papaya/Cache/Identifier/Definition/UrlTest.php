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

namespace Papaya\Cache\Identifier\Definition;

require_once __DIR__.'/../../../../../bootstrap.php';

class UrlTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Cache\Identifier\Definition\URL
   */
  public function testGetStatus() {
    $environment = $_SERVER;
    $_SERVER = array(
      'HTTPS' => 'on',
      'HTTP_HOST' => 'www.sample.tld',
      'SERVER_PORT' => 443
    );
    $definition = new URL();
    $this->assertEquals(
      array(
        URL::class => 'https://www.sample.tld/'
      ),
      $definition->getStatus()
    );
    $_SERVER = $environment;
  }

  /**
   * @covers \Papaya\Cache\Identifier\Definition\URL
   */
  public function testGetSources() {
    $definition = new URL();
    $this->assertEquals(
      \Papaya\Cache\Identifier\Definition::SOURCE_URL,
      $definition->getSources()
    );
  }
}
