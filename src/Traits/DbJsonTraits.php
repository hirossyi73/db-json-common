<?php

namespace Hirossyi73\DbJsonCommon\Traits;
use \Closure;

trait DbJsonTraits
{
    private $dbJsonfuncs = [];
    
    public function __construct(){
        if (property_exists($this, 'dbJson') && !is_null($this->dbJson)) {
            $funcName = null;
            if (is_string($this->dbJson)) {
                $this->dbJson = [$this->dbJson];
            }
            $funcPrefix = ['get', 'set', 'forget', 'clear'];
            foreach ($this->dbJson as $key => $json) {
                if (is_numeric($key)) {
                    $funcKey = studly_case($json);
                } else {
                    $funcKey = $key;
                }

                foreach ($funcPrefix as $prefix) {
                    $this->dbJsonfuncs[$prefix.$funcKey] = [
                        'method' => $prefix.'Json',
                        'json' => $json,
                    ];
                }
            }
        }
    }

    public function __call($method, $parameters)
    {
        if(array_key_exists($method, $this->dbJsonfuncs)){
            $callMethod = $this->dbJsonfuncs[$method]['method'];
            if(method_exists($this, $callMethod)){
                $funcparams = array_merge([$this->dbJsonfuncs[$method]['json']], $parameters);
                return $this->{$callMethod}(...$funcparams);
            }
        }
        return parent::__call($method, $parameters);
    }

    /**
     * get value from json
     */
    protected function getJson(...$parameters)
    {
        list($dbcolumnname, $key, $default) = $parameters + [null, null, null];
        $json = $this->{$dbcolumnname};
        if(!isset($json)){return $default;}
        return array_get($json, $key, $default);
    }

    /**
     * set value from json
     * 
     */
    protected function setJson(...$parameters){
        list($dbcolumnname, $key, $val, $forgetIfNull) = $parameters + [null, null, null, null];
        if (!isset($dbcolumnname) && !isset($key)) {
            return $this;
        }
        // if key is array, loop key value
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->setJson($dbcolumnname, $k, $v);
            }
            return $this;
        }

        // if $val is null and $forgetIfNull is true, forget value
        if($forgetIfNull && is_null($val)){
            return $this->forgetJson($dbcolumnname, $key);
        }

        $value = $this->{$dbcolumnname};
        if (is_null($value)) {
            $value = [];
        }
        $value[$key] = $val;
        $this->{$dbcolumnname} = $value;

        return $this;
    }
    
    /**
     * forget value from json
     * 
     */
    protected function forgetJson(...$parameters){
        list($dbcolumnname, $key) = $parameters + [null, null];
        if (!isset($dbcolumnname) && !isset($key)) {
            return $this;
        }
        
        $value = $this->{$dbcolumnname};
        if (is_null($value)) {
            $value = [];
        }
        array_forget($value, $key);
        $this->{$dbcolumnname} = $value;

        return $this;
    }
    /**
     * clear value from json
     * 
     */
    protected function clearJson(...$parameters){
        list($dbcolumnname, $isNull) = $parameters + [null, true];
        if (!isset($dbcolumnname)) {
            return $this;
        }
        $this->{$dbcolumnname} = $isNull ? null : [];
        return $this;
    }
}
