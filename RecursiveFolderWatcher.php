<?php
/**
 * Watch a folder for any changes on files
 */
class RecursiveFolderWatcher
{
	/**
	 * @var string
	 */
	private $pathToWatch;

	/**
	 * @var resource
	 */
	private $inotify;

	/**
	 * @var array
	 */
	private $watchDescriptors = array();

	/**
	 * @var function
	 */
	private $callbackFunction;

	/**
	 * @var boolean
	 */
	private $testsHaveRunForThisWatch = false;


	/**
	 * Class constructor
	 */
	public function __construct($pathToWatch, $callbackFunction)
	{
		$this->pathToWatch = $pathToWatch;
		$this->callbackFunction = $callbackFunction;
	}


	/**
	 * Watch a folder for any changes in files
	 *
	 * @return void
	 */
	public function watch() {
		$this->inotify = inotify_init();

		$this->addWatch($this->pathToWatch);

		while (true) {
			$events = inotify_read($this->inotify);

			if ($events) {
				$this->testsHaveRunForThisWatch = false;
				array_walk($events, array($this, 'handleEvent'));
			}

			usleep(1000);
		}
	}

	/**
	 * Add a watch on a folder
	 *
	 * @return void
	 */
	protected function addWatch($folderPath) {
		$watchDescriptor = inotify_add_watch($this->inotify, $folderPath, IN_ALL_EVENTS);
		$this->watchDescriptors[$watchDescriptor] = $folderPath;

		if (is_dir($folderPath)) $this->addWatchToSubFolders($folderPath);
	}

	/**
	 * Add watches to sub folders
	 *
	 * @return void
	 */
	protected function addWatchToSubFolders($parentFolder) {
		$iterator = new DirectoryIterator($parentFolder);

		foreach ($iterator as $fileInfo) {
			if ($fileInfo->isDir() && !$fileInfo->isDot()) {
				$this->addWatch($fileInfo->getPathname());
			}
		}
	}

	/*
	 * Check if there is anything to do with an event
	 *
	 * @return void
	 */
	protected function handleEvent(array $event) {
		if ($this->doesActionRequiresRunningUnitTest($event['mask'])) {
			$this->runUnitTests();
		}

		if ($this->actionIsForNewFolder($event['mask'])) {
			$folderPath = $this->getPathForWatchDescriptor($event['wd']) . $event['name'];
			$this->addWatch($folderPath);
		}
	}

	/*
	 * Check it the action is for the creation of a new folder
	 *
	 * @return void
	 */
	protected function actionIsForNewFolder($mask) {
		return (IN_CREATE === (IN_CREATE & $mask) && IN_ISDIR === (IN_ISDIR & $mask));
	}

	/*
	 * Cehck if the action required running the unit tests
	 *
	 * @return void
	 */
	protected function doesActionRequiresRunningUnitTest($mask) {
		$needsUnitTestRun = false;

		if (IN_MODIFY === (IN_MODIFY & $mask)) {
			$needsUnitTestRun = true;
		}

		if ((IN_CREATE === (IN_CREATE & $mask) && 0 === (IN_ISDIR & $mask))) {
			$needsUnitTestRun = true;
		}


		return $needsUnitTestRun;
	}


	/*
	 * Return the path for a descriptor
	 *
	 * @return string
	 */
	protected function getPathForWatchDescriptor($watchDescriptor) {
		if (array_key_exists($watchDescriptor, $this->watchDescriptors)) {
			return $this->watchDescriptors[$watchDescriptor] . '/';
		} else {
			return '';
		}
	}


	/*
	 * Run the unit tests
	 *
	 * @return void
	 */
	protected function runUnitTests() {
		if (!$this->testsHaveRunForThisWatch) {
			$this->testsHaveRunForThisWatch = true;
			call_user_func($this->callbackFunction);
		}
	}




}