<?php

/*
 * This file is part of IchabodCMS API Wrapper
 *
 * (c) James Rickard <james@frodosghost.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IchabodCms\Api\Exception;

/**
 * IchabodCms\Api\Exception\QueryException
 *
 * @author James Rickard <james@frodosghost.com>
 */
class QueryException extends \Exception
{

    public function __construct($message = null, $body = [], $code = 0, \Exception $previous = null)
    {
        $this->body = $body;

        parent::__construct($message, $code, $previous);
    }

    public function getBody()
    {
        return $this->body;
    }
}
