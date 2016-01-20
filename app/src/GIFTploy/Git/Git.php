<?php

namespace GIFTploy\Git;

/**
 * General class for git management.
 *
 * @author Patrik Chotěnovský
 */
class Git
{

    /**
     * Array of default options.
     *
     * @var array
     */
    public static $options = [
        "environment_variables" => array(),
        "command" => "git",
        "process_timeout" => 3600
    ];

    /**
     * Returns instance of Repository.
     *
     * @param string $dir
     * @return Repository|null Repository or null if doesn't exist
     */
    public static function getRepository($dir)
    {
        try {
            return new Repository($dir);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Clone a repository to selected path.
     *
     * @param string $path      Destination path for repository clone
     * @param string $url       Url of repository to clone
     * @param string $branch    Branch to clone
     * @param ProcessConsole $processConsole
     * 
     * @return Repository
     *
     * @throws RuntimeException If clone process has failed
     */
    public static function cloneRepository($path, $url, $branch, ProcessConsole $processConsole = null)
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

    /**
     * Sets and override options.
     *
     * @param array $options
     */
    public static function setOptions(array $options)
    {
        $options = array_filter($options, function($value) {
            return !is_null($value);
        });

        self::$options = array_merge(self::$options, $options);
    }

    /**
     * Returns configured and prepared process.
     *
     * @param string $command   Command to execute
     * @param array $args       Additional arguments
     * @param array $options    Options to override
     *
     * @return \Symfony\Component\Process\Process   Prepared process to run
     */
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

    /**
     * Configure and run process.
     *
     * @param type $command     Command to execute
     * @param array $args       Additional arguments
     * @param array $options    Options to override
     * @param ProcessConsole $processConsole
     *
     * @return \Symfony\Component\Process\Process     Running process
     */
    public static function run($command, array $args = [], array $options = [], ProcessConsole $processConsole = null)
    {
        if ($processConsole) {
            $args[] = "--progress";
        }

        $process = self::getProcess([$command], $args, $options);
        
        return self::runProcess($process);
    }

    /**
     * Run given process.
     *
     * @param \Symfony\Component\Process\Process    $process    Configured process
     *
     * @return \Symfony\Component\Process\Process   Running process
     */
    public static function runProcess(\Symfony\Component\Process\Process $process)
    {
        $process->run();

        return $process;
    }
}
