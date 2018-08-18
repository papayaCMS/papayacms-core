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

namespace Papaya\Administration\Theme\Editor\Changes;

require_once __DIR__.'/../../../../../../bootstrap.php';

class DialogTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Administration\Theme\Editor\Changes\Dialog::createDialog
   */
  public function testCreateDialogWithEmptyPage() {
    $page = $this->createMock(\Papaya\Content\Structure\Page::class);
    $page
      ->expects($this->once())
      ->method('groups')
      ->will($this->returnValue(new \EmptyIterator()));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Record $record */
    $record = $this->createMock(\Papaya\Database\Interfaces\Record::class);
    $command = new Dialog($record);
    $command->papaya($this->mockPapaya()->application());
    $command->themePage($page);
    $this->assertInstanceOf(\Papaya\UI\Dialog::class, $command->createDialog());
  }

  /**
   * @covers \Papaya\Administration\Theme\Editor\Changes\Dialog::createDialog
   */
  public function testCreateDialogWithOneEmptyGroup() {
    $page = new \Papaya\Content\Structure\Page();
    $page->name = 'SAMPLE_PAGE';
    $page->title = 'Page title';
    $page->groups()->add($group = new \Papaya\Content\Structure\Group($page));
    $group->name = 'SAMPLE_GROUP';
    $group->title = 'group title';

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Record $record */
    $record = $this->createMock(\Papaya\Database\Interfaces\Record::class);
    $command = new Dialog($record);
    $command->papaya($this->mockPapaya()->application());
    $command->themePage($page);
    $this->assertInstanceOf(\Papaya\UI\Dialog::class, $command->createDialog());
  }

  /**
   * @covers \Papaya\Administration\Theme\Editor\Changes\Dialog::createDialog
   */
  public function testCreateDialogWithOneValueOfUnknownType() {
    $page = new \Papaya\Content\Structure\Page();
    $page->name = 'SAMPLE_PAGE';
    $page->title = 'Page title';
    $page->groups()->add($group = new \Papaya\Content\Structure\Group($page));
    $group->name = 'SAMPLE_GROUP';
    $group->title = 'group title';
    $group->values()->add($value = new \Papaya\Content\Structure\Value($group));
    $value->name = 'SAMPLE_VALUE';
    $value->title = 'value title';
    $value->fieldType = 'UNKNOWN_FIELD_TYPE';
    $value->default = 'foo';

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Dialog\Field\Factory\Exception $exception */
    $exception = $this->createMock(\Papaya\UI\Dialog\Field\Factory\Exception::class);
    $factory = $this->createMock(\Papaya\UI\Dialog\Field\Factory::class);
    $factory
      ->expects($this->once())
      ->method('getField')
      ->with('UNKNOWN_FIELD_TYPE', $this->isInstanceOf(\Papaya\UI\Dialog\Field\Factory\Options::class))
      ->will($this->throwException($exception));

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Record $record */
    $record = $this->createMock(\Papaya\Database\Interfaces\Record::class);
    $command = new Dialog($record);
    $command->papaya($this->mockPapaya()->application());
    $command->themePage($page);
    $command->fieldFactory($factory);
    $this->assertInstanceOf(\Papaya\UI\Dialog::class, $dialog = $command->createDialog());
    $this->assertCount(1, $dialog->fields[0]->fields);
  }

  /**
   * @covers \Papaya\Administration\Theme\Editor\Changes\Dialog::createDialog
   */
  public function testCreateDialogWithOneValue() {
    $page = new \Papaya\Content\Structure\Page();
    $page->name = 'SAMPLE_PAGE';
    $page->title = 'Page title';
    $page->groups()->add($group = new \Papaya\Content\Structure\Group($page));
    $group->name = 'SAMPLE_GROUP';
    $group->title = 'group title';
    $group->values()->add($value = new \Papaya\Content\Structure\Value($group));
    $value->name = 'SAMPLE_VALUE';
    $value->title = 'value title';
    $value->fieldType = 'UNKNOWN_FIELD_TYPE';
    $value->default = 'foo';

    $factory = $this->createMock(\Papaya\UI\Dialog\Field\Factory::class);
    $factory
      ->expects($this->once())
      ->method('getField')
      ->with('UNKNOWN_FIELD_TYPE', $this->isInstanceOf(\Papaya\UI\Dialog\Field\Factory\Options::class))
      ->will($this->returnValue($this->createMock(\Papaya\UI\Dialog\Field::class)));

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Record $record */
    $record = $this->createMock(\Papaya\Database\Interfaces\Record::class);
    $command = new Dialog($record);
    $command->papaya($this->mockPapaya()->application());
    $command->themePage($page);
    $command->fieldFactory($factory);
    $this->assertInstanceOf(\Papaya\UI\Dialog::class, $dialog = $command->createDialog());
    $this->assertCount(1, $dialog->fields[0]->fields);
  }

  /**
   * @covers \Papaya\Administration\Theme\Editor\Changes\Dialog::createDialog
   */
  public function testCreateDialogWithEmptyDefinition() {
    $papaya = $this->mockPapaya()->application(
      array(
        'request' => $this->mockPapaya()->request(
          array('theme' => 'sample', 'set_id' => 23, 'page_identifier' => 'SAMPLE_PAGE')
        )
      )
    );
    $definition = $this->createMock(\Papaya\Content\Structure::class);
    $definition
      ->expects($this->once())
      ->method('getPage')
      ->with('SAMPLE_PAGE')
      ->will($this->returnValue(NULL));
    $themeHandler = $this->createMock(\Papaya\Theme\Handler::class);
    $themeHandler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('sample')
      ->will($this->returnValue($definition));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Content\Theme\Set $themeSet */
    $themeSet = $this->createMock(\Papaya\Content\Theme\Set::class);
    $themeSet
      ->expects($this->once())
      ->method('load')
      ->with(23);

    $command = new Dialog($themeSet);
    $command->papaya($papaya);
    $command->themeHandler($themeHandler);
    $this->assertInstanceOf(\Papaya\UI\Dialog::class, $command->createDialog());
  }

  /**
   * @covers \Papaya\Administration\Theme\Editor\Changes\Dialog::themePage
   */
  public function testThemePageGetAfterSet() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Record $record */
    $record = $this->createMock(\Papaya\Database\Interfaces\Record::class);
    $command = new Dialog($record);
    $command->themePage($themePage = $this->createMock(\Papaya\Content\Structure\Page::class));
    $this->assertSame($themePage, $command->themePage());
  }

  /**
   * @covers \Papaya\Administration\Theme\Editor\Changes\Dialog::themePage
   */
  public function testThemePageGetImplicitCreate() {
    $papaya = $this->mockPapaya()->application(
      array(
        'request' => $this->mockPapaya()->request(
          array('theme' => 'sample', 'set_id' => 23, 'page_identifier' => 'SAMPLE_PAGE')
        )
      )
    );
    $definition = $this->createMock(\Papaya\Content\Structure::class);
    $definition
      ->expects($this->once())
      ->method('getPage')
      ->with('SAMPLE_PAGE')
      ->will($this->returnValue($this->createMock(\Papaya\Content\Structure\Page::class)));
    $themeHandler = $this->createMock(\Papaya\Theme\Handler::class);
    $themeHandler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('sample')
      ->will($this->returnValue($definition));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Record $record */
    $record = $this->createMock(\Papaya\Database\Interfaces\Record::class);
    $command = new Dialog($record);
    $command->papaya($papaya);
    $command->themeHandler($themeHandler);
    $this->assertInstanceOf(\Papaya\Content\Structure\Page::class, $command->themePage());
  }

  /**
   * @covers \Papaya\Administration\Theme\Editor\Changes\Dialog::themeHandler
   */
  public function testThemeHandlerGetAfterSet() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Record $record */
    $record = $this->createMock(\Papaya\Database\Interfaces\Record::class);
    $command = new Dialog($record);
    $command->themeHandler($themeHandler = $this->createMock(\Papaya\Theme\Handler::class));
    $this->assertSame($themeHandler, $command->themeHandler());
  }

  /**
   * @covers \Papaya\Administration\Theme\Editor\Changes\Dialog::themeHandler
   */
  public function testThemeHandlerGetImplicitCreate() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Record $record */
    $record = $this->createMock(\Papaya\Database\Interfaces\Record::class);
    $command = new Dialog($record);
    $this->assertInstanceOf(\Papaya\Theme\Handler::class, $command->themeHandler());
  }

  /**
   * @covers \Papaya\Administration\Theme\Editor\Changes\Dialog::fieldFactory
   */
  public function testFieldFactoryGetAfterSet() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Record $record */
    $record = $this->createMock(\Papaya\Database\Interfaces\Record::class);
    $command = new Dialog($record);
    $command->fieldFactory($fieldFactory = $this->createMock(\Papaya\UI\Dialog\Field\Factory::class));
    $this->assertSame($fieldFactory, $command->fieldFactory());
  }

  /**
   * @covers \Papaya\Administration\Theme\Editor\Changes\Dialog::fieldFactory
   */
  public function testFieldFactoryGetImplicitCreate() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Record $record */
    $record = $this->createMock(\Papaya\Database\Interfaces\Record::class);
    $command = new Dialog($record);
    $this->assertInstanceOf(\Papaya\UI\Dialog\Field\Factory::class, $command->fieldFactory());
  }

  /**
   * @covers \Papaya\Administration\Theme\Editor\Changes\Dialog::callbackSaveValues
   */
  public function testCallbackSaveValues() {
    $messages = $this->createMock(\Papaya\Message\Manager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\Papaya\Message\Display::class));
    $cache = $this->createMock(\Papaya\Cache\Service::class);
    $cache
      ->expects($this->once())
      ->method('delete')
      ->with('theme', '');

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Record $record */
    $record = $this->createMock(\Papaya\Database\Interfaces\Record::class);
    $command = new Dialog($record);
    $command->papaya(
      $this->mockPapaya()->application(
        array('messages' => $messages)
      )
    );
    $command->cache($cache);
    $command->callbackSaveValues();
  }

  /**
   * @covers \Papaya\Administration\Theme\Editor\Changes\Dialog::callbackShowError
   */
  public function testCallbackShowError() {
    $errors = $this->createMock(\Papaya\UI\Dialog\Errors::class);
    $errors
      ->expects($this->once())
      ->method('getSourceCaptions')
      ->will($this->returnValue(array()));

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Dialog $dialog */
    $dialog = $this->createMock(\Papaya\UI\Dialog::class);
    $dialog
      ->expects($this->once())
      ->method('errors')
      ->will($this->returnValue($errors));

    $messages = $this->createMock(\Papaya\Message\Manager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\Papaya\Message\Display::class));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Record $record */
    $record = $this->createMock(\Papaya\Database\Interfaces\Record::class);
    $command = new Dialog($record);
    $command->papaya(
      $this->mockPapaya()->application(
        array('messages' => $messages)
      )
    );
    $command->callbackShowError(new \stdClass, $dialog);
  }

  /**
   * @covers \Papaya\Administration\Theme\Editor\Changes\Dialog::cache
   */
  public function testCacheGetAfterSet() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Record $record */
    $record = $this->createMock(\Papaya\Database\Interfaces\Record::class);
    $command = new Dialog($record);
    $command->cache($cache = $this->createMock(\Papaya\Cache\Service::class));
    $this->assertSame($cache, $command->cache());
  }

  /**
   * @covers \Papaya\Administration\Theme\Editor\Changes\Dialog::cache
   */
  public function testCacheGetImplicitCreate() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Record $record */
    $record = $this->createMock(\Papaya\Database\Interfaces\Record::class);
    $command = new Dialog($record);
    $command->papaya($this->mockPapaya()->application());
    $this->assertInstanceOf(\Papaya\Cache\Service::class, $command->cache());
  }

}
