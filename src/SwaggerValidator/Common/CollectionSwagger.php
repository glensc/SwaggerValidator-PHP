<?php

/*
 * Copyright 2016 Nicolas JUHEL <swaggervalidator@nabbar.com>.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace SwaggerValidator\Common;

/**
 * Description of CollectionSwagger
 *
 * @author Nicolas JUHEL<swaggervalidator@nabbar.com>
 * @version 1.0.0
 */
abstract class CollectionSwagger extends \SwaggerValidator\Common\Collection
{

    /**
     *
     * @var array
     */
    private $mandatoryKeys = array();

    abstract public function __construct();

    /**
     * Return the content of the reference as object or mixed data
     * @param string $key
     * @return mixed
     * @throws \SwaggerValidator\Exception
     */
    public function get($key)
    {
        return $this->__get($key);
    }

    public function set($key, $value = null)
    {
        return $this->__set($key, $value);
    }

    /**
     * @param string $jsonData The Json Data to be unserialized
     */
    abstract public function jsonUnSerialize(\SwaggerValidator\Common\Context $context, $jsonData);

    /**
     * Throw a new \SwaggerValidator\Exception with automatic find method, line, ...
     * @param string $message
     * @param mixed $context
     * @throws \SwaggerValidator\Exception
     */
    protected function throwException($message, $context = null)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 11);
        array_shift($trace);

        $file = null;
        $line = null;

        for ($i = 0; $i < 10; $i++) {
            $oneTrace = array_shift($trace);

            if (!empty($oneTrace['file']) && $oneTrace['file'] != __FILE__) {
                $file = $oneTrace['file'];
                break;
            }
        }

        if (!empty($file) && !empty($oneTrace['line'])) {
            $line = $oneTrace['line'];
        }

        $e = new \SwaggerValidator\Exception();
        $e->init($message, $context, $file, $line);
        throw $e;
    }

    /**
     * List of keys mandatory for the current object type
     * @param string $key
     */
    protected function registerMandatoryKey($key)
    {
        if (in_array($key, $this->mandatoryKeys)) {
            $this->mandatoryKeys[] = $key;
        }
    }

    /**
     * Return true if all mandatory keys are defined or the missing key name
     * @return boolean|string
     */
    public function checkMandatoryKey()
    {
        foreach ($this->mandatoryKeys as $key) {
            if (!array_key_exists($key, $this)) {
                return $key;
            }
        }

        return true;
    }

    protected function getCleanClass($class)
    {
        $classPart = explode('\\', $class);
        return array_pop($classPart);
    }

    protected function extractNonRecursiveReference(\SwaggerValidator\Common\Context $context, &$jsonData)
    {
        if (!is_object($jsonData) || !property_exists($jsonData, \SwaggerValidator\Common\FactorySwagger::KEY_REFERENCE)) {
            return $jsonData;
        }

        if (count(get_object_vars($jsonData)) > 1) {
            \SwaggerValidator\Exception::throwNewException('External Object Reference cannot have more keys than the $ref key', array('context' => $context, 'JsonData' => $jsonData), __FILE__, __LINE__);
        }

        $key = \SwaggerValidator\Common\FactorySwagger::KEY_REFERENCE;
        $ref = $jsonData->$key;

        return \SwaggerValidator\Common\CollectionReference::getInstance()->get($ref)->getJson($context->setExternalRef($ref));
    }

    protected function registerRecursiveDefinitions(&$jsonData)
    {
        if (is_object($jsonData) && ($jsonData instanceof \stdClass)) {
            return $this->registerRecursiveDefinitionsFromObject($jsonData);
        }
        elseif (is_array($jsonData) && !empty($jsonData)) {
            return $this->registerRecursiveDefinitionsFromArray($jsonData);
        }
    }

    protected function registerRecursiveDefinitionsFromObject(\stdClass &$jsonData)
    {
        if (!is_object($jsonData) || !($jsonData instanceof \stdClass)) {
            return;
        }

        foreach (array_keys(get_object_vars($jsonData)) as $key) {
            if ($key === \SwaggerValidator\Common\FactorySwagger::KEY_REFERENCE) {
                \SwaggerValidator\Common\CollectionReference::registerDefinition($jsonData->$key);
            }
            elseif (is_array($jsonData->$key)) {
                return $this->registerRecursiveDefinitionsFromArray($jsonData->$key);
            }
            elseif (is_object($jsonData->$key)) {
                return $this->registerRecursiveDefinitionsFromObject($jsonData->$key);
            }
        }
    }

    protected function registerRecursiveDefinitionsFromArray(&$jsonData)
    {
        if (!is_array($jsonData) || empty($jsonData)) {
            return;
        }

        foreach (array_keys($jsonData) as $key) {
            if ($key === \SwaggerValidator\Common\FactorySwagger::KEY_REFERENCE) {
                \SwaggerValidator\Common\CollectionReference::registerDefinition($jsonData[$key]);
            }
            elseif (is_array($jsonData[$key])) {
                return $this->registerRecursiveDefinitionsFromArray($jsonData[$key]);
            }
            elseif (is_object($jsonData[$key])) {
                return $this->registerRecursiveDefinitionsFromObject($jsonData[$key]);
            }
        }
    }

    protected function getModelGeneric(\SwaggerValidator\Common\Context $context, &$generalItems, $typeKey = null)
    {
        if (!is_array($generalItems)) {
            $generalItems = array();
        }

        switch ($typeKey) {
            case \SwaggerValidator\Common\FactorySwagger::KEY_PARAMETERS:
                $key = \SwaggerValidator\Common\FactorySwagger::KEY_PARAMETERS;
                $cls = '\SwaggerValidator\Object\Parameters';
                break;

            case \SwaggerValidator\Common\FactorySwagger::KEY_RESPONSES:
                $key = \SwaggerValidator\Common\FactorySwagger::KEY_RESPONSES;
                $cls = '\SwaggerValidator\Object\Responses';
                break;

            default:
                $this->getModelGeneric($context, $generalItems, \SwaggerValidator\Common\FactorySwagger::KEY_PARAMETERS);
                $this->getModelGeneric($context, $generalItems, \SwaggerValidator\Common\FactorySwagger::KEY_RESPONSES);
                return;
        }

        if (!isset($this->$key) || !is_object($this->$key)) {
            return;
        }

        if (!array_key_exists($key, $generalItems)) {
            $generalItems[$key] = array();
        }

        if ($this->$key instanceof $cls) {
            $this->$key->getModel($context->setDataPath($key), $generalItems[$key]);
        }

        return;
    }

    protected function getModelConsumeProduce(&$generalItems)
    {
        if (!is_array($generalItems)) {
            $generalItems = array();
        }

        $list = array(
            \SwaggerValidator\Common\FactorySwagger::KEY_CONSUMES,
            \SwaggerValidator\Common\FactorySwagger::KEY_PRODUCES,
        );

        foreach ($list as $key) {

            if (!isset($this->$key) || !is_array($this->$key)) {
                continue;
            }

            if (!array_key_exists($key, $generalItems)) {
                $generalItems[$key] = array();
            }

            $generalItems[$key] = $this->$key;
        }

        return;
    }

    /**
     * Check that entry JsonData is an object of stdClass
     * @param \stdClass $jsonData
     * @return boolean
     */
    protected function checkJsonObject(\SwaggerValidator\Common\Context $context, &$jsonData)
    {
        if (!is_object($jsonData)) {
            $this->throwException('Mismatching type of JSON Data received', $context);
        }

        if (!($jsonData instanceof \stdClass)) {
            $this->throwException('Mismatching type of JSON Data received', $context);
        }

        return true;
    }

    /**
     * Check that entry JsonData is an object of stdClass or an array
     * @param \stdClass $jsonData
     * @return boolean
     */
    protected function checkJsonObjectOrArray(\SwaggerValidator\Common\Context $context, &$jsonData)
    {
        if (is_object($jsonData) && !($jsonData instanceof \stdClass)) {
            $this->throwException('Mismatching type of JSON Data received', $context);
        }
        elseif (!is_object($jsonData) && !is_array($jsonData)) {
            $this->throwException('Mismatching type of JSON Data received', $context);
        }

        if (is_array($jsonData)) {
            parent::setJSONIsArray();
        }

        return true;
    }

}
