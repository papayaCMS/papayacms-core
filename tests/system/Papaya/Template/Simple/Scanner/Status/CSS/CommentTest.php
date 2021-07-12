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

namespace Papaya\Template\Simple\Scanner\Status\CSS;
require_once __DIR__.'/../../../../../../../bootstrap.php';

class CommentTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Template\Simple\Scanner\Status\CSS\Comment::getToken
   * @dataProvider provideValidTokenData
   * @param string $expected
   * @param string $buffer
   * @param int $offset
   */
  public function testGetToken($expected, $buffer, $offset) {
    $status = new Comment();
    $token = $status->getToken($buffer, $offset);
    $this->assertEquals($expected, (string)$token);
  }

  /**
   * @covers \Papaya\Template\Simple\Scanner\Status\CSS\Comment::isEndToken
   */
  public function testIsEndTokenExpectingTrue() {
    $token = new \Papaya\Template\Simple\Scanner\Token(
      \Papaya\Template\Simple\Scanner\Token::COMMENT_END, 0, ''
    );
    $status = new Comment();
    $this->assertTrue($status->isEndToken($token));
  }

  /**************************
   * Data Provider
   *************************/

  public static function provideValidTokenData() {
    return array(
      array('TEXT@2: " foo "', '/* foo */', 2),
      array('COMMENT_END@7: "*/"', '/* foo */', 7),
      array('TEXT@0: " foo "', ' foo */', 0),
      array('COMMENT_END@0: "*/"', '*/', 0),
      array('TEXT@0: "/* foo "', '/* foo */', 0)
    );
  }
}
