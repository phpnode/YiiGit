<?php
/**
 * Represents a git remote repository
 * @author Charles Pick
 * @package packages.git
 */
class AGitRemote extends CComponent {
	/**
	 * The name of the remote git repository
	 * @var string
	 */
	public $name;

	/**
	 * The url to use when fetching from the remote repository
	 * @var string
	 */
	public $fetchUrl;

	/**
	 * The url to use when pushing to the remote repository
	 * @var string
	 */
	public $pushUrl;

	/**
	 * The main repository this remote belongs to
	 * @var AGitRepository
	 */
	public $repository;
	/**
	 * A list of branches on the remote server
	 * @var AGitBranch[]
	 */
	protected $_branches;

	/**
	 * Constructor
	 * @param string $name the name of the remote repository
	 * @param AGitRepository $repository the main git repository this remote belongs to
	 */
	public function __construct($name, AGitRepository $repository) {
		$this->repository = $repository;
		$this->name = $name;
	}

	/**
	 * Gets a list of git branches for this remote repository
	 * @return AGitBranch[] an array of git branches
	 */
	public function getBranches() {
		if ($this->_branches === null) {
			$this->_branches = array();
			foreach(explode("\n",$this->repository->run("branch -r")) as $branchName) {
				$branchName = trim($branchName);
				if (substr($branchName,0,strlen($this->name) + 1) != $this->name.'/') {
					continue;
				}
				$branchName = substr($branchName,strlen($this->name) + 1);
				$branch = new AGitBranch($branchName,$this->repository,$this);
				$this->_branches[$branchName] = $branch;
			}
		}
		return $this->_branches;
	}
	
	public function hasBranch()
	{
		throw Exception('Please implement AGitRemote::hasBranch().');
		return true;
	}
}