<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2020 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Administration\Settings {

  use Papaya\Administration\Settings\Profiles\ChoiceSetting;
  use Papaya\Administration\Settings\Profiles\FlagSetting;
  use Papaya\Administration\Settings\Profiles\IntegerSetting;
  use Papaya\Administration\Settings\Profiles\LanguageIdSetting;
  use Papaya\Administration\Settings\Profiles\PageIdSetting;
  use Papaya\Administration\Settings\Profiles\TextSetting;
  use Papaya\Application\Access;
  use Papaya\Configuration\CMS;
  use Papaya\Iterator\SortIterator as SortIterator;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\UI\Text\Translated\Collection as TranslatedList;

  class SettingGroups implements Access {

    use Access\Aggregation;

    const CACHE_SERVICE_CHOICES = [
      'apc' => 'APC', 'file' => 'File system', 'memcache' => 'Memcache'
    ];

    const UNKNOWN = 0;
    const DATABASE = 1;
    const PATHS = 2;
    const DEBUGGING = 3;
    const LANGUAGE = 4;
    const PROJECT = 5;
    const INTERNALS = 6;
    const SYSTEM = 7;
    const LAYOUT = 8;
    const DEFAULT_PAGES = 9;
    const CHARSETS = 10;
    const ADMINISTRATION = 11;
    const AUTHENTICATION = 12;
    const SUPPORT = 13;
    const FILES = 14;
    const CACHE = 15;
    const LOGGING = 16;
    const SESSION = 17;
    const FEATURES = 18;
    const FEATURES_EXPERIMENTAL = 19;

    private static $_LABELS = [
      self::UNKNOWN => 'Unknown',
      self::DATABASE => 'Database',
      self::PATHS => 'URLs and Directories',
      self::DEBUGGING => 'Debugging',
      self::LANGUAGE => 'Language',
      self::PROJECT => 'Project Defaults',
      self::INTERNALS => 'Internals',
      self::SYSTEM => 'System',
      self::LAYOUT => 'Layout',
      self::DEFAULT_PAGES => 'Default Pages',
      self::CHARSETS => 'Charsets / Encoding',
      self::ADMINISTRATION => 'Administration UI',
      self::AUTHENTICATION => 'Authentication',
      self::SUPPORT => 'Support',
      self::FILES => 'Files / Media Database',
      self::CACHE => 'Cache',
      self::LOGGING => 'Logging',
      self::SESSION => 'Session',
      self::FEATURES => 'Features',
      self::FEATURES_EXPERIMENTAL => 'Experimental Features'
    ];

    private static $_PROFILES = [
      self::UNKNOWN => [],
      self::DATABASE => [],
      self::PATHS => [],
      self::DEBUGGING => [
        CMS::DBG_DATABASE_ERROR => FlagSetting::class,
        CMS::DBG_XML_OUTPUT => FlagSetting::class,
        CMS::DBG_XML_ERROR => FlagSetting::class,
        CMS::DBG_XML_USERINPUT => FlagSetting::class,
        CMS::DBG_PHRASES => FlagSetting::class,
        CMS::DBG_SHOW_DEBUGS => FlagSetting::class
      ],
      self::LANGUAGE => [],
      self::PROJECT => [
        CMS::REDIRECT_PROTECTION => FlagSetting::class,
        CMS::CONTENT_LANGUAGE => [LanguageIdSetting::class, LanguageIdSetting::FILTER_IS_CONTENT],
        CMS::CONTENT_LANGUAGE_COOKIE => FlagSetting::class,
        CMS::DEFAULT_PROTOCOL => [
          ChoiceSetting::class,
          [
            0 => 'None',
            1 => 'http',
            2 => 'https',
          ]
        ],
        CMS::DEFAULT_HOST_ACTION => [
          ChoiceSetting::class,
          [
            0 => 'None',
            1 => 'Redirect'
          ]
        ],
        CMS::PUBLISH_SOCIALMEDIA => FlagSetting::class
      ],
      self::INTERNALS => [],
      self::SYSTEM => [
        CMS::BROWSER_CRONJOBS => FlagSetting::class,
        CMS::BROWSER_CRONJOBS_IP => TextSetting::class,
        CMS::SPAM_LOG => FlagSetting::class,
        CMS::SPAM_BLOCK => FlagSetting::class,
        CMS::SPAM_SCOREMIN_PERCENT => [IntegerSetting::class, 1, 10],
        CMS::SPAM_SCOREMAX_PERCENT => [IntegerSetting::class, 1, 90],
        CMS::SPAM_STOPWORD_MAX => [IntegerSetting::class, 3, 10],
        CMS::DATABASE_CLUSTER_SWITCH => [
          ChoiceSetting::class,
          [0 => 'manual', 1 => 'object', 2 => 'connection']
        ],
        CMS::VERSIONS_MAXCOUNT => [IntegerSetting::class, 1, 2000],
        CMS::URL_NAMELENGTH => [IntegerSetting::class, 1, 100],
        CMS::URL_LEVEL_SEPARATOR => [
          ChoiceSetting::class,
          ['' => '[ ]', ',' => ',', ':' => ':', '*' => '*', '!' => '!', '/' => '/'],
          FALSE
        ],
        CMS::URL_ALIAS_SEPARATOR => [
          ChoiceSetting::class,
          [',' => ',', ':' => ':', '*' => '*', '!' => '!'],
          FALSE
        ]
      ],
      self::LAYOUT => [],
      self::DEFAULT_PAGES => [
        CMS::PAGEID_DEFAULT => PageIdSetting::class,
        CMS::PAGEID_USERDATA => PageIdSetting::class,
        CMS::PAGEID_STATUS_301 => PageIdSetting::class,
        CMS::PAGEID_STATUS_302 => PageIdSetting::class,
        CMS::PAGEID_ERROR_403 => PageIdSetting::class,
        CMS::PAGEID_ERROR_404 => PageIdSetting::class,
        CMS::PAGEID_ERROR_500 => PageIdSetting::class,
      ],
      self::CHARSETS => [],
      self::ADMINISTRATION => [],
      self::AUTHENTICATION => [
        CMS::PASSWORD_ALGORITHM => [
          ChoiceSetting::class,
          [0 => 'PHP Default (suggested)', 1 => 'BCrypt'],
        ],
        CMS::PASSWORD_REHASH => FlagSetting::class,
        CMS::COMMUNITY_AUTOLOGIN => FlagSetting::class,
        CMS::COMMUNITY_REDIRECT_PAGE => PageIdSetting::class,
        CMS::COMMUNITY_RELOGIN => FlagSetting::class,
        CMS::COMMUNITY_RELOGIN_EXP_DAYS => [IntegerSetting::class, 1, 40],
        CMS::COMMUNITY_RELOGIN_SALT => TextSetting::class,
        CMS::COMMUNITY_API_LOGIN => [
          ChoiceSetting::class,
          [
            0 => 'Handle',
            1 => 'Email',
            2 => 'Handle or email',
          ]
        ],
        CMS::COMMUNITY_HANDLE_MAX_LENGTH => [IntegerSetting::class, 10, 40],
      ],
      self::SUPPORT => [],
      self::FILES => [
        CMS::MAX_UPLOAD_SIZE => [IntegerSetting::class, 1, 100000],
        CMS::NUM_UPLOAD_FIELDS => [IntegerSetting::class, 1, 20],
        CMS::MEDIADB_THUMBSIZE => [IntegerSetting::class, 1, 10000],
        CMS::IMAGE_CONVERTER => [
          ChoiceSetting::class,
          [
            'gd' => 'GD',
            'netpbm' => 'netpbm',
            'imagemagick' => 'Image Magick',
            'graphicsmagick' => 'GraphicksMagick'
          ]
        ],
        CMS::THUMBS_FILETYPE => [
          ChoiceSetting::class,
          [
            1 => 'GIF',
            2 => 'JPEG',
            3 => 'PNG'
          ]
        ],
        CMS::MEDIA_CUTLINE_MODE => [
          ChoiceSetting::class,
          [
            0 => 'manual only',
            1 => 'text (source)',
            2 => 'title (source) / text'
          ]
        ],
        CMS::MEDIA_CUTLINE_LINK_TARGET => [
          ChoiceSetting::class,
          [
            '_self' => '_self',
            '_blank' => '_blank'
          ],
          FALSE
        ],
        CMS::THUMBS_JPEGQUALITY => [IntegerSetting::class, 20, 100],
        CMS::THUMBS_TRANSPARENT => FlagSetting::class,
        CMS::THUMBS_MEMORYCHECK_SUHOSIN => FlagSetting::class,
        CMS::SENDFILE_HEADER => FlagSetting::class,
        CMS::BANDWIDTH_SHAPING => FlagSetting::class,
        CMS::BANDWIDTH_SHAPING_LIMIT => IntegerSetting::class,
        CMS::BANDWIDTH_SHAPING_OFFSET => IntegerSetting::class
      ],
      self::CACHE => [
        CMS::CACHE_BOXES => FlagSetting::class,
        CMS::CACHE_DATA => FlagSetting::class,
        CMS::CACHE_DATA_MEMCACHE_SERVERS => TextSetting::class,
        CMS::CACHE_DATA_SERVICE => [ChoiceSetting::class, self::CACHE_SERVICE_CHOICES],
        CMS::CACHE_DATA_TIME => [IntegerSetting::class, 0, 9999999999],
        CMS::CACHE_MEMCACHE_SERVERS => TextSetting::class,
        CMS::CACHE_OUTPUT => FlagSetting::class,
        CMS::CACHE_PAGES => FlagSetting::class,
        CMS::CACHE_SERVICE => [ChoiceSetting::class, self::CACHE_SERVICE_CHOICES],
        CMS::CACHE_THEMES => FlagSetting::class,
        CMS::CACHE_TIME_BOXES => [IntegerSetting::class, 0, 9999999999],
        CMS::CACHE_TIME_BROWSER => [IntegerSetting::class, 0, 9999999999],
        CMS::CACHE_TIME_FILES => [IntegerSetting::class, 0, 9999999999],
        CMS::CACHE_TIME_OUTPUT => [IntegerSetting::class, 0, 9999999999],
        CMS::CACHE_TIME_PAGES => [IntegerSetting::class, 0, 9999999999],
        CMS::CACHE_TIME_THEMES => [IntegerSetting::class, 0, 9999999999],
        CMS::CACHE_DISABLE_FILE_DELETE => FlagSetting::class,
        CMS::COMPRESS_CACHE_OUTPUT => FlagSetting::class,
        CMS::COMPRESS_CACHE_THEMES => FlagSetting::class,
        CMS::COMPRESS_OUTPUT => FlagSetting::class,
      ],
      self::LOGGING => [],
      self::SESSION => [],
      self::FEATURES => [],
      self::FEATURES_EXPERIMENTAL => []
    ];

    private static $_SETTING_GROUP_MAP;

    /**
     * @param $setting
     * @return NULL|SettingProfile
     */
    public function getProfile($setting) {
      $group = $this->getGroupOfSetting($setting);
      if (isset(self::$_PROFILES[$group][$setting])) {
        $profileData = self::$_PROFILES[$group][$setting];
        if (is_array($profileData)) {
          $profileClass = array_shift($profileData);
        } else {
          $profileClass = $profileData;
          $profileData = [];
        }
        return new $profileClass(...$profileData);
      }
      return NULL;
    }

    /**
     * @param $group
     * @return \Traversable
     */
    public function getSettingsInGroup($group) {
      return new SortIterator(isset(self::$_PROFILES[$group]) ? array_keys(self::$_PROFILES[$group]) : []);
    }

    /**
     * @return \Traversable
     */
    public function getTranslatedLabels() {
      $unknownGroupLabel = new TranslatedText(self::$_LABELS[self::UNKNOWN]);
      return new SortIterator(
        new TranslatedList(self::$_LABELS),
        static function ($a, $b) use ($unknownGroupLabel) {
          $unknown = (string)$unknownGroupLabel;
          $a = (string)$a;
          $b = (string)$b;
          if ($a === $unknown) {
            return -1;
          }
          if ($b === $unknown) {
            return 1;
          }
          return strcmp($a, $b);
        }
      );
    }

    /**
     * @param string $setting
     * @return int
     */
    public function getGroupOfSetting($setting) {
      $map = $this->getSettingGroupMap();
      return isset($map[$setting]) ? $map[$setting] : self::UNKNOWN;
    }

    private function getSettingGroupMap() {
      if (NULL === self::$_SETTING_GROUP_MAP) {
        foreach (self::$_PROFILES as $group => $profilesGroup) {
          foreach ($profilesGroup as $setting => $profile) {
            self::$_SETTING_GROUP_MAP[$setting] = $group;
          }
        }
      }
      return self::$_SETTING_GROUP_MAP;
    }
  }
}

