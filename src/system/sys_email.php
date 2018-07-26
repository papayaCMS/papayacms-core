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
* Email class
*
* @package Papaya-Library
* @subpackage Email
*/

class email extends base_object {
  /**
  * Open tag
  * @var string $tagOpen
  * @access private
  */
  var $tagOpen = '{%';
  /**
  * Close tag
  * @var string $tagClose
  * @access private
  */
  var $tagClose = '%}';

  /**
  * array with "To" recipients
  *
  * (email, name) - list
  * @var array
  * @access private
  */
  var $addressTo = array();
  /**
  * array with "CC" recipients
  *
  * (email, name) - list
  * @var array
  * @access private
  */
  var $addressCC = array();
  /**
  * array with "BCC" recipients
  *
  * (email, name) - list
  * @var array
  * @access private
  */
  var $addressBCC = array();

  /**
  * array with sender data
  *
  * (email, name)
  * @var array
  * @access private
  */
  var $addressFrom = array();


  /**
  * Headers
  * @var array Headers
  * @access private
  */
  var $headers;
  /**
  * Subject
  * @var string
  * @access private
  */
  var $subject = '';
  /**
  * text body
  * @var string
  */
  var $body = '';
  /**
  * html body
  * @var string
  * @access private
  */
  var $bodyHTML = '';
  /**
  * attachements and embedded resources
  * @var array
  * @access private
  */
  var $attachments = array();
  /**
  * attachements and embedded resources
  * @var array
  * @access private
  */
  var $images = array();

  /**
  * base charset - should be utf-8 all times
  * @var string
  * @access private
  */
  var $charset = 'utf-8';
  /**
  * encoding for headers and default encoding
  *
  * attachments use base64
  * @var string
  * @access private
  */
  var $encoding = 'quoted-printable';
  /**
  * encoding for plain text part
  * @var string
  */
  var $encodingText = 'quoted-printable';
  /**
  * encoding for html part
  * @var string
  */
  var $encodingHTML = 'quoted-printable';

  /**
  * line breaks in message
  * @var string
  * @access private
  */
  var $ln = "\n";

  /**
  * line breaks in message header
  * @var string
  * @access private
  */
  var $headerLn = "\n";
  /**
  * indent char for header linebreaks
  * @var string
  */
  var $indentChar = "\t";

  /**
  * array with used content ids
  * @var array
  */
  var $contentIds = array();

  private $_returnPath = '';

  /**
  * Constructor
  *
  * @param mixed $charset optional, charset for text/html parts
  * @param mixed $encoding optional, encoding for text/html parts
  * @access public
  */
  function __construct($charset = NULL, $encoding = NULL) {
    if (isset($charset)) {
      $this->charset = $charset;
    }
    if (isset($encoding)) {
      $this->encoding = $encoding;
    }
  }

  /**
  * add a recipient
  *
  * samples:
  *  addAdress('name@domain.tld'),
  *  addAdress('Name <name@domain.tld>'),
  *  addAdress('name@domain.tld', 'Name', 'BCC')
  *
  * @param $address
  * @param string $name optional, default value ''
  * @param string $mode optional, default value 'To'
  * @access public
  */
  function addAddress($address, $name = '', $mode = 'To') {
    if ($this->hasNoLineBreaks($address) && $this->hasNoLineBreaks($name)) {
      if (preg_match('~^\s*(.*)\s*<([^>]+)>~', $address, $matches)) {
        $address = $matches[2];
        $name = $matches[1];
      }
      switch (strtoupper($mode)) {
      case 'BCC' :
        $this->addressBCC[] = array($address, papaya_strings::ensureUTF8(trim($name)));
        break;
      case 'CC' :
        $this->addressCC[] = array($address, papaya_strings::ensureUTF8(trim($name)));
        break;
      case 'TO' :
      default :
        $this->addressTo[] = array($address, papaya_strings::ensureUTF8(trim($name)));
        break;
      }
    }
  }

  /**
  * Set email sender (From, ReplyTo)
  *
  * @param string $address
  * @param string $name optional, default value ''
  * @access public
  */
  function setSender($address, $name = '') {
    if ($this->hasNoLineBreaks($address) && $this->hasNoLineBreaks($name)) {
      if (preg_match('~^\s*(.*)\s*<([^>]+)>~', $address, $matches)) {
        $address = $matches[2];
        $name = $matches[1];
      }
      $this->addressFrom = array($address, papaya_strings::ensureUTF8(trim($name)));
      $this->setReturnPath($address, FALSE);
    }
  }

  /**
  * Get current email sender
  *
  * @access public
  * @return string
  */
  function getSender() {
    if (isset($this->addressFrom) && is_array($this->addressFrom)) {
      if (isset($this->addressFrom[0]) && isset($this->addressFrom[1])) {
        return $this->formatAddress($this->addressFrom[0], $this->addressFrom[1]);
      } elseif (isset($this->addressFrom[0])) {
        return $this->formatAddress($this->addressFrom[0]);
      }
    }
    return FALSE;
  }

  /**
  * attach a file
  *
  * @param string $fileName
  * @param string $mimeType optional, default value 'application/octet-stream'
  * @param string $encoding optional, default value 'base64'
  * @access public
  * @return boolean
  */
  function addAttachment($fileName, $mimeType = 'application/octet-stream', $encoding = 'base64') {
    if (file_exists($fileName) && is_file($fileName) && is_readable($fileName)) {
      $fileTitle = basename($fileName);
      $this->attachments[] = array(
        'file' => $fileName,
        'title' => $fileTitle,
        'encoding' => $encoding,
        'mimetype' => $mimeType,
        'cid' => ''
      );
      return TRUE;
    } else {
      $this->addMsg(MSG_ERROR, 'Cannot read attachment file.');
      return FALSE;
    }
  }

  /**
  * attach data
  *
  * @param string $fileTitle filename (attachment.ext)
  * @param string $data binary data string
  * @param string $mimeType optional, default value 'application/octet-stream'
  * @param string $encoding optional, default value 'base64'
  * @access public
  * @return boolean
  */
  function addAttachmentData(
    $fileTitle,
    $data,
    $mimeType = 'application/octet-stream',
    $encoding = 'base64'
  ) {
    if (!empty($data)) {
      $this->attachments[] = array(
        'title' => $fileTitle,
        'encoding' => $encoding,
        'mimetype' => $mimeType,
        'cid' => '',
        'data' => $data
      );
      return TRUE;
    } else {
      $this->addMsg(MSG_ERROR, 'Cannot read attachment data.');
      return FALSE;
    }
  }

  /**
  * embed image file
  *
  * @param string $fileName
  * @param string $cId content id (<img src="cid:..."/>)
  * @param string $mimeType optional, default value 'image/jpeg'
  * @param string $encoding optional, default value 'base64'
  * @access public
  * @return boolean
  */
  function addImage($fileName, $cId, $mimeType = 'image/jpeg', $encoding = 'base64') {
    if (file_exists($fileName) && is_file($fileName) && is_readable($fileName)) {
      $fileTitle = basename($fileName);
      $this->images[] = array(
        'file' => $fileName,
        'title' => $fileTitle,
        'encoding' => $encoding,
        'mimetype' => $mimeType,
        'cid' => $cId
      );
      return TRUE;
    } else {
      $this->addMsg(MSG_ERROR, 'Cannot read image file.');
      return FALSE;
    }
  }

  /**
  * embed image data
  *
  * @param string $fileTitle filename (image.ext)
  * @param string $data binary data string
  * @param string $cId content id (<img src="cid:..."/>)
  * @param string $mimeType optional, default value 'image/jpeg'
  * @param string $encoding optional, default value 'base64'
  * @access public
  * @return boolean
  */
  function addImageData($fileTitle, $data, $cId,  $mimeType = 'image/jpeg', $encoding = 'base64') {
    if (!empty($data)) {
      $this->images[] = array(
        'title' => $fileTitle,
        'encoding' => $encoding,
        'mimetype' => $mimeType,
        'cid' => $cId,
        'data' => $data
      );
      return TRUE;
    } else {
      $this->addMsg(MSG_ERROR, 'Cannot read image data.');
      return FALSE;
    }
  }

  /**
  * set message subject
  *
  * @param string $str
  * @param array $fillValues replace {%key%} -> value
  * @access public
  * @return boolean
  */
  function setSubject($str, $fillValues = NULL) {
    if ($this->hasNoLineBreaks($str)) {
      $this->subject = $this->replaceTemplateMarkers(
        papaya_strings::ensureUTF8(trim($str)), $fillValues
      );
      return TRUE;
    }
    return FALSE;
  }

  /**
   * set message plain text body
   *
   * @param string $str
   * @param array $fillValues replace {%key%} -> value
   * @param int $wrapColumn
   * @access public
   */
  function setBody($str, $fillValues = NULL, $wrapColumn = 0) {
    $str = $this->replaceTemplateMarkers(papaya_strings::ensureUTF8($str), $fillValues);
    if ($wrapColumn > 10) {
      $lines = preg_split("(\r\n|\n\r|[\r\n])", $str);
      $result = '';
      if (isset($lines) && is_array($lines) && count($lines) > 0) {
        foreach ($lines as $line) {
          if ($line == '-- ') {
            $result .= $line.$this->ln;
            continue;
          }
          $result .= wordwrap(chop($line), $wrapColumn, $this->ln).$this->ln;
        }
        $str = substr($result, 0, -strlen($this->ln));
      }
    }
    $this->body = $this->reformatLineEnds($str);
  }

  /**
  * set message plain text body
  *
  * @param string $str
  * @param array $fillValues replace {%key%} -> value
  * @access public
  */
  function setBodyHTML($str, $fillValues = NULL) {
    $str = $this->replaceTemplateMarkers(papaya_strings::ensureUTF8($str), $fillValues);
    $str = $this->reformatLineEnds($this->replaceTagURIs($str));
    $str = htmlentities($str, ENT_NOQUOTES, 'utf-8');
    $charTable = array_flip(get_html_translation_table(HTML_SPECIALCHARS, ENT_NOQUOTES));
    $str = str_replace(array_keys($charTable), array_values($charTable), $str);
    $this->bodyHTML = $str;
  }

  /**
  * replace tag uri
  *
  * image src attributes with cid:...
  *
  * @param $str
  * @access private
  * @return string
  */
  function replaceTagURIs($str) {
    $patternImg = '~(<img[^>]+src=)(["\'])(.*?)(\\2)([^>]*>)~i';
    $patternBackImg = '~(<(body|table|td)[^>]+background=)(["\'])(.*?)(\\3)([^>]*>)~i';
    $patternA = '~(<a[^>]+href=)(["\'])(.*?)(\\2)([^>]*>)~i';
    $replace = array();
    $uris = array();
    if (preg_match_all($patternImg, $str, $matches, PREG_SET_ORDER)) {
      foreach ($matches as $match) {
        if (!isset($uris['img'][$match[3]])) {
          if ($fileSrc = $this->embedImage($match[3])) {
            $replace[$match[0]] = $match[1].'"'.$fileSrc.'"'.$match[5];
            $uris['img'][$match[3]] = $fileSrc;
          }
        } elseif (!isset($replace[$match[0]])) {
          $replace[$match[0]] = $match[1].'"'.$uris['img'][$match[3]].'"'.$match[5];
        }
      }
    }
    if (preg_match_all($patternBackImg, $str, $matches, PREG_SET_ORDER)) {
      foreach ($matches as $match) {
        if (!isset($uris['img'][$match[4]])) {
          if ($fileSrc = $this->embedImage($match[4])) {
            $replace[$match[0]] = $match[1].'"'.$fileSrc.'"'.$match[6];
            $uris['img'][$match[4]] = $fileSrc;
          }
        } elseif (!isset($replace[$match[0]])) {
          $replace[$match[0]] = $match[1].'"'.$uris['img'][$match[4]].'"'.$match[6];
        }
      }
    }
    if (preg_match_all($patternA, $str, $matches, PREG_SET_ORDER)) {
      foreach ($matches as $match) {
        if (!isset($uris['a'][$match[3]])) {
          $url = trim($match[3]);
          if (preg_match('~^(mailto:|#)~i', $url)) {
            $fileSrc = $url;
          } else {
            $fileSrc = $this->getAbsoluteURL($url, '', FALSE);
          }
          $replace[$match[0]] = $match[1].'"'.$fileSrc.'"'.$match[5];
          $uris['a'][$match[3]] = $fileSrc;
        } elseif (!isset($replace[$match[0]])) {
          $replace[$match[0]] = $match[1].'"'.$uris['a'][$match[3]].'"'.$match[5];
        }
      }
    }
    if (is_array($replace) && count($replace) > 0) {
      return str_replace(array_keys($replace), array_values($replace), $str);
    }
    return $str;
  }

  /**
  * try to embed image and return cid or absolute url
  *
  * @param $fileSrc
  * @access public
  * @return string
  */
  function embedImage($fileSrc) {
    if (0 === strpos($fileSrc, '/')) {
      $basePath = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
      if (substr($basePath, -1) == '/') {
        $basePath = substr($basePath, 0, -1);
      }
    } else {
      $basePath = $this->getBasePath(TRUE);
    }
    $fileSrcInternal = $fileSrc;
    if (defined('PAPAYA_ADMIN_PAGE') &&
        PAPAYA_ADMIN_PAGE &&
        substr($fileSrcInternal, 0, 3) == '../') {
      $fileSrcInternal = substr($fileSrcInternal, 3);
    } elseif (substr($fileSrc, 0, 2) == './') {
      $fileSrcInternal = substr($fileSrcInternal, 2);
    }
    if (defined('PAPAYA_PATH_WEB')) {
      $fileSrcInternal = PAPAYA_PATH_WEB.$fileSrcInternal;
    } else {
      $fileSrcInternal = '/'.$fileSrcInternal;
    }
    $data = $this->parseRequestURI($fileSrcInternal);
    $fileName = NULL;
    $fileData = NULL;
    switch ($data['output']) {
    case 'image' :
      $pImage =
        '~/?(sid([a-z]*([\da-f]{32}))/)?
          (([a-z\d_-]+/)*)([a-z\d_-]+)\.(image)(\.(jpg))(\?.*)?~ix';
      if (preg_match($pImage, $fileSrc, $regs)) {
        if (!(isset($this->imgGenerator) && is_object($this->imgGenerator))) {
          $this->imgGenerator = new base_imagegenerator();
        }
        $query = new \PapayaRequestParametersQuery();
        $params = $query->setString($regs[10])->values()->toArray();
        if (isset($regs[6]) &&
            $this->imgGenerator->loadByIdent($regs[6]) &&
            !empty($params['img'])) {
          $fileData = $this->imgGenerator->generateImage(
            FALSE,
            $params['img'],
            $this->papaya()->options['PAPAYA_THUMBS_FILETYPE']
          );
          $cId = $this->generateContentId();
          $this->addImageData(
            basename($fileSrc),
            $fileData,
            $cId,
            image_type_to_mime_type(
              $this->papaya()->options['PAPAYA_THUMBS_FILETYPE']
            )
          );
          return 'cid:'.$cId;
        }
      }
      return '';
    case 'thumb' :
    case 'thumbnail' :
    case 'media' :
      $path = '';
      $depth = $this->papaya()->options->get('PAPAYA_MEDIADB_SUBDIRECTORIES', 1);
      for ($i = 0; $i < $depth; $i++) {
        $path .= $data['media_id'][$i].'/';
      }
      $localFile = $data['media_id'];
      switch ($data['ext']) {
      case 'thumb' :
        /** @noinspection PhpMissingBreakStatementInspection */
      case 'thumbnail' :
        if (file_exists($this->papaya()->options['PAPAYA_PATH_THUMBFILES'].$path.$localFile)) {
          $fileName = $this->papaya()->options['PAPAYA_PATH_THUMBFILES'].$path.$localFile;
          break;
        }
      case 'media' :
        if (($pos = strrpos($localFile, '.')) > 0) {
          $localFile = substr($localFile, 0, $pos);
        }
        if (file_exists($this->papaya()->options['PAPAYA_PATH_MEDIAFILES'].$path.$localFile)) {
          $fileName = $this->papaya()->options['PAPAYA_PATH_MEDIAFILES'].$path.$localFile;
        } else {
          $mediaDB = base_mediadb::getInstance();
          if ($file = $mediaDB->getFile($localFile)) {
            $fileName = $file['FILENAME'];
          }
        }
        break;
      }
      break;
    default :
      $fileName = $basePath.$fileSrc;
      break;
    }
    if (isset($fileName) && is_file($fileName) && is_readable($fileName)) {
      $mimeType = image_type_to_mime_type(
        $this->papaya()->options['PAPAYA_THUMBS_FILETYPE']
      );
      if (isset($mimeType)) {
        $cId = $this->generateContentId();
        $this->addImageData(basename($fileSrc), file_get_contents($fileName), $cId, $mimeType);
        return 'cid:'.$cId;
      }
    }
    return $this->getAbsoluteURL($fileSrc, '', FALSE);
  }

  /**
  * generate a content id
  *
  * @param string $prefix Prefix string for content id
  * @access private
  * @return string
  */
  function generateContentId($prefix = 'img') {
    do {
      $cId = $prefix.md5(rand(0, time()));
    } while (isset($this->contentIds[$cId]));
    $this->contentIds[$cId] = TRUE;
    return $cId;
  }

  /**
  * Email has a text body content
  * @return boolean
  */
  function hasBodyText() {
    return !empty($this->body);
  }

  /**
  * Email has a html body content
  * @return boolean
  */
  function hasBodyHTML() {
    return !empty($this->bodyHTML);
  }

  /**
  * Email has attachment files
  * @return boolean
  */
  function hasAttachments() {
    return (
      isset($this->attachments) &&
      is_array($this->attachments) &&
      count($this->attachments) > 0
    );
  }

  /**
  * Email has incline images
  * @return boolean
  */
  function hasImages() {
    return (
      isset($this->images) &&
      is_array($this->images) &&
      count($this->images) > 0
    );
  }

  /**
  * adds header name/value pair
  *
  * @param string $name name of header (i.e. what stands before ':')
  * @param string $value content of header (i.e. what stands after ':')
  * @param boolean $replace if existing headers of this name should be replaced
  * @access public
  * @return boolean TRUE optional
  */
  function setHeader($name, $value, $replace = TRUE) {
    if ($this->hasNoLineBreaks($value)) {
      if ($replace && isset($this->headers) && is_array($this->headers)) {
        foreach ($this->headers as $i => $header) {
          list($key) = $header;
          if ($key == $name) {
            unset($this->headers[$i]);
          }
        }
      }
      $this->headers[] = array($name, $value);
      return TRUE;
    } else {
      $this->addMsg(MSG_ERROR, "Header contains linebreak chars.");
      return FALSE;
    }
  }

  /**
  * adds an explicit return-path header
  *
  * (at least one known web-based mail client does not receive
  *  any mails at all if you don't send it)
  *
  * @access public
  * @param string $email
  * @param boolean $force
  */
  function setReturnPath($email, $force = TRUE) {
    if (empty($this->_returnPath) || $force) {
      if (!empty($email)) {
        if (!\PapayaFilterFactory::isEmail($email)) {
          $this->addMsg(MSG_ERROR, "Return path must be a valid email address");
          return;
        }
      }
      $this->_returnPath = $email;
    }
  }

  /**
  * check for forbidden linebreak chars (header injections)
  *
  * @param string $str
  * @access private
  * @return boolean
  */
  function hasNoLineBreaks($str) {
    if (FALSE === strpos($str, "\n") && FALSE === strpos($str, "\r")) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * set template markers
  *
  * @param $tagOpen
  * @param $tagClose
  * @access public
  */
  function setTemplateMarkers($tagOpen, $tagClose) {
    $this->tagOpen = $tagOpen;
    $this->tagClose = $tagClose;
  }

  /**
  * get template markers
  *
  * @access public
  * @return array (open, close)
  */
  function getTemplateMarkers() {
    return array($this->tagOpen, $this->tagClose);
  }

  /**
  * replace template patterns with values
  *
  * @param string $templateString
  * @param mixed $fillValues optional, default value NULL
  * @access public
  * @return string
  */
  function replaceTemplateMarkers($templateString, $fillValues = NULL) {
    $template = new base_simpletemplate();
    $template->tagOpen = $this->tagOpen;
    $template->tagClose = $this->tagClose;
    return $template->parse($templateString, $fillValues);
  }

  /**
  * convert all line breaks to $this->ln
  *
  * @param string $str
  * @access private
  * @return string
  */
  function reformatLineEnds($str) {
    $str = str_replace(array("\r\n", "\r"), "\n", $str);
    if ($this->ln != "\n") {
      $str = str_replace("\n", $this->ln, $str);
    }
    return $str;
  }

  /**
  * Rewrap quoted printable encoded text content
  * @param string $str
  * @param integer $lineLength
  * @return string
  */
  function reWrapQP($str, $lineLength) {
    $str = str_replace(array("\r\n", "\r"), "\n", $str);
    $lines = explode("\n", $str);
    $result = '';
    if (isset($lines) && is_array($lines) && count($lines) > 0) {
      foreach ($lines as $line) {
        $result .= $this->reWrapQPLine($line, $lineLength).$this->ln;
      }
    }
    return $result;
  }

  /**
  * Rewrap quoted printable encoded text line
  * @param string $str
  * @param integer $lineLength
  * @return string
  */
  function reWrapQPLine($str, $lineLength) {
    if (trim($str) == '') {
      $result = '';
    } elseif (strlen($str) <= $lineLength) {
      $result = $str;
    } elseif (FALSE !== strpos($str, ' ')) {
      $result = $this->reWrapQPLineAtSpace($str, $lineLength);
    } elseif (FALSE !== strpos($str, '=')) {
      $result = $this->reWrapQPLineAtEqual($str, $lineLength);
    } else {
      $result = $this->reWrapQPLineAtOffset($str, $lineLength);
    }
    return $result;
  }

  /**
  * Rewrap quoted printable encoded text line at line length
  * @param string $str
  * @param integer $lineLength
  * @return string
  */
  function reWrapQPLineAtOffset($str, $lineLength) {
    $result = substr(chunk_split($str, $lineLength, '='.$this->ln), 0, -strlen($this->ln) - 1);
    return $result;
  }

  /**
  * Rewrap quoted printable encoded text line at whitespace
  * @param string $str
  * @param integer $lineLength
  * @return string
  */
  function reWrapQPLineAtSpace($str, $lineLength) {
    $result = '';
    $buffer = '';
    $offset = 0;
    $separator = ' ';
    $str .= $separator;
    while (FALSE !== ($pos = strpos($str, $separator, $offset))) {
      if ($pos >= $offset) {
        $word = substr($str, $offset, $pos - $offset + strlen($separator));
        if (strlen($word) >= $lineLength) {
          if ($buffer != '') {
            $result .= $buffer.'='.$this->ln;
          }
          if (FALSE !== strpos($word, '=')) {
            $result .= $this->reWrapQPLineAtEqual($word, $lineLength);
          } else {
            $result .= $this->reWrapQPLineAtOffset($word, $lineLength);
          }
          $result .= '='.$this->ln;
          $buffer = '';
        } elseif (strlen($buffer.$word) >= $lineLength) {
          $result .= $buffer.'='.$this->ln;
          $buffer = $word;
        } else {
          $buffer .= $word;
        }
        $offset = $pos + 1;
      } else {
        break;
      }
    }
    $word = substr($str, $offset);
    if (strlen($word) >= $lineLength) {
      $result .= $buffer.'='.$this->ln;
      if (FALSE !== strpos($word, '=')) {
        $result .= $this->reWrapQPLineAtEqual($word, $lineLength);
      } else {
        $result .= $this->reWrapQPLineAtOffset($word, $lineLength);
      }
    } elseif (strlen($buffer.$word) >= $lineLength) {
      $result .= $buffer.'='.$this->ln.$word;
    } else {
      $result .= $buffer.$word;
    }
    return $result;
  }

  /**
  * Rewrap quoted printable encoded text line at encoded char start
  * @param string $str
  * @param integer $lineLength
  * @return string
  */
  function reWrapQPLineAtEqual($str, $lineLength) {
    $result = '';
    $buffer = '';
    $offset = 0;
    $separator = '=';
    while (FALSE !== ($pos = strpos($str, $separator, $offset + 1))) {
      if ($pos >= $offset) {
        $word = substr($str, $offset, $pos - $offset);
        if (strlen($word) >= $lineLength) {
          if ($buffer != '') {
            $result .= $buffer.'='.$this->ln;
          }
          $result .= $this->reWrapQPLineAtOffset($word, $lineLength);
          $result .= '='.$this->ln;
          $buffer = '';
        } elseif (strlen($buffer.$word) >= $lineLength) {
          $result .= $buffer.'='.$this->ln;
          $buffer = $word;
        } else {
          $buffer .= $word;
        }
        $offset = $pos;
      } else {
        break;
      }
    }
    $word = substr($str, $offset);
    if (strlen($word) >= $lineLength) {
      $result .= $buffer.'='.$this->ln;
      $result .= $this->reWrapQPLineAtOffset($word, $lineLength);
    } elseif (strlen($buffer.$word) >= $lineLength) {
      $result .= $buffer.'='.$this->ln.$word;
    } else {
      $result .= $buffer.$word;
    }
    return $result;
  }

  /**
   * checks receipient for linebreak chars or invalid email address format
   *
   * @param string $address receipient email address
   * @param string $name
   * @access private
   * @return string $to receipient email address if valid, else FALSE
   */
  function formatAddress($address, $name = '') {
    if (trim($name) != '') {
      $name = str_replace('\\', '\\', $name);
      if (FALSE !== strpos($name, '"')) {
        $name = str_replace('"', '\\"', $name);
      }
      if (FALSE !== strpos($name, ',') || FALSE !== strpos($name, ';')) {
        $name = '"'.$name.'"';
      }
      return $this->encodeHeader($name).' <'.$this->encodePunyCode($address).'>';
    } else {
      return $this->encodePunyCode($address);
    }
  }

  /**
  * convert IDN address to punycode
  *
  * check email adress for specialchars an try to encode
  *
  * @param $email
  * @access private
  * @return string
  */
  function encodePunyCode($email) {
    $email = papaya_strings::ensureUTF8($email);
    if (preg_match('~[^a-zA-Z\d._@-]~', $email)) {
      if (function_exists('idn_to_ascii')) {
        return idn_to_ascii($email);
      } else {
        $this->addMsg(MSG_ERROR, 'Invalid email address');
        return '';
      }
    }
    return $email;
  }

  /**
  * encodes UTF8 string to RFC-2047 compatible ASCII string
  *
  * @param string $str string to be encoded
  * @param integer $nameOffset string to be encoded
  * @param integer $lineLength string to be encoded
  * @access private
  * @return string $str encoded string (if $str was not plain ascii)
  */
  function encodeHeader($str, $nameOffset = 0, $lineLength = 70) {
    if (($lineLength > 0 && strlen($str) > ($lineLength - $nameOffset)) ||
        preg_match('~[^\x21-\x3C\x3E-\x7E\x09\x20\x0A]~', $str)) {
      if ($lineLength > 0) {
        $maxLength = $lineLength - $nameOffset - strlen($this->charset);
        if ($maxLength < 1) {
          return '';
        }
      } else {
        $maxLength = 99999;
      }
      switch($this->encoding) {
      case 'base64' :
        $encoded = base64_encode($str);
        $maxLength -= $maxLength % 4;
        $encStart = '=?'.$this->charset.'?B?';
        $encEnd = '?=';
        $str = trim(
          chunk_split($encoded),
          $maxLength,
          $encEnd.$this->headerLn.$this->indentChar.$encStart
        );
        $str = $encStart.$str.$encEnd;
        break;
      case 'quoted-printable' :
      default :
        $encStart = '=?'.$this->charset.'?Q?';
        $encEnd = '?=';
        $encoded = $this->encodeQuotedPrintable($str, FALSE, TRUE);
        $lines = explode('='.$this->ln, $this->reWrapQPLine($encoded, $maxLength));
        $str = '';
        foreach ($lines as $line) {
          $str .= $this->headerLn.$this->indentChar;
          if (FALSE !== strpos($line, '=')) {
            $str .= $encStart.rtrim($line, "\t\n\r\0\x0B").$encEnd;
          } else {
            $str .= rtrim($line, "\t\n\r\0\x0B");
          }
        }
        $str = substr($str, strlen($this->headerLn.$this->indentChar));
        break;
      }
    }
    return $str;
  }

  /**
  * encode a string
  *
  * @param $str
  * @param string $encoding optional, default value 'base64'
  * @access private
  * @return string
  */
  function encodeString($str, $encoding = 'base64') {
    $result = '';
    switch ($encoding) {
    case '8bit' :
      $result = $this->reformatLineEnds(wordwrap($str, 72));
      if (substr($result, -strlen($this->ln)) != $this->ln) {
        $result .= $this->ln;
      }
      break;
    case 'base64' :
      $result = chunk_split(base64_encode($str), 76, $this->ln);
      break;
    case 'quoted-printable' :
      $result = $this->encodeQuotedPrintable($str);
      if (substr($result, -strlen($this->ln)) != $this->ln) {
        $result .= $this->ln;
      }
      break;
    case 'binary' :
      return $str;
      break;
    }
    return $result;
  }

  /**
   * encode a file
   *
   * @param string $fileName
   * @param string $encoding optional, default value 'base64'
   * @see email::encodeString
   * @access private
   * @return string
   */
  function encodeFile($fileName, $encoding = 'base64') {
    if (file_exists($fileName) && is_file($fileName) && is_readable($fileName)) {
      $binaryString = file_get_contents($fileName);
      return $this->encodeString($binaryString, $encoding);
    }
    return '';
  }

  /**
  * encode a string using quoted-printable
  *
  * @param string $str
  * @param boolean $wrap - rewrap using soft breaks
  * @param boolean $encodeQuestionMark - encode the question mark
  * @access private
  * @return string
  */
  function encodeQuotedPrintable($str, $wrap = TRUE, $encodeQuestionMark = FALSE) {
    $result = $str;
    $result = preg_replace_callback(
      '~[^\x21-\x3C\x3E-\x7E\x09\x20\x0A\x0D]~',
      array($this, 'encodeQPChar'),
      $result
    );
    if ($encodeQuestionMark) {
      $result = str_replace('?', '=3F', $result);
    }
    $result = preg_replace_callback(
      '~[\x09\x20]$~m',
      array($this, 'encodeQPChar'),
      $result
    );
    if ($wrap) {
      $result = $this->reWrapQP($result, 72);
    } else {
      $result = $this->reformatLineEnds($result);
    }
    return $result;
  }

  /**
  * callback encode a special byte using quoted-printable
  *
  * @param array $match match array from preg_replace_callback
  * @access private
  * @return string
  */
  function encodeQPChar($match) {
    $charCode = dechex(ord(substr($match[0], 0, 1)));
    return '='.strtoupper(str_pad($charCode, 2, '0', STR_PAD_LEFT));
  }

  /**
  * returns headers formatted
  *
  * @access private
  * @return string $header formatted header
  */
  function getHeaders() {
    $result = '';
    if (isset($this->headers) && is_array($this->headers) && count($this->headers) > 0) {
      foreach ($this->headers as $entry) {
        $result .= $entry[0].': '.$this->encodeHeader($entry[1], strlen($entry[0]))."\n";
      }
    }
    return $result;
  }

  /**
  * returns address headers formatted
  *
  * @param $addresses
  * @param string $headerName To, CC, BCC - can be empty
  * @access private
  * @return string
  */
  function getAddressHeader($addresses, $headerName = '') {
    $result = '';
    if (isset($addresses) && is_array($addresses) && count($addresses) > 0) {
      foreach ($addresses as $entry) {
        $result .= $this->formatAddress($entry[0], $entry[1]).",".$this->headerLn.$this->indentChar;
      }
      if (trim($headerName) != '') {
        $result = $headerName.': '.substr($result, 0, -strlen($this->headerLn) - 2).$this->headerLn;
      } else {
        $result = substr($result, 0, -strlen($this->headerLn) - 2);
      }
    }
    return $result;
  }

  /**
  * Encode address list to a string
  * @param array $addresses
  * @return string
  */
  function getAddressEmails($addresses) {
    $result = '';
    if (isset($addresses) && is_array($addresses) && count($addresses) > 0) {
      foreach ($addresses as $entry) {
        $result .= $this->formatAddress($entry[0]).", ";
      }
      $result = substr($result, 0, -2);
    }
    return $result;
  }

  /**
   * get ettachments for message body
   *
   * @access private
   * @param string $boundary
   * @return string
   */
  function getAttachments($boundary) {
    $mimeString = '';
    if (isset($this->attachments) &&
        is_array($this->attachments) &&
        count($this->attachments) > 0) {
      foreach ($this->attachments as $attachment) {
        $mimeString .= $this->ln.'--'.$boundary.$this->ln;
        $mimeString .= 'Content-Type: '.$attachment['mimetype'].';'.$this->ln.$this->indentChar.
          'name="'.$attachment['title'].'"'.$this->ln;
        $mimeString .= 'Content-Transfer-Encoding: '.$attachment['encoding'].$this->ln;
        $mimeString .= 'Content-Disposition: attachment;'.$this->ln.$this->indentChar.
            'filename="'.$attachment['title'].'"'.$this->ln.$this->ln;
        if (isset($attachment['file']) && is_readable($attachment['file'])) {
          $mimeString .= $this->encodeFile($attachment['file'], 'base64');
        } elseif (!empty($attachment['data'])) {
          $mimeString .= $this->encodeString($attachment['data'], 'base64');
        }
      }
    }
    return $mimeString;
  }

  /**
   * get images for message html part
   *
   * @access private
   * @param string $boundary
   * @return string
   */
  function getImages($boundary) {
    $mimeString = '';
    if (isset($this->images) && is_array($this->images) && count($this->images) > 0) {
      foreach ($this->images as $image) {
        $mimeString .= $this->ln.'--'.$boundary.$this->ln;
        $mimeString .= 'Content-Type: '.$image['mimetype'].';'.$this->ln.$this->indentChar.
          'name="'.$image['title'].'"'.$this->ln;
        $mimeString .= 'Content-Transfer-Encoding: '.$image['encoding'].$this->ln;
        $mimeString .= 'Content-ID: <'.$image['cid'].'>'.$this->ln;
        $mimeString .= 'Content-Disposition: inline;'.$this->ln.$this->indentChar.
          'filename="'.$image['title'].'"'.$this->ln.$this->ln;
        if (isset($image['file']) && is_readable($image['file'])) {
          $mimeString .= $this->encodeFile($image['file'], 'base64');
        } elseif (!empty($image['data'])) {
          $mimeString .= $this->encodeString($image['data'], 'base64');
        }
      }
    }
    return $mimeString;
  }

  /**
   * get a message part start boundary string
   *
   * @param string $boundary
   * @param string $charset
   * @param string $contentType
   * @param string $encoding
   * @param string $boundaryPart
   * @access private
   * @return string
   */
  function getBoundaryStart($boundary, $charset, $contentType, $encoding, $boundaryPart = '') {
    if (trim($charset) == '') {
      $charset = $this->charset;
    }
    if (trim($encoding) == '') {
      $encoding = $this->encoding;
    }

    $result = '--'.$boundary.$this->ln;
    if ($contentType == 'text/plain' || $contentType == 'text/html') {
      $result .= 'Content-Type: '.$contentType.'; charset='.$charset.$this->ln;
      $result .= 'Content-Transfer-Encoding: '.$encoding.$this->ln.$this->ln;
    } elseif (0 === strpos($contentType, 'multipart')) {
      $result .= 'Content-Type: '.$contentType.';'.$this->ln.
        $this->indentChar.'boundary="'.$boundaryPart.'"'.$this->ln;
      $result .= $this->ln;
    } else {
      $result .= 'Content-Type: '.$contentType.$this->ln;
      $result .= 'Content-Transfer-Encoding: '.$encoding.$this->ln.$this->ln;
    }
    return $result;
  }

  /**
  * get a message part end boundary string
  *
  * @param string $boundary
  * @access private
  * @return string
  */
  function getBoundaryEnd($boundary) {
    return '--'.$boundary.'--'.$this->ln;
  }

  /**
  * get a new boundary string
  *
  * @access public
  * @return string
  */
  function createBoundary() {
    $uniqId = md5(rand(0, time()));
    if (defined('PAPAYA_DISABLE_XHEADERS') && PAPAYA_DISABLE_XHEADERS) {
      return '----'.$uniqId.'.partSeparator';
    } else {
      return '----'.$uniqId.'.papayaCMS';
    }
  }


  /**
   * set subject or body using template (backwards compatibility)
   *
   * @see $this->templates
   * @param string $name must be 'body', 'body_html' or 'subject' in order to be used
   * @param $template
   * @param $fillValues
   * @return bool
   * @access public
   */
  function setTemplate($name, $template, $fillValues) {
    switch ($name) {
    case 'body' :
      $this->setBody($template, $fillValues);
      return TRUE;
    case 'subject' :
      $this->setSubject($template, $fillValues);
      return TRUE;
    default :
      $this->addMsg(MSG_WARNING, 'Template must be for body or subject');
      return FALSE;
    }
  }

  /**
  * returns mailto: link with body and subject to call users
  * email client (use in <a href="%s">link</a>)
  *
  * @param string $to receipients email adress, may be empty
  * @param string $subject subject of message, taken from $this->subject if empty
  * @param string $body subject of message, taken from $this->body if empty
  * @access public
  * @return string mailto string
  */
  function getMailtoLink($to = '', $subject = NULL, $body = NULL) {
    if (isset($subject)) {
      $this->setSubject($subject);
    }
    if (isset($body)) {
      $this->setBody($body);
    }
    $link = sprintf('mailto:%s?', rawurlencode($to));
    if (isset($subject)) {
      $link .= sprintf('subject=%s&', $this->encodeHeader($subject, 0, 0));
    } elseif ($this->subject != '') {
      $link .= sprintf('subject=%s&', $this->encodeHeader($this->subject, 0, 0));
    }
    if (isset($body)) {
      $link .= sprintf('body=%s&', $this->encodeHeader($body, 0, 0));
    } elseif ($this->body != '') {
      $link .= sprintf('body=%s&', $this->encodeHeader($this->body, 0, 0));
    }
    return substr($link, 0, -1); // removes trailing ? or &
  }
  /**
  * get the mimetype for a message part
  *
  * @param boolean $hasText optional, default value FALSE
  * @param boolean $hasHTML optional, default value FALSE
  * @param boolean $hasImages optional, default value FALSE
  * @param boolean $hasAttachments optional, default value FALSE
  * @access public
  * @return array (boolean $multiPart, string $mimeType)
  */
  function getMimeType(
    $hasText = FALSE, $hasHTML = FALSE, $hasImages = FALSE, $hasAttachments = FALSE
  ) {
    if ($hasAttachments) {
      $type = 'multipart/mixed';
      $multiPart = TRUE;
    } elseif ($hasImages) {
      $type = 'multipart/related';
      $multiPart = TRUE;
    } elseif ($hasText && $hasHTML) {
      $type = 'multipart/alternative';
      $multiPart = TRUE;
    } elseif ($hasHTML) {
      $type = 'text/html';
      $multiPart = FALSE;
    } else {
      $type = 'text/plain';
      $multiPart = FALSE;
    }
    return array('multipart' => $multiPart, 'mimetype' => $type);
  }

  /**
  * get message header and body
  *
  * @access public
  */
  function getMessage($includeTo = FALSE) {
    if (defined('PAPAYA_DISABLE_XHEADERS') && PAPAYA_DISABLE_XHEADERS) {
      //no user agent - try http host
      if (!empty($_SERVER['HTTP_HOST'])) {
        $this->setHeader('User-Agent', $_SERVER['HTTP_HOST']);
      }
    } elseif (!empty($_SERVER['HTTP_HOST'])) {
      $this->setHeader('User-Agent', 'papaya CMS - '.$_SERVER['HTTP_HOST']);
    } else {
      $this->setHeader('User-Agent', 'papaya CMS');
    }
    $headerStr = $this->getHeaders();
    if ($includeTo) {
      $headerStr .= $this->getAddressHeader($this->addressTo, 'TO');
    }
    $headerStr .= $this->getAddressHeader($this->addressCC, 'CC');
    $headerStr .= $this->getAddressHeader($this->addressBCC, 'BCC');
    if ($from = $this->getSender()) {
      $headerStr .= 'From: '.$from.$this->headerLn;
      $headerStr .= 'ReplyTo: '.$from.$this->headerLn;
    }

    $msgType = $this->getMimeType(
      $this->hasBodyText(),
      $this->hasBodyHTML(),
      $this->hasImages(),
      $this->hasAttachments()
    );

    $bodyStr = '';
    if ($msgType['multipart']) {
      $boundary = $this->createBoundary();
      $headerStr .= 'MIME-Version: 1.0'.$this->headerLn;
      $headerStr .= 'Content-Type: '.$msgType['mimetype'].';'.$this->headerLn.
        $this->indentChar.'boundary="'.$boundary.'"';

      $bodyStr .= 'This is a multi-part message in MIME format.'.$this->ln;
      $bodyStr .= $this->getMessagePart($msgType, $boundary);
    } else {
      $headerStr .= 'Content-Type: '.$msgType['mimetype'];
      $headerStr .= '; charset='.$this->charset.$this->headerLn;
      if ($this->hasBodyHTML()) {
        $headerStr .= 'Content-Transfer-Encoding: '.$this->encodingHTML;
        $bodyStr .= $this->encodeString($this->bodyHTML, $this->encodingHTML);
      } else {
        $headerStr .= 'Content-Transfer-Encoding: '.$this->encodingText;
        $bodyStr .= $this->encodeString($this->body, $this->encodingText);
      }
    }
    return array('header' => $headerStr, 'body' => $bodyStr);
  }

  /**
  * get the next part of a multipart message
  *
  * @param array $msgPartType
  * @param string $boundary
  * @access public
  * @return string
  */
  function getMessagePart($msgPartType, $boundary) {
    $result = '';
    switch ($msgPartType['mimetype']) {
    case 'multipart/mixed':
      $msgSubPartType = $this->getMimeType(
        $this->hasBodyText(), $this->hasBodyHTML(), $this->hasImages()
      );
      $boundarySubPart = $this->createBoundary();
      $result .= $this->getBoundaryStart(
        $boundary, '', $msgSubPartType['mimetype'], '', $boundarySubPart
      );
      $result .= $this->getMessagePart($msgSubPartType, $boundarySubPart);
      $result .= $this->getAttachments($boundary);
      $result .= $this->getBoundaryEnd($boundary).$this->ln;
      break;
    case 'multipart/related':
      $msgSubPartType = $this->getMimeType($this->hasBodyText(), $this->hasBodyHTML());
      $boundarySubPart = $this->createBoundary();
      $result .= $this->getBoundaryStart(
        $boundary, '', $msgSubPartType['mimetype'], '', $boundarySubPart
      );
      $result .= $this->getMessagePart($msgSubPartType, $boundarySubPart);
      $result .= $this->getImages($boundary);
      $result .= $this->getBoundaryEnd($boundary).$this->ln;
      break;
    case 'multipart/alternative' :
      $result .= $this->getBoundaryStart($boundary, '', 'text/plain', $this->encodingText);
      $result .= $this->encodeString($this->body, $this->encodingText).$this->ln;
      $result .= $this->getBoundaryStart($boundary, '', 'text/html', $this->encodingHTML);
      $result .= $this->encodeString($this->bodyHTML, $this->encodingHTML).$this->ln;
      $result .= $this->getBoundaryEnd($boundary).$this->ln;
      break;
    case 'text/html' :
      $result .= $this->encodeString($this->bodyHTML, $this->encodingHTML).$this->ln;
      break;
    case 'text/plain' :
      $result .= $this->encodeString($this->body, $this->encodingText).$this->ln;
      break;
    }

    return $result;
  }

  /**
  * output the mail to debug this object
  *
  * @access public
  */
  function debugMessage() {
    $mailContent = $this->getMessage();
    $mailContent['to'] = $this->getAddressHeader($this->addressTo);
    $mailContent['subject'] = $this->encodeHeader($this->subject, 8);
    return $mailContent;
  }

  /**
  * sends email and encodes headers if necessary
  * subject or body may be empty if templates are used; if set, they override template settings
  *
  * @param string $to receipients email adress,  taken from $this->addressTo if empty
  * @param string $subject subject of message, taken from $this->subject if empty
  * @param string $body subject of message, taken from $this->body if empty
  * @access public
  * @return boolean mail sent
  */
  function send($to = NULL, $subject = NULL, $body = NULL) {
    if (isset($to)) {
      unset($this->addressTo);
      $this->addAddress($to);
    }
    if (isset($subject) && trim($subject) != '') {
      $this->setSubject($subject);
    }
    if (isset($body) && trim($body) != '') {
      $this->setBody($body);
    }

    if (0 === strpos(PHP_OS, 'WIN')) {
      $mailContent = $this->getMessage(TRUE);
      $result = @mail(
        $this->getAddressEmails($this->addressTo),
        $this->encodeHeader($this->subject, 8),
        $mailContent['body'],
        $mailContent['header']
      );
    } else {
      $safeMode = ini_get('safe_mode');
      $mailContent = $this->getMessage(FALSE);
      if (\PapayaFilterFactory::isEmail($this->_returnPath) &&
          !$safeMode) {
        $result = @mail(
          $this->getAddressHeader($this->addressTo),
          $this->encodeHeader($this->subject, 8),
          $mailContent['body'],
          $mailContent['header'],
          '-f '.$this->_returnPath
        );
      } else {
        $result = @mail(
          $this->getAddressHeader($this->addressTo),
          $this->encodeHeader($this->subject, 8),
          $mailContent['body'],
          $mailContent['header']
        );
      }
    }
    if (!$result && $this->papaya()->hasObject('messages')) {
      $this->addMsg(MSG_ERROR, 'Email could not be sent.');
    }
    return $result;
  }
}

