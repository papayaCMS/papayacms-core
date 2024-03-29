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

namespace Papaya\CMS\Application\Profile;

require_once __DIR__.'/../../../../../bootstrap.php';

class RequestTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Application\Profile\Request::createObject
   */
  public function testCreateObject() {
    $options = $this->mockPapaya()->options(
      array(
        'PAPAYA_URL_LEVEL_SEPARATOR' => '[]',
        'PAPAYA_PATH_WEB' => '/'
      )
    );
    $application = $this->mockPapaya()->application(array('options' => $options));
    $profile = new Request();
    $request = $profile->createObject($application);
    $this->assertInstanceOf(
      \Papaya\Request::class,
      $request
    );
  }

  public function testGetPropertyModeInitializeFromParameter() {
    $options = $this->mockPapaya()->options(
      array(
        'PAPAYA_URL_LEVEL_SEPARATOR' => '[]',
        'PAPAYA_PATH_WEB' => '/'
      )
    );
    $application = $this->mockPapaya()->application(array('options' => $options));
    $profile = new Request();
    $request = $profile->createObject($application);
    $request->setParameters(
      \Papaya\Request::SOURCE_PATH,
      new \Papaya\Request\Parameters(['output_mode' => 'ext'])
    );
    $this->assertEquals(
      [['extension' => 'ext']],
      $request->mode->getLazyLoadParameters()
    );
  }

  public function testGetPropertyModeInitializeFromParameterXmlPreviewMode() {
    $options = $this->mockPapaya()->options(
      array(
        'PAPAYA_URL_LEVEL_SEPARATOR' => '[]',
        'PAPAYA_PATH_WEB' => '/'
      )
    );
    $application = $this->mockPapaya()->application(array('options' => $options));
    $profile = new Request();
    $request = $profile->createObject($application);
    $request->setParameters(
      \Papaya\Request::SOURCE_PATH,
      new \Papaya\Request\Parameters(['output_mode' => 'xml'])
    );
    $this->assertNull($request->mode->getLazyLoadParameters());
    $this->assertEquals(
      [
        'id' => -1,
        'extension' => 'xml',
        'type' => 'page',
        'charset' => 'utf-8',
        'content_type' => 'application/xml',
        'path' => '',
        'module_guid' => '',
        'session_mode' => '',
        'session_redirect' => '',
        'session_cache' => ''
      ],
      iterator_to_array($request->mode)
    );
  }
}
