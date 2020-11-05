<?php

namespace MariusBuescher\NodeComposer;


class NodeContext
{
    /**
     * @var string
     */
    private $vendorDir;

    /**
     * @var string
     */
    private $binDir;

    /**
     * @var string
     */
    private $osType;

    /**
     * @var string
     */
    private $systemArchitecture;

    /**
     * NodeContext constructor.
     * @param string $vendorDir
     * @param string $binDir
     * @param string $osType
     * @param string $systemArchitecture
     */
    public function __construct(
        $vendorDir,
        $binDir,
        $osType = null,
        $systemArchitecture = null
    ) {
        $this->vendorDir = $vendorDir;
        $this->binDir = $binDir;

        $this->osType = $osType;
        if (!$this->osType) {
            $this->osType = stripos(PHP_OS, 'WIN') === 0 ? 'win' : strtolower(PHP_OS);
        }
        $this->systemArchitecture = is_string($systemArchitecture) ? $systemArchitecture : php_uname('m');
    }

    /**
     * @return string
     */
    public function getOsType()
    {
        return $this->osType;
    }

    /**
     * @return string
     */
    public function getSystemArchitecture()
    {
        return $this->systemArchitecture;
    }

    /**
     * @return string
     */
    public function getVendorDir()
    {
        return $this->vendorDir;
    }

    /**
     * @return string
     */
    public function getBinDir()
    {
        return $this->binDir;
    }
}