#### Introduction

A Yii wrapper for the git command line executable. Allows access to all git commands via a simple object oriented interface.

#### Usage examples

Add some files and folders to git
<pre>
$repository = new AGitRepository("path/to/your/git/repo");
$repository->add("somefile.txt");
$repository->add("somedirectory");
</pre>

Commit some files with git
<pre>
$repository->commit("Added some files");
</pre>

Checkout an existing branch
<pre>
$repository->checkout("some-existing-branch");
echo $repository->activeBranch->name; // some-existing-branch
</pre>

Checkout a new branch
<pre>
$repository->checkout("some-new-branch", true);
echo $repository->activeBranch->name; // some-new-branch
</pre>

List all branches
<pre>
foreach($repository->branches as $branch) {
	echo $branch->name."\n";
}
</pre>

List all tags with metadata
<pre>
foreach($repository->tags as $tag) {
	echo $tag->name"\n";
	echo $tag->authorName"\n";
	echo $tag->authorEmail"\n";
	echo $tag->message"\n";
}
</pre>

List all the commits on the current branch
<pre>
foreach($repository->activeBranch->commits as $commit) {
	echo $commit->authorName." at ".$commit->time."\n";
	echo $commit->message."\n";
	echo str_repeat("-",50)."\n";
}
</pre>

List all the files affected by the latest commit
<pre>
foreach($repository->activeBranch->latestCommit->files as $file) {
	echo $file."\n";
}
</pre>

Check if a tag exists on the default remote ('origin')
<pre>
$repository->remote->hasTag('myTag');
</pre>

List all branches on a remote repository called 'upstream'
<pre>
foreach($repository->getRemote('upstream')->getBranches() as $branch) {
	echo $branch."\n";
}
</pre>

#### API

AGitRepository
<pre>
setPath($path, $createIfEmpty = false, $initialize = false)
getPath()
run($command)
add($file)
rm($file, $force = false)
commit($message = null, $addFiles = false, $amend = false)
status()
checkout($branchName, $create = false, $force = false)
clean($deleteDirectories = false, $force = false)
cloneTo($targetDirectory)
cloneFrom($targetDirectory)
cloneRemote($sourceUrl)
push($remote, $branch = "master", $force = false)
fetch($repository)
getActiveBranch()
getBranches()
hasBranch()
createBranch($branchName)
deleteBranch($branchName, $force = false)
getCommit($hash)
getTags()
getTag($name)
hasTag($tag)
addTag($name, $message, $hash = null)
removeTag($tag)
getRemotes()
getRemote($remote = null)
hasCommit($hash)
</pre>

AGitCommit
<pre>
getAuthorName()
getAuthorEmail()
getTime()
getSubject()
getMessage()
getNotes()
getParents()
getFiles()
</pre>

AGitBranch
<pre>
getCommits()
getCommit($hash)
getLastCommit()
</pre>

AGitTag
<pre>
getAuthorName()
getAuthorEmail()
getMessage()
getCommit()
</pre>

AGitRemote
<pre>
getBranches()
hasBranch($branch)
getTags()
hasTag($tag)
</pre>