<?php

namespace MariusBuescher\NodeComposer;

class ArchitectureMap
{
    /**
     * @var array
     */
    private static $map = array(
        'x86_64' => 'x64',
        'amd64' => 'x64',
        'i586' => 'x86',
    );

    /**
     * @param string $phpArchitecture
     * @return string
     */
    public static function getNodeArchitecture($phpArchitecture)
    {
        $lowercaseArchitecture = strtolower($phpArchitecture);
        return isset(static::$map[$lowercaseArchitecture]) ? static::$map[$lowercaseArchitecture] : $phpArchitecture;
    }
}