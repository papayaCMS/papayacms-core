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

namespace Papaya\CMS\Application\Profiles;

use Papaya\CMS\Application\Profile;

require_once __DIR__.'/../../../../../bootstrap.php';

class CMSTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Application\Profiles\CMS::getProfiles
   */
  public function testGetProfiles() {
    /** @var \Papaya\Application $application */
    $application = $this->createMock(\Papaya\Application::class);
    $profiles = new CMS();
    $list = $profiles->getProfiles($application);
    $this->assertEquals(
      array(
        'Database' => new Profile\Database(),
        'Front' => new Profile\Front(),
        'Images' => new Profile\Images(),
        'Languages' => new Profile\Languages(),
        'Messages' => new Profile\Messages(),
        'Media' => new Profile\Media(),
        'Options' => new Profile\Options(),
        'Plugins' => new Profile\Plugins(),
        'Profiler' => new Profile\Profiler(),
        'Request' => new Profile\Request(),
        'Response' => new Profile\Response(),
        'Session' => new Profile\Session(),
        'Surfer' => new Profile\Surfer(),

        'AdministrationUser' => new Profile\Administration\User(),
        'AdministrationLanguage' => new Profile\Administration\Language(),
        'AdministrationPhrases' => new Profile\Administration\Phrases(),
        'AdministrationRichText' => new Profile\Administration\RichText(),

        'References' => new Profile\References(),
        'PageReferences' => new Profile\Page\References()
      ),
      $list
    );
  }
}
