<?php namespace Mmanos\Installer\Console;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FrontendCommand extends \Symfony\Component\Console\Command\Command
{
	/**
	 * Configure the command options.
	 *
	 * @return void
	 */
	protected function configure()
	{
		$this->setName('frontend')
			->setDescription('Configure an existing Laravel application with helpful front-end functionality');
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
		
		$this->createAssetDirectories($directory, $output)
			->installCasset($directory, $output)
			->installBootstrap($directory, $output)
			->installJquery($directory, $output)
			->installFontAwesome($directory, $output)
			->installIEResponsive($directory, $output)
			->createLayout($directory, $output)
			->configureMVC($directory, $output)
			->createEmailLayout($directory, $output);
		
		exec('composer dump-autoload', $out);
		echo implode("\n", $out) . "\n";
		
		$output->writeln('<comment>Front end installed!</comment>');
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
			$output->writeln('<info>Installing mmanos/laravel-casset...</info>');
			exec('composer require mmanos/laravel-casset:1.4.*', $out);
			echo implode("\n", $out) . "\n";
		}
		
		if (!File::has($directory.'/app/config/app.php', 'Mmanos\Casset\CassetServiceProvider')) {
			File::replaceOnce(
				$directory.'/app/config/app.php',
				"'Illuminate\Workbench\WorkbenchServiceProvider',",
				"'Illuminate\Workbench\WorkbenchServiceProvider',\n\t\t'Mmanos\Casset\CassetServiceProvider',"
			);
		}
		
		if (!File::has($directory.'/app/config/app.php', "'Casset'")) {
			File::replaceOnce(
				$directory.'/app/config/app.php',
				"=> 'Illuminate\Support\Facades\View',",
				"=> 'Illuminate\Support\Facades\View',\n\t\t'Casset'            => 'Mmanos\Casset\Facades\Casset',"
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
			$output->writeln('<info>Installing twbs/bootstrap...</info>');
			exec('composer require twbs/bootstrap:3.3.1', $out);
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
			$output->writeln('<info>Installing frameworks/jquery...</info>');
			exec('composer require frameworks/jquery:1.11.1', $out);
			echo implode("\n", $out) . "\n";
		}
		
		$output->writeln('jquery installed');
		
		return $this;
	}
	
	/**
	 * Install the Font Awesome library.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function installFontAwesome($directory, $output)
	{
		if (!File::exists($directory.'/public/assets/libs/font-awesome')) {
			File::copyFolder(
				dirname(__FILE__).'/../stubs/public/assets/libs/font-awesome',
				$directory.'/public/assets/libs/font-awesome'
			);
		}
		
		$output->writeln('font-awesome installed');
		
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
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/views/layouts/default.blade.stub',
			$directory.'/app/views/layouts/default.blade.php'
		);
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/views/layouts/default/navbar.blade.stub',
			$directory.'/app/views/layouts/default/navbar.blade.php'
		);
		
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/views/common/alerts.blade.stub',
			$directory.'/app/views/common/alerts.blade.php'
		);
		
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/public/assets/css/mixins.stub',
			$directory.'/public/assets/css/mixins.less'
		);
		
		File::mkdir($directory.'/public/assets/css/layouts');
		File::mkdir($directory.'/public/assets/css/layouts/default');
		
		File::copyIfNone(
			$directory.'/vendor/twbs/bootstrap/less/variables.less',
			$directory.'/public/assets/css/layouts/default/variables.less'
		);
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/public/assets/css/layouts/default/bootstrap.stub',
			$directory.'/public/assets/css/layouts/default/bootstrap.less'
		);
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/public/assets/css/layouts/default/layout.stub',
			$directory.'/public/assets/css/layouts/default/layout.less'
		);
		
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
		
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/controllers/IndexController.stub',
			$directory.'/app/controllers/IndexController.php'
		);
		
		File::put(
			$directory.'/app/controllers/BaseController.php',
			File::get(dirname(__FILE__).'/../stubs/app/controllers/BaseController.stub')
		);
		
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/views/index/index.blade.stub',
			$directory.'/app/views/index/index.blade.php'
		);
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/views/error/index.blade.stub',
			$directory.'/app/views/error/index.blade.php'
		);
		
		File::mkdir($directory.'/public/assets/css/controllers');
		File::mkdir($directory.'/public/assets/js/controllers');
		
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/public/assets/css/controllers/index/index.stub',
			$directory.'/public/assets/css/controllers/index/index.less'
		);
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/public/assets/js/controllers/index/index.stub',
			$directory.'/public/assets/js/controllers/index/index.js'
		);
		
		File::replace(
			$directory.'/app/routes.php',
			"Route::get('/', function()\n{\n\treturn View::make('hello');\n});",
			"Route::get('/', 'IndexController@getIndex');\nRoute::controller('index', 'IndexController');"
		);
		
		$output->writeln('mvc configured');
		
		return $this;
	}
	
	/**
	 * Create a default email layout.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function createEmailLayout($directory, $output)
	{
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/views/emails/layouts/default.blade.stub',
			$directory.'/app/views/emails/layouts/default.blade.php'
		);
		
		$output->writeln('default email layout created');
		
		return $this;
	}
}
