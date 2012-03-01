<?php
/**
 * Represents a git commit.
 *
 * @author Charles Pick
 * @package packages.git
 */
class AGitCommit extends CComponent {
	/**
	 * The commit hash
	 * @var string
	 */
	public $hash;
	/**
	 * The name of the commit author
	 * @var string
	 */
	public $authorName;
	/**
	 * The commit author's email address
	 * @var string
	 */
	public $authorEmail;
	/**
	 * The time of the commit
	 * @var string
	 */
	public $time;
	/**
	 * The commit subject
	 * @var string
	 */
	public $subject;
	/**
	 * The commit message
	 * @var string
	 */
	public $message;
	/**
	 * The commit notes
	 * @var string
	 */
	public $notes;
	/**
	 * The branch this commit is on
	 * @var AGitBranch
	 */
	public $branch;
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
	 * @param AGitBranch $branch the git branch
	 */
	public function __construct(AGitBranch $branch) {
		$this->branch = $branch;
	}

	/**
	 * Gets a list of parent commits
	 * @return AGitCommit[] array of parent commits
	 */
	public function getParents() {
		if ($this->_parents === null) {
			$this->_parents = array();
			$command = 'show --pretty="format:%P" '.$this->hash;
			foreach(explode(' ',$this->branch->repository->run($command)) as $commitHash) {
				if (!empty($commitHash)) {
					$this->_parents[$commitHash] = $this->branch->getCommit($commitHash);
				}
			}
		}
		return $this->_parents;
	}

	/**
	 * Gets a list of files affected by this commit
	 * @return array the files affected by this commit
	 */
	public function getFiles() {
		if ($this->_files === null) {
			$command = 'show --pretty="format:" --name-only '.$this->hash;
			foreach(explode("\n",$this->branch->repository->run($command)) as $line) {
				$this->_files[] = trim($line);
			}
		}
		return $this->_files;
	}
}