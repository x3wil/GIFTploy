<?php

namespace GIFTploy\Git;

class Git
{

    public static $options = [
        "environment_variables" => array(),
        "command" => "git",
        "process_timeout" => 3600
    ];

    public static function getRepository($dir)
    {
        try {
            return new Repository($dir);
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function cloneRepository($path, $url, $branch, \GIFTploy\ProcessConsole $processConsole = null)
    {
        $cloneArgs = ['--branch', $branch];

        if (!$processConsole) {
            $cloneArgs[] = '-q';
        }

        $process = self::run('clone', array_merge($cloneArgs, [$url, $path]), [], $processConsole);

        if (!$process->isSuccessFul()) {
            throw new RuntimeException(sprintf('Error while initializing repository: %s', $process->getErrorOutput()));
        }

        return $process;
//        return new Repository($path, $options);
    }

    public static function setOptions(array $options)
    {
        $options = array_filter($options, function($value) {
            return !is_null($value);
        });

        self::$options = array_merge(self::$options, $options);
    }

    protected static function getProcess($command, array $args = [], array $options = [])
    {
        $options = array_merge($options, self::$options);

        $builder = \Symfony\Component\Process\ProcessBuilder::create(array_merge(array($options["command"], $command), $args));
        $builder->inheritEnvironmentVariables(false);

        $process = $builder->getProcess();
        $process->setEnv($options["environment_variables"]);
        $process->setTimeout($options["process_timeout"]);
        $process->setIdleTimeout($options["process_timeout"]);

        return $process;
    }

    public static function run($command, array $args = [], array $options = [], \GIFTploy\ProcessConsole $processConsole = null)
    {
        if ($processConsole) {
            $args[] = "--progress";
        }

        $process = self::getProcess($command, $args, $options);

        $callback = !$processConsole ? null : function($type, $buffer) use ($processConsole) {
            ob_clean();

            while (ob_get_level()) {
                ob_end_flush();
            }

            ob_start();

            if (\Symfony\Component\Process\Process::ERR === $type) {
                $processConsole->flushProgress($buffer, false);
            } else {
                $processConsole->flushProgress($buffer, true);
            }

            ob_flush();
            flush();
        };

        $process->run($callback);

        return $process;
    }

}
