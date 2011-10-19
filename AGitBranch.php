<?php
/**
 * Represents a git branch
 *
 * @author Charles Pick
 * @package packages.git
 */
class AGitBranch extends CComponent {
	/**
	 * The repository this branch belongs to
	 * @var AGitRepository
	 */
	public $repository;

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
	 */
	public function __construct($name, AGitRepository $repository) {
		$this->repository = $repository;
		$this->name = $name;
	}
	/**
	 * Gets a list of commits in this branch
	 * @return AGitCommit[] an array of git commits, indexed by hash
	 */
	public function getCommits() {
		if ($this->_commits !== null) {
			return $this->_commits;
		}
		if (!$this->isActive) {
			$branchName = $this->repository->getActiveBranch()->name;
			$this->repository->checkout($this->name);
			$this->isActive = true;
			$commits = $this->getCommits();
			$this->isActive = false;
			$this->repository->checkout($branchName);
			return $commits;
		}
		$this->_commits = array();
		$separator = "|||---|||---|||";
		$lineSeparator = "|||||-----|||||-----|||||";
		$command = 'log --pretty=format:"%H'.$separator.'%an'.$separator.'%ae'.$separator.'%cd'.$separator.'%s'.$separator.'%B'.$separator.'%N'.$lineSeparator.'"';
		foreach(explode($lineSeparator,$this->repository->run($command)) as $line) {
			$line = trim($line);
			if (!$line) {
				continue;
			}
			$parts = explode($separator,$line);
			$commit = new AGitCommit($this);
			$commit->hash = array_shift($parts);
			$commit->authorName = array_shift($parts);
			$commit->authorEmail = array_shift($parts);
			$commit->time = array_shift($parts);
			$commit->subject = array_shift($parts);
			$commit->message = array_shift($parts);
			$commit->notes = array_shift($parts);
			$this->_commits[$commit->hash] = $commit;
		}
		return $this->_commits;
	}
}