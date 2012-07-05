<?php
/**
 * Represents a git remote repository
 *
 * @author Charles Pick
 * @author Jonas Girnatis <dermusterknabe@gmail.com>
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
	 * Constructor
	 * @param string $name the name of the remote repository
	 * @param AGitRepository $repository the main git repository this remote belongs to
	 */
	public function __construct($name, AGitRepository $repository)
	{
		$this->repository = $repository;
		$this->name = $name;
	}

	/**
	 * Gets a list of git branches for this remote repository
	 * @return AGitBranch[] an array of git branches
	 */
	public function getBranches()
	{
		$branches = array();
		foreach(explode("\n",$this->repository->run("ls-remote --heads " . $this->name)) as $ref) {
			$ref = explode('refs/heads/', trim($ref), 2);
			$branchName = $ref[1];
			$branch = new AGitBranch($branchName,$this->repository,$this);
			$branches[$branchName] = $branch;
		}
		return $branches;
	}

	/**
	 * Checks if this remote repository has a specific branch
	 * @param string $branch branch name
	 * @return bool true if remote repository has specific branch, false otherwise
	 */
	public function hasBranch($branch)
	{
		$branches = $this->getBranches();
		return isset($branches[$branch]);
	}

	/**
	 * Deletes the remote branch with the given name
	 * @param string $branchName the branch name
	 * @return string the response from git
	 */
	public function deleteBranch($branchName)
	{
		return $this->repository->run("push $this->name :$branchName");
	}


	/**
	 * Gets a list of tags for this remote repository
	 * @return AGitTag[] an array of tags
	 */
	public function getTags()
	{
		$tags = array();
		foreach(explode("\n",$this->repository->run("ls-remote --tags " . $this->name)) as $i => $ref) {
			if($i == 0) { continue; } //ignore first line "From: repository..."
			if(substr_count($ref, '^{}')){ continue; } //ignore dereferenced tag objects for annotated tags
			$ref = explode('refs/tags/', trim($ref), 2);
			$tagName = $ref[1];
			$tag = new AGitTag($tagName,$this->repository,$this);
			$tags[$tagName] = $tag;
		}
		return $tags;
	}

	/**
	 * Checks if this remote repository has a specific tag
	 * @param string $tag tag name
	 * @return bool true if remote repository has specific tag, false otherwise
	 */
	public function hasTag($tag)
	{
		$tags = $this->getTags();
		return isset($tags[$tag]);
	}

	/**
	 * @return string remote name
	 */
	public function __toString()
	{
		return $this->name;
	}
}