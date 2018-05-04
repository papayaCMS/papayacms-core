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

/**
* Abstract superclass for the template parser. The actual parsers for the different parsing states
* extend this class.
*
* @package Papaya-Library
* @subpackage Template
*/
abstract class PapayaTemplateSimpleParser {

  /**
  * List of tokens from scanner
  *
  * @var array(PapayaTemplateSimpleScannerToken)
  */
  protected $_tokens = array();

  /**
  * Construct a parser object taking the token list to operate on as
  * argument
  */
  public function __construct(array &$tokens) {
    $this->_tokens = &$tokens;
  }

  /**
  * Execute the parsing process on the provided tokenstream
  *
  * This method is supposed to handle all the steps needed to parse the
  * current subsegment of the tokenstream. It is supposed to return a valid
  * PapayaTemplateSimpleAst.
  *
  * If the parsing process can't be completed because of invalid input a
  * PapayaTemplateSimpleParserException needs to be thrown.
  *
  * The methods protected methods read and lookahead should be used to
  * operate on the tokenstream. They will throw PapayaTemplateSimpleParserExceptions
  * automatically in case they do not succeed.
  *
  * @return \PapayaTemplateSimpleAst
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
   * If no match can be found a PapayaTemplateSimpleParserException will thrown indicating what
   * has been expected and what was found.
   *
   * The $expectedTokens parameter may be an array of tokens or a scalar
   * value, which is handled the same way an array with only one entry would
   * be.
   *
   * The special Token \PapayaTemplateSimpleScannerToken::ANY may be used to indicate
   * everything is valid and may be matched. However if it is used no other
   * token may be specified, which does not make any sense, anyway.
   *
   * @param array|int|string $expectedTokens
   * @throws \PapayaTemplateSimpleException
   * @return \PapayaTemplateSimpleScannerToken
   */
  protected function read($expectedTokens) {
    // Allow scalar token values for better readability
    if (!is_array($expectedTokens)) {
      return $this->read(array($expectedTokens));
    }

    foreach ($expectedTokens as $token) {
      if ($this->matchToken(0, $token)) {
        return array_shift($this->_tokens);
      }
    }

    // None of the given tokens matched
    throw $this->createMismatchException($expectedTokens);
  }

  /**
   * Try to match any of the $expectedTokens against the given tokenstream
   * position and return the matching one.
   *
   * This method tries to match the current tokenstream at the provided
   * lookahead posistion against all of the provided tokens. If a match is
   * found it simply returned. The tokenstream remains unchanged.
   *
   * If no match can be found a PapayaTemplateSimpleParserException will thrown indicating what
   * has been expected and what was found.
   *
   * The $expectedTokens parameter may be an array of tokens or a scalar
   * value, which is handled the same way an array with only one entry would
   * be.
   *
   * The special Token \PapayaTemplateSimpleScannerToken::ANY may be used to indicate
   * everything is valid and may be matched. However if it is used no other
   * token may be specified, which does not make any sense, anyway.
   *
   * The position parameter may be provided to enforce a match on an
   * arbitrary tokenstream position. Therefore unlimited lookahead is
   * provided.
   *
   * @param array|int|string $expectedTokens
   * @param int $position
   * @param bool $allowEndOfTokens
   * @throws \PapayaTemplateSimpleException
   * @return \PapayaTemplateSimpleScannerToken|NULL
   */
  protected function lookahead($expectedTokens, $position = 0, $allowEndOfTokens = FALSE) {
    // Allow scalar token values for better readability
    if (!is_array($expectedTokens)) {
      return $this->lookahead(array($expectedTokens), $position, $allowEndOfTokens);
    }

    // If the the requested characters is not available on the tokenstream
    // and this state is allowed return a special ANY token
    if ($allowEndOfTokens === TRUE && (!isset($this->_tokens[$position]))) {
      return new \PapayaTemplateSimpleScannerToken(\PapayaTemplateSimpleScannerToken::ANY, 0, '');
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
    return (count($this->_tokens) <= $position);
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
   * The special Token \PapayaTemplateSimpleScannerToken::ANY is not valid here.
   *
   * The method return TRUE if tokens were removed, otherwise FALSE.
   *
   * @param array|int|string $expectedTokens
   * @param boolean
   * @return bool
   */
  protected function ignore($expectedTokens) {
    // Allow scalar token values for better readability
    if (!is_array($expectedTokens)) {
      return $this->ignore(array($expectedTokens));
    }

    // increase position until the end of the tokenstream is reached or
    // a non matching token is found
    $found = TRUE;
    $position = 0;
    while (count($this->_tokens) > $position) {
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
      array_splice($this->_tokens, 0, $position);
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
   * of providing the current tokenstream as well as instantiating the
   * subparser.
   *
   * @param string $subparserClass
   * @throws \LogicException
   * @return \PapayaTemplateSimpleAst
   */
  protected function delegate($subparserClass) {
    $subparser = new $subparserClass($this->_tokens);
    if ($subparser instanceof \PapayaTemplateSimpleParser) {
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

    if ($type === \PapayaTemplateSimpleScannerToken::ANY) {
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
   * @return \PapayaTemplateSimpleException
   */
  protected function createMismatchException($expectedTokens, $position = 0) {
    // If the tokenstream ended unexpectedly throw an appropriate exception
    if (!isset($this->_tokens[$position])) {
      return new \PapayaTemplateSimpleExceptionUnexpectedEof($expectedTokens);
    }

    // We found a token but none of the expected ones.
    return new \PapayaTemplateSimpleExceptionUnexpectedToken(
      $this->_tokens[$position], $expectedTokens
    );
  }
}
