<?php
require_once('../axenia/core/util.php');
require_once('../axenia/locale/Lang.php');

//test2
//print_r(isInEnum("343434,434234,1", 434234), false);
//print_r(isInEnum("343434,434234,1", "434234"), false);
//print_r(false === isInEnum("343434,434234,1", "4342324"));

//if(Util::isInEnum("343434,434234,1", "434234")){
//    print_r(true);
//}

print_r(Util::insert(':name is :age years old.', array('name' => 'Bob', 'age' => '65')));

print_r("\r\n\r\n-------Test for posInEnum\r\n");

var_dump(Util::posInEnum("q,w,e", 'q') == 0);
var_dump(Util::posInEnum("q, w,e", 'w') == -1);
var_dump(Util::posInEnum("q,w,e", 'k') == -1);
var_dump(Util::posInEnum("q,w,e", 'e') == 2);

print_r("\r\n-------Test for search array\r\n");

var_dump(array_search('🇬🇧 English', Lang::defaultLang()) == 'en');
var_dump(array_search('🇬🇧 English2', Lang::defaultLang()) === false);