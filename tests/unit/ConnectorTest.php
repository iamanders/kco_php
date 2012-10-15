<?php

/**
 * Copyright 2012 Klarna AB
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * File containing the Klarna_Checkout_Connector unittest
 *
 * PHP version 5.3
 *
 * @category  Payment
 * @package   Klarna_Checkout
 * @author    Klarna <support@klarna.com>
 * @copyright 2012 Klarna AB
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache license v2.0
 * @link      http://integration.klarna.com/
 */

require_once 'Checkout/ResourceInterface.php';
require_once 'Checkout/ConnectorInterface.php';
require_once 'Checkout/HTTP/HTTPInterface.php';
require_once 'Checkout/HTTP/Request.php';
require_once 'Checkout/HTTP/Response.php';
require_once 'Checkout/Exception.php';
require_once 'Checkout/Connector.php';
require_once 'tests/ResourceStub.php';
require_once 'tests/CurlStub.php';

/**
 * General UnitTest for the Connector class
 *
 * @category  Payment
 * @package   Klarna_Checkout
 * @author    Rickard D. <rickard.dybeck@klarna.com>
 * @author    Christer G. <christer.gustavsson@klarna.com>
 * @copyright 2012 Klarna AB
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache license v2.0
 * @link      http://integration.klarna.com/
 */
class Klarna_Checkout_ConnectorTest extends PHPUnit_Framework_TestCase
{

    /**
     * Stubbed Order Object
     *
     * @var Klarna_Checkout_ResourceInterface
     */
    public $orderStub;

    /**
     * Set up tests
     *
     * @return void
     */
    public function setUp()
    {
        $this->httpInterface = $this->getMock(
            'Klarna_Checkout_HTTP_HTTPInterface'
        );

        $this->orderStub = new Klarna_Checkout_ResourceStub;

        $this->digest = $this->getMock(
            'Klarna_Checkout_Digester', array('createDigest')
        );
    }

    /**
     * Test invalid method throws an exception.
     *
     * @return void
     */
    public function testApplyInvalidMethod()
    {
        $this->setExpectedException('InvalidArgumentException');


        $digest = $this->getMock('Klarna_Checkout_Digester');

        $object = new Klarna_Checkout_Connector(
            $this->httpInterface, $digest, 'aboogie'
        );

        $object->apply('FLURB', $this->orderStub);
    }

    /**
     * Data Provider with HTTP Error Codes.
     *
     * @return array
     */
    public function responseErrorCodes()
    {
        return array(
            array(400, "Bad Request"),
            array(401, "Unauthorized"),
            array(402, "PaymentRequired"),
            array(403, "Forbidden"),
            array(404, "Not Found"),
            array(406, "HTTP Error"),
            array(409, "HTTP Error"),
            array(412, "HTTP Error"),
            array(415, "HTTP Error"),
            array(422, "HTTP Error"),
            array(428, "HTTP Error"),
            array(429, "HTTP Error"),
            array(500, "Internal Server Error"),
            array(502, "Service temporarily overloaded"),
            array(503, "Gateway timeout")
        );
    }

    /**
     * Test apply with GET method throws an exception if status code is an
     * error.
     *
     * @param int    $code    http error code
     * @param string $message error message
     *
     * @dataProvider responseErrorCodes
     * @return void
     */
    public function testApplyGetErrorCode($code, $message)
    {
        $this->setExpectedException(
            'Klarna_Checkout_HTTP_Status_Exception', $message, $code
        );

        $curl = new Klarna_Checkout_HTTP_Curl_Stub;

        $data = array(
            'code' => $code,
            'headers' => array(),
            'payload' => $message
        );
        $curl->addResponse($data);

        $this->digest->expects($this->once())
            ->method('createDigest')
            ->with('aboogie')
            ->will($this->returnValue('stnaeu\eu2341aoaaoae=='));

        $object = new Klarna_Checkout_Connector($curl, $this->digest, 'aboogie');
        $result = $object->apply('GET', $this->orderStub);

        $this->assertNotNull($result, 'Response Object');
    }

    /**
     * Test apply with POST method throws an exception if status code is an
     * error.
     *
     * @param int    $code    http error code
     * @param string $message error message
     *
     * @dataProvider responseErrorCodes
     * @return void
     */
    public function testApplyPostErrorCode($code, $message)
    {
        $this->setExpectedException(
            'Klarna_Checkout_HTTP_Status_Exception', $message, $code
        );

        $curl = new Klarna_Checkout_HTTP_Curl_Stub;

        $data = array(
            'code' => $code,
            'headers' => array(),
            'payload' => $message
        );
        $curl->addResponse($data);

        $this->digest->expects($this->once())
            ->method('createDigest')
            ->with('[]aboogie')
            ->will($this->returnValue('stnaeu\eu2341aoaaoae=='));

        $object = new Klarna_Checkout_Connector($curl, $this->digest, 'aboogie');
        $result = $object->apply('POST', $this->orderStub);

        $this->assertNotNull($result, 'Response Object');
    }
}
