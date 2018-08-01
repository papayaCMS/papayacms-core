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

use Papaya\Application;
use Papaya\Cache;
use Papaya\Content;
use Papaya\Controller;
use Papaya\Request;

/**
 * Some of the old bootstraps use the this class/file as the starting point,
 * they need to be changed and use ../core.php. For BC validate that
 * the Autoloader exists and include it otherwise.
 */
if (!class_exists('\\Papaya\\Autoloader', FALSE)) {
  if (!defined('PAPAYA_INCLUDE_PATH')) {
    define('PAPAYA_INCLUDE_PATH', dirname(__DIR__).'/');
  }
  include_once PAPAYA_INCLUDE_PATH.'system/Papaya/Autoloader.php';
  spl_autoload_register(\Papaya\Autoloader::class.'::load');
}

define('PAPAYA_PAGE_ERROR_UNKNOWN', 0);
define('PAPAYA_PAGE_ERROR_DATABASE', 1);
define('PAPAYA_PAGE_ERROR_ACCESS', 2);
define('PAPAYA_PAGE_ERROR_PATH', 3);

define('PAPAYA_PAGE_ERROR_PAGE', 101);
define('PAPAYA_PAGE_ERROR_PAGE_PUBLIC', 102);
define('PAPAYA_PAGE_ERROR_PAGE_CONTENT', 103);
define('PAPAYA_PAGE_ERROR_PAGE_DOMAIN', 104);
define('PAPAYA_PAGE_ERROR_PAGE_RECURSION', 105);

define('PAPAYA_PAGE_ERROR_BOX', 201);
define('PAPAYA_PAGE_ERROR_BOX_CONTENT', 202);

define('PAPAYA_PAGE_ERROR_VIEW', 301);
define('PAPAYA_PAGE_ERROR_OUTPUT', 302);
define('PAPAYA_PAGE_ERROR_OUTPUT_SETTINGS', 303);

define('PAPAYA_PAGE_ERROR_IMAGE', 401);
define('PAPAYA_PAGE_ERROR_IMAGE_CREATE', 402);
define('PAPAYA_PAGE_ERROR_IMAGE_ACCESS', 403);

define('PAPAYA_PAGE_ERROR_MEDIA', 501);
define('PAPAYA_PAGE_ERROR_MEDIA_ACCESS', 502);
define('PAPAYA_PAGE_ERROR_MEDIA_FILE', 503);
define('PAPAYA_PAGE_ERROR_MEDIA_THUMBNAIL', 504);
define('PAPAYA_PAGE_ERROR_MEDIA_READ', 505);

/**
* Output page
*
* @package Papaya
* @subpackage Frontend
*/
class papaya_page extends base_object {

  /**
   * @var int
   */
  public $versionDateTime;

  /**
   * @var bool
   */
  public $readOnlySession = FALSE;

  /**
   * @var \Papaya\Content\Language
   */
  public $contentLanguage;

  /**
   * @var string
   */
  public $statisticRequestId = NULL;

  /**
   * @var int
   */
  private $boxId = 0;

  /**
   * @var NULL|bool
   */
  private $_isPreview = NULL;

  /**
  * ID of topic
  * @var integer $topicId
  */
  var $topicId = 0;

  /**
  * topic object
  * @var papaya_topic $topic
  */
  var $topic = NULL;

  /**
  * Ouput filter manager, instance of
  * @var papaya_output $output
  */
  var $output = NULL;

  /**
  * Layout
  *
  * @var \Papaya\Template $layout
  */
  var $layout = NULL;

  /**
  * Domain handler (vhosts for papayaCMS)
  * @var base_domains $domains
  */
  var $domains = NULL;

  /**
   * The id of the current domain, used from other objects to load domain data later.
   * @var integer
   */
  private $_currentDomainId = 0;

  /**
  * error data
  * array(status => http status, msg => string message, code => error code)
  * @var NULL|array
  */
  var $error = NULL;

  /**
  * redirect data
  * array(status => http status, url => target url)
  * @var NULL|array
  */
  var $redirect = NULL;

  /**
  * Output modus
  * @var string $mode
  */
  var $mode = 'html';

  /**
  * Request data array
  * @var array $requestData
  */
  var $requestData = array();

  /**
  * current visitor language identifier
  * @var string
  */
  var $visitorLanguage = '';

  /**
  * Accept .gz compressed files
  *
  * @var bool $acceptGzip
  */
  var $acceptGzip = FALSE;

  /**
  * Session Name Suffix
  * @var string
  */
  var $sessionName = '';

  /**
  * Global Session Params
  * @var array
  */
  var $sessionParams = array();

  /**
  * default status for pages is public
  *
   * @var bool
  */
  var $public = TRUE;

  /**
  * allow session for current output mode
  * @var integer
  */
  var $allowSession = \Papaya\Session::ACTIVATION_NEVER;
  /**
  * redirect to handle session id in path
   *
   * @var bool
  */
  var $allowSessionRedirects = FALSE;
  /**
  * session cache mode private or nocache
  * @var string
  */
  var $allowSessionCache = 'private';

  /**
  * current content language data
  * @var string
  */
  var $contentLanguageIdent;

  private $_pageDocument = NULL;
  private $_filterOptions = array();
  private $_boxesList = NULL;

  /**
   * @var base_outputfilter
   */
  public $filter = NULL;

  /**
  * execute page controller
  *
  * @access public
  */
  function execute() {
    Request\Log::getInstance();
    $application = $this->papaya();
    $application->registerProfiles(
      new Application\Profiles\Cms()
    );
    $application->profiler->start();
    if (!defined('PAPAYA_ADMIN_PAGE')) {
      define('PAPAYA_ADMIN_PAGE', FALSE);
    }
    if (!defined('PAPAYA_WEBSITE_REVISION')) {
      define('PAPAYA_WEBSITE_REVISION', '');
    }
    if (defined('PAPAYA_SESSION_NAME')) {
      $this->sessionName = PAPAYA_SESSION_NAME;
    }
    if (!isset($_SERVER['HTTP_HOST'])) {
      $_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'];
    }
    $options = $application->options;
    if (!$options->load()) {
      $controller = new Controller\Error\File($this);
      $controller->setStatus(503);
      $controller->setError('Service Unavailable', 'DATABASE');
      $controller->setTemplateFile($options->get('PAPAYA_ERRORDOCUMENT_503', ''));
      $controller->execute($application, $application->request, $application->response);
      $application->response->end();
    }

    $baseSessionDomain = $options->get('PAPAYA_SESSION_DOMAIN', '');
    $this->domains = new base_domains();
    $this->domains->handleDomain($application->request->languageId);
    $this->_currentDomainId = $this->domains->getCurrentId();
    $languageId = $this->domains->getCurrentLanguageId();
    $request = $application->request;
    if ($request->languageId !== $languageId) {
      $request->language(
        $this->papaya()->languages->getLanguage($languageId, Content\Languages::FILTER_IS_CONTENT)
      );
    }
    $options->defineDatabaseTables();
    $options->setupPaths();
    $application->messages->setUp($application->options);

    /* handle redirected errors */
    $redirectErrorCode = $request->getParameter(
      'redirect', 0, NULL, \Papaya\Request::SOURCE_QUERY
    );
    if (in_array($redirectErrorCode, array(403, 404, 500), FALSE)) {
      $message = $request->getParameter(
        'msg', '', NULL, \Papaya\Request::SOURCE_QUERY
      );
      $code = $request->getParameter(
        'code', 0, NULL, \Papaya\Request::SOURCE_QUERY
      );
      $options->defineConstants();
      $this->getError($redirectErrorCode, $message, $code);
      exit;
    }
    $this->requestData = base_object::parseRequestURI();
    if ($this->isPreview()) {
      $this->sessionName .= 'admin';
      define('PAPAYA_ADMIN_SESSION', TRUE);
      define('PAPAYA_SESSION_DOMAIN', $baseSessionDomain);
      if (
        $options->get('PAPAYA_UI_SECURE', FALSE) &&
        'https' !== $application->request->getUrl()->scheme
      ) {
        $url = $application->request->getUrl();
        $url->scheme = 'https';
        $this->doRedirect(301, $url->getUrl(), 'Secure administration');
      }
    } else {
      define('PAPAYA_ADMIN_SESSION', FALSE);
    }

    $this->initializeParams();
    if ($options->get('PAPAYA_CONTENT_LANGUAGE_COOKIE', FALSE) &&
        isset($_COOKIE) && is_array($_COOKIE) && isset($_COOKIE['lng']) &&
        preg_match('(^[a-zA-Z]+$)D', $_COOKIE['lng'])) {
      $this->visitorLanguage = $_COOKIE['lng'];
    }
    $this->initPageMode();
    /* exit script handling */
    if (isset($_GET['exit'])) {
      $this->allowSessionRedirects = FALSE;
      $this->startSession();
      $urlPattern = '(^/sid[a-z]+([a-zA-Z\d,-]{20,40})((?:/.*)|$))i';
      if (preg_match($urlPattern, $_SERVER['REQUEST_URI'], $regs)) {
        $this->doRedirectToPath(
          301,
          $regs[2].'?exit='.urlencode($_GET['exit']),
          FALSE,
          'External Link, Remove SID'
        );
      } else {
        $targetUrl = $_GET['exit'];
        $this->logRequestExitPage($targetUrl);
        $this->protectedRedirect(301, $targetUrl);
      }
    } else {
      $this->startSession();
      if ($this->isPreview()) {
        $previewDomain = $application->session->values['PAGE_PREVIEW_DOMAIN'];
        $this->sendHeader('X-Papaya-Preview-Domain: '.$previewDomain);
        if (!empty($previewDomain)) {
          $domainOptions = new \Papaya\Configuration\Storage\Domain($previewDomain);
          $application->options->load($domainOptions);
          if ($domainId = $domainOptions->domain()->id) {
            $this->_currentDomainId = $domainId;
          }
        }
      }
    }
    $options->defineConstants();
    /* redirect script handling */
    if (!empty($_GET['redirect'])) {
      $targetUrl = base_object::getAbsoluteURL(
        (string)$_GET['redirect'],
        empty($_GET['title']) ? '' : (string)$_GET['title']
      );
      $this->protectedRedirect(302, $targetUrl);
      exit;
    } elseif (!empty($_POST['redirect'])) {
      $targetUrl = base_object::getAbsoluteURL(
        (string)$_POST['redirect'],
        empty($_POST['title']) ? '' : (string)$_POST['title']
      );
      $this->protectedRedirect(302, $targetUrl);
      exit;
    }
    if ($options->get('PAPAYA_DEFAULT_HOST', '') != '' &&
        $options->get('PAPAYA_DEFAULT_HOST_ACTION', 0) > 0 &&
        strtolower($_SERVER['HTTP_HOST']) != strtolower($options->get('PAPAYA_DEFAULT_HOST'))) {
      $this->doRedirectToPath(
        301, $_SERVER['REQUEST_URI'], TRUE, 'Redirect To Default Host'
      );
    }
    $this->checkAcceptGzip();
  }

  /**
   * This method performs a protected redirect.
   *
   * 1) it checks whether the target URL contians the system URL
   * 2) it checks whether the referer contains the system URL
   * 3) it checks whether the target URL contains any of the configured domains
   * 4) if any of the above fails, a redirect page will be shown to inform the visitor
   *    about a possibly spoofed redirect
   *
   * @param integer $code the redirection status code to use
   * @param string $targetUrl the target url to redirect to
   */
  function protectedRedirect($code, $targetUrl) {
    if ($this->papaya()->options->get('PAPAYA_REDIRECT_PROTECTION', FALSE)) {
      $protocol = \PapayaUtilServerProtocol::get();
      $systemUrl = $protocol.'://'.strtolower(
        $this->papaya()->options->get(
          'PAPAYA_DEFAULT_HOST',
          empty($_SERVER['HTTP_HOST']) ? 'localhost' : ''
        )
      );
      if (FALSE !== strpos($targetUrl, "\n") &&
          FALSE !== strpos($targetUrl, "\r")) {
        //don't redirect to prevent header injection
        $this->sendHeader('X-Papaya-Status: redirect link contains newline');
      } elseif (0 === strpos($targetUrl, $systemUrl)) {
        //own hostname - just redirect
        $this->doRedirect($code, $targetUrl, 'Absolute Link (same domain)');
      } elseif (isset($_SERVER['HTTP_REFERER']) &&
                0 === strpos($_SERVER['HTTP_REFERER'], $systemUrl)) {
        //from own hostname - just redirect
        $this->doRedirect($code, $targetUrl, 'External Link (referer checked)');
      } else {
        $urlData = base_url_analyze::parseURL($targetUrl);
        if ($this->domains->load($urlData['host'], 0)) {
          $this->doRedirect($code, $targetUrl, 'External Link (domain checked)');
        } else {
          $this->getRedirect($code, $targetUrl);
        }
      }
    } else {
      $this->doRedirect($code, $targetUrl, 'Redirect Link');
    }
  }

  /**
  * Redirect to another path of the same installation
  *
  * @param integer $code
  * @param string $path
   * @param bool $defaultHost
  * @param string $reason
  */
  function doRedirectToPath($code, $path, $defaultHost = FALSE, $reason = NULL) {
    if ($defaultHost &&
        defined('PAPAYA_DEFAULT_HOST') &&
        PAPAYA_DEFAULT_HOST != '') {
      $host = PAPAYA_DEFAULT_HOST;
    } else {
      $host = $_SERVER['HTTP_HOST'];
    }
    $protocol = \PapayaUtilServerProtocol::get();
    $targetUrl = $protocol.'://'.strtolower($host).$path;
    $this->doRedirect($code, $targetUrl, $reason);
  }

  /**
  * Send a http redirect
  * @param integer $code
  * @param string $targetUrl
  * @param string $reason
  */
  function doRedirect($code, $targetUrl, $reason = NULL) {
    $response = new \Papaya\Response\Redirect(
      $targetUrl,
      ($code == 301) ? $code : 302,
      $reason
    );
    $response->send(FALSE);
    $this->papaya()->profiler->store();
    $response->end();
  }

  /**
  * Initialize parameters
  *
  * @access public
  */
  function initializeParams($sessionParamName = NULL) {
    if (isset($_REQUEST['tt']['page_id']) && $_REQUEST['tt']['page_id'] > 0) {
      $this->topicId = (int)$_REQUEST['tt']['page_id'];
    } elseif (isset($_REQUEST['p_id']) && $_REQUEST['p_id'] > 0) {
      $this->topicId = (int)$_REQUEST['p_id'];
    } elseif (isset($this->requestData['page_id']) &&
              $this->requestData['page_id'] > 0) {
      $this->topicId = (int)$this->requestData['page_id'];
      $_REQUEST['p_id'] = (int)$this->requestData['page_id'];
    } else {
      $aliasPage = $this->checkAlias();
      if (TRUE === $aliasPage) {
        $this->topicId = -1;
      } elseif (FALSE === $aliasPage) {
        $this->topicId = $this->papaya()->options->get('PAPAYA_PAGEID_DEFAULT', 0);
      } else {
        $this->topicId = (int)$aliasPage;
      }
      $_REQUEST['p_id'] = $this->topicId;
    }
    if (isset($this->requestData['language'])) {
      $_REQUEST['language'] = $this->requestData['language'];
    }
    if (isset($_GET['__cms_box_id']) && $_GET['__cms_box_id'] > 0) {
      $this->boxId = (int)$_GET['__cms_box_id'];
    } else {
      $this->boxId = -1;
    }
  }

  /**
  * Check alias
  *
  * @return mixed Either FALSE when redirect status is 404, otherwise TRUE.
  * returns int page-ID of alias if the request URI is an alias.
  */
  function checkAlias() {
    $options = $this->papaya()->options;
    if (!empty($_SERVER['REDIRECT_PAPAYA_STATUS'])) {
      $status = (int)$_SERVER['REDIRECT_PAPAYA_STATUS'];
    } else {
      $status = empty($_SERVER['REDIRECT_STATUS']) ? 200 : (int)$_SERVER['REDIRECT_STATUS'];
    }
    if ($status != 404) {
      $pathData = $this->papaya()->request->getParameters(Request::SOURCE_PATH);
      $isPageMode = $pathData['mode'] == '' || $pathData['mode'] == 'page';
      $isStartPage = isset($pathData['is_startpage']) && $pathData['is_startpage'];
      if (!$isPageMode || $isStartPage) {
        return FALSE;
      }
    }
    $options->setupPaths();
    $requestURI = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    $publicFilesPath = $options->get('PAPAYA_PATH_PUBLICFILES', '');
    if (preg_match('(^/?([\\("]|\\\\"))', $requestURI)) {
      /* mac-ie css hack handling - path starts with ( or " */
      $this->sendHTTPStatus(200);
      exit;
    } elseif (preg_match('(^/?blank.gif)', $requestURI)) {
      /* ivw sends a relative location header for blank.gif.
         If a client does not support relative location headers,
         papaya will get the request and send a transparent 1px gif
      */
      $this->sendHTTPStatus(200);
      $this->sendHeader('Content-type: image/gif');
      $this->sendHeader('Expires: '.gmdate('D, d M Y H:i:s', (time() + 2592000)).' GMT');
      // @codingStandardsIgnoreStart
      printf(
        '%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%',
        71, 73, 70, 56, 57, 97, 1, 0, 1, 0, 128, 255, 0, 192, 192, 192, 0, 0, 0, 33,
        249, 4, 1, 0, 0, 0, 0, 44, 0, 0, 0, 0, 1, 0, 1, 0, 0, 2, 2, 68, 1, 0, 59
      );
      // @codingStandardsIgnoreEnd
      exit;
    } elseif (preg_match('(favicon\.ico)i', $requestURI)) {
      /* if we get an error for a favicon.ico - let's look in the theme */
      $webFile = 'papaya-themes/'.$options->get('PAPAYA_LAYOUT_THEME', 'default').'/favicon.ico';
      $localFile = $this->getBasePath(TRUE).$webFile;
      if (file_exists($localFile) && is_readable($localFile)) {
        $fileData = array(
          'file_name' => 'favicon.ico',
          'file_size' => filesize($localFile),
          'mimetype' => 'image/x-icon'
        );
        $this->sendHTTPStatus(200);
        papaya_file_delivery::outputFile($localFile, $fileData);
        exit;
      } else {
        $this->getError(
          404,
          'File/path '.$_SERVER['REQUEST_URI'].' not found',
          $options->get('PAPAYA_PAGE_ERROR_PATH', 0)
        );
      }
      exit;
    } elseif (!empty($publicFilesPath) && 0 === strpos($requestURI, $publicFilesPath)) {
      $options->defineConstants();
      $this->getMediaThumbFile(substr($requestURI, strrpos($requestURI, '/') + 1));
      exit;
    } else {
      $urlMounter = new base_urlmounter;
      $this->allowSessionRedirects = FALSE;
      if ($alias = $urlMounter->locate()) {
        switch ($alias[1]) {
        case 3 :
          $this->doRedirect(302, $alias[0], 'Alias Redirect');
          return TRUE;
        case 2 :
          if ($url = $urlMounter->executeAliasPlugin($alias[2])) {
            if (is_string($url)) {
              $url = $this->getAbsoluteUrl($url);
              if ($aliasUrl = $urlMounter->getAliasURL($url)) {
                $this->doRedirect(302, $aliasUrl, 'Alias Plugin Redirect');
              } else {
                $requestUrl = new \Papaya\Url($url);
                $request = new \Papaya\Request($this->papaya()->options);
                $request->load($requestUrl);
                $urlData = array(
                  'topic_id' => $request->getParameter(
                    'page_id', NULL, NULL, \Papaya\Request::SOURCE_PATH
                  ),
                  'lng_ident' => $request->getParameter(
                    'language', NULL, NULL, \Papaya\Request::SOURCE_PATH
                  ),
                  'viewmode_ext' => $request->getParameter(
                    'output_mode', NULL, NULL, \Papaya\Request::SOURCE_PATH
                  ),
                  'url_params' => $requestUrl->getQuery(),
                );
                return $this->setRequestFromAlias($urlData);
              }
            } elseif (is_array($url)) {
              return $this->setRequestFromAlias($url);
            } elseif ($url === TRUE) {
              //url was handled in plugin
              exit;
            }
          }
          break;
        case 1 :
          $this->sendHTTPStatus();
          $this->sendHeader('Content-type: text/html; charset=utf-8');
          echo $urlMounter->getOutput($alias[0], $alias[2]);
          exit;
        default :
          if ($aliasUrl = $urlMounter->getAliasURL($alias[0])) {
            $this->doRedirect(302, $aliasUrl, 'Alias Redirect');
          } else {
            //no redirect needed - put data into variables
            return $this->setRequestFromAlias($alias[2]);
          }
        }
      }
    }
    return TRUE;
  }

  /**
  * Initialize request data from alias configuration
  * @param array $alias
  * @return integer page id
  */
  function setRequestFromAlias($alias) {
    define('PAPAYA_ALIAS_PAGE', TRUE);
    $this->sendHTTPStatus();
    $application = $this->papaya();
    $parameterGroupSeparator = $application
      ->options
      ->get('PAPAYA_URL_LEVEL_SEPARATOR');
    $request = $application->request;
    $parameters = $request->getParameters(Request::SOURCE_QUERY);
    $query = new Request\Parameters\QueryString($parameterGroupSeparator);
    //get addtional parameters and merge them
    if (!empty($_SERVER['REDIRECT_QUERY_STRING'])) {
      $parameters->merge(
        $query->setString($_SERVER['REDIRECT_QUERY_STRING'])->values()
      );
    }
    if (!empty($alias['url_params'])) {
      $parameters->merge(
        $query->setString($alias['url_params'])->values()
      );
    }
    $reference = new \PapayaUiReferencePage();
    if (isset($alias['topic_id'])) {
      $reference->setPageId($alias['topic_id']);
    }
    if (isset($alias['lng_ident'])) {
      $reference->setPageLanguage($alias['lng_ident']);
    }
    if (isset($alias['viewmode_ext'])) {
      $reference->setOutputMode($alias['viewmode_ext']);
      $this->mode = $alias['viewmode_ext'];
    }
    $reference->setParameters($parameters);
    //exchange request object
    $request = new \Papaya\Request($application->options);
    $request->load(new \Papaya\Url($reference->get()));
    $request->setParameters(Request::SOURCE_QUERY, $parameters);
    $application->setObject(
      'Request', $request, \PapayaApplication::DUPLICATE_OVERWRITE
    );
    // bc stuff
    $_GET = $request->getParameters(Request::SOURCE_QUERY)->toArray();
    $requestData['page_id'] = $alias['topic_id'];
    $requestData['language'] = $alias['lng_ident'];
    $requestData['ext'] = $alias['viewmode_ext'];
    $this->requestData = \PapayaUtilArray::merge(
      $this->requestData,
      $requestData
    );
    return $alias['topic_id'];
  }

  /**
  * Convert a query string into an array
  * @param string $queryString
  * @return array
  */
  function queryStringToArray($queryString) {
    $query = new Request\Parameters\QueryString(
      $this->papaya()->options->get('PAPAYA_URL_LEVEL_SEPARATOR', '')
    );
    return $query->setString($queryString)->values()->toArray();
  }

  /**
  * Start new session
  *
  * @access public
  */
  function startSession() {
    $session = $this->papaya()->session;
    if ($this->isPreview()) {
      $startSession = \Papaya\Session::ACTIVATION_DYNAMIC;
    } elseif ($this->allowSession &&
              $this->papaya()->options->get('PAPAYA_SESSION_START', FALSE)) {
      $startSession = $this->allowSession;
    } else {
      $startSession = \Papaya\Session::ACTIVATION_NEVER;
    }
    $session->setName('sid'.$this->sessionName);
    $this->sendHeader('P3P: CP="NOI NID ADMa OUR IND UNI COM NAV"');
    if (\PapayaUtilServerAgent::isRobot()) {
      if ($redirect = $session->redirectIfNeeded()) {
        $redirect->send();
        exit;
      }
    } elseif (($startSession == \Papaya\Session::ACTIVATION_ALWAYS) ||
              ($startSession == \Papaya\Session::ACTIVATION_DYNAMIC && $session->id()->existsIn())) {
      if ($this->papaya()->options->get('PAPAYA_SESSION_START', FALSE) &&
          !$this->papaya()->options->get('PAPAYA_DB_CONNECT_PERSISTENT', FALSE)) {
        $this->output->databaseClose();
      }
      $fallback = $this->papaya()->options->get('PAPAYA_SESSION_ID_FALLBACK', 'rewrite');
      switch ($fallback) {
      case 'get' :
        $session->options()->fallback = \Papaya\Session\Options::FALLBACK_PARAMETER;
        break;
      case 'rewrite' :
        $session->options()->fallback = \Papaya\Session\Options::FALLBACK_REWRITE;
        break;
      default :
        if ($this->isPreview()) {
          $session->options()->fallback = \Papaya\Session\Options::FALLBACK_REWRITE;
        } else {
          $session->options()->fallback = \Papaya\Session\Options::FALLBACK_NONE;
        }
      }
      $session->options()->cache = $this->papaya()->options->get(
        'PAPAYA_SESSION_CACHE', \Papaya\Session\Options::CACHE_NONE
      );
      if ($redirect = $session->activate($this->allowSessionRedirects)) {
        $redirect->send();
        exit;
      }
      if ($this->readOnlySession) {
        $session->close();
      }
      $this->sessionParams = $session->values['PAPAYA_SESSION_PAGE_PARAMS'];
    }
  }

  /**
  * Set page option
  *
  * @param string $name
  * @param mixed $value
  * @access public
  */
  function setPageOption($name, $value) {
    if (isset($value)) {
      $this->sessionParams[$name] = $value;
    } else {
      unset($this->sessionParams[$name]);
    }
    $this->papaya()->session->setValue('PAPAYA_SESSION_PAGE_PARAMS', $this->sessionParams);
  }

  /**
  * Get page option
  *
  * @param string $name
  * @access public
  * @return mixed parameter value or NULL
  */
  function getPageOption($name) {
    if (is_array($this->sessionParams) &&
        isset($this->sessionParams[$name])) {
      return $this->sessionParams[$name];
    } else {
      return NULL;
    }
  }

  /**
  * Is access valid
  *
  * @param integer $topicId
  * @access public
   * @return bool
  */
  function validateAccess($topicId) {
    if ($this->isPreview()) {
      return $this->validateEditorAccess();
    } else {
      return ($this->papaya()->surfer->canView($topicId));
    }
  }

  /**
  * preview or part of current domain
  *
  * @access public
   * @return bool
  */
  function validateDomain() {
    if ($this->isPreview()) {
      return TRUE;
    } elseif (defined('PAPAYA_PAGEID_DOMAIN_ROOT') && PAPAYA_PAGEID_DOMAIN_ROOT > 0) {
      $result = $this->topic->hasParent(PAPAYA_PAGEID_DOMAIN_ROOT);
    } else {
      $result = TRUE;
    }
    if ($result && $this->topic->topic['topic_protocol'] > 0) {
      $protocol = \PapayaUtilServerProtocol::isSecure() ? 2 : 1;
      if ($protocol != $this->topic->topic['topic_protocol']) {
        $targetUrl = $this->topic->topic['topic_protocol'] == 2 ? 'https://' : 'http://';
        $targetUrl .= empty($_SERVER['HTTP_HOST']) ? 'localhost' : $_SERVER['HTTP_HOST'];
        $targetUrl .= empty($_SERVER['REQUEST_URI']) ? '/' : $_SERVER['REQUEST_URI'];
        $this->protectedRedirect('302', $targetUrl);
      }
    } elseif ($result && defined('PAPAYA_DEFAULT_PROTOCOL') && PAPAYA_DEFAULT_PROTOCOL > 0) {
      $protocol = \PapayaUtilServerProtocol::isSecure() ? 2 : 1;
      if ($protocol != PAPAYA_DEFAULT_PROTOCOL) {
        $targetUrl = PAPAYA_DEFAULT_PROTOCOL == 2 ? 'https://' : 'http://';
        $targetUrl .= empty($_SERVER['HTTP_HOST']) ? 'localhost' : $_SERVER['HTTP_HOST'];
        $targetUrl .= empty($_SERVER['REQUEST_URI']) ? '/' : $_SERVER['REQUEST_URI'];
        $this->protectedRedirect('302', $targetUrl);
      }
    }
    return $result;
  }

  /**
  * validate editor access (preview, debug outputs)
  *
  * @access public
   * @return bool
  */
  function validateEditorAccess() {
    $application = $this->papaya();
    $GLOBALS['PAPAYA_USER'] = $application->getObject('AdministrationUser');
    if (is_null($GLOBALS['PAPAYA_USER']->isValid)) {
      $GLOBALS['PAPAYA_USER']->initialize();
      $GLOBALS['PAPAYA_USER']->execLogin();
    }
    return ($GLOBALS['PAPAYA_USER']->isValid);
  }


  /**
  * set visitor language parameter cookie
  * @param string $lngString
  * @return void
  */
  function setVisitorLanguage($lngString) {
    if (defined('PAPAYA_CONTENT_LANGUAGE_COOKIE') && PAPAYA_CONTENT_LANGUAGE_COOKIE) {
      if (defined('PAPAYA_SESSION_DOMAIN') && PAPAYA_SESSION_DOMAIN != '') {
        $domain = PAPAYA_SESSION_DOMAIN;
      } else {
        $domain = NULL;
      }
      if (defined('PAPAYA_SESSION_PATH') && PAPAYA_SESSION_PATH != '') {
        $path = PAPAYA_SESSION_PATH;
      } else {
        $path = '/';
      }
      setcookie('lng', urlencode($lngString), NULL, $path, $domain);
    }
  }

  public function createPage() {
    if ($this->isPreview()) {
      return new papaya_topic();
    } else {
      return new papaya_publictopic();
    }
  }

  /**
  * Get cache identifier
  *
  * @access public
  * @return FALSE|string $cacheId
  */
  function getCacheId() {
    $definition = NULL;
    $pagePlugin = $this->papaya()->plugins->get(
      $this->topic->topic['TRANSLATION']['module_guid'],
      NULL,
      $this->topic->topic['TRANSLATION']['topic_content']
    );
    if (
      $pagePlugin &&
      (
        $boxesList = $this->getBoxes(
          $boxesId = $this->topic->getBoxesTopicId(),
          $boxGroupId = $this->topic->getBoxGroupsTopicId()
        )
      )
    ) {
      if ($pagePlugin instanceof \Papaya\Plugin\Cacheable) {
        $definition = $pagePlugin->cacheable();
      } elseif (isset($pagePlugin->cacheable) && $pagePlugin->cacheable == FALSE) {
        return FALSE;
      } elseif (method_exists($pagePlugin, 'getCacheId')) {
        $definition = new Cache\Identifier\Definition\Callback(array($pagePlugin, 'getCacheId'));
      } else {
        $definition = new Cache\Identifier\Definition\BooleanValue(TRUE);
      }
      $definition = new Cache\Identifier\Definition\Group(
        new Cache\Identifier\Definition\BooleanValue(\PapayaUtilRequestMethod::isGet()),
        new Cache\Identifier\Definition\Url(),
        new Cache\Identifier\Definition\Surfer(),
        new Cache\Identifier\Definition\Parameters('PAPAYA_SESSION_PAGE_PARAMS'),
        $definition,
        $debug = $boxesList->cacheable()
      );
      if ($status = $definition->getStatus()) {
        return 'page_'.$this->topicId.'_'.md5(serialize($status));
      }
    }
    return FALSE;
  }

  /**
  * Use generic output cache
  *
  * @access public
  * @return bool
  */
  function useCache() {
    $method = empty($_SERVER['REQUEST_METHOD']) ? '' : strtoupper($_SERVER['REQUEST_METHOD']);
    $agent = empty($_SERVER['HTTP_USER_AGENT']) ? '' : strtolower($_SERVER['HTTP_USER_AGENT']);
    return (
      !$this->isPreview() &&
      $method == 'GET' &&
      $this->papaya()->options->get('PAPAYA_CACHE_OUTPUT', FALSE) &&
      $this->papaya()->options->get('PAPAYA_CACHE_TIME_OUTPUT', 0) > 0 &&
      $agent != 'mnogosearch-dimensional'
    );
  }

  /**
   * Check if here is something that does not allow to use gzip
   *
   * @return bool
   */
  private function canUseGzip() {
    return (!headers_sent() && (ob_get_level() < 1 || ob_get_contents() == ''));
  }

  /**
   * Get cache
   *
   * @param integer $cacheId
   * @access public
   * @return bool
  */
  function getCache($cacheId) {
    if (defined('PAPAYA_CACHE_OUTPUT') && PAPAYA_CACHE_OUTPUT &&
        defined('PAPAYA_CACHE_TIME_OUTPUT') && PAPAYA_CACHE_TIME_OUTPUT > 0) {
      if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
        if ('"'.$cacheId.'"' == $_SERVER['HTTP_IF_NONE_MATCH'] ||
            $cacheId == $_SERVER['HTTP_IF_NONE_MATCH']) {
          if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
              time() < (
                strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) + (int)PAPAYA_CACHE_TIME_OUTPUT
              )) {
            $this->sendHTTPStatus(304);
            /* We set the session cookie earlier, thus set the lng cookie.
               This is just to be safe, so the client (e.g. a proxy) doesn't
               forget it from his cached version.
             */
            $this->setVisitorLanguage($this->topic->currentLanguage['identifier']);
            /* Etag, Expires and Cache-Control were set before and the client
               already has all the other headers from his cached version
             */
            exit;
          }
        }
      }
      $cache = \Papaya\Cache::getService($this->papaya()->options);
      $cacheIdGzip = $cacheId.'.gz';
      if ($this->acceptGzip &&
          defined('PAPAYA_COMPRESS_CACHE_OUTPUT') &&
          PAPAYA_COMPRESS_CACHE_OUTPUT) {
        //compressed output
        $contentGzip = $cache->read(
          'output',
          $this->topicId,
          '.'.$cacheIdGzip,
          PAPAYA_CACHE_TIME_OUTPUT
        );
        $mtime = $cache->created(
          'output',
          $this->topicId,
          '.'.$cacheIdGzip,
          PAPAYA_CACHE_TIME_OUTPUT
        );
        if ($contentGzip && FALSE !== $mtime && $this->canUseGzip()) {
          $this->sendHTTPStatus(200);
          $this->setVisitorLanguage($this->topic->currentLanguage['identifier']);
          $this->output->sendHeader();
          $this->sendHeader('Last-Modified: '.gmdate('D, d M Y H:i:s', $mtime).' GMT');
          $this->sendHeader('Content-Encoding: gzip');
          $this->sendHeader('X-Papaya-Gzip: yes');
          $this->sendHeader('X-Papaya-Cache: yes');
          $this->sendHeader('Content-Length: '.strlen($contentGzip));
          echo $contentGzip;
          flush();
          return TRUE;
        }
      } else {
        //uncompressed output
        $content = $cache->read(
          'output',
          $this->topicId,
          '.'.$cacheId,
          PAPAYA_CACHE_TIME_OUTPUT
        );
        $mtime = $cache->created(
          'output',
          $this->topicId,
          '.'.$cacheId,
          PAPAYA_CACHE_TIME_OUTPUT
        );
        if ($content && FALSE !== $mtime) {
          $this->sendHTTPStatus(200);
          $this->setVisitorLanguage($this->topic->currentLanguage['identifier']);
          $this->output->sendHeader();
          $this->sendHeader('Last-Modified: '.gmdate('D, d M Y H:i:s', $mtime).' GMT');
          $this->sendHeader('X-Papaya-Cache: yes');
          $this->sendHeader('Content-Length: '.strlen($content));
          echo $content;
          flush();
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Set cache
   *
   * @param integer $cacheId
   * @param integer $topicId
   * @param string $page
   * @access public
   * @return bool
   */
  function setCache($cacheId, $topicId, $page) {
    if (defined('PAPAYA_CACHE_OUTPUT') && PAPAYA_CACHE_OUTPUT &&
        defined('PAPAYA_CACHE_TIME_OUTPUT') && PAPAYA_CACHE_TIME_OUTPUT > 0) {
      $cache = \Papaya\Cache::getService($this->papaya()->options);
      if (defined('PAPAYA_COMPRESS_CACHE_OUTPUT') &&
          PAPAYA_COMPRESS_CACHE_OUTPUT) {
        $cache->write(
          'output',
          $topicId,
          '.'.$cacheId.'.gz',
          gzencode($page),
          PAPAYA_CACHE_TIME_OUTPUT
        );
      }
      $cache->write('output', $topicId, '.'.$cacheId, $page, PAPAYA_CACHE_TIME_OUTPUT);
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Initialize page mode
  *
  * @access public
  */
  function initPageMode() {
    $pathParameters = $this->papaya()->request->getParameters(Request::SOURCE_PATH);
    $this->versionDateTime = 0;
    if (isset($pathParameters['preview_time']) &&
              $pathParameters['preview_time'] > 0) {
      $this->versionDateTime = (int)$pathParameters['preview_time'];
    }
    if (!$this->isPreview()) {
      $this->versionDateTime = 0;
    }
    if (!empty($pathParameters['mode']) && $pathParameters['mode'] != 'page') {
      $pathParameters['output_mode'] = $pathParameters['mode'];
    } elseif (empty($pathParameters['output_mode'])) {
      $pathParameters['output_mode'] = 'html';
    }
    switch ($pathParameters['output_mode']) {
    case 'xml':
      $this->mode = 'xml';
      break;
    case 'php':
      $this->mode = $this->papaya()->options->get('PAPAYA_URL_EXTENSION', 'html');
      break;
    default:
      $this->mode = $pathParameters['output_mode'];
    }
    unset($this->output);
    $this->output = new papaya_output;
    $pageModes = array(
      'urls', 'status', 'thumb', 'thumbnail', 'media', 'popup', 'download', 'image', '.theme-wrapper'
    );
    if (
      (isset($this->mode) && in_array($this->mode, $pageModes))
    ) {
      $this->readOnlySession = ($this->mode != 'image');
      $this->allowSession = \Papaya\Session::ACTIVATION_DYNAMIC;
      $this->allowSessionRedirects = FALSE;
      $this->allowSessionCache = 'private';
    } elseif ($this->output->loadViewModeData($this->mode)) {
      $this->readOnlySession = FALSE;
      if ($this->isPreview()) {
        $pageStatus = new \Papaya\Content\Page\Status();
      } else {
        $pageStatus = new \Papaya\Content\Page\Publication\Status();
      }
      $pageStatus->load($this->topicId);
      if ($pageStatus->sessionMode == 0) {
        $this->allowSession = $this->papaya()->options->get(
          'PAPAYA_SESSION_ACTIVATION', \Papaya\Session::ACTIVATION_ALWAYS
        );
      } else {
        $this->allowSession = $pageStatus->sessionMode;
      }
      if ($this->allowSession != \Papaya\Session::ACTIVATION_NEVER) {
        if ($this->papaya()->options->get('PAPAYA_SESSION_CACHE') == 'nocache') {
          $this->allowSessionCache = 'nocache';
        } else {
          $this->allowSessionCache = 'private';
        }
        $this->allowSessionRedirects =
          (bool)$this->output->viewMode['viewmode_sessionredirect'];
        switch ($this->output->viewMode['viewmode_sessionmode']) {
        case 1 :
          //read only session
          $this->allowSession = \Papaya\Session::ACTIVATION_DYNAMIC;
          $this->readOnlySession = TRUE;
          break;
        case 2 :
          //no session
          $this->allowSession = \Papaya\Session::ACTIVATION_NEVER;
          $this->readOnlySession = TRUE;
          break;
        }
      }
    } else {
      $this->allowSession = \Papaya\Session::ACTIVATION_DYNAMIC;
      $this->readOnlySession = FALSE;
      $this->allowSessionRedirects = TRUE;
    }
  }

  /**
  * Get request result
  *
  * @access public
  * @return string '' default
  */
  function get() {
    $this->sendHeader('X-Generator: papaya CMS');
    $application = $this->papaya();
    $controllers = $this->createController($this->mode);
    $result = $controllers->execute(
      $application, $application->request, $application->response
    );
    if (TRUE !== $result) {
      $application->response->send();
    }
    $application->profiler->store();
  }

  private function createController($mode) {
    $controllers = new Controller\Group();
    switch ($mode) {
    case 'image':
      $controllers->add(new Controller\Image());
      break;
    case 'urls':
      $controllers->add(new Controller\Callback(array($this, 'getUrls')));
      break;
    case 'status':
      $controllers->add(new Controller\Callback(array($this, 'getStatus')));
      break;
    case 'thumb':
    case 'thumbnail':
      $controllers->add(new Controller\Callback(array($this, 'getMediaThumbFile')));
      break;
    case 'media':
      $controllers->add(new Controller\Callback(array($this, 'getMediaFile')));
      break;
    case 'popup':
      $controllers->add(new Controller\Callback(array($this, 'getMediaPopup')));
      break;
    case 'download':
      $controllers->add(new Controller\Callback(array($this, 'outputDownload')));
      break;
    case 'outputs' :
      $controllers->add(new Controller\Callback(array($this, 'getOutputs')));
      break;
    case 'xml':
      $controllers->add(new Controller\Callback(array($this, 'getXMLOutput')));
      break;
    case '.theme-wrapper' :
      $controllers->add(new Controller\Callback(array($this, 'getThemeFile')));
      break;
    default:
      $controllers->add(new Controller\Callback(array($this, 'getPageOutput')));
      break;
    }
    return $controllers;
  }

  public function getThemeFile() {
    $themeWrapperUrl = new \Papaya\Theme\Wrapper\Url();
    switch ($themeWrapperUrl->getMimetype()) {
    case 'text/javascript' :
    case 'text/css' :
      $themeWrapper = new \Papaya\Theme\Wrapper($themeWrapperUrl);
      $response = $themeWrapper->getResponse();
      $response->send(TRUE);
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Get URLs
  *
  * @access public
  */
  function getUrls() {
    $this->topic = $this->createPage();
    $map = new base_sitemap(
      $this->topic,
      array('root' => 0, 'format' => 'static', 'forstart' => 0, 'forend' => 999)
    );
    $this->sendHeader('Content-type: text/html');
    echo $map->getUrls();
  }

  /**
  * get the current status for the web page
  *
  * currently checks the database connection only
  *
  * @access public
  */
  function getStatus() {
    /** @var dbcon_base $database */
    $database = $this->papaya()->getObject('Database')->getConnector();
    $allStatus = TRUE;
    if (@$database->connect($this, TRUE)) {
      $status['DATABASE'] = TRUE;
    } else {
      $status['DATABASE'] = FALSE;
      $allStatus = FALSE;
    }
    if (@$database->connect($this, FALSE)) {
      $status['DATABASE_WRITE'] = TRUE;
    } else {
      $status['DATABASE_WRITE'] = FALSE;
      $allStatus = FALSE;
    }
    $paths = array(
      'PATH_CACHE' => 'PAPAYA_PATH_CACHE',
      'PATH_FILES' => 'PAPAYA_PATH_MEDIAFILES',
      'PATH_THUMBNAILS' => 'PAPAYA_PATH_THUMBFILES'
    );
    foreach ($paths as $name => $path) {
      $realPath = $this->papaya()->options->get($path, '');
      if (empty($realPath)) {
        $status[$name] = FALSE;
        $allStatus = FALSE;
        continue;
      }
      $directory = new \Papaya\File\System\Directory($realPath);
      if (!($status[$name] = $directory->isWriteable())) {
        $allStatus = FALSE;
      }
    }
    $this->sendHeader('Content-type: text/xml');
    $result = new \PapayaXmlDocument();
    $cms = $result->appendElement('cms', array('status' => $allStatus ? 'OK' : 'ERROR'));
    foreach ($status as $option => $value) {
      $cms->appendElement(
        'option', array('name' => $option, 'status' => ($value ? 'OK' : 'ERROR'))
      );
    }
    echo $result->saveXML();
  }


  /**
  * Get page xml
  *
  * @see papaya_xsl::xml
  * @access public
  * @return string xml or ''
  */
  function getPageXML() {
    $loaded = $this->topic->loadOutput(
      $this->topicId, $this->requestData['language'], $this->versionDateTime
    );
    if ($loaded) {
      if ($this->validateDomain()) {
        if ($this->topic->checkPublishPeriod($this->topicId)) {
          if ($this->validateAccess($this->topicId)) {
            $sandbox = $this->papaya()->messages->encapsulate(array($this, 'generatePage'));
            if (call_user_func($sandbox)) {
              $this->sendHTTPStatus();
              $this->sendHeader('Content-type: text/xml; charset=utf-8');
              return $this->layout->getXml();
            }
            $this->getError(500, 'Service Unavailable', PAPAYA_PAGE_ERROR_PAGE);
          } else {
            $this->getError(403, 'Access forbidden', PAPAYA_PAGE_ERROR_ACCESS);
          }
        } else {
          $this->getError(
            404,
            sprintf('Page #%d currently not published', $this->topicId),
            PAPAYA_PAGE_ERROR_PAGE_PUBLIC
          );
        }
      } else {
        $this->getError(
          404,
          'Page not in domain root',
          PAPAYA_PAGE_ERROR_PAGE_DOMAIN
        );
      }
    } else {
      $this->getError(404, 'Page not found', PAPAYA_PAGE_ERROR_PAGE);
    }
    return '';
  }

  /**
  * get page meta data only
  *
  * @access public
  * @return string xml or ''
  */
  function getPageMetaXML() {
    $loaded = $this->topic->loadOutput(
      $this->topicId, $this->requestData['language'], $this->versionDateTime
    );
    if ($loaded) {
      if ($this->validateDomain()) {
        if ($this->topic->checkPublishPeriod($this->topicId)) {
          if ($this->validateAccess($this->topicId)) {
            $sandbox = $this->papaya()->messages->encapsulate(array($this, 'generatePage'));
            if (call_user_func($sandbox)) {
              $this->sendHTTPStatus();
              $this->sendHeader('Content-type: text/xml; charset=utf-8');
              return $this->layout->getXml();
            }
            $this->getError(500, 'Service Unavailable', PAPAYA_PAGE_ERROR_PAGE);
          } else {
            $this->getError(403, 'Access forbidden', PAPAYA_PAGE_ERROR_ACCESS);
          }
        } else {
          $this->getError(
            404,
            sprintf('Page #%d currently not published', $this->topicId),
            PAPAYA_PAGE_ERROR_PAGE_PUBLIC
          );
        }
      } else {
        $this->getError(
          404,
          'Page not in domain root',
          PAPAYA_PAGE_ERROR_PAGE_DOMAIN
        );
      }
    } else {
      $this->getError(404, 'Page not found', PAPAYA_PAGE_ERROR_PAGE);
    }
    return '';
  }

  /**
  * Get page
  *
  * @access public
  * @return string $str or ''
  */
  function getPage() {
    $this->contentLanguage = $this->topic->currentLanguage;
    $requestedLanguage = $this->papaya()->request->getParameter('language', '');
    if (
      !$this->isPreview() &&
      !empty($requestedLanguage) &&
      (
        $this->contentLanguage['id'] != $this->papaya()->request->languageId ||
        $this->contentLanguage['identifier'] != $requestedLanguage
      )
    ) {
      $url = $this->getAbsoluteURL(
        $this->getWebLink(
          NULL,
          $this->contentLanguage['identifier'],
          NULL,
          $this->papaya()->getObject('Request')->getParameters(
            \Papaya\Request::SOURCE_QUERY
          ),
          NULL,
          $this->topic->topic['TRANSLATION']['topic_title']
        )
      );
      $this->doRedirect(
        301,
        $url,
        'Invalid language'
      );
    }
    if ($this->validateDomain()) {
      if ($this->topic->checkPublishPeriod($this->topicId)) {
        if ($this->validateAccess($this->topicId)) {
          $viewId = $this->topic->getViewId();
          if ($viewId > 0) {
            if ($this->filter = $this->output->getFilter($viewId)) {
              $sandbox = $this->papaya()->messages->encapsulate(array($this, 'generatePage'));
              if (!call_user_func($sandbox, $this->filter->data)) {
                $this->getError(500, 'Service Unavailable', PAPAYA_PAGE_ERROR_PAGE);
                return '';
              }
              if ($this->filter->checkConfiguration()) {
                $str = $this->filter->parsePage($this->topic, $this->layout);
                $this->sendHTTPStatus();
                $this->sendHeader('Last-modified: '.gmdate('D, d M Y H:i:s').' GMT');
                $this->output->sendHeader();
                $application = $this->papaya();
                $application->session->close();
                if ($application->options->get('PAPAYA_LOG_RUNTIME_REQUEST', FALSE)) {
                  Request\Log::getInstance()->logTime('Page generated');
                  Request\Log::getInstance()->emit(FALSE);
                }
                $response = $this->papaya()->response;
                $response->sendHeader('X-Papaya-Cache: no');
                if ($this->acceptGzip  &&
                    $this->papaya()->options->get('PAPAYA_COMPRESS_OUTPUT', FALSE) &&
                    $this->canUseGzip()) {
                  $response->sendHeader('Content-Encoding: gzip');
                  $response->sendHeader('X-Papaya-Gzip: yes');
                  $response->content(new \Papaya\Response\Content\Text(gzencode($str)));
                } else {
                  $this->sendHeader('X-Papaya-Gzip: disabled');
                  $response->content(new \Papaya\Response\Content\Text((string)$str));
                }
                if ($application->options->get('PAPAYA_LOG_RUNTIME_REQUEST', FALSE)) {
                  Request\Log::getInstance()->logTime('Page delivered');
                }
                $response->send();
                $this->logRequest($this->topic->getContentLanguageId());
                return $str;
              } else {
                $this->getError(
                  $this->filter->errorStatus,
                  'Output mode "'.
                    papaya_strings::escapeHTMLChars(basename($this->mode)).
                    '"for page #'.$this->topicId.
                    ' not available: '.$this->filter->errorMessage,
                  PAPAYA_PAGE_ERROR_OUTPUT_SETTINGS
                );
              }
            } else {
              $this->getError(
                500,
                'Output mode "'.
                  papaya_strings::escapeHTMLChars(basename($this->mode)).'" for page #'.
                  $this->topicId.' not found',
                PAPAYA_PAGE_ERROR_OUTPUT
              );
            }
          } else {
            $this->getError(
              500,
              'View "'.
                papaya_strings::escapeHTMLChars(basename($this->mode)).'" for page #'.
                $this->topicId.' not found',
              PAPAYA_PAGE_ERROR_VIEW
            );
          }
        } else {
          $this->getError(403, 'Access forbidden', PAPAYA_PAGE_ERROR_ACCESS);
        }
      } else {
        $this->getError(
          404,
          sprintf('Page #%d currently not published', (int)$this->topicId),
          PAPAYA_PAGE_ERROR_PAGE_PUBLIC
        );
      }
    } else {
      $this->getError(
        404,
        'Page not in domain root',
        PAPAYA_PAGE_ERROR_PAGE_DOMAIN
      );
    }
    return FALSE;
  }

  /**
  * Send out the Expires and Cache-Control header.
  *
  * @param integer $expires time until the request should expire in the browser
  */
  private function sendCacheHeaders($expires) {
    if ($expires > 0) {
      $this->sendHeader(
        sprintf(
          'Expires: %s GMT',
          gmdate('D, d M Y H:i:s', time() + (int)$expires)
        )
      );
      $this->sendHeader(
        sprintf(
          'Cache-Control: %s, max-age=%d, pre-check=%d, no-transform',
          $this->papaya()->session->isActive() ? 'private' : 'public',
          $expires,
          $expires
        )
      );
      $this->sendHeader('Pragma: ');
    } else {
      $this->sendHeader(
        sprintf(
          'Expires: %s GMT',
          gmdate('D, d M Y H:i:s', time() - 5184000)
        )
      );
      $this->sendHeader(
        'Cache-Control: no-store, no-cache, must-revalidate, no-transform'
      );
    }
  }

  /**
  * check url path level difference between cms root and request url
  *
  * @return string|FALSE return root url if it is different
  */
  function checkURLPathLevel() {
    $pageUrl = $this->getWebLink();
    if (FALSE !== strpos($pageUrl, '/')) {
      return $this->getAbsoluteUrl($pageUrl);
    }
    return FALSE;
  }

  /**
   * Generate page
   *
   * @access public
   * @param null $filterParams
   * @param bool $outputContent
   * @param bool $allowRedirect
   * @return string
   */
  function generatePage($filterParams = NULL, $outputContent = TRUE, $allowRedirect = TRUE) {
    $this->layout = new \PapayaTemplateXslt();

    $defaultViewMode = $this->papaya()->options->get(
      'PAPAYA_URL_EXTENSION', 'html', new \Papaya\Filter\NotEmpty()
    );
    if (isset($this->output->viewMode) && !empty($this->output->viewMode['viewmode_ext'])) {
      $currentViewMode = $this->output->viewMode['viewmode_ext'];
    } else {
      $currentViewMode = $defaultViewMode;
    }
    $filterParams['viewmode'] = $currentViewMode;
    $this->_filterOptions = $filterParams;
    if ($outputContent) {
      $document = $this->getPageDocument();
      if ($document->documentElement) {
        $this->layout->add($this->getPageDocument(), 'content');
      }
      if (!(defined('PAPAYA_ALIAS_PAGE') && PAPAYA_ALIAS_PAGE) &&
          $allowRedirect &&
          (
           $url = $this->topic->checkURLFileName(
             $this->papaya()->request->getParameter(
               'page_title', '', NULL, \Papaya\Request::SOURCE_PATH
             ),
             $filterParams['viewmode']
           )
         )) {
        $this->doRedirect(301, $url, 'URL Title Fixation');
      } elseif ($allowRedirect && $url = $this->checkURLPathLevel()) {
        $this->doRedirect(301, $url, 'URL Path Fixation');
      }
      $this->layout->add($this->topic->getMetaInfos(), 'meta');
    }
    $this->layout->add(
      $this->output->getViewsList(
        $this->topic->getViewId(),
        $this->topic->topicId,
        $this->topic->topic['TRANSLATION']['topic_title']
      ),
      'views'
    );
    $this->layout->add(
      $this->topic->getTranslationsData($this->topic->topic['TRANSLATION']['lng_id']),
      'translations'
    );
    $this->setVisitorLanguage($this->topic->currentLanguage['code']);
    if ($outputContent) {
      $serverUrl = \PapayaUtilServerProtocol::get().'://'.\PapayaUtilServerName::get();
      $url = strtr(
        $serverUrl.$this->papaya()->options->get('PAPAYA_PATH_WEB', '/'),
        '\\',
        '/'
      );
      $this->layout->parameters()->set('PAGE_BASE_URL', $url);
      if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '?') > 0) {
        $url .= substr($_SERVER['REQUEST_URI'], 1, strpos($_SERVER['REQUEST_URI'], '?') - 1);
      } elseif (!empty($_SERVER['REQUEST_URI'])) {
        $url .= substr($_SERVER['REQUEST_URI'], 1);
      }
      $this->layout->parameters()->set('PAGE_URL', $url);
      $this->layout->parameters()->set(
        'PAGE_REQUEST_QUERYSTRING', $this->papaya()->request->url->getQuery()
      );
      $this->layout->parameters()->set(
        'PAGE_REQUEST_METHOD', $this->papaya()->request->getMethod()
      );
      $this->layout->parameters()->set(
        'PAGE_REQUEST_URL', (string)$this->papaya()->request->url
      );
      $this->layout->parameters()->set(
        'PAGE_TITLE',
        $this->topic->topic['TRANSLATION']['topic_title']
      );
      if (defined('PAPAYA_WEBSITE_REVISION') && trim(PAPAYA_WEBSITE_REVISION) != '') {
        $this->layout->parameters()->set('PAPAYA_WEBSITE_REVISION', PAPAYA_WEBSITE_REVISION);
        $this->layout->parameters()->set('PAGE_WEBSITE_REVISION', PAPAYA_WEBSITE_REVISION);
      }
      $themeHandler = new \Papaya\Theme\Handler();
      $this->layout->parameters()->set('PAGE_THEME', $themeHandler->getTheme());
      $this->layout->parameters()->set('PAGE_THEME_SET', $themeHandler->getThemeSet());
      $this->layout->parameters()->set('PAGE_THEME_PATH', $themeHandler->getUrl());
      $this->layout->parameters()->set('PAGE_THEME_PATH_LOCAL', $themeHandler->getLocalThemePath());
      $this->layout->parameters()->set(
        'PAGE_WEB_PATH', $this->papaya()->options->get('PAPAYA_PATH_WEB', '/')
      );
      $this->layout->parameters()->set(
        'PAGE_LANGUAGE',
        $this->topic->currentLanguage['code']
      );
      $this->layout->parameters()->set('PAGE_MODE_PUBLIC', $this->isPreview() ? 0 : 1);
      $this->layout->parameters()->set('PAGE_OUTPUTMODE_CURRENT', $currentViewMode);
      $this->layout->parameters()->set('PAGE_OUTPUTMODE_DEFAULT', $defaultViewMode);
      $this->layout->parameters()->set(
        'PAGE_URL_LEVEL_SEPARATOR',
        $this->papaya()->options->get('PAPAYA_URL_LEVEL_SEPARATOR', '')
      );
      $this->layout->parameters()->set('PAPAYA_TIME_OFFSET', date('O'));
      $this->layout->parameters()->set(
        'PAPAYA_DBG_DEVMODE',
        $this->papaya()->options->get('PAPAYA_DBG_DEVMODE', FALSE)
      );
      $this->layout->parameters()->set(
        'PAPAYA_DEBUG_LANGUAGE_PHRASES',
        $this->papaya()->options->get('PAPAYA_DEBUG_LANGUAGE_PHRASES', FALSE)
      );
      if (empty($_SERVER['HTTP_USER_AGENT']) ||
          $_SERVER['HTTP_USER_AGENT'] != 'mnogosearch-dimensional') {
        $boxesList = $this->getBoxes(
          $boxesId = $this->topic->getBoxesTopicId(),
          $boxGroupId = $this->topic->getBoxGroupsTopicId()
        );
        if ($boxesList) {
          $data = $boxesList->parsed(
            $boxesId,
            $this->topic->topic['TRANSLATION']['lng_id'],
            empty($this->output->viewMode['viewmode_id'])
              ? 0 : (int)$this->output->viewMode['viewmode_id']
          );
          if ($data) {
            $this->layout->add($data, 'boxes');
          }
        }
        unset($boxesList);
      }
      $this->topic->databaseClose();
    }
    return TRUE;
  }

  /**
   * Get the boxes list for the current page, load them if not already loaded
   */
  public function getBoxes($boxPageId = NULL, $boxGroupPageId = NULL) {
    if (!isset($this->_boxesList)) {
      if ($boxPageId || $boxGroupPageId) {
        if ($this->isPreview()) {
          $this->_boxesList = new base_boxeslinks($this->topic);
        } else {
          $this->_boxesList = new papaya_public_boxeslinks($this->topic);
        }
        $this->_boxesList->setPageId($boxPageId, $boxGroupPageId);
        $this->_boxesList->load(
          $this->topic->topic['TRANSLATION']['lng_id'],
          empty($this->output->viewMode['viewmode_id'])
            ? 0 : (int)$this->output->viewMode['viewmode_id']
        );
      } else {
        return FALSE;
      }
    }
    return $this->_boxesList;
  }

  /**
   * Returns the page xml document for the current request,
   * if document not yet generated, this will trigger it.
   *
   * @return DOMDocument
   */
  public function getPageDocument() {
    if (NULL === $this->_pageDocument) {
      $this->_pageDocument = new \PapayaXmlDocument();
      $xml = \PapayaUtilStringXml::repairEntities(
        $this->topic->parseContent(TRUE, $this->_filterOptions)
      );
      if (!empty($xml)) {
        $errors = new \PapayaXmlErrors();
        $errors->activate();
        try {
          $this->_pageDocument->loadXml($xml);
          $errors->emit();
          $errors->deactivate();
        } catch (\PapayaXmlException $e) {
          $message = new \Papaya\Message\Log(
            \Papaya\Message\Logable::GROUP_SYSTEM,
            \Papaya\Message::SEVERITY_ERROR,
            $e->getMessage()
          );
          $message->context()->append(
            new \Papaya\Message\Context\Text($xml)
          );
          $this->papaya()->messages->dispatch($message);
          $errors->deactivate();
        }
      }
    }
    return $this->_pageDocument;
  }

  /**
  * Get xml wrapped output for a box without appling a template
  * @return string
  */
  function getBoxXML() {
    if (!$this->topic->topicExists($this->topicId)) {
      $this->topicId = $this->papaya()->options->get('PAPAYA_PAGEID_DEFAULT', 0);
    }
    $this->topic->loadOutput(
      $this->topicId, $this->requestData['language'], $this->versionDateTime
    );
    $boxes = new papaya_boxes;
    if ($boxes->load($this->boxId, $this->topic->topic['TRANSLATION']['lng_id'])) {
      $result = $boxes->parsedBox($this->topic);
      if (!\PapayaXmlDocument::createFromXml($result, TRUE)) {
        $result = '<box><![CDATA['.str_replace(']]>', ']]&gt;', $result).']]></box>';
      }
      return $result;
    }
    return '';
  }

  /**
  * Get box output after appling template if needed
  *
  * @return string|FALSE
  */
  function getBox() {
    if (!$this->topic->topicExists($this->topicId)) {
      $this->topicId = $this->papaya()->options->get('PAPAYA_PAGEID_DEFAULT', 0);
    }
    $this->topic->loadOutput(
      $this->topicId, $this->requestData['language'], $this->versionDateTime
    );
    if ((isset($this->output->viewMode) && !empty($this->output->viewMode['viewmode_ext'])) ||
        $this->output->loadViewModeData($this->mode)) {
      if ($this->isPreview()) {
        $boxes = new base_boxes;
      } else {
        $boxes = new base_boxes_public;
      }
      $loaded = $boxes->load(
        $this->boxId, $this->papaya()->request->languageId
      );
      if ($loaded) {
        if ($this->validateDomain() &&
            $this->topic->checkPublishPeriod($this->topicId) &&
            $this->validateAccess($this->topicId)) {
          /**
           * @var \Papaya\Response $response
           */
          $response = $this->papaya()->response;
          $response->setCache(
            $this->papaya()->session->isActive() ? 'private' : 'public',
            $boxes->getBoxBrowserCacheTime($boxes->box)
          );
          $viewId = $boxes->getViewId();
          $useFilter = empty($boxes->box['TRANSLATION']['module_useoutputfilter'])
            ? FALSE : $boxes->box['TRANSLATION']['module_useoutputfilter'];
          if ($viewId > 0) {
            if ($this->filter = $this->output->getFilter($viewId)) {
              if (!$useFilter) {
                $str = $boxes->parsedBox($this->topic);
                $this->sendHeader('Last-modified: '.gmdate('D, d M Y H:i:s').' GMT');
                $this->output->sendHeader();
                $this->papaya()->session->close();
                return $str;
              } elseif ($this->filter->checkConfiguration()) {
                $xmlString = $boxes->parsedBox(
                  $this->topic,
                  $this->filter->data
                );
                if (!empty($xmlString)) {
                  $str = $this->filter->parseBox($this->topic, $boxes->box, $xmlString);
                } else {
                  $str = FALSE;
                }
                if (FALSE === $str) {
                  $this->papaya()->session->close();
                  echo $this->filter->errorMessage;
                  return FALSE;
                } else {
                  $this->sendHeader('Last-modified: '.gmdate('D, d M Y H:i:s').' GMT');
                  $this->output->sendHeader();
                  $this->papaya()->session->close();
                  return $str;
                }
              } else {
                $this->getError(
                  $this->filter->errorStatus,
                  'Output mode "'.
                    papaya_strings::escapeHTMLChars(basename($this->mode)).
                    '"for page #'.$this->topicId.
                    ' not available: '.$this->filter->errorMessage,
                  PAPAYA_PAGE_ERROR_OUTPUT_SETTINGS
                );
              }
            } else {
              $this->getError(
                404,
                'Output mode "'.
                  papaya_strings::escapeHTMLChars(basename($this->mode)).'" for page #'.
                  $this->topicId.' not found',
                PAPAYA_PAGE_ERROR_OUTPUT
              );
            }
          } else {
            $this->getError(
              404,
              'View "'.
                papaya_strings::escapeHTMLChars(basename($this->mode)).'" for page #'.
                $this->topicId.' not found',
              PAPAYA_PAGE_ERROR_VIEW
            );
          }
        } else {
          $this->getError(
            403,
            'Access forbidden',
            PAPAYA_PAGE_ERROR_ACCESS
          );
        }
      } else {
        $this->getError(
          404,
          'Box #"'.(int)$this->boxId.'" not found',
          PAPAYA_PAGE_ERROR_BOX
        );
      }
    } else {
      $this->getError(
        404,
        'Output mode "'.$this->mode.'" not found',
        PAPAYA_PAGE_ERROR_OUTPUT
      );
    }
    return FALSE;
  }

  /**
   * Execute output controller
   *
   * @param $controller
   * @return bool Valid content | Error
   */
  function executeController($controller) {
    if (!$controller instanceof Controller\Group) {
      $controller = new Controller\Group($controller);
    }
    $application = $this->papaya();
    $result = $controller->execute(
      $application, $application->request, $application->response
    );
    if ($result) {
      $application->response->send();
    }
  }

  /**
   * Load media file data
   *
   * @param integer $id
   * @param null $version
   * @access public
   * @return mixed array $data or boolean
   */
  function loadMediaFileData($id, $version = NULL) {
    $mediaDB = base_mediadb::getInstance();
    if ($fileData = $mediaDB->getFile($id, $version)) {
      return $fileData;
    }
    return FALSE;
  }

  /**
  * Get media popup
  *
  * @access public
  */
  function getMediaPopup() {
    if ($file = $this->checkMediaPerm($this->requestData['media_id'])) {
      if ($file['FILETYPE'] >= 1 && $file['FILETYPE'] <= 3) {
        if (empty($file['file_title']) || trim($file['file_title']) == '') {
          $caption = $file['file_title'];
        } else {
          $caption = $file['file_name'];
        }
        $this->sendHeader('Content-type: text/html');
        printf(
          '<html><head><title>%s</title></head><body style="padding: 0px; margin: 0px;">',
          papaya_strings::escapeHTMLChars($caption)
        );
        printf(
          '<img src="%s" style="border: none; margin: 0px;" alt="%s" />',
          'image.media.'.($this->isPreview() ? 'preview.' : '').$file['file_id'],
          papaya_strings::escapeHTMLChars($caption)
        );
        print('</body></html>');
      } else {
        $this->logRequest();
        $this->outputFile($file['FILENAME'], $file);
      }
      exit;
    }
  }

  /**
  * Get media file
  *
  * @access public
  */
  function getMediaFile() {
    if (isset($this->requestData['media_id'])) {
      if ($file = $this->checkMediaPerm($this->requestData['media_id'])) {
        $this->logRequest();
        $this->outputFile($file['FILENAME'], $file);
      } else {
        $this->getError(404, 'File not found', PAPAYA_PAGE_ERROR_MEDIA_FILE);
      }
    }
  }

  /**
  * Get media tumbnail file
  *
  * @access public
  */
  function getMediaThumbFile($mediaId = NULL) {
    if (empty($mediaId) || $mediaId instanceof \PapayaApplication) {
      $mediaId = empty($this->requestData['media_id']) ? NULL : $this->requestData['media_id'];
    }
    if (!empty($mediaId)) {
      $mediaFilePattern = '~^
        (
         (
           (?:.*?([a-fA-F\d]{32})) # title, mode and id
           v([0-9]+) # version
         )
         (?:\.[0-9a-zA-Z]+)?
         (?:_([a-z]+))?_ #resize mode
         (\d+)x(\d+) #resize size
         (?:_([\da-fA-F]{32}))? # optional params
        )
        (\.[\w\d]+)? # extension
        $~x';
      if (preg_match($mediaFilePattern, $mediaId, $matches)) {
        // is thumbnail
        if ($file = $this->checkMediaPerm($matches[2])) {
          $path = '';
          $depth = $this->papaya()->options->get('PAPAYA_MEDIADB_SUBDIRECTORIES', 1);
          for ($i = 0; $i < $depth; $i++) {
            $path .= $matches[1][$i].'/';
          }
          $fileName = $this->papaya()->options->get('PAPAYA_PATH_THUMBFILES').$path.$matches[1];
          if (isset($matches[9])) {
            $fileName .= $matches[9];
            $requestExt = strtolower($matches[9]);
          } else {
            $requestExt = '';
          }
          if (is_file($fileName)) {
            $file['file_size'] = filesize($fileName);
            if ($requestExt != '') {
              $fileNameExt = strrchr($file['file_name'], '.');
              $file['file_name'] = substr(
                $file['file_name'], 0, strlen($fileNameExt) * -1
              ).$requestExt;
              switch ($requestExt) {
              case '.gif' :
                $file['mimetype'] = 'image/gif';
                break;
              case '.jpg' :
                $file['mimetype'] = 'image/jpeg';
                break;
              case '.png' :
                $file['mimetype'] = 'image/png';
                break;
              }
            }
            if (isset($file['PUBLIC_FILE']) && $file['PUBLIC_FILE']) {
              $file['url'] = $this->_storageSetPublic(
                'thumbs',
                $matches[1].$matches[9],
                $file['mimetype']
              );
            }
            $this->logRequest();
            $this->outputFile($fileName, $file);
          } else {
            $this->getError(404, 'Thumbnail not found', PAPAYA_PAGE_ERROR_MEDIA_THUMBNAIL);
          }
        }
      } elseif ($file = $this->checkMediaPerm($mediaId)) {
        $this->logRequest();
        $this->outputFile($file['FILENAME'], $file);
      } else {
        $this->getError(404, 'Invalid FileId', PAPAYA_PAGE_ERROR_MEDIA);
      }
    } else {
      $this->getError(404, 'Empty FileId', PAPAYA_PAGE_ERROR_MEDIA);
    }
  }

  /**
  * Call setPublic() appropriately on the media file.
  *
  * @see \Papaya\Media\Storage\Service::setPublic
  * @param string $storageGroup
  * @param string $storageId
  * @param string $mimeType
  * @return string public url or empty
  */
  private function _storageSetPublic($storageGroup, $storageId, $mimeType) {
    $options = $this->papaya()->options;
    $storage = \Papaya\Media\Storage::getService(
      $options->get('PAPAYA_MEDIA_STORAGE_SERVICE'),
      $options
    );
    if (!$storage->isPublic($storageGroup, $storageId, $mimeType)) {
      $storage->setPublic(
        $storageGroup,
        $storageId,
        TRUE,
        $mimeType
      );
      if (!$storage->isPublic($storageGroup, $storageId, $mimeType)) {
        return '';
      }
    }
    return $storage->getUrl($storageGroup, $storageId, $mimeType);
  }

  /**
  * get mime type of a file name
  * @param $fileName
  * @return string
  */
  function getFileMimeType($fileName) {
    $mediaDB = base_mediadb::getInstance();
    $properties = $mediaDB->getFileProperties($fileName, '');
    return $properties['mimetype'];
  }


  /**
  * output download file
  *
  * @access public
  * @return mixed
  */
  function outputDownload() {
    if (isset($this->requestData['media_id'])) {
      if ($data = $this->checkMediaPerm($this->requestData['media_id'])) {
        $this->logRequest();
        $this->papaya()->session->close();
        if (papaya_file_delivery::outputDownload($data['FILENAME'], $data)) {
          return TRUE;
        } else {
          $this->getError(404, 'Cannot read file', PAPAYA_PAGE_ERROR_MEDIA_READ);
        }
      } else {
        $this->getError(403, 'Permission denied', PAPAYA_PAGE_ERROR_ACCESS);
      }
    }
    $this->getError(404, 'File not found', PAPAYA_PAGE_ERROR_MEDIA_FILE);
  }

  /**
  * This method generates an XML output showing the metadata of a topic, i.e.
  * which outputs exist for this page.
  */
  function getOutputs() {
    $this->topic = $this->createPage();
    if ($this->topic->topicExists($this->topicId)) {
      if ($str = $this->getPageMetaXML()) {
        $response = $this->papaya()->response;
        $response->sendHeader('Content-type: text/xml; charset: utf-8');
        $response->content(new \Papaya\Response\Content\Text($str));
        $response->send(TRUE);
      } else {
        $this->getError(
          404,
          'Page #'.(int)$this->topicId.' no content found',
          PAPAYA_PAGE_ERROR_PAGE_CONTENT
        );
      }
    } elseif ($this->topicId == -1) {
      $this->getError(
        404,
        'File/path '.$_SERVER['REDIRECT_URL'].' not found',
        PAPAYA_PAGE_ERROR_PATH
      );
    } else {
      $this->getError(
        404,
        'Page #'.(int)$this->topicId.' not found',
        PAPAYA_PAGE_ERROR_PAGE
      );
    }
  }

  /**
  * This method generates the XML representation of a page, i.e. the input of
  * the XSLT-template.
  */
  function getXMLOutput() {
    $this->topic = $this->createPage();
    if (!($this->validateEditorAccess())) {
      $this->getError(
        403,
        'Access forbidden',
        PAPAYA_PAGE_ERROR_ACCESS
      );
    } elseif (($this->boxId > 0)) {
      if ($str = $this->getBoxXML()) {
        $response = $this->papaya()->response;
        $response->setCache('nocache');
        $response->setContentType('text/xml', 'charset: utf-8');
        $response->content(new \Papaya\Response\Content\Text($str));
        $response->send(TRUE);
      } else {
        $this->getError(
          404,
          'Box #'.(int)$this->boxId.' no content found',
          PAPAYA_PAGE_ERROR_BOX_CONTENT
        );
      }
    } elseif ($this->topic->topicExists($this->topicId)) {
      if ($str = $this->getPageXML()) {
        $response = $this->papaya()->response;
        $response->setCache('nocache');
        $response->setContentType('text/xml', 'charset: utf-8');
        $response->content(new \Papaya\Response\Content\Text($str));
        $response->send(TRUE);
      } else {
        $this->getError(
          404,
          'Page #'.(int)$this->topicId.' no content found',
          PAPAYA_PAGE_ERROR_PAGE_CONTENT
        );
      }
    } elseif ($this->topicId == -1) {
      $this->getError(
        404,
        'File/path '.$_SERVER['REDIRECT_URL'].' not found',
        PAPAYA_PAGE_ERROR_PATH
      );
    } else {
      $this->getError(
        404,
        'Page #'.(int)$this->topicId.' not found',
        PAPAYA_PAGE_ERROR_PAGE
      );
    }
  }

  /**
  * This method generates the output of a page.
  */
  function getPageOutput() {
    if ($this->papaya()->options->get('PAPAYA_LOG_RUNTIME_REQUEST', FALSE)) {
      Request\Log::getInstance()->logTime('Page defined');
    }
    $this->domains->validateLanguage(
      $this->papaya()->request->languageId
    );
    $this->topic = $this->createPage();
    if ($this->boxId > 0) {
      /**
       * @var \Papaya\Response $response
       */
      $response = $this->papaya()->response;
      if ($output = $this->getBox()) {
        $response->content(new \Papaya\Response\Content\Text((string)$output));
      }
      $response->send();
      $response->end();
    } elseif ($this->topic->topicExists($this->topicId)) {
      $loaded = $this->topic->loadOutput(
        $this->topicId,
        $this->requestData['language'],
        $this->versionDateTime
      );
      if (!$loaded) {
        $this->getError(404, 'Page not found', PAPAYA_PAGE_ERROR_PAGE);
      } elseif ((
                 isset($this->output->viewMode) &&
                 !empty($this->output->viewMode['viewmode_ext'])
                ) ||
                $this->output->loadViewModeData($this->mode)
               ) {
        $expires = $this->topic->getExpires();
        $this->sendCacheHeaders($this->isPreview() ? 0 : $expires);
        if ($this->useCache()) {
          //check cache
          if ($cacheId = $this->getCacheId()) {
            $this->sendHeader('ETag: "'.$cacheId.'"');
            //get cache
            if (!($this->getCache($cacheId))) {
              /* save the topic id because it may get changed through usage of
                 the symlink module */
              $topicId = $this->topicId;
              //generate page
              $page = $this->getPage();
              //set cache
              if ($this->topic->cacheable &&
                  $this->papaya()->response->getStatus() == 200) {
                $this->setCache($cacheId, $topicId, $page);
              }
            } else {
              $this->logRequest(
                $this->topic->languageIdentToId($this->requestData['language']),
                TRUE
              );
            }
          } else {
            $this->getPage();
          }
        } else {
          $this->getPage();
        }
        return TRUE;
      } else {
        $this->getError(
          404,
          'Output mode "'.$this->mode.'" not found',
          PAPAYA_PAGE_ERROR_OUTPUT
        );
      }
    } elseif ($this->topicId == -1) {
      $this->getError(
        404,
        sprintf(
          'File/path %s not found',
          isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : $_SERVER['REQUEST_URI']
        ),
        PAPAYA_PAGE_ERROR_PATH
      );
    } else {
      $this->getError(
        404,
        'Page #'.(int)$this->topicId.' not found',
        PAPAYA_PAGE_ERROR_PAGE
      );
    }
    if ($this->papaya()->options->get('PAPAYA_LOG_RUNTIME_REQUEST', FALSE)) {
      Request\Log::getInstance()->emit();
    }
    return FALSE;
  }

  /**
  * This method outputs a media file.
  *
  * @param string $fileName
  * @param array $data
  * @access public
  */
  function outputFile($fileName, $data) {
    $this->papaya()->session->close();
    papaya_file_delivery::outputFile($fileName, $data);
    $this->getError(404, 'File not found', PAPAYA_PAGE_ERROR_MEDIA_FILE);
  }

  /**
  * This method checks, whether the current surfer is allowed to access a
  * specific media file.
  *
  * @param integer $mid
  * @access public
  * @return array $file
  */
  function checkMediaPerm($mid) {
    if (preg_match('~^([\da-fA-F]{32})v(\d+)(\.[0-9a-zA-Z]+)?$~', $mid, $matches)) {
      // normal new mediaID
      $file = $this->loadMediaFileData($matches[1], $matches[2]);
    } elseif (preg_match('~^([\da-fA-F]{32})$~', $mid, $matches)) {
      // normal new mediaID without a version
      $file = $this->loadMediaFileData($matches[1]);
    } elseif (preg_match('~^(.*)\.(.*)$~', $mid, $matches)) {
      // old mediaID
      $file = $this->loadMediaFileData($matches[1]);
    } else {
      return FALSE;
    }

    if ($file) {
      // Here it could be checked, whether a file is on the clipboard (folderid=-1)
      // and then allow or forbid the access.
      // Otherwise it could be set in getFolderPermissions.

      if ($this->isPreview()) {
        $this->papaya()->administrationUser->initialize();
        $this->papaya()->administrationUser->login();
      }

      if (!$this->isPreview() || !$this->papaya()->administrationUser->isValid) {
        // if the file's folder has no surfer related permissions it may be returned
        $mediaDB = base_mediadb::getInstance();
        $folderPermissions = $mediaDB->calculateFolderPermissions($file['folder_id']);
        if (!isset($folderPermissions['surfer_view']) &&
            !isset($folderPermissions['surfer_edit'])) {
          //public file - add status
          $file['PUBLIC_FILE'] = TRUE;
          $file['url'] = $this->_storageSetPublic(
            'files',
            $file['file_id'].'v'.$file['current_version_id'],
            $file['mimetype']
          );
          return $file;
        }
        // the surfer has one of the folder permissions
        $surfer = $this->papaya()->surfer;
        if ($surfer->hasOnePermOf(array_keys($folderPermissions['surfer_view']))) {
          return $file;
        }
      } elseif ($this->validateEditorAccess()) {
        return $file;
      }
    }
    // if we couldn't allow access, let the visitor know it
    $this->getError(403, 'Access forbidden to media file', PAPAYA_PAGE_ERROR_MEDIA_ACCESS, TRUE);
    return FALSE;
  }

  /**
  * This method determines the users browser examining the HTTP_USER_AGENT var.
  *
  * @access public
  * @return string OPERA or IE or STD
  */
  function getUserAgent() {
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
      $agentString = '';
    } else {
      $agentString = strtolower($_SERVER["HTTP_USER_AGENT"]);
    }
    if (strpos($agentString, 'opera') !== FALSE) {
      return 'OPERA';
    } elseif (strpos($agentString, 'msie') !== FALSE) {
      return 'IE';
    } else {
      return 'STD';
    }
  }

  /**
  * This method checks, whether gzip compression may be used
  */
  function checkAcceptGzip() {
    if (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.0') {
      $this->acceptGzip = FALSE;
      return;
    } else {
      $this->acceptGzip = function_exists('gzopen');
    }
    if ($this->acceptGzip && isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
      $gzipPattern = '~gzip\s*(;\s*q=(\d(\.\d{1,3})?))?~';
      if (preg_match($gzipPattern, $_SERVER['HTTP_ACCEPT_ENCODING'], $regs)) {
        if ((!isset($regs[1])) || ($regs[1] == '') || ((float)$regs[2] != 0)) {
          $this->acceptGzip = TRUE;
          return;
        }
      }
    }
    $this->acceptGzip = FALSE;
  }

  /**
  * This method logs the request to the statistic.
  *
  * @param integer $lngId optional, default value 0
   * @param bool $cachedPage optional, default value FALSE
  * @access public
  */
  function logRequest($lngId = 0, $cachedPage = FALSE) {
    $this->papaya()->session->close();
    if ($this->papaya()->options->get('PAPAYA_PAGE_STATISTIC', FALSE)) {
      $statObj = new base_statistic_logging();
      if (!isset($this->requestData)) {
        $this->requestData = base_object::parseRequestURI();
      }
      $this->statisticRequestId =
        $statObj->logRequest($this->requestData, $lngId, $cachedPage);
    }
  }

  /**
  * This method logs the exit page of a request.
  *
  * @see base_statistic_logging::logRequest
  * @see base_statistic_logging::logExitPage
  * @param string $url
  * @access public
  */
  function logRequestExitPage($url) {
    $this->papaya()->session->close();
    if ($this->papaya()->options->get('PAPAYA_PAGE_STATISTIC', FALSE)) {
      $statObj = new base_statistic_logging();
      $this->statisticRequestId = $statObj->logRequest($this->requestData);
      $statObj->logExitPage($this->statisticRequestId, $url);
    }
  }

  /**
   * This method handles errors, as it redirects accordingly or generates an error output.
   *
   * @param integer $error
   * @param string $errorString
   * @param null $errorCode
   * @param bool $verbose optional, default value FALSE
   * @access public
   */
  function getError($error, $errorString, $errorCode = NULL, $verbose = FALSE) {
    if (!$verbose) {
      $this->mode = $this->papaya()->options->get('PAPAYA_URL_EXTENSION', 'html');
      $this->topic = $this->createPage();
      if (empty($this->requestData['language'])) {
        $lngIdent = '';
      } else {
        $lngIdent = $this->requestData['language'];
      }
      switch ($error) {
      case 404:
        $errorStatus = 404;
        $this->topic->loadOutput(
          $this->papaya()->options->get('PAPAYA_PAGEID_ERROR_404', 0), $lngIdent
        );
        break;
      case 403:
        $errorStatus = 403;
        $this->topic->loadOutput(
          $this->papaya()->options->get('PAPAYA_PAGEID_ERROR_403', 0), $lngIdent
        );
        break;
      default:
        $errorStatus = 500;
        $this->topic->loadOutput(
          $this->papaya()->options->get('PAPAYA_PAGEID_ERROR_500', 0), $lngIdent
        );
      }
      $this->error = array(
        'status' => $errorStatus,
        'code' => $errorCode,
        'message' => $errorString
      );
      if ((empty($_GET['redirect']) || $_GET['redirect'] != $errorStatus) &&
          $this->topic->topicId) {
        $targetUrl = $this->getAbsoluteURL(
          $this->papaya()->options->get('PAPAYA_PATH_WEB', '/'),
          $this->getBaseLink()
        );
        $targetUrl .=
          '?redirect='.$errorStatus.
          '&msg='.urlencode($errorString).
          '&code='.(int)$errorCode;

        $protocol = \PapayaUtilServerProtocol::get();
        $currentUrl = $protocol.'://'.\PapayaUtilServerName::get().$_SERVER['REQUEST_URI'];

        if (base_url_analyze::comparePathDepth($targetUrl, $currentUrl) !== 0) {
          $this->doRedirect(302, $targetUrl, 'Error Redirect');
        } else {
          $this->sendHTTPStatus($errorStatus);
        }
      } else {
        $this->sendHTTPStatus($errorStatus);
      }
      if ($this->topic->topicId) {
        if ($this->topic->checkPublishPeriod($this->topic->topicId)) {
          if ($this->validateAccess($this->topic->topicId)) {
            $viewId = $this->topic->getViewId();
            if ($viewId > 0) {
              if (!(isset($this->output) && is_object($this->output))) {
                $this->output = new papaya_output;
              }
              $this->output->loadViewModeData($this->mode);
              if ($this->filter = $this->output->getFilter($viewId)) {
                $this->generatePage($this->filter->data, TRUE, FALSE);
                if ($this->filter->checkConfiguration()) {
                  $str = $this->filter->parsePage($this->topic, $this->layout);
                  $this->sendHeader('Last-modified: '.gmdate('D, d M Y H:i:s').' GMT');
                  $this->output->sendHeader();
                  $this->sendHeader('Content-length: '.strlen($str));
                  print $str;
                  exit();
                } else {
                  $this->getErrorHTML(
                    $this->filter->errorStatus,
                    'Output mode "'.
                      papaya_strings::escapeHTMLChars(basename($this->mode)).'"for page #'.
                      $this->topic->topicId.' not available: '.$this->filter->errorMessage,
                    PAPAYA_PAGE_ERROR_OUTPUT_SETTINGS
                  );
                }
              } else {
                $this->getErrorHTML(
                  500,
                  'Output mode "'.
                    papaya_strings::escapeHTMLChars(basename($this->mode)).'" for page #'.
                    $this->topic->topicId.' not found',
                  PAPAYA_PAGE_ERROR_OUTPUT
                );
              }
            } else {
              $this->getErrorHTML(
                500,
                'View "'.
                  papaya_strings::escapeHTMLChars(basename($this->mode)).'" for page #'.
                  $this->topic->topicId.' not found',
                PAPAYA_PAGE_ERROR_VIEW
              );
            }
          } else {
            $this->getErrorHTML(403, 'Access forbidden', PAPAYA_PAGE_ERROR_ACCESS);
          }
        } else {
          $this->getErrorHTML(
            404,
            sprintf('Error page #%d currently not published', (int)$this->topicId),
            PAPAYA_PAGE_ERROR_PAGE_PUBLIC
          );
        }
      } else {
        $this->getErrorHTML($error, $errorString, $errorCode);
      }
    } else {
      $this->getErrorHTML($error, $errorString, $errorCode);
    }
  }

  /**
   * This method generates a simple default error HTML output.
   *
   * @param integer $status
   * @param string $errorString
   * @param integer|string $errorCode
   */
  function getErrorHTML($status, $errorString, $errorCode) {
    $application = $this->papaya();
    $controller = new Controller\Error($this);
    $controller->setStatus($status);
    $controller->setError($errorCode, $errorString);
    $controller->execute($application, $application->request, $application->response);
    $application->response->send(TRUE);
  }

  /**
  * This method generates an informational redirection HTML page, that enables the visitor
  * to go to the requested page while informing him about possible security issues.
  *
  * It can use a defined papaya page for this or use a simple default HTML output generated
  * by getRedirectHTML()
  *
  * @param integer $status the redirect status code
  * @param string $url the URL to be redirected to
  * @access public
  */
  function getRedirect($status, $url) {
    $this->mode = $this->papaya()->options->get('PAPAYA_URL_EXTENSION', 'html');
    $this->topic = $this->createPage();
    if (empty($this->requestData['language'])) {
      $lngIdent = '';
    } else {
      $lngIdent = $this->requestData['language'];
    }
    switch ($status) {
    case 302:
      $this->topic->loadOutput(
        $this->papaya()->options->get('PAPAYA_PAGEID_STATUS_302', 0),
        $lngIdent
      );
      break;
    case 301:
    default :
      $status = 301;
      $this->topic->loadOutput(
        $this->papaya()->options->get('PAPAYA_PAGEID_STATUS_301', 0),
        $lngIdent
      );
      break;
    }
    $this->redirect = array(
      'status' => $status,
      'url' => $url
    );
    $GLOBALS['PAPAYA_PAGE'] = $this;
    if ($this->topic->topicId) {
      if ($this->topic->checkPublishPeriod($this->topic->topicId)) {
        if ($this->validateAccess($this->topic->topicId)) {
          $viewId = $this->topic->getViewId();
          if ($viewId > 0) {
            if (!(isset($this->output) && is_object($this->output))) {
              $this->output = new papaya_output;
            }
            $this->output->loadViewModeData($this->mode);
            if ($this->filter = $this->output->getFilter($viewId)) {
              $this->generatePage($this->filter->data, TRUE, FALSE);
              if ($this->filter->checkConfiguration()) {
                $str = $this->filter->parsePage($this->topic, $this->layout);
                $this->sendHeader('Last-modified: '.gmdate('D, d M Y H:i:s').' GMT');
                $this->output->sendHeader();
                print $str;
                exit();
              } else {
                $this->getErrorHTML(
                  $this->filter->errorStatus,
                  'Output mode "'.
                    papaya_strings::escapeHTMLChars(basename($this->mode)).'"for page #'.
                    $this->topic->topicId.' not available: '.$this->filter->errorMessage,
                  PAPAYA_PAGE_ERROR_OUTPUT_SETTINGS
                );
              }
            } else {
              $this->getErrorHTML(
                500,
                'Output mode "'.
                  papaya_strings::escapeHTMLChars(basename($this->mode)).'" for page #'.
                  $this->topic->topicId.' not found',
                PAPAYA_PAGE_ERROR_OUTPUT
              );
            }
          } else {
            $this->getErrorHTML(
              500,
              'View "'.
                papaya_strings::escapeHTMLChars(basename($this->mode)).'" for page #'.
                $this->topic->topicId.' not found',
              PAPAYA_PAGE_ERROR_VIEW
            );
          }
        } else {
          $this->getErrorHTML(403, 'Access forbidden', PAPAYA_PAGE_ERROR_ACCESS);
        }
      } else {
        $this->getErrorHTML(
          404,
          sprintf('Error page #%d currently not published', (int)$this->topicId),
          PAPAYA_PAGE_ERROR_PAGE_PUBLIC
        );
      }
    } else {
      $this->getRedirectHTML($status, $url);
    }
  }

  /**
  * This method generates a simple redirect HTML page, if no papaya page is defined.
  *
  * @param integer $status the redirect status code
  * @param string $url the URL to be redirected to
  */
  function getRedirectHTML($status, $url) {
    $this->sendHTTPStatus(200);
    $this->sendHeader('Content-type: text/html; charset=utf-8');
    printf(
      '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
      <html>
      <head>
      <title>Redirect - %1$d</title>
      <style type="text/css">
      <!--
        body {
          background-color: #FFF;
          font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
        }
        th, td {
          font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
        }
        a {
          color: #95A41A;
        }
      //-->
      </style>
      </head>
      <body>
      <div align="center">
      <br />
      <br />
      <br />
      <table cellpadding="4" border="0" width="400">
        <tr>
          <th colspan="2">The page you requested is on another domain!
            Please click the link below to proceed to the requested page.</th>
        </tr>
        <tr valign="top">
          <td align="center"><a href="%2$s">%2$s</a></td>
        </tr>
      </table>
      </div>
      </body>
      </html>',
      (int)$status,
      papaya_strings::escapeHTMLChars(papaya_strings::ensureUTF8($url))
    );
    exit();
  }

  /**
  * Send HTTP status
  *
  * @param integer $status
  * @access public
  */
  function sendHTTPStatus($status = NULL) {
    $response = $this->papaya()->response;
    if (isset($status)) {
      $response->setStatus($status);
    }
    $response->sendStatus();
  }

  /**
   * This method sends out a string as header()
   *
   * @param string $headerStr
   * @param bool $replace replace existing header
   * @return bool
   */
  function sendHeader($headerStr, $replace = TRUE) {
    static $headersSent;
    $file = '';
    $line = '';
    if (isset($headersSent) && $headersSent) {
      //already sent some headers and reported an error
      return FALSE;
    } elseif ($headersSent = headers_sent($file, $line)) {
      //report the error to log
      $errorMsg = 'WARNING #2 Cannot modify header information - headers already sent';
      $this->logMsg(
        MSG_WARNING,
        \Papaya\Message\Logable::GROUP_PHP,
        $errorMsg,
        $errorMsg.' in '.$file.':'.$line,
        TRUE,
        2
      );
      return FALSE;
    } else {
      if ($this->papaya()->options->get('PAPAYA_DISABLE_XHEADERS', FALSE) &&
          strtoupper(substr($headerStr, 0, 2)) == 'X-') {
        return TRUE;
      }
      header($headerStr, $replace);
    }
    return TRUE;
  }

  /**
  * Validate if the request page/box should contain the public content
  *
  * For now the current status is assigned to the old $public member variable for bc, too.
  *
   * @return bool
  */
  public function isPreview() {
    if (isset($this->_isPreview)) {
      return $this->_isPreview;
    }
    $this->_isPreview = $this->papaya()->request->getParameter(
      'preview', FALSE, NULL, \Papaya\Request::SOURCE_ALL
    );
    $this->papaya()->pageReferences->setPreview($this->_isPreview);
    $this->public = !$this->_isPreview;
    return $this->_isPreview;
  }

  /**
  * Return the current domain id, this can be set after handleDomain() and changed id
  * a preview domain is found in the session.
  *
   * @return bool
  */
  public function getCurrentDomainId() {
    return $this->_currentDomainId;
  }
}

