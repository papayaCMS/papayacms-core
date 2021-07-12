<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Router\Route {

  use Papaya\Response\Failure as FailureResponse;
  use Papaya\Router;
  use Papaya\TestFramework\TestCase;

  /**
   * @covers \Papaya\Router\Route\Error
   */
  class ErrorTest extends TestCase {

    public function testRouteReturnErrorResponse() {
      $router = $this->createMock(Router::class);
      $route = new Error('message', 500, 'error.identifier');

      $response = $route($router);
      $this->assertInstanceOf(FailureResponse::class, $response);
    }
  }

}
