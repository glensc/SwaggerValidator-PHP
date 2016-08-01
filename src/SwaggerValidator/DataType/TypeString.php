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

namespace SwaggerValidator\DataType;

/**
 * Description of string
 *
 * @author Nicolas JUHEL<swaggervalidator@nabbar.com>
 * @version 1.0.0
 */
class TypeString extends \SwaggerValidator\DataType\TypeCommon
{

    const PATTERN_BYTE     = '^(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?$';
    const PATTERN_DATE     = '^\d{4}-\d{2}-\d{2}$';
    const PATTERN_DATETIME = '\d{4}-\d{2}-\d{2}[tT]\d{2}:\d{2}:\d{2}(\.\d)?(z|Z|[+-]\d{2}:\d{2})';
    const PATTERN_URI      = '^http[s]?:\/\/(?:[\w\-._~!$&\'()*+,;=]+|(%[0-9A-Fa-f]{2})+)(\/((?:[\w\-._~!$&\'()*+,;=:@]|%[0-9A-Fa-f]{2})+\/?)*)?(\?(?:[\w\-._~!$&\'()*+,;=:@\/\\]|%[0-9A-Fa-f]{2})*)?(#(?:[\w\-._~!$&\'()*+,;=:@\/\\]|%[0-9A-Fa-f]{2})*)?';
    const PATTERN_IPV4     = '^([01]?[0-9][0-9]?|2[0-4][0-9]|25[0-5])(\.([01]?[0-9][0-9]?|2[0-4][0-9]|25[0-5])){3}$';
    const PATTERN_IPV6     = '(^\d{20}$)|(^((:[a-fA-F0-9]{1,4}){6}|::)ffff:(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[0-9]{1,2})(\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[0-9]{1,2})){3}$)|(^((:[a-fA-F0-9]{1,4}){6}|::)ffff(:[a-fA-F0-9]{1,4}){2}$)|(^([a-fA-F0-9]{1,4}) (:[a-fA-F0-9]{1,4}){7}$)|(^:(:[a-fA-F0-9]{1,4}(::)?){1,6}$)|(^((::)?[a-fA-F0-9]{1,4}:){1,6}:$)|(^::$)|(^::1$)';

    public function __construct()
    {
        parent::registerMandatoryKey('type');
    }

    public function validate(\SwaggerValidator\Common\Context $context)
    {
        if (!$this->__isset('type')) {
            return $context->setValidationError(\SwaggerValidator\Common\Context::VALIDATION_TYPE_SWAGGER_ERROR, null, __METHOD__, __LINE__);
        }

        if ($this->type != \SwaggerValidator\Common\FactorySwagger::TYPE_STRING) {
            return $context->setValidationError(\SwaggerValidator\Common\Context::VALIDATION_TYPE_SWAGGER_ERROR, null, __METHOD__, __LINE__);
        }

        if (!$this->type($context, $context->getDataValue())) {
            return $context->setDataCheck('type')->setValidationError(\SwaggerValidator\Common\Context::VALIDATION_TYPE_DATATYPE, $context->getDataPath() . ' is not a valid string !!', __METHOD__, __LINE__);
        }

        if (!$this->pattern($context, $context->getDataValue())) {
            return $context->setDataCheck('pattern')->setValidationError(\SwaggerValidator\Common\Context::VALIDATION_TYPE_PATTERN, null, __METHOD__, __LINE__);
        }

        if (!$this->format($context, $context->getDataValue())) {
            return false;
        }

        if (!$this->enum($context, $context->getDataValue())) {
            return $context->setDataCheck('enum')->setValidationError(\SwaggerValidator\Common\Context::VALIDATION_TYPE_DATAVALUE, null, __METHOD__, __LINE__);
        }

        // completer les test integer
        \SwaggerValidator\Common\Context::logValidate($context->getDataPath(), get_class($this), __METHOD__, __LINE__);
        return true;
    }

    protected function type(\SwaggerValidator\Common\Context $context, $valueParams)
    {
        if (is_string($valueParams)) {
            return true;
        }

        return false;
    }

    protected function format(\SwaggerValidator\Common\Context $context, $valueParams)
    {
        if (!$this->__isset('format')) {
            return true;
        }

        if ($this->format == 'byte' && preg_match('#' . self::PATTERN_BYTE . '#', $valueParams)) {
            /**
             * @see RFC 4648 : http://www.ietf.org/rfc/rfc4648.txt
             */
            return true;
        }

        if ($this->format == 'binary') {
            /**
             * @todo get an example or regex for validation format
             */
            return true;
        }

        if ($this->ormat == 'date' && preg_match('#' . self::PATTERN_DATE . '#', $valueParams)) {
            /**
             * @see RFC 3339 : http://www.ietf.org/rfc/rfc3339.txt
             */
            return true;
        }

        if ($this->format == 'date-time' && preg_match('#' . self::PATTERN_DATETIME . '#', $valueParams)) {
            /**
             * @see RFC 3339 : http://www.ietf.org/rfc/rfc3339.txt
             */
            return true;
        }

        if ($this->format == 'password') {
            /**
             * Format specified only to obfucate input field
             */
            return true;
        }

        if ($this->format == 'uri' && preg_match('/' . self::PATTERN_URI . '/', $valueParams)) {
            /**
             * Format specified only to obfucate input field
             */
            return true;
        }

        if ($this->format == 'ipv4' && preg_match('/' . self::PATTERN_IPV4 . '/', $valueParams)) {
            /**
             * Format specified only to obfucate input field
             */
            return true;
        }

        if ($this->format == 'ipv6' && preg_match('/' . self::PATTERN_IPV6 . '/', $valueParams)) {
            /**
             * Format specified only to obfucate input field
             */
            return true;
        }

        if ($this->format == 'string' && $this->type == 'string') {
            /**
             * default format for string... but if type is not string then error on swagger
             */
            return true;
        }

        if (empty($this->format)) {
            return true;
        }

        return $context->setValidationError(\SwaggerValidator\Common\Context::VALIDATION_TYPE_DATATYPE, 'The format does not match with registred patterns', __METHOD__, __LINE__);
    }

    protected function getExampleFormat(\SwaggerValidator\Common\Context $context)
    {
        if ($this->format == 'byte') {
            /**
             * @see RFC 4648 : http://www.ietf.org/rfc/rfc4648.txt
             */
            \SwaggerValidator\Common\Context::logModel($context->getDataPath(), __METHOD__, __LINE__);
            return base64_encode('ceci est un test 1234567890');
        }

        if ($this->format == 'binary') {
            /**
             * @todo get an example or regex for validation format
             */
            \SwaggerValidator\Common\Context::logModel($context->getDataPath(), __METHOD__, __LINE__);
            return 0xa125d1f15b51;
        }

        if ($this->ormat == 'date') {
            /**
             * @see RFC 3339 : http://www.ietf.org/rfc/rfc3339.txt
             */
            \SwaggerValidator\Common\Context::logModel($context->getDataPath(), __METHOD__, __LINE__);
            return date('Y-m-d');
        }

        if ($this->format == 'date-time') {
            /**
             * @see RFC 3339 : http://www.ietf.org/rfc/rfc3339.txt
             */
            \SwaggerValidator\Common\Context::logModel($context->getDataPath(), __METHOD__, __LINE__);
            return date('c');
        }

        if ($this->format == 'password') {
            /**
             * Format specified only to obfucate input field
             */
            \SwaggerValidator\Common\Context::logModel($context->getDataPath(), __METHOD__, __LINE__);
            return 'pwdExample1';
        }

        if ($this->format == 'uri') {
            /**
             * Format specified only to obfucate input field
             */
            \SwaggerValidator\Common\Context::logModel($context->getDataPath(), __METHOD__, __LINE__);
            return 'http://localhost/path/script.php?query#fragment';
        }

        if ($this->format == 'ipv4') {
            /**
             * Format specified only to obfucate input field
             */
            \SwaggerValidator\Common\Context::logModel($context->getDataPath(), __METHOD__, __LINE__);
            return '127.0.0.1';
        }

        if ($this->format == 'ipv6') {
            /**
             * Format specified only to obfucate input field
             */
            \SwaggerValidator\Common\Context::logModel($context->getDataPath(), __METHOD__, __LINE__);
            return '::1';
        }

        if ($this->format == 'string' && $this->type == 'string') {
            /**
             * default format for string... but if type is not string then error on swagger
             */
            \SwaggerValidator\Common\Context::logModel($context->getDataPath(), __METHOD__, __LINE__);
            return 'This is an example of string type and format string';
        }

        return $this->getExampleType($context);
    }

    protected function getExampleType(\SwaggerValidator\Common\Context $context)
    {
        \SwaggerValidator\Common\Context::logModel($context->getDataPath(), __METHOD__, __LINE__);
        return 'This is a basic example of string type';
    }

}
