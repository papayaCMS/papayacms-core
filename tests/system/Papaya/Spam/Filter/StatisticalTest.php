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

class PapayaSpamFilterStatisticalTest extends PapayaTestCase {

  /**
  * @covers \PapayaSpamFilterStatistical::setReference
  * @covers \PapayaSpamFilterStatistical::getReference
  */
  public function testGetReferenceAfterSet() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaSpamFilterStatisticalReference $reference */
    $reference = $this->createMock(PapayaSpamFilterStatisticalReference::class);
    $filter = new \PapayaSpamFilterStatistical();
    $filter->setReference($reference);
    $this->assertSame($reference, $filter->getReference());
  }

  /**
  * @covers \PapayaSpamFilterStatistical::getReference
  */
  public function testGetReferenceImplicitCreate() {
    $filter = new \PapayaSpamFilterStatistical();
    $this->assertInstanceOf(PapayaSpamFilterStatisticalReference::class, $filter->getReference());
  }

  /**
  * @covers \PapayaSpamFilterStatistical::setRelevanceLimit
  */
  public function testSetRelevanceLimit() {
    $filter = new \PapayaSpamFilterStatistical();
    $filter->setRelevanceLimit(0.3);
    $this->assertAttributeEquals(
      0.3, '_relevantDerivation', $filter
    );
  }

  /**
  * @covers \PapayaSpamFilterStatistical::setRelevanceLimit
  */
  public function testSetRelevanceLimitExpectingException() {
    $filter = new \PapayaSpamFilterStatistical();
    $this->expectException(RangeException::class);
    $this->expectExceptionMessage('RangeException: $derivation must be between 0 and 0.4');
    $filter->setRelevanceLimit(1);
  }

  /**
  * @covers \PapayaSpamFilterStatistical::setTokenLimit
  */
  public function testSetTokenLimit() {
    $filter = new \PapayaSpamFilterStatistical();
    $filter->setTokenLimit(99);
    $this->assertAttributeEquals(
      99, '_maximumRelevant', $filter
    );
  }

  /**
  * @covers \PapayaSpamFilterStatistical::setTokenLimit
  */
  public function testSetTokenLimitExpectingException() {
    $filter = new \PapayaSpamFilterStatistical();
    $this->expectException(RangeException::class);
    $this->expectExceptionMessage('RangeException: $count must be greater than 0');
    $filter->setTokenLimit(-23);
  }

  /**
  * @covers \PapayaSpamFilterStatistical::filterTokens
  */
  public function testFilterTokens() {
    $tokens = array(
      's' => 1,
      'normal' => 2,
      'really-long-token-above-the-limit-of-the-character-count' => 3
    );
    $filter = new \PapayaSpamFilterStatistical();
    $this->assertEquals(
      array('normal' => 2),
      $filter->filterTokens($tokens)
    );
  }

  /**
  * @covers \PapayaSpamFilterStatistical::getProbability
  * @covers \PapayaSpamFilterStatistical::calculateProbability
  */
  public function testGetProbabilityIsHam() {
    $filter = new \PapayaSpamFilterStatistical();
    $filter->setReference($this->getSpamReferenceMock());
    $this->assertLessThan(0.5, $filter->getProbability('papaya'));
  }

  /**
  * @covers \PapayaSpamFilterStatistical::getProbability
  * @covers \PapayaSpamFilterStatistical::calculateProbability
  */
  public function testGetProbabilityIsSpam() {
    $filter = new \PapayaSpamFilterStatistical();
    $filter->setReference($this->getSpamReferenceMock());
    $this->assertGreaterThan(0.5, $filter->getProbability('casino'));
  }

  /**
  * @covers \PapayaSpamFilterStatistical::getProbability
  * @covers \PapayaSpamFilterStatistical::calculateProbability
  */
  public function testGetProbabilityIsBalanced() {
    $filter = new \PapayaSpamFilterStatistical();
    $filter->setReference($this->getSpamReferenceMock());
    $this->assertEquals(0.5, $filter->getProbability('download'), '', 0.00001);
  }

  /**
  * @covers \PapayaSpamFilterStatistical::getProbability
  */
  public function testGetProbabilityIsUnknown() {
    $filter = new \PapayaSpamFilterStatistical();
    $filter->setReference($this->getSpamReferenceMock());
    $this->assertEquals(0.5, $filter->getProbability('unknown'), '', 0.00001);
  }

  /**
  * @covers \PapayaSpamFilterStatistical::classify
  * @covers \PapayaSpamFilterStatistical::getProbabilities
  * @covers \PapayaSpamFilterStatistical::aggregateProbabilities
  */
  public function testClassifyExpectingSpam() {
    $filter = new \PapayaSpamFilterStatistical();
    $filter->setReference($this->getSpamReferenceMock());
    $this->assertGreaterThan(
      0.5,
      $filter->classify('', array('papaya' => 1, 'money' => 2, 'gamble' => 1), 1),
      ''
    );
  }

  /**
  * @covers \PapayaSpamFilterStatistical::classify
  * @covers \PapayaSpamFilterStatistical::getProbabilities
  * @covers \PapayaSpamFilterStatistical::aggregateProbabilities
  */
  public function testClassifyExpectingHam() {
    $filter = new \PapayaSpamFilterStatistical();
    $filter->setReference($this->getSpamReferenceMock());
    $this->assertLessThan(
      0.5,
      $filter->classify('', array('papaya' => 1, 'cms' => 2, 'gamble' => 1), 1),
      ''
    );
  }

  /**
  * @covers \PapayaSpamFilterStatistical::classify
  * @covers \PapayaSpamFilterStatistical::getProbabilities
  * @covers \PapayaSpamFilterStatistical::aggregateProbabilities
  */
  public function testClassifyExpectingBalanced() {
    $filter = new \PapayaSpamFilterStatistical();
    $filter->setReference($this->getSpamReferenceMock());
    $this->assertEquals(
      0.5,
      $filter->classify(
        '', array('papaya' => 1, 'casino' => 1, 'download' => 1, 'unknown' => 1), 1
      ),
      '',
      0.00001
    );
  }

  /**
   * @covers \PapayaSpamFilterStatistical::getDetails
   * @covers \PapayaSpamFilterStatistical::getProbabilities
   * @covers \PapayaSpamFilterStatistical::aggregateProbabilities
   * @covers \PapayaSpamFilterStatistical::compareProbabilityRelevance
   * @dataProvider provideTokenSamples
   * @param float $expected
   * @param array $report
   * @param array $tokens
   * @param float $relevanceLimit
   * @param int $tokenLimit
   */
  public function testProbabilityRelevanceSortAndFilter(
    $expected, $report, $tokens, $relevanceLimit, $tokenLimit
  ) {
    $filter = new \PapayaSpamFilterStatistical();
    $filter->setReference($this->getSpamReferenceMock());
    $filter->setRelevanceLimit($relevanceLimit);
    $filter->setTokenLimit($tokenLimit);
    $this->assertEquals(
      $expected, $filter->classify('', $tokens, 1), '', 0.00001
    );
    $this->assertEquals(
      $report, $filter->getDetails()
    );
  }

  /*********************
  * Data Provider
  *********************/

  public static function provideTokenSamples() {
    return array(
      'standard' => array(
        0.55782,
        array(
          'casino' => array(
            'count' => 1,
            'probability' => 93
          ),
          'papaya' => array(
            'count' => 2,
            'probability' => 7
          ),
          'money' => array(
            'count' => 1,
            'probability' => 88
          ),
          'gamble' => array(
            'count' => 2,
            'probability' => 88
          )
        ),
        array('money' => 1, 'papaya' => 2, 'gamble' => 2, 'casino' => 1),
        0.2,
        15
      ),
      'with relevance limit' => array(
        0.38764,
        array(
          'papaya' => array(
            'count' => 2,
            'probability' => 7
          ),
          'money' => array(
            'count' => 1,
            'probability' => 88
          )
        ),
        array('unknown' => 1, 'papaya' => 2, 'download' => 1, 'money' => 1),
        0.2,
        15
      ),
      'without relevance limit' => array(
        0.409323,
        array(
          'papaya' => array(
            'count' => 2,
            'probability' => 7
          ),
          'money' => array(
            'count' => 1,
            'probability' => 88
          ),
          'download' => array(
            'count' => 1,
            'probability' => 50
          ),
          'unknown' => array(
            'count' => 1,
            'probability' => 50
          )
        ),
        array('unknown' => 1, 'papaya' => 2, 'download' => 1, 'money' => 1),
        0.0,
        15,
      ),
      'most relevant item only' => array(
        0.065212,
        array(
          'papaya' => array(
            'count' => 2,
            'probability' => 7
          )
        ),
        array('unknown' => 1, 'papaya' => 2, 'download' => 1, 'money' => 1),
        0.0,
        1,
      ),
      'with empty token list' => array(
        0.5,
        array(),
        array(),
        0.0,
        15,
      )
    );
  }

  /*********************
  * Fixtures
  *********************/

  /**
   * @return \PHPUnit_Framework_MockObject_MockObject|\PapayaSpamFilterStatisticalReference
   */
  private function getSpamReferenceMock() {
    $reference = $this->createMock(PapayaSpamFilterStatisticalReference::class);
    $reference
      ->expects($this->any())
      ->method('load')
      ->withAnyParameters();
    $reference
      ->expects($this->any())
      ->method('item')
      ->withAnyParameters()
      ->will($this->returnCallback(array($this, 'callbackSpamReferenceItem')));
    $reference
      ->expects($this->any())
      ->method('getHamCount')
      ->will($this->returnValue(5));
    $reference
      ->expects($this->any())
      ->method('getSpamCount')
      ->will($this->returnValue(5));
    return $reference;
  }

  public function callbackSpamReferenceItem($word) {
    $items = array(
      'papaya' => array('ham' => 2, 'spam' => 0),
      'cms' => array('ham' => 1, 'spam' => 0),
      'installer' => array('ham' => 1, 'spam' => 0),

      'casino' => array('ham' => 0, 'spam' => 2),
      'money' => array('ham' => 0, 'spam' => 1),
      'gamble' => array('ham' => 0, 'spam' => 1),

      'download' => array('ham' => 2, 'spam' => 2),
      'free' => array('ham' => 1, 'spam' => 1)
    );
    return isset($items[$word]) ? $items[$word] : NULL;
  }

}
