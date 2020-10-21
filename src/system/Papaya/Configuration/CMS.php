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

namespace Papaya\Configuration {

  use Papaya\Content;
  use Papaya\Session;
  use Papaya\Streamwrapper\S3 as S3Streamwrapper;
  use Papaya\URL\Current as CurrentURL;
  use Papaya\Utility;

  /*
   * Define a default project name, using the http host name
   *
   * @var string
   */
  \define(
    'PAPAYA_CONFIGURATION_HOSTNAME',
    isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''
  );

  /**
   * The new papaya cms configuration option object. This replaces base_options and provides the
   * same api (mostly). It can be used in the same way, but adds the new features.
   *
   * @package Papaya-Library
   * @subpackage Configuration
   */
  class CMS extends GlobalValues {

    const DOCUMENT_ROOT = 'PAPAYA_DOCUMENT_ROOT';
    const INCLUDE_PATH = 'PAPAYA_INCLUDE_PATH';

    const DB_URI = 'PAPAYA_DB_URI';
    const DB_URI_WRITE = 'PAPAYA_DB_URI_WRITE';
    const DB_TBL_OPTIONS = 'PAPAYA_DB_TBL_OPTIONS';
    const DB_TABLEPREFIX = 'PAPAYA_DB_TABLEPREFIX';
    const DB_CONNECT_PERSISTENT = 'PAPAYA_DB_CONNECT_PERSISTENT';
    // maintance (defined in configuration file)
    const MAINTENANCE_MODE = 'PAPAYA_MAINTENANCE_MODE';
    const ERRORDOCUMENT_MAINTENANCE = 'PAPAYA_ERRORDOCUMENT_MAINTENANCE';
    const ERRORDOCUMENT_503 = 'PAPAYA_ERRORDOCUMENT_503';
    // security (defined in configuration file)
    const PASSWORD_REHASH = 'PAPAYA_PASSWORD_REHASH';
    const PASSWORD_ALGORITHM = 'PAPAYA_PASSWORD_ALGORITHM';
    const PASSWORD_METHOD = 'PAPAYA_PASSWORD_METHOD';
    const PASSWORD_PREFIX = 'PAPAYA_PASSWORD_PREFIX';
    const PASSWORD_SUFFFIX = 'PAPAYA_PASSWORD_SUFFFIX';
    const DISABLE_XHEADERS = 'PAPAYA_DISABLE_XHEADERS';
    const HEADER_HTTPS_TOKEN = 'PAPAYA_HEADER_HTTPS_TOKEN';
    // profiler (defined in configuration file)
    const PROFILER_ACTIVE = 'PAPAYA_PROFILER_ACTIVE';
    const PROFILER_DIVISOR = 'PAPAYA_PROFILER_DIVISOR';
    const PROFILER_STORAGE = 'PAPAYA_PROFILER_STORAGE';
    const PROFILER_STORAGE_DIRECTORY = 'PAPAYA_PROFILER_STORAGE_DIRECTORY';
    const PROFILER_STORAGE_DATABASE = 'PAPAYA_PROFILER_STORAGE_DATABASE';
    const PROFILER_STORAGE_DATABASE_TABLE = 'PAPAYA_PROFILER_STORAGE_DATABASE_TABLE';
    const PROFILER_SERVER_ID = 'PAPAYA_PROFILER_SERVER_ID';
    // debug (defined in configuration file)
    const DBG_DEVMODE = 'PAPAYA_DBG_DEVMODE';
    const DEBUG_LANGUAGE_PHRASES = 'PAPAYA_DEBUG_LANGUAGE_PHRASES';

    // name the project
    const PROJECT_TITLE = 'PAPAYA_PROJECT_TITLE';

    // log & debugging old
    const DBG_DATABASE_ERROR = 'PAPAYA_DBG_DATABASE_ERROR';
    const DBG_XML_OUTPUT = 'PAPAYA_DBG_XML_OUTPUT';
    const DBG_XML_ERROR = 'PAPAYA_DBG_XML_ERROR';
    const DBG_XML_USERINPUT = 'PAPAYA_DBG_XML_USERINPUT';
    const DBG_PHRASES = 'PAPAYA_DBG_PHRASES';
    const DBG_SHOW_DEBUGS = 'PAPAYA_DBG_SHOW_DEBUGS';

    // paths and path urls
    const PATH_DATA = 'PAPAYA_PATH_DATA';
    const PATH_TEMPLATES = 'PAPAYA_PATH_TEMPLATES';
    const PATH_WEB = 'PAPAYA_PATH_WEB';
    const PATH_THEMES = 'PAPAYA_PATH_THEMES';
    const CDN_THEMES = 'PAPAYA_CDN_THEMES';
    const CDN_THEMES_SECURE = 'PAPAYA_CDN_THEMES_SECURE';
    const PATH_PUBLICFILES = 'PAPAYA_PATH_PUBLICFILES';
    const PATH_ADMIN = 'PAPAYA_PATH_ADMIN';

    // Community / Surfers
    const COMMUNITY_REDIRECT_PAGE = 'PAPAYA_COMMUNITY_REDIRECT_PAGE';
    const COMMUNITY_AUTOLOGIN = 'PAPAYA_COMMUNITY_AUTOLOGIN';
    const COMMUNITY_RELOGIN = 'PAPAYA_COMMUNITY_RELOGIN';
    const COMMUNITY_RELOGIN_SALT = 'PAPAYA_COMMUNITY_RELOGIN_SALT';
    const COMMUNITY_RELOGIN_EXP_DAYS = 'PAPAYA_COMMUNITY_RELOGIN_EXP_DAYS';
    const COMMUNITY_API_LOGIN = 'PAPAYA_COMMUNITY_API_LOGIN';
    const COMMUNITY_HANDLE_MAX_LENGTH = 'PAPAYA_COMMUNITY_HANDLE_MAX_LENGTH';

    const CONTENT_LANGUAGE = 'PAPAYA_CONTENT_LANGUAGE';
    const CONTENT_LANGUAGE_COOKIE = 'PAPAYA_CONTENT_LANGUAGE_COOKIE';

    const DEFAULT_PROTOCOL = 'PAPAYA_DEFAULT_PROTOCOL';
    const DEFAULT_HOST = 'PAPAYA_DEFAULT_HOST';
    const DEFAULT_HOST_ACTION = 'PAPAYA_DEFAULT_HOST_ACTION';
    const REDIRECT_PROTECTION = 'PAPAYA_REDIRECT_PROTECTION';

    const FLASH_DEFAULT_VERSION = 'PAPAYA_FLASH_DEFAULT_VERSION';
    const FLASH_MIN_VERSION = 'PAPAYA_FLASH_MIN_VERSION';

    const PAGE_STATISTIC = 'PAPAYA_PAGE_STATISTIC';
    const STATISTIC_PRESERVE_IP = 'PAPAYA_STATISTIC_PRESERVE_IP';
    const PUBLISH_SOCIALMEDIA = 'PAPAYA_PUBLISH_SOCIALMEDIA';

    // spam filter
    const SPAM_LOG = 'PAPAYA_SPAM_LOG';
    const SPAM_BLOCK = 'PAPAYA_SPAM_BLOCK';
    const SPAM_SCOREMIN_PERCENT = 'PAPAYA_SPAM_SCOREMIN_PERCENT';
    const SPAM_SCOREMAX_PERCENT = 'PAPAYA_SPAM_SCOREMAX_PERCENT';
    const SPAM_STOPWORD_MAX = 'PAPAYA_SPAM_STOPWORD_MAX';

    const DATABASE_CLUSTER_SWITCH = 'PAPAYA_DATABASE_CLUSTER_SWITCH';
    const SEARCH_BOOLEAN = 'PAPAYA_SEARCH_BOOLEAN';
    const VERSIONS_MAXCOUNT = 'PAPAYA_VERSIONS_MAXCOUNT';

    const UI_LANGUAGE = 'PAPAYA_UI_LANGUAGE';
    const UI_SECURE = 'PAPAYA_UI_SECURE';
    const UI_SECURE_WARNING = 'PAPAYA_UI_SECURE_WARNING';
    const UI_THEME = 'PAPAYA_UI_THEME';
    const UI_SEARCH_ANCESTOR_LIMIT = 'PAPAYA_UI_SEARCH_ANCESTOR_LIMIT';
    const UI_SEARCH_CHARACTER_LIMIT = 'PAPAYA_UI_SEARCH_CHARACTER_LIMIT';

    const PROTECT_FORM_CHANGES = 'PAPAYA_PROTECT_FORM_CHANGES';

    const USE_RICHTEXT = 'PAPAYA_USE_RICHTEXT';
    const RICHTEXT_BROWSER_SPELLCHECK = 'PAPAYA_RICHTEXT_BROWSER_SPELLCHECK';
    const RICHTEXT_TEMPLATES_FULL = 'PAPAYA_RICHTEXT_TEMPLATES_FULL';
    const RICHTEXT_TEMPLATES_SIMPLE = 'PAPAYA_RICHTEXT_TEMPLATES_SIMPLE';
    const RICHTEXT_CONTENT_CSS = 'PAPAYA_RICHTEXT_CONTENT_CSS';
    const RICHTEXT_LINK_TARGET = 'PAPAYA_RICHTEXT_LINK_TARGET';

    const XSLT_EXTENSION = 'PAPAYA_XSLT_EXTENSION';
    const TRANSLITERATION_MODE = 'PAPAYA_TRANSLITERATION_MODE';

    const BROWSER_CRONJOBS = 'PAPAYA_BROWSER_CRONJOBS';
    const BROWSER_CRONJOBS_IP = 'PAPAYA_BROWSER_CRONJOBS_IP';

    const URL_NAMELENGTH = 'PAPAYA_URL_NAMELENGTH';
    const URL_EXTENSION = 'PAPAYA_URL_EXTENSION';
    const URL_LEVEL_SEPARATOR = 'PAPAYA_URL_LEVEL_SEPARATOR';

    const URL_ALIAS_SEPARATOR = 'PAPAYA_URL_ALIAS_SEPARATOR';
    const URL_FIXATION = 'PAPAYA_URL_FIXATION';

    const DATAFILTER_USE = 'PAPAYA_DATAFILTER_USE';
    const IMPORTFILTER_USE = 'PAPAYA_IMPORTFILTER_USE';
    const PUBLICATION_AUDITING = 'PAPAYA_PUBLICATION_AUDITING';
    const PUBLICATION_CHANGE_LEVEL = 'PAPAYA_PUBLICATION_CHANGE_LEVEL';

    const GMAPS_API_KEY = 'PAPAYA_GMAPS_API_KEY';
    const GMAPS_DEFAULT_POSITION = 'PAPAYA_GMAPS_DEFAULT_POSITION';

    const LAYOUT_THEME = 'PAPAYA_LAYOUT_THEME';
    const LAYOUT_THEME_SET = 'PAPAYA_LAYOUT_THEME_SET';
    const LAYOUT_TEMPLATES = 'PAPAYA_LAYOUT_TEMPLATES';

    const PAGEID_DEFAULT = 'PAPAYA_PAGEID_DEFAULT';
    const PAGEID_USERDATA = 'PAPAYA_PAGEID_USERDATA';
    const PAGEID_STATUS_301 = 'PAPAYA_PAGEID_STATUS_301';
    const PAGEID_STATUS_302 = 'PAPAYA_PAGEID_STATUS_302';
    const PAGEID_ERROR_403 = 'PAPAYA_PAGEID_ERROR_403';
    const PAGEID_ERROR_404 = 'PAPAYA_PAGEID_ERROR_404';
    const PAGEID_ERROR_500 = 'PAPAYA_PAGEID_ERROR_500';
    const MEDIADB_SUBDIRECTORIES = 'PAPAYA_MEDIADB_SUBDIRECTORIES';

    const LATIN1_COMPATIBILITY = 'PAPAYA_LATIN1_COMPATIBILITY';
    const INPUT_ENCODING = 'PAPAYA_INPUT_ENCODING';
    const DATABASE_COLLATION = 'PAPAYA_DATABASE_COLLATION';

    const OVERVIEW_ITEMS_UNPUBLISHED = 'PAPAYA_OVERVIEW_ITEMS_UNPUBLISHED';
    const OVERVIEW_ITEMS_PUBLISHED = 'PAPAYA_OVERVIEW_ITEMS_PUBLISHED';
    const OVERVIEW_ITEMS_MESSAGES = 'PAPAYA_OVERVIEW_ITEMS_MESSAGES';
    const OVERVIEW_ITEMS_TASKS = 'PAPAYA_OVERVIEW_ITEMS_TASKS';
    const OVERVIEW_TASK_NOTIFY = 'PAPAYA_OVERVIEW_TASK_NOTIFY';

    const LOGIN_RESTRICTION = 'PAPAYA_LOGIN_RESTRICTION';
    const LOGIN_CHECKTIME = 'PAPAYA_LOGIN_CHECKTIME';
    const LOGIN_NOTIFYCOUNT = 'PAPAYA_LOGIN_NOTIFYCOUNT';
    const LOGIN_BLOCKCOUNT = 'PAPAYA_LOGIN_BLOCKCOUNT';
    const LOGIN_NOTIFYEMAIL = 'PAPAYA_LOGIN_NOTIFYEMAIL';
    const LOGIN_GC_ACTIVE = 'PAPAYA_LOGIN_GC_ACTIVE';
    const LOGIN_GC_TIME = 'PAPAYA_LOGIN_GC_TIME';
    const LOGIN_GC_DIVISOR = 'PAPAYA_LOGIN_GC_DIVISOR';

    const SUPPORT_BUG_EMAIL = 'PAPAYA_SUPPORT_BUG_EMAIL';
    const SUPPORT_PAGE_NEWS = 'PAPAYA_SUPPORT_PAGE_NEWS';
    const SUPPORT_PAGE_MANUAL = 'PAPAYA_SUPPORT_PAGE_MANUAL';

    // media database
    const MAX_UPLOAD_SIZE = 'PAPAYA_MAX_UPLOAD_SIZE';
    const NUM_UPLOAD_FIELDS = 'PAPAYA_NUM_UPLOAD_FIELDS';
    const PATH_MEDIADB_IMPORT = 'PAPAYA_PATH_MEDIADB_IMPORT';
    const MEDIADB_THUMBSIZE = 'PAPAYA_MEDIADB_THUMBSIZE';
    const IMAGE_CONVERTER = 'PAPAYA_IMAGE_CONVERTER';
    const IMAGE_CONVERTER_PATH = 'PAPAYA_IMAGE_CONVERTER_PATH';
    const FILE_CMD_PATH = 'PAPAYA_FILE_CMD_PATH';
    const THUMBS_FILETYPE = 'PAPAYA_THUMBS_FILETYPE';
    const THUMBS_JPEGQUALITY = 'PAPAYA_THUMBS_JPEGQUALITY';
    const THUMBS_BACKGROUND = 'PAPAYA_THUMBS_BACKGROUND';
    const THUMBS_TRANSPARENT = 'PAPAYA_THUMBS_TRANSPARENT';
    const BANDWIDTH_SHAPING = 'PAPAYA_BANDWIDTH_SHAPING';
    const BANDWIDTH_SHAPING_LIMIT = 'PAPAYA_BANDWIDTH_SHAPING_LIMIT';
    const BANDWIDTH_SHAPING_OFFSET = 'PAPAYA_BANDWIDTH_SHAPING_OFFSET';
    const SENDFILE_HEADER = 'PAPAYA_SENDFILE_HEADER';
    const MEDIA_CUTLINE_MODE = 'PAPAYA_MEDIA_CUTLINE_MODE';
    const MEDIA_CUTLINE_LINK_CLASS = 'PAPAYA_MEDIA_CUTLINE_LINK_CLASS';
    const MEDIA_CUTLINE_LINK_TARGET = 'PAPAYA_MEDIA_CUTLINE_LINK_TARGET';
    const MEDIA_ALTTEXT_MODE = 'PAPAYA_MEDIA_ALTTEXT_MODE';
    const MEDIA_CSSCLASS_IMAGE = 'PAPAYA_MEDIA_CSSCLASS_IMAGE';
    const MEDIA_CSSCLASS_SUBTITLE = 'PAPAYA_MEDIA_CSSCLASS_SUBTITLE';
    const MEDIA_CSSCLASS_DYNIMAGE = 'PAPAYA_MEDIA_CSSCLASS_DYNIMAGE';
    const MEDIA_CSSCLASS_LINK = 'PAPAYA_MEDIA_CSSCLASS_LINK';
    const MEDIA_CSSCLASS_MAILTO = 'PAPAYA_MEDIA_CSSCLASS_MAILTO';
    const MEDIA_ELEMENTS_IMAGE = 'PAPAYA_MEDIA_ELEMENTS_IMAGE';
    const MEDIA_STORAGE_SERVICE = 'PAPAYA_MEDIA_STORAGE_SERVICE';
    const MEDIA_STORAGE_S3_BUCKET = 'PAPAYA_MEDIA_STORAGE_S3_BUCKET';
    const MEDIA_STORAGE_S3_KEYID = 'PAPAYA_MEDIA_STORAGE_S3_KEYID';
    const MEDIA_STORAGE_S3_KEY = 'PAPAYA_MEDIA_STORAGE_S3_KEY';
    const THUMBS_MEMORYCHECK_SUHOSIN = 'PAPAYA_THUMBS_MEMORYCHECK_SUHOSIN';

    // caching
    const CACHE_SERVICE = 'PAPAYA_CACHE_SERVICE';
    const CACHE_NOTIFIER = 'PAPAYA_CACHE_NOTIFIER';
    const CACHE_DISABLE_FILE_DELETE = 'PAPAYA_CACHE_DISABLE_FILE_DELETE';
    const CACHE_MEMCACHE_SERVERS = 'PAPAYA_CACHE_MEMCACHE_SERVERS';
    const CACHE_OUTPUT = 'PAPAYA_CACHE_OUTPUT';
    const CACHE_TIME_OUTPUT = 'PAPAYA_CACHE_TIME_OUTPUT';
    const CACHE_PAGES = 'PAPAYA_CACHE_PAGES';
    const CACHE_TIME_PAGES = 'PAPAYA_CACHE_TIME_PAGES';
    const CACHE_BOXES = 'PAPAYA_CACHE_BOXES';
    const CACHE_TIME_BOXES = 'PAPAYA_CACHE_TIME_BOXES';
    const CACHE_TIME_BROWSER = 'PAPAYA_CACHE_TIME_BROWSER';
    const CACHE_TIME_FILES = 'PAPAYA_CACHE_TIME_FILES';
    const CACHE_THEMES = 'PAPAYA_CACHE_THEMES';
    const CACHE_TIME_THEMES = 'PAPAYA_CACHE_TIME_THEMES';
    const COMPRESS_OUTPUT = 'PAPAYA_COMPRESS_OUTPUT';
    const COMPRESS_CACHE_OUTPUT = 'PAPAYA_COMPRESS_CACHE_OUTPUT';
    const COMPRESS_CACHE_THEMES = 'PAPAYA_COMPRESS_CACHE_THEMES';
    const CACHE_DATA = 'PAPAYA_CACHE_DATA';
    const CACHE_DATA_TIME = 'PAPAYA_CACHE_DATA_TIME';
    const CACHE_DATA_SERVICE = 'PAPAYA_CACHE_DATA_SERVICE';
    const CACHE_DATA_MEMCACHE_SERVERS = 'PAPAYA_CACHE_DATA_MEMCACHE_SERVERS';
    const CACHE_IMAGES = 'PAPAYA_CACHE_IMAGES';
    const CACHE_IMAGES_TIME = 'PAPAYA_CACHE_IMAGES_TIME';
    const CACHE_IMAGES_SERVICE = 'PAPAYA_CACHE_IMAGES_SERVICE';
    const CACHE_IMAGES_MEMCACHE_SERVERS = 'PAPAYA_CACHE_IMAGES_MEMCACHE_SERVERS';

    // logging and debugging
    const LOG_PHP_ERRORLEVEL = 'PAPAYA_LOG_PHP_ERRORLEVEL';
    const LOG_DATABASE_CLUSTER_VIOLATIONS = 'PAPAYA_LOG_DATABASE_CLUSTER_VIOLATIONS';
    const LOG_DATABASE_QUERY = 'PAPAYA_LOG_DATABASE_QUERY';
    const LOG_DATABASE_QUERY_SLOW = 'PAPAYA_LOG_DATABASE_QUERY_SLOW';
    const LOG_DATABASE_QUERY_DETAILS = 'PAPAYA_LOG_DATABASE_QUERY_DETAILS';
    const LOG_EVENT_PAGE_MOVED = 'PAPAYA_LOG_EVENT_PAGE_MOVED';
    const LOG_ERROR_THUMBNAIL = 'PAPAYA_LOG_ERROR_THUMBNAIL';
    const LOG_RUNTIME_DATABASE = 'PAPAYA_LOG_RUNTIME_DATABASE';
    const LOG_RUNTIME_REQUEST = 'PAPAYA_LOG_RUNTIME_REQUEST';
    const LOG_RUNTIME_TEMPLATE = 'PAPAYA_LOG_RUNTIME_TEMPLATE';
    const LOG_ENABLE_EXTERNAL = 'PAPAYA_LOG_ENABLE_EXTERNAL';
    const PROTOCOL_DATABASE = 'PAPAYA_PROTOCOL_DATABASE';
    const PROTOCOL_DATABASE_DEBUG = 'PAPAYA_PROTOCOL_DATABASE_DEBUG';
    const PROTOCOL_WILDFIRE = 'PAPAYA_PROTOCOL_WILDFIRE';
    const PROTOCOL_XHTML = 'PAPAYA_PROTOCOL_XHTML';
    const PROTOCOL_XHTML_OUTPUT_CLOSERS = 'PAPAYA_PROTOCOL_XHTML_OUTPUT_CLOSERS';
    const QUERYLOG = 'PAPAYA_QUERYLOG';
    const QUERYLOG_SLOW = 'PAPAYA_QUERYLOG_SLOW';
    const QUERYLOG_DETAILS = 'PAPAYA_QUERYLOG_DETAILS';

    // session handling
    const SESSION_NAME = 'PAPAYA_SESSION_NAME';
    const SESSION_START = 'PAPAYA_SESSION_START';
    const SESSION_ACTIVATION = 'PAPAYA_SESSION_ACTIVATION';
    const SESSION_CONSENT_COOKIE_REQUIRE = 'PAPAYA_SESSION_CONSENT_COOKIE_REQUIRE';
    const SESSION_CONSENT_COOKIE_NAME = 'PAPAYA_SESSION_CONSENT_COOKIE_NAME';
    const SESSION_CONSENT_COOKIE_VALUES = 'PAPAYA_SESSION_CONSENT_COOKIE_VALUES';
    const SESSION_DOMAIN = 'PAPAYA_SESSION_DOMAIN';
    const SESSION_PATH = 'PAPAYA_SESSION_PATH';
    const SESSION_SECURE = 'PAPAYA_SESSION_SECURE';
    const SESSION_HTTP_ONLY = 'PAPAYA_SESSION_HTTP_ONLY';
    const SESSION_ID_FALLBACK = 'PAPAYA_SESSION_ID_FALLBACK';
    const SESSION_CACHE = 'PAPAYA_SESSION_CACHE';
    const DB_DISCONNECT_SESSIONSTART = 'PAPAYA_DB_DISCONNECT_SESSIONSTART';

    //internal path options
    const PATH_CACHE = 'PAPAYA_PATH_CACHE';
    const PATHWEB_ADMIN = 'PAPAYA_PATHWEB_ADMIN';
    const PATH_MEDIAFILES = 'PAPAYA_PATH_MEDIAFILES';
    const PATH_THUMBFILES = 'PAPAYA_PATH_THUMBFILES';
    const MEDIA_PUBLIC_DIRECTORY = 'PAPAYA_MEDIA_PUBLIC_DIRECTORY';
    const MEDIA_PUBLIC_URL = 'PAPAYA_MEDIA_PUBLIC_URL';
    const MEDIA_STORAGE_DIRECTORY = 'PAPAYA_MEDIA_STORAGE_DIRECTORY';
    const MEDIA_STORAGE_SUBDIRECTORY = 'PAPAYA_MEDIA_STORAGE_SUBDIRECTORY';

    // internal flags
    const ADMIN_PAGE = 'PAPAYA_ADMIN_PAGE';

    // version information
    const WEBSITE_REVISION = 'PAPAYA_WEBSITE_REVISION';
    const VERSION_STRING = 'PAPAYA_VERSION_STRING';
    const DEPENDENCIES = 'PAPAYA_DEPENDENCIES';

    // feature toggles
    const FEATURE_BOXGROUPS_LINKABLE = 'PAPAYA_FEATURE_BOXGROUPS_LINKABLE';

    /**
     * This is a list of all available options and their default values.
     *
     * @var array(string=>mixed)
     */
    private $_cmsOptions = [
      // base options (defined in configuration file)
      self::INCLUDE_PATH => NULL,
      self::DB_URI => NULL,
      self::DB_URI_WRITE => NULL,
      self::DB_TBL_OPTIONS => 'papaya_options',
      self::DB_TABLEPREFIX => 'papaya',
      self::DB_CONNECT_PERSISTENT => FALSE,
      // maintance (defined in configuration file)
      self::MAINTENANCE_MODE => FALSE,
      self::ERRORDOCUMENT_MAINTENANCE => '',
      self::ERRORDOCUMENT_503 => '',
      // security (defined in configuration file)
      self::PASSWORD_REHASH => FALSE,
      self::PASSWORD_ALGORITHM => 0,
      self::PASSWORD_METHOD => 'md5',
      self::PASSWORD_PREFIX => '',
      self::PASSWORD_SUFFFIX => '',
      self::DISABLE_XHEADERS => FALSE,
      self::HEADER_HTTPS_TOKEN => '',
      // profiler (defined in configuration file)
      self::PROFILER_ACTIVE => FALSE,
      self::PROFILER_DIVISOR => 1,
      self::PROFILER_STORAGE => 'file',
      self::PROFILER_STORAGE_DIRECTORY => '',
      self::PROFILER_STORAGE_DATABASE => '',
      self::PROFILER_STORAGE_DATABASE_TABLE => 'details',
      self::PROFILER_SERVER_ID => 'dv1',
      // debug (defined in configuration file)
      self::DBG_DEVMODE => FALSE,
      self::DEBUG_LANGUAGE_PHRASES => FALSE,

      // name the project
      self::PROJECT_TITLE => PAPAYA_CONFIGURATION_HOSTNAME,

      // log & debugging old
      self::DBG_DATABASE_ERROR => TRUE,
      self::DBG_XML_OUTPUT => TRUE,
      self::DBG_XML_ERROR => TRUE,
      self::DBG_XML_USERINPUT => TRUE,
      self::DBG_PHRASES => FALSE,
      self::DBG_SHOW_DEBUGS => FALSE, // only used by cronjob output

      // paths and path urls
      self::PATH_DATA => '',
      self::PATH_TEMPLATES => '',
      self::PATH_WEB => '/',
      self::PATH_THEMES => '/papaya-themes/',
      self::CDN_THEMES => '',
      self::CDN_THEMES_SECURE => '',
      self::PATH_PUBLICFILES => '',
      self::PATH_ADMIN => '/papaya',

      // Community / Surfers
      self::COMMUNITY_REDIRECT_PAGE => 0,
      self::COMMUNITY_AUTOLOGIN => TRUE,
      self::COMMUNITY_RELOGIN => FALSE,
      self::COMMUNITY_RELOGIN_SALT => '',
      self::COMMUNITY_RELOGIN_EXP_DAYS => 7,
      self::COMMUNITY_API_LOGIN => 0,
      self::COMMUNITY_HANDLE_MAX_LENGTH => 20,

      self::CONTENT_LANGUAGE => 1,
      self::CONTENT_LANGUAGE_COOKIE => FALSE,

      self::DEFAULT_PROTOCOL => Utility\Server\Protocol::BOTH,
      self::DEFAULT_HOST => '',
      self::DEFAULT_HOST_ACTION => 0,
      self::REDIRECT_PROTECTION => FALSE,

      self::FLASH_DEFAULT_VERSION => '9.0.28',
      self::FLASH_MIN_VERSION => '',

      self::PAGE_STATISTIC => FALSE,
      self::STATISTIC_PRESERVE_IP => FALSE,
      self::PUBLISH_SOCIALMEDIA => FALSE,

      // spam filter
      self::SPAM_LOG => FALSE,
      self::SPAM_BLOCK => FALSE,
      self::SPAM_SCOREMIN_PERCENT => 10,
      self::SPAM_SCOREMAX_PERCENT => 90,
      self::SPAM_STOPWORD_MAX => 10,

      self::DATABASE_CLUSTER_SWITCH => 0,
      self::SEARCH_BOOLEAN => 0,
      self::VERSIONS_MAXCOUNT => 99999,

      self::UI_LANGUAGE => 'en-US',
      self::UI_SECURE => FALSE,
      self::UI_SECURE_WARNING => TRUE,
      self::UI_THEME => 'green',
      self::UI_SEARCH_ANCESTOR_LIMIT => 6,
      self::UI_SEARCH_CHARACTER_LIMIT => 100,

      self::PROTECT_FORM_CHANGES => TRUE,

      self::USE_RICHTEXT => TRUE,
      self::RICHTEXT_BROWSER_SPELLCHECK => FALSE,
      self::RICHTEXT_TEMPLATES_FULL => 'p,div,h2,h3,h4,h5,h6,blockquote',
      self::RICHTEXT_TEMPLATES_SIMPLE => 'p,div,h2,h3',
      self::RICHTEXT_CONTENT_CSS => 'tinymce.css',
      self::RICHTEXT_LINK_TARGET => '_self',

      self::XSLT_EXTENSION => '', // empty = auto
      self::TRANSLITERATION_MODE => 0,

      self::BROWSER_CRONJOBS => FALSE,
      self::BROWSER_CRONJOBS_IP => '127.0.0.1',

      self::URL_NAMELENGTH => 50,
      self::URL_EXTENSION => 'html',
      self::URL_LEVEL_SEPARATOR => '*', // * gets not encoded in get-forms

      self::URL_ALIAS_SEPARATOR => ',',
      self::URL_FIXATION => FALSE,

      self::DATAFILTER_USE => FALSE,
      self::IMPORTFILTER_USE => FALSE,
      self::PUBLICATION_AUDITING => FALSE,
      self::PUBLICATION_CHANGE_LEVEL => FALSE,

      self::GMAPS_API_KEY => '',
      self::GMAPS_DEFAULT_POSITION => '50.94794501585774, 6.944365873932838',

      self::LAYOUT_THEME => 'dynamic',
      self::LAYOUT_THEME_SET => '',
      self::LAYOUT_TEMPLATES => 'responsive',

      self::PAGEID_DEFAULT => 1,
      self::PAGEID_USERDATA => 0,
      self::PAGEID_STATUS_301 => 0,
      self::PAGEID_STATUS_302 => 0,
      self::PAGEID_ERROR_403 => 0,
      self::PAGEID_ERROR_404 => 0,
      self::PAGEID_ERROR_500 => 0,
      self::MEDIADB_SUBDIRECTORIES => 1,

      self::LATIN1_COMPATIBILITY => TRUE,
      self::INPUT_ENCODING => 'utf-8',
      self::DATABASE_COLLATION => 'utf8_general_ci',

      self::OVERVIEW_ITEMS_UNPUBLISHED => 15,
      self::OVERVIEW_ITEMS_PUBLISHED => 10,
      self::OVERVIEW_ITEMS_MESSAGES => 10,
      self::OVERVIEW_ITEMS_TASKS => 10,
      self::OVERVIEW_TASK_NOTIFY => FALSE,

      self::LOGIN_RESTRICTION => 0,
      self::LOGIN_CHECKTIME => 600,
      self::LOGIN_NOTIFYCOUNT => 10,
      self::LOGIN_BLOCKCOUNT => 20,
      self::LOGIN_NOTIFYEMAIL => '',
      self::LOGIN_GC_ACTIVE => FALSE,
      self::LOGIN_GC_TIME => 86400,
      self::LOGIN_GC_DIVISOR => 50,

      self::SUPPORT_BUG_EMAIL => 'info@papaya-cms.com',
      self::SUPPORT_PAGE_NEWS => 'http://www.papaya-cms.com/news/',
      self::SUPPORT_PAGE_MANUAL => 'http://www.papaya-cms.com/manual/',

      // media database
      self::MAX_UPLOAD_SIZE => 7,
      self::NUM_UPLOAD_FIELDS => 1,
      self::PATH_MEDIADB_IMPORT => '',
      self::MEDIADB_THUMBSIZE => 80,
      self::IMAGE_CONVERTER => 'gd',
      self::IMAGE_CONVERTER_PATH => '',
      self::FILE_CMD_PATH => '',
      self::THUMBS_FILETYPE => 3,
      self::THUMBS_JPEGQUALITY => 90,
      self::THUMBS_BACKGROUND => '#FFFFFF',
      self::THUMBS_TRANSPARENT => TRUE,
      self::BANDWIDTH_SHAPING => FALSE,
      self::BANDWIDTH_SHAPING_LIMIT => 10240,
      self::BANDWIDTH_SHAPING_OFFSET => 1048576,
      self::SENDFILE_HEADER => FALSE,
      self::MEDIA_CUTLINE_MODE => 0,
      self::MEDIA_CUTLINE_LINK_CLASS => 'source',
      self::MEDIA_CUTLINE_LINK_TARGET => '_self',
      self::MEDIA_ALTTEXT_MODE => 0,
      self::MEDIA_CSSCLASS_IMAGE => 'papayaImage',
      self::MEDIA_CSSCLASS_SUBTITLE => 'papayaImageSubtitle',
      self::MEDIA_CSSCLASS_DYNIMAGE => 'papayaDynamicImage',
      self::MEDIA_CSSCLASS_LINK => 'papayaLink',
      self::MEDIA_CSSCLASS_MAILTO => 'papayaMailtoLink',
      self::MEDIA_ELEMENTS_IMAGE => 0,
      self::MEDIA_STORAGE_SERVICE => 'file',
      self::MEDIA_STORAGE_S3_BUCKET => '',
      self::MEDIA_STORAGE_S3_KEYID => '',
      self::MEDIA_STORAGE_S3_KEY => '',
      self::THUMBS_MEMORYCHECK_SUHOSIN => TRUE,

      // caching
      self::CACHE_SERVICE => 'file',
      self::CACHE_NOTIFIER => '',
      self::CACHE_DISABLE_FILE_DELETE => FALSE,
      self::CACHE_MEMCACHE_SERVERS => '',
      self::CACHE_OUTPUT => FALSE,
      self::CACHE_TIME_OUTPUT => 0,
      self::CACHE_PAGES => FALSE,
      self::CACHE_TIME_PAGES => 0,
      self::CACHE_BOXES => FALSE,
      self::CACHE_TIME_BOXES => 0,
      self::CACHE_TIME_BROWSER => 0,
      self::CACHE_TIME_FILES => 2592000,
      self::CACHE_THEMES => TRUE,
      self::CACHE_TIME_THEMES => 2592000,
      self::COMPRESS_OUTPUT => FALSE,
      self::COMPRESS_CACHE_OUTPUT => FALSE,
      self::COMPRESS_CACHE_THEMES => TRUE,
      self::CACHE_DATA => FALSE,
      self::CACHE_DATA_TIME => 60,
      self::CACHE_DATA_SERVICE => 'file',
      self::CACHE_DATA_MEMCACHE_SERVERS => '',
      self::CACHE_IMAGES => FALSE,
      self::CACHE_IMAGES_TIME => 60,
      self::CACHE_IMAGES_SERVICE => 'file',
      self::CACHE_IMAGES_MEMCACHE_SERVERS => '',

      // logging and debugging
      self::LOG_PHP_ERRORLEVEL => 2047,
      self::LOG_DATABASE_CLUSTER_VIOLATIONS => FALSE,
      self::LOG_DATABASE_QUERY => 0,
      self::LOG_DATABASE_QUERY_SLOW => 3000,
      self::LOG_DATABASE_QUERY_DETAILS => FALSE,
      self::LOG_EVENT_PAGE_MOVED => TRUE,
      self::LOG_ERROR_THUMBNAIL => FALSE,
      self::LOG_RUNTIME_DATABASE => FALSE,
      self::LOG_RUNTIME_REQUEST => FALSE,
      self::LOG_RUNTIME_TEMPLATE => FALSE,
      self::PROTOCOL_DATABASE => FALSE,
      self::PROTOCOL_DATABASE_DEBUG => FALSE,
      self::PROTOCOL_WILDFIRE => FALSE,
      self::PROTOCOL_XHTML => TRUE,
      self::QUERYLOG => 0,
      self::QUERYLOG_SLOW => 3000,
      self::QUERYLOG_DETAILS => FALSE,

      // session handling
      self::SESSION_START => TRUE,
      self::SESSION_ACTIVATION => Session::ACTIVATION_DYNAMIC,
      self::SESSION_DOMAIN => '',
      self::SESSION_PATH => '',
      self::SESSION_SECURE => FALSE,
      self::SESSION_HTTP_ONLY => TRUE,
      self::SESSION_ID_FALLBACK => 'rewrite',
      self::SESSION_CACHE => 'private',
      self::SESSION_CONSENT_COOKIE_REQUIRE => false,
      self::SESSION_CONSENT_COOKIE_NAME => 'cookieconsent_status',
      self::SESSION_CONSENT_COOKIE_VALUES => 'allow,dismiss',
      self::DB_DISCONNECT_SESSIONSTART => FALSE,

      //internal path options
      self::PATH_CACHE => NULL,
      self::PATHWEB_ADMIN => NULL,
      self::PATH_MEDIAFILES => NULL,
      self::PATH_THUMBFILES => NULL,
      self::MEDIA_PUBLIC_DIRECTORY => NULL,
      self::MEDIA_PUBLIC_URL => NULL,
      self::MEDIA_STORAGE_DIRECTORY => NULL,
      self::MEDIA_STORAGE_SUBDIRECTORY => NULL,

      // feature toggles
      self::FEATURE_BOXGROUPS_LINKABLE => TRUE
    ];

    /**
     * Create configuration object and initialize default option values
     */
    public function __construct() {
      parent::__construct($this->_cmsOptions);
    }

    /**
     * The option list is only readable, it can not be changed.
     *
     * @return array
     */
    public function getOptionsList() {
      return $this->_cmsOptions;
    }

    /**
     * Load and define (fixate) all options
     *
     * @return bool
     */
    public function loadAndDefine() {
      if ($this->load()) {
        $this->defineConstants();
        return TRUE;
      }
      return FALSE;
    }

    /**
     * Setup paths, define database table constants and define current options as constants to fixate
     * them.
     */
    public function defineConstants() {
      $this->setupPaths();
      $this->defineDatabaseTables();
      parent::defineConstants();
    }

    /**
     * The method defines several path constants depending on other options.
     */
    public function setupPaths() {
      $this->set(self::PATH_CACHE, $this->get(self::PATH_DATA).'cache/');
      if ('s3' === $this->get(self::MEDIA_STORAGE_SERVICE)) {
        $basePath = 's3://'.
          $this->get(self::MEDIA_STORAGE_S3_KEYID).':@'.
          $this->get(self::MEDIA_STORAGE_S3_BUCKET).'/';
        S3Streamwrapper::setSecret(
          $this->get(self::MEDIA_STORAGE_S3_KEYID),
          $this->get(self::MEDIA_STORAGE_S3_KEY)
        );
        S3Streamwrapper::register('s3');
        $this->set(self::MEDIA_STORAGE_SUBDIRECTORY, 'media/');
      } else {
        $basePath = $this->get(self::PATH_DATA);
        $this->set(self::MEDIA_STORAGE_DIRECTORY, $this->get(self::PATH_DATA).'media/');
        if (empty($_SERVER['DOCUMENT_ROOT']) || ('' === $this->get(self::PATH_PUBLICFILES))) {
          $this->set(self::MEDIA_PUBLIC_DIRECTORY, '');
          $this->set(self::MEDIA_PUBLIC_URL, '');
        } else {
          $this->set(
            self::MEDIA_PUBLIC_DIRECTORY,
            Utility\File\Path::cleanup(
              $_SERVER['DOCUMENT_ROOT'].$this->get(self::PATH_PUBLICFILES)
            )
          );
          $url = new CurrentURL();
          $url->setPath($this->get(self::PATH_PUBLICFILES));
          $this->set(
            self::MEDIA_PUBLIC_URL, $url->getPathURL()
          );
        }
      }
      $this->set(self::PATH_MEDIAFILES, $basePath.'media/files/');
      $this->set(self::PATH_THUMBFILES, $basePath.'media/thumbs/');

      if ('' === (string)$this->get(self::PATH_TEMPLATES, '')) {
        $templatePaths = [
          Utility\File\Path::getDocumentRoot().'/../templates/',
          $this->get(self::PATH_DATA).'templates/'
        ];
        foreach ($templatePaths as $templatePath) {
          $templatePath = Utility\File\Path::cleanup($templatePath);
          $this->set(self::PATH_TEMPLATES, $templatePath);
          if (\file_exists($templatePath) && \is_dir($templatePath)) {
            break;
          }
        }
      }
      $this->set(
        self::PATHWEB_ADMIN,
        Utility\File\Path::cleanup(
          $this->get(self::PATH_WEB).$this->get(self::PATH_ADMIN)
        )
      );
    }

    /**
     * Define the database table name constants, these constants should be replaced by
     * calls to {@see \Papaya\Database\Acccess::getTableName()} using the class constants in
     * {@see \Papaya\Content\Tables}.
     *
     * But for now the use of these constants is scattered all over the source in the
     * base system and modules, so we need to define them for compatibility.
     *
     * @todo Remove usage of these constants
     */
    public function defineDatabaseTables() {
      $prefix = $this->get(self::DB_TABLEPREFIX, 'papaya');
      foreach (Content\Tables::getTables() as $tableConstant => $tableName) {
        if (!\defined($tableConstant)) {
          \define($tableConstant, $prefix.'_'.$tableName);
        }
      }
    }
  }
}
