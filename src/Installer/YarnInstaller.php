<?php

namespace MariusBuescher\NodeComposer\Installer;

use Composer\IO\IOInterface;
use MariusBuescher\NodeComposer\InstallerInterface;
use MariusBuescher\NodeComposer\NodeContext;

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

        $output = $return = null;

        exec(
            $this->context->getBinDir() . DIRECTORY_SEPARATOR . 'npm install --global yarn@' . $version,
            $output,
            $return
        );

        if ($return !== 0) {
            throw new \RuntimeException('Could not install yarn');
        }

        $sourceDir = $this->getNpmBinaryPath();

        $this->linkExecutables($sourceDir, $this->context->getBinDir());

        return true;
    }

    public function isInstalled()
    {
        $output = array();
        $return = null;

        $nodeExecutable = $this->context->getBinDir() . DIRECTORY_SEPARATOR . 'yarn';

        exec("$nodeExecutable --version", $output, $return);

        if ($return === 0) {
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
        $output = array();
        $return = null;

        exec($this->context->getBinDir() . DIRECTORY_SEPARATOR . 'npm -g bin', $output, $return);

        if ($return !== 0) {
            throw new \RuntimeException('npm must be installed');
        }

        return $output[0];
    }
}