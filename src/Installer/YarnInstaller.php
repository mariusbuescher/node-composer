<?php

namespace MariusBuescher\NodeComposer\Installer;

use Composer\IO\IOInterface;
use MariusBuescher\NodeComposer\InstallerInterface;
use MariusBuescher\NodeComposer\NodeContext;
use Symfony\Component\Process\Process;

class YarnInstaller implements InstallerInterface
{
    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var NodeContext
     */
    private $context;

    /**
     * NodeDownloader constructor.
     * @param IOInterface $io
     * @param NodeContext $context
     */
    public function __construct(
        IOInterface $io,
        NodeContext $context
    ) {
        $this->io = $io;
        $this->context = $context;
    }

    /**
     * @param string $version
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function install($version)
    {
        if (!is_string($version)) {
            throw new \InvalidArgumentException(
                sprintf('Version must be a string, %s given'), gettype($version)
            );
        }

        $process = new Process(
            $this->context->getBinDir() . DIRECTORY_SEPARATOR . 'npm install --global yarn@' . $version
        );
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Could not install yarn');
        }

        $sourceDir = $this->getNpmBinaryPath();

        $this->linkExecutables($sourceDir, $this->context->getBinDir());

        return true;
    }

    public function isInstalled()
    {
        $nodeExecutable = $this->context->getBinDir() . DIRECTORY_SEPARATOR . 'yarn';

        $process = new Process("$nodeExecutable --version");
        $process->run();

        if ($process->isSuccessful()) {
            $output = explode("\n", $process->getIncrementalOutput());
            return $output[0];
        } else {
            return false;
        }
    }

    /**
     * @param string $sourceDir
     * @param string $targetDir
     */
    private function linkExecutables($sourceDir, $targetDir)
    {
        $yarnPath = realpath($sourceDir . DIRECTORY_SEPARATOR . 'yarn');
        $yarnLink = $targetDir . DIRECTORY_SEPARATOR . 'yarn';

        if (realpath($yarnLink)) {
            unlink($yarnLink);
        }

        symlink($yarnPath, $yarnLink);

        $yarnpkgPath = realpath($sourceDir . DIRECTORY_SEPARATOR . 'yarnpkg');
        $yarnpkgLink = $targetDir . DIRECTORY_SEPARATOR . 'yarnpkg';

        if (realpath($yarnpkgLink)) {
            unlink($yarnpkgLink);
        }

        symlink($yarnpkgPath, $yarnpkgLink);
    }

    /**
     * @return string
     */
    private function getNpmBinaryPath()
    {
        $process = new Process($this->context->getBinDir() . DIRECTORY_SEPARATOR . 'npm -g bin');
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('npm must be installed');
        } else {
            $output = explode("\n", $process->getIncrementalOutput());
            return $output[0];
        }
    }
}