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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiControlCommandActionTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Control\Command\Action
  */
  public function testDataWithImplicitCreate() {
    $command = new \Papaya\Ui\Control\Command\Action();
    $command->parameters(new \Papaya\Request\Parameters(array('test' => 'success')));
    $command->callbacks()->getDefinition = array($this, 'callbackGetDefinition');
    $this->assertEquals('success', $command->data()->get('test'));
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Action
  */
  public function testAppendToWithValidationSuccessful() {
    $command = new \Papaya\Ui\Control\Command\Action();
    $command->parameters(new \Papaya\Request\Parameters(array('test' => 'success')));
    $command->callbacks()->getDefinition = array($this, 'callbackGetDefinition');
    $command->callbacks()->onValidationSuccessful = array($this, 'callbackValidationSuccessful');
    $this->assertAppendedXmlEqualsXmlFragment(
      /** @lang XML */'<success>success</success>', $command
    );
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Action
  */
  public function testAppendToWithValidationFailed() {
    $command = new \Papaya\Ui\Control\Command\Action();
    $command->parameters(new \Papaya\Request\Parameters());
    $command->callbacks()->getDefinition = array($this, 'callbackGetDefinition');
    $command->callbacks()->onValidationFailed = array($this, 'callbackValidationFailed');
    $this->assertAppendedXmlEqualsXmlFragment(
      /** @lang XML */'<failed/>', $command
    );
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Action
  */
  public function testDataGetAfterSet() {
    $validator = $this
      ->getMockBuilder(\Papaya\Request\Parameters\Validator::class)#
      ->disableOriginalConstructor()
      ->getMock();
    $command = new \Papaya\Ui\Control\Command\Action();
    $command->data($validator);
    $this->assertSame(
      $validator, $command->data()
    );
  }

  public function callbackValidationSuccessful(
    /** @noinspection PhpUnusedParameterInspection */
    $context, \Papaya\Ui\Control\Command\Action $command, \Papaya\Xml\Element $parent) {
    $parent->appendElement('success', array(), $command->data()->get('test'));
  }

  public function callbackValidationFailed(
    /** @noinspection PhpUnusedParameterInspection */
    $context, \Papaya\Ui\Control\Command\Action $command, \Papaya\Xml\Element $parent
  ) {
    $parent->appendElement('failed');
  }

  public function callbackGetDefinition() {
    return array(
      array('test', '', new \Papaya\Filter\NotEmpty())
    );
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Action::callbacks
  */
  public function testCallbacksGetAfterSet() {
    $callbacks = $this->createMock(\Papaya\Ui\Control\Command\Action\Callbacks::class);
    $command = new \Papaya\Ui\Control\Command\Action();
    $this->assertSame($callbacks, $command->callbacks($callbacks));
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Action::callbacks
  */
  public function testCallbacksGetImplicitCreate() {
    $command = new \Papaya\Ui\Control\Command\Action();
    $this->assertInstanceOf(\Papaya\Ui\Control\Command\Action\Callbacks::class, $command->callbacks());
  }
}
