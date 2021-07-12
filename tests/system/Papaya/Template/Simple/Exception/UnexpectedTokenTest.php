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

namespace Papaya\Template\Simple\Exception;
require_once __DIR__.'/../../../../../bootstrap.php';

class UnexpectedTokenTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Template\Simple\Exception\UnexpectedToken::__construct
   */
  public function testConstructor() {
    $expectedToken = new \Papaya\Template\Simple\Scanner\Token(
      \Papaya\Template\Simple\Scanner\Token::TEXT, 42, 'sample'
    );
    $e = new UnexpectedToken(
      $expectedToken, array(\Papaya\Template\Simple\Scanner\Token::VALUE_NAME)
    );
    $this->assertEquals(
      $expectedToken, $e->encounteredToken
    );
    $this->assertEquals(
      array(\Papaya\Template\Simple\Scanner\Token::VALUE_NAME), $e->expectedTokens
    );
    $this->assertEquals(
      'Parse error: Found TEXT@42: "sample" while one of VALUE_NAME was expected.',
      $e->getMessage()
    );
  }
}
