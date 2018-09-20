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
namespace Papaya\Content;

/**
 * Defines the tables used by the \Papaya\Content\* classes. Allows to prefix the
 * current table name with the defined table prefix.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Tables extends \Papaya\Application\BaseObject {
  const OPTIONS = 'options';

  const LANGUAGES = 'lng';

  const PHRASES = 'phrase';

  const PHRASE_GROUPS = 'phrase_module';

  const PHRASE_GROUP_LINKS = 'phrase_relmod';

  const PHRASE_TRANSLATIONS = 'phrase_trans';

  const PHRASE_LOG = 'phrase_log';

  const VIEWS = 'views';

  const VIEW_CONFIGURATIONS = 'viewlinks';

  const VIEW_MODES = 'viewmodes';

  const VIEW_DATAFILTER_CONFIGURATIONS = 'datafilter_links';

  const VIEW_DATAFILTERS = 'datafilter';

  const MODULES = 'modules';

  const MODULE_GROUPS = 'modulegroups';

  const MODULE_OPTIONS = 'moduleoptions';

  const AUTHENTICATION_USERS = 'auth_user';

  const AUTHENTICATION_USER_GROUP_LINKS = 'auth_link';

  const AUTHENTICATION_USER_OPTIONS = 'auth_useropt';

  const AUTHENTICATION_GROUPS = 'auth_groups';

  const AUTHENTICATION_PERMISSIONS = 'auth_perm';

  const AUTHENTICATION_MODULE_PERMISSIONS = 'auth_modperm';

  const AUTHENTICATION_MODULE_PERMISSION_LINKS = 'auth_modperm_link';

  const AUTHENTICATION_LOGIN_TRIES = 'auth_try';

  const AUTHENTICATION_LOGIN_IPS = 'auth_ip';

  const COMMUNITY_USER = 'surfer';

  const COMMUNITY_GROUPS = 'surfergroups';

  const COMMUNITY_GROUP_PERMISSIONS = 'surferlinks';

  const COMMUNITY_PERMISSIONS = 'surferperm';

  const DOMAINS = 'domains';

  const DOMAIN_GROUPS = 'domain_groups';

  const PAGES = 'topic';

  const PAGE_TRANSLATIONS = 'topic_trans';

  const PAGE_PUBLICATIONS = 'topic_public';

  const PAGE_PUBLICATION_TRANSLATIONS = 'topic_public_trans';

  const PAGE_VERSIONS = 'topic_versions';

  const PAGE_VERSION_TRANSLATIONS = 'topic_versions_trans';

  const PAGE_DEPENDENCIES = 'topic_dependencies';

  const PAGE_REFERENCES = 'topic_references';

  const PAGE_BOXES = 'boxlinks';

  const PAGE_LINK_TYPES = 'linktypes';

  const BOXES = 'box';

  const BOX_TRANSLATIONS = 'box_trans';

  const BOX_PUBLICATIONS = 'box_public';

  const BOX_PUBLICATION_TRANSLATIONS = 'box_public_trans';

  const BOX_VERSIONS = 'box_versions';

  const BOX_VERSION_TRANSLATIONS = 'box_versions_trans';

  const MEDIA_FILES = 'mediadb_files';

  const MEDIA_FILE_TRANSLATIONS = 'mediadb_files_trans';

  const MEDIA_FOLDERS = 'mediadb_folders';

  const MEDIA_FOLDER_TRANSLATIONS = 'mediadb_folders_trans';

  const MEDIA_MIMETYPES = 'mediadb_mimetypes';

  const TAGS = 'tag';

  const TAG_TRANSLATIONS = 'tag_trans';

  const TAG_LINKS = 'tag_links';

  const TAG_CATEGORY = 'tag_category';

  const TAG_CATEGORY_TRANSLATIONS = 'tag_category_trans';

  const THEME_SKINS = 'theme_sets';

  /**
   * Return tablename with optional prefix.
   *
   * @param string $tableName
   * @param bool $prefix
   *
   * @return string
   */
  public function get($tableName, $prefix = TRUE) {
    if ($prefix && isset($this->papaya()->options)) {
      $prefixString = $this->papaya()->options->get('PAPAYA_DB_TABLEPREFIX', 'papaya');
      if ('' !== $prefixString && 0 !== \strpos($tableName, $prefixString.'_')) {
        return $prefixString.'_'.$tableName;
      }
    }
    return $tableName;
  }

  /**
   * Returns a list of all cms base system tables. For now the key is a constant name for bc
   * definitions
   *
   * @return array
   */
  public static function getTables() {
    return self::$tableConstants;
  }

  /**
   * This array is for backwards compatibility. It was provided by base_options originally.
   * Legacy sources uses constants to access the tables names. This array maps the old global
   * constants (defined dynamically on startup) to the new class constants.
   *
   * If no new constants exists so far. The name is provided as an string.
   *
   * @var array(string=>string)
   */

  /**
   * Papaya database tables
   *
   * @var array $tables
   */
  public static $tableConstants = [
    'PAPAYA_DB_TBL_AUTHUSER' => self::AUTHENTICATION_USERS,
    'PAPAYA_DB_TBL_AUTHGROUPS' => self::AUTHENTICATION_GROUPS,
    'PAPAYA_DB_TBL_AUTHLINK' => self::AUTHENTICATION_USER_GROUP_LINKS,
    'PAPAYA_DB_TBL_AUTHPERM' => self::AUTHENTICATION_PERMISSIONS,
    'PAPAYA_DB_TBL_AUTHOPTIONS' => self::AUTHENTICATION_USER_OPTIONS,
    'PAPAYA_DB_TBL_AUTHMODPERMS' => self::AUTHENTICATION_MODULE_PERMISSIONS,
    'PAPAYA_DB_TBL_AUTHMODPERMLINKS' => self::AUTHENTICATION_MODULE_PERMISSION_LINKS,
    'PAPAYA_DB_TBL_AUTHTRY' => self::AUTHENTICATION_LOGIN_TRIES,
    'PAPAYA_DB_TBL_AUTHIP' => self::AUTHENTICATION_LOGIN_IPS,

    'PAPAYA_DB_TBL_BOX' => self::BOXES,
    'PAPAYA_DB_TBL_BOX_PUBLIC' => self::BOX_PUBLICATIONS,
    'PAPAYA_DB_TBL_BOX_VERSIONS' => self::BOX_VERSIONS,
    'PAPAYA_DB_TBL_BOX_TRANS' => self::BOX_TRANSLATIONS,
    'PAPAYA_DB_TBL_BOX_PUBLIC_TRANS' => self::BOX_PUBLICATION_TRANSLATIONS,
    'PAPAYA_DB_TBL_BOX_VERSIONS_TRANS' => self::BOX_VERSION_TRANSLATIONS,

    'PAPAYA_DB_TBL_BOXGROUP' => 'boxgroups',
    'PAPAYA_DB_TBL_BOXLINKS' => self::PAGE_BOXES,
    'PAPAYA_DB_TBL_CRONJOBS' => 'cronjobs',
    'PAPAYA_DB_TBL_IMAGES' => 'images',
    'PAPAYA_DB_TBL_LINKTYPES' => 'linktypes',
    'PAPAYA_DB_TBL_LNG' => self::LANGUAGES,
    'PAPAYA_DB_TBL_LOG' => 'log',
    'PAPAYA_DB_TBL_LOG_QUERIES' => 'log_queries',
    'PAPAYA_DB_TBL_TODOS' => 'todos',
    'PAPAYA_DB_TBL_MEDIA_LINKS' => 'media_links',
    'PAPAYA_DB_TBL_MESSAGES' => 'messages',
    'PAPAYA_DB_TBL_MIMETYPES' => 'mimetypes',
    'PAPAYA_DB_TBL_MODULES' => self::MODULES,
    'PAPAYA_DB_TBL_MODULEGROUPS' => self::MODULE_GROUPS,
    'PAPAYA_DB_TBL_MODULEOPTIONS' => self::MODULE_OPTIONS,
    'PAPAYA_DB_TBL_URLS' => 'urls',
    'PAPAYA_DB_TBL_DOMAINS' => self::DOMAINS,
    'PAPAYA_DB_TBL_DOMAIN_GROUPS' => self::DOMAIN_GROUPS,

    'PAPAYA_DB_TBL_PHRASE' => self::PHRASES,
    'PAPAYA_DB_TBL_PHRASE_LOG' => self::PHRASE_LOG,
    'PAPAYA_DB_TBL_PHRASE_MODULE' => self::PHRASE_GROUPS,
    'PAPAYA_DB_TBL_PHRASE_MODULE_REL' => self::PHRASE_GROUP_LINKS,
    'PAPAYA_DB_TBL_PHRASE_TRANS' => self::PHRASE_TRANSLATIONS,

    'PAPAYA_DB_TBL_SURFER' => self::COMMUNITY_USER,
    'PAPAYA_DB_TBL_SURFERGROUPS' => self::COMMUNITY_GROUPS,
    'PAPAYA_DB_TBL_SURFERPERM' => self::COMMUNITY_PERMISSIONS,
    'PAPAYA_DB_TBL_SURFERPERMLINK' => self::COMMUNITY_GROUP_PERMISSIONS,
    'PAPAYA_DB_TBL_SURFERCHANGEREQUESTS' => 'surferchangerequests',
    'PAPAYA_DB_TBL_SURFERDATA' => 'surferdata',
    'PAPAYA_DB_TBL_SURFERDATATITLES' => 'surferdatatitles',
    'PAPAYA_DB_TBL_SURFERDATACLASSES' => 'surferdataclasses',
    'PAPAYA_DB_TBL_SURFERDATACLASSTITLES' => 'surferdataclasstitles',
    'PAPAYA_DB_TBL_SURFERCONTACTDATA' => 'surfercontactdata',
    'PAPAYA_DB_TBL_SURFERCONTACTPUBLIC' => 'surfercontactpublic',
    'PAPAYA_DB_TBL_SURFERCONTACTS' => 'surfercontacts',
    'PAPAYA_DB_TBL_SURFERCONTACTCACHE' => 'surfercontactcache',
    'PAPAYA_DB_TBL_SURFERLISTS' => 'surferlists',
    'PAPAYA_DB_TBL_SURFERACTIVITY' => 'surferactivity',
    'PAPAYA_DB_TBL_SURFERBLACKLIST' => 'surferblacklist',
    'PAPAYA_DB_TBL_SURFERFAVORITES' => 'surferfavorites',

    'PAPAYA_DB_TBL_MEDIADB_MIMEGROUPS' => 'mediadb_mimegroups',
    'PAPAYA_DB_TBL_MEDIADB_MIMEGROUPS_TRANS' => 'mediadb_mimegroups_trans',
    'PAPAYA_DB_TBL_MEDIADB_MIMETYPES' => self::MEDIA_MIMETYPES,
    'PAPAYA_DB_TBL_MEDIADB_MIMETYPES_EXTENSIONS' => 'mediadb_mimetypes_extensions',
    'PAPAYA_DB_TBL_MEDIADB_FILES' => self::MEDIA_FILES,
    'PAPAYA_DB_TBL_MEDIADB_FILES_DERIVATIONS' => 'mediadb_files_derivations',
    'PAPAYA_DB_TBL_MEDIADB_FILES_TRANS' => self::MEDIA_FILE_TRANSLATIONS,
    'PAPAYA_DB_TBL_MEDIADB_FILES_VERSIONS' => 'mediadb_files_versions',
    'PAPAYA_DB_TBL_MEDIADB_FOLDERS' => self::MEDIA_FOLDERS,
    'PAPAYA_DB_TBL_MEDIADB_FOLDERS_TRANS' => self::MEDIA_FOLDER_TRANSLATIONS,
    'PAPAYA_DB_TBL_MEDIADB_FOLDERS_PERMISSIONS' => 'mediadb_folders_permissions',

    'PAPAYA_DB_TBL_TAG' => self::TAGS,
    'PAPAYA_DB_TBL_TAG_TRANS' => self::TAG_TRANSLATIONS,
    'PAPAYA_DB_TBL_TAG_CATEGORY' => self::TAG_CATEGORY,
    'PAPAYA_DB_TBL_TAG_CATEGORY_TRANS' => 'tag_category_trans',
    'PAPAYA_DB_TBL_TAG_LINKS' => self::TAG_LINKS,
    'PAPAYA_DB_TBL_TAG_CATEGORY_PERMISSIONS' => 'tag_category_permissions',

    'PAPAYA_DB_TBL_TOPICS' => self::PAGES,
    'PAPAYA_DB_TBL_TOPICS_VERSIONS' => self::PAGE_VERSIONS,
    'PAPAYA_DB_TBL_TOPICS_PUBLIC' => self::PAGE_PUBLICATIONS,
    'PAPAYA_DB_TBL_TOPICS_TRANS' => self::PAGE_TRANSLATIONS,
    'PAPAYA_DB_TBL_TOPICS_VERSIONS_TRANS' => self::PAGE_VERSION_TRANSLATIONS,
    'PAPAYA_DB_TBL_TOPICS_PUBLIC_TRANS' => self::PAGE_PUBLICATION_TRANSLATIONS,
    'PAPAYA_DB_TBL_TOPICS_DEPENDENCIES' => self::PAGE_DEPENDENCIES,
    'PAPAYA_DB_TBL_TOPICS_REFERENCES' => self::PAGE_REFERENCES,

    'PAPAYA_DB_TBL_VIEWS' => self::VIEWS,
    'PAPAYA_DB_TBL_VIEWMODES' => self::VIEW_MODES,
    'PAPAYA_DB_TBL_VIEWLINKS' => self::VIEW_CONFIGURATIONS,
    'PAPAYA_DB_TBL_IMPORTFILTER' => 'importfilter',
    'PAPAYA_DB_TBL_IMPORTFILTER_LINKS' => 'importfilter_links',
    'PAPAYA_DB_TBL_DATAFILTER' => self::VIEW_DATAFILTERS,
    'PAPAYA_DB_TBL_DATAFILTER_LINKS' => self::VIEW_DATAFILTER_CONFIGURATIONS,

    'PAPAYA_DB_TBL_THEME_SETS' => self::THEME_SKINS,

    'PAPAYA_DB_TBL_LOCKING' => 'locking',

    'PAPAYA_DB_TBL_SPAM_STOP' => 'spamstop',
    'PAPAYA_DB_TBL_SPAM_IGNORE' => 'spamignore',
    'PAPAYA_DB_TBL_SPAM_LOG' => 'spamlog',
    'PAPAYA_DB_TBL_SPAM_REFERENCES' => 'spamreferences',
    'PAPAYA_DB_TBL_SPAM_WORDS' => 'spamwords',
    'PAPAYA_DB_TBL_SPAM_CATEGORIES' => 'spamcategories'
  ];
}
