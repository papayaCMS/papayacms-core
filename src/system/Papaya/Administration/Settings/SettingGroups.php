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
  use Papaya\Administration\Settings\Profiles\ColorSetting;
  use Papaya\Administration\Settings\Profiles\CSSClassSetting;
  use Papaya\Administration\Settings\Profiles\EmailSetting;
  use Papaya\Administration\Settings\Profiles\FileSystemChoiceSetting;
  use Papaya\Administration\Settings\Profiles\FileSetting;
  use Papaya\Administration\Settings\Profiles\FlagSetting;
  use Papaya\Administration\Settings\Profiles\GeoPositionSetting;
  use Papaya\Administration\Settings\Profiles\HostNameSetting;
  use Papaya\Administration\Settings\Profiles\IntegerSetting;
  use Papaya\Administration\Settings\Profiles\LanguageCodeSetting;
  use Papaya\Administration\Settings\Profiles\LanguageIdSetting;
  use Papaya\Administration\Settings\Profiles\PageIdSetting;
  use Papaya\Administration\Settings\Profiles\PathSetting;
  use Papaya\Administration\Settings\Profiles\ReadOnlyDSNSetting;
  use Papaya\Administration\Settings\Profiles\ReadOnlySetting;
  use Papaya\Administration\Settings\Profiles\TextSetting;
  use Papaya\Administration\Settings\Profiles\ThemeSkinSetting;
  use Papaya\Administration\Settings\Profiles\URLSetting;
  use Papaya\Administration\Settings\Profiles\XSLTExtensionSetting;
  use Papaya\Application\Access;
  use Papaya\Configuration\CMS;
  use Papaya\Iterator\Callback as CallbackIterator;
  use Papaya\Iterator\Filter\Callback as CallbackFilterIterator;
  use Papaya\Iterator\SortIterator;
  use Papaya\Request\Parameters\GroupSeparator;
  use Papaya\Session;
  use Papaya\Session\Options as SessionOptions;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\UI\Text\Translated\Collection as TranslatedList;
  use papaya_parser as PapayaTagParser;

  class SettingGroups implements Access, \IteratorAggregate {

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
    const DEPRECATED = 20;

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
      self::FEATURES_EXPERIMENTAL => 'Experimental Features',
      self::DEPRECATED => 'Deprecated'
    ];

    private static $_PROFILES = [
      self::DATABASE => [],
      self::PATHS => [
        CMS::CDN_THEMES => URLSetting::class,
        CMS::CDN_THEMES_SECURE => URLSetting::class,
        CMS::PATH_DATA => PathSetting::class,
        CMS::PATH_WEB => PathSetting::class,
        CMS::PATH_TEMPLATES => PathSetting::class,
        CMS::PATH_THEMES => PathSetting::class,
        CMS::PATH_PUBLICFILES => PathSetting::class
      ],
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
        CMS::PROJECT_TITLE => TextSetting::class,
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
        CMS::DEFAULT_HOST => HostNameSetting::class,
        CMS::DEFAULT_HOST_ACTION => [
          ChoiceSetting::class,
          [
            0 => 'None',
            1 => 'Redirect'
          ]
        ],
        CMS::PUBLISH_SOCIALMEDIA => FlagSetting::class
      ],
      self::INTERNALS => [
        CMS::DB_TABLEPREFIX => ReadOnlySetting::class,
        CMS::DB_URI => ReadOnlyDSNSetting::class,
        CMS::DB_URI_WRITE => ReadOnlyDSNSetting::class,
        CMS::PATH_ADMIN => ReadOnlySetting::class,
        CMS::MEDIADB_SUBDIRECTORIES => [IntegerSetting::class, 0, 10]
      ],
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
          GroupSeparator::CHOICES,
          FALSE
        ],
        CMS::URL_ALIAS_SEPARATOR => [
          ChoiceSetting::class,
          [',' => ',', ':' => ':', '*' => '*', '!' => '!'],
          FALSE
        ],
        CMS::GMAPS_API_KEY => TextSetting::class,
        CMS::GMAPS_DEFAULT_POSITION => GeoPositionSetting::class,
        CMS::TRANSLITERATION_MODE => [
          ChoiceSetting::class,
          [
            // @ToDO Add Class Constants
            0 => 'papaya',
            1 => 'ext/translit',
          ],
          FALSE
        ],
        CMS::SEARCH_BOOLEAN => [
          ChoiceSetting::class,
          [
            // @ToDo Add Class Constants
            0 => 'generic SQL (LIKE)',
            1 => 'MySQL FULLTEXT',
            2 => 'MySQL FULLTEXT BOOLEAN (MySQL >= 4.1)'
          ]
        ],
        CMS::URL_FIXATION => FlagSetting::class,
        CMS::XSLT_EXTENSION => XSLTExtensionSetting::class
      ],
      self::LAYOUT => [
        CMS::LAYOUT_TEMPLATES => [
          FileSystemChoiceSetting::class,
          \Papaya\Configuration\Path::PATH_TEMPLATES,
          '',
          NULL,
          FileSystemChoiceSetting::INCLUDE_DIRECTORIES
        ],
        CMS::LAYOUT_THEME => [
          FileSystemChoiceSetting::class,
          \Papaya\Configuration\Path::PATH_THEMES,
          '',
          NULL,
          FileSystemChoiceSetting::INCLUDE_DIRECTORIES
        ],
        CMS::LAYOUT_THEME_SET => ThemeSkinSetting::class
      ],
      self::DEFAULT_PAGES => [
        CMS::PAGEID_DEFAULT => PageIdSetting::class,
        CMS::PAGEID_USERDATA => PageIdSetting::class,
        CMS::PAGEID_STATUS_301 => PageIdSetting::class,
        CMS::PAGEID_STATUS_302 => PageIdSetting::class,
        CMS::PAGEID_ERROR_403 => PageIdSetting::class,
        CMS::PAGEID_ERROR_404 => PageIdSetting::class,
        CMS::PAGEID_ERROR_500 => PageIdSetting::class,
      ],
      self::CHARSETS => [
        CMS::DATABASE_COLLATION => [
          ChoiceSetting::class,
          [
            'utf8_bin' => 'utf8_bin',
            'utf8_czech_ci' => 'utf8_czech_ci',
            'utf8_danish_ci' => 'utf8_danish_ci',
            'utf8_estonian_ci' => 'utf8_estonian_ci',
            'utf8_general_ci' => 'utf8_general_ci',
            'utf8_icelandic_ci' => 'utf8_icelandic_ci',
            'utf8_latvian_ci' => 'utf8_latvian_ci',
            'utf8_lithuanian_ci' => 'utf8_lithuanian_ci',
            'utf8_persian_ci' => 'utf8_persian_ci',
            'utf8_polish_ci' => 'utf8_polish_ci',
            'utf8_roman_ci' => 'utf8_roman_ci',
            'utf8_romanian_ci' => 'utf8_romanian_ci',
            'utf8_slovak_ci' => 'utf8_slovak_ci',
            'utf8_slovenian_ci' => 'utf8_slovenian_ci',
            'utf8_spanish2_ci' => 'utf8_spanish2_ci',
            'utf8_spanish_ci' => 'utf8_spanish_ci',
            'utf8_swedish_ci' => 'utf8_swedish_ci',
            'utf8_turkish_ci' => 'utf8_turkish_ci',
            'utf8_unicode_ci' => 'utf8_unicode_ci'
          ],
          FALSE
        ],
        CMS::INPUT_ENCODING => [
          ChoiceSetting::class,
          [
            'utf-8' => 'utf-8',
            'iso-8859-1' => 'iso-8859-1'
          ],
          FALSE
        ],
        CMS::LATIN1_COMPATIBILITY => FlagSetting::class
      ],
      self::ADMINISTRATION => [
        CMS::OVERVIEW_ITEMS_MESSAGES => [IntegerSetting::class, 1, 99],
        CMS::OVERVIEW_ITEMS_TASKS => [IntegerSetting::class, 1, 99],
        CMS::OVERVIEW_TASK_NOTIFY => FlagSetting::class,
        CMS::OVERVIEW_ITEMS_PUBLISHED => [IntegerSetting::class, 1, 99],
        CMS::OVERVIEW_ITEMS_UNPUBLISHED => [IntegerSetting::class, 1, 99],
        CMS::UI_SEARCH_ANCESTOR_LIMIT => [IntegerSetting::class, 1, 999],
        CMS::UI_SEARCH_CHARACTER_LIMIT => [IntegerSetting::class, 1, 9999],
        CMS::UI_LANGUAGE => [LanguageCodeSetting::class, LanguageCodeSetting::FILTER_IS_INTERFACE],
        CMS::UI_THEME => [
          FileSystemChoiceSetting::class,
          \Papaya\Configuration\Path::PATH_ADMINISTRATION,
          '/styles/themes/',
          '(^.+\.ini)'
        ],
        CMS::UI_SECURE => FlagSetting::class,
        CMS::UI_SECURE_WARNING => FlagSetting::class,
        CMS::USE_RICHTEXT => FlagSetting::class,
        CMS::RICHTEXT_TEMPLATES_FULL => [TextSetting::class, 1000, '(^([a-z]+)(,[a-z+])*)'],
        CMS::RICHTEXT_TEMPLATES_SIMPLE => [TextSetting::class, 1000, '(^([a-z]+)(,[a-z+])*)'],
        CMS::RICHTEXT_BROWSER_SPELLCHECK => FlagSetting::class,
        CMS::RICHTEXT_LINK_TARGET => [
          ChoiceSetting::class,
          ['_self' => '_self', '_blank' => '_blank'],
          FALSE
        ],
        CMS::RICHTEXT_CONTENT_CSS => [
          FileSystemChoiceSetting::class,
          \Papaya\Configuration\Path::PATH_THEME_CURRENT,
          '',
          '(.css$)',
          FileSystemChoiceSetting::INCLUDE_FILES | FileSystemChoiceSetting::INCLUDE_OPTION_NONE
        ]
      ],
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
        CMS::LOGIN_CHECKTIME => [IntegerSetting::class, 60, 999999],
        CMS::LOGIN_BLOCKCOUNT => [IntegerSetting::class, 1, 99999],
        CMS::LOGIN_NOTIFYCOUNT => [IntegerSetting::class, 1, 99999],
        CMS::LOGIN_NOTIFYEMAIL => EmailSetting::class,
        CMS::LOGIN_GC_ACTIVE => FlagSetting::class,
        CMS::LOGIN_GC_DIVISOR => [IntegerSetting::class, 1, 999],
        CMS::LOGIN_GC_TIME => [IntegerSetting::class, 60, 1000000],
        CMS::LOGIN_RESTRICTION => [
          ChoiceSetting::class,
          [
            // @ToDo Add Class Constants
            0 => 'None',
            1 => 'Use block list',
            2 => 'Use block and access list',
            3 => 'Restrict to acess list'
          ]
        ]
      ],
      self::SUPPORT => [
        CMS::SUPPORT_BUG_EMAIL => EmailSetting::class,
        CMS::SUPPORT_PAGE_MANUAL => URLSetting::class,
        CMS::SUPPORT_PAGE_NEWS => URLSetting::class
      ],
      self::FILES => [
        CMS::FLASH_DEFAULT_VERSION => [TextSetting::class, 0, '(^\d{1,3}(\.\d{1,4}){0,2}$)D'],
        CMS::FLASH_MIN_VERSION => [TextSetting::class, 0, '(^\d{1,3}(\.\d{1,4}){0,2}$)D'],
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
        CMS::MEDIA_CUTLINE_LINK_CLASS => CSSClassSetting::class,
        CMS::MEDIA_ALTTEXT_MODE => [
          ChoiceSetting::class,
          [
            // @ToDo Add Class Constants
            0 => 'explicit only', 1 => 'description'
          ]
        ],
        CMS::MEDIA_CSSCLASS_DYNIMAGE => CSSClassSetting::class,
        CMS::MEDIA_CSSCLASS_IMAGE => CSSClassSetting::class,
        CMS::MEDIA_CSSCLASS_SUBTITLE => CSSClassSetting::class,
        CMS::MEDIA_CSSCLASS_LINK => CSSClassSetting::class,
        CMS::MEDIA_CSSCLASS_MAILTO => CSSClassSetting::class,
        CMS::MEDIA_ELEMENTS_IMAGE => [
          ChoiceSetting::class,
          [
            PapayaTagParser::ELEMENTS_SPAN => 'span (only with subtitle)',
            PapayaTagParser::ELEMENTS_FIGURE => 'figure/figcaption (only with subtitle)',
            PapayaTagParser::ELEMENTS_FIGURE_MANDATORY => 'figure/figcaption (always)',
          ]
        ],
        CMS::IMAGE_CONVERTER_PATH => PathSetting::class,
        CMS::FILE_CMD_PATH => FileSetting::class,
        CMS::PATH_MEDIADB_IMPORT => PathSetting::class,
        CMS::THUMBS_JPEGQUALITY => [IntegerSetting::class, 20, 100],
        CMS::THUMBS_TRANSPARENT => FlagSetting::class,
        CMS::THUMBS_MEMORYCHECK_SUHOSIN => FlagSetting::class,
        CMS::THUMBS_BACKGROUND => ColorSetting::class,
        CMS::SENDFILE_HEADER => FlagSetting::class,
        CMS::BANDWIDTH_SHAPING => FlagSetting::class,
        CMS::BANDWIDTH_SHAPING_LIMIT => IntegerSetting::class,
        CMS::BANDWIDTH_SHAPING_OFFSET => IntegerSetting::class,
        CMS::MEDIA_STORAGE_SERVICE => [
          ChoiceSetting::class,
          [
            // @ToDo Add Class Constants
            'file' => 'File system', 's3' => 'AWS S3'
          ]
        ],
        CMS::MEDIA_STORAGE_S3_BUCKET => TextSetting::class,
        CMS::MEDIA_STORAGE_S3_KEY => TextSetting::class,
        CMS::MEDIA_STORAGE_S3_KEYID => TextSetting::class
      ],
      self::CACHE => [
        CMS::CACHE_BOXES => FlagSetting::class,
        CMS::CACHE_DATA => FlagSetting::class,
        CMS::CACHE_DATA_MEMCACHE_SERVERS => TextSetting::class,
        CMS::CACHE_DATA_SERVICE => [ChoiceSetting::class, self::CACHE_SERVICE_CHOICES],
        CMS::CACHE_DATA_TIME => [IntegerSetting::class, 0, 9999999999],
        CMS::CACHE_MEMCACHE_SERVERS => TextSetting::class,
        CMS::CACHE_NOTIFIER => TextSetting::class,
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
      self::LOGGING => [
        CMS::LOG_PHP_ERRORLEVEL => [
          ChoiceSetting::class,
          [
            // @ToDo Refactor To Class Constants Using Static Expressions
            0 => '~E_ALL',
            30719 => 'E_ALL & ~E_STRICT',
            30711 => 'E_ALL & ~(E_STRICT | E_NOTICE)',
            29687 => 'E_ALL & ~(E_STRICT | E_NOTICE | E_USER_NOTICE)'
          ],
          FALSE
        ],
        CMS::LOG_DATABASE_CLUSTER_VIOLATIONS => FlagSetting::class,
        CMS::LOG_DATABASE_QUERY => [
          ChoiceSetting::class,
          [
            // @ToDo Add Class Constants
            0 => 'none', 1 => 'slow', 2 => 'all'
          ]
        ],
        CMS::LOG_DATABASE_QUERY_SLOW => [
          IntegerSetting::class, 0, 999999
        ],
        CMS::LOG_DATABASE_QUERY_DETAILS => FlagSetting::class,
        CMS::LOG_EVENT_PAGE_MOVED => FlagSetting::class,
        CMS::LOG_ERROR_THUMBNAIL => FlagSetting::class,
        CMS::LOG_RUNTIME_DATABASE => FlagSetting::class,
        CMS::LOG_RUNTIME_REQUEST => FlagSetting::class,
        CMS::LOG_RUNTIME_TEMPLATE => FlagSetting::class,
        CMS::PROTOCOL_DATABASE => FlagSetting::class,
        CMS::PROTOCOL_DATABASE_DEBUG => FlagSetting::class,
        CMS::PROTOCOL_WILDFIRE => FlagSetting::class,
        CMS::PROTOCOL_XHTML => FlagSetting::class,
        CMS::QUERYLOG => [
          ChoiceSetting::class,
          [
            // @ToDo Add Class Constants
            0 => 'none', 1 => 'slow', 2 => 'all'
          ]
        ],
        CMS::QUERYLOG_SLOW => [IntegerSetting::class, 0, 100000],
        CMS::QUERYLOG_DETAILS => FlagSetting::class
      ],
      self::SESSION => [
        CMS::DB_DISCONNECT_SESSIONSTART => FlagSetting::class,
        CMS::SESSION_ACTIVATION => [
          ChoiceSetting::class,
          [
            Session::ACTIVATION_ALWAYS => 'always',
            Session::ACTIVATION_NEVER => 'never',
            Session::ACTIVATION_DYNAMIC => 'dynamic',
          ]
        ],
        CMS::SESSION_CACHE => [
          ChoiceSetting::class,
          [
            SessionOptions::CACHE_PRIVATE => 'private',
            SessionOptions::CACHE_NONE => 'none'
          ]
        ],
        CMS::SESSION_HTTP_ONLY => FlagSetting::class,
        CMS::SESSION_ID_FALLBACK => [
          ChoiceSetting::class,
          [
            // @ToDo - Replace Keys With Class Constants
            'none' => 'none',
            'rewrite' => 'Path Rewrite',
            'get' => 'Transparent SID'
          ]
        ],
        CMS::SESSION_SECURE => FlagSetting::class,
        CMS::SESSION_START => FlagSetting::class,
        CMS::SESSION_DOMAIN => HostNameSetting::class,
        CMS::SESSION_PATH => PathSetting::class
      ],
      self::FEATURES => [
        CMS::PROTECT_FORM_CHANGES => FlagSetting::class,
        CMS::PUBLICATION_CHANGE_LEVEL => FlagSetting::class,
        CMS::DATAFILTER_USE => FlagSetting::class,
        CMS::PUBLICATION_AUDITING => FlagSetting::class,
        CMS::FEATURE_BOXGROUPS_LINKABLE => FlagSetting::class
      ],
      self::FEATURES_EXPERIMENTAL => [
        CMS::IMPORTFILTER_USE => FlagSetting::class
      ],
      self::DEPRECATED => [
        CMS::URL_EXTENSION => [
          ChoiceSetting::class,
          ['html' => 'html', 'papaya' => 'papaya'],
          FALSE
        ],
        CMS::PAGE_STATISTIC => FlagSetting::class,
        CMS::STATISTIC_PRESERVE_IP => FlagSetting::class
      ]
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
        new TranslatedList(
          new CallbackFilterIterator(
            self::$_LABELS,
            static function ($value, $key) {
              return (
                $key === self::UNKNOWN || (
                  isset(self::$_PROFILES[$key]) && count(self::$_PROFILES[$key]) > 0)
              );
            }
          )
        ),
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

    public function getIterator() {
      return new CallbackIterator(
        $this->getTranslatedLabels(),
        function($label, $group) {
           return new SortIterator($this->getSettingsInGroup($group));
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

