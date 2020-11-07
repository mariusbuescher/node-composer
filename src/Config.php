<?php

namespace MariusBuescher\NodeComposer;

use MariusBuescher\NodeComposer\Exception\NodeComposerConfigException;

class Config
{
    /**
     * @var string
     */
    private $nodeVersion;

    /**
     * @var string
     */
    private $yarnVersion;

    /**
     * @var string
     */
    private $nodeDownloadUrl;

    /**
     * Config constructor.
     */
    private function __construct()
    {
    }

    /**
     * @param array $conf
     * @return Config
     */
    public static function fromArray(array $conf)
    {
        $self = new self();

        $self->nodeVersion = $conf['node-version'];
        $self->nodeDownloadUrl = isset($conf['node-download-url']) ? $conf['node-download-url'] : null;
        $self->yarnVersion = isset($conf['yarn-version']) ? $conf['yarn-version'] : null;

        return $self;
    }

    /**
     * Selects best config from configs list
     *
     * @param Config[] $configs
     * @return Config
     */
    public static function selectBest(array $configs) {
        $maxNodeVersion = null;
        $maxYarnVersion = null;
        $nodeDownloadUrl = null;
        foreach ($configs as $config) {
            if ($maxNodeVersion === null) {
                $maxNodeVersion = $config->nodeVersion;
            } elseif (version_compare($config->nodeVersion, $maxNodeVersion, '>')) {
                $maxNodeVersion = $config->nodeVersion;
            }
            if ($maxYarnVersion === null) {
                $maxYarnVersion = $config->yarnVersion;
            } elseif (version_compare($config->yarnVersion, $maxYarnVersion, '>')) {
                $maxYarnVersion = $config->yarnVersion;
            }
            if ($nodeDownloadUrl === null) {
                $nodeDownloadUrl = $config->nodeDownloadUrl;
            } elseif ($nodeDownloadUrl !== $config->nodeDownloadUrl) {
                throw new NodeComposerConfigException('Defined different nodejs download urls are unsupported right now');
            }
        }

        $ret = new self();
        $ret->nodeDownloadUrl = $nodeDownloadUrl;
        $ret->yarnVersion = $maxYarnVersion;
        $ret->nodeVersion = $maxNodeVersion;

        return $ret;
    }

    /**
     * @return string
     */
    public function getNodeVersion()
    {
        return $this->nodeVersion;
    }

    /**
     * @return string
     */
    public function getYarnVersion()
    {
        return $this->yarnVersion;
    }

    /**
     * @return string
     */
    public function getNodeDownloadUrl()
    {
        return $this->nodeDownloadUrl;
    }
}