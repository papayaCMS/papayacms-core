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

namespace Papaya\CMS\Content\Structure;

require_once __DIR__.'/../../../../../bootstrap.php';

class ValuesTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Content\Structure\Values::__construct
   */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $values = new Values($group);
    $this->assertEquals(Value::class, $values->getItemClass());
  }

  /**
   * @covers \Papaya\CMS\Content\Structure\Values::load
   */
  public function testLoad() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $document = new \Papaya\XML\Document();
    $document->load(__DIR__.'/../TestData/structure.xml');
    $values = new Values($group);
    $values->load($document->xpath()->evaluate('//page[1]/group[1]')->item(0));
    $this->assertCount(1, $values);
    $this->assertEquals('Font color', $values[0]->title);
    $this->assertEquals('COLOR', $values[0]->name);
    $this->assertEquals('text', $values[0]->type);
    $this->assertEquals('color', $values[0]->fieldType);
    $this->assertEquals('#FF0000', $values[0]->default);
    $this->assertEquals('main font color', $values[0]->hint);
  }

  /**
   * @covers \Papaya\CMS\Content\Structure\Values::load
   */
  public function testLoadValueWithMultipleParameters() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $document = new \Papaya\XML\Document();
    $document->load(__DIR__.'/../TestData/structure.xml');
    $values = new Values($group);
    $values->load($document->xpath()->evaluate('//page[1]/group[2]')->item(0));
    $this->assertCount(1, $values);
    $this->assertEquals(
      array(
        'justify' => 'Justify',
        'left' => 'Left',
        'right' => 'Right'
      ),
      $values[0]->fieldParameters
    );
    $this->assertEquals('Text Alignment', $values[0]->hint);
  }

  /**
   * @covers \Papaya\CMS\Content\Structure\Values::load
   */
  public function testLoadValueWithSimpleParameterAsAttribute() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $document = new \Papaya\XML\Document();
    $document->load(__DIR__.'/../TestData/structure.xml');
    $values = new Values($group);
    $values->load($document->xpath()->evaluate('//page[1]/group[3]')->item(0));
    $this->assertCount(2, $values);
    $this->assertEquals('200', $values[0]->fieldParameters);
  }

  /**
   * @covers \Papaya\CMS\Content\Structure\Values::load
   */
  public function testLoadValueWithParametersList() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $document = new \Papaya\XML\Document();
    $document->load(__DIR__.'/../TestData/structure.xml');
    $values = new Values($group);
    $values->load($document->xpath()->evaluate('//page[1]/group[3]')->item(0));
    $this->assertCount(2, $values);
    $this->assertEquals(
      array(
        'foo.png' => 'foo.png',
        'bar.png' => 'bar.png'
      ),
      $values[1]->fieldParameters
    );
  }
}
