<?php

namespace ModernMedia\Monolog\Handler;

use Monolog\Handler\NativeMailerHandler;

/**
 * Class NativeHtmlMailerHandler
 * @package MarijnvdWerf\Monolog\Handler
 */
class NativeHtmlMailerHandler extends NativeMailerHandler {

    protected $headers = array(
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset="utf8"'
    );

}
