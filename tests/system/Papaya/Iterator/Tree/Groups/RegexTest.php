<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaIteratorTreeGroupsRegexTest extends PapayaTestCase {

  /**
   * @covers PapayaIteratorTreeGroupsRegex
   */
  public function testIteration() {
    $iterator = new PapayaIteratorTreeGroupsRegex(
      array('Administration', 'Application', 'Cache', 'Configuration', 'Iterator'),
      '(^(?P<char>.))',
      'char'
    );
    $this->assertEquals(
      array(
         0 => 'A',
         1 => 'Administration',
         2 => 'Application',
         3 => 'C',
         4 => 'Cache',
         5 => 'Configuration',
         6 => 'I',
         7 => 'Iterator'
      ),
      iterator_to_array(
        new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST),
        FALSE
      )
    );
  }

  /**
   * @covers PapayaIteratorTreeGroupsRegex
   */
  public function testIterationKeppingIndex() {
    $iterator = new PapayaIteratorTreeGroupsRegex(
      array('admin' => 'Administration', 'app' => 'Application'),
      '(^(?P<char>.))',
      'char'
    );
    $this->assertEquals(
      array(
         0 => 'A',
         'admin' => 'Administration',
         'app' => 'Application'
      ),
      iterator_to_array(
        new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST)
      )
    );
  }

  /**
   * @covers PapayaIteratorTreeGroupsRegex
   */
  public function testIterationWithoutMatchExpectingElementAsGroup() {
    $iterator = new PapayaIteratorTreeGroupsRegex(
      array('Administration', 'Application'),
      '(^-$)'
    );
    $this->assertEquals(
      array(
         0 => 'Administration',
         1 => 'Application'
      ),
      iterator_to_array(
        new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST),
        FALSE
      )
    );
  }

  /**
   * @covers PapayaIteratorTreeGroupsRegex
   */
  public function testIterationWithoutMatchKeepingEmptyStringIndex() {
    $iterator = new PapayaIteratorTreeGroupsRegex(
      array('' => 'Empty', 'some' => 'Filled'),
      '(^-$)'
    );
    $this->assertEquals(
      array(
         '' => 'Empty',
         'some' => 'Filled'
      ),
      iterator_to_array(
        new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST),
        TRUE
      )
    );
  }

  /**
   * @covers PapayaIteratorTreeGroupsRegex
   */
  public function testIterationWithNotAllItemsMatchingToAGroup() {
    $iterator = new PapayaIteratorTreeGroupsRegex(
      array('Administration', '# no group', 'Application'),
      '(^\w)'
    );
    $this->assertEquals(
      array(
         0 => 'A',
         1 => 'Administration',
         2 => 'Application',
         3 => '# no group'
      ),
      iterator_to_array(
        new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST),
        FALSE
      )
    );
  }

  /**
   * @covers PapayaIteratorTreeGroupsRegex
   */
  public function testIterationMatchingKeys() {
    $iterator = new PapayaIteratorTreeGroupsRegex(
      array('admin' => 'Administration', 'app' => 'Application'),
      '(^(?P<char>.))',
      'char',
      PapayaIteratorTreeGroupsRegex::GROUP_KEYS
    );
    $this->assertEquals(
      array(
         0 => 'a',
         'admin' => 'Administration',
         'app' => 'Application'
      ),
      iterator_to_array(
        new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST)
      )
    );
  }
}