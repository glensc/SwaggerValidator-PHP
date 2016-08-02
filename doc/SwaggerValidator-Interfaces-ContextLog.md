SwaggerValidator\Interfaces\ContextLog
===============






* Interface name: ContextLog
* Namespace: SwaggerValidator\Interfaces
* This is an **interface**






Methods
-------


### logLoadFile

    mixed SwaggerValidator\Interfaces\ContextLog::logLoadFile($file, $method, $line)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $file **mixed**
* $method **mixed**
* $line **mixed**



### logLoadRef

    mixed SwaggerValidator\Interfaces\ContextLog::logLoadRef($ref, $method, $line)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $ref **mixed**
* $method **mixed**
* $line **mixed**



### logReplaceRef

    mixed SwaggerValidator\Interfaces\ContextLog::logReplaceRef($oldRef, $newRef, $method, $line)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $oldRef **mixed**
* $newRef **mixed**
* $method **mixed**
* $line **mixed**



### logDecode

    mixed SwaggerValidator\Interfaces\ContextLog::logDecode($decodePath, $decodeType, $method, $line)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $decodePath **mixed**
* $decodeType **mixed**
* $method **mixed**
* $line **mixed**



### logValidate

    mixed SwaggerValidator\Interfaces\ContextLog::logValidate($path, $type, $method, $line)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $path **mixed**
* $type **mixed**
* $method **mixed**
* $line **mixed**



### logModel

    mixed SwaggerValidator\Interfaces\ContextLog::logModel($path, $method, $line)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $path **mixed**
* $method **mixed**
* $line **mixed**



### logDebug

    mixed SwaggerValidator\Interfaces\ContextLog::logDebug($message, $method, $line)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $message **mixed**
* $method **mixed**
* $line **mixed**



### logValidationError

    mixed SwaggerValidator\Interfaces\ContextLog::logValidationError($validationType, $method, $line)





* Visibility: **public**


#### Arguments
* $validationType **mixed**
* $method **mixed**
* $line **mixed**

