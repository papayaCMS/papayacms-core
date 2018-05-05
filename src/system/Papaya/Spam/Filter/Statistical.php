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
* The statistical spam filter uses a reference table of tokens to calculate the spam probability of
* a given token list.
*
* @package Papaya-Library
* @subpackage Spam
*/
class PapayaSpamFilterStatistical implements \PapayaSpamFilter {

  private $_minimumTokenLength = 3;
  private $_maximumTokenLength = 30;
  private $_maximumRelevant = 15;
  private $_relevantDerivation = 0.2;
  private $_defaultRating = 0.5;
  private $_defaultRatingProbability = 0.3;

  private $_reference = NULL;
  private $_report = array();


  /**
  * Classify the given token list, return the probability the token list is spam.
  *
  * @param string $text ignored - original text
  * @param array $tokens prepared token list
  * @param integer $languageId
  * @return float probability between 0 and 1
  */
  public function classify($text, array $tokens, $languageId) {
    $this->_report = array();
    $tokens = $this->filterTokens($tokens);
    $this->getReference()->load(array_keys($tokens), $languageId);
    $probabilities = $this->getProbabilities($tokens);
    return $this->aggregateProbabilities($tokens, $probabilities);
  }

  /**
  * Return the details for the last call off classify. This will return an array with all
  * relevant words, their count and their probability in percent (0 - 100).
  *
  * @return array(string=>array('count'=>integer,'probability'=>integer))
  */
  public function getDetails() {
    return $this->_report;
  }

  /**
  * Getter for reference data object including implicit create.
  *
  * @return \PapayaSpamFilterStatisticalReference
  */
  public function getReference() {
    if (is_null($this->_reference)) {
      $this->_reference = new \PapayaSpamFilterStatisticalReference();
    }
    return $this->_reference;
  }

  /**
  * Setter for reference data object.
  *
  * @param \PapayaSpamFilterStatisticalReference $reference
  */
  public function setReference(\PapayaSpamFilterStatisticalReference $reference) {
    $this->_reference = $reference;
  }

  /**
   * Set the minimum derivation that a tokens needs to be considered relevant. The derivation is
   * relative to 0.5, so a maximum of 0.4 is allowed. A minimum of 0 is possible.
   *
   * @param integer|float $derivation
   * @throws \RangeException
   */
  public function setRelevanceLimit($derivation) {
    $derivation = (float)$derivation;
    if ($derivation < 0.0 || $derivation > 0.4) {
      throw new \RangeException('RangeException: $derivation must be between 0 and 0.4');
    }
    $this->_relevantDerivation = $derivation;
  }

  /**
   * Set the maximum count of tokens that are used to calculate the probability.
   *
   * @param integer $count
   * @throws \RangeException
   */
  public function setTokenLimit($count) {
    \PapayaUtilConstraints::assertInteger($count);
    if ($count <= 0) {
      throw new \RangeException('RangeException: $count must be greater than 0');
    }
    $this->_maximumRelevant = $count;
  }

  /**
  * Check token list for tokens that are to short or two long. Return a list without them.
  *
  * @param array $tokens
  * @return array(string=>integer)
  */
  public function filterTokens($tokens) {
    $result = array();
    foreach ($tokens as $word => $count) {
      $length = strlen($word);
      if ($length >= $this->_minimumTokenLength && $length <= $this->_maximumTokenLength) {
        $result[$word] = $count;
      }
    }
    return $result;
  }

  /**
  * Get the probabilities for all relevant tokens.
  *
  * @param array $tokens
  * @return array(string=>float)
  */
  public function getProbabilities(array $tokens) {
    $result = array();
    foreach ($tokens as $word => $count) {
      $wordProbability = $this->getProbability($word);
      if (abs(0.5 - $wordProbability) >= $this->_relevantDerivation) {
        $result[$word] = $wordProbability;
      }
    }
    uasort($result, array($this, 'compareProbabilityRelevance'));
    return $result;
  }

  /**
  * Compare two probabilities by their relevance. The relevance is the distance from "0.5".
  * The method is used by {@see self::getProbabilities()}
  *
  * @param $probabilityOne
  * @param $probabilityTwo
  * @return integer
  */
  public function compareProbabilityRelevance($probabilityOne, $probabilityTwo) {
    $relevanceOne = abs(0.5 - $probabilityOne);
    $relevanceTwo = abs(0.5 - $probabilityTwo);
    if ($relevanceOne > $relevanceTwo) {
      return -1;
    } elseif ($relevanceOne < $relevanceTwo) {
      return 1;
    }
    return 0;
  }

  /**
  * Aggregate the probabilities of the relevant tokens into one value.
  *
  * @param array $tokens
  * @param array $probabilities
  * @return float
  */
  private function aggregateProbabilities(array $tokens, array $probabilities) {
    $counter = $this->_maximumRelevant;
    $ratingCounter = 0;
    $hamminess = 1;
    $spamminess = 1;
    foreach ($probabilities as $word => $wordProbability) {
      $this->_report[$word] = array(
        'count' => $tokens[$word],
        'probability' => round($wordProbability * 100)
      );
      for ($repeat = $tokens[$word]; $repeat > 0; $repeat--) {
        $hamminess *= (1.0 - $wordProbability);
        $spamminess *= $wordProbability;
        ++$ratingCounter;
      }
      if (--$counter <= 0) {
        break;
      }
    }
    if ($ratingCounter > 0) {
      $hamminess = 1 - pow($hamminess, (1 / $ratingCounter));
      $spamminess = 1 - pow($spamminess, (1 / $ratingCounter));
      $probability = ($hamminess - $spamminess) / ($hamminess + $spamminess);
      $probability = (1 + $probability) / 2;
      return $probability;
    } else {
      return $this->_defaultRating;
    }
  }

  /**
   * Get the probability of an word, look into the database if the tokens exists or return the
   * default value.
   *
   * @param string $word
   * @return float
   */
  public function getProbability($word) {
    $reference = $this->getReference();
    if ($item = $reference->item($word)) {
      return $this->calculateProbability(
        $item, $reference->getHamCount(), $reference->getSpamCount()
      );
    } else {
      return $this->_defaultRating;
    }
  }

  /**
  * Calculate the probability for a single item from reference db.
  *
  * @param array $data
  * @param integer $summaryHam
  * @param integer $summarySpam
  * @return float
  */
  private function calculateProbability($data, $summaryHam, $summarySpam) {
    $ham = ($summaryHam > 0) ? $data['ham'] / $summaryHam : $data['ham'];
    $spam = ($summarySpam > 0) ? $data['spam'] / $summarySpam : $data['spam'];
    $rating = $spam / ($ham + $spam);
    $all = $data['ham'] + $data['spam'];
    return (
      ($this->_defaultRatingProbability * $this->_defaultRating) + ($all * $rating)
    ) / ($this->_defaultRatingProbability + $all);
  }
}
