<?php
/**
* Static utility Class to identify robots by useragent strings
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Util
* @version $Id: Agent.php 39403 2014-02-27 14:25:16Z weinert $
*/

/**
* Static utility Class to identify robots by useragent strings
*
* @package Papaya-Library
* @subpackage Util
*/
class PapayaUtilServerAgent {

  /**
  * If it contains one of these substrings it is not an robot
  *
  * @var array
  */
  private static $_agents = array(
    'Lynx', 'FirePHP'
  );

  /**
  * If it contains one of these substrings it is an robot
  *
  * @var array
  */
  private static $_robots = array(
    ':robot', 'AOLpress', 'ASPSeek', 'ASPseek', 'Anonymouse.org', 'Ask Jeeves',
    'AvantGo', 'BSDSeek', 'BilgiBot', 'Bimbot', 'BladeRunner', 'Blaiz-Bee',
    'BlitzBOT', 'BlogBot', 'Bloglines', 'Bookmark-Manager', 'Bot42', 'CCC-178_8',
    'CFNetwork', 'COAST WebMaster', 'COAST scan engine', 'Charybdis', 'Checkbot',
    'Chilkat', 'CoMaSYSTEM', 'ColdFusion', 'Combine', 'Crawl', 'Cynthia',
    'DISCo Pump', 'DLFileWI', 'DLMAN', 'DataparkSearch', 'DeleGate',
    'Desktop Sidebar', 'DiaGem', 'DoCoMo', 'Drupal', 'EasyDL', 'EmailSiphon',
    'EuripBot', 'Exabot', 'FDM', 'FOTOCHECKER', 'FairAd', 'FavIconizer', 'FavOrg',
    'FeedValidator', 'FindoFix', 'Firefly', 'Francis', 'GOFORITBOT', 'GetRight',
    'Getweb', 'Gigabot', 'GobbleGobble', 'Goldfire', 'Google', 'Gozilla', 'Gulliver',
    'GurujiBot', 'GurujiBot', 'HBZ-Digbib', 'HBZ-Digibib', 'HSE', 'HTMLParser',
    'HTTrack', 'HuckleberryBot', 'ISC Systems', 'Ideare', 'IlTrovatore', 'IlseBot',
    'ImagesHere', 'Indexer', 'Indy Library', 'Ineta', 'Infoseek', 'InsurancoBot',
    'InternetLinkAgent', 'ItsyBitsy', 'JSpindel', 'Jakarta Commons', 'Java',
    'Jigsaw', 'KRetrieve', 'Kopernikus', 'LBot', 'LeechGet', 'LiSEn', 'Link Checker',
    'Link Sleuth', 'LinkAlarm', 'LinkControl', 'LinkLint', 'LinkMan', 'LinkWalker',
    'Live.Com', 'Lorkyll', 'MEGAUPLOAD', 'MELBOT', 'MFC_Tear_Sample', 'MJ12bot',
    'MMHttp', 'MS Search', 'MSRBOT', 'MVAClient', 'MaSagool', 'Maoch', 'Mercator',
    'MetaGer_PreChecker', 'MetagerBot', 'Microsoft Data Access',
    'Microsoft Office Protocol Discovery', 'Microsoft URL Control', 'Missigua',
    'MnoGoSearch', 'Mo College', 'Mozzarella', 'My-Bot', 'MyEngines-Bot', 'MySource',
    'NECBot', 'NG-Search', 'NG/1.0', 'NG/2.0', 'NPBot', 'NaverBot', 'NetObjects',
    'NetResearchServer', 'NetSprint', 'Netcraft', 'Netluchs', 'Netprospector',
    'NextGenSearchBot', 'Norton', 'NuSpidr', 'Nutch', 'OPen sourfce retriver',
    'OctBot', 'Octora', 'Offline Explorer', 'OmniExplorer_Bot', 'Openfind',
    'Oracle Ultra Search', 'Oracle Ultra Search', 'PEAR', 'PHOTO CHECK', 'PHP',
    'POE-Component-Client', 'Pagebull', 'Perl', 'PictureOfInternet',
    'Pluck Soap Client', 'Plucker', 'Plumtree', 'Poirot', 'Pompos', 'PostFavorites',
    'Powermarks', 'PuxaRapido', 'PycURL', 'Python-urllib', 'QMina', 'RAMPyBot',
    'RPT-HTTPClient', 'RSSOwl', 'RealDownload', 'Renderer', 'Robot', 'Robozilla',
    'SAcc', 'SEOsearch', 'SMBot', 'STEROID', 'ScanWebBot', 'Scivias', 'Scooter',
    'ScoutAbout', 'Search', 'SearchTone', 'Searcher', 'Seekbot', 'Semager', 'shelob',
    'Shrook', 'SignSite', 'SiteBar', 'SiteSucker', 'SiteXpert', 'Skywalker', 'Slurp',
    'SlySearch', 'SmartDownload', 'Snapbot', 'Snappy', 'Snoopy', 'Spider', 'Spinne',
    'SquidClamAV_Redirector', 'Star Downloader', 'Steeler', 'Steganos', 'SumeetBot',
    'SuperBot', 'SurferX', 'SurveyBot', 'SygolBot', 'SynooBot', 'Szook', 'T-Online',
    'TCF', 'Tagyu', 'Tcl http client', 'TeamSoft', 'Teleport', 'Teradex Mapper',
    'Tkensaku', 'Touche', 'Twiceler', 'URI::Fetch', 'URL Validator', 'URLBase',
    'Ultraseek', 'UniversalFeedParser', 'Vagabondo', 'Validome XML-Validator',
    'Vayala', 'Verity-URL-Gateway', 'Viking', 'W3C_Validator', 'WWW-Mechanize',
    'WWWC', 'WWWOFFLE', 'Wapsilon', 'Watchfire WebXM', 'Web Downloader',
    'WebCapture', 'WebCopier', 'WebCorp', 'WebDAV', 'WebDownloader', 'WebImages',
    'WebReaper', 'WebRepository', 'WebSnatcher', 'WebStripper', 'WebTrends',
    'WebWasher', 'WebarooBot', 'Webshuttle', 'Webster Pro', 'Webverzeichnis.de',
    'West Wind Internet Protocols', 'Wget', 'WhizBang', 'Whizbang',
    'Wildsoft Surfer', 'WinSysClean', 'WinSysClean', 'WordPress', 'XenTarY',
    'Xenu Link Sleuth', 'Yahoo Pipes', 'Yahoo! Mindset', 'YooW!', 'ZyBorg',
    'agadine', 'aipbot', 'appie', 'asterias', 'bigfoot.com', 'blogchecker',
    'blogsear.ch', 'bot', 'bumblebee@relevare.com', 'cHAINsAW massacre', 'cfetch',
    'cometrics-bot', 'cosmos', 'crawl', 'csci', 'curiosity', 'curl', 'db/0.2; spc',
    'eCatch', 'eagle', 'ejupiter', 'eltopi', 'facebookexternalhit', 'findlinks', 'flunky',
    'fmII URL validator', 'gazz', 'genieBot', 'gnome-vfs', 'gonzo',
    'headbangers.info', 'ht://check', 'htdig', 'http://putf.info/', 'httpclient',
    'httpunit', 'iOpus', 'iSiloX', 'ia_archiver', 'icerocket', 'kykapeky',
    'lanshanbot', 'larbin', 'libcurl', 'libwww', 'linkchecker.sourceforge',
    'lithopssoft.com', 'lwp', 'medical-info.de', 'miniRank', 'mnogo',
    'mnogosearch-dimensional', 'moget', 'msnbot', 'mylinkcheck', 'mysmutsearch',
    'nagios-plugins',
    'nestListener', 'nestReader', 'netforex.org', 'noyona', 'oegp', 'ozelot',
    'page_verifier', 'panscient.com', 'papaya-Benchmarking-Tool', 'penthesila',
    'penthesilea', 'petitsage.fr', 'playstarmusic.com', 'pmafind',
    'pressemitteilung.ws', 'psbot', 'puf', 'redax', 'reifier', 'riba-it.de',
    'seo.ag', 'sitecheck.internetseer.com', 'spider', 'suchbaer.de', 'sun4u',
    'szukaj', 'teoma', 'thumbshots.de', 'topicblogs', 'troovziBot',
    'vias.ncsa.uiuc.edu', 'voyager', 'w3development', 'w3mir', 'webbot',
    'webcollage', 'webmeasurement-bot', 'www.adressendeutschland.de',
    'www.anonymous.com', 'www.walhello.com', 'wwwster', 'yacy.net', 'yahoo.com',
    'zero-knowledge', 'flash mediaserver'
  );

  /**
  * Cache for robot identifications (true/false)
  */
  private static $_cache = array();

  /**
   * Fetch the user agent from $_SERVER['HTTP_USER_AGENT'].
   *
   * @param string
   * @return string
   */
  public static function get() {
    return !empty($_SERVER['HTTP_USER_AGENT'])
      ? $_SERVER['HTTP_USER_AGENT'] : '';
  }

  /**
   * Check if the given user agent string is an robot.
   *
   * If the user agent string is empty, $_SERVER['HTTP_USER_AGENT'] is used.
   *
   * The result is cached in the class, it is possible to call this method without to much overhead
   * for the checks.
   *
   * @param string $userAgent
   * @param boolean $useCache
   * @param boolean
   * @return bool
   */
  public static function isRobot($userAgent = '', $useCache = TRUE) {
    if (empty($userAgent)) {
      $userAgent = self::get();
    }
    if (!empty($userAgent)) {
      if ($useCache && isset(self::$_cache[$userAgent])) {
        return self::$_cache[$userAgent];
      }
      return self::$_cache[$userAgent] = self::_checkAgentIsRobot($userAgent);
    }
    return FALSE;
  }

  /**
   * Check if the given user agent string is an robot.
   *
   * This method checks the user agent string agains the two internal lists of user agents and
   * robots.
   *
   * @param string $userAgent
   * @param boolean
   * @return bool
   */
  private static function _checkAgentIsRobot($userAgent) {
    if (self::_checkAgainstList($userAgent, self::$_agents)) {
      return FALSE;
    }
    return self::_checkAgainstList($userAgent, self::$_robots);
  }

  /**
  * Check if the user agent contains one of the identifier strings in the list.
  *
  * @param string $userAgent
  * @param array $list
  * @return boolean
  */
  private static function _checkAgainstList($userAgent, $list) {
    foreach ($list as $pattern) {
      if (strpos($userAgent, $pattern) !== FALSE) {
        return TRUE;
      }
    }
    return FALSE;
  }
}