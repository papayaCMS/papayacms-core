<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaTemplateSimpleScannerTest extends PapayaTestCase {

  /**
  * @covers PapayaTemplateSimpleScanner::__construct
  */
  public function testConstructor() {
    $status = $this->getMock('PapayaTemplateSimpleScannerStatus');
    $scanner = new PapayaTemplateSimpleScanner($status);
    $this->assertAttributeSame(
      $status, '_status', $scanner
    );
  }

  /**
  * @covers PapayaTemplateSimpleScanner::scan
  * @covers PapayaTemplateSimpleScanner::_next
  */
  public function testScanWithSingleValidToken() {
    $token = $this->getTokenMockObjectFixture(6);
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

    $scanner = new PapayaTemplateSimpleScanner($status);
    $tokens = array();
    $scanner->scan($tokens, 'SAMPLE');
    $this->assertEquals(
      array($token),
      $tokens
    );
  }

  /**
  * @covers PapayaTemplateSimpleScanner::scan
  * @covers PapayaTemplateSimpleScanner::_next
  */
  public function testScanWithEndToken() {
    $token = $this->getTokenMockObjectFixture(6);
    $status = $this->getStatusMockObjectFixture(
      // getToken() returns this elements
      array($token),
      // isEndToken() returns TRUE
      TRUE
    );

    $scanner = new PapayaTemplateSimpleScanner($status);
    $tokens = array();
    $scanner->scan($tokens, 'SAMPLE');
    $this->assertEquals(
      array($token),
      $tokens
    );
  }

  /**
  * @covers PapayaTemplateSimpleScanner::scan
  * @covers PapayaTemplateSimpleScanner::_next
  */
  public function testScanWithInvalidToken() {
    $status = $this->getStatusMockObjectFixture(
      array(NULL) // getToken() returns this elements
    );
    $scanner = new PapayaTemplateSimpleScanner($status);
    $tokens = array();
    $this->setExpectedException('UnexpectedValueException');
    $scanner->scan($tokens, 'SAMPLE');
  }

  /**
  * @covers PapayaTemplateSimpleScanner::scan
  * @covers PapayaTemplateSimpleScanner::_next
  * @covers PapayaTemplateSimpleScanner::_delegate
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

    $scanner = new PapayaTemplateSimpleScanner($status);
    $tokens = array();
    $scanner->scan($tokens, 'SAMPLETEST');
    $this->assertEquals(
      array($tokenOne, $tokenTwo),
      $tokens
    );
  }

  /**
  * @covers stdClass
  * @dataProvider provideCssExamples
  */
  public function testScannerWithCssExamples($expected, $string) {
    $scanner = new PapayaTemplateSimpleScanner(new PapayaTemplateSimpleScannerStatusCss());
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

  private function getTokenMockObjectFixture($length) {
    $token = $this
      ->getMockBuilder('PapayaTemplateSimpleScannerToken')
      ->disableOriginalConstructor()
      ->getMock();
    $token
      ->expects($this->any())
      ->method('__get')
      ->will($this->returnValue($length));
    return $token;
  }

  private function getStatusMockObjectFixture($tokens, $isEndToken = NULL) {
    $status = $this->getMock('PapayaTemplateSimpleScannerStatus');
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
    if (!is_null($isEndToken)) {
      $status
        ->expects($this->any())
        ->method('isEndToken')
        ->with($this->isInstanceOf('PapayaTemplateSimpleScannerToken'))
        ->will($this->returnValue($isEndToken));
    }
    return $status;
  }

  /*****************************
  * Individual assertions
  *****************************/

  public function assertTokenListEqualsStringList($expected, $tokens) {
    $string = array();
    foreach ($tokens as $token) {
      $strings[] = (string)$token;
    }
    $this->assertEquals(
      $expected,
      $strings
    );
  }
}