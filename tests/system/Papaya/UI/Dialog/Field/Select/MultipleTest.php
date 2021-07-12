<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\UI\Dialog\Field\Select {

  use Papaya\TestFramework\TestCase;

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Multiple
   */
  class MultipleTest extends TestCase {

    public function testAppendTo() {
      $select = new Multiple('A Caption', 'a-name', [1 => 'one', 2 => 'two']);
      $this->assertXmlStringEqualsXmlString(
        '<field caption="A Caption" class="DialogFieldSelectMultiple" error="yes" mandatory="yes">
          <select name="a-name" size="5" type="multiple">
            <option value="1">one</option>
            <option value="2">two</option>
          </select>
        </field>',
        $select->getXML()
      );
    }

    public function testAppendToWithBothSelected() {
      $select = new Multiple('A Caption', 'a-name', [1 => 'one', 2 => 'two']);
      $select->setDefaultValue([1,2]);
      $this->assertXmlStringEqualsXmlString(
        '<field caption="A Caption" class="DialogFieldSelectMultiple" error="yes" mandatory="yes">
          <select name="a-name" size="5" type="multiple">
            <option value="1" selected="selected">one</option>
            <option value="2" selected="selected">two</option>
          </select>
        </field>',
        $select->getXML()
      );
    }

    public function testAppendToWithDataAttributes() {
      $select = new Multiple('A Caption', 'a-name', [1 => 'one', 2 => 'two']);
      $select->callbacks()->getOptionData = static function($option, $index) {
        return ['index'=> $index];
      };
      $this->assertXmlStringEqualsXmlString(
        '<field caption="A Caption" class="DialogFieldSelectMultiple" error="yes" mandatory="yes">
          <select name="a-name" size="5" type="multiple">
            <option value="1" data-index="one">one</option>
            <option value="2" data-index="two">two</option>
          </select>
        </field>',
        $select->getXML()
      );
    }

    public function testAppendToWithOptionGroups() {
      $values = new \RecursiveArrayIterator(
        [
          'Group A' =>  [1 => 'one', 2 => 'two']
        ]
      );
      $select = new Multiple('A Caption', 'a-name', $values);
      $this->assertXmlStringEqualsXmlString(
        '<field caption="A Caption" class="DialogFieldSelectMultiple" error="yes" mandatory="yes">
          <select name="a-name" size="5" type="multiple">
            <group caption="Group A">
              <option value="1">one</option>
              <option value="2">two</option>
            </group>
          </select>
        </field>',
        $select->getXML()
      );
    }

    public function testGetSizeAfterSet() {
      $select = new Multiple('A Caption', 'a-name', ['one' => 1, 'two' => 2]);
      $select->setSize(42);
      $this->assertSame(42, $select->getSize());
    }

    public function testGetCallbackGetAfterSet() {
      $callbacks = $this->createMock(Callbacks::class);
      $select = new Multiple('A Caption', 'a-name', ['one' => 1, 'two' => 2]);
      $this->assertSame($callbacks, $select->callbacks($callbacks));
    }
  }

}
