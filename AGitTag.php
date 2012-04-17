<?php
/**
 * Represents a git tag.
 * @author Charles Pick
 * @package packages.git
 */
class AGitTag extends CComponent
{
	/**
	 * The name of the tag
	 * @var string
	 */
	public $name;

	/**
	 * The name of the author of the tag
	 * @var string
	 */
	public $authorName;

	/**
	 * The email address of the author of the tag
	 * @var string
	 */
	public $authorEmail;

	/**
	 * The message for this tag
	 * @var string
	 */
	public $message;

	/**
	 * The hash of the commit that this tag refers to
	 * @var string
	 */
	public $hash;

	/**
	 * The repository this tag belongs to
	 * @var AGitRepository
	 */
	public $repository;

	/**
	 * The remote repository this tag belongs to, if any
	 * @var AGitRemote
	 */
	public $remote;

	/**
	 * The commit that this tag points to
	 * @var AGitCommit
	 */
	protected $_commit;

	/**
	 * Constructor.
	 * @param string $name the name of the tag
	 * @param AGitBranch|null $branch the branch this tag belongs to
	 */
	public function __construct($name, AGitRepository $repository, AGitRemote $remote = null) {
		$this->repository = $repository;
		$this->name = $name;
		$this->remote = $remote;

//		$this->loadData();
	}

	/**
	 * Loads the data for the tag
	 */
// 	protected function loadData() {
// 		$lineSeparator = "|||||-----|||||-----|||||";
// 		$command = 'show --pretty=format:"'.$lineSeparator.'%H" '.$this->name;
// 		$response = explode($lineSeparator,$this->branch->repository->run($command));
// 		$lines = explode("\n", array_shift($response));
// 		array_shift($lines); // we already have the name of the tag
// 		if (substr($lines[0],0,8) == "Tagger: ") {
// 			$tagger = trim(substr(array_shift($lines),8));
// 			if (preg_match("/(.*) <(.*)>/u",$tagger,$matches)) {
// 				$this->authorEmail = trim(array_pop($matches));
// 				$this->authorName = trim(array_pop($matches));
// 			}
// 			else {
// 				$this->authorName = $tagger;
// 			}
// 		}
// 		$this->message = trim(implode("\n",$lines));
// 		$this->hash = array_pop($response);
// 	}

	/**
	 * Gets the commit this tag points to
	 * @return AGitCommit the commit this tag points to
	 */
	public function getCommit()
	{
		throw Exception('Implement AGitTag::getCommit()');
		//return $this->branch->getCommit($this->hash);
	}
}