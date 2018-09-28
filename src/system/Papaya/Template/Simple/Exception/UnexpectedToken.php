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

use Papaya\Template\Simple;

class UnexpectedToken extends Parser {
  /**
   * The token encountered during the scan.
   *
   * This is the token object which was not expected to be found at the given
   * position.
   *
   * @var Simple\Scanner\Token
   */
  public $encounteredToken;

  public function __construct($encounteredToken, array $expectedTokens) {
    $this->encounteredToken = $encounteredToken;
    $this->expectedTokens = $expectedTokens;

    $expectedTokenStrings = [];
    foreach ($expectedTokens as $expectedToken) {
      $expectedTokenStrings[] = Simple\Scanner\Token::getTypeString($expectedToken);
    }

    parent::__construct(
      \sprintf(
        'Parse error: Found %s while one of %s was expected.',
        (string)$encounteredToken,
        \implode(', ', $expectedTokenStrings)
      )
    );
  }
}
