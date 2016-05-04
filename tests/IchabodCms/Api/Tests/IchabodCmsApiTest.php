<?php

/*
 * This file is part of IchabodCMS API Wrapper
 *
 * (c) James Rickard <james@frodosghost.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IchabodCms\Api\Tests;

use IchabodCms\Api\IchabodCmsApi;

class IchabodCmsApiTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $client = new IchabodCmsApi('foo', 'bar');
        $this->assertInstanceOf('IchabodCms\Api\IchabodCmsApi', $client);
    }

    /**
     * @covers IchabodCmsApi::getLastError
     * @tests ->getLastError()
     */
    public function testGetLastErrorOnSetup()
    {
        $client = new IchabodCmsApi('foo', 'bar');
        $this->assertFalse($client->getLastError(), '->getLastError() returns falses when no data is set from query');
    }

}
