<?php
/**
 * sample to test
 *
 * http://localhost/samples/index.php/zone/default/entry/a/b/c
 * http://localhost/samples/index.php/zone-default-entry-a-b-c.html
 *
 */
require_once("../SlightPHP.php");
$slight=new SlightPHP;
$slight->_debug=true;
$slight->splitFlag="-_";
$slight->appDir=".";
$slight->defaultZone = "zone";
$slight->pluginsDir="../plugins";
$slight->loadPlugin("SCache");

/**
 * File Cache Samples
 */
//CacheEngine = array("File","APC","MemCache");
echo phpversion();
var_dump($result = SCache::getCacheEngine($cacheengine="File"));
var_dump($result = SCache::getCacheEngine($cacheengine="apc"));
var_dump($result = SCache::getCacheEngine($cacheengine="memcache"));
//�����ļ����λ��
/*$cache->setCacheFileDir($dir=);
//����Ŀ¼���
$cache->setCacheFileDepth($depth=3);
//����$timestampʱ�����룬-1�����ã�Ĭ����һ��
$cache->set($key,$value,$timestamp=);
$cache->get($key);
$cache->del($key);

//��������ҳ��
$cache->pageCache($timestamp);

*/
/**
 * APC Cache Samples
 */
//CacheEngine = array("File","APC","MemCached");
/*
$result = $cache->setCacheEngine($cacheengine="APC");
//�����ļ����λ��
$cache->setCacheFileDir($dir=);
//����Ŀ¼���
$cache->setCacheFileDepth($depth=3);
//����$timestampʱ�����룬-1�����ã�Ĭ����һ��
$cache->set($key,$value,$timestamp=);
$cache->get($key);
$cache->del($key);
*/
?>
