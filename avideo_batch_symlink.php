<?php
/**
 *
 * 批次對 既已完成登錄的avideo檔案, 建立以 (nid.副檔案) 命名 之 符號連結,
 * 以便應用端透過有命名規則的 url 存取.
 *
 * @date:
 *   2014-02-08
 *
 */
include_once dirname(__FILE__).'/nas.conf';
include_once dirname(__FILE__).'/nas.inc';

$info = phase2_check($argv);
//生成 $batch_dir, $batch_path, $batch_log, $entries 四個變數
foreach($info as $k=>$v){$$k=$v;}

foreach($entries as &$entry):  
  if( false === entry_validation($entry, $batch_path) )continue;
  
  //建立符號連結:
  mk_avideo_symlink($entry);
endforeach;

//回寫log:
file_put_contents($batch_log, json_encode($entries));

/**
 * 為單檔建立符號連結
 * log:
 *   不要小看這個動作, 也是磨了好久, 付出很大的代價(毁損不少檔案!)才弄到目前的程度.
 *   符號連結要特別小心, 刪除不怕(不會傷害到原始檔案), 最怕的是不小心覆寫, 直接對原始檔案覆寫下去!
 */
function mk_avideo_symlink(&$entry){

  //注意: 這兩個變數不一樣, 一個是要建立符號連結的家目錄, 一個則是url的根網址
  global $symlink_base_dir, $symlink_base_url;
  
  $lfname = $entry['nid'].".".$entry['ext'];
  $lfpath = $symlink_base_dir.'/'.$lfname;

  if (is_link($lfpath) && is_file($lfpath)){
    $ans = 'Existed';
  }else{ //執行php建立符號連結的指令:
    /**
     * 以下用法正確, 不必懷疑, 在 php 裏, 用 "/" 代表路徑之分隔字元, 不管是在 linux 下或 windows 下皆一體適用.
     * 用 symlink 失敗, 最有可能就是 window cmd console 視窗不是以[系統管理者]的權限開的,
     * 改以[系統管理者]權限開啟即可正常運作.
     */
    $ans = symlink($entry['path'], $lfpath)?'Succeed':'Failed';
  }  
  printf("symlink: %s '%s' '%s'\n", $ans, $entry['path'], $lfpath);
  
  /*
   * 警告! $asx_fname 命名格式千萬要注意! 因為後面會有寫入動作, 怕取到其他既有檔案的檔名, 不小心會對其它檔案造成覆寫誤損!
   */
  $asx_fname = $entry['nid'].'.asx';
  $asx_fpath = $symlink_base_dir.'/'.$asx_fname; 
  
  // avideo 要對外發布的 url:
  $entry['url'] = $symlink_base_url.'/'.$lfname;
  
  //製作檔案, 其實是執行寫入動作, 所以才要對前面 $asx_fname 的命名特別小心.
  mk_asx_file($asx_fpath, $entry);
  
}
 
/**
 * 建立asx格式的檔案
 * log:
 *   裏面有 file_put_contents, 開發階段要特別注意.
 */
function mk_asx_file($asx_path, $entry){
  //先確認要寫入的檔案是 .asx
  if( subtok($asx_path,".",-1) != 'asx') die("Error file type found in '$asx_path' !\n File type must be .asx\n");
  
  $text = sprintf(" 
  <ASX version =\"3.0\">
    <Title>ASX - created by NSA221@hsuyen.dyndns.biz</Title>
      <Entry>
        <Title>%s</Title>
        <ref href=\"%s\"/>
      </Entry>
  </ASX>",
  $entry['title'], $entry['url']);
  
  echo "file_put_contents: $asx_path\n";
  file_put_contents($asx_path, $text);
}
