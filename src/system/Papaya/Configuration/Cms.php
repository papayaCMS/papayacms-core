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

namespace Papaya\Configuration;
/**
 * Define a default project name, using the http host name
 *
 * @var string
 */
define(
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
class Cms extends GlobalValues {

  /**
   * This is a list of all available options and their default values.
   *
   * @var array(string=>mixed)
   */
  private $_cmsOptions = array(
    // base options (defined in configuration file)
    'PAPAYA_INCLUDE_PATH' => NULL,
    'PAPAYA_DB_URI' => NULL,
    'PAPAYA_DB_URI_WRITE' => NULL,
    'PAPAYA_DB_TBL_OPTIONS' => 'papaya_options',
    'PAPAYA_DB_TABLEPREFIX' => 'papaya',
    'PAPAYA_DB_CONNECT_PERSISTENT' => FALSE,
    // maintance (defined in configuration file)
    'PAPAYA_MAINTENANCE_MODE' => FALSE,
    'PAPAYA_ERRORDOCUMENT_MAINTENANCE' => '',
    'PAPAYA_ERRORDOCUMENT_503' => '',
    // security (defined in configuration file)
    'PAPAYA_PASSWORD_REHASH' => FALSE,
    'PAPAYA_PASSWORD_ALGORITHM' => 0,
    'PAPAYA_PASSWORD_METHOD' => 'md5',
    'PAPAYA_PASSWORD_PREFIX' => '',
    'PAPAYA_PASSWORD_SUFFFIX' => '',
    'PAPAYA_DISABLE_XHEADERS' => FALSE,
    'PAPAYA_HEADER_HTTPS_TOKEN' => '',
    // profiler (defined in configuration file)
    'PAPAYA_PROFILER_ACTIVE' => FALSE,
    'PAPAYA_PROFILER_DIVISOR' => 1,
    'PAPAYA_PROFILER_STORAGE' => 'file',
    'PAPAYA_PROFILER_STORAGE_DIRECTORY' => '',
    'PAPAYA_PROFILER_STORAGE_DATABASE' => '',
    'PAPAYA_PROFILER_STORAGE_DATABASE_TABLE' => 'details',
    'PAPAYA_PROFILER_SERVER_ID' => 'dv1',
    // debug (defined in configuration file)
    'PAPAYA_DBG_DEVMODE' => FALSE,
    'PAPAYA_DEBUG_LANGUAGE_PHRASES' => FALSE,

    // name the project
    'PAPAYA_PROJECT_TITLE' => PAPAYA_CONFIGURATION_HOSTNAME,

    // log & debugging old
    'PAPAYA_DBG_DATABASE_ERROR' => TRUE,
    'PAPAYA_DBG_XML_OUTPUT' => TRUE,
    'PAPAYA_DBG_XML_ERROR' => TRUE,
    'PAPAYA_DBG_XML_USERINPUT' => TRUE,
    'PAPAYA_DBG_PHRASES' => FALSE,
    'PAPAYA_DBG_SHOW_DEBUGS' => FALSE, // only used by cronjob output

    // paths and path urls
    'PAPAYA_PATH_DATA' => '',
    'PAPAYA_PATH_TEMPLATES' => '',
    'PAPAYA_PATH_WEB' => '/',
    'PAPAYA_PATH_THEMES' => '/papaya-themes/',
    'PAPAYA_CDN_THEMES' => '',
    'PAPAYA_CDN_THEMES_SECURE' => '',
    'PAPAYA_PATH_PUBLICFILES' => '',
    'PAPAYA_PATH_ADMIN' => '/papaya',

    // Community / Surfers
    'PAPAYA_COMMUNITY_REDIRECT_PAGE' => 0,
    'PAPAYA_COMMUNITY_AUTOLOGIN' => TRUE,
    'PAPAYA_COMMUNITY_RELOGIN' => FALSE,
    'PAPAYA_COMMUNITY_RELOGIN_SALT' => '',
    'PAPAYA_COMMUNITY_RELOGIN_EXP_DAYS' => 7,
    'PAPAYA_COMMUNITY_API_LOGIN' => 0,
    'PAPAYA_COMMUNITY_HANDLE_MAX_LENGTH' => 20,

    'PAPAYA_CONTENT_LANGUAGE' => 1,
    'PAPAYA_CONTENT_LANGUAGE_COOKIE' => FALSE,

    'PAPAYA_DEFAULT_PROTOCOL' => \PapayaUtilServerProtocol::BOTH,
    'PAPAYA_DEFAULT_HOST' => '',
    'PAPAYA_DEFAULT_HOST_ACTION' => 0,
    'PAPAYA_REDIRECT_PROTECTION' => FALSE,

    'PAPAYA_FLASH_DEFAULT_VERSION' => '9.0.28',
    'PAPAYA_FLASH_MIN_VERSION' => '',

    'PAPAYA_PAGE_STATISTIC' => FALSE,
    'PAPAYA_STATISTIC_PRESERVE_IP' => FALSE,
    'PAPAYA_PUBLISH_SOCIALMEDIA' => FALSE,

    // spam filter
    'PAPAYA_SPAM_LOG' => FALSE,
    'PAPAYA_SPAM_BLOCK' => FALSE,
    'PAPAYA_SPAM_SCOREMIN_PERCENT' => 10,
    'PAPAYA_SPAM_SCOREMAX_PERCENT' => 90,
    'PAPAYA_SPAM_STOPWORD_MAX' => 10,

    'PAPAYA_DATABASE_CLUSTER_SWITCH' => 0,
    'PAPAYA_SEARCH_BOOLEAN' => 0,
    'PAPAYA_VERSIONS_MAXCOUNT' => 99999,

    'PAPAYA_UI_LANGUAGE' => 'en-US',
    'PAPAYA_UI_SECURE' => FALSE,
    'PAPAYA_UI_SECURE_WARNING' => TRUE,
    'PAPAYA_UI_THEME' => 'green',
    'PAPAYA_UI_SKIN' => 'default',
    'PAPAYA_UI_SEARCH_ANCESTOR_LIMIT' => 6,
    'PAPAYA_UI_SEARCH_CHARACTER_LIMIT' => 100,

    'PAPAYA_PROTECT_FORM_CHANGES' => TRUE,

    'PAPAYA_USE_RICHTEXT' => TRUE,
    'PAPAYA_RICHTEXT_BROWSER_SPELLCHECK' => FALSE,
    'PAPAYA_RICHTEXT_TEMPLATES_FULL' => 'p,div,h2,h3,h4,h5,h6,blockquote',
    'PAPAYA_RICHTEXT_TEMPLATES_SIMPLE' => 'p,div,h2,h3',
    'PAPAYA_RICHTEXT_CONTENT_CSS' => 'tinymce.css',
    'PAPAYA_RICHTEXT_LINK_TARGET' => '_self',

    'PAPAYA_XSLT_EXTENSION' => '', // empty = auto
    'PAPAYA_TRANSLITERATION_MODE' => 0,

    'PAPAYA_BROWSER_CRONJOBS' => FALSE,
    'PAPAYA_BROWSER_CRONJOBS_IP' => '127.0.0.1',

    'PAPAYA_URL_NAMELENGTH' => 50,
    'PAPAYA_URL_EXTENSION' => 'html',
    'PAPAYA_URL_LEVEL_SEPARATOR' => '*', // * gets not encoded in get-forms

    'PAPAYA_URL_ALIAS_SEPARATOR' => ',',
    'PAPAYA_URL_FIXATION' => FALSE,

    'PAPAYA_DATAFILTER_USE' => FALSE,
    'PAPAYA_IMPORTFILTER_USE' => FALSE,
    'PAPAYA_PUBLICATION_AUDITING' => FALSE,
    'PAPAYA_PUBLICATION_CHANGE_LEVEL' => FALSE,

    'PAPAYA_GMAPS_API_KEY' => '',
    'PAPAYA_GMAPS_DEFAULT_POSITION' => '50.94794501585774, 6.944365873932838',

    'PAPAYA_LAYOUT_THEME' => 'dynamic',
    'PAPAYA_LAYOUT_THEME_SET' => '',
    'PAPAYA_LAYOUT_TEMPLATES' => 'responsive',

    'PAPAYA_PAGEID_DEFAULT' => 1,
    'PAPAYA_PAGEID_USERDATA' => 0,
    'PAPAYA_PAGEID_STATUS_301' => 0,
    'PAPAYA_PAGEID_STATUS_302' => 0,
    'PAPAYA_PAGEID_ERROR_403' => 0,
    'PAPAYA_PAGEID_ERROR_404' => 0,
    'PAPAYA_PAGEID_ERROR_500' => 0,
    'PAPAYA_MEDIADB_SUBDIRECTORIES' => 1,

    'PAPAYA_LATIN1_COMPATIBILITY' => TRUE,
    'PAPAYA_INPUT_ENCODING' => 'utf-8',
    'PAPAYA_DATABASE_COLLATION' => 'utf8_general_ci',

    'PAPAYA_OVERVIEW_ITEMS_UNPUBLISHED' => 15,
    'PAPAYA_OVERVIEW_ITEMS_PUBLISHED' => 10,
    'PAPAYA_OVERVIEW_ITEMS_MESSAGES' => 10,
    'PAPAYA_OVERVIEW_ITEMS_TASKS' => 10,
    'PAPAYA_OVERVIEW_TASK_NOTIFY' => FALSE,

    'PAPAYA_LOGIN_RESTRICTION' => 0,
    'PAPAYA_LOGIN_CHECKTIME' => 600,
    'PAPAYA_LOGIN_NOTIFYCOUNT' => 10,
    'PAPAYA_LOGIN_BLOCKCOUNT' => 20,
    'PAPAYA_LOGIN_NOTIFYEMAIL' => '',
    'PAPAYA_LOGIN_GC_ACTIVE' => FALSE,
    'PAPAYA_LOGIN_GC_TIME' => 86400,
    'PAPAYA_LOGIN_GC_DIVISOR' => 50,

    'PAPAYA_SUPPORT_BUG_EMAIL' => 'info@papaya-cms.com',
    'PAPAYA_SUPPORT_PAGE_NEWS' => 'http://www.papaya-cms.com/news/',
    'PAPAYA_SUPPORT_PAGE_MANUAL' => 'http://www.papaya-cms.com/manual/',

    // media database
    'PAPAYA_MAX_UPLOAD_SIZE' => 7,
    'PAPAYA_NUM_UPLOAD_FIELDS' => 1,
    'PAPAYA_PATH_MEDIADB_IMPORT' => '',
    'PAPAYA_MEDIADB_THUMBSIZE' => 80,
    'PAPAYA_IMAGE_CONVERTER' => 'gd',
    'PAPAYA_IMAGE_CONVERTER_PATH' => '',
    'PAPAYA_FILE_CMD_PATH' => '',
    'PAPAYA_THUMBS_FILETYPE' => 3,
    'PAPAYA_THUMBS_JPEGQUALITY' => 90,
    'PAPAYA_THUMBS_BACKGROUND' => '#FFFFFF',
    'PAPAYA_THUMBS_TRANSPARENT' => TRUE,
    'PAPAYA_BANDWIDTH_SHAPING' => FALSE,
    'PAPAYA_BANDWIDTH_SHAPING_LIMIT' => 10240,
    'PAPAYA_BANDWIDTH_SHAPING_OFFSET' => 1048576,
    'PAPAYA_SENDFILE_HEADER' => FALSE,
    'PAPAYA_MEDIA_CUTLINE_MODE' => 0,
    'PAPAYA_MEDIA_CUTLINE_LINK_CLASS' => 'source',
    'PAPAYA_MEDIA_CUTLINE_LINK_TARGET' => '_self',
    'PAPAYA_MEDIA_ALTTEXT_MODE' => 0,
    'PAPAYA_MEDIA_CSSCLASS_IMAGE' => 'papayaImage',
    'PAPAYA_MEDIA_CSSCLASS_SUBTITLE' => 'papayaImageSubtitle',
    'PAPAYA_MEDIA_CSSCLASS_DYNIMAGE' => 'papayaDynamicImage',
    'PAPAYA_MEDIA_CSSCLASS_LINK' => 'papayaLink',
    'PAPAYA_MEDIA_CSSCLASS_MAILTO' => 'papayaMailtoLink',
    'PAPAYA_MEDIA_ELEMENTS_IMAGE' => 0,
    'PAPAYA_MEDIA_STORAGE_SERVICE' => 'file',
    'PAPAYA_MEDIA_STORAGE_S3_BUCKET' => '',
    'PAPAYA_MEDIA_STORAGE_S3_KEYID' => '',
    'PAPAYA_MEDIA_STORAGE_S3_KEY' => '',
    'PAPAYA_THUMBS_MEMORYCHECK_SUHOSIN' => TRUE,

    // caching
    'PAPAYA_CACHE_SERVICE' => 'file',
    'PAPAYA_CACHE_NOTIFIER' => '',
    'PAPAYA_CACHE_DISABLE_FILE_DELETE' => FALSE,
    'PAPAYA_CACHE_MEMCACHE_SERVERS' => '',
    'PAPAYA_CACHE_OUTPUT' => FALSE,
    'PAPAYA_CACHE_TIME_OUTPUT' => 0,
    'PAPAYA_CACHE_PAGES' => FALSE,
    'PAPAYA_CACHE_TIME_PAGES' => 0,
    'PAPAYA_CACHE_BOXES' => FALSE,
    'PAPAYA_CACHE_TIME_BOXES' => 0,
    'PAPAYA_CACHE_TIME_BROWSER' => 0,
    'PAPAYA_CACHE_TIME_FILES' => 2592000,
    'PAPAYA_CACHE_THEMES' => TRUE,
    'PAPAYA_CACHE_TIME_THEMES' => 2592000,
    'PAPAYA_COMPRESS_OUTPUT' => FALSE,
    'PAPAYA_COMPRESS_CACHE_OUTPUT' => FALSE,
    'PAPAYA_COMPRESS_CACHE_THEMES' => TRUE,
    'PAPAYA_CACHE_DATA' => FALSE,
    'PAPAYA_CACHE_DATA_TIME' => 60,
    'PAPAYA_CACHE_DATA_SERVICE' => 'file',
    'PAPAYA_CACHE_DATA_MEMCACHE_SERVERS' => '',

    // logging and debugging
    'PAPAYA_LOG_PHP_ERRORLEVEL' => 2047,
    'PAPAYA_LOG_DATABASE_CLUSTER_VIOLATIONS' => FALSE,
    'PAPAYA_LOG_DATABASE_QUERY' => 0,
    'PAPAYA_LOG_DATABASE_QUERY_SLOW' => 3000,
    'PAPAYA_LOG_DATABASE_QUERY_DETAILS' => FALSE,
    'PAPAYA_LOG_EVENT_PAGE_MOVED' => TRUE,
    'PAPAYA_LOG_ERROR_THUMBNAIL' => FALSE,
    'PAPAYA_LOG_RUNTIME_DATABASE' => FALSE,
    'PAPAYA_LOG_RUNTIME_REQUEST' => FALSE,
    'PAPAYA_LOG_RUNTIME_TEMPLATE' => FALSE,
    'PAPAYA_PROTOCOL_DATABASE' => FALSE,
    'PAPAYA_PROTOCOL_DATABASE_DEBUG' => FALSE,
    'PAPAYA_PROTOCOL_WILDFIRE' => FALSE,
    'PAPAYA_PROTOCOL_XHTML' => TRUE,
    'PAPAYA_QUERYLOG' => 0,
    'PAPAYA_QUERYLOG_SLOW' => 3000,
    'PAPAYA_QUERYLOG_DETAILS' => FALSE,

    // session handling
    'PAPAYA_SESSION_START' => TRUE,
    'PAPAYA_SESSION_ACTIVATION' => \Papaya\Session::ACTIVATION_DYNAMIC,
    'PAPAYA_SESSION_DOMAIN' => '',
    'PAPAYA_SESSION_PATH' => '',
    'PAPAYA_SESSION_SECURE' => FALSE,
    'PAPAYA_SESSION_HTTP_ONLY' => TRUE,
    'PAPAYA_SESSION_ID_FALLBACK' => 'rewrite',
    'PAPAYA_SESSION_CACHE' => 'private',
    'PAPAYA_DB_DISCONNECT_SESSIONSTART' => FALSE,

    //internal path options
    'PAPAYA_PATH_CACHE' => NULL,
    'PAPAYA_PATHWEB_ADMIN' => NULL,
    'PAPAYA_PATH_MEDIAFILES' => NULL,
    'PAPAYA_PATH_THUMBFILES' => NULL,
    'PAPAYA_MEDIA_PUBLIC_DIRECTORY' => NULL,
    'PAPAYA_MEDIA_PUBLIC_URL' => NULL,
    'PAPAYA_MEDIA_STORAGE_DIRECTORY' => NULL,
    'PAPAYA_MEDIA_STORAGE_SUBDIRECTORY' => NULL,

    // feature toggles
    'PAPAYA_FEATURE_BOXGROUPS_LINKABLE' => FALSE
  );

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
   * @return boolean
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
    $this->set('PAPAYA_PATH_CACHE', $this->get('PAPAYA_PATH_DATA').'cache/');
    switch ($this->get('PAPAYA_MEDIA_STORAGE_SERVICE')) {
      case 's3' :
        $basePath = 's3://'.
          $this->get('PAPAYA_MEDIA_STORAGE_S3_KEYID').':@'.
          $this->get('PAPAYA_MEDIA_STORAGE_S3_BUCKET').'/';
        \Papaya\Streamwrapper\S3::setSecret(
          $this->get('PAPAYA_MEDIA_STORAGE_S3_KEYID'),
          $this->get('PAPAYA_MEDIA_STORAGE_S3_KEY')
        );
        \Papaya\Streamwrapper\S3::register('s3');
        $this->set('PAPAYA_MEDIA_STORAGE_SUBDIRECTORY', 'media/');
      break;
      default :
        $basePath = $this->get('PAPAYA_PATH_DATA');
        $this->set('PAPAYA_MEDIA_STORAGE_DIRECTORY', $this->get('PAPAYA_PATH_DATA').'media/');
        if (empty($_SERVER['DOCUMENT_ROOT']) || ('' === $this->get('PAPAYA_PATH_PUBLICFILES'))) {
          $this->set('PAPAYA_MEDIA_PUBLIC_DIRECTORY', '');
          $this->set('PAPAYA_MEDIA_PUBLIC_URL', '');
        } else {
          $this->set(
            'PAPAYA_MEDIA_PUBLIC_DIRECTORY',
            \PapayaUtilFilePath::cleanup(
              $_SERVER['DOCUMENT_ROOT'].$this->get('PAPAYA_PATH_PUBLICFILES')
            )
          );
          $url = new \PapayaUrlCurrent();
          $url->setPath($this->get('PAPAYA_PATH_PUBLICFILES'));
          $this->set(
            'PAPAYA_MEDIA_PUBLIC_URL', $url->getPathUrl()
          );
        }
      break;
    }
    $this->set('PAPAYA_PATH_MEDIAFILES', $basePath.'media/files/');
    $this->set('PAPAYA_PATH_THUMBFILES', $basePath.'media/thumbs/');

    if ($this->get('PAPAYA_PATH_TEMPLATES', '') == '') {
      $templatePaths = array(
        \PapayaUtilFilePath::getDocumentRoot().'/../templates/',
        $this->get('PAPAYA_PATH_DATA').'templates/'
      );
      foreach ($templatePaths as $templatePath) {
        $templatePath = \PapayaUtilFilePath::cleanup($templatePath);
        $this->set('PAPAYA_PATH_TEMPLATES', $templatePath);
        if (file_exists($templatePath) && is_dir($templatePath)) {
          break;
        }
      }
    }
    $this->set(
      'PAPAYA_PATHWEB_ADMIN',
      \PapayaUtilFilePath::cleanup(
        $this->get('PAPAYA_PATH_WEB').$this->get('PAPAYA_PATH_ADMIN')
      )
    );
  }

  /**
   * Define the database table name constants, these constants should be replaced by
   * calls to {@see \PapayaDatabaseAcccess::getTableName()} using the class constants in
   * {@see \Papaya\Content\PapayaContentTables}.
   *
   * But for now the use of these constants is scattered all over the source in the
   * base system and modules, so we need to define them for compatibility.
   *
   * @todo Remove usage of these constants
   */
  public function defineDatabaseTables() {
    $prefix = $this->get('PAPAYA_DB_TABLEPREFIX', 'papaya');
    foreach (\Papaya\Content\Tables::getTables() as $tableConstant => $tableName) {
      if (!defined($tableConstant)) {
        define($tableConstant, $prefix.'_'.$tableName);
      }
    }
  }
}
