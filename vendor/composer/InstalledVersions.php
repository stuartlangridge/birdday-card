<?php

namespace Composer;

use Composer\Semver\VersionParser;






class InstalledVersions
{
private static $installed = array (
  'root' => 
  array (
    'pretty_version' => 'dev-main',
    'version' => 'dev-main',
    'aliases' => 
    array (
    ),
    'reference' => 'eb3b4f34f7627e9dbab36a69f2f23650df2244ca',
    'name' => '__root__',
  ),
  'versions' => 
  array (
    '51degrees/fiftyone.geolocation' => 
    array (
      'pretty_version' => '4.1.1+2',
      'version' => '4.1.1.0',
      'aliases' => 
      array (
      ),
      'reference' => 'bac07acde60f3879bff14f245bb7b3bf220cfc2c',
    ),
    '51degrees/fiftyone.pipeline.cloudrequestengine' => 
    array (
      'pretty_version' => '4.1.1+3',
      'version' => '4.1.1.0',
      'aliases' => 
      array (
      ),
      'reference' => 'e8314b099799f7e4ea6d760f552f470856d221db',
    ),
    '51degrees/fiftyone.pipeline.core' => 
    array (
      'pretty_version' => '4.1.1+4',
      'version' => '4.1.1.0',
      'aliases' => 
      array (
      ),
      'reference' => '4635f769679bec799c6f6956ea881ea0b075a8bd',
    ),
    '51degrees/fiftyone.pipeline.engines' => 
    array (
      'pretty_version' => '4.1.0',
      'version' => '4.1.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '039cb02b87d1e3b8959e5e4b191469254a672577',
    ),
    '__root__' => 
    array (
      'pretty_version' => 'dev-main',
      'version' => 'dev-main',
      'aliases' => 
      array (
      ),
      'reference' => 'eb3b4f34f7627e9dbab36a69f2f23650df2244ca',
    ),
    'gkralik/php-uglifyjs' => 
    array (
      'pretty_version' => '1.0.2',
      'version' => '1.0.2.0',
      'aliases' => 
      array (
      ),
      'reference' => '0c63301bf131c6c0e48ef26cf40e65bf20936932',
    ),
    'mustache/mustache' => 
    array (
      'pretty_version' => 'v2.13.0',
      'version' => '2.13.0.0',
      'aliases' => 
      array (
      ),
      'reference' => 'e95c5a008c23d3151d59ea72484d4f72049ab7f4',
    ),
    'natxet/cssmin' => 
    array (
      'pretty_version' => 'v3.0.6',
      'version' => '3.0.6.0',
      'aliases' => 
      array (
      ),
      'reference' => 'd5d9f4c3e5cedb1ae96a95a21731f8790e38f1dd',
    ),
    'nodejs-php-fallback/nodejs-php-fallback' => 
    array (
      'pretty_version' => '1.5.2',
      'version' => '1.5.2.0',
      'aliases' => 
      array (
      ),
      'reference' => '0acdf44b057868e4a6bc4e28551dbf91e5ce13fe',
    ),
    'nodejs-php-fallback/uglify' => 
    array (
      'pretty_version' => '1.0.4',
      'version' => '1.0.4.0',
      'aliases' => 
      array (
      ),
      'reference' => 'b3572042b6710173f8a3378b8b5c76f8ecdc74a8',
    ),
  ),
);







public static function getInstalledPackages()
{
return array_keys(self::$installed['versions']);
}









public static function isInstalled($packageName)
{
return isset(self::$installed['versions'][$packageName]);
}














public static function satisfies(VersionParser $parser, $packageName, $constraint)
{
$constraint = $parser->parseConstraints($constraint);
$provided = $parser->parseConstraints(self::getVersionRanges($packageName));

return $provided->matches($constraint);
}










public static function getVersionRanges($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

$ranges = array();
if (isset(self::$installed['versions'][$packageName]['pretty_version'])) {
$ranges[] = self::$installed['versions'][$packageName]['pretty_version'];
}
if (array_key_exists('aliases', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['aliases']);
}
if (array_key_exists('replaced', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['replaced']);
}
if (array_key_exists('provided', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['provided']);
}

return implode(' || ', $ranges);
}





public static function getVersion($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['version'])) {
return null;
}

return self::$installed['versions'][$packageName]['version'];
}





public static function getPrettyVersion($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['pretty_version'])) {
return null;
}

return self::$installed['versions'][$packageName]['pretty_version'];
}





public static function getReference($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['reference'])) {
return null;
}

return self::$installed['versions'][$packageName]['reference'];
}





public static function getRootPackage()
{
return self::$installed['root'];
}







public static function getRawData()
{
return self::$installed;
}



















public static function reload($data)
{
self::$installed = $data;
}
}
