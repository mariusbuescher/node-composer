<?php

namespace MariusBuescher\NodeComposer;


interface InstallerInterface
{
    /**
     * @param string $version
     * @return bool
     */
    public function install($version);

    /**
     * @return string|false
     */
    public function isInstalled();
}