<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaUiTokensTest extends PapayaTestCase {

  /**
  * @covers PapayaUiTokens::__construct
  */
  public function testConstructor() {
    $tokens = new PapayaUiTokens();
    $this->assertAttributeEquals(
      200, '_maximum', $tokens
    );
  }

  /**
  * @covers PapayaUiTokens::__construct
  */
  public function testConstructorWithMaximum() {
    $tokens = new PapayaUiTokens(100);
    $this->assertAttributeEquals(
      100, '_maximum', $tokens
    );
  }

  /**
  * @covers PapayaUiTokens::create
  * @covers PapayaUiTokens::storeTokens
  */
  public function testCreate() {
    $tokens = new PapayaUiTokens_TestProxy();
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
  * @covers PapayaUiTokens::create
  * @covers PapayaUiTokens::getTokenHash
  * @covers PapayaUiTokens::loadTokens
  * @covers PapayaUiTokens::storeTokens
  */
  public function testCreateIntegration() {
    $tokens = new PapayaUiTokens();
    $values = $this->getMock(
      PapayaSessionValues::class,
      array('get', 'set'),
      array($this->createMock(PapayaSession::class))
    );
    $values
      ->expects($this->once())
      ->method('get')
      ->with($this->isInstanceOf(PapayaUiTokens::class))
      ->will(
        $this->returnValue(
          array('sample_token_two' => array(NULL, 'd41d8cd98f00b204e9800998ecf8427e'))
        )
      );
    $values
      ->expects($this->once())
      ->method('set')
      ->with(
        $this->isInstanceOf(PapayaUiTokens::class),
        $this->contains(array(NULL, 'd41d8cd98f00b204e9800998ecf8427e'))
      );
    $session = $this->getMock(PapayaSession::class, array('__get', 'isActive'));
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
  * @covers PapayaUiTokens::create
  */
  public function testCreateWithoutSessionExpectingNull() {
    $tokens = new PapayaUiTokens();
    $session = $this->getMock(PapayaSession::class, array('getValue', 'setValue', 'isActive'));
    $session
      ->expects($this->any())
      ->method('isActive')
      ->will($this->returnValue(FALSE));
    $tokens->papaya($this->mockPapaya()->application(array('Session' => $session)));
    $this->assertNull($tokens->create());
  }

  /**
  * @covers PapayaUiTokens::create
  */
  public function testCreateTriggeringCleanup() {
    $tokens = new PapayaUiTokens_TestProxy(2);
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
  * @covers PapayaUiTokens::validate
  */
  public function testValidate() {
    $tokens = new PapayaUiTokens_TestProxy();
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
  * @covers PapayaUiTokens::validate
  */
  public function testValidateWithoutSessionExpectingTrue() {
    $tokens = new PapayaUiTokens();
    $session = $this->getMock(PapayaSession::class, array('getValue', 'setValue', 'isActive'));
    $session
      ->expects($this->any())
      ->method('isActive')
      ->will($this->returnValue(FALSE));
    $tokens->papaya($this->mockPapaya()->application(array('Session' => $session)));
    $this->assertTrue($tokens->validate('sample_token'));
  }

  /**
  * @covers PapayaUiTokens::validate
  */
  public function testValidateWithTime() {
    $validTime = time() + 9999;
    $tokens = new PapayaUiTokens_TestProxy();
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
  * @covers PapayaUiTokens::validate
  */
  public function testValidateWithVerification() {
    $tokens = new PapayaUiTokens_TestProxy();
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
  * @covers PapayaUiTokens::validate
  */
  public function testValidateWithInvalidTimeExpectingFalse() {
    $invalidTime = time() - 9999;
    $tokens = new PapayaUiTokens_TestProxy();
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
  * @covers PapayaUiTokens::validate
  */
  public function testValidateWithInvalidVerificationExpectingFalse() {
    $tokens = new PapayaUiTokens_TestProxy();
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
  * @covers PapayaUiTokens::validate
  */
  public function testValidateWithInvalidTokenExpectingFalse() {
    $tokens = new PapayaUiTokens_TestProxy();
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
  * @covers PapayaUiTokens::cleanup
  */
  public function testCleanupFirstItems() {
    $tokens = new PapayaUiTokens_TestProxy(2);
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
  * @covers PapayaUiTokens::cleanup
  */
  public function testCleanupOldItems() {
    $tokens = new PapayaUiTokens_TestProxy(2);
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
  * @covers  PapayaUiTokens::getVerification
  * @dataProvider provideVerificationHashesAndData
  */
  public function testVerification($expected, $for) {
    $tokens = new PapayaUiTokens_TestProxy();
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
      'string' => array('09a15e9660c1ebc6f429d818825ce0c6', 'stdClass'),
      'object' => array('09a15e9660c1ebc6f429d818825ce0c6', new stdClass()),
      'complex string' => array('e9a0aef46725a205149d6a0af38eeb3e', 'sample_stdClass'),
      'array' => array('e9a0aef46725a205149d6a0af38eeb3e', array('sample', new stdClass)),
      'array in array' => array('3ffefdbd45ffc7b445275f404f5e201e', array(array('sample'))),
    );
  }

  /**************************
  * Fixtures
  ***************************/

  public function getSessionObjectFixture($owner, $get = NULL, $set = NULL) {
    $session = $this->getMock(PapayaSession::class, array('__get', 'isActive'));
    $values = $this->getMock(PapayaSessionValues::class, array('get', 'set'), array($session));
    $session
      ->expects($this->any())
      ->method('isActive')
      ->will($this->returnValue(TRUE));
    if (!is_null($get) || !is_null($set)) {
      if (!is_null($get)) {
        $values
          ->expects($this->once())
          ->method('get')
          ->with($this->equalTo($owner))
          ->will($this->returnValue($get));
      }
      if (!is_null($set)) {
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

class PapayaUiTokens_TestProxy extends PapayaUiTokens {

  public $_tokens = NULL;

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
