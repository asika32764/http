<?php
/**
 * Part of Asika\Http project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Asika\Http\Test;

use Asika\Http\AbstractMessage;
use Asika\Http\Stream\Stream;
use Asika\Http\Test\Stub\StubMessage;

/**
 * Test class of AbstractMessage
 *
 * @since {DEPLOY_VERSION}
 */
class AbstractMessageTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test instance.
	 *
	 * @var AbstractMessage
	 */
	protected $message;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->message = new StubMessage;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
	}

	/**
	 * Method to test getProtocolVersion().
	 *
	 * @return void
	 *
	 * @covers Asika\Http\AbstractMessage::getProtocolVersion
	 * @covers Asika\Http\AbstractMessage::withProtocolVersion
	 */
	public function testWithAndSetProtocolVersion()
	{
		$this->assertEquals('1.1', $this->message->getProtocolVersion());

		$message = $this->message->withProtocolVersion('1.0');

		$this->assertNotSame($this->message, $message);
		$this->assertEquals('1.0', $message->getProtocolVersion());
	}

	/**
	 * Method to test getHeader().
	 *
	 * @return void
	 *
	 * @covers Asika\Http\AbstractMessage::getHeader
	 * @covers Asika\Http\AbstractMessage::withHeader
	 */
	public function testWithAndGetHeader()
	{
		$message = $this->message->withHeader('Content-Type', 'text/json');

		$this->assertNotSame($this->message, $message);
		$this->assertEquals(array('text/json'), $message->getHeader('Content-Type'));
		$this->assertEquals(array('text/json'), $message->getHeader('content-type'));

		$message = $this->message->withHeader('X-Foo', array('Foo', 'Bar'));

		$this->assertNotSame($this->message, $message);
		$this->assertEquals(array('Foo', 'Bar'), $message->getHeader('X-Foo'));
	}

	/**
	 * Method to test hasHeader().
	 *
	 * @return void
	 *
	 * @covers Asika\Http\AbstractMessage::hasHeader
	 */
	public function testHasHeader()
	{
		$this->assertFalse($this->message->hasHeader('X-Foo'));

		$message = $this->message->withHeader('Content-Type', 'text/json');

		$this->assertTrue($message->hasHeader('Content-Type'));
		$this->assertTrue($message->hasHeader('content-type'));
	}

	/**
	 * Method to test getHeaders().
	 *
	 * @return void
	 *
	 * @covers Asika\Http\AbstractMessage::getHeaders
	 */
	public function testGetHeaders()
	{
		$this->assertEquals(array(), $this->message->getHeaders());

		$message = $this->message->withHeader('X-Foo', array('Foo', 'Bar'));
		$message = $message->withHeader('X-Bar', array('Flower', 'Sakura'));

		$expected = array(
			'X-Foo' => array('Foo', 'Bar'),
			'X-Bar' => array('Flower', 'Sakura'),
		);

		$this->assertEquals($expected, $message->getHeaders());
	}

	/**
	 * Method to test getHeaderLine().
	 *
	 * @return void
	 *
	 * @covers Asika\Http\AbstractMessage::getHeaderLine
	 */
	public function testGetHeaderLine()
	{
		$this->assertEquals('', $this->message->getHeaderLine('X-Foo'));

		$message = $this->message->withHeader('X-Foo', array('Foo', 'Bar'));

		$this->assertEquals('Foo,Bar', $message->getHeaderLine('X-Foo'));
		$this->assertEquals('Foo,Bar', $message->getHeaderLine('x-foo'));
	}

	/**
	 * Method to test withAddedHeader().
	 *
	 * @return void
	 *
	 * @covers Asika\Http\AbstractMessage::withAddedHeader
	 */
	public function testWithAddedHeader()
	{
		$message = $this->message->withAddedHeader('X-Foo', 'One');

		$this->assertNotSame($this->message, $message);
		$this->assertEquals(array('One'), $message->getHeader('X-Foo'));

		$message = $message->withAddedHeader('X-Foo', 'Two');

		$this->assertEquals(array('One', 'Two'), $message->getHeader('X-Foo'));

		$message = $message->withAddedHeader('X-Foo', array('Three', 'Four'));

		$this->assertEquals(array('One', 'Two', 'Three', 'Four'), $message->getHeader('X-Foo'));
	}

	/**
	 * Method to test withoutHeader().
	 *
	 * @return void
	 *
	 * @covers Asika\Http\AbstractMessage::withoutHeader
	 */
	public function testWithoutHeader()
	{
		$message = $this->message->withAddedHeader('X-Foo', 'One');

		$this->assertNotSame($this->message, $message);
		$this->assertEquals(array('One'), $message->getHeader('X-Foo'));

		$message2 = $message->withoutHeader('X-Foo');

		$this->assertNotSame($this->message, $message2);
		$this->assertEquals(array(), $message2->getHeader('X-Foo'));

		$message3 = $message->withoutHeader('x-foo');

		$this->assertNotSame($this->message, $message3);
		$this->assertEquals(array(), $message3->getHeader('X-Foo'));
	}

	/**
	 * Method to test getBody().
	 *
	 * @return void
	 *
	 * @covers Asika\Http\AbstractMessage::getBody
	 */
	public function testWithAndGetBody()
	{
		$message = $this->message->withBody(new Stream);

		$this->assertNotSame($this->message, $message);
		$this->assertInstanceOf('Psr\Http\Message\StreamInterface', $message->getBody());
	}
}
