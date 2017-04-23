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
 * Description of ReferenceFile
 *
 * @author Nicolas JUHEL<swaggervalidator@nabbar.com>
 * @version 1.0.0
 */
class ReferenceFile
{

    const PATH_TYPE_URL  = 1;
    const PATH_TYPE_FILE = 2;

    private $fileUri;
    private $fileObj;
    private $fileTime;
    private $fileHash;
    private $basePath;
    private $baseType;

    public function __construct(\SwaggerValidator\Common\Context $context, $filepath)
    {
        $this->fileUri = $filepath;

        $urlPart = parse_url($filepath);

        if (empty($urlPart['scheme']) || empty($urlPart['host']) || strtolower($urlPart['scheme']) == 'file' || file_exists($filepath)) {
            $this->baseType = self::PATH_TYPE_FILE;
            $this->basePath = realpath(dirname($filepath));

            if (substr($this->basePath, -1, 1) != DIRECTORY_SEPARATOR) {
                $this->basePath .= DIRECTORY_SEPARATOR;
            }
        }
        elseif ($urlPart !== false) {
            $this->baseType  = self::PATH_TYPE_URL;
            $urlPart['path'] = dirname($urlPart['path']);

            $this->basePath = $urlPart['scheme'] . '://';

            if (!empty($urlPart['user']) || !empty($urlPart['pass'])) {
                $this->basePath .= $urlPart['user'] . ':' . $urlPart['pass'] . '@' . $urlPart['host'];
            }
            else {
                $this->basePath .= $urlPart['host'];
            }

            if (!empty($urlPart['port'])) {
                $this->basePath .= ':' . $urlPart['port'];
            }

            $this->basePath .= $urlPart['path'];
        }
        else {
            $context->throwException('Pathtype not well formatted : ' . $filepath, null, __FILE__, __LINE__);
        }

        $contents = file_get_contents($this->fileUri);

        if (empty($contents)) {
            $context->throwException('Cannot read contents for file : ' . $filepath, null, __FILE__, __LINE__);
        }

        $this->fileTime = $this->getFileTime($context);
        $this->fileHash = hash('SHA512', $contents . '#' . $this->fileTime, true);
        $this->fileObj  = json_decode($contents, false);

        if (empty($this->fileObj)) {
            $context->throwException('Cannot decode contents for file : ' . $filepath, null, __FILE__, __LINE__);
        }

        $context->logLoadFile($this->fileUri, __METHOD__, __LINE__);
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        $ctx = new \SwaggerValidator\Common\Context();
        return $this->getReference($ctx, $name);
    }

    /**
     * Var Export Method
     */
    protected function __storeData($key, $value = null)
    {
        if (property_exists($this, $key)) {
            $this->$key = $value;
        }
    }

    public static function __set_state(array $properties)
    {
        $obj = new static;

        foreach ($properties as $key => $value) {
            $obj->__storeData($key, $value);
        }

        return $obj;
    }

    public function getFileTime(\SwaggerValidator\Common\Context $context)
    {
        if ($this->baseType !== self::PATH_TYPE_URL) {
            return filemtime($this->fileUri);
        }

        $curl = curl_init($this->fileUri);

        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FILETIME, true);

        $result = curl_exec($curl);

        if ($result === false) {
            $context->throwException('CURL Error : ' . curl_errno($curl) . ' => ' . curl_error($curl), curl_getinfo($curl), __METHOD__, __LINE__);
        }

        $timestamp = curl_getinfo($curl, CURLINFO_FILETIME);

        curl_close($curl);

        if ($timestamp != -1) {
            return $timestamp;
        }

        return time();
    }

    public function getReference(\SwaggerValidator\Common\Context $context, $ref)
    {
        $obj            = $this->fileObj;
        $propertiesList = explode('/', $ref);
        array_shift($propertiesList);

        foreach ($propertiesList as $property) {

            if (empty($property)) {
                continue;
            }

            if (empty($obj)) {
                $context->throwException('Cannot find property "' . $property . '" from ref : ' . $this->fileUri . '#/' . $ref, null, __FILE__, __LINE__);
            }

            if (is_object($obj) && isset($obj->$property)) {
                $obj = $obj->$property;
            }
            elseif (is_array($obj) && isset($obj[$property])) {
                $obj = $obj[$property];
            }
            else {
                $context->throwException('Cannot find property "' . $property . '" from ref : ' . $this->fileUri . '#/' . $ref, null, __FILE__, __LINE__);
            }
        }

        return $obj;
    }

    public function extractAllReference(\SwaggerValidator\Common\Context $context)
    {
        $refList = array();

        if (is_object($this->fileObj)) {
            $refList = $this->extractReferenceObject($context, $this->fileObj);
        }
        elseif (is_array($this->fileObj)) {
            $refList = $this->extractReferenceArray($context, $this->fileObj);
        }

        if (is_object($this->fileObj) && property_exists($this->fileObj, \SwaggerValidator\Common\FactorySwagger::KEY_DEFINITIONS)) {

            $keyDef = \SwaggerValidator\Common\FactorySwagger::KEY_DEFINITIONS;

            foreach (array_keys(get_object_vars($this->fileObj->$keyDef)) as $key) {
                $ref = $this->fileUri . '#/' . $keyDef . '/' . $key;
                $id  = \SwaggerValidator\Common\CollectionReference::getIdFromRef($context, $ref);
                \SwaggerValidator\Common\CollectionReference::registerDefinition($context, $ref);
                $context->logReference('replace', $id, $key, __METHOD__, __LINE__);
            }
        }

        return array_unique($refList);
    }

    private function extractReferenceArray(\SwaggerValidator\Common\Context $context, array &$array)
    {
        $refList = array();

        foreach ($array as $key => $value) {
            if ($key === \SwaggerValidator\Common\FactorySwagger::KEY_REFERENCE) {
                $ref       = $this->getCanonical($context, $value);
                $refList[] = $ref;

                $context->logReference('replace', $ref[0], $value, __METHOD__, __LINE__);

                $value = $ref[0];
            }
            elseif (is_array($value)) {
                $refList = $refList + $this->extractReferenceArray($context, $value);
            }
            elseif (is_object($value)) {
                $refList = $refList + $this->extractReferenceObject($context, $value);
            }
            else {
                continue;
            }

            $array[$key] = $value;
        }

        return $refList;
    }

    private function extractReferenceObject(\SwaggerValidator\Common\Context $context, \stdClass &$stdClass)
    {
        $refList = array();

        foreach (get_object_vars($stdClass) as $key => $value) {
            if ($key === \SwaggerValidator\Common\FactorySwagger::KEY_REFERENCE) {
                $ref       = $this->getCanonical($context, $value);
                $refList[] = $ref;

                $context->logReference('replace', $ref[0], $value, __METHOD__, __LINE__);

                $value = $ref[0];
            }
            elseif (is_array($value)) {
                $refList = $refList + $this->extractReferenceArray($context, $value);
            }
            elseif (is_object($value)) {
                $refList = $refList + $this->extractReferenceObject($context, $value);
            }
            else {
                continue;
            }

            $stdClass->$key = $value;
        }

        return $refList;
    }

    public function getCanonical(\SwaggerValidator\Common\Context $context, $fullRef)
    {
        $fileLink = \SwaggerValidator\Common\CollectionFile::getReferenceFileLink($fullRef);
        $innerRef = \SwaggerValidator\Common\CollectionFile::getReferenceInnerPath($fullRef);

        if (!empty($fileLink)) {
            $fileLink = $this->getFileLink($context, $fileLink);
        }
        else {
            $fileLink = $this->fileUri;
        }

        if (!empty($innerRef)) {
            $innerRef = str_replace('//', '/', $innerRef);
        }
        else {
            $innerRef = '/';
        }

        return array(
            $fileLink . '#' . $innerRef,
            $fileLink,
            $innerRef
        );
    }

    private function getFileLink(\SwaggerValidator\Common\Context $context, $uri)
    {
        $scheme = parse_url($uri, PHP_URL_SCHEME);

        if (strtolower($scheme) == 'file') {
            return $this->getFilePath($context, urldecode(substr($uri, 7)));
        }

        if ($this->baseType !== self::PATH_TYPE_URL) {

            if ($scheme === false || $scheme === null) {
                return $this->getFilePath($context, $uri);
            }
            else {
                return $this->getUrlLink($this->basePath . $uri);
            }
        }
        else {
            return $this->getUrlLink($this->basePath . $uri);
        }
    }

    private function getFilePath(\SwaggerValidator\Common\Context $context, $filepath)
    {
        $filepath = str_replace('/', DIRECTORY_SEPARATOR, $filepath);

        if (substr($filepath, 0, 1) == DIRECTORY_SEPARATOR) {
            $filepath = substr($filepath, 1);
        }

        if (file_exists($this->basePath . $filepath)) {
            return realpath($this->basePath . $filepath);
        }
        else {
            $context->throwException('Cannot load file from ref : ' . $filepath, null, __FILE__, __LINE__);
        }

        return false;
    }

    private function getUrlLink($url)
    {
        $address = explode('/', $this->basePath . $url);
        $keys    = array_keys($address, '..');

        foreach ($keys AS $keypos => $key) {
            array_splice($address, $key - ($keypos * 2 + 1), 2);
        }

        return str_replace('./', '', implode('/', $address));
    }

}
