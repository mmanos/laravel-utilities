<?php namespace Mmanos\Installer\Console;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NewCommand extends \Symfony\Component\Console\Command\Command
{
	/**
	 * Configure the command options.
	 *
	 * @return void
	 */
	protected function configure()
	{
		$this->setName('new')
			->setDescription('Create a new Laravel application')
			->addArgument('name', InputArgument::REQUIRED);
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
		$this->verifyApplicationDoesntExist(
			$directory = getcwd().'/'.$input->getArgument('name'),
			$output
		);
		
		$output->writeln('<info>Crafting application...</info>');
		
		$this->install($input->getArgument('name'));
		
		$this->setPermissions($directory, $output);
		
		$output->writeln('<comment>Application ready! Build something amazing.</comment>');
	}
	
	/**
	 * Verify that the application does not already exist.
	 *
	 * @param string          $directory
	 * @param OutputInterface $output
	 * 
	 * @return void
	 */
	protected function verifyApplicationDoesntExist($directory, $output)
	{
		if (is_dir($directory)) {
			$output->writeln('<error>Application already exists!</error>');
			
			exit(1);
		}
		
		return $this;
	}
	
	/**
	 * Install the Laravel application template.
	 *
	 * @param string $name
	 * 
	 * @return $this
	 */
	protected function install($name)
	{
		exec("composer create-project laravel/laravel $name --prefer-dist", $output);
		
		echo implode("\n", $output) . "\n";
		
		return $this;
	}
	
	/**
	 * Set write permissions on app/storage directories.
	 *
	 * @param string          $install_path
	 * @param OutputInterface $output
	 * 
	 * @return $this
	 */
	protected function setPermissions($install_path, $output)
	{
		chmod($install_path.'/app/storage/cache', 0777);
		chmod($install_path.'/app/storage/logs', 0777);
		chmod($install_path.'/app/storage/meta', 0777);
		chmod($install_path.'/app/storage/sessions', 0777);
		chmod($install_path.'/app/storage/views', 0777);
		
		$output->writeln('<info>/app/storage/* directories made writable</info>');
		
		return $this;
	}
}
