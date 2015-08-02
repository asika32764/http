<?php
/**
 * Part of Asika\Http project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Asika\Http\Test\Transport;

use Asika\Http\Transport\CurlTransport;

/**
 * Test class of CurlTransport
 *
 * @since {DEPLOY_VERSION}
 */
class CurlTransportTest extends AbstractTransportTest
{
	/**
	 * Property options.
	 *
	 * @var  array
	 */
	protected $options = array(
		'options' => array(CURLOPT_SSL_VERIFYPEER => false)
	);

	/**
	 * Test instance.
	 *
	 * @var CurlTransport
	 */
	protected $instance;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->instance = new CurlTransport;

		parent::setUp();
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
}
