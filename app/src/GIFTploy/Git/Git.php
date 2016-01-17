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

        return new Repository($path);
    }

    public static function setOptions(array $options)
    {
        $options = array_filter($options, function($value) {
            return !is_null($value);
        });

        self::$options = array_merge(self::$options, $options);
    }

    public static function getProcess($command, array $args = [], array $options = [])
    {
        $options = array_merge($options, self::$options);
        $builder = \Symfony\Component\Process\ProcessBuilder::create(array_merge([$options["command"]], $command, $args));
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

        $process = self::getProcess([$command], $args, $options);
        
        return self::runProcess($process);
    }

    public static function runProcess(\Symfony\Component\Process\Process $process)
    {
        $process->run();

        return $process;
    }
}
