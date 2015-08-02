<?php
/**
 * Part of Asika\Http project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Asika\Http\Test\Transport;

use Psr\Http\Message\StreamInterface;
use Asika\Http\Request;
use Asika\Http\Stream\Stream;
use Asika\Http\Stream\StringStream;
use Asika\Http\Transport\AbstractTransport;
use Asika\Http\Uri\PsrUri;
use Asika\Http\Uri\UriHelper;

/**
 * Test class of CurlTransport
 *
 * @since {DEPLOY_VERSION}
 */
abstract class AbstractTransportTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Property options.
	 *
	 * @var  array
	 */
	protected $options = array(
		'options' => array()
	);

	/**
	 * Test instance.
	 *
	 * @var AbstractTransport
	 */
	protected $instance;

	/**
	 * Property downloadFile.
	 *
	 * @var  string
	 */
	protected $destFile;

	/**
	 * setUpBeforeClass
	 *
	 * @return  void
	 */
	public static function setUpBeforeClass()
	{
		if (!defined('ASIKA_HTTP_TEST_URL'))
		{
			static::markTestSkipped('No ASIKA_HTTP_TEST_URL provided');
		}
	}

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		if (!$this->instance->isSupported())
		{
			$this->markTestSkipped(get_class($this->instance) . ' driver not supported.');
		}

		$this->destFile = __DIR__ . '/downloaded.tmp';
	}

	/**
	 * createRequest
	 *
	 * @param StreamInterface $stream
	 *
	 * @return Request
	 */
	protected function createRequest($stream = null)
	{
		return new Request($stream ? : new StringStream);
	}

	/**
	 * testRequestGet
	 *
	 * @return  void
	 */
	public function testRequestGet()
	{
		$request = $this->createRequest();

		$request = $request->withUri(new PsrUri(ASIKA_HTTP_TEST_URL))
			->withMethod('GET');

		$response = $this->instance->request($request);

		$this->assertEquals(200, $response->getStatusCode());
		$this->assertJson($response->getBody()->getContents());

		$request = $this->createRequest();

		$request = $request->withUri(new PsrUri(ASIKA_HTTP_TEST_URL . '?foo=bar&baz[3]=yoo'))
			->withMethod('GET');

		$response = $this->instance->request($request);

		$data = json_decode($response->getBody()->getContents(), true);
		$this->assertEquals(array('foo' => 'bar', 'baz' => array(3 => 'yoo')), $data['_GET']);
	}

	/**
	 * testBadDomainGet
	 *
	 * @return  void
	 *
	 * @expectedException  \RuntimeException
	 */
	public function testBadDomainGet()
	{
		$request = $this->createRequest();

		$request = $request->withUri(new PsrUri('http://not.exists.url/flower.sakura'))
			->withMethod('GET');

		$this->instance->request($request);
	}

	/**
	 * testBadPathGet
	 *
	 * @return  void
	 */
	public function testBadPathGet()
	{
		$request = $this->createRequest();

		$request = $request->withUri(new PsrUri(dirname(ASIKA_HTTP_TEST_URL) . '/wrong.php'))
			->withMethod('POST');

		$request->getBody()->write(UriHelper::buildQuery(array('foo' => 'bar')));

		$response = $this->instance->request($request);

		$this->assertEquals(404, $response->getStatusCode());
		$this->assertEquals('Not Found', $response->getReasonPhrase());
	}

	/**
	 * testRequestPost
	 *
	 * @return  void
	 */
	public function testRequestPost()
	{
		$request = $this->createRequest();

		$request = $request->withUri(new PsrUri(ASIKA_HTTP_TEST_URL))
			->withMethod('POST');

		$request->getBody()->write(UriHelper::buildQuery(array('foo' => 'bar')));

		$response = $this->instance->request($request);

		$data = json_decode($response->getBody()->getContents(), true);

		$this->assertEquals(array('foo' => 'bar'), $data['_POST']);
	}

	/**
	 * testRequestPut
	 *
	 * @return  void
	 */
	public function testRequestPut()
	{
		$request = $this->createRequest();

		$request = $request->withUri(new PsrUri(ASIKA_HTTP_TEST_URL))
			->withMethod('PUT');

		$request->getBody()->write(UriHelper::buildQuery(array('foo' => 'bar')));

		$response = $this->instance->request($request);

		$data = json_decode($response->getBody()->getContents(), true);

		$this->assertEquals(array('foo' => 'bar'), $data['data']);
		$this->assertEquals('PUT', $data['_SERVER']['REQUEST_METHOD']);
	}

	/**
	 * testRequestCredentials
	 *
	 * @return  void
	 */
	public function testRequestCredentials()
	{
		$request = $this->createRequest();

		$uri = new PsrUri(ASIKA_HTTP_TEST_URL);
		$uri = $uri->withUserInfo('username', 'pass1234');

		$request = $request->withUri($uri)
			->withMethod('GET');

		$response = $this->instance->request($request);

		$data = json_decode($response->getBody()->getContents(), true);

		$this->assertEquals('username', $data['_SERVER']['PHP_AUTH_USER']);
		$this->assertEquals('pass1234', $data['_SERVER']['PHP_AUTH_PW']);
	}

	/**
	 * testRequestPostScalar
	 *
	 * @return  void
	 */
	public function testRequestPostScalar()
	{
		$request = $this->createRequest();

		$request = $request->withUri(new PsrUri(ASIKA_HTTP_TEST_URL . '?foo=bar'))
			->withMethod('POST');

		$request->getBody()->write('flower=sakura');

		$response = $this->instance->request($request);

		$data = json_decode($response->getBody()->getContents(), true);

		$this->assertEquals(array('foo' => 'bar'), $data['_GET']);
		$this->assertEquals(array('flower' => 'sakura'), $data['_POST']);
	}

	public function testDownload()
	{
		$this->unlinkDownloaded();

		$this->assertFileNotExists((string) $this->destFile);

		$request = $this->createRequest(new Stream);

		$src = dirname(ASIKA_HTTP_TEST_URL) . '/download_stub.txt';

		$request = $request->withUri(new PsrUri($src))
			->withMethod('GET');

		$response = $this->instance->download($request, $this->destFile);

		$this->assertEquals('This is test download file.', trim(file_get_contents($this->destFile)));
	}

	/**
	 * unlinkDownloaded
	 *
	 * @return  void
	 */
	protected function unlinkDownloaded()
	{
		if (is_file($this->destFile))
		{
			unlink($this->destFile);
		}
	}
}
