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

namespace Papaya\CMS {

  /**
   * A class extending the \Papaya\Application service locator that
   * allows to declare the profiles as properties
   *
   * @property \Papaya\Database\Manager $database
   * @property \papaya_page|NULL $front
   * @property \Papaya\UI\Images images
   * @property \Papaya\CMS\Content\Languages $languages
   * @property \Papaya\Media\MediaDatabase $media
   * @property \Papaya\Message\Manager $messages
   * @property \Papaya\CMS\CMSConfiguration $options
   * @property \Papaya\CMS\Plugin\Loader $plugins
   * @property \Papaya\Profiler $profiler
   * @property \Papaya\Request $request
   * @property \Papaya\Response $response
   * @property \Papaya\Session $session
   * @property \base_surfer $surfer
   * @property \Papaya\CMS\Reference\Factory $references
   * @property \Papaya\CMS\Reference\Page\Factory $pageReferences
   * @property \base_auth $administrationUser
   * @property \Papaya\CMS\Administration\Languages\Selector $administrationLanguage
   * @property \Papaya\CMS\Administration\Phrases $administrationPhrases
   * @property \Papaya\CMS\Administration\RichText\Toggle $administrationRichText
   */
  class CMSApplication extends \Papaya\Application {

    public function __construct() {
      $this->registerProfiles(
        new Application\Profiles\CMS()
      );
    }
  }
}
