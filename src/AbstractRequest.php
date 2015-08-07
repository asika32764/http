<?php
/**
 * Part of Asika\Http project.
 *
 * @copyright  Copyright (C) 2015 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Asika\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Asika\Http\Helper\HeaderHelper;
use Asika\Http\Helper\HttpValidationHelper;
use Asika\Http\Stream\Stream;
use Asika\Http\Uri\PsrUri;

/**
 * The AbstractRequest class.
 * 
 * @since  {DEPLOY_VERSION}
 */
abstract class AbstractRequest extends AbstractMessage implements RequestInterface, MessageInterface
{
	/**
	 * Property method.
	 *
	 * @var  string
	 */
	protected $method;

	/**
	 * Property uri.
	 *
	 * @var  UriInterface
	 */
	protected $uri;

	/**
	 * Property requestTarget.
	 *
	 * @var  string
	 */
	protected $requestTarget;

	/**
	 * Property allowMethods.
	 *
	 * @var  array
	 */
	protected $allowMethods = array(
		'CONNECT',
		'DELETE',
		'GET',
		'HEAD',
		'OPTIONS',
		'PATCH',
		'POST',
		'PUT',
		'TRACE',
	);

	/**
	 * Class init.
	 *
	 * @param string|UriInterface    $uri
	 * @param string                 $method
	 * @param string|StreamInterface $body
	 * @param array                  $headers
	 */
	public function __construct($uri = null, $method = null, $body = 'php://memory', $headers = array())
	{
		if (!$body instanceof StreamInterface)
		{
			$body = new Stream($body, Stream::MODE_READ_WRITE_RESET);
		}

		if (!$uri instanceof UriInterface)
		{
			$uri = new PsrUri((string) $uri);
		}

		foreach ($headers as $name => $value)
		{
			$value = HeaderHelper::allToArray($value);

			if (!HeaderHelper::arrayOnlyContainsString($value))
			{
				throw new \InvalidArgumentException('Header values should ony have string.');
			}

			if (!HeaderHelper::isValidName($name))
			{
				throw new \InvalidArgumentException('Invalid header name');
			}

			$normalized = strtolower($name);
			$this->headerNames[$normalized] = $name;
			$this->headers[$name] = $value;
		}

		$this->stream = $body;
		$this->method = $this->validateMethod($method);
		$this->uri    = $uri;
	}

	/**
	 * Retrieves the message's request target.
	 *
	 * Retrieves the message's request-target either as it will appear (for
	 * clients), as it appeared at request (for servers), or as it was
	 * specified for the instance (see withRequestTarget()).
	 *
	 * In most cases, this will be the origin-form of the composed URI,
	 * unless a value was provided to the concrete implementation (see
	 * withRequestTarget() below).
	 *
	 * If no URI is available, and no request-target has been specifically
	 * provided, this method MUST return the string "/".
	 *
	 * @return string
	 */
	public function getRequestTarget()
	{
		if ($this->requestTarget !== null)
		{
			return $this->requestTarget;
		}

		if (!$this->uri)
		{
			return '/';
		}

		$target = $this->uri->getPath();

		if ($this->uri->getQuery())
		{
			$target .= '?' . $this->uri->getQuery();
		}

		if (empty($target))
		{
			$target = '/';
		}

		return $target;
	}

	/**
	 * Return an instance with the specific request-target.
	 *
	 * If the request needs a non-origin-form request-target — e.g., for
	 * specifying an absolute-form, authority-form, or asterisk-form —
	 * this method may be used to create an instance with the specified
	 * request-target, verbatim.
	 *
	 * This method MUST be implemented in such a way as to retain the
	 * immutability of the message, and MUST return an instance that has the
	 * changed request target.
	 *
	 * @link http://tools.ietf.org/html/rfc7230#section-2.7 (for the various
	 *     request-target forms allowed in request messages)
	 *
	 * @param mixed $requestTarget
	 *
	 * @return static
	 * @throws \InvalidArgumentException if the request target is invalid.
	 */
	public function withRequestTarget($requestTarget)
	{
		if (preg_match('/\s/', $requestTarget))
		{
			throw new \InvalidArgumentException('RequestTarget cannot contain whitespace.');
		}

		$new = clone $this;
		$new->requestTarget = $requestTarget;

		return $new;
	}

	/**
	 * Retrieves the HTTP method of the request.
	 *
	 * @return string Returns the request method.
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * Return an instance with the provided HTTP method.
	 *
	 * While HTTP method names are typically all uppercase characters, HTTP
	 * method names are case-sensitive and thus implementations SHOULD NOT
	 * modify the given string.
	 *
	 * This method MUST be implemented in such a way as to retain the
	 * immutability of the message, and MUST return an instance that has the
	 * changed request method.
	 *
	 * @param string $method Case-sensitive method.
	 *
	 * @return static
	 * @throws \InvalidArgumentException for invalid HTTP methods.
	 */
	public function withMethod($method)
	{
		$method = $this->validateMethod($method);

		$new = clone $this;

		$new->method = $method;

		return $new;
	}

	/**
	 * Retrieves the URI instance.
	 *
	 * This method MUST return a UriInterface instance.
	 *
	 * @link http://tools.ietf.org/html/rfc3986#section-4.3
	 * @return UriInterface Returns a UriInterface instance
	 *     representing the URI of the request.
	 */
	public function getUri()
	{
		return $this->uri;
	}

	/**
	 * Returns an instance with the provided URI.
	 *
	 * This method MUST update the Host header of the returned request by
	 * default if the URI contains a host component. If the URI does not
	 * contain a host component, any pre-existing Host header MUST be carried
	 * over to the returned request.
	 *
	 * You can opt-in to preserving the original state of the Host header by
	 * setting `$preserveHost` to `true`. When `$preserveHost` is set to
	 * `true`, this method interacts with the Host header in the following ways:
	 *
	 * - If the the Host header is missing or empty, and the new URI contains
	 *   a host component, this method MUST update the Host header in the returned
	 *   request.
	 * - If the Host header is missing or empty, and the new URI does not contain a
	 *   host component, this method MUST NOT update the Host header in the returned
	 *   request.
	 * - If a Host header is present and non-empty, this method MUST NOT update
	 *   the Host header in the returned request.
	 *
	 * This method MUST be implemented in such a way as to retain the
	 * immutability of the message, and MUST return an instance that has the
	 * new UriInterface instance.
	 *
	 * @link http://tools.ietf.org/html/rfc3986#section-4.3
	 *
	 * @param UriInterface $uri          New request URI to use.
	 * @param bool         $preserveHost Preserve the original state of the Host header.
	 *
	 * @return static
	 */
	public function withUri(UriInterface $uri, $preserveHost = false)
	{
		$new = clone $this;
		$new->uri = $uri;

		if ($preserveHost)
		{
			return $new;
		}

		if (!$uri->getHost())
		{
			return $new;
		}

		$host = $uri->getHost();

		if ($uri->getPort())
		{
			$host .= ':' . $uri->getPort();
		}

		$new->headerNames['host'] = 'Host';
		$new->headers['Host'] = array($host);

		return $new;
	}

	/**
	 * validateMethod
	 *
	 * @param string $method
	 *
	 * @return  string
	 */
	protected function validateMethod($method)
	{
		if ($method === null)
		{
			return $method;
		}

		if (!is_string($method))
		{
			throw new \InvalidArgumentException('Method should be a string.');
		}

		$method = strtoupper($method);

		if (!in_array($method, $this->allowMethods))
		{
			throw new \InvalidArgumentException('Invalid HTTP method: ' . $method);
		}

		return $method;
	}
}
