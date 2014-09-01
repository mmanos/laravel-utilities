<?php namespace Mmanos\Installer\Console;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrepareCommand extends \Symfony\Component\Console\Command\Command
{
	/**
	 * Configure the command options.
	 *
	 * @return void
	 */
	protected function configure()
	{
		$this->setName('prepare')
			->setDescription('Prepare an exsiting Laravel application');
	}
	
	/**
	 * Execute the command.
	 *
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * 
	 * @return void
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('<info>Preparing application...</info>');
		
		$directory = getcwd();
		
		$this->createClassesDir($directory, $output)
			->installHelpers($directory, $output)
			->configureEventCallbacks($directory, $output)
			->configureHttpExceptionsHandler($directory, $output)
			->configureResponseHeaders($directory, $output)
			->setTimezone($directory, $output)
			->setCompany($directory, $output)
			->createAssetDirectories($directory, $output)
			->installCasset($directory, $output)
			->installBootstrap($directory, $output)
			->installJquery($directory, $output)
			->installIEResponsive($directory, $output)
			->createLayout($directory, $output)
			->configureMVC($directory, $output);
		
		exec('composer dump-autoload', $out);
		echo implode("\n", $out) . "\n";
		
		$output->writeln('<comment>Application preapred!</comment>');
	}
	
	/**
	 * Create a classes directory and autoload it.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function createClassesDir($directory, $output)
	{
		File::mkdir($directory.'/app/classes');
		File::gitkeep($directory.'/app/classes');
		
		File::replace_once(
			$directory.'/app/start/global.php',
			"app_path().'/models',",
			"app_path().'/models',\n\tapp_path().'/classes',"
		);
		
		File::replace_once(
			$directory.'/composer.json',
			"\"app/models\",",
			"\"app/models\",\n\t\t\t\"app/classes\","
		);
		
		$output->writeln('app/classes directory created and autoloaded');
		
		return $this;
	}
	
	/**
	 * Install helpers file and load it.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function installHelpers($directory, $output)
	{
		if (!File::exists($directory.'/app/helpers.php')) {
			File::copy(
				dirname(__FILE__).'/../stubs/app/helpers.stub',
				$directory.'/app/helpers.php'
			);
		}
		
		File::append_once(
			$directory.'/app/start/global.php',
			File::get(dirname(__FILE__).'/../stubs/app/start/global.helpers.partial')
		);
		
		$output->writeln('app/helpers.php file installed');
		
		return $this;
	}
	
	/**
	 * Install events file and load it.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function configureEventCallbacks($directory, $output)
	{
		if (!File::exists($directory.'/app/events.php')) {
			File::copy(
				dirname(__FILE__).'/../stubs/app/events.stub',
				$directory.'/app/events.php'
			);
		}
		
		File::append_once(
			$directory.'/app/start/global.php',
			File::get(dirname(__FILE__).'/../stubs/app/start/global.events.partial')
		);
		
		$output->writeln('app/events.php file installed');
		
		return $this;
	}
	
	/**
	 * Configure handler for http exceptions.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function configureHttpExceptionsHandler($directory, $output)
	{
		File::append_once(
			$directory.'/app/start/global.php',
			File::get(dirname(__FILE__).'/../stubs/app/start/global.httpexceptions.partial')
		);
		
		$output->writeln('http exception handler added');
		
		return $this;
	}
	
	/**
	 * Configure response headers in after filter.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function configureResponseHeaders($directory, $output)
	{
		File::append_once(
			$directory.'/app/filters.php',
			File::get(dirname(__FILE__).'/../stubs/app/filters.headers.partial')
		);
		
		$output->writeln('response headers support added');
		
		return $this;
	}
	
	/**
	 * Set app timezone to America/Chicago.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function setTimezone($directory, $output)
	{
		File::replace(
			$directory.'/app/config/app.php',
			"'timezone' => 'UTC',",
			"'timezone' => 'America/Chicago',"
		);
		
		$output->writeln('app timezone set to America/Chicago');
		
		return $this;
	}
	
	/**
	 * Set company name in app config.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function setCompany($directory, $output)
	{
		File::config_append_once(
			$directory.'/app/config/app.php',
			"'company' => 'Laravel Site'"
		);
		
		$output->writeln('company name set in app config');
		
		return $this;
	}
	
	/**
	 * Create asset directories and structure.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function createAssetDirectories($directory, $output)
	{
		File::mkdir($directory.'/public/assets');
		File::mkdir($directory.'/public/assets/css');
		File::mkdir($directory.'/public/assets/js');
		File::mkdir($directory.'/public/assets/img');
		File::mkdir($directory.'/public/assets/libs');
		
		$output->writeln('asset directories created');
		
		return $this;
	}
	
	/**
	 * Install the Casset package.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function installCasset($directory, $output)
	{
		if (!File::has($directory.'/composer.json', 'mmanos/laravel-casset')) {
			exec('composer require mmanos/laravel-casset:1.2.*', $out);
			echo implode("\n", $out) . "\n";
		}
		
		if (!File::has($directory.'/app/config/app.php', 'Mmanos\Casset\CassetServiceProvider')) {
			File::replace_once(
				$directory.'/app/config/app.php',
				"'Illuminate\Workbench\WorkbenchServiceProvider',",
				"'Illuminate\Workbench\WorkbenchServiceProvider',\n\t\t'Mmanos\Casset\CassetServiceProvider',"
			);
		}
		
		if (!File::has($directory.'/app/config/app.php', "'Casset'")) {
			File::replace_once(
				$directory.'/app/config/app.php',
				"=> 'Illuminate\Support\Facades\View',",
				"=> 'Illuminate\Support\Facades\View',\n\t\t'Casset'          => 'Mmanos\Casset\Casset',"
			);
		}
		
		File::mkdir($directory.'/public/assets/cache');
		File::writable($directory.'/public/assets/cache');
		File::gitignore($directory.'/public/assets/cache');
		
		$output->writeln('casset installed');
		
		return $this;
	}
	
	/**
	 * Install the Twitter Bootstrap package.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function installBootstrap($directory, $output)
	{
		if (!File::has($directory.'/composer.json', 'twbs/bootstrap')) {
			exec('composer require twbs/bootstrap:3.2.0', $out);
			echo implode("\n", $out) . "\n";
		}
		
		$output->writeln('twitter bootstrap installed');
		
		return $this;
	}
	
	/**
	 * Install the jQuery package.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function installJquery($directory, $output)
	{
		if (!File::has($directory.'/composer.json', 'frameworks/jquery')) {
			exec('composer require frameworks/jquery:1.11.1', $out);
			echo implode("\n", $out) . "\n";
		}
		
		$output->writeln('jquery installed');
		
		return $this;
	}
	
	/**
	 * Install the ie8 responsive libraries.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function installIEResponsive($directory, $output)
	{
		if (!File::exists($directory.'/public/assets/libs/html5shiv/html5shiv.min.js')) {
			File::mkdir($directory.'/public/assets/libs/html5shiv');
			
			File::copy(
				'http://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.2/html5shiv.min.js',
				$directory.'/public/assets/libs/html5shiv/html5shiv.min.js'
			);
		}
		if (!File::exists($directory.'/public/assets/libs/respond/respond.min.js')) {
			File::mkdir($directory.'/public/assets/libs/respond');
			
			File::copy(
				'http://cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js',
				$directory.'/public/assets/libs/respond/respond.min.js'
			);
		}
		
		$output->writeln('ie8 responsive support installed');
		
		return $this;
	}
	
	/**
	 * Create a default layout.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function createLayout($directory, $output)
	{
		if (!File::exists($directory.'/app/views/layouts/default.blade.php')) {
			File::copy(
				dirname(__FILE__).'/../stubs/app/views/layouts/default.blade.stub',
				$directory.'/app/views/layouts/default.blade.php'
			);
		}
		if (!File::exists($directory.'/app/views/layouts/default/navbar.blade.php')) {
			File::copy(
				dirname(__FILE__).'/../stubs/app/views/layouts/default/navbar.blade.stub',
				$directory.'/app/views/layouts/default/navbar.blade.php'
			);
		}
		
		if (!File::exists($directory.'/public/assets/css/mixins.less')) {
			File::copy(
				dirname(__FILE__).'/../stubs/public/assets/css/mixins.stub',
				$directory.'/public/assets/css/mixins.less'
			);
		}
		
		File::mkdir($directory.'/public/assets/css/layouts');
		File::mkdir($directory.'/public/assets/css/layouts/default');
		
		if (!File::exists($directory.'/public/assets/css/layouts/default/variables.less')) {
			File::copy(
				$directory.'/vendor/twbs/bootstrap/less/variables.less',
				$directory.'/public/assets/css/layouts/default/variables.less'
			);
		}
		if (!File::exists($directory.'/public/assets/css/layouts/default/bootstrap.less')) {
			File::copy(
				dirname(__FILE__).'/../stubs/public/assets/css/layouts/default/bootstrap.stub',
				$directory.'/public/assets/css/layouts/default/bootstrap.less'
			);
		}
		if (!File::exists($directory.'/public/assets/css/layouts/default/layout.less')) {
			File::copy(
				dirname(__FILE__).'/../stubs/public/assets/css/layouts/default/layout.stub',
				$directory.'/public/assets/css/layouts/default/layout.less'
			);
		}
		
		$output->writeln('default layout created');
		
		return $this;
	}
	
	/**
	 * Configure the controllers and views.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function configureMVC($directory, $output)
	{
		File::delete($directory.'/app/controllers/HomeController.php');
		File::delete($directory.'/app/views/hello.php');
		
		if (!File::exists($directory.'/app/controllers/IndexController.php')) {
			File::copy(
				dirname(__FILE__).'/../stubs/app/controllers/IndexController.stub',
				$directory.'/app/controllers/IndexController.php'
			);
		}
		
		File::put(
			$directory.'/app/controllers/BaseController.php',
			File::get(dirname(__FILE__).'/../stubs/app/controllers/BaseController.stub')
		);
		
		if (!File::exists($directory.'/app/views/index/index.blade.php')) {
			File::copy(
				dirname(__FILE__).'/../stubs/app/views/index/index.blade.stub',
				$directory.'/app/views/index/index.blade.php'
			);
		}
		if (!File::exists($directory.'/app/views/error/index.blade.php')) {
			File::copy(
				dirname(__FILE__).'/../stubs/app/views/error/index.blade.stub',
				$directory.'/app/views/error/index.blade.php'
			);
		}
		
		File::mkdir($directory.'/public/assets/css/controllers');
		File::mkdir($directory.'/public/assets/js/controllers');
		
		if (!File::exists($directory.'/public/assets/css/controllers/index/index.less')) {
			File::copy(
				dirname(__FILE__).'/../stubs/public/assets/css/controllers/index/index.stub',
				$directory.'/public/assets/css/controllers/index/index.less'
			);
		}
		if (!File::exists($directory.'/public/assets/js/controllers/index/index.js')) {
			File::copy(
				dirname(__FILE__).'/../stubs/public/assets/js/controllers/index/index.stub',
				$directory.'/public/assets/js/controllers/index/index.js'
			);
		}
		
		File::replace(
			$directory.'/app/routes.php',
			"Route::get('/', function()\n{\n\treturn View::make('hello');\n});",
			"Route::get('/', 'IndexController@getIndex');\nRoute::controller('index', 'IndexController');"
		);
		
		$output->writeln('mvc configured');
		
		return $this;
	}
}
