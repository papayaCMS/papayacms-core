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

namespace Papaya\Template\Simple;

/**
 * Abstract superclass for the template parser. The actual parsers for the different parsing states
 * extend this class.
 *
 * @package Papaya-Library
 * @subpackage Template
 */
abstract class Parser {
  /**
   * List of tokens from scanner
   *
   * @var array(\Papaya\Template\Simple\Scanner\Token)
   */
  protected $_tokens = [];

  /**
   * Construct a parser object taking the token list to operate on as
   * argument
   *
   * @param array $tokens
   */
  public function __construct(array &$tokens) {
    $this->_tokens = &$tokens;
  }

  /**
   * Execute the parsing process on the provided token stream
   *
   * This method is supposed to handle all the steps needed to parse the
   * current subsegment of the token stream. It is supposed to return a valid
   * Papaya\Template\Simple\AST.
   *
   * If the parsing process can't be completed because of invalid input a
   * PapayaTemplateSimpleParserException needs to be thrown.
   *
   * The methods protected methods read and lookahead should be used to
   * operate on the token stream. They will throw \Papaya\Template\Simple\Parser\Exception
   * automatically in case they do not succeed.
   *
   * @return \Papaya\Template\Simple\AST
   */
  abstract public function parse();

  /**
   * Try to read any of the $expectedTokens from the token list and return
   * the matching one.
   *
   * This method tries to match the current token list against all of the
   * provided tokens. If a match is found it is removed from the token list
   * and returned.
   *
   * If no match can be found a \Papaya\Template\Simple\Parser\Exception will thrown indicating what
   * has been expected and what was found.
   *
   * The $expectedTokens parameter may be an array of tokens or a scalar
   * value, which is handled the same way an array with only one entry would
   * be.
   *
   * The special Token \Papaya\Template\Simple\Scanner\Token::ANY may be used to indicate
   * everything is valid and may be matched. However if it is used no other
   * token may be specified, which does not make any sense, anyway.
   *
   * @param array|int|string $expectedTokens
   * @throws \Papaya\Template\Simple\Exception
   * @return \Papaya\Template\Simple\Scanner\Token
   */
  protected function read($expectedTokens) {
    // Allow scalar token values for better readability
    if (!\is_array($expectedTokens)) {
      return $this->read([$expectedTokens]);
    }

    foreach ($expectedTokens as $token) {
      if ($this->matchToken(0, $token)) {
        return \array_shift($this->_tokens);
      }
    }

    // None of the given tokens matched
    throw $this->createMismatchException($expectedTokens);
  }

  /**
   * Try to match any of the $expectedTokens against the given token stream
   * position and return the matching one.
   *
   * This method tries to match the current token stream at the provided
   * lookahead position against all of the provided tokens. If a match is
   * found it simply returned. The token stream remains unchanged.
   *
   * If no match can be found a \Papaya\Template\Simple\Parser\Exception will thrown indicating what
   * has been expected and what was found.
   *
   * The $expectedTokens parameter may be an array of tokens or a scalar
   * value, which is handled the same way an array with only one entry would
   * be.
   *
   * The special Token \Papaya\Template\Simple\Scanner\Token::ANY may be used to indicate
   * everything is valid and may be matched. However if it is used no other
   * token may be specified, which does not make any sense, anyway.
   *
   * The position parameter may be provided to enforce a match on an
   * arbitrary token stream position. Therefore unlimited lookahead is
   * provided.
   *
   * @param array|int|string $expectedTokens
   * @param int $position
   * @param bool $allowEndOfTokens
   * @throws \Papaya\Template\Simple\Exception
   * @return \Papaya\Template\Simple\Scanner\Token|null
   */
  protected function lookahead($expectedTokens, $position = 0, $allowEndOfTokens = FALSE) {
    // Allow scalar token values for better readability
    if (!\is_array($expectedTokens)) {
      return $this->lookahead([$expectedTokens], $position, $allowEndOfTokens);
    }

    // If the the requested characters is not available on the tokenstream
    // and this state is allowed return a special ANY token
    if (TRUE === $allowEndOfTokens && (!isset($this->_tokens[$position]))) {
      return new Scanner\Token(Scanner\Token::ANY, 0, '');
    }

    foreach ($expectedTokens as $token) {
      if ($this->matchToken($position, $token)) {
        return $this->_tokens[$position];
      }
    }

    // None of the given tokens matched
    throw $this->createMismatchException($expectedTokens, $position);
  }

  /**
   * Validate if the of the tokenstream is reached. The position parameter
   * may be provided to look forward.
   *
   * @param int $position
   * @return bool
   */
  protected function endOfTokens($position = 0) {
    return (\count($this->_tokens) <= $position);
  }

  /**
   * Try to read any of the $expectedTokens from the token list remove them from
   * the token stream.
   *
   * This method tries to match the current token list against all of the
   * provided tokens. Matching tokens are removed from the list until a non
   * matching token is found or the token list ends.
   *
   * The $expectedTokens parameter may be an array of tokens or a scalar
   * value, which is handled the same way an array with only one entry would
   * be.
   *
   * The special Token \Papaya\Template\Simple\Scanner\Token::ANY is not valid here.
   *
   * The method return TRUE if tokens were removed, otherwise FALSE.
   *
   * @param array|int|string $expectedTokens
   * @param bool
   * @return bool
   */
  protected function ignore($expectedTokens) {
    // Allow scalar token values for better readability
    if (!\is_array($expectedTokens)) {
      return $this->ignore([$expectedTokens]);
    }

    // increase position until the end of the tokenstream is reached or
    // a non matching token is found
    $found = TRUE;
    $position = 0;
    while (\count($this->_tokens) > $position) {
      foreach ($expectedTokens as $token) {
        if ($found = $this->matchToken($position, $token)) {
          ++$position;
          continue;
        }
      }
      if ($found) {
        continue;
      } else {
        break;
      }
    }

    // remove the tokens from the stream
    if ($position > 0) {
      \array_splice($this->_tokens, 0, $position);
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
   * Delegate the parsing process to a subparser
   *
   * The result of the subparser is returned
   *
   * Only the name of the subparser is expected here, the method takes care
   * of providing the current token stream as well as instantiating the
   * subparser.
   *
   * @param string $subparserClass
   * @throws \LogicException
   * @return \Papaya\Template\Simple\AST
   */
  protected function delegate($subparserClass) {
    $subparser = new $subparserClass($this->_tokens);
    if ($subparser instanceof self) {
      return $subparser->parse();
    }
    throw new \LogicException('Invalid parser class: '.$subparserClass);
  }

  /**
   * Match a token on the tokenstream against a token type.
   *
   * Returns true if the token at the given position exists and the provided
   * token type matches type of the token at this position, false otherwise.
   *
   * @param $position
   * @param int $type
   * @return bool
   */
  protected function matchToken($position, $type) {
    if (!isset($this->_tokens[$position])) {
      return FALSE;
    }

    if (Scanner\Token::ANY === $type) {
      // A token has been found. We do not care which one it was
      return TRUE;
    }

    return ($this->_tokens[$position]->type === $type);
  }

  /**
   * Handle the case if none of the expected tokens could be found.
   *
   * @param array $expectedTokens
   * @param int $position
   * @return \Papaya\Template\Simple\Exception
   */
  protected function createMismatchException($expectedTokens, $position = 0) {
    // If the tokenstream ended unexpectedly throw an appropriate exception
    if (!isset($this->_tokens[$position])) {
      return new Exception\UnexpectedEOF($expectedTokens);
    }

    // We found a token but none of the expected ones.
    return new Exception\UnexpectedToken(
      $this->_tokens[$position], $expectedTokens
    );
  }
}
