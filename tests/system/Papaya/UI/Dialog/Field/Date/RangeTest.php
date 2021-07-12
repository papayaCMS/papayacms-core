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

namespace Papaya\UI\Dialog\Field\Date {

  use Papaya\Filter;
  use Papaya\TestFramework\TestCase;

  require_once __DIR__.'/../../../../../../bootstrap.php';

  /**
   * @covers \Papaya\UI\Dialog\Field\Date\Range
   */
  class RangeTest extends TestCase {

    public function testGetFilterIfMandatory() {
      $range = new Range('Caption', 'name', TRUE);
      $this->assertInstanceOf(Filter\AssociativeArray::class, $range->getFilter());
    }

    public function testGetFilterIfNotMandatory() {
      $range = new Range('Caption', 'name', FALSE);
      $this->assertInstanceOf(Filter\LogicalOr::class, $range->getFilter());
    }

    public function testCreateWithInvalidTimeOptionExpectingException() {
      $this->expectException(\InvalidArgumentException::class);
      new Range('Caption', 'name', FALSE, 42);
    }

    public function testAppendTo() {
      $field = new Range('Caption', 'name');
      $field->papaya($this->mockPapaya()->application());
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<field caption="Caption" class="DialogFieldDateRange" data-include-time="false" error="no">
        <group data-selected-page="fromTo">
          <labels/>
          <input name= "name[start]" type="date" />
          <input name= "name[end]" type="date" />
        </group> 
      </field> ',
        $field->getXML()
      );
    }

    public function testAppendToWithValues() {
      $field = new Range('Caption', 'name');
      $field->papaya($this->mockPapaya()->application());
      $field->setDefaultValue(['start' => '2019-10-26', 'end' => '2019-10-28']);
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<field caption="Caption" class="DialogFieldDateRange" data-include-time="false" error="no">
        <group data-selected-page="fromTo">
          <labels/>
          <input name="name[start]" type="date" value="1572048000">2019-10-26</input>
          <input name="name[end]" type="date" value="1572220800">2019-10-28</input>
        </group> 
      </field> ',
        $field->getXML()
      );
    }

    public function testAppendToWithValuesIncludingTime() {
      $field = new Range('Caption', 'name', FALSE, Filter\Date::DATE_OPTIONAL_TIME);
      $field->papaya($this->mockPapaya()->application());
      $field->setDefaultValue(['start' => '2019-10-26 8:00', 'end' => '2019-10-28 9:00']);
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<field caption="Caption" class="DialogFieldDateRange" data-include-time="true" error="no">
        <group data-selected-page="fromTo">
          <labels/>
          <input name="name[start]" type="datetime" value="1572076800">2019-10-26 08:00:00</input>
          <input name="name[end]" type="datetime" value="1572253200">2019-10-28 09:00:00</input>
        </group> 
      </field> ',
        $field->getXML()
      );
    }

    public function testAppendToWithLabels() {
      $field = new Range('Caption', 'name');
      $field->papaya($this->mockPapaya()->application());
      $field->labels(new \ArrayIterator(['field_id' => 'label text']));
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<field caption="Caption" class="DialogFieldDateRange" data-include-time="false" error="no">
        <group data-selected-page="fromTo">
          <labels>
            <label for="field_id">label text</label>
          </labels>
          <input name= "name[start]" type="date" />
          <input name= "name[end]" type="date" />
        </group> 
      </field> ',
        $field->getXML()
      );
    }
  }
}
