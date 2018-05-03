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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaContentStructureTest extends PapayaTestCase {

  /**
   * @covers PapayaContentStructure::load
   */
  public function testLoad() {
    $pages = $this->createMock(PapayaContentStructurePages::class);
    $pages
      ->expects($this->once())
      ->method('load')
      ->with($this->isInstanceOf(PapayaXmlElement::class));

    $definition = new PapayaContentStructure();
    $definition->pages($pages);
    $definition->load(__DIR__.'/TestData/structure.xml');
  }

  /**
   * @covers PapayaContentStructure::load
   */
  public function testLoadWithString() {
    $pages = $this->createMock(PapayaContentStructurePages::class);
    $pages
      ->expects($this->once())
      ->method('load')
      ->with($this->isInstanceOf(PapayaXmlElement::class));

    $definition = new PapayaContentStructure();
    $definition->pages($pages);
    $definition->load(/** @lang XML */'<structure/>');
  }

  /**
   * @covers PapayaContentStructure::load
   */
  public function testLoadWithEmptyString() {
    $pages = $this->createMock(PapayaContentStructurePages::class);
    $pages
      ->expects($this->never())
      ->method('load');

    $definition = new PapayaContentStructure();
    $definition->pages($pages);
    $definition->load('');
  }

  /**
   * @covers PapayaContentStructure::load
   */
  public function testLoadWithXmlElement() {
    $document = new PapayaXmlDocument();
    $node = $document->appendElement('structure');

    $pages = $this->createMock(PapayaContentStructurePages::class);
    $pages
      ->expects($this->once())
      ->method('load')
      ->with($this->isInstanceOf(PapayaXmlElement::class));

    $definition = new PapayaContentStructure();
    $definition->pages($pages);
    $definition->load($node);
  }

  /**
   * @covers PapayaContentStructure::getXmlDocument
   */
  public function testGetXmlDocumentWithExistingValue() {
    $definition = new PapayaContentStructure();
    $definition->pages()->add($page = new PapayaContentStructurePage());
    $page->name = 'page_one';
    $page->groups()->add($group = new PapayaContentStructureGroup($page));
    $group->name = 'group_one';
    $group->values()->add($value = new PapayaContentStructureValue($group));
    $value->name = 'value_one';

    $data = array(
      'page_one' => array(
        'group_one' => array(
          'value_one' => 42
        )
      )
    );

    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */'<values>
        <page_one>
          <group_one>
            <value_one type="text">42</value_one>
          </group_one>
        </page_one>
      </values>',
      $definition->getXmlDocument($data)->documentElement->saveXml()
    );
  }

  /**
   * @covers PapayaContentStructure::getXmlDocument
   */
  public function testGetXmlDocumentWithXhtmlValue() {
    $definition = new PapayaContentStructure();
    $definition->pages()->add($page = new PapayaContentStructurePage());
    $page->name = 'page_one';
    $page->groups()->add($group = new PapayaContentStructureGroup($page));
    $group->name = 'group_one';
    $group->values()->add($value = new PapayaContentStructureValue($group));
    $value->name = 'value_one';
    $value->type = 'xhtml';

    $data = array(
      'page_one' => array(
        'group_one' => array(
          'value_one' => /** @lang XML */'<b>Xhtml</b>'
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
      $definition->getXmlDocument($data)->documentElement->saveXml()
    );
  }

  /**
   * @covers PapayaContentStructure::getXmlDocument
   */
  public function testGetXmlDocumentWithEmptyValueUsingDefault() {
    $definition = new PapayaContentStructure();
    $definition->pages()->add($page = new PapayaContentStructurePage());
    $page->name = 'page_one';
    $page->groups()->add($group = new PapayaContentStructureGroup($page));
    $group->name = 'group_one';
    $group->values()->add($value = new PapayaContentStructureValue($group));
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
      $definition->getXmlDocument($data)->documentElement->saveXml()
    );
  }

  /**
   * @covers PapayaContentStructure::getArray
   */
  public function testGetArrayFromXmlWithExistingValue() {
    $definition = new PapayaContentStructure();
    $definition->pages()->add($page = new PapayaContentStructurePage());
    $page->name = 'page_one';
    $page->groups()->add($group = new PapayaContentStructureGroup($page));
    $group->name = 'group_one';
    $group->values()->add($value = new PapayaContentStructureValue($group));
    $value->name = 'value_one';

    $document = new PapayaXmlDocument();
    $document->loadXml(
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
   * @covers PapayaContentStructure::getArray
   */
  public function testGetArrayFromXmlWithExistingXhtmlValue() {
    $definition = new PapayaContentStructure();
    $definition->pages()->add($page = new PapayaContentStructurePage());
    $page->name = 'page_one';
    $page->groups()->add($group = new PapayaContentStructureGroup($page));
    $group->name = 'group_one';
    $group->values()->add($value = new PapayaContentStructureValue($group));
    $value->name = 'value_one';
    $value->type = 'xhtml';

    $document = new PapayaXmlDocument();
    $document->loadXml(
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
            'value_one' => /** @lang XML */'<b>Xhtml</b>'
          )
        )
      ),
      $definition->getArray($document->documentElement)
    );
  }

  /**
   * @covers PapayaContentStructure::pages
   */
  public function testPagesGetAfterSet() {
    $definition = new PapayaContentStructure();
    $definition->pages($pages = new PapayaContentStructurePages());
    $this->assertSame($pages, $definition->pages());
  }

  /**
   * @covers PapayaContentStructure::pages
   */
  public function testPagesImplicitCreate() {
    $definition = new PapayaContentStructure();
    $this->assertInstanceOf(PapayaContentStructurePages::class, $definition->pages());
  }

  /**
   * @covers PapayaContentStructure::getIterator
   */
  public function testGetIteratorReturnsPages() {
    $definition = new PapayaContentStructure();
    $this->assertInstanceOf(PapayaContentStructurePages::class, $definition->getIterator());
  }

  /**
   * @covers PapayaContentStructure::getPage
   */
  public function testGetPageExpectingPage() {
    $page = new PapayaContentStructurePage();
    $page->name = 'PAGE';
    $pages = new PapayaContentStructurePages();
    $pages->add($page);
    $definition = new PapayaContentStructure();
    $definition->pages($pages);
    $this->assertSame($page, $definition->getPage('PAGE'));
  }

  /**
   * @covers PapayaContentStructure::getPage
   */
  public function testGetPageExpectingNull() {
    $pages = new PapayaContentStructurePages();
    $definition = new PapayaContentStructure();
    $definition->pages($pages);
    $this->assertNull($definition->getPage('PAGE'));
  }
}

