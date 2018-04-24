<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUtilArrayMapperTest extends PapayaTestCase {

  /**
  * @covers PapayaUtilArrayMapper::byIndex
  */
  public function testByIndex() {
    $this->assertEquals(
      array(
        42 => 'caption one',
        'foo' => 'caption two'
      ),
      PapayaUtilArrayMapper::byIndex(
        array(
          42 => array(
            'key' => 'caption one'
          ),
          'foo' => array(
            'key' => 'caption two'
          ),
          'bar' => array(
            'wrong_key' => 'caption three'
          )
        ),
        'key'
      )
    );
  }

  /**
  * @covers PapayaUtilArrayMapper::byIndex
  */
  public function testByIndexWithTraversable() {
    $this->assertEquals(
      array(
        42 => 'caption one',
        'foo' => 'caption two'
      ),
      PapayaUtilArrayMapper::byIndex(
        new ArrayIterator(
          array(
            42 => array(
              'key' => 'caption one'
            ),
            'foo' => array(
              'key' => 'caption two'
            ),
            'bar' => array(
              'wrong_key' => 'caption three'
            )
          )
        ),
        'key'
      )
    );
  }

  /**
  * @covers PapayaUtilArrayMapper::byIndex
  */
  public function testByIndexMappingBothUsingLists() {
    $this->assertEquals(
      array(
        42 => 'caption one',
        21 => 'caption two'
      ),
      PapayaUtilArrayMapper::byIndex(
        array(
          array(
            'id' => 42,
            'title' => 'caption one'
          ),
          array(
            'id' => 21,
            'title' => 'caption two'
          )
        ),
        array('caption', 'title'),
        array('identifier', 'id')
      )
    );
  }

  /**
  * @covers PapayaUtilArrayMapper::byIndex
  */
  public function testByIndexMappingKeyOnly() {
    $this->assertEquals(
      array(
        42 => array(
          'id' => 42,
          'title' => 'caption one'
        ),
        21 => array(
          'id' => 21,
          'title' => 'caption two'
        )
      ),
      PapayaUtilArrayMapper::byIndex(
        array(
          array(
            'id' => 42,
            'title' => 'caption one'
          ),
          array(
            'id' => 21,
            'title' => 'caption two'
          )
        ),
        NULL,
        'id'
      )
    );
  }

  /**
  * @covers PapayaUtilArrayMapper::byIndex
  */
  public function testByIndexMappingKeyNotFound() {
    $this->assertEquals(
      array(
        0 => array(
          'id' => 42,
          'title' => 'caption one'
        ),
        1 => array(
          'id' => 21,
          'title' => 'caption two'
        )
      ),
      PapayaUtilArrayMapper::byIndex(
        array(
          array(
            'id' => 42,
            'title' => 'caption one'
          ),
          array(
            'id' => 21,
            'title' => 'caption two'
          )
        ),
        NULL,
        'identifier'
      )
    );
  }
}
