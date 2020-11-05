<?php

namespace MariusBuescher\NodeComposer\Exception;

/**
 * Exception when not possible to verify version
 *
 * @package MariusBuescher\NodeComposer\Exception
 */
class VersionVerificationException extends \RuntimeException
{

    /**
     * CantVerifyVersionException constructor.
     *
     * @param string $app App for what version verification failed
     * @param mixed $neededVersion Needed version
     * @param mixed $gotVersion Got version
     * @param int $code Error code
     * @param mixed $previous Previous error
     */
    public function __construct($app, $neededVersion, $gotVersion, $code = 0, $previous = null)
    {
        parent::__construct(
            sprintf(
                "Could not verify %s version (needed - %s; got - %s)",
                $app,
                json_encode($neededVersion),
                json_encode($gotVersion)
            ),
            $code,
            $previous
        );
    }

}