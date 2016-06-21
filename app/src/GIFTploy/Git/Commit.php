<?php

namespace GIFTploy\Git;

/**
 * Class for managing single commit.
 *
 * @author Patrik Chotěnovský
 */
class Commit
{
    /**
     * Instance of Repository.
     *
     * @var Repository
     */
    protected $repository;

    /**
     * Commit hash.
     *
     * @var string
     */
    protected $commitHash;

    /**
     * Abbreviated commit hash.
     *
     * @var string
     */
    protected $commitHashAbbrev;

    /**
     * Parent commit hash.
     *
     * @var string
     */
    protected $parentHash;

    /**
     * Authors name.
     *
     * @var string
     */
    protected $authorName;

    /**
     * Authors e-mail
     *
     * @var string
     */
    protected $authorEmail;

    /**
     * Commit date.
     *
     * @var \DateTime
     */
    protected $date;

    /**
     * Commit message.
     *
     * @var string
     */
    protected $message;

    /**
     * List of created, modified and deleted files.
     *
     * @var array
     */
    protected $files = [];

    /**
     * Constructs a commit.
     * If commit data is not given, constructor will fetch them from log.
     *
     * @param Repository $repository Instance of Repository
     * @param string $commitHash Commit hash
     * @param array $data Optional given commit data
     */
    public function __construct(Repository $repository, $commitHash, array $data = [])
    {
        $this->repository = $repository;
        $this->commitHash = $commitHash;

        if (empty($data)) {
            $data = $this->repository->getLog()->getCommitData($commitHash);
        }

        $this->setData($data);
    }

    /**
     * Sets commit data from given array.
     *
     * @param array $data Array of commit data where keys are Commit class properties
     *
     * @return Commit
     */
    protected function setData(array $data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
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
     * Returns commit hash.
     *
     * @return string
     */
    public function getCommitHash()
    {
        return $this->commitHash;
    }

    /**
     * Returns sbbreviated commit hash.
     *
     * @return string
     */
    public function getCommitHashAbbrev()
    {
        return $this->commitHashAbbrev;
    }

    /**
     * Returns parent commit hash.
     *
     * @return string
     */
    public function getParentHash()
    {
        return $this->parentHash;
    }

    /**
     * Returns authors name.
     *
     * @return string
     */
    public function getAuthorName()
    {
        return str_replace(' ', '&nbsp;', $this->authorName);
    }

    /**
     * Returns authors e-mail.
     *
     * @return string
     */
    public function getAuthorEmail()
    {
        return $this->authorEmail;
    }

    /**
     * Returns formated commit date.
     *
     * @return string
     */
    public function getDate($format = 'j.n.Y H:i')
    {
        return str_replace(' ', '&nbsp;', $this->date->format($format));
    }

    /**
     * Returns commit date as UNIX timestamp.
     *
     * @return string
     */
    public function getDateTimestamp()
    {
        return $this->date->getTimestamp();
    }

    /**
     * Returns commit message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns list of created, modified and deleted files.
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }
}
