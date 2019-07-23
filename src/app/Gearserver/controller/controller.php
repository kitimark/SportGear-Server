<?php
namespace Gearserver;

class controller {
    protected $container;
    public function __construct($c) {
        $this->container = $c;
    }

    public function __get($property) {
        if($this->container->has($property)) {
            return $this->container->get($property);
        }
        return $this->{$property};
    }
    
    public function groupArray($arr, $group, $preserveGroupKey = false, $preserveSubArrays = false) {
        $temp = array();
        foreach($arr as $key => $value) {
            $groupValue = $value[$group];
            if(!$preserveGroupKey)
            {
                unset($arr[$key][$group]);
            }
            if(!array_key_exists($groupValue, $temp)) {
                $temp[$groupValue] = array();
            }
    
            if(!$preserveSubArrays){
                $data = count($arr[$key]) == 1? array_pop($arr[$key]) : $arr[$key];
            } else {
                $data = $arr[$key];
            }
            $temp[$groupValue][] = $data;
        }
        return $temp;
    }
}