<?php
/**
 * Part of Asika\Http project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Asika\Http\Uri\Tests;

use Asika\Http\Uri\UriHelper;

/**
 * Tests for the Asika\Http\Uri\UriHelper class.
 *
 * @since  1.0
 */
class UriHelperTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test the parse_url method.
	 *
	 * @return  array
	 *
	 * @covers  Asika\Http\Uri\UriHelper::parseUrl
	 * @since   1.0
	 */
	public function testParseUrl()
	{
		$url = 'http://localhost/Windwalker_development/j16_trunk/administrator/index.php?option=com_contact&view=contact&layout=edit&id=5';
		$expected = parse_url($url);
		$actual = UriHelper::parseUrl($url);
		$this->assertEquals($expected, $actual, 'Line: ' . __LINE__ . ' Results should be equal');

		// Test all parts of query
		$url = 'https://john:doe@www.google.com:80/folder/page.html#id?var=kay&var2=key&true';
		$expected = parse_url($url);
		$actual = UriHelper::parseUrl($url);
		$this->assertEquals($expected, $actual, 'Line: ' . __LINE__ . ' Results should be equal');

		// Test special characters in URL
		$url = 'http://Windwalker.org/mytestpath/È';
		$expected = parse_url($url);

		// Fix up path for UTF-8 characters
		$expected['path'] = '/mytestpath/È';
		$actual = UriHelper::parseUrl($url);
		$this->assertEquals($expected, $actual, 'Line: ' . __LINE__ . ' Results should be equal');

		// Test special characters in URL
		$url = 'http://mydomain.com/!*\'();:@&=+$,/?%#[]" \\';
		$expected = parse_url($url);
		$actual = UriHelper::parseUrl($url);
		$this->assertEquals($expected, $actual, 'Line: ' . __LINE__ . ' Results should be equal');

		// Test url encoding in URL
		$url = 'http://mydomain.com/%21%2A%27%28%29%3B%3A%40%26%3D%24%2C%2F%3F%25%23%5B%22%20%5C';
		$expected = parse_url($url);
		$actual = UriHelper::parseUrl($url);
		$this->assertEquals($expected, $actual, 'Line: ' . __LINE__ . ' Results should be equal');

		// Test a mix of the above
		$url = 'http://john:doe@mydomain.com:80/%È21%25È3*%(';
		$expected = parse_url($url);

		// Fix up path for UTF-8 characters
		$expected['path'] = '/%È21%25È3*%(';
		$actual = UriHelper::parseUrl($url);
		$this->assertEquals($expected, $actual, 'Line: ' . __LINE__ . ' Results should be equal');

		// Test invalild URL
		$url = 'http:///mydomain.com';
		$expected = parse_url($url);
		$actual = UriHelper::parseUrl($url);
		$this->assertEquals($expected, $actual, 'Line: ' . __LINE__ . ' Results should be equal');
	}
}
