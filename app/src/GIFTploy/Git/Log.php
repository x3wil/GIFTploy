<?php

namespace GIFTploy\Git;

/**
 * Class for set up and return parsed log.
 *
 * @author Patrik Chotěnovský
 */
class Log
{

    /**
     * Instance of Repository.
     *
     * @var Repository
     */
    protected $repository;

    /**
     * Instance of Parser.
     *
     * @var Parser
     */
    protected $parser;

    /**
     * Commits limit from log.
     *
     * @var integer
     */
    protected $limit = 0;

    /**
     * Numerical offset.
     *
     * @var integer
     */
    protected $offset = 0;

    /**
     * @param Repository $respository
     */
    public function __construct(Repository $respository, Parser\Parser $parser)
    {
        $this->repository = $respository;
        $this->parser = $parser;
    }

    /**
     * Returns Repository
     *
     * @return Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Sets log limit.
     *
     * @param integer $limit
     * 
     * @return Log
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Returns configured log limit.
     *
     * @return integer
     */
    public function getLimit()
    {
        return (int) $this->limit;
    }

    /**
     * Sets numerical log offset.
     *
     * @param integer $offset
     *
     * @return Log
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Returns configured log offset.
     *
     * @return integer
     */
    public function getOffset()
    {
        return (int) $this->offset;
    }

    /**
     * Returns commit data for single commit from log.
     *
     * @param string $commitHash    Commit hash
     * 
     * @return array
     */
    public function getCommitData($commitHash)
    {
        $args = [
            $commitHash,
            '-1',
        ];

        $logParser = $this->getParserOutput($args);

        return $logParser->parse()->current();
    }

    /**
     * Returns single instance of Commit from log.
     *
     * @param string $commitHash    Commit hash
     *
     * @return Commit
     */
    public function getCommit($commitHash)
    {
        return new Commit($this->repository, $commitHash, $this->getCommitData($commitHash));
    }

    /**
     * Returns array of Commit instances based on setted limit and offset.
     *
     * @return array
     */
    public function getCommits(array $args = [])
    {
        if ($this->getLimit() > 0) {
            $args[] = '-'.($this->getLimit());
        }

        if ($this->getOffset() > 0) {
            $args[] = '--skip='.$this->getOffset();
        }

        $rawLog = $this->getParserOutput($args);

        $commits = [];
        foreach ($this->parser->parse($rawLog) as $commit) {
            $commits[] = new Commit($this->getRepository(), $commit['commitHash'], $commit);
        }

        return $commits;
    }

    /**
     * Prepare and run process for log command and returns instance of LogParser.
     *
     * @param array $args   Additional arguments for log command.
     * @return LogParser
     */
    protected function getParserOutput(array $args = [])
    {
        $logArgs = array_merge([
            '--numstat',
            '--summary',
            '--pretty=format:COMMITSTART%H%n%h%n%P%n%aN%n%ae%n%ct%n%s%n%b%nENDOFOUTPUTGITMESSAGE',
            ], $args);

        return $this->repository->run('log', $logArgs)->getOutput();
    }

}
