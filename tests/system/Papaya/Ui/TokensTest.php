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

namespace Papaya\UI {

  class TokensTest extends \PapayaTestCase {

    /**
     * @covers \Papaya\UI\Tokens::__construct
     */
    public function testConstructor() {
      $tokens = new Tokens();
      $this->assertAttributeEquals(
        200, '_maximum', $tokens
      );
    }

    /**
     * @covers \Papaya\UI\Tokens::__construct
     */
    public function testConstructorWithMaximum() {
      $tokens = new Tokens(100);
      $this->assertAttributeEquals(
        100, '_maximum', $tokens
      );
    }

    /**
     * @covers \Papaya\UI\Tokens::create
     * @covers \Papaya\UI\Tokens::storeTokens
     */
    public function testCreate() {
      $tokens = new \PapayaUiTokens_TestProxy();
      $tokens->papaya(
        $this->mockPapaya()->application(
          array(
            'Session' => $this->getSessionObjectFixture(
              $tokens, NULL, array('token_1' => array(NULL, 'd41d8cd98f00b204e9800998ecf8427e'))
            )
          )
        )
      );
      $tokens->_tokens = array();
      $this->assertEquals(
        'token_1', $tokens->create()
      );
    }

    /**
     * @covers \Papaya\UI\Tokens::create
     * @covers \Papaya\UI\Tokens::getTokenHash
     * @covers \Papaya\UI\Tokens::loadTokens
     * @covers \Papaya\UI\Tokens::storeTokens
     */
    public function testCreateIntegration() {
      $tokens = new Tokens();
      $values = $this
        ->getMockBuilder(\Papaya\Session\Values::class)
        ->setConstructorArgs(array($this->createMock(\Papaya\Session::class)))
        ->getMock();
      $values
        ->expects($this->once())
        ->method('get')
        ->with($this->isInstanceOf(Tokens::class))
        ->will(
          $this->returnValue(
            array('sample_token_two' => array(NULL, 'd41d8cd98f00b204e9800998ecf8427e'))
          )
        );
      $values
        ->expects($this->once())
        ->method('set')
        ->with(
          $this->isInstanceOf(Tokens::class),
          $this->contains(array(NULL, 'd41d8cd98f00b204e9800998ecf8427e'))
        );
      $session = $this->createMock(\Papaya\Session::class);
      $session
        ->expects($this->any())
        ->method('isActive')
        ->will($this->returnValue(TRUE));
      $session
        ->expects($this->any())
        ->method('__get')
        ->with('values')
        ->will($this->returnValue($values));
      $tokens->papaya($this->mockPapaya()->application(array('Session' => $session)));
      $this->assertRegExp(
        '(^[a-f\d]{32}$)', $tokens->create()
      );
    }

    /**
     * @covers \Papaya\UI\Tokens::create
     */
    public function testCreateWithoutSessionExpectingNull() {
      $tokens = new Tokens();
      $session = $this->createMock(\Papaya\Session::class);
      $session
        ->expects($this->any())
        ->method('isActive')
        ->will($this->returnValue(FALSE));
      $tokens->papaya($this->mockPapaya()->application(array('Session' => $session)));
      $this->assertNull($tokens->create());
    }

    /**
     * @covers \Papaya\UI\Tokens::create
     */
    public function testCreateTriggeringCleanup() {
      $tokens = new \PapayaUiTokens_TestProxy(2);
      $tokens->papaya(
        $this->mockPapaya()->application(
          array(
            'Session' => $this->getSessionObjectFixture(
              $tokens,
              NULL,
              array(
                'sample_token_two' => array(NULL, 'd41d8cd98f00b204e9800998ecf8427e'),
                'token_1' => array(NULL, 'd41d8cd98f00b204e9800998ecf8427e')
              )
            )
          )
        )
      );
      $tokens->_tokens = array(
        'sample_token_one' => array(NULL, 'd41d8cd98f00b204e9800998ecf8427e'),
        'sample_token_two' => array(NULL, 'd41d8cd98f00b204e9800998ecf8427e')
      );
      $this->assertEquals(
        'token_1', $tokens->create()
      );
    }

    /**
     * @covers \Papaya\UI\Tokens::validate
     */
    public function testValidate() {
      $tokens = new \PapayaUiTokens_TestProxy();
      $tokens->papaya(
        $this->mockPapaya()->application(
          array(
            'Session' => $this->getSessionObjectFixture(
              $tokens, NULL, array()
            )
          )
        )
      );
      $tokens->_tokens = array(
        'sample_token' => array(NULL, 'd41d8cd98f00b204e9800998ecf8427e')
      );
      $this->assertTrue($tokens->validate('sample_token'));
    }

    /**
     * @covers \Papaya\UI\Tokens::validate
     */
    public function testValidateWithoutSessionExpectingTrue() {
      $tokens = new Tokens();
      $session = $this->createMock(\Papaya\Session::class);
      $session
        ->expects($this->any())
        ->method('isActive')
        ->will($this->returnValue(FALSE));
      $tokens->papaya($this->mockPapaya()->application(array('Session' => $session)));
      $this->assertTrue($tokens->validate('sample_token'));
    }

    /**
     * @covers \Papaya\UI\Tokens::validate
     */
    public function testValidateWithTime() {
      $validTime = time() + 9999;
      $tokens = new \PapayaUiTokens_TestProxy();
      $tokens->papaya(
        $this->mockPapaya()->application(
          array(
            'Session' => $this->getSessionObjectFixture(
              $tokens, NULL, array()
            )
          )
        )
      );
      $tokens->_tokens = array(
        'sample_token' => array($validTime, 'd41d8cd98f00b204e9800998ecf8427e')
      );
      $this->assertTrue($tokens->validate('sample_token'));
    }

    /**
     * @covers \Papaya\UI\Tokens::validate
     */
    public function testValidateWithVerification() {
      $tokens = new \PapayaUiTokens_TestProxy();
      $tokens->papaya(
        $this->mockPapaya()->application(
          array(
            'Session' => $this->getSessionObjectFixture(
              $tokens, NULL, array()
            )
          )
        )
      );
      $tokens->_tokens = array(
        'sample_token' => array(NULL, 'fbad4b6f1e710ddf1a3d37106d096688')
      );
      $this->assertTrue($tokens->validate('sample_token', 'verification'));
    }

    /**
     * @covers \Papaya\UI\Tokens::validate
     */
    public function testValidateWithInvalidTimeExpectingFalse() {
      $invalidTime = time() - 9999;
      $tokens = new \PapayaUiTokens_TestProxy();
      $tokens->papaya(
        $this->mockPapaya()->application(
          array('Session' => $this->getSessionObjectFixture($tokens))
        )
      );
      $tokens->_tokens = array(
        'sample_token' => array($invalidTime, 'd41d8cd98f00b204e9800998ecf8427e')
      );
      $this->assertFalse($tokens->validate('sample_token'));
    }

    /**
     * @covers \Papaya\UI\Tokens::validate
     */
    public function testValidateWithInvalidVerificationExpectingFalse() {
      $tokens = new \PapayaUiTokens_TestProxy();
      $tokens->papaya(
        $this->mockPapaya()->application(
          array('Session' => $this->getSessionObjectFixture($tokens))
        )
      );
      $tokens->_tokens = array(
        'sample_token' => array(NULL, 'd41d8cd98f00b204e9800998ecf8427e')
      );
      $this->assertFalse($tokens->validate('sample_token', 'INVALID_VERIFICATION_DATA'));
    }

    /**
     * @covers \Papaya\UI\Tokens::validate
     */
    public function testValidateWithInvalidTokenExpectingFalse() {
      $tokens = new \PapayaUiTokens_TestProxy();
      $tokens->papaya(
        $this->mockPapaya()->application(
          array('Session' => $this->getSessionObjectFixture($tokens))
        )
      );
      $tokens->_tokens = array(
        'sample_token' => array(NULL, 'd41d8cd98f00b204e9800998ecf8427e')
      );
      $this->assertFalse($tokens->validate('non_existing_token'));
    }

    /**
     * @covers \Papaya\UI\Tokens::cleanup
     */
    public function testCleanupFirstItems() {
      $tokens = new \PapayaUiTokens_TestProxy(2);
      $tokens->_tokens = array(
        'sample_token_one' => array(NULL, 'd41d8cd98f00b204e9800998ecf8427e'),
        'sample_token_two' => array(NULL, 'd41d8cd98f00b204e9800998ecf8427e')
      );
      $tokens->cleanup();
      $this->assertAttributeEquals(
        array('sample_token_two' => array(NULL, 'd41d8cd98f00b204e9800998ecf8427e')),
        '_tokens',
        $tokens
      );
    }

    /**
     * @covers \Papaya\UI\Tokens::cleanup
     */
    public function testCleanupOldItems() {
      $tokens = new \PapayaUiTokens_TestProxy(2);
      $tokens->_tokens = array(
        'sample_token_one' => array(NULL, 'd41d8cd98f00b204e9800998ecf8427e'),
        'sample_token_two' => array(time() - 9999, 'd41d8cd98f00b204e9800998ecf8427e')
      );
      $tokens->cleanup();
      $this->assertAttributeEquals(
        array('sample_token_one' => array(NULL, 'd41d8cd98f00b204e9800998ecf8427e')),
        '_tokens',
        $tokens
      );
    }

    /**
     * @covers       \Papaya\UI\Tokens::getVerification
     * @dataProvider provideVerificationHashesAndData
     * @param string $expected
     * @param mixed $for
     */
    public function testVerification($expected, $for) {
      $tokens = new \PapayaUiTokens_TestProxy();
      $this->assertEquals(
        $expected, $tokens->getVerification($for)
      );
    }

    /**************************
     * Data Provider
     ***************************/

    public static function provideVerificationHashesAndData() {
      return array(
        'empty string' => array('d41d8cd98f00b204e9800998ecf8427e', ''),
        'string' => array('09a15e9660c1ebc6f429d818825ce0c6', \stdClass::class),
        'object' => array('09a15e9660c1ebc6f429d818825ce0c6', new \stdClass()),
        'complex string' => array('e9a0aef46725a205149d6a0af38eeb3e', 'sample_stdClass'),
        'array' => array('e9a0aef46725a205149d6a0af38eeb3e', array('sample', new \stdClass)),
        'array in array' => array('3ffefdbd45ffc7b445275f404f5e201e', array(array('sample'))),
      );
    }

    /**************************
     * Fixtures
     ***************************/

    /**
     * @param object|NULL $owner
     * @param mixed $get
     * @param mixed $set
     * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Session
     */
    public function getSessionObjectFixture($owner, $get = NULL, $set = NULL) {
      $session = $this->createMock(\Papaya\Session::class);
      $values = $this
        ->getMockBuilder(\Papaya\Session\Values::class)
        ->setConstructorArgs(array($session))
        ->getMock();
      $session
        ->expects($this->any())
        ->method('isActive')
        ->will($this->returnValue(TRUE));
      if (NULL !== $get || NULL !== $set) {
        if (NULL !== $get) {
          $values
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo($owner))
            ->will($this->returnValue($get));
        }
        if (NULL !== $set) {
          $values
            ->expects($this->once())
            ->method('set')
            ->with(
              $this->equalTo($owner),
              $this->equalTo($set)
            );
        }
        $session
          ->expects($this->any())
          ->method('__get')
          ->withAnyParameters()
          ->will($this->returnValue($values));
      }
      return $session;
    }
  }
}

namespace {

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

  require_once __DIR__.'/../../../bootstrap.php';

  class PapayaUiTokens_TestProxy extends \Papaya\UI\Tokens {

    public $_tokens;

    public $tokenNumber = 1;

    public function getTokenHash() {
      return 'token_'.($this->tokenNumber++);
    }

    public function cleanup() {
      parent::cleanup();
    }

    public function getVerification($for) {
      return parent::getVerification($for);
    }
  }
}
