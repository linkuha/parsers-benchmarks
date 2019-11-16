<?php

namespace helpers;

/**
 * Class ConfigHelper to store the configuration in an array
 *
 * Example to use:
 *
 * ConfigHelper::write('conf1.txt', array( 'setting_1' => 'foo' ));
 * $config = ConfigHelper::read('conf1.txt');
 * $config['setting_1'] = 'bar';
 * $config['setting_2'] = 'baz';
 * ConfigHelper::write('conf1.txt', $config);
 *
 * @package helpers
 */
class ConfigHelper
{
	public static function read($filename)
	{
		$config = include $filename;
		return $config;
	}
	public static function write($filename, array $config)
	{
		$config = var_export($config, true);
		file_put_contents($filename, "<?php return $config ;");
	}
}