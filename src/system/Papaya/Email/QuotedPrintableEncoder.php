<?php
/*
 * papaya CMS
 *
 * @copyright 2000-2021 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\EmailMessage {

  class QuotedPrintableEncoder {

    public const ENCODE_QUESTION_MARK = 1;
    public const WRAP = 2;

    /**
     * @private
     */
    private const LINE_END = "\n";

    /**
     * @var int
     */
    private $_lineLength;
    /**
     * @var int
     */
    private $_flags;

    public function __construct(
      int $lineLength = 72,
      int $flags = self::ENCODE_QUESTION_MARK | self::WRAP
    ) {
      $this->_lineLength = $lineLength;
      $this->_flags = $flags;
    }

    public function encode(string $text): string {
      $result = preg_replace_callback(
        '([^\x21-\x3C\x3E-\x7E\x09\x20\x0A\x0D])',
        function ($match) {
          return $this->encodeCharacter($match[0]);
        },
        $text
      );
      if (($this->_flags & self::ENCODE_QUESTION_MARK) === self::ENCODE_QUESTION_MARK) {
        $result = str_replace('?', '=3F', $result);
      }
      $result = preg_replace_callback(
        '~[\x09\x20]$~m',
        function ($match) {
          return $this->encodeCharacter($match[0]);
        },
        $result
      );
      if (($this->_flags & self::WRAP) === self::WRAP) {
        return $this->reWrap($result);
      }
      return $this->reformatLineEnds($result);
    }

    private function encodeCharacter(string $character): string {
      $charCode = dechex(ord(substr($character, 0, 1)));
      return '='.strtoupper(str_pad($charCode, 2, '0', STR_PAD_LEFT));
    }

    private function reWrap(string $text): string {
      $text = str_replace(["\r\n", "\r"], "\n", $text);
      $lines = explode("\n", $text);
      $result = '';
      if (isset($lines) && is_array($lines) && count($lines) > 0) {
        foreach ($lines as $line) {
          $result .= $this->reWrapLine($line, $this->_lineLength).self::LINE_END;
        }
      }
      return $result;
    }

    /**
     * wrap quoted printable encoded text line
     * @param string $text
     * @param integer $lineLength
     * @return string
     */
    private function reWrapLine(string $text, int $lineLength): string {
      if (trim($text) === '') {
        $result = '';
      } elseif (strlen($text) <= $lineLength) {
        $result = $text;
      } elseif (FALSE !== strpos($text, ' ')) {
        $result = $this->reWrapLineAtSpace($text, $lineLength);
      } elseif (FALSE !== strpos($text, '=')) {
        $result = $this->reWrapLineAtEqual($text, $lineLength);
      } else {
        $result = $this->reWrapLineAtOffset($text, $lineLength);
      }
      return $result;
    }

    /**
     * wrap quoted printable encoded text line at line length
     * @param string $text
     * @param int $lineLength
     * @return string
     */
    private function reWrapLineAtOffset(string $text, int $lineLength): string {
      return substr(
        chunk_split($text, $lineLength, '='.self::LINE_END),
        0,
        -strlen(self::LINE_END) - 1
      );
    }

    /**
     * Rewrap quoted printable encoded text line at whitespace
     * @param string $text
     * @param int $lineLength
     * @return string
     */
    private function reWrapLineAtSpace(string $text, int $lineLength): string {
      $result = '';
      $buffer = '';
      $offset = 0;
      $separator = ' ';
      $text .= $separator;
      while (FALSE !== ($pos = strpos($text, $separator, $offset))) {
        if ($pos >= $offset) {
          $word = substr($text, $offset, $pos - $offset + strlen($separator));
          if (strlen($word) >= $lineLength) {
            if ($buffer !== '') {
              $result .= $buffer.'='.self::LINE_END;
            }
            if (FALSE !== strpos($word, '=')) {
              $result .= $this->reWrapLineAtEqual($word, $lineLength);
            } else {
              $result .= $this->reWrapLineAtOffset($word, $lineLength);
            }
            $result .= '='.self::LINE_END;
            $buffer = '';
          } elseif (strlen($buffer.$word) >= $lineLength) {
            $result .= $buffer.'='.self::LINE_END;
            $buffer = $word;
          } else {
            $buffer .= $word;
          }
          $offset = $pos + 1;
        } else {
          break;
        }
      }
      $word = substr($text, $offset);
      if (strlen($word) >= $lineLength) {
        $result .= $buffer.'='.self::LINE_END;
        if (FALSE !== strpos($word, '=')) {
          $result .= $this->reWrapLineAtEqual($word, $lineLength);
        } else {
          $result .= $this->reWrapLineAtOffset($word, $lineLength);
        }
      } elseif (strlen($buffer.$word) >= $lineLength) {
        $result .= $buffer.'='.self::LINE_END.$word;
      } else {
        $result .= $buffer.$word;
      }
      return $result;
    }

    /**
     * wrap quoted printable encoded text line at encoded char start
     * @param string $text
     * @param int $lineLength
     * @return string
     */
    private function reWrapLineAtEqual(string $text, int $lineLength): string {
      $result = '';
      $buffer = '';
      $offset = 0;
      $separator = '=';
      while (FALSE !== ($pos = strpos($text, $separator, $offset + 1))) {
        if ($pos >= $offset) {
          $word = substr($text, $offset, $pos - $offset);
          if (strlen($word) >= $lineLength) {
            if ($buffer !== '') {
              $result .= $buffer.'='.self::LINE_END;
            }
            $result .= $this->reWrapLineAtOffset($word, $lineLength);
            $result .= '='.self::LINE_END;
            $buffer = '';
          } elseif (strlen($buffer.$word) >= $lineLength) {
            $result .= $buffer.'='.self::LINE_END;
            $buffer = $word;
          } else {
            $buffer .= $word;
          }
          $offset = $pos;
        } else {
          break;
        }
      }
      $word = substr($text, $offset);
      if (strlen($word) >= $lineLength) {
        $result .= $buffer.'='.self::LINE_END;
        $result .= $this->reWrapLineAtOffset($word, $lineLength);
      } elseif (strlen($buffer.$word) >= $lineLength) {
        $result .= $buffer.'='.self::LINE_END.$word;
      } else {
        $result .= $buffer.$word;
      }
      return $result;
    }

    /**
     * convert all line breaks to self::LINE_END
     *
     * @param string $text
     * @return string
     */
    private function reformatLineEnds(string $text): string {
      $text = str_replace(array("\r\n", "\r"), "\n", $text);
      if (self::LINE_END !== "\n") {
        $text = str_replace("\n", self::LINE_END, $text);
      }
      return $text;
    }
  }
}


