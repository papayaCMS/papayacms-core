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

namespace Papaya\Utility\Server;
require_once __DIR__.'/../../../../bootstrap.php';

class AgentTest extends \Papaya\TestCase {

  /**
   * @backupGlobals enabled
   * @covers \Papaya\Utility\Server\Agent::get
   */
  public function testGet() {
    $_SERVER['HTTP_USER_AGENT'] = 'Googlebot/2.1 (+http://www.google.com/bot.html)';
    $this->assertEquals(
      'Googlebot/2.1 (+http://www.google.com/bot.html)', Agent::get()
    );
  }

  /**
   * @backupGlobals enabled
   * @covers \Papaya\Utility\Server\Agent::get
   */
  public function testGetExpectingEmptyString() {
    $_SERVER['HTTP_USER_AGENT'] = '';
    $this->assertEquals(
      '', Agent::get()
    );
  }

  /**
   * @covers       \Papaya\Utility\Server\Agent::isRobot
   * @covers       \Papaya\Utility\Server\Agent::_checkAgentIsRobot
   * @covers       \Papaya\Utility\Server\Agent::_checkAgainstList
   * @dataProvider provideRobots
   * @param string $userAgent
   */
  public function testIsRobotExpectingTrue($userAgent) {
    $this->assertTrue(
      Agent::isRobot($userAgent)
    );
  }

  /**
   * @covers       \Papaya\Utility\Server\Agent::isRobot
   * @covers       \Papaya\Utility\Server\Agent::_checkAgentIsRobot
   * @covers       \Papaya\Utility\Server\Agent::_checkAgainstList
   * @dataProvider provideUserAgents
   * @param string $userAgent
   */
  public function testIsRobotExpectingFalse($userAgent) {
    $this->assertFalse(
      Agent::isRobot($userAgent)
    );
  }

  /**
   * @covers \Papaya\Utility\Server\Agent::isRobot
   */
  public function testIsRobotExpectingTrueUsingCachedResult() {
    Agent::isRobot('Googlebot/2.1 (+http://www.google.com/bot.html)');
    $this->assertTrue(
      Agent::isRobot('Googlebot/2.1 (+http://www.google.com/bot.html)')
    );
  }

  /**
   * @backupGlobals
   * @covers \Papaya\Utility\Server\Agent::isRobot
   */
  public function testIsRobotExpectingTrueReadingServerVariable() {
    $_SERVER['HTTP_USER_AGENT'] = 'Googlebot/2.1 (+http://www.google.com/bot.html)';
    $this->assertTrue(
      Agent::isRobot()
    );
  }

  /**
   * @backupGlobals enabled
   * @covers \Papaya\Utility\Server\Agent::isRobot
   */
  public function testIsRobotExpectingFalseWithEmptyUserAgent() {
    $_SERVER['HTTP_USER_AGENT'] = '';
    $this->assertFalse(
      Agent::isRobot()
    );
  }

  /***********************
   * Data Provider
   ************************/

  // @codingStandardsIgnoreStart
  public static function provideRobots() {
    return array(
      array('Googlebot/2.1 (+http://www.google.com/bot.html)'),
      array('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'),
      array('Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)'),
      array('check_http/v1.4.14 (nagios-plugins 1.4.14)'),
    );
  }

  public static function provideUserAgents() {
    return array(
      array('Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2 FirePHP/0.4'),
    );
  }
  // @codingStandardsIgnoreEnd
}
