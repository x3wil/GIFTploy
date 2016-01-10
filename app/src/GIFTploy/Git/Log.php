<?php

namespace GIFTploy\Git;

class Log
{

    protected $repository;
    protected $limit = 0;
    protected $offset = 0;

    public function __construct(Repository $respository)
    {
        $this->repository = $respository;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    public function getLimit()
    {
        return (int)$this->limit;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    public function getOffset()
    {
        return (int)$this->offset;
    }

    public function getCommits()
    {
        $args = [
			'--numstat',
			'--summary',
			'--pretty=format:COMMITSTART%H%n%h%n%P%n%aN%n%ae%n%ct%n%s%n%b%nENDOFOUTPUTGITMESSAGE',
        ];

        if ($this->getLimit() > 0) {
            $args[] = '-'.($this->getLimit());
        }

        if ($this->getOffset() > 0) {
            $args[] = '--skip='.$this->getOffset();
        }

		$logRaw = $this->repository->run('log', $args);

        $logParser = new LogParser($logRaw->getOutput());

        $commits = [];
        foreach ((array)$logParser->parse() as $commit) {
            $commits[] = new Commit($this->getRepository(), $commit['commitHash'], $commit);
        }

        return $commits;
    }
}
