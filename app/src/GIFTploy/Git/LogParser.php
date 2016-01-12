<?php

namespace GIFTploy\Git;

/**
 * Description of LogParser
 *
 * @author Pat
 */
class LogParser
{
    protected $log;
    protected $data = [];

    public function __construct($rawLog)
    {
        $this->log = $rawLog;
    }

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

    protected function shiftLine(&$lines)
    {
        return array_shift($lines);
    }

    protected function shiftDate(&$lines)
    {
        $dateTime = new \DateTime;

        return $dateTime->setTimestamp(array_shift($lines));
    }

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
