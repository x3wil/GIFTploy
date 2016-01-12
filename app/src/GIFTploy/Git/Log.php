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

    public function getCommitData($commitHash)
    {
        $args = [
            $commitHash,
            '-1',
        ];

        $logParser = $this->getParserOutput($args);

        return $logParser->parse()->current();
    }

    public function getCommit($commitHash)
    {
        return new Commit($this->repository, $commitHash, $this->getCommitData($commitHash));
    }

    public function getCommits()
    {
        $args = [];

        if ($this->getLimit() > 0) {
            $args[] = '-'.($this->getLimit());
        }

        if ($this->getOffset() > 0) {
            $args[] = '--skip='.$this->getOffset();
        }

        $logParser = $this->getParserOutput($args);

        $commits = [];
        foreach ($logParser->parse() as $commit) {
            $commits[] = new Commit($this->getRepository(), $commit['commitHash'], $commit);
        }

        return $commits;
    }

    protected function getParserOutput(array $args = [])
    {
        $logArgs = array_merge([
			'--numstat',
			'--summary',
			'--pretty=format:COMMITSTART%H%n%h%n%P%n%aN%n%ae%n%ct%n%s%n%b%nENDOFOUTPUTGITMESSAGE',
        ], $args);

		$logRaw = $this->repository->run('log', $logArgs);

        return new LogParser($logRaw->getOutput());
    }

}
