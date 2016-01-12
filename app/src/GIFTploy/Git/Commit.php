<?php

namespace GIFTploy\Git;

/**
 * Description of Commit
 *
 * @author Pat
 */
class Commit
{
    protected $repository;
    protected $commitHash;
    protected $commitHashAbbrev;
    protected $parentHash;
    protected $authorName;
    protected $authorEmail;
    protected $date;
    protected $message;
    protected $files = [];

    public function __construct(Repository $repository, $commitHash, array $data = [])
    {
        $this->repository = $repository;
        $this->commitHash = $commitHash;

        if (empty($data)) {
            $data = $this->repository->getLog()->getCommitData($commitHash);
        }

        $this->setData($data);
    }

    protected function setData(array $data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function getCommitHash()
    {
        return $this->commitHash;
    }

    public function getCommitHashAbbrev()
    {
        return $this->commitHashAbbrev;
    }

    public function getParentHash()
    {
        return $this->parentHash;
    }

    public function getAuthorName()
    {
        return str_replace(' ', '&nbsp;', $this->authorName);
    }

    public function getAuthorEmail()
    {
        return $this->authorEmail;
    }

    public function getDate($format = 'j.n.Y H:i')
    {
        return str_replace(' ', '&nbsp;', $this->date->format($format));
    }

    public function getDateTimestamp()
    {
        return $this->date->getTimestamp();
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getFiles()
    {
        return $this->files;
    }
}
