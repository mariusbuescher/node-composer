<?php

namespace MariusBuescher\NodeComposer;

use Composer\Util\Filesystem;
use Composer\Util\ProcessExecutor;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

/**
 * Class to symlink bin files
 *
 * @package MariusBuescher\NodeComposer
 */
class BinLinker
{
    /**
     * Composer vendor bin dir
     *
     * @var string
     */
    protected $vendorBinDir;

    /**
     * Filesystem instance for operations with files
     *
     * @var SymfonyFilesystem
     */
    protected $filesystem;

    /**
     * Os type
     *
     * @var string
     */
    private $osType;

    /**
     * Filesystem constructor.
     *
     * @param string $vendorBinDir Where all bin files should be kept?
     * @param string $osType OS type
     */
    public function __construct($vendorBinDir, $osType)
    {
        $this->vendorBinDir = $vendorBinDir;
        $this->osType = strtolower($osType);
        $this->filesystem = new SymfonyFilesystem();
    }

    /**
     * Links bin item
     *
     * @param string $from Path from where to create symlink
     * @param string $to Path to where create symlink
     */
    public function linkBin($from, $to)
    {
        if ($this->osType === 'win') {
            $this->filesystem->dumpFile(
                $to . '.bat',
                $this->generateBatchCode($from)
            );
        } else {
            $this->filesystem->symlink($from, $to);
        }
    }

    /**
     * Generates batch code
     *
     * @param string $from Path from where to create symlink
     *
     * @return string
     */
    protected function generateBatchCode($from)
    {
        $binPath = dirname(
            $this->filesystem->makePathRelative($from, $this->vendorBinDir)
        );
        $caller = basename($from);

        $binPath = str_replace('/', '\\', $binPath);

        return "@ECHO OFF". PHP_EOL .
            "setlocal DISABLEDELAYEDEXPANSION". PHP_EOL .
            "set EXE_PATH=%~dp0\\".trim(ProcessExecutor::escape($binPath), '"\'').PHP_EOL.
            "set NODE_PATH=%EXE_PATH%\\node_modules".PHP_EOL.
            "set Path=%EXE_PATH%;%Path%;".PHP_EOL.
            "{$caller} %*".PHP_EOL;
    }

    /**
     * Unlinks bin item
     *
     * @param string $to Path to unlink
     *
     * @return bool
     */
    public function unlinkBin($to) {
        if ($this->osType === 'win') {
            if ($this->filesystem->exists($to . '.bat')) {
                $this->filesystem->remove($to . '.bat');
                return true;
            }
            return false;
        } elseif ($this->filesystem->exists($to)) {
            $this->filesystem->remove($to);
            return true;
        }
        return false;
    }

}