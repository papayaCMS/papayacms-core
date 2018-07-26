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

class PapayaUtilArrayMapperTest extends PapayaTestCase {

  /**
  * @covers \PapayaUtilArrayMapper::byIndex
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
  * @covers \PapayaUtilArrayMapper::byIndex
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
  * @covers \PapayaUtilArrayMapper::byIndex
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
  * @covers \PapayaUtilArrayMapper::byIndex
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
  * @covers \PapayaUtilArrayMapper::byIndex
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
