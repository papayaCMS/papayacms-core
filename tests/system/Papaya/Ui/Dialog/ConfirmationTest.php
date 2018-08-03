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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiDialogConfirmationTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\UI\Dialog\Confirmation::__construct
  */
  public function testConstructor() {
    $owner = new stdClass();
    $dialog = new \Papaya\UI\Dialog\Confirmation($owner, array('sample' => 'foo'));
    $this->assertAttributeSame(
      $owner, '_owner', $dialog
    );
    $this->assertEquals(
      array('sample' => 'foo'), $dialog->hiddenFields()->toArray()
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Confirmation::__construct
  */
  public function testConstructorWithParameterGroup() {
    $owner = new stdClass();
    $dialog = new \Papaya\UI\Dialog\Confirmation($owner, array('sample' => 'foo'), 'group');
    $this->assertAttributeSame(
      $owner, '_owner', $dialog
    );
    $this->assertEquals(
      'group', $dialog->parameterGroup()
    );
    $this->assertEquals(
      array('sample' => 'foo'), $dialog->hiddenFields()->toArray()
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Confirmation::setMessageText
  */
  public function testSetMessageText() {
    $owner = new stdClass();
    $dialog = new \Papaya\UI\Dialog\Confirmation($owner, array('sample' => 'foo'), 'group');
    $dialog->setMessageText('Message text');
    $this->assertAttributeEquals(
      'Message text', '_message', $dialog
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Confirmation::setButtonCaption
  */
  public function testSetButtonCaption() {
    $owner = new stdClass();
    $dialog = new \Papaya\UI\Dialog\Confirmation($owner, array('sample' => 'foo'), 'group');
    $dialog->setButtonCaption('Button caption');
    $this->assertAttributeEquals(
      'Button caption', '_button', $dialog
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Confirmation::isSubmitted
  */
  public function testIsSubmittedExpectingTrue() {
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('post'));
    $dialog = new \Papaya\UI\Dialog\Confirmation(new stdClass(), array('sample' => 'foo'));
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $dialog->parameters(
      new \Papaya\Request\Parameters(array('confirmation' => 'a9994ecdd4cc99b5ac3b59272afa0d47'))
    );
    $this->assertTrue($dialog->isSubmitted());
  }

  /**
  * @covers \Papaya\UI\Dialog\Confirmation::isSubmitted
  */
  public function testIsSubmittedExpectingFalse() {
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('get'));
    $dialog = new \Papaya\UI\Dialog\Confirmation(new stdClass(), array('sample' => 'foo'));
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $this->assertFalse($dialog->isSubmitted());
  }

  /**
  * @covers \Papaya\UI\Dialog\Confirmation::execute
  */
  public function testExecuteExpectingTrue() {
    $owner = new stdClass();
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('post'));
    $tokens = $this->createMock(\Papaya\UI\Tokens::class);
    $tokens
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('TOKEN_STRING'), $this->equalTo($owner))
      ->will($this->returnValue(TRUE));
    $dialog = new \Papaya\UI\Dialog\Confirmation($owner, array('sample' => 'foo'));
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $dialog->tokens($tokens);
    $dialog->parameters(
      new \Papaya\Request\Parameters(
        array(
          'confirmation' => 'a9994ecdd4cc99b5ac3b59272afa0d47',
          'token' => 'TOKEN_STRING'
        )
      )
    );
    $this->assertTrue($dialog->execute());
  }

  /**
  * @covers \Papaya\UI\Dialog\Confirmation::execute
  */
  public function testExecuteExpectingFalse() {
    $owner = new stdClass();
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('get'));
    $dialog = new \Papaya\UI\Dialog\Confirmation($owner, array('sample' => 'foo'));
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $this->assertFalse($dialog->execute());
  }

  /**
  * @covers \Papaya\UI\Dialog\Confirmation::execute
  */
  public function testExecuteCachesResultExpectingFalse() {
    $owner = new stdClass();
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('get'));
    $dialog = new \Papaya\UI\Dialog\Confirmation($owner, array('sample' => 'foo'));
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $dialog->execute();
    $this->assertFalse($dialog->execute());
  }

  /**
  * @covers \Papaya\UI\Dialog\Confirmation::appendTo
  */
  public function testAppendTo() {
    $owner = new stdClass();
    $tokens = $this->createMock(\Papaya\UI\Tokens::class);
    $tokens
      ->expects($this->once())
      ->method('create')
      ->with($this->equalTo($owner))
      ->will($this->returnValue('TOKEN_STRING'));
    $dialog = new \Papaya\UI\Dialog\Confirmation(
      $owner,
      array('sample' => 'foo'),
      'group'
    );
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->tokens($tokens);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<confirmation-dialog action="http://www.test.tld/test.html" method="post">
      <input type="hidden" name="group[sample]" value="foo"/>
      <input type="hidden" name="group[confirmation]" value="a9994ecdd4cc99b5ac3b59272afa0d47"/>
      <input type="hidden" name="group[token]" value="TOKEN_STRING"/>
      <message>Confirm action?</message>
      <dialog-button type="submit" caption="Yes"/>
      </confirmation-dialog>',
      $dialog->getXML()
    );
  }
}
