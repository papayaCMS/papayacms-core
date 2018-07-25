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

use Papaya\Controller\Error\File;

require_once __DIR__.'/../../../bootstrap.php';

class PapayaControllerFactoryTest extends PapayaTestCase {

  /**
  * @covers PapayaControllerFactory::createError
  */
  public function testCreateError() {
    $error = PapayaControllerFactory::createError(404, 'Test', 'TEST');
    $this->assertInstanceOf(PapayaControllerError::class, $error);
    $this->assertAttributeEquals(
      404, '_status', $error
    );
    $this->assertAttributeEquals(
      'Test', '_errorMessage', $error
    );
    $this->assertAttributeEquals(
      'TEST', '_errorIdentifier', $error
    );
  }

  /**
  * @covers PapayaControllerFactory::createError
  */
  public function testCreateErrorWithFile() {
    $error = PapayaControllerFactory::createError(
      404, 'Test', 'TEST', __DIR__.'/Error/TestData/template.txt'
    );
    $this->assertInstanceOf(File::class, $error);
    $this->assertAttributeEquals(
      'SAMPLE', '_template', $error
    );
  }

}
