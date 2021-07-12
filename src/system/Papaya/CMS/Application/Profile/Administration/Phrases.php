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
namespace Papaya\CMS\Application\Profile\Administration {

  use Papaya\Application;
  use Papaya\CMS\Administration\Phrases as PhraseTranslations;

  /**
   * Application object profile for the phrase translations (used in Administration UI)
   *
   * @package Papaya-Library
   * @subpackage Application
   */
  class Phrases implements Application\Profile {
    /**
     * Create the profile object and return it
     *
     * @param \Papaya\CMS\CMSApplication $application
     * @return PhraseTranslations
     */
    public function createObject($application) {
      $language = $application->languages->getLanguage(
        isset($application->administrationUser)
          ? $application->administrationUser->options['PAPAYA_UI_LANGUAGE']
          : $application->options['PAPAYA_UI_LANGUAGE']
      );
      if (!$language) {
        $language = new \Papaya\CMS\Content\Language();
      }
      return new PhraseTranslations(
        new PhraseTranslations\Storage\Database(), $language
      );
    }
  }
}
