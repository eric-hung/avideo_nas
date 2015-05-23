<?php
/**
 *
 * 批次登錄 avideo檔案
 * 
 *
 * @date:
 *   2015-05-16
 *     1 改寫取得檔案清單檔的邏輯; 使之可同時適用於 linux 及 win7 系統
 *
 *   2014-02-09
 *     1 調整程式架構. 尚未經過測試驗證; 留待下次執行時驗證.
 *
 *   2014-02-06
 *     1 開發測試完成. 執行結果符合預期.
 *
 */
include_once dirname(__FILE__).'/nas.conf';
include_once dirname(__FILE__).'/nas.inc';

$info = phase1_check($argv);
//生成 $batch_dir, $batch_path 兩個變數
foreach($info as $k=>$v){$$k=$v;}

// 若 vm 已產生 $lst_file 但無法連上網, 則將 true 改為 false
$lst_file = (true) ? gen_avideo_list_file($batch_path) : $batch_path . '.lst';

if(!is_file($lst_file))die("$lst_file created failed!\n");

$cmd = "curl -F \"list_file=@$lst_file\" $avideo_reg_api";
echo $cmd;

$log_file = str_replace('.lst', '.log', $lst_file);
file_put_contents($log_file, shell_exec($cmd));

/**
 * 產生清單檔
 */
function gen_avideo_list_file($batch_path){
  global $allowed_avideo_exts;
  
  $batch_dir = basename($batch_path);
  $lst_file = $batch_path.'.lst';
  
  $ccc = get_entry_list($batch_path, $batch_dir, $allowed_avideo_exts);

  $entries = array();
  foreach($ccc as $c):
    $abs_fpath = dirname($batch_path) . '/' . $c;
    $duration = get_duration($abs_fpath);
    $entries[] = array(
      'loc' => $c,
      'title' => subtok(subtok($c, "/", -1), ".", 0, -1),
      'nid' =>0,
      'duration' => $duration,
      'size' => number_format(filesize($abs_fpath)),
      'ext' => subtok($c, ".", -1),
    );
  endforeach;
  
  file_put_contents($lst_file, json_encode($entries));

  //chdir($cwd);
  
  return $lst_file;
}

/**
 * 取得 avideo檔案 之 相對路徑之標準格式
 */  
function get_entry_list($batch_path, $batch_dir, $allowed_avideo_exts) {
  $ccc = array();
  $sub_dirs = glob($batch_path.'/*');
  foreach($sub_dirs as $sub_dir):
    $items = glob($sub_dir.'/*');
    foreach($items as $item):
      if (in_array(subtok($item, '.', -1), $allowed_avideo_exts))
        $ccc[] = str_replace($batch_path, $batch_dir, $item);
    endforeach;
  endforeach;
  return $ccc;
}  
