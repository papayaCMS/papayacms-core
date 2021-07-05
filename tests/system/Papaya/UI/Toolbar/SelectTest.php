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

namespace Papaya\UI\Toolbar;
require_once __DIR__.'/../../../../bootstrap.php';

/**
 * @covers \Papaya\UI\Toolbar\Select
 */
class SelectTest extends \Papaya\TestCase {

  public function testOptionsExpectingException() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Argument $options must be an array or implement Traversable.');
    /** @noinspection PhpParamsInspection */
    new Select('foo', 'failed');
  }

  public function testGetCurrentValue() {
    $select = new Select('foo', array(23 => 'bar'));
    $select->defaultValue = 21;
    $select->papaya(
      $this->mockPapaya()->application(
        array(
          'Request' => $this->mockPapaya()->request(array('foo' => '23'), 'http://www.test.tld')
        )
      )
    );
    $this->assertSame(
      23, $select->getCurrentValue()
    );
  }

  public function testGetCurrentValueNotInListUseDefault() {
    $select = new Select('foo', array(42 => 'bar'));
    $select->defaultValue = 21;
    $select->papaya(
      $this->mockPapaya()->application(
        array(
          'Request' => $this->mockPapaya()->request(array('foo' => '23'), 'http://www.test.tld')
        )
      )
    );
    $this->assertSame(
      21, $select->getCurrentValue()
    );
  }

  public function testGetCurrentValueAfterSet() {
    $select = new Select('foo', array(42 => 'bar'));
    $select->currentValue = 42;
    $this->assertSame(
      42, $select->currentValue
    );
  }

  public function testAppendTo() {
    $document = new \Papaya\XML\Document;
    $document->appendElement('sample');
    $select = new Select('foo', array('foo' => 'bar'));
    $select->papaya($this->mockPapaya()->application());
    $select->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample>
        <combo name="foo" action="http://www.test.tld/test.html">
        <option value="foo">bar</option>
        </combo>
        </sample>',
      $document->saveXML($document->documentElement)
    );
  }

  public function testAppendToWithAllProperties() {
    $document = new \Papaya\XML\Document;
    $document->appendElement('sample');
    $select = new Select('foo', array('foo' => 'bar'));
    $select->papaya($this->mockPapaya()->application());
    $select->defaultCaption = 'Please Select';
    $select->defaultValue = 42;
    $select->caption = 'Sample Caption';
    $select->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample>
        <combo name="foo" action="http://www.test.tld/test.html" title="Sample Caption">
        <option value="42">Please Select</option>
        <option value="foo">bar</option>
        </combo>
        </sample>',
      $document->saveXML($document->documentElement)
    );
  }

  public function testAppendToWithActionParameters() {
    $reference = $this->createMock(\Papaya\UI\Reference::class);
    $reference
      ->expects($this->any())
      ->method('getParameterGroupSeparator')
      ->will($this->returnValue('[]'));
    $reference
      ->expects($this->once())
      ->method('getRelative')
      ->with(NULL, FALSE)
      ->will($this->returnValue('sample.php'));
    $reference
      ->expects($this->once())
      ->method('getParametersList')
      ->will($this->returnValue(array('additional' => '42')));
    $document = new \Papaya\XML\Document;
    $document->appendElement('sample');
    $select = new Select('foo', array('foo' => 'bar'));
    $select->papaya($this->mockPapaya()->application());
    $select->reference = $reference;
    $select->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample>
        <combo name="foo" action="sample.php">
        <parameter name="additional" value="42"/>
        <option value="foo">bar</option>
        </combo>
        </sample>',
      $document->saveXML($document->documentElement)
    );
  }

  public function testAppendToWithCurrentValue() {
    $document = new \Papaya\XML\Document;
    $document->appendElement('sample');
    $select = new Select('foo', array('foo' => 'bar'));
    $select->papaya($this->mockPapaya()->application());
    $select->currentValue = 'foo';
    $select->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample>
        <combo name="foo" action="http://www.test.tld/test.html">
        <option value="foo" selected="selected">bar</option>
        </combo>
        </sample>',
      $document->saveXML($document->documentElement)
    );
  }
}
