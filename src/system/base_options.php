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

/**
* Basic options of papaya-cms
*
* @package Papaya
* @subpackage Core
*/
class base_options extends base_db {

  /**
  * papaya database table options
  * @var string $tableOptions
  */
  var $tableOptions = PAPAYA_DB_TBL_OPTIONS;

  /**
  * Option groups
  * @var array $optionGroups
  */
  var $optionGroups;

  /**
  * Option links
  * @var array optLinks
  */
  var $optLinks;

  /**
  * Option fields
  * name => array(groupId, checkFunction, inputControl, inputControlOptions,
  *   defaultValue, optional)
  * @var array $optFields
  */
  public static $optFields = array(
    'PAPAYA_DBG_DATABASE_ERROR' => array(3, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 1),
    'PAPAYA_DBG_XML_OUTPUT' => array(3, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 1),
    'PAPAYA_DBG_XML_ERROR' => array(3, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 1),
    'PAPAYA_DBG_XML_USERINPUT' => array(3, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 1),
    'PAPAYA_DBG_PHRASES' => array(3, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 0),
    'PAPAYA_DBG_SHOW_DEBUGS' => array(3, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 1),

    'PAPAYA_PATH_DATA' => array(2, 'isPath', 'input', '100', ''),
    'PAPAYA_PATH_TEMPLATES' => array(2, 'isPath', 'input', '100', '', TRUE),
    'PAPAYA_PATH_WEB' => array(2, 'isPath', 'input', '100', '/'),
    'PAPAYA_PATH_THEMES' => array(2, 'isPath', 'input', '100', '/papaya-themes/'),
    'PAPAYA_CDN_THEMES' => array(2, 'isHTTPX', 'input', '200', '', TRUE),
    'PAPAYA_CDN_THEMES_SECURE' => array(2, 'isHTTPX', 'input', '200', '', TRUE),
    'PAPAYA_PATH_PUBLICFILES' => array(2, 'isPath', 'input', '200', '', TRUE),

    'PAPAYA_PROJECT_TITLE' => array(5, 'isNoHTML', 'input', '50', 'Papaya-Project'),
    'PAPAYA_COMMUNITY_REDIRECT_PAGE' => array(5, 'isNum', 'pageid', '5', 0),
    'PAPAYA_COMMUNITY_AUTOLOGIN' => array(5, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 1),
    'PAPAYA_COMMUNITY_RELOGIN' => array(5, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 0),
    'PAPAYA_COMMUNITY_RELOGIN_SALT' => array(5, 'isNoHTML', 'input', '40', ''),
    'PAPAYA_COMMUNITY_RELOGIN_EXP_DAYS' => array(5, 'isNum', 'input', '40', 7),
    'PAPAYA_COMMUNITY_API_LOGIN' => array(
      5,
      'isNum',
      'combo',
      array(0 => 'Handle', 1 => 'Email', 2 => 'Handle or email'),
      0
    ),
    'PAPAYA_COMMUNITY_HANDLE_MAX_LENGTH' => array(5, 'isNum', 'input', '40', 20),
    'PAPAYA_CONTENT_LANGUAGE' => array(5, 'isNum', 'function',
      'getContentLanguageCombo', 1),
    'PAPAYA_CONTENT_LANGUAGE_COOKIE' => array(5, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 0),
    'PAPAYA_DEFAULT_PROTOCOL' => array(5, 'isNum', 'combo',
      array(0 => 'None', 1 => 'http', 2 => 'https'), 0),
    'PAPAYA_DEFAULT_HOST' => array(5, 'isHTTPHost', 'input', '100', ''),
    'PAPAYA_DEFAULT_HOST_ACTION' => array(5, 'isNum', 'combo',
      array('0' => 'None', '1' => 'Redirect'), 0),

    'PAPAYA_FLASH_DEFAULT_VERSION' => array(5, '(^\d{1,3}(\.\d{1,4}){0,2}$)D', 'input',
      20, '9.0.28'),
    'PAPAYA_FLASH_MIN_VERSION' => array(5, '(^\d{1,3}(\.\d{1,4}){0,2}$)D', 'input',
      20, '', TRUE),
    'PAPAYA_REDIRECT_PROTECTION' => array(5, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 0),
    'PAPAYA_PAGE_STATISTIC' => array(5, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 0),
    'PAPAYA_STATISTIC_PRESERVE_IP' => array(5, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 0),
    'PAPAYA_SPAM_LOG' => array(5, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 1),
    'PAPAYA_SPAM_BLOCK' => array(5, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 0),
    'PAPAYA_SPAM_SCOREMIN_PERCENT' => array(5, 'isNum', 'input', 3, 10),
    'PAPAYA_SPAM_SCOREMAX_PERCENT' => array(5, 'isNum', 'input', 3, 90),
    'PAPAYA_SPAM_STOPWORD_MAX' => array(5, 'isNum', 'input', 3, 10),
    'PAPAYA_PUBLISH_SOCIALMEDIA' => array(5, 'isNum', 'combo',
       array(TRUE => 'on', FALSE => 'off'), 0),

    'PAPAYA_PASSWORD_ALGORITHM' => array(
      7,
      'isNum',
      'combo',
      array(0 => 'PHP Default (suggested)', 1 => 'BCrypt'),
      'en-US'
    ),
    'PAPAYA_PASSWORD_REHASH' => array(
      7,
      'isNum',
      'combo',
      array(TRUE => 'on', FALSE => 'off'),
      0
    ),

    'PAPAYA_DATABASE_CLUSTER_SWITCH' => array(7, 'isNum', 'combo',
      array(0 => 'manual', 1 => 'object', 2 => 'connection'), 0),
    'PAPAYA_VERSIONS_MAXCOUNT' => array(7, 'isNum', 'input', '5', 200),
    'PAPAYA_XSLT_EXTENSION' => array(7, 'isAlpha', 'function',
      'getXSLTExtensionsCombo', '', TRUE),
    'PAPAYA_BROWSER_CRONJOBS' => array(7, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 0),
    'PAPAYA_BROWSER_CRONJOBS_IP' => array(7, 'isAlphaNumChar', 'input', 100, '127.0.0.1'),
    'PAPAYA_URL_NAMELENGTH' => array(7, 'isNum', 'input', 3, 50),
    'PAPAYA_URL_EXTENSION' => array(7, 'isAlpha', 'combo',
      array('html' => 'html', 'papaya' => 'papaya'), 'html'),
    'PAPAYA_URL_LEVEL_SEPARATOR' => array(7, '(^.$)', 'combo',
      array('' => '[ ]', ',' => ',', ':' => ':', '*' => '*', '!' => '!', '/' => '/'), '/', TRUE),
    'PAPAYA_URL_ALIAS_SEPARATOR' => array(7, '(^.$)', 'combo',
      array(',' => ',', ':' => ':', '*' => '*', '!' => '!'), ',', TRUE),
    'PAPAYA_URL_FIXATION' => array(7, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 0),
    'PAPAYA_SEARCH_BOOLEAN' => array(7, 'isNum', 'combo',
      array(
        0 => 'generic SQL (LIKE)',
        1 => 'MySQL FULLTEXT',
        2 => 'MySQL FULLTEXT BOOLEAN (MySQL >= 4.1)'
      ), 0),
    'PAPAYA_PROTECT_FORM_CHANGES' => array(7, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 1),
    'PAPAYA_TRANSLITERATION_MODE' => array(7, 'isNum', 'combo',
      array(
        0 => 'papaya',
        1 => 'ext/translit',
      ), 0),
    'PAPAYA_PUBLICATION_CHANGE_LEVEL' => array(7, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 0),
    'PAPAYA_GMAPS_API_KEY' => array (7, 'isSomeText', 'input', '500', 0),
    'PAPAYA_GMAPS_DEFAULT_POSITION' => array (7, 'isGeoPos', 'geopos', '1024',
      '50.94794501585774, 6.944365873932838'),

    'PAPAYA_LAYOUT_TEMPLATES' => array(8, 'isAlphaNum', 'dircombo',
      array('', 'templates'), 'default-xhtml'),
    'PAPAYA_LAYOUT_THEME' => array(8, 'isAlphaNum', 'dircombo',
      array('papaya-themes/', 'page'), 'default'),
    'PAPAYA_LAYOUT_THEME_SET' => array(8, 'isAlphaNum', 'function',
      'getThemeSetsCombo', '', TRUE),

    'PAPAYA_PAGEID_DEFAULT' => array(9, 'isNum', 'pageid', '5', 1),
    'PAPAYA_PAGEID_USERDATA' => array(9, 'isNum', 'pageid', '5', 0),
    'PAPAYA_PAGEID_STATUS_301' => array(9, 'isNum', 'pageid', '5', 0),
    'PAPAYA_PAGEID_STATUS_302' => array(9, 'isNum', 'pageid', '5', 0),
    'PAPAYA_PAGEID_ERROR_403' => array (9, 'isNum', 'pageid', '5', 0),
    'PAPAYA_PAGEID_ERROR_404' => array (9, 'isNum', 'pageid', '5', 0),
    'PAPAYA_PAGEID_ERROR_500' => array (9, 'isNum', 'pageid', '5', 0),

    'PAPAYA_DB_TABLEPREFIX' => array(6, 'isAlpha', 'input', 5, 'papaya'),
    'PAPAYA_PATH_ADMIN' => array(6, 'isAlpha', 'input', 60, '/papaya'),
    'PAPAYA_MEDIADB_SUBDIRECTORIES' => array(6, 'isNum', 'input', 1, 1),

    'PAPAYA_LATIN1_COMPATIBILITY' => array(10, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 1),
    'PAPAYA_INPUT_ENCODING' => array(10, 'isAlphaNumChar', 'combo',
      array(
        'utf-8' => 'utf-8',
        'iso-8859-1' => 'iso-8859-1'
      ), 'utf-8'),
    'PAPAYA_DATABASE_COLLATION' => array(10, 'isAlphaNumChar', 'combo',
      array(
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
      ), 'utf8_general_ci'),

    'PAPAYA_LOGIN_RESTRICTION' => array(12, 'isNum', 'combo',
      array(
        0 => 'None',
        1 => 'Use Blacklist',
        2 => 'Use Blacklist and Whitelist',
        3 => 'Restrict to Whitelist'
      ), 0),
    'PAPAYA_LOGIN_CHECKTIME' => array(12, 'isNum', 'input', 5, 600),
    'PAPAYA_LOGIN_NOTIFYCOUNT' => array(12, 'isNum', 'input', 5, 10),
    'PAPAYA_LOGIN_BLOCKCOUNT' => array(12, 'isNum', 'input', 5, 20),
    'PAPAYA_LOGIN_NOTIFYEMAIL' => array(12, 'isEMail', 'input', 50, ''),
    'PAPAYA_LOGIN_GC_ACTIVE' => array(12, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 0),
    'PAPAYA_LOGIN_GC_TIME' => array(12, 'isNum', 'input', 10, 86400),
    'PAPAYA_LOGIN_GC_DIVISOR' => array(12, 'isNum', 'input', 3, 50),

    'PAPAYA_SUPPORT_BUG_EMAIL' => array (13, 'isEMail', 'input', 50,
      'info@papaya-cms.com'),
    'PAPAYA_SUPPORT_PAGE_NEWS' => array (13, 'isHTTPX', 'input', 50,
      'https://www.papaya-cms.com/news/'),
    'PAPAYA_SUPPORT_PAGE_MANUAL' => array (13, 'isHTTPX', 'input', 50,
      'https://www.papaya-cms.com/manual/'),

    'PAPAYA_MAX_UPLOAD_SIZE' => array(14, 'isNum', 'input', 5, 7),
    'PAPAYA_NUM_UPLOAD_FIELDS' => array(14, 'isNum', 'input', 2, 1),
    'PAPAYA_PATH_MEDIADB_IMPORT' => array(14, 'isPath', 'input', '100', ''),
    'PAPAYA_MEDIADB_THUMBSIZE' => array(14, 'isNum', 'input', 4, 80),
    'PAPAYA_IMAGE_CONVERTER' => array(14, 'isAlpha', 'combo',
      array(
        'gd' => 'GD',
        'netpbm' => 'netpbm',
        'imagemagick' => 'Image Magick',
        'graphicsmagick' => 'GraphicksMagick'
      ), 'gd'),
    'PAPAYA_IMAGE_CONVERTER_PATH' => array(14, 'isPath', 'input', 100, ''),
    'PAPAYA_FILE_CMD_PATH' => array(14, 'isFile', 'input', 100, ''),
    'PAPAYA_THUMBS_FILETYPE' => array(14, 'isNum', 'combo',
      array(
        1 => 'GIF',
        2 => 'JPEG',
        3 => 'PNG'
      ), 3),
    'PAPAYA_THUMBS_JPEGQUALITY' => array(14, 'isNum', 'input', 3, 80),
    'PAPAYA_THUMBS_BACKGROUND' => array(14, 'isHTMLColor', 'input', 7, '#FFFFFF'),
    'PAPAYA_THUMBS_TRANSPARENT' => array(14, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 1),
    'PAPAYA_BANDWIDTH_SHAPING' => array(14, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 0),
    'PAPAYA_BANDWIDTH_SHAPING_LIMIT' => array(14, 'isNum', 'input', 10, 10240),
    'PAPAYA_BANDWIDTH_SHAPING_OFFSET' => array(14, 'isNum', 'input', 10, 1048576),
    'PAPAYA_SENDFILE_HEADER' => array(14, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 0),
    'PAPAYA_MEDIA_CUTLINE_MODE' => array(14, 'isNum', 'combo',
      array(
        0 => 'manual only',
        1 => 'text (source)',
        2 => 'title (source) / text'
      ),
      0
    ),
    'PAPAYA_MEDIA_CUTLINE_LINK_CLASS' => array(14, 'isAlpha', 'input', 50, 'source'),
    'PAPAYA_MEDIA_CUTLINE_LINK_TARGET' => array(
      14, 'isAlphaNum', 'combo', array('_self' => '_self', '_blank' => '_blank'), '_self'
    ),
    'PAPAYA_MEDIA_ALTTEXT_MODE' => array(14, 'isNum', 'combo',
      array(0 => 'explicit only', 1 => 'description'), 0),
    'PAPAYA_MEDIA_CSSCLASS_IMAGE' => array(14, 'isAlpha', 'input', 50, 'papayaImage'),
    'PAPAYA_MEDIA_CSSCLASS_SUBTITLE' => array(14, 'isAlpha', 'input', 50, 'papayaImageSubtitle'),
    'PAPAYA_MEDIA_CSSCLASS_DYNIMAGE' => array(14, 'isAlpha', 'input', 50, 'papayaDynamicImage'),
    'PAPAYA_MEDIA_CSSCLASS_LINK' => array(14, 'isAlpha', 'input', 50, 'papayaLink'),
    'PAPAYA_MEDIA_CSSCLASS_MAILTO' => array(14, 'isAlpha', 'input', 50, 'papayaMailtoLink'),
    'PAPAYA_MEDIA_STORAGE_SERVICE' => array(
      14, 'isAlphaNum', 'combo', array('file' => 'File system', 's3' => 'AWS S3'), 'file'
    ),
    'PAPAYA_MEDIA_STORAGE_S3_BUCKET' => array(
      14, 'isAlphaNumChar', 'input', 20, '', FALSE
    ),
    'PAPAYA_MEDIA_STORAGE_S3_KEYID' => array(
      14, 'isAlphaNumChar', 'input', 40, '', FALSE
    ),
    'PAPAYA_MEDIA_STORAGE_S3_KEY' => array(
      14, 'isNoHTML', 'input', 64, '', FALSE
    ),
    'PAPAYA_THUMBS_MEMORYCHECK_SUHOSIN' => array(
      14, 'isNum', 'combo', array(TRUE => 'on', FALSE => 'off'), 1
    ),
    'PAPAYA_MEDIA_ELEMENTS_IMAGE' => array(
      14,
      'isNum',
      'combo',
      array(
        papaya_parser::ELEMENTS_SPAN => 'span (only with subtitle)',
        papaya_parser::ELEMENTS_FIGURE => 'figure/figcaption (only with subtitle)',
        papaya_parser::ELEMENTS_FIGURE_MANDATORY => 'figure/figcaption (always)',
      ),
      1
    ),

    'PAPAYA_CACHE_SERVICE' => array(15, 'isAlpha', 'combo',
      array('apc' => 'APC', 'file' => 'File system', 'memcache' => 'Memcache'), 'file'),
    'PAPAYA_CACHE_DISABLE_FILE_DELETE' => array(15, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), FALSE),
    'PAPAYA_CACHE_NOTIFIER' => array(15, 'isSomeText', 'input', 400, ''),
    'PAPAYA_CACHE_MEMCACHE_SERVERS' => array(15, 'isSomeText', 'input', 400, '', TRUE),
    'PAPAYA_CACHE_OUTPUT' => array(15, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 0),
    'PAPAYA_CACHE_TIME_OUTPUT' => array(15, 'isNum', 'input', '10', 0),
    'PAPAYA_CACHE_TIME_PAGES' => array(15, 'isNum', 'input', '10', 0),
    'PAPAYA_CACHE_PAGES' => array(15, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 0),
    'PAPAYA_CACHE_TIME_FILES' => array(15, 'isNum', 'input', '10', 2592000),
    'PAPAYA_CACHE_BOXES' => array(15, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 0),
    'PAPAYA_CACHE_TIME_BOXES' => array(15, 'isNum', 'input', '10', 0),
    'PAPAYA_CACHE_TIME_BROWSER' => array(15, 'isNum', 'input', '10', 0),
    'PAPAYA_CACHE_THEMES' => array(15, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 1),
    'PAPAYA_CACHE_TIME_THEMES' => array(15, 'isNum', 'input', '10', 2592000),
    'PAPAYA_COMPRESS_OUTPUT' => array(15, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), FALSE),
    'PAPAYA_COMPRESS_CACHE_OUTPUT' => array(15, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), FALSE),
    'PAPAYA_COMPRESS_CACHE_THEMES' => array(15, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), TRUE),
    'PAPAYA_CACHE_DATA' => array(15, 'isNum', 'combo', array(TRUE => 'on', FALSE => 'off'), 0),
    'PAPAYA_CACHE_DATA_TIME' => array(15, 'isNum', 'input', '10', 60),
    'PAPAYA_CACHE_DATA_SERVICE' => array(15, 'isAlpha', 'combo',
      array('apc' => 'APC', 'file' => 'File system', 'memcache' => 'Memcache'), 'file'),
    'PAPAYA_CACHE_DATA_MEMCACHE_SERVERS' => array(15, 'isSomeText', 'input', 400, '', TRUE),

    'PAPAYA_LOG_PHP_ERRORLEVEL' => array(
      16,
      'isNum',
      'combo',
      array(
        0 => 'None',
        30719 => 'E_ALL & ~E_STRICT',
        30711 => 'E_ALL & ~(E_STRICT | E_NOTICE)',
        29687 => 'E_ALL & ~(E_STRICT | E_NOTICE | E_USER_NOTICE)'
      ),
      30719
    ),
    'PAPAYA_LOG_DATABASE_CLUSTER_VIOLATIONS' => array(
      16, 'isNum', 'combo', array(TRUE => 'on', FALSE => 'off'), 0
    ),
    'PAPAYA_LOG_DATABASE_QUERY' => array(
      16, 'isNum', 'combo', array(0 => 'none', 1 => 'slow', 2 => 'all'), 0
    ),
    'PAPAYA_LOG_DATABASE_QUERY_SLOW' => array(
      16, 'isNum', 'input', 6, 3000
    ),
    'PAPAYA_LOG_DATABASE_QUERY_DETAILS' => array(
      16, 'isNum', 'combo', array(TRUE => 'on', FALSE => 'off'), 0
    ),
    'PAPAYA_LOG_EVENT_PAGE_MOVED' => array(
      16, 'isNum', 'combo', array(TRUE => 'on', FALSE => 'off'), 1
    ),
    'PAPAYA_LOG_ERROR_THUMBNAIL' => array(
      16, 'isNum', 'combo', array(TRUE => 'on', FALSE => 'off'), 1
    ),
    'PAPAYA_LOG_RUNTIME_DATABASE' => array(
      16, 'isNum', 'combo', array(1 => 'on', FALSE => 'off'), 0
    ),
    'PAPAYA_LOG_RUNTIME_REQUEST' => array(
      16, 'isNum', 'combo', array(TRUE => 'on', FALSE => 'off'), 0
    ),
    'PAPAYA_LOG_RUNTIME_TEMPLATE' => array(
      16, 'isNum', 'combo', array(TRUE => 'on', FALSE => 'off'), 0
    ),
    'PAPAYA_PROTOCOL_DATABASE' => array(
      16, 'isNum', 'combo', array(TRUE => 'on', FALSE => 'off'), 0
    ),
    'PAPAYA_PROTOCOL_DATABASE_DEBUG' => array(
      16, 'isNum', 'combo', array(TRUE => 'on', FALSE => 'off'), 0
    ),
    'PAPAYA_PROTOCOL_WILDFIRE' => array(
      16, 'isNum', 'combo', array(TRUE => 'on', FALSE => 'off'), 0
    ),
    'PAPAYA_PROTOCOL_XHTML' => array(
      16, 'isNum', 'combo', array(TRUE => 'on', FALSE => 'off'), 0
    ),
    'PAPAYA_QUERYLOG' => array(
      16, 'isNum', 'combo', array(0 => 'none', 1 => 'slow', 2 => 'all'), 0
    ),
    'PAPAYA_QUERYLOG_SLOW' => array(
      16, 'isNum', 'input', 6, 3000
    ),
    'PAPAYA_QUERYLOG_DETAILS' => array(
      16, 'isNum', 'combo', array(1 => 'on', FALSE => 'off'), 0
    ),

    'PAPAYA_SESSION_START' => array(17, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 1),
    'PAPAYA_SESSION_ACTIVATION' => array(17, 'isNum', 'combo',
      array(1 => 'always', 2 => 'never', 3 => 'dynamic'), 1),
    'PAPAYA_DB_DISCONNECT_SESSIONSTART' => array(17, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 0),
    'PAPAYA_SESSION_DOMAIN' => array(17, 'isHTTPHost', 'input',
      200, '',  TRUE),
    'PAPAYA_SESSION_PATH' => array(17, 'isPath', 'input',
      200, '',  TRUE),
    'PAPAYA_SESSION_SECURE' => array(17, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 0),
    'PAPAYA_SESSION_HTTP_ONLY' => array(17, 'isNum', 'combo',
      array(TRUE => 'on', FALSE => 'off'), 1),
    'PAPAYA_SESSION_ID_FALLBACK' => array(17, 'isAlpha', 'combo',
      array(
        'none' => 'None',
        'rewrite' => 'Path Rewrite',
        'get' => 'Transparent SID'
      ), 'rewrite'),
    'PAPAYA_SESSION_CACHE' => array(17, 'isAlpha', 'combo',
      array('private' => 'private', 'nocache' => 'nocache'), 'private'),

    // features
    'PAPAYA_DATAFILTER_USE' => array(
      18, 'isNum', 'combo', array(TRUE => 'on', FALSE => 'off'), 0
    ),
    'PAPAYA_PUBLICATION_AUDITING' => array(
      18, 'isNum', 'combo', array(TRUE => 'on', FALSE => 'off'), 0
    ),
    'PAPAYA_FEATURE_BOXGROUPS_LINKABLE' => array(
      18, 'isNum', 'combo', array(TRUE => 'on', FALSE => 'off'), 1
    ),

    // experimental features
    'PAPAYA_IMPORTFILTER_USE' => array(
      19, 'isNum', 'combo', array(TRUE => 'on', FALSE => 'off'), 0
    ),

    // Administration UI
    'PAPAYA_OVERVIEW_ITEMS_UNPUBLISHED' => array(11, 'isNum', 'input', 2, 15),
    'PAPAYA_OVERVIEW_ITEMS_PUBLISHED' => array(11, 'isNum', 'input', 2, 10),
    'PAPAYA_OVERVIEW_ITEMS_MESSAGES' => array(11, 'isNum', 'input', 2, 10),
    'PAPAYA_OVERVIEW_ITEMS_TASKS' => array(11, 'isNum', 'input', 2, 10),
    'PAPAYA_OVERVIEW_TASK_NOTIFY' => array(
      11, 'isNum', 'combo', array(TRUE => 'on', FALSE => 'off'), 0
    ),
    'PAPAYA_UI_SEARCH_CHARACTER_LIMIT' => array(11, 'isNum', 'input', 4, 100),
    'PAPAYA_UI_SEARCH_ANCESTOR_LIMIT' => array(11, 'isNum', 'input', 3, 7),
    'PAPAYA_UI_LANGUAGE' => array(
      11, 'isAlpha', 'function', 'getInterfaceLanguageCombo', 'en-US'
    ),
    'PAPAYA_UI_SECURE' => array(
      11, 'isNum', 'combo', array(TRUE => 'on', FALSE => 'off'), 0
    ),
    'PAPAYA_UI_SECURE_WARNING' => array(
      11, 'isNum', 'combo', array(TRUE => 'on', FALSE => 'off'), TRUE
    ),
    'PAPAYA_UI_THEME' => array(
      11,
      'isAlphaChar',
      'filecombo',
      array('styles/themes/', '(^.+\.ini)', TRUE, 'admin'),
      'green.ini',
      TRUE
    ),
    'PAPAYA_USE_RICHTEXT' => array(
      11, 'isNum', 'combo', array(TRUE => 'on', FALSE => 'off'), 1
    ),
    'PAPAYA_RICHTEXT_TEMPLATES_FULL' => array(
      11, '(^([a-z]+)(,[a-z+])*)', 'input', 200, 'p,div,h2,h3,h4,h5,h6,blockquote'
    ),
    'PAPAYA_RICHTEXT_TEMPLATES_SIMPLE' => array(
      11, '(^([a-z]+)(,[a-z+])*)', 'input', 200, 'p,div,h2,h3'
    ),
    'PAPAYA_RICHTEXT_CONTENT_CSS' => array(
      11, 'isFile', 'filecombo',
      array('', '(^.+\.css$)', TRUE, 'current_theme'), 'tinymce.css', TRUE
    ),
    'PAPAYA_RICHTEXT_LINK_TARGET' => array(
      11, 'isAlphaChar', 'combo', array('_self' => '_self', '_blank' => '_blank'), '_self'
    ),
    'PAPAYA_RICHTEXT_BROWSER_SPELLCHECK' => array(
      11, 'isNum', 'combo', array(TRUE => 'on', FALSE => 'off'), FALSE
    )
  );

  /**
  * Create and/or return a single instance of the options object.
  *
  * Because this class is deprecated, we return the global options object from the
  * application registry.
  *
  * @param boolean $reset
  * @return base_options
  */
  public static function getInstance($reset = FALSE) {
    /** @var \Papaya\Application\CMSApplication $application */
    $application = \Papaya\Application::getInstance();
    return $application->options;
  }

  /**
  * Define database table
  *
  * @access public
  */
  function defineDatabaseTables() {
    $options = $this->papaya()->options;
    if ($options instanceof \Papaya\CMS\CMSConfiguration) {
      $options->defineDatabaseTables();
    }
  }

  /**
   * Load and define
   *
   * @access public
   * @param bool $loadFromDB
   * @return boolean $result
   */
  function loadAndDefine($loadFromDB = TRUE) {
    $options = $this->papaya()->options;
    if ($options instanceof \Papaya\CMS\CMSConfiguration) {
      $options->loadAndDefine();
    }
  }

  /**
   * get an option value
   *
   * @param string $name
   * @param bool|float|int|string $defaultValue returned if not avaliable
   * @return bool|float|int|string
   */
  function getOption($name, $defaultValue = '') {
    return $this->get($name, $defaultValue);
  }

  /**
  * get an option value
  *
  * @param string $name
  * @param string|integer|boolean $defaultValue returned if not avaliable
  * @return bool|float|int|string
  */
  function get($name, $defaultValue = '') {
    return $this->papaya()->options->get($name, $defaultValue);
  }
}
