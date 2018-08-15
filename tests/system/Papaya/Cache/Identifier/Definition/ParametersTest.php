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

namespace Papaya\Cache\Identifier\Definition;

require_once __DIR__.'/../../../../../bootstrap.php';

class ParametersTest extends \PapayaTestCase {

  /**
   * @covers       Parameters
   * @dataProvider provideParameterData
   * @param mixed $expected
   * @param mixed $group
   * @param mixed $names
   * @param mixed $data
   */
  public function testGetStatus($expected, $group, $names, $data) {
    $definition = new Parameters($names, $group);
    $definition->parameterGroup($group);
    $definition->papaya(
      $this->mockPapaya()->application(
        array(
          'request' => $this->mockPapaya()->request($data)
        )
      )
    );
    $this->assertEquals($expected, $definition->getStatus());
  }

  /**
   * @covers Parameters
   */
  public function testGetSourcesWithDefaultMethodGet() {
    $definition = new Parameters(array('foo'));
    $this->assertEquals(
      \Papaya\Cache\Identifier\Definition::SOURCE_URL,
      $definition->getSources()
    );
  }

  /**
   * @covers Parameters
   */
  public function testGetSourcesWithMethodPost() {
    $definition = new Parameters(
      array('foo'), NULL, \Papaya\Request\Parameters\Access::METHOD_POST
    );
    $this->assertEquals(
      \Papaya\Cache\Identifier\Definition::SOURCE_REQUEST,
      $definition->getSources()
    );
  }

  public static function provideParameterData() {
    return array(
      array(
        TRUE,
        NULL,
        array('foobar'),
        array('foo' => 'bar')
      ),
      array(
        array(Parameters::class => array('foo' => 'bar')),
        NULL,
        array('foo'),
        array('foo' => 'bar')
      ),
      array(
        array(Parameters::class => array('foo' => '')),
        NULL,
        array('foo'),
        array('foo' => '')
      ),
      array(
        array(Parameters::class => array('bar' => '42')),
        NULL,
        array('foo', 'bar'),
        array('bar' => '42')
      ),
      array(
        array(Parameters::class => array('foo' => '21', 'bar' => '42')),
        NULL,
        array('foo', 'bar'),
        array('foo' => '21', 'bar' => '42')
      ),
      array(
        array(Parameters::class => array('bar' => '42')),
        'foo',
        array('bar'),
        array('foo' => array('bar' => '42'))
      ),
      array(
        array(Parameters::class => array('foo[bar]' => '42')),
        NULL,
        array('foo/bar'),
        array('foo' => array('bar' => '42'))
      ),
      array(
        array(Parameters::class => array('bar' => '42')),
        NULL,
        'bar',
        array('bar' => '42')
      ),
    );
  }
}
