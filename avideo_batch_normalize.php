<?php
/**
 *
 * 將批次登錄目錄下的 avideo檔案 依照 [avideo檔案管理辦法] 正規化
 * 
 *
 * @date:
 *   2015-05-16
 *     1 初版
 *
 */
include_once dirname(__FILE__).'/nas.conf';
include_once dirname(__FILE__).'/nas.inc';

$info = phase1_check($argv);
//生成 $batch_dir, $batch_path 兩個變數
foreach($info as $k=>$v){$$k=$v;}

batch_dir_normalize($batch_path, $batch_dir, $allowed_avideo_exts);

/**
 * 將檔案目錄結構正規化
 */  
function batch_dir_normalize($batch_path, $batch_dir, $allowed_avideo_exts) {
  $item_paths = glob($batch_path.'/*');

  foreach($item_paths as $item_path):
    //若不是檔案, 或是不相干的檔案則略過
    if( !is_normalable($item_path, $allowed_avideo_exts) ) continue;
    //echo $item_path.PHP_EOL;    
    $fname = basename($item_path);    
    $dir_path = dirname($item_path);
    $new_dir_path = $dir_path . '/' . subtok($fname, '.', 0, -1);
    $new_path = $new_dir_path . '/' . $fname;
    //echo $new_path.PHP_EOL;continue;
    
    //要用 rename 搬移之前, 必須先建目錄; 建成功才搬移:
    if( is_dir($new_dir_path) )
      rename($item_path, $new_path);
    elseif( @mkdir($new_dir_path) )
      rename($item_path, $new_path);
    else
      echo "Directory: $new_dir_path failed to create." . PHP_EOL;

  endforeach;
}

/**
 * 是否可正規化?
 */
function is_normalable($item, $allowed_avideo_exts) {
  
  global $allowed_labels;
  
  // 1 必須是檔案
  if(!is_file($item)) return false;
  
  // 2 副檔名必須在允許清單當中
  if(!in_array(subtok($item, '.', -1), $allowed_avideo_exts)) return false;

  // 3 主檔名命名規則必須符合以下要件:
  $basename = basename($item);

  $fs = explode('_', subtok($basename, '.', 0, -1));
  if ( 2 != count($fs) || !in_array(strtolower($fs[0]), $allowed_labels) ) return false;
  
  return true;  
}
