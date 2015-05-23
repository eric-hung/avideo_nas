<?php
/**
 *
 * 批次對 既已完成登錄的avideo檔案, 上傳其同目錄下的 avideo_photo,
 * 以便在網頁上瀏覽時能夠一眼就看到封面圖.
 *
 * @date:
 *   2014-02-09
 *     1 微調程式架構. 注意調整後尚未經過測試驗證; 下次執行時若有發現錯誤再一併處理
 *     2 那個中文或空白字元之目錄名稱影響到圖檔上傳的問題仍尚未解決, 留待下次執行時再一併處理.
 *
 *   2014-02-08
 *     1 開發測試完成. 可順利執行並達到預期目的.
 *     2 發現會有中文目錄下的圖檔無法順利上傳的問題; 凡是無法上傳者的所屬目錄名稱皆含有中文或是空白字元.
 *
 */
include_once dirname(__FILE__).'/nas.conf';
include_once dirname(__FILE__).'/nas.inc';

$info = phase2_check($argv);
//生成 $batch_dir, $batch_path, $batch_log, $entries 四個變數
foreach($info as $k=>$v){$$k=$v;}

foreach($entries as &$entry):  
  if( false === entry_validation($entry, $batch_path) )continue;

  //上傳 avideo_photo 圖檔
  upload_avideo_photo($entry); 
endforeach;

//回寫log:
file_put_contents($batch_log, json_encode($entries));

/**
 * 上傳登錄圖檔
 */
function upload_avideo_photo(&$entry){
  global $avideo_photo_upload_api, $allowed_avideo_photo_exts;
  
  //上傳 avideo_photo:
  $dir = subtok($entry['path'], '/', 0, -1);
  $arg_str = "-iname '*.".implode("' -o -iname '*.", $allowed_avideo_photo_exts);
  $cmd = "find $dir ".$arg_str."'";
  $result = explode("\n",shell_exec($cmd));
  array_pop($result);

  if( empty($result) )continue;
  
  $entry['avideo_photo'] = $result;
  $api = $avideo_photo_upload_api.'/'.$entry['nid'];
  $args = "";
  foreach($result as $photo):  
    $args .= " -F \"lst[]=@$photo\"";
  endforeach;
  $cmd = "curl $args $api";
  echo2($cmd);
  echo2(shell_exec($cmd));
  //print_r($entry);exit;
}