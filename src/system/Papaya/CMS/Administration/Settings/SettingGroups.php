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

namespace Papaya\CMS\Administration\Settings {

  use Papaya\CMS\Administration\Settings\Profiles\ChoiceSetting;
  use Papaya\CMS\Administration\Settings\Profiles\ColorSetting;
  use Papaya\CMS\Administration\Settings\Profiles\CSSClassSetting;
  use Papaya\CMS\Administration\Settings\Profiles\EmailSetting;
  use Papaya\CMS\Administration\Settings\Profiles\FileSystemChoiceSetting;
  use Papaya\CMS\Administration\Settings\Profiles\FileSetting;
  use Papaya\CMS\Administration\Settings\Profiles\FlagSetting;
  use Papaya\CMS\Administration\Settings\Profiles\GeoPositionSetting;
  use Papaya\CMS\Administration\Settings\Profiles\HostNameSetting;
  use Papaya\CMS\Administration\Settings\Profiles\IntegerSetting;
  use Papaya\CMS\Administration\Settings\Profiles\LanguageCodeSetting;
  use Papaya\CMS\Administration\Settings\Profiles\LanguageIdSetting;
  use Papaya\CMS\Administration\Settings\Profiles\PageIdSetting;
  use Papaya\CMS\Administration\Settings\Profiles\PathSetting;
  use Papaya\CMS\Administration\Settings\Profiles\ReadOnlyDSNSetting;
  use Papaya\CMS\Administration\Settings\Profiles\ReadOnlySetting;
  use Papaya\CMS\Administration\Settings\Profiles\TextSetting;
  use Papaya\CMS\Administration\Settings\Profiles\ThemeSkinSetting;
  use Papaya\CMS\Administration\Settings\Profiles\URLSetting;
  use Papaya\CMS\Administration\Settings\Profiles\XSLTExtensionSetting;
  use Papaya\Application\Access;
  use Papaya\CMS\CMSConfiguration;
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
        CMSConfiguration::CDN_THEMES => URLSetting::class,
        CMSConfiguration::CDN_THEMES_SECURE => URLSetting::class,
        CMSConfiguration::PATH_DATA => PathSetting::class,
        CMSConfiguration::PATH_WEB => PathSetting::class,
        CMSConfiguration::PATH_TEMPLATES => PathSetting::class,
        CMSConfiguration::PATH_THEMES => PathSetting::class,
        CMSConfiguration::PATH_PUBLICFILES => PathSetting::class
      ],
      self::DEBUGGING => [
        CMSConfiguration::DBG_DATABASE_ERROR => FlagSetting::class,
        CMSConfiguration::DBG_XML_OUTPUT => FlagSetting::class,
        CMSConfiguration::DBG_XML_ERROR => FlagSetting::class,
        CMSConfiguration::DBG_XML_USERINPUT => FlagSetting::class,
        CMSConfiguration::DBG_PHRASES => FlagSetting::class,
        CMSConfiguration::DBG_SHOW_DEBUGS => FlagSetting::class
      ],
      self::LANGUAGE => [],
      self::PROJECT => [
        CMSConfiguration::PROJECT_TITLE => TextSetting::class,
        CMSConfiguration::REDIRECT_PROTECTION => FlagSetting::class,
        CMSConfiguration::CONTENT_LANGUAGE => [LanguageIdSetting::class, LanguageIdSetting::FILTER_IS_CONTENT],
        CMSConfiguration::CONTENT_LANGUAGE_COOKIE => FlagSetting::class,
        CMSConfiguration::DEFAULT_PROTOCOL => [
          ChoiceSetting::class,
          [
            0 => 'None',
            1 => 'http',
            2 => 'https',
          ]
        ],
        CMSConfiguration::DEFAULT_HOST => HostNameSetting::class,
        CMSConfiguration::DEFAULT_HOST_ACTION => [
          ChoiceSetting::class,
          [
            0 => 'None',
            1 => 'Redirect'
          ]
        ],
        CMSConfiguration::PUBLISH_SOCIALMEDIA => FlagSetting::class
      ],
      self::INTERNALS => [
        CMSConfiguration::DB_TABLEPREFIX => ReadOnlySetting::class,
        CMSConfiguration::DB_URI => ReadOnlyDSNSetting::class,
        CMSConfiguration::DB_URI_WRITE => ReadOnlyDSNSetting::class,
        CMSConfiguration::PATH_ADMIN => ReadOnlySetting::class,
        CMSConfiguration::MEDIADB_SUBDIRECTORIES => [IntegerSetting::class, 0, 10]
      ],
      self::SYSTEM => [
        CMSConfiguration::BROWSER_CRONJOBS => FlagSetting::class,
        CMSConfiguration::BROWSER_CRONJOBS_IP => TextSetting::class,
        CMSConfiguration::SPAM_LOG => FlagSetting::class,
        CMSConfiguration::SPAM_BLOCK => FlagSetting::class,
        CMSConfiguration::SPAM_SCOREMIN_PERCENT => [IntegerSetting::class, 1, 10],
        CMSConfiguration::SPAM_SCOREMAX_PERCENT => [IntegerSetting::class, 1, 90],
        CMSConfiguration::SPAM_STOPWORD_MAX => [IntegerSetting::class, 3, 10],
        CMSConfiguration::DATABASE_CLUSTER_SWITCH => [
          ChoiceSetting::class,
          [0 => 'manual', 1 => 'object', 2 => 'connection']
        ],
        CMSConfiguration::VERSIONS_MAXCOUNT => [IntegerSetting::class, 1, 2000],
        CMSConfiguration::URL_NAMELENGTH => [IntegerSetting::class, 1, 100],
        CMSConfiguration::URL_LEVEL_SEPARATOR => [
          ChoiceSetting::class,
          GroupSeparator::CHOICES,
          FALSE
        ],
        CMSConfiguration::URL_ALIAS_SEPARATOR => [
          ChoiceSetting::class,
          [',' => ',', ':' => ':', '*' => '*', '!' => '!'],
          FALSE
        ],
        CMSConfiguration::GMAPS_API_KEY => TextSetting::class,
        CMSConfiguration::GMAPS_DEFAULT_POSITION => GeoPositionSetting::class,
        CMSConfiguration::TRANSLITERATION_MODE => [
          ChoiceSetting::class,
          [
            // @ToDO Add Class Constants
            0 => 'papaya',
            1 => 'ext/translit',
          ],
          FALSE
        ],
        CMSConfiguration::SEARCH_BOOLEAN => [
          ChoiceSetting::class,
          [
            // @ToDo Add Class Constants
            0 => 'generic SQL (LIKE)',
            1 => 'MySQL FULLTEXT',
            2 => 'MySQL FULLTEXT BOOLEAN (MySQL >= 4.1)'
          ]
        ],
        CMSConfiguration::URL_FIXATION => FlagSetting::class,
        CMSConfiguration::XSLT_EXTENSION => XSLTExtensionSetting::class,
        CMSConfiguration::EXIT_REDIRECT_ENABLE => FlagSetting::class,
      ],
      self::LAYOUT => [
        CMSConfiguration::LAYOUT_TEMPLATES => [
          FileSystemChoiceSetting::class,
          \Papaya\CMS\Configuration\Path::PATH_TEMPLATES,
          '',
          NULL,
          FileSystemChoiceSetting::INCLUDE_DIRECTORIES
        ],
        CMSConfiguration::LAYOUT_THEME => [
          FileSystemChoiceSetting::class,
          \Papaya\CMS\Configuration\Path::PATH_THEMES,
          '',
          NULL,
          FileSystemChoiceSetting::INCLUDE_DIRECTORIES
        ],
        CMSConfiguration::LAYOUT_THEME_SET => ThemeSkinSetting::class
      ],
      self::DEFAULT_PAGES => [
        CMSConfiguration::PAGEID_DEFAULT => PageIdSetting::class,
        CMSConfiguration::PAGEID_USERDATA => PageIdSetting::class,
        CMSConfiguration::PAGEID_STATUS_301 => PageIdSetting::class,
        CMSConfiguration::PAGEID_STATUS_302 => PageIdSetting::class,
        CMSConfiguration::PAGEID_ERROR_403 => PageIdSetting::class,
        CMSConfiguration::PAGEID_ERROR_404 => PageIdSetting::class,
        CMSConfiguration::PAGEID_ERROR_500 => PageIdSetting::class,
      ],
      self::CHARSETS => [
        CMSConfiguration::DATABASE_COLLATION => [
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
        CMSConfiguration::INPUT_ENCODING => [
          ChoiceSetting::class,
          [
            'utf-8' => 'utf-8',
            'iso-8859-1' => 'iso-8859-1'
          ],
          FALSE
        ],
        CMSConfiguration::LATIN1_COMPATIBILITY => FlagSetting::class
      ],
      self::ADMINISTRATION => [
        CMSConfiguration::OVERVIEW_ITEMS_MESSAGES => [IntegerSetting::class, 1, 99],
        CMSConfiguration::OVERVIEW_ITEMS_TASKS => [IntegerSetting::class, 1, 99],
        CMSConfiguration::OVERVIEW_TASK_NOTIFY => FlagSetting::class,
        CMSConfiguration::OVERVIEW_ITEMS_PUBLISHED => [IntegerSetting::class, 1, 99],
        CMSConfiguration::OVERVIEW_ITEMS_UNPUBLISHED => [IntegerSetting::class, 1, 99],
        CMSConfiguration::UI_SEARCH_ANCESTOR_LIMIT => [IntegerSetting::class, 1, 999],
        CMSConfiguration::UI_SEARCH_CHARACTER_LIMIT => [IntegerSetting::class, 1, 9999],
        CMSConfiguration::UI_LANGUAGE => [LanguageCodeSetting::class, LanguageCodeSetting::FILTER_IS_INTERFACE],
        CMSConfiguration::UI_THEME => [
          FileSystemChoiceSetting::class,
          \Papaya\CMS\Configuration\Path::PATH_ADMINISTRATION,
          '/styles/themes/',
          '(^.+\.ini)'
        ],
        CMSConfiguration::UI_SECURE => FlagSetting::class,
        CMSConfiguration::UI_SECURE_WARNING => FlagSetting::class,
        CMSConfiguration::USE_RICHTEXT => FlagSetting::class,
        CMSConfiguration::RICHTEXT_TEMPLATES_FULL => [TextSetting::class, 1000, '(^([a-z]+)(,[a-z+])*)'],
        CMSConfiguration::RICHTEXT_TEMPLATES_SIMPLE => [TextSetting::class, 1000, '(^([a-z]+)(,[a-z+])*)'],
        CMSConfiguration::RICHTEXT_BROWSER_SPELLCHECK => FlagSetting::class,
        CMSConfiguration::RICHTEXT_LINK_TARGET => [
          ChoiceSetting::class,
          ['_self' => '_self', '_blank' => '_blank'],
          FALSE
        ],
        CMSConfiguration::RICHTEXT_CONTENT_CSS => [
          FileSystemChoiceSetting::class,
          \Papaya\CMS\Configuration\Path::PATH_THEME_CURRENT,
          '',
          '(.css$)',
          FileSystemChoiceSetting::INCLUDE_FILES | FileSystemChoiceSetting::INCLUDE_OPTION_NONE
        ]
      ],
      self::AUTHENTICATION => [
        CMSConfiguration::PASSWORD_ALGORITHM => [
          ChoiceSetting::class,
          [0 => 'PHP Default (suggested)', 1 => 'BCrypt'],
        ],
        CMSConfiguration::PASSWORD_REHASH => FlagSetting::class,
        CMSConfiguration::COMMUNITY_AUTOLOGIN => FlagSetting::class,
        CMSConfiguration::COMMUNITY_REDIRECT_PAGE => PageIdSetting::class,
        CMSConfiguration::COMMUNITY_RELOGIN => FlagSetting::class,
        CMSConfiguration::COMMUNITY_RELOGIN_EXP_DAYS => [IntegerSetting::class, 1, 40],
        CMSConfiguration::COMMUNITY_RELOGIN_SALT => TextSetting::class,
        CMSConfiguration::COMMUNITY_API_LOGIN => [
          ChoiceSetting::class,
          [
            0 => 'Handle',
            1 => 'Email',
            2 => 'Handle or email',
          ]
        ],
        CMSConfiguration::COMMUNITY_HANDLE_MAX_LENGTH => [IntegerSetting::class, 10, 40],
        CMSConfiguration::LOGIN_CHECKTIME => [IntegerSetting::class, 60, 999999],
        CMSConfiguration::LOGIN_BLOCKCOUNT => [IntegerSetting::class, 1, 99999],
        CMSConfiguration::LOGIN_NOTIFYCOUNT => [IntegerSetting::class, 1, 99999],
        CMSConfiguration::LOGIN_NOTIFYEMAIL => EmailSetting::class,
        CMSConfiguration::LOGIN_GC_ACTIVE => FlagSetting::class,
        CMSConfiguration::LOGIN_GC_DIVISOR => [IntegerSetting::class, 1, 999],
        CMSConfiguration::LOGIN_GC_TIME => [IntegerSetting::class, 60, 1000000],
        CMSConfiguration::LOGIN_RESTRICTION => [
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
        CMSConfiguration::SUPPORT_BUG_EMAIL => EmailSetting::class,
        CMSConfiguration::SUPPORT_PAGE_MANUAL => URLSetting::class,
        CMSConfiguration::SUPPORT_PAGE_NEWS => URLSetting::class
      ],
      self::FILES => [
        CMSConfiguration::FLASH_DEFAULT_VERSION => [TextSetting::class, 0, '(^\d{1,3}(\.\d{1,4}){0,2}$)D'],
        CMSConfiguration::FLASH_MIN_VERSION => [TextSetting::class, 0, '(^\d{1,3}(\.\d{1,4}){0,2}$)D'],
        CMSConfiguration::MAX_UPLOAD_SIZE => [IntegerSetting::class, 1, 100000],
        CMSConfiguration::NUM_UPLOAD_FIELDS => [IntegerSetting::class, 1, 20],
        CMSConfiguration::MEDIADB_THUMBSIZE => [IntegerSetting::class, 1, 10000],
        CMSConfiguration::IMAGE_CONVERTER => [
          ChoiceSetting::class,
          [
            'gd' => 'GD',
            'netpbm' => 'netpbm',
            'imagemagick' => 'Image Magick',
            'graphicsmagick' => 'GraphicksMagick'
          ]
        ],
        CMSConfiguration::THUMBS_FILETYPE => [
          ChoiceSetting::class,
          [
            0 => 'Original',
            1 => 'GIF',
            2 => 'JPEG',
            3 => 'PNG'
          ]
        ],
        CMSConfiguration::MEDIA_CUTLINE_MODE => [
          ChoiceSetting::class,
          [
            0 => 'manual only',
            1 => 'text (source)',
            2 => 'title (source) / text'
          ]
        ],
        CMSConfiguration::MEDIA_CUTLINE_LINK_TARGET => [
          ChoiceSetting::class,
          [
            '_self' => '_self',
            '_blank' => '_blank'
          ],
          FALSE
        ],
        CMSConfiguration::MEDIA_CUTLINE_LINK_CLASS => CSSClassSetting::class,
        CMSConfiguration::MEDIA_ALTTEXT_MODE => [
          ChoiceSetting::class,
          [
            // @ToDo Add Class Constants
            0 => 'explicit only', 1 => 'description'
          ]
        ],
        CMSConfiguration::MEDIA_CSSCLASS_DYNIMAGE => CSSClassSetting::class,
        CMSConfiguration::MEDIA_CSSCLASS_IMAGE => CSSClassSetting::class,
        CMSConfiguration::MEDIA_CSSCLASS_SUBTITLE => CSSClassSetting::class,
        CMSConfiguration::MEDIA_CSSCLASS_LINK => CSSClassSetting::class,
        CMSConfiguration::MEDIA_CSSCLASS_MAILTO => CSSClassSetting::class,
        CMSConfiguration::MEDIA_ELEMENTS_IMAGE => [
          ChoiceSetting::class,
          [
            PapayaTagParser::ELEMENTS_SPAN => 'span (only with subtitle)',
            PapayaTagParser::ELEMENTS_FIGURE => 'figure/figcaption (only with subtitle)',
            PapayaTagParser::ELEMENTS_FIGURE_MANDATORY => 'figure/figcaption (always)',
          ]
        ],
        CMSConfiguration::IMAGE_CONVERTER_PATH => PathSetting::class,
        CMSConfiguration::FILE_CMD_PATH => FileSetting::class,
        CMSConfiguration::PATH_MEDIADB_IMPORT => PathSetting::class,
        CMSConfiguration::THUMBS_JPEGQUALITY => [IntegerSetting::class, 20, 100],
        CMSConfiguration::THUMBS_TRANSPARENT => FlagSetting::class,
        CMSConfiguration::THUMBS_MEMORYCHECK_SUHOSIN => FlagSetting::class,
        CMSConfiguration::THUMBS_BACKGROUND => ColorSetting::class,
        CMSConfiguration::SENDFILE_HEADER => FlagSetting::class,
        CMSConfiguration::BANDWIDTH_SHAPING => FlagSetting::class,
        CMSConfiguration::BANDWIDTH_SHAPING_LIMIT => IntegerSetting::class,
        CMSConfiguration::BANDWIDTH_SHAPING_OFFSET => IntegerSetting::class,
        CMSConfiguration::MEDIA_STORAGE_SERVICE => [
          ChoiceSetting::class,
          [
            // @ToDo Add Class Constants
            'file' => 'File system', 's3' => 'AWS S3'
          ]
        ],
        CMSConfiguration::MEDIA_STORAGE_S3_BUCKET => TextSetting::class,
        CMSConfiguration::MEDIA_STORAGE_S3_KEY => TextSetting::class,
        CMSConfiguration::MEDIA_STORAGE_S3_KEYID => TextSetting::class
      ],
      self::CACHE => [
        CMSConfiguration::CACHE_BOXES => FlagSetting::class,
        CMSConfiguration::CACHE_DATA => FlagSetting::class,
        CMSConfiguration::CACHE_DATA_MEMCACHE_SERVERS => TextSetting::class,
        CMSConfiguration::CACHE_DATA_SERVICE => [ChoiceSetting::class, self::CACHE_SERVICE_CHOICES],
        CMSConfiguration::CACHE_DATA_TIME => [IntegerSetting::class, 0, 9999999999],
        CMSConfiguration::CACHE_MEMCACHE_SERVERS => TextSetting::class,
        CMSConfiguration::CACHE_NOTIFIER => TextSetting::class,
        CMSConfiguration::CACHE_OUTPUT => FlagSetting::class,
        CMSConfiguration::CACHE_PAGES => FlagSetting::class,
        CMSConfiguration::CACHE_SERVICE => [ChoiceSetting::class, self::CACHE_SERVICE_CHOICES],
        CMSConfiguration::CACHE_THEMES => FlagSetting::class,
        CMSConfiguration::CACHE_TIME_BOXES => [IntegerSetting::class, 0, 9999999999],
        CMSConfiguration::CACHE_TIME_BROWSER => [IntegerSetting::class, 0, 9999999999],
        CMSConfiguration::CACHE_TIME_FILES => [IntegerSetting::class, 0, 9999999999],
        CMSConfiguration::CACHE_TIME_OUTPUT => [IntegerSetting::class, 0, 9999999999],
        CMSConfiguration::CACHE_TIME_PAGES => [IntegerSetting::class, 0, 9999999999],
        CMSConfiguration::CACHE_TIME_THEMES => [IntegerSetting::class, 0, 9999999999],
        CMSConfiguration::CACHE_DISABLE_FILE_DELETE => FlagSetting::class,
        CMSConfiguration::COMPRESS_CACHE_OUTPUT => FlagSetting::class,
        CMSConfiguration::COMPRESS_CACHE_THEMES => FlagSetting::class,
        CMSConfiguration::COMPRESS_OUTPUT => FlagSetting::class,
      ],
      self::LOGGING => [
        CMSConfiguration::LOG_PHP_ERRORLEVEL => [
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
        CMSConfiguration::LOG_DATABASE_CLUSTER_VIOLATIONS => FlagSetting::class,
        CMSConfiguration::LOG_DATABASE_QUERY => [
          ChoiceSetting::class,
          [
            // @ToDo Add Class Constants
            0 => 'none', 1 => 'slow', 2 => 'all'
          ]
        ],
        CMSConfiguration::LOG_DATABASE_QUERY_SLOW => [
          IntegerSetting::class, 0, 999999
        ],
        CMSConfiguration::LOG_DATABASE_QUERY_DETAILS => FlagSetting::class,
        CMSConfiguration::LOG_EVENT_PAGE_MOVED => FlagSetting::class,
        CMSConfiguration::LOG_ERROR_THUMBNAIL => FlagSetting::class,
        CMSConfiguration::LOG_RUNTIME_DATABASE => FlagSetting::class,
        CMSConfiguration::LOG_RUNTIME_REQUEST => FlagSetting::class,
        CMSConfiguration::LOG_RUNTIME_TEMPLATE => FlagSetting::class,
        CMSConfiguration::PROTOCOL_DATABASE => FlagSetting::class,
        CMSConfiguration::PROTOCOL_DATABASE_DEBUG => FlagSetting::class,
        CMSConfiguration::PROTOCOL_WILDFIRE => FlagSetting::class,
        CMSConfiguration::PROTOCOL_XHTML => FlagSetting::class,
        CMSConfiguration::QUERYLOG => [
          ChoiceSetting::class,
          [
            // @ToDo Add Class Constants
            0 => 'none', 1 => 'slow', 2 => 'all'
          ]
        ],
        CMSConfiguration::QUERYLOG_SLOW => [IntegerSetting::class, 0, 100000],
        CMSConfiguration::QUERYLOG_DETAILS => FlagSetting::class
      ],
      self::SESSION => [
        CMSConfiguration::DB_DISCONNECT_SESSIONSTART => FlagSetting::class,
        CMSConfiguration::SESSION_ACTIVATION => [
          ChoiceSetting::class,
          [
            Session::ACTIVATION_ALWAYS => 'always',
            Session::ACTIVATION_NEVER => 'never',
            Session::ACTIVATION_DYNAMIC => 'dynamic',
          ]
        ],
        CMSConfiguration::SESSION_CACHE => [
          ChoiceSetting::class,
          [
            SessionOptions::CACHE_PRIVATE => 'private',
            SessionOptions::CACHE_NONE => 'none'
          ]
        ],
        CMSConfiguration::SESSION_HTTP_ONLY => FlagSetting::class,
        CMSConfiguration::SESSION_ID_FALLBACK => [
          ChoiceSetting::class,
          [
            // @ToDo - Replace Keys With Class Constants
            'none' => 'none',
            'rewrite' => 'Path Rewrite',
            'get' => 'Transparent SID'
          ]
        ],
        CMSConfiguration::SESSION_SECURE => FlagSetting::class,
        CMSConfiguration::SESSION_START => FlagSetting::class,
        CMSConfiguration::SESSION_DOMAIN => HostNameSetting::class,
        CMSConfiguration::SESSION_PATH => PathSetting::class,
        CMSConfiguration::CONSENT_COOKIE_REQUIRE => FlagSetting::class,
        CMSConfiguration::CONSENT_COOKIE_NAME => [
          TextSetting::class, 50, '(^[a-z\d_]+$)'
        ],
        CMSConfiguration::CONSENT_COOKIE_LEVELS => [
          TextSetting::class, 500
        ]
      ],
      self::FEATURES => [
        CMSConfiguration::PROTECT_FORM_CHANGES => FlagSetting::class,
        CMSConfiguration::PUBLICATION_CHANGE_LEVEL => FlagSetting::class,
        CMSConfiguration::DATAFILTER_USE => FlagSetting::class,
        CMSConfiguration::PUBLICATION_AUDITING => FlagSetting::class,
        CMSConfiguration::FEATURE_BOXGROUPS_LINKABLE => FlagSetting::class,
        CMSConfiguration::FEATURE_MEDIA_DATABASE_2 => FlagSetting::class
      ],
      self::FEATURES_EXPERIMENTAL => [
        CMSConfiguration::IMPORTFILTER_USE => FlagSetting::class
      ],
      self::DEPRECATED => [
        CMSConfiguration::URL_EXTENSION => [
          ChoiceSetting::class,
          ['html' => 'html', 'papaya' => 'papaya'],
          FALSE
        ],
        CMSConfiguration::PAGE_STATISTIC => FlagSetting::class,
        CMSConfiguration::STATISTIC_PRESERVE_IP => FlagSetting::class
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
        $profile = new $profileClass(...$profileData);
        $profile->papaya($this->papaya());
        return $profile;
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
