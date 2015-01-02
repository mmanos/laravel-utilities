<?php namespace Mmanos\Installer\Console;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AuthorizeCommand extends \Symfony\Component\Console\Command\Command
{
	/**
	 * Configure the command options.
	 *
	 * @return void
	 */
	protected function configure()
	{
		$this->setName('authorize')
			->setDescription('Add authorization support to an existing Laravel application');
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
		
		$this->createAuthModels($directory, $output)
			->createAuthController($directory, $output)
			->createSignupController($directory, $output)
			->installAcl($directory, $output);
		
		exec('composer dump-autoload', $out);
		echo implode("\n", $out) . "\n";
		
		$output->writeln('<comment>Authorize installed!</comment>');
	}
	
	/**
	 * Create all migrations, models, services, validators, and event handlers for auth.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function createAuthModels($directory, $output)
	{
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/database/migrations/2015_01_01_000000_create_authorize_tables.stub',
			$directory.'/app/database/migrations/2015_01_01_000000_create_authorize_tables.php'
		);
		File::copy(
			dirname(__FILE__).'/../stubs/app/models/User.stub',
			$directory.'/app/models/User.php'
		);
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/models/Role.stub',
			$directory.'/app/models/Role.php'
		);
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/models/Service/User.stub',
			$directory.'/app/models/Service/User.php'
		);
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/models/Service/User/Auth.stub',
			$directory.'/app/models/Service/User/Auth.php'
		);
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/models/Validator/User.stub',
			$directory.'/app/models/Validator/User.php'
		);
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/models/Validator/User/Auth.stub',
			$directory.'/app/models/Validator/User/Auth.php'
		);
		
		File::mkdir($directory.'/app/classes/Event');
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/classes/Event/User/Auth.stub',
			$directory.'/app/classes/Event/User/Auth.php'
		);
		File::delete($directory.'/app/classes/.gitkeep');
		
		File::appendOnce(
			$directory.'/app/events.php',
			"\nEvent::subscribe(new Event\User\Auth);\n"
		);
		
		$output->writeln('migrations/models/services/validators/callback-handlers installed');
		
		return $this;
	}
	
	/**
	 * Create the auth controller and resources.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function createAuthController($directory, $output)
	{
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/controllers/AuthController.stub',
			$directory.'/app/controllers/AuthController.php'
		);
		
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/views/auth/login.blade.stub',
			$directory.'/app/views/auth/login.blade.php'
		);
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/views/auth/forgot.blade.stub',
			$directory.'/app/views/auth/forgot.blade.php'
		);
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/views/auth/reset.blade.stub',
			$directory.'/app/views/auth/reset.blade.php'
		);
		
		File::appendOnce(
			$directory.'/app/routes.php',
			File::get(dirname(__FILE__).'/../stubs/app/routes.auth.partial')
		);
		
		if (File::exists($directory.'/app/views/layouts/default/navbar.blade.php')) {
			File::replace(
				$directory.'/app/views/layouts/default/navbar.blade.php',
				"url_li('login', 'Log in')",
				"action_li('AuthController@getLogin', 'Log in')"
			);
			File::replace(
				$directory.'/app/views/layouts/default/navbar.blade.php',
				"url_li('logout', 'Logout')",
				"action_li('AuthController@getLogout', 'Logout')"
			);
		}
		
		File::delete($directory.'/app/views/emails/auth/reminder.blade.php');
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/views/emails/auth/forgot.blade.stub',
			$directory.'/app/views/emails/auth/forgot.blade.php'
		);
		
		$output->writeln('auth controller and resources installed');
		
		return $this;
	}
	
	/**
	 * Create the auth controller and resources.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function createSignupController($directory, $output)
	{
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/controllers/SignupController.stub',
			$directory.'/app/controllers/SignupController.php'
		);
		
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/views/signup/index.blade.stub',
			$directory.'/app/views/signup/index.blade.php'
		);
		
		File::appendOnce(
			$directory.'/app/routes.php',
			File::get(dirname(__FILE__).'/../stubs/app/routes.signup.partial')
		);
		
		if (File::exists($directory.'/app/views/layouts/default/navbar.blade.php')) {
			File::replace(
				$directory.'/app/views/layouts/default/navbar.blade.php',
				"url_li('signup', 'Sign up')",
				"action_li('SignupController@getIndex', 'Sign up')"
			);
		}
		
		$output->writeln('signup controller and resources installed');
		
		return $this;
	}
	
	/**
	 * Install ACL functionality.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function installAcl($directory, $output)
	{
		if (!File::has($directory.'/composer.json', 'zendframework/zend-permissions-acl')) {
			$output->writeln('<info>Installing zendframework/zend-permissions-acl...</info>');
			exec('composer require zendframework/zend-permissions-acl:2.2.6', $out);
			echo implode("\n", $out) . "\n";
		}
		
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/classes/Acl.stub',
			$directory.'/app/classes/Acl.php'
		);
		
		File::copyIfNone(
			dirname(__FILE__).'/../stubs/app/config/acl.stub',
			$directory.'/app/config/acl.php'
		);
		
		File::appendOnce(
			$directory.'/app/start/global.php',
			File::get(dirname(__FILE__).'/../stubs/app/start/global.acl.partial')
		);
		
		File::appendOnce(
			$directory.'/app/filters.php',
			File::get(dirname(__FILE__).'/../stubs/app/filters.acl.partial')
		);
		
		$output->writeln('acl installed');
		
		return $this;
	}
}
