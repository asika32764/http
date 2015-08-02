<?php
/**
 * Part of Asika\Http project.
 *
 * @copyright  Copyright (C) 2015 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Asika\Http\Stream;

use Psr\Http\Message\StreamInterface;

/**
 * The PhpInputStream class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class PhpInputStream extends Stream implements StreamInterface
{
	/**
	 * Property cache.
	 *
	 * @var  string
	 */
	protected $cache;

	/**
	 * Property reachedEof.
	 *
	 * @var  boolean
	 */
	protected $reachedEof;

	/**
	 * Class init.
	 *
	 * @param   string|resource  $stream  The stream resource cursor.
	 */
	public function __construct($stream = 'php://input')
	{
		parent::__construct($stream, self::MODE_READ_ONLY_FROM_BEGIN);
	}

	/**
	 * Reads all data from the stream into a string, from the beginning to end.
	 *
	 * This method MUST attempt to seek to the beginning of the stream before
	 * reading data and read the stream until the end is reached.
	 *
	 * Warning: This could attempt to load a large amount of data into memory.
	 *
	 * This method MUST NOT raise an exception in order to conform with PHP's
	 * string casting operations.
	 *
	 * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
	 *
	 * @return string
	 */
	public function __toString()
	{
		if ($this->reachedEof)
		{
			return $this->cache;
		}

		$this->getContents();

		return $this->cache;
	}

	/**
	 * Returns whether or not the stream is writable.
	 *
	 * @return bool
	 */
	public function isWritable()
	{
		return false;
	}

	/**
	 * Read data from the stream.
	 *
	 * @param   int  $length  Read up to $length bytes from the object and return
	 *                        them. Fewer than $length bytes may be returned if underlying stream
	 *                        call returns fewer bytes.
	 *
	 * @return   string  Returns the data read from the stream, or an empty string
	 *                   if no bytes are available.
	 *
	 * @throws \RuntimeException if an error occurs.
	 */
	public function read($length)
	{
		$content = parent::read($length);

		if ($content && !$this->reachedEof)
		{
			$this->cache .= $content;
		}

		if ($this->eof())
		{
			$this->reachedEof = true;
		}

		return $content;
	}

	/**
	 * Returns the remaining contents in a string
	 *
	 * @return string
	 *
	 * @throws \RuntimeException if unable to read or an error occurs while reading.
	 */
	public function getContents($maxLength = -1)
	{
		if ($this->reachedEof)
		{
			return $this->cache;
		}

		$this->cache .= $contents = stream_get_contents($this->resource, $maxLength);

		if ($maxLength === -1 || $this->eof())
		{
			$this->reachedEof = true;
		}

		return $contents;
	}
}
