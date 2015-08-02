<?php
/**
 * Part of Asika\Http project.
 *
 * @copyright  Copyright (C) 2015 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Asika\Http\Helper;

use Psr\Http\Message\UploadedFileInterface;

/**
 * The ServerHelper class.
 * 
 * @since  {DEPLOY_VERSION}
 */
abstract class ServerHelper
{
	/**
	 * Recursively validate the structure in an uploaded files array.
	 *
	 * Every file should be an UploadedFileInterface object.
	 *
	 * @param   array  $files  Files array.
	 *
	 * @return  boolean
	 */
	public static function validateUploadedFiles(array $files)
	{
		foreach ($files as $file)
		{
			if (is_array($file))
			{
				static::validateUploadedFiles($file);

				continue;
			}

			if (! $file instanceof UploadedFileInterface)
			{
				return false;
			}
		}

		return true;
	}
}
