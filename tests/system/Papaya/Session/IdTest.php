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

namespace Papaya\Session;
require_once __DIR__.'/../../../bootstrap.php';

class IdTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Session\Id::__construct
   */
  public function testConstructor() {
    $sid = new Id('sample');
    $this->assertAttributeEquals(
      'sample', '_name', $sid
    );
  }

  /**
   * @covers \Papaya\Session\Id::getName
   */
  public function testGetName() {
    $sid = new Id('sample');
    $this->assertEquals(
      'sample', $sid->getName()
    );
  }

  /**
   * @covers \Papaya\Session\Id::__toString
   */
  public function testMagicMethodToString() {
    $request = $this->getParameterStubFixture(
      array(
        \Papaya\Request::SOURCE_COOKIE => array('sample' => '012345678901234567890123456789ab')
      )
    );
    $sid = new Id('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertEquals(
      '012345678901234567890123456789ab', (string)$sid
    );
  }

  /**
   * @covers \Papaya\Session\Id::existsIn
   * @dataProvider provideValidParametersForExistsIn
   * @param int $source
   * @param $parameters
   */
  public function testExistsInExpectingTrue($source, $parameters) {
    $request = $this->getParameterStubFixture($parameters);
    $sid = new Id('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertTrue($sid->existsIn($source));
  }

  /**
   * @covers \Papaya\Session\Id::existsIn
   * @dataProvider provideInvalidParametersForExistsIn
   * @param int $source
   * @param array $parameters
   */
  public function testExistsInExpectingFalse($source, array $parameters) {
    $request = $this->getParameterStubFixture($parameters);
    $sid = new Id('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertFalse($sid->existsIn($source));
  }

  /**
   * @covers \Papaya\Session\Id::existsIn
   */
  public function testExistsInWithInvalidSourceExpectingFalse() {
    $request = $this->getParameterStubFixture(array());
    $sid = new Id('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertFalse($sid->existsIn(-1));
  }

  /**
   * @covers \Papaya\Session\Id::validate
   * @dataProvider provideValidSessionIds
   * @param string $sessionId
   */
  public function testValidateExpectingSessionId($sessionId) {
    $sid = new Id('sample');
    $this->assertEquals($sessionId, $sid->validate($sessionId));
  }

  /**
   * @covers \Papaya\Session\Id::validate
   * @dataProvider provideInvalidSessionIds
   * @param string $sessionId
   */
  public function testValidateExpectingNull($sessionId) {
    $sid = new Id('sample');
    $this->assertNull($sid->validate($sessionId));
  }

  /**
   * @covers \Papaya\Session\Id::getId
   * @covers \Papaya\Session\Id::_readCookie
   * @covers \Papaya\Session\Id::_isCookieUnique
   * @backupGlobals enabled
   * @dataProvider provideCookieStrings
   * @param string $cookieString
   */
  public function testGetIdFromCookie($cookieString) {
    $request = $this->getParameterStubFixture(
      array(
        \Papaya\Request::SOURCE_COOKIE => array('sample' => '012345678901234567890123456789ab')
      )
    );
    $_SERVER['HTTP_COOKIE'] = $cookieString;
    $sid = new Id('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertEquals(
      '012345678901234567890123456789ab', $sid->getId()
    );
  }

  /**
   * @covers \Papaya\Session\Id::_readCookie
   * @covers \Papaya\Session\Id::_isCookieUnique
   * @backupGlobals enabled
   * @dataProvider provideAmbiguousCookieStrings
   * @param string $cookieString
   */
  public function testGetIdWithAmbiguousCookie($cookieString) {
    $request = $this->getParameterStubFixture(
      array(
        \Papaya\Request::SOURCE_COOKIE => array('sample' => '012345678901234567890123456789ab')
      )
    );
    $_SERVER['HTTP_COOKIE'] = $cookieString;
    $sid = new Id('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertEquals(
      '', $sid->getId()
    );
  }

  /**
   * @covers \Papaya\Session\Id::_readCookie
   * @covers \Papaya\Session\Id::_isCookieUnique
   * @backupGlobals enabled
   */
  public function testGetIdWithTwoCookiesWithTheSameValue() {
    $request = $this->getParameterStubFixture(
      array(
        \Papaya\Request::SOURCE_COOKIE => array('sample' => '25b482735512613d6b61983c400bd3d9')
      )
    );
    $_SERVER['HTTP_COOKIE'] = 'sample=25b482735512613d6b61983c400bd3d9; sample=25b482735512613d6b61983c400bd3d9';
    $sid = new Id('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertEquals(
      '25b482735512613d6b61983c400bd3d9', $sid->getId()
    );
  }

  /**
   * @covers \Papaya\Session\Id::getId
   * @covers \Papaya\Session\Id::_readCookie
   * @covers \Papaya\Session\Id::_readPath
   */
  public function testGetIdFromPath() {
    $request = $this->getParameterStubFixture(
      array(
        \Papaya\Request::SOURCE_PATH => array('session' => 'sample012345678901234567890123456789ab')
      )
    );
    $sid = new Id('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertEquals(
      '012345678901234567890123456789ab', $sid->getId()
    );
  }

  /**
   * @covers \Papaya\Session\Id::getId
   * @covers \Papaya\Session\Id::_readCookie
   * @covers \Papaya\Session\Id::_readPath
   */
  public function testGetIdFromPathFallbackToDefaultName() {
    $request = $this->getParameterStubFixture(
      array(
        \Papaya\Request::SOURCE_PATH => array('session' => 'sid012345678901234567890123456789ab')
      )
    );
    $sid = new Id('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertEquals(
      '012345678901234567890123456789ab', $sid->getId()
    );
  }

  /**
   * @covers \Papaya\Session\Id::getId
   * @covers \Papaya\Session\Id::_readPath
   * @covers \Papaya\Session\Id::_readQuery
   */
  public function testGetIdFromQueryString() {
    $request = $this->getParameterStubFixture(
      array(
        \Papaya\Request::SOURCE_QUERY => array('sample' => '012345678901234567890123456789ab')
      )
    );
    $sid = new Id('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertEquals(
      '012345678901234567890123456789ab', $sid->getId()
    );
  }

  /**
   * @covers \Papaya\Session\Id::getId
   * @covers \Papaya\Session\Id::_readBody
   */
  public function testGetIdFromRequestBody() {
    $request = $this->getParameterStubFixture(
      array(
        \Papaya\Request::SOURCE_BODY => array('sample' => '012345678901234567890123456789ab')
      )
    );
    $sid = new Id('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertEquals(
      '012345678901234567890123456789ab', $sid->getId()
    );
  }

  /**
   * @covers \Papaya\Session\Id::getId
   */
  public function testGetIdFromCachedValue() {
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getParameter')
      ->withAnyParameters()
      ->will($this->returnValue('012345678901234567890123456789ab'));
    $sid = new Id('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $sid->getId();
    $this->assertEquals(
      '012345678901234567890123456789ab', $sid->getId()
    );
  }

  /**
   * @covers \Papaya\Session\Id::getId
   */
  public function testGetIdExpectingEmptyString() {
    $request = $this->getParameterStubFixture(array());
    $sid = new Id('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertEquals(
      '', $sid->getId()
    );
  }

  /***********************
   * Fixtures
   **********************/

  /**
   * @param array $parameters
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Request
   */
  public function getParameterStubFixture(array $parameters) {
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->any())
      ->method('getParameter')
      ->withAnyParameters()
      ->willReturnCallback(
        function (
          /** @noinspection PhpUnusedParameterInspection */
          $name, $default, $filter, $source
        ) use ($parameters) {
          if (isset($parameters[$source][$name])) {
            return $parameters[$source][$name];
          }
          return $default;
        }
      );
    return $request;
  }

  /***********************
   * Data Provider
   ***********************/

  public static function provideCookieStrings() {
    return array(
      'empty' => array(''),
      'same prefix' => array(
        'sample=25b482735512613d6b61983c400bd3d9; sampleadmin=e3ad802bfca87740d29ddc43c4397c44'
      ),
      'same suffix' => array(
        'sample=25b482735512613d6b61983c400bd3d9; adminsample=e3ad802bfca87740d29ddc43c4397c44'
      ),
      'other' => array(
        'foo=e3ad802bfca87740d29ddc43c4397c44'
      )
    );
  }

  public static function provideAmbiguousCookieStrings() {
    return array(
      'two cookies' => array(
        'sample=25b482735512613d6b61983c400bd3d9; sample=e3ad802bfca87740d29ddc43c4397c44'
      ),
      'three cookies, two exact duplicates' => array(
        'sample=25b482735512613d6b61983c400bd3d9; sample=25b482735512613d6b61983c400bd3d9; sample=e3ad802bfca87740d29ddc43c4397c44'
      )
    );
  }

  public static function provideValidParametersForExistsIn() {
    return array(
      'any from cookie' => array(
        Id::SOURCE_ANY,
        array(
          \Papaya\Request::SOURCE_COOKIE => array('sample' => '25b482735512613d6b61983c400bd3d9')
        )
      ),
      'any from path' => array(
        Id::SOURCE_ANY,
        array(
          \Papaya\Request::SOURCE_PATH => array('session' => 'sample25b482735512613d6b61983c400bd3d9')
        )
      ),
      'cookie' => array(
        Id::SOURCE_COOKIE,
        array(
          \Papaya\Request::SOURCE_COOKIE => array('sample' => '25b482735512613d6b61983c400bd3d9')
        )
      ),
      'path' => array(
        Id::SOURCE_PATH,
        array(
          \Papaya\Request::SOURCE_PATH => array('session' => 'sample25b482735512613d6b61983c400bd3d9')
        )
      ),
      'query' => array(
        Id::SOURCE_QUERY,
        array(
          \Papaya\Request::SOURCE_QUERY => array('sample' => '25b482735512613d6b61983c400bd3d9')
        )
      ),
      'request body' => array(
        Id::SOURCE_BODY,
        array(
          \Papaya\Request::SOURCE_BODY => array('sample' => '25b482735512613d6b61983c400bd3d9')
        )
      ),
    );
  }

  public static function provideInvalidParametersForExistsIn() {
    return array(
      'any on empty' => array(
        Id::SOURCE_ANY,
        array()
      )
    );
  }

  public static function provideValidSessionIds() {
    return array(
      'md5 - 4' => array('25b482735512613d6b61983c400bd3d9')
    );
  }

  public static function provideInvalidSessionIds() {
    return array(
      'empty' => array(''),
      array('foo')
    );
  }
}
