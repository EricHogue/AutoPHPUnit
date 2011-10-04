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
	 * @var string
	 */
	private $configFile = '';




	/**
	 * Class constructor
	 */
	public function __construct()
	{
		$this->readParams();
		$this->autoPHPUnitPath = realpath(dirname(__FILE__));
	}

	/**
	 * Run the program
	 *
	 * @return void
	 */
	public function run() {
		$this->runUnitTests();
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


		$configOption = $this->getConfigParamForPHPUnit();

		//--testdox
		$command = 'phpunit ' . $configOption . ' '  . $this->pathToTest;
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

	/**
	 * Read the command line params
	 *
	 * @return void
	 */
	private function readParams() {
		$options = getopt('c::p::', array('config::', 'path::'));

		$this->readConfigFile($options);
		$this->readPathToTest($options);
	}

	/**
	 * Read the config file from the options
	 *
	 * @return void
	 */
	private function readConfigFile(array $options) {
		$configFile = '';

		if (array_key_exists('c', $options)) {
			$configFile = $options['c'];
		} else if (array_key_exists('config', $options)) {
			$configFile = $options['config'];
		}

		$this->configFile = $configFile;
	}

	/**
	 * Read the path to test
	 *
	 * @return void
	 */
	private function readPathToTest(array $options) {
		$path = '';

		if (array_key_exists('p', $options)) {
			$path = $options['p'];
		} else if (array_key_exists('path', $options)) {
			$path = $options['path'];
		} else {
			$path = getcwd();
		}

		$this->pathToTest = $path;
	}

	/**
	 * Return the config params for the phpunit command
	 *
	 * @return string
	 */
	private function getConfigParamForPHPUnit() {
		$path = $this->configFile;

		if (0 === strlen($path)) {
			$path = $this->pathToTest . '/phpunit.xml';
		}

		if (file_exists($path)) {
			return '-c ' . $path;
		}

		return '';
	}

}

$runner = new AutoPHPUnit();
$runner->run();
