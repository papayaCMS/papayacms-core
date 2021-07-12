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

namespace Papaya\UI\Dialog {

  require_once __DIR__.'/../../../../bootstrap.php';

  class ElementTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\UI\Dialog\Element::collect
     */
    public function testCollectWithDialog() {
      $dialog = $this->getDialogMock();
      $element = new Element_TestProxy();
      $element->collection($this->getCollectionMock($dialog));
      $this->assertTrue($element->collect());
    }

    /**
     * @covers \Papaya\UI\Dialog\Element::collect
     */
    public function testCollectWithoutDialog() {
      $element = new Element_TestProxy();
      $element->collection($this->getCollectionMock());
      $this->assertFalse($element->collect());
    }

    /**
     * @covers \Papaya\UI\Dialog\Element::_getParameterName
     * @dataProvider provideKeysForGetParameterName
     * @param string $expected
     * @param string|array $keys
     */
    public function testGetParameterName($expected, $keys) {
      $element = new Element_TestProxy();
      $request = $this->mockPapaya()->request();
      $application = $this->mockPapaya()->application(array('request' => $request));
      $element->papaya($application);
      $element->collection($this->getCollectionMock());
      $this->assertEquals(
        $expected, $element->_getParameterName($keys)
      );
    }

    /**
     * @covers \Papaya\UI\Dialog\Element::_getParameterName
     */
    public function testGetParameterNameWithDialog() {
      $dialog = $this->getDialogMock();
      $dialog
        ->expects($this->once())
        ->method('parameterGroup')
        ->will($this->returnValue('group'));
      $dialog
        ->expects($this->once())
        ->method('getParameterName')
        ->will($this->returnValue(new \Papaya\Request\Parameters\Name('param')));
      $element = new Element_TestProxy();
      $request = $this->mockPapaya()->request();
      $application = $this->mockPapaya()->application(array('request' => $request));
      $element->papaya($application);
      $element->collection($this->getCollectionMock($dialog));
      $this->assertEquals(
        'group[param]', $element->_getParameterName('param')
      );
    }

    /**
     * @covers \Papaya\UI\Dialog\Element::hasDialog
     */
    public function testHasDialogExpectingTrue() {
      $dialog = $this->getDialogMock();
      $element = new Element_TestProxy();
      $element->collection($this->getCollectionMock($dialog));
      $this->assertTrue($element->hasDialog());
    }

    /**
     * @covers \Papaya\UI\Dialog\Element::hasDialog
     */
    public function testHasDialogWithoutAttachedCollectionExpectingFalse() {
      $element = new Element_TestProxy();
      $this->assertFalse($element->hasDialog());
    }

    /**
     * @covers \Papaya\UI\Dialog\Element::hasDialog
     */
    public function testHasDialogWithoutAttachedDialogExpectingFalse() {
      $element = new Element_TestProxy();
      $element->collection($this->getCollectionMock());
      $this->assertFalse($element->hasDialog());
    }

    /**
     * @covers \Papaya\UI\Dialog\Element::getDialog
     */
    public function testGetDialog() {
      $dialog = $this->getDialogMock();
      $element = new Element_TestProxy();
      $element->collection($this->getCollectionMock($dialog));
      $this->assertSame($dialog, $element->getDialog());
    }

    /**
     * @covers \Papaya\UI\Dialog\Element::getDialog
     */
    public function testGetDialogExpectingNull() {
      $element = new Element_TestProxy();
      $this->assertNull($element->getDialog());
    }

    /*****************************
     * Data Provider
     *****************************/

    public static function provideKeysForGetParameterName() {
      return array(
        array('test', 'test'),
        array('group[test]', array('group', 'test')),
        array('group[subgroup][test]', array('group', 'subgroup', 'test'))
      );
    }

    /*****************************
     * Mocks
     *****************************/

    private function getDialogMock() {
      return $this
        ->getMockBuilder(\Papaya\UI\Dialog::class)
        ->setConstructorArgs(array(new \stdClass()))
        ->getMock();
    }

    public function getCollectionMock($owner = NULL) {
      $collection = $this->createMock(Elements::class);
      if ($owner) {
        $collection
          ->expects($this->any())
          ->method('hasOwner')
          ->will($this->returnValue(TRUE));
        $collection
          ->expects($this->any())
          ->method('owner')
          ->will($this->returnValue($owner));
      } else {
        $collection
          ->expects($this->any())
          ->method('hasOwner')
          ->will($this->returnValue(FALSE));
      }
      return $collection;
    }
  }

  class Element_TestProxy extends Element {

    public function appendTo(\Papaya\XML\Element $parent) {
    }

    public function _getParameterName($key, $withGroup = TRUE) {
      return parent::_getParameterName($key, $withGroup);
    }
  }
}
