<?php

namespace MariusBuescher\NodeComposer;


use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\RemoteFilesystem;
use MariusBuescher\NodeComposer\Exception\NodeComposerConfigException;
use MariusBuescher\NodeComposer\Exception\VersionVerificationException;
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

        $extraConfig = $this->composer->getPackage()->getExtra();
        $packageConfig = $extraConfig['mariusbuescher']['node-composer'] ?? [];

        $this->updateConfigFromPackageProvidesConfig(
            $packageConfig,
            'node-version',
            $packageConfig['package-for-node-version'] ?? 'imponeer/composer-nodejs-installer',
            'nodejs/node'
        );
        $this->updateConfigFromPackageProvidesConfig(
            $packageConfig,
            'yarn-version',
            $packageConfig['package-for-yarn-version'] ?? 'imponeer/composer-yarn-installer',
            'yarnpkg/yarn'
        );

        if (empty($packageConfig)) {
            throw new NodeComposerConfigException('You must configure the node composer plugin');
        }

        $this->config = Config::fromArray($packageConfig);
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
     * Updates local config with data from some specific packages
     *
     * @param array $config Local config for the update
     * @param string $configKey Local config key that will be updated if specific package will be found
     * @param string $packageName Package name from where to fetch provides section data
     * @param string $providesName Provides key name
     */
    protected function updateConfigFromPackageProvidesConfig(array &$config, $configKey, $packageName, $providesName)
    {
        $foundPackages = $this->composer->getRepositoryManager()->getLocalRepository()->findPackages($packageName);
        if (isset($foundPackages[0])) {
            $provides = $foundPackages[0]->getProvides();
            $config[$configKey] = $provides[$providesName]->getPrettyConstraint();
        }
    }
}
