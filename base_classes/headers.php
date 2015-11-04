<?PHP

class headers
{
	public static $statusCodes = array(
		200 => '200 OK', //Standard response for successful HTTP requests.
		201 => '201 Created', //The request has been fulfilled and resulted in a new resource being created.
		202 => '202 Accepted', //The request has been accepted for processing, but the processing has not been completed.
		203 => '203 Non-Authoritative Information', //The server successfully processed the request, but is returning information that may be from another source.
		204 => '204 No Content', //The server successfully processed the request, but is not returning any content.
		205 => '205 Reset Content', //The server successfully processed the request, but is not returning any content. Plz reset document view.
		206 => '206 Partial Content', //The server is delivering only part of the resource due to a range header sent by the client.
		207 => '207 Multi-Status', //The message body that follows is an XML message and can contain a number of separate response codes, depending on how many sub-requests were made.
		208 => '208 Already Reported', //The members of a DAV binding have already been enumerated in a previous reply to this request, and are not being included again.
		226 => '226 IM Used', //The response is a representation of the result of one or more instance-manipulations applied to the current instance.
		300 => '300 Multiple Choices', //Indicates multiple options for the resource that the client may follow.
		301 => '301 Moved Permanently', //This and all future requests should be directed to the given URI.
		302 => '302 Found', //Moved Temp
		303 => '303 See Other', //Similar to Temp except 1.1 standard
		304 => '304 Not Modified', //Indicates the resource has not been modified since last requested.
		305 => '305 Use Proxy', //Use a proxy
		306 => '306 Switch Proxy', //Subsequent requests should use the specified proxy.
		307 => '307 Temporary Redirect', //In this occasion, the request should be repeated with another URI, but future requests can still use the original URI.
		308 => '308 Resume Incomplete', //This code is used in the Resumable HTTP Requests Proposal to resume aborted PUT or POST requests.
		400 => '400 Bad Request', //The request cannot be fulfilled due to bad syntax.
		401 => '401 Unauthorized', //Similar to 403 Forbidden, but specifically for use when authentication is possible but has failed or not yet been provided
		402 => '402 Payment Required', //Requires payment, duh
		403 => '403 Forbidden', //The request was a legal request, but the server is refusing to respond to it.
		404 => '404 Not Found', //The requested resource could not be found but may be available again in the future.
		405 => '405 Method Not Allowed', //A request was made of a resource using a request method not supported by that resource.
		406 => '406 Not Acceptable', //The requested resource is only capable of generating content not acceptable according to the Accept headers sent in the request.
		407 => '407 Proxy Authentication Required', //The client must first authenticate itself with the proxy.
		408 => '408 Request Timeout', //The server timed out waiting for the request.
		409 => '409 Conflict', //Indicates that the request could not be processed because of conflict in the request.
		410 => '410 Gone', //Indicates that the resource requested is no longer available and will not be available again.
		411 => '411 Length Required', //The request did not specify the length of its content, which is required by the requested resource.
		412 => '412 Precondition Failed', //The server does not meet one of the preconditions that the requester put on the request.
		413 => '413 Request Entity Too Large', //The request is larger than the server is willing or able to process.
		414 => '414 Request-URI Too Long', //The URI provided was too long for the server to process.
		415 => '415 Unsupported Media Type', //The request entity has a media type which the server or resource does not support.
		416 => '416 Requested Range Not Satisfiable', //The client has asked for a portion of the file, but the server cannot supply that portion.
		417 => '417 Expectation Failed', //The server cannot meet the requirements of the Expect request-header field.
		418 => "418 I'm a teapot", //April FOOOOLS
		422 => '422 Unprocessable Entity', //The request was well-formed but was unable to be followed due to semantic errors.
		423 => '423 Locked', //The resource that is being accessed is locked.
		424 => '424 Failed Dependency', //The request failed due to failure of a previous request.
		425 => '425 Unordered Collection', //Defined in drafts of "WebDAV Advanced Collections Protocol".
		426 => '426 Upgrade Required', //The client should switch to a different protocol.
		428 => '428 Precondition Required', //The origin server requires the request to be conditional.
		429 => '429 Too Many Requests', //The user has sent too many requests in a given amount of time.
		431 => '431 Request Header Fields Too Large', //The server is unwilling to process the request because either an individual header field, or all the header fields collectively, are too large.
		500 => '500 Internal Server Error', //A generic error message, given when no more specific message is suitable.
		501 => '501 Not Implemented', //The server either does not recognise the request method, or it lacks the ability to fulfill the request.
		502 => '502 Bad Gateway', //The server was acting as a gateway or proxy and received an invalid response from the upstream server.
		503 => '503 Service Unavailable', //The server is currently unavailable (because it is overloaded or down for maintenance).
		504 => '504 Gateway Timeout', //The server was acting as a gateway or proxy and did not receive a timely response from the upstream server.
		505 => '505 HTTP Version Not Supported', //The server does not support the HTTP protocol version used in the request.
		506 => '506 Variant Also Negotiates', //Transparent content negotiation for the request results in a circular reference.
		507 => '507 Insufficient Storage', //The server is unable to store the representation needed to complete the request.
		508 => '508 Loop Detected', //The server detected an infinite loop while processing the request.
		509 => '509 Bandwidth Limit Exceeded', //NO MORE TUBES FOR U
		510 => '510 Not Extended', //Further extensions to the request are required for the server to fulfill it.
		511 => '511 Network Authentication Required', //The client needs to authenticate to gain network access.
		598 => '598 Network read timeout error', //Network read timeout error.
		599 => '599 Network connect timeout error', //Network connect timeout error.
	);
	public static $responseHeaders = array(
		'Accept-Ranges',
		'Age',
		'Allow',
		'Cache-Control',
		'Connection',
		'Content-Encoding',
		'Content-Language',
		'Content-Length',
		'Content-Location',
		'Content-MD5',
		'Content-Disposition',
		'Content-Range',
		'Content-Type',
		'Date',
		'ETag',
		'Expires',
		'Last-Modified',
		'Link',
		'Location',
		'P3P',
		'Pragma',
		'Proxy-Authenticate',
		'Refresh',
		'Retry-After',
		'Server',
		'Set-Cookie',
		'Strict-Transport-Security',
		'Trailer',
		'Transfer-Encoding',
		'Vary',
		'Via',
		'Warning',
		'WWW-Authenticate',
	);
	public $statusHeader;
	public static $standardResponseHeaders = array(
		'Content-Type' => 'text/plain; charset=utf-8',
		'Cache-Control' => 'max-age=3600',
	);

	public static function printHeaders($object)
	{
		if (isset($object->statusHeader))
		{
			if (in_array($object->statusHeader, self::$statusCodes))
			{
				header('HTTP/1.1 ' . self::$statusCodes[$object->statusHeader]);
			}
		}

		if (isset($object->responseHeaders))
		{
			if (is_array($object->responseHeaders))
			{
				if (!empty($object->responseHeaders))
				{
					foreach ($object->responseHeaders as $header => $content)
					{
						if (in_array($header, self::$responseHeaders))
						{
							header($header . ': ' . $content);
						}
					}
				}
			}
		}

		if (!empty(self::$standardResponseHeaders))
		{
			foreach (self::$standardResponseHeaders as $header => $content)
			{
				if (!isset($object->responseHeaders[$header]))
				{
					header($header . ': ' . $content);
				}
			}
		}
	}
}
