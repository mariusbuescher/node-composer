<?php

namespace MariusBuescher\NodeComposer;


use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\CompletePackage;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\RemoteFilesystem;
use MariusBuescher\NodeComposer\Exception\VersionVerificationException;
use MariusBuescher\NodeComposer\Exception\NodeComposerConfigException;
use MariusBuescher\NodeComposer\Installer\NodeInstaller;
use MariusBuescher\NodeComposer\Installer\YarnInstaller;

class NodeComposerPlugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var Config
     */
    private $config;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->config = $this->findBestConfig();

        if ($this->config === null) {
            throw new NodeComposerConfigException('You must configure the node composer plugin');
        }

        if ($this->config->getNodeVersion() === null) {
            throw new NodeComposerConfigException('You must specify a node-version');
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            ScriptEvents::POST_UPDATE_CMD => array(
                array('onPostUpdate', 1)
            ),
            ScriptEvents::POST_INSTALL_CMD => array(
                array('onPostUpdate', 1)
            )
        );
    }

    public function onPostUpdate(Event $event)
    {
        $context = new NodeContext(
            $this->composer->getConfig()->get('vendor-dir'),
            $this->composer->getConfig()->get('bin-dir')
        );

        $nodeInstaller = new NodeInstaller(
            $this->io,
            new RemoteFilesystem($this->io, $this->composer->getConfig()),
            $context,
            $this->config->getNodeDownloadUrl()
        );

        $installedNodeVersion = $nodeInstaller->isInstalled();

        if (
            $installedNodeVersion === false ||
            strpos($installedNodeVersion, 'v' . $this->config->getNodeVersion()) === false
        ) {
            $this->io->write(sprintf(
                'Installing node.js v%s',
                $this->config->getNodeVersion()
            ));

            $nodeInstaller->install($this->config->getNodeVersion());

            $installedNodeVersion = $nodeInstaller->isInstalled();
            if (strpos($installedNodeVersion, 'v' . $this->config->getNodeVersion()) === false) {
                $this->io->write(array_merge(['Bin files:'], glob($context->getBinDir() . '/*.*')), true, IOInterface::VERBOSE);
                throw new VersionVerificationException('nodejs', $this->config->getNodeVersion(), $installedNodeVersion);
            } else {
                $this->io->overwrite(sprintf(
                    'node.js v%s installed',
                    $this->config->getNodeVersion()
                ));
            }
        }

        if ($this->config->getYarnVersion() !== null) {
            $yarnInstaller = new YarnInstaller(
                $this->io,
                $context
            );

            $installedYarnVersion = $yarnInstaller->isInstalled();

            if (
                $installedYarnVersion === false ||
                strpos($installedYarnVersion, $this->config->getYarnVersion()) === false
            ) {
                $this->io->write(sprintf(
                    'Installing yarn v%s',
                    $this->config->getYarnVersion()
                ));

                $yarnInstaller->install($this->config->getYarnVersion());

                $installedYarnVersion = $yarnInstaller->isInstalled();
                if (strpos($installedYarnVersion, $this->config->getYarnVersion()) === false) {
                    $this->io->write(array_merge(['Bin files:'], glob($context->getBinDir() . '/*.*')), true, IOInterface::VERBOSE);
                    throw new VersionVerificationException('yarn', $this->config->getYarnVersion(), $installedYarnVersion);
                } else {
                    $this->io->write(sprintf(
                        'node.js v%s installed',
                        $this->config->getNodeVersion()
                    ));
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    /**
     * @inheritDoc
     */
    public function uninstall(Composer $composer, IOInterface $io)
    {
    }

    /**
     * Find best node config to use from all package tree
     *
     * @return Config|null
     */
    protected function findBestConfig() {
        $extraConfig = $this->composer->getPackage()->getExtra();

        if (isset($extraConfig['mariusbuescher']['node-composer'])) {
            return Config::fromArray($extraConfig['mariusbuescher']['node-composer']);
        }

        $configs = [];
        /**
         * @var CompletePackage $package
         */
        foreach ($this->composer->getRepositoryManager()->getLocalRepository()->getPackages() as $package) {
            $extraConfig = $package->getExtra();
            if (isset($extraConfig['mariusbuescher']['node-composer'])) {
                $config[] = Config::fromArray($extraConfig['mariusbuescher']['node-composer']);
            }
        }
        if (empty($configs)) {
            return null;
        }
        return Config::selectBest($configs);
    }
}
