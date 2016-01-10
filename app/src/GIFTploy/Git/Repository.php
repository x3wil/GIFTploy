<?php

namespace GIFTploy\Git;

class Repository
{

    protected $dir;

    public function __construct($dir)
    {
        $this->initDir($dir);
    }

    protected function initDir($dir)
    {
        if (!is_dir($dir.'/.git')) {
            throw new \Exception('Directory "'.$dir.'" is not valid GIT directory.');
        } else if (!is_dir($dir)) {
            throw new \Exception('Directory "'.$dir.'" does not exist.');
        }

        $this->dir = $dir;
    }

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

    public function getLog()
    {
        return new Log($this);
    }
}
