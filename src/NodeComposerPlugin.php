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
use MariusBuescher\NodeComposer\Installer\NodeInstaller;

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

        if (!isset($extraConfig['mariusbuescher']['node-composer'])) {
            throw new NodeComposerConfigException('You must configure the node composer plugin');
        }

        $this->config = Config::fromArray($extraConfig['mariusbuescher']['node-composer']);
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
            $context
        );

        $installedVersion = $nodeInstaller->isInstalled();

        if (
            $installedVersion === false ||
            strpos($installedVersion, 'v' . $this->config->getNodeVersion()) === false
        ) {
            $nodeInstaller->install($this->config->getNodeVersion());
        }
    }
}