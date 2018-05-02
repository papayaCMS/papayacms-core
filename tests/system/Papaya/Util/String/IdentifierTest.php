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

class PapayaUtilStringIdentifierTest extends PapayaTestCase {

  /**
  * @covers PapayaUtilStringIdentifier::toUnderscoreUpper
  */
  public function testToUnderscoreUpper() {
    $this->assertEquals(
      'SAMPLE_IDENTIFIER', PapayaUtilStringIdentifier::toUnderscoreUpper('sampleIdentifier')
    );
  }

  /**
  * @covers PapayaUtilStringIdentifier::toUnderscoreLower
  */
  public function testToUnderscoreLower() {
    $this->assertEquals(
      'sample_identifier', PapayaUtilStringIdentifier::toUnderscoreLower('sampleIdentifier')
    );
  }

  /**
  * @covers PapayaUtilStringIdentifier::toCamelCase
  */
  public function testToCamelCase() {
    $this->assertEquals(
      'sampleIdentifier', PapayaUtilStringIdentifier::toCamelCase('Sample_Identifier')
    );
  }

  /**
  * @covers PapayaUtilStringIdentifier::toCamelCase
  */
  public function testToCamelCaseWithUpperCaseFirstChar() {
    $this->assertEquals(
      'SampleIdentifier', PapayaUtilStringIdentifier::toCamelCase('Sample_Identifier', TRUE)
    );
  }

  /**
  * @covers PapayaUtilStringIdentifier::toCamelCase
  */
  public function testToCamelCaseWithNumericPart() {
    $this->assertEquals(
      'numeric_23', PapayaUtilStringIdentifier::toCamelCase('Numeric_23', FALSE)
    );
  }

  /**
   * @covers PapayaUtilStringIdentifier::toArray
   * @dataProvider provideIdentifiersAndParts
   * @param string $identifier
   * @param array $parts
   */
  public function testToArray($identifier, array $parts) {
    $this->assertEquals(
      $parts, PapayaUtilStringIdentifier::toArray($identifier)
    );
  }

  /**
  * @covers PapayaUtilStringIdentifier::toArray
  */
  public function testToArrayWithUnknownStructure() {
    $this->assertEquals(
      array('1.2.3'), PapayaUtilStringIdentifier::toArray('1.2.3')
    );
  }

  public static function provideIdentifiersAndParts() {
    return array(
      array('simple', array('simple')),
      array('Single', array('single')),
      array('camelCase', array('camel', 'case')),
      array('lower_case', array('lower', 'case')),
      array('UPPER_CASE', array('upper', 'case')),
      array('mixed_VARIANTVersion', array('mixed', 'variant', 'version')),
      array('numeric_0', array('numeric', '0')),
      array('numeric_1suffix', array('numeric', '1suffix')),
      array('numeric23_42', array('numeric23', '42'))
    );
  }
}
