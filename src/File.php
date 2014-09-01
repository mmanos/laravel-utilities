<?php namespace Mmanos\Installer\Console;

use Exception;

class File
{
	/**
	 * Return true if the given path exists. False, otherwise.
	 *
	 * @param string $path
	 * 
	 * @return boolean
	 */
	public static function exists($path)
	{
		return file_exists($path);
	}
	
	/**
	 * Create a directory, if it does not already exist.
	 *
	 * @param string  $path
	 * @param integer $permissions
	 * 
	 * @return void
	 * @throws Exception
	 */
	public static function mkdir($path, $permissions = 0755)
	{
		if (self::exists($path)) {
			return;
		}
		
		if (!mkdir($path, $permissions)) {
			throw new Exception('Error creating directory: ' . $path);
		}
	}
	
	/**
	 * Write to a file.
	 *
	 * @param string $path
	 * @param string $content
	 * 
	 * @return void
	 * @throws Exception
	 */
	public static function put($path, $content)
	{
		self::mkdir(dirname($path));
		
		if (false === file_put_contents($path, $content)) {
			throw new Exception('Error writing to file: ' . $path);
		}
	}
	
	/**
	 * Get the contents of a file.
	 *
	 * @param string $path
	 * @param mixed  $default
	 * 
	 * @return mixed
	 */
	public static function get($path, $default = null)
	{
		if (!self::exists($path)) {
			return $default;
		}
		
		return file_get_contents($path);
	}
	
	/**
	 * Return true if the given file contains the given search string.
	 *
	 * @param string $path
	 * @param mixed  $search
	 * 
	 * @return mixed
	 */
	public static function has($path, $search)
	{
		$data = self::get($path, '');
		
		return (strstr($data, $search) === false) ? false : true;
	}
	
	/**
	 * Delete the given file.
	 *
	 * @param string $path
	 * 
	 * @return void
	 * @throws Exception
	 */
	public static function delete($path)
	{
		if (!self::exists($path)) {
			return;
		}
		
		if (!unlink($path)) {
			throw new Exception('Error deleting file: ' . $path);
		}
	}
	
	/**
	 * Copy a file.
	 *
	 * @param string $from
	 * @param string $to
	 * 
	 * @return void
	 * @throws Exception
	 */
	public static function copy($from, $to)
	{
		self::mkdir(dirname($to));
		
		if (!copy($from, $to)) {
			throw new Exception('Error copying file to: ' . $to);
		}
	}
	
	/**
	 * Modify a file.
	 *
	 * @param string $path
	 * @param string $search
	 * @param string $replace
	 * 
	 * @return void
	 */
	public static function replace($path, $search, $replace)
	{
		$content = str_replace($search, $replace, self::get($path, ''));
		
		self::put($path, $content);
	}
	
	/**
	 * Modify a file, if not already modified.
	 *
	 * @param string $path
	 * @param string $search
	 * @param string $replace
	 * 
	 * @return void
	 */
	public static function replace_once($path, $search, $replace)
	{
		$content = self::get($path, '');
		
		foreach ((array) $replace as $r) {
			if (strstr($content, $r) !== false) {
				return;
			}
		}
		
		self::replace($path, $search, $replace);
	}
	
	/**
	 * Append to a file.
	 *
	 * @param string $path
	 * @param string $content
	 * 
	 * @return void
	 * @throws Exception
	 */
	public static function append($path, $content)
	{
		if (!file_put_contents($path, $content, FILE_APPEND)) {
			throw new Exception('Error appending to file: ' . $path);
		}
	}
	
	/**
	 * Append to a file, if not already modified.
	 *
	 * @param string $path
	 * @param string $content
	 * 
	 * @return void
	 */
	public static function append_once($path, $content)
	{
		$data = self::get($path, '');
		
		if (strstr($data, $content) !== false) {
			return;
		}
		
		self::append($path, $content);
	}
	
	/**
	 * Append to a config file, if not already modified.
	 *
	 * @param string $path
	 * @param string $content Ex: "'key' => array(1, 2, 3)"
	 * 
	 * @return void
	 */
	public static function config_append_once($path, $content)
	{
		$data = self::get($path, '');
		
		if (strstr($data, $content) !== false) {
			return;
		}
		
		self::replace(
			$path,
			"\n);",
			"\n\t$content,\n);"
		);
	}
	
	/**
	 * Create a .gitignore file in the requested directory, set to ignore all content.
	 *
	 * @param string $path
	 * 
	 * @return void
	 */
	public static function gitignore($path)
	{
		$path = rtrim($path, '/');
		
		File::put($path.'/.gitignore', "*\n!.gitignore");
	}
	
	/**
	 * Create a .gitkeep file in the requested directory.
	 *
	 * @param string $path
	 * 
	 * @return void
	 */
	public static function gitkeep($path)
	{
		$path = rtrim($path, '/');
		
		File::put($path.'/.gitkeep', '');
	}
	
	/**
	 * Make the given path writable by the webserver.
	 *
	 * @param string  $path
	 * @param integer $mode
	 * 
	 * @return void
	 * @throws Exception
	 */
	public static function writable($path, $mode = 0777)
	{
		if (!chmod($path, $mode)) {
			throw new Exception('Error making path writable: ' . $path);
		}
	}
}