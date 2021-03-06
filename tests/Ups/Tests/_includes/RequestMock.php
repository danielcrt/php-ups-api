<?php

namespace Ups\Tests;

use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use Ups\RequestInterface;
use Ups\ResponseInterface;

class RequestMock implements RequestInterface
{
    const RESPONSE_DIRECTORY = '/../_files/responses';
    const REQUEST_DIRECTORY = '/../_files/requests';

    /**
     * @var string
     */
    protected $access;

    /**
     * @var string
     */
    protected $request;

    /**
     * @var string
     */
    protected $endpointUrl;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var string
     */
    private $responsePath;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param string|null $responsePath
     */
    public function __construct(LoggerInterface $logger = null, $responsePath = null)
    {
        $this->logger = $logger;
        $this->responsePath = $responsePath;
    }

    /**
     * @param string $request
     *
     * @return SimpleXMLElement
     */
    public function getExpectedRequestXml($request)
    {
        $args = func_get_args();
        if (isset($args[1]) && is_array($args[1])) {
            $args = $args[1];
        } else {
            $args = null;
        }

        $request = realpath(__DIR__ . self::REQUEST_DIRECTORY . $request);
        if ($request && is_file($request)) {
            $request = file_get_contents($request);
            if (isset($args)) {
                $request = call_user_func_array('sprintf', array_merge([$request], $args));
            }

            return new SimpleXMLElement($request);
        }

        return;
    }

    /**
     * @return SimpleXMLElement
     */
    public function getRequestXml()
    {
        return new SimpleXMLElement($this->getRequest());
    }

    /**
     * @param string|null $access      The access request xml
     * @param string|null $request     The request xml
     * @param string|null $endpointUrl The UPS API Endpoint URL
     * @param string|null $method method
     * @param array|null $headers headers
     *
     * @return ResponseInterface
     */
    public function request($access = null, $request = null, $endpointUrl = null, $method = null, $headers = [])
    {
        if (null !== $access) {
            $this->setAccess($access);
        }
        if (null !== $request) {
            $this->setRequest($request);
        }
        if (null !== $endpointUrl) {
            $this->setEndpointUrl($endpointUrl);
        }

        $response = realpath(__DIR__ . self::RESPONSE_DIRECTORY . $this->responsePath);
        if ($response && is_file($response)) {
            $response = file_get_contents($response);
            if (!empty($response)) {
                if (function_exists('mb_convert_encoding')) {
                    $response = mb_convert_encoding($response, 'UTF-8', mb_detect_encoding($response));
                }
                $response = new SimpleXMLElement($response);
                if (isset($response->Response) && isset($response->Response->ResponseStatusCode)) {
                    return (new ResponseMock())->setResponse($response);
                }
            }
        }

        return new ResponseMock();
    }

    /**
     * @param $access
     *
     * @return $this
     */
    public function setAccess($access)
    {
        $this->access = $access;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * @param $request
     *
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return string
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param $endpointUrl
     *
     * @return $this
     */
    public function setEndpointUrl($endpointUrl)
    {
        $this->endpointUrl = $endpointUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getEndpointUrl()
    {
        return $this->endpointUrl;
    }

    /**
     * Get the value of method
     *
     * @return  string
     */ 
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the value of method
     *
     * @param  string  $method
     *
     * @return  self
     */ 
    public function setMethod(string $method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get the value of headers
     *
     * @return  array
     */ 
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set the value of headers
     *
     * @param  array  $headers
     *
     * @return  self
     */ 
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }
}
