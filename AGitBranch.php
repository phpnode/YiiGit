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
	 * Holds the tags in this branch
	 * @var AGitTag[]
	 */
	protected $_tags;

	/**
	 * Constructor
	 * @param string $name the name of the branch
	 * @param AGitRepository $repository the git repository this branch belongs to
	 * @param AGitRemote|null $remote the remote git repository that this branch belongs to, or null if it's a local branch
	 */
	public function __construct($name, AGitRepository $repository, AGitRemote $remote = null) {
		$this->repository = $repository;
		$this->name = $name;
		$this->remote = $remote;
	}

	/**
	 * Gets a list of tags in this branch
	 * @return AGitTag[] the list of tags
	 */
	public function getTags() {
		if ($this->_tags !== null) {
			return $this->_tags;
		}
		if (!$this->isActive) {
			$branchName = $this->repository->getActiveBranch()->name;
			if ($this->remote) {
				$this->repository->checkout($this->remote->name."/".$this->name);
			}
			else {
				$this->repository->checkout($this->name);
			}
			$this->isActive = true;
			$tags = $this->getTags();
			$this->isActive = false;
			$this->repository->checkout($branchName);
			return $tags;
		}
		$this->_tags = array();
		foreach(explode("\n",$this->repository->run("tag")) as $tagName) {
			$tagName = trim($tagName);
			if ($tagName != "") {
				$this->_tags[$tagName] = new AGitTag($tagName,$this);
			}
		}
		return $this->_tags;
	}

	/**
	 * Gets a tag with a specific name
	 * @param string $name the name of the tag
	 * @return AGitTag|boolean the tag, or false if it doesn't exist
	 */
	public function getTag($name) {
		if (!$this->hasTag($name)) {
			return false;
		}
		return $this->_tags[$name];
	}

	/**
	 * Determines whether the branch has a specific tag or not
	 * @param AGitTag|string $tag a tag instance or the name of a tag
	 * @return boolean true if the branch contains this tag
	 */
	public function hasTag($tag) {
		if ($tag instanceof AGitTag) {
			$tag = $tag->name;
		}
		$tags = $this->getTags();
		return isset($tags[$tag]);
	}

	/**
	 * Adds the given tag to the branch
	 * @param AGitTag $tag the tag to add
	 * @return AGitTag|boolean the added tag, or false if the tag wasn't added
	 */
	public function addTag(AGitTag $tag) {
		$command = "tag -a ".$tag->name;
		if ($tag->hash != "") {
			$command .= " ".$tag->hash;
		}
		if ($tag->message != "") {
			$command .= " -m '".addslashes($tag->message)."'";
		}
		$this->_tags = null;
		$this->repository->run($command);
		$t = $this->getTag($tag->name);
		foreach($t as $attribute => $value) {
			$tag->{$attribute} = $value;
		}
		return $tag;
	}

	/**
	 * Removes a particular tag from the repository
	 * @param AGitTag|string $tag the tag instance or name of the tag to remove
	 * @return boolean true if removal succeeded
	 */
	public function removeTag($tag) {
		if ($tag instanceof AGitTag) {
			$tag = $tag->name;
		}
		if (!$this->hasTag($tag)) {
			return false;
		}
		$command = "tag -d ".$tag;
		$this->repository->run($command);
		$this->_tags = null;
		return true;
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
			if ($this->remote) {
				$this->repository->checkout($this->remote->name."/".$this->name);
			}
			else {
				$this->repository->checkout($this->name);
			}
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
		$this->parseCommitList($this->repository->run($command),$separator,$lineSeparator);
		return $this->_commits;
	}

	/**
	 * Gets a commit by its hash
	 * @param string $hash 40 chararcter commit hash of the commit
	 * @return AGitCommit
	 */
	public function getCommit($hash) {
		$len = strlen($hash);
		if ($len == 40 && isset($this->_commits[$hash])) {
			return $this->_commits[$hash];
		}
		elseif ($len < 40) {
			throw new AGitException('Abbreviated commit hashes are not supported yet.');
		}
		$separator = "|||---|||---|||";
		$lineSeparator = "|||||-----|||||-----|||||";
		$command = 'show --pretty=format:"%H'.$separator.'%an'.$separator.'%ae'.$separator.'%cd'.$separator.'%s'.$separator.'%B'.$separator.'%N'.$lineSeparator.'" '.$hash;
		$commits = $this->parseCommitList($this->repository->run($command),$separator,$lineSeparator);
		return array_shift($commits);
	}

	/**
	 * Parses a list of commits
	 * @param string $response the response from git
	 * @param string $separator the field separator
	 * @param string $lineSeparator the line separator
	 * @return array
	 */
	protected function parseCommitList($response, $separator, $lineSeparator) {
		$response = explode($lineSeparator,$response);
		$commits = array();
		foreach($response as $line) {
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
			$commits[$commit->hash] = $commit;
			$this->_commits[$commit->hash] = $commit;
		}
		return $commits;
	}

	/**
	 * Gets the latest git commit
	 * @return AGitCommit
	 */
	public function getLastCommit() {
		$commits = $this->getCommits();
		return array_shift($commits);
	}

}