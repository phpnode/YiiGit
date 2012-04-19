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

Checkout a new branch
<pre>
$repository->checkout("some-new-branch", true);
echo $repository->activeBranch->name; // some-new-branch
</pre>

Checkout an existing branch
<pre>
$repository->checkout("some-new-branch");
echo $repository->activeBranch->name; // some-new-branch
</pre>

List all branches

<pre>
foreach($repository->branches as $branch) {
	echo $branch->name."\n";
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