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

namespace SwaggerValidator\Object;

/**
 * Description of HeaderItem
 *
 * @author Nicolas JUHEL<swaggervalidator@nabbar.com>
 * @version 1.0.0
 */
class HeaderItem extends \SwaggerValidator\Common\CollectionSwagger
{

    const KEY_ITEM = 'item';

    public function __construct()
    {

    }

    /**
     * Var Export Method
     */
    protected function __storeData($key, $value = null)
    {
        if (property_exists($this, $key)) {
            $this->$key = $value;
        }
        else {
            parent::__storeData($key, $value);
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

    public function jsonUnSerialize(\SwaggerValidator\Common\Context $context, $jsonData)
    {
        $this->checkJsonObject($context, $jsonData);
        $keyItem = self::KEY_ITEM;

        $header = $this->extractNonRecursiveReference($context, $jsonData);

        $this->set(
                $keyItem, \SwaggerValidator\Common\FactorySwagger::getInstance()->jsonUnSerialize(
                        $context->setDataPath('header'), $this->getCleanClass(__CLASS__), $this->name, $header
                )
        );

        $context->logDecode(get_class($this), __METHOD__, __LINE__);
    }

    public function jsonSerialize()
    {
        return json_decode(parent::jsonEncode($this->item));
    }

    public function validate(\SwaggerValidator\Common\Context $context)
    {
        if ($this->__isset(self::KEY_ITEM)) {

            $context->setLocation(\SwaggerValidator\Common\FactorySwagger::LOCATION_HEADER);
            $context->dataLoad();

            return $this->get(self::KEY_ITEM)->validate($context);
        }

        $context->throwException('Cannot find a well formed item in the headeritem object', __METHOD__, __LINE__);
    }

    public function getModel(\SwaggerValidator\Common\Context $context)
    {
        if ($this->__isset(self::KEY_ITEM)) {

            $context->setLocation(\SwaggerValidator\Common\FactorySwagger::LOCATION_HEADER);

            return $this->get(self::KEY_ITEM)->getModel($context);
        }

        $context->throwException('Cannot find a well formed item in the headeritem object', __METHOD__, __LINE__);
    }

}
