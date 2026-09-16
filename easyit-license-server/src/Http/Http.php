<?php
declare(strict_types=1);
namespace EasyIT\LicenseServer\Http;
final class Http
{
 public static function headers():array{ $h=[];foreach($_SERVER as $k=>$v){if(str_starts_with($k,'HTTP_')){$n=strtolower(str_replace('_','-',substr($k,5)));$h[$n]=(string)$v;}}return $h; }
 public static function body():string{return (string)file_get_contents('php://input');}
 public static function path():string{return (string)(parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH)?:'/');}
 public static function method():string{return strtoupper((string)($_SERVER['REQUEST_METHOD']??'GET'));}
}
