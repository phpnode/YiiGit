<?php
/**
 * Represents a git branch
 *
 * @author Charles Pick
 * @author Jonas Girnatis <dermusterknabe@gmail.com>
 * @package packages.git
 */
class AGitBranch extends CComponent {
	/**
	 * The repository this branch belongs to
	 * @var AGitRepository
	 */
	public $repository;

	/**
	 * The remote repository this branch belongs to, if any
	 * @var AGitRemote
	 */
	public $remote;

	/**
	 * Whether this is the active branch or not
	 * @var boolean
	 */
	public $isActive = false;
	/**
	 * The name of the git branch
	 * @var string
	 */
	public $name;
	/**
	 * Holds the commits in this branch
	 * @var AGitCommit[]
	 */
	protected $_commits;

	/**
	 * Constructor
	 * @param string $name the name of the branch
	 * @param AGitRepository $repository the git repository this branch belongs to
	 * @param AGitRemote|null $remote the remote git repository that this branch belongs to, or null if it's a local branch
	 */
	public function __construct($name, AGitRepository $repository, AGitRemote $remote = null)
	{
		$this->repository = $repository;
		$this->name = $name;
		$this->remote = $remote;
	}

	/**
	 * Gets a list of commits in this branch
	 * @return AGitCommit[] an array of git commits, indexed by hash
	 */
	public function getCommits()
	{
		if (is_null($this->_commits)) {
			$this->_commits = array();
			$branchName = $this->remote ? $this->remote->name.'/'.$this->name : $this->name;
			$command = 'log --pretty=format:"%H" ' . $branchName;
			foreach(explode("\n",$this->repository->run($command)) as $hash) {
				$hash = trim($hash);
				if (!$hash) {
					continue;
				}
				$this->_commits[$hash] = new AGitCommit($hash, $this->repository);
			}
		}

		return $this->_commits;
	}

	/**
	 * Gets a commit by its hash
	 * @param string $hash 40 chararcter commit hash of the commit
	 * @return AGitCommit
	 */
	public function getCommit($hash)
	{
		$len = strlen($hash);
		if ($len == 40 && isset($this->_commits[$hash])) {
			return $this->_commits[$hash];
		} elseif ($len < 40) {
			throw new AGitException('Abbreviated commit hashes are not supported yet.');
		}
		return null;
	}

	/**
	 * Gets the latest git commit
	 * @return AGitCommit
	 */
	public function getLastCommit()
	{
		$commits = $this->getCommits();
		return array_shift($commits);
	}
	
	/**
	 * @return string branch name
	 */
	public function __toString()
	{
		return $this->name;
	}
}