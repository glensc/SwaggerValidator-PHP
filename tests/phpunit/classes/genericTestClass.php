<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of genericTestClass
 *
 * @author Nabbar
 */
class genericTestClass extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var \Swagger\Object\Swagger
     */
    public $swaggerObject;

    /**
     *
     * @var \Swagger\Common\ReferenceFile
     */
    public $swaggerFileObject;

    /**
     *
     * @var string
     */
    public $swaggerFilePath;

    /**
     *
     * @var string
     */
    public $scheme;

    /**
     *
     * @var string
     */
    public $host;

    /**
     *
     * @var string
     */
    public $basePath;

    /**
     *
     * @var array
     */
    public $swaggerModel;

    public function setUp()
    {

    }

    public function tearDown()
    {

    }

    public static function setUpBeforeClass()
    {
        \Swagger\Common\CollectionReference::prune();
        \Swagger\Common\CollectionFile::prune();
        \Swagger\Common\CollectionType::pruneInstance();
        \Swagger\Common\Factory::pruneInstance();
        \Swagger\Common\FactorySwagger::pruneInstance();

        \Swagger\Common\Context::setConfig('log', 'loadFile', false);
        \Swagger\Common\Context::setConfig('log', 'loadRef', false);
        \Swagger\Common\Context::setConfig('log', 'replaceRef', false);
        \Swagger\Common\Context::setConfig('log', 'decode', false);
        \Swagger\Common\Context::setConfig('log', 'validate', false);
        \Swagger\Common\Context::setConfig('log', 'model', false);
    }

    public static function tearDownAfterClass()
    {
        \Swagger\Common\CollectionReference::prune();
        \Swagger\Common\CollectionFile::prune();
        \Swagger\Common\CollectionType::pruneInstance();
        \Swagger\Common\Factory::pruneInstance();
        \Swagger\Common\FactorySwagger::pruneInstance();

        \Swagger\Common\Context::setConfig('log', 'loadFile', false);
        \Swagger\Common\Context::setConfig('log', 'loadRef', false);
        \Swagger\Common\Context::setConfig('log', 'replaceRef', false);
        \Swagger\Common\Context::setConfig('log', 'decode', false);
        \Swagger\Common\Context::setConfig('log', 'validate', false);
        \Swagger\Common\Context::setConfig('log', 'model', false);
    }

    public function swaggerBuild()
    {
        $this->assertNotEmpty($this->swaggerFilePath);
        $this->assertFileExists($this->swaggerFilePath);

        $this->swaggerFileObject = \Swagger\Common\CollectionFile::getInstance()->get($this->swaggerFilePath);

        $this->assertInternalType('object', $this->swaggerFileObject);
        $this->assertInstanceOf('\Swagger\Common\ReferenceFile', $this->swaggerFileObject);

        $this->assertObjectHasAttribute('fileUri', $this->swaggerFileObject);
        $this->assertNotEmpty($this->swaggerFileObject->fileUri);

        $this->assertObjectHasAttribute('fileObj', $this->swaggerFileObject);
        $this->assertNotEmpty($this->swaggerFileObject->fileObj);

        $this->assertObjectHasAttribute('fileTime', $this->swaggerFileObject);
        $this->assertNotEmpty($this->swaggerFileObject->fileTime);

        $this->assertObjectHasAttribute('fileHash', $this->swaggerFileObject);
        $this->assertNotEmpty($this->swaggerFileObject->fileHash);

        $this->assertObjectHasAttribute('basePath', $this->swaggerFileObject);
        $this->assertNotEmpty($this->swaggerFileObject->basePath);

        $this->assertObjectHasAttribute('baseType', $this->swaggerFileObject);
        $this->assertNotEmpty($this->swaggerFileObject->baseType);

        $this->swaggerObject = new \Swagger\Object\Swagger();

        $this->assertInternalType('object', $this->swaggerObject);
        $this->assertInstanceOf('\Swagger\Object\Swagger', $this->swaggerObject);

        $this->swaggerObject->jsonUnSerialize($this->swaggerGetContext(), $this->swaggerFileObject->fileObj);

        $this->assertInternalType('object', $this->swaggerObject);
        $this->assertInstanceOf('\Swagger\Object\Swagger', $this->swaggerObject);

        $this->assertTrue(isset($this->swaggerObject->swagger));
        $this->assertNotEmpty($this->swaggerObject->swagger);

        $this->assertInternalType('string', $this->swaggerObject->swagger);

        $this->assertTrue(isset($this->swaggerObject->info));
        $this->assertNotEmpty($this->swaggerObject->info);

        $this->assertInternalType('object', $this->swaggerObject->info);
        $this->assertInstanceOf('\Swagger\Object\Info', $this->swaggerObject->info);

        $this->assertTrue(isset($this->swaggerObject->paths));
        $this->assertNotEmpty($this->swaggerObject->paths);

        $this->assertInternalType('object', $this->swaggerObject->paths);
        $this->assertInstanceOf('\Swagger\Object\Paths', $this->swaggerObject->paths);

        $schemes = $this->swaggerObject->schemes;
        if (!empty($schemes)) {
            $this->assertInternalType('array', $schemes);
            $this->scheme = array_shift($schemes);
        }

        $host = $this->swaggerObject->host;
        if (!empty($host)) {
            $this->assertInternalType('string', $host);
            $this->host = $host;
        }

        $basepath = $this->swaggerObject->basePath;
        if (!empty($basepath)) {
            $this->assertInternalType('string', $basepath);
            $this->basePath = $basepath;
        }
        if (substr($this->basePath, -1, 1) == '/') {
            $this->basePath = substr($this->basePath, 0, -1);
        }
    }

    public function swaggerGetContext($mode = null, $type = null)
    {
        $context = new \Swagger\Common\Context($mode, $type);

        $this->assertInternalType('object', $context);
        $this->assertInstanceOf('\Swagger\Common\Context', $context);

        return $context;
    }

    /**
     * @depends swaggerBuild
     */
    public function loadModel()
    {
        $keyParameters = \Swagger\Common\FactorySwagger::KEY_PARAMETERS;
        $keyResponses  = \Swagger\Common\FactorySwagger::KEY_RESPONSES;
        $keyConsumes   = \Swagger\Common\FactorySwagger::KEY_CONSUMES;
        $keyProduces   = \Swagger\Common\FactorySwagger::KEY_PRODUCES;

        $this->assertInternalType('object', $this->swaggerObject);
        $this->assertInstanceOf('\Swagger\Object\Swagger', $this->swaggerObject);

        $model = $this->swaggerObject->getModel($this->swaggerGetContext());

        $this->assertNotEmpty($model);
        $this->assertInternalType('array', $model);

        $this->swaggerModel = array();

        foreach ($this->swaggerObject->getModel($this->swaggerGetContext()) as $path => $pathItem) {

            foreach ($pathItem as $method => $operation) {
                $parameters = array();
                $responses  = array();
                $consumes   = array();
                $produces   = array();

                if (!empty($operation[$keyParameters])) {
                    $parameters = $operation[$keyParameters];
                }

                if (!empty($operation[$keyResponses])) {
                    $responses = $operation[$keyResponses];
                }

                if (!empty($operation[$keyConsumes])) {
                    $consumes = $operation[$keyConsumes];
                }

                if (!empty($operation[$keyProduces])) {
                    $produces = $operation[$keyProduces];
                }

                $this->swaggerModel[strtolower($method) . '=' . $path] = array(
                    'path'         => $path,
                    'method'       => $method,
                    $keyParameters => $parameters,
                    $keyResponses  => $responses,
                    $keyConsumes   => $consumes,
                    $keyProduces   => $produces
                );
            }
        }

        $this->assertInternalType('array', $this->swaggerModel);
        $this->assertGreaterThan(0, count($this->swaggerModel));
    }

    public function dataProviderModel()
    {
        $result = array();

        if (empty($this->swaggerModel)) {
            return array(array(''));
        }

        if (!is_array($this->swaggerModel)) {
            return array(array(''));
        }

        foreach (array_keys($this->swaggerModel) as $key) {
            $result[$key] = array($key);
        }
    }

    public function mockContextRequest($path, $queryString, $method, $header, $bodyRaw, $postForm)
    {
        if (empty($header) || !is_array($header)) {
            $header = array();
        }

        if (!empty($bodyRaw)) {
            $bodyRaw = json_encode($bodyRaw, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        if (strlen($bodyRaw) > 0) {
            $header['Content-Type']   = 'application/json';
            $header['Content-Length'] = strlen($bodyRaw);
        }

        $_FILES = array();
        $_POST  = array();

        if (!empty($postForm) && is_array($postForm)) {
            foreach ($postForm as $key => $value) {
                if (is_array($value) && array_key_exists('tmp_name', $value)) {
                    $_FILES[$key] = $value;
                }
                else {
                    $_POST[$key] = $value;
                }
            }
        }

        $context = new \Swagger\Common\Context();
        $context->mock(array(
            'Scheme'         => $this->scheme,
            'Host'           => $this->host,
            'Method'         => $method,
            'BasePath'       => $this->basePath . $path,
            'php://input'    => $bodyRaw,
            'REQUEST_URI'    => $this->basePath . $path . $queryString,
            'SERVER_NAME'    => $this->host,
            'REQUEST_SCHEME' => $this->scheme,
            'REQUEST_METHOD' => $method,
                ) + $header);

        $context->setType(\Swagger\Common\Context::TYPE_REQUEST);

        return $context;
    }

    public function mockContextResponse($path, $method, $header, $bodyRaw)
    {
        if (empty($header) || !is_array($header)) {
            $header = array();
        }

        if (!empty($bodyRaw)) {
            $bodyRaw = json_encode($bodyRaw, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        if (strlen($bodyRaw) > 0) {
            $header['Content-Type']   = 'application/json';
            $header['Content-Length'] = strlen($bodyRaw);
        }

        $context = new \Swagger\Common\Context();
        $context->mock(array(
            'Scheme'         => $this->scheme,
            'Host'           => $this->host,
            'Method'         => $method,
            'BasePath'       => $this->basePath . $path,
            'php://output'   => $bodyRaw,
            'REQUEST_URI'    => $this->basePath . $path,
            'SERVER_NAME'    => $this->host,
            'REQUEST_SCHEME' => $this->scheme,
            'REQUEST_METHOD' => $method,
                ) + $header);

        $context->setType(\Swagger\Common\Context::TYPE_RESPONSE);

        return $context;
    }

}
