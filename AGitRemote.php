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
	 * A list of tags on the remote server
	 * @var AGitTag[]
	 */
	protected $_tags;

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
			foreach(explode("\n",$this->repository->run("ls-remote --heads " . $this->name)) as $ref) {
				$ref = explode('refs/heads/', trim($ref), 2);
				$branchName = $ref[1];
				$branch = new AGitBranch($branchName,$this->repository,$this);
				$this->_branches[$branchName] = $branch;
			}
		}
		return $this->_branches;
	}

	/**
	 * Checks if this remote repository has a specific branch
	 * @param string $branch branch name
	 * @return bool true if remote repository has specific branch, false otherwise
	 */
	public function hasBranch($branch) {
		$branches = $this->getBranches();
		return isset($branches[$branch]);
	}

	/**
	 * Gets a list of tags for this remote repository
	 * @return AGitTag[] an array of tags
	 */
	public function getTags() {
		if ($this->_tags === null) {
			$this->_tags = array();
			foreach(explode("\n",$this->repository->run("ls-remote --tags " . $this->name)) as $ref) {
				if(substr_count($ref, '^{}')){ continue; } //ignore dereferenced tag objects for annotated tags
				$ref = explode('refs/tags/', trim($ref), 2);
				$tagName = $ref[1];
				$tag = new AGitTag($tagName,$this->repository,$this);
				$this->_tags[$tagName] = $tag;
			}
		}
		return $this->_tags;
	}

	/**
	 * Checks if this remote repository has a specific tag
	 * @param string $tag tag name
	 * @return bool true if remote repository has specific tag, false otherwise
	 */
	public function hasTag($tag) {
		$tags = $this->getTags();
		return isset($tags[$tag]);
	}
}