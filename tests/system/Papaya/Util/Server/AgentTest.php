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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUtilServerAgentTest extends \PapayaTestCase {

  /**
  * @backupGlobals enabled
  * @covers \PapayaUtilServerAgent::get
  */
  public function testGet() {
    $_SERVER['HTTP_USER_AGENT'] = 'Googlebot/2.1 (+http://www.google.com/bot.html)';
    $this->assertEquals(
      'Googlebot/2.1 (+http://www.google.com/bot.html)', \PapayaUtilServerAgent::get()
    );
  }

  /**
  * @backupGlobals enabled
  * @covers \PapayaUtilServerAgent::get
  */
  public function testGetExpectingEmptyString() {
    $_SERVER['HTTP_USER_AGENT'] = '';
    $this->assertEquals(
      '', \PapayaUtilServerAgent::get()
    );
  }

  /**
   * @covers \PapayaUtilServerAgent::isRobot
   * @covers \PapayaUtilServerAgent::_checkAgentIsRobot
   * @covers \PapayaUtilServerAgent::_checkAgainstList
   * @dataProvider provideRobots
   * @param string $userAgent
   */
  public function testIsRobotExpectingTrue($userAgent) {
    $this->assertTrue(
      \PapayaUtilServerAgent::isRobot($userAgent)
    );
  }

  /**
   * @covers \PapayaUtilServerAgent::isRobot
   * @covers \PapayaUtilServerAgent::_checkAgentIsRobot
   * @covers \PapayaUtilServerAgent::_checkAgainstList
   * @dataProvider provideUserAgents
   * @param string $userAgent
   */
  public function testIsRobotExpectingFalse($userAgent) {
    $this->assertFalse(
      \PapayaUtilServerAgent::isRobot($userAgent)
    );
  }

  /**
  * @covers \PapayaUtilServerAgent::isRobot
  */
  public function testIsRobotExpectingTrueUsingCachedResult() {
    \PapayaUtilServerAgent::isRobot('Googlebot/2.1 (+http://www.google.com/bot.html)');
    $this->assertTrue(
      \PapayaUtilServerAgent::isRobot('Googlebot/2.1 (+http://www.google.com/bot.html)')
    );
  }

  /**
  * @backupGlobals
  * @covers \PapayaUtilServerAgent::isRobot
  */
  public function testIsRobotExpectingTrueReadingServerVariable() {
    $_SERVER['HTTP_USER_AGENT'] = 'Googlebot/2.1 (+http://www.google.com/bot.html)';
    $this->assertTrue(
      \PapayaUtilServerAgent::isRobot()
    );
  }

  /**
  * @backupGlobals enabled
  * @covers \PapayaUtilServerAgent::isRobot
  */
  public function testIsRobotExpectingFalseWithEmptyUserAgent() {
    $_SERVER['HTTP_USER_AGENT'] = '';
    $this->assertFalse(
      \PapayaUtilServerAgent::isRobot()
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
