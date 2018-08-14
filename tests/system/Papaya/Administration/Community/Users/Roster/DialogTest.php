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

namespace Papaya\Administration\Community\Users\Roster;

class DialogTest extends \PapayaTestCase {

  /**
   * @covers Dialog::prepare
   */
  public function testPrepare() {
    $dialog = new Dialog();
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->prepare();
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<dialog-box action="http://www.test.tld/test.html" method="get">
        <title caption="Users"/>
        <options>
          <option name="USE_CONFIRMATION" value="no"/>
          <option name="USE_TOKEN" value="no"/>
          <option name="PROTECT_CHANGES" value="yes"/>
          <option name="CAPTION_STYLE" value="0"/>
          <option name="DIALOG_WIDTH" value="s"/>
          <option name="TOP_BUTTONS" value="no"/>
          <option name="BOTTOM_BUTTONS" value="yes"/>
        </options>
        <field caption="Search" class="DialogFieldInput" error="no">
          <input type="text" name="filter" maxlength="1024"/></field>
        <field class="DialogFieldButtons" error="no">
          <buttons>
            <button type="submit" align="right">Filter</button>
            <button type="submit" align="left" name="filter-reset[1]">Reset</button>
          </buttons>
        </field>
        <field class="DialogFieldListview" error="no">
          <listview><toolbar position="bottom right"/></listview>
        </field>
      </dialog-box>',
      $dialog->getXML()
    );
  }

  /**
   * @covers Dialog::execute
   */
  public function testExecute() {
    $users = $this->createMock(\Papaya\Content\Community\Users::class);
    $users
      ->expects($this->once())
      ->method('load')
      ->with(
        array('filter' => NULL),
        20,
        0
      );
    $dialog = new Dialog_TestProxy();
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->parameters(new \Papaya\Request\Parameters(array()));
    $dialog->users($users);
    $this->assertTrue($dialog->execute());
  }

  /**
   * @covers Dialog::execute
   */
  public function testExecuteWithFilter() {
    $users = $this->createMock(\Papaya\Content\Community\Users::class);
    $users
      ->expects($this->once())
      ->method('load')
      ->with(
        array('filter' => 'foo'),
        20,
        20
      );
    $dialog = new Dialog_TestProxy();
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->parameters(
      new \Papaya\Request\Parameters(
        array(
          'page' => 2
        )
      )
    );
    $dialog->data(
      new \Papaya\Request\Parameters(
        array(
          'filter' => 'foo'
        )
      )
    );
    $dialog->users($users);
    $this->assertTrue($dialog->execute());
  }

  /**
   * @covers Dialog::execute
   */
  public function testExecuteWithFilterReset() {
    $users = $this->createMock(\Papaya\Content\Community\Users::class);
    $users
      ->expects($this->once())
      ->method('load')
      ->with(
        array('filter' => NULL),
        20,
        0
      );
    $dialog = new Dialog_TestProxy();
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->data(
      new \Papaya\Request\Parameters(
        array(
          'filter' => 'foo',
          'filter-reset' => '1'
        )
      )
    );
    $dialog->users($users);
    $this->assertTrue($dialog->execute());
  }

  /**
   * @covers Dialog::users
   */
  public function testUsersGetAfterset() {
    $users = $this->createMock(\Papaya\Content\Community\Users::class);
    $dialog = new Dialog();
    $dialog->users($users);
    $this->assertSame($users, $dialog->users());
  }

  /**
   * @covers Dialog::users
   */
  public function testUsersImplicitCreate() {
    $dialog = new Dialog();
    $this->assertInstanceOf(\Papaya\Content\Community\Users::class, $dialog->users());
  }

  /**
   * @covers Dialog::listview
   */
  public function testListviewGetAfterSet() {
    $listview = $this->createMock(\Papaya\UI\Listview::class);
    $dialog = new Dialog();
    $dialog->listview($listview);
    $this->assertSame($listview, $dialog->listview());
  }

  /**
   * @covers Dialog::listview
   */
  public function testListviewImplicitCreate() {
    $dialog = new Dialog();
    $dialog->papaya($this->mockPapaya()->application());
    $this->assertInstanceOf(\Papaya\UI\Listview::class, $dialog->listview());
  }

  /**
   * @covers Dialog::createUserItem
   */
  public function testCreateUserItem() {
    $dialog = new Dialog();
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->createUserItem(
      new \stdClass, $dialog->listview()->items, array('id' => 42, 'caption' => 'test')
    );
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<listitem title="test" href="http://www.test.tld/test.html?page=1&amp;user_id=42"/>',
      $dialog->listview()->items[0]->getXml()
    );
  }

  /**
   * @covers Dialog::paging
   */
  public function testPagingGetAfterSet() {
    $paging = $this
      ->getMockBuilder(\Papaya\UI\Toolbar\Paging::class)
      ->disableOriginalConstructor()
      ->getMock();
    $dialog = new Dialog();
    $dialog->paging($paging);
    $this->assertSame($paging, $dialog->paging());
  }

  /**
   * @covers Dialog::paging
   */
  public function testPagingImplicitCreate() {
    $dialog = new Dialog();
    $this->assertInstanceOf(\Papaya\UI\Toolbar\Paging::class, $dialog->paging());
  }

  /**
   * @covers Dialog::reference
   */
  public function testReferenceGetAfterSet() {
    $reference = $this->createMock(\Papaya\UI\Reference::class);
    $dialog = new Dialog();
    $dialog->reference($reference);
    $this->assertSame($reference, $dialog->reference());
  }

  /**
   * @covers Dialog::reference
   */
  public function testReferenceImplicitCreate() {
    $dialog = new Dialog();
    $this->assertInstanceOf(\Papaya\UI\Reference::class, $dialog->reference());
  }

  /**
   * @covers Dialog::setParameterNameMapping
   */
  public function testSetParameterNameMapping() {
    $dialog = new Dialog();
    $dialog->setParameterNameMapping('user', 'surfer_id');
    $dialog->setParameterNameMapping('filter', 'search');
    $dialog->setParameterNameMapping('page', 'offset_page');
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->createUserItem(
      new \stdClass, $dialog->listview()->items, array('id' => 42, 'caption' => 'test')
    );
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<listitem title="test" href="http://www.test.tld/test.html?offset_page=1&amp;surfer_id=42"/>',
      $dialog->listview()->items[0]->getXml()
    );
  }

  /**
   * @covers Dialog::setParameterNameMapping
   */
  public function testSetParamterNameMappingExpectingException() {
    $dialog = new Dialog();
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Unknown parameter identifier "unknown-parameter".');
    $dialog->setParameterNameMapping('unknown-parameter', 'some');
  }

}

class Dialog_TestProxy
  extends Dialog {

  public $_executionResult = TRUE;
}
