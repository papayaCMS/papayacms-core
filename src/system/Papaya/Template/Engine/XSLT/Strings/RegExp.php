<?php
declare(strict_types=1);

namespace Papaya\Template\Engine\XSLT\Strings {

  use Papaya\Template\Engine\XSLT\Namespaces;
  use Papaya\Template\Engine\XSLT\XpathError;
  use DOMDocument;
  use DOMElement;
  use ErrorException;
  use Exception;

  abstract class RegExp {

    /**
     * Returns true if the supplied string matches a given regular expression.
     *
     * @link https://www.w3.org/TR/xpath-functions/#func-matches
     *
     * @param string $input
     * @param string $pattern
     * @param string $flags
     * @return bool
     * @throws XpathError
     */
    public static function matches(string $input, string $pattern, string $flags = ''): bool {
      $pattern = self::createPatternString($pattern, $flags);
      return (bool)preg_match($pattern, $input);
    }

    /**
     * Returns a string produced from the input string by replacing any substrings that match a
     * given regular expression with a supplied replacement string.
     *
     * @link https://www.w3.org/TR/xpath-functions/#func-replace
     *
     * @param string $input
     * @param string $pattern
     * @param string $replacement
     * @param string $flags
     * @return string
     * @throws XpathError
     */
    public static function replace(string $input, string $pattern, string $replacement, string $flags = ''): string {
      $pattern = self::createPatternString($pattern, $flags, FALSE);
      self::validateReplacementString($replacement, $flags);
      return preg_replace($pattern, $replacement, $input);
    }

    /**
     * Returns a sequence of strings constructed by splitting the input wherever a separator
     * is found; the separator is any substring that matches a given regular expression.
     *
     * @link https://www.w3.org/TR/xpath-functions/#func-tokenize
     *
     * @param string $input
     * @param string|FALSE $pattern
     * @param string $flags
     * @return \DOMElement
     * @throws XpathError
     */
    public static function tokenize(string $input, $pattern = ' ', string $flags = ''): DOMElement {
      if ($pattern === FALSE) {
        $pattern = '(\\s+)u';
        $input = trim($input);
      } else {
        $pattern = self::createPatternString($pattern, $flags);
      }
      $document = new DOMDocument();
      $document->appendChild(
        $document->createElementNS(Namespaces::XMLNS_FN, 'tokens')
      );
      foreach (preg_split($pattern, $input) as $tokenString) {
        $token = $document->documentElement->appendChild(
          $document->createElementNS(Namespaces::XMLNS_FN, 'token')
        );
        $token->textContent = $tokenString;
      }
      return $document->documentElement;
    }

    /**
     * Analyzes a string using a regular expression, returning an XML structure
     * that identifies which parts of the input string matched or failed to
     * match the regular expression, and in the case of matched substrings,
     * which substrings matched each capturing group in the regular expression.
     *
     * @link https://www.w3.org/TR/xpath-functions/#func-analyze-string
     *
     * @param string $input
     * @param string $pattern
     * @param string $flags
     * @return \DOMElement
     * @throws XpathError
     */
    public static function analyzeString(string $input, string $pattern, string $flags): DOMElement {
      $pattern = self::createPatternString($pattern, $flags, FALSE);
      $document = new DOMDocument();
      $document->appendChild(
        $document->createElementNS(Namespaces::XMLNS_FN, 'analyze-string-result')
      );
      $offset = 0;
      preg_match_all($pattern, $input, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
      foreach ($matches as $matchGroup) {
        [$matchContent, $matchOffset] = $matchGroup[0];
        if ($offset < $matchOffset) {
          $nonMatchNode = $document->documentElement->appendChild(
            $document->createElementNS(Namespaces::XMLNS_FN, 'non-match')
          );
          $nonMatchNode->textContent = substr($input, $offset, $matchOffset - $offset);
        }
        $offset = $matchOffset + strlen($matchContent);
        $matchNode = $document->documentElement->appendChild(
          $document->createElementNS(Namespaces::XMLNS_FN, 'match')
        );
        if (count($matchGroup) === 1) {
          $matchNode->textContent = $matchContent;
        } else {
          $index = 1;
          $groupOffset = $matchOffset;
          for ($i = 1, $c = count($matchGroup); $i < $c; $i++) {
            [$subMatchContent, $subMatchOffset] = $matchGroup[$i];
            if ($subMatchOffset < $groupOffset) {
              continue;
            }
            if ($subMatchOffset > $groupOffset) {
              $matchNode->appendChild(
                $document->createTextNode(
                  substr($input, $groupOffset, $subMatchOffset - $groupOffset)
                )
              );
            }
            $groupOffset = $subMatchOffset + strlen($subMatchContent);
            $matchNode->appendChild(
              $matchGroupNode = $document->createElementNS(Namespaces::XMLNS_FN, 'group')
            );
            $matchGroupNode->setAttribute('nr', (string)$index++);
            $matchGroupNode->textContent = $subMatchContent;
          }
        }
      }
      if ($offset < strlen($input)) {
        $nonMatchNode = $document->documentElement->appendChild(
          $document->createElementNS(Namespaces::XMLNS_FN, 'non-match')
        );
        $nonMatchNode->textContent = substr($input, $offset);
      }
      return $document->documentElement;
    }

    /**
     * Cleanup pattern string and validate flags.
     *
     * Empty patterns are not allowed.
     * If "q" is provided in the flags quote the whole pattern.
     *
     * @link https://www.w3.org/TR/xpath-functions/#flags
     *
     * @param string $pattern
     * @param string $flags
     * @param bool $allowEmptyMatch
     * @return string
     * @throws XpathError
     */
    private static function createPatternString(string $pattern, string $flags, $allowEmptyMatch = TRUE): string {
      if (empty($pattern)) {
        throw new XpathError(Namespaces::XMLNS_ERR.'#FORX0002', 'Invalid regular expression.');
      }
      if (preg_match('([^smixq])u', $flags, $matches)) {
        throw new XpathError(
          Namespaces::XMLNS_ERR.'#FORX0001', 'Invalid regular expression flags: '.$matches[0].'.'
        );
      }
      if (FALSE !== strpos($flags, 'q')) {
        $pattern = preg_quote($pattern, '(');
        $flags = str_replace('q', '', $flags);
      }
      $patternString = '('.$pattern.')u'.$flags;
      try {
        if (FALSE === preg_match($patternString, '')) {
          throw new ErrorException('Invalid PCRE Pattern.');
        }
      } catch (Exception $e) {
        throw new XpathError(
          Namespaces::XMLNS_ERR.'#FORX0002',
          'Invalid regular expression.'
        );
      }
      if (!$allowEmptyMatch && preg_match($patternString, '')) {
        throw new XpathError(
          Namespaces::XMLNS_ERR.'#FORX0003', 'Regular expression matches zero-length string.'
        );
      }
      return $patternString;
    }

    /**
     * @param string $replacement
     * @param string $flags
     * @return bool
     * @throws XpathError
     */
    private static function validateReplacementString(string $replacement, string $flags): bool {
      if (FALSE !== strpos($flags, 'q')) {
        return TRUE;
      }
      $s = preg_replace('(\\\\\\\\|\\\\\\$|\\$\\d)', '*', $replacement);
      if (FALSE !== strpos($s, '\\') || FALSE !== strpos($s, '$')) {
        throw new XpathError(
          Namespaces::XMLNS_ERR.'#FORX0004', 'Invalid replacement string.'
        );
      }
      return TRUE;
    }
  }
}
