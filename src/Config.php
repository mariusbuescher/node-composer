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

        if ($self->nodeVersion === null) {
            throw new NodeComposerConfigException('You must specify a node-version');
        }


        return $self;
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