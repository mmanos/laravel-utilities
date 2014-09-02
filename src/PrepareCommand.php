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
			->setDescription('Prepare an exsiting Laravel application by adding helpful functionality');
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
			->configureEnvDetection($directory, $output)
			->installHelpers($directory, $output)
			->configureEventCallbacks($directory, $output)
			->configureHttpExceptionsHandler($directory, $output)
			->configureResponseHeaders($directory, $output)
			->setTimezone($directory, $output)
			->setCompany($directory, $output)
			->createCustomModel($directory, $output)
			->ignoreLocalConfigs($directory, $output)
			->addCarbonAlias($directory, $output);
		
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
		
		File::replaceOnce(
			$directory.'/app/start/global.php',
			"app_path().'/models',",
			"app_path().'/models',\n\tapp_path().'/classes',"
		);
		
		File::replaceOnce(
			$directory.'/composer.json',
			"\"app/models\",",
			"\"app/models\",\n\t\t\t\"app/classes\","
		);
		
		$output->writeln('app/classes directory created and autoloaded');
		
		return $this;
	}
	
	/**
	 * Configure local environment detection.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function configureEnvDetection($directory, $output)
	{
		File::replaceOnce(
			$directory.'/bootstrap/start.php',
			"'local' => array('homestead'),",
			"'local' => array('*.local'),"
		);
		
		$output->writeln('local env detection configured');
		
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
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/helpers.stub',
			$directory.'/app/helpers.php'
		);
		
		File::appendOnce(
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
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/events.stub',
			$directory.'/app/events.php'
		);
		
		File::appendOnce(
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
		File::appendOnce(
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
		File::appendOnce(
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
		File::configAppendOnce(
			$directory.'/app/config/app.php',
			"'company' => 'Laravel Site'"
		);
		
		$output->writeln('company name set in app config');
		
		return $this;
	}
	
	/**
	 * Create a custom model class for other models to extend.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function createCustomModel($directory, $output)
	{
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/models/Model.stub',
			$directory.'/app/models/Model.php'
		);
		
		$output->writeln('custom model installed');
		
		return $this;
	}
	
	/**
	 * Ignore local configs in git.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function ignoreLocalConfigs($directory, $output)
	{
		File::appendOnce(
			$directory.'/.gitignore',
			'/app/config/local'
		);
		
		$output->writeln('/app/config/local added to .gitignore');
		
		return $this;
	}
	
	/**
	 * Add Carbon class alias.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function addCarbonAlias($directory, $output)
	{
		if (!File::has($directory.'/app/config/app.php', "'Carbon'")) {
			File::replaceOnce(
				$directory.'/app/config/app.php',
				"=> 'Illuminate\Support\Facades\View',",
				"=> 'Illuminate\Support\Facades\View',\n\t\t'Carbon'          => 'Carbon\Carbon',"
			);
		}
		
		$output->writeln('carbon class alias added');
		
		return $this;
	}
}
