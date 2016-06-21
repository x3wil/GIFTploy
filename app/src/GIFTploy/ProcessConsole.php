<?php

namespace GIFTploy;

class ProcessConsole
{

    protected $consoleEnabled = false;
    protected $outputBuffering = false;
    protected $timeStart = 0;

    public function __construct()
    {
        $this->consoleEnabled = true;
        $this->timeStart = microtime(true);

        $this->enableOutputBuffering();
    }

    public function isConsoleEnabled()
    {
        return $this->consoleEnabled;
    }

    private function enableOutputBuffering()
    {
        ob_clean();

        while (ob_get_level()) {
            ob_end_flush();
        }

        header('Content-Type: text/html; charset=utf-8');
        ob_start();

        echo '<link rel="stylesheet" href="/css/console.css">';
        echo '<script src="/js/console.js"></script>';

        return $this;
    }

    public function closeConsole()
    {
        if (!$this->isConsoleEnabled()) {
            return false;
        }

        $this->flushTotalTime();

        ob_end_flush();

        $this->consoleEnabled = false;
    }

    public function flushProgress($message, $result = null, $onNewLine = true)
    {
        if (!$this->isConsoleEnabled()) {
            return false;
        }

        $output = '';

        if ($onNewLine) {
            $output .= '<br>';
        }

        if ($message != '') {
            if ($result === true) {
                $output .= sprintf('<span class="true">%s</span>', $message);
            } elseif ($result === false) {
                $output .= sprintf('<span class="false">%s</span>', $message);
            } else {
                $message = sprintf('[%s] %s', date('Y-m-d H:i:s'), $message);
                $output .= sprintf('<span class="default">%s</span>', $message);
            }
        }

        return $this->flushPlain($output);
    }

    public function flushPlain($message)
    {
        if (!$this->isConsoleEnabled()) {
            return false;
        }

        echo $message;

        ob_flush();
        flush();

        return $this;
    }

    public function flushTotalTime()
    {
        return $this->flushPlain(sprintf('<br><br>Total time - %ss', round(microtime(true) - $this->timeStart, 1)));
    }

    public function flushResult($result, $errorMessage, $onNewLine = false)
    {
        $message = ($result ? 'OK' : 'Error ('.$errorMessage.')');

        return $this->flushProgress($message, $result, $onNewLine);
    }

}
