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

class PapayaTemplateSimpleScannerTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Template\Simple\Scanner::__construct
  */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Template\Simple\Scanner\Status $status */
    $status = $this->createMock(\Papaya\Template\Simple\Scanner\Status::class);
    $scanner = new \Papaya\Template\Simple\Scanner($status);
    $this->assertAttributeSame(
      $status, '_status', $scanner
    );
  }

  /**
  * @covers \Papaya\Template\Simple\Scanner::scan
  * @covers \Papaya\Template\Simple\Scanner::_next
  */
  public function testScanWithSingleValidToken() {
    $token = $this->getTokenMockObjectFixture(6);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Template\Simple\Scanner\Status $status */
    $status = $this->getStatusMockObjectFixture(
      // getToken() returns this elements
      array($token, NULL),
      // isEndToken() returns FALSE
      FALSE
    );
    $status
      ->expects($this->once())
      ->method('getNewStatus')
      ->with($this->equalTo($token))
      ->will($this->returnValue(FALSE));

    $scanner = new \Papaya\Template\Simple\Scanner($status);
    $tokens = array();
    $scanner->scan($tokens, 'SAMPLE');
    $this->assertEquals(
      array($token),
      $tokens
    );
  }

  /**
  * @covers \Papaya\Template\Simple\Scanner::scan
  * @covers \Papaya\Template\Simple\Scanner::_next
  */
  public function testScanWithEndToken() {
    $token = $this->getTokenMockObjectFixture(6);
    $status = $this->getStatusMockObjectFixture(
      // getToken() returns this elements
      array($token),
      // isEndToken() returns TRUE
      TRUE
    );

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Template\Simple\Scanner\Status $status */
    $scanner = new \Papaya\Template\Simple\Scanner($status);
    $tokens = array();
    $scanner->scan($tokens, 'SAMPLE');
    $this->assertEquals(
      array($token),
      $tokens
    );
  }

  /**
  * @covers \Papaya\Template\Simple\Scanner::scan
  * @covers \Papaya\Template\Simple\Scanner::_next
  */
  public function testScanWithInvalidToken() {
    $status = $this->getStatusMockObjectFixture(
      array(NULL) // getToken() returns this elements
    );
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Template\Simple\Scanner\Status $status */
    $scanner = new \Papaya\Template\Simple\Scanner($status);
    $tokens = array();
    $this->expectException(UnexpectedValueException::class);
    $scanner->scan($tokens, 'SAMPLE');
  }

  /**
  * @covers \Papaya\Template\Simple\Scanner::scan
  * @covers \Papaya\Template\Simple\Scanner::_next
  * @covers \Papaya\Template\Simple\Scanner::_delegate
  */
  public function testScanWithSubStatus() {
    $tokenOne = $this->getTokenMockObjectFixture(6);
    $tokenTwo = $this->getTokenMockObjectFixture(4);
    $subStatus = $this->getStatusMockObjectFixture(
      // getToken() returns this elements
      array($tokenTwo),
      // isEndToken() returns TRUE
      TRUE
    );
    $status = $this->getStatusMockObjectFixture(
      // getToken() returns this elements
      array($tokenOne, NULL),
      // isEndToken() returns FALSE
      FALSE
    );
    $status
      ->expects($this->once())
      ->method('getNewStatus')
      ->with($this->equalTo($tokenOne))
      ->will($this->returnValue($subStatus));

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Template\Simple\Scanner\Status $status */
    $scanner = new \Papaya\Template\Simple\Scanner($status);
    $tokens = array();
    $scanner->scan($tokens, 'SAMPLETEST');
    $this->assertEquals(
      array($tokenOne, $tokenTwo),
      $tokens
    );
  }

  /**
   * @coversNothing
   * @dataProvider provideCssExamples
   * @param $expected
   * @param $string
   */
  public function testScannerWithCssExamples($expected, $string) {
    $scanner = new \Papaya\Template\Simple\Scanner(new \Papaya\Template\Simple\Scanner\Status\CSS());
    $tokens = array();
    $scanner->scan($tokens, $string);
    $this->assertTokenListEqualsStringList(
      $expected,
      $tokens
    );
  }

  /**************************
   * Data Provider
   *************************/

  public static function provideCssExamples() {
    return array(
      array(
        array(
          'TEXT@0: "div"',
          'WHITESPACE@3: " "',
          'TEXT@4: "{"',
          'WHITESPACE@5: " "',
          'TEXT@6: "margin:"',
          'WHITESPACE@13: " "',
          'TEXT@14: "1em;"',
          'WHITESPACE@18: " "',
          'TEXT@19: "}"'
        ),
        'div { margin: 1em; }'
      ),
      array(
        array(
          'TEXT@0: "div"',
          'WHITESPACE@3: " "',
          'TEXT@4: "{"',
          'WHITESPACE@5: " "',
          'TEXT@6: "margin:"',
          'WHITESPACE@13: " "',
          'VALUE_NAME@14: "/*$foo.bar.foobar*/"',
          'WHITESPACE@33: " "',
          'VALUE_DEFAULT@34: "1em"',
          'TEXT@37: ";"',
          'WHITESPACE@38: " "',
          'TEXT@39: "}"'
        ),
        'div { margin: /*$foo.bar.foobar*/ 1em; }'
      ),
      array(
        array(
          'TEXT@0: "div"',
          'WHITESPACE@3: " "',
          'TEXT@4: "{"',
          'WHITESPACE@5: " "',
          'TEXT@6: "margin:"',
          'WHITESPACE@13: " "',
          'VALUE_NAME@14: "/*$foo*/"',
          'WHITESPACE@22: " "',
          'VALUE_DEFAULT@23: "1em"',
          'WHITESPACE@26: " "',
          'VALUE_NAME@27: "/*$bar*/"',
          'VALUE_DEFAULT@35: "2em"',
          'TEXT@38: ";"',
          'WHITESPACE@39: " "',
          'TEXT@40: "}"'
        ),
        'div { margin: /*$foo*/ 1em /*$bar*/2em; }'
      ),
      array(
        array(
          'TEXT@0: "h5"',
          'WHITESPACE@2: " "',
          'TEXT@3: "{"',
          'WHITESPACE@4: " "',
          'TEXT@5: "font-family:"',
          'WHITESPACE@17: " "',
          'VALUE_NAME@18: "/*$headlines.h5.fontfamily*/"',
          'WHITESPACE@46: " "',
          'VALUE_DEFAULT@47: "Georgia"',
          'TEXT@54: ","',
          'WHITESPACE@55: " "',
          'TEXT@56: "serif;"',
          'WHITESPACE@62: " "',
          'TEXT@63: "}"'
        ),
        'h5 { font-family: /*$headlines.h5.fontfamily*/ Georgia, serif; }'
      )
    );
  }

  /******************************
   * Fixtures
   *****************************/

  /**
   * @param $length
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Template\Simple\Scanner\Token
   */
  private function getTokenMockObjectFixture($length) {
    $token = $this
      ->getMockBuilder(\Papaya\Template\Simple\Scanner\Token::class)
      ->disableOriginalConstructor()
      ->getMock();
    $token
      ->expects($this->any())
      ->method('__get')
      ->will($this->returnValue($length));
    return $token;
  }

  /**
   * @param array $tokens
   * @param bool|NULL $isEndToken
   * @return \PHPUnit_Framework_MockObject_MockObject
   */
  private function getStatusMockObjectFixture($tokens, $isEndToken = NULL) {
    $status = $this->createMock(\Papaya\Template\Simple\Scanner\Status::class);
    if (count($tokens) > 0) {
      $status
        ->expects($this->exactly(count($tokens)))
        ->method('getToken')
        ->with(
          $this->isType('string'),
          $this->isType('integer')
         )
        ->will(
          call_user_func_array(
            array($this, 'onConsecutiveCalls'),
            $tokens
          )
        );
    }
    if (NULL !== $isEndToken) {
      $status
        ->expects($this->any())
        ->method('isEndToken')
        ->with($this->isInstanceOf(\Papaya\Template\Simple\Scanner\Token::class))
        ->will($this->returnValue($isEndToken));
    }
    return $status;
  }

  /*****************************
  * Individual assertions
  *****************************/

  /**
   * @param array $expected
   * @param array $tokens
   */
  public function assertTokenListEqualsStringList($expected, array $tokens) {
    $strings = array();
    foreach ($tokens as $token) {
      $strings[] = (string)$token;
    }
    $this->assertEquals(
      $expected,
      $strings
    );
  }
}
