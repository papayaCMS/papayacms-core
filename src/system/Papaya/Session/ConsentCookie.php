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
namespace Papaya\Session {

  use Papaya\Application\Access as ApplicationAccess;
  use Papaya\Configuration\CMS as CMSSettings;

  class ConsentCookie implements ApplicationAccess {

    const LEVEL_BASIC = 0;
    const LEVEL_EXTENDED = 1;

    const DEFAULT_NAME = 'cookieconsent_status';
    const DEFAULT_LEVELS = 'dismiss,allow';

    private static $_level;

    use ApplicationAccess\Aggregation;

    /**
     * @return bool
     */
    public function isRequired() {
      return (bool)$this->papaya()->options->get(
        CMSSettings::CONSENT_COOKIE_REQUIRE, FALSE
      );
    }

    /**
     * @return int
     */
    public function getLevel() {
      if (!$this->isRequired()) {
        return 99;
      }
      if (isset(self::$_level)) {
        return self::$_level;
      }
      $cookieName = $this->papaya()->options->get(CMSSettings::CONSENT_COOKIE_NAME, self::DEFAULT_NAME);
      $levels = array_map(
        static function($value) {
          return trim($value);
        },
        explode(
          ',',
          $this->papaya()->options->get(CMSSettings::CONSENT_COOKIE_LEVELS, self::DEFAULT_LEVELS)
        )
      );
      if (
        isset($_COOKIE[$cookieName]) &&
        FALSE !== ($current = array_search($_COOKIE[$cookieName], $levels))
      ) {
        return self::$_level = (int)$current;
      }
      return self::$_level = -1;
    }

    public function hasLevel($level) {
      return $level <= $this->getLevel();
    }

    public static function reset(){
      return self::$_level;
    }
  }
}
