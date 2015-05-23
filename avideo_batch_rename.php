<?php
/**
 *
 * 批次改名 avideo檔案
 * 像 pornhub 的檔案命名就需要改名過才比較好處理
 *
 * 此檔只會產生 script command list, 並不會真的執行 rename 動作
 * 這是為了讓 user 可以再次確認是否有例外狀況發生.
 *
 * 真正要執行時, 請先將結果輸出到 a.bat, 變更權限後再再執行 a.bat
 *
 * @date:
 *   2014-04-03
 *     1 開發測試完成. 執行結果符合預期.
 *
 */
include_once dirname(__FILE__).'/nas.conf';
include_once dirname(__FILE__).'/nas.inc';

$info = phase1_check($argv);
//生成 $batch_dir, $batch_path 兩個變數
foreach($info as $k=>$v){$$k=$v;}

chdir($batch_path);

rename_pornhub();

//////////////////////////////////////////////////////////////////////////////////////////
function rename_pornhub(){
  $cmd = 'ls -1';
  $result = shell_exec($cmd);
  $src_list = explode("\n", $result);

  $cmd="ls -1 | sed 's/Pornhub Mobile //g' | sed 's/- Free Mobile Iphone Porn & Sex//g'";
  $result = shell_exec($cmd);
  $dest_list = explode("\n", $result);

  foreach($src_list as $idx => $src){
    if(empty($src))
      continue;
      
    //為了將檔案名稱當中含有單引號字號escape掉, 要把 ' 替代為 '\'' 這是相當tricky的方式.
    printf("mv '%s' '%s'\n", str_replace("'", "'\''", $src), str_replace("'", "'\''", $dest_list[$idx]));
  }
}
