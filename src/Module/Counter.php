<?php

namespace Bleech\Module;

class Counter {
  static $currentId = 0;
  static function next(){
    return self::$currentId++;
  }
}
