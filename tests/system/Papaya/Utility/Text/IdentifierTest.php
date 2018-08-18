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

namespace Papaya\Utility\Text;
require_once __DIR__.'/../../../../bootstrap.php';

class IdentifierTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Utility\Text\Identifier::toUnderscoreUpper
   */
  public function testToUnderscoreUpper() {
    $this->assertEquals(
      'SAMPLE_IDENTIFIER', Identifier::toUnderscoreUpper('sampleIdentifier')
    );
  }

  /**
   * @covers \Papaya\Utility\Text\Identifier::toUnderscoreLower
   */
  public function testToUnderscoreLower() {
    $this->assertEquals(
      'sample_identifier', Identifier::toUnderscoreLower('sampleIdentifier')
    );
  }

  /**
   * @covers \Papaya\Utility\Text\Identifier::toCamelCase
   */
  public function testToCamelCase() {
    $this->assertEquals(
      'sampleIdentifier', Identifier::toCamelCase('Sample_Identifier')
    );
  }

  /**
   * @covers \Papaya\Utility\Text\Identifier::toCamelCase
   */
  public function testToCamelCaseWithUpperCaseFirstChar() {
    $this->assertEquals(
      'SampleIdentifier', Identifier::toCamelCase('Sample_Identifier', TRUE)
    );
  }

  /**
   * @covers \Papaya\Utility\Text\Identifier::toCamelCase
   */
  public function testToCamelCaseWithNumericPart() {
    $this->assertEquals(
      'numeric_23', Identifier::toCamelCase('Numeric_23', FALSE)
    );
  }

  /**
   * @covers \Papaya\Utility\Text\Identifier::toArray
   * @dataProvider provideIdentifiersAndParts
   * @param string $identifier
   * @param array $parts
   */
  public function testToArray($identifier, array $parts) {
    $this->assertEquals(
      $parts, Identifier::toArray($identifier)
    );
  }

  /**
   * @covers \Papaya\Utility\Text\Identifier::toArray
   */
  public function testToArrayWithUnknownStructure() {
    $this->assertEquals(
      array('1.2.3'), Identifier::toArray('1.2.3')
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
