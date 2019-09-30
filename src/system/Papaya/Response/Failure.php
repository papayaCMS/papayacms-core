<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\Response;

use Papaya\Response;
use Papaya\Utility;

/**
 * @package Papaya-Library
 * @subpackage Response
 */
class Failure extends \Papaya\Response {

  /**
   * Error template
   *
   * @var string
   */
  protected $_template =
    '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
     <html>
       <head>
         <title>{%status%} - {%message%}</title>
         <style type="text/css">
         <!--
         body {
           background-color: #FFF;
           font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
         }
         th, td {
           font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
         }
         pre {
           color: gray;
           font-weight: bold;
           font-size: 1.4em;
         }
         a {
           color: #95A41A;
           white-space: nowrap;
           font-size: 0.8em;
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
             <th colspan="2">Error in page request!</th>
           </tr>
           <tr valign="top">
             <td align="center"><pre>{%artwork%}</pre></td>
           </tr>
           <tr>
             <th><h3>{%message%}</h3></th>
           </tr>
           <tr>
             <th>{%status%}.{%identifier%}</th>
           </tr>
           <tr valign="top">
             <th>
               <hr style="border: none; border-bottom: 1px solid black;">
               <h4><a href="http://{%host%}/">http://{%host%}/</a></h4>
             </th>
           </tr>
         </table>
       </div>
       </body>
     </html>';

  private $_errorMessage;
  /**
   * @var int|string
   */
  private $_errorIdentifier;

  /**
   * @param string $errorMessage
   * @param int|string $errorIdentifier
   * @param int $status http status code (default 500)
   */
  public function __construct($errorMessage, $errorIdentifier = 0, $status = Status::INTERNAL_SERVER_ERROR_500) {
    $this->_errorMessage = $errorMessage;
    $this->_errorIdentifier = $errorIdentifier;
    $this->setStatus($status);
  }

  /**
   * @return Content\Text
   */
  public function createContent() {
    $replace = [
      '{%status%}' => Utility\Text\XML::escape($this->getStatus()),
      '{%artwork%}' => Utility\Text\ASCII\Artwork::get($this->getStatus()),
      '{%identifier%}' => Utility\Text\XML::escape($this->_errorIdentifier),
      '{%message%}' => Utility\Text\XML::escape($this->_errorMessage),
      '{%host%}' => Utility\Text\XML::escape(Utility\Server\Name::get()),
    ];
    return new Response\Content\Text(
      \strtr(
        $this->_template, $replace
      )
    );
  }

  public function send($end = TRUE, $force = TRUE) {
    $this->setCache(self::CACHE_NONE);
    parent::send($end, $force);
  }
}
