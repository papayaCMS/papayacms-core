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

namespace Papaya\UI {

  require_once __DIR__.'/../../../bootstrap.php';

  class ControlTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\UI\Control::getXML
     */
    public function testGetXml() {
      $control = new Control_TestProxy();
      $document = new \Papaya\XML\Document;
      $control->nodeStub = array(
        $document->appendElement('sample')
      );
      $this->assertEquals(
      /** @lang XML */
        '<sample/>', $control->getXML()
      );
    }

    /**
     * @covers \Papaya\UI\Control::getXML
     */
    public function testGetXmlWithTextNode() {
      $control = new Control_TestProxy();
      $document = new \Papaya\XML\Document;
      $control->nodeStub = array(
        $document->createTextNode('sample')
      );
      $this->assertEquals(
        'sample', $control->getXML()
      );
    }

    /**
     * @covers \Papaya\UI\Control::getXML
     */
    public function testGetXmlWithSeveralNodes() {
      $control = new Control_TestProxy();
      $document = new \Papaya\XML\Document;
      $control->nodeStub = array(
        $document->createTextNode('sample'),
        $document->createElement('sample'),
        $document->createComment('comment')
      );
      $this->assertEquals(
      // language=XML prefix=<fragment> suffix=</fragment>
        'sample<sample/><!--comment-->', $control->getXML()
      );
    }
  }

  class Control_TestProxy extends Control {

    public $nodeStub = array();

    public function appendTo(\Papaya\XML\Element $parent) {
      foreach ($this->nodeStub as $node) {
        $parent->appendChild(
          $parent->ownerDocument->importNode($node)
        );
      }
    }
  }
}
