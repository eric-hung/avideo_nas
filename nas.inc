<?php
include_once dirname(__FILE__)."/nas.conf";

/**
 *
 */
function get_duration($file_path){
  if(!is_file($file_path)) return 0;
  
  $cmd = sprintf("ffmpeg -i '%s' 2>&1 | grep Duration | awk '{print $2}' | tr -d ,", $file_path);
  $string = shell_exec($cmd);
  $fs = explode(".", $string);
  $hms = explode(":", $fs[0]);
  
  return $hms[0]*3600 + $hms[1]*60 + $hms[2];
}

/**
 * @param $dir
 *   絕對路徑
 * @param $allowed_exts
 *   符合之副檔名陣列
 *
 * @return
 *   檔案清單(不含路徑)
 */
function get_file_list($dir, $allowed_exts) {
  // Open a known directory, and proceed to read its contents
  if(!is_array($allowed_exts))
    $allowed_exts = (array)$allowed_exts;
    
  $files = array();
  if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
      while (($entry = readdir($dh)) !== false) {
        $ext = substr(strrchr($entry, '.'), 1);
        if( is_file($dir . '/' . $entry) && in_array($ext, $allowed_exts))
          $files[] = $entry;
      }
      closedir($dh);
    }
  }
  return $files;
}

/**
 * 找出 $batch_dir 的絕對路徑; 邏輯上必須是唯一, 不能出現兩次.
 */
function get_batch_path($batch_dir){
  global $endpoint_list;
  
  $times = 0;
  foreach($endpoint_list as $endpoint):
    if( empty($endpoint) )continue;
    
    $bp = $endpoint.'/'.$batch_dir;
    if(is_dir($bp)):
      $batch_path = $bp;
      $times++;
    endif;
  endforeach;

  if($times == 0)
    return null;
  elseif($times == 1)
    return $batch_path;
  else return false;
}
 
/**
 * 驗證 entry, 順便在確認其絕對路徑有效時, 加進path屬性
 */
function entry_validation(&$entry, $batch_path){
  $entry['path'] = null;
  
  //驗證 entry 是否有意義?
  if(empty($entry))return false;
  
  //驗證 nid 是否有意義?
  if( !is_numeric($entry['nid']) || $entry['nid']==0 )return false;

  //驗證檔案絕對路徑是否存在?
  $abs_path = dirname($batch_path).'/'.$entry['loc'];
  if(!is_file($abs_path))return false;

  $entry['path'] = $abs_path;
  return true;
} 
/**
 *
 */
function phase2_check($argv) {

  $info = phase1_check($argv);
  
  //驗證 [批次作業日誌檔] 是否存在?
  $batch_log = $info['batch_path'].'.log';
  if(!is_file($batch_log))die("$batch_log not found!\n");

  //驗證 [批次作業記錄] 是否為空?
  $entries = json_decode(file_get_contents($batch_log), true);
  if(empty($entries))die("No entries found!\n");
  //print_r($entries);exit;
  
  return array_merge($info, array('batch_log' => $batch_log, 'entries' => $entries));
}

/**
 *
 */
function phase1_check($argv) {

  //驗證參數數目是否合法? 
  if(count($argv)!=2)die("Exactly 1 parameter required!\nExample: avideo_batch_reg-2014-02-07\n");

  //驗證參數格式是否合法?
  $legal_pat = '/^avideo_batch_reg\-[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/';
  if( !preg_match($legal_pat, $argv[1], $matches))
    die("Parameter format Error!\nExample: avideo_batch_reg-2014-02-07\n");

  //驗證 [批次作業目錄] 是否存在?
  $batch_dir = $argv[1];
  $batch_path = get_batch_path($batch_dir);
  if(!$batch_path)die("$batch_dir not found in any endpoint!\n");
  
  return array('batch_dir' => $batch_dir, 'batch_path' => $batch_path);
}

/* 引自php官網:
 * subtok(string,chr,pos,len)
 *
 * chr = chr used to seperate tokens
 * pos = starting postion
 * len = length, if negative count back from right
 *
 *  subtok('a.b.c.d.e','.',0)     = 'a.b.c.d.e'
 *  subtok('a.b.c.d.e','.',0,2)   = 'a.b'
 *  subtok('a.b.c.d.e','.',2,1)   = 'c'
 *  subtok('a.b.c.d.e','.',2,-1)  = 'c.d'
 *  subtok('a.b.c.d.e','.',-4)    = 'b.c.d.e'
 *  subtok('a.b.c.d.e','.',-4,2)  = 'b.c'
 *  subtok('a.b.c.d.e','.',-4,-1) = 'b.c.d'
 */
function subtok($string,$chr,$pos,$len = NULL) {
  return implode($chr,array_slice(explode($chr,$string),$pos,$len));
}

function echo2($text){
  echo $text."\n";
}
?>

