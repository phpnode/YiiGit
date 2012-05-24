<?php
/**
 * Represents a git commit.
 *
 * @author Charles Pick
 * @author Jonas Girnatis <dermusterknabe@gmail.com>
 * @package packages.git
 */
class AGitCommit extends CComponent {
	/**
	 * The commit hash
	 * @var string
	 */
	public $hash;

	/**
	 * The repository this commit is on
	 * @var AGitRepository
	 */
	public $repository;

	/**
	 * The name of the commit author
	 * @var string
	 */
	protected $_authorName;

	/**
	 * The commit author's email address
	 * @var string
	 */
	protected $_authorEmail;

	/**
	 * The time of the commit
	 * @var string
	 */
	protected $_time;

	/**
	 * The commit subject
	 * @var string
	 */
	protected $_subject;

	/**
	 * The commit message
	 * @var string
	 */
	protected $_message;

	/**
	 * The commit notes
	 * @var string
	 */
	protected $_notes;

	/**
	 * Holds an array of files included in this commit
	 * @var array
	 */
	protected $_files;

	/**
	 * Holds an array of parent commits
	 * @var AGitCommit[]
	 */
	protected $_parents;

	/**
	 * Constructor
	 * @param AGitRepository $repository the git repository
	 */
	public function __construct($hash, AGitRepository $repository)
	{
		$this->hash = $hash;
		$this->repository = $repository;
	}

	/**
	 * The name of the commit author
	 * @var string
	 */
	public function getAuthorName()
	{
		if(is_null($this->_authorName)){
			$this->loadData();
		}
		return $this->_authorName;
	}

	/**
	 * The commit author's email address
	 * @var string
	 */
	public function getAuthorEmail()
	{
		if(is_null($this->_authorEmail)){
			$this->loadData();
		}
		return $this->_authorEmail;
	}

	/**
	 * The time of the commit
	 * @var string
	 */
	public function getTime()
	{
		if(is_null($this->_time)){
			$this->loadData();
		}
		return $this->_time;
	}

	/**
	 * The commit subject
	 * @var string
	 */
	public function getSubject()
	{
		if(is_null($this->_subject)){
			$this->loadData();
		}
		return $this->_subject;
	}

	/**
	 * The commit message
	 * @var string
	 */
	public function getMessage()
	{
		if(is_null($this->_message)){
			$this->loadData();
		}
		return $this->_message;
	}

	/**
	 * The commit notes
	 * @var string
	 */
	public function getNotes()
	{
		if(is_null($this->_notes)){
			$this->loadData();
		}
		return $this->_notes;
	}

	/**
	 * Gets a list of parent commits
	 * @return AGitCommit[] array of parent commits
	 */
	public function getParents()
	{
		if ($this->_parents === null) {
			$this->_parents = array();
			$command = 'log --pretty=%P -n 1 '.$this->hash;
			foreach(explode(' ',$this->repository->run($command)) as $commitHash) {
				if (!empty($commitHash)) {
					$this->_parents[$commitHash] = $this->repository->getCommit($commitHash);
				}
			}
		}
		return $this->_parents;
	}

	/**
	 * Gets a list of files affected by this commit
	 * @return array the files affected by this commit
	 */
	public function getFiles()
	{
		if ($this->_files === null) {
			$command = 'show --pretty="format:" --name-only '.$this->hash;
			foreach(explode("\n",$this->repository->run($command)) as $line) {
				$this->_files[] = trim($line);
			}
		}
		return $this->_files;
	}
	
	/**
	 * Retrieves the contents of a file at a specific commit.  If binary, it will
	 * return the binary content.
	 *
	 * @param string $filename
	 * @return string
	 */
	public function getFileContents($filename)
	{
		$command = sprintf('show --raw %s:%s', $this->hash, $filename);
		return $this->repository->run($command);
	}

	/**
	 * Loads the metadata for the commit
	 */
	protected function loadData()
	{
		$delimiter = "|||---|||---|||";
		$command = 'show --pretty=format:"%an'.$delimiter.'%ae'.$delimiter.'%cd'.$delimiter.'%s'.$delimiter.'%B'.$delimiter.'%N" ' . $this->hash;

		$response = $this->repository->run($command);

		$parts = explode($delimiter,$response);
		$this->_authorName = array_shift($parts);
		$this->_authorEmail = array_shift($parts);
		$this->_time = array_shift($parts);
		$this->_subject = array_shift($parts);
		$this->_message = array_shift($parts);
		$this->_notes = array_shift($parts);
	}

	/**
	 * @return string commit hash
	 */
	public function __toString()
	{
		return $this->hash;
	}
}