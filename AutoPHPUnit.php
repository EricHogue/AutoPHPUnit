<?php
/**
 * Automaticaly run the PHPUnit tests everytime a file is saved
 *
 *
 * Make it recursive
 * Clear the screen
 * Get the path
 */
class AutoPHPUnit
{
	/**
	 * @var string
	 */
	private $pathToTest;

	/**
	 * @var string
	 */
	private $autoPHPUnitPath;



	/**
	 * Class constructor
	 */
	public function __construct()
	{
		$this->pathToTest = getcwd();
		$this->autoPHPUnitPath = realpath(dirname(__FILE__));
	}

	/**
	 * Run the program
	 *
	 * @return void
	 */
	public function run() {
		$this->clearConsole();
		$this->watchFiles();
	}

	/**
	 * Wait for a change in the file system
	 *
	 * @return void
	 */
	protected function watchFiles() {
		require $this->autoPHPUnitPath . '/RecursiveFolderWatcher.php';

		$o = new RecursiveFolderWatcher($this->pathToTest, array($this, 'runUnitTests'));
		$o->watch();
	}

	/**
	 * Callback function to run the unit tests
	 *
	 * @return void
	 */
	public function runUnitTests() {
		$this->clearConsole();

		$command = 'phpunit -c ' . $this->pathToTest . '/phpunit.xml ' . $this->pathToTest;
		error_log($command);
		echo `$command`;
	}

	/**
	 * Clear the console
	 *
	 * @return void
	 */
	protected function clearConsole() {
		passthru('clear');
	}
}

$runner = new AutoPHPUnit();
$runner->run();