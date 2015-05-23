<?php
/**
 * 檢視log檔
 */
include_once dirname(__FILE__).'/nas.conf';
include_once dirname(__FILE__).'/nas.inc';

$info = phase2_check($argv);
//生成 $batch_dir, $batch_path, $batch_log, $entries 四個變數
foreach($info as $k=>$v){$$k=$v;}

print_r($entries);