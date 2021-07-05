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

namespace Papaya\UI\Dialog\Field\Select;
require_once __DIR__.'/../../../../../../bootstrap.php';

class GroupedTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Grouped::setValues
   */
  public function testSetValues() {
    $select = new Grouped(
      'Caption', 'name', array('Group Caption' => array(21 => 'half', 42 => 'full'))
    );
    $this->assertEquals(
      array('Group Caption' => array(21 => 'half', 42 => 'full')), $select->getValues()
    );
    $this->assertEquals(
      new \Papaya\Filter\ArrayElement(array(21, 42)), $select->getFilter()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Grouped::setValues
   */
  public function testSetValuesComplex() {
    $select = new Grouped(
      'Caption',
      'name',
      array(
        array(
          'caption' => 'Group Caption',
          'options' => array(21 => 'half', 42 => 'full')
        )
      )
    );
    $this->assertEquals(
      new \Papaya\Filter\ArrayElement(array(21, 42)), $select->getFilter()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Grouped::appendTo
   * @covers \Papaya\UI\Dialog\Field\Select\Grouped::_appendOptionGroups
   */
  public function testAppendTo() {
    $document = new \Papaya\XML\Document();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $select = new Grouped(
      'Caption', 'name', array('Group Caption' => array(21 => 'half', 42 => 'full'))
    );
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $select->papaya($application);
    $select->collection($this->createMock(\Papaya\UI\Dialog\Fields::class));
    $select->appendTo($node);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<field caption="Caption" class="DialogFieldSelectGrouped" error="yes" mandatory="yes">
        <select name="name" type="dropdown">
          <group caption="Group Caption">
            <option value="21">half</option>
            <option value="42">full</option>
          </group>
        </select>
      </field>',
      $document->saveXML($node->firstChild)
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Grouped::appendTo
   * @covers \Papaya\UI\Dialog\Field\Select\Grouped::_appendOptionGroups
   */
  public function testAppendToWithComplexLabel() {
    $document = new \Papaya\XML\Document();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $select = new Grouped(
      'Caption',
      'name',
      array(
        array(
          'caption' => new \Papaya\UI\Text('Group Caption'),
          'options' => array(21 => 'half', 42 => 'full')
        )
      )
    );
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $select->papaya($application);
    $select->collection($this->createMock(\Papaya\UI\Dialog\Fields::class));
    $select->appendTo($node);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<field caption="Caption" class="DialogFieldSelectGrouped" error="yes" mandatory="yes">
        <select name="name" type="dropdown">
          <group caption="Group Caption">
            <option value="21">half</option>
            <option value="42">full</option>
          </group>
        </select>
      </field>',
      $document->saveXML($node->firstChild)
    );
  }
}
