<?php

namespace GIFTploy\Git;

/**
 * Class for managing single repository.
 *
 * @author Patrik Chotěnovský
 */
class Repository
{

    /**
     * Working directory path.
     *
     * @var string
     */
    protected $dir;

    /**
     * List of commits returned from first getLog().
     *
     * @var array
     */
    protected $commits = [];

    /**
     * Constructs a repository.
     * If directory is not exists or is not git directory, throws exception.
     *
     * @param type $dir
     */
    public function __construct($dir)
    {
        $this->initDir($dir);
    }

    /**
     * Checks and sets valid git directory.
     *
     * @param type $dir     Working directory path
     * 
     * @throws Exception    Directory is not exists or is not git directory
     */
    protected function initDir($dir)
    {
        if (!is_dir($dir.'/.git')) {
            throw new \Exception('Directory "'.$dir.'" is not valid GIT directory.');
        } else if (!is_dir($dir)) {
            throw new \Exception('Directory "'.$dir.'" does not exist.');
        }

        $this->dir = $dir;
    }

    /**
     * Returns git directory.
     *
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * Run process scoped to this repository by setting --git-dir and --work-tree.
     *
     * @param type $command Command to execute
     * @param array $args   Additional arguments
     * 
     * @return type     Running process
     */
    public function run($command, array $args = [])
    {
        $baseCommand = [
            '--git-dir',
            $this->dir.'/.git',
            '--work-tree',
            $this->dir,
        ];

        $process = Git::getProcess(array_merge($baseCommand, [$command]), $args);

        return Git::runProcess($process);
    }

    /**
     * Returns instance of Log scoped to this repository.
     *
     * @param Parser\Parser $parser
     * @return Log
     */
    public function getLog(Parser\Parser $parser)
    {
        return new Log($this, $parser);
    }

    /**
     * Returns instance of Commit by given commit hash.
     * Method looks to $this->commits first and then return new instance of Commit if does not exist.
     *
     * @param type $commitHash  Commit hash
     * @return Commit
     */
    public function getCommit($commitHash)
    {
        return isset($this->commits[$commitHash]) ? $this->commits[$commitHash] : new Commit($this, $commitHash);
    }

}
