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
namespace Papaya\Response {

  interface Status {
    const CONTINUE_100 = 100;

    const SWITCHING_PROTOCOLS_101 = 101;

    const PROCESSING_102 = 102;

    const OK_200 = 200;

    const CREATED_201 = 201;

    const ACCEPTED_202 = 202;

    const NON_AUTHORITATIVE_INFORMATION_203 = 203;

    const NO_CONTENT_204 = 204;

    const RESET_CONTENT_205 = 205;

    const PARTIAL_CONTENT_206 = 206;

    const MULTI_STATUS_207 = 207;

    const MULTIPLE_CHOICES_300 = 300;

    const MOVED_PERMANENTLY_301 = 301;

    const FOUND_302 = 302;

    const SEE_OTHER_303 = 303;

    const NOT_MODIFIED_304 = 304;

    const USE_PROXY_305 = 305;

    const TEMPORARY_REDIRECT_307 = 307;

    const BAD_REQUEST_400 = 400;

    const AUTHORIZATION_REQUIRED_401 = 401;

    const PAYMENT_REQUIRED_402 = 402;

    const FORBIDDEN_403 = 403;

    const NOT_FOUND_404 = 404;

    const METHOD_NOT_ALLOWED_405 = 405;

    const NOT_ACCEPTABLE_406 = 406;

    const PROXY_AUTHENTICATION_REQUIRED_407 = 407;

    const REQUEST_TIME_OUT_408 = 408;

    const CONFLICT_409 = 409;

    const GONE_410 = 410;

    const LENGTH_REQUIRED_411 = 411;

    const PRECONDITION_FAILED_412 = 412;

    const REQUEST_ENTITY_TOO_LARGE_413 = 413;

    const REQUEST_URI_TOO_LARGE_414 = 414;

    const UNSUPPORTED_MEDIA_TYPE_415 = 415;

    const REQUESTED_RANGE_NOT_SATISFIABLE_416 = 416;

    const EXPECTATION_FAILED_417 = 417;

    const UNPROCESSABLE_ENTITY_422 = 422;

    const LOCKED_423 = 423;

    const FAILED_DEPENDENCY_424 = 424;

    const NO_CODE_425 = 425;

    const UPGRADE_REQUIRED_426 = 426;

    const INTERNAL_SERVER_ERROR_500 = 500;

    const METHOD_NOT_IMPLEMENTED_501 = 501;

    const BAD_GATEWAY_502 = 502;

    const SERVICE_TEMPORARILY_UNAVAILABLE_503 = 503;

    const GATEWAY_TIME_OUT_504 = 504;

    const HTTP_VERSION_NOT_SUPPORTED_505 = 505;

    const VARIANT_ALSO_NEGOTIATES_506 = 506;

    const INSUFFICIENT_STORAGE_507 = 507;

    const NOT_EXTENDED_510 = 510;

    const LABELS = [
      self::CONTINUE_100 => 'Continue',
      self::SWITCHING_PROTOCOLS_101 => 'Switching Protocols',
      self::PROCESSING_102 => 'Processing',
      self::OK_200 => 'OK',
      self::CREATED_201 => 'Created',
      self::ACCEPTED_202 => 'Accepted',
      self::NON_AUTHORITATIVE_INFORMATION_203 => 'Non-Authoritative Information',
      self::NO_CONTENT_204 => 'No Content',
      self::RESET_CONTENT_205 => 'Reset Content',
      self::PARTIAL_CONTENT_206 => 'Partial Content',
      self::MULTI_STATUS_207 => 'Multi-Status',
      self::MULTIPLE_CHOICES_300 => 'Multiple Choices',
      self::MOVED_PERMANENTLY_301 => 'Moved Permanently',
      self::FOUND_302 => 'Found',
      self::SEE_OTHER_303 => 'See Other',
      self::NOT_MODIFIED_304 => 'Not Modified',
      self::USE_PROXY_305 => 'Use Proxy',
      self::TEMPORARY_REDIRECT_307 => 'Temporary Redirect',
      self::BAD_REQUEST_400 => 'Bad Request',
      self::AUTHORIZATION_REQUIRED_401 => 'Authorization Required',
      self::PAYMENT_REQUIRED_402 => 'Payment Required',
      self::FORBIDDEN_403 => 'Forbidden',
      self::NOT_FOUND_404 => 'Not Found',
      self::METHOD_NOT_ALLOWED_405 => 'Method Not Allowed',
      self::NOT_ACCEPTABLE_406 => 'Not Acceptable',
      self::PROXY_AUTHENTICATION_REQUIRED_407 => 'Proxy Authentication Required',
      self::REQUEST_TIME_OUT_408 => 'Request Time-out',
      self::CONFLICT_409 => 'Conflict',
      self::GONE_410 => 'Gone',
      self::LENGTH_REQUIRED_411 => 'Length Required',
      self::PRECONDITION_FAILED_412 => 'Precondition Failed',
      self::REQUEST_ENTITY_TOO_LARGE_413 => 'Request Entity Too Large',
      self::REQUEST_URI_TOO_LARGE_414 => 'Request-URI Too Large',
      self::UNSUPPORTED_MEDIA_TYPE_415 => 'Unsupported Media Type',
      self::REQUESTED_RANGE_NOT_SATISFIABLE_416 => 'Requested Range Not Satisfiable',
      self::EXPECTATION_FAILED_417 => 'Expectation Failed',
      self::UNPROCESSABLE_ENTITY_422 => 'Unprocessable Entity',
      self::LOCKED_423 => 'Locked',
      self::FAILED_DEPENDENCY_424 => 'Failed Dependency',
      self::NO_CODE_425 => 'No code',
      self::UPGRADE_REQUIRED_426 => 'Upgrade Required',
      self::INTERNAL_SERVER_ERROR_500 => 'Internal Server Error',
      self::METHOD_NOT_IMPLEMENTED_501 => 'Method Not Implemented',
      self::BAD_GATEWAY_502 => 'Bad Gateway',
      self::SERVICE_TEMPORARILY_UNAVAILABLE_503 => 'Service Temporarily Unavailable',
      self::GATEWAY_TIME_OUT_504 => 'Gateway Time-out',
      self::HTTP_VERSION_NOT_SUPPORTED_505 => 'HTTP Version Not Supported',
      self::VARIANT_ALSO_NEGOTIATES_506 => 'Variant Also Negotiates',
      self::INSUFFICIENT_STORAGE_507 => 'Insufficient Storage',
      self::NOT_EXTENDED_510 => 'Not Extended'
    ];
  }
}
