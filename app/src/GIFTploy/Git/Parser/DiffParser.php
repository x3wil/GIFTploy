<?php

namespace GIFTploy\Git\Parser;

/**
 * Class for parsing raw diff output.
 *
 * @author Patrik Chotěnovský
 */
class DiffParser implements Parser
{

    /**
     * Explode raw diff by files and returns parsed data as generator.
     *
     * @return \Generator
     */
    public function parse($rawDiff)
    {
        $diffByFiles = preg_split('~^diff --git ~m', $rawDiff);

        foreach ($diffByFiles as $rawFileDiff) {

            if (empty($rawFileDiff)) {
                continue;
            }

            yield $this->parseFileDiff($rawFileDiff);
        }
    }

    /**
     * Parse raw diff of a single file and returns its data.
     *
     * @param string $rawFileDiff Raw diff of single file
     *
     * @return array
     */
    protected function parseFileDiff($rawFileDiff)
    {
        $fileData = [];
        $matches = [];

        preg_match('~^(.[\s\/][^@@]*)~m', $rawFileDiff, $matches);

        $lines = explode("\n", $matches[1]);

        $fileData['filename'] = $this->shiftFilename($lines);
        $fileData['mode'] = $this->shiftMode($lines);
        $fileData['binary'] = $this->shiftBinaryType($lines);
        $fileData['changes'] = $this->getChanges($rawFileDiff);

        return $fileData;
    }

    /**
     * Removes first line of raw diff and returns filename.
     *
     * @param array $lines Raw diff passed by reference
     * @return string
     */
    protected function shiftFilename(&$lines)
    {
        $matches = [];
        preg_match('~^a(.*)\sb.*$~', array_shift($lines), $matches);

        return trim($matches[1], '/');
    }

    /**
     * Removes first line of raw diff and returns mode.
     *
     * @param array $lines Raw diff passed by reference
     * @return string
     */
    protected function shiftMode(&$lines)
    {
        $line = array_shift($lines);

        if (strpos($line, 'index') === 0) {
            $mode = 'modify';
        } else {
            $mode = (strpos($line, 'deleted') === 0) ? 'delete' : 'create';

            // removes next line starting with 'index...'
            array_shift($lines);
        }

        return $mode;
    }

    /**
     * Removes first line of raw diff and returns whether it is binnary or not.
     *
     * @param array $lines Raw diff passed by reference
     * @return string
     */
    protected function shiftBinaryType(&$lines)
    {
        $line = array_shift($lines);

        return (strpos($line, 'Binary files') === 0);
    }

    /**
     * Extract files changes from raw diff and returns as array.
     *
     * @param string $rawFileDiff Raw diff
     * @return array
     */
    protected function getChanges($rawFileDiff)
    {
        $diffByFiles = explode('@@', $rawFileDiff);
        $changes = [];

        array_shift($diffByFiles);

        foreach ($diffByFiles as $part) {

            $matches = [];

            if (preg_match('~^\s[-\+]?([\d]+),[-\+]?([\d]+) [-\+]?([\d]+)(,[-\+]?([\d]+))?\s$~', $part, $matches)) {
                $change = [
                    'leftLine' => [
                        'start' => (int)$matches[1],
                        'count' => (int)$matches[2],
                    ],
                    'rightLine' => [
                        'start' => (int)$matches[3],
                        'count' => (isset($matches[5]) ? (int)$matches[5] : 0),
                    ],
                ];

                continue;
            } else {

                $lines = explode("\n", preg_replace('~\t~', '    ', $part));
                array_pop($lines);
                array_shift($lines);

                $change['lines'] = $lines;
            }

            $changes[] = $change;
        }

        return $changes;
    }

}
