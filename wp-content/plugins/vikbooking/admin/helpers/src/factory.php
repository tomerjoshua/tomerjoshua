<?php
/** 
 * @package     VikBooking
 * @subpackage  core
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Factory application class.
 *
 * @since 1.5
 */
final class VBOFactory
{
	/**
	 * Application configuration handler.
	 *
	 * @var VBOConfigRegistry
	 */
	private static $config;

	/**
	 * Application platform handler.
	 *
	 * @var VBOPlatformInterface
	 */
	private static $platform;

	/**
	 * Class constructor.
	 * @private This object cannot be instantiated. 
	 */
	private function __construct()
	{
		// never called
	}

	/**
	 * Class cloner.
	 * @private This object cannot be cloned.
	 */
	private function __clone()
	{
		// never called
	}

	/**
	 * Returns the current configuration object.
	 *
	 * @return 	VBOConfigRegistry
	 */
	public static function getConfig()
	{
		// check if config class is already instantiated
		if (is_null(static::$config))
		{
			// cache instantiation
			static::$config = new VBOConfigRegistryDatabase([
				'db' => JFactory::getDbo(),
			]);
		}

		return static::$config;
	}

	/**
	 * Returns the current platform handler.
	 *
	 * @return 	VBOPlatformInterface
	 */
	public static function getPlatform()
	{
		// check if config class is already instantiated
		if (is_null(static::$platform))
		{
			if (defined('ABSPATH'))
			{
				// running WordPress platform
				static::$platform = new VBOPlatformOrgWordpress();
			}
			else
			{
				// running Joomla platform
				static::$platform = new VBOPlatformOrgJoomla();
			}
		}

		return static::$platform;
	}
}
