<?php
function db(){static $p;if(!$p)$p=new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);return $p;}
function key_(){return hash('sha256',APP_KEY,true);}
function enc($s){$iv=random_bytes(16);return base64_encode($iv.openssl_encrypt($s,'aes-256-cbc',key_(),OPENSSL_RAW_DATA,$iv));}
function dec($s){$b=base64_decode($s);return openssl_decrypt(substr($b,16),'aes-256-cbc',key_(),OPENSSL_RAW_DATA,substr($b,0,16));}
function hpn($h){return preg_replace('/\D/','',$h);}
function hh($h){return hash_hmac('sha256',hpn($h),APP_KEY);}
