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

use Papaya\Controller\Error;

require_once __DIR__.'/../../../bootstrap.php';

class PapayaControllerErrorTest extends PapayaTestCase {

  /**
  * @covers Error::setStatus
  */
  public function testSetStatus() {
    $controller = new Error();
    $controller->setStatus(403);
    $this->assertAttributeEquals(
      403, '_status', $controller
    );
  }

  /**
  * @covers Error::setError
  */
  public function testSetError() {
    $controller = new Error();
    $controller->setError('ERROR_IDENTIFIER', 'ERROR_MESSAGE');
    $this->assertAttributeEquals(
      'ERROR_MESSAGE', '_errorMessage', $controller
    );
    $this->assertAttributeEquals(
      'ERROR_IDENTIFIER', '_errorIdentifier', $controller
    );
  }

  /**
  * @covers Error::execute
  * @covers Error::_getOutput
  */
  public function testControllerExecute() {
    $application = $this->mockPapaya()->application();
    $request = $this->mockPapaya()->request();
    $response = $this->mockPapaya()->response();
    $response
      ->expects($this->once())
      ->method('setStatus')
      ->with(
        $this->equalTo(500)
      );
    $response
      ->expects($this->once())
      ->method('setContentType')
      ->with(
        $this->equalTo('text/html')
      );
    $response
      ->expects($this->once())
      ->method('content')
      ->with(
        $this->isInstanceOf(PapayaResponseContentString::class)
      );
    $controller = new Error();
    $this->assertTrue(
      $controller->execute($application, $request, $response)
    );
  }
}
