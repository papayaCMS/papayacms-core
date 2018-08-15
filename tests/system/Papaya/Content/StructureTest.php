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

namespace Papaya\Content;

require_once __DIR__.'/../../../bootstrap.php';

class Test extends \PapayaTestCase {

  /**
   * @covers Structure::load
   */
  public function testLoad() {
    $pages = $this->createMock(Structure\Pages::class);
    $pages
      ->expects($this->once())
      ->method('load')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));

    $definition = new Structure();
    $definition->pages($pages);
    $definition->load(__DIR__.'/TestData/structure.xml');
  }

  /**
   * @covers Structure::load
   */
  public function testLoadWithString() {
    $pages = $this->createMock(Structure\Pages::class);
    $pages
      ->expects($this->once())
      ->method('load')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));

    $definition = new Structure();
    $definition->pages($pages);
    $definition->load(/** @lang XML */
      '<structure/>');
  }

  /**
   * @covers Structure::load
   */
  public function testLoadWithEmptyString() {
    $pages = $this->createMock(Structure\Pages::class);
    $pages
      ->expects($this->never())
      ->method('load');

    $definition = new Structure();
    $definition->pages($pages);
    $definition->load('');
  }

  /**
   * @covers Structure::load
   */
  public function testLoadWithXmlElement() {
    $document = new \Papaya\XML\Document();
    $node = $document->appendElement('structure');

    $pages = $this->createMock(Structure\Pages::class);
    $pages
      ->expects($this->once())
      ->method('load')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));

    $definition = new Structure();
    $definition->pages($pages);
    $definition->load($node);
  }

  /**
   * @covers Structure::getXMLDocument
   */
  public function testGetXmlDocumentWithExistingValue() {
    $definition = new Structure();
    $definition->pages()->add($page = new Structure\Page());
    $page->name = 'page_one';
    $page->groups()->add($group = new Structure\Group($page));
    $group->name = 'group_one';
    $group->values()->add($value = new Structure\Value($group));
    $value->name = 'value_one';

    $data = array(
      'page_one' => array(
        'group_one' => array(
          'value_one' => 42
        )
      )
    );

    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<values>
        <page_one>
          <group_one>
            <value_one type="text">42</value_one>
          </group_one>
        </page_one>
      </values>',
      $definition->getXMLDocument($data)->documentElement->saveXML()
    );
  }

  /**
   * @covers Structure::getXMLDocument
   */
  public function testGetXmlDocumentWithXhtmlValue() {
    $definition = new Structure();
    $definition->pages()->add($page = new Structure\Page());
    $page->name = 'page_one';
    $page->groups()->add($group = new Structure\Group($page));
    $group->name = 'group_one';
    $group->values()->add($value = new Structure\Value($group));
    $value->name = 'value_one';
    $value->type = 'xhtml';

    $data = array(
      'page_one' => array(
        'group_one' => array(
          'value_one' => /** @lang XML */
            '<b>Xhtml</b>'
        )
      )
    );

    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<values>
        <page_one>
          <group_one>
            <value_one type="xhtml"><b>Xhtml</b></value_one>
          </group_one>
        </page_one>
      </values>',
      $definition->getXMLDocument($data)->documentElement->saveXML()
    );
  }

  /**
   * @covers Structure::getXMLDocument
   */
  public function testGetXmlDocumentWithEmptyValueUsingDefault() {
    $definition = new Structure();
    $definition->pages()->add($page = new Structure\Page());
    $page->name = 'page_one';
    $page->groups()->add($group = new Structure\Group($page));
    $group->name = 'group_one';
    $group->values()->add($value = new Structure\Value($group));
    $value->name = 'value_one';
    $value->default = 21;

    $data = array(
      'page_one' => array(
        'group_one' => array(
          'value_one' => '   '
        )
      )
    );

    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<values>
        <page_one>
          <group_one>
            <value_one type="text">21</value_one>
          </group_one>
        </page_one>
      </values>',
      $definition->getXMLDocument($data)->documentElement->saveXML()
    );
  }

  /**
   * @covers Structure::getArray
   */
  public function testGetArrayFromXmlWithExistingValue() {
    $definition = new Structure();
    $definition->pages()->add($page = new Structure\Page());
    $page->name = 'page_one';
    $page->groups()->add($group = new Structure\Group($page));
    $group->name = 'group_one';
    $group->values()->add($value = new Structure\Value($group));
    $value->name = 'value_one';

    $document = new \Papaya\XML\Document();
    $document->loadXML(
    /** @lang XML */
      '<values>
        <page_one>
          <group_one>
            <value_one type="text">42</value_one>
          </group_one>
        </page_one>
      </values>'
    );

    $this->assertEquals(
      array(
        'page_one' => array(
          'group_one' => array(
            'value_one' => 42
          )
        )
      ),
      $definition->getArray($document->documentElement)
    );
  }

  /**
   * @covers Structure::getArray
   */
  public function testGetArrayFromXmlWithExistingXhtmlValue() {
    $definition = new Structure();
    $definition->pages()->add($page = new Structure\Page());
    $page->name = 'page_one';
    $page->groups()->add($group = new Structure\Group($page));
    $group->name = 'group_one';
    $group->values()->add($value = new Structure\Value($group));
    $value->name = 'value_one';
    $value->type = 'xhtml';

    $document = new \Papaya\XML\Document();
    $document->loadXML(
    /** @lang XML */
      '<values>
        <page_one>
          <group_one>
            <value_one type="xhtml"><b>Xhtml</b></value_one>
          </group_one>
        </page_one>
      </values>'
    );

    $this->assertEquals(
      array(
        'page_one' => array(
          'group_one' => array(
            'value_one' => /** @lang XML */
              '<b>Xhtml</b>'
          )
        )
      ),
      $definition->getArray($document->documentElement)
    );
  }

  /**
   * @covers Structure::pages
   */
  public function testPagesGetAfterSet() {
    $definition = new Structure();
    $definition->pages($pages = new Structure\Pages());
    $this->assertSame($pages, $definition->pages());
  }

  /**
   * @covers Structure::pages
   */
  public function testPagesImplicitCreate() {
    $definition = new Structure();
    $this->assertInstanceOf(Structure\Pages::class, $definition->pages());
  }

  /**
   * @covers Structure::getIterator
   */
  public function testGetIteratorReturnsPages() {
    $definition = new Structure();
    $this->assertInstanceOf(Structure\Pages::class, $definition->getIterator());
  }

  /**
   * @covers Structure::getPage
   */
  public function testGetPageExpectingPage() {
    $page = new Structure\Page();
    $page->name = 'PAGE';
    $pages = new Structure\Pages();
    $pages->add($page);
    $definition = new Structure();
    $definition->pages($pages);
    $this->assertSame($page, $definition->getPage('PAGE'));
  }

  /**
   * @covers Structure::getPage
   */
  public function testGetPageExpectingNull() {
    $pages = new Structure\Pages();
    $definition = new Structure();
    $definition->pages($pages);
    $this->assertNull($definition->getPage('PAGE'));
  }
}

