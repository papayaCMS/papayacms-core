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

namespace Papaya\UI\Dialog\Field;

require_once __DIR__.'/../../../../../bootstrap.php';

class CallbackTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Dialog\Field\Callback
   */
  public function testConstructorWithAllArguments() {
    $xhtml = new Callback(
      'Caption', 'name', array($this, 'callbackGetFieldString'), 42, $this->createMock(\Papaya\Filter::class)
    );
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<field caption="Caption" class="DialogFieldCallback" error="no">
        <select/>
      </field>',
      $xhtml->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Callback
   */
  public function testAppendToWithCallbackReturningString() {
    $xhtml = new Callback(
      'Caption', 'name', array($this, 'callbackGetFieldString')
    );
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<field caption="Caption" class="DialogFieldCallback" error="no">
        <select/>
      </field>',
      $xhtml->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Callback
   */
  public function testAppendToWithCallbackReturningDomElement() {
    $xhtml = new Callback(
      'Caption', 'name', array($this, 'callbackGetFieldDomElement')
    );
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<field caption="Caption" class="DialogFieldCallback" error="no">
        <select/>
      </field>',
      $xhtml->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Callback
   */
  public function testAppendToWithCallbackReturningXMLAppendable() {
    $xhtml = new Callback(
      'Caption',
      'name',
      function() {
        $result = $this->createMock(\Papaya\XML\Appendable::class);
        $result
          ->expects($this->once())
          ->method('appendTo')
          ->with($this->isInstanceOf(\Papaya\XML\Element::class));
        return $result;
      }
    );
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<field caption="Caption" class="DialogFieldCallback" error="no"/>',
      $xhtml->getXML()
    );
  }

  public function callbackGetFieldString() {
    return /** @lang XML */
      '<select/>';
  }

  public function callbackGetFieldDomElement() {
    $document = new \DOMDocument();
    return $document->createElement('select');
  }
}
