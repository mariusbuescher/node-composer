<?php

namespace MariusBuescher\NodeComposer\Installer;

use Composer\IO\IOInterface;
use Composer\Util\RemoteFilesystem;
use InvalidArgumentException;
use MariusBuescher\NodeComposer\ArchitectureMap;
use MariusBuescher\NodeComposer\InstallerInterface;
use MariusBuescher\NodeComposer\NodeContext;
use Symfony\Component\Process\Process;

class NodeInstaller implements InstallerInterface
{
    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var RemoteFilesystem
     */
    private $remoteFs;

    /**
     * @var NodeContext
     */
    private $context;

    /**
     * @var string
     */
    private $downloadUriTemplate;

    /**
     * NodeDownloader constructor.
     * @param IOInterface $io
     * @param RemoteFilesystem $remoteFs
     * @param NodeContext $context
     * @param string $downloadUriTemplate
     */
    public function __construct(
        IOInterface $io,
        RemoteFilesystem $remoteFs,
        NodeContext $context,
        $downloadUriTemplate = null
    ) {
        $this->io = $io;
        $this->remoteFs = $remoteFs;
        $this->context = $context;

        $this->downloadUriTemplate = is_string($downloadUriTemplate) ? $downloadUriTemplate :
            'https://nodejs.org/dist/v${version}/node-v${version}-${osType}-${architecture}.${format}';
    }

    /**
     * @param string $version
     * @throws InvalidArgumentException
     * @return bool
     */
    public function install($version)
    {
        if (!is_string($version)) {
            throw new InvalidArgumentException(
                sprintf('Version must be a string, %s given', gettype($version))
            );
        }

        $this->downloadExecutable($version);

        return true;
    }

    /**
     * @return string|false
     */
    public function isInstalled()
    {
        $nodeExecutable = $this->context->getBinDir() . DIRECTORY_SEPARATOR . 'node';

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
     * @param string $version
     */
    private function downloadExecutable($version)
    {
        $downloadUri = $this->buildDownloadLink($version);

        $fileName = $this->context->getVendorDir() . DIRECTORY_SEPARATOR .
            pathinfo(parse_url($downloadUri, PHP_URL_PATH), PATHINFO_BASENAME);

        $this->remoteFs->copy(
            parse_url($downloadUri, PHP_URL_HOST),
            $downloadUri,
            $fileName,
            true
        );

        $targetPath = $this->context->getVendorDir() . DIRECTORY_SEPARATOR .
            pathinfo(parse_url($downloadUri, PHP_URL_PATH), PATHINFO_BASENAME);

        $targetPath = preg_replace('/\.(tar\.gz|zip)$/', '', $targetPath);

        $this->unpackExecutable($fileName, $targetPath);

        $this->linkExecutables($targetPath, $this->context->getBinDir());
    }

    /**
     * @param string $version
     * @return string
     */
    private function buildDownloadLink($version)
    {
        return preg_replace(
            array(
                '/\$\{version\}/',
                '/\$\{osType\}/',
                '/\$\{architecture\}/',
                '/\$\{format\}/'
            ),
            array(
                $version,
                strtolower($this->context->getOsType()),
                ArchitectureMap::getNodeArchitecture($this->context->getSystemArchitecture()),
                $this->context->getOsType() === 'win' ? 'zip' : 'tar.gz'
            ),
            $this->downloadUriTemplate
        );
    }

    /**
     * @param string $source
     * @param string $targetDir
     */
    private function unpackExecutable($source, $targetDir)
    {
        if (realpath($targetDir)) {
            $files = glob($targetDir . DIRECTORY_SEPARATOR . '**' . DIRECTORY_SEPARATOR . '*');
            foreach ($files as $file) {
                unlink($file);
            }
        } else {
            mkdir($targetDir);
        }

        if (preg_match('/\.zip$/', $source) === 1) {
            $this->unzip($source, $targetDir);
        } else {
            $this->untar($source, $targetDir);
        }
    }

    /**
     * @param string $source
     * @param string $targetDir
     */
    private function unzip($source, $targetDir)
    {
        $zip = new \ZipArchive();
        $res = $zip->open($source);
        if ($res === true) {
            // extract it to the path we determined above
            $zip->extractTo($targetDir);
            $zip->close();
        } else {
            throw new \RuntimeException(sprintf('Unable to extract file %s', $source));
        }

        unlink($source);
    }

    /**
     * @param string $source
     * @param string $targetDir
     */
    private function untar($source, $targetDir)
    {
        $process = new Process(
            "tar -xvf ".$source." -C ".escapeshellarg($targetDir)." --strip 1"
        );
        $process->run();
        
        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf(
                'An error occurred while untaring NodeJS (%s) to %s',
                $source,
                $targetDir
            ));
        }

        unlink($source);
    }

    /**
     * @param string $sourceDir
     * @param string $targetDir
     */
    private function linkExecutables($sourceDir, $targetDir)
    {
        $nodePath = $this->context->getOsType() === 'win' ?
            realpath($sourceDir . DIRECTORY_SEPARATOR . 'node.exe') :
            realpath($sourceDir . DIRECTORY_SEPARATOR . 'bin/node');
        $nodeLink = $targetDir . DIRECTORY_SEPARATOR . 'node';

        if (realpath($nodeLink)) {
            unlink($nodeLink);
        }

        symlink($nodePath, $nodeLink);

        $npmPath = $this->context->getOsType() === 'win' ?
            realpath($sourceDir . DIRECTORY_SEPARATOR . 'npm') :
            realpath($sourceDir . DIRECTORY_SEPARATOR . 'bin/npm');
        $npmLink = $targetDir . DIRECTORY_SEPARATOR . 'npm';

        if (realpath($npmLink)) {
            unlink($npmLink);
        }

        symlink($npmPath, $npmLink);
    }
}