<?php
/**
* Class to identify robots by useragentstrings
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
* @subpackage Session
* @version $Id: base_robots.php 39260 2014-02-18 17:13:06Z weinert $
*/

/**
* Class to identify robots by useragentstrings
*
* @package Papaya-Library
* @subpackage Session
*/
class base_robots extends base_object {

  var $agents = array(
    'Lynx', 'FirePHP'
  );

  var $robots = array(
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
    'nestListener', 'nestReader', 'netforex.org', 'noyona', 'oegp', 'ozelot',
    'page_verifier', 'panscient.com', 'papaya-Benchmarking-Tool', 'penthesila',
    'penthesilea', 'petitsage.fr', 'playstarmusic.com', 'pmafind',
    'pressemitteilung.ws', 'psbot', 'puf', 'redax', 'reifier', 'riba-it.de',
    'seo.ag', 'sitecheck.internetseer.com', 'spider', 'suchbaer.de', 'sun4u',
    'szukaj', 'teoma', 'thumbshots.de', 'topicblogs', 'troovziBot',
    'vias.ncsa.uiuc.edu', 'voyager', 'w3development', 'w3mir', 'webbot',
    'webcollage', 'webmeasurement-bot', 'www.adressendeutschland.de',
    'www.anonymous.com', 'www.walhello.com', 'wwwster', 'yacy.net', 'yahoo.com',
    'zero-knowledge', 'flash mediaserver');

  /**
  * Singleton function: get a previous instance or create one if none exists.
  * @return base_robots
  */
  function &getInstance() {
    static $robotChecker;
    if (isset($robotChecker) &&
        is_object($robotChecker) &&
        is_a($robotChecker, 'base_robots')) {
      return $robotChecker;
    } else {
      $robotChecker = new base_robots;
      return $robotChecker;
    }
  }

  /**
  * get list of known robots
  * @return array $this->robots list of robots
  */
  function getRobotsList() {
    return $this->robots;
  }

  /**
  * checks whether useragent is robot
  *
  * can be called without instantiating an object, use base_robots::checkRobot();
  *
  * @param string $userAgent useragent id string to check,
  *               uses $_SERVER['HTTP_USER_AGENT'] if empty
  * @return boolean TRUE if useragent is a known robot, else FALSE
  */
  function checkRobot($userAgent = NULL) {
    if (empty($userAgent) &&
        isset($_SERVER['HTTP_USER_AGENT']) &&
        $_SERVER['HTTP_USER_AGENT'] != '') {
      $userAgent = $_SERVER['HTTP_USER_AGENT'];
    }
    $robotObj = &base_robots::getInstance();
    if (!empty($userAgent)) {
      if (isset($robotObj->agents) &&
          is_array($robotObj->agents)) {
        foreach ($robotObj->agents as $pattern) {
          if (strpos($userAgent, $pattern) !== FALSE) {
            return FALSE;
          }
        }
      }
      if (isset($robotObj->robots) &&
          is_array($robotObj->robots)) {
        foreach ($robotObj->robots as $pattern) {
          if (strpos($userAgent, $pattern) !== FALSE) {
            return TRUE;
          }
        }
      }
    }
    return FALSE;
  }

}
