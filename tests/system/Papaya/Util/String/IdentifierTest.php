<?php
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
  */
  public function testToArray($identifier, $parts) {
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
