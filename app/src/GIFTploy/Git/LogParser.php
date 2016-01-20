<?php

namespace GIFTploy\Git;

/**
 * Class for parsing raw log output.
 *
 * @author Patrik Chotěnovský
 */
class LogParser
{
    /**
     * Raw log output.
     *
     * @var string
     */
    protected $log;

    /**
     * Sets a raw log.
     *
     * @param type $rawLog
     */
    public function __construct($rawLog)
    {
        $this->log = $rawLog;
    }

    /**
     * Explode raw log by commits and returns by a single commit data.
     *
     * @return Generator
     */
    public function parse()
    {
        $logByCommits = explode('COMMITSTART', $this->log);

        foreach ($logByCommits as $rawCommitLog) {

            if (empty($rawCommitLog)) {
                continue;
            }

            yield $this->parseCommitLog($rawCommitLog);
        }
    }

    /**
     * Parse raw log of a single commit and returns its data.
     *
     * @param string $rawCommitLog  Raw log of single commit
     * 
     * @return array
     */
    protected function parseCommitLog($rawCommitLog)
    {
        $commitData = [];
        $lines = explode("\n", $rawCommitLog);

        $commitData['commitHash'] = $this->shiftLine($lines);
        $commitData['commitHashAbbrev'] = $this->shiftLine($lines);
        $commitData['parentHash'] = $this->shiftLine($lines);
        $commitData['authorName'] = $this->shiftLine($lines);
        $commitData['authorEmail'] = $this->shiftLine($lines);
        $commitData['date'] = $this->shiftDate($lines);
        $commitData['message'] = $this->shiftMessage($lines);
        $commitData['files'] = $this->shiftFiles($rawCommitLog);

        return $commitData;
    }

    /**
     * Removes and returns first line of raw log.
     *
     * @param array $lines  Raw commit log passed by reference
     * @return string
     */
    protected function shiftLine(&$lines)
    {
        return array_shift($lines);
    }

    /**
     * Removes first line of raw log and returns as DateTime object.
     *
     * @param array $lines  Raw commit log passed by reference
     * @return DateTime
     */
    protected function shiftDate(&$lines)
    {
        $dateTime = new \DateTime;

        return $dateTime->setTimestamp(array_shift($lines));
    }

    /**
     * Removes and returns commit message from couple of lines of raw log.
     *
     * @param array $lines  Raw commit log passed by reference
     * @return string
     */
    protected function shiftMessage(&$lines)
    {
        $message = "";
        $line = array_shift($lines);

        while ($line !== 'ENDOFOUTPUTGITMESSAGE')
        {
            if (!empty($line)) {
                $message .= $line;
            }

            $line = array_shift($lines);
        }

        return trim($message);
    }

    /**
     * Extract files from raw log and returns as array.
     * Files are sorted by mode: create, delete and modify.
     *
     * @param string $commitLog   Raw commit log
     * @return array
     */
    protected function shiftFiles($commitLog)
    {
        $matches = [];

        preg_match_all("~^[\d|-]+\s+[\d|-]+\s+(.*)$~m", $commitLog, $matches);
        $files = array_fill_keys($matches[1], "modify");

        preg_match_all("~^\s*(create|delete)\s+mode\s+\d+\s+(.*)$~m", $commitLog, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {

            $files[$match[2]] = $match[1];
        }

        $filesArr = [
            "modify" => [],
            "delete" => [],
            "create" => [],
        ];

        foreach ($files as $file => $treatment) {
            $filesArr[$treatment][] = $file;
        }

        return $filesArr;
    }

}
