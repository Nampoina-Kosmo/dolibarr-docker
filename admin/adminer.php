<?php
/** Adminer - Compact database management
* @link https://www.adminer.org/
* @author Jakub Vrana, https://www.vrana.cz/
* @copyright 2007 Jakub Vrana
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
* @version 6.0.2
*/namespace
Adminer;const
VERSION="6.0.2";error_reporting(24575);set_error_handler(function($fd,$hd){return!!preg_match('~^Undefined (array key|offset|index)~',$hd);},E_WARNING|E_NOTICE);$Kd=!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"));if($Kd||ini_get("filter.default_flags")){foreach(array('_GET','_POST','_COOKIE','_SERVER')as$W){$im=filter_input_array(constant("INPUT$W"),FILTER_UNSAFE_RAW);if($im)$$W=$im;}}$_COOKIE=array_filter($_COOKIE,'is_scalar');if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");function
connection($g=null){return($g?:Db::$instance);}function
adminer(){return
Adminer::$instance;}function
driver(){return
Driver::$instance;}function
connect(){$Yb=adminer()->credentials();$H=Driver::connect($Yb[0],$Yb[1],$Yb[2]);return(is_object($H)?$H:null);}function
idf_unescape($u){if(!preg_match('~^[`\'"[]~',$u))return$u;$Uf=substr($u,-1);return
str_replace($Uf.$Uf,$Uf,substr($u,1,-1));}function
q($P){return
connection()->quote($P);}function
idx($Da,$x,$k=null){return($Da&&array_key_exists($x,$Da)?$Da[$x]:$k);}function
number($W){return
preg_replace('~[^0-9]+~','',$W);}function
int_type(){return'(tiny|small|medium|big)?int(eger|\d)?';}function
number_type(){return'(^('.int_type().'|decimal|numeric|number|real|(binary_|half_|scaled_)?float\d?|(binary_)?double( precision)?|(small)?money)$)';}function
text_type(){return'char|text'.(JUSH=="sql"?'|enum|set':'');}function
is_searchable(array$m,array$W){if(!isset($m["privileges"]["where"]))return
false;$T=$m["type"];$Tj=$W["val"];$Ua='binary$|bytea|raw|image|bfile|^vector$'.(JUSH=="mssql"?'|^timestamp$':'|^bit').(JUSH=="oracle"?'|^blob|^long|rowid':'');if(preg_match("~$Ua~",$T))return
false;if(preg_match(number_type(),$T)){$uh='-?\d+(\.\d+)?';return(bool)preg_match('~^'.$uh.(preg_match('~IN$~',$W["op"])?"( *, *$uh)*":'').'$~',$Tj);}if(preg_match('~^(small)?date|^timestamp~',$T))return(bool)preg_match('~^\d+-\d+-\d+~',$Tj);if(preg_match('~^time~',$T))return(bool)preg_match('~^\d+:\d+~',$Tj);if(preg_match('~^bool~',$T)||(JUSH=="mssql"&&$T=="bit"))return(bool)preg_match('~^(t|f|true|false|[01])$~i',$Tj);return
true;}function
remove_slashes(array$Y,$Kd=false){$H=array();foreach($Y
as$x=>$W)$H[stripslashes($x)]=(is_array($W)?remove_slashes($W,$Kd):($Kd?$W:stripslashes($W)));return$H;}function
bracket_escape($u,$Na=false){static$Ll=array(':'=>':1',']'=>':2','['=>':3','"'=>':4','='=>':5');return
strtr($u,($Na?array_flip($Ll):$Ll));}function
url_escape($P){static$Ll=array();if(!$Ll){$Ll=array(' '=>'+');foreach(str_split("\"'<>#%&+=?".ini_get("arg_separator.input"))as$hb)$Ll[$hb]=sprintf('%%%02X',ord($hb));for($s=0;$s<256;$s++){if($s<32||$s>126)$Ll[chr($s)]=sprintf('%%%02X',$s);}}return
strtr((string)$P,$Ll);}function
min_version($Gm,$pg="",$g=null){$g=connection($g);$ok=$g->server_info;if($pg&&preg_match('~([\d.]+)-MariaDB~',$ok,$A)){$ok=$A[1];$Gm=$pg;}return$Gm&&version_compare($ok,$Gm)>=0;}function
charset(Db$f){return(min_version("5.5.3",0,$f)?"utf8mb4":"utf8");}function
ini_set($Rh,$X){return(function_exists('ini_set')?\ini_set($Rh,$X):false);}function
ini_bool($mf){$W=ini_get($mf);return(preg_match('~^(on|true|yes)$~i',$W)||(int)$W);}function
ini_bytes($mf){$W=ini_get($mf);switch(strtolower(substr($W,-1))){case'g':$W=(int)$W*1024;case'm':$W=(int)$W*1024;case'k':$W=(int)$W*1024;}return$W;}function
max_input_vars($I,$gi){$tg=(int)ini_get("max_input_vars");return($tg?(int)floor(($tg-$gi)/$I):0);}function
max_input_vars_error(){$mf="max_input_vars";return
lang(0,"<b>$mf = ".ini_get($mf)."</b>");}function
sid(){static$H;if($H===null)$H=(SID&&!($_COOKIE&&ini_bool("session.use_cookies")));return$H;}function
set_password($Fm,$M,$U,$E){$_SESSION["pwds"][$Fm][$M][$U]=($_COOKIE["adminer_key"]&&is_string($E)?array(encrypt_string($E,$_COOKIE["adminer_key"])):$E);}function
get_password(){$H=get_session("pwds");if(is_array($H))$H=($_COOKIE["adminer_key"]?decrypt_string($H[0],$_COOKIE["adminer_key"]):false);return$H;}function
get_val($F,$m=0,$Jb=null){$Jb=connection($Jb);$G=$Jb->query($F);if(!is_object($G))return
false;$I=$G->fetch_row();return($I?$I[$m]:false);}function
get_vals($F,$d=0){$H=array();$G=connection()->query($F);if(is_object($G)){while($I=$G->fetch_row())$H[]=$I[$d];}return$H;}function
get_key_vals($F,$g=null,$rk=true){$g=connection($g);$H=array();$G=$g->query($F);if(is_object($G)){while($I=$G->fetch_row()){if($rk)$H[$I[0]]=$I[1];else$H[]=$I[0];}}return$H;}function
get_rows($F,$g=null,$l="<p class='error'>"){$Jb=connection($g);$H=array();$G=$Jb->query($F);if(is_object($G)){while($I=$G->fetch_assoc())$H[]=$I;}elseif(!$G&&!$g&&$l&&(defined('Adminer\PAGE_HEADER')||$l=="-- "))echo$l.adminer()->error()."\n";return$H;}function
unique_array($I,array$w){foreach($w
as$v){if(preg_match("~^(PRIMARY|UNIQUE)$~",$v["type"])&&!$v["partial"]){$H=array();foreach($v["columns"]as$x){if(!isset($I[$x]))continue
2;$H[$x]=$I[$x];}return$H;}}}function
escape_key($x){if(preg_match('(^([\w(]+)('.str_replace("_",".*",preg_quote(idf_escape("_"))).')([ \w)]+)$)',$x,$A))return$A[1].idf_escape(idf_unescape($A[2])).$A[3];return
idf_escape($x);}function
where(array$Z,array$n=array()){$H=array();foreach((array)$Z["where"]as$x=>$W){$x=bracket_escape($x,true);$d=escape_key($x);$m=idx($n,$x,array());$Ed=$m["type"];$zf=$m&&(is_blob($m)||preg_match('~binary~',$Ed));$H[]=$d.($zf&&!is_utf8($W)?" = ".driver()->quoteBinary($W):(JUSH=="sql"&&$Ed=="json"?" = CAST(".q($W)." AS JSON)":(JUSH=="pgsql"&&preg_match('~^jsonb?$~',$m["full_type"])?"::jsonb = ".q($W)."::jsonb":(JUSH=="sql"&&is_numeric($W)&&preg_match('~\.~',$W)?" LIKE ".q($W):(JUSH=="mssql"&&strpos($Ed,"datetime")===false?" LIKE ".q(preg_replace('~[_%[]~','[\0]',$W)):" = ".unconvert_field($m,q($W)))))));if(JUSH=="sql"&&preg_match('~char|text~',$Ed)&&preg_match("~[^ -@]~",$W))$H[]="$d = ".q($W)." COLLATE ".charset(connection())."_bin";}foreach((array)$Z["null"]as$x)$H[]=escape_key($x)." IS NULL";return
implode(" AND ",$H);}function
where_columns(array$n){$H=array();foreach((array)$_GET["null"]as$x)$H[$x]=true;foreach((array)$_GET["where"]as$x=>$W){$x=bracket_escape($x,true);foreach($n
as$B=>$m){if($x==$B||strpos($x,idf_escape($B))!==false)$H[$B]=true;}}return$H;}function
where_check($W,array$n=array()){parse_str($W,$kb);remove_slashes(array(&$kb));return
where($kb,$n);}function
where_link($s,$d,$X,$Oh="="){$Lh=($X!==null?$Oh:"IS NULL");return"&where[$s][col]=".url_escape($d).($Lh!=first(adminer()->operators())?"&where[$s][op]=".url_escape($Lh):"")."&where[$s][val]=".url_escape($X);}function
convert_fields(array$e,array$n,array$L=array()){$H="";foreach($e
as$x=>$W){if($L&&!in_array(idf_escape($x),$L))continue;$Ea=convert_field($n[$x]);if($Ea)$H
.=", $Ea AS ".idf_escape($x);}return$H;}function
cookie_path(){return
strtr(preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),array(";"=>"%3B",","=>"%2C"));}function
cookie($B,$X,$eg=2592000){header("Set-Cookie: $B=".rawurlencode($X).($eg?"; expires=".gmdate("D, d M Y H:i:s",time()+$eg)." GMT":"")."; path=".cookie_path().(HTTPS?"; secure":"").($B=="adminer_import"?"":"; HttpOnly")."; SameSite=lax",false);}function
get_url($qm,$Qb){$http_response_header=null;$gd=array();set_error_handler(function($fd,$l)use(&$gd){$gd[]=preg_replace('~^file_get_contents\([^)]*\):\s*~','',$l);return
true;});$H=file_get_contents($qm,false,$Qb);restore_error_handler();$Fe=(function_exists('http_get_last_response_headers')?http_get_last_response_headers():$http_response_header);return
array($H,(preg_match('~^HTTP/[\d.]+ (\d+)~',idx($Fe,0,''),$A)?$A[1]:''),(array)$Fe,($H===false?implode("\n",$gd):''),);}function
get_settings($Tb){parse_str($_COOKIE[$Tb],$sk);return$sk;}function
get_setting($x,$Tb="adminer_settings",$k=null){return
idx(get_settings($Tb),$x,$k);}function
save_settings(array$sk,$Tb="adminer_settings"){$X=http_build_query($sk+get_settings($Tb));cookie($Tb,$X);$_COOKIE[$Tb]=$X;}function
restart_session(){if(!ini_bool("session.use_cookies")&&(!function_exists('session_status')||session_status()==PHP_SESSION_NONE))session_start();}function
stop_session($Td=false){$tm=ini_bool("session.use_cookies");if(!$tm||$Td){session_write_close();if($tm&&ini_set("session.use_cookies",'0')===false)session_start();}}function&get_session($x){return$_SESSION[$x][DRIVER][SERVER][$_GET["username"]];}function
set_session($x,$W){$_SESSION[$x][DRIVER][SERVER][$_GET["username"]]=$W;}function
auth_url($Fm,$M,$U,$j=null){$pm=remove_from_uri(implode("|",array_keys(SqlDriver::$drivers))."|username|ext|".($j!==null?"db|":"").($Fm=='mssql'||$Fm=='pgsql'?"":"ns|").session_name());preg_match('~([^?]*)\??(.*)~',$pm,$A);return"$A[1]?".(sid()?SID."&":"").($_GET["ext"]?"ext=".url_escape($_GET["ext"])."&":"").($Fm!="server"||$M!=""?url_escape($Fm)."=".url_escape($M)."&":"")."username=".url_escape($U).($j!=""?"&db=".url_escape($j):"").($A[2]?"&$A[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($lg,$Ig=null){if($Ig!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($lg!==null?$lg:$_SERVER["REQUEST_URI"]))][]=$Ig;}if($lg!==null){if($lg=="")$lg=".";header("Location: $lg");exit;}}function
query_redirect($F,$lg,$Ig,$pj=true,$od=true,$zd=false,$zl=""){if($od){$Nk=microtime(true);$zd=!connection()->query($F);$zl=format_time($Nk);}$Gk=($F?adminer()->messageQuery($F,$zl,$zd):"");if($zd){adminer()->error
.=adminer()->error().$Gk.script("messagesPrint();")."<br>";return
false;}if($pj)redirect($lg,$Ig.$Gk);return
true;}class
Queries{static$queries=array();static$start=0;}function
queries($F){if(!Queries::$start)Queries::$start=microtime(true);Queries::$queries[]=(driver()->delimiter!=';'?$F:(preg_match('~;$~',$F)?"DELIMITER ;;\n$F;\nDELIMITER ":$F).";");return
connection()->query($F);}function
apply_queries($F,array$S,$id='Adminer\table'){foreach($S
as$Q){if(!queries("$F ".$id($Q)))return
false;}return
true;}function
queries_redirect($lg,$Ig,$pj){$jj=implode("\n",Queries::$queries);$zl=format_time(Queries::$start);return
query_redirect($jj,$lg,$Ig,$pj,false,!$pj,$zl);}function
format_time($Nk){return
lang(1,max(0,microtime(true)-$Nk));}function
relative_uri($pm=''){return
preg_replace_callback('~^[^?]*~',function($A){return
str_replace(":","%3A",$A[0]);},preg_replace('~^[^?]*/([^?]*)~','\1',($pm?:$_SERVER["REQUEST_URI"])));}function
remove_from_uri($ni=""){return
substr(preg_replace("~(?<=[?&])($ni".(SID?"":"|".session_name()).")=[^&]*&~",'',relative_uri()."&"),0,-1);}function
get_files($B,$mc=false){$Gd=$_FILES[$B];if(!$Gd)return
null;foreach($Gd
as$x=>$W)$Gd[$x]=(array)$W;$H=array();foreach($Gd["error"]as$x=>$l){if($l)return$l;$o=$Gd["name"][$x];$Gl=$Gd["tmp_name"][$x];$Ob=file_get_contents($mc&&preg_match('~\.gz$~',$o)?"compress.zlib://$Gl":$Gl);if($mc){$Nk=substr($Ob,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$Nk))$Ob=iconv("utf-16","utf-8",$Ob);elseif($Nk=="\xEF\xBB\xBF")$Ob=substr($Ob,3);}$H[]=array($o,$Ob);}return$H;}function
get_file($x,$mc=false,$tc=""){$Jd=get_files($x,$mc);if(!is_array($Jd))return$Jd;$H='';foreach($Jd
as$Gd){$Ob=$Gd[1];$H
.=$Ob;if($tc)$H
.=(preg_match("($tc\\s*\$)",$Ob)?"":$tc)."\n\n";}return$H;}function
upload_error($l){$Ag=($l==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($l?lang(2).($Ag?" ".lang(3,$Ag):""):lang(4));}function
is_utf8($W){return(preg_match('~~u',$W)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$W));}function
format_number($W){preg_match('~^#+([^#0]+)(?:(#+)\1)?(#*0)$~u',lang(5),$A);$wk=strlen($A[3]);$H=number_format($W,0,".","");$H=preg_replace('~\B(?=(\d{'.(strlen($A[2])?:$wk).'})*\d{'.$wk.'}$)~',$A[1],$H);return
strtr($H,preg_split('~~u',lang(6),-1,PREG_SPLIT_NO_EMPTY));}function
format_status(array$R,$x){$W=idx($R,$x,'?');if(!is_numeric($W))return
h($W);if($W<0)return'?';$Aa=($x=="Rows"&&(JUSH=="sqlite"||$R["Engine"]==(JUSH=="pgsql"?"table":"InnoDB")));return($Aa?"~ ":"").format_number($W);}function
friendly_url($W){return
preg_replace('~\W~i','-',$W);}function
table_status1($Q,$_d=false){$H=table_status($Q,$_d);return($H?reset($H):array("Name"=>$Q));}function
column_foreign_keys($Q){$H=array();foreach(adminer()->foreignKeys($Q)as$p){foreach($p["source"]as$W)$H[$W][]=$p;}return$H;}function
fields_from_edit(){$H=array();foreach((array)$_POST["field_keys"]as$x=>$W){if($W!=""){$W=bracket_escape($W);$_POST["function"][$W]=$_POST["field_funs"][$x];$_POST["fields"][$W]=$_POST["field_vals"][$x];}}foreach((array)$_POST["fields"]as$x=>$W){$B=bracket_escape($x,true);$H[$B]=array("field"=>$B,"full_type"=>"","type"=>"","privileges"=>array("insert"=>1,"update"=>1,"where"=>1,"order"=>1),"null"=>true,"auto_increment"=>($B==driver()->primary),);}return$H;}function
dump_headers($Re,$Zg=false){$H=adminer()->dumpHeaders($Re,$Zg);$ii=$_POST["output"];if($ii!="text"||$H=="tar"){$Fb=($ii!="text"&&$ii!="file"&&preg_match('~^[0-9a-z]+$~',$ii)?".$ii":"");header("Content-Disposition: attachment; filename=".adminer()->dumpFilename($Re).".$H$Fb");}session_write_close();if(!ob_get_level())ob_start(null,4096);ob_flush();flush();return$H;}function
dump_csv(array$I){$Xl=$_POST["format"]=="tsv";foreach($I
as$x=>$W){if(preg_match('~["\n]|^0[^.]|\.\d*0$|'.($Xl?'\t':'[,;]|^$').'~',$W))$I[$x]='"'.str_replace('"','""',$W).'"';}echo
implode(($_POST["format"]=="csv"?",":($Xl?"\t":";")),$I)."\r\n";}function
parse_csv($bc,$dk){$H=array();preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$bc,$rg);foreach($rg[0]as$I){preg_match_all("~((?>\"[^\"]*\")+|[^$dk]*)$dk~",$I.$dk,$sg);$H[]=$sg[1];}return$H;}function
csv_value($W){return(preg_match('~^".*"$~s',$W)?str_replace('""','"',substr($W,1,-1)):$W);}function
apply_sql_function($q,$d){return($q?($q=="unixepoch"?"DATETIME($d, '$q')":($q=="count distinct"?"COUNT(DISTINCT ":strtoupper("$q("))."$d)"):$d);}function
get_temp_dir(){return
ini_get("upload_tmp_dir")?:sys_get_temp_dir();}function
file_open_lock($o){if(is_link($o))return;$ae=@fopen($o,"c+");if(!$ae)return;@chmod($o,0660);if(!flock($ae,LOCK_EX)){fclose($ae);return;}return$ae;}function
file_write_unlock($ae,$fc){rewind($ae);fwrite($ae,$fc);ftruncate($ae,strlen($fc));file_unlock($ae);}function
file_unlock($ae){flock($ae,LOCK_UN);fclose($ae);}function
first(array$Da){return
reset($Da);}function
password_file($h){$o=get_temp_dir()."/adminer.key";if(!$h&&!file_exists($o))return'';$ae=file_open_lock($o);if(!$ae)return'';$H=stream_get_contents($ae);if(!$H){$H=rand_string();file_write_unlock($ae,$H);}else
file_unlock($ae);return$H;}function
rand_string(){return(function_exists('random_bytes')?bin2hex(random_bytes(16)):md5(uniqid(strval(mt_rand()),true)));}function
select_value($W,$_,array$m,$xl){if(is_array($W)){$H="";if(array_filter($W,'is_array')==array_values($W)){$Kf=array();foreach($W
as$V)$Kf+=array_fill_keys(array_keys($V),null);foreach(array_keys($Kf)as$If)$H
.="<th>".h($If);foreach($W
as$V){$H
.="<tr>";foreach(array_merge($Kf,$V)as$_m)$H
.="<td>".select_value($_m,$_,$m,$xl);}}else{foreach($W
as$If=>$V)$H
.="<tr>".($W!=array_values($W)?"<th>".h($If):"")."<td>".select_value($V,$_,$m,$xl);}return"<table>$H</table>";}if(!$_)$_=adminer()->selectLink($W,$m);if($_===null){if(is_mail($W))$_="mailto:$W";if(is_url($W))$_=$W;}$W=driver()->value($W,$m);$H=adminer()->editVal($W,$m);if($H!==null){if(!is_utf8($H))$H="\0";elseif($xl!=""&&is_shortable($m))$H=shorten_utf8($H,max(0,+$xl));else$H=h($H);}return
adminer()->selectVal($H,$_,$m,$W);}function
is_blob(array$m){return
preg_match('~blob|bytea|raw|file'.(JUSH=="mssql"?'|binary|image':'').'~',$m["type"])&&!in_array($m["type"],idx(driver()->structuredTypes(),lang(7),array()));}function
is_mail($Wc){$Ga='[-a-z0-9!#$%&\'*+/=?^_`{|}~]';$Jc='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';$Ei="$Ga+(\\.$Ga+)*@($Jc?\\.)+$Jc";return
is_string($Wc)&&preg_match("(^$Ei(,\\s*$Ei)*\$)i",$Wc);}function
is_url($P){$Jc='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';return
preg_match("~^((https?):)?//($Jc?\\.)+$Jc(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i",$P);}function
is_ipv6($pa){$r='[\da-f]{1,4}';$yf='\d{1,3}(\.\d{1,3}){3}';return(bool)preg_match("~^(($r:){7}$r|($r:){6}$yf|(($r:)*$r)?::(($r:)*($r|$yf))?)$~iD",$pa);}function
is_shortable(array$m){return!preg_match('~'.number_type().'|date|time|year~',$m["type"]);}function
url_host($Ne){return(strpos($Ne,":")!==false?"[$Ne]":$Ne);}function
server_parts(array$zi){return
array("scheme"=>(string)$zi["scheme"],"host"=>(string)$zi["host"],"port"=>(string)$zi["port"],"socket"=>(string)$zi["socket"],"path"=>(string)$zi["path"],);}function
parse_server($M){if($M=="")return
server_parts(array());if($M[0]==":"&&!is_ipv6($M)){$Bj=substr($M,1);if(preg_match('~^\d+$~D',$Bj))return
server_parts(array("port"=>$Bj));return(preg_match('~^/[-\w.:/]*$~D',$Bj)?server_parts(array("socket"=>$Bj)):null);}$Rj="";if(preg_match('~^([-+.\w]+)://~',$M,$A)){$Rj=strtolower($A[1]);$M=substr($M,strlen($A[0]));}if(preg_match('~^\[(.+)](:(\d+))?(/[-\w./]*)?$~D',$M,$A))return(is_ipv6($A[1])?server_parts(array("scheme"=>$Rj,"host"=>$A[1],"port"=>$A[3],"path"=>$A[4])):null);if(is_ipv6($M))return
server_parts(array("scheme"=>$Rj,"host"=>$M));if(preg_match('~^(/[-\w./]*)(:(\d+))?$~D',$M,$A))return
server_parts(array("scheme"=>$Rj,"host"=>$A[1],"port"=>$A[3]));return(preg_match('~^([-\w.]*)(:(\d+))?(/[-\w./]*)?$~D',$M,$A)?server_parts(array("scheme"=>$Rj,"host"=>$A[1],"port"=>$A[3],"path"=>$A[4])):null);}function
count_rows($Q,array$Z,$_f,array$r){$F=" FROM ".table($Q).($Z?" WHERE ".implode(" AND ",$Z):"");return($_f&&(JUSH=="sql"||count($r)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$r).")$F":"SELECT COUNT(*)".($_f?" FROM (SELECT 1$F GROUP BY ".implode(", ",$r).") x":$F));}function
slow_query($F){$j=adminer()->database();$_l=adminer()->queryTimeout();$yk=driver()->slowQuery($F,$_l);$g=null;if(!$yk&&support("kill")){$g=connect();if($g&&($j==""||$g->select_db($j))){$Lf=number(get_val(connection_id(),0,$g));echo
script("const timeout = setTimeout(() => { ajax('".js_escape(ME)."script=kill', function () {}, 'kill=$Lf&token=".get_token()."'); }, 1000 * $_l);");}}ob_flush();flush();$H=@get_key_vals(($yk?:$F),$g,false);if($g){echo
script("clearTimeout(timeout);");ob_flush();flush();}return$H;}function
get_token(){$mj=rand(1,1e6);return($mj^$_SESSION["token"]).":$mj";}function
verify_token(){list($Hl,$mj)=explode(":",$_POST["token"]);return($mj^$_SESSION["token"])==$Hl&&in_array($_SERVER["HTTP_SEC_FETCH_SITE"],array("","same-origin"));}function
compress_alphabet(){return
strtr(implode(range('"','~')),"'\\","!\n");}function
decompress_string($P,$zc=""){$ya=array_flip(str_split(compress_alphabet()));$y=strlen($P);$Cm=($y?13*($y-1)/2-$ya[$P[0]]:0);$Ua="";$Bj=0;$Cj=0;for($s=1;$s<$y;$s+=2){$Bj=($Bj<<13)+$ya[$P[$s]]*93+$ya[$P[$s+1]];$Cj+=13;while($Cj>=8&&$Cm>=8){$Cj-=8;$Cm-=8;$Ua
.=chr($Bj>>$Cj);$Bj&=(1<<$Cj)-1;}}if($Ua=="")return"";if($zc!=""&&function_exists('inflate_init'))return
inflate_add(inflate_init(ZLIB_ENCODING_RAW,array('dictionary'=>$zc)),$Ua,ZLIB_FINISH);return($zc==""&&function_exists('gzinflate')?gzinflate($Ua):inflate($Ua,$zc));}function
inflate($Ua,$zc=""){$bg=array(3,4,5,6,7,8,9,10,11,13,15,17,19,23,27,31,35,43,51,59,67,83,99,115,131,163,195,227,258);$cg=array(0,0,0,0,0,0,0,0,1,1,1,1,2,2,2,2,3,3,3,3,4,4,4,4,5,5,5,5,0);$Cc=array(1,2,3,4,5,7,9,13,17,25,33,49,65,97,129,193,257,385,513,769,1025,1537,2049,3073,4097,6145,8193,12289,16385,24577);$Ec=array(0,0,0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9,10,10,11,11,12,12,13,13);$H=$zc;$Ni=0;do{$Ld=inflate_bits($Ua,$Ni,1);$T=inflate_bits($Ua,$Ni,2);if(!$T){$Ni=($Ni+7)&~7;$y=inflate_bits($Ua,$Ni,16);$Ni+=16;$H
.=substr($Ua,$Ni>>3,$y);$Ni+=$y<<3;}else{if($T==1){$jg=array_merge(array_fill(0,144,8),array_fill(0,112,9),array_fill(0,24,7),array_fill(0,8,8));$Fc=array_fill(0,30,5);}else{$ig=inflate_bits($Ua,$Ni,5)+257;$Dc=inflate_bits($Ua,$Ni,5)+1;$Uh=array(16,17,18,0,8,7,9,6,10,5,11,4,12,3,13,2,14,1,15);$Og=array_fill(0,19,0);$Ng=inflate_bits($Ua,$Ni,4)+4;for($s=0;$s<$Ng;$s++)$Og[$Uh[$s]]=inflate_bits($Ua,$Ni,3);$Pg=inflate_table($Og);$dg=array();while(count($dg)<$ig+$Dc){$Yk=inflate_symbol($Ua,$Ni,$Pg);if($Yk==16)$dg=array_merge($dg,array_fill(0,inflate_bits($Ua,$Ni,2)+3,end($dg)));elseif($Yk==17)$dg=array_merge($dg,array_fill(0,inflate_bits($Ua,$Ni,3)+3,0));elseif($Yk==18)$dg=array_merge($dg,array_fill(0,inflate_bits($Ua,$Ni,7)+11,0));else$dg[]=$Yk;}$jg=array_slice($dg,0,$ig);$Fc=array_slice($dg,$ig);}$kg=inflate_table($jg);$Hc=inflate_table($Fc);while(($Yk=inflate_symbol($Ua,$Ni,$kg))!=256){if($Yk<256)$H
.=chr($Yk);else{$y=$bg[$Yk-257]+inflate_bits($Ua,$Ni,$cg[$Yk-257]);$Gc=inflate_symbol($Ua,$Ni,$Hc);$Ah=strlen($H)-$Cc[$Gc]-inflate_bits($Ua,$Ni,$Ec[$Gc]);for($s=0;$s<$y;$s++)$H
.=$H[$Ah+$s];}}}}while(!$Ld);return($zc==""?$H:substr($H,strlen($zc)));}function
inflate_bits($Ua,&$Ni,$Vb){$H=0;for($s=0;$s<$Vb;$s++){$H+=((ord($Ua[$Ni>>3])>>($Ni&7))&1)<<$s;$Ni++;}return$H;}function
inflate_table(array$dg){$Q=array();$ub=0;for($Va=1;$Va<=max($dg);$Va++){foreach($dg
as$Yk=>$y){if($y==$Va){$Q[$Va][$ub]=$Yk;$ub++;}}$ub<<=1;}return$Q;}function
inflate_symbol($Ua,&$Ni,array$Q){$ub=0;$Va=0;do{$ub=($ub<<1)+inflate_bits($Ua,$Ni,1);$Va++;}while(!isset($Q[$Va][$ub]));return$Q[$Va][$ub];}function
script($Ck,$Kl="\n"){return"<script".nonce().">$Ck</script>$Kl";}function
script_src($qm,$pc=false){return"<script src='".h($qm)."'".nonce().($pc?" defer":"")."></script>\n";}function
nonce(){return' nonce="'.get_nonce().'"';}function
on($jd,$xe,$Ba=null){$Ca=array();foreach(array_slice(func_get_args(),2)as$W)$Ca[]=json_encode($W,256);return" data-on$jd='".str_replace(array('&','<',"'"),array('&amp;','&lt;','&#039;'),"$xe(".implode(", ",$Ca).")")."'";}function
input_hidden($B,$X=""){return"<input type='hidden' name='".h($B)."' value='".h($X)."'>\n";}function
input_token(){return
input_hidden("token",get_token());}function
target_blank(){return' target="_blank" rel="noreferrer noopener"';}function
h($P){return
str_replace(array('&','<','"',"'","\0"),array('&amp;','&lt;','&quot;','&#039;','&#0;'),$P);}function
nl_br($P){return
str_replace("\n","<br>",$P);}function
checkbox($B,$X,$nb,$Pf="",$c="",$sb="",$Rf=""){$H="<input type='checkbox' name='$B' value='".h($X)."'".($nb?" checked":"").($Pf==""&&$sb?" class='$sb'":"").($Rf?" aria-labelledby='$Rf'":"").$c.">";return($Pf!=""?"<label".($sb?" class='$sb'":"").">$H".h($Pf)."</label>":$H);}function
optionlist($C,$Zj=null,$um=false){$H="";foreach($C
as$If=>$V){$Th=array($If=>$V);if(is_array($V)){$H
.='<optgroup label="'.h($If).'">';$Th=$V;}foreach($Th
as$x=>$W)$H
.='<option'.($um||is_string($x)?' value="'.h($x).'"':'').($Zj!==null&&($um||is_string($x)?(string)$x:$W)===$Zj?' selected':'').'>'.h($W);if(is_array($V))$H
.='</optgroup>';}return$H;}function
html_select($B,array$C,$X="",$c="",$Rf=""){static$Pf=0;$Qf="";if(!$Rf&&substr($C[""],0,1)=="("){$Pf++;$Rf="label-$Pf";$Qf="<option value='' id='$Rf'>".h($C[""]);unset($C[""]);}return"<select name='".h($B)."'".($Rf?" aria-labelledby='$Rf'":"")."$c>".$Qf.optionlist($C,$X)."</select>";}function
html_radios($B,array$C,$X="",$dk=""){$H="";foreach($C
as$x=>$W)$H
.="<label><input type='radio' name='".h($B)."' value='".h($x)."'".($x==$X?" checked":"").">".h($W)."</label>$dk";return$H;}function
confirm($Ig=""){return
on('click','confirmClick',$Ig?:lang(8));}function
print_fieldset($t,$ag,$Jm=false){echo"<fieldset><legend>","<a href='#fieldset-$t' class='toggle'>$ag</a>","</legend>","<div id='fieldset-$t'".($Jm?"":" class='hidden'").">\n";}function
bold($Xa,$sb=""){return($Xa?" class='active $sb'":($sb?" class='$sb'":""));}function
js_escape($P){return
str_replace("<","\\x3C",addcslashes($P,"\r\n'\\"));}function
js_escape_re($P){return
addcslashes(preg_quote($P,"/"),"\r\n");}function
pagination_href($D){return
remove_from_uri("page|next").($D?"&page=$D".($_GET["next"]!=""?"&next=".url_escape($_GET["next"]):""):"");}function
pagination($D,$cc){return" ".($D==$cc?($D?"<b>".($D+1)."</b>":$D+1):'<a href="'.h(pagination_href($D)).'">'.($D+1)."</a>");}function
hidden_fields(array$fj,array$Ve=array(),$Ui=''){$H=false;foreach($fj
as$x=>$W){if(!in_array($x,$Ve)){if(is_array($W))hidden_fields($W,array(),$x);else{$H=true;echo
input_hidden(($Ui?$Ui."[$x]":$x),$W);}}}return$H;}function
hidden_fields_get(){echo(sid()?input_hidden(session_name(),session_id()):''),($_GET["ext"]?input_hidden("ext",$_GET["ext"]):""),(isset($_GET[DRIVER])?input_hidden(DRIVER,SERVER):""),input_hidden("username",$_GET["username"]);}function
on_upload_progress(&$om){$om=(ini_bool("session.upload_progress.enabled")&&ini_get("session.upload_progress.name")?rand_string():"");return($om?on('submit','uploadProgress',ME."upload=$om",SESSION_NAME."=$om"):"");}function
file_input($c,$Bj=""){$vg="max_file_uploads";$wg=ini_get($vg);$Ag="upload_max_filesize";$Bg=ini_bytes($Ag);$Ri=ini_bytes("post_max_size");if($Ri&&$Ri<$Bg){$Ag="post_max_size";$Bg=$Ri;}$Cg=ini_get($Ag);return(ini_bool("file_uploads")?"<input type='file'$c".on('change','fileChange',(int)$wg,lang(9,"$vg = $wg"),$Bg,lang(9,"$Ag = $Cg")).">$Bj":lang(10));}function
enum_input($T,$c,array$m,$X,$Zc=""){preg_match_all("~'((?:[^']|'')*)'~",$m["length"],$rg);$Ui=($m["type"]=="enum"?"val-":"");$nb=(is_array($X)?in_array("null",$X):$X===null);$H=($m["null"]&&$Ui?"<label><input type='$T'$c value='null'".($nb?" checked":"")."><i>$Zc</i></label>":"");foreach($rg[1]as$W){$W=stripcslashes(str_replace("''","'",$W));$nb=(is_array($X)?in_array($Ui.$W,$X):$X===$W);$H
.=" <label><input type='$T'$c value='".h($Ui.$W)."'".($nb?' checked':'').'>'.h(adminer()->editVal($W,$m)).'</label>';}return$H;}function
input(array$m,$X,$q,$La=false,$mm=false){$B=h(bracket_escape($m["field"]));echo"<td class='function'>";$ed=driver()->enumLength($m);if($ed){$m["type"]="enum";$m["length"]=$ed;}$C=($m["type"]=="enum"||$m["type"]=="set");if(is_array($X)&&!$q&&!$C)$q="json";$Gf=($q=="json"||preg_match('~^jsonb?$~',$m["full_type"]));if($Gf&&$X!=''&&(JUSH!="pgsql"||$m["type"]!="json")&&(is_array($X)||!$_POST["save"]))$X=json_encode(is_array($X)?$X:json_decode($X),128|64|256);$Aj=(JUSH=="mssql"&&$mm&&$m["auto_increment"]);if($Aj&&!$_POST["save"])$q=null;$je=(isset($_GET["select"])||$Aj?array("orig"=>lang(11)):array())+adminer()->editFunctions($m);$c=" name='fields[$B]".($C?"[]":"")."'".($La?" autofocus":"");echo
driver()->unconvertFunction($m)." ";$Q=$_GET["edit"]?:$_GET["select"];if($m["type"]=="enum")echo
h($je[""])."<td>".adminer()->editInput($Q,$m,$c,$X);else{$ze=(in_array($q,$je)||isset($je[$q]));$Md=0;foreach($je
as$x=>$W){if($x===""||!$W)break;$Md++;}echo(count($je)>1?"<select name='function[$B]'".on('change','functionChange').on_help_value('^SQL$').">".optionlist($je,$q===null||$ze?$q:"")."</select>":h(reset($je)))."<td".($Md&&count($je)>1?on('input','skipOriginal',$Md):"").">";$of=adminer()->editInput($Q,$m,$c,$X);if($of!="")echo$of;elseif(preg_match('~bool~',$m["type"]))echo"<input type='hidden'$c value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$X)?" checked":"")."$c value='1'>";elseif($m["type"]=="set")echo
enum_input("checkbox",$c,$m,(is_string($X)?explode(",",$X):$X));elseif(is_blob($m)&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$B'>";elseif($Gf)echo"<textarea$c cols='50' rows='12' class='jush-json'>".h($X).'</textarea>';elseif(($wl=preg_match('~text|lob|memo~i',$m["type"]))||preg_match("~\n~",$X)){if($wl&&JUSH!="sqlite")$c
.=" cols='50' rows='12'";else{$J=min(12,substr_count($X,"\n")+1);$c
.=" cols='30' rows='$J'";}echo"<textarea$c>".h($X).'</textarea>';}else{$bm=driver()->types();$Zl=$bm[$m["type"]];if(preg_match('~date|time|year~',$m["type"])){$be=(preg_match('~time~',$m["type"])&&preg_match('~^\d+$~',$m["length"])?$m["length"]+1:0);$Dg=($Zl?$Zl+$be:0);}elseif(!preg_match('~int|vector~',$m["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$m["length"],$A))$Dg=(preg_match("~binary~",$m["type"])?2:1)*$A[1]+($A[3]?1:0)+($A[2]&&!$m["unsigned"]?1:0);else$Dg=($Zl?$Zl+($m["unsigned"]?0:1):0);echo"<input".((!$ze||$q==="")&&preg_match('~^'.int_type().'$~',$m["type"])&&!preg_match('~\[]~',$m["full_type"])?" type='number'":"")." value='".h($X)."'".($Dg?" data-maxlength='$Dg'":"").(preg_match('~char|binary~',$m["type"])&&$Dg>20?" size='".($Dg>99?60:40)."'":"")."$c>";}echo
adminer()->editHint($Q,$m,$X),(count($je)>1?script("fire(qs('select', qsl('td').previousSibling), 'change');",""):"");}}function
process_input(array$m){$u=bracket_escape($m["field"]);$q=idx($_POST["function"],$u);if($q=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?idf_escape($m["field"]):false);if($q=="NULL")return"NULL";if(is_blob($m)&&ini_bool("file_uploads")){$Gd=get_file("fields-$u");if(!is_string($Gd))return
false;return
driver()->quoteBinary($Gd);}$X=idx($_POST["fields"],$u);if($X===null)return
false;if($m["type"]=="enum"||driver()->enumLength($m)){$X=idx($X,0);if($X=="orig"||!$X)return
false;if($X=="null")return"NULL";$X=substr($X,4);}if($m["auto_increment"]&&$X=="")return
null;if($m["type"]=="set")$X=implode(",",(array)$X);if($q=="json"){$X=json_decode($X,true);if(!is_array($X))return
false;return$X;}return
adminer()->processInput($m,$X,$q);}function
search_tables(){$_GET["where"][0]["val"]=$_POST["query"];$ck="<ul>\n";foreach(table_status('',true)as$Q=>$R){$B=adminer()->tableName($R);if(isset($R["Engine"])&&$B!=""&&(!$_POST["tables"]||in_array($Q,$_POST["tables"]))){$G=connection()->query("SELECT".limit("1 FROM ".table($Q)," WHERE ".implode(" AND ",adminer()->selectSearchProcess(fields($Q),array(),$R)),1));if(!$G||$G->fetch_row()){$bj="<a href='".h(ME."select=".url_escape($Q)."&where[0][op]=".url_escape($_GET["where"][0]["op"])."&where[0][val]=".url_escape($_GET["where"][0]["val"]))."'>$B</a>";echo"$ck<li>".($G?$bj:"<p class='error'>$bj: ".adminer()->error())."\n";$ck="";}}}echo($ck?"<p class='message'>".lang(12):"</ul>")."\n";}function
on_help($wl,$vk=0){return
on('mouseover','helpMouseover',$wl,$vk).on('mouseout','helpMouseout');}function
on_help_value($wj="",$_j=""){return
on('mouseover','helpValueMouseover',$wj,$_j).on('mouseout','helpMouseout');}function
edit_form($Q,array$n,$I,$mm,$l='',$F='',$zl=''){$el=adminer()->tableName(table_status1($Q,true));page_header(($mm?lang(13):lang(14)),$l,array("select"=>array($Q,$el)),$el);adminer()->editRowPrint($Q,$n,$I,$mm,$F,$zl);if($I===false){echo"<p class='error'>".lang(15)."\n";return;}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";$Uc=false;$Qm=($mm&&!isset($_GET["select"])?where_columns($n):array());$Rb=(count($Qm)!=count($n));if(!$Rb)$Qm=array();if(!$n)echo"<p class='error'>".lang(16)."\n";else{echo"<table class='layout nowrap'".on('keydown','editingKeydown').">\n";$La=!$_POST;foreach($n
as$B=>$m){echo"<tr".($Qm[$B]?on('change','whereChange'):"")."><th>".adminer()->fieldName($m);$k=idx($_GET["set"],bracket_escape($B));if($k===null){$k=$m["default"];if($m["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$k,$xj))$k=$xj[1];if(JUSH=="sql"&&preg_match('~binary~',$m["type"]))$k=bin2hex($k);}$X=($I!==null?($m["type"]=="set"&&is_array($I[$B])?implode(",",$I[$B]):(is_bool($I[$B])?+$I[$B]:$I[$B])):(!$mm&&$m["auto_increment"]?"":(isset($_GET["select"])?false:$k)));if(!$_POST["save"]&&is_string($X))$X=adminer()->editVal($X,$m);if(($mm&&!isset($m["privileges"]["update"]))||$m["generated"])echo"<td class='function'><td>".select_value($X,'',$m,null);else{$Uc=true;$q=($_POST["save"]?idx($_POST["function"],bracket_escape($B),""):($mm&&preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?"now":($X===false?null:($X!==null?'':'NULL'))));if(!$_POST&&!$mm&&$X==$m["default"]&&preg_match('~^[\w.]+\(~',$X))$q="SQL";if(preg_match("~time~",$m["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$X)){$X="";$q="now";}if($m["type"]=="uuid"&&$X=="uuid()"){$X="";$q="uuid";}if($La!==false)$La=($m["auto_increment"]||$q=="now"||$q=="uuid"?null:true);input($m,$X,$q,$La,$mm);if($La)$La=false;}}if(!fields($Q)&&driver()->primary!="")echo"<tr>"."<th><input name='field_keys[]'".on('input','fieldChange').">"."<td class='function'>".html_select("field_funs[]",adminer()->editFunctions(array("null"=>isset($_GET["select"]))))."<td><input name='field_vals[]'>";echo"</table>\n";}echo"<p>\n";if($Uc){echo"<input type='submit' value='".lang(17)."'>\n";if(!isset($_GET["select"])&&$Rb){$_c=($Qm&&($l!=""||adminer()->error!="")?" disabled":"");echo"<input type='submit' name='insert' value='".($mm?lang(18):lang(19))."' title='Ctrl+Shift+Enter'$_c".($mm?on('click','ajaxForm',lang(20)):"").">\n";}}echo($mm?"<input type='submit' name='delete' value='".lang(21)."'".confirm().">\n":"");if(isset($_GET["select"]))hidden_fields(array("check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]));echo
input_hidden("referer",(isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"])),input_hidden("save",1),input_token(),"</form>\n";}function
repeat_pattern($Ei,$y){return
str_repeat("$Ei{0,65535}",$y/65535)."$Ei{0,".($y%65535)."}";}function
shorten_utf8($P,$y=80,$Uk=""){if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$y).")($)?)u",$P,$A))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$y).")($)?)",$P,$A);return(isset($A[2])?h($A[1]).$Uk:h(preg_replace('~\n[^\n]*\z~',"\n",$A[1]))."$Uk<i>…</i>");}function
icon($Qe,$B,$Pe,$Bl,$c=""){return"<button ".($B?"type='submit' name='$B'":"draggable='true' tabindex='-1'")." title='".h($Bl)."' class='icon icon-$Qe".($B?"":" jsonly")."'$c><span>$Pe</span></button>";}function
copy_icon(){$Ub=lang(22);return"<a href='' class='jsonly icon-copy' title='$Ub'><span>$Ub</span></a>";}if(isset($_GET["file"])){if(substr(VERSION,-4)!='-dev'){if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){header("HTTP/1.1 304 Not Modified");exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");}ini_set("zlib.output_compression",'1');if($_GET["file"]=="default.css"){header("Content-Type: text/css; charset=utf-8");echo
decompress_string('%c(ADg~Z9.uC>]~.3$i#V4&_?UqjkCbUi6!n7_^@%:>!T#8;}*:pbFpd%ydIC""!*lpu2Rd:XrOM1l~#m;h*tmNtn-K%;J/^Y*Q4P1@XxoU:<QWC~Ad
*wPUjMZVuxYpXE#]<&s3ePkKT#dKk1E:Ul2tyYT$30t^mt2IE._i,ba[]QH;&^G>XeCRnZ7F0S>T&;8AGU1t~%>-+Z0WfbKWl>U:ijN_z+h1*84v9?R/g<"kr`70Q,eAMX30u"wX8T$kB(|"|H0:,cYV2.iD~aXAjj1$~>,`?&Z9_<(!
qrT<(,_y%ATmH^FSdYHTXuUHpb2R3nhiJ=6B9zbpQiHwSWo:Fy
iA)+i]r,Z>=ERflWaj|1)rr"HHn)9CNWV<s
a8s*vQw0Q)
vcwcD.Wb5!3Tg9BKn%bJD*5Q5t[^6tIF=@(|pe],Vv1:0?fqkYe|lVr.Aj%xiFt!D.)?#3j$)/DW!"l{6LO&dBI5$(J+")2,j`tT8]n:oKf
gw:;1
Q5qe+(QGKmV5*nc<OAd+7atqW^^@"(^<HoB^&aPW20o.#;m#HB0b<Ml+"8bxP$)XLG8g5H*^0:Lik[-D"
^U]w?_&s)x6G=#;<k.VW8&kOm!*%NL"g+}OvS;Fa`c3RY]s}&8(oFCWd=@G,
O;)3?53PGPO#~0PHvP8u$S#sj
>=?FH8{R^MV.mJf8l53,&#z`fuue1wS[?1]bVxc_:<c&);2C0@,GJ"PkZ,y^`3M+qdTaLdM1to%O)Se:5oM[e5&^_10$Vdl<q[_7z<6rht[Qa`l[wHxmSBSA+ljgc-Qo,(j?)yk(g+o,H4NAm1h27wS2!b>LL@]vD^W$Q+I^`]TUND2tRJ7vqf!NW*=$iZhrakYxxyrcaey2pJ)h!T?`=<{-+tc*lC?&
#/28r(1}W56^E@-"WSdAB%^h
lU3Ne^/KQHrW^w<4`Tq7|5<2.J%;JSC3B?5;|-N$pL2Ad4FgH"[9!6{^|)uh3X-Vrec8<.kP4w#`i,^X"AkN!dLS`5tqW-&"q!Yp
Nh<uHt;6PFruK^TBkkQ#*8@Q57e-&Xa!Xk]u7zK6J8tfByUB
&aao]/B-|W-2,U)iHM]b=j6)r*,s4E:>j
t.^7_hzF9glrNKW83b38O<~MM0PT*r
Y6VFVGx:m6R.y!s[LbxY`eN~Pv(?f5[M4=DZPTr#7xA}B*c|N?2:8KrdfH#+3W%ghLi]ipdyn,r;%}lJn6z!NvU"*eiRc7,a-Qg@J{m@9j^m^d$r/q+K&Cj}:[J3;5Syu[.@-06A/BRwlm_kRi,!<yCM+}Fs*U3$^WZ(XdbjMYuY`{omV|0Q2U61UDIm0p&j0eB<=SUkE!$3b?E[il65<R(c95Tv&jGxE9R_RT
amR4HIml08>-"uN=WQ9E_;yLmOm[ZF66Smep_,*l(J
Y>Tv4P+zc=QCe"UiN+wGmd0*0tt1tUO>Eu<MU34>P<1C0M&
v+nG$Pv(-P$k
wyAI9;o=f>E!*CM<[J9<p+B%p8CvW0Q3;+5WgIek|r-/.sYNwRcZ$%0"e(HP^p|i"l&p@;E^*8B-QBJ>A9cVP2GhB>@Ge51w*m~t*jR^I-]Um#P`-:|[*WEl^WF4%@uyY1vB/s=Jqx|6]%_I+AssSXW@AwD&K/|k22qOLfSYul;%eNI+vM%$mK~W)&P>w
zIh$68;.DhX.`B!ER6qc:=eu5%B3,_W)
6Z4x%|!7dId@!klmyN<>YW1CTA6@y$6@hUke3R*_W4[t@U@63Eqh"6D2Uo?
igD;U]8.by@dH7D&IS,+
tbJxyqMfshY)9GrH)iw!#daveO9Emn#/H5-GGaFIZYT"l7dg4
KRA"pU)@P7{e1o)T9o"MiX*+)*$?{.C@H?HL$jFoNJDu_(mv~a
u|DlC~w$(lIam7(Wu}v#qf99k78,c"Q,C
8vJSHp#zKuO4V$A=e=]?k
s|&2&rTR9.s.j=hGEvJNQ*:pj/!sC`6iIUA3TEVVxtt$23lk%aa$,Fh;#~O:%<jaBcY;oem}HY
a#c>1ea!xwfj%r}qcfXA:cGp)l#_F9D!R8=E;Z3e,Rn8-,@?DG3&CKkJ~#b;6Xm9GP!%YYQ3,LS@S;~caSMRUah9DN1`|Z_SGtY(H/WCLWl#qv~rN+:2Buwnd$[-/g9s?e
8x30)ipvppEZjd!Qo1
@im7".`@bFSRtCXNRZ#sb*X>ebIyY!EvG[<AHH68gA./R[wQYq$mB=E#NI/D,Y
ma$^U:5o_)b=i=J?X4:|m0aZ&Xj=eIV.!&Y0q4!@EWyI/&54t,+}gug($I4Ue1yJGjdF&Z<KZGH/QDdM=cBBxQk}i>*RioRgnC2Bfe)KT9cPD,sQjuN0L}+Fo86D#qaT"B;<pg@~j.WA_zk;CHS@Zw*LooeOvBtY<!!}_S%$8Q@$_^yN/-])<0"GJYYCkMH`4:Gx3~P{1w&E2mD)aOV)q4UGo/1Tf3"<amM-8(4IY<2p[]"+Us%LFJMmtBa2Brg
K$rY
MKlhpcQ^J0&K
)iLuwAhTgST{,*RnH$82>)8<oPilf)]tXT/g8&iPqwp8!rw}M*o$?Kl=z%S}[,!;a9c
2<qn;eLbkZBr[iQ^gPA4[(LFHNf+Oa5j@)QnyVHID!BWrq^*>$z#$Fyc-"iGyemj]]gwwf/]lSlWBY^=w>`[/~Qjz!9vi>sez(1zq$x:*-=yc)9vy6^#o}B-xcWIIUsY=%J2v}E@o2xayE7g+x`;WD=~w($ReBw8ZYY5c@x=Hm;|9Fgn*yNNGm(EUYxFJ(Z>-F3n_ZcdX!y4e63XwYyzS<Lw8#78=Fx]x(ofczkT58EI^Oe4@-A<FzuXo
Cb34#mnvP
tj4gqYsvig<5F=!5?D0WjlK+tUurt$gBBETm,*qs7W?
ssg$kU6vF:y#e"r/74V.(r=(@chK:QrP^zW((c/6piLBbu4yuqKiXkSZPGK1WaD>J&"j0<D6rRhq6v?I&yfP44r-_bgbw:cv]4DQclj97;WnR)VEZ.aVu98vhw-JUPC]<zc1UoOJk-,DJh^j.o>olI0)hI8tZBvhk<C5/|b@`7(s7uR6Z&9UIZs4qn`6@xshWG)vSKsA]R3<md(Q,L0Tx=DR]Dn6fHM!3)?DV.ey!5iu+1%15=5&,oJwX-P@xaYipy?xr}erY$[wMj/Mq[S=81lYcJxCBMY%>Ou*B8F-z)Avrm^/A))Ppn4!?vpaUGd*U&Qj3icSwcXMa|"2`K>&@ulfVfwcW-;VB.lru1aB^aaCSb%gq7GJ5$O!3n&o^YZ41?a4WXCPNM.uU"(5cINwBp89Y2fm<L*c!gCNOS[$n
`Xg$mo!GKUstOr?_fj<JNDqn./vM:;i2tI5VM)gamg5dT:5
T,Bn`:i6,ja2_tyup2mnE%rmV;)g`R%Sg)nCkriSY!i=Qzv:FX"`dG*wcrpNQ0LvnM8)G`Hhow^g!L?~)(f60;H0PGA/e`u@<NI>xpcSMVGqX8h5i7?FYar2>nurEU9C!AWyu>gtBJ%!pUfRoH3?eFD<t9xN30ydnM+[7N-o#_RAcRBZwnw=:aM^6r647vLgaLr|*q).SR[+X@E^7
KoPI-<];#>)4f}V_>;yPnUO8hx0[wuIetH4aa=!q+1%B63>.Q-^=D,GpExCK_M]o1}=kyf$jvdssieP";-`:bBi59cQ_@2nD1XaDIs8_/[h*Dyc%]uUYk#KD-VcsI
HIL9cyM]`:F<R]u9Abfcm":ywB@LyN.Hx;x>He[-3,Vj63ICGH.rE0]ya"$gU%Guw]"`EHxW&I2^(Me:.3lsd*@{2dE"QO5;fF0C[JV^i2F79>KhdP,3D0y":USGZcd[&H?:]0"w66^)$3:;-z3x<XdA><diNURoF!VSl5N~8g4Y>Ye),ib`AO"r!f3[PH_cCmF#03bokoW&s+$wG.lFKd[DPMYg9r/9Iv
zh|44nP9p.B"S(@rI)@d[+84%.!nbG{!OpLI}D5Y(X][rc}bHbHsCuiq
3#JrIf0N@h;hMe
WHSBd?3i[6]"x-~;7Sb9>O+o/P;#}1jAk*Xx
a4<w:A[(W.9sUPj,2
l&U[QN#uFR"#$LlS
sKka1e+n~B8Se`o$G^B5Rv-Jj4pd])n+6;|`ze/AQ&|KnS)vle7s<SHbQK3u-G{f
*(skt"dh6dwxjZ+S!/7s7l66kH89oj_L4L1@.?hC4qR)WVl6mYla=C#@%-UM`P=lX=%K]q<gAw$1xSsDU,9U1OF}KRUvHJvHQqbtkVp^%Oufw-n.
uJ5.v)P8YWp%Bk13ZV(2PaIHa%AO2f"_r:hJ/!)_b6]ne^oEI_tWxlN`JxF"|ju(5"o5XpmmJVEK2Ht)lqD:DEi+%h#/>.}f}RMjq*0Tw?
a/qb>`=k6ttPI0tP');}elseif($_GET["file"]=="dark.css"){header("Content-Type: text/css; charset=utf-8");echo
decompress_string(')OsbOb3V?!K0U*,j#-$TY2N&[`b!>wsTd_N`GuxPN9GOol*1@VDLlh_fdc430fu#lZ-r!f<.+=s=X(J2e>*"$r2geZo4@leYjQ1%,Ya^fK)KWrns9HN3Za[M&Ua[o)7sBH/u8kXg}4drw:$n$88?$
q.DLTGX#<D1t"V<MYp_Ma&R!lNy=^42%5+QTJ"M_zEIVt2b&@<iW5HXxa7"+HENrVp[-(?;l^q7O9Hb]:Sr
,WOw[;eXJ3/AYxWiY8v=afr;mm
2j7~=*!Bp~Z"dLH|e`)gkNjaXDNCg,tOd/Bee9aAhUna-ZLB;OF8<%r2e1x*xX$ZiG_Ot<kzJ%FMb$)(Q`hL2F*U3b$cI[XzX_yVm!=X`6&,RA>7e!9gn|F:S?FGgzw]+AWONX6E]$Hu$5^-Av"t[SRPD-dDP9jn"tZoFsSBWi!U
]MxVmGbSp6ix~D-FZ7DoJXY/zE9!l0/]_ZhqV=[.*yn"zS|U3V:p0%cK5pT+2_?0*<"/w-9$DgzF7#yWi<W,3"4>QoJftal+Tm>(PeM9JHTs;vxkWm9$<A7*iHsBl8Ig]>qQ38jy4P@0/ej$G,X[`Y>gf_|8q*^2Dnu#YI<#>h+;DK|$/DDimVm(m`WCVEYX1jS%84q"FCpAaU/4Yf
Q<ovd>ujL>jlSK$ADUHDsn1a>o@
;@5f]$+ZQNcbu-^=v>xaijt5[sMndunEa-5T28EWI"G!j1uhd)s:ch9c-:STXv8Dq82x=D]meVP[+d`LIY+k0"G?9H47
NBubq<z`![Z&|@7?P6j_[UcU{fnW0X^j_=5(,s<ii_zJS27M>X{xnK3M[W-rsA0k}H{mrK*vZ2&pNC@DA0;NWwLj&)j-eg5PfwA;O70]r,58hd_Eqn{Y@Ws+We9XpZFh)z(-@LIrbPy8da(hAcZV#?1X}E7dx7tw`28WL.XVqgdV!&yvq?3hO5.EHdr-kP>4[llRl9i0C+sj[+"u^v6Y#jXxd');}elseif($_GET["file"]=="functions.js"){header("Content-Type: text/javascript; charset=utf-8");echo
decompress_string('!c4]`nsZ51ptW"tBs^f-cSgTKbI1q0pX
i/S4_e4Ka%hwTAlP"L=t/*"6#@B,7~w6rZfJ#*=>a&]lq/*4>5`Fb9?Co~=jE4u%
t>cAt1}e8q9_RbAy12;Ux.tJDbc6yo|Zprh>hKzjob77ssF?pG<hX*bEjltaV`9(zy^usH40zkMM"McY
V707_X9U3bx~+F$);@OHaH?v1dKl#9q/(/yZS4k`G+bI@r2!9xKyf"jZ5>aZ(M+$*$2}jpf{p)^3l5
iJJ3I5BPJ"xlyc^fCDHS+dK1m1+vCx_MA/Wit:6_]`iROGku0;72%UMS";wlDHcc}X0i{+
+g)G*ZA!w>x/?aKbq;g^FuV#II^?l0j,3G78`XOn?+m4]3/!%^BW:hU]J4c2k=
iJM.o$C0bE-4W0vJI+9^e</7r4vYiOd.y43jS[^7j^Cs1uas2a7$Br,@HQ}V,1"k0J?:CEruov`sKV<v<d0[kjnjqvyd#_myug`xQiVE*y58#J~ynv8y&7XEdGPy%=Q^U$KhDh]L>]c@kj]:csEC^q*P}c9Hn,ESzuz&+rPNW!Kl13$Y1x$tt7T6=SO!]:HZ|(b&7%W7Z/21lKTXL<vyfmwIvfh"K3LK1s<BhsXV`TVV?(^I0cqp>.BZ9O4Wt*71Dh>6mNM2@Cx
f"0Y@.#f%F_@18hgQW.E/-thnF=r<Bl,tOY,6r(Z^*wd)#b7W6N)3U/Q3YJT>,bmG
NXP[y
fUMe{b=`4xTonAX`E@mZen38{K^gk7#!7.i[kr6@;DSaX1yw<aWu<wf
ssStyJFSYnjHv3YP`;B)5->v;nh3Y!G.zvQ"B.}i8d{5+Coo/%|K=KbC$0%9YySBke(UMx^4)@d*6cV;_y.IX]#b]ba]UCbb_iCDR
_lUE:7z[RXYo:O=V6l+iojgjWJWO1
<twp+?^2gc?@i;<MFs_)dJ"j,ih!XljxfR-_RT"j<2H.PVtI5YKbwyPq5MDXHJ}
27#:m*n"hsdc$ndn+
2^T=77Z@.rhk3pJY`"Gx/p#ZW?K>=`wi^I2hDf;0cgXAK@9-ga67rr?;QSv8vS]Y}[UaR.cGkAjR<R*Oe
,627rtV3)XJfBG`^Kip;DhELu.+:Vo33x)#u}-cbKJE3WN$bb_`x`w@>RP"^*ykdhTHe2Vuk9!C/oJCcUd*+Vo&t/nGo(Nh-s_A&hO)w^<N,z#Cs^LauK0d#:NWdpmBwT)v>~XtywX*^8"dD>j;S^us=?L0)yTndN+UR05x-+jcr^]ZThkdLiTP7<nAxcrsahb}DY[&4|ZnAu7hTys0
b(}s{Bk-?30?{YmI}bzN
`q"IEsrfUE1ODTJHO8I>s%")3&s<U,%+.e/*9gXQC>?DGPg5k1^;iRD^l=.Ka3/gqtfqs4*KPiw/LRCbMRq>]/t!c:w/x^F]yuMB
F/1&t,t/<
>vmOJ;Q$[AF`{F6OIWPqW;`Y+uB2MgX<oL3E)Ie`ojg5zL<u?nTT_K=LgN?efssrtCuvmHd4u_ftW&`wNnB7_ggN1DpxT]mI
kk/:72=%GgC%av7fY#00oymXT-JAwbgK4."9E:&|e@:4iQ^@)+S7?$CLlI7D!/gdMOZ4o,<z:[IT/`v^>pv7)rG_dxKMD^NxvDfv-:LG`$k`)gWXeP[n^-Yht~7cq<SzjqrRTRFFY@&(3&XD
NqOJC8)ZLu%PXwG#])x1Y1D%Of8gU;S5txN?plO&A7Dv`0nTu0P]awFPmaDiG?i:TgJLAbdb.k+P[>EOE+j6]:LE{ZxQ8u9
B$63P@,MdTS.wsVPl4r#
X|yT03YF[0M9I-?Mc>`VPy`2.kxRPyd7Hav9ymiLP`I2E1Kqu0/e$OUIwI!}2GpHiMVp*riC4T&DC*D@pI19J/Vk_IJqm
EeaocVyB/p
#Ex;fA+Lj]4m(+{c&&g*Y2L<T7:da81X&78mf_+rXom[IBnc*l4[_I=MR3u*}^=BQ6{B>R2.@XuL=rgo#_{ny+"HRB1;"tvC;S%ki3|CQM3ojRyq[Z!psqn
!JiK.$B!8v(9bF|X6e_PB)mf1=%9ClZOvJ+A@*7%<.W7"xH@U/RH]!a0`/{t]ePk2%8sS:R<*>|:|t_kNoaa,En.DlX<|7##~8@!mv40MNPn.r(+9atk|^jJ^Of9I.eJ:pmIIgI_$7TyXqdSQQ)<1e@SGFe]"+JQMHc?I23j,SY!>,a$9mq"&"$>w6~*@3@01q:QI.F*)[#Fie?sn;OU5g;i-n@g4?m-(nQu>,Am/$^1n7I<iPV<nP0kp(EK~ZG[KQ"+,T):.hkG)@*k@ljU
nzMlEdN3EMcGg%PxGw;Z>e^vu0rE%?(LC5$9<pA:Mwqp_]"e[*!C]CqxtZG2$hp&Q0CN2xx9:Yc}2Kt=+/]wlt!NV!/zj:dyBNj)
Gv0j;oM8O]ra*hDVs*>krd>CSMpQY<1H^S`iw?-Ny#=J6T+P<j/i}#eg"e`N6E;oH$SPr]6h>@sZ{tKK*uh)*If({YONDiFvQL;8!tX]i;48Re;>=;C;u/}5QJD:L&e+=u#uXe&w_OeDQ2$2Ujl>lWJu48`"[MaolYT!3VDJJ:h-5OiQvN"t"y3O38=7xtWk&&9%+v_NU>J]P1o5s?!q+c}BM?,eF(xPX5`dN%s$_0B$A4+BGeJU_#5nbg?
giRqFUeTU7r,5c1&Mu4A6?_qqMuEz1t%Z"WkggbTTs+6Dn4u<ga9u[{Ouu.q@(1T@_rBh<n%2Z]*wuH5$#-e#YcjBg{:Z)
ozqqCyZL"x6@=LJLK="kft8E%Cx!/3_D,]v>:J36079wA)8-eW2;wzo-t{rUn>yGokL#ZmG&+c2kqIToBU?M24.
-0Rb,J"sw&$uw}6i#M+pFM>4J(L787N;
/b;8bwR=Hp*H`Bw01@UHIuG%w%>S;Vi?)7YmE`Z2@ebd2.7!wl9-^=MQm`(/QPZP,`<5B$?Nv74U^="3|.F)1Zy9ykVT1P*^t^X.i?,-tiE>,e|u>=8+Lrb!#OFs<V_WPqsq#Y8NS*M+g0`JM1Q`nSZ@">H<_
`!Lov@ZACjI?sYLXSS33<(N</),*XvGjUT
^?ZDIWgTv9@*=W@il1ddlS+2
g!u?If`25_4(!Ap:"F=S{lyPJS!1SZ@->lqnFBjX(2mFY`#q47awxv-E>f18y^Q-
]Wh[&k;i<=xh.,lfb}UxiP0C^l9eH<!B:+HxAS&N`R4!T8&21KEuv~sG
y]oo~
#l&sCnsuPv`Keq%rYGHI|Vw$B%cKLcVZYJj!"Vq10M7%[3D.4CEv?8R;^.Bgnu8_+${iZOn/w9)9Q=]*K$|&M2G]ic7pZ@YVqSI6^-5E9(L;~9+D|#0`Rb|8TCcVS
d%UK7B_VhyhmN%F(sUab#Ha@x7XOV?f%KejH7!+JhYJBV3B*x=6No"YSR?/+,Y9em4!C3*Z!LsSnxs
?:UWx=L98EBf"-Z*5h&Dad`d;W#w^[:C:jpntu!,3c!EAdr?wBNiq_:P8zeWknpS:p*9cjE*I<<p+0UtRyW|])*):_mFJYA(?QK|e8G,.g8Q;*kNoa]
B>Bo>@.IBBo
>o;EI@7O,s4,2Z%T9[[%/;=IG;kFWov{W]P%_M3tI;X_`u.m9lq"W"8~o2RaD-Bv5]f1)q.3O{DqGRfEF(u?yd".Z!myVL;.UpHuI/rlg6-d-DbE)`#p$N*V3BniH0f9-nZLs87Uh.[deHiPGY2PM{TPm|&Phwl?EuXa4WuBQ4Fhg+VQJG%@V8JH6h?PXf(VB9a~@pPWgl!.oCrwoP"O5y?qiU@CrSg@^!e:KmvT"W4)::e,I+7E4Am]Gu6*V0S[;k0v1_ZrM_mO<@g5`>Fu_sP4Nl.]x4l}-X%H2Rhkoj<2A9dKwQM&15=,s+f%E|GT!O&/aA`:Em^mas=BAkV(nU8E<}n*j$tD%mPS:s&CMA0VrdF:2h*,kzMZiG+9!b:@X>HiFt9}X1c4tp]vLX@=5E
62/We2+*b)2fJ-dX:uY-+:hpmhr`uWF2:;jJ0V"-`*!67*4`Y?Cj6rkhHVs$`AV?RV2Skr:<!O*+0*CG(%<<5W8UDmZT/)j*zB&^#a6[!tAqdd$:=ufG~3
_aRvE"Wi^Pwz*"8F#BM:^;=3CF<[m/gSO-AEPBpO;o6]AzkK3q,8t[Nx/b%su=#P^zyJ3>gJAq`=o)6n&A#NPCV6&00Vi./WcN"dv:2xXP0aI&Hp7IT`lz`qZSf8x2oJX1LN[l,"$eO.;++?c!2804Nhg-"G$,`b-{$7CU@6PLcX`<YwlIrr5&0.^ly`"9dH2_px1M6w0r3cpp6TX&N10`b}*HZ"uD$T!Tws.I5sjBtNEtMn+R+o(N3_ewgn)A(@/.;J/iQDjt9Y]LEF2M*=^Mo)n-=-L@whsdAZvAg+3%+&]A=!Ckw}!un-]-b3A8dX4|jK+vCx(ACR7
)Kq]HP&cVU%=vK=vWt-/rpJWGA*ENv_hQj0"nT1SkH&l$T4:D*wekT>,*^cmLsoHg(G~dwLZ&vF|beXh4ASK";eV2tS0t+rs"T`7_1(`xmx0iHx73n"kT~;Kac+|hA4kQ?wg_xdX@>>BZph+;?Icf9BYf.W!N(P=qL>PPmZDl,Xy*<SsDQA1*SdwoIq2YN&#PHixZ=.TJr
vh$Q=F:*weX)
gb:!n*,QT^1tC{`3:5/1exF5
pM=-lNw1Vpuu-uqol8tg!2!PLQ#uHV`J?o(ue?_O-03JVg#s#cV/vuiwGun%:0acmf4/mP:ol@d"Ia<Rg)DZuV&jyw):)@mP8Dp)#W$yQ
iI34
^W@)`uP[uI)ivMoXwR+nugY2f!Irr?G!g,E.H6orN|P1[j5LTTAlluoYyyfzL<#aZXt:a|nK>MF8tuhaod.F7R0G2Xb}4]
$=F[GIMM;a2:iKg]fwWt?/@wZsJW"sZ&9PhsL%b+cgVG.d"n+g=4`jMUoC/?u6
VSk{,>ksAyX:M4^K1JI{!c#rtgPyniNlLvb@C}j^/z:n!k6Y0)Oqv}&&X?I;5dk7kgy:@7USe`,0pUL6Z[6U0k=k640d?<QXuYU"B&w6&B$-I
1@(
RI;/XSoSd
2Xq!G/t]_JEl,F7,V5xA?T?sK/v6"e9$UEq[;ohCe)jQG;RHEV;i?V70Gjx+lfaN;#YwJ?^}*adDBCRacXwE#Xu=u!k)gt!r%@w>B9^9w8,NC$s3Ba-)2u*HhE$vGv"aJ9+{ja02#GQ!b9NDaENzYY#X1{Knsm+Hs2H[;:dD$.)di|Ivti=Lv[Q=GJEE^MF$GCF"*vuk:y`[u9#W>BSVj=M)&KA,W#30"^94Ih8nFgdJT=NJP5Q{U{d9f7d4=KXZEDX
E_e%iZDT1AkWfX@K0"<nTFw<?}9.n>h{Re^&U~PaCMWvvj,h
hmr<^-8U72J<EDhkn;26_b?xwVDR1n#dD`8ZY.]a}G*F_^xtc4
D!2rn-1MZT<*D(8aZcb/pu^zGq(a/S=a)S6qC;FWOa=]alyeN?$;>3q5XVKkHIX,/2a{iaUG@tFvAPAoRe%/@2:q1gqY4`Qt;O:eK/_VC{C},?fjZ>
84)8!R,3A*ACG(C`r;W!HIO1Kf-EuDc%h9pYY0^v!Py^}GVti*uR%6@aM#qXfWEdC0mE[nZ_5,JA`F1!}dT!*$E!]94>b?Nb"t*SxPGlj]|FqHjnVJm5K3lFVGV)L_*${-iq4)kg{W#T[6A!k#phA6@P,pZD>QqfH!s8Z`*$7MJjcb$Q=QTvT*>K("Kt~@#goS>_5/7)7GXq4Bh;9G|CN9|1a7WH_By2L<&$>UdiZ
3fE3,u!iQDSl4T-8cI*71@laZjsnF6=$lqUeac4>L<kbn_4C|N5[{Eqo^W{>p3`-e(MYkcG@;*wxBOsaV:z+dGjrnEK>Ecq#9GUx:5l,@$vpQg6N>aLGnyo,yHB#<s6qN,3T*7HMHO`a5yXJ=S@9K2KkZ2-5|wmo<N8Kpn;+Hx+LB26@l+Uhg6}w|Q:Q<J~#<YR:{E4sHK9)=A4"5rr1t/]*EdU^TLtyAwXrsRABLjP(Z5N9Sio+f6%3_mBJGTevT5G-/w&Y2W/A@D"q%1LHfYU^k0TJ9R#H>s@<&]&Tz$K.IP})aN7p9E2W]TBJhhP&=BVh{gFK.-St_)
Z2J[%N@
c%Duy<PTbL.2D1
^$sfEi"SF^~br]]/*wk0V:%-hy4<5+PsxefHVCFJ@,,;=jP7}g7A*;F5mR^wLP4R_0"):+r;42o[+9lI1EAHO#~8UKU8=O+"05Pp_=sl+Y-N0GPEy<?PMXbCRlX]C(Umom:puI5$2Y0upVj-5lD,j!3
0Z{QFekr/WZsDg;v)L0(X,8oo+{fUru#|g6g+[,cd-A$nA!JEVFT5SzJb&jywVrev:,51L)%?Wt3b6!
P"Ng0sWxK)3/TR|?vX`eC;XjBaU8x,>@1#r%?!#&v&w!980[nmn!W4xI$%},5!YsOr<j#Pu;wu0s^0t](t@y
?1GTKyGd;s.fMzac
GCb2IW(sLV
b,G5_J/Cp>
?$=8T[APojU>G7&^p-GY*O8>&gxJ!Tm/52Tr~^OxDy=9qihGR)/a~C?+`c6bJ(A2+,P9ir^jtc]F}@;o!aV3!j~=0^"u4lPr|ZWyr6.,l,QlGk%]94Lt&uoqHY
mdVEbF,{6!)i9xO]*c,h%BOK3P":,Mw.*^17-nQ!TU^9#hB(5lr(;*t7Yp4l>I/N.#=1:qJCfQ$7AwYdE<M!,qXc*1>JN"AiHRA
2Ld~?;NxbU&oi{l;0WIxKPwtc6)&%r+LEZ
eHkn30z^.avoQN2sSxGIh-SZ7E&Zs0AXgF|jZKFE2kx[[TOKrlrZhOa)|pL)!tIU=kQIR$VW/[-Z5B-j&+XaAxRO^H+
$JH
,a^h/FB>ehD@F[^9fvN"&.cpt0AmIPaGO`KOng#WabMvJ<r*NATf%wNL|n)qtZI
TD]Q4*Ww+7Al%tbS+wB?o-!.e)hqGpxYxG@P|jLKU
~-e%tNgR#AC]aqhf+u@.W7imBW)/VPc/sB5!&MF_R@0ito65
7@kZuJ*[:<0zRD>)c1Hem#1ZL&]&MN7q"mTmQ-&Q(+UA"wT9F9yA=rgtAv7[S~[M@3#)G0)d;KXYCaH1N?Fwuz46QV
(^*XA1Es^OST1(aU<#
4aUsuJTe3~kpX2`_HrDRL7r2),A$mohB=2sC(d<lW.;|PCQHrF@E1]7IivK)_Obd!5f<uNq3aZ&urmetKsNtyHecc%@;jD5`;%p4k+dl;S0y=udJQ*ivvxK~+qN_NP?pE7m}lw1i;%dnN*^aWf
$12uX%sf@F)t[U?+c#bO+ddeg>b0dc;D^y^s_^|Ug-?Kv8qqbh%pCw]$*lGtsr1^+LP=(8dDKn+?Qc!E)4|;B8Q+m<6uE&j]#%v[oyNpXTbdI"V,0mXB4@A9`Z?Amn<]G9~kF/kg,!@KQc2Me6:g}xPU,L}BuLoJHw2RdE6WL2/4W5o5Gdij:*fG,%zQ39&^:kw?o&3DQKX/
x?"^s
<n*%-x;11!@UG#RH8(76o"u,#dZb&3kaUAUtC};U,}h-&d03E3EMYDkfwy7(/phD(`e%gyiDc0Ls1eKGRG^I^3eN2&r?/oQm*Uj"uYNMY3$?@zACd~gX]H@J*Aa2Tk6S,2&ub)fBtkPWI?S*4,DGCbR?gQe}nA0nRmW((O5f99ty@{74^3F{`4%ev08Q2%uxEJ_(Bai$JO@WPwT%)q2"d.
oL3Edd}JUeh[r]ZXw8io(oj_/E.EKnLYes,lWkz2!qo!(r`^1WEK8$0AV)Xf[,r!T9bx#m4fGYx[>[BucXG4rLS!3Av`j^!X}_%uqa`QwQCcXZDwP2GF^
#3=Q
T[>FlXF{i|UOg]+RSiVUf%h"0y9LR?BG`z2xiJ65=JcU`itxogvx_/d@T(wKeBj5mF2X)d53OUQ`@=qJHpVF]qkC
%yR`BEF8<#%oyl9ErJKVW1z;RSju=.WT0

KPn-g+c4cKnyyBL}yEyoui`SH&1D3+OA[S0Wj6"HEL"evBx:F>cU=
ofn_8,]Cc`^ge7#,fSvj0%"7X*EkWzpJu7JETGt,yzs93u!2>2m5L+vp1e7|JYP8,C2gvz]lbvdu47a.Ki]h^o/f!X/K(P,2PwQp4ySL:RObaNNi<3,R*8ry)f9[ffo>"C)@./oK$o`/eD
-B0MBG*(9q7!u2-HrB,;ftR8t*U:|#@&*iNDSD:
PV/tW9`i:,w-dJLSCp;SH_&x5/4.8RERA`o`l3pWT^_bMps#e:xT;0ndw-;Aa,&f,Ehm[OwIr!qe$,ap=;"ezcZ=M)gQrq"XN%HBtn|s#oX2xf!yW]J>+U0Kb%NcwW|l:W>:If>2=`wp=i
^F2t8tg;Kd$4"YjnTipgr_8Z$
gR@
Sp3<RhC<GRoZRbilYaMor.2&o[cEv94^u2B%];`yL(QYbz#G$F.sb;C5@sidjfD)$SDV0j/LcP#7fpOB__hT:S%j(k?>V$5|u+e"&~#Jrsn!lO6^#qq!rZ&dl|<_3FH:/4>r-NNl
moY+U(eStRsfvLH9o
UN
/v3U/s?4WFw+k)ceHzIdkie7Vq;jj__o<LqlyH!|#
hNJ<:!Vk3b5F8=Rz7B9Yh#mW:7"|4:bKUF%.OFV_y-%uX7^&g*b`D8^u4WK!=%RfB%J!"L33`b3,f2o
??`6*1RCY@m}i*rb^8LTtw@?F~i%cegw"9<7KAUVph3=u@
/E_dxlJVT/HO0%SbK2{O%1i3Zb<q)r4CM=MpQ$6NV*(ao%w#9cGh{-h4.NnT[8+
]G7<gv#8MaMeE0fdRLvqkT8d2RuY)7*_&ctbn8xfUYx*g>4l*lCAG0OL0HY5!-",B1hy]1">q#RUU)UR@vytZr~4webt"PthTZ2_HYunw@.vQD8U9)9n50KdoE"N%C#&MC]/l-S;k`@tH%16)pt3w.}3@_%>.^<MQOC"a[OA_%EGzIe=Te^g{VWj*xv-kutVxieq&JU1<Ajf*mp<Lc~<nFKm[Cj8h=#o{+%.%@tZ"Y*&x&xc8pb@DF{GLg.+}62OXd;TNicAgq9vO[B#NmpH{](xTs24RDTO6rD7n[r,:,k.p;sQq5xDm%B1jGf,8tqs4W0js<4i"QJYm(v96`-vYSLi_ToaR&]vQa-&-C%Pv6IAed!O%g~&;F3nRxwCC##h8w]%eF[AFUg:`jbE6xT[5xv4,Rn5Cf|t|Vi<;F?"|AmkLAC_ZJIZNMIe1(:/nNcpJtW#T2q;nvg9L+bh
(5q8;jF+rp)r35,3T@)x`M#eVnU[25y"V`i,rS&V23<|rkC.!,$jmx8:Z-$AQz6zLiFTOJ5K(0iGQ:Opt-HG2xW6.lT?U!#miHWRCP8GRqtT!DpbI3/ehvIn+_cLG6AG%dj"gy9x@*n:?lC<9tGHWG$opdl0^vPw$pM1azA-V5cB-fyUE#]a"p8H
~.^mJ/?53076Bn!":]5Jo$L<32MLnUG_2s=JtgDg|(M3bsFcn>=SmJ~jUH*t*[`scF*Gy6SH`j2]`G0/q(x@-o?Ud`Yo2oHwYi6?(Ni%W/"]J:G-xnH;IkI=O;|rG<UBi/UHElAxlBqrl%wBbiZ;sK;3G37Bwwm*nngSV/d/<m(&SiGWA_VAJ<;"tV=+=73)+ASGa#hws"$qM+RD"RUm&uK",S`G6ZusED@gu1|&q!Hl4G(#+,`Rp/uHE#Jq>T-%NjhjRxvbo_<**@qgWmiaabTTF!wjvAGS?T*6sdFZSGWc*ntB#jo#AF[3f50G|E<hzq0I25v8.1!%GIwJD8ZD`.4H1UPo,Z9b+F;V^H|YtvEH"<=I,Hy8a5nb".4vxGvO?&>HpvTpN5NWq.9-Z([lZDDm!Vptk<<yX`^qJ%e@g8vAUH"a)FteMjvO+=;B9xg7D5/!!<Ak!7KQYn7La5>*<B3TH&,8jNqQ"uRNl)+t=vg5)1p]-6EZbp*B2`|CRMkw+[`LUrYr#p@@)mxg73cfEkqA2@qA8c{_EH(T+yKT!Wu3ZEtj-fqT^;z#2]L+V^![(
6`fwJf2d6Ocu9S5%1YnTx4^TlE4)$Q3ciGU*fy5oc3|)_%4WE!Ft+2Za+K1B%9OAoD^/jk*"~Aij|0n9huBIyav=}299%qC&5-(G_0}eB`Bo&OPm,Vlf;mRj(_nw!!h[QKd&d`TY>mEWq?9&l"x)k-huiQ5(TOBT>N3I$m).x8eM:2y!c9H-d)(k@9r#vC,nG,>qpcz[j:9+
TY6(`kvX,,<qC
yG?+:cK~fi=/f~!)Tr3582HXPSFh1IBV`*v)i*llLxa;I+:.fJF/Ij&]!?Fl,*LY=ocA-Sg*BI[^x#8s[D!F&csW38-vTQxFhZ
NV&fNF)<QV]U{oj&DJ_YRKIscEk%$3B3u9H?2lYvP`bh8G0p?wY4F%:K6QJ/4:&8qY#Y7nkMN)<S2NLu$$WHa)p
CJBDh0#:U]OF2phF2-jw"PRMtK/sw5?w^(y!:L-J$m1U$8Dy=SC4uYxR7eLUF%ZeN$3Z1Jk9g
sJL*"c`&"i-dw8b6$jHYq_BViUQdC"{ktBC78/pECY8h!2G`(5JNH!ebIh-aKA,?g?
YALkkF&CdV!6=m$3rf"UtRu1d+Ii/}hdj7M?3SyfHGz#p!cqcb`WHnEl#YjcVV5&eFc6qgPOV~enMPZr4z*F%?KBqoxZXh41>9YU4VqO=zvyIJsU)DYOK?yltu,V*P>VQz)_o9@@Z0/s0Q0B
[5%iTE7tp)y-*h_q@G(2lj#*0c&SrW<GH]*4mgxmmp[+<@:@s3h
CjR$2jyEFs=xn"=Cl]]D:LQ6+KQ#!H1HeKzs0&yO*M|bij]J5v*fuOp*NyTN2B^72_a+p=Lyuc!mUsu6R.sXEn&lM${lquO(;GVOYOvQI3^fAfO>zjT8cd#*&jA.L^OL&.=Q$ly*RYnCGXiRm3(s)h<2}RWL#(or$T+Vk*a2V
Y`G<b]4@&%a-e9*D>8rdi)!:eZ#,PI4t{<(>1^VEG]GpU`nq{sCA4@;xkGL
=FKplrQKYbIiVh{3]3v^Sn3^lfwn#]~uZp)3Av}3F)WX|tv%=7wpR)/xl.AuvdsI`mBF?u`]I^e
KQq5XNKRh2m-K,Bv8^cs)cy@HUs;ADJqi&G
**?J5f/9%#!cPbv9UUZ0rih-V#E@6<kM)7AGAL^>!^gqRpweq)&1harDui
?*B#8r)$5+l+b,qdqv
_uRy8iJqu:/8lB~DC1DHMESYBN1&S:>lncM50LN7KJwFoZsCUIK8<HveDcb@CYu5:3VYENzbstWK/o<,m1iqU]&w.iS^(qCFz,{ydWtTj<}6jX>e0u,fR+LX&u:
g[l.7yF(-5/IUR7_[e3B:
i_C"Gx<kyXUUmam
.yWyu
n?C5"o:jf#fe^+|Z.)PvR.|hH)k,28YRfQ979x79}k3-&$niBo
%?bRD>AXNd7?.hTPIChIg]I],*y).=
=gmI4Yi?GAUP1R,(?43F&;,yys.IU
.S$MyM9:>aD)lbosfViK^T;s;#w5NaTUXMa&d/Qs&B1-l0*UEbxA@sXl.JMD}2M>h&2<[xEpj<7-`--WJuDafS3dSn)bi;j=-$1t`8i,LDZa@.$NyB6AS^`.%k]32CG1,4x]Jt@fj=ucYcCE!bM"p7tc&GvI}8|5O1p`ZdJcz/.L4aW>0pu[Vq2<QrL<,Qj,e;h;+Z+!BvDF+1AoliK:0)0@lCu@O^c"@mECe
J41oV[0%1t_H3MZa$wWhv-_$*VAq]l_@;2fnf^bo/J[_-*#J;"HD+W`CilZu_MSdUaIL9YMdZk)n<Z-u2x8v7"XtS`VcAO?1>vNPCohxlP)&x+kj#38EpA4l?U=[Wl%T))YKD/?a06kQ}IgrH.}g8G.#&#&O]HLYS(Zg3:Q>)OTe=rz5;?Sj^.RFAxM:v8,(@#e[jb|9KfH?I,-[H3EX}5}5WX
"7N-Cq1g3u61&>,$IPAUrb!rG+"stMuM-(<3(soNvR.`,76J09Ux*4xy4`U{bs;k$!rf`WB@(M.Uy+B]@R<5<$o/>PM0^N13X7F4k
PwWK0{iftDmh7>w**S_#FQcM"=*5T;wpdRK^6E,r-q_[,"qVv}Xsu~!h)3$<lEm,0&IF=2k.%fvV4>W*0sO&)!d=(YX7j?9|E"pyoTITFTXe$y/_>8#:w2,0v)?Jga;J6Jm),;,C95c;a&e$$=wN6qDZA@d:1[=G1F/{x=e9$|Hq`DT45wIu_Kh_GRUOYUJt6<C5o8iN%2rY`yc3_x8pE]CCh%L=^1Nf4%.5qCEQ#9R=eK[kIVp&W_fV611~BmO~VnsB,Wd#/Z-,pb_+XYiaVG$J3$oOi1d8n].I$-tqS8"hYg[&$555[F))UPhiUIh.$+5&M<$N%E1uYTjzUKP_Jd=HKeq,5)82/0K%e.To9m0c(
E^V}8E?oxfu9O4N)jP,lNCeW]#)2oCnPFP[TT2MWczDuqR=wx&%)9(Dzk:c!Gc:6x$qD=M#)6d`ey{uryfHG
l<E,fnzNOW_"Zidi?cE<p9Z@sGU%-d0^bD>PJwD[6,F(hsCv1(eAsb:
@Q4-HS)
3
e!7uH7;
.h2QarQ+$VH%j#oTCG|-B#H]s1*By`&!h
8&$x~(?8hrmJKAa%g"?D"TtV#1hI/y,FzK#q)m"p?I$GDR-nf)RQKDT"$*AjASN^d*2CBp&*ak(xqah0HN
DayE.3d"":
;Ds9QU1"3oFZNaD*j&-#^7L1mi6@gaR#AMTe71,BcD/-U*>*1/=vEty:%u^.Yr4#glVETpH5hHRs8$i;Y<j3g/UP7WoR{TQ*#GT^f<m*n9Pk5JhaP;j2nLt;QKdN@;UW:4f-sjgkgtR1HJCZ86JY(w(RF6>AQWu-bP0m-piN0>i7-_+g^q5:3g,@+6Q_<y
>4PrhLZS2YfOw"7_NM=oyd1.6*ha.vE^Tr-9m4q@m^DGOLGt_HqZ3k]VZE,Z%i8)p3E2:p<vaydR#[>{Heo$"(tbV!7D3k<M2by11cF!G:uTclb0rE5:;<Aik
Ck6O_<Ek28`&IQ/GWig3])7MPdZ(U^nR5OhcpLHWC/DRC;EVK"-(q7-d3%"5+*[L7)?J-fKXEGOJ>DfNJk_TgaP=Ti8/(ZJ`")bA#@-_TE-|3RONc%QR*x
qh.>;CU*uP<Rhv!o9RE@?9KTnK`jBiwr;x&1x#=suMZ
ynF.l#IP2ZJ5r]z:v?Wx{=`(fFC$!e^PC4$-02r+},~^WRHKs(.::<Muo`z
]SS082^.SH|#?O@M!3k>}q1<pAVm?PT"qS7fP<0p7Mc+*LT/jFV*zd1FW#CpZ/l-`PN;Y5sb2_W;k2Rb(+63vP|M`dr&>tpb1DHwe-eZ[d^Hm7Fl2UVGOZ0MX!]&4&@3qwY5dUaTz[e1~f~%JY#:5gLiq"`Uy8X<r<jjOZ.dOmy2=*vTS0reMr
5ZW93+$+03`:wE5ZVp($!XjZ[l4&hlkW%(b{R`8pO#ZT9J`E.b;(`8(8p[n6kFsxt92IT+7;8KL0"L(CG%RWg#s[56El8p4|C`KAHll;/;Jp<7F{B>qO;LJ]&M+Mk,AY8_XAxPa<2sER#yq~
(WX@#G)C?,8TN;&7laPp[liP(gmxlL7Bl&la76E]t!sq.[*69h|2&n5chW?u`3t;k;W+h7|ce');}elseif($_GET["file"]=="jush.js"){header("Content-Type: text/javascript; charset=utf-8");echo
decompress_string('#hs]`sBZDp8)6qiyhhM<^0T`)V{Vi)bSi73Q"1]=gN@TnLndr=EGb.Twz:GZvaV_xn}")>FPe<yTG,]y^DyG!Y`Q_ZK8x0PLAuOs4BvxCt&omYT@dVVnR7ut:Z|S#rmd>1ikKvQ[fuxb(G/XYCym)Mbvf<F2auZBgncdgbimCs2uH/G`S!/dI;,pSp]S!H6q,=Iv=Pj49kYR-v=Myr9v5oBTWV$][tU[y
17lRiBE4WY$X5=MrFt56kyx3aAjsl@UP!u79&G8tWg1!NvinMom-x:!qIG|O:ZF8<
"h
W*U$/XK!y=e%ZuoCqN#+X=M>kRA+dx#CG$;}TPb9ZB3&-d%]"lP&#DK{y{a47gU}%2?d"NvAoVTF$Z@03|h.C6H{Wy7x1:5Cp97vHBi6&(`?$%WF`7;"i,nby0Wx^2B1bK2"?W]UEbk{F3icqi6pX2P]GZs_/4XT[bLO[G6YT5,uxo)UJM
"A|lvQ3!.Iv,Eb}^J/2W,y9*OsT:(L48qG>D0PF
T4a@{dAuITE5(LQI42&>L`>yLUf;y=6wpIvyfLl-C
=ZCwpGP0k9l#ytvngwK
GWi
~l5U$h]kXqau7%=wnYF+x
-=7g}YxN$1FW~Bm
M[,p{AedZIl@64-C$KkZ@Vej`&")lwG]1]QwI#(x7C/=u?P>[C$DIJ]weH[wMp[i%7[vzqK?[J*]@8[=*6HKi)BYG0m6?aZ;xvmXMx#lWbL[KiTMWK4eRIS^Be0?gQ$[;?s`)c@=H!c?O_jl{a*crOTm2vQq=j%VzW+nV4HVrF";<p+4yIzqrBI!EGC1:EF`Zy67FIuttM<?//@ZL_5QIm$430(y5y|db#nhD+wSd!%GW!s!:1YItAi1.-iJqFlco2F]&flFtR-`[ys.0Adx3uNyc>;M12<GT(|5iUQ,WyvU}p+C;c1V%[cSS]g
N)LjDK1Zzs~B}z)edQjh/MLKI3LJi^C2;!4hjvtN_uy.B7jX8sHbvug>q/{8s;C;
`t.E3Fc"BWvOE*k(r9[l
?+ZMltGi>tYP?IbKkP*hkJTCjNlxDGlHk%-lXX3(s9kkUqn;!aY3xm2V%UhBSk
[|k6%sy9H$F7t,w0f,X5J_`mI*]JTY*d7@;Ycge}q+u82:nxO-loR:fg!kuJIl%5Y^*C=XJdmgZ&9YL7l?LXG_2$GRy<b=aW1To!(^#^
2-VF^sqL>Lw.48l,!]>cDQe!banMZ,Z[0tz<+ECrraPR?WuUh5)HP.V){.yRdm*v8l>3@s&_c^SdI_A9Vj{W6+lOV,P`JfHyusbn|=1[@%s_?E=lgA?cs7|8;a>k3C{KXA
o@G,MAg8ikRhv@Ybw]=z<9x$(W-UsP[Bg|1NYDo&gI2UtDkJ7,>$`-!5x+gFLeZ}n~ZW0
.2j_UvEB8|MD[<?QO|kN!"uU`W6o[WN)b%X3vmg!2@;/`+G)bMHHSJFX+
xs)1KdgtJ8ih9/Fv;ncm&ainrY7jy|hHqhEFu>T@Pa){Li&=OPW`HYGy0j"5fo3-nfr`5)C1C`%&0&x!DI
0lDXL+|EKHiQ>^eU0ZBEc9~5hqCs:`;;n4(wu$]iN?ASy=(.r$ua?;_BQpnjCw""PXXZgn|92+LLQ%z]8^Nu:da[t]<L]_m-7(xSl-sps>^ba^#_@UJAIbH^z&lK&8Q_VQ]p3h9@Uf)Ce%WQjVJXjaG&XIx4|)epRr0sO8J
I=Yl}q4rdycq`F([h]0wgmW<%U1/~>e+=li[bi_=<Jqy`[r)[@XA1>3y4*2*Vfjci!R?t950JUydJ"ew
E;95*`Se9Y=t/*:qSTug$#h{$md[H+g;XF8C04l7dq1^[fMJ4TAnqy&$/k5iX(jx_P(eq$F/n4dt`>K4X)0G<69B?wgrCq^%&DU@+^]q/2na2kVo^N+|9)f>32L*t4KKeRPm2hiO(
[6=Hl/_"usvVy%6mj0>U0Ha:2KUS]_VUcRxB%);43:V0EuF$ClGd7&o?l!:.o#EAVh(Ov-tv
ARZr#x:
/4`5T-`#8c`vtQ?Hg.B<
P?JY-"S2`sIjBUye+-a~3`!EA"9S?6BfV&m
0feZDTT@#u.hH;L(DJB5sF
YZ!e[yA3WeL+/ee98lDA-;m=GoBrn%N*6_v9.oHj9Pi3~pzwZBa(FQo1xDXE/yIgTc%LA3d^bTL.N+vB8Cyk;vI<#lLaUwDyS[S<HN2;F>W,aV*%qk#m=Zd9IwDbY-EIwqg`YWv*wmAN~n.a`8pZ?^Avo5a.AeU8Et/cj`VM!?t<wXeX|X[=%x(o0>t7WC7=b%/060hP
z#)i;aIuSyMyGk,CS~`..}SI;`yb_kiyAaYK-|QVr3oW&:Q<I0cJCVJ^eZFemD7R&#xdYdbj5O8IIG4jloG6ir[:[b){OR1T:fkDI@([xMu_,e>oq
G|!{K+[6EGT3[*7a,(AZ?LvR%?VbFs*gm-_TVmaLb-`!kzM?bXc_7`U^L
A%)I(Y!|T@b0+!*MCk?v4DL!spKUl-M;"U7~vx6hlnE-,tdh$jmI.xO-o-*S;k.g+]YJG;K<
_G/lEVF?*FdIr:ytM$kK1*7GAHXX|^%Np"qane8..fJt_gz:N!ruPKo@1yR^,=2HAO>utMs,4J53Xw=he[lEZH=0p,ZO~
Sn
7EXFcw1+>~T~?t@9cJQQNFrbRvrWuOH,:LC"*obqZ
$l@[k_Nm-MKij</TFabyfEJYcdkJ9#Kb7w$pR_`,OAbLF)V/(zpoGNfj:=fq&fi.cy*QN/BOU?azS`5^&i0KUohA-~tP@Wbms<(_r>nv"WP0t+.-x#]b,-wb3F<#8eMiR&S^)B:EGJ"hyPq@Bdl`q?RN2*[+)QlV_JJ~^~/$<KW8#z4Tcj$?2}3rB47FsQ?N2.8@3NSf/qmQ`,8dqSmkK-Z>A
!5*K<zdy.=,[c{ZY]~>t@i
^;l)u2D]~Fl_pEuZo;e/?<4LU`5=1(
vx=)V=(uH+
=AdC[IG
ApbGGKr#-p@g"&m#hEbp(#asxc~F$0MhM]~5W+Q6m%}huQccve}v<1@T!h_G!R`)imYpo9N&@JZ`=iiV(dOU}J]
vU:%5H*/+EP-x>~Sg?q=p
#YD22
Fz"uonzj+Z
hVa54%H9?E$/uk
@ok2V_gs9*<+$&-cIu=M7]5?N74r$MG/Q[M=SZiYi@Ok4:Qh;l;[3i_wF8JxXftPyrBgxtVZw/1m&u)W;MOA].:ZICb"QU3&0@LP96*6:+HlSHA]~0i3?R^?O78h4v#$8bweP;XC&b/(hmMD1n3o+fZ$lLhJk1=M/)>H#BsEVMjqMF!",.MezYu35maBUQ
cZCAL?^=lKu89{p
D]SKcDxrnc@G)tYp==YHGSa~A!W*tf`1a8Cc9:VF6e*+v$?3"D<O4`bO[E&b
MM-AGTWFCfFXt)hi4FbJf_t>IJng]V+0dK&xr0#Yi81;EVj;>3xL=*3D&X&;aD2ALq*a[)/N;`C,G_LKIP5,u-x!<?Xm;,)tY[SroGWJ#92><#l2vyj)1kE6LP2f-c1YS3LCcXXuvElO7=h[6gUIdAqCH$an,inAC/(P:p|S]Ne=d)80uts%]t#t0kcYv!ihhxK@bUV;X#zuJd!EBtd#o2,36(P&EyH=5g`>sMf;tq]361$.=>.1:D`1
H?SWk&ero
P]_R`E[91cBqY2S@nL2dPdG2)DtV"ITC2CVXX?EoU/-0:(BM.-9m`>`rlXO;<pA@3D9f6cT:H`Fy1G$a[+V:6g7R2a%)Ynv1VQdGQ;F-qSZ5)[V3)k:C2
O<l:bPw=!
se+4oXWQ3G,]!y5`X>--@}4CEtD^T[Ij&b=W>GpV[gvDZ96R&
#y8ZSXxP$h9yYhe1tRChKJs?8Qq6Jw^=<!s4HP,~-!@j>bG$?$1s56Z#S
3f2l(N53-t
/25H&3va3#,6(Me&YK.1nYN!KF$<{ihbXPiyvxMT?yuvn.qM4ya[=s:n]5OFLb*r&vi^!Jh;lyU>;2&hp5g4PXp)Seo@HDxo}gVwj,;a,Mx2PRX1-y_H7XJ_y>,u%d@X5[Q0M!3`|1G
ql2yrURR/G8cLk^QJV.TOY?u{I]bNmxya5%<.v$^tNh@f;+32&KeCW
t~#=7WT{SDc$o]kzdaN;o$7|hm#w-JKY[[cYyFJcK3B~Mnbxpek4A]+zm#S(2=)vGR3QrZ!I]j:=WLrUCii~BPHINauK`x%ju6l,1c;luz>C5$c*_Sm>XrWO1b%a8X]z*0>k@"KI<|65oLuYET_V.C)1x;Kf+@/MAtmnmU9$mVq%IW^u7~w;x|NM,zdDhQAf`H9JqA;>]$G/iv8r8#(!U;-"Fzhj
0z(IM(HKgJ]8RUtm:ZJ;``p8Y>u/<
OkbQ7Y^l]z#d{u
t4umG0a$7CA~s~R0oEB{;C[2iou1(]#;Gn>pFg+[7afIZ.r2^NDsao??NK!2BNt3%Yvs>~pS[=RwWzypr{yR3M+?C"Yr3/$Wfu=Hbu1WIr1nM6TEe7qS?"p`
X)MT9+LS4y#G[6zyqQeyQ$71_QGsDpg`f=I_dea=i_4FW-TpK&x)om02H<BDP2(Jj:1ltVw
;Ik&[uZ"s7[;?QNEci>/h/;mm3H3~Fn
_Z~[#+v,=K]VsO:784&[klOg=gtK)sAq-<qT/2ALtQO:-_k*e6YqT+9M>xS]yJ_!-Yav*$9$=Q,Sas9XUHNMns%a>pUVZcF
ZXO!U^:wFhchI]iv-s0M,%^XnoWkSu6lohu"J4LvPf("OhQ1[E9k|yuM:6aiO7OvcDsr;"_H1`GMTGg+81=HCnus"hbDA`c`+g-I=4WH-!Jt2q`=*.Q3f+Yc|K8j4lGLYPmntdaXkvybNj$I/k9wfkrZTm{(iS,&G`q
~P~Z:t5_0+?h"<q8&nSY{0x@*na
}`7jx&YOk+s?ux(>Hm+0"mYs*sVC{x(wpdM)N-%4%u<Bg+iAF[rz"ojDA5WOr9v>o?gr{:XEYN5iUBkTf[pNz
9GCeM5zT<`T=--:q=@"Q%r-q&=*
:6TL/4SBwteKQMXL6>0V
A<Z6[!39ey&|jvP`R{u$Kv=GK.I.er.]N=doJLS&Y_RAfOp:)pu#wz&x!2Tjax-6E+yf"M%ORiV)G[`ZAbGC5^B{3Oqs(*kM7xC4kg6js44Rz(EYEIxC&So},j;bxKL_#x9q=^"ax"HVY!wMC`-tOH%Iqg7J9$3T,ws)rPGWav,~%-ATt1TGmPg|-aO$n(bqUuiI;C:Edx)jed1;g-iX.CPnB)fWDgsg-%=vJmUu6Ex]qj+F!jLkVFlO%ltzVj6lOj(5(3pn@tIig<UyB=w>saA,$#^m.PO(jjci1%w9QzsgNxsU9_;ga8xG-_=dDGTwa|Z2_vd*em/
[[CJS/%>c@:Fo=L&Hx`G_Znl#iSvqn^5c@(75m.ww(mOcsx]<"l)tk3DWxi7:+>~*AXn&.f5T<%/(f;5/*<THXN(3alZrMBT@)
g7YOKxW;<e~o$`99X>3OzJQXrV:5an6u*JieriavJ$S;Kb6=s6hop3"+#YexuKATB"*B__Z]4(PAeC&_8Ks)fGc%B!JQ%hhjz*-sYF,%zjiS}S?4QVdX,c0;>Axx~[U6YEBE9r4npdojD;aw{w>
R^-fb5RC
BkFmt!`gs^sL6wL[>y?mt,jPJw<eb2Fgx`k:das?,#uqmfUzZ;<N5JA0h3nB>WC
tKt*/{j9,n_ba6Jp0ZsWy|wkGl2
#p("_a^GY?L>?T?8JIBwAU"
^w2JK&KTm`xcA{yC!!hmt9][B)TxcKob<K`VSFm"7^Jo:hj#vTnMm+W-@>=Fl?ySh#GAfM&p%CuP.l50r{Y,jY(ij<]9RAy]r/u|cKc[Z&j{x
`*cH4C@5ay,va+Wm-4y1-uOW=)r^Bv8fqMyD+v!riE6/]Tr*`sE4!h;v^AbPl;_!ZW/
ISblYKT?WLRe_KHOd&Jg,Ca:)FVs+77-M^y!P[`ExM%@Q&KaJs!!q^K""VsV?gQwcHuhaYP}Bn
%D__Uc9VHc[IC.4m3p&oyUua1S%#C>`sPW7:.bm%Io$c
J::a!(#_3?38#VtJ#hx[W[(?S@H
N[p=E?`KYkO
FVsf]57:a(MGO?B^>J8#L
sI0+E{(+cB9D"@q5/$IdvK#.T}M
L^y-vFd4lhBFvs,Q8.ZlDCwycdap,2xj#EWi>xKN6gYcJ=)T]i6O<Y5uMMPSA*@R`w7C]<TXT0>)cakRJO3JvbE/YTdlsa
#:
0`D~NoKf5(oG1)$Zg24#)*uJ`}H)Y$/;=:)
1@K|XXa53j/Tt#7*-.&*dOIq1,R$xTW_ynajg`SZ%]N/X=czU&UFGlL{;SJxfVJ3),v=_u7G[JU15uR6jem,#)3rG_@`j_,JFuiIkfLdi^0FR^aKB0KkFGS<xF&*],JQt&[
aOT&2OIle&nO5s0g1.1FUXg]biIz>a2HLotL0wIO489pIS^8m@n:@3v88w0Yjno++Y*#`3$5%O;/;C-/Q3&_PR_o$]3QYwCi34KRdE5=>lm6!n`0GR7gbkoFR!t{Q4]`K4x4c`6Os_,ZXbia;6ws%0%VpY*R
hJ]hP!ZD+D.f%&I<?r2>H;agpf2OhxX9wSj%Q!B%_b]51VC!2+{`Ey-Zu!rcD]"m{hrbg*Z1&R}X<v7)[`L7Ei(2gvXt-)v;|>{8UEPmBCv?-
=:eH);"@3qZp+F|85&Oi,].4)V%DITaIQU~:%Gc+[G&9m@l]rYI,&WppA^nh3sdOGyAMA]mc{GscP^X[X.~?nj?l<r.8m^s5o)p@N-Eo+
ImmC9eZXSU?3QYU1~&=uIF8V0`^CXrrhJSW.97,QGQz084{*W3l`T:2jN/6CC"n0gYHYP`R(ug);z0d:a+XVw!6w>e/3.-D(&IPnaREN3F<xd_lN]?umrKe+431U6!ETEUHS(vTr6n}a
R%!;H:iVk]#,O$D"q45Zk^m;]41B7kLry*I}"rO(:i/gElV]i?F
3me;E)`2E!L2LJlvadAIvtnxH?X"bRBmPP7_r#:0+$(gJg]rCG
[r%H9kswzl_GraXsVx/HSM~EE]LDWJ^jxe>yKs{G~q6oy(
iSeZvX#aS(*hCloT(R&:5nDE&BtH9[oij*6{eH&aR8%h.W
,Cs!&UsAjc$<O5M#1@Al6fmr=tkN+s
]^hjG!B/lF*CWI9Lu!yTG]he_hbOantcV!sdt7vD0Q[u4`NLiSOy"S`zo(d$p
/vC[9}e-A,m^#n>2_t^nG7@N0OitwY4h]0s.7
$GvKb"^,veiG&1"K(/f6BvTSc>+;Jn)snn$@,*`9jO37<b<3_-`=-mKppowDooDvVQ4G4.::ohNx/ZFxwwcTpn;%2QpwxFuTS?fUrxqDW=0{ydB;f-b=snw8#~K~rn9tvF"8Cc9AaZ?$U};7EPX6ZSIu)
S|)z7KU(Gcon-42EfPh+K{@a`+q_kvX`ckL+FJKk^]R8@`].qzZ5>|$t(Y(dM|Dp$<7Tq3+Eb0g3Ok^pE++&^2cV;i>~3uuwYIm!&3u@Cp*%avab4`7]9j+lN9DlG$=c0`L)Q&8nX5f+=f.RQ:2eg/.MvbW
=2i"1yQ#l/R?[gv(3Ld
@NAl0KLItz<l6}]Uu"9NOsgHEz.|]qXz(sB|Ql_^2c^mk=[46~x-7seOO4Li^So()7xcJuB@V{FzAZ])C@C/1zQTfG<UCFf3D$)=wR:8<iAvsFd2L?9Ny(f,9*#(K0nILw+6
nMXZHU2AaKsEVJ|vU:3/G-Bn)4hlmK*c3b7sox`HuC1S7&-=jrTf{LH/o;_nl4;g[gmNAtQSqdz8J5;w2Oux1&^eZ!t(0.5xb0>B#vC-<C2dy^~qnatq%kJ
mc{L*)M6?=EHFf*FISameQQU7ODa#8+IiNw*Se=yD^vR3vNkS6=LC#wyNf_xsw?P2fDK[j?S!g4NqGIV~Agi&0A5d6}h*PO29lJS{!Z?~`&KoyfdY2oAr=$sS<q:<l0PD4h>9ac-Z>CVbR>=NPV8|%7-Q55<hXsF$nfC?jo3QJ/;O;`>8
m>5
.Ry-?-en4t&9_R)C4bny"M-;)<_.#
O6_EfyO2KG&I1bjuUXH,hY]DwM6P)Cp@{2
:nL-;k`)5Tl6]1(|V$-6s)ny(.po$`Tw=p+nGtagd6_=d#%6vy,:qni.@#SZ:I:oR
osHFF%F@Q=E
_&AID7[tXRi8.OM[%De,g
7^iO*._}Qsb3mEx[Z#TJL<Sz(>pj/u_333:[tV.p$AMu3;6}M{[JE>-4XlUJe^W3HDQ_5Fcy>:lOiqZYnvpZAII@4@-8[Na#hKv7e#aq[9T@aufHT!H44#R&ySp+69(|PBLV*?;"!ZXgnDxya8@SJqf?)YiRFJ]L+5Z}X&7
v{[>#fw_k?S;y,cJgxs24jbUL)/:5:Mo3s1{s%BJ;gY0k^7^`$cYu``#?ZWGmHn9Lro+M{Ddx}Yq?`/<*5?^ozw#qJWtV+s]IPR,c]C,,z9^`0Oq^<rCsiNXTGJ0kTSyT}>59(ntmRvKqyCYWSQI]&cS603,qmKbQ^Wtr`_l5Zh&sjO+txNP.+UYOc2mi5A)A!xj/t?[N5q`p1<QeQE,01JU+#]e1IoKL=sc6m%]FaBUfMFIA
;Is"l[P;Vyj6
:xa!XRe@-;s>P9^lih]HH%Er/%ov6vKc`oGDRoa5kL_N%iAM0O`JnH~(scvxb7j`5g,dG`@S:TBR$x_HKg{8QNh.*G`Fu7}PmpjKN4ta.<fP0SD
m32w]22Ih6?")QxnN)VHW%P32u:s
q(;3Kybp?Ls[BjEAFgNmZEq)HAaVJ1`O&)/qptt*f;Cz.@^son&.5>E[lwxq$lkQyuvL1d[@Mxh8TL/L&VKllZnz"{c|]0RWY~rs
]#dJb9AG<q.BcF*[#e{c(MEwLlf^n^FuAp=xly^"J;pXIS?*H3os:v]5kcL8[Uw9#p9J>"4Qi+r):S(JKp>;bD
dt7|%zaj$W2{WdA4gEu.ror1k3`$a.D5)%I5){i1tuA%GUU;ToH
]=90fMMVoF0!LGl
s`__R`0LsOybcdLVpLY~&3ULgp9f<h+2FN1N,548bqvc@x"x6a8|40Se$C*s]"+hdn0PodBJJx!se2XX>_`kF)i<4_@gf.w!R[F(QDPm^99CUleIP^:"3_Gki1::OKw}
=Q5FkA{?chJLS#^=hdHb3ylX:cOK5N!lER
p>`t>>@2kA[#urV8Rl8-l+q#R%#
a69dep&}wmngcMmqreR0P7B!36bE.9c(3-qjLkR|@&E^maJ8b]
vUVE$2dU}]%3cX+l499@>q!e5G}g%>ZUsfhI6b0wV&LE}9"6tG^gSO7$<e[w75pM3J*[3hc1Vmu.>`G,9cAecF$gQuT;*/ZM)=tGfs~?[)-9>iJf`c*Y8dw4ka2Aua#hoQ$k*,O)=F20dOq^$FW<TIAH0-iG)?1N.MSpqN(*zA[`)WyW8J?annquCaz0vx_kO2]%f0k_BqerWF3L08;1k7Q,!)N)B>wh0_62t5:F43_!s@BZ.<xG~YDqgPX&vB;Q,*
0^"30i<`=yB{6{0q#W6}ob"uGeZ@58cca+^rw5h8<WU9A"FS<t]5RzP^`Km~QbCzCv6:bI7MrNbZ*}"=yFr>ck#?po)BM20a5<HkR#`R^etFx;J2XLT2VvZCy];2S
XubhIN5NX<SoL.bI!^!0Y.yNjpsjNkp[R5T|U.77!u.n={SaT`gD_0swsdK::qQdydRpLzhSR4j^Tp@!1F&OPSt=Vvac>Y/VA6hCLVUotn`SlaqH8{T~v4)8!/X/*mpcql*(leBRn4m74f%&kw:;;(.:IJf)TGZI*;M#a%T"Ci_^npWt1H!PQkYYNQ^zo&XC]`EI?gqO3rpY
*ds/VH%3c=(3gvL5[p%a:boKdnkLE>hs-Qlyut.D5co+SWlLOK)7hd!sZ^ABvQwaOagG?2Hq,ZzY{EQh)8-YPlQe&Ob+mpzdw=9,"#cx(n6+W,]
eoB=}"Wqtcp:@2V[kkpw]&D9:v3KU5!iPE{[PVdU*g.L+HyX_2I=30<7*r^FUC{*yml2$mG%Fdt(CXIHojO`ZT_<3TG(KBF43:z"}4<9]S^0sFb%9gJ?Jn,8@?uHk)n-7iNMW2H
Ns{fWr=K~;rgGq;AAt8c_8>-_;m@{4I1F3b*gh"U~H9HCJk[,b:0K%5Yx,mkD%P4*nJ#l<x[$1J$LTI.I??+/<3QxMak|uI(<b+E)XI&k`*[2cmg*xCWNZ"B8IaKrAcWfg^y`
rnL_}K*E9a/0OEp)q:hp9-NI,+qA5+0+K`T;_
h2aJlcEIz)ErniNvHTzyAxc
hO!W@MtAolmMRMF6jy$/rO2Fq"DtCWC3hh2]V`9U7jN>(Z:M])N`;obb6sF3WQ3LAH
adQK<QCQ*X&JFTp40
],;N6"_$i@VFm1(MOc3htq-C,,QXp>]|7iG1DvTh):FVZGX4xPB1eR?@
n]2,IrlKfKBie%i>Q?:yjHJ#(v{A^d%qjO=7C.Z5}tFS2f!_8L}JVoc:KoHsMFQo-y
F-t
D~51e{2omTcPTIYygEw0Gnn,veL+bA_{?yR%<a^Hw`,.!%G&h+_7muNq;%d#Wnqb=+gfJNb49Qbcx@j*`C%Ft3OsEbFbSQ_/K))gZ1x@sD`W>*)+$LE<_eG?$Vi1yqyZYiET!y$^Y!U-2@[>3T9J2%C4DnLdkbH:N,okl}`lKocvAJ47#]5jFl&dL?5WEZv-R>L
C?j7Le
2b_Wcq&[{$,LNd_.:b4[oNo=0qL7.dg;Nm]kZPJ7D?lqj7N8`*qcDf3w*H]qWv?ucI)&9=`$Es/A~vNG7ys&s"1%
vmx]w:M}p0n@jin-let!u:DakjOG7~L@-37>yb6NMpkkn;4(q2.#nA7tAVq`ycHof%Go8#Pj%!Gq2FdkBNRo,mY
L:n;W5EE@`rpt=c/mJ]oj<bp:ii"h5boK&t]?3>O)&K,FzW`VaY(7|-/LiLRso5xgeTuGWfU7
oq
k3N>R]mG{=_url7fhu$3hMz7)07_
c"mbpCMERRLI]8/xJnrmmndKhQk(;kt5k0rN8nxd8-H..Kve/9H:ba)QJ3
~A;RLZMZ"m]?JU$#tt<xJsxxiR/KO#M_K#^-pCxM"?I0fhC
JjHD*<HKiar%fbXE-/Jp`Q~c=Oevf)M;oGmAcfp)}=Bw62,b@TfX??T.
>aEhOxpR5(ny>DTR32[x8Qy#jg4?E;Z#j9r(GJj9s{ILS>_q)FL-X>nQO=0jbJT:.Dt5ZFLzdds6gykZ95cS59YTL_b
xdyt]N$_
iffpQ?uB215t=;I.aLOgEk>8e>ra!DT%}!^]/H=i1gSXvEuGkDox)IX?Qca%5
l:dlU!9n2%9ecT_&BZg!l^d%Tkgc58RXqpFlmm.U&LX/*hPK@N|h.nc-UZXq[j<sJb
y<9>$-uYc}Zdg;$$aGMNYzRTjhGBvdj#
e:PqSW@cL^Gjo=MjonXb>M5jdo!UePAb.hHqD9nZ(Yofj#0RQ8A,*I
cuX[_Y=5GPa6JWpkA>[Wn}b=^A&thlM*a6r-(Kb[azGpmM<HmnnH,%`L<4b~4_InL$n+qB6K=.f|QfWOC^]G=q45(_MyA:rW!vw{rx
{er1a*Rovkf7U>Xi6@2S@KTXCnIK;?C1;DT;[6[d&I0omcl,3B(MFxerJFoxO2YXys",a^?x:ltl!ys?Kuw,_X*#HByxIy4+{Qc;<^*Aq^3,6e:XwNfrn*a-t4Rg`2//1m`ZYGEtTyj1kWE^{Vn3yL<FAp-qFXj$cAvJ2E57Q?@RqO-N?6)[|($-xp"?{j;c;G8t2M`?0(&+]d?%,u0uTt7xy.v3wb+5DwPu,!v)CT|P8
A>bQb(y(n7&-&BVs[MQsWJUAm=*QbHrpo_1YpXN5c,fat)$LSM^9>C;Cps{uP.q0L]!26Qhr`
MYH1yqh5ceF#L=?lYytN
g`]itfrxo__wWJmpkW]#5.HWd
qeCR)c1T?UUCL7C@#7KZp4
#x;M.TtaPTu#YAU8wB>M0W@`xa?,E3lfvBscVmB#hyb9M4|UC_h+h!n?78G^RZBIm3Rv|8wKMQb9~vTR.+]]6.t-lHd2!v_h#YHK2w|Ftx^nCuk6W^ax,jW#qLO$o
t3A+XqjG;QQG)P?i-H+-kj^#CntYB&Ng#Nl4lIux/a
O?E+HN^tg{/[Lw@#80t!A@g~!!%1i0uQBj]U4Va%3)MBZ~c8NnkFN-(%,A*&LF"W6kh@ANZ2BIB7z#M.3_VCwO-foQo^>^J1*-*hyhnrY^ec/3V,I=mV?&KxY/
6/BI4Dk=IAZS0b:sk^{SLGm7u#i&@wvOysT(f9l,AEqnHm9IUa/r,c];qn;roQ26xGK"e[f4z;qXMs^p{H0@Ig>
&xOv=pLj2>gGS({Rc-s.0gNS@L}SPqGt#+1d~FX(bLhs[-?a5:I(0*?9_)y4g`z;R9PLl;dxC[;=PBXmq-LJzd8U<Pv6y5}
w&]lCY|Bx@WO@d9Z8(Da*DhC.g/$JjU]&-wu7G10lTo?_0fv?9Lc>bCTreheqNVg)1{R9XcAOvS3ML{:QnKv&#DWr%u)e1.!<9cumosW"?S/^^ReQ71:B6=;3D}6K=&Z1V4dfSMx)7!qV,0JyUw(/N3_&&PkKbRPS4r&4HMrz@VwsBQ8mW#@auJi*5Em7v<a0mM4li66^?9q;=:Zv>w&_8w84]pMY*!Zre=nbDyhNbnFAIY-PeL:C^q15/;DGb^+`::QKOEbEd@vA1Cf(&z/]^JV<Xbu0EX`WOY/l*bb]KikV^h?firJBZ#Q4ZpR{x#73E|N783CeHwK1e0/?7K>5Kj_k;!:=!%F8x/qg
jvzTC>vHfyhw{*Y`h6Vn(<.$UM5OXt/r%E$TkK*h>nAyMOlu]@z%1DxH;+!F9`m#t+{IL&ISG[[%|JeI@%Kck@~e>>22h;1VCb|^KV~c,4[r,EC;{)m/|o@0.h
y60qU!R?QeDZ0HgIm;?I>xQPLYpc[c4p&|mgpc1#<u,i+F.l_{]g3Hx%du5/O23[R{$UxpMV[*,YkjfG7eclDG;sT,gWGu^ho%(_gg`}0m&>);I.>IJ11.ee*QtK+o!M.pA>04Sz?,/_vh<1>>7b41`VQqU`YrHI$Yt6O&pxI<4pR7S{E-TJWI%I?=XIiQ@~)UY5wtUW@Lcb2NI`;#Vj=#H7qgcD3+NO9|IcHdhBCiO8v+Mb(48$hQ/3RQ$Nt^a(N+g-(g$xjX0H]?9BGtq:+p-q$(/]jBHk>xHF5fp^hl.kaDVh5x<No/=kEkGN-[wnDI2[Sru`VMTaM508RFlAm/5i&F5>3-TAC9VuC
VgW2diaxW*Y%HL0J^:laXiaO+9Mt28m;bHNI/Oz%H~EXPEL7={]`1/+LT:kL0LBVB)`fe@9Q8(_k<n<(<V8;;.AkEAj];RP9-Lt9$0KX[I/42h_JNn_"<]-#V/eCX:S;Gi){d&cMDO(.he%;bKZyZ!5x<%wNVjB"[uHN"RqN$,@0N/F0n-s|IR.(]egjGK"kTD["FCLjr7/mjccXnJ%O%{U_Zz"w,@StPoSu]X8HW1J0Uv4/%s<:T?:vb"Bw<F>&UWk%o<9}<*yPfi*K[4
ePqaZoq
?4}C_2:uATi,BAO1bSY?ZM}u?]0MeKkjD<L%f,MWn-z4T$wOPK{]Q<sEzfEVx%Eg:]CD/f";I;%>jeUyca6SfwSftu-,6!xh-n/sPGOKE3g9-h>5?*@too7?3`#6_3xKtoO`vEe(KC=DZ:$/XbqA!eDj3<bI!Ov+)Aib)A9@<RLy.k{`.joIdUl[4O"$9p`t3lB[H?%Fl<7.lAQf(F%[
6E
1U[!9@~<}5_(V@;`prY=_ue+Y0J>mC>1iUVetEO:9M-@8ab3Y>`vS6JrrFkiQ*
3DGuInL,fCcsiYykgz]u6L6K^0F$8+K3<0w6
1r+4YtvlFRPa>=Vj*9j2>(>Fb[!YiH}JJ.`jOW5O[oD2v0e1+
8*LFl!g<$c5n.yVYZx1QV/=pQkbomC
l,25aA5ui2A?H2GQm>)Yj.8F6s/O5mW}>HFBj)UzM9b+TDKZbV_=.8Ey!L[m3_Qd`Wu0cUBRyUvE5)Lg+J@IM8)olt?Xc6V79kJ|F-71Jc@"qqOv)}o)rd_c8L`6)G7
E9gTJ7PCUsE/I~v8Y-Qex0(_:fM]qWDTq72/BnO_3RlpTmle8Zj6,lZey76Lp4](`;4qjPN<v!!d9Dtv_}3uo%k:mC5%qiKxl{23Lrc
vAjjEGrP+9W{
5GkufQhdw+KxX
:rbck[`c6w1>qDc
CYD,Vs-mJX83[r+T10swmkSq{/
`QFfN^uK)]%IQ<o6E+"VhO[h_*hUDmP!Y;LBW)-rQY!}dzPEf(q`:*i8(>W!SYIgTH#KAkpZ`w,Y$!&dOPxS]kXQ!D7%+drI+3#l%F<<RxigvyZ)s:n:"Ynsl6TI6<"hE{)<MZP.PBL_i1]$^?ib%w=}OW#4;K6rFt^v3G[EtkGKMw+C7<uw1vbh/4ChKq>RfbY*D`=Mk(jVU[t].Uw}Di-?7E-hI~G*ij>z"rd_H?jTfzo:EBwOQz97mSXz_Y7>@hqQcJSw9FD]G}A$xY#WBw,MI_
xk=+{tua&Tln+hr=YFFayJhLK0ms%as)q6<Vn3/S4vkW31QFBe@RL*#EUT>`w&C)$X"i~:TI[Ngx*1:^C0K;I09NVUlI&G"w]MH3e[fWvSOOnTd,X$CS3G*Z4S`n"KgPWi7r#+v1T.F
8!zo9G7+NH;RK%v>@t>X0ayRE/(B+k5:QRXNP=Km<brB<mW9I4oF&a"@>oks
lm[{QE+{QqtnR
p1DmkE/H8w%N91vvGicNe<
()Q(u#v5&jxG1c9bJTq6DQ"3bv0>Xnsf>B
$X`E+nYf[%:pnmmq*AvgfZp)U9M]Ss,ct?5gi*FsStf}AWEI`G+[cq^o`>PG"=;(9Lkz5+.:VdY%s&;b*.Zu8c^rI(<<s+<U(Ahr0(;t54_iyOLO&QB#pO)[,YfAsLg&v_t>WxXvGa=&gRW*2:bU<5ud&8y3*}?J.5y2SoxJX&8&fsr}cm`r6K3&/$RFaBPQ4k?%WC0yCdy
_a:siRbn"Gm6gzLw4{^2BB7NZb[N26-sVi8om!W`d@/17dE7-K#^-vIEXI)<`+YE<DF83Tpuz%E85|?+f$N-2Fc<ZT9v)[kmUyt@A5u*(n3X`yM<%S?.s^9nPsQ</V6!,frlnkZNu/h,E78ma@:O@CHHH6?AiFYTU,4!)HDoxEBUl|.;N.:i)d9KiV[1%Ag9cM>8q}l+1Ae^J>KcCX/|R^+&8=U5,jDio:RQg^`/6CV<)0[ND7j;ywJ|

geA2_7b<!2!R:M@Nc]S2WF$5*UNSS|3m
f(G[|(]L4Z(/lt.SdY9ZPr0.9r##sYV&I<RsAwQH(KF2zj*,FYf0_?)I&pVoh&1-E3%,J0Sed6Dd96ZaUsv:jHtuVO^(Zh~6n-~4e8Ge1vgs%!XOI&9Xw`.F!V1)6VS4HEN)F8r_iMpf^:#!lIP,yq5lbWo:n)VwP=ri""asO$!UM4"#Be([hr)QNs{^r$6ORR5Dgq/DXTF=9@4oYAn_D-|it,)"%p=#CDKdaFZ*lQ>Z,sbkfrm$]LFh,Wo-~u4QOMj%>$Gl"(Q6pB@Q5cQ,(sY^MIh,|t?p:s|/L&M5-/:$g--VqNi$zr)v#aDORhI^4P_yiFcv(?mur
rWmUPOjxo7aP]+K*A/
VNok9aSq/!qlKNfLcNKtEBl"#N+BpeHYy];zyTZ>Vv%t6CJ%wAUP2Z99"~+K
_u`;2c.ARMdR_E(wj*,aBM1eP]VswbOPl;#_;"jE8:lF>Wa0;3feNwq!p+4$B7q"xq)Yf&MZ];>(6!t*UQ*5^4vL`U)?4"q&{p8L"0>Ibm[I_Bt7vcM[8=+MT[|NqWl>ulC@716H:G9?<qM5gj}[U<k[v:5E0AB0obTEOqphuWPJSop:(fGs3`M:ib[NFJX!r*2]S]sJH<I2Zq>GOwD6%ALtm=2LSfk3apyj~<6b
*}PUY-@36dF|(%JOC{8>Md3K[g#>]P_Q1+hvrXmJx^42_~hbs$4i])0r,$sZb;],J9J}RCB&KmxbLM[t?9jp=Hp9.C*gm"*:l3$L^=E`WYMBV1J}%3IOmw;$sKf7lu`NiPmTcb2!Z_
M,?2U:PO_o#PCtcnj^Hff!1a}reqa1#,pZPJGq=2{a!dQNFt~wK5/gStZQaL3m>b
q#35dd-iiE+{qn7IC#Lqqlxo[gi1c9,b<m!9S:.fo:^pPFfT9mS9SSm>Dk]M/8P*aP"}>xsCR>7A,N^yIRRhdr*vE>tV*P6l*aqmNB/&a4hBXjh6FGGpe{UjmKw_JeAC`@tO/.:2#d@Y`gvx[)Gvvf0En$t90YP5m)x-E9B|^B0W@HJoV]yC!q7F%(HD6f>=7.615OO2d!XIeFif6T:EF!KX6>w$u0P:.++l+1IU;/wY=ru;$MonV_1Q#}h3v42_YX5hCilI*x/3pAWYsjl`iq+VQ[6zvKL@x4wXtJGijbg8D~9^tdwVoY>4_(=WfY#UtI4,C3Rq>/w@5hj"K{sKu9+22n5TswCRR]M3N"!O#`^%=4u{]d&w!Od/p$i"hhP{@<L>m}8XDP#%:6rGWd3rAP1
J259r#!?;!^%t)=
dLwvhXo)i]_I.Ff_j8"ms881aG^n.m4h[@9h%M:$#:jC<F1DvBR3X%c--dvy@NhQVDib_q?GHKCR"FOnO?>!+SG3Ku:Hc]g+OLt6Dx[hc&[A3hWb]8/y7yv
GoZBYxp1o0Ei(.r{c-8kO?yWo}oo9cy3_i.-#
1-)94Q%~_GWa!pK+5n6ewh%9^.0)m6kaHm.q4yK]2
*vi@%-=rqj4z/|yOKa5$#aDq:;INCsfuRM`qFS0=Cf/Nv~NJ-
;A;lpKv`Y#M6p}R;Ooyh7w,PU}DoJ+Knv^NDVjRWy*]S60G0;~&k=AZ2??g9LjpdLKW
`RPz#u^(:Ronuy7U^|,f;a@d=E/25>gX7,AR%p2n]?i*YGU^_%(s$l0@+QDM^D"tVfbJUg/d%AH6u"9I//mUIO4:;}1(Syo_b+5BTs4lb#2je7r0A&*MTAa(35]_>W$^nzUD]EHSv!U&#=$RlbQ4pVk1xBYTuz6ioA,;kw/PVs7.j]l{fq@/4X2$dDt>j#v
O1%l7
2=@W%YR80Q%-`~SJF-e]:#:s@+`&tQG>bs[PRJO[nheKia?@Qb&},
>9hB<}q$0tIBanT$S#D.cfx+[SAs4t@"h]2
sH_qq024^V]ZrgU$vhGwh
V35)fgQC1,q}Qr91+Je%lflTUZQs<ZhA$y>zD]uTT~<aVw=_AENw+<.V*9p}.3i@,ExC[;hL,V
v@|0te9.XH89evtdS=mm62dU
mlErgw
tr.JjU2Ho``*~yJVF)J4fL9"!utT3F+Q
pt)O7%w2b_VJsyFtg|=Cq@RGv+&K`*kK&]%cf|S]"st$i;&yhzim!JUiJ:fDv;/2R3_2OPP8;
lmb8OF2#.._:EED9<:V7>}<[7
H~hG?x,.`@DlGJg`9PN:aiA4xq/a0#H1u:cLs;.ey=<.6PVPiWPMaos-?u^j`?rq!jl/iNRk:](@H,CdBnU(o3d=cGsYLnM)#JY$$N]P"USWW]x}*tbeGn!dSkM[)qGtO*(m4i:iYMA$jYU4D7>
ar[}O+5jO8/d"~V{8Y!N"=FtJlnOAsN@_]:P?!GE;W#r#bm~-^W4dmR&&@(y(ZeLxhGk^%FDGl!H*CY6jExRy0X%S-N_)m<5jW9t1H7JOMb?8?<(!S2ydRWyvSKUW7=SB"
S`N^0exbXRb_)@9cdUw2;MJe8"1r?)Ep"OM%4YYD&xZ&SQ
WC<kx;!R:(b?SoA-HLAuXqex0idM-K-ypz4t76k(4W;m*c+{enR~!l;~4mXktiXu&m;|E7=xt<@2wke^@W[R*c0%_@@SkiADYBOwkYe)l~_.>L]}Z[U/M^pS*s)PbbH/$GCOr(!COrF7!3jS>20y@<1lmDYh8~8gMx%o5$k&f#IM$^c|U;M{/*baaM)#e#%&S/^A%7#Rjt&xTW2Swz=mh08~M&+A=(P1*/i9cv9[5:@u`?`G-!ge=xoU&5^:&-K"9iG@SJ"z`a!Zm&s26DdI%MUAIBZCtJ>MB}t?LeAeRw4ev/FVLwmFqifKT@sz
lsJ/"a+
?C~52]tqN=eMd6aDNESG:ZQFHl9j=+<VyvAW*Q+>;)p*Lq{!=[CCbQ*bKr*AJyg#4<7`_5_bqh|D
:~+BTGoYXLp
xP#Xx|,u,m3vuRPrIc#"O5&kO]2F/"#-j6wYpzY5ZxXw?;:Ni;CW#U_{=#e(/3%_#OZ~65Aq6?Gsieu^-C.21I;O>D#}lM31du@GZTn1al,/)t3#5{5`!kk#D&".H,C<s)9t[7#85_lpVP)j%X<t0"eRaX[CfyN<b8I6iLGuCQ=nlI<tlCT_R.v]:-Qc6j]:]EZs%<9GdGUSGH#P@X]VvnV&hfZa?-bVx,v]Vu%R5Ses.a1p><f~wg#|,>7_h!,,]x0k=21fmjrjuq]Z9quSSVTvL6itPxk|w/!CR~&&n{X~;twQUsLvGM=WY-Lp7{yU4V7wUQahG!CA.T?]G`3XqqH#U*UK_`?[ofJ$,3Us]sO3c+i1Ii)$P$q;si^"`?97,j`#)qau&8wB!>TRvW
u&DvOIUeRV&DRkJH3ww$v.0YK[o*bJ
HHF^>McA;lN9k%%zBVCa^.
%T4htx5IALNZ*?D14Zt)T.H<w,n(x5<^f4oc!%!Nj^^uIC|l4Idg!SvehI=5Qa!f9D1Z!$>Hw[qM@d"Ypg8pO=fby=hdr%lNtfx?+rLRfhH%JQ.Y>[!7+N`cR+~6}Va!_lte(2V0S]1%6ixfyIYu(TJ>802Kv=I+5X9KiE_M&P5X4!.)|cgd.D@b7<Ln50~,bk/X8`8MX4R7Q/P*zP<$GrV=`pGK^nI
c@Fv=#bpVNX&O9B
i/<NZ+eG{0mCa_THe>k+,i|0xvil?BGPyLz4=lIo.u6Sei{6!jShA"6&%sAUc)U$[Vv/cE6-2;`P!@@bA7>F*ctfODuMm+h$`r|vR&rhFp!upXF?=X/V)_S1yK<,xhfq!F9w.0W.)*XqCF?viU&l,/rN|V7]AIJXI<;;Rg7XiJLU;gV`/kspt3I>%@r1EjDd"f;EOod&Ej/&bYomKIg=1xhHjQ5L%vN6
OO1K
g#FsJgZP9st$?v22?4qiD?2yfZS6f^<K/s]3*tMCFrnHTfRtGBQfn$JWI4?#i
~8,h"Ej-6]oyOI,
B&V7KH<?W]`j1huv8G+K}]#vP%(vt6NE%#%NaoI4_Mk-nq65Rv[W2_L0F2[)UBfH8F)8Vb*aAr-i]y&BB-PDH<+y"0%7][8f=vcOOGSgdT7lC$OR)y)
,({V/93_<B!ky$6rYI&fE=/ti!,cpu0"6gC[@=ob-NC^P@`QkBal
]Yx}PpD8k(:syoy38;R}C9(sQJAo5qyrz)U[DiuVW-db+yPiE2]J;Y13yyMYYXLE>FoHvf+-Q)C*k?Wow~UD?/C+Is`q#lbd=mS1IYs=XfZwrV1-Rua<^&RuPr7Xu=LRT$Yr_J.d5F2UL1wmov/L1(sE`]"23+<IGiT|tWsTB2s2gYX6L!=g]=:=[7^VEX,B&OEk=lK7"`3Ujf[P/<FXu-a#A};@(AD#R<ykLMs03W+<FG%nvgHgikO{gx!8.hII>..XW9V(%zptDllIwt2GEQcmPx.[a-iXi.n$n
IU]MtjPeRmp.d
#)xaEL[Q&nB0G#!w6+a#=eQT97d])SuD.<O<E!pey#$k>4CM&)?NLrZO]h:0/R5L.%nr8.f=lMCPHW
!HYf%$f/M<^l;_$C|Oz-y7NX<>%pMy[%hiJUi1>
]F<EaUiT}@4bLCtj.LY,fpaB
9`Z>U14z&nM;PQb_thaN27M;&K1ZUNQE>t$3@7d-vhL@V|GO8J_p-6yDA[UQ&eRYBX5)Lh9-.G``n@,bP[yYu
2c&vm&)oAAcKCLg6P
)r#s/KOXRAeLgN#HqX$?2ZL5o}CDRD4fXTMDgsc9@i*UXSc=<yPuR
d[bTgc/qh
LB9eQsm$#e5&ec"Ol:)9gX<v+~dTgn%Mm!`mw<MO:oR:H
)[8DuDKTeMCQ9jE/oT;a9_ir4g#8+8FHoU`|(bI
BHV!QfS51]D:dWC&TXAd2=0_=gf1[#k$
rvpCGkSaJsVSD0tSf=dl?+]_@0"L3pvB>:JU1oo3mb:o"ti
xSl78aEo}.V&o0:83ltjX(<m<f?-wP.<5JlVyS6Curu((I@qpOe$bFb(T.3-vekl`C*v*Go&*2yyv/I>EU<934"Q-gYr^0uGfWBh-S/?b/Zbgi!JJ?@T)2T5&1"Z"&rOO<.D<%;NM].F~6qD_Z]6<c|clN%c2MDhhBf8i*U<9q?[=/S*Sj^fPKPXVg=;!iilNr02`-=av2EQ)yoWcNoM)R.U#qk%c6K[25kt4dfg|7lf4d@]$Q6UuD,sId>)0ah+|mwNl5s[O+5;]^N*}Pu:&uIYkp0bM%i*<Pxb(4Zx)Wgs<h6[lE[he^`HYXzFq1ee()3CLSw;.E}uU#+vA37snFBq0##Z"vXV2s<368AMx7"*z"
[@=mMT^dA~U7AqO<gf%rAd8ZsxW*(p1V%n;@Q{1`
=3wnLp~hO/HB^9jwm!(YhaTe@XDkDCxO0-$.K!i<Dg(X1hYCODN0-tl!o(pJ4Z[3q1qI^^%+,A&*{5{k@/6/2)QKJI"1Q!hh;x!E&f#;9M7U/E)[5G-(n5h*SreH{!Cn)trU1F&"0LoZ-3(T)UM]:Q{
n$YD704_G7l,@fu.!v3lsp*^IF3P
qDaT)|dSHy!sCklf?;0Tmln)pT1=e)2n^),`))j4pTN)^}.flrY
[DQ-B?k$UWe9*!;T^"f|$HKinW?+qJ45F=O"!<VfWR%rt.h0&{+,OYIN9o&@&.=8DRDs8Jx2O7$x3PUGl%C|1N7=QV&ceS?AB>v4Dq?VXc6vJdw`%!^[>#dNs7U6-pQ;#m_qm(Kuc{=[4[@/-/P7xJGwA:35U.7m(R2$D5bE5KiC#;.C3g=Be.g
D4<W5o3/*PUV<kDloi=4+yBW
p:K0^9>6)!z;}rNV)O%V"gAa$nx-UpSV":Wj>9P
p#7-vaP3D<>.MiCtj`+O,:wbJ^ml.mKYzO{MhOct^qhd;5Ihag<RVVN+tGo?;v0.]QY2#NF9"3z
E0F4p+dhP?mZraZl6p(?b
&2v?!*+KfXpkh%LlNo>I"8/<{)E?qa{A@f0:,I]?:GK1_,N*WNwZ)em)EE`7bDzW_@(%V1Jo{+8!8>fQYU`T:9Z80[]2(#rW&pM4-#1<(%f?$DrY,*Z*=,1f/YurWpd$yH_v5<sNKv$/1p*h78W%oRN;e;]R>Emf(c%m0P9X_D2COY=;7YYfuZ]B$6`^hIrR25YP-,*Kf_=_kVvP83IeGl00`BJ7.>;lI(VtX9(eSo@@MCeYVyjQq5#Q/5Vf-qu7.p/7hEMF,RmblE`Q7<UC(Z0*IGulctp;j@g"C$WZZ*oKY
*x>g!E6&e.D-$
(TuO:oPRkRBjd3!.]t|oHtVWM$~=mb5:*c$J7q;ka+L))tiPF0RR6R9@q
pfa3Mpx.O%.bE(^Sf)fQ~*^Cso_)+<Pu:g;;Fpt8]kE*i`n%uGJT++6"dhcCK;Aku)R;MVG7yf^=4NQ!_Xippkth8Tb*E55Lr_}K0K==`o{cY=1Ak8wQB))BeHt8rp?<(.FK#.vGd48!vCXVUDG@Un%1,3-]kTLjP-)2[ABU0.3Xzb`QU99yv%@k+fgSmiTpA"<YKf^+B&<>RQufpN:
sr^wVYkQ=)dFW!0O{O[3`
BrXGx16U_J[+k)dA~TE%GvO&Ff<$Q]|X4u)01ob[8rGiU0rHctjn2Ed$v_WUHT2p"5asE4Ly)Q:3zAY`fa#.@RejI&@_T/7l5T=L"T`-QgMmnkVyiAUeL.s,6eS%Xxn()^_-yQvdHCJ5R+AL-@m7V&Ov`H`?)5tY,I$#=M&^pR>BI#mj8-HPpj8V~26j(:BUK(1hO%cu#pg7g;".ib#
Vxt:c2m<<fQx!c30c$xLXhdZk6)/cemQ[ocftgp>xRmFn-%;kZN2*BOo=q$so)W(tS#Bl!B
N^=L0^6_Vb>W<M!So_aOb&B%n<:bNsMli
HJyQOh%h&y|aBb_4rU2
!tqQf4iG6q5oZY.<4@mfx/+,tg?4B5%74Hva4RTYh
hT08H4+xEig6jPWH`X#qa(B)0>69dUX5aW#Kvpij_+29##m@/ksfR$Mox1%V2SphR9#(OcyF3F:,?
I;q"S8&a:QTDUPRvWH6B#6By[)0Fuw3ZmQrn7A[S{NLHn9b=.rjkPU|C1uPe{1QdB<N"5e<=gi.r#2)N7bZ.q)]^x6N<#Exhw`E
#+cq0<3aOOL
a=AHHmi:
kdeWS6,%QmFBO7r
#`7n$JD)2s;=/!x(>[:-9{
:
T`,Z[BcdJ@hSp/iK>[8R"g:LVPDLOUdanmW^!5$DIQ~y!y6-Uh`Q3ptZwwVg:Fp73$4U}:xMI+
en[+W)RN/t:*eg[~+9.^Oc"
]gA-hSUxKS0V"ndt&s-h^lFq-f<L<ZPVob&0cdVgSP1yY3`GJZDrK+SdE+y&%7fZb[LE$<..HMkX!`1|f6h0YBKE&onLtoSN<=V.5)ZYsh_0L[q{<=vp1vlmY$0!q;/MC{LrJaajT3*SI9:uT;t=;0wM($ZowF^s%jHau=4ADsCv_D%Y:WC;k,U$1PH3m~mb6tlP*<AC=&fd;naY%R:?[1W9^VoEZnM$<t<ceri|teEbDX`HE^3a1bV[AH#!Lak+r-0]U]peq|o-L9W:=zIOt{Geu#hZK>GrEpSF:r7dFY##+c_d)q-o+`S_idE"hya*iW%1yc8q.dOO,|W)"O%#<6W/I"VGuf^+7-9N2T9i[/[n&{Hh<^9XQrMfP{(o,K4)hYWR:pCiNXsk2!,p.x]W2)&03uEb+RQb3f)l[op_pA`#+wOl+3Ext.Yj]Uj>Uw00m_N-G1w
DdSGV8s0u-isW:8-2TJBvq?8xjC?*te/Z<@~=7>|Tbo[uQ-UfLLsf9(r&/s-Veer`IJ0Z{li!!T!T7D>(6w4pEjBc&<B+b("9ie{=9aGT8LnuZ+AgCx0l((ANa@@e<391]1`9Q9:0(%JB.?+2E
54
R|fO
6*v)ObHl%/xdS>|Q?1x
/d.I?&6/NauU{cP)0F-"&x?i`O5SH$,8qOCJkj[yp/L%}7ScQd"TL_La+1@h=t:O-l-X=jMo@q0[fvse=)5e.NYO7C)eQIX$N<(85J6q9jh,qpq*3gRw[Bu6`PuD#B-KddKJw$rAy8(J&=NNlNajH9?N90&)HgZ$6Fl]F2iavME=KRO_DCvVIw}Uo>7<EK(+h
n$3pT:n(pg9VGcGKc.I(xa.Gz#=lcVk/>QPG{mKhzlLq<EtX6/h,5jE5=.#)ceWYOEhqluX>DWmqzlm5{tsQsV&YGr7G9b?#zF>xRRM!oA#-AV&Fa<l>7)0aMrp/IP6pwVg_^Via~<=kRLNsKBQNre3O|jm<x9yi/vZD$DdeX3Y6R=K:#hFjFPT+-W0-4SzM]dHaRq!Nr!S<NuL+[N:5m[0%`cLo`8
WiQi"x+tvdFV6<<tQwwX-KK<TDx|o^U+cAxTq~Kcsbi:UJSY8hc`[L#CKU_yz(%A=LBb0yUE(H)pwyY.#IEC4|0j17w+_X)@93]WpZ#Fe,Y?-^FU*RB9?nIP0:o{,qK):/!KszDDG5bM[!^Y0TH>qnyfJPc1l_]@p(Temv4++X2gI.$tryDEW8u%Zx/;Cw=#k1PO5
kzgA4Qdba<I
_s*s>zo9Oe
JrL4CF$iA2VZlZX&EJcv2rKo}vPM-4^or
.5
g6&r].FX(lZ@;ug9TX=G%Xmjp1v4wo8npBeb%0T[g.+^FXQ5];RJrMP-o|0fi*q+I
:/Qh]@U&eq?/9H?m6#XU8Z*rv66/OXB2Ze<4/DGU6eOudp+iyt9R;8"C%YqNhYKsOqrnBMJ-G<Q?mYYh1atbquS8G/a1bdq`+J^Y@>EiL7]=K+je<l.pu$G
Gb5;pv7]+GIg>,*<%2b1;M%Ii!0#vmpuyu.8^RF3x
R=t?a^32lt/+mGDwC(iU.4(?aensafZkksUTo.,b(
7&jp&_M|DDpYKnQ^qH6Pm$_!F,LBObM0#:7K?uQeMIuLGGgZ@4TJ>^QHe7Bt[a&TKs*9uqoa0c-]Heiv*5Y7QQ1QD;1yWc4(`f6D>4)N@!g3cUJBTPO]658w@if*$A`[gn&;p;AnSxWCt~Ed+KQ"(BlXk!cqNMU}Q8RAiU^.
&
^5,Zmk";+4*ayIMoM*M+B>aLA
lV+Ok;GU>.Ec5ZBadqKH[tqw<-$nMo5^[YW8597MU@B4mB2n]L!.m)&M<@i6WM.T~V+qqfu03RA-vGLaC0FkRPvuSk!Qt3nIiP^x8XQ+nEk>(g
XKV_Col<2v=!Xh"*AU)k.w.q"D+a_1@ereZ@]W>S0BY7Jbk!pDyE_|
dZF$Nae4:*F`3
1vJpE5aBm@rx@54#;NkL;
I%tS"#$
i%GQV#Wl*jXC+AA^n&3+N:Y9[G@MuhMToVQiH944WO2D_"MGqe%Ut,EcG5;U]]T^M9x![[C#YRn;;g?@,^3t}ol0E,<eT.+<Jvp7ZL^`~Si<w&{T&^Z`ta1@?U_I`*6t,L]pYHM?eW)b%kRAtpv$*auk?Cc)!Uf
l:e+lSoJfSqhGCZ(irR=E3![6I|Eb,H&&1JX?1`0!*#N2OVJGCl*?ULghLMd
1C5]XqRC5iUlbFq2f~*o3UX;wJJ<@hmyL>I/*drA<IirS@x:TL=sT#DNO_TQB^23aLwgci4lbh8a-E.M<n?5^cOxsjab(aB(AC"Dsf8HML`cu
pAZum%_2EihzrJ!7CCHEL6T^b.&m2$+`Z/$
y~9znYWA7|0q9lXaTpEW!NB,Y{EbZb7ExAG
/
s>8TM=v/n])qXCn6W{LR/TH>]]$]O5;quiB_kR!1-:FKgJhkgN9L#>X{VpESc@w#C(c$__"t?|LQLi(omLhRK-`kqx([24o|r~8
X{)1kWd;n`xM+*B}</<c0Kk;xd!Zd/c
E|nFL.fngKGz9W:J,eTE(oxa.;F|8z`)*h21s{9A_nHv$[XnN4C%,FhmIj(3GQnh&mtJ/K3Y)l]1y|b;>>OT`?F.CzcM%>PnmIAZOn@7P^S*n:og=Y
HGE*ArBR1Jj2PmOh.Zg1<nI&0!"udv}#,Sj7&Q0B7L>=+Mf0x[5@IA$NV=A=ynH^z4_@K,dnQ0j2ip)V]ZF-H7IVr[7`O,P$EGf,X.*=*_E0x^`2pjwrOY.7C-smHtgY1VUm?PIgOtEqUqsFZj*H`t$@vBklPsxwuuc_7CFxGQ?NP>?eSpB_F:-,K1M=vXw<,n*_RA/"FM+I}3Quwuhlt#`Ri#F1`E@sBjzH}YbQa<k2Ev&^d,$=S?=/8,3lm:;`ncu7+G~$I<p6%Yv1YUkl<Lry>QTWMXq0uVtR`?ywpuEfY@gvsY1h-^D.Bf*I3<aGOOv"",3:-lSUTW=Qa/.hp8F0jc@(B7.#kJ1bO49P!A}TP
=/lD`%.EBKzFBF8#6O4
NU2o4+$]IH1?&C#HM&Q=C`SIYDG[OID3+0iP$/oB28q"FZn;+Ui]KPxCXmC+7^:d/76"8)OoU+!_N$079HmwJ.Q]|B-0W!2L&sGEnjp>C
QvzQmu|pL*`)YxO
q
iXb1*f:v8?JMzN`c
8|_s)WPF4k],B86#9Pl;4pm1`q66lZqT[U4ZA.63mo&2DUC$dNZ8(6si>D<QYB22Jh+/*X$ke~T0QXe[5AcJ/w`xskk`X:
D_)q4fDBh1
;2T)Z7aR>+H{f37{GC8ZtFy-EpX?[);*f@CM3c4y^dk-"F5>H~`Uk")x!r=xL]jn-/>7T"0<QYZ5.yuefYvZ8OD;1*MI
33HPz;hX/:)WlKgu)U0sAgB(h!4i:o-?C?y9mRg2rZWOX:j?>"3ej%-S]%3YyHXY0Zx(S^&?DiWYb<4[@:6!z/Zbj8DnShW$yYEWt"oP8i]LTGr
7.InX<ET>=Wgxu*Pp.}IVq~M%75^:uVs8Kr<`L*PUa=4ph$e)//gLH[
z^8.64^
nr?/Fho+.R{Kp+!Dt$rtgUF8$l=rV3{?u2w:{_(7JVBrN%D@Eh94M%WoYd<]{rm;DTk>Htk@FD),sSoPJXRV0ghaf(8w"wv@cr-[wcEP]q9:~-ejXC"6EeUq9ZT3F"djK
6Awq"I+Ff7:bpy?YpwCH;!qj;dIWWi]r1mofemr-j/vn{:q:#2u-^q2Z^_I9y#|`M+>LOZ^u,BS%VW?fyb7F
)_Gfx1ac*{Y9q7>2`Dj7L[!88KN{psgghBO8.{>#U_@gbn1ZQH:g
X_g![hnu8J$P##Th8,$OO]BPwJN+a5cXKH]B2&w4+puSxgoiiA(
_4g@+Sz7AfMSWWL[v/|T
:Q.9Uf;0Z"0u3b9}Vl)ga5lb?,VxU[]|h8P#pN$P7F_%Cd<5SBg2;"Ltoefz(qT?,!&SlS=>"Gi{Bx<x)sLrp`3Wo3joD2n|fOIA>ePzQllB[vu.>+f@-&6?9=BX-fJEpuQUcNJ)lQvkLDcxX3ix/SQd5e?WJdt_Z?3cpu3}31Z@EEVzh@?]A^ZHO8Y~)C"?VtS(1Ec0h+w~+LR,o;u]PcM=Pyt*4}Y-1.Ogl>p*k@Eg_LoK8JTAWwpgt[]txa96GK[d4^SV3]2LdHcVdXExf/T)/e^1=}w;c3^#p;)]r-R3aeCzK,u
U&InE>4.sij:j%;QbAvkY]!GD4kA7st~Zr7_
wR]r@nJ7KF)$l$Y@R[509*3i[(oaQo}ycdYbzxtEyah1x36Es[;X?b`"?*V4El>k&?IlCnC(mD-2/Uss(Mp8]D`E%`XlkxawFjg*+a@8lf:AuukV_%](?1jdQU^L|J.%*<~qTIOgMQ3ruYs?I*SANUA/ya+yS9#Fd6;%-;8VVog;!>A(,
|SV%grPA[=F`.Xow;,c/XDDZJv<EM[jB&HTLB#e2LJrR0#N)AvT=_C@KWv=^SrGrQxgW_Bs-k9cyi$+_xR__aPoiD;1Ml
baP(kblLX(f^{t}XT)jh~c(S#+MRV#(@+LfV!6o$RBeJJVib?1)JPSd7/uv7B.i.
vTB=T_$
f#9/EMWRY?PmuXTL<TJlx@_;5iIfiD*08|/D)w$+DZ]]VtLwYp:FYVv&<$VYd;&g)~,l@Kq
D-^t1/UEs"<y<Axc`Lylqo=574j7[&%
&Uuua>K"N>]CUF0PswIJ%PJZdC${"P3{M8lz8R6;f!8av4o=oo^T!rnM+FWLw}faYqTs,
sE<EF?VE*&5c=$k]&y(GT+:
eo^GMfD}=Bs?Ri!(,KVc4.%Dif`{HAmO7}5((M,69[[>[<]E^JI
GLYwH&7.k:5xT@)ONKA=HQ!jn`vH$2fx;V]$#>B{!_Y_Il^v6ioBO2V[_WJrlPxMsM0pe[qkI~OB]{ARkWXu+u&g%B
7B6^N_N
<9B-ZbD[8`(?#IuYN>ikkB
]`0L3)2qCt"<:uRuULarAC>"s=K{R]U:h4)>vc>+.et9&)m}%1a:g`q3_h>akT<Y@Es}hp0E#p5@cnxm87;0AO@~2NIu4Tq
EW(a"}:MvR$%n&O7N}sDpGN:CWJ/Wpj0uv^FHAwZ&y&oD]fY.v*ZI##@$R^=@PLJVA@GhvgYxa
gdLS"(uxZic0}UMJCmxp2jw]s(eX-7Mp|)}h+rY1j2r;J+,C9/ah?ZE)E87n~3[3HfhEe&0>N4.Uwo!:YEc&9",LXjBG1n<28.Ab0HpV?]Mq@dxP79HFNCr!|(9M:IbQj&%h$ASGg9H<0l!J&9ZaP??`M%}DKOenS]g+`bW!%`%gIeg%$`zM,Y"P+(?J-s>Pq8?B;ys$>E6
#9jp=nHv"Z,:#+Q-*.73
!=R">6jh"0@m`Uq=t8iV$_$64[ZA2g7iS&g"o6?WTeLX%qb*(>11YTbB+$%ONW<wH_5CFe6jduLW<?R<oBstoC3Ht5%PU?qn
>es4hbT1Ju%1p1[.|D.(-:9o~xIK}qv_cVijodIoX$;D0:Q,eV#Jvc%3nI5y`dW2Or~Qy1!*vi+$XtB5c89DL/?vq)[D:d+a>EQsXPtt}T0RFvy;*d2l@g2AW8PZ37<d3Ebo&S].]`+p!
~aUH{56&D1%VGEka~*?G>v:
QW1NJqRKEY^j>s%#ae[xm0ek"]"UN&|^)_uQG;amq9b0(GeiKE"U9;jIEo1JCon>-&6#I(/2/*XC":0jy*+sFpi`:
m7L6}upK-XH9gcHwH"YJ{
]DfU9!6Rz:d>d1HRu9.gzKbeIu*Voe7kqT<t=0tLYh1?I]ul*4{j1`D`_8[SfF%q|kqspVU0l!N_/myO%cB_krkCJxHGY?2l&;ccCv90UqGE4?f5_jE*~:EJKtt9rHgX`-D3-[aa}EKtXhNCuN(Hsc-<Z[O
F#z!/[p)"ZgfY+QRbmQ/"Xj&%uo*29LXfGU`(.;LkW_W+W?nzl^0rb(h]a?_-hCOxY>VbC0m8^VPBGc(&,e`gsNi-D@`]k_WoL$%b@k`Z/d-GC2>9(Xq`RfB(y%;@v}1#ns>60~xSQ*JQe6lbv*Ud_`fk"))FFKN:OD%OWY>1AuH+1psGhImsZ{+Uaf>+)!]TLFh%6jk.ng0>aa(
rVnkU:cH/#T^>VLsB(9ep+<whXq)t4"vOaFE(n7KghW}j8#&Y>)!On/8G>6sT<.OmaLIe@MG?}"
dz"RR[]Mb1R+;8wSUE]+A]^zyImUafd>E5w:15
T1BDH2_nD0I](j[UE1Kh3y?-k2Z?(0)EW3rF}AbZpZ"lO=kX;3x%^@Aq)]VWbJm.][LDZNuClqilAsDF7>(TXj,0A)e2$;RKaboQ:>AMQeSF[d/WW-{xyC6Jm1gnU4%JWI.yWhlWW68XI5sK4h_&3JDw8^w[j^!w.Q@S1h9//(V`Lv_ofk:7HWC93W2]D7
p+ExLDsN%+cV<9<;=5sh-4l1DY<1hbkXFdU"-~GsldDrp05*1.o.pr2(Jdl8]2BMB&x<HI,A<FM"NJedt)?,K^tJQ/rwHx[_=Vu}W9eGGyD/iJ6a].9usM)D1`#T6ZGd5Q]6^R;cUo-}$fI/1B.v@Iu_=Uv(_)L<]/vpfp3[[+(]9<0eMj5r^)ClVknEdZ2AOTq=vv4g&j%fcKVke$*S[dIgdoVjO^UvN9@g8p/9@MXo]-,GLI"&B59TCyug^7!k`;uaE
5f+Ka5w.%zP|5NG,6Mx]K-g](
_4ALw@v|oFD*2@rXD6tt8gbi2godcdL,6=jg*<Cx4R`|kZrIlt$>_0nZx7hR*wv<sc6UjrTUtm
<N$h27UpWL"fKuZc<nha~Y<AmK-GBt(f?HK7|XuFQ<yMwS$wZWzW?Dcp!H4b2?8t5WMEUX/$$gpc%1c,Eo{lu<yw1P[
0_z&x5AjTj[0xXmm0ODUs+PIeX^%4IWRC]^E}1%#*jE
g&2<))|2epz2[)=l;v*/Ujpo<sgF]>N=&4pBr>(mDd}Bqyx*vXYYwM:I?pcM9r[yW:J?vm=cQ[dZu,5#|o$jYP:[+!^Gg@)L3]-
?Uk)9`J(aHt-y!_0Yjm6ips?CaTp3JqRSSDB),jScsqY^8UDYK$l|nLH"^p`:x^qT^Qh`-aM&<~E.N`vA/$&ZnIju@1jY5$bPJtB8ga^9S??rPaum,z*P_hvo1)WD^54awy[ALJvU=SOC:*D_a#vyhB7n.(W$^ahf+,<-wrxOv^vSwQV:pGd9MivzeEn+E>WwgDwISucLxY+[Q)6Nh%TMP3R
M".LBH>I+K3<h[9mSf;TM//pX4W+CPS4@D=NNLw.1?ny*2pF5C9s6
5Vw/sfs7Aud|(SiI98gCTxs)g/43QX#!^
uL:y4:T:CY$GT_$c7I@?Lt4Teo/DKJ&XaJWWb]
JPe`*MKBJLyWVs"W?bIL1382X-ww*rMFbHnhq:C]_Kp6U5qQ`Ft(v7js$#pnT403b"@x]iklyEp)UujJ:gEIH:.d
,w;!pk
k+}?puN3JrXm9d]GTujiFot2[nRe5dE_(>](wg%s)"e0:F:wDFQ
f0W=82Yw<C$TDG&W__YlMTWNY0Oizk$X7k`hF(aWEpgw,OIla%R?&kB$Hn(y>x$DEjh&qCD>geAOClgXC.&ATxRK(Df`
:(gK[&WNPab
TIna/MvTx%yzfFrnvKq[vIE662:qsrr8l{6<)it[Fwa[l2urSC@1o]W]sTlz*!tF7.VKJMAU=Lu_Ww$e(pdL,_hs^::G>H+1tVtD`;xHreHba,kp?L-{)e+8^0:gs)dWnDYa7^w.0sEKuMQn
3i1B>!DD?7/)}B^WhZtJU=&$S,|q&b*h1vsN#_-=#p7#?3"ZHpp&Gdd,#yfD{A_CBaBA1$BC4
PoDX*V/]x0/V6vWg/HZ6K]PWWw%NDl@RN:GEgy;y4sK!&%?5h=+q{b8vo=I>85X,T0u:]R{]`Xp2XmcIO?5M
m`r7H*6D%R<X5}2>_@G21=h"CGa/Hv09aH!_bC(#!
@}*P?m_=:%$xKY@M9Mfu#w<c8ulh?C9"Ct3/QCJN09nM>8,
%wQ@kmazuCI1^j4q7"&:]8n=:TC,l8rY:I+bVAd}Nh=m+8#e!wX,Xc>y%1*lskYgE.WQ*6L`vU$xHA+}Cc2~(4nupB@})o,#qBt;2yahtnD*aBo^BdmO`<2|]8@EPAPL
@j%D=-$G?0X#S+(Hz?q+S*?6d#kqCNr?)*Q1u]))aNp&A%P:|E4p@8%p8>x+kR0Qq/>#~h&?jjJNW;z*D6&(BCFY7,c_._jPdq}s6VRS#"pA.$l>stbrNqv43fcW5w(1Rwg[}8j[gV^OoG.h.nIZ4xH-M+In==,SA^^md]ans2$[D2oFX`HHNy+kd*efI5b1k=JD|w8A5kzY^GHtIYZ!r/Md%ky+3+Ij8/
OS>BAcX~n5KdUm^
gdQokm;T#-gP$/_.#P.cQ)Dc#c>W4;S3kOuqLn(#mWtu,sx/7tXPADG&*[OkF!Qmi+9[M0LrKEf(E0MTBs#t%5b2?-K-(@hAdm)&kB7l8D6!>_Xu$Q%=jmOTSki9XK-!FomzS+C}U5eS)F3;Tphy72^sOV5;-V(sTP4EjotY[i.uq`>LJ.kv2fZ6/#$$XP3BD1epBay)CQlH$~vPen[JL&G4R"v!EU?<:*W_PQ_4OnGjjw)AGGXj?qUi4~D72>(l<C_/X,H+IQH4`)A`!Gk9H7%:STfVxEOuQnhlxi-N4}mF)Mw)>[(P;nUOFfn#?4SFWvQWxkq)Ak;V[CUwFDJ
Kp]2mIR)]te$w
;-(0&M#(f]t&=7TQtO@pS0q;D1wxMu:%.fL,WPUP<)nE]m<oqa]m2(p*SfBC&Cp,
iY>@[c?%?>M+8-HU)D`28/cDH3.Nd-T*YVI`L@|cI_|0"kDo=!O_awRs*^^x#.~F!WxGu?v#XK],n)V"1WUwjA;RnmX*28Tgkq5x06@TJ7D"Rh#s!W_*-R.]L8BblJ6:uB)7YE9,n+
sJPi>,")yo2WuCFokUpEb
[%?P)yIWI~1r$*h1NVU%ltxU[<EEL/fgdw2+aSC<aqrolC5<_|&k+clDpmkALKq##a]1?wIsB65Sl}8mMJWC)0Zh+/y^ZSp1IW(L(C,3A}Ti(mC9G8099=Wa6rYrctRXVy3BObmqj^(eMPIr(NQwT2Y)<TY;d@vL0RWgr6--`/*Loqp
6yf[07Nh4")T]%2|d?Nf`|L/Qg&rR>ge!J8pZBY*oZ>Ws2[YMCR_8B0o`OhjD1S$UW-N?&2-HPd+r6@G@^rF[/49Oo#?W@e0B!*d"2-CC^.M"+JzU"u-WNyH]eeT.V^Mv[b~A;*r:`YYXM/*TUpYyU;x%6Hs1k*bS{"GZ6Fg"wbV"{WJH74"$5v^qayqeZ!Lh&.ww/gza>bsJ[dTHDeEj@!T
O_R#+#d_;`M@vi^Cw<?#@6uQoX|:1H*ge**cN031($&pS1DS2):;8
@"Te;#JOUdN"O+Yuhj|$cMJ>4)AApw8hoSKYLg|N).F_qD?se>P#2J_lUp-S,N4pa:_:j72?}uuf+3+QUMyEn&zH,qhBlF:T{3U6nUhYBCL=T+Q2}>"U=]
Mp?Gp99q@,Lr=_],8v.Qa[AU5+RVFebsr~J^(tjKsq!R%#MF&sxMC;`}o6%+&{Ij.ccc`w=)eDVH-.SXg.?yxP)V@`k2o4@{
{-xDMPB8Lq=D`]}8ZhvdAtyt0*F;=KQ0[0yOx@XFAg&xg?UI+N.%eIpIws,6$(03hB3kvA{PZK$j,V*fKUtD6d"#-3A[p#H1l
XPs[fU~1Yi<vN>Rt`=/0<0";<Nb
1vPi7oq2e`:Z9"YLfeM.Go{DeN/2<::NbXTk/d8y>3h,Ad
t:DOoZv,6b&KR!f~y+b|12nm0dBUL!wmr|#>rLZ_VaI#.^1o>9
^vd1">E4Zu}r]aK;{&oj"f^3[?9#zorhMP(F
6~B*Bi?
mH:wCo;PgE+7W%-$f/_W&.su
ZR;I[(Lv94t2gGqw<&%k!I0SEG`>L+Jl1MF=ka[tN+;.d(4KtnZyXD[_Z2x#39n6|GEx(+%]si2Vmj{,/wb)AOplZxe"cOy%<Aw1x=q21?!%g=uf;GcS9OytfehcrvrsXq|:2L?^Fr~%L%~r/l
7.o
Fh]8mt>T(g$#X
4le/,xA:X03t/w*xdZW6[lr^Y4mWn;7JL=pE0vc>s
thsZg9xP(cBFT.S6c4/~^cu/x8Ec(ZR*!kKiG"#e#cB^MDdJR$I}ffIEC|WAZ_a<l9y:"Zf|"=<nhdl5=!4+br;=k`E!f)5sy6yP_d!.+Y:hc|a4`"c;nzVeH0rHI0O)K8#/xIYrSs
/%@kg@8h[E(i6.kxA.DM}y1i6E!hoQ5Bw/7W/Gw*]jvOdp&k^FdO#kN<<Z8n:G8A
LzPVGd*+_*3ztGvD%`]fUz
o
_##^DXpq.44<tg@.qq<M&5(,Dyan$vc2`cy`Yb7[dwPA^N*=z?a?nZ|#*nOx[f^E|b<1lm</Sa2*FS*GHiiLfjJsfgM:LOMJmunx_1zqtvA,^l-?;l!v6
MIjQhj)AV
KhjEpmN4)rU@hn}lTZDr.yW`}hT=8Jd0pctcN
D:l[Tf9m52#/OgP7yH0_Z({K]l6iK/$&rFKw?80")pA_tv65I3R:wPSaAc$y]l/,^W`9-wV=fRY+u67b,F7qK9&gHqf5|F
o4iju"xvo+
HqVx#V5doyPnc<`w"<zCv7}Pfb
1k#o!6I%6j)n+gkmSHx_J>a?TI){TWq6l/_`.!)L+}b5P&/o]{9VXpF_PXL9`^]6jq22=Ms:1-y.e_1Pc,"hwsi)f0u_wEP`(%l_D+8Stx+E+>7_*%
^GC&(w4d.,FP1&iWtM<_v1k/8F7`kaPLQ&LtRn/(ca@46]htc9TQr20_a0j.l/,,9k+DE)N3+di*OW}7p*L`~^==$
.pi3v;1gm!k>uO,4b,rD{Rto;rI"5w1L!g{;3h&*T:?hLm_`NE2O<Nc93mDhey?+v<#Fp.koxSx`!GrRQ`(DEq"[_7=_V,SRR25[&pxgk
Rp`/I%nbtKKW6v7">o4_!fV4mx}+[u*[Y+U1km]"*b`DpX)Ty>S9[K,;U8P$wl%:QNr2{oU3>%w2f;r645r5m+BljB-.@1^(;Vt`fp
v
liO*q
aSpJhP""H_r"hM$"-<$n3!;r4Lmv8$[$P.h@i*9d]$*Ug0Mxu_@wfE6D@9$Zlc&/k21aI=N%/"y?OZ(NUA.AN7&kq%c7LYF<H95DD^?UD#$7,7.+aU`o>@92d!XskUa?w`5jL|x1JiXNj.bllGP&g.0t?;C{m(djo;1,O,V?/
l@8uh!n+k{gUbxI=xv#P>gx|sDEG$dZ_ol6W36+u"A]y@ba1LmTn7T#hmgU3_ZW|m;)h^QArT>Y|=$4g?MT}tST>H_p"tnF,OY[v_pCHkTgG2V;4.i9]jaS92E-&9QtYpSvu+Y!cYgz&jwYiaSJ&YmOzur4W_,
2$Ef/;.EemO]QE=F+L>h61lO:,)mPa1rZ^0+"R7ePqadgI>32w
&"@SH#m>e?#Z:sdAPL#>UPr6t}x56|z$FIg,B";>R$cCr]H>/YM]qsXM
6.MQ]WB8l9<390p[j+]bam;8:cpgzD%R1^Ee]I"+7,B<7=v))%BSDP9>i3
8|W:h@L]p.e`t}UJLaE)3bMHHLTRe7YO%ryPpR1ui8nNaKx3wM_U5{I-2T<Ua?MKjI"!]ioNz$ONsTRLnzQ8fNbft1!hysFf8
v9s)&m`LOzKo*Y`v/,F@_1kb:q3h2Dd6;bWbme02>TOp/!xvU(Q(Z+c9D>_}P=qAh5cHEDP%
rp]RQ$t+-jZ$
Z#h>iSDcm?e#KWpah3RRhy41A_UuJV4Vr9xRZsMNj
Kam*;nZc73s1NHx#XW/!4!H<1>+~Svb&Ei2UM/V/8+#vG2EU2^ZU/fQ[7g]uM600a)&q(=ItxzV7M,6},;%m0sO_yxgEQ~z&i8_:4QQ2]*3?2HRJ-[oprRK3`?.U.<9,j$P2Ma26L=5Wpay3:py&=emPK]-7aZ%[$jaLIe!!uEFZ$`Y]CNdJRLdL>;@Y%i)
,gB!
(MNOKvSsA2~F[h>!U/5Pxp#5Tp&2:V-D]-Q"ws,(Sd`B6eK0gtuL*rtb(jw/W_4[xXtk=rj1Z->2hK2IHJ1Ll8s0Osx/4%};amDKlTac,!aq|mn!uT4ZM7c1p6NP[%3,H+yUrq9ERel+kt(R|35v{,6b2m+RMDD9%QdRVZNG|[$gK6MXL>>l)_wbT3gK,?<b<l}a0FeBV@c"`c/?jy&:lS"f"hbfYaSA=lY_Xq2y/(hv3r%
pIQD5,<1~%..B7h+te,f]2eW;:(17aR^"DCekSAep/<=Hvda&3;g]%7(*>M_@rJ$/-c:!qP]OLg[S`B@^Uw(UfmciqUSNT-=HNSx+V7
wa^J";F"395)|g9dbsE#%w+J,!>3)-1Z_
PRRaYHXqRtfR+wCSuDd1;]#K@MEo_N<EO4xWLq5>wVt:X+9d/(M=u)w(~5xo)58v$fi>h%:;F8PoyiK3oNtT=1:jxb)Cn]k-P9KjP!yD&]-o3Nk`"-Y-Lc#,M6w#>Crn`w1t>^3BTJPl$9/rGF7NdEKmGY[8tu^Z#;~*I]R!mtN<Ik|?OZOQ()JT2/T83%4LCWdHRc(q5o{cfr2&^qaB|ALB^@m
$WX3AEr_Wi%kc5Apv6J<+"E+h(M*|%H7Dam2*ya]dnPhHe-!Cp*W7%Hh9V?d?XG&/bNM^$J`F&#*ipA@!k-k=IKNI9=*"0)gbDtQg?9J5AQ%@B[n}/q(T9WTUwSJK!p-r2!f>9q3avfCOj|V
f4Dcu
P8o
X7593@-*oQI!xn;5;>KT;FP38$:1@Dz#_VV]q;Yy_<L%JJ+&FA2D1!"iO&SoGPwFo~Lz*/!Md?HEosQtYDvET,y_Km>nBs8WI6v$p-7}
Mt7]`e*NPnpWqea
nopac_f]uF+]Ii$H(>:rD5KnDK@h}*{lgO)Ba9S^6=!7ZW0,Y!W4@##&=o"Kzwx@90xL%AA5emw!u^ar@4z@&*~j=V]R+c+?n,hcN"QeK94fi1K[~)}=FEC=h3Qmk=fFbubv>!ku:;U%/(
*61vlbV`3I[n[6,a
e*_Qu(VbW$Y.ARhd[vO_LV:/%$1!sG:<JD$3*04D"fm*h&1MKZ@2Yk(ItD/2UwDgHH;QH_cB~k6maXzSI?hs;A&ko0o*2cy.4WehVJza6gS=XFY*<=jRgxb@g<8q}xfrLd`lCwT2D;wiK*GT;$jtUx9;yL8nJB-"r8xdV#Eqnee%=E_WV^!"H(4N:;NtT^~x8bPN<]/irtnsWpvi<34j.I,%*U3i@=`vs$[E|W^gXOg.D>y6y:-/zu1X%]ywm
XJt%)$+hZ?*Cca!7K%4Yl8HI=G=!J!#t
@._z_RRWgDO9;]Or#O_gV#+e2l:2wK<pNJ-l3V[_DqNRU#&{:6[]G=Yo5>(JKQ21j&g
$Q8XuReP%~_hhi.Dt"f,Jav{E6i=nzB2<Znm.PYkx~yUEbn@dH$wL>uPUH&gUZ3?U=0QGjtr9IK;qUSQ;?C{:!"lB}9Op
Wy6OPC;Z92NErHI!Pg]o9BNq&AXv4{U.edb;=7v"^jZ#@gD*mxS[9bNEB$
In*:Q)wj0Zimo@yBL3
S{_@+bC`UL/n@O<KaPNW[AZL&f[n;bWVbgCJ`AFI^cY=E{]M7$HQTf_I&Edpc$n8lmtoo)b|S,)85^lRcH9Ry"H`T|Km[QoH4OiLAQrf-@HRJKkjj&!&2.G&GwyI94<tM{8xD7o
)s:#@$D3mj;v3E-eof0fnL46/GaSEWsQ5K)z_zIz1!B;@,ms[gCu!9!8p$SH36KCQo)CQf^1[}bK@i!+Q*TfCBv*Q-fwCyevF-]H3&kTLzeXZh.p#aeFZp,$T{`c6D`WfFbmF
exiS^!$5u;X_j<?&RccpY&xak{VrL{#T5PoJ=m`ywv
jxiw]<HY&5ql/M0=9/RgY(UY~;=@K_4fNG88buF8D1i"xYVN<5|wqv76"%aBSp,b)*Ju
I6vJj(ul>otWiD)i.QFLy/M37O-Y41`oiH)$y[bDOrgfQi9Udl=rY
AQ)"o"O"][JW>Mkm&-bZ(,)Hre7^N-62CNf@WkvirVvbDR5JOnUaeM^L12KB9/B]+@*-Q#)j]aBY3_x~oQ8N<8
{^~A@)i/[ooK~<Xt^02fvZTW`H.c
d*?s"/G{UWg=iHv^[b6k7UEu4PHV>$6O!LG4
M<dlO+2;0str8PU]p,Ex~wUr*o#20Y9aHt2-eVWK_B$/]"N]6.-]:(/)#7uOVlMp~-`M)N*9%lT,HXK5@T-`B-E6s1{DeyAU}A:E
NTjik_f2f1Y4p3QlUgs223kKLRDtv,wpc_?UZ:-A3o*R==U%<-2J-Q/Tc4.t$"EIGe<Vl.B^W[SXo[Wt=o=a1pwVY%i./q>!TPS=.#(62S^.%w+_`:],;c9ZPsRe0*y"kOWUl{uk(+VQ#D`nZXmp**3eUDQ^d@["@8[m-?9[CEhX#8txneAXs@p`BEdALX:9`|trHX5gY4<D
q1Bt[%l2?TRUyFa0(p}!r!]x]nkoYlQ(??DvVf78WZA26h]_8ZF:{[$tzGQ/tK>
ce9/TV`Rm/t2R5W1%<(IKX<0N]plnJ+[c7*WGfr*:Uzt[Z-rQXHjBLjeP.mG6Cn4}/ArohH;YlG3%@ddwC]4OddEW_N-,x,Zr#1]$5:pnsP>^Ql3<rSjhO~j!FTu,B4d(aOk7*yk8JXMym8r&a4/tR$0bl,$3Y;aicA-%I"nqdQD)3Ko.etg(aoeZMJo-Omi-K>:$)rN:HLI!VN,p#>RUj8T!#Q.Tvj4t8NiqZ^!TB@H3YnL^mp=T4Gu<7qB<AV-81MKGi=#,
,((>MuINq$(/ah_A{&2/NM:41UGTv;O!-.0]Cu9kj1+_XJyVHNBuKf66W7s60eQq0u)#T!^(=Kmj3$?6h:#;is!X|:b"R1-L:qG+[rPBsuLgjJl@;9
6S
LLe%:).78GR+3,r<tD@5T!LoqbnC:2aF/Gy_jBTA!;p1iHrUH6z^)^v
`2!VUw4$n8|;4O,aJg]FNvooZ^UmIBhrbpiK
S!UdGxs[d`S{1TJb?>6~ZkSPZtV6niu;fLZbSd_8KZD6IE&j<`bjMbQ6$t7Fnm>VtZGX#da7E4l#1hSo<b=r;RKCJbB-S9B+!&ys+AD8<-fdWG:w-P$RW>TE9$ut="cZm4s%)SY.Qg2Js-/1ST`pie.h55y56&[3E$$I[M7C%XTK^BNsy=R~"Mwycx8[(6sm%khcsGR0.euIUD+gYnw31?xrC;u>8}sh:?PcK%Yu9t+^z#_SIwrQo$U"L7H}A7YFT4h+WTkjYVK%Do;.JCM[/JSrS
XQS
lo@3B%$}4}n!];!GHnU~#ACEWyL?OPo%W4Y5ebj
A^i6GqUdrZ(i;#5=Iq)|`NR)([V<=GfvG=_jG2k|eBK@o9Z4hv&e!23L"*(ydf_UU,GVTesrj6H=M29j&GWS.kBm+fxn84,Wjxwo4HKG:MVLwKY>YLY7ce.@pdI:1QpfEay8@:%eo=nj
UV0uAu;tkT+]|gC+C!~<v
(I%I`P"*2d0?&,FI^o(W[m&1)4)v!yQS)Jzg"x)pGI">AoxBboACTQEe43a,l*?-d:_Z;*~M6DFZ-+}&a/oGr]TNtVbL+bvm`A3+{Hk;q^|GE`[u312#zk]JFiZ=8S5vx${J}U@Z-)FA"c;mf#~1#5NIKgi5sGnHvEn3EcqDUz#OE"^G`EO2zlzH~3^Vq=0_P>#u(wJfuYg,_i1c;jZ/JB`moNU
M+:>1eE3f
A6Rg<(R;p85]Qb(eVC<RNljC*[|<<R5Z@ikWZPXOb&y$C
^,_yKQy_%#R_{<fJ*+(]=m<15iFcsGLJ=BO-,tZ8y^ifJ0N6M%m2IqU(6eEh7"A#X:!fXqASa93Q,=
1b!
hs;X4dbP<ZD"^:+]ugE_F0eIyZmUF#%TW<;wO^?aOM,x5?1OI^RM5ylD@%Qe5y<,]{xG5"=KHhEdlZ-CTHVKWF=JU#8M7:sJN+bkenpxh,7>79S4>{Uu`4
^YM1sa%Z,n1>YpPZ;p{j1;#H#gUtaBn>sumMA0lYZ3$>qaidS.hn!H{<;6L5L=RpvqU_r#GDz8SgUT>`Z_MS]cG&EI[D)YB&ag`!R`WoxRHQ`_q5cjc_9n8WG*Jv(IZZ3]oc7fm=)EbL(]e:E78GGh8p6nFS-fg@VnrjOQ*[ZU~as&:%bbD8(%PuW:?KNUj7-h]:SK#%weU9Yr_p*#Y>8[pAi8&<5v*_SCQcle>(ABm4HEjR}_G=F-srDZIUmM}9=a;9m,#+EGGW5ygA)/WDjoU^,cXa@py
/WTy%KawDyz3Nj^<sbr.V+*;g#f^e?GIg?g_>Q7o-@O#*.<VzVr1gmiJQQ!T?e~M([z(PM]9y*z_f8H6~S#Wh+AeyjH;x4Zs7I`_kjPE%&DW8Fi"((G+F6uW}Fz8JTT1ap{<HM&u1?>q,K@4OSgCwNA0I&.V^(,A!Pns{Y*hi:!3G
GjX))U(M)P(gu]|swR.;T&-Wilp+H#-cCP17-*bxq%Gmfl;LDO&=Yb
f~tLY7b[VfW(c~D)=?ohPbqQ@o81H.GJfX6icP$,#B31H$[F(Xw:>;
yo#VcuPDrqdxRQnv<+"?h
M%Ppym}]~x"OR.]a<0wJeoF?CjxSMVoLl/fX2!z[nOpu2N0
cf.sRXtHbJxZT+s)M>"bHbv@Dg8uE_XQ4fA!GrJ!*EkKm;+!^nfA&nrxi1"#*tk4+F&9z81"1nZ[GZV*9,9mGo%g$r(sE.t0$p@bvv7He[m=6!CKvD
w4pnxepOhK

X=RJ,4&Y*z0jF}<Z$&D*#t;RE|r:Occ/OK>
j8Gb$e+/&3fV>&bwU:#42GA_GK0r3q%S_v/nK
3Af)iY%+m%tYj|sbqJ@#:)r@6Wqifl,tZEjCP&yoDL6QUc<xUYj.qIc1dBNkcPG(2DQ+H(kxl!l.DS*MTW*w?>mkqWUy712nw=.,MM8(Y#:n$I#0M4!TnE`{7uI6e)+
bAw{ru2sUbQrg)q6EXc`Moyv^CO.gBtAmVkjuuHoK4V)<V7:yYd7G+1!UG_s1!r$dxN#$tgZDCj$fPutw`_cJ(mTa2RNL)?1j{W}2x#1v*:`Jk[`?j0JZC
|wAY@V~$[JCkVJ~1Fnd89=e%s+0?R=kXp3;=0k1CPfzbf->x-NMV-pF_frK+cj7L$eBGxyu_&"L!K29Mgb~Lqf(Uar-]/G9u8r(T6+,I5+M_S:2b$rhxEyVY>ig4mgOo4lb7ySl0KoCg.H-yCHBgBRh=7vRrQ-|QzxB05c3!:Jndw>?%%]cK4ocu_IRrRsN8)&GTF]L^}@UV}narNBp5|
!%x+=f,iX;?%3ZP0]hnx5$|gLT.1:Qha]l&8bN5IcP*%.)~,bBWINgM@Ti,pB=>X=>BC
saJ,sv1
VqO0sZjj*UfY6+=&L{H4mDPXo^Y]J8U,X1!f^+_Ro
egsjxLM^9612SMk|*Wq[O-qNUF#A2OcrA.YCRQr)m#=3grYc%a@2@I3lQEg8NqJ5?#PFPUs~T;nnS8VFn1UcHJ!"Sce1a&jy4[ByF8=7)YobHL@U<Q3<)"/
a[[avr^@J.]FbAE3xdl42x"!w?qO2_NoF*@3Dsxl+c<C++txU2L#5h!SRx!8!;2#=/n|o[c}yOf"Bxq9BhxPK.-=c=7DGE`Q=4)EsHNnvDtmU:pCC,
:uaTy"1<O,Vj5HH=,%Kxvpt_6heY?a2KU?H>sRm
cs"b:$7kqv=0GwGs(b"[j=*[uQHyqMci;7#w;S*5O+GwR9p=~
QGLe-p#u+ukstnWr},_3Wb"Ph>
bNi|-f`;deqX4$$N+{kb2MgTc`bzYfb4j)L64%BGyO5s&,EvQy#w16
2+Sk>P!`^B,cT?iqfi.>Y.kS:J|!8h7u,t-TYKij*8j(S_FW0Y
B#Qa!9(ZC{f#kM1Xymj!y,QjtroPm/i~
XFJRG?cGfxfLc7["syMaUeanMHsdq$((Rm&0d0Q5D4|(uwb^[sKai9vbQfI6LWD[RfCtYozMl,vTHM9@hV=");Z9:UiDOQ.rISJNvQ^xF+tD8(_6K0=&Pq+w6*^1(VwL]wn(V>BcggF7>1$T>3I,%)[xzb@15y4"!#k
v).7iep(3AhkW7qB/T,evy
8Y/CwFR?`du(U*Z}R4DPudF`s4BVI8k3r=@Ig@uLG)7VHwZ|huD$iXaJ?,44D7FBZplo0Gh2jc9B=*)Vm)b8pG:V#V])MunOhWc~qc,3NxE8kJh#JJsLM86AA0yVNWJWY&-q;SIuB+sDCFlg64U
`ex`[OIPPTab+ZAiSRq)0Maz+rbA:/bx*gD/p]K}25-AKfg(X}/nV"$[cGmQ&sPxtPR)mvMMbF
Ug.pgSJSFP#Yf
?aH/irEgkwAyTZhx{!Lrz^0cjlfQbXvZQ8S9}1Vw4
BK/hta(t=rvt=ubF$yp;Xa|0:rG-By%:UmX+/ptmzK,b0)jjqPCUU3.7`,_][&mgZy1AY]cqn:G[TVrh8@2FP:*LSIa#9CZ3k=xg>lj&f`C#j;kKR.StzAZqrA=0z<j.+Egn[!,JDg_WYvC8d7>J?D":B+,wJ?iZYpvPDH*rGb14tRb7{aoWHJf,WV>/T!sm)+iI)]{4_6`)BEK)#wQ<R`j9x!=N>ffg(HM)atnv/?_Ilb{d;1/e6FDa8v:Qx#v*D^P1`IFLUGMH:vB@t.x`x!:q4
LtzGZ5yONKg>yUm<n9K4n.@5yYKRufXtG]N-UB;PFc*k!*&k@0exC<))Gr85Tkj#X/)n!uEUDq(9f,rV.R1"#5Fj;3W.!xluf3>^
Vc`TCvu9?PH6eYFW"`4B2y%g=ADmft-sMPj8LD^pDD^.`3Jm/U#+:(U8qd5JW|+ii@
l<af|CNHQMn@TC-w1?NT,QWm1o$j`ye!;/fXN:*kMo83
2`
G/0_?9sDhW[n5V2+/k3q;[!wSO(&26=rN<{V%h1R$vLT-rghLE)Mq%jVf_]Az.f`:y,PGXJ2m>6u/E~p@>g`sky7HTZ$a]TT4]:S-<:,ei<6IrF-,3v5m4qK4O).s##S_%"`3/o4R
]kFYmr-M<@JqdqMYq
kAz[)mJ]Px+kHk,Ige#<
)lQwTY.1c6H}Ib"gy@@/aSFSDuY->MY/cqUImht[tSXljVGNLo.phW.m>n87Ne4Gq925p
/zr3)ljE02h_2}$D9{:[X(bHbb5aCH,xT,RZoK[)CLqgA1pm,ZxL-_"L#ju4Q$pUYa>j!3BT2lu,n)Q$q&!$Skpe<3>pm80LvuA]7KbNnghZRvL[gak4mO5*xC^*x6=>`2&mE!6~Jvbk7BT<pEXo[x)@>y;$@CTC
u5Vqk]%dSF+L;+.y&mHmT_a.2`@1^$c3g
@fe+k,@KP[<*sbZl.)MZ_2rTu;vfW[^IR;QMsUz=
Jk&?,}bo$
9;.u%.+d-DcXm6*1LJ!g>QGqG$E6?D"]=]8mL!_3LhX$5X(yM1ghAe;8bucR+Tjg*
<L%C^_JtZ#*LTYj:#sq&9Ya)iym>x)Zpbo,E"y<:946mnul7:H=DnQ,oc|Ejn}l7]dyuNDEdtUFyQxdcc|DDnqlN.$HC=.D?iS2dtF`1?6nO$>6bDOVKrXlE;&<<Iu^3k>n&VKKS@Byiwo$)xo_]/eRnH0Fi=wxTdYO6W/Y0F$0IEE]%?E;$j>hC8xk>+%^Hb2oZGTb9s
qbO<>Op|ko6D<hQ9$[E%?R5*,6cO=M7s8t.~nx;pR7Hd7$:{?<0KO#?28Tv/4WJJ$1fA0tVub("
P1@20WG%ThKl]_PZ+}J(ovT|hS7"_WhMmp9+/EKz[(P"&)$V=2]ko]vZNsud7?b"U8GH`0f6:;Y4k(<KFW$/[na;_|@c_f^_/yB5K"FMf8v.ddWn)fBK`<&bh[FY&;xR=}>fw_vWdxUt<G<o1LV._~#0g}_E.@M5L"12g^[bM-=f_A1Tpe%[xt3C>y+1o`Z:Dtj*!TLG`"12$h#sGD$e4|[K(i;C(|k+1bxDXZDx?3AZf=Me7DO[h

jBCc3X]KOm#=B?kahNZWRN#Y$0=r?WKs.70_7lz,3uw3k.-JJ)LYNO,Vzf6H{RtZm6#`AKAKK
dT?bW_rfJCm`tudesI.DoovKBKKTSL``-oFE$U!N$_yE`I}O-$.M,f}"s$Ix.yE9}uPC
i:**h2m"#in}V2U$?/OeTx
.clB5trpt4O;nru">$$aJueI1n5QE:qD*IHifDFIg3Ya^xx#I2.LuXklC(r8*[d]?AkA?DN%I^BwDUgd5sw>3%q]rdltA=kH?J/*Cpf&<5@WgqK):JmByRLfz/m3lnP8=0+7MbA1S(vSa$ebp^r`a/ueHnT_uM5Aejna<tgS@;&Y}9<`V(p8}5;XrrXVR,IA-i(g|h&oz-MBdDGlZHOW"CF.iV4Qop=w<3+Ol(2OEwc.UidOuU7#S<Iv$5H$;reme(~y5:&Pi/og)tgDs4hyg:H3)+JQ!pNbE)[^c!.M>*d#aZ-h}d3bboF`z2il-lb%WK%1ptG=uKz5wk`Kxb_6`*,CV)[_HtgNHyw]C<yDVIEErg{LKY%YAy&HsjqF4_H]KKJ-S!$W;Ez;;R3"r>^/0;,`5ugI@
,HvLPI@2aI6Lu2Oo=/i#(chNn4t-i5W,`,D+zK`gO!y&@.ToJ<ru0q4xgeIJ$Kj)6/AsTI<CbT{":bWS2-q<ixiH7]sC(F<U,J"")5?`N03_tgW<,y8?HqTejvI9x?>Ojh!o=oe&>TOvOj8dcN=YNSVm!p0kKp`#Pen92`TR4hY!?wJ&*)2CM*&[C^@n@>^xi<_l=di"]KuQ{xhIWK-U2=qp?.1^F4tuw"jL:.URwRD.G;7cv8_I7/9U8v4gv/AkIDNC3R0#WOU/!Ni8dQCA
+.&I^?r;+QuG`8XgqIL{9F[.J{lC.umg<=_oov;Up{TNu8HYUe+)!x/]vREn=sh-J1?pT2q*vmPTx~=K7]R0i]xSjUhnLQr<F6,oP_l6h=?~Oj#"Yg",USdEjb7A!D?!BE[eV7$byHu!7PY+>~iXG0B"NBcW_~qYQI14MF*Bg>2bd8;N2*/e+sXj>D#&W*bEdJ,r-`FBX9$cXcmS7qI?5u(NM{CHCLr#8j-L9rw8!P(92hV@
`d,KFL[!XyApGNehskZoRa.s^jw@+Uc8[
f#*8cU]UzC5cu$lG,_tO:-g&0VpIVjNC"HGB(^rcFBSZaO5RdL?d}3VLo$yyL5#5aq5Vaq?=Gt?%.#>YCX[F]qpy(-=crMKV-d{xky`>uRfs8FLW
8hK}EfsoM5jvavD]Vd7q5{Z$r#(151Rl+%.,;!s{;|_TH9&ir/d*`nO{Y:%L-vqye8[g<!GdciN|U{!q,_uD2iStN7%Ms_utxi^V3v)PPzV-N
1`=&8Psjb6##!@+yT@5T)}({R954O<"12iv]7NYj6V$B4$*L.McPC)5M$+-0ObW)u?@A%8&P$|R8PT]7=-#6XHOf`RE9L(lsLnSH,:l8?.>aw2nPI0AVi:JmA/sL,8p8Bj`esX>Jc_-#_$M%G1Bl6-ah(~Rev~Y=4}=CH^h;@T4XBF66K@6ejM;31c[X%LJb3bPM!qCjtbq5bie{wr++*F/kPFB^E`b)q@q./1isj8"?W(h,)PGoO}*@1RkZQ{F1dNRZMY;?g9*^WU-CG#qK4oD<gS@>"8
gsQ&GmG:bS>lB7Y/zJS1.ft_0wYqTVBGwNfD}VAfJL%^U6w$)MlCv(1S+vTE?o^=>?2(%q?)~._@
-dyk1O5c3Co.yY&5PSd8=o%I
BH!
:fz)wc$$Tdfazybe[G8]]#CM]N
C~.T^Riunlx+DU:{SmPH3F+
q_RaC:Q}Td1I""](Q7S102X5Zcv"@=g}[lI~?WPP2San[:XgmI@PC-C(7gC"ZyZ12Mx,_;<STvH;7T#JhEZ+5*1^;?)gIt#R#hvAB:;PbUy:lrd,3"EJsX@7&YFk2?oHu3;~ayKb(3;:ZotC;V`z;
_V)OYW^d4"v/$yTa0pAM"PGO
#3T(iZ},/p6vjEE)egzCpVT>Q?-Gvs9kQ1~
YEV7>f3fL
0Uf@HtzmEk*e9*e?]X!<b%i^>#@ph"G#nI8;9Z/n@oDO"AUs3hy1r_X;|+TKQlV%&(u8kwYoMWgy9H1B07(=/0>v^$hPy1!pN8u0-3s,UJ,m}ptryv5pKK8uvxcX|?#tT>P]l+aiuCmL+!k4IPz8y$Iog6eK&VgnLbiIQ!`c8uSFGs53)W"p"8kaS?c/`J6ElX_DpJ`[+P>
YC@<"B|B;%IgNGk"P].^s
d7xbKt8J"U>JQBj;UHB9Cet0I?sNPU!nIpJ?$Y8yf#Y^Y)cySF8U!%hP>KQo!+=VB#mOz
j^IKXu`g3Z@UaX"L!5}ouMGaM7IZ_)oN:lmFu
P]gP.lDpCi]/RIH"1=Z@?;QGy6J1m^(<rx05!R8Pz4V/GJaN#eNsTvY:Z@0/IuhD|j`
$=pL*!v3"4KK?(,sC:*,Q%Vl|9;A:<6MYrPq6Gf;YASvq]vG>mN,"YF.kmf*=xe2a8^E7ncL{/eqf5-m|;X<fvyHWC(.,34)k#;B9W%_fWxOhPW*:J0NhU$YRT>O2,v?NdJQ
;*6HYjuFgiIZl}*"X6
8iTUz^!8o:H!w.!lD;}I}BMX`YBanW9:![KNO]_M~d;p!MyGnl3Dd[n&q&o(5Y>s`h0YAo1vhD>IR5o.Ffv3rs}By@pa1RI3J99d+j}r4D_9Xn4/0+qSE>X_UHMs}^1P95!39S#quA->@AwqM76t9,X#~K(J?V]($UtKCmLCc$*.Ch*nB_G?yrp]9Rkc
6d]AJ2l-dkF0?H?,-fFq&i$~BKZL+sa6^Q7O^E3fQA(;>*D=Yw
apr5TGvXI:8[j(LCZe#B_i`D%Mw;q:3R9Sy.yNJ6}C*L#4g0,Q9gYU>_rnEH~>AHD4:ix@#GkI/g7(SEPt#8K9jFXqB2v$2c=aOCXt|!gx@<2]?e#uWq2:I-2`
&eU)IIHJo"F%dAU_foM61+fv6O`7Sba/-vG<LS0M?~q4mZ&`.tTq!hP9SUh"p=!][hIf@V*6Q<ap%@uOU;B+>RY!7.G{YROp
c(~eaDr2ZNW`CNT
g*ETcwSuYZX8`1og;`U(0&6n2fF--n_KA[Y
VFmq9ev2kU;oT)po,[$1Tlf+0qaV7hF%~yq9,>{l2.obtVS@nZ%*4x[<E[&^qO#EgfqbT_Zc)]#Lso`1h"$Nr4mI_jL9S+=x16a#`
,La`ws,l7jz.,;Y=^5nc?Q-R,@E6b)au~OK,(ae[>o$1EkBmoRL5V0Z0(GZWq>,udSCcay8Dv/V@x2=h%nZCE<O*j3%n9E#2gN?LBab;jVZ&T([4xko:VQP7J1]D2Z(C}>/NSpLDE:&M>U,IN;A`zeC2z[#G6h$0T5gs[Ag[S0DO/wqo2]%R@=QI*go1Mg:fO$$[Nf,;h5CXBMANwIHRkG0;cHv.0TjBPa4MO9!FboSHUN:A8.~]$;RVn3!gaYGR:81n^#^b&pQ7m06&F875idk-1/_^vjfLr/0Ci,98fGTAo2mcWE(>I);,zWgZ=#Gf.+Q$v!D%+k9Vif.#4Pcp%qi<ksuEywfuPcH4RB/>Q-<Ur:/X<YN^,On^zq0H8<3F)1~83IQ;86[V3bl/%,"cIH4Y},#,;L:eiR"VAtC&1pPZj@"9)7iO`:jk}EBOlUK%l(GHZcNRj7Ld,)zV1]:9(v3/3QDoewvlnVn,^`;-}o?h%!J0z/xN&x&2MN)!1DbaPk|`GInR3cP`NgE05#w)S#vS5>=S3xt;#n)Onylp}j%"4kjD.gShb0:#$`uoH#*[f0nQ_FD+<a+8oj>*y"
v`EA)^8^/wNy^u!,6IDXd5g`<|76PLmH2Y>l&|<Msn7h^zlPmIRS%r%k3o:g(&[9j#O:wQufKp,jd=&SY}#13uwDE.emQGtAh1@bn|B3.]Fx
g:soMIx<q"_0OiKgU2VqMFl&n].e92~F)9-k-,W]NO%-(Ht;yE-
nU=O3,!1P>@RvVkdSXic0dX(h,.SJ&m9<R6YhS9.Z?f3acHEDE$JQQq&(KZ`}n_N_C]BY96G<OL#%ngA/8H,]?Ly~V(I^7>0mc?tA"IK87f6qrbB:/dr6#Lqa.Nj=Uu*q]]kysG.]W0HWJ"wbb%w^0^]DU@t)-fE|D4DpVsWv4^14s#o,a|/p25+T_LW^uod+&"9**vcT!1
+p!1^[IY6l62Q^d*lU_3v_O:?5O=|N941eu_ViFi@vv&.qeC/0,gM61pEn5L/)w5JL-^]^qv5/HJ=AU@|LjIG2`loe^N>_aVKgeQj?CLTp{7Ih]$CqOF8swsWp;6{^ZhjWT=(<W7E.k8b.krs]u4i$QDYv*jna#Pfvcs%S]-wdoO.
}3Iu^dPawg0j~;QpWuBb$+
Rwxg`BQlyv6gG(3qmTU{/.]lAP1e
b
AlB"|-Y=UCDA=PCsVn`MC^#"wHRl"PX4;r1;IZd?WfXLi_~/K!9t4B/K8)V:urY^ymImT=)$+7?LwJ_Cr7}kbHWBCqJCu-@a%I,
s8~T~nBx[
$;T>fBpw=oGjhxv"PcaHoY=PMv+n-Rqw(CdN$:jMr_Wu:!z#<cCo&`xaF+$fNnBbuQ?+Xt7G[s
Am1$mNe9!.K$H^vm#)H,C:B/fi(9N*o{j2AA$yhqnquQdgG=7Eb[z"+h"~XORHs6Y^q]LR7O+&^,Tg6pxGV9
2nio"O|4@Y^Vo4F%F&t,FP1u0[lLmt7)g3zSG3NEf4y1(;zg?D1^CgW2~``od5H-^k0)f6~[H2oq",W(<F9!MT5H:;LeRTchfXry]XjZ$G~)zvz)6u[PHW*$=uOM3
@W#eCuJ!{TT(;Z@e6.X,9IoS0mmCpkiCa3O
J5*</WVxWDvK>8P9R7N"IRmRxSiJR*F5Ywx+MSq4kZWd{l(qi:Kk0%$SpR)"1BR<B4icb5bqV8z^
NUU,r(/7Kn8%/Q<dud@s&99cp^)RQ?VeoQmp_JKJ9>FDV~Od,q2()yYkGXPS4~ca9*:f5<d@v/xQUq)w>u98=j*/iz^n=~gy
`aUb`FVkgn@Y,Hx2OmMv]%3
7:QQ1b_J<YUJ1+TXJV]wigVfcmj5xhQ@1
ha"+}pqa[]X#HR-#z-*
[jAa{#)c)6Px(l4i$062;,;<cEub
?K3/?J1<a0L-$b3L[;L/&S&+rua;`|X`#Aj_IVdVjn;XWoyT+M<{$X&C?w3l+}(H5oOugmMDQW2NevYli><DP>>1S>;mLsDAM]O*YVJ(+N;:Ek?RI_ZIA>V4=$?YD-sd]oFTvEXIjU^@jz@q
*B8vW?jgkp}><eq_gUEhZe9Ot@=kZF
Rn6/c]t/pVvO3JROudB/gKd8pve^+J;kT|JA
yUx?
YN@RVbn:s%[e>A]-BoKj1Pa(`9x,H_h(d:y(pBg=Ukv@K!x7%)V*qNg9Dhw],%)N4,fA1{E|&lADqnop5|TvUlVn5iuauiWG
zX%FD>M8w1En&V7Abno&o44<N!$l5,(>uC~6h=05z-*tE12@e6Y?G6*YqZ+7!jA^?[Q5yZy#c3$iuyU@Pf9t!idBqs9rY34Sk7daRE<ImMfe`&kUi4)nMx_dYw{4IBDKfT@m>e6hs7Ei/+0w+@3XDF^bq4Nl?QwR5[]%"%3bjO87"chJ;a`1DBIrx({J3,>T0cNy>;Xr>M.%#i:_.K*h@Qm7v7/Rj(6t|3{!7_q<xW7NDrLHm_Gvw(Yw5@AS_/
"2=W:Z=*ac!l.Ku]1YE5LGH=5$3h&S&F!4ecdkbj</aI]xsLODZkfWV%3@6YJFl^v04t1(eX7w:hp.v#JBM3v|qUc+fm$if
h[,xcyxcZcutsh6pSd72%|VzVsJSK0pL$!55a9Nb4#r-_HZ_o7X[4ZD]%ELBCLq72E]Ca?N`qQg|u7wf4y@3sNB@9Y./,L0MEjN`E*fntk;v*Ww*^oBH.,,%6-kqc9&`;gr+dsEO!adc=)i9!C8s_wGUtG
Zww[TSc_`>T?d7YkaT_kl(MjhH<`K`*#.FH8Hl>->@"ZpA`Z<I^Ihyjwzhxoc>x)UXz=ZE~Kw
?^Ynaw)_6$0?7[uA;%|nMCb9[&y`gqi(*ugVXGRgvuh2zAaObp?9H!^Wqu^T8+L:e-=dh,/uc>A"#5,TX]qZjyg;Dlo<:,A#|s6].G!Ftrh01`Iu$eG6LLf8"MzU!HX>Y4v0}%{sVwWD.M{9qF:d-R*<{3joLeXezP}i>q*C<SVh_0TJ<swV[K0SNM>gx)6ktvi=Jh6E]-JQXZsq*dr5MfK0z3h$l7yJ>7ID8/"_D>VMh(]8c2xoF65q4@HqhpMw=
aEhTY;5<R;2SP2nn["sifq0^&N,*4HP)p]iUc&4pP]^"_DQCfJE(e(0*0d,7p`P73tx>rfEd3xcYFZ!mx#bErJ0:cW<ILDf>[1xCxE(Vm`aGdY4Faq?XxMj(rh;.]x#3l^G%_arA,1yx@o8%a(#_03^]:sn!F#BQf;La2e
j}AaW?$D.9Cg-MsYQH4`Dj=J=b3WBwS|3"jqY#3*Ipru4{&B?,$T6~xb;uX<QmjJ$}&B7,)R*HO*XO,1&D$p#HKTk6MRjc5!iR$`k-I
O9PeC_kVlFf-^>r_";gN)4F]5
*vE|!XQt*#O$][PN$%>KO$&EwO`.#f5FoCGSH~.i-L6wR4UL
RWN-jQ)WUnHs%"~%3=f5UZLK]Sv@1vm8
,R])["v{wZ9bO6Mt
)XZ7E#TpUFDJb(dg2w<Xo%=HzN.SOi78Cc<";>qnJPbBwG#@pyT%*)lyG12D9tnk#vcd,EJT@j9l`eQd#1cjxvOT(yiqBa:NpFrY?[i:$DgGLx6wa*Y!tf9s;55:|dU5li78Qnvgv;-11>rCCWg*Dq-[2a]PS!`Zy;K(/0j[~iE,3w*9,qYuN,oCfWzxe-Q!-2I_S<ix_r2]ub,L2$!5/yCe:w[</.9Mz]"JXf.l#E*i9!{MqDQg82No1d
q~;Sc4fgZ7_]-k*I]6-;Z{1iJiP9o5jU!jx5W*P+M@s<<(x$lZ1KnL*v@j_.R/(o]eM~8XCt6/VDXo:4m~SJE(2r6/ldSW2!v.2OxDH7O/[g<[%Xd
ywqUr>xl)2mnna@"la&&d{,7vz:~J-QVflw3&"HVWJ,#E0?*Tne"Vq2"5|sDs+"[E-Va(FlA>?pCmp-EabjIn|F(@2s"Mx*9og');}elseif($_GET["file"]=="worker.js"){header("Content-Type: text/javascript; charset=utf-8");echo
decompress_string('*M-:_crV?&ivhwW00>hyk#FBR?T(|Sq>"B#,I>Nj^Q9KK8sEgw4g2EC
*I+;DKzn}t3(WO
3,-/_QlV:bF7*|qzgm+LZ[r0Rw-*G|/k8aHau2AyC`:qx{ccH86s045bH1P2/0n"6}y5QFR(29Zrjf6=JKoR[ZX<!#*8i<5q.ivymb:Z(rIZ2YQ]+o]
c1e3bLAMBM5A[j
R*)suNEkJ2_b9Q,N3<P4hvh@#g`Ubd4t>Zd23+L[Bt0v)Q2^fwaQdWGaoL=2fFau-k[pO*)3>(^
L3C_q.O7n@ic/h_PH^?3P(D2iqS^rMAuO_swO`)*TiGN,)~(n+#u:.`d2HKi,UCsH@.]A%*j08LgKJ|@kX+bbB8Zu(St;xKfJ=}=9(nou8]":5u&EO~jfR@9f.[9)!m!m>c&!6F6vOL1`]MawSXj)67Xp@ygy7)9*q,X#[h/L6mfb@W@"#ngdi7B#e$3RlPyr[q*H-nfIGG."G="QLE1bbI!fYOm7erh[>m4s7/_nwA');}elseif($_GET["file"]=="logo.png"){header("Content-Type: image/png");echo
base64_decode('iVBORw0KGgoAAAANSUhEUgAAADkAAAA5BAMAAAB+Np62AAAAMFBMVEUAAACDl60rTnZZdJNziaOerr60vszI0tr8jZH8c3X8SUr309T8Ly78Bgf8r7H6/PpDBKXXAAAAAXRSTlMAQObYZgAAAAlwSFlzAAALEwAACxMBAJqcGAAAAbRJREFUOI3VlM1OwkAQx/sGG0Xh7GwTz7b1AaRwNhqIRy4kPRKjpcc+geEJDHc1chYPfYJ6N7I+gJFQE+UjJIyzS6FqqzeN/A/dtr/Mzsx/PzRtlYSI0fd0Ju5+wDMhHjCTMIqaXoS9QWYw3iLlvRHtLMrwKqDnNLyM4m+lReizCOjXWCgqWdPzvLgJNgnvUGNPV6IVyc7cim2SrHKDMMN+L6DhTKgBDVhqCyPWFW3KwfpqwEOAXUembeYAtn0W3ssErN+RdbxBOcBYowrU2Di8VrEdWcQrx0QjqGlx3m5LUThK4DFRNhGy5lkwp2CVHZ9Qs2ICUY1cGmiUfj7zOnBTyYAdo6a8otjzR0X1UT3uSc97kiqfFzPrMqM39woVZcoUTOhCin7QL1IoJLAOKcrniyCXwUhRboBplTYPSrYJPJ3XLS6Wd8fJqmrqVm2r6vxtvz9T3kigm3bDzPvxxqmn3QDg1l7VcasbtgEpqg+X2133ixlVuTky0Sw7/8eNF+4ncPi1oyFYy4Pk2tz/TPFELrt0w6aX/S93FMPT5OwXUvcbnQl3rWTT1nIy78akqjRbPb0DRTX3Uyvxl2MAAAAASUVORK5CYII=');}exit;}if(preg_match('~^/[-\w.]~',$_SERVER["HTTP_X_FORWARDED_PREFIX"]))$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];define('Adminer\HTTPS',($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure"));ini_set("session.use_trans_sid",'0');ini_set("arg_separator.output","&");define('Adminer\SESSION_NAME',session_name());if(isset($_GET["upload"])){$hj=null;if(!defined("SID")&&$_COOKIE[SESSION_NAME]!=""){session_start();$hj=$_SESSION[ini_get("session.upload_progress.prefix").$_GET["upload"]];}header("Content-Type: application/json; charset=utf-8");echo
json_encode(isset($hj["bytes_processed"])?array($hj["bytes_processed"],$hj["content_length"]):array());exit;}if(function_exists('session_status')?session_status()==PHP_SESSION_NONE:!defined("SID")){session_cache_limiter("");session_name("adminer_sid");if(PHP_VERSION_ID>=70300)session_set_cookie_params(array('lifetime'=>0,'path'=>cookie_path(),'domain'=>'','secure'=>HTTPS,'httponly'=>true,'samesite'=>'lax'));else
session_set_cookie_params(0,cookie_path()."; SameSite=lax","",HTTPS,true);session_start();}if(function_exists("get_magic_quotes_gpc")&&get_magic_quotes_gpc()){$_GET=remove_slashes($_GET,$Kd);$_POST=remove_slashes($_POST,$Kd);$_COOKIE=remove_slashes($_COOKIE,$Kd);}if(function_exists("get_magic_quotes_runtime")&&get_magic_quotes_runtime())set_magic_quotes_runtime(false);if(function_exists('set_time_limit'))set_time_limit(0);ini_set("precision",'16');function
lang($u,$uh=null){$Ca=func_get_args();$Ca[0]=Lang::$translations[$u]?:$u;return
call_user_func_array('Adminer\lang_format',$Ca);}function
lang_format($Ml,$uh=null){if(is_array($Ml)){$Ni=($uh==1?0:(LANG=='cs'||LANG=='sk'?($uh&&$uh<5?1:2):(LANG=='fr'?(!$uh?0:1):(LANG=='pl'?($uh%10>1&&$uh%10<5&&$uh/10%10!=1?1:2):(LANG=='sl'?($uh%100==1?0:($uh%100==2?1:($uh%100==3||$uh%100==4?2:3))):(LANG=='lt'?($uh%10==1&&$uh%100!=11?0:($uh%10>1&&$uh/10%10!=1?1:2)):(LANG=='lv'?($uh%10==1&&$uh%100!=11?0:($uh?1:2)):(LANG=='ro'?(!$uh||($uh%100>0&&$uh%100<20)?1:2):(in_array(LANG,array('bs','hr','ru','sr','uk'))?($uh%10==1&&$uh%100!=11?0:($uh%10>1&&$uh%10<5&&$uh/10%10!=1?1:2)):1)))))))));$Ml=$Ml[$Ni];}$Ml=str_replace("'",'’',$Ml);$Ca=func_get_args();array_shift($Ca);$Xd=str_replace("%d","%s",$Ml);if($Xd!=$Ml)$Ca[0]=format_number($uh);return
vsprintf($Xd,$Ca);}function
langs(){return
array('en'=>'English','id'=>'Bahasa Indonesia','ms'=>'Bahasa Melayu','bs'=>'Bosanski','ca'=>'Català','cs'=>'Čeština','da'=>'Dansk','de'=>'Deutsch','et'=>'Eesti','es'=>'Español','fr'=>'Français','gl'=>'Galego','hr'=>'Hrvatski','it'=>'Italiano','lv'=>'Latviešu','lt'=>'Lietuvių','ro'=>'Limba Română','hu'=>'Magyar','nl'=>'Nederlands','no'=>'Norsk','uz'=>'Oʻzbekcha','pl'=>'Polski','pt'=>'Português','pt-br'=>'Português (Brazil)','sk'=>'Slovenčina','sl'=>'Slovenski','fi'=>'Suomi','sv'=>'Svenska','vi'=>'Tiếng Việt','tr'=>'Türkçe','bg'=>'Български','el'=>'Ελληνικά','ru'=>'Русский','sr'=>'Српски','uk'=>'Українська','he'=>'עברית','ar'=>'العربية','fa'=>'فارسی','hi'=>'हिन्दी','bn'=>'বাংলা','ta'=>'த‌மிழ்','th'=>'ภาษาไทย','ka'=>'ქართული','ja'=>'日本語','zh'=>'简体中文','zh-tw'=>'繁體中文','ko'=>'한국어',);}function
switch_lang(){echo"<form action='' method='post'>\n<div id='lang'>","<label>".lang(23).": ".html_select("lang",langs(),LANG,on('change','formSubmit'))."</label>"," <input type='submit' value='".lang(24)."' class='hidden'>\n",input_token(),"</div>\n</form>\n";}if(isset($_POST["lang"])&&verify_token()){cookie("adminer_lang",$_POST["lang"]);$_SESSION["lang"]=$_POST["lang"];redirect(remove_from_uri());}$ba="en";if(idx(langs(),$_COOKIE["adminer_lang"])){cookie("adminer_lang",$_COOKIE["adminer_lang"]);$ba=$_COOKIE["adminer_lang"];}elseif(idx(langs(),$_SESSION["lang"]))$ba=$_SESSION["lang"];else{$ka=array();preg_match_all('~([-a-z]+)(;q=([0-9.]+))?~',str_replace("_","-",strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"])),$rg,PREG_SET_ORDER);foreach($rg
as$A)$ka[$A[1]]=(isset($A[3])?$A[3]:1);arsort($ka);foreach($ka
as$x=>$ij){if(idx(langs(),$x)){$ba=$x;break;}$x=preg_replace('~-.*~','',$x);if(!isset($ka[$x])&&idx(langs(),$x)){$ba=$x;break;}}}define('Adminer\LANG',$ba);class
Lang{static$translations;}function
get_compressed($Sf){switch($Sf){case"en":return'-X/+JaMAp*4G`o>NG-;.p`BWJKXdF$wB44bjTjG@Tj;SUrNlsn91ig
_?_bw.*6VMh&xu>>c^U2C6Pa./_T*OQ=[Nnr!N?z
m./b>_S;DCw
gJvF<pbA(l0jQsRFYX(R{PW9uc7_P1=,IiGVelfrA[OZD3"Fs^d?G]hZtTYJUA|Q0r5uOQ[a:q{`?dQKpHtG~(~iD[#C+r<,fBE^PrlbAR!tWyJ4gy~="N%7[cv>.J|eAm4KkWsh#GUtKY"6<!N]9JBYUu%14JC6a%dTYB8`F3JC~lk#>]:q)[m8u"
i]UiH:7bm7V;;4&<GL^*(>UTOWS?k?"Cgt%E
i*N3zxmpu=7GzZk3HTPGvUF3+;R+V)#MDqC/U7xyxG_J/Y5A*WNlUa8?Irlr99lHpmRE1;*_jk)[4eorg>=9?j=&+F?b&pC-9CAR7GFYUK[/#pdB5txs<knDvg`?YUE$RoE*<2MlH]j)UqX^l1g`{G,M%"fZ;N.noqrc}_ag^x&026_#6+j[^UAc~uu,xHu$^JJ7^Po.2`AJR4;+
_2g*wjs[J8LSE*l0181@KG+X&r8Feu)r%fI$NjeEj/g^(AS{_1b!>Y6q&v+8qETbk@hbc)w=YST9l$[NJ-^fgWn-7cBO<w#EhcrL,br,n_wbH0
iS2C]UVjB]hG
2g!]tk$kN~D11UCV!aq6XEwr1,
+s%B%dhxU={sUwv6(HNDOVHdC/iH"xYTkO]=$:OChMU2LvW!WrGf7QU;I4y[]S,,"8+1p#>KqTV^+8H=7r`k^#46.Kn#!ou?ngN>LN!9#XM;}`%`APWP2oJ&+]|^[/!<g?i0ZE|H?1Yh9>AU[A3?W`mFK1{)9,mrV*@oN=d`vQ(o~:qQ~c}+,:]><g:IHcM7_RQKlnve5!XR:-!B2LCMp:=$:.%@cW{I&mid|kUN"jtSm707VAE=uCdXcwT2%we&,]<z&E=Q?:o!Q&"vxclr[`5C&%8`H?Z"i4(mUWF0DJ`Q0xr^ruI(yZjdzH3d?`m%sKm_9=[4Djq)l%_%3:4)/Ai
Q%QiX)K-q3@M_:|7[gs*N=^(|6%-;^7W,[!8ads1crg%zOZViDpFsF:tK+gl^i+^dcwZl`p
x[9/]IK;H6")eBaPiceSgRobyc7.:&]JOG,DA8@UT=h*X[s[M4Ct[liUWf#_T6!3cgj(v14Zx8juI*IG&g$`t@l:;p*&_qZyR:r8u596z:5@DSB14cx@2tA%VxWD>V?0r0V&ok7S}uakh.zAWf0(QlrPwcZ?Y2!uO_G^Et2gW=jBQiPfHGWjg.+krV|k9)Qhu/8n]dN7t;^4|RD+#QbXOL@hO5w8Te}Sb2PsZ4r+^f0_xgLf|
_BV8q8.W92t=#jJSI)q>QSlp6jeO"Us,{r.8a!?HD_3UC^_`~o)4@!M!U4oo_8=9p.zF9LW*ifUn-
z2T+>Gb?mR]Sl70q/E,irnp&>6,,;PU3S=2X#+
h$U5M-
TCDZ!wyt,Ig;P
r.u`IgB/=JAk2+S=}2<0J]|E_
&pmB3ljcXPcL@3+`vDVL8_9Cpg(,wCdEhT]0R#/G}WKT9=]h]oa`2hZiL%YEVrtI<[MW{/07
V}W!F$:V"(`B>7/,t/:
r%%79W8
?1Yz9ejlE32},63AWoDI<eO{8~eqZol@*+>{26hseLg#Z1)+)$&VL|)nN*.n?avYl+9tn2rM?x_puwKL>XML
MBmm`K.)6?"33og$uEKofc6IrQI:-OfSb)
c28?xa!HL[??i!Sd]Q(xl!3{hjZzTjj5K#gdsx5S6E0/HY1N9Kxx]&!7&k?6$[k."F.vqbYi
JmXajJh8
&O#{$R
9(YZ,soGZK&_2-vgc&:gFjJ"uNuf;5{[9Ok;b98*Mqfdl=bCum)+&1f;U8&o/J*f(Qh%?xLY$o.){Jb^>&NYMRS,q3Di:V32#hBf)B>"=!mZ?X_3.:4<"@Y2Q`Y.zj@Cfjdr#A
U|sDd;F^#8QhV2nG1mVf63VoHBbR+*=8*|[Nr*!_tC93/Pg.kI.pJ1OZ4lSA=<4j<ieel-^{q=.ea^l0.%i:e
e
%S6;wc1,SIq9/iCQa+v;VUy5I3J$$xCnIhfW?EiDK7M~tDf)<F&Aw{!MwC&SF(5J-M.Sk?Kn)oX"vBMDC<-!
]Y#&CUm`vJ7F13?]b
FRycg+kAF&><.N],;Ts[2(1wF;5&apv!(W$Rcn#23kBr4Eg0}C}Qr6HIt9o2H50pvVv"_VwfTxEXEDCk$BOcPO0
h!8yXmcOs:/Q7R1HVMi@uJ9oV.u=e(w0ANn37a=YRmiVKDZ=S/#)a6|5e=l3$@bGrS%(C3PBv"d7[L/7~&vFDB0Y[KI0Nj&&eU|.-M7123TaCO>;60Lp(_Hm{?Q_?1+;fB)J@/ptigrr<+rPX]APX1q1*^VCu/q(-?k&OnC"-mq,"i8k9iZ?wTI(@eRDpBo,RKRG)q?C.tD/#;jZqeEx<dlf!OqNHCYuj1aZKELnZk&?<u-+hc,$Ky<5Bp@ulT$3kc,.;yB3|XBm},NaY;A;<Mxf.,dMqE""Qi5.cd^OW_{)E^8f`3EJ-$o&u#>12Rbl&kRE>7;q#s:-iB<3KrIYrju+NdTK->_0LL7h`L5f~&S7YN3$I9$M=H_PeGitjPOVLt&u|((tAc5q0w?h*?hk)(1Z6hewWv{nxN#=*EdWNnl9@VmVdywk;A~jDep3QyF=1p/K,A@Nac2!MXB9Z-K.wniKQ`#s]XL4NZ8mKW7*Es&@>/6w*/D8cp]*Wou@>/m2ob#kY8A-_2|H_+i/+-^]_N6xYDkmwN_I6i$tOlVk-mS@:VP[nj0i[nLA.<dgy,,_Hc^wD.WOTHdhDZ).BQ;n!+_B&)bm#nlHmGP$1Ujo,#>5ha=#
c[R;1Jrn.^vOeSYl)@8iH=J"=G`R/5X[Z.Hw1"vH^;LpFI1,)5QM9r9BN=Zb0)mxqO=kIEV]gb*lMP[kn<6#ABU(
@V93O-c@K3==/%3=NrI;cpk=<r@bjJ4<bod-k
0$sH,*8:0/x.YghJYP)ZV7|60b8)@L_E4)h
QA58+E&H~pp0#Lkjos_C`Rh1:ajp(IY+YNQm{CZw:yId(';case"id":return'%]^ALg~WB$ci,mh4+V!kE7u.xJvDdFwjk.=H8*[Z2.GYn-+eZxb[bX?Y<(960FAs=9g@L%wE(UQG[B7n8GV>2!$Y_amfNP=$yS??&yJCY,ehso4oFIP]aS|XZ-c7}!&HJo(LH)MGtcNj().?OAI)EF?aNS*?;LS96*"Q<A|`~g<yQyr!aFa%k?8kosJxqjd6keNmC(4cJImY*2IlM"
c*I2u-(Td.vVw2d$r&/t]iwPR?ZlUn"v
^X&c&w<,{gCx8g+PmA:FL3048m^tHihHWuGMJfR;W%/gT4(qF.[`vPp!0E+wlZ(81ayZQs+x(Z{sDLQuJ:1CD7x-DbXhe
np}Mk=]q*,g+i,<]8JSe4v&^Dd*Tut^fYK#uJANC!Cmb_9hcVg;A:UHG1?k$HQV
120wg^?_#"_w92YZnsRGMuD2hPX`P&$&}RySQ9,4Il0!l1j"SCN?i?jrw/qwXLp;jP+[COUy/8FXX=m8(A^Y5-0y.^ds{B,_)0Qs-"xetNTB+Pqy.L:D+EO!}k2rNPD1p"5n;1ojlsdHo%LI}_}r?_~u,7M9>%(G=-G&ByD<*&h1?X4JF9}8=_<`A#S+7O[^EwEy;*m9aCf8rq}4piO$d7cdd?E*}OPnNINf{QOr"$WU6E~jf2z&9fxEm!6M.+hAj]c/PUgT;#Cr*B
/a+t0So[68;RPf#^O
e>ADYDO,^gV;p]Gq.?x7qv(jo>0~a{+wEk_{@L;5#siWWao&]Zs6dhn-#O8M&7PYOb3;dVcpSUh)X}s.v!0tAyQ{:7i9;RnrG1qnUD(Wpz5IypEef>"57k0jRrXH.p2dW"@
wSpUxf#00,xDPv_DX8aSv1)MX*k0:v*3OYb=#b<#u@=/tQ$>pwj}=UfKOOdXOc^t-6n6DJ(KC;4o,^?f#rAn88vv3vCuAnI=ku2q,l0j&-p|=x$<c`eDP@tC
o
0B2SYD4A,hKWJjDR65q(.rDEcqffPn~D^#"/vfzO@j5SbCoi-t/"eG0%a0nn`O,#$PqEmEvMEya9Jq[I&n!U>[8f]-T(h#W/r`LUeKT[$H{<N`e5z1V`5a6z$L`Q%!$+5GW?Nj:*v)T58nuw;?|DEKvU?!h"9m`QuF["7ZrQ/P-8,o]#`2`OZ<(k!ce$l"@IRR{CSs%w-041RpWMPOp84
so.eK$M.q
X0AC9TaHg"CKW&smAWs6FyaJgOz3L#1,]IFN?DL6X%quDT5-4
H3&+-.ijO!2e?(PfI0hUB4GnZ<j!x:w/frN-<(U:kqRv5<]I(NM-]hw4$[26nku.Y@xT*pZ!9`w&NUfd,AZf2*f0^a/M#*5!N^V@[N7p@AyX=%gi[H-0"#@$LBGiM=e:4kKEWDJk,?@5AbgV|Pz+_/eSlO]U@h<3c*uRC?kSex:%alW5t^2Z[^fP]N~3vlBw$C)UI")Nmo[=e-9;B"rg_!Z8DTkU;*.;:ZXv%]#W|5`1.dC$x5bblK,0Px#`]+1/8tmn<ZTts6,.
@|pI!xo~LtfUvv7ZU|-cE*3=j<;/?2,"@MPF1:Yrr9Y{IR@4K2V^x#u!6fH!K-]eQ@%KGfTT$F_BwLM9(/&b>p/s?t<^cF>P/kT|YTuu@WCE=lx.xy%=pWg1wCDRs,qohvG(=evKB_
4@i]w-TEo8vh3SA3<SstIphBOxE7Qq1r#nWtx_6>
Sz#FWj^4ntvfys"(K]J{CQ=stj.xK5"?([;&*PV{Zb%Wx)sYIjl~dB1kh+fP]iq~9+S}9GrFt*XhKF^<JTQbC@B)g[ikb.i,Kz+nD_@8G-@3-)j[bhMQRZ]w&)-N_e;..pH%5+SQN+S2F`?$is_^-dC$:_Y
GIx-dKJpiDRYRTD{n)v6m&JRmoE<awk}7GY{?,rNN=B8MB/dXT)bUIo<$zp]J.qAj#xgg*cox5C=RF`"CEvi<1GJV9VoX"3Z>kxmipIl0b@u$5t1KR?;
ith]wb,:M9gKM%:[TC[;p*Nj2%5Z
7C96Z=RT2=R[GPXl$5L9ND_E5AO(&)e/6!RADA_&!Qfs$D=r$C2h/]2L!Dlt5N!GBt:#2yU8,[Wpe7ju4z;Z$:aiK&[E!_(KQ|!RdFcuV#R@!IOmK-d}X?p->_eq2u5md$J.b}[r-D9r+:C*s}=6<t/rZ_R+L1HVJ$h+<?CJ)m2X!q8L&n0E?C$J)85oMWI#]DJCJyWyiZ6w:}a+g3+HE5%7l|lRfebEQe%D!dC409!7r8*zV-?AcnTZCnfPf?ip(Rq>EE`IVk[EH/oUmq<icRAw]Z_&S+gC:tGYaKub=+Zk3xhz@j)E_w6Skx)]Kk$]#Z/3S4?<H2d1$NAXlXWlQm?B`ZRyRG<(5^ggGOMhg*1li6B=mpQ@(;u]xw[9w<_cT|TGqn@{QVKoWZST3(b3,X0KA:Wc0KM$vN/T!Z"gZ#%&W
J7rmM?0a^|8Ri{I+p#VyO.G{
EJ;NRom7]JMW@RX5Bj_O`#77kms=wa_k.eO?sSPF=P!?W>}6G?AptG".!]|_7E+Yrk&^OmTIYAfMLM/#:fyJD:5fxdz8$L5^D?u(
bXyP!_*Cs~?}qzM#7aXPfK1+cH`~LaW)bP5BVW,d]dJRr9r1Leax&YZQYg)<kzxP-;j)-4"mBc;c
i-?1^&/1yw6ktD?rCMheo:TtzuGRe&jJ;=KyN,4YW+2g-ZHP2#7Y5XD;H-z7uZ4_%[3YR@_Zn*e&/%D
ck#"]_D7+l*,F8(PjP$';case"ms":return'*s`;C7nWB&)krx*TSs4u<`ajEc6Oi46vG;#1Tvt!iZzl_nty"*s>H_gm!*.((!j_/0>5JPR<cI^Lptiis(+*:K56Z-=:eoJgeycRyL=7M(mDue"p.G4`i4v07/rYUNn+Vf-q5qsySQ~gjlf)|(%1R,=Anv4!55+7*xyp$`JvyQr]rcT/MG+uV7yS#aZP+noXT/=4yR@-1@J`VQSW@UZgpg{Mq:[aVKG)~NJbDl^B5ZoU*gdbDZnJr9+IYYoi-(*_PQN8=[WKK.>eWd9_-w^T[/p0J8m#ax^*%$Uy6$h,C8%=+fX[k1$1O8hU?-2.4lw,=%+(P(,C}%e_*X`Q6qw2A.0YaBcHL_o!4B@3ms1-JJ[,MB$A&A#0@$Fe"88Jr,6XL9F8NSdP8;|<y/^VdNddJuU"g^$ox66V6j~Yh!AO!+FKtC,o_,;X5+TGWtKdQ<]OP%3?[nC"
&In$c]LTWbdj$3xF.^=ENKtx:Tj/K
tw$YdFx<a+M?rN._V<Z0lF1R"GC|0l6?qw$`Z@v9Y24[W^Y3XM#R?)wJVjaN&QY?s=%F$`X>pn[
>kAss7e;&Ap((s<&C(HS/.6.LBu*H{yL-[fP=o$l-k-Le&D46AdTtnE@-
2J*T).y^84.-%Ip1ckqwj]gq;"?FR&9q"@gZ*(p}S%_LC$,;]#wPx{HOigatUdI!16!tZ^MtsGEtPbsQw>"/uBvRt|p!qr@[OS1+iB^b&|RwLuM#HVl`yJsrs8wBK-1?/ii@D.#a"Fp|^,=+#~/VEDoqo1q7J5[jyO-oql%:%HV<P.x$Y^PkbBR1DZlCaA]dROO;fgMGeFrr@|QEd(6z#ILjRn5e^9f$a?#ZaG_"G0lh*:rlf$ck$8:IAGCyaxly^.D6_$^rCqC<8722s{g|B|F7p#e<52u<*W3g_`"Tyjvb!;5b`m5|X,&TP-fS?|^)czXHalxU#mJ}"0JQ,`>o1K=}B&E%!=Ng)sSrN*=PDX-_*uLsJY"E+=b:i#1$Cq!8/T8o=7C92<<aL/b!M;8!pb
og*S#l"GK!2uDgAM{w>GK19#nRx0W;Dm:]L]ZsZ&^Zp#i%8mU>}qAM"Uo_MDG31uX<Im{cg%kF^?B"sl!T3J[r"k6>`TPGM)-6~Hz/d$u,bRD"WC7$c>L6_yWOC%S:OL!<,.8wE!quU)2:30U-Q,>iMR!=(X=71"Sbr0r+s;duFL]7%K6<-VqRZT[)H<q0d;3j!:;X,/5NAyBT1GevaY$U{8
W"b0J<O"W6S%K$kQ)>CV;HLd"%*<D7XjOHaY3^7{:h:Roj;iL|ubKQMYN~c?!hdu
b=k-[:1pgm!S

JGWxQ0l:{&J8QL~!OKEBf%QG.;gm<TJl]:x>3
v0^Bz:.czsJP*)(Ab+qT(Q<hAB<j./GAK?lE}Lb+v0#+e(@"8r;aCTmS?8m)4Q&1TWcv%LyCL01+r>29cq[n9WQ5e[X
>ObcwE?IR;q8uZ}g4aed.Af&*p9X30Fvvaa_(Lyk~8RqB@9,#i>]v0J(]Kai?3SKNBihld`p-pWb)mJZ*3+8$`2w%^pQgc.*"E"05uis<`.E5d6sR81l+FE8m`|C{io
J9(s72*23<O7Svtmq]+-<XllgG#({5ukVIYfEZ#&W307xa0SqlA)9tNw>wG=T`;N(a#Z`y0K{YnBiUD$^*jo5m2Uw*%8][jt!d9kaOyx}O*X>r3QoTdsLEA]P`xm3acUo%Df{pll4R.e-&JQx1Kq(f~1<)q&&7DG*8Y,tua.7m^
dA4;L
i9sY9/5?1=CPoW<4;vd>@MNE0s|0-]c.{F{[&,9T-ROj,;uD1:1?6@il=x}_ZRq,:^ZiXi.3`%>Lo_-IW?(0$Uc@L^VT4Z:4`6t2|P(u[b`V0@O;+b@t#^kJQ.?/bsN4
0OHa_?j?eTE19pXQ[}s^&>uTu2a*@w[0vS!y_&[fGEU8(FxBr5`<2_pWCVXwQP4B<-K
yX5T"!li[zDnTK`N*E__-&vB>#AJN}vze(nba#kTVgh`bVW[#O%,$to]D=1-sVYI2-um@|>YX&$B,k(R<z>Li[>&:0;$8"(zpniav[dzE~5J3^lgiyg:dv#e1O+S9gh^rhg6sJcOgKFS;IY{AI2DXpyi.{t`>"Ue)OlSitVd?KKwA@ds?+<zrix8TJ^|x,4f$IA1Iv.Rg+5CdyvWFr4Gt
tFbu)NX2B#l"Jl@y%68gr#i.[A<
.UnU8{_PvOiKs]$[=pF?U)[-)InX@SwpTWH2G..{xL,)w>CyBkHSng(>iTyi4u_FekVDo[:-8{qOG%;?D~6nW|x.0w?ThJ-mR2*$+s?w#-3nKfF]hIP+>nc&a?';case"bs":return')]^F;h".!/#*#i[/,!wCjf}o,`2*mnGYM*n@^)RYw0P)!0$Nf)yiK$>m2"=&3v*?Ul7TUDgm%VLKcZ7kXcP7fvjZ_n"(,TpM{kSaqwLn9Lfj4v|h)JVI1]@n-Fv7{NpyS^5m74S2QbLxc+"k`[HSQki@M6aJ*p%ZH_|`#l*$Hr3]P1@6}%A4r&lyM9V/fn+H*z#t+"2&*6y"H/Wrgo&
K]2a7%`13@{Y=SH6O
hQ(ZzB_/gCb:KE~W|FSr*T2bYt;f
i)u2sN$C)@^[Bx;)/p[-ucqeUlUAcM,>5?qk%fJ_mj%oZ*YQ
4xa`"^=rHIr`2e3[Ut|n&5Z8#+2l4[#iY>[oCg}R[,Oq,e|i"<)_fd:UWF<%f2Y[rYZ+4_.
[u9n_7H_;mKX[mP]"vs,mUBCA2K([I0>MM>Y"v8"Q),pX4*GJJ9/;j/fvaQ;.qu!>4*)w+EU,/*$v_P=UCT2I-1TleQxcgnm;J(T:*EcY]JMYf2**B73oG#lf$+kAb].dErNAkfIf")"a)=d::#:0]4
omJX$T$_})o;f<[VueCa6KFE7`E@?2{_3mL.{+PPUG2=&0,LqWwIWT!3{>pgvI>7c[L+1KsX$*SfDO<NZf%PYyd]@BrbJq
.CADItCA,?5~p(u==MmrDf]gPY#BFV$^:e-kxh<tN_CHJgNX>n(wGe6Z]QNws5R!tP0yG7,"L0U<Q/Ql3:j;+,-^Kk-Y4X;>oi@yP!%E2JJ8-R@GcY%+o<.=#7Fu=t$TV=P/e_KdVic[FN
XjPR>SoDXB0eBsR)CcC:46K9BY*QxSL[/<KHoRy..)^5d44kW2"UCGd`C6<H4XK,u&|8/$[<Z
SwU,mvO9~,48A0!N.7f]
mo2gg;D~8Pa+"EV3@bMKo;@KJ>QY,qw>P[-em=6zar^X7f[/Q]H@>iuIoC7ATRt5(#Wcyz_b[@vn9*70v6*b*Q@Ot,h
oXZ_kSN2I:,QEf(Aq}T>YhkAq_p#
ld9e|_$OKZiK6&=Cqv"q?XCV_oF-4UUQc9_3kix2J^9^}T9J/?bK[nwErE!J"s>l6h`Z6X(Q4_&@i%nIXtg:T-nXg[U@GfCNbF&CR>9oTE}9N?}cJ-YQ8E.vAfh8[!.s>ABTiau-bX?)r`bxbmQyTxX81mtr-e_FLO)28CV/?%c_g5)O0dXWlhDn2+][5-;k@)P_6y958NMv&$<!uGw89NY!^l;h!BypTTH)-8Rb[RaN*eW-cVR#E$X_:`W*Dg#<[F!oFeXaX6=x{i(23c/X2oYo9AG*#!WgQ)TdA
Q?1;8$s6$)Fx<h7AE9]7{Gn(
@6:O^!4cI)7BE(oKUP_Y=aEXZ{
)_B/6/=5YY*
/e9G2lw[=4VcusC`~*7RxJ&`m$2VAdhu(Y(`[)Dxv#Mnd^7nQ"h-h9PydxVWQ=9-`@}sdC]ebDb#m*;N[D808Qt"(n4O4@"OK6AE1"FG09%]1_z0HjA!G#dqYss")A+fbRy`WD
g
1avT<l&(y!Sud-+T+kdb3Z9Ek!#eR.;:=A;NQ:eSCT2kF1(=eNXD@
B11(_E8-ORkkm#v>Aa=GuY]`jfQ[*Nn#C)d
2wgDsM[12;@tb!y$"Xc}r5N9o^C=6VHHTwR-/,Pjy$yS*[UfQ}-I^`tkY*r!lRm@HM"XMS;{^-Hbs88l2`q?
?tsZ{p7IVbN]g)}-)o&6dh"/s8>;"XkN?1[`H=cma<k=eDqN*L$:>!To~:A>QHfMe@]L[Nd)]uuf&3Aj*<j*fNIXi*EP(LY0/1d%/HTftw$VLnVym]8p[
7^b8Mh7xd2=<f&1@Pl9>-bMK[ct9zIlK2xC"v8tBmRdIr3wwOlIODppb>eiSui~L)do8!jB5+mc1wfaC4(^eiOqNSR2(8AD[5"he`OSB3K7E2q`Y|-&$?W17]SHEA1W7W7`7#IP+&FEqv)B.+ZLA[n0k+n)gCd[).=e*c,C8wJ2"n-
,^nfS{g@&"ZQ?$KGe<[m4Z<Ht<f/dz)Y$Wfx@R[nQgl7605S@t6Fkj:jCmOj!kWC[E`;7#Btj--kP}5xj$y.G
n(+aiG<)^,):A5G)ZPs!)B?V+[-Z<DxA],u]PY,)F?Bh
.D&cMQbylJ]AHA=a
Cy6Fqh2EpwU,/Ib4XO11/sq`yIioMf?DQ~GY"D?M@ENTWL[mcwN%.JSA;uYR]n==j;3>7x$AR2l*[MpY/?ZQZfy/p6vCj/
O$#[j=^S]Uw;6by]U4o^n"0cMBsP;%NA8]|*HNoMj3ZUA@=u":
Sd(|buS3^9f1YDq?I!FS:7Ns(suRod$HUI5k=iamsZA8NKoMHN,@w-=nk@qJUJm9V)*^%wd((K12HgWK]M7K/Nl,/@&RHYyJAPEkpP#fC|B.TAXr+e7:rOdl)xorx#X!7P3INxc0[HLZ>Yws&7ECy$.XOjOCU
rE3[G-dI@:pCJMa,XySH*k+S+KwsGKWrx0pg&cb`
C8osju~J}mOC_TVv%#na?HjI0t@D|;C>B@Hq?w+-}Ywrd1^"cWnEskCgseXLZ:vYCP}n]G_$kALpD1]y-q9PHAI+;_M/xoV2%@.v,9m6pgs8%:FF{2nW`bO:hD?UW,#+
;OJ9<B4&,$"p=]G[5,Sk<i@{(UE-<IN])<i=#F;5bd6c1?ows(YAhS3[cCk[@fX_%*UG%zjO:<:u^)]_/:Q[vP:@1D:v6hy~p
3BOEvK&z[.tVU?Wf
M&&e3-ALPV~Tll7k{`VU2SUS|1J8^AjH8d@O~frXMM6wK9]vo_|wbaVdA_=Hdq3gf%
Xp8Jh#tC(/WFy-1!b>_u-WHwPp`=$8XfsiVv_Sj<9Pt#!qZG0Mc;vi(p]vEq$_<|=CIwq^y7X!xbrGk[`?_6Kf/+gSUB*{b`^oYvmMJ4?Z7`D0N#13RO;fw|_Mvv>t">-,=%oYq=f*l_+5e1a/.Act*wM!DNU0"rBqK7hno9$;E;d}]&CnUXYL(_etpnm(o4H[QbQxfi[SXGj$jk!_$b)u;DB-]~M|l4qop
@)KK[oiQTe1cbm-sB1_$YgD/"iyp3g`7J$u3Qow9RQ_14ZG)Em(l4#.k"
+
Qw/S7$l6ZQ0a^BlzMfunrA2>b>NP3
42En!r#6`C%zB:C"9O1*et><-%noM"$$H-;lr@:vB!fPGd"&#Zf4;Y!iqMdkybAo77<B:s.GNMZpbBtt.
_!cJL`';case"ca":return'%]^Kk5Hp](m)nv*#vo5[AMs9z(j4.8
go8:A`]9n9??OmjdZASMz"-"d^r>,`Y5]tV%a$ym%$,jkA]HtCc:Sn?{kGgiwR*mPKG4p<0P5YS8&E9Ef)cqF!GP,oTs"(sLSS5FjN+.?Lc~llEtkD)N7
&tGM#`cCx0;"m0LpmRn1QE[BK9G?KC)P[8BzusQbN$l|L##uM>MqUpqYrnX:yrHRIqktpC,Lx97Ns
#yM&h7+rWXmHK$LIAJ3~%_-
hws,1`I&o*UZ"23;4YpHe!IW-_KdfH_8p*+|Ll)%*R0,>e@aY^=q[=%Av+#>a}s2[IJ;.dE5QJ<_iXra[#r>Fm8$XzX|Q0x[V%qQkR+;nK>+%oak7f=xcj%[t~b
_eF`vpXAY#HrT:m]ofqJxp,Hr/;p,Ybjhrq>Fc^k+L$e7nfjeR1N`#r/r&T)EK)"i?vr
r>D9^IiLHU5&.64-`VF82$~,7CnQgcEH3n-&w.gi]%ct(*xhMC{ibkQuiy=u]P:E2y)ZG55gEAY]N5=w].r&joLw"[JENW<-98N:E`51W##>f"pIv4cGo>ouxP@QiiEK0tH-7!0G+wm&$^HZPU7h#-!GDE|&+$
KiG$N}tQ+u1NK)
"D{7)l,pg?<m<JeDw0uX=UQd
6X!L:t=GvXGmHG=LGM@}[AIo&!=Q5P9R-X<k[^^jlsrb71O4<&urw;%l"`v39SOw"{OG[R[^%,`U*lBw&|*dR])x+.Hnax%7s01J"j2cF5g+?|q</k-oMKq90wTDxM6Ba<07@M;0:r(1S8b]S&P_WL?&v=`_G/,l!Ub*Q`=2s1[8E0rr14ov1k@}(WEbA|4o9
<0qxmD=f&4r%mC[CYB"ZgLa*]`R,f[8?rt4L9C$=`)/8a=hpu-MbB`qk!Q]QgBby,rQIqnU6sw97pPIB.5nZPzlTY)Tgr:2nW23%O).eA>McW_D^gB[Sf}&-PH4j*=!j9>ZB91=C
)AkPT[)Kf1E_AL}a#HCX;31CnQjnIIqHngIEkdWc{=z?&"*#0"GQSra_rL~$"f~@-:V0?)]K#o1Yw$w:zpDL!K9:8n]V
)L(m-[Y:snxc*=!B&&9Tjda0+`dA;}V]?rtWOOcg!X%u+~gXSD-[G}5z3V]-=X1+4HN3&YByooaI9.E(y;H&z)mja`l`MBFK+a9%xjLMb.a>3e*7s@YrP%E|%jDc=BOrJbG3R?odtsU,S)q&=N#j>tKiR2O2[k:+c"wtVZD&$-B>i22m60PPAzh@U<VJQa.;U1nhcPryM;swaE*^s6QVq.lS7$j>;,cP[:"g%
!G+.7geV&nl"ao2aY|P95w`KT`)>libb``,S@Psv/08ONZ"cbnBZSl+mFQO$H7<EMaJGD!G>uM5C*a%Hu+u?^sT-Uw"TKRE{kgr5`*0g%aM6N{$4d4<yF-S=KC25AyWWf.D)*k#,m-L%Iw&r9s2E4O/<2a"Kf%#LajT!J8Hu,?.v&7H{G`x:uAV;oc6}8JYjlDJ:,!lL5Dxd0*XxU:Yk9F2Qv!W&1Mm;L`EFS
14@5tF,a/?ezGsJq*$/7an[o>H4/F*U9&Zv%=ML~)XPokI)O;A=+>/VtI>SNXgtvf4I##Zmw:bR]Nc7I54,Wp8;~0p*cXva?!BGYYM!bny-QJmR8g
Z(CtN9k:72Ld6kv3+pP!-e-^pMe*b*d-$30X,L?M!tY(8oo$Sie6u~(r5x(CUe?%
Q<>s`3)=~@,%:vYgO/g,Rd1=,>*4[^pe
:@o_&{6@;&;i+_3hSbS-&m;uN#Vd,q/NQ57_3Jbd0O>
Zt`z"jdk3e29Bd6.>"NB"6Oqo2&f"wK%K1McRmT8oRdR3}N!Q|Q5mBLzJ1mGs}.NQ{JlY!]yF9#P&#y`yPBLpMG*j2.x8d*UV.K4>PMOqVQw=/d7@mmY:ns.s-@n=pfh`N^k!5#~YU&Va3im7:/Q&F>}!H4*UD+M*0F_Mv!.]Uf-@-`r-qPxVGm{KeZ=oq3h9z2;Til=4AN.K/dXCbA;esQ%>q?x`P,kn*N@3RQlS)b?Rxkw]&fUSAqnUX_(P3*ieYis.<2zG3b,V-ye?xZtQSA+Rf>k_$>A:$%G<EWTp9Rc"k_q){j$UhR+EoM9C]fTiR]*F)x/%w^"aN
%PCI$Ol"n@ajZ3I1x%omEe|.vx<qQdE:gd2PJ3~_%8N9TR>#woC7U0s3mg@?bq2%ZJcJP)]_rLS62-
YA]30Y
Sy_!OGObDo>DUoCx1rQWc5QIS!uuGMK[p/UgB3[pzH7_P#*`jT.CGE97+^@J!U!<}fAF"E#j[6H!M_l6v!ul6)ftp#"%e7.<LkOjT:%4(>)W~>*e0rgW4s`_bIkyT/nvZ;v)Ea&Z0a:)J3d#%;8tcN#?
"_F%n1>uyf?N1"y-^[>?M_v-[
]gW7<qX+pPHiy.Z5<YQ2+%g9-_2ru~6pZMLY
"SQAK^/i.ttv"5y4t+c*eo-CGByX~ZRJs
#Lw%v?Sb?ZkDVRz;.!-ds$)TQ*@4qi-R=Dh@g]kkgmT2^pk^5i$W}f090@W
Hkj*&]@luT+/M)1M/=1*bIn^%n;30XN-0W=[fowE{)
mV:PJ62|]wcQI_`6qVoD)BJ1p0CDV8;h?ef2^8ry>:ECnXaf&7b5i#PVM0H+#/bTl*F"*wKC=1s<b!05uLip525dN|g}X8jvMza(mnpy>/r(+|i9u8K#?hgVS!
J6vN`C8&Lhxbp_
)G6889snyMc_xIq2=UobGWT97&dPXs.IG+gOgiFEs^Q4OEgo"~xkTY3ze|M+<9JKM38ZWzKQ)/iJuqW?ViMWqDs@C|]7os"&QdRtFCAhoFHa-[,*BNlp9&;S=Rs3W]bKc}?Kd4-qN=ds8L@n.SJ1@=orpGLdE(Qg^eh?+<A#.mCivoXx4kSWmRPU2UA7drgt!`$irktJOvK$GE_y9s[1``A|PR5_A@E7WAX1<r]%FSnz
7;OR$JFs,n
@48lH+?K)YG)9V5Dkxlf0H:^Iq=t6|0o>UV/UsKc`*UH6@HKd$wa8KT@yz`+tH=}J+U5*)CfQix4-gZ(&W+FmY>k+X-XAhx6ri[qIfQw3b#hBf&0S!A}D_mXLJc#G0:B';case"cs":return'(]^@)bTG2*U!(ie"]Vx!QJ7*QE8$Y98o;-,oH0`(
kj/`GoR.lLWq7mD]Kz=Sd`qw%^O}N8`kr#Mt5yZGTJ@T&j(/)vb;w.jO[jB#JEp;cgAieB@8Vc<9[,+fG(dwa4@RV493LXNx+Apa9>bmwUn=+.xLs_oXe5C#,vK20UwSp*jO-%@!e9"48OE=kt]Rny!G.@>jg(
VAUG-;CVjd>cv*Y(`+@_i.yw8Xsqb2~d7(FnnCJT"P.K1F/Dwr3F(L}7IH&]"tl<fVL:?vXAl!D$[Ck1S=Jp4?B=-Z(1sp=I38:lr
&+`o_P/u-k8$5mAp/c}C6u7E21*4yQxB95jWxWedjUwD2l,BWi]jm1V3$rz.no{3iy,H7Ccy@H[#~A?h)HM;_Gyu,<)wU.`isA4f.t2vDc+97H5JjMz$Qd{vhL%iyAMkx"mGG!p0dZiS*mpaKK4KWV/TE](yJv[W6+i[.@XUK+L<f)?<1ugXy7<utaDwkZuQ<g$Xkpi?3Ml1Zi"iE)obEOuMWGOcGyF/"/~I:=DLr`X$C]++AnF`"gwrGt{sA06h_:wF-=Mc._/I6bp_:H`[Wy-)EbL_rN~3^553*F8U{wX&7fVN<`lF``iYh.6HS$:w5nlu4&zt72fKdST4
$F,^kg,k0/4V+~v,6I]E.yti;C!z?:Jg]__q6cenhd8pbL&ALU,`*tSa=F2"g|KR`wV6lQm_Ol6:j27A<Ll=`K(L#xHhHKx}E[RR]42SZ)JP*S/sG^(*!Y*ki3OKpKnmQ?a#<6EL&6;m,2FAu^yUn_Rp9Qpc].1G
A;+PrfT#<B=W+Cl2BaJ?ELM^D6(8r[DGCP1&.Z9Oy@?,YI5FO=gT]Y!U<^4.wP`.#[p44myCexk>A-)?LcsDGM<hV_rnKL<?D]QUXD,#PW/:$;!
w1fy5Y?fk)nO`@BN|]mk]c<AeoTn8GkqZcrgb*MuBfoB@8&<&.N7UfElz-Mw~dvM:O^6vi>CGvbBS9_c7BWFdW-P&OnkX6F%u([uo)qD$L(CNGq3dh"9%`:=cxwt}4.*uuS@uk:XQDLbg-Xp$JvF
;*79kGw@RHv!>]+A
AAGkz.FX"`B-tdDWh"Mtw?Bu5)GY_
z[K3(QYgGdt=D>`a{E1E(<(Ra_`&lhLsCuik0A-B(NxIShWn#*(-X#[T3,=>{%t.7$PUhtP<ln+yX6|)wCp,-]s1IXp(zL8H{jk3QVRaW"1@]H|tWo`COh-xcB:
MRJN;NtwWTFRCB>__+gLok1S/e/H^5c*(m`9P5Y"2A~Osu1]IlHZrAu6]sDychGFFpSp^NZT((@$e&Kr-7?nZ4VM7&-_-Rh`rkJ>E;XYdk"i1n)J;C(ak:97y%"iu"|l)4GBll?(s6A?)`P28=pa|MyM@_<-&pIH.KLd47~8$lkTY<8v01}6sS%R2r+&&E>8-7,<`M6(p+gXp7F4uc=@ho4dEQgZiqI-,O&Ab;1aDe^r$]9%U_]+FkaG(&NmkE
f,G[:O*_V&=G%8/r_?e]L-m^0sP55Z!n9XAI_*,nH4["Sa<RFDwy=acI^F*wdo0XV.81,YTUL;!nHZ_&D<9vc/<TI>OQr8R/J1qJ13d:b]oc7G.1i?Qz*WV*:u,1AV#$UQ]Loa=%(O;!C3.%dZhleZ2#YTjb
@q
mMtyIf0jWFNt*@>@8EwA4uLS+fO7V.":F@(1Ck_~X%Y0Hw4C,o]3)w116ZM]"Va^?:M[FWZ1svc^@
n&X_-#=S
%EKth+;Jv:uBp@e,paJ8bCS
[r$^`s")-Ot?`Z^7j4IUo^+e@LSW#"z6GsGR{&g_,aZcv7W/zQQd!U="^A=doPGPFaC-D)FWT^E3oN5:;*~;w`fi|ZCE[5^K78bK1Vge]O+c[)ey
"@-z+KbX2{>{[/C@q&2U$T"),h2HG8LBE`"-"9+?I3$(^
A<&w90ycWW$]6Ej{>Qrm5mVgB#iaPt%9^:2;$yT^Z@V.HL@OF^
m3iY6bBVPxF7zXzf.Zqd_*S]D0{?YjZa@9eA0*2,@i?(b:ysm6s>FyD5K&QrQ%40(66l75yn6#zh"aG3.w%i?7&fDrz;A5L>&&N*kJX[s>%SWw/[EIA9hi)$ye=qt$j)N^HKDrYmhFRP,)DihYX]6$5+RZOe;GUF[.8+E!6LIk)G7t<)go
Qor3oIBq-OUz*c.HEvMoEe297n#A+2B5"LS?"|n!LayOwMbbbLBbT]u6^DC/rB_XIQPQ$=AbYc:uP7)cH3-esUp:fBOc`jcKl>b8n"+IV;m3yg]]pWo3axCL@S?4"p[$8oO.N*l)fm.@F8KnF5tsWm09B9(CcEI..{>vJ_D2U[OZ=wZzg~frR*#L7SA:6f_LU4iV<w2n`oYim=k/aejNH09<Fg&9A.Sly,m~;c0z9lx6"hQ>2$r~c?WTQ&a
q,k=f!<<Z@
OV:0i-mPm[P]Q<.bZ,D=#GZ74gi[R"![sF?J9K)Wj=KmKfdFos7RQ+qW7n^7GuX/iGN1_;TXq@:b5T;&aelF+&TNZPOv=3%$-IPx9ubQb5bYdWYQ%]hhORZYZ.g8y/9cPW_4FYsomjf2@*obc]S0c#tGV<UcMEHhw^52`bhc"dBw
JA=q-TW_hF8"dU%(p|_>=8-OH^p"TDC1=|9e/c#4U_[!p$oBG{1E:To1bZfc.;!u?1MM8|(aXCqN>n4Xh2N68Ocd8|6,TUEt
,bg&Wm-OC^PqUFyg<Nfhp>)0V.M:E5e$k:vTzq?+zwD&A%ako6Vd^U5QoXnEpZ3Xg.5<Cf5
|j[_y&Ci7AeD+Vm=

vaWS>CCinF&&^@{j1h6x#*-6qfn&Hhl>%Cb;8Dek<V5<^&d<bqpi.%FeYw=)N,*3Zy8U0#pI
0m(3VS.aZaI94R0C4^YJEt]k4p)j$q=2Cb8+W),)f19lZslU$#>f0;0;I1jIK0/9,sOfZPiRM>KmA.6|Tq>
W8Faq#g[xjA,m[=i;g[[C6t?>`#DI$[S`(bs,Ui@!yj]c;+hs@58y!b8if5_Wr3I<D*4.[wUFbr+yr?:+hG4HJ;
AtpaFgK.w4m9>V=9i"3Ni8+uxP8-WdQ|A`SR6T
%thPmFuGD2y:gXYOE-R]Vt{W~h7(wJ16&?2#_4Dl3+%Q=_#f`I%I#cKn]p
Ta&JySPExJZ>5*#I[2ewVWWaLqsEop&[+[?~xxiVc?t}fw*Sn#%I,Lwr#u#RN!XMc&-d9X5g214HWBETtnrV:$9D!bFi]=atnl*a>$nJ<(rw@p=],)9mT}`swewo05Y=x65r^`C[g8k9<)9!o{&b^x.Qh~b?kY@{Ofj#aKwbeaMSHiw_q~P~8%>ZjbXqGf0pBHBCVQmwL]20QSU2(-p
sQiIKj4<krSvj^W3_pU}/EqYxB/Q?+(s-x"FA7kCTG(=o%)W';case"da":return',Zu;C7oD)(nk|NR!)54Y2mGpNA@-N4Jf92vQtmjG46tm&T1#@%(P"Bt=Qf*qf=2x-ML6X^+Me#Tl66h!)CWJKaz1*l4
cx_>5FSw;,K
wc4%z[_fb_:,F_(-^A:hmdE?+cRCde+j&@:w<bmEVM7TO:k_d?Yh4w&uiNgT5P+!1G`O$J~Pgy#(Mou&~Dt)c,-^Ojiz%fe
jz)W=(+o<_a$7f.uq]|E[l<fGafR^l#kF/j`ODDI|D3^)rQY6r:EGCkZK1f40UsHVhAuEJarsMW<u8w*y`[!4bK=j"c<{,S#xri?)h]y|&]bGeVq1C:nsxZ9FkZ5<2a3vJyUj6Bn`]]hR_vLpT7XxF&lX.Iu;0!mb9Y->[sCf7PnR1dMSFMfi]A&m+`fVC;:((/I8hpbm</^TL0.F%pjVU.^|Yg.9E""pML#U`vN&H`O*c2)]b"".5FL2"-B26e4:.P4q)eg>#[4)Z26.fi]Jk_D(v-oQ"[(xS
Y3rKxF6A?u@<mk3TTx*tC
isEu.5^)KL4RNk`#^yafe!Sl%bxW.Ulo@e%J[fL|B!yLvY^SX)"*gvpPJX^NyidYNthoi^`<My2L!06DS9CxWaV7a~J:lW!x"&vr")oT*myP984
hCj(Ggcl]4&5n`U

3g+LC8u[Mw;
P<Y+k)Dfn.s`^@~C)Pa<W[=uojgAIY+bh.@x0*oB}b@OTLp]6HB/+SSXuS1p?!
Y-7w4?dEt#5{(s&1OTU[iX]MN4-IW"Yzv`3^CBJq@Z`zEjmz&,Qrny<33L.Bx1x[t`R5y0j.CTOi5T5xpbRcL<xjQeKCu,B:[62`!ZBrZG81]
LI=4$NHVRb1u!=OySUr9oee=[Vd5rj(7T8wXwx(^[f<3lbncGg/s$H<T)w*2/7.];TtgM}2eO)H"6WwK#Zne`p>xI:CiNdJfk?h9[(Axu9%eixN!b35-tA#t(Z"3DHM@Z-a!,<cs./QO@}(qo/P-V}==
>tv!&8#z$`Kz!FR(</=2ZL]x.b23CWY]0z#rqR!J@eKwrfuYo1.JhZP;rQA%S>X)Ot.qBY-m?30I67|p9IhgOu%@+vN30D"@km
N<.
d^t3S:;LXjmpU8t-<)>tebq:g_f!tIHcuIwxyt?^e#O0A6-}YnA"+_OG)7$lfHL3<ID~qx$Dq!f*oG=/Ijx^a]Wj<N[Aqlei^QKt6{tYP$VPQ]ZNr6;O#1&]nKTprsvB8o)M:wB.)*`F^Q5uKq2J/)Mk_;8lFtO6R6>:%-pR3Aj7RV-D(zZW`veL,(><x%F*18rPM`e4)hk.L*^q)pt64)o
[6,pwoKt7ni&N{VH)pf~$kc^?kw"&X6g`rD?@4;L#5+~.,+]v`oTLJ$HTFdp<wR}xo^ELF$d5lUmJgq03~?O-}bb.5n/sN!IYJ9{7#-keQ^G3.vseD(I#FE0laD,y8McrbZ
5xT!3LH1X;$K-^0*$%@AoN[8E?1_%IFZ5|yQO9>.Ud<:HA#}Adj<g-2>"oY%NU0!Qhb"/u1d"a;Dxgal=@*khlT3*Hd<t^3Lv1(C[jgQKo:Yg8$@1qq!=]:-V3Z>bC6xlcD%0D$-<lsxlKW^!9uHRP%V*.8e
VS{/3_:7a;QNOQ;R34`)GlF)8TPUath=f$f]:P9.QhZ36.NV?9.N,>!0[5[tQEyO==5@yWZNqP_l9sI&9*Ay*2HGEIvO9gMFH0E</k@SePw<<TBp*Po4$!@_}$c?BjaRX!9fqE(:T-as,ugGOn<<oG|ZBbE*0`wIR5lrD6[0e#4yGc66]3/l@bW]@5-B3Ajv>Fvhv.4+uky-sa3@VR
^#L0?Q6MgZl|DyhX,_8yxT
L,m*p!eT)pWn[qn&s[p>o4FBQRZ]A*nP|LTOG1=%^*}M|ZM,@TvdHC_e5fdU6Iv!lPA*6=k!dyOe*E>U&G4VpPB>~5/y:$-reB6MaGvw`D*0/Zk`{o6`#GRvRip.RX,V~D9RHNT7ku)DwZ,ye1jq#(N=(o:2pE:gbR16nb0e<Jt:q1wN[u)B~3;_-9Q)6pAL!(~#r.Jq}#f,Gi_O}%M+;Xu!za0iU`|GsH(2yRF2}gV:)"HBfXDcwyzE<mq,eB!]245@=U:(y4-u5W-Dzwp$BKlj$?JVW0.1lt8l7yrO*A0IHMR7WA:F4_Wo"J:@%Gd%}5;SKwN81!qjN;QYJ=h0?=6B!:Wo**lekm>3o@:VhZbL`gEg_9"VH5L&#FSeU@S!asV=dDk!2ZSJt&^JX^$qc3ftA/xqG]b"8R[R=M>Vw+bUY+vn#a;bQ+J";^P`5p4=+7*o/jx-,k"o40r2u^dUIy+gEqU-{AJf3m;efh_`X=)=aUdxquY]}NM<BA+[!X2P-HPchQI]z+8Gn*OGcsfPb*DYC;7(M+t,e"Nj,>flQU]3Q+(-^WI2"f,3Ll~UoZC@~uw5j@#Hf+@<byr$B1a*!`:9y+Ukf5#R2-^z#bOleBUXImtig4{G)rkoR%d!.SPQo3?
<.i*Um[NwM+MY*1IGL=^[gfP6P>kAU9A_-K`6e)xP#*3Ec!Nf9FG
;)QdNh,&@w"<1(/g-r0<a!SgA)I|Ojs^%f"Y]<4@u{B/Q$EC8Nf?I4RBB4l*Z[xQ,[=i[xk(2Jd3/)?uvFk3DHhI
Y&ehbvtDw?fqK^KAcF&llA5A|qemsTnt#mbM;PJ7NJ1@9wXJ4-hR.:jS|1^wD"0o}%%f8
0jAHCh[9G7!iJ*&k~pFc
9<=>mB5#k{X|cJ"TObvyq+J~g$dm.n=;0%8a/Iiti:Lj/FF(0dLN.f+$t4l%&4iH90JJPFqFo)';case"de":return')]^;C6kp=(nk|^~.v"ECeB&:MTAd0W:m<g;@//nG>`<ldV
I-QN6?)SK_(r:J&+l![GL[W0uDMLLJb6KVA8dC-G^}J8t5B0]^SKtV
@]9hB]v?.kC+!fp+/XyAV<I,?%&l>#D(G5bf3Pj2jJUvSPTd$l3;T`SMF<7Ya,cy~KC,/8P6e/lnWpCW>G|P!EH:S77hwCC83UGbOma+,qHg@/q(hD9-[kwD}*h/+cHcTyj><$3[Uz$TeL=ahc?qZ.e,LYfKP3cEq"W<QxhG~q90|M6M7kX$4Y:O_V??}:.eM3po;gAG
+id^EWoFl!/|t/S$:B*$vuMI/dQOAWTmts7i:+(4<
&&9Z3<-2&|qs;el-ToHSIMW1?xHM"trjU$u6b$.8Y&gm6:F@6|lXDGSMrIOEyFG8>&BY9hB.HrVV^ET)7d01PbP6LkG7wm$UnMttCdlA/3hmM?xFP,+c+-;*ma4?["YVF)]+hY0gl5j}bBa6V
N6_<BzuPYk:e9u,RBd^0=aN|=ls}yGsc>qSwUZK#PHfwI3Kj*k6,Q4;a=,8cUM^lF`0b`!sn:6gv@Qq|4I!Hq(<l2v6yJ_H~dc2NN:mjK}>Vv>.aZ3mK(CCGGEBQVQ84!K]ZBI[lm5B<<+`_a4[GFg&aE6E2pS<ae$w!B^N+;J1BGn+fei
tC4$%EUq@tGeZGrWu0a^^N69%,Ffy1BI&9-?SD?j`EZ8,..o7o)Ic4_D4iU&3X(`3Q2o>_Mb$&5HExS:ID9Ljy[XTVt#&t[2a?kwRQ>Oba!t@LIRWNvYMFOTAEpSN_tp:P$77Q}IoDW`tatpdcu/=S~&}TdAAiR2dq5H~,j#p0p&;`GL1H0Lybg
zxC2<43p3^T#H2i_-5<,MdTPXIf+:#6ngu"2J<|6teY=6K%"(p"pj+v+jFSy;2t/r0He#B|39?Dliw;ao/X]72VCQU+f=%7lgYi;_;!a`;Fh7.|R1P
wim*dpU.>W-n^IFI09:{aF,8rby~A?mv0IgGS(y9c+S=?MN[yk1|H29^DgK6Eiv-H,](]j%8=s8*</!_"[5Tx>/8M/lBda-%IWS94~HhK0Y[&fq)`Io}j[KkZY&?>.3j-w4%Y)8][S:b?~rei^:5xT2XM-6YWI4b@F?8^R$H,)Zv!v>>b8;-L<CXKb_j/]Q"/[Qp2v@Sy{rIvP1%^:.?PNYwQIiVn(x
LO([MRURBpP(9s7N29Ada`5Wn*GAZnwzCmW#>nd^"4yeQ31*oeM^DU^Cva>,x,fo2{,+o08>oD,2[]
"v4"1Q=!?ACsO>6RsDEj4i6z%ys`zo*_cFE:j++`}Kin0#d^Of=nC,JR`3DQJR=_^[u,_Bj;{9R[~%+v"phVY^V$<FzBF2u3cl$G31gOT-14`BDM[iyPI>t$Sk_aEFRPr2=RpE*0VZ/W)+FIltS"^X12D9XH4]vM2=&qP7YD%4(_CkrMNBbREZjH/SINjk[qb<<E?sB$0s!Sv^l]qSkfS9-^X6c,AYU0cf|hJV`Sjy,$
,*@k<s3k*&/s=[Hx$<.3CgSpPZJv2rR_*V8}n:t"#5l^GoM0DwC0d;p_SMXJQJ=Gp&B>
orzPu/t39%,b4HflCn.]z?)wVoz.uW~KyF>=e0ddk#*(;gEfn`[Uym4T,>Sg0ck-X(0[$/<6P;K91fNlaQ:va5ZW6;a_{1dT:^~FC*btI/,/3h+s
9R14p<Qm?+`jGPJ1<93u_COhXPH)Ia-c^CI}"QV?ZyD#9F)eg{jlX|z$es48c`QM
WauMwQMrMJuFti;JCsNZc*)]Ts+x&P/Cj[@"@Vcw>sbw!.1QD,4FZ+#w17m]<lDE%Xk-WFyhytyC)FYp6rv__:Unq:Y[#4<esJ$m@u$8p?kmK&=cI;2EyaQ
ZaR3[?cNMTT3
<`KO/9uO@ty;Kw<#U])2$20.$-5s+|/%AcV3thBXr=oqR[I{2]R[W[T:@iHwU`_]ZR=;a8
2X*^@3e>qq[<,l=it)aV,
88/@vB8yAwehPDLr6xm!!CV2XW+Yt.^[U`8a&/%VrtMAU.fluG@3LdWyjFNoEsn7m>19fR=u>fZoA9;l_$/ZKuY+Le*rCs)J*CI^A$ok=T5%v:?#mwKr)g_6
%bWF?^U#M
C}jK9vnJW1r>]"Qah&^6aUC]Uf=sWq#H?BL$DF9Gq3cN:;Fd#*xl6rhT`S3=r[N)txL1k[f,dAnBwWaVh$mHfnsx1xf|Dk2tqD$f#~Or
kqVl*e38Z]a&_Yk+&>x!{"0#J#m%*3B;EB@sJXy?TBZ;,[IVi2/NS?EYP]AAAOD2/]10XX&PMujmjg6G<Z*HHF:&_fhDq$$r`d+i(=L(f42yKFfAyQH/-I^m
[f8YodOKaTU-fx`r+6F9#`
h0%-
@4KUnCZf&<s[!~9}J"O)aM"ga(bt>mH,0wjs,<4h_H=UwbKJmWsDx//XR>6L=CKvU;WnGMxEo&H<lp[OKb&g]OgAPGy$6{<DuqW&_8GG9;myaJRCGkot6Yq=/eqgqrYJV{yE=CWY_lE{HSL<#D*5=!$i6][fh#;sb*c2Z{9P&Mb`7J:EkfU%Jc+}XBnDf|eMq#dKET4(N`Nl%i1EJ2/.bi3OMhiO-0XHSvK~O2
M1OaICXj0Da=)A%T
cH$<RsOh#LECAF,YeigjuDmr@t()dx,YG2A/ORi?+U(sM.3T$GgyVJ_=lZ@@lMih.Yj1,hYQuL(}_CAyPo=lPqGI.MG!>v2{^jtE$5(<((P#riu}fwk9gLRyl`au
G!t;,l}U7#:S.C,P
4k8WT%v|7Hwm!86+YF<Yjh_@&lcVaV]GaraU.+6ulWj;TS5US*S`C`uR`*(F1uEh^q;&jgyP$YbE!fv0ZPO$nTc^gcd*nKXyU9rCw]SH:eE;[!6hrZWc<H8d1DBpLTV3WQ&3j>@w@(W$DU"-BG.2YBmfxADfX,)`KM>W_MT{^Ra#EPHd5?;R$yXXj,6Q%H5`p@^J`o]MWI`5944k*tC
;AU&at"Jm`lm(Hu~rL
(ymXf@%+$.tnMD>ke[)UP*);PR^*l5f=LV$9-Os**@]5VqfGNHZQ_6>Q&"ERyBt]+#zyE:wDBT@,+!Cq,aIuSUZiGPx1~T"3f]1KT0#[Pvi@Z4D(E]c#^=KpuXS#mU`@e4B-Hxl$k$G>O(@uLS*0(.gtO16)TwnF6vHeo,V`iH,9c$DQD"~Hv!J<)O.-trvZVmw9uq8s|4k2saijTb!h--S,x&.';case"et":return'$s`;;6KZ+$#5$fnN>SU(3cMD$;oFkNVuV@rwCmM!s=gexvSgUGw,n_,+PcA:suFn[,r:4ZjRuaP"b2d8HW``K
M@:]M`q<c4??)`=q$K7_TpLLfx7nMeSN7Sk)1"XuzoDI%&-"SMT^TXv,DFBGv88hMh0dGFGFYrRf`ZpDeb*54eMxJBG>MyYNL^<Whe7Pkw=sfvk&-YJA|?DGHFqx[$0)Nu<QXNFHluQqQ^Sm!b;ef?b[sT}B,yr,ddwnGxv,+jmKZG#P4+k*"a=ARY0#+Fe$qbW1:czNK?,t=Mv-~xo=BG:7rnAD-mDyJ/B-0S"K{vq$JdH!=:0d+G34Lwn^jU
$WY).7Z`NjN1qPn<v=5Q
ZRR
_YaxX*i+>&S8;Ne[,Gt_2ROsLR^h.kE#Rgfj;aQ<U<BgjE;J
N,=#Z`eZ$~lnn}>M2:+pKEkt8$2HetkW?ZSRAGfwbw.I!d]O7v;Hy5s*B#!s9"m)fZ&gTyD&-iR6h9cXnY(Fj$elaUmDR`y=v~TG:s[%qwiEhE)#p9WZuUc
c~lNw&^IM}G^y(nXWwtBY@k#33&)peH5):#CX|d4H|8dEa"O"^s!%r`
NVv;0=g=Hc9go3+H,jL1oPLl31AqywF}Dl4k#Z&r#EszfW8;MXKXsOni&sBcK|*@?k4O%v]<njK&!;kuoJQQ^DxykO6tihv9-p6kNp:e)Ef~U>O|K1w5kWt=i;cQ+U_JIbMisd,aa_^me1x
[F+GY*hg;)jeqj1"sy
}T_7X<-MaM:3brl>),2rHMCw^gh5TF2QWZ4p>$Ot7.GB94:E7-+I5g(#!l9d$@LBffv_+s0W
#kLH*Z#5+,u(]S9H4?(qM`nJ7iIy*MNao,_6>kQ*B~.pdV!gszMC$Qfok]5-%+H70aCtBJ<W@L2Z.M.cLq<v
H4nn`t~UGC+u$>j&L?n?K7$^).OuW)RoFFJ?RK}uPTG`-&Tqp"3a?2Gi)Oct
yzWNN}@N??!5:A<|$#D<D~%oKy!w`&-Y8J^ptb2u[5EIEzWYFdR6fcv75{$0!mds(P,OL"_nq"+oT7_*v>?;],DU`Di;5Ek`nHBtFXt:eB.H/ir%:=d5=HV?M"WcgC&@Eq^}>F_Jk"]*1MUNgg1$loM`0->$r/SBo
6|jt>v1p;zCgx<,k5cDW#
RTt_n(F~KYOWPz9Nu(EK"_UBDDJ`"ZF.GCU
m#CpVp-u6s]OTkQYdd1|(9,|8(H|+I3v(E#)>QT%pe->23?
F{@Ihe-1I0FI.Jq9Qo"~`2w$6v(Opb;PS"ju=npfi>l1z(vKBmw?>$<SM0T*Y5Q;4Zw,n?:URWkj4fh,(.MkBZ/cOKt+!Pl|#bA4>uu+@I]V<`p$a8^%WD;x^|e3px%fjUCL`16.1$0tIX?USpO=Tn56o_M;qSL5N=s>f&+jD,OV4hrA^O9IE*(_^xDN%wksG_dZktg6.,G?$jqO=XV:!`ng9DC1Z8s[AMn.q5m
t="gM(%@nj5LH?6}nq_:dGa>Yv.HEm5M6:W2Eep>/rp+Q(.[$tv-c0XKX?J^UaZCMY
aC!FppD*O]9(`iCNn7Ad```ob=-y}K~%*/^:}To_?q:Z2UfItRp@&"+k
pCra0Q#lCK`yvo$eU*w,T*76;fmko#KE5c>W1kt15M7gZO:-KO9j!8EyXmq}>5_(n`./Sim%tuh
5B_aDtgUOlj{K7A
=3j+O|R5VKnZ>{OYm(hV@@b*?+yVcW#m/A.UfEw2C*y@NU>;M13"KSn18<!,QDlcdg<LwvN.AGf^^=7G<)1{>uK3dq<2z##F9KJb?S2xBB"#v=,[!{/4CX8_nJn;Bd;2s,/yTmjK<6/!M
@RszH
M[s+eXPc2eXdTklG*xYX%&km6ah}c^r`cq^f0f`Va`gzV![@puKw?Rv2XO,Mq4HzTrt1-^PQjkjs@!A-QQJg!Wb@0OWu8q<mk7VxMeehhsBwb/7F)[[T!o<=1.;,(BpDGN7DX%"?.i#Wcw1CTy(,Tbg=3i@fK95w/w;r,9l(RVJQBgqD#apf="=N.Qp?Z
j9<N4w:~:EiDF9i&3e-vrYi;I.b]dcRzfjxGgBN%3&>D1!LiI0HbdCVHr(B0rR#QROEtSu.qa1B[=5V7StjLVH)<-$%{6vTYCO%q"(k&L@lPROnY4B2NX
l~2SDA_`dEh,+a5?khW+3,L$F=8Ax<CVXA/Kh6>(>9UF8<8,i:#B1y>G/C+*G+>M;6+f5Fvj,N3t:X54lMTDX!;Ee4@[7w
3ECD;1Zvgy
gt5-t*X@W7`f6GS`D!YyS+c5T6=n
cJC!kSQ4T5i):-}PO+OaiMj8@Q)^C_]Y/Xi)!tEcL+034-KP-r-XY,n=i4]GKRNk$/q,Wk
FB0.+};):1o24EgxsKbA8P';case"es":return'%`G;BcsDI(o5*o;$>5#/ngw(M&y_j-)_/),ogqiftlV)ZXb!o0VgLpMxu(iv6"BBaLa-fx-yP1!^+Mm%t*#/D.hhZVcW
b4b7^S@sa@NShUE,VXG!dpp?8aMy(tnAkh[o1y7*f&;Ps>qlRZ:46Lu)y>2dA^jxGLkcsY-]!|9Di"-$3)^l*gd7:lX<2aH3PkCZN[ve1m0{^UJp3*48g30/r~Y&tQ]tfD#`NcM2v{^t27_4KtmyaS)W0rtWxQ*#y/t`:NZ3xR]x9^]6[|Aev
F^`]@x>,5qR2H{l7JO3E/&)=s)A`e+ZC^ki~IvYs*r2jpp.nh.6<htDGur/ay?DRU._))agmj%T;v_wzujM3&ajGG0xNt*(c#Dt_>vcx9TwuDW(&cd"psYB:RqvzFNjjXha?c$feYjwP#u5M=/[%sBtr[g*@D;FHLo!76;xAt@e/)FCtyj&MD)*eS7B
:eH{N9Hc<L^pF{46Bwh,5G;ss0s+pB3/GI,7&67p?@-)X>fC!)`,n
<9m3)5gA/U=5^_S^2~Bs<9#kg|EYAoltnvT;`sFzh2%2ebV-+v-m>z=v167TRBxCc]sAjB?*
0Xl
oFVGY:,)![789bS%~JqO!(_/fGBs%uY
ten49Le)0=KrT&nMc3uywFQLvHnl*oh^~84FVhz$9I%_;fOpgvjY8CrttD8D@L[5}M3+U)9#p4t"QJ"JJ&UkKN3bZac!/mNe2]de0Zpt,&kr-T}(WsO&/6_?oiD84%x5[aQ/RqoB.a_F4HKGLol,2f@/+!U3UK>9<`O(RheogTb@#WK/EWy_+9P4)+0*b;Nx:XVjxOg&2&<XouB-[Hs!{8V1OGTF<XElX1ZI6.,$dnaUyD{9pZ1mkZ@5OSSGgY&S;7ort$FjArh&wFS5Iu(Du#J.V3w,omfM=+<Z#=Fk<n`At#!+EOU=[gP;-C8o;j$.%nzCd-$4zq3T%pV-q&dAy:zI^.hQ|?%Wi?/3M;w/?
L&YsYTq1rr>S}(3D/L"-1%Hb}2;[C9h(e0V4*eA"3D^;k/i/S@ct*A~>[o,js3{yJ`6.SEkHKP~As+WH
%/EwQ-5kbC;]$e=QH|b#.YPkp[[C<D)KPJEch)qIrwFCw:QY.
/pndp@GQ"c+bjiH7RK?$wGH@3990yhUY(%wbgA5`f%+jrVj^*Rz$QY>P:B)tdj`DQpkKM#BT$7L77NN>[~%?6&pK4y+|XZyVbAyIL}-d:/fd"2#N#I-ZOU)78rh~vLn}oYo6QsgyIg;G)wna<XWIWpy0fLC1"y:((5%3%qb*jV"
1]^zhp?R.)%wXRf(4]jc-_H08E1U%2[co,5e$m+u,Fy05F-8)4$PPiecUfioQO*IW=^N6hQ0%dNcj/?;DV*(GMk3b-y|-qh7!257t`Rv#/%;%L0sgE3G6V+pX4dJLiUmAm#lgN$e+?4uZz*<nE9ZY#$y11BpaakK>(Z:aAlayKCx-?8^f/LNt!9=4lqhd2/B<e5Ee!>=*N!tw)
l*ogz!`hLTI3|(OU-pF8%!e)AS.>h8>_.rla8Iubv[|qcQ~8>E<5S8aE?n`hz1pV^DZ.L+Jr~t/*F/n#wpnnHxe0H:"d=+Nn4vjV("HryiM0:vu_"SDA(xcrUPJ=!7MQy"OZO-A1S%Twi<_jb[EK*
e$~"mU*cU2S
Sp@W2$&;gehCI
?meyB&WRRK~<R;n-mT/vHp(`>uXcdB]PapJp!A]L^n1q|=Hq|PKc:j2.j&ETRYaNoyJ/1Dh$CE-1[kGmUoc>a?x">(Y-ELy1]=4#A@Je:.CeL[J!o^_RJf-FhDO!v+Q/kP4c)`>T+;o!bfw74bMPi+.UW8&g8N+k(;>L{E,F~?:-.msokL,jWv/Cez)TRGO,Z!~+-X(AZ.Hc/[Z16gvIgs~,LY3oUIB_}ok^8;Olalu/rUe?`J:5pbNpz(MW_P%VyBd9jU(!([wl3<KdokF(t
xFY!&CfP(`p@Dkzt+^UN:+>RB#r,/;y+C!p"sdqr|=k%hMu90`|VAVyQ
0E(Go50JndoAPU^;YQ3P,ydZ^jJ
D)MC>
`D:u(LDJUSb)
X04D6;K^=VRq!euyEa+Pfva;o8s,Hc|:9UH&CNLV3R(Rp*9ZjDXl$s!Zn"E!|)uZ~#cAgR<,:?85up*W4s&HJJiq<k
=yTFiYY]WD&j]4OY-<Aux6rjJE3+j9x+_?Ls5)j6Z,cTVYo0*b=(8>A3!Q<[QCS$C|!<Ef7-?P@@!iwojzBT?;RA"~*02`$BH=8c2d8.L1dSVcP@S
u+!7)~/`jL[8UT=Y?q%p/B[H(I<xE<4}dpoKeS.<MPmrkXQ+JLh?$[EHq$3{d8]GL/VBv9D~$u-e!SPc)E=.xAdR-q:uOm`=X;(*vi4SCSg`o~(2bt0Bk+l$rCN{X/.0;Pk[yq-zN.lR9&pIwTU3yZc)@.c`nQk?n^n_0xx-LU1e*4F9Y]K6P:3D6jy[RlSe(5yIZZo7ZcB
C!5uH[;hvi3Z?S
#SD5CsHK8iR;."ot0h5!0LgvZL2kI!=:FQ]_tE<Y0t
sGb^_P0ZM$-UWrk-siGvCB!&`0nyl27kpBj|(8@rG+d,vzaV(K`?of<:9Z&5
5^Ys|(7p$oVkHt>q*Dw?:AHKyBCWua7jM/R@Cs@28maX3m.IC;|Zm5C6N>UiokWSK
g1_@bgC>9r/Do[:M^g}<Q^:hjZ?THG
qgyv6:WUV4X@OXW=M1Xm[yJBmTmDjEam@J!aS?KrA$vnFMBMgE
S$_<{d:UI3*Mf9oXk_/Ug?/Ta^;1EecZ#8Xe[)e2ch|m*Py`UFkYS
4H$jO95BJ.-i|ERBX5aecb@PP!{j+cfF`xSy1T*O^4l]l%0Xq%vW{d|Wjc~R!%;SPK|kbHU"x]ZD`vFUe_hcH/y4Rb]-hD-FK_:S9AQM,>]J:jX*e[*0Wgt,,Ws$RWc90Q*%@k|D_A:#;Tqj5jpvUyZ0I&.t6r$u:L)?/DiM3&*govLS)m40xCjR8S%]v=d/wCk
HbIPt-xFfDRBG,B^w/S[xo~dzx^Z+b2?^$AX)FTH2X
PlV/(%G@/GZ[GX&lrFk6VzRFTPdAvMQGs=]P7!Jz
^V@Vf?1.`C2e(3m%J@M/qJ`t-x(`0q4!C$l1O6k>l46i{#!C5MKLgJ>-*o9*3C4BBFMNn';case"fr":return')ZuKk6LD)?SXcdP+8EnFNF2;zo2DQ?q2x8H#k<Jp&t+97;Cn2^&fB]jgKygq%Z%.Y<jnx<n.bs(H>XJ=9;#@0dBE=]`f=_vs<cIm.Jy_J0"W_;wR)afo*4yH?G83JL+PJ(zq*9$K5_^l}U0UuE2xQ`0FTU<`yGVRpF)"pbY4EEB^(/:0cil
;5}H"/ZarW0z)65pj7vxLN{j#0;
7z)MJLyS}#(XImf&B>x%*YRo
w`XCy}Y,k15=;a9:w(7Fo#j7Q{w?B[^RKdR6X1j#^3mHA{$>Ji=sV{$r]
K2wYYbeNRJ&uc}9?j&KTob9]_3O1MT^"/Tn
K=_-)Z.%2$"X!`JaT69FwV,f6})Fm~UJm.n<"h+`!Mc*-45O=p"QP:8:5o?1q|-YB.6)V~^7_Vtpc:`Hl_FSZGjKRjkXr#mQ>Tu=MHb|t3jIJ/?0[g8dr:T8HV_3$W&5g!)8U3<wLJmfZ(VD6{u_"V`NvBwCvdl$,.&.t+]JL=5zT%4FF.p7S,lH;dPF[L!-sevlQ.prF[gBhpmDPtvVF(gd/Tboai-`:>-6Bi6nMF#8-]w,(D=uR4^lfAXkE06[8Ia9>7"+h6`t`pu8veM
pd&kGmqPw[+(D|9QK*ZpYUki
d3|^:;bW"GI6~/c48q2)%2-lJ/&@vKvLOB3$Z>?u{R;#~18KbW:EH3y#Qd*j!EOjNeiJ`<G?9*Ll$qzKfEiH%b5g:r=%BTPoz>c)0N4WGE+K56!f4I![I[/8FPW@XE0vSv0A/r%p*.uthq?D`q?XjO]OJL1>XPq,}]a;,>-negTo#5d:qNOJhH]y38cxY5%9PC50.0j5Fu"x:ZC*%PKokw2&W;`f$w)Nk;HgKm($@G,F6RxW$(=BHCClrs9F8M:P
t9oev-j[r{#M,N6XD`2^>a^EP4Z|#GpjBC!iZ@MaF}yyhh
&>%=kp!MO[MXE3TWmiuA$(#cvTxxm)d7,(rQ8SFpjJpSE1ZC8#+AQ!.FlSxhW&7J-1m^{mOh1+[-0Wf-gcvel"`#_U"Hy>;q,x86]1H`9Y5dvx0[O1A_HosQnT@VJ%H0hh;V_g9YWxi((RJSB[u6h,W_r`w0>$-M"8(hrSr.a`4X@Jon@r2)jeU,<ahnrQGW$:zpm$Fn@/7y?"w.04iBC[xtIjs6JhfEhu@DM4fUShgk"efK7cHO:AH-0TXRVHZdK>~C<FIj`x_:Lw!4n.d6hCsCe=(><<>k%G`s"SH)nyP`"ck+B6h9bL5dwj59BbKfMr^%Jh@h1%Lj03]!d5[Ia`"m":TDl^C9yF!]qsO7Y+j?aBk,_Hm#L.tKSqrp,/Ru(=8>v0;UB2&8T_U
3FZ1y`4V=)guCe9BS]5!sHIJmPm#/Xo=]SG=:GgJldtFV?4QZ<wG(_[@^/s-m$tAp$XQo()PdiuKYs/p4SjMYY,Ix!F@pA@fmc+R[cu2JjA?Z+G(]#5P46>h{piR^.NT<%
`ziub8e):%"#LF+xkSwxeR;-Ep62_,(4ulExH<8c/W@}U_bjOY,artZMEOS0j:ahIYQVI;JL,N[jh((j^1tv+88
3H
|.&-8Vy*}J3/bUqk`Y20k4c?dPj,8BGE)rgWUb{0gY9j44I,%<n-Z
@!EmCj/."2_UOwM:M5Ono!%[ar`<iZ/+|XM=udn/x45,oC(1(M<ri<".JPX:BN(uMOW7[NZvO
a7^^o:P11Xx(Q--6w`?YRW5_Bjs62aV<(Nyd`E7.p$t6o/Y">^OoiMf/i2`;]<8Y{u.h|]3r-dfYNMj-=Y0pJB";==]nF&VUAmRB?Xqk5e?:|u+%Js>3n>$O(GCo=x7BJyiiO2!*DOgpT+IkaLe)Bc+/kGT_ASk85I9kE#/h-m7>@#R]Kv9;T^#N
Co769rir*)PF=RX"^Ay6?)nquO<aV[i68[)A
.$t"hMGwpO{U|)$+=H4>%GBY7@yC^5H+%TObsR:e,HP1Z,KF*/[B2]692+IEk]&#,kvQgnK2
]d2^?3LV9Y%!!OD0[CEs;=3(Sd9-epF?3[kMPQ%UEh9*pY#J?L9|.
)vtdIC:#GZZB8zs-x/)*e&,82x1w<5Eq<A>d#)ZeY7#tj=iO(Z+YZ13Yy)7-){qtNgx,qC!FrnL_h%v]oO:)@_6bQ7TOCVtc`w$Jm=Kk%w>gUnrK!$eW]?UkbC.%66,9%GX$UV@5bwO}x+=kiZDA%"Mrt5`~p@r$gNs$DB0[Rt)a-S<zPr1E1F`
Qg/fLH=Ud.(TpmUEOlr{#O&buzX-ERTQ1Ghf@?QKQ^:;-RQk6A[5KBTkh|tyT@toe/HNch]=Cq729)=-cI+*;P1R*.jl:/)
U2>G"&$[,he96#&k<5F&mS+~^uA^+Kk`8hdeJ/5`)c:o-*1<Mh&55{+U8kWzf*lDV!=3Saf|En4(*/y?1PGtvgUct<-qb&^m.=@CQwJ%#c+~k=g}sG^KSVlnXLi-e;=rKf?kQ!!,C;UMslfV]H6Dw^4i9%qQG+8.K&AYr2NjQzI!2st51
O;%kNf&0GUH1Dc]V-)cWcmP~L]2`*GB:?T
W;Hr0ve<oE&4ZbmUw?4GjvQF5,q!xuMEJv1._3pj
M1.*CFW+=43)6D8jq}pL0t_=U!@]/?q?L%[vH{<<L3T4@qM(o3_bsH/&b-W{JSrM6ku@^(S//e+k,Wx/oHJ#MMZ[*G@aARwjX#,/7zuW.z[BB!BzWhOgaP^w=yF`h^KqhM
@3-?Zshus"5xM1vi6$M#h<dc(qNB
oeizn9u7)kru;E`j9{i`Y?b
2<;qFxl#uM63
)sk4DafhqSRfU=t
_HX-`aGtj7m`C<@P:p=ZZ6*u/mOLs$<AP.fD+WxnMP5^taUTq6Vln<
t|4pnLDbYOQ0)TKce7J@=fLlSHsL0DDYv!^X/ptP4{B=11t5C5<]1y[K6SoY%lxADXfJD[:aP>7EM639i#k)uA=%g7=BR;HS(vNmNBa0%^E}As;|
,>&%Aju)YAK>h,k:zO]^>b=;j1zW^yRv0d#
|x3B)AlGk0Ino;Z:v(6,A^].O!{TQBEj7Za!vx,e9$Ulp2zX(%ta9;L^OrhD`w:^5P]!U@OJ]>?yZe2TFtK5tq%5je&QXphZRvu?M@spQ"q8I?(!|>ADj`=Bba<H8Oe_1_2rwm+<7Ylx0;tvcWuIN-ikBv/[1iSD=
.EKq$BiZT+s9Wi_hGGbFdfzyAQSd*Y$e*qr0BV%.+k~IsiU>ykFp5"Aq)
od$#E';case"gl":return'.]^KjbP.!E&4oi`":-#j,L^`
D"S38(uCHgOKCSW5UM%h1G.x
6V1/pu=6Gr"NfEZb~_xSzudfdnU>8;N,VJ`Fp_$,TM6tD5y7e5>pW"whoXkeAqo-
t4XCOM$BT)<4_J
^.H=n:3cy/%5a?votqMf52I=KYr`[Y"+6t9FY<YY]kJStR,7qQDjzd9hmwYLm(l6Uu&^0=.-%2PBsMP%_Q:D2_tz%w{oyR*pC6
m@wdmR)~L@,`y7OQ]]Iu4{M#s"*`&mvXmExO?Hi]SPysh1]u[eRa^<v}n+E~fJ))2Rm&H?]s"9Xa1v+q3u8<>i"<XGz(k0F4RYGIPU,,#lBOXkPJ-X;ZlW(Y9p.K_@P+q9RgZ#>Um:_8_!qm.!oFePs*F3ayP%SP80WjDE$vM(kP2SQrWsq0flc#DX@,BB=-Nb=>!dqPs,S1.qMRoAeUPKiEtDL[,gF[7dsVo+$Cn8(#Oe;O5n03m:R[$ha:bCic&G4[b1a?`CRlG(M,,2Q}Ea!C:t[0k[0LlY0,dmhi
=H{T~T#EpB5R$fsa>S$d
^bu-&HEoge40"(7bhl>
i;XzcMU>"mB+p4^;9-U8S=-)fXZ?U$^z#`#(i45XmU5ah:!YliI:LK;@NKJt%^-_t@B*kEP.7KdJ.diN5yAW2?:I6YkmH(iMO4vM,DMZ&r"s$@^ld(ENaBB8:W-TR[u`-6NABqWp-DC7@Rk*(dhx1TF]a1-#wq03Q0.cweQw8p)f.bwB!g_m&?-8+L"wDrH[]]-9P]/wpw^XU&u0)I8.*e=!m)GF:r.hw4R%k5W"m14ki_anB[^4>J*^z(p]ovNad*5xI2j,_=Ex(wG986Pd9ePwb/7m=A
I,;^B`yIPDZ[%buf%8f0%WS)V7{U|p}=b`,mo&#59"ES,4(OML,q`*~-kFHtQ2MZGALxJBzPH[J!m4^t@bB"AZmW]%zvsX^X#o0*#U7HWb?ZQ,I0]U|/U=-W;<"<1F:nW>.,,Q?CtBj:.tM#UW[GeAfXEnuHwv.t&K^j]75PVL|E
V[V)]wb[0-xp2U`d&
ZVRDU"TSLm;$4uOY=M_&KpWc/D6]Ig7}<?VktZZ+:IV>Y(.o(]D%sS7e!vf(ZLD
J8FDrd"=+m:WeP,~cdwdf@)pOrhzF0A
4Uz&3Txvgl//eLpztE9RY.qP-|@#M?"AoF*[I8x2hp?f<QR|q^`le`t1g]YRl1+AhIO(HpBW2n6i<1
DpCI/AX]!#ZWcQolIoX!2&`/<Xx+<RWnPnb%]KiQo70tpm5"u1uw53A^:%pbEmxN@-R/107Z}R8MK+y[>5
,xZpF)Z3cFQtC&`,YglKwBn=n#-xOaob,B6WT6#GEaD(P}w1v;ANbz4Ak|_xqc*>bmds5MgM#svY*Yu[P*ykDsl;xRd=yBwep.*Qvl<cE3E7NMY[
CQ)w2n-hHs^;gv"lAQO0ea]i`0<I*rU,HeoDbKgG.S(ydJ_#]d].~];Z3W.5z40S!b7m>t@+w*tZoc"39<7:=+QggORPQu
T~K#(],Y5H7VUYHzP1^@a41UO^I=nCIf#9n0NR(SpX_s)2><w)$KsyNb7.<7MZ"$BNRgd*HE-$xOCO-)/KY*s=:S,B*Xdq8K?2C4hbU6e6?9s03N!#ZcKpb}>q>#s3x`ctcZ5:mUy^Xhut,w<um[y"F,@56nt-/@qP[8X/^5ko&lD=#xZR%KPS?A4n(=)eix@)m)*Bs!fRL.<)WV&g_^cHCfAqd:OK0Eh/PB%WQ;%:fRW(etM3f0@f$zYY9Zqm87
0=e]t3VUSmb/]?G+eu34Sb~LdE6Eorbi_ih9fchtQcg2Bf?H5>QE^v|OOpC-4,"VSjO:[1WZr4Uir"K$V8E9s+?(6llvFMuFVJ"GaJe^"BMtOI}Q3Feu`tO^RnJLna;t@D$YPG?0K"jSiu[uHe[3Plq7ElB!,6X506rO(ZH!I%?M:QQ,A<{$",&d5`t0g[~J)%fg2g(-ynx0ehW-s@!_J($G&]<)q%@jACNu+eLPrGc[uPg?j]JHK
<hMV5BKtSq>r!`UHDQSlSkpB>lwDveOAVXpK)j/[:9%ks%>>*Rc.htEYo4}=9dR@fJ*f8PeMP+7r^3W;7^ANd`pJ.3#"K!;6@1!
Gj*:?Jq2%r&sr,MfXcTRd8uUOe70%okMHL9*+yL+:1*[>wO]=azHc/Z^(R0.0D3vcsUT#ZC`m"eu00e2mq-,rf(1!W|NbZupV3X4M(4DD?[#<$pH}&RV$O;<@:<vHw=3D<`W@U_1QyB?ioL^)@[Q
G[0!"ey7PiHXH(MU`.5](BoKdA"{44D.+ON]<bpd1BN+*,CbHH.!*6Ys0SS7ZDyRjU<%Cj65@">IZ4a81[B"UKA.3*D$ZKyy%x&B8EK*,nw?UM*7Uo;n!jL>X0G}e1_0.Pemrt1ogNFn243De+`+uhU.NB

y*k_rz,$sI>y"|^%JwHl*!:5lD<yNS
qS<
5<b0PBRs]jn^z<{8TpEpyVtR@anpp#{3?t^e$Ud#B.lfXBxnMy20&1<]?pSf$RtnPv|-Y:#Y($=;y](`Ew1)}-8cP]7aQ`R
60Td_)z;xMhRWV]ho2sb,2

Nj8^R9yvLE3P(x.xLl)/,VK;k33f%/qR"4{bUw9iI-@bi2pfx@aiZ;j%siidAL+^(f@So@4/(V!m8V}OEp6KsGs
3iNY!<5`wC8R=#2@xTiyN.D;fP>,/#-kvD"b:2h^O5Hqav0Om:NTXed.|4>icE|<tN
3pfF8cEF*4Qq8+R1X^^?we&~D!<0B>lRwr*S*Zw
NiB+jLWjYkd>74rk7W:%=(A2*
(#R^jf(p0N^xR8`BaR@[l%Bj1)2T]WxWr,@1E
:;Leuje~n^:h+90.jd;7O2=qNF74D6df",Uu6<yHsF-a&9^]`U)FVeyA3tCDAU-eFY<KKC.M+lX]ue*OXr1ntA#Uh8/nD$uM_=@f-fo_8D7!y|$
;;u-yDJcHO=ap9`MV+CK$k2rAmyH2W-}]X(;0
OL+zFqR/iYZSw&tZ4cCsdy49VE-]hF.,GZ$0:E@3i%,z[70a
lJGP{&b+$?DRg7U5Dn~]wS&%J#g0[tgN&';case"hr":return'$]^;;6L.!/#H!ib#%d(K^jzK/d>2l,U-QaZa6d??UP><"@L`&<3?91kIO@Y8Fm)]0^E/%QNkcqaKDWO[2xll[n(dsCs%GFHM{r]<KMLX]P/FKLW/#b06}(z^)c9,}9PyS[<n"*waNO~`Mw;C
^7B}Gzp".5p*Rob*ptoY"Q!Kkc&2>gc~C>)oK3I5w-F,?7bL(p((5G(:;}-11LW|r_ui1BC(@}
]5OEIoqZM2}Mw:O`lQ.Ahw}sW$]F}:&.`XE5gvz#@ula3Qb*-N($v9t,_-dK+#[@?0Hsai3+LjFHTHpswsci-EUIeXkym<rXb7!T~3p#>"f,=S6=@^ZXWG3g}S;,Grz)dwc="]08Ju0KE#&NoGV4Oqjacx"ln&0-pn1ffl~lj:6Ngsoud)hsQm7rO0_=+ox$:uARs7p^o[s!)T]6B"t&rnF2wcWcY!EQt./o#[Vwb$xqlJ;-ha#40xd4(aq)Bi>X_9C+s!)B$
Kmr
w(;8Jm|rVq;K+`&b"UJ?*m6svc3UAtXSE?ITK>6!}g!pF]s<ChMi>@c@Y$i^j38l"Fnu@yc*_lm>MPD:Tr?/+uEgt`V*lhHp~9.sB-Jxq%Dp4S~7(lcy5J6GX8+bR]sw%GJ2;jvc0L?p2v>>oq_lU#ovWdaS|Iu4-u3$8U]HBjq_K?90~N.+na=-txE=nfKT:7v,[n
+JNc`vF42#"zi8[T8%LbEnNpiPA5b6R4NnP4CCAw3*O"DDNC6?`^QdwNLh7W#/qVSAj6-aNpvQe[im:Pe9pLw[!Hp/-O(~FwC4t&TBS-Pi,tL07j7~U?ZB/H5N$}tlAf[GDB(HM)82qPXzrN8-DO?d3d6j,G@*;Y)KU3k/7/b,kbe=X2#rIJ@>%V?ss^WxV@,5.`l!oj/z^/dR<sd)YDM1N`Ps5w,)@e!wknqPfIY/gF-4PJmIG(=`xZ"^MiO?I[I#un0Ku1)9!|spj
pl>SC:b4L&R/CQ
EwTA%L%SA@!>$bo"[4=@-D1)mhIOjYTU}ZM_6P;iww<).u_5T^OQy
U#Q;sqzK!6H?TP~X8p!@QQ}@u,xO@cF>G.#
vj#@{RPciTjHZhIYfibw"%?2?e3ej`(-!FosJaG/,_kE.>7sQ!pQkdQuQ/IT+fTqRrC18Xe5%s4XTLF_3g@TMBfBC%QWJk!P!+T"gXp3}Sy+GBZ,g`|I`GY$l9A31%H
Z)MnmyFsvyuj?T%FU9xv{<~oK6w70yxL|=e><kxDltDBD36$!=gS
vP!8ki2rk.PhDt6y=3V]Dt`!+`%A`!(t/k&C=;yS0}%6#O3}Q:
;gS5ax<xf@b:(1ey|LM;I"Z:-^;4,oMv8jrYmD+Z1rF89bn?I"Y>b9avo
ionnvyA!7r@lE2LlCBg(gvuTj-7Q(?zQe(,AHZ561uzOQ%JeTMB^f",X(Th&@9S-&oN"k%wD.u!>Qln"5:.C.<4@
D~WjtwYMrZ<2
@&0%FJ`mk/qX)9AiRrRew;T,CG*pkAX3)*+;jS^C&,v)@+C&&8U$pA5Ez[`!G=H$f%v39]ScO%?L7hZdIRCDqKRJJ9k>6E;3bAn[*Su>+x|5I,%1cI6H>AO:rsPV
"9V)DKie5*+FYKo4uIM(G!E:2#VP,}i=ot.*d)R9Tf%EZLW/;sth<f!RRe<n35`A01oFZm<904.YH$XDr&#m-`s%#n%AJ!M0br<p3Xw0a}v+i7+v6{K>v`l*%ZB?%.r-_m<tlYy9/A-nQ2;<O5gHq|,9?Ta;8%(`iy8KDTjza"VK^:oe#YP2mg]K9$"bR-<ZJr"t;KOl2
<G[?@qtBuNP0YUSCBU;iX}VC6w;]/
JD<Nq;fv87)R`SEgaw#.t$F&)A5N5ZN$OTOR4H?mbUP3JLD
iKGZ=SRcv&]
s74,]F5e6F.7F.;R/t,Vyt,6>:bynwZ6xO<T9obJBrgB@?]W(m=Mj*?n,
f8D]QgH,DpZCb.$+"+l(GaCDW![ML4`uXC(M.?j!TRt}(9KFx9:H-j_nQD`&bzdDdhPDF[#!9q$|8^0gTauv.M&a4nPn#8nhvNPO[/R5FjWVJ}%)4nJOqGbj@jahO~r7XHB{DJ(b^Un,?EA2?wDglHG:AZmq$t0P.$nZ1Wf?3j/8r)=%]?=&8}7NpuUbS7"97{_wm3X.`:?pkY_c+Scc/5t|910[y<mnatcE(.V=Z68%1(hkM$Lr-@sk9
&>::WGkw^e1N
)j{ew:eC((vLkV
Cp%E5<&su3V?&=5<Bmof9[)c/dKt(xkR%l,;m~YV6Wqrk^
--?nglB<V?0HN]~Xd%Tg``Lx%8-an(4l,kYFmK9`#
7`E+#dt"9.C8JVu_4RB6!%C62W=3HT((7tj8)9MC+37f[J$47^bFuIX%$.6^t.$X]vSp<Ff0A5&V$8SSQPz1aD&L9.W(jVx/"Il/PQunxUeV7S17H7WD*@&4qua9|7c"mj^2YHNPC<Zs,bRi0Vg(:"Qwa$rtx(VLL5/`uxa>4A4saQ)G>H[YbC;U
IOh:(.:SvsAWd;$.W.1woe9=W9
RZDAuH9r.QWKbP0N(RbkwCzl?L:SQf]1!dbBD=fE5D?Zz4H%/NWu^#P
G?!?h`$l]Vd_#<~HoWEs`#jSq=0>f,YXfJ_y2>"c7+_O2KIrl91R/#/AGuk(Xe;#yym6x,61Z2d+?9R3@Co.W[E@]x}
|bM>`**xj,FK+Vv`=COoGwA;X^#YE2#a1Ca8<*hSn(b
z2
G-2qTkNk33j|DETvO]"jx5BYZ)(-hR1{4U1}.3"6_|,0C-a!t-W5kY@6hwDN.d9~r?u%VT?EqimRNtl0F(D-LThQdYU2$MG[H~=w9|SD*;uM.qK-^zlgsB=o^K-g@@4FVcr005/}W6=&4|1kyIe=8KAwlkUQIM:[3)iXgrDKm$(zM<D!<{2H9)EnVOyW4o
IiWSQlk1{BUC;(~4X3yc]s_x"bMB/6rF##C+nTEswhT/Qx)]QY9LQSkIvVy1a;h][u$BY`G"7qS$,FL0x@lVPEn2tu9T^Fx57aP:c)5Sd@En];TA`N~Dr*KY[j?uV+>m?eTe05"l)x%k:r%^XKkx(To(49k`z!>+pCT8Mynx/k&j)*1[Kz$NLhaP@/D92_h[)-~+kKo+swAF]rjZ
DJZfxtK<-cY8rY;C`/R2PFS2N@pS%m^RPh';case"it":return'"]^@iaLZ;#>)ni`"XI=-CT[]s12-#8@3ZwOZH9xk5,J,/9?tL4Q.}x&^%?`>pbUg=00iq:/jCnA6EE6uPJW.Smw?$X{$H7jW(P;LHXj3CU$O
>5wffo9B)]Yl$6j&CaEqVvvS2Vp,rRv<r+=C@x#%#j(tuzx}96vuhmGv(hdaz#EyL7L_(yRfBaZJw:$wB0t!"8P`mtslU,Mnh!GFb:i0ehA^HG;Qxi>JL_b(HRlU3H#`YMc1p1xG(i)o/^mcy:YBQ!^DCSrw.{kMc5(,vs`.P_N8Vp+lVy$gc|CdlyS,8#MFT?vpL:nviiB5BU0/.[@J4uo5jz#a!//i9JGr@=+KNz[fP-hc
t]a$]3(4N@F>*CKOU(>"81i#1iSuyy;j*A}%p8ENRucCGwP]se;*#Y[92C.QY"ZCn&co{Ild^`Msh+~d{lz<R`q1JA8T+M@yG64hkyfPB$m$R".yL:tHi4P#.X[%1<2`AuL1>:kM@ZF+H"W%[%ns8xLaM;rcaAVV!qA`;pkItX,Jw/u(JXGwh803:<gcV#x
B!Q0g5%Mm]&sKHxk>yoQcV4VN*:*%%C
68SHjS@ZY#B6(w5!tB..fPM6A/W>I%K/YQ!A$08(XV~1A4(;"n!XgbDudI{c9HL/M%m"au=^`s>JWR2?%[[qRmie(!8ESd9$n/k`Lx-9E<^U@aFq%+,m`F?_V4R)F
{@H;U]D5qOsd(6<R5G$SY/#3K9j-?PjJoA!j*,A).aLWh^f&9Hz/lApn_Gdq%`QTyn79g^=K@H*!Yhu)Ma;t!
q+b-EmnpWNT-ePS-{?Zq[K6W.g#Y:EZ=iJ$+Kg*QogS6
(F=-$ZY(CP]-H[*lHE<j(^S]0a2B-DA-Z};;;4P145-y!1eOIr=;z(Ps<dG-fQJJbMP,V4!rdd!Lnl<@nC-0[(x;k59!Xui!v|^Op!0mwvw~+?4vU9:O-.?u=+AiVb40TGwj!i#B0Mm!u*2Uwf[
TR-}S79^P$&d$sB.Cv1/0KS0S,Rm<2ZjKyP[NsD78N26HCxB8a6..nB=.gFMRRa=T|-i#i7$f[[>CRdUiySdT=
bg:[S6.Z|.#u!IGb84gqr%sm.!qC:+Kt$>e"%f_m)(T!K[6#y2DvRNqx9Z:%#pgM|hQgQj,X.E<%uO%)e:?D4/5[%wmT
%0!!/>^tj-A2
"uLjDuv^bm7<jDcDqRobqe2Je-4"?k)0C8|F8uh8^S(Q5)zpGD"NzxB
~Ippg
5lNJcsal
q:Xy:E
^9GAm5Iv+yjKX1Lc]>5-:`6*yD0Ln$=^EL{4Anx:($uSa/F=m)^5,Y;Yb=v:g;{VX+#NsxC_IL)C6JRmG>IY)M,#IG(Y
W.uw9,8eo*.n6=jl$&K#;kxwYTh}
AY<D*#MYhu&bQ6_g)RlSXT/Izru8r;X_c>6xJ6*nco&*RA01bW[%@K<f*<z,o2!=r2sDDO]08(SL;utml$j!#<0M-AD!6DK7uSCvp!Gs0h.t~!%&z*s<to,%*EQYET:-~H8a|Yos`i/-|[7C+Va/s1x&4[-FXb9,3&]WHHu-3b9Q/RS!C:co`UC&P%|<%,{q-?`S4ZecOY4DRA%pPifD(7DA:,wsersr|O"blH2E6fPP7Rp_v<YV_4[:a!iYrcxXU*W"Z@_!5Nxrf,~<0=1;>w*keWu<`A.!hB|"t>%S&v/vB%]pPSB_~5;:"e]!ws0G.IS[H/Zg^gholG-XBqL%7emnT*pBR+tngHxm*CG;3QA
.4;%/ji?]
z00=a*pc/;9I?E0#$gq]Sm~bCcM#obEm|e#M`y.9iQ%$8[xTKZM"9yv7tK326#j@H?&bv8Qd3XkHn(=gN2#:HV&t7S=x7`Ae<hYv?pKdNSK/Qb:GAg*R>_v4@!j83qpBD4u^2?Yj`I3+yms;4Au[7O3Q:Af7ub2pRBOCX?Wm#9Zjh./T[`vtfJG&kMwp?=R<K7EwL1zei/Ns4-d+;=UU,j%g@d*Z#/qKI*0>?=qe@X}.PX[NIP{rC$}<Y0kArSXpW;UGdW)+ghI*F1LNTb;G#+%LJu>;N&K&KB=Gqlk9or6@mStg>ylV;PW3jih+OxSvP:mWEZMSD^r8)Z
:VL=m?A5jq7<M
4/!="K7+4TCdt{=pNIr*@}38,u$hjCIX$.EP>JfSoGB-2=!GQ7R#29M8ko_F"(RUulxx
AQ^<DY[x*M4cMw5`GU8I}WX_gD1YnDHMg8>MtXn]Q]sCV+J4OZksRq:IAY"dbvUs4n@E8>Lp`6owy3b*{"`;6@
C:%x.#Izhb@jR~3#bem:_w8!5b0/)z2D#4F_b1Dytw)7gl,yTD_zJOIUF@hzi5IN+Va";uxST%RPy|:1,<eGsMD/B`YRr16LqeAHx@$Jt]J=/
qmZy$&nvY_c~w75}4UnqdbZjpin$4qbO.e=,yZ<)4`s].Eec12;-NmNn)>_BUh!ekCkN9L=6Cj;.YQftOdP4LHME2->7P/wU+A&Vy$hK`S7c61jICUG$pOKoHS7GYS@aVh
*^Zk&`0luu{V?T$hY5I1
sc8C)9g%<Fc<eu3d&6=g;"`;WBF_;as~.eCo)]eb3;I;@53wTxgSd=`eMZ$77tGndvy_f<:Hh.6Dx7&[BR-KkB#t&9a}w!@KK.[9f_c<7:O0D]yxMLgJ-rnpvWeJ,i>"yu:VtLsEMSXbj(e<>d&e>HJdk,%r5etD:`0/`7K$Nos)];+.1|R}!8LW`&p1w[&>wVnR,%OQM(:[.c-t
qt@Z1;L8cD:/B?h*tMw8D!a=,jaCKtfwC8HLW>dV8Dq[ltY';case"lv":return'"s`:X5LZ;0w=&U)#!-OF,tyq$QQi-]KG"ifkg]v<jdm*$0q-tkw*~)D&!P!/Z[D@HDJ[+QXveabc]Y#"{RSN,g^:.i#<xRI(9bmeui@x5]S6GGoS0rM#olf;Skuj@
;h;b?rml<h*q6H-U)v@$}&3b(kf:Uw8vIkIrjH$jv-6_4@68.0!%W;|FvNZn..%l|;InoT7HHijMNBIo(`yM|R6t*p.9-VAp+(#ctny7#x#tev{ts[I0(*f7>9EjcB/4ROf2-xRg,B/ejRy
Ms|tf/)BdC.?T7PvkrM1T`?,KB!#R2rL.z&?>EN3L>d3eK}7Yrz=NFvm
UGh7GbB-sEe+*vK"!2"#:_.pE2xB
):<UT8Mi*l*p,
JdkQ?J/2e^y=xWxuw:P!__Z6Gcp^{-iGhELs4>=D
HU/I]B
m7
tplFZx>ah:e4pi=TbHx3V7?N*S?z?jn6pN6N0!>!Gw&mbtZ4WHpltIp0TiTukq9bH`OA9qZ:a.<nCQy_sl9F0TtgqWkEIv>SR;x(C/M;BXoScBEDjNO5FoOurUs`i*yh]G;>^m4`2FQKD-2Pnk0<]hKN%].e!xW_kWm%kr^l]LHqt5G,bGR-,!-x`]%l]=Rad7Co-Op$2rhmQZ4)kP%>W6MFV9Nc,TivA.kf.b#0^Y6T+-PYYjKwWOc*:}CU%ya.tK<zq`:`,&1:f.D9NschZtO9ti7AVZjQyWI/MG&.H]$cs~cKlqywouFqUu$HK8N/Q/G,<]dL45RvI6G5rhr`i(:p[@m{XNPyMZjl6
/xv>F4Q.p&GnFkQK@,$nX5KNJP+D:?>VLom=cZoqXD((7+l5t_
vOxDaF}K4;XbI9sUDn#C1JtCUBYmBV[5K@ij~+y^|7I_{e0Gn:8?X;Kov0F/McZ1Do2TVv~X+^kg`=3V*[)QpyNbGn)TiGiV!cmPE=0tZwe:1a@rWH*?;rG,kCKm#qPMwRkR&Yoyq-Hx
I^h58<EQVfZIo1AhH[cNd9V.gyn@"|CWfq:!+>;@vN-MM,2ETC_Iy-Pb
=<48cKz82/g8tc$;622Rww,A.q;Y&Qv-jBb`RWysC
y[}#e@j+QQ6Wjt["-5BJ/7BVm<(j?jz4J7O;0cE&n"Wu!P7)OnDU&S[B--gTeDRvs1-WvW1vD>``>+}%N-wZ:]?aZV!s%qB_3#}L}Jd2DGaZ}jg)LxmIJ.@CUC~K]ygEh&}l!O9j&U5qM%ru}b{-
R5v@7r?P&c1`GRn(_i@EP>-ENZn6Ru2>%l&ypCB0V^n)W`"ZIfX[TS_dZ>i>;8Y)]C#a,=cDYL53y8@IY9;+CEONR;4}
5Z*J]HUi40J:CNHPY0pDan;29OYY-NIYKrR6:!Y>0v~4(8/T6^GQcFp_Vl{*rlVJYCBZAeCnBq/1fhr;%0olUIR@n?7"{HFSEKFfv;^;E]XEX%2NML<[o5=!juVc5:xl3%,mTlud,Nhj+(I]Lqeg
`7t/CQvIYZO}]V8HLNRyAAJb)]iOL|-mjB]z*Ho8@c9%?ga23wqPI0?YXr,99=pnW(c]im[Zy!gV1I2:(4!QZ<ceTW
V.`c&:*P|8)L=CZ1xk]OCXk_Vuq31.Ct#_Otb>bpfHo)-rSHTv[nw=1S/-=
c7@
2iT(H"Ve3p[tbl@+|Z
K`9deZhI$3-
:@b@dx7Q*JT4I/@81h8Ye&yV,;4b5:^*4-Q@C[4)+)wk[aZQ=Ac=EO!f)a=4^$(vHr/
[[=Uqh3qT?9==[sqFnMY2R9k3}Xu(#l~NM[pqrftVTQ1#p@,u5rr"<N$kAlq-8doNG9ua?y}@AoIFAJ6N:cMY7P7[iK>J@@=R07*;I)g`.$r`#`3C~F"?znM%LI^j0MgWJB"Sfhv]"n52&s:Y-wT?9QcYRth5N7L5$[h4?n6Q(4J%PP0#JNaC6_XXyviqlf,D
6SaYy=)0;"(5:Nli0W`-q/e|R_Hu4rFK0oV.v#`zud??wS6H#,#"RQ)sk0(2`;"J9.T`>,_apvT@-m1t)XPjsh!f9)>z0_+n,/WC)19F-[hy,+oGH2o7<W>[g-/s+]3f5yJrlAN:3Ty:9w32Jf[.3_jBO3y6s%^Rigki=2beTE$%$Pi~/:1x";@m[^JSmlD%%@ehhqpyT^*|D1ZfLoFLJss#s98-^=.76unJ<#A&v8k[&=D$B?ehcjr~
S:A9y]%2jHK+G=4RC0iR6SKrW))TpKX9q#
SCHMK{%..qshCvd4bVK0dC2KfguG2M$ZZFT_b
3w</QSoCQz55AtS<kaqp+c9fK@Z$wA^!`tT/.*s=4SO(*HDc-jGD5a289)y[Enb37cRBPNEZ0sr8w,(=?0Gs<+
GQzaO03W[E;q-VWtT9YLz
BC{QSs3qWheF,.oG1W>L7KN85&(9U?pdJ;SUs::^$hN02S+jg4^e+Tr(+h69$Wp_kKd%}J$TEb=>N#+j4&klZ,[o^,YOGGC1J4GY]fsI/;m0S%JEwhZe}"WP/iT%~Wff
yEM>lp>sNkVa#~74=wxq9yLX1sQ=Ei)-_TR;3{<b[)xmp,g?kMNrQER^/
Z#QtL^DkDOGDJm7aXFPnM+Tb/YaVH/I0u4Y+KE
35B
C0gX<S#^)t}I^pQl.Mt9]614;`D)B5kdM8l(w0L8o#y02gZ^%Pt=~3*XS>pc~IKaa`VICNGB->Omr$6"(..R!xmurh9*EQ[1
v;FqvB^$InC2m8H_pVx
<,I+u//AgM"G7%PR#kM.ttV`.bFewWt%ML.L[$1`ie<Iku`2c(fRg5?<;7<63#B(ax"f(RU
(XOB/F<8Mx+EJEm1]PwIQ5"?YGl6<t[[G*Nb2H!X0TC$6_H0.n"]p1A1pA)]jL0B>PgQMWUuJT?1>MOB":FYU>ErL>DDF_c}wo/h5yBoK{aKwhL<!3:@jS0{wB';case"lt":return'&s`@qbOZ+#A`oid"*iW?PKMY?_9rj$+S?xI?+5+Zo[oU]ZLM@w
)fNScUoI7]cA<,S|g7ySya>N?lL5^^i_CV
l1oq>iPqNh&=,]usitkP=L+;x#.3yd~?zX^WloUG4FEBl^%%]j^
76#
Idv+r*Tb^mrt`JE<niky:`WVTQ`MT&1B:IhD:-vUn8k7zd1yuo[uxU-rp-CBZVa@s+x7pl7G}x>46OQ/1k|aS;t<geBb[9PJZ%T8Ju$mFytNVU+q:u6fmGf$I`ymf]e
F<oO$n|lhXP)pg]nP>M
iKwiqJ-ud)J=nuLM{w>qU_j!VBcUxB<<+f3J%8nhac[)x^R8z`5)Vna6_L$"x$`1leeEk#
5NxGaLkRq~?LVWNz<c1.69<yNlJPZIfD$=o}QP_PZV5)nPmN&}gBn0
g]g9
#HG!&$vRY*=hEkFS6L#-+BlkyQ/9L^g}Z+$IF*+6pwnI*`1MND40#35c#1)2oH]Pi1`I?z,Q"*0G0"KMshJD]rpM4dT0#+5B8q+8fT0m6?Fm+PyIICRbk+>o>R;Tp0[Y6WOtYbbomgyoli)<j$fP9#"`PEt}VWw-KC*T-Y0DGuM_kQZSyqPMt[kt/|")NBnG2aKwVJW?Ci!YQUCKP[
&lq%##<!nLYkJ
lFlhTl,V!WZ9:@OG<WnPha3th[ZA%
zpg+XQW>P,p;bhyL*@capuFYjM>P@i,8J
=?*
,qA[yjF>rlyhMW{r#:?Q@*VTkc?G!L]R]lKW*%j/mIw;P_$P$j96GwaK+=cq^"D^U;8HL>ePtuK-)%eiEFbf17~`[#-Jj*Ss#C}2N.Zw[iRn%Uz)bF?3s+XpPGnxV4E<(,$)#UM_8oR,`VS4Q9g[7j$5_pJ!
":cv5F?E1zg3AF2PLY8/
b"#BGDv@HE|S?Vn+XeY2OB*ON7!^&OYojDtEjatQ20ba
?E[yZ1D@YzN{M6*TGJt`t6!GU-q~R[tfE}Sx7A_Q/+.,dLKM.&v&od<Q)+rxlC/LwiFc4=4h
.Byl/_lOe%UX@c#qM!wG5Qip*79_0E:wWw>&e<W**:m0m1V-v.rZyOIv_9dYAiw
N^}Y."&5<]<>H/:/j`Lk}]Isu-f
}[YwI)iY~]?UEO;Ft/~Dt$Zl3){dTv`
0g
Bk5Ouspr5:Q$_HJ{^zu+Uw!9uA^IdSvDO,O(/_x((&ai;6o8=A5s#8=t)T(e<^[!,66k3Q!P+G#E"=n/#o[B4G!UAayOX>T#oRdi2.h27Z778Ks5cA0.i`K_<`,wy(iqvT`Jnsp>Q8VB.>VgVo-
d<P8LDHOVHZ~?@.^OQb9D-wlatErna:5dOMW_Y<>K9$ilN]uA&lRgi]Q
p
YMqu<Y/x(f&//U4>lcKZR7v+h.P&)2?QQaoV5An>z5nP_3NP~?n.OC>b;B322)#MU4L]a#.2B18$*7A>v@J8"[yo;,ng!j^KK]g]cZX^@
$V^DLWZSyWXd4cEc
J):BrwICLl4Z/@pZ`Rxj:NAdhW(nZTw|`3iGhS<:#2uHx8h)eT78m+Z/m#ANVo4*gu>n>#PT3{n:.]^yGJg#8D6EN(D|dr"UL6w;JU=&P:>`C!P%%cjCUCEc]0C12r2hxZ0XS%74^}uLZ`.Uu>P^9axV[JY094aJJ]pl%jGwt!Za&:o7hn(VWDA^heE]cguEc<4Aeh[8]Bo,(JXXQ`NVeA!96qFYrHh3S/E}RT<_0e4!t$.)qNX_I<AFbB`hFm3pIsH=8b+OtiXE1kEWhy&0iKDxtWjNp%T*Mp?JN~=Wdy9<vHLzM4LUr-nD5Aw3;O,v3{KxN(Ui]8
+"IpDj:G(0=()arn>X"3fyw0AK//[f^d|gagp5>khuJ".GjZV;h#{h1@Ecjcs]r&zqS5](f;w]-^6g[x;ZUT+.P1IlP/;J*3lZ*gnEbBKC._m!/Qi0KY[IN%}.DtV,nj"VhWj=E!T(f]BOx5,/L.aj3hNx-<v-kWHq[<SJyJuyhf0Xdd3*B.y%|JcNF]T>?ue[,f0wKKUBK".uRL$UlFWSq]o,VCE8(uHE<yP^.l`RqDwN76(?gji%Z`i5)l!wU<(W!v->?.2HeDit,3uIR>,S3P*??N:FX<cDK*>Z[_mvge>E#FJ=_ZVG3_)&Rp@5jh??@I[xfBbP;_PJsP@laU::t3F:}..KTUa6:ep"(sO&Vgv(aZ;oO5aS53Pv*_Eq5&uBv"mQx6>54!#5NTmjyc+5Tu!
PbaZAF>8W-CRFhBy_f[).4PD~AH(b7/-Wy=d:o@iwezB)a4ew/|
lIE&fuzIA=5"YJR*V^R#6f,sxRVwT,k0YJTqxy&uGu@6b0xmXG.JUG7Mzw
.ne9
ISElm57sdsNeOOBQg6V/WSx/j(A_57?XS;w,tBX^RY%Eou3OTc5.&"imhlV<^"IW!D"(
.3`4F5.b<z5uihS~QAU/WG&#y|[I,bu
*wf$_Gqu240`bob4,"wuRZCX6B.4r1N6';case"ro":return'&]^;BbpD9(nk|u#)MNFT8-5D=dATzN6+s-sQ:bH^m7(QJ3X#+W)!4V,ukO=%7.>5-$VOU[-,rcc
eC#EY=Y1aM%6=K%^+v
7,L?DcaNa#M,Qmy>.Gk3ney}[mC-"4Rh>}cFL~iv9A8-vvx_"
lwi0C#u|pK%9a>wgH/^N1}!!M`Zb5W3"z"yfC+Z:evmHNe
lv_U6_~vmDvtw%)1X2-2+:#u77
TqZ&VA$D&"C6#$@k8frwPlHPtXQQVD[S1C?[XLQ=c(bq5"z)s.R_--;K,oc+Zr:pr:+[h!WMfVF?D<lpttVn@,gOmVJ6!3@wyj
2*a%F?hg$O8H1hl?K:nyEI+`1DkiyriD#et@_yGWrGiN8sB$}@iyO^-.AI8Gel7<Gy<P!!vU{N$Q*Kptd)T%|?H`Q^wVu=+iNtsK8YN`$35+2&Je};+O8pEsIe`#crQCJ(v+7-{wD>JoRxd<:-Wlg9KK9*iUp<:U$E&6S;ek<m:k1(XfQ!
FF?<bg*}OM0g!P,-a;xtUKM[u9o9@U/kr{vP"0!4%jm,nTy_oZbcwyoj(ZBV1s;meSP5%$VGf]%_c>DDS)IXI](4AgMp&|Y0M@ECbwQI**:?JqM+fIWP%349>zE]Q1"*oNLB[&Gjr^<L
]Fk8o7dce*tR/1po]fefsMS;y)kxg.Lqg_5n}@[n=#wJ*2e$^N-syk0)ahY$v>+02`"W;#j[wq1<mOc>6JvG/SVefmm-SeQ:#+koqX,+3@n7p#i+yfF*LUnE
f}VqxDJY.eaSy}73
^uowBqJ#,90dR:NIf(o*{@,XkMLo.E@9fWfF2)-!}khvW$_"YaLWK<0wEO*v|:3FLT>Ov#Ez&7x-aTL96D04WLjrVAzxs".mDB9!pGstD*H1&,A[H54`E"+D+bKIc/WLIt[i0.CCy?qud
lHxXtKl9>O3A1mghCwhJxqO4!4k#x_(w|41KkdX.qa~XC->.FPSHBZ*)MNm=z88,*8pZdvR_gA6;ug&cexQBSX
i
P-jK<&#}o-*x;#/+g#f^>7[
J;`~7j?eI-cFUdUZS81iq$`x*}7mnI+8W%spL4&%v$7=Ra.zQq>/,it0yTE}E~VR&PT(BG(ZI/48o8!O%rCWRAYJ7>l2<~41Rn7f5Y#-:K.(lvY<<CeFDLRm!YO:pM
(5]F3h-4cM8TwrHm$(ZmQ>vaYiUwMZSy8?Iy|&ygOdL<g*{"xhU/!v$VB0x>D[1Y1drh>xn!-P-ZgNEecAlY1a
;F!r;t=zOq7@$4FWEUIa%Tn<"~4YtO)Y6=i*^Q$}[>xQ
1YT^Ey(mM
osapKx(+V5^,s$7+f5{u^EJ4r3U5EMxS`f},|9Bd-`Gx}nk/5"XQRV/l1b#23(coP,B:EfcdvuJ<0MEL
2o0{sFWTk(*&e[4TV=OPrcVxt|S~8LW?(Tm4.Km]l>0Jpl".#-^QR<:gvxAQ3__a@E9s0r`FaOI:&~9M:]ZZtN@Qh9Xy1Jk(+)Wf!6lVp_,M_h&{*F&9kcQ4U>w"V3C9_>@9V8wHWgcF"V%90C$oMg)4]/hkK=9+[m]j;g%bZRe14
ct`?@20o#00M9QA]=,Py.8HLwKo7hAejd2Sy
}iWT=DZ?ci(4,(PT0%wxX3&y@Osoq7E2QSIDV9qb&k;7K^$l%N{$,?84ZEU4&+$8Oyt/Wo?:sKq^w<Q&qAQxMm"W,D$9NQfqQ"K0x>n1qY/Nm#n
N>A<eHw05S$O6kz
?4)qF3Mc+wxbr`(@Y#ywz>D(QG)`|Fll0_B0*qBGA0u1heotHVwvQW)r>Us7}/[GB(9N"P[pgK*Y7Phfos/a@`"*j;u.pWlV,p4#Riwx(wX.WouSoQx+=O[*@7z_z(1[vs^!YOCgC
3jR-6C=G;8`rOW_%dE!S7gXsu</ia(@L}DwPIIz.8T
f=mM_Q"@Rl#Leg2~MbMzlNS`h@SdW-ZS69_A^6ZQHL@"jR//=H..;.1kkafK>Lf[Wi,PR?@Xmtd&
TCH*]8s.:3`wl=i,0P$`$.bkkDp9g!2B2(Xam4@/=QU#&UK"SZ~)_oPdWcz8w@uokX@*l:.dy=Lp>nH8f9sEh3j,%8.[8OuD_W.v,d^t1r1$<!sMhV?;u:*VEgq?4l2g9%KiS1,e5ctEx?HZ
UIBt5D)CU2r?#vR@Yef,2z2O2R$
d11EF{cfOs?4Qh&x84?
S21Vd`6A6]e7utExD}1,ISJd`G)sV;%l&
DQ#UcLU&sM;XR.yvEl1UCPparouII%3BFP]12VP
%H@>r3<R$@0.ZJ9-*bdM4BH&vpE.FHMSE(P;m#0RD&gnMY
`rAC1[FHWEFML.Fm3jt?%Hr@I?*Ad7+Vh(u$B(xNH;jRxRl;:CseHs+c9vi!o)?FvQ=Q&UeFj#{AT-*D35Bbe8jm
Da=_V,<m+/`E>Fq8hPDpdh>)Qnl2?bIKBIkc81mH`A3!,{)/D)gG_}Y*AjD(p~l4=h>IppB_@]j0gs#JQn5q0OKajUSeW4<TBH<`G8#5ygs`=K"+bY[zk_iz;
<ZgKCZGg7h1syQ+Btt?F=3soeJ
fKb6jC_eSsVeV%pZ6qntQlODAz!y"2:@KG1Pnh&7z)3`Lak`253Tmr&g[0o0D"z!bNUXm@6-wN%CcrMs-lh<O2H$2jfMKD7OiC
JmSOjt39n/l8OT
{nwFFu~p)uFnS8nS*"^D+2H;
45-a>)]NAe^}?$;~JIhV-LGBl1kT9f+euQ>J)A
iW/r|PS@~[ia0KDb`*L11/$RPAz_rz"F/(CRTOsc[66UQhxqNs#&SZyY[AD1KZA4JC6?t@qk:V?a4]:s">>-8
UL/b$,{:ZNCP%xa4aW+XCbS9|W8pbG_^/e}T&UMvz7
aph"q/^_@":|YZg1m+F#>!FD]"kAG>E%V_dNpk3.uREM0QMO]j
Z%KU~6@>,6qU<cB+cI*D6Ul%}PXB$H3_i:Zwy4jpAqJ?iJOvlske
7crjVO[3o-fByiz#Sy7[-IK).-5NXSV!,{*0e!o>/j.~okjC&OO_GLlgr[6x[[`U[HT<QMwo>oFVZU]]-oJ]s}_EwjZoVpg0/<usFA%Y*FG^6.XT+x0rdHGYtD1LeyG6&ZJZyhPRk`&}em>Ap1Vrq`b90ryK(aHW#$GbT&8{>~EZ)uSF";7^/fK,t<0tr:oIWg<,)N*)6dZ0lt[.]q%KlXD=r3<Ac~XL;saSvV"]E%g;a(.*Vb^KA?
e$BZ6+9OvHr<-W{pi-O@8yP`Zm)2js2:7/G+s4jW["H&a
Z:c<<Q|L{#-U>^RCv)=t8a(_`kasd%!7ThZKe8C];imieI%>><:K;BG=O<`>awnn}ay;ibOCAOaI(_L+eei,hslHUSOX*juO[h{!PN6';case"hu":return'*]^@AbTA`+XkgerD-*8,X,f(S#^`B)e6|-/M5JPr(mN
4PyR7`%mHRy5xU"-5firBNvY+MIAp,}.%3BuR.J`gAcVbNBEb>`Qs_$,W
:gewcq.r@qI#78kThu7cSy4-
G<t.a3A|p^yb[U+;pgB^tC>@WGySDWl7H/"zWurodn@rb"?lhWFzIvNc/EW&MRAB?_`k$$EY2rbjGM2Ob^1;F0yb+|D4v)0f2Hhkz%ryj52Q3`VM;pO9c6R~Rq`}H,uFj.1la<[gK03Dq0RAi2u"Gl#&sD^kl3S-0=s
_Af0,t@w
)SZj=i_$k0#;*@A%Atu?_r&rWnPw%KdLQN%`Uutv$DN]}Qq,t2EiPX"H(INGCz#*MH/I`mZ[c^`b0.rQ>UB_6nV87mb0tvCEcs4ObufpJmUfgbObjk9/C??@`wxQ^+`TqEiPS^3Q
Bgg<ge4Wllb]B#wQwi_(FQcUVB*=pbxx1"m*y]&@W
dPlkf9bKk7k>O[!Uv!.S^-Gx6#,w2iyE?m
]BHjEc=:=l0a5b]lQ]7f1p5B}Nm_
I">HwX1bf
0y]n!!d+4*@2BWS.+2unSr<xO2NURfxJje(cU)VC@",IbSm4:%pg(I7qQa[c3aHa7qu{lRPWyAS^.<)f_|^*6I,<g
Mn-1
kc>ear@5ltG*=<hU;.j)xPa
fPFAwH/[ZpLADMmU^9;d~yJUE*|4^0eyK*A
G@AJVO
@(Pjm]1_AyD64lb3KcKs#^HR"k-}ca"$0qKeGP6jKi!YSHTTb"0v,D8hB~.v1PVo/ISSt;$D777#UKD_q]mu44!,@9br(;:&:E0w%s8&`)UpZmYTHYDctDfe@3&3I"_(r9y2vB0g4U"k27D>5Q24dnF|Zgp.IM#ZO0eUaF+hMJF>s;LW<^R)FqpoD/=W93vabqE(%y!^6A08@Z8<"GTeL[xNQerRVSD#Kv@@(Eq5(9xJ)|qk3[2}3L9Y^V-YX^QK5!,3]Z8!kXs;2_!)-jV-bGc

a4gvmkJL.T,,mv3[O:/&,SH^ciVEhb=RiOMcwENNojp[/Cc&d/6jI7u,")S!@OH#r2M^Yv!]^O
1ML9oqy<0Z?nuG/PG{7D.VWq#U7akr0_8n)=
;5yv2us[w^D%VAJ.2->:FS)@-<AWt/eWUcTO?ORX8+"wlbG"w"c_%R>"K
FZ8iP+Q-HkEG,^E3UInEwgp.[^8Ut!q#.=
5Ed;xOSs<98t8G!46hZBJyoV&~4$THj,vzpdj+TyObHa<q:mRR*1v?1]NigCSKb[npi<M.?`oIuPks(~
HQ86^C$ENgi#@PK0Hl`3SlJ7P40L?yWKT1XOJkR=fQa%o,U]o4~WcWHBb$l^
3^2Kb9o4K
N.q0*G#&?OUjgis|Z)O:,u;qhslwAvJBN%[OZ-nC<zwC%dd>f04Uc|BFx};$xiVUj[eH^_3^7b(6u9/DcisaI=K*(pffk*nGxQ3"[#EFPd8qdip7;u+qR
EegldhjjNHf1O6grL`[n(64K;DH[ty*4Ck5a9C9lQVF
vzCXO5V}[ikacm_78$o/CBc/E;P#F_;44)D:;qB$/kxZ^&cXK3/!&E.)T?A}Cj4zk8$DP5mU=L-a#/J`8?
h@$"c&Ad(o,P;]2DU7g6O"ZgcWJ;z@B)WR@"e0=#&fdT|+!Gj+m-
W-j&y+vZop;K$d2|G}QlB6S04D2lL8mT($!aNCd5OpK`>QuSN&Kuxr3@<#wg[.2pa".-Y<
%OIeQuwwuYtT)V`j"g~Tj#>X6Lp>LMkPr^=`xqS2h8^W>Kau;O`;T2[(V9rBAH~V;2Ax}!+v.bR8!5tIu8IXQD6Ltk&Y<)KnLoU9tA=IGBTCsM<q*x^B
`t6(7+BO$Br2-3#>5}==ZLxr[b/v"@TQJQfudyrxjAvL:e5S(K8.@#;QFv44gBRMOGK5NCq|7CaS
:A|yf&(cfp?R>h&"2u!q;D<OqIXy!SZZk]L1s7"UOyy)SGr2bj]rsDPa,Qe9SF3H{mgJ./|OPn.1e(&MO`1`P0%+j%Xa4l[-JNu!Z&XqX<w(dvRy.3bkfUm5u(@s95EF/E!?a/|48yt6nU"Rg=UA*,@&ZqrE*NbE,YH?1*J<:hsy#fMSG9+;[m|jC(G%"^,_dQI=,j=+Ur)$aEzW@1
yyKlKhK$G[rY8_d)Kue*Okf7(+jV=9Q&C]yV/<hIFYw:6),k>udv5A*`q}!ujhP)oWSzjP&G)ylF<1:?0#/83VIRfxudf@D1J*dSA-&<`xCao{N;dy"5yd-3sR;!<8Q[x?E,,GDWj2HVPT;%6qOb[r.GN-6qrDu-:dr&&xKOPmE|H92Z!ofeE(N|HdnIT`.JJ~$^EeaBJ#"c[(F7lvxBj]3G%f`Js>
7(fFQS^f.v"c^Toc1={P@O3BCA`8pd&8%)MqEH#sNhyx3TgNZUVAh<:6JMy-_)c2l&dQj`<bbeSaMJJxKZ+eV7@6H_MgI0g5}WlSZ5;hw4e_-au^TBCeY?`wdB[UQj/dmdVvW%1IlVB]43$S+eNOH<E#72-bS9IN^5lCoVyUI?-KH&dO~<Se5C!;RN}<s#"bDW1`vS89uZMg-[!UdQAAoRxtysIGStTT-);v(4)<{ppm:S;EVwv<p+q?+C@Moe!$0t!iBv]sUNHX*<bA$[]0l"u(!jrWA;}LPkmekQFj^YsbH8Mn5n20Uvfj6>z
_v|rl&gYp/#(c708f6FGff"],=TpPP#`GsU0n-wHH.M
p:^-cW1IG+VFi^N_r"5wfj2RBv9_Vu?wWd:>Y0f<6u#+&gjL3;I6Q1iKbPQabdT#[Jk4]%9PIP7iy-CEn-W:|fK3gU}_p>}Y{4z%-<u(@K"
96?ZP.?(NVMp>rsL!B|R
"jgWut?>7rvO5[V<-1YOxp76.*Ep>JT/[b);@IBxmr<GMnMl`Y,0p!;irJVfD:R;gINd5aI$APXAq$z!
1&nFK22V.vfDmr%hv&.t$RKEeNbb<Zj6*
@1pMf9jv&4aK:)$T`rFm&WMKMRD2JqLWlNP$ky@L|J~ThCT5$n%.7]NJsT;hM&T^Rb,J^)6dftJ>;q~$QAs1}3M9u.+s+Kr+Wj28r8pq&7(IS<8WB.{YP62PBpoZnv~BH9iO2R-Y4sF===7]Z%^fc4ZyD
C$nM5OdoLKaK)"VkcD<?kyJ!7Z,Br!m<NVO2x>mgP=B-+1;<Y"n9NDXcp?)4OD,P,htbE1@7i^G(;dtOgQGC_9lA?2)Jp"psTC_?)XJhC%^GS612A2vWcEEG$uQWftGI6F^#_*rJqj~2N4oCgAPF>J(2i6j^Z5)o1tW<%lkKB,=?(j-i|!B9DsJ1verVIjKB)C^Q}E$su5kh%NXaiN8>?BnO=N=lOpFw)-oWv_78U^N!Q';case"nl":return'*Zu@ibO
q$"S,id"K_G-UT]`
Y]D/$z0^Z[dUizdz5O9X8u.+Yf?-_v%*WD6jn"y9V~NJ9)I`$Wm}fa7g5QWjPg_QLu::t$>#.%vqhfaQyMv$63Zuy|m_68b.m0Qhv;CnhdF9Z{avu8;6^C8^y4kH,0hBM4h#n=,{$)4R+Fx>]v=Epew@]XV[w*iw.GQWro3d7PuEeTG0d&Ug,by3u:rYe5l8a(_TO`<s+$8"x0`P)q[Z`aQ6"jPW2dc+=WSPS$iw..AI#/^ix_L!qGFmM[*t9Dt!0{h%y5qC?90}J~
$d>XiNd/CXPL[`o$J2P/<_
U*ReU05Mx~b%PU4{;eN5F;cf+=y"<8[/f*YHcSB_lniP(lGE_1!v;mPM2iFO!<W8>N9oK[gycooJ`AQ*`~gAF)jD6N2Y&5[HILH6itF?,;I[/Tc.Z)Av;x?l1
e]*#gEBB/3u2c5aH7e(8cQ-iHkkmiu](l#oia3w-mUa2Qk"hu]k1;:=m!wmt&J[TGs*72c?i&(4*d[mP:Lt9f{*S^/=M^M@GPyFynJRjDQ"-?972j]f]a~s(t?(Y:nh9SQXu?&wl&z3-(8jl[>Nb;&r}Lo/qj~))7T`U3QB$m,qYs)STG)+DOGpImv<u6<(o^Z2L_cS[`v_*mO:bC@CwGKZP4NG,o!AH*48,3ESOv~vtFqW-;D8jsefyNF0%(n74g/U;xm!r^7nw7I!T,%!S7dUbZK6Sxkm(trf:+6Seb@UH?<T"*>OD49xMHLfx-|$r^,CghCDrT4v6iYL3[K*weZcprUENC}k;3[K"7j+#p#[m[_7xR3u*oM-Qno"IX[K&QJe[(a[I#8J(&J,d(V"l8pfkRB?/XE5z:s*cdJ2);i#agOp0oRS.aX[Jj^0r6vM_+QOVt0_+VbL(j;-I/caJr+9n91U1=/md
J^8D$
=P}VSWR2k+Co1<U5g]}l{fe2]7"Pb4F9JnGmb_eCa@vaC-<%(e-H2vk(h-VB7AwR:KFtQ9)U
]{@IPtw"o8i=EsRUxbtzaex1o]!1gg7L8tXR#2#N_CX!&"cF/$E:t=doo?)!ed4K0,j2aDg~X1E8#z,Dq3%-hirBr=tR&w379~*4K>yB>=3%;A)]fyAFm_w?0o@4eFPDo}uv@9#+jQfd:jlK&8c.Y,@(mF.j%nA|GIl-tuo:W<2{s7Is)ntcYy/5,ge;8=(eO,[4po?uadRkf*W#JxFY_Unc$@e^MM&mY.;/MBCOYPaOM;HXfV9*%?I3!rDPom1S^1O=oH=E<5r/RYj=N+yQ;sTs1x$xY9/w?@K?Ec_2cx;{n[CrwEeX(M]JP>^F8|(duz.c]eD^^)=,/e-6Co%Uaw[LY2A:u"gn5XHE>zb}l`^PMsoj5%kbsI$`@Nf$aXET#!>sj`3=PA=|I
!>I/eKp&Pp5yE/sO"v8l@k2_LE;?E|V_`fVy1)"&Ywd0K=)L),5JEdOuQpK`e+AU<"#w^JX:V&_Std+5m6NC#qM)%Ln#>G];u(09oMI1FsDRjst.!5%ki-HzJG%F03M-HPAEj2UU"7@>iNs#Q25Q3>1^nMY)l/J*P~4/<V&^k^5wmz$!hR0^%B
1i4TMCibprJyMbC$/+aKO3T6:V`4b[l3F*zf=LuIO1Z=kisGUA7:>x]GFbS<v4I,-BETNHHQ[J#>~49`aFj-Ht/r]Q{)DMv_.#Iab0k_.x^pB6=QF$NL8N
wNKL)B-L:XThD[j5:E/b".Oz]nGs+M=X
Oh@3(T1jy,*>;"g.RVSG}P8hR^n1aMCgi;%&M:dYL$Y_*5sAgm*E^tTi#`BlgU_Mk3&9`*W4K;{4XnGeW@vJl6}l
7ic[=vVNTHbzRJh.7Q_YG%<q_v8Tt~o&5.tWc*O+w8BG;]V(v|Q4KKA
p@g-t6)Z;%!WTC@(
"LLW;1TfWAOuEj@J6kU3C&8&1+4FRH<q!Vi?US";,
T$LPOvdR<Sjsp`%J53u"byaOQ"Pm(rV984%c&L5#vuo(07KF1T-Z+qO_AC3F2+!
|ZubssR.pC.ymFd1G&d.tUu1*;oXbf$0x:
!:g=%-2r_o2|5J/h->%e@3X%Gb8{O]X.Hh!/U$Fd6jUt>K%N5wN+e}F]7OTOW9c;sK3_;#J<;8w:SMRm#K.BtoA-(zkW3sj|)s$IWve.!Vu#N=.f=x9y!S:ArscE;ro{B
C^*t_dp{yc*GPxs_#k`Gp@c>
^?v:rSfjbD4`"qI?oKz2k`iD}dcm0s#MloaNeIrB^&s5?;]5liZ%#V/-rW4
*<SQkKL!*9t)(N3#6A](Q^orh5>LQ$whLA#eBYj.W<S2m],/<w*jV74vNTuqUKztmD]:g:NfW223<sv%q;He,ojEo@ac7w$_
JN[yisp}NNfv>D)7S(r
jFqGlr03wz(d+%@Nnw1t.a1c._KEA42tc{#hi<!y!uqJ0]i"bh6+U<>ukJ]ABmUBn9<+!EP9&T`9u?e]NoaLOd_w9Y7j-}cMEd
/P;^v<m$w:dA4NMF4gKHXg60{Z{hX7O]{%gdjy*hXu+HX%$WW?sq,=l"
T=]/yax&e2rCH?tlP(q-=2LgAW=&2{U>6,3`8%=i"S
I%+@cF@qu.pIqEi1MNta"[xujF`tibgHE>wDQrxEp:Q`>86$"J@t{Dh2Fn!4^T@Uw:%UHsUt>f0;#1gi)TaDQF5q:#K!;N3PfF53-ZbaN,[
|ypd
6T%Lb$r5!r6n&ggZ%#ZKNV9?5~4@(z4)PB6RZZ!lTF:*b#]l[[,~aOvUDz#qRbsz=Kq2JR0?4`imbldOCur>W72lrdelg8#2"7[<1tv@uAB?lsD=]{k#ypwA';case"no":return')Zu;BcrZ+#@!@ii#XAKTxv(nW86#voUSuPvL][1=}W+SmGyU;ANr-yG7?n0tWDgv0cKq=sc.i0@9W#InQp{lzu7Vzu9Uhm$GoO;`vh^rjFkaWoCCg+sL<duT*7T`EY^r/XoR-Q>va]3m[pQ^I+(4_E4+P/(CE=q+Q.O&so-D:Mb?^YYX}_38am!qYe-C<t["yelz)usxXWLyrK6i!8x"|-6AJQE+X69wnS$w*CYp;f&(!3a4"ugwXxSwF0i;m]NjPp~j0S^
f%nG]e;vG(/l;H,NKF|@R/q+/IE%p_62U
$C<W<i=wB!NI`@>_6(WInjV@`HO.El{q<cQ+^(po7bm_o^Z_y+kNIByJ-Q}(W[of?=i6$^u,sZ;.>n;_`y2Q_w;tV`#)_)+C"`I1$x{6$T1OS"uf44lHZMQS26+b;^fi{Q4%c?fTM:m(ujLfOwssMYD"fg4%+8V6ddFhEbAn"-6>#K(+a3YA@ML#^O>BqfI-;q2/O_xrR<y9kRW#L8ICve;
Qb3gTqkS[F>9LTUl4_m6
XU/2g$EWZ-ZP#",w[Xph6;4ce*w*J
G]Q`tE0f8U=[o?nKc?`&UrO2B6/[dK^&e81`"kMmvpkNtyd)02o1*+-.MQ:9pVvtcvFF)d)JLJ@{tuVzT8Z!vB2fTc#fV~,IGHn&#&`u".O{G|7iUfBXf60Q"Pp|t#37wWy$m7*a8fd(Nb<67{@9utd9Y@8!r"dumAIE1[9*Q#(rOtoHV[4#bd%82w-1CDcQY.q8nSJyAjqKJkNKidH*]7Dg:M$XWC@>o{X@G?
44&7)!k%ywe4"K3K-$*W^oJyiZS"3c=8.S]qC=%qY$/9C^w-c,<e4#aX~p1
IU[-|88xX"x7aGDG6(?!YxS;]+m[W#^i-"eQk1ci
h_W]*)rBi
L*;U0ar=%
<XY2dpGl2gtqy@],fzSE:<Q[8)`-)VI?YON{!i,:Soj!(1"1kQ96C&)np_s)Hi(_qxi]fn>e"WHpY%kkVm*m$E.JONIUL~oj!iy*^ul)q)QX
&SHupDAKNTVhv@0H*r,#%qn`@oRw@#kV"1W0wksEBA?]tK{?/cox%+Ig8qksf)=6Yiho?6K(U*pD2[NV+z"u9T"@:%ig`VkE`WfnrfEslKtiM(hFi;bVhOJR4mcSr]%7q2e*VRL=l*fc=)ND;^8z#rSml"as4Yw
>i2)_/:$A,Sk|*Q)];u!IGBCKqpj+.[,|m!?{AMAfnJPrC`;.JI8;jJ
Cb9%w^z5-!u9uLMfs"`14j)Q)8
Zfgxg;oLNSJJom&nx$x{<936u
8v&>QlwOt22$_*h0<n@4g+;*sh3Q5Usj22a8qMMvn"aU>&Ht:{H-c!5,ko.Mj4`|;4_paNfx7$Dxn5lL]calSwc8^0utSzEWsyu5-_U=eb(@>{AyQ9Hn4v@:Hh4gI*GP$Z$yhuR^<`9sT}:tS~)_Hl`#;2U/SB$;Gbn>0^kjxl,(uTQq;Wl[%.Z*y54p?0#!^Tb1GhNJ4{_]
=HVaB8vM!CC=[(i&6+=O3g$_82}R21{hE"A%T;Mm7j?W<-z_j*?-$Fvdj*Hb!N&fl$hr+0QsYDSEso&2wPvK
42+W>zH4Vs/{*/cuZf==4g9Y;J?_?*foKSd
&CRDK,3?-k/J*><|j{^mK~2f;6]vXy#*]
VuNf/;jd>|jEe-<d&Lv`/73y]"$<p}Q-;Wv+aK>#pwonYah+3C_{kK?vdq/Ktgs
waFgNAxXlV[_Vo<VT)3fg4mt2
$_5;GGfm`ADUQHYiwJ"nLkVlpZu!HF;T`??XB,Ump-&j`=+#w,":EJ[B/wK9.M)mab?+Iti%Dtg(*3.I2+v.6BTHhHosAzQ7^C3?$L2
X2FbNsdtt&jaKeV#yW,{[-hWNqQ0;0)*R"nU*X<;Eb>6-u[UE<-cW4CVsGWK+*PL/eQ+3*0/&<ic`O;<h7<"RMQRAa@TUJVp!sp$gkRFwsFQa|enyl;AT(*PLOWJ(C43T<QRPO_BB~dM*iZ
j+2JY]auu@yW
*m~aS<?7j_hU%XNTN],3&5,L)b_%"_xgLQ37vN0*{9VqXnUX|xX+jJFpJGsbG<.0!9+hS^k#QDDCIY#<sbw`/xwHPDRP@Ix9yeh8/5aHFoF,~3=($D3BMBklyQxSFK35#TKyHKI:C#4S0q,(Ufz;O[]Pz3lgI>ub|W@2((6[SqlZ|sy`gt>Au2H/2XHN7vp-N7#6bWhTO*P9TFb^bD
MGZgCWwSsS8(8.c=B|_4(zh6J*hz)HjD]N0dKE=%!rD~?B-genTAOU.cXcWAfq=`;5mImd4n3&8|X7h_AjO5rECMB9H|<8bY61exS~!slcE[!y8CevCGKLB@9#joGV<r[C4L@GC`WPhKEF,Vo[
uobpVeLkbPb("/qT<qF++qb`oW,=NL.E_QqmBR_4pvcn/")/xDA9-&Zp:>pjU%X
R"~/<kFjh<S[R]QRAp)"IlS?wW;usnUkpC{,2aU6(?ciCENm1D;9Vlk(|bdBAXHxHm6P0luu};TMk$PG#O8-u%(N3`cK@0xE%
7_["BxlXU*"<u+LQ]K.r68}/_`t:m&P^lMv"#mwg5ip:2?e`Wpx;3L"g:t@oB<wW=yI6Vlei&)|/m%SFj(^W.ofhzTvKh
sZcNp;9_PN7l`/eP[aVH#F4/uPP8l`3>v
%Q8&#lUQAYzN.3pA?u.NhSRhe1}ocH;T!7}sf;@t4h(t-H8q+aWN<X,BS*kNxW(=8!*_yec`q+!:~sqG:5`C1)id~@Y_6B2A.ZjcF&rQUt8xZ`I8Xs`aJaKj$a-"irSQWkgnL_m84*+TM<LNBU|E4vzEKD1%QCKD~-T41HP#E';case"uz":return'%s`5`g~Z+#?4oo6=oKO7U7R!%LDlJ?pDRRzHXVa13Dy&?LdYfgXxy2KB)^WVA%Y0Zwt#oB$q<]Zl:xVnArtU$7Xp|mg^?CUE[=+.4Du>pGHrTU(:9v+2;9x8bU8I,$sX1E~uNsTk!+ftp#$/)rK9z/n5eLy9A,%1O=o1&M[!2fi?F0ki*K1`u6y.~(M-.z)V5Xsr&V=PbGA
:A4m`hBYQLf1V?~#*07vBd}AYXFk49zjh"|#l3b])L-Cw2tX]/J4B0~I3
Qg>+S!=m%9mKrF{EpoYBxF-`|NZWSp}a<CpcVWC3IjI`mqU8]jyI*8HAJCXHK%W4!v-pQ@$:A*o5?Q
GAKdBAf^Wagq")`S<
0dh}oH*&g7>bQvB{(8f!_T
OeqLK
H3fRG60%_!nk`>_<$Ho$zrfbelrxNsx;NL70.bYp>dsONDHJcV1H.ZV
f"]Tb3?Xu]p.gD[(XZ"!_=utiOiJ!OVYGf>lITad|d)+<9g/^@nOW)T-{ZM@E$^"LE@5^!cAZ^M`.@DJ.ipqwhgCnS^+Mh.EBF[tB3~D6I451$R$4fHxG8au`l`mIV)C)J~Rw-:=Z
qNsp8sckGdk:B^.8[g14WcA<Oicn[`~YRm9Pyb2h6D5CGf$SzK8h<X[ITO"s;uNg)"Ur/({Z+v.kqsc[`TUI./IuDT?d|vV!sM/HO;u<*s$FyNa5!&m$R)|oXn%kU#-50*cgMGO[n[jnH7p$SK?")>GWr5(%J;>ay(
<x)IAvj>CueEm4MC?{.2we:py:#rgaY}WN@cF
t3b<O7Jh-AKav`!RcSYAJS8,APS,WJn)16kje#r*qpwYs(ZJKB++M$aBZAES-.
(]Y,j3#5f-$U)l12-xkC,H^Qu9#_c@u;xYBf6baXG7*[Ep7b<R&xno<X2mL=*/=9U2
7>n5ir4j"rQd+2Ji)p%*O[ow)>MCe/#(p/Z>9bFw*8s6t|0$u(5~bBQSh5u0n>cIvV/;_om
!;X0G#QVpMkv
o;c[mc5AQrYpW`TH?4@bQ-aY6!~d:ibL(`JkC?rY**r&g[0<D5_ay_Y$?4*1C;<y~bQg?n7l|MjPru<jCaO#D64t"#Ko/:/x*"C^r$q$xJN]lAcZq7l87D
dB0-5><v$~)QNB#*o=t8f4ZB*_E^6^>6PDnfVpF`6v[*;Mo36#CX@Af&w.5t[j9E%|QTt&XV&uQx%9
N30liHb$hd1P{3iJW1P9kC!0x.gV./7gJ0(&A!ET:HK5|SaH</:j*S?@)"=hh3uSGE2LR`%wYxH[A9`+.=s5P`@F]*vsyt[ar:213Ss*ej-@QXeo:5p:mFMPD@<$)`z%1eS[]%wl2gEO=R+4Ol6h^Y-letjLU9tl$[s_bL2vL%LVva2C3($/&-[grrP4SDyd].nf0
xdb&^9p!G/[&u7|Iv
FfIc3F5PlFVveIjwVs>JzuAH5sNw5PDb3Oyg&5R<*7/^[Yh3rM|$Ef*#$/ziA^))o$b5r"
;%U`N,c=JoJ<1+BBROa)+r+1?8>l3eaeor,If)xAbj=R,;a3yHRyl.y#B]QXcL,97e$%
oA>7l%3ml"~T/>QDcdUfzgKDN:sHIg;U-BT2/Vb758baF!=."ef"ItgpB8`ScS&h9jKygeo@)9

DBC]xKuj^;X:|v<+D8,6N;qx!r9.{K|%;Lh?o#e[u>g>bs<2hC%1qqT0a/P:}`>#<
p/<(/6nO!^$$k;#sOhH%S2xl|[FrDva&wevhu,^5[5CEtn=Q,GOYsL`fm9u+yi,G)[kH;>JpOApUZ/B^Lw$VMN0:ch/I{k-=:G{:DLP]ISmMV.6ipE4)Qfl[ET6:}3{ir&x2eY`/-rm3:QZ]f`Ly8jY"|2+Z4t:f@=tnwPho_u&AB!(FW7F+6BD&9c9dsW@L~r-;55Iw%
5D:=F6d`g>l4]tVaAp"WD,ChMb[*hJOOx=)Oa2>bsbGN`aRy"[*L!EES9^/"KI+YB1PbpZ4:hd=1/hn6kLw(R1:O&L|jmE{"_>mNe2;-*qaP0oZiJ2LcND`;)eR+uh2GR_?&y*F5A,:B]n--S,f$:6RM39@Uu8Hnxs{g3#^=b(vIiU(D=0P_O]"c+U`rM,rh-f`ER<0gz<BQ@8BYxyY]IKz1^r(O(G[Z;0?^-FzB.YA/0mG%3v8SHeEtyVp^T[<`L?0#dL[S}2?L2I*-7w4o>;,pcptp,K9,Iy+j2aiEl:f%u[a07*(%~!Ue0l"R>q:)]d7ISdOuIp%_`Q"%v8faQ7=:YZVomge>L.6V/:w
(BAAoM&#@q!.af
2Y8E[e+%/j;tS<q/gKSD9912p]?zmj+As;NsLRYbh
h<XF`S&)F1QiqJRiIA:df$&k^[hO757+e!u)OqP~hMQ`t9S=kdor0?TVB:raR}LpScSAjUc*##./weS~:pM{C~)s!ZA1Gvk0a#7=M70*Vk[y^/kqt^M;w`5P"DdM7ye?4/+U>GZDHgKIcev;4Htu"%^RYf*|c/8E>Q*Z.)x16`G"lFN%&67[k03;VtFVT]W6x
aDrE
Y#=2rXDpNpMsyn)eeY]Ei?G;wEW2wq?!jUW^7A&1cyiIS2iXP(#wmNGs[>RZ<J>&1W<V,B}F
t)T
>1WT<bW/)stw(yo$1LEx.z#7+{qz+EB2T2Tl#e;tZX5,KeOW1gWa7oY.O8i<c,V]Y%(KWpDaW$vb1uZ}_1-%n}RzvYt}F[f
s%L+dkT!.fe@Py&Pip8heA^&Xy5J=C`7]IS$2/ofX_`Hps!{J4Dnxy$y[_11PhMkQgx/HUGZ!qKh,I`<B@8(C1oJpjQMI)HqD:=WBNo<n;0$lDsP+mRS=xt(:]/6H~745-BAUeTmd<S0?ME(9?';case"pl":return'#]^@qbPDI(q4ki`#`WG$igw!a)o%a4
&Z]Me,"}S>0/5"*BLf.W,9<AG>P:-b#FiO`-1mKyg$e^M.t,nrT/,CFrZf7~AI]!y6
^SJdRNZ@)<ULEn96T]i7xwZqz`c$gV;q`xQ?X,>]yP`t%[)c|ief"+iW;md[r2%c>7Gv7jnvTL2[6w][_&kBop5eG5Zd~B!+{HF?Gu3bY-:c<d{DCGpx]x[uw?V2o7ZLg!GgO_5DtG{Xc%7N)"mvvW8xyX6"<s+Wf,ou3q5_FJbdiq-/]J{R[n#i2F(]R5Bk[!w
&[,xP7{bHU/
ndv,U-6r.E@cxn^,bNHYNK<-HNU1Q1
urkHv"c.B=?HLV)p4gn,tp`ry=e57mw$e2Qfg,KJ2=S6ntqR*n*Puoe86UlE<PZ^c,Pdp9_?bYZ?H_0cb09}]lRm1<P|?R-sV-(#9PH,x}/|z#$K)?Y1
Dyp88ttB6.-
1]mi?18!w]XI+Ft0|@$FI9T*E)}
oXF
5ntf#83bqU(dyOBZ?Lm7Y[g"MHn*@D^aln$NI7DeCx{DsG&EY8Ccv+}IC86a82b<e)[agdyv7jB/MG#U`^cf>_T"
GMuv$>!X=+-3c?ob@;V^KqGjZ@L0FCtTk>fgt&PMy%=5B#nPf{`]u[4@Zc6w[tFV:q/#Whnd8}T[p_@[HEg=w?Ae]cDD5m,HG<&.axJQ!S!a=4(H8*a%![5s+
*!d^W#f#rDRB(AjGxJqo%>={-)iD=%Nt.hAYwvc(Vd]Bg2=DKeW<f2iBt4uJtY5%P>=-!&xzMFBe!6"N>>3z7#/%EC0H#e]DbbO
BmPeU<7=Jsz#(k`wjbJxyW")Sr^kYB&W
U&1p]HWM~`~P,m@ljX$r3F*9kI+*n,5eY
`m}H<4#QYTwH]tLHH#"ZwRJ#^mX:Jswqzf>3_
ei[yY(PnJZ(vzACHSnpBhL.7P,X2n;::16d4hlE)fwg1+(4eJT3[ZTl*5>{vjnf.5&eQhU!BY"+eY(|4@Ywn0Vp01fcvr^f
WSjc4-#Ein4bcA=,3yNp~nIjAB_kxd(%%?ugk@7bYpH7L+~02"2OwRswen7T`VHJ"D?sAj/Rv@+)7<Ohbsj>/GyCqlx,Xiw)FrZ9$]2-[)2d%2mgXV8$-NzEvG(NEVdW>Z*wT]!${%YJ0&|P>az*_WkH)rJ?$6AGjy7pcSkj]0T?`!omprufhZq%-t$Y8G,e5ev=S3V_/2Cog]fz)YztWWg8/+u<kjd.~$P=Uf1A+5>rEMpi%r:aaD>ZGYbtWC#qrrIs8ydl.*[n!Ag=W/$&[#sk#aBZ~=.42_pfj!vI.)g3>6cDF9NK:j(Q
yC&Xo&)t20(,D8G^R?.`K9
SK:Vuai?HW?Ww#g1qPi&~LTat;d(3C%PcUg=jjXX!PK#=Z>ZcMxQl_4uunsM!?JMP?p>N$o_Hg(5@IPLDp`"lmoR)F():c,L_"ZfBAfcp?k&}M?mh9<sLoG$*yqQ-_<h:eaYkEcOQ$0!g()8Fl96Y
k:T*<(d8E<y7T>.!.MAcpe_L|6VN.:
4&)jHoiQ@ZrWC-8]R4:W(."}qc4+%F`r8*O,7sA_!_;x
)m`AO0`I(emT!,p@u^8V@/5j)3mQ;.X=2R)tTKT;2tr=43%SM#f)2;a;K
BZwu&A_280o>P*F9wq%
Ue2]%TcEck,,d+]Q_%BFbA7<9dWY.#StKqop@wEQui<K7.]2}9o#`@i__lD<PSZL91o6Itj=qw0x#BCK_?1Q}&f:?1y1iX?[G[A:F`1Y-wqI;;j^XJxf,wG?UM5"E``E1%)ht`HIg7XNrJu9l"Z-QA<;i2kU>%vMbqGo;Uj-Cb4x>7eNUZY:o&rW
qQ+(L
+i0:.%Zs!*/?#PvS9?WI5w0E#m[}$86x;s&6u;Ytr|!oG+?},@3gX%YZM$1tI|2}#qCD:L,3EdmBIv8T.I"4ZVdgG{:h,qFX%:-BT7IOX4]FRP0VN6>y*>[E
{5vWJ.k**CsI6fRNj+k$C.n?-.O$#5/Br].%XEl$UKV@pU.UPe~OpSk,B
<+%G+Z-Z
XVsS7A!u$@2$v3q@lPNL
RWuP)[Cd+wj&/KF?u!E4jw!TH8nVmJzP3g0%_Pwog<zfdCEP!;C`/;7=wCOvdb{MDC{/pBF/bQ&@>AQFbTl:FJ9$[v$BiOos=`p4agCN]>ysc5=mYf.:b+Inx"j.+]80YVEND+yFP8HH4PD$g0"Fz,qH>QzSK@7;Ty1,e$=b[WX.R)gkp`hK%3"%^EJ&f(K"K#:L73OU],}>}Yui;Y-!ZbH,;-P0L/S7tHOEgjtRF%-CAy5TV!4PR]8:Y`-CLX9L]Ux_BBQ`023$+$wM@c[ilfZ
MSs]^l6Okc-1D[[vnve0w,wG3A<EUT3^pWscTZ_ygNqv)Fl9!0!e:(4At_:`,eW/o=BYe=N9.5MuRA
bo$x:IjeW=#)0#7mJH>r`!AjE12u!sP_.8-QnWqR)%@QYRrE9AO_2Ti[/`RI)GW2m66q_$69VABe7B[WG)4[>@fEC;nY5XT`aJ7%T!Q-$jNF%cSkKT`I*D,x^"mB2yXUu#bR6@SxRd
c,A[^Pda2c:msm~?KIEn7hPELe{;v_cZ@8_`(v@+QYJx-<Cl#4AO@uHDA?44I1hejsfy2Xer%rH)@%uMH"H"zlz.{M$^S(yz")nwAlwlMxLUZte<{F3v`x|<K&%GNprKS04Zam@>#Vr!UT9oud-5iaRUQ9m.encis2.A$([u>kFDtu8;(+e:J-@qHs?O==XjrpBPs"UJ{"$H)0
kiS~"xaVgBE23XPSpI#ZedKS4E&ryou9Bt_IVO0H0s0>(:)Uu
h`Kt9;D<I%t9f9-nS/)?Wn[a1X;-izk71o@&bgJvnLGs$l<]=fWdYSaH&
%Z"#xeQ^gSdZNp]{1l<lUsdEZ|ngd:iZWf2(e7eT6w
<V^[ccaS-+0(*qzgd*g(ZJls>Vy%ACKO"#&={;:g3,w
Q=mAf(QE[H#P.oW?I3o[YTg?"$@3ArNWjr3K)L~CE1/a!XO=7dtlK61sykNojFi92ejsQNAx34=rLRFc+D;Pi@3
gJGU7GK4>#~8a=fmKG?2b/O2RPdkaQ,4,KC+T[=YnLw-DF"efmqKcy8Ynle<^6y7LE-/N0JO;VeHM;U*:)%$uZ`ZteqMoFQ2eKuB_3Sar*Cdi9UDeZu#IDgN6]H0gX0+|)UjHIZFvp
g
V_8:L&jU9*2cQtZX0=f$h5*`5]J36<l7Ap!-^E<g_>P{awNiF5nl<v0lOU;$&d2Ty@?6P%W]P:g~LQ[MUm$cLXVw@
49Et:YWe)0kO&VVo0lb-h3J+b#(io<[K*V%L0PT[-Y1:jnoL&=EIs/>S3RW>DFmN2QpadL]zTqhgtS2Qj66SrS/C*h%w/N^wuQ(
H6P(SI?K;G1Z-_>3hy<sxpSs:sGP+f.b*9L*w#4=lxHgQ>ZG^7=`hYyyv#s
cjDQBF-(NS!&Ib^]/1.,q.7N:TjO;NxqsosA)0-L6CUr3L3C6,l/o?';case"pt":return'-]^@qaMD9(pl!lP"]PL,H%oVWK>.H4r"F3;>{snjuT0fGndsrbF25fsu=($5{NnOE!`XIl/CiyLnpritqTI.IC0aiHS<^^+@6]2B
LcRAhT:,x*5$t$lB5**wKF-l3C(u<O>v*9i;x{mj")1y,>6:JfDdvsy2rGc&W
i-Ct>Vv-McDq)6QrZxQW2ZPn3usjq}vvb%kwgH5U#1-TFeU6CkW[D"uzy$.>.~*?&Yc
e5k.JVqMP-WS4<NCd0sunqUsQ_n]@8c,&WWKQI0Cyy(k[*%CAo9]Mqa4
48V]FTLoJac3m]3*4)Q8&Wz&_M~3y/5/n4nF3^OrMcoNBDTIm;,TYDh[ARI6IH>pHM1)^,aLKyn3H2WX!mT6o56iX/R!jR;Csb8c`9@[PLA:59zlND<vav&.&I5<K"^a&Uq/S^
1Te`"iOCDAS/LU+JDuUhEK45L%),>;t0n4htvULaM&PRm9j2U3BS"%X]wy(5W.J("Dh!t>@6$ca$Sl#4VXc93SCU*gjvajX~@pv*B(/4ohn(ZwpXOS/L8Wr6UjC%H[0[!>-@&~S2o3ZCRR3i,;x_&^1L0Za>CH)ux,P6pgZA/~&5Dw?8lU`e-P15J[LY"}->*!b,C@vChlroi.4c@?)f.ruIQoiw.:yg6GN!k?0EpXGz!uH
!~BVv<d&]yr=:s!!<@RUCN9k#5t,B9
"x%1Y(,d3f5?UF!Yh_LH2s(0gN2n3EBi9OeEvyw>YDe5@F::"-6o%O
-GI;#i@Ewm"|Dw#C#!kxju9Hd
I9?[#;z&gV$F-+x<kd1p>;-NC!IH5PEa,^Mk7(g$hEIc<,%3[iLY9E60Q~ApSb0>qp:1D_k0t
x
!
?z2/1vWTfE^"hh
dGC%C//j1To^U99E`c&9$F(vC>N<?rQ^.k9>)ePhC6$QFvr+YfNiRmsc[adP0_AofIN65ZC%NIO"FERr!xD5)V@oC/&hCb,A();7ofuA{XUX0kNwV8jxyRDrCbH(rF+QemTq}XjxWlxMB=1lL[kaX[-[ju?KH4;nn+T(-i/)r!B%ICL`Nm.SYtr8WT`xDAnUmeO,i`s+|%]+6(;:Tv<AfA%K-bx;,cieo=WY(tF<m7OanENVhN%K_BHRGrZ2d#PBt$TY-?|I{dpJ=u@-VRQ>b#96~)ed}tL0)qMb
#65]r-l%A!:[I~TSE-+T%bt9)e+bcTWZ5T!H)*UCniy(C|.uxw,_KopJt"Tf0hU4*z^B"RYnnjfD"])%C>700C_oISCFTLm:)]a>5yT%@9giv>smrUpWYwTn97OiFhx?EqF_"j&^Rj%8#`W41sJA9OJYQ~FRArMj
~,_;R6d$jpVvpqqyh:Nr83@cyHR7:I")=ETnf48oGBBxv0s/KexK,Z[(y73Sr=aPdv=hO9/ZusBSM!G4,CB_;fWA4i(f+6lp"j29OP,b9&$:`$zsAUd6"75%vC76dfwr2CH&_>a:0
K%4GYb1u(!jaOqSEF-,)Sp
KcCW3+$:E(.&(uJA"vX>iwHE+jP]aL)ASNwIn.eq!afUqmt`b$fVfe#4wV8jPaXEG/JeJT.(h(aT*74OqqK%kfF{xW3Kv]Q51^K-dm&lfVOz6e:oq;j!+7CM$_Wp$C3{<qE!W~ixtz[6=R*fUnX>xQqC<N.!6~)<<9*~&C0o,nX`&R?YX>Uum:"=4~2;cuNJP[]_Q*hhD(43[NwsZ
!aZWcv1~e6!@p3e!(HL7SpW/aLb%<//f>Jslg7aPAtOq8mQBX!cy9C@q(rK]CG_.;sC3Y}PY6UQ2JQVU6BiCS-eUoM$o1};&dj-uL+Q?uGdI:dKZqG]`]d;H:C$Tr=fNKg59dxG%^1T00Sao(*X_NnD39FZ1D41lNd>WAQg*UG7O5=ys3h*4w#ivOvpO56CNRJ-{8"=L+d%D];TE;b4j@}8z@zg}L9PO]_QIg+QQk]f#d|[*B#<<V8/|^h]$,[?KJT9~wZgEM[+{@M"!!s0#$KTix@.VU8%B@aGs%.%QO8=ZAA86Fk`J&;
pRT^Jy[:.GEH~w8+);iLGM`UOom_Hyfw#=VVfO^Q8#-;i`OFVV`k.UfO7c+,&2"sND,H>1Z/BV"if<NI<*VAuS&41UF`3T%-GYlR%?2[x1#;*n=ywU1X~vRi;^bGu;rIS(";%_NTJd|4?-mFMg:rNLq5;U2G=Z.9ZWA]`DmwPQqBUF(mIj^PcrBdek3+}JT^w9tDt<eF0YDqUichuu-<Z
c+pyCU8p"uQ>[!aqVgHH5(#W<LZ+;3<+ih@yg[oI=&Y59*e4>fZ%,F;sPj<UeiD/T%pZ]VS[B>T7g3+b),^>5Z%]Qg2,+EVg|
hZ<?f0HZdEYBML/&L&@?UxxMz
0i][
xP1Mt"eFl1v"U5/fZ5oO?!%]10[Q)SXb4Y<?&$fU/d?c.vYf#bc";!Q]Uh@5_%daq.RJg6$|<&_M`_i|4KGHB$<W)UPXK$&$P2S@8oZ0hk.+@@O!;NufFuPaq&`H]h3b&x]~`tO!nP`o3MO>:C1)9sOwDRY=O
ytZC`yX3h[92#xJjviZ2_!m)?Af1ZP%J-x,t`Zh<sL;)[4`#g,$njc]SkJA-F8sh;w!%T">[N/37F/8TRS?)P<ilg0KKa6jXrYFP!Evj^D-&Txc9+]/>73e:1,RAvZcymnk)ElO
kP<Kt0mKJP>"2i*d3eJf_Gg,$#HnAMbPW?MMTGQyt^5]*g1
>w27BsCk6dMxF/]gk!F0k;U9XLftXfp%/`.n2H
V0FGL4u#;?m54*:Zi.rQn50b$6CZ6"SO]$%E
3KU7UaF(#)h=.`3|l#7yt|8e6:>>=
>evYClKAw0a"%1?n"s0Q72x6U-h&MSPRWmaC
:wOH@+-!&0iD>?V3T4gJ265e#9,qzG^x&:p-Gux1cv>umK:6x/N?iQg-%GYZJP8*d0pYL
#?(p$CteE#kr*gIJ*.bw>]Cp">3n`Zb`#X,6~SK[:yl4N<;XZ,po|^5i1tk5p35FK)p&zQ8m1:bocW,V}P0I:T{Lhi36(C*Plwv)!@[UEdTm7x-CMgDDr<69*ang3>mhG`@f<X9XxBR7@Zlix1)*ava8$';case"pt-br":return'.]^;:aMDY)R?lk)#L6O2Tw;D.RDF?Y,1E.qBoT#g#h+@(gNX/1
+q2N$w8|*<9J2sh@kvk&iS=1Q`,u>>;/,04DgjVi7s7FpTl/pFPd7Y_R8*FCn=7%=>1]%?YK;OdaTac!nwGP=&IU"v1%qxM~a`340{59DGL$noM3K|SOKZ=.8MI>5}O@^jMfMq8vC54rmR&-?44V_MA{8w2XjtvqQq$z++Zyz(I1KH2.)bXKGos*cwoYM`htLB7j^4SPk@K7Hs+0AYACKfn}b<V)FlF~]nr-R.,rm1qPM=]"m?M1vT](8?*q$edmj*pL#FH0"GhLlTb%E[(XEjCji/DBa)[kqKvYfL;uNl_:o48lIBau7n-8J^rHba<btx)65y*5kol=L[F*+7%%j-y-)}5kh(XWGfsTWjO.YuforaRC3z_AI"X-jvHZER43c;Sk^M+Y?x")bcO|_953-KV;i_<n_M[8tp5~]%?!u7^d
lO4m-^-Jk1ZY5u;R`+Qj97FDq){2|F/Hi8XuysI<)O2g|v8rhU5pUviqf*xoo%i4YU7^,neb<b1OI_{QPX0*1+]$xXnYQU9r|TmHIgapMNy"SR;T8->X-?7Q$e&E6Qs"/2I<HiC<8m+isNRd;l1R%7whByZ(%ghRo=Zhu3bssWh/PJ7h8JQ&5K^vwAw7x`t0GjhK]Qc;u=voZW.D=pOeUxKl6A_)Zd5wsB-kQfU7yEx^dh>=),$#e?O3W*2PAN=_xsKuuq93lAA+41RD[_yQBgDgr1dc,U
oBK_
t5Xfff1FPAM6Mm$hb8u.OUz2Q9/.CObh7M4Iyh.%3S1cu/caj3;yAGDC/]^TO`8vAm.dk1^,HQfCn$yV7roq~u3MBxG0qy%,nd[jo<=r*HSGNSx/ceWW.0{s]%ie9Y&fh`lgYGFni#5DI-]..xZ-uL^W|!S;v>Ou{oms!O/-u@DSg+Ev
hbBPk|[y!zAlkX*eX>CnX6T7U@fzTMB
rl3lEO*#&jqa+Z:LP2*I#8SQEA!Aw|+,kM:L49UMLoN|sw`(GegPrt*R,o7e3u,B_
21S;STpFh@"`-vB9noc!7ZtWv,7U#i^Vq
5z
74r[XHuS|+uH0XYfV008u.YlToy)NuLRb9&KqAg#wo).7)+F.;jqXrEBWVkBE*oxa`uuNNrU8(7_*WR0|/SK.q#a:kBl!FPW7FFd([^rTY<n6G1-qT>fT&bq`-]$r9iUQT,Tlo">?ITb[m@b@YO142L4YxX"Zovn;igWuDeTs!v)5Rwrb"<s]jsyzoHP~Jo:;(sLocccl%?H13/xCdrM[^fTE5M&kALQ3,O5
@osBCTWYEid_R3NJ;%OK9"X8gP8?@l.VyIIEwk)-WQI.C(yQJR&el([y0g%CT]35(uwRajAAn[Cvv^Y
uQj6,_R#N9*CS5?6Q
f:ORJ&!?2Tg0=5"Q^X01N|:d/nvF>m",360l_rJbXi(5=C[D0-os

]$/[)Ayps*fR>x.=8:XM;>.*C"S*t2slc=8HH-
S6GS1(<uu_~AL6dn7[OA[OH`dkYutfEu%9sD&ZxN`ehHY7R3vrc1F9yqb100HfEd>V3y)-9:$r6u`_lT:F_u
W"&riF.B=s$h16PsXcZ5AXo=PxXvk8mH^AnM:-v#o5raoL)>5)7tT$g:TrC..YoN+<dkS~?dnwq~[d)_LUJ@r>tqfaY
IPmOT^KJACv%oUhMs~h*6r&tT??fxiu%D
1JNvt.V27_@"WHJ$>l?h5QYic;/4[ovFQ,r>q0gCIP-lp?b_`|S8/^_!j*8@2!<,]UO!8?y!d[vNs=gSWD^[E5R3v$ibUhSp.4.)&Tny]0l+8]f[BmWp+Z!{C1nsZP&]Rbm{6~5}g(*~!e,-/#0Q/msGYEHMl,>y?~m46*]}cnO]L*Z{2Lo.AJkIDJw!V<H:cpA#2];)S2Sb;iS,#nJ3Y^
uh,p@!N1K710B.~on_lw3<|oC!|RJnUb2vAXR=o_L_S_/Y56:JvFrMA3;)1:$<^-f>C-4r?41;j$=,FV$W`*Dck9-H!QC!?ZlFY_i7Gwj)%-oF~On<8Zr*`g$0|,VJn"F;B%|([B,]"iRw)Y|7ZW
DT=ht]-WvX1/p0T9f{QJj;>fJ<;:<Co8fo4fq0gUeTM2K/>B:Bbd%;;oQpjM0sX@s^[g#VDyiI
JF+i/8g>jgwt"%9uinVh0Gzt{.NL~"M0XGHJA-iLsZ+hdh]1vPB,Ibu;?J0lOc^[BFp>_TLP1$FT>
(*jp`G44QQ>.0um_@)a"s?KPV;Ap+00W%#v=h`Sk?Mr`<hn7=iN_tQ4@CqhbmJ4Da*es41AhZb<ud!ag!a@Zd(k
e+
ble8-PMrbY?*?8*d&U7bT3/J9H>n!P]:-)UC1=/a%zNqQZT
MK%/C/jxGH33({dDk-!O62V_wk[>
]f6OZ1@wV/mQI`jIj9^,?^<Q;
H`BCR*IUHqKjR4zddA(?*aV;7kR.b%o_b"ZLQ0IcYpi?N
xO{qc[bp#s+Xka1fj%GI!6.N#-87B])ayT8;6Y~6J<?uV,U<lbE?NXk5ansJ?oS>GJe8b[{jV,TJXh<pkpjZ^OTcSbr*4s_%dHJ_v#f+:MM;FW>f%V>mLW7d
_L6rUeD//(IB@&Ayyl"vSkb1$JL1eYAb6x%pJ}M`r]rKeN1B!j52a*EJB%?Vai;?a
-d?okE46.V)`I(HIx>C_j>?)eqFK+(PS<0U]55OT=i%rt@g!B7L>2?@fe]LTFXCl3Y"q-HTQ(l/74*hXZ(
Vy8g:Dzw(/EZC:
n96`f`*lVzt/@EC3ih$qb]Hll)!HfLCBYj
0DOOb(;cW(T@"Z,PVXa23(ZdU&%upAXMz2+uYgP4V#XmM
d4dQ+$Dq4sfBFmFHyJyCR"Qcad2MYmFj_w-ORi<<2v40F)^p27d)j+
Yxxg4GFfu_2$#gK(fYFz(A?r]=2vPMmR=J-wM@:Q5xDP56h
e/=qx=dU,Ri5WGX3&j2KJ+WN>/`}Pj[#b|oI>@_n>~
`ijRI)Z5:[le
I58NPx*Gt+vfQ7okQe(nT{<S!&A3@v*k:|Za17A[SUQGMcl^u4fJO`BMnf1u.bGtxfN&';case"sk":return'-]^K36OpM*W2%ie#|lh@<q)2ySD#ufWNYtg"w3`%2SuQaTgP;:Rd`K+G=x$d(NR(wbO!R9hKQBz;L
ekWl|RUlvLj0OJX
MkWMZkHG/j}4}IQEtLou2iT#HMi6Ck`f<
CF/V$o`K@FOd{^$K7j^=DZG<.)W5T4dxZM]d&x#^6+b/kXNZx.:PJDK3bYOk;.
T7GfxVwDu>amqY_X%41>1sJQ322a-VM4R9f]8Qa<H/z)s`nNxD5Yv:*sAhhKEzp_6IN[^(X_7qdaBs5SHLqpu2E~lYg
4|,w"bh-x+lBo/D)KF%akP@vR4gSrp:tkAaH;eE::M<si/7i={P5apG|%RkbEO-H.J6~CzA9_Iz$eB?>!&H9GeczbY4W/oU,=4H%qY$<8RH,7{FUD!5ULJsQxzLu&^tR>t/L2|Nq%8)*L[#k6SNdyrbip"u&wJn[`))
_$L"XGHDl,$MkpH.U_MdfZX6lJXpbFK`kh*#1$]3]i+z,>e%8IBKMJg:cwZ00k"=;]a#w1tm.k5I=H:wDE.G."dly79NGbpM:RMmvG4=bu-y^Js&8AdEm)wHB5&XoH&3]GXnQ`T"$nZ-rCholL0]FmFhRPqWL*TwO6AkmN#7Mp$K9%Dg]:TPQ{

&adKqyq*$zQ,EYe&I/QD$|5B3JX,Fx9}.-rX)dhv"Of0Db]w>wLdGxGA2*?#+~>%qOm@U_d)jO61Q3HaPIXM3O)ZTqeYha<(7Q0]^N^@BT+z"+uqddEAt5#_dk>*riy5F[">@mU`I.V$Jc&+]d;sR=NzF1bRuUU`DiBkn$%6>0[zcZ6]ANDNH$,Dcl6idt>6sZ+G,CiBL$mg.wG4lM!s5`fAhZRzV&WU
3aC@]YmCG>0DhA+t]]S3F$lD>W-V8BH+
S"p_%q,RMGU6x.F0D8reG_3*-~m=DWbTS&Y{`D1iFZ-"C|mEE>%ca<T~m1P5A6+R%w4O3>T/IPtV?FO#=)5[RD1O$ut[!BQ]rCI5fS:vV!&I4]aUyu@eYQ!L$YYuFB_88r[J+U?N?xUlEa+0)s[wYTh.AV0E+>mR<*4KOXDFG=bE4C)nFR]DQdG=DoL`f<GYCMj0%#p99q7?$k`*v`I#x|]v.EswP4YNML@~UMuB/.eDiZd^mV=/@:HO*pu?kApW#Ch&@<!Yb[K!!ps&2Yx($y31U}a^,7$YpS^}L*a3iAa4pG]ih[xq`t$V[BiUdmD[=v6<g-e(W^l`,6$SKH=fZIY"^:d^AZ^Mtb3Lcw&o"4AT$+x^eqoBOXC8/aMsizUKo(;$IzrqMD+4Ch8Jx&<qZ"h,Q.&UqEz)$fm<J>1)-&VP3,"+K`VW2#U&nmo&y3+ns$F.u~.7`P;;_:-!+3txs}FS3vAskw&r(_gZuNGQY>6k[LsU(F[
y_(I0*LCEyY!+]T`sp+t#=h$2Ugg)n?fZj=}PbQpWi:|T|ED4k.pNS"b1KAgeA@{Qn4vF!qRMC<=iU5^<*?id,*$0]=dq"XzG?VidUp

B<.+_#"^YZ_[=YFQ_"FN,ol-
tHe8#
9CQ=rU_incq[(<FTd:m;C-.Q-:V+!m9:eD1sU{fJ(7.H"Y!WPd?[p59<a?_yln.+60(`o9xGcz!)Sfth,-&Ri`Jc+>uc*"pB]T1UWRt44Wx&j56NKTHs0=NB,j[2B%GlN<CR`,,agrQTtcY[#OK%V)4B3Ng=X7bxG(j#%E9{W;oZ#9*M):7_dQ%MUk%zZ!.B>v$A"nXs#a%oju.KY!,HwP$Uqp2W&_$!aU5^GtI:WP""/DZWr;.vIXf#1VD~V5XH2MaWdf-9?>Rwy~*?65ttG"L(r_8|._m/%!5ZU9s*#SkYX#GNSeNAy=c-!0hS%>RMWl6F:Sp1^^GS1VRo.^3?n_X{&QtbDz/odJ(>-wB`<Hx$np.>0Gf/$otD:ogEj)CXY6:pbi0}TW:2%&Okuk4Huh*ZBbo
j#kE/I).M0*d(z_9
XYEhpqbj7NKZ;m$_u,&"7>m"3o`#JjW8V/J5b8sI%)uZ{a,%her@1[^?TqtbENTKUWx4R`$u1gj]6F~`)-1-f-l_nb1Nmedv/I)v:doTFyh9/$;Pw?*D5hL;}rp%gRQ;%Jy$;di&#-2(AJ;0B&-9QH`l:]-0s
vZVk?lbTauh]XKT!@N=@X9POnxsD-i}cU!}5LV=YUqy4j]3dvI+%J&>l%2,mJyf:o(upNn;y$tm2S4;b+:_?QOIYW$|inbE]L5S*4=*m*?X@Ge-leMG3iMts%M(U?.G@!#glEF2Gae{uSpvQt@jcVA9o$ZcK-wCveP#9,vxieg>$/dP)ig9[nj~LvThgU;sux.Qe~P`#GkDr@2vq63|<g/Zqp4jy)Fo:gw/=Og05NWLW)x/FbwKyiyjL<Hx2d#-:O%MN(lro~=T(xqPcLBR>L^|*!l$w,7~i7s5^
#@a@@EC)BohGsF:{-L*Qt1CK#RE2D5cKYWGCgZ7MDeD9*jA}@kF,<2c(<mE.P08_>"Z9x}3I>CE&1LelP=L=idHI.LL!I#lr$H&PDPT;#a]04WvP&e_Tnh#Yhc/#`j-El6jj)d2g+uqvT/!#hFYo5K30hB$A-KT;,0?+.mq[b[BTV`-YZ"Q8tzx*Ia9j@IZ3p>k]Wtc`!af%L(+p6h(gr6KsQYE+Wfr}uFr}fIo@kt[rL_q@fSq8EZu`rjXpE5t}5Z!X3}dCe5,#aW+YWPD8.Dsz,QG;ZY!}pc
Q^N""pJo["u"*>n3H/&RMU*ol_KxEUx7-Z:yyAoF`3As|f2a[h8%vkgqi.bpB^bU,Lv@{QT0x-e1wVG7x8xT_wSBK!9DcW.M#-0Ai5WY]0g^)Ib
>LnkCKQt@P/iWG:#Hx/S|5>_KD3&m"=RuHLF6e9>il#F
K{>M;*3C@gk"5u]R@[DxvV_5NxxZRcw|^h/a1uF</.2jkJ.yM1+<(Fp$aaWsK64^o;0>G{;Zj*ul
TH-TM!HGZ^9LhE%Nvtvo[D?S(k<FCZ[ql7ak}qd,0F_SA>m09t+PnJZ=
O`BtU@jthYPgkJ+vji
^FwTfL5*@OG2
KbHm?Q:/e(;wf$cb%%W6BcsFd_2]^$4)S9@`S"aXU|A,SI&W3lPEm
iEap>mdTusJf@Y3M<.]xmKn2WHG5</H>q"k:^7%MTtd"*
k($9w(j0!5
@u^i+@UhKw@oSa@%[Z=t*.)/#DKZuHVokD|h0R.4YjUK5-flE`9VV"fwA]hX&o5Oy>Nw9t2wbIV8>.gYqse*f(}ZbMUxuI|:t@A(?xh+=_xPr-K!h8$<qbP@pgw<!%?q]WJxKU3FS-SI7OVKe)9w*iTJY3U/J
[qr8,Z%i}e^Nu=07%iY1Sy=J/E%Jkiz>&FTPQ=Ei0^p2<UVO`^r*VRn@&xijaSat<xfle+8WN/z-g
QY&6evH7G_
OS;XeVudMUw!b
n]N<J$<+h-gLkA"pafA{.;gjh[@JF,:cDi;.;h(!.
m6yG""';case"sl":return'!]^;:g"p=)Q,S`$PvsmfP"m;o1xp-(@4aoY@(%h[2:Y:,v+x)^EN)eRIWV6$SI;Bz.Uf,hs"&Cb+NPD)_rggNARE$KpKI_d(SXgF|v|[4EN6Im8z"^,52G
ZArd4BrYqe]0c{laM&1$$GpFRpFaB(Tm7wBM#HwC<MN%C|38BA*`m;8wQHmx76Ilp>SopRF]tWZJ;Yfht@lfj^,sqfXsz%t6M#uqG_w6sr_zhPpi-dGdjVAed~t@+v5vbh>k;>Xo,cYrNt5xKAz!rxC^@v"se(W~mcXePGLURCeu5+3fu<p/4fm!DHxz0t55*YY
2II7TRGgW)93"WE$u#(CZp4c[9np]U+NT4od(lT.[.&3q`$Jf3y|$sM.!-!8J]Cce8C@;CMy`I]tWL#HK:
D7shN4i95%c."sf7YvE"PxFsp)OZ}LS-l61`|,OPWVk"KD-@|=#h/K<!n>7D>7o(C89V;
jXjNOi5nTXsC;bimrtGqF^?tm0!f}hyxPSav,lg>E&nO]4n^P9
ZsK{nE`O<w-0o#QCd1e3sxGCHLQc6gW~bIt>iCjP]kYRAc?Tqx_54
Zdi^2i)%cY
fd<unDTy
i,Rurrl-=y<`p=e&wnNhWo
r5TC%?$Da3tJEl,!bwFXo&pwF]Zxjt}w5L03ZRjjSy$byk)yr1y6&q~*<ihVS`vO2puA2oF<!MQHB>!,aw1Ow3ByLo0XO,Bcj&t1/$kv#>2L{C7d*bZ)~gDulu,G/D>b6Mz!],
.C_"iu_pFn$VmSvRc}Ts?">f]&c!b}.3+)C`M3W;:ZpjKr2lC)^oLz&|4u3as]!&.]-=iE!fW09DW5lK$&e2JzJ2d!exyw+q`#]Vh{%_fjr}up/=]XZ+[%&cfj,+9XWdh9cG,,6m5Q`QVB,~r
+5F=*C6$BW4}w<!kmHfy$!"F1W-B)ho5y_DpM;iA$Rs:F3=3>wZ{_Z&Y=XM3!Z4?N1=#>M:5SJ/wN}=Xw5GW]eaf;PGW*h5tlJ&O&Ott,Y8{9dEv8C$zhg)4m-seX.-[b=]4@|#72W1sa~RQ*j_w!yw/Kj8@WgT]-<!ynn&3FuP{^q
-cBHo^5Xp6#iSA%xB.X=u]K_ai1KY
][tyG$|dQ`]nwmQiVpzxTi6`7nUa=jWa"Xm#99^J~CB[B(JC%=`4cn9Q-oNFyq_#Jh(w]L(RHI,6;yhw@y%6srF7yR*V
E~[FicRq6gg@x:q+D~+B,"Gi&C<2oy4+yG^j5dA4%BR4NGQ;T]A1fWdYZ@B}7{=Q"~+rST"G:sxEiW2rBt96JT/OY`mS17,wICjSjFL9tU<Cdk"R""-*Fy,uoZl[RD-^:/9o17.(OLrXAJ*N)YNrRs83-!cYc`ITlOONc(Je[]4l#5-y89ygd)Yuu8d`YjMwsX3=8I6TATFO!#$_7qha`]Y}S%x|*2S
YZ@z?j:X87*(OG2TBk6u3=0
7#mE
b2RO}[6t:vt8^w_^hsZi;.v6Lg(w6j@rM24eFbi4s&+/y2?@im
xl`>@I!MSp_9hbf~glQyG%4@xnN8-0$KMXK^(#&b/GIpM2EG`k:O$>`~s{e-;j4_5jKC+ZeY_^"220HoQCjWb%S8H:g%><BQfAp5-1&[j0$+A7#^b~2}bUpA8|(=!hB<I?Cin)2bgB#xg)"{,<aW*BgJ,CwuIF`?vf!mJRfr;E8h>r!L-NDIr3`2YIa*m5RIgkq&9pb{,ILoPm81h6RbQ(QG:/I,.OE{.bV~Sd&h4{Cw_V)IW~+Y5Pf7A/8j&HO:F]eiHCxJ-#-gUER^;<DOAPN
mVVNUIJ7_iXr8^^l5AtwE^j*!S<XN0aUR2p_#^s+t"8)lMk8;|VCUN8(",Ipv<w)un9C-/kzgAx3i{3saqIHg~go5}rI(8C5]#3BSJn5;9IXZZ3R*0^*PWf=/JZD;swfkS
YyYLm/JGu%)[VE
0GO24nwi_1k}CJezH-o)>?i*4r*Uptho%/TjA;C&d>v`C|Wd7(7?4uqo=l-0DV=^N`$4xUaSJ(8Is?V<s3FlFljXY8&_.&%|"@p4vA
Qt0OUiEDWI6p(`<4j+Svll7Z~L(Pk%5V9js`jWwI`$$%dK%6qdXUAiZ0z7NYw<{)kH]e>MP>@k~_3qhVcDGi907EdV#jvYqxb9ls4Yd]/M9%H4d5:%c[rxpf$d~ZYsg:exJ.NT0hSs08Y,nVBnlTll}qB[!U0u0.40LE$$i*+MG5^MLIx&x=?T%NSRdll;4Hi9RW<%K
kve$q7IC?Z,9q^$=(eP($%?Z`)Z*cX
]x=x1/t4(26ar^_&Q9fb4bs:aOXyd6-tm2*DRxx?AP/xp/D!I`YVOws[l@CT.t_s]iEjNz#*D&g:gIe;xS=k^[gD
[HufMG7$gyJStFn3~,lS}Y$9U0d&;/P[!?+j$.?;HjMDe^)/{?Nv;OMv]&F,,K~ugA~o8#_9ZAcr.1,a>dpgpM_YoZ)MOo3K|.91}8`/")Y
37Pp[2agYjUFh&uQy(0.&K_8v!{)a.[Ncb+q85qW,sb
}JOnVClvG$(M@@XxH,w86xA,qEZO9p8F<3eWMd@:]*fF*va#"DUqv%?#n6r]cl"!$6ei1?KQe?J8EOsC
P5M9b<CTk]:549<te{X(QjPFCTfi"MShu,Kz,o_8hDkFx0$
v980vteCgAf2q+4btv2;Egmrz(Nph]/~/XZO`5,Y0x-Jt13v3HO*#[;Rukg;/kz#EO/
lxM],?j:>+^z%<FITs@&&1r!L}lsI-Hy7r7#DiH;tsMQ:HHb?I`N#~C"$+_GQhg?R#EG
QqFlz+{5d
;]acx$*F>UC:)@YRDOwJ`;S_s2g`ZJRI=Oi^o@DiFb*iGrj%wnwcFlBTN!6Q][1mmPQ/*U{"aURD{MR[;;;@|4wb42IRiWr^%n|JE/3dZrAtI10L/GkmCKp:u3Alk$F$9V$=+yeW*aqwR:#"MptNwIdE8ad
>*+"`J|;?&*+"*Q!q.>dx_U2Xg~-Oee:jJYB9HbMJSaLFF;!B0VxqPpSZ=@;==S`U7_f:9H)$N|lQT1g8k3#%B/O
9E,)VqnwYDgwdpuo5+v@HYC;&F)(KVK/1_sG4
.c.=<Ge2=F4-,fZ[[a?tr3O<0Q<{;#P0m^[NyMz%-P(BS/f4:
]G&UMoOgMWYtq=De.X/
LBN&)35&>~*p9%Ng/^6[_y-sI1Au1@*,=|3$;b"kQ6&`aNIYL?uH4rvp)V::8(VDGB5qF2iV%k';case"fi":return'"]^;;5I+N/#?lpZ"bbWfX"=e+CEdRkwT;n@H@%qY:*m0b;QSZpeiu[kve:mJLEN/GQs(ZMN"?T)_4_L/[(eXB13HP5`9wjT$<J@._YqX5qM]xxk6T7<vY`^$[G=jqw5mS-V6wtdqJ1XyJ7uFNNzksb@f<r<T_<7#)x5M8et*4Be0?WTQghQE^wuTRyzi6^Dh|E*yjbqz!X-yBmaahfIJpscv7F
/.6oAA%)l$CuMHwwOT3>D#Jq#jjf&<G*PNRr&g8IxND7(l#(C%J6k,&v;>w6<0L+Z(lZeA.5yj`"5#-<arO1<i?}l,JhmUVb!G5z(<1f!wyOY$hTYEmj_)V]@}ma+T]w#SL09!^umV^BVC`e^OU,x>AlTzDffY1{6|S>kU,[QSDK8My-"1dDda8/a{,%[u"aUwc3DZ"O7iPYJ+B"$P>sK?nJS2NBRcoq`:GXIy&!2H1YDbD!jT3o8G,o2(Hb.QJ}bo/hH-LZu28NA@U$TY3NN"2dh.&GqKc`)V8{%x$Opt*%^,tp$v;-x
h_5#=cOLSYKUy!Vtsz*~L-$-L!t6`7XD[Cc+dR)@P!qH5?o5P(Dz=]@L)/s.up/@Oc"&$)&#5kNME*&F$7"?<}Q,$p()n5!~Ds?IG9UjGBp>&_gU2D@:A)f&yQ&i@D(;M6#]@jB`NJd@<3^HH
sP:>46BtX.bpd^Z`/88;(sY7Nnq)!?XOcOBhJu7d[w5~lB4:(BMe!o8&]*wswj5R0YQ45|q*$|2X:aee3TinMt[N.Ld<ou[OH%:!QCs+/:l[!erZUaWq90d-L3$tI$%
xD<UeG1@Uvu,^tFjZ>%)f;/QY7"CVV^Yc5)_o|B@V?d2k,ET"N=fLj^xRAqUpw#j^Q>ktghks.2XPu`21]fgT6T04^/4;o3/aq%7gXWi4~M?NlM.V_=Bo&0$d<T=M&IWM>y9,UfP!l00e^5sVN%mau1V-=/Ryc=4&`LWtuAPYpA~03+?uz?~!^M(IA.6bx-dnN=76(*JdI#z=58=1?,k;W;7"jL&3$)!.uU<eb4fXbl!@0S`
W]Ukxo8>{ZF7RR^Z-S0w@<FkREuCEB#wf;]$k>l5p.$UylHb@^&
DBRIYVk`E,`D:ipP;U_VJ2UQ!8-.1!bOH@|PX?pfF%JT].5^.`lg:?)(M#{61Ie!UPDL)"+LGyLsk5I.!%raGD8a>J@`u8%.?Yl#Vewd,;C1n*jy.g1DvdsC)^^_bz&xznS
[!GHok0!`OuqO#8@@cuo67waUMQz&1>v#Mg3QE
6#O{=nZE%FquGA,9Qq>|
*VvS}88]wd#9y/dK=]Ps._|2)/5dnf~vwcD>JDTO,5S%eZxbUS}Y*qxy?cZ[#>$mwaA!nU"<s,1/$yMfZZ0KLEMKss8]>X(>_)<kh9gn$i@3G;aHf]VHwv:8-*![>+WX[!jLHw^[}0]#<(vAa7Ef)j%O-uf,e6r0|>=/
AjZ>*ij4YC0P.3!;loEtY<Av&pd$q2(h9C)<tG,s`TQN0wguXM8k@?h}e[f}o=i[-yk"Q@MMi)W%lFp{9txjg0O=s[1tn1+DOWmf3Wxn835y%#H.-msM,8CU"$^rm_xR*"LvBWeIUgm@$jXR#%/^35F
*NQ|_r"j]y&X^q,@6LK0`Rh5,:=NsON/:DA%EX*p8Q:YmKo93-#b3gn7.%idE;t7=2E9NW#>hx#pj"P5bURMNg$@/2X41-CT3ZeKtc8$h4/;[Yd;+KTR"nAoyg(2Rl9%&]+Fsn<KMpNo.(!XFxOxGEx*^&A
l9,t#^0di:C6H^MQX=tirW7nJ[UQy|ZN@R&r]+9^7TM
P-[4JKa2iLu2SnlQc:T7D/lDbC+/1RalpY`iA+q%l6B`0@3?FCd
!:%BF*EmwMZA5XvPs(6"xi0L+i(kQdQ!g_tNO106hV5*(dbdS/Kc"VNG
<ai5!oicmg90m($&V&|DlF"V8DDLB;Z=zA)5NSGgnU9P]#~hxd&;dDk
vqt>6$:WSa$[k"<,2&p4b+EQLo;qmwHor:sywN0SwY/b
CeW)y
_"83"?YIH
x=+!(cj6v~>2?#/Ff{Hy4xH6"^&QS?;)Dg-(S::j@"Y@*-l?8F)rZH(^6]Vl#igZ
8!z#6LRl
lg5I^6CP6HE__!VB3.hrM,IIeE/.8>ODdv_S8gK^9+LLB)Y5UF:CHHnH0fwwqZ2~NO^BoF>9Q;yDBX7|ki>&kCg];[gg=W64,%wuNZe?<UJ:*vXRG}-o^E7jih6Z]!b;_|LF%cX,GElK";>[Xnv[,jNA/yF`<vi3apeC&;27*Jt@YN6zw^i%"7.Fh<
j<&[M0R7jA^Y<<>LiTX[4VUpYmbT9?x].4Jc
gs?LD;UV;+;Jkal
V1U`x[[~/s2?):#SaIi)o&c>tu@I"+f+3YCP;EY=hQ5qF:60C|(n#EcS^Je~::QO7&NM79"`o+a.vbefe$5l`NBM/af;x*&k
|*6l.Y^sX)`ox$DZy;,txgq8|d/-Qadra9{*]#v-*)tF^ysw#fPGs.z$]4K"1>wl.FqKTpIJmIV4*rX`q,%l+5DH!:Gy[y&MxLyBh,$pz&2$fHG&%s
+2u,RYY"*X%z7h&W99N|DWutMSoqe$wxemJN+&/+GDh:eIj]NUW#)XF_A)2y4dZ/<
1:iyZAynIO*_)>f3>UQAIIeI2+t&nq0UN:PYS9k*tQh(4Kx"`M]
TJ":7zDOk{s)%kkW:cj2I5K&@bUJ3mBWnwA<.{dBo"/77d+M.~@r"g
?
627]t65mXm=^a?sMNm,vN&9%`
<b/a01-Hp3Pgu_Mvpgk3*Zi%>#,:v7giBk$mw"%a?p&02V;G7<l>f,:DQ0O^92Jk*<D,*_tG
j!]br8IdV<2/[)1Z,36>:&^@EWr.!g
Tx59JZt#j)U]DCN5}V>RkTSu;h-7j"BKv;*."F$aMs3/;y>g[?csESb%%2r?7l4Z[*&)@Wz1(8b8G<(%r,@lrx7#&S/n.mPC&4H;p:H&JO"4mR^=492ODkO=-"3ctV]36Nk"M=XBfE)(t_(f]Yym8k#r+Xt^:D
i(]>
B5urN-MT9]L-oNxSd^}o+:u?f2rn)s4NFf;FNJWu$yO82&;`Hq<A4W.
z(_2vL`yPJ|<YgtEoq)@-G{e,Ys(|!1i}@/IlF&=8d@r+9#T5l:YFg0';case"sv":return',Zu;:g~Z+$"5$iY]EX9)@TD[IZ&SA>Ka#@RB`R28r!T&y"$-8K(.WBf#_:TscN)J,TtV5b.o9%S?+@f*L3eMjjtIn>uLfEh=a&%MxV_Vv5h,#tAX}L/N&q|!N[ewYSH)rb!,oiUFMRs4F^mA~fbq,[IyzA#[z=CSz_mT(91td2Sho8F=u.!`I4}Pojg;rCP)|X~++ps(W]RMAb@a~**d&B9C(C:PpMHN
+LwO/dbuIt_%cn%&y}#^>E@OP[O!
[;r)=H$vM?y
7
Oh2S"Qt<6wWvU8/,6$=Zya1b)aJ?*2Fc)dw"CiyUF^p_5cv%=M_w(.{hh4P*4BkYH[6&/mFb+8"q:J;.SD!Xg#0mq]i]:I+YYe>T/7OV3c<`0V,<_l{!DQf9tc_I12m.xAPE)S[TW4TbG7+W8kVR#M`5$WPX:!e^b(>Ix.!</NXTKMUBDq)MUC=MkS^Ffn_B-e&S[T|xgy
R__AmCNA&SfqL084"e-C2ecX+Q
w2N6H$kr=+R7j+N![N,;%w*yW$VHB8D9KaC8onWm"JNQ8pN.`$;861)f)$9TI4j%G7R$>bw39Mx@SJx+HI/m.WW*t(OS0:cwKSE/"T<7i8W`49W7~M{4S,pmwK6+)wbV=dXG0(w`L7e5R5p(uQ5
.o5ZR8zA"(,N7Tdcz2ey26"A5$jc}%LHTnWYDe0gd3kM#)aks-}QQ5Ge%bs@t%GOT*B][$$b/kP?(<R#Or",@ZE"P/H)kc?*D8ZB[NSI`eQu41JQ?iXh.Y*4?2]6j3=gN
eGZArQ@4mih,5WRw8mnjBvZ#(J4fQJzEavL]7x%GmdUn$umdn7w4zn6v&OtG^lsC8Z"HUf.<Bp7n1jUjR>4h0=SR;>7mUy`$N0:DqE*W%6_OQ>a,lJ)=p3Fp<7}[4q!%gP{]D7S&z#[yaUaJ^3Zb2$bT:B+$mCLY<eN5N7!gOl&5(k[n@=eO.lLR3Ri"wnjQp-Aecb7R(iomF!z%_nP"n3bVPmMhT.b/8
501<sfQD12kaHn@D?bW*(a[MY7R%TU4^BVe+563gI^#O"d>jjz&=L6.Pa(l-LMAt/N;Zlk?ycmQcbcG
7hYOh2I$G>M1UM-[;A*QO?ZWvUc7y,)$vJ<P,ESD[bUJ$*lfMQq!:,A
pt;"^+sC$xCqX6W@1-W.YR9NDMQ`>0Hhkx_#$i=+PFH4rqJi!F8oGW)%yfo($!?f)-f5ycQ"WCv!?/INOUQNveYa_HSS-Me1
3}C1G,+s66XHwok|%+PQ"S;s7~T#kkv5d%)
(?DcHjc;mE(=2fM@-UToea9Oi,r95cm?"A!QCYy&8SVrGa*/#Z=&uB_^r&W1Q+@1Zbdv=5v.gIjnT>*"kBr)oi$EQKoV]1OR7A>OohEJc0g5tz7IY5NX/)>-9uqzeLY0g-[SU_@E=oY.hi0o9Qp.0o$mhB_[$p9N0L1;8nAwSu/OP|%=Q_NO)n2j9@_Qt3P/y
B@lj-kUP6A0Z8zQ%W@#2gfE+H6ww#Mhb^{,"a9CuC/oW*JjU/-%!P|)T5b*iiU20ixPe-y]
L1*.QK^zV{r*S/p,e%i_c`)vVd0`)<-r#L^p-5;KZv0MVO*Q2[YM;?w(!++%^&H08]8"#w=Eq.h{w9AzuW7Lr%byqZqgLWK"=MtI@f!`S)3|e[R(NQv%<a?$qX/z43e3T/$076*3ZQ,ko,:-$!.Fw1U).K4.WZ>h&QTiPzO0;(GiI?VkpW0<T%GWKM1f&fY[kH69#
i6YukHs6D7:!mO5"i?^+WjeDD`3iC*fvY3:u4`P^IU2K=xeK[Sujt%C
e>w9uL1R^Zs^soKCbCgULus$?!F0#;j:0>bV@M565"fFwbq-G+M`1E&o`)f}
].w]1*fp*`3eFr
n~7{#)N/.5ljQj-r_?N0/3dP"o_t@LJJ0,1f#&e&gB#;(Fq=0oAElFcy-JOksQ&r//ja8<>mV~c;I90nZmdyj!0tI`8Nj
PD[06loqk^3CfcHT1#quc
8hZk?ewYg":-ZztxJvjDD4PCE^&)&lFrgW+*b<WfW(/99^p}&rmLnvutX
B{tU,[o{NzfI#"15.KK>4^hCS,-"Cy;4E!SowfL;f/;3!>t^mET]LofkZY^>y;]wdh+v1H0lW}oabNEDgFeJVJ&wuyTd5[tE<uU:##Uq.6yM3[D/_YVv6v?S2{%Lk<K@*&Rcs|,si.L;-.^r>`L~1DPjIB"u%i+n**d>#z@asc3/T.hb#YAuGA^?_(X>M[d>KKA"K@*#8Ui4ofDS;p[lLiPNblds""3>Zt=zdJfalo$y%.fl_q/8/oY2]c$%E*lTsK>(^%P<;d7"!S%Ce,c":am)x=qhoWKW.Yu![a%D18n"HG2p
qH_mne;rB/bDu2XgNh[Jq((M_IpCmWv5`g~`kb|,lk
]0wd44PK-&9MS+;J>!]Y?n4sA">T2)n`E)bbrZT:&s%f,lE!88nGX20r(1Bvx"NL4?X&CG<SJ9a</0B62^n]xXK8.xI@AXh}:;mt7h$Mw}]PFXf-?a1He#X{5j;.QB3p_(V-a7N5V1S&4}`sYS@%+fr@t/N{R]%*<kw*a4=qke92(8TtL09xGOs!v2u{3%`DAj;c.|sL?jX+BvRm2!1ly"Szn<RIB(abedHidHFP[Of(5FCF[,#5hFe/h1[H=AqaO34F9@D2Zv67]SauI4yQ,+e+21s+kN[&t1)MFq4/T:%3kq(k"sDg_^(L1WoB9SR68~2}jj[y,`*G;S(S7+NpBRZ&Z7EC^2"TZ
UY1_u2Pqnk0Od&Iq>d8q%0&=Y>8}WB+&(Rr&mxwzm=:<nFwZ^d4AHm]8CX>l?hb<T82xGm3_`gmky;X49Et{&GcM>9dV#G;>)kNY4.7?Ma%+R6o`-Q[r-[Y$y,Eu[mLb>q(P&>K,e@3y0N*}8)jk4-R$6mrpIhC@(ZPRTq`2_|UaHDdsRz8NaIDWbWt~o)';case"vi":return',]^A
]@Z[/f7plTIZ-C4%t7pHQo##tp4#.Dpn6eE=!(SK<N3oA}?YI
YSdLOK(QC-TfCpS;x|oJ(<eF,rN#$fqfkW
Mn*I+$uw7<]l;b!JH^5M-
e6+4qFLCT681Tei=-?jL>D%6ze?pb^FhjopEH7f6uiPuhvIUiH>wmB.h~oVE_A2fxOwLiAvDDuTLnLH!XfL.V`3TaN`fG]uj8Pibm25N}p%bgXA@jdQg|wnq[-lGG3VWOv:DXwUHF${b,<(X*rYWr)H0Kr[$dTYlF,3.)CX?W>T:ERhA9izs"]idYlj$Jw4+lk[?69T:i^)W2<mkdAW
5q5UUdT^(B!y#=<Z8_sA1,JQSZDYdG)<8iK4)u1Zlx"+3`-p"S1$^S{t+xUGty6%]L&P}JGV0h!Te[t<#<Ez"A]vHwi
l%so{ln(EiH%jh14Cpmv-omM39}X|BFxc1+Pkecr[=:;^BA6g]tm|p*?qPI6~*`-*VRKiwN<+4iBdpp-2+$c(Rd>vaD(n#lRNf*+4g=ui9;>OosVkAu_j4lqm?oH7tW7Q*om8gLniT#vp_
HZCwZWshyU%ad
le]/0aFl16efbX>2Dm)4<h[fyiYX={!juz*[i(w;SDM()f*yp!:9ZGs
&OWgn.1Wql/[Y>cNRdlf]/pXV{Ptp1xq&T3$ga`v-}+j=Fjw7BJ/v^u8h=>lM4=av!+HYdjP&%*t,c_d;a*/q:Z^7W@Xm.D|b!?(l3Ron^gk$KgF_2;Z0/:EhNJwWahCab?/hkXr^I24B~Z/b(0~$|W{H{dTF5#{jY^h#z?mGe58ruo=*[ARa~>(0#Dg_tAg`h+J:QVaA*)0lGgBpB/S!`*rl_?W_N2MqF%^k"d+<[`;yjHz]4$(sKQw^J9T%e0wq&377U],
3HR;)Mxg,6?lpJkn?PxvB@<:$2Yt##XNUDR5{e%*U*;eCXR9aHPedHJYe8w.RmOLx#-EvgOK(o,hp+M<O"49C@ips=NQPp;;^$Vov8nMG&j2t1Kl0gw0lmh81r7)q%f67DSSC2cKE2YKJ)e:rwWplc4DT(>OCt.W(*Zu&`OJWmdayN?Y71:kGMjrFQzMrGNm;,Vb?/k"ZrpBe?NeCtrUj7Ph:i=j?omMnp[
SVa]UPi;zW-f^
dPobm(fp9H191-fS;r,>a"7UiD11vu]&4uF6Ak%U1Sp>py`8PiuK9=?@yDgH_dDWO:jY*)P@PANqFuTbgef?"WPH:;GnHZq)fk%8hKY57-h]Fem7wE"q+b2uH`WKteV_HZy4)eMSnA,cJgmIj6^u>Al._j#-k1k2OuBtXxz=cSpI2EP
AV
aa3jd6JxO$pmj_ZQ(53uhXi1#Ae#!I]0o!UJrTTv?:cfE<I)k?n3&t>n
52RR.SmyNje%{$|?r7xg|w-!n,1?J0`3B6GK.6?o![{bq9"RgCy/1q->1fRh=T~`h5RW--/&F>iDTK(cLZN6*g[pZ:aUJW
+&t{=uK4w3L4#|gOh;-(*F74mrjD=py$?js5*_J&/)Sjmt<Ppcgg3r<]oQp`*~cljSVQlS^_k>+Av2BGct</%@-b`QCL:>6.,GW0B,?9rlYbwVyfj17
r$[P>-Y5d[9~Jh$x;D:nV>I|g3LdZfa
n_
/g%^(;6`<N/qcW2X)5*rZ.}u>2+/i8!4K5+dvEwyJu)1-.D"mQK"k(6W<tw]MF}u3vr-ucNuKIq
k0,o@eV@l+9,zDF5~%bxOPO8*!|:r`,`(=qQ,XpOhO-b]VNUSQ.:/!L^#m#$Ti1wj](Kx)K.@6B$-0GDg8#;@<8%F#M3SdBd>F$H4rF/.trou.h9@:[,/H3.pieWXA!"]<~Q@Tzg2Z8[I3y@??{FSZ^=Tc0k$sg4,.!5kv#T[L&;[a@Tj:dOz#^%@PxVL47+Yv.%]$M!_[{fEPC!k?d-;*4Xu.h$94(&.>1bU1Ui^r4>3=E9uO$
@HeeGrBILbIc)$"R/ldbtC92-u_]whUQ[h/M!3gh0[GqbS=H2O(
eTpxl>3T,4;B2A%8UbbGj/+O]&;<JUh9{#RP2q%i@0a=Z>x*AtjF0Z[
`?}Jc%#V%w9^!+7?#0Nog2"XTp4#36bu`>%9-Oj&DI]kK@S2yi^2@M=-YQ7eDi
j^Sn(=%S_~Qin4W:FMT=58YECEYCnG4dqKo-.+9)-<Gq^$3,O1(-u:$&hh1$pdEZOKXi;T.658eXj
ZEm+toKQi*h.L+A7jci$La67y[Mz$t2o%;G{.uY}l;)*r/wLWe0dgRa+=HFfeTKj3TZ]wN.At}?0k}lS.!k].ossND[]"zJP?K>7`INMujgqe>?MC$W/Tnkc"1_uj,Ch@cPNUz9:u<lM9ESsm!o1B*uqxF(dU2*NE5hPH<i
h,0;-DE.
PsBvzT:^;3#/bkZxg6k6@0D"px,]P(h^w2oi&3r8pxHF1f#)15q?];ZBd<Z,,9DB1ZNf?.zPk-78tubT:a:*],$5E<Up}*a`@54[ag7YMU+FI2KDA

si!pr.WDGunf_#-H%;Y%x~/25!aS2wQFiXo&f7
r1l+D;fiy#FASz"0Kk
L0=0h#xlkTom.%?Yvyg1F}9"[KnarG3=KrjUJF_t7|8I9{u-oDEerit*(JCb:AF|_BG:[#"+rUJo=D(F#uln=
+.d^EP!Ub`%7e";062<aS(DoA<0W?joLfZs`H|1^0}EoFf-b!4)d`L`p)66K,g:dhPt`.fd+=LjhL_wvEE;t)RAf9,hf8*1x6g2pd"rj-#+m^_/=PwQ27d:@mcMC=.v^[6s:fAZG:nZ{+>?AFNuq$B8)amH)I3[VJ+7f`d!aI1^uM2`+Do0TVz`u/{nz&VqxE@^YcBxgtRYj!6s1H87]O!?7UIiskw8-Vf"zk^Zv`Q3+]QJwoBBF!Qfv*1G1KW_Og-iQua0yWS9&6F(dimX#SZkv/|bHosW^"JW>dJUx[k
B)jW&&]1a
P]kZ;my=J6YLZJ@kPs*.^PH&+BW@Y/3d3
<QXMP<iYSla?82R$QEk;uoJmKR@2!NCZulqh=#"=Bs]4G%FA$@Hl[RSj%b&1nO*[<YTWx!V;!]c.06YDEtBT`tLh(4hHA1>u1_~l]oG(C`>ZV!ebWLe1}8</ZCVNkvfZ^OUrWkq6{H4=eab2~GP80qS?)647}Ct[c)ni"T!@5A+7xg3$i1%+O.-0@kDj~0l+ouBZCq&99a{GU;2]Imx%IkqfzGZq)=rEuGP5Srhmty7xUs?hq8W!CYMZymO9ul?
EEUD}rt@yI<TCK%:CTFZa./Md5N%{n>&>kvYS&=DZj%s$o)';case"tr":return'*]^@aaMDY(nXc={)5NRo+%KJs^tGf*|fpEs-1-,-0xH
b<E5qYimL?8ibv~7^9pKyu%lwz#72)Il-fnH4O=UU_!=y/
oqU/5j,(RB<5;M8`g|WMb<OJ]|Dzsq3{rp&5_rMr%.S6odXviVk?w|)[&/k`nX`B@o=<9:M2#e6ey$SPcJUFBO<otz7sK&Uz_fV.tph(R)9b644x[x[L8AN3j<q,lEO<Vgx"Q.h~t4"-PFYa@zol1$ykXM!hVqGuwBIDk
:^JJ]Sh%/6c3!(SNK?OlGhabTx$-gV/|1L3qr]Y_TV/h,*$mk
<K<t=r(?_58qOf_Ru?D7d$FKA:6Gn#Q}[2[}MSLTCAJ[_l1RmUw!q=/c],0;/ofL6OnD%*ag41?>tKd:xnb$rl-}yijo!^EGWQgTu8F_e"^%u.<)C_??t?fF"MQyY(dv^7.>DFi/e.pzN6reo;dZk/To*Sf%l2ZI^_*)[}aoTaaRKfG}rXsU;-sWxb"4E_j+KJx@."#L>?kr<jW*gL>O_(RmX&r>TXlz0TJ8,Ptu"ztFt|g#p?J,Gt_Q30O
338gh1"HST"$&OajP`%KqjcVfJ61Y,7a*mP1m]J08*w-/|2Fpb)p.y/y?c=2PoaP-WCSvXsxZ`!Xiq&x5@51xp:8;*P!/-T}!*3kG^2WqgS8
|jf=gR6Uj.!K?lUrA,9H(?KQDW$o[aoJH]Fbi$]i]7ZdYL[k$jSe2yyXY7vIsy~4&#VpC4fQ$U-I%&Qw&]cR%Y^)o`61Y=+BJ50!|XGK3_~
]DDCpg6vvkr+-c~$ofnQq7%K_=8=DK$H&oj!yi.85j8$N`1_8O9s]=PyoPtbhbRP?P0mpN6cu]Oqc@]xheR,_$Dt_>o3*&W3>f:p&[}%B5$E&lL-"MvMM*pijD(f>Gx[HT`?60}5DXD2$Yjs;HL@J2I7IAB(!7DV.eAn]Pq**v|,BuNXp6E9ynL>lZFr+Qoio)4LQ4X_A"XS`F^WRy}Zxsvz!wywljAWYqoi6cUA}#Sh?,jd!
sv4jTL`,LKQwoxM^N5v[3b`6"^2?Fi=Wm-
B&rMvtU"R!PvJ|pB.~G!kP"VwvBMNZBLB%KN%_*TT_a1L}YU"o`m5SbT@wauD|0@Y}#+rjOAwo6AS*1lf]r9GN?gd|_av:mrMT7=#]BB4W%_U+k^<VDCqnf:3:lH2(]*
SU3nO(Rl@G]8yftYJAH<#6F;lyyvL4nuve[!ia7.ee;Q8+u>maS4TeUMV$p#Gh.fwxNqDNx!)"|=J@:^b8{o$d>Kuahyf-<R"%h@syu7OGQE;VK(OsTAhFtx@u5wh@#8?nJ
WJGf0h>7l
FgOMXJ*>}&],R@taot2p^-7iYtPB0tGc/&/i)j7%!#fy1MRLX+A9{-/2g!MDX1qh}O3oS5V3*Sex8F~JD$ZP2Bto,QMA~<haK!#<)*O)(ZG)rz(LqWSyi-kf6:yi9B<8Q%,FnCq%!hf+^u<u.4
_/;|TPPnvefb:*J~)iiL[7)fp-ceBEo54r!+353EfU17<.-.d{Jb+e>,YfsN8q6*7P^f;2;$2v-Xp&wre
Xz_
WveCxvT4@FG3geLq`;CPWs#(<<V`8e(BduHxmZy:$DgphAvVax17QjhMR5G|UrRs!`gTWQHE97CYDC?UI#i{:<ve;S%(#!d@9WGp?8d)-mvjSfQbwgMc=^CW.s[s<8%W8?<xtRb(GC,SnLUkQG,C+4F.!hXv?zAN8QCYVFsn`5%Eg:2r=z0N!9
:#!V@J7u,UJ0<?C)=ZaW1W|%yu+ZnfF>C8PpOC5S%n5GjQMcUuycUWgt]N]pEe#w/V;4CX&JiMKxRZ38nI-4bxS,7&[X!s2nlPL2h><=Vd{;(;Y+g=zm50OxkBKiuHlY]Dyo1MiY{7Hy_Y+U&V:DTwhii8R#1(xZH4N-,
K$1Nr.wY%d:t?8]Z61NafP4A8wpd3?n5m%,FobY9dkBG__~Lz#H%_o)Nzv_v01
wGX_h&])/FRi#hKNy2:
B@%2>%g7r
:Bj7@uZ;=63*>vSxL
FJ4IZj3o@"UB9GgZemJ8MB/V/*rI%?R*8JPS0uIIEUcJ,LF&q_mjX~J{2*ZQ!
uGp.$FvVgF$oEx[k3;87g9YySdgs8rV}!QpeOLMHlg>o!nNX>-r[:xke$_>"cv?L?Rr2;5Zg9_`gCk$-nRB.&mySQ9]v
[F4*xACN(L}KZtU[E]jqw`~:d_{U
DucaHkg9kkwvL.9r01yM$wuy@qHsEoo=
;xZxL!S?RYH;j83O;MM,QG(kWS3!NCo%,OWG=X2G6!/X5#crKCp^9ZctI";Ql<zB)Y(NjNY:9^6KZN16a#;mu-
y~%3MtRNZe:Jm|[9xP0<8:)U]w
7={6$lY<GA"6rb#wE@]+`d;LeA{yT<kGivo<~RvTAC$VWKAR"rz_I9S(M:R#OYds8?Yb(Yc[>I,!o.LyHvL*D+fqtVN9`Tv.~Q3DrJ
k
ATm,Y],Ve~c?BZx<2Bga;X97
*+~t71G3swLBUSIGg.p>.WxdU?f2S<wr!$0k,Q<HvJ$[o#Xv6CK?eT,C9H1UoDp%?__iLg7G_>]#S&jBk+RXqdyoTG7>A_%[}`0?s(eS/j`WtHhm4fhoD4E9il{+!&A^:OCLk9p7vVt:KIkvy[J*%%=0!EkJ@hX>P&[c:rAuIjYN}F!Cg:+,zg/+M
*t9KB!gi}1X,ng4NYQhb*T-2XguR8C@.|Q3P8IHi|5K=#`OBnL/;P&A[QOl&^A`)
D]*4$.gI5O.DM.*<I;A-UBW*DE>odCP6ei`UyGND65:S$1Me;7KDTEu[$00n3=r>ytvCht!NDZ6~X<_q4&9nl@3oidKlypi~9BHfB$0$2uxEyP2CI|9p)&6s@Mk4dP
?sj%7E%o@-3D,dmt9nRQ8eRDO^<CJ5)7!*d0(F^FH!n]GU7v|
z_BT&-}o~rNDq?:#tS)Y}]I(~4]3wJu1PuO*.hFDIIKJcn$G2I?FF59k13qfLEu
A]&-,@KRq4Nvxiv>>YQDpVyqIqI
j8VY3ik$cDD5vQ]H^8505KtE"B3A"B$`wD@IU3/28Zy>7Kw>WwVY<47&zesaiv+1jCF,}1vFVuj1PBnH/_;
I#B+^V@^<,!d;$HZJx(^@S7e#ycRaOV:.4N5;Ak2^k27[GQKsE{4GQahKWty:qdlZY"iV]BX299"9
XV,50C7#9D"vStBYz;bF{:V^|w>N`-_JZN75ly:wA';case"bg":return'-ev;Bg~Z+E(5$i]
d0OCy7I)
.0^<O%O}GL0u(Lkf9r77.:[6DK2rTZ%oPp^0)z%Y3RtOc?j5c!"L;/^/:n*}w>MbxQxVD}_gqimCJ8`znC4U
eN5snPGm7l@MxHA?Fn6Dt.ur8SG]*;"A^:iMN8d!~F#rL1*>QjYj!A,Km.7?5$c%Fl7TAfY9FWX9c:Z.+F{;0IPjx=7y8[?LIiTL6ANZ
8Z#lYT`/Hl(l2Z""B{>6Uee9"f^WAbh[NUT6uo@1`!"nvwhq7ec&h5*r;Ggn6nN%hsl|VE6>c`_zJ]NfpQe,IX)rF[8Ko[oKXMRbK;]<(fEdW#%G
0$Ys,H{v0DzMz;@O0kueSNGt|_-/E7qCfpDiY($*R0p6[b&v<j%qRG>,U5YUokwKcM8E,W9;|$T]=c{l+jB;2bV/p2jC=Hsx>wCLQ)W>T+Yi@&AvuTJAA^~j
"U-iY.x2pLo"^a,Axw=7>%lu-p7zCL#j6OBx3STW?J"(E_U*`;dk<-(*)-*klV(~K=pbUVRdC2plO~FbjgAbs9y3x8iY!@)S
)u.-un{5gLGN!/yD1_,J6i)H[#n]!"rZ/C
_Oi6!^eDl4/F>e"}lwi^[DU}H?Q%81V,O<W{6pr<!!vncedQ,CfrlCH7t?V{7!O
01G=?rM+EYE>(SGG!*aO"M7I5
s>%hQ6=mn`DVDL-bKX=]t}<0dU$QF]u/c~Lf1Q#{.,.3@W7&XgIdt0(7&QuzU1;bwmdUf1`*Tz8m]K!A:!G|x+X^
J`@Ioy5eZVfP739m8i^A0d6
@dlX,&m:lM^6Nb[?TodZDH(Z>MK2]3ewUt9Z*Ra/D8<lHgAW+1Bi:G}h66]&0+c3v2mr+s"cj-K3"sp"GmJ>h^&R,g$Q)o_WJbu!@.2,1C2T]McH0Cj-]7+:_!8Lfb7]5D}Ug1`$d.zWgV4#b3K4)])YJCI$vbW#_cSg|."Q59L)Ug$e1C(wECPLy.A8|:Kv(J@%|WOoxpMlW5>/rl/.J9[f5khi46SPbQIv_K}DZZeCiA$6U;4
*;ePzR-th5J*_M8I[iZb(209E&y9k1lXq@/A&Z%o{4z>fkpj^a4/UD[#b![&+f5c{Y)ygjtM%A,TN3%E(J.=37Cx%nT:?D)NFX
oe.0qmIx6Vjr^dI:,F;eGZgTV`]]9m/^i]n
Wnb+TVuT;r31020,pR^]:+9K(F4>[
hPaL)^q)wDeg^iY^),oVspZpb1Y`0&aa=MIX,3!WtUQE"afXk
xNo)0jj0k1:|CXU@0RELM@DVhz
jP6FJCE=<0,2&Cj/Jw
_{>,:kqj4JU]@
.&Vk:rY@5zP{;8+=G
F.q3DPFbG,7NFf46Nw"$Y41s,&qs)W<S!AM519pTCDHxx*YibP!F5!;k(JK]C=ag@k=`1Z*Nhw0*l{_Ct0G`[(-ge<=5VKJ.3?Tt#9i#;S@0oKWIBN@>n|pT22v0iN:"xf5fb8!z,~w}S]Ta%CjiMJlN5*wf.27oC>#1@@n^!rj8Ost,%"-Do{[FuhiGQ#X+Zvu4tf8z(B-,?FoTvJ4$4?d+yFLzc"(|%^JWTdbWG1?0U1CEyZxs74y}PqW[:HO)Ty5dLe>#o)]WffV8Y<-eG9R_Ss>G-U6DgzhG#a
4$HZ.K:#98TT[CO-sv7PRp[$>u;xgO>#!w6+V#md?xB3nAWAGZnVg1[CQD;&OJ;B"t?c&Gd(Q;XQ5cu76$=d$RKb
k{?>#B!$nW-TO1=0%i82l>Y@Efs>Y-dVdeq./{;6Ojq:*!0!4G
`;.Q(qbpyY32;/5T6NrX{NF?mED!+lFJ?pe^mUo4Q/)VUK(&2ak,z3z=%<`8!-T;{G,MULcP2&NwjO52%acTx1+v.rMpb6=<m-N$&j
(yv3Oa_|#wP|2jv>VZJ{<F:2k?wSVi26ZnR6/!3biQl950m}`ca,oUB0j::Sgy`q+6Z48MU9N,ToYCheD<Uv38z">cE;wY>qv[E!.@$(:}Q(.34$#Is}@
KVap(dIqkd)FZ%={%B>J*5nUq-2gt|%lt4Y86M-w!kr;`L)(I@h.GCGplR-<gJ)r>{kc]iJGDP(fvzXIAG+|:WGSMe?^r`f_`zvV(Eo@aj@w<nrLh.Y
bnEw?Pfy-<^3o}I_M>
a:RR")g+^G]5fm?e[F}x4b0WCs8[J50wvy9d`^5O|@&^E(B2qMET-npyQB+7|w
Hupnt*g
1BsA$*@5D0BCu3t)6HqFe-hwro>Lm6l(
4w~?r,`a_vGy7$?OReyIFc)^(Fbfr1dj>Y
=
LsA<
aJ+5]oQ=oJw@7Ngqex/6!(X8R9j#esO[QG
Q0:%LkKgoM4Sp*D_s~:,6fx)e]c4hY,Urc0kJ]kig,_Gi<p]OM!i+(eLd[/c8NC6`3-f=r7r%1D
[g(lvT>_G=QyOCr;6!bK79@c)nf{chRQvfQ9XXYGvw/&xH%u,9/*[LYkc%QxIb8?IKyYyt..^pr$17J#j9Io7;-Nh5_0WR%V=~LN?cGI5Ep9e2g+f&YSkE;D<2qV.ZY>sT>?V
1G/eRV_3&9xLCRav!ysH,fs:VvPNpJW~G,=Ld0q>Q(bJVC3(lEk~#{<]E$*{1;P*>1x`;Gcc(32zZIQqYxJjFpRO?QX`7)JPPXsXXeTu(Vur11jkUr(^"gg<8u&!0bVR_E4>)"Sj*n-3o2W3C8-R`:eNr,!d[7fme0(+3>I#*L)at2d1PcAPDk@W4lj,Z=11_HEc3D)+9>o^Px&m+)WqrmE6TF"sH!aodq/!e0,
Ge1)w:<+RNG2l^(B%990McfdGuZ2O[9M.A
.XDr1`x.>D_A-e%L~%~
L?V63w.(
gQ7AQIylc}yaoEJ$
E.CCjO+PfiRv&+W=-L|%wi^9`]I`)D-]JxN0<6O<fuDE/_;ZROFh#j)8H/xcP7EYj/j/S;wYqU4S`Dx7[t+1P>/"=&yQ#kA({F:5YL!1[397/s:u^W0t^,7^$I,%YVwY4FVcLT>or;O(g#Q0DI*FTJXJ/
<=.3^?a;Iv3FJ&)G8,dRWN`R+e.S_#|*E[$?%s1*/fJ91j4dGD[WNM"!_n~Z&>4(CA7(g,d0CGz2_GJ2|Oa"gu/&xEH38<O/D<7g+.i^@00gVCB!uW.xTI
A&*Umqh|Xqk)46q}S_4++o"C+K=wc73#;F]DP4flj5_KkbXYB-$m*>xMR3<hgj+b*Ejh0F,)djRG=TNnI<_i:(`fG?t92%e/M,ogHHqq9$".<d<;Uj[wxF"w.Mk(;*6>d*PFgRx(?N#?7VgGCOTU,s7{-}77pid"T#
t295Ys.5:At3*DIW&:KP0rS+2V/RvBT1|T
6o
76^g/M5EWI;?Ppy)z>.u)nn:FPE,^.
C@Zo4
Mw?e[^-jAI?Qk#/{"afVcmh^7h9f7>dAm14J7ExDVUIpG>P"BBgkL@KXTRq)$3xqK6h$
m,$+c?7VpZRr+TGlTWjxP-G!M:7V
A`oJGe=P+^Bo)lL0SUur!`4+@*-:;aak$CLlVr@=hGOZqjf:NfK*gjw`5)lW"VB$%sN+L>Nq6LI!QkawJpx[gZlKUXiRRp.mT
+I50H~=-WmNwlx"I>
V!.rUTR%@;&xA{CIsaa^1hmU.0Q3Cjx)YYong$_(w|hOc1MdkzKCc`x+&AHuW5gCs`Jt58-yr!slX(yF+5J,AL-K`#0cChISfs%7qwNjsskx`|"e3Yh!1I)IVpv=<|Pxho*|?+mX5mH~GfdADBWFo4RGPi)P:8aI5XB!a5?X&>j[RObbE
-R[F2uO-qRE
E&+xDemS[{t_u@L.,?LTyDh/pb312h.m_)scCllA<"syNnOhTGGlamUo,&(*s7up;rVhBZRF6"fWYNs-%GDXFto-:$`$mE9}D|jCs0RAI=[=qXKAw7?oqFHEqQx[1dpi:n:JPQa^xsd(';case"el":return'.h_F{aLp]B|GFv(#U7q-R-Y!du}s9$kBJ^;3;:pqoq?U_(*_93JXKus8?DQ/<C|=p?&s
a]xYEU
m?GA}*JG2N-pc]ntNwwckZu/9SNp%<K(j9n4i1HV4[:;CEDk6U/d]sj(
fdiky"/v
vFOUdBled1n
bn=OnZ9GTlr
L`ls<P#*OgE6]?fctKkr$uGTVq+vaC:Vq!7S/N,flG{TyVPF);ikoEF>-[cF~kQqR:&j,LLlP#K#3GP[qI9h4slq?#s6Lp#p,!V<U#i0irO$$0$AiqG=07{xb]#tjs<>A*ALz51U1W.hW/mOU5+G~#_%)876#C/*)>M=Xh=r$%K,,*BSn@:8^&g;5O"j
/c/tq7Fn9C"u%hNs9?5kE3$8Dp
9Q|*zITrB7k33R_5Ged/uWD9ypK=O%rXrT_;u&&g(-iy;sHq-v9Dv*N.G[2u(I/$JZm2>o*ckb,dh0O3v,3W
ez":2
@lA]:[gQTPME$}E3!~RN_=[P#0Jf(Z%?D=uSo36g00okm%PuGocnmfDVLMo/5J"zZzK~apsu#Nyh#6*eE}Q7"xbnVL
C0XW~%>UZx-s`!Q0D6e?^#(4Ay.ZK*[^
Pki0]&_EkI;=k~P"2PEW
CYNNpcX,Z3UUw6*VMcI1p/M86J?M[?
&@g_m<hl;+sh[^ZMr&`:+2bBI8A8NmVo/5QRiaClaM/un!tQ>XnLKI&gN~9kP0Xp;iZk0@gCainX#g<-M@[DG+2B@{9IE+:0NL4`3xFQKG[.C63Kb1/Y13.dQD%a3C9GG+R|R-Tu1>ACeRlUj[7C#H8b_qJbq@7KH^RRV`mCE@=cXT7p7
SZExEOC)2G,cLAg^H5Ypn6X`MiA-T8dBcvrVW19TI)2(]b`IB!P&!*^L$pd70?[8x%Hx/!h
[IKn+fS*]?#K"0He%~M_chQT?tRF)d7lKI&<m)gp#DO
bew|iL,lD6F01|VOK[FKIo6$2W@k&+D8=xu{UY@z:6N<k@8oLYYqQ
FvUkckfqEw5ph-#y>gP+n9E<DQ
&(r9mw{]w*VH)ST
Pn}m<PsE-yo5QiDSaXU3;nrSxqwGW*gvyFrO-PuFI3,iS7HraehpsvK@SGNQ*]K8WykR3D(QueTq,(}/u9+UnCE"3Xw1J1Xee!A&P_`Kvt4a;tu6BTW#}mHEvI)-bTIeWvDa)XF4*>mRBV:Ud^S!nZOqeHXmlM3n<kfR8vS+GEDH,=nc%T8"K&mN=I[Oe)#&=-hHuE_=>VR+#!ZLrRmi};<#Hf/t!z)SL"WZ;#Ac9*~V|GbZYg2j]hd
p.bSXD~=
Hq99+1woOy<MXqT7a^K|?V]/kTb4+`vb4DH)pB+mTC.I@llmWr1TwHPYJ2q&`8;.2##LLKT@:l;<hS5YPlDe-8)>He9X(S#Yl@Rtdhl*:_@@y1h[_:QE36$2tflOZ[Qo"Lk{#d;qEB%yVMeR0xAt@jTzi#1D
VI:pH;K@%#m1{5c8(%3YwN.O?K{ew#.Q+T]+KMBGOux;F9,qLiXZlt]b81dxd#!^_5o(1XTK3KVff327L"K:|@?JOwqq,eL-8Hr*rrwA0HT8GV+8Vji3~NTnJGCNXU-"DAqEpR:@R4$#*ZD7Fd]p1tmpZ!C8`MG_VpO(!%xNuaMUSKE[7<fEYOP#dcT*8$K/8xts|O-o
,bc$(B(DlLx!?zN8nw>N!qoQ/wo`Xe8=1f#G8V1A]SNhEUG>C%Z?3Rs&o9ML1$>tFhdIb]^~z)sntj!Ny9sfcU/_xhF9^ZjFN^]WM<HiK
Rpuw-g-^h<<M>QHcs2?Z@!9[QqJGv44xrv@~)aZ.tQIL.R*=fXg%v+sH`DbGkuG}
8GG`0K+$]^5QfOfZ,bz$0w.]L
e*8jNTl:].mG<mDv2rehxd0oO03QOKx?%[%*&@v,x0H[O>8T
#
p]dIVNdLd^pG5pY
`Pvx+14>qA3ZJA0$v($(iR7<b|Wv&18Mh0FHHTUs7l/lI`<$5^$a.q@lWeGD?(1_,Sw;l>eHgZ1BaM_;$
Ui04^g+[GG]WW
:DCaCG((%w-GO)%f;jOXf?C*
<ozfXIYb9?D+|tm/YJZ*k;<*gDl[3%wgP8CvO.r!cUK=je<BCK_d!K"`LER$`pdI9KZxVAd.)V/f~]af:]=IR$`;hD%6a=Su_LyO3Fy88^[8p>%.S#%0XguOctu511a#gVRT@4s?m<2Kt%0"gbfn9F[N<<ii5&Bp8ID*Hg2UFZ
C,!L:soMc2((ddtLS]D&!egn;h`Vjg9_,Bw;LYVe*>S_g*l"$N%,[8y3t|a?e
<=pa.i+iL"
"ndK_b09Qbjcky>#|2iEk@:sy`^3lT(0jc^
vKjW(3B#g_#@@G!:Q
#;8W$hUp,>)RfZ`me_43SWs&SBH;5#xO(l(jYQ^=-$g<m*^pR;~TdyxSk7{L^M?mmh/T0WLdaV
2Lnih_%6[&Hv@Ne@P<0NbnuIY2^E^^=,37]Q9%;%m6MS&AbRYHfY[|6B5$R+uVx2/}i*):FQyO?=%|5f[H=,4;J<^.`Rb+CS#8@/#mO12+1$%RILSueAv`sHy/1y!/2mWr!cEN1Z</9j.L?-$l
HfD<Cq%K6)Ww5jw.#BF_hPf?HvcZ$agLJ$;>Cle$~A#P*kg^8`NKIZ0UKIwb^cJ>#`>p>22]9m+io91hOKsJo*!&ybeC[#]]>LnQ>PL!@s;]NlJc&D2>?MGX@hc*J6v=l*Y!B2"![d$$~n?3Y<Clt.0B,6]o,tH2uX3D_jmTlg<LRwvey"lGzjggs9,SEXnU~oFS{8b/ou0sE.[-/uH@:on4$e*BQ_Ils]SE#Tw:q19G"5}Z4,*.l8i_|TQ#_bfs)2l+*fHhi/pvq"o
VNZ6.gkvOS}m%mM/OnXkXJt<nc^VUyMy!u)uphsDSB"UY.:H"Ri/Q;WtLR+lK8YL0p+PzbTc)o2C?j]gvxW;A4J-ia#)pap$}6l8-fz(_,LlNeD%[0`E:%cE.Sa6gkk>mXE&cHa$OyJ*`2kX4iR4RN:Eohycf5U/pf]2*A4VibQu.tWy
"XMQATdi&x>>/3!epDh_9-H}Z5I3"fLX$xbBVKA/DYbr<80K1(r,m&b*@MI40Xa(Iyh!4c1]kg[yTui0vD)m7WqSHHc6.aG!/KFmQ`(E)Z)k7j4@9]f$uwFjs1msvIlyJGGRZHH(,1fS@9g}MCiO_rjQdQAH/.xtb0La2vi~q3L>X)=cac5N
i32WF
Jp;g<o{LK,7(47faVE
Hf-z1)*y`~2]malw>C6aUwv&AgWoGo+{PEwI^dwAh}noK
,Y)1L;-qy!]{EH$Y5q^wK]i!CT;KL3a4;f6U8Q(<!!B4d&n31nczS,a+^hF;DdP_P!q"K<6]adn7?{(k>|5JJ$iM>4Mr%q@zrIcx6u8kiL_D`=0@9uh|`,Ju_3vb"iOZ+u,}@:4Jld!]=zGf
#A&Q;I;iO6;+`f7@utd8YjaeB9Y9,UJ]MsiH5h9c&u,[vVrP=@aVOkl%#HOg*$!XzInJfpA>1^KoZZaGsJyV_2Qd#x[ZTKt7NoeK*n>ec44tJHQ,""uxOePZV5Wlc[eF9q?$;wnkKp@gvUB<iJJ_!aIf)8xywapyHq6m|%
UgsrXsbS4kb
sA]qQ;GA(.ye%];kI!t%9WZi>
Jfsous<[JUS0Qh:0i+!t
`nwS@8BVS`htCdbGNr4x|iYQNJcj^`gniCdsh^qxL5sVK$7EdBC1yWtRB7FAVj<b&3tfhCv7]v__hM0n{-8:5",C94X0Dq;8LmB5-:_t!d7d;Did&0vH+:bCyk=Rl07kUt?4An=EA4{g~8Y,;dz3@;3wt?h2V`=Un<<h[N=67/CA4BvD&#u1,>Tu?s:*-,pH@6"sER-.g14>/-M"2fCaZp5[V09ssQUW-$<s9rf5k5nC1veAdX
0=Q,&n_Hi_Pz(31r!lUj+_l[Ujv!kw71t3mNw5GD81oy0f#Rpim4L!0aYpj/<myfPvW<Tk]zi/^k`S-z6E(NXqZ=Gn0sK=>9H28e^tgQ2P+$7Mb`^`d<h]kpGmGSJS&iIbg>RC6`j65xO/_eETdZ_6w]T)U7wn!qf)&|=WrwDU0wFa0v_Gtjd6fw%HeDQsh~MnPwJ%N(@>5eNDE<KR->dQ[Da85O51;rqd7wL1Zua|K&vQb`U5^7w%P@tCZfUuxTac`iK)YWp*KF_5Y|2{Mh2FQ+-&<)3<G/b_Y9.sG_x}I!m@<INK,Ul."D
y##?_w4&aJ[+7Ap4Bz#3@Fkoq>.`cyRJsmAByJs%OF&PbQK-7sbt;0_gyw7+)kEY`e
/"/3f[9Rx^@es`B.;yvRt034#6:%A4J5=q7S7LSLN&';case"ru":return'&evLUaLs&+YGXlT&rK#=Zm<+M@PcB&>MT59"P-t4uW+k3Fb@TG};s6GiyQ#@F?+q`EL7a/$2D)6/!HEiVE]v(<0-<*C`+=.7Oxsf;iDBHt,sj/ZY|IqGdH,yQ
&hhprG[,9/xu(AUL{G[EE2#1K
/E:+=cwng8]AXcResti<EUA1b?|<fp8egijv{3yx|5()P+?lun_4B)VFgxzF0+`:=MhHS1y5ApjHm`a_gHO10p+g{efi+7S0)nln&#L%w2%9V8ZfbKD<Vw&skjEW8g#?bEPBsM^qlSjtd,yFyq
$yFbMJ^T_b9eC)(fNiI<5S--N6BJpVk(xSR7esS=f|cd,Gol_>]UaO8Z)c>UexgrDg+KCS[~OrOt[YDb<.i_E#,I$SJx@Y$/s>
n$^Fo"-h!sg;2Z@AR:-*,OV[!)9(pjy11aH:zC9hfPuDRFZ<]k9D!+^jd8~"m_x2Dvf<U7MplPwEy@/miTIU|o|c/50%+A{&+Em<zq0H<i/,Y1<YM(%;WF~:.FYGfywq6&-N9JtQ1*&A<b~^5H1`{Cvj$V&.m$Eb[FsX2pA+8`j7h)Zq9#=GacvFfEEQ?5knpYhlgJGsK08V9d]S*;#J"ee0=$H#J=%B-(KKIPS-w7[pj=?-,&
_;8tXDeC8Apm?8MgNOK`[Wle>s@Oh3(5",Spb8TXJaBW)68,,?!^2o(>+jmALEW@rU:[VN2bH@7WW)&BA]jm%[O7-4^bk5xl
@eqr4u=W)s(yPEQcy?H0N!1`A^[(?L:e$6X.5M=_^ebc>Z#vD=dpTSv
1gbO6g_o9E@pHX:K:G%Hzix2eG`bTL}/tF[&_FSmt`~YoOuny4EpL"C@uZT&jrS9kpffQo+qdLlLj8T(#8+2k45ukO21a*9-/`Fywh4F[gbVk<e,Hq;XjAD@1=^$43emKS&+)Of"k?/=@*,2:yBH:0?Y[Px]Y]l48ImZ,_o:AHsMR)(W5g!,!+_fALmo)>dEvb/`Cv{0eha)!
kg8`5;?u2#mGD$b8D$8Sc%g8+7e!kVf!B>b4)fQv:+fA>+U`Xf<1%nFK?.e/,SXO8Qjfp0_5;d2T7Ceog%z[>X~;j"[^k.;L&a.F7Lp-2[r8I#|Nq^)%QVX<`hL@jR:9iOTylf-=K*>6R5]dS9-k5VpBNyRv24CClVWx~]8R?i=v+bqv9K{p#O*#`uhqY4x]?#;]Y7[p3Y!C4>q1zVZxeJ4Ti#CL=jplj&-@Q0!e9@]3xF=*+B.Cq?0)psetQ$bKv+tE#Y5+V*35}w,(d"8D[WFr7/1L]&le8q,3XU,`^/uS*lTqR)qy]U-WeZ
cs-79`9PIVm^gdT:M/cRL$+CP^Iy"=qi.PxbPh8Ui
u[0M$(5V0%XvjM?C"B?SqP&~/;ErhsSxMq=8R(%|V2yzpso9+gEs]Nl8aM(XK*II&RN0N8s=KuJ:7S+]cRKE_SU8Woeg^Lo.Ly,RKJHD-`l_iiE5qrN~!|uF>(ADgf7#KMjyj*X!C"2m3xRa`DE=9}DT2sL/`8W9j5mJ)]*7QHLZ?YdkG8Sjxq@xe|4OpFXQLJ"{I=*R0.Hy

<rV.-J9R%.A"%gm?ut[aunOK!(mf@M2v.:;k>mJJ*uW)(+=l*y%r0HaC?HRw/#H._OaIz"
=5A)<x(P.%|a*+~eFun,Dl<v%o
s}:_cLloend5h^m"sp25pD><K8!U(wP,)>R]`/&$4D@&Cfd8]ld92T%WR8p7y:b!qxy]ff"2@iC?R~U15
AhJ;hCAC7<$Pf
;.0?#l^&Q6=}$8`T4r^[3a+G20Txz(B+^a/wHvM."`[ks*<|
>vOZ4bawk2M@*lk53!ff6C8-I9|0@]X.D?za0;sPhb!oJ=USr>6F9.FX0:_4l#qj`@gd?`+<
%$;6:qy+fBHk9.BR6aW<f8TG;!-1&g%D6;[yO(*eP,3ZE/+D%D@0.Z?S@8E
G|#>#VdPQ5K%VN<s2ER^e/eD)$/x=R+zIO^YPI#!X!u5b,gG)wk8Q+upGS]pN^q^S.A(Vp>4O2Tn3c#)@xoOAS_O),Vb=-0_"nWNG"$WAFPAeF(H%OCs->3bU$VT0_VE=oofgi/7Kjh|ccB4
<D:kuTU);"};5n:`bu4A5:W%IZ_A&QR4}!/?z(3]ti)B7Ot0cLMEyBv%>LKgtZ#1IPgOr8S&:xF(5OmL,:^[0wct+Q9?QSN0`^14a:RdhszN-
(bP[tW,!T<DOU$?&676j9"H"q?6<PE7I`dIypuI71!<qX0;;CD!R8mvB
gB:G@3*#/J
r
MqZf~&Wp3q9d=/&kC06!<&CFH+K*u3&qw>LpaYTAzEQTbrcP`EhkoP3/.2w>6ssQ%J8j4n*G?GWRl)E0a:xfDUyN]G_SD+~)FfF`p@~.:9U"[O|=;BtU[Z.`voI6#w}@*]G=J"P!3U[kxm]$1K<Tj]#l{-#lFc)fHU2#]%Hw6]=&U#/KrAE2pfy
jK+CO!UmF>{kkeAQU1"etC9O^sg_N%1k5rA+*.T?M#k`yOk+kVBSIxydX_RjTW4oXRJs"nfkska0{]Z,#YrGU.N_fcLssNZ1$,n0oAhBVbK"L%wW1)JsAw.OA(GVq,)0CSGL060rDbs
F7a<Tq5-zN%7*9jh|?+L?+@D=E2`AO2_UAUWT=06R.>,
,Lq7L0DmpFpoX{J^lM6xjjIu]L1)uvc^GHdBN/0+>x#.fO:d)0AAbkS>H*$km%vW_xLCkLcbx3;7B@a;0^"!4dr|kJkJ676nG]XwIR-5>0M%;he9Jiahc08bGm5:sNKpj--5%{qO=WL@
[c9!dtVmhtR,Kpl]@mty_ACX@Aa55*H%s>&E5g|:Dpx9H9N
s#7
PmFTpB.[C[A`,=tOvco"GGD/uLoW?V>U8u*sO;mVqw/X`nI!4
*3HVbg`2b"]GltuXvSUxfQ]:Ht$jhcMA!T)wXI"y2@3,Rt*Lt(65gPUQ0+LLc"fx:]T_^/@h!$l)e(v"n556^>@KnNVaShl8}BnKUL:NANPj#89OV1RC[gF@Nr+;rtjJe-:vUc=RYL>tRW1q1p.c?/=v7svjp`a3GE>bUj8Nom0A%&i0wP@ds@cCXZ%KQ6tdWPIoBRx_[+gEhC%jIZ90oj%2lv!d6t?nqi}UBK!r*$BZDSSDtN_M66`73"0>V
4$8KCy"9kHQm0W/Kwk0M^8r1B&r]<DM6k"5SvS|T+G.>gt*m]j/<CV#%B&|&e?p#[7!pPny4[SEB/2oN-<VV*);U#^wPJf/PU"L?GP(3%f8;CJ_&jlYWTS3NomU39irF/Bs>MgUBgvDjg/w_$Cc
FyD;`l?IMKC%rB=&)%kiMEwo<jogvwo5B*tx@O?Cd04eqVExu>>j+HQ
,lIt>ouW`V_OCG*U!j:vQbq%rT@cf:C6!DJyIJZ0==8meoOKkRBwyc)$-JwsB6
iu@}L#EN@`<Clmq_<;DIicT
lO?7-l1a^zED
|?]FU-4AS1K.=K<8i5aJP/a8*1f,`:Fx)Et<GZ">@kc6vq<sDyOI18QeGnDtD562j2rN#W;S~bes]c:s[uC%i9fiFZe2]m6&UMPkCeL%(srA}UN]O3&xo0=ng2lMV(H(XskxN-c`-i`Pjjx(unrA`,M;(:S(@A<#@UCi[w1xHvs@wWWm~:IKol`YpF@JvvT)OENc"g6k$9qisB>ca^|D$)|+Q90H2Z:g+a+uxA.g)$qgLZNT;eAyD93k@`#t+@g_qK.gU(J+g"I(}p|HehX!"P_S.:PR|[6cxrMO*nRuN"cXu^rjq*wEbUZE_/Aacln!?g_2;24gXHzA_mBBs=`/PZ6oh+X?ew4M&XWs@tK*q!Fc<9U*NF`@bfc6<U6Y|j4r:;v-(4X%F=i6-QbWql|r@&|6L"1pR*,+5_eRNBiE<a3eBy/eaAqZM4Un?3iF<
07r=uKoroPGEDmsH1WN9PR):<81EByWx*)cZU7IM~4p8wM0M0T.bARfeHg<HA>Gc[wES98u+Jg}XNTG=}C=)gK,E8fDU[`WnM*ovy7`8btKK4`A:YO.@!+T(@^,Yc%+l|bdJUioJxex(i)9F"KaV4,g.aw3dY.;k}E!!"mE5L4c$CXHK6I`JFvB(!Mv!#r$6-eJXq,y2*KGYbwt$DxW`1R-RrW#x*e7$WvDqRX]NS?$ncok!*`C6x,%^Na.AUd]U,c_mz;})bAoyXG8(>_ZjSYNmD(}R-Q9l6NAn/GBpUQ_($<)6J[;3,9_#mw|&uO&^g]vIQVtH_yls|65$P41d9KVZ0d61}IWnI>H0v6udFV]3;qtbM[/XpZ>lg@UMI_01!.gB_95r~X;#HDU7nlRy@[fg*TWIb)Gl--"%;';case"sr":return',c0<%bOZK/f+pS`"3C!wdY!Kp1/ensFbz%v!)<#a].1CQZhf_E7i/M7]n86_a])P(j4nc?SlyZeT^:nd/e/;tJHz%u6B@r0OM<e,mo"7vcpV2):d6s$dmG06~]jY<aI_9bQgw)Av*/0HxoaB.%8d&VC,3sbq9Xh[<=>%vk0NSBj7Z7<1.12K,ZjA`*{JIt{Q:wo.y]5TBi9#cd<SS9Y+@/Vjw&HiO9``&AQi:-xX@0yxNEPx9VMR_bGRF3fXS`Ef#=z8}uZP:l9sZyR:R!Vmz<xR}(51XsJZw#
lU0(o!Ak0x0Qe
Wb0!KBUoJKq#gso<IPTYd*P4<v6>=OOo<v3Ma:5Eal%
%QoThp1SANE%J(d=C_mU#O%8PW.&>b)nxfV>wM+`8f%`+4TS4DABIGZf-]leKD/>(lu<F1ZC-PURwM9yM"5rIX*-afk..Ks/1;(v%IApCkdZ^3_v*yS|LIlC2IYo3Y(.-@@>y2tl2_mdldxveqx)oy`sOIPi!GRzAFFR%z.n,;FY(OuC1Utk[[wZmcH]"16pQ/iXN3(3Vbq<iQB.YYj3E/t<xXHE,HU`QA@dT{c`dW)t.n3t$hyvhHik5:d@qo6`/8>7/&RMOZd9pPL"z&StXB>ASlw`.[-:%RT+Ee:tq`g+^E.i$!oJwK[(,M>ZBlB=./3E@ONGFIR#23-1)kDSg(fH4^(Vgx@62f-4,y$adX$F]o)oU=vf?z`G@Ss.![N=P.9lBI?_URl
wxfsURV;8[r-N(uV-Jnx9enhe
9"fr9:)W!mvj7cccLSs43>qQAgb~3_KRSs%Gi3[T_`p2`[dh2&)a
bK&#doiQ`R*_Jij^b7@YH`So5sXhQs/G""+u=i*#Bv0)"aRamD**iSMS@3}sF^Mrm!Lw1G9$fTk;K?S:Q2Jet`zQq2N_PSv:j.?mey@nZljJ7gpjFEZ-1rt:&bYk)!3T%+loKqpCPMI,Y%4pM;>g>IA6b[~li3S:YG#9Xm/ipfZmhB;7>ma])rQAb=oTX1iT4ZWG&kndL5?i^LAOh/bqVi|6a6c2pU~%n*0B#Q5xFO7ERO)oSGf=_UA8paYV?UAx/M@cpuGg..N^TItceo)*{NcJJ!PV@J!J^e#G3e5HWH&^Z<sJG8&A~"<^NU>Gi3OjYciMf@rPzWR-Z7Re=A|dtoQA]42)[A<%Y3lF:UQ_c6v4A+gjIlha`A2&ohAM3z)=B%=pCif_(MknN)(+$M&uA<X403>*b0!g[9snDvW<uC6Sa#6gV0|STna,naY]5;A=Fi9]
1=THMLCCxgAho`xoHRx4:S@{RP#qR`QHu?o}Wo"|[4./0Tr&1)6F+k336-#t<h(}U6PqrQdkR6jt<rVFvL4zBnFtFTZzAF]+7j
KcIFM[gh]Q2U=q6fhYE%6kBLb""D6KZ@i@v1V5=9B2US=*YVUM|fT;fMJTFxGf;OQgk"ub|bl$N[DSr1bR<+R#wR]6B8UqSCM&q>#u!()
V9:-aw;fkt-%j)sL
u?W=hsp{WTHPKa3^RMgH&i,FQ$/
"[%,,|pEL.]`<J*bjX<vnFeW9_n0R,D[;>ZUjoP)EY>{=Pjt/CwPaT&[;BEYl[Fis#:e8vVq0|h<L?#?0boYm.6ITcVBIlwRCpGzap1ews9yqPS=WO><1kD^y>j;%~7kW>FV]JW>&ZnPA+.YB@/<i*7ZHD4,cR`EPT[a(3cvS<-uAB7%,)$b]%&vY(1wbDtI
:c>H7(
rl!leOk9s1XfJFZJ0URNGokJVVyQlGaf"Z7F0uEAu(4hrqq4ZXl=>&xyp2/6,]=|=;rx`IxCP:Geh-N<-j:$(wf!M-`d9S(|]5c"oBWFD0tqG+ouk1)wabEUnP2InZ
~pbi"Nn/</l<rcr-Ii}]]/;[
Qx&Iu"It#1X3t>UJ
KF<wk
TDnym-RUgVR%n,pmG1&!o=$4u[54uAt6w`h:M
dbElQ:.^C*05$aE@iX.?>aykUo~3{`8IWUyL6sQ9wSe^)n;Q;!Fs@&qYJ7D9Me`#lA%9wypTSACu(?1VlZGBnIwmRf8dP
.p(jo.)7+5.H:#YlP.&ac0SxNM$&Cj}2r4DtNP)Pqv9tr19
2`y@u&`i=boS9ZYA!EBEhS0^vs/Ry/.PbeD<&gG/Lc=W,7*JU5LxJR)][X"krN)o+^%eAf}g?Fk;rd#h*AQAM[j:(;aScRjiaa$Fj
5Ci&p[5m
[(JR`OQ}]uPw80FSq_c-GCl/l|y7C7&#8ZL_ruR@QB
opLW(&saUP7Y%M>+c7{RMK4QW;d;0^.YpQ[G83:p5$^Bk.M;Lpqv-`O[44BAAa5qE(c3aM7cNADosmN;BqFiB/?ZycN9xsH75D.tz>5TI
-,2c[GH7]6_g
@+8ND!pdQ;):M9"f.g`)`uf<Qj6;u/
9:z,pb^,KOy9z^9C}@&R_?&gHooV9b#)Lxe){on5!2C[Cf{o$/>QfF.jM,+wbtIk`;XQhXmTdL(B|3g(,]t_?e7K([~_I
gI/f@_7V6w.O[["nK6)#zh>CCM8_%%<GCvU%4oqw/jKMZr4ya#c9<eASaWE??ixNOr:nj1FUW=!d.CCM@S]#^/Ux8l6<)pkGw3RmRm<XiVQ9$Zc
}uJ_d@iMO5WCp&Roc:DiO)&<t>B"hVf65N@+#FA[_(7V;0@2Hnzo
S?:e){eFqY5,mJ,2AE`bS#:$cxElM
YrG.3Y?~8YJ>;(MURwm/,F9b[~sv"@&]qk%8]W[?6aq"b!w.8+<Vdj^AqtV#ZS+6IL:^@v:0#_
/H5(4mT.oP,a<qb0tu3Fq9oy
dm)tG}=yDE$:@(jpL$D3%7A,XCKhGK1wfB3)rE2HW0j3/=[dx*u+%XSqiq#B+0VU?DP4g92NrmPS8`j0GtsXRUgv(bLks#5hx~fE>Vp]LTtD8xi-[bA5Jpa#R[so3y:O"6TuED0E(!ULS
paQxFsxfabSZQP5LxV5sS:SqYfpPFq
-YX7gn18wNLZy,4^oiUcI!Du~99.W7w*aPT*O@BVjxwi7eaaMNrN4D%>JRJ
c8E1uOpk~.Yb=bLP{&O7W@,Xdt?&-1Q2?a4&%!?L35t3F
}8v[eCg^/hSgq,^@V<<G&aQ[9g~8*OEmJj+X(B|KeLTKYmc9-2!8!wK9lctJ*h:P|bV%1D#F*5Z>ht%Vlh/f5em7vQ[dX/Ns"KyKM6mY<p2g``=5cEmkBPI_)`_M5?;I
;ogQ8*!Bod:=MDq;2kxY-m]-0`qecClO+Uxg>#9i#gjG(v?vRp[rpemSq?N<=In)yMf;SBV4nrD4
yTWgk^63-VaLt0asJ^u:^,
GmE".r%rI|L)pcM.sdWk]I[bo#t**lnFqM0(%@$vrJ=Z&f?}m/dw5-Ag#;uV?j_u.A1ka&@juXGqc@y5#@!KTE8UV5q1c<9CKEDu,cf;
|A~%)e16=Cib2gXR~Os6Wt<p%-$f{>|fZ6htdg-vPF]=t+yu+d=L03LcuXI1aq/.3gUV7-mab$5D`gU3Us`nyP(9p8,[s@3LYFcH?KX-#a00"
Xx8]U_
T+4,w@5u;USo<A$%FI%
f]2m2xMm(15q+Z0IMOCx#wqML"/T.3t3@","EN&:S
)$B"Ox
WA}wk+2w+5*8/xByT`?b/E{:]`_?)!.U-7WO)++),pP:@e>kR)w^
3!ab]ITHnMJ;s(5S%^A5_6V]s`VgaJc.pk@{ab/e0niKO$z$vqB-Yr/rTpq#Dq[wfd)&U-b<KXQRVh2Y1:R0X?-&wn0vHP
CI#aTxrQfp:VwmXh8RDG@kPHD&@4XUOueIz,IO2=~<i<5W4YZ&k9iZwxPv,_oYsL]9bLoktcAmnsbI9^Db]#PxJ@o=nDT@%)V]HWja_yho)';case"uk":return'*ev@qf{p=(p)js?]d;OoR?}VWJC;l3;SDYio*.Jj$Z{;?$m#hQ#9gX?4}qt""8;$J)Ch3#lg@s27@fZ#Yp^P(;eX7c,[ayv^-n2F(Vrxa`BqIxZnIvv+/?EhqZ#`{GP#$]1mFIhw
-^qPyhL?+sV
?ewo5Bsi+@/5Yv
g.UfFd]4jHMT_uG=|e`
La7Uox^
vG~i)R-.HG5F"IU"~W44KFZ2P;D%qE+2qk;;)a89^=7eSM|M-vrcwoBkTsTKHuAm35QI;w7_4h,`B2/=pg)K"EPpl3sQmgR)/3h7(N#=
-5h9Gc<URQ@0z(,/O;kl[0c7wq0.R7$hX;8~
O(v+SM%E$g&:&m<)OW-0jI.yp*kK_+`V_;u8!k8t"CD2@j>)3Ztm:+d0R[SP9pwA$s21Q99-l&gU&soX&ghW~oA0Ndk2G:Y//?LADM_#gjT-dOYPkf=$T9@<?M}:IGr7p#*6RBtR%N>o@4d]R^XNog(Qxep.K9
J`L03|GYc.A{GZNIBcsET.pu&)-+pU2,XIe^C&Sbizn+2EvpH&LHTz?s,xblw>8)c;ET@%fK2?FyOC(rX^LirenjNiX;#d8u0L2D8w7Q7mHt^3Y
d6d:#{@U1Tt2I?/h%i,.5SrZ++DL(j
h`nEdCj3*?YwuIjNFmQ4kg,Pk$,Hn3oO)q?d(.6fe`al^S!f:g*Ii[Sm=66_sIRuxbaS
vdjI>y=tc(yY-Yaa8iZwRThN5K(j7RUST[iupusq/,[dau_vmGD,m3*lV.u*p6t.AzKd>;E<W,-_f_i<C6+:fAR,A4q.FR)RcwhB:iO-<)fUV+&
Ep=-)&
V?m*F`qDg`PrTf[($qa7^aem:b<Y]5Hc%:Ndwdv`vInsyt%953"vc==!6?nm0welNmz$spKmlNyToJntT`@vB_w0JUaWHp9n^]A=wkR[5A@$[#k!y95cU=lQBxNl}Cx6`=xM3:>>,d[c5*j6sMh
d]n0A;BEW>B%,BDB`1N"^w0Iwe):r?EC^yis79Ksv/nj(Z.4_2/JS^xfj3y"AG<EO)2tjy4c0o4R{#Q;7tX]IHg0J-,y.9aw"VFDxc{s=mfelV^YT*`^9#5s2L[5[vt*TaigTt^[d[!P4[:6(?FopnWEn(fO{fi?}3A8j7sjo2$%9BowkKUe,,q:#9U%!$jbRx16TiyH
QjR=81h
9IIq8Ls+
RQBS.I}RkAd%j!,CJ%o&^hq5v^&_p*(Tc
;:
HCQ/_;P
@Q*A<qWvLv5sD.1^ISWjOy]mtCfUIkc!RTL"D|svp(F~swki*|/xPD&c&2gS2sdg@I>]a1S+n4a~SdI;XrP|7_Y,d!R`j>SNdh_lJ*_0wnH26v:;U[I0=mxgS^U*m7mx9b^ep%0~blUee*X404"q?ZRYSS#NLXp>h[3[l85:G9svsq9qv,$)P)o}ZsGq[.UP=C%&(~OBpO?ZnGT8)+>`oy2bA__"Mv[uWM3$aDM1(|Z/,hkB>+
RGkj+K8_D+DrWQrw(1AQ(<1TInNcybZ=k-{V[2A0"b3U^J
I"m+OP9?JYIhQ$3phDE5_&h>1u;dZN1p1+xPXcIfUjq-r}EZFq/%8Iz!#1H3+R3`f}n}W#]<SbE
h`#Zqg)lQWQi,."hq/M(GNa__)$ofd=t+]-Q;SDSX}(!0Q`~!V79I#h!Vf:y-LD7]<i`BJ"{x0-q9F&-yT_S-V3CInR`_>lTV?mRBr6yoxutF%x*
~U-7Qdy%3.O[LOr=yfYUiY35P36TYZZjBZH#L9)1G)prvTL_26b?
E(xrN~$}Hl4|a#1C]B6QGq<Uk!?!>^@Yl2jD
W^c[
BURDP.&~S)Hd2i%82-f20dWd#WsWs<?V]ty1!:]EbNEGUF_0xt!Ah|G9Z</Qz&ca6**SGJakrL$
kf
mNL8pi&)Lp<c="qE1K`oiGt$vX[9sWlP-$n)P%U<[2"C-M>+QtCHmR
ei6D>Z*SdLM0=X3*=S7)E)6z9A?"B7e"?!xy0[QfVIO-
gNK"paP0b+uC^MvhWl&+is>MAvycT&).(a`+_c"/Kegd-vtRPq.1Rlu0plI:<_Xe.?+l~.dptB^bK#VI>u5
WX<29Oq6
P&Fsix//knh,)864T}0ZnDO|&&oy@Vgs`~;maKh;"^?jdRvpJ>3%YW
w(RqULS5V!!$.B@;)n)BD#=_W#5FBe0,.6^ZJqrR->C0]%p-#Ej_4h43up]?t)me~FJ"},ES8Ky))g^P%aFI#U{e6Q8T}j-W6s,Erl}2(^jX)PGo.mNleuA"jE1`joa8*StQ@KoUX2MHt]xKZDX0<mnMG?_%3isKk@.ZbVGH:8o4mq{Jfn2`e4m@#:"`)?=3?o1c4Yfv|<(V6ixz(<hwLf}Zwwsu>c*c
6plX"2%8YY!7,L(OhG0vYSg9@d:TkUv7
+YyaUwa0CJeE{@icIby^[k?+*?P_h6,Y.Lz.H
!9YDvB!vUdEXS^y<*0>5c<wHv"pJ2;c>wN<*
M1WMCZNSJLPlR-(yu/NcJ@c!4`3HV~bp:kfJtbAer6$zbkeWtADrB-_6B2,xU/T}h/BnFfv<AGAKQ}#&>A5%4~GR=dN9dp$8kin`KX/Juc81`?@ZV#`/;pc"m/pbgWa)g)D?NWU<HX7%:x9ByK+OV5y7K|e%&ZXJ-/WXTO(:D!U8)&9:L3FkYID(30LzXc0Re^M>q/T4vAGb&kJ}$80Mx{f+qJY3uYrN(a1jp}00anNDO13;68U$YU;_YzTy2U^D/u4a54XPGBe,4FY`cmqpLjSJ!Yh1Hm$^`U]SVwJc7[uq1"<8wh^IdF8n"GALd_UXD9/&
;?ey"m<l+uO0Hob7aUnQ6w&b}Q,"If)SG<hiWDSFQOzVip_.2xc4lhJ7C7x

`
bmD3)YM]3B7LEbim*b.?pFsT7<yW!pvN2}gq>qp6hN9VJs0J(J5uc6`:b{xmfBJ:6r3^iW%yos/*]B:0clgmp^ln+kf0.o?zIaf#(_rS5_8Q=V1hsG&pQ-,@cA(<LjIPixQXPHm2Q)*tDC2&PGDl(D]J:.`4?`dA13aUDk@60;jy_!+NZ
se.Q]mJmato6D/)q%QRkTZ7&/QfzXt1[x<:5mhDcjrt/K:XDJsA-^9LRE`6p&V96`)><-kYr0Uw07cO"kvo1Ilv@_[XftOB6*>.S2{f,CoQSm*GLQxKspaH<b2wAh9RO1C0`qvc3J|4]I8O/U|L26ZVAd&!FuG])sJM!sW*"d|@SEDA>=#0@h9P":7X|gPQIe_*.`KKg//j>mMtAg;rC0myVrD_1Br4WGMFf6~rt3rw%]_>|+{J?d{OT2>_Duqgh^QR"96F6Q)qRkMS]wSwp_p=&
`7NG>l{EC.<$7vdT*f_+Qq_bJ)6g3^*M8%/C"6G`MBuwMm
4_n`C_aeQCb
o9C&"jLRNFuk[JkCa}QkJtI-oNVewr&[s+PbL*YUK)0=#R/83,t,G:6NtMnyJh9/kEtFR9?n+H`tFqsnp8]lrk$H5Ssz!w&WOx>Sx
/#B>CRF;XsZMW.1,*HuT6;cA
X@E?_f{Bat+T(rqLiRf.Q/{P%Qb
ads`wH_L=)E%2!<4D1oWy@g==4ro__2@ZKn)R_LdtVQ&95E#34ZV)33c~;
b;G^7:L0!^ny6vhinXyBc/Y.4RQvFHOV=^G":gZ|kUJ1=$Ma6_,2v3w5$%i3!BP+_i,r2*Z"-St,Hf
?C#9G[&dn`m!wB+Z)q;-?L*NYnFu.VW2?[)76CYmoy*b,hHMo<<9B=(^nM-1cI8g9p)@v0@[iZv?OJE*4,VN`noxjW@"4bkxh/NYicj[7/aiB[+=LFbe_pK?Bj7gQA8Bh_iU:7:p
Lj3eI:J/hpf,A;Pv1DHuai6Ne3oxxhVe_GN_Tn,pQCU(PILG`]="Onh?]"Y^Me8VR!bAx`a6<FW:3d`+Fh*Smyt[N^fJo%xP>{nMm!2Xr$R]/NAdxN;$fh2F@bRw.YS+n=2L0KYaJ@9u-Fk*3[cd)4CpRiMiX{[0p8vY1&6%w,k{5Q+@+F*k1&J;fFnU"-w%o5rT$!wW?uvlYlw."jQVo%G=YhcT$#H>&-!p/1Y>W_IpGxl%A?58-P;=Mtu(_l4yofdYY@ZXyXsAs[iq_xQ<F~)Dtn7M0zk88MNUNTZ39tH~=1Tn!WB>I~KwJXC0y<1$$8u/A`c;/QB]ry^;M{5;';case"he":return'#s`0:6KZ+&iq4.1ENu"<
S:`d-[8t)Itqh{Z
egQ3KeQCGC/[8s_p>3
mkuZPPERNOy5>dL?HsdiLFmJze/i(P&=cWoo,[+NaSv_}2
j?rZx:-bkJToy4dqTful>y..@gr6S(9~MGB?TI%cs.T]69PEP"n7V*%BdjP6[Xd<EIJ;Udy]b:7bn^YZt9Bt*nSp4AWj&Yq*mjtn!_pVy+7efcOH9nc]DEk)^utzuIbJ)
bjIlRGC/m[_&$!:3cnIhQ]cWKaq$S5y
KnLX5mIE+7e<>Fm|6*?H*??VsTTiquR~>r<JHdB6pt[`Y_0kbr+netYYD-0ZUU_H6}8nV^9Av;3#<Oi7K:Y{GeMJhk@(G.oeG:]U).i3P?JCPN<$(]k_y[1Yu:K6)9Ee4buVXgsvBBxJ%vPJ(5Xn]|^3CYc/,m.-OuW
:T0M*WmpS7s{ya$t7k=y1[0dc^9la@6OET7HR1eevnDkkI4Q3Tep`}M(f:+nl9po-RZr.n8-2-,#M0cwX[2ir6p}jDQ">m,OvnWh+i3"&DEXYnPyE=jbD`!o<O1!H,:v>/MO4FdclI6<H27uVlZS6E=5/h3igZn<rpwS]Y+{/qkbpqS%,kQcMDa+s2<_;zm(dL
f+].g#B^$V=5Y;jEnL=eYVhv@i83yn8T"Y2eSFD5EPqw;&]O@9_9;AT-p^&9`mZ9]rJ.+@e-;"/NnJiK
d!trP]*]3UitHYsh@vFtlf9Bd|@e5:j_LgQo`Dp_uTMe:A<Y?A?lb@q2!mcSfXwdsS&u)ZUlnaw{t3)qP1C",?<2(T3U#}x"cUy]OZ"PuOv0dQaXZ;G`e&evZrAd"EaiT0rzQ3jQ[Y.4lNJ2CzEJeCQ~6M_y[f>G<ICun}IVy63PnRrsQhhtF^c6@W]{br12ZZV8,,g8WF^:*^]4:7,{`uPD30kdV?5gJz"&oj$oRv)]."y3XhYlTs+3/Xf
3_n~pV;5gj@L-,&-UwuoD
:q[>R{UW/kK>Qx:(P(5h./pZF#&/2_Fw)e"+Mejz0~JX7/N7lY:;m7eP]PZ|+zccwn6p39Y3Lj;6kAn}S`X1P]?7wRveIg
`]_2dB&YbM4rnL&a18}e6)hGD-aD3,tH7_HGCh}7,TUYpb(Kx-b&7U1
Le^(T8)<hFDV7hJMV.%DTm15Rf*3?,,M;`6MU@lD{FrywyeQi1/i/DmEOXbd4nNf-;uQ&B3Q@/zw_8=LKf~QJ1eRhhD33y_:X:]#yvs.vxW<7.uF7Mu?T3BX@PRg8Z4Jdw*Pt_@,6w*B
+>V.#Qs
RoRB,BnYb[%(<O.?KTH(!{qXhkLr2KT-O_4Hi8dh.>9*ILm:[`E4xoI!+w4Y,
]~21:S[2hzaFpk-Yj2j62Z7E+m"aq[RYZ{HIfu!CknH)t%8dy_r?aKbVv-kw9}=Gq^Hh7vJ4_.)aPNqj)MKS@p_to#x^hD@e`]..1+p??U^qxf0M73GBKV7rl%`I,%kRosS2gF[~!b<.1Jv7BW7|j8ZDw~@LLbn_R/F|9b1,s43"j^Q(N`.n"Lj1;s.umG^bW}3q`t0^yy7B7.
xn]]MD=T"iX(!pj$g7#`2l7ficv#Tp{N5v.-?H%s%lMQLlt@j>x92E).tIY9fOU=?A/gvf8Z)7,?:=Ar=8vwvPYvs1#CcJ#Ylt7GENQG!Y_Y(9Z0taG?6W+GGv{k"5B,)0iCC+!8;1MWP1-
BXG4(F7al0n>u^`S?h`10&_J+lUHM5S:n4P7:))nV_N/`L4Sb>4.Xy/:e8
p4AH;hc]C(>X2/T)](N`b1rwi)3hU"]]ro,{;%/#.oeUi|7Zk?3SOULU`NjH"|]:^DIDF3UEuzY&EVs"(UyrZQMQae2zZ#LmvpJR`r%a;@W`#5)`y]qOOFHvf4I2%@K6q&,P<x6z#wm"7=H-f@wxZn=-.%hHn*s++Bk[-vhb>asc)g3$n$T4o;DHUH9TlO-?__`}kE
,m9
I+}i.%1yS+x[>6;(haGs6bZaBYr9Y&xxg1~e4X`NkR>cS)i@eGJ]Ti%f#QbvMf0;`/Yh
(XmPTTV+MdGNGA<"oQkY<e,Q,2k~C{Q^Ofl(bNAdt1akq%1xV/e8P3TOU6WLWKhgZbw}F;L$sYR-ioNa538*luIMw/7$imQsR,)h[(1v<"[@Rfa>7o>A-Gmmar]&<aZgn7mYX?*AX#6^CpuAkOf[1a<KyRZcE`7S.>cAf)W_7*otj{7JEc"pTli)9E*7_9q9_hpyBr$ESxCA3%<ElIhW*QXbNRbU/ZWec-_V0"y]GDOu.l#
cDAo"=Td8MKoyVnio"qk>;[op?iXA-_EQf`!Cp&[6%VU`V<v[Z$7Qv@wH,faL7jE;*l|hw)0L:>d/ddg;XBH-NrgDhrxURakJSyx]_2kbYY&c5r120dUKhxa[q_[I!jIQ_XDB)w-w`7xS1y$yg.0ddGeM:rC/BEg,fJRk.VaIpZcl`8ylxMgZ4JhcMTb/[Jmr?^>Ohi[TqMM""qb+I?{7&JXIe;VB/qKg[OvJ3E[SR._
w95"!Z:PcBpD3Q0Qm_PvxC[3XI7Jqc".yGr??#_iGJD/l6s<I[Xvh?>`!35n~8;A~;-jqtX';case"ar":return')s`0qaLZ;!LX+/|%+8$9!tz:v7I:1j.o4HV
GS
-=&>P@Q15_<i7R/&5$7p?7awE(9Oh4N5g5G[`yF{M=m,/WgWlVbYD%=0aFbHnhD4@rk.iPg~44m)oBhGbOa
`:=+hLJf7^lAllhgH$n_a,`/`0IwE0Ub<qt1bSfBO@QVkmKhb:YD;yoF,epgvshj#?D(KyV]Kof*5$a
Lp!/,@l}]a7q2hKssKVL+fdff;H]"Xa8+yI`yzMS6jjI4.^kQvmAyhIk:]DuVA[?1t=F@DS|QT]/-yWuEm[p;$siUdn"Lql-w+q1Lf83h`nUsKV>GRc6rHk^o6p|q(Zc>@.d*qMKhG!8k;=pCf:t)r8uXfh]tly_!?tm6&udXYPSuyk-NL&Skw4jb~2!1<QIC8;D3%R@]I7K!8vMs<S7(dV>b}LLTW<#9mV|fVPuxBc=;q$_gfa0-cPJ[roq;(7G`W!fd8`MEp(i[)k:SaycufU7D>BriI`v+5Bhj[W-DN_wDIq>lJw&aUYW*5!c`WXa4)X,->&#y"+T3{j-bVD*qS1`ni*G(?SDbqb9sgbFyWl2+)Gb/#OF_gJ#;(P"AhZ_j]0vJ&;AOzN]`0H_50D*n":7[J6FP.WE;tF6tIu_$4DfTPg%8iO1^fJ}&)8oFbNZvH6.?Z!5LAj~QJgp`4H-#l%~AEC0ue<*@SwSVBHV&,V"]"DZ0w,3f@srsw[KorQs)[qQc1Wzsio1dnig:>@1=4B!#EAtf5?h7|)/Uy<D,d6o
+:HPdEGH{I>:zQmN2:Rc=cejza+Abn}7*06;.0q%mF
uwfy4f&}7k[Thi9G.1!,suf2pibYvytOy`j<DvQq/7A
vF:(VM]By}yuwWH,7G6$[crBAht{.|LNbQ"s2P_fRbz$GN&Kn8Mh(n!FXp.mRCOf[V;4o&f;pXBLD(hC#`:03SK&`N*ZK[uT8i&`o+l)G:El),tUqA[?-P#Kxmkts@f,lU`etaU.5^u6.)`=F57iu7.a+h=am/9q5G<Ak4bZ5M<CNoow%^E2LQ+0?oO7FHhsyE.6Y^7B"
I;)q>g%^1sc/bpJ~E=Dp_mceDPU)d;gF>+_@
P!7C]/m:r;>8t*.8kCsuy;G<l/i.<D{vTAad|ar".]o[C$C<(^+61Wn8D1&INO."m3>I3g{kq<cjL!cbX*<ok1<&}i.k/"W":<6L4#A>01I1<H?Bk#3ktS(lR_T/Rd4#[HR+:;08FP^[Y-V0+8,OwP>-Cxabw5`CdRZJ$WP;GT,9/"#X^(1T_<s8Bn8;G8s&u7S04.^rN5Tu~J.%Ja3qyS>B_5{)V0g`rRaup+>&~BHXR@uA]9?nfMQ;%TH$(I_inT`d5&Lf#otO|WJFDt1qqLx7REUGZJCl?B|%:F<0mR%j[#l>wc?wb18P~hhHVj;avd]Ugm?Gr
6haVLkC.st|!K;nv{O,$j.;YSH;XFe[Txu6*#"^UXeh+%9*o6wJAtS6yc;^//U>lW0::F6Z5|L$dLJMpu!sw`7@Q$A#sS^+Aa$BM
W[#ZN7JKaz-vDOB%&{j)yd/1QeeI:M<hWIQmP5nQ64fMRiavS2t(!llM0ATkmuTKriCF"rrb#<B^+lK1ND3G`T_|V?@);1.9Vg0s*Yd"kdHQ)aiz[ZArM6_XmY1gdTBPWj[6&B7j@)sP/R]Rg7^Oq`v%(GR[%*_BsW0-sG7;kg.JpCrD>@2hi)L(S6ZQ.ci6BeN?(}t5&$3~x0un4a+St%`/W-j}Lo=`/7EY2ZGu"cn;=&?sg@Dm,GiA3qm)^MZVXj:Ykl[TF<kMz"f>%#d%gzaxY:@8$&xtPn!eHpvF,<WQn(cjt(E;fj;+W,xbl~xneG
]LjTEtb^73?h0:5mFV<9r2j+UDkW*.gQ9W`,ShM<lU/PEY^P[]m^lP]4_M0K=ht0Dhh=hWT:TUB&PY8"SX=mUF+7P1
^{F7,pIq+$@Zioi(d|^>hq&6SM^fyALRePtO)sv~V+_O+_*SQX=i?gu}Pn&Q0,)?vFxcN8jxcJI8@W4vcI>4wiK>=N9On(xp)>]RR.m[if;N!6yrZ}HkDL)cpfP6L2?4i;`Y,{@zcFr*^9%8+Nqy$6KNL2L+LDA8/3y*eI*KKxN5413V@a1-S:cG).!$X
-wR4h=7$I*-Au/w371wVl09_`BypMiAkNcu>.0
])D`v:~/z3]Z>6>Ah@`ldPAn9dL^
-"#Gr3n5Owg4#~4Z8eTXEi)]:#YpUwO&_+guIn;>$U+,fw6|D_V:Z@3vCI`jeh<cIV-87iraJ`a_KWS&:v^Ji?98eMq!_jk~>>mZaA%
u,S}0QC"GoYxoJQtz!uz*uM&?9UkVI;f*w#z7dP_x)Z5fng
lXTNa#p_$XB|fC+lb:NUBwD"h#b^H-)DWyrFM1XV>KQgEgy*mw[F$7woj~FsIw`##c`@]a=:nnZ~)e`U`3xCpoxEL1d19rekV2i7;vU?e"-|Kw5AMoL^qlcG6Y8m>aup:2-]7{:-9i5)$uyk+]';case"fa":return'.s`/VbW
q!,I9N8%e26;/D[DI1T<B]C%$x&bK&.%}FiT1
)-~=8l25`aR3giPX{3GZZDYxFN[1?<;x_7oLywsoLs0S*q!7tZ7rHnA2}s.<k@sn)X[I|FmkjlebR6Oc>2P+h_/yzQ/x~WC>C6Lq4Aw
gC|vMe(Iq^D@J80tr@NM$b>X`uPK2FWlu*2n}QNi:7GP!a2_D16EK`sI
35VpFwkBSl@ZmS
dlb]^w-JYAgOel.I`cbRu^iwjtWHKy;$gnmy[[lkly>6`+IC%cH!|QQt3C0k<u2B@@NX",RoHwaG8
[J{B(`@X23+uq8Y1GVU1MbQ57[ME5E"qKEQO7hU_K+_sS@(%xRs<?f8k}S9(C7</-)fR._bDmu!h")`0aJ
&g&M>toz1hU*lTY}9qGu1mA87JGQT^=g;}gKW+#7,gAFf*_~s6MY"~T3p|.zZnxP2DPb?!x"5E5A9
F7J:nuACXTbVwnpJ#z?Upp_|^Xy#4WMkEp$T-2Fciltu=V,oN^Sjd#s%KQ6!ZYt9QZA-pwm!Nq+lSg/CWepSb1GzIA(jX
9IRi?d8U_zGYM%m`G,s_D8&>RE=^R"!3q3:NdxgqeK9J^8RR
zD/!Nks=O<?x13p,?_Ij%*qL/v`K3Y9!>9ZUd
|j^wLn#@,q9Jzt6-D#+Pd@^oBrx53Ap)sR0-<d=Lc4vte7H.$u(7s@PY;WbV3Q`Tg_H7MGL
?lJ^(
Us)9Bc%K6V;fv&)@d/tV~!/W*kJ*.=%g+`w4Fywp@JDorMD/G"6F{Fo&I<IQnAE06YNg>9[:K`xF,U%PhjkqmY(swLVX#Q2,fU7gvwtln,0X4U$uiBFfFU{t8ydr^U>@4CkcJXI1xq#InR+,okz;S4cQ56fuc.QJr!SbEflb^@<o1srM<a#V!"FgDA^yFTPCXK8YZwhY[]"8ar*=o4Ig4GuP63|)ZQ8"+H~<3H*B>>J7gojC=nLwtr*xUD?cgSW1eOMf`7&I9kH,~^TSIH7;A=~&F9GH(-xIAv%kFtUwkjH>&ZWl%1b$A=8B{XU`ePvAP@,?]((6^5E?DS@I`YI8-3w^0TU7;1+IKbD%D2:o_2[?Xt;g6n]6&vRw@a;:TarszQO%u3&v06
,hF4="*(Xa+;8:IyMG=#.DwM#NN?^jO}#BW:vZnxZL2-[>h$]/#$9E(2q.Z1hnVFCpbf"+9]:Gf,U1A[F?e0`=y/iV#nXX1f?:2[80t,?%.(7Ei=@II_4s/L`brU=n2R]e>*l?nP2`7ng*)2Y/
gTyuE_Sj>=wm!g])v**g]Qd(Lr^RV8F/-3*,Iixg|IB:;`6+t4ub+;&dkH7R|fI3iQ-bgN0_KWEO]#XunuXhDM[P9]r`N._IS#&TYc$6,18?7c/o;K_&IxnNs#=mPt!6_^6<+n+CI)A!R:fTER=;?/
5Ks~-DFa4dBHHhkJIHOedy]a)-Z6?p%9>RHxj
,+yh_4V>$4:#/-"r7jOrcc2w8s/bs_tmPq4eoyX21<tw"*+:=^4+-7VfLE/a<jN9lW[bw>h`N8;f=F?8n+`/Y{qsxV_2Y7@%:x(NZ#dLvts`:0Elv2G.?%XQg@WM57P_BU^64K1HA#OFeXMgs^Hddip,x"[BnSV:bZ.o8@H5_MQteT6/#RUtr0F{gYmmu=Q]*iZ,yes#ViWV,TuMj<xh@n>)g*`Lu.6$E@S3ujNR@i<h)bRk`S>R2kAS0vN:F0I,yZXp1CQk>fg%>^wyGz[u9U^lB/,%SBPIpw$}o7u>
N"[7+^F
:40=l52&91T6=YK,2JPSZn@@LUq6juJ@Fdma_.YI&/lTJI#=D-=oG<O>OUCUYH:<8>^w&f?W:$yc_ta2V.QLXX*UI/9K7DY;uAE5kW,KRNh
%^H.U+rN^"B%y`;Uf81=
GBEJR[
q9Fov.]!gb6uIO7/>,?g8hFG`hKEV1$"Q@j,[7j.+
xK
L;i*<k,DE3@IKH;qqI$8W42)*2KJZ2:7L:%~>bh2!TIpf
iNVruS.E;0BxwWj$m,c)GWP$Nw0V?DL(u(%Q!IhGauh8!`F-m?B!:3[mPBZRRw(]r}kI4E-nR-6?5M
SQu[&9&#{.2UK*L"W2C9!c=?D@W>:-wjJ@*D$TcA+u?
=R]mVEkOj33VI*_";vw>][FgVKt2]<:UN@nu2M_0{]u9pCW0}L+BGigC"OCULMHU~,kGQns+ArmWh@:PZt}ylF=dd`)sqisc0kNW1P2M>5u3;kgNp3"D[CR9g[u:2uU
H`"GyUFU1
)=e&e
]X*F9pkqu"A+yEucE@evfY/iUT(Raj!lxd
Y);vLiN]3JBs5-N>FA+=`"Y-YwiO]zIKeL&5T|vTv!3fo$**"6_>?"vz2|<SC<@b2,c=]T#$NT4j0MQf9A@8nA/}<{u/,zTtRGj%b>gq?tv.:$se#/b0AE?;)D=u-nnQPJy2`z[tSS0?)%sgEG+6ME*0VrP0r{>?KC[%Tuy!2Z,&_SqFNvNQV,V@JKfA5af!xM>I,N&&+rc|ww*LfiD`-0Il/k1CXO&wm8S__iSaS<(-:89sv.2nKa?B;3Eq`}<xMZ7U>_o<XR*JUR>q;)0&06wvW-"oXE/%IDw651b7?B<y6bDiJ>lfE1&HH>/f$Qlc*`hk$u"4>s+AO^IJeYb(:w)Cm;dT$i6rNvM_9=4Vdz>i)xKVA;K{qy0^9{N]_WeoZ`Z6#}u_W#3HFi$IET:rrCLzW]SHez"UWm#kLdi|xdN&';case"hi":return'&sXF{5LZI$_hy6|*YK.(FiI#,^Yf3E+
iT"b=]7=fP5`uVd"zUg((>V
nCv3`UVcz?2u,E5@;fnLTl*
mB?pwByAfDsL.0bsoJokPJ@nWR
myh-vssvB}b/>s7Mj<D%LKw=@8tC`bT5m=RoJw@!!6M`uyGT?W?Vm0ux4=L.+<vkF
rzaMoXSU)o@|Xxk:kznEG/oh^rx`"N+kdhw7Ce?yEq1!A.W,m3uhT*d|8aFyd1YBNA6wU:f/xWR;M^dD$A#6oa3a,4Vp.Js5Qn%_$etW+&i)-tFy^emNsZn3)8w
2:-bIGRDdwHDRCvtB+mkxr1NESl_eU,6FcysORiaC1h5r3thZ|s6*~n979Bn2NJ[R04aysQ-`_f<LWpNC*#^l>*{a(%9iGm(TX!Fa8)KHcmU#.8D0p0<bgEidi:!jyILG-!C%iddD}l0D@/_&X0-8*R*u.bby)sV:4/
n^0{qD.&)oPf0.9
^kOL24Ce$9,wn/@{o?k.2fDsv+AdeTJZHC=XIXXNg3VRR%&xc%O=I3Ej#n/d>eUW*;K7PQNCZx:3Z(tT0AiTqYQ2<}X4Gv4JyOX:wTH(TRi>V$y~e7cWh~Z]GwXGh5cP:i
#;i-Kdy-Oh>?TMG`|cP$;LR%t?3/ZRs#3NYKI`7"0wd^&P
uasJoCY=Y5pmW+^=&0gDlN[eaL5@,cCkc-._]0bA<DH=5>tS*c,oMzmg9LLb9K`[o?p^O!+[CurrpnWzLa!~j07y4!r*b-HNtk@@0(0tvfPJ^7;NRnbRa}LV]d&z39)M;:1[+1vC.1]{5x"dgpoNbag
tf"Kv2
tI8P#eLx.DJOJ]^NxDJTB6QSQdGg~.?L#lEQey(l#3>Gm9/D$>"mF&oKA8a(^wd?HLaU!:1+J]?DtWgM:Z/)VK_/MIG>O">nWv?gyU/tQ%"A(CanD<!j!4j>#g}
=)X[G5jW,Mi*oMtTy5c@TU2`l.sC~xPsW2AY.K:96+?x:N:4CF<BJW"&.d*)T%y*mO,T0/[)AX<h-Ax(uaGJ$DlfOZ}lol0j9"2X@2r=o0%-{s~;]#L@tA#.5rN1_S[54-(gnN_pOx:`-lBU#<uGUo*&4=~Q2XRD5kqsM?.QaOKRsPApqQWWP+!4zn6<?^rV,^a^_BKl`nfgT[K+rR*oVmNXcd,o3TJIPufV@H,9Pl`D]FuKbOX-o_OH_Yi+}hiPeZegSQ4b
/O<YYD_Z2e]D5YsWJJuvWIa@9"P}b"P+S`+A56Pf7h5Q&)d%vUtTHkQhTFfZ#wnXU_^EpFS|oEls:u3DGEbpR+*D5+t|#)&au`&[e`*#=Vr*.WZ1N+X"pFR~(?`I=O=Y?UGC.aX4H{=^jp=pu0ur1}i1.[lZa2dc+Es-N$2aBH=OPGCypnlK30Jm_hY#^8noF_Qq/_Ec36K}Xx"mil17={^0,Fd!23cWC^NmiT_f$<iqKR+7dVZuR:ZPWuN;6E)_!0Cz3e!Obp%x"UFE0HfD),;T"@DdH2>SO$]GrFAwRY%f4sq>GA:uSCY$f5C(x1j+o!s[_"^FAmcJE=70TH@Lq^2>8"6hYmH1r&bV(0ClYypKg;_Nk~m0TaV0[>U{EAQm@q2M:m6|%3%iYY_Oh**+`@^{HdxR.U#sN,!=Q
G-A-*v?s3J/&@E<rlMHU&Hd
gOJLu>7J5tvA_%)y+nowo+-OdK8"e{.Afja$X&u&*CM]${N<$0GYaB/kBh:>sZ_mu`j5u}L@hbd1CmZOpU6f[355v5[b#TYQQC.bY/tv9cZJ3[gq9u,*n>9{`"a$3Rqt"qXh>9b79!&M(z=*)&3;.dlb)>@cmnq!eMu_ghCfI?#X+:*X*N+
d/(viH8kk*5pf%6n*%8?)Ddup
_Q9;3#
y8fZS"!EDADH{=Tg1dr:@9
V>YLOS1zk)R,OMfL,f2G]u/a5G_MRef^Y`6?9;ZQCfdpCojEc3#n"u?j?a
Pe*.*A`KgK(37;5NTB3.sQd_bww)-NY8Q#pND#Xgn;VP/&mv?2+b<>Jq<r<N^<bw3mBNf<Rq`#yh&tD$wcy5PhEf[8QH,#.]KL[1Uc2L#voH44K7C`fjni!`bI)sEL)1b3=G*,DT:Xa!k&gK,KmqwTWJs2Y$tQ31L,/17<wO}=/>!?B.0UA
47CUMCN%eF@.kPw!G8H6,JZmF#t)<;8)P(S#JaVU7.71tAsS`O"K%Aw
d6[`7E4"Q_&%1IqN4![vpll0XVelbYWw9Wvf^[(LG]=!-*_r{CB>e(XQdrfNu&!.bTN9VBd_Z*);?<N,WA%+5m;N^"aNXc@S7:N"gOv8U4I5@ZXP3`Z8mWcs=x@tQ44X,W6MidCs1=+a^9@I-;Cj3uGVmw^j"@RtmtSSR(5!LQ$5#NAIdGt8)
:!!ue-CtEa~_u`$0&R."^"1oJF|5II/xy=kW<G}K.CQt>)FQXHOM;RbOGpsd-mP]WxXga^HwB
lq0a8^W,+JF+i9`RRi}<1v=3*wF<N2}%AECoh/YIa1Gt2!9ix&vp0eH%l(3FAx>[znGxB2^p)^?@0s}4/?Zb~m`)>2wN3]T#*K3THD1L_9b"k+rZ=vH5AyN"vv17*)2]x"LZ[4<vR
-/HC|rlj=){q>"V$@4sbS^1=oww#Rj]Ydj>CoOu]YS~S"ll6tNncaG*_yM+2@/TEa$g4?HrQ*GTu]@,*`E;&
]k;>/aJx-)WJD<j!6;./Mymp?H%~>n(WPzRs/,RkY%BbqLeDV6##I4YxY*h`>*nKsCUa7$p5i[kH
b?Jy6)A_tnno5*-UP?2?^4?!5ups"*`WY/`g[J~&1,S@z,2y!aK+kY<F"c4?sZ,Q8_-dr+)#@?w,Fq>*%4DX
*?62-qt|.[:Oy<>:bY[mN=E!4
38:Y+nr(-y62S5Zv8q3{wDHpZ}_{Di${qu0|;;5!S8Kyn=K[Sb]JccL!85Pem-XI^A&0l#$blw=-$_9^bDR<*PCSs=vtGQ$uq:sxS@BPN4.i3@N:%`VytRC07qyC3#obP2nXdv[x3:lY`?wVNO`$tqL#
.j/J([VTC;y,oU>Z*)r,K%UmaLBb*okjYX.dB7`AILCV0y(+V86PFnnr22_tB>B
&?OfW?{>d`x2c9Bu&Q8^K<[9|sIuUcCF~ii0eh5p)X<W*37tPx%X@TTPmZsv8[t6K$"pTxyqL,1D5tT73@x[k^w]k1"3:A)
G+{:4vDBg-/2d`cqNX-9Jd
x2?=)zH>N#5bVqrcffd8nEku2FU3u%[G,|
NVpP=NunY"g?(+T$|/IyE-ffVf|ny>6;=!|kftW#NB;":7kMw1Y6Zq
E=VklTHM/C_M[4s}nC&BLeX8Y1qbWL=9Aq#`ZCA*q3SnrxFjb2*1@+rCY(]bO(#,2Bjl#=";m>ktf=tt&s)0rDw9J^r[*edhjN;OY8Ky*
fXIBxP
Qii[CW:rfOK[_23M6lg!#sJ[F-l#"=~aNqo0prX$SDe:^<>B60[2I/Dnd-6g$PMlt*t?s!!Rg85^"QSpV_u5a5<jMvA^od7#<W(U(b2CLQ9s0w`<DFcnF;5Oq<A6]"r';case"bn":return')s`FCbSpm)SMjY;l@%1,<3QY*DJAx7O%a&:Lj)ga].(6of2geG<-}jbDL"bbC>0<!y5^,i$!z?KhLp%xAB-])y6W>cz?DE&FAFKH+HBAFB=]dsgv2GpYQyb4w6QAXx[kK]YyBMrM[52@)SJZ?#2wxe+_Na>"{xcIP%AO(o7K#sm;6HSN:[JmA!Ns2E*x[w{1XKL6an%sd
Zm.`Ho
BdcjMia8q%y|x}#9/
l[hu<a%k,1rNmJs0l!m]MM/Eo}<03mn6C5IQWULY%1:i!K1-@!j,&{j6(q-lUK^ru!XsvR%0e,GfIWl@j]hf;qP^,Tr#d?5B]vU-v*=bm!L$O}RTF=<KK*W`a0YvM8GdU~LMD7,jc%6AOX?o*j!52^DFj6NWJX)|UiT|LxRVodxP$8.D>Hj([MNb(s"")9,(xg
/r-q()wOn"-Xu0zz(ylCEZKGCelmA>1?BsH<ibgswnip<A6lNj}h!J^IDqw.zc#_)`Hg$V%2pr<]3F%qqMA?#cO
Xv*h`fD&,fYKoIn]9pJaJ_#j9o+`mX!bP@n/
8lm^"$Z]v~Y^N4K:
tuky8-smC7u4L`s.ht)!=(_o-q<-2/a,a!wK~A(EpBSWApW!R4!d6&0]rd=/gONKL.Q><
L2x00W^m1FZ0HdlX$d{Y5YCph+;0-?"#WA@-^9@@zvbTpkZiun3N!/zjyb-JG&3gq!;9m6u#lJ`[A!qY?1"]"n$qGhQN|d}u7XGecoz>-/_Yqxtpx_18{LXG/sepqw5<i*546WnrI,W(J``9.Ftv^+(_Dnc_!Nl3A<syevqNVPQak)JDHh<mV#"^mc7vf<TYJ7Fw12JG(V+%UU!%76r4]StiVd|mq)[9Bi"SfYtb$s0,_CT>.7f4NDC=hk^S3#DdAFg2qCQZ-#<Q^/HN.t8m*xpP1?<O"mhKUBPTiyqlmoW9A+XezPF^J9*90K59jbJff_nmp0]C@)7U7&E1=?|Y_^Rq<+Z2z5LJ^x>6Ow&@2h#vg1z4FtCaO*woSm3lbLC=b;.:.yop}?xi@$5$;y(MVPpusTk:-o$@M
GOBS<<.80/Xj}jySy*F_(o49`YNw;OWJ2-}G2!3JgXsPO_8Z^L[:Ra9s>IuNy."4^nfa)glt<PkQXW+.ja#!tG*&4xq`84Uga:Lz)vxw@t],;Jg]eG^"&`c=I.w>dl:&Xg@XfBmU(1KV]&Z7.@>;j9TxNuMu2`b@:/Z(~i"lDQyg!NZG7l/ir#hm-Z~El8q,hX4R9]qQur[p6.YA{q&#v2duieS9OeyoCu:j+f$l*0cDD<;/Ij)!:lDV-VZi-.kgntgbEc*Dc%$tXJ1N!g)[`%,*X&+ukN#lPeM#A:|a?LZ#shpU~]K=QUDq^NQ*0!ix&pt=N8e5tGC;j!|uJTE5{bmV6N8Wj+_eJPBU&;[NzuAV5jHHyIk8LN.ep%_[;8G&Pr+o)Lyl$Dm@R.LhcUPUJrO(,(s7{&u5;f[#"[oGpR)Dn=JNg/B?cC-M>BKQ8`FgYf+q.vRvc,"=b97ilwL6`hde:7U!ccii>bl5dWK@k#[5wHiL`F=[,.PvO2J4]o,Yn={=P[WQkSS[cJP[9_M?%9F;X>omg0}k@V
Ll<!I8ukJKLCGxB|uAO}V}_9Fq[m%
p1c$y:8o/V`;SQFJ/|,N<>(0Di!DheXPUUw6
$U~)$=goj$w]OeqQQw!`9i@c2Dmb%6~*?24dq"E+/XgJ
$b=F(0G"$^/Rg#CPj8io#)P_:FZURF[>=XV9f.+!,R>CM%&Kp,++Qz=8T>71qKTM
lA(_W%}H<XJO,5<m-U)=uB*!q4O-NcFptdVF6Nl]n8KF#8F2%72NVJ^co7I2L(+SK,>`D3
KfWGr{;YS[H$!_Vd,MAGg3g@3?^&dz9RU"D(M"a^Y<p2LEOzWk3SoUHjb^M4k;[>$)Nn.1NNxvGPlbmBOMWJ.A$/hN4gupGb@Wc
P`+Vnhvzvc!Vrz:a/Bk]m}epM],]#),`b5L5)+0;<=^r[@vI%`j@b]/KGqZpK)P)^gK}]q!e52Pg<xYRT&
!?t#W2%@K"->J)evf^>QaL,`YoCQsI<pZ^;I|$tB6X["7)F8yu>oj35d}C6hir;/#Z0/5R/cU8KGsu;QO?^WG/BMy.
-_YTThEl3bbfQe]xJOr2V9Ku`k*->Ey&cN+$dBT.v3"4,&gs3Yg8?
9D!]AG/Lwy-J$}ezLW]MaE^MP.N~_o1X8e8
";[b/{Yiq<pBfW^xc7m;M:VbCl;2H$euYm&E&`r:sM_Aq0b:[XXQ#N*@wOm+M8wS#t$qP+jP.[.Qe}+Zfj_e)*;QH?)Kc/:P64l0-7.SNqiG)?UC&]@}o;RTWWyg*{VK^wAU9}dM1MxM]U:_i~nvYGk.4GT&qJK?%?iNLDhjqY&RcfZ3]0QDdtZ?Tj^w
?UOL]+t"~rW6y3Ta/<52RmdKT_00QX<Up
$DX[V2=-VNyjVsA(Fdm6=v>H&BtB0h5Y*^C4"_.8S_j!>?"%D^x-hn-2mO#x>[@/V83f59wO
GEgG];9vPD^44aW(3K8u4/ITaTp}93:Gylvmj_9G&ljSNo-arjS+R:cWc9?lrMPpU"AsK3s"=kyA:QOA>{nhxX+T.[A}1ktU5%;2RSav^b#KW_-AO{6.FYC56nu1TlnyapYf>(wLv
[/0|W+lZiGTp`jt(Q3vLOI7i9?a*T@DrO[B.8`Wu;fM;^AOsF_ht21:/S*IX5-Q:A}P][@;K-:KyCb,#c
8Ci$lCl;.8<:TUx{0VXk3BDy?
K:Y.=()z_Zw1)]B<s*cp;TcnA}(A.Mm85"!&11KrX=h:UgB
Yugx*/pUsZXy8Y,ti|=-=e-
a0T=KOD(VIl$3K]oF(Mvl$C;od"lb5p}QtO%KU7jWI]a*vcB:qQ!JwoM/{CKC7p)9Ek!yMXLv6lj
3k6v)dW!9v4oGsW-s+<^gxXJa6a7LZ("l
87!./)JR,tl@>x6N%UnS^+1LL4*^G9),,]yF~[B8tXvt`L$RmollQQ>CmBE*x!Ju:IY
#`RUn+#]"AaRBiX0m[bJcF7.&y,Dd6?dj`ebhV0D@ZVE$IZy6$+84+4m.9u-/Z>iiXGXK-BP|91d-356fh!K~FO[C$`X)[H
HPo06Igr
R@["nruU[/Sn`Y7(O9Rk@y1uVtwJwF?$wh?6!a^:<DP#R:3DW$smJI#QsmM|Ws0cCO.(>"GSGLWlIp!WDN^.[cIskihXiCoIgW&pAWC@/)3k[f19,;2k:8;P$G-7x-3%;nEtV5CXRO>VOAWGl8!Z!;iS
%tsHqJT31*zOs5}DToKK(nFwDySn)v2)FLnAF#pKGH*v7@x6:7tw#5$
4c:az`=LkK+Vqmln)?j[mc)eHE[A@$Ghkin-xp`hyy-@yY5iz0qRUt.M#02Zi09+#uxd4!Rl)vp35K62_
p((i+I/(Fy>
EWWZx?.l30Hy8A/of$A=HB%T:8/x(K"SaA5(!]J1<;4E"xeI[DFH(P<.La7Ql#0yd9e
wCZ*$^.n#$Q2$AF:I>0"Gpi)F@G0"*,1RY7=2&9CELeZ839,tDa21w5aKwO5`(]@!((I0"$%Z=
V).NWEq-t;FGV6!i-JC@,j[dQV
8>so|BDg9P0GqNp!R,t40_82DJKIov+"*dYU058msuS5G6y"BYbq]MwU=+][5,bBDh_LnNjkf;1AZQvgSFbM9q(.&!/WEhRfPhplH.Q(;-_`EM&?
W
(x.=7*?G=w8>8m_5APfZM<SMqRYb,#={LtO%dz+_1]pS$okCiU7BL`';case"ta":return'%s`PYboZ;%hW8s?G1AC%NrL$QeG(+01baNYIGqrQ=THpL";Dl-h?*%<5OJ<9kTOj%9Vg6arbHE:7~fnGt8!=]7_sRX73kBMq:B0Jc^M6Dm]l&Y#N%lF[KtK6{IOtSBQaIn{Wdv#H9xgw+xYs7/f]SHgZ/hy!t_Yg$N/1rY(XQDbw8O8kUG&h;(Nh{]aB0a;jWLWRaaDcFNc`Z
Pm^#8r{p:bOw+c
LVkpszD)_<<H88LklW`sj2?uYrJ{`xjK(QTDi%wu*iK;!_gG!6;7&Dt/"He{0A5.ui3CEN+@#,t6cI=yT`9tC#mY-&ThcV?J$D!}fu0B<Bvi2rxI6LscKaAH^H3U_8Ve]{s#iNL}b"p@cR)q&P$+&8[snY>99!^&`p&VoUFE
xj6Z+pLK4+ot/;l!4#
+VO2ERe%PUK6*@h<Mz]=JyKmJAbo)wJJn]en+"Cn:+06SR/M$0,0Xs_7tnU;E?.UI?6FdLIVBk$G--yod>cFsiRO%04=C]l:8mf{$2&gnY%OZ;PyOX
:*TB`ZjfOv@#
f/QPDrk7H5xQ(}tDc,vdpP9x=[_3?S;k;Yd4AwS}Q9@
CoRWa#4UA#!KQ8=bQ+(On?O}`U.`Iak]]{MxD93ZOsm8k8L/>d&lDZtK;If:bjJ$Z-]p^/%gGa]J]DN33k.Cs{<WJB7`?q7W&/&R^T(0sZT{H(GJ.dCSaI0`Gjg&4
=)ZSsS7{>zkij#HU0iP|s#xgLv7k/:2"YVDL,7Q0?5"{YE6G4`RTnaNPXo!laiXvHAZ.ck?TZm2WCtW3K&,%JEG8^8=~O=2:1rBs7Em9:Pc)h3c[I&$xC*$LRIS&QG#N/[jSH<x|01Brs.E7L1KPhCfu3qT!ObLmhtlmtM+k=G>Zw*3s@cn~B56on!iSr]3^K"h9J]Krw>_T]SFL1^WKfXv&n
RP%6phJ&e@ThQd6lG%]I/POx`x68@#`QO"uW/{%k/zVpa!S"k8+^o5f"=a
ip_`]H1NHkB0J[d"Xs;+Dw(%l7yUW3]G-#M^Vc>rl6
QTAQUrZ"u^c>K~.jr]wg=;qU$}u:&Wx-m*/YTIO)2eEw;6]y-sQ`X/EmW)[AWM^vr/FUb%%C/*w1p?(![R@.L$ev!lIzDG2(+^$)yKw=JqcsP+]*
+v8o[]7F>9ClJK>Ch!euVO$g[M,-!:H
KyNjcPy<X,o">j&cp%YmVEmW<$5ur[^4fi>fj_9Ei`z44d,OB`)bolTe^A;F%T9_+y0x5+6;d-~P,Zn6D#=P$3t.bRy[|u`b>f[5g0*=E#z_0)/KBO[2gE3#K.f4Hx%0jS6gVx~VfiTcKSiS3HTP.?<*(#l60gw-T<]eaTU#X&_3l$i"O"MY6P;y"`ksz1crv4yjF0b=ze6)1RBi%4AvF4ApkEtTLy2#]^!V%.NKP<&_2>F^GWu_2f7.fOcn?_61>41k}9P_M.n!=qu+o2$6P#E,vFAX8]GTUe[AI;uv1QbJn/J7}[`X&.}NO2X2:Fsnzvf,:Uv
xlPp]<wsH&mf]l;M{tl<]Be
zYSmwPX>F]&DzL%JkYMTf-8,b;ckmdxQ7qyErL.uqH{rnwcNr6Au-kTG*<GH<e_v[aW<V"u=I?.R,T%SK!auge}4UN=qC8._)AHpdhy1ENfA24~Rrg/Ne4W(P2Z2EDx%qh+8}2IcDTijTgtim
&%/nUAL.Jp0o68JV$deK1[2pulVFAuoazsNFh-kwS1V^d$mq3Jm&,U"hD5^[&kS<gEj!6hoG+k2,p,25~(c7l,/M>Yo4+S&b#>=5gwn_3=J[?eq!`pZx^yd_UEsR/L[8|P-:+$?+$06G/pFS%JN%Fe
6Zev>-t
U9;&`o_,)C/y9Z^*j:.xg>5mp%BE*Q/dckZqk+shXe4mI.bWOX_6@3.E&df,(tpWatp!Ftyr5Y3G]uYZ"z_?uU+uYxH9*%u&?mHfU5C7pLJ^S[_PR8]qxSpht/NNwO3:W)&,#IM.syIU)#dN(OM{e=uDQ]%|pZ)p&^NG#U.V-*73_*5}/op}gly<b;)Z9,Egm,QUi98>0?tK2"q,D:@bTWQ@5GKuWzI7g2n@5<Wv>r_Qb0l~0B]eS!>Ve.I;Z2jUVY_<cp*:Zs@t+]4V_,gRZ&g$"`Jv<mc
gr^6gmrl4_>+1eGeJdf(dDQ~im#z1{Nsw#.|P}p4Nk;>
B/#sxl_;`I>ki%}Pv@?6<yi?nwNbu
MG4G
D;8je?rrC<GPFJZXhMA`N4dMmPF6N[ScM^aT`}0far5QlYxG.*LNjpbXc4EIZNyI8HbBDIgKdI-blv(#A-##a^UC3/nN%!9N7Jq5R:w_kc];@`i=%Z%R%0:d[zhSSR.JweOJDK.aU{3Vj[3t0$tNApae14mt=i!e75=p$^ncX^&,TlB>3j]D:,C{E`.HM%V]]W!M>p[{U%Ywe@?]G<6*F}*8T(IV/S.t&6EDpJ9g4~Mqb;kKBY^L]P/B%wbOeg4XoV<&Md&lp9;3U->LDW-<>"+{I*edo"./"s5d`t9m(y/|Q~By1VXz]>6)DKm%*GeG0jmm)"<<El@qZ"Ww(FloAJ#*ME.#Hc%F7t_A9B4_T?-kscI*sjRwdbBR@NY,a.XVSwPN9?pQdZUh%Y<oA3li`NNQj_,gQ}#w$dnu!+&"m(WI?!jG,?dSHy&i.>F]KbvedGXQ*=J=&TQB]TNw^F%)OR7*<Jw2_MObG<N87(Ja:!6doGY:4a#)RKt.%ox,GTH{>o[w>AQF1Ues<>"^BmY^T}Y<:ldbnffdBqh`f(ZYk&_09EZBHD0IURk/iQ-4Z`MLE23y>1u8W;L_Fk>{Jl+_+
nnGswu2f$^BIB.2(CNJ8D=g9#{5}_iyf<sHt[EPGb&.+MU(})"&
(?T|yE##r;eB*S[6A~1gCqd_]r`,!/EW2Cc2uM!m(}(Q
LSJ+I"rWWB;>hJ&Xf!t.uF3$nB&<@mkF*,:nyB(>>#(DYk
EGL~myFE6XMW:{&=9y$k1BZtkFMu(taQ*h+1vYaF"Di&A_In@vkVJm+l=>bq3uwVM%%R(O6d8ub+R)p3HOVKKbqxgl57ZuoEnmIk,^H);ebo7k*vwgtH]}@GDD5KOQmXg6z%@<Yk>s_:<eimLb$<($l*P{ke?~2>`j7E8#e3)}T@-Y@hF<Z$yo=4=$byUkMK51G`)=+:NG!GvjkPAb.U];]*FTrLDUE*jYew??V&#V.(E>D;=p/VO1q19G=A+}_/wXB2G0v2%79+?&5DAIHtMqBf2;dBxSu@NeY<UX*8JD1.000s+)I@XB6NS1%b)!l>RD7QkL9+V>",=p@4LV+K6*W24CYNUZ8}=k%>?I,Mf?(#OnPn^P;p%jnEs<dfOqECi;,StulxK|-g0c/3I6OA';case"th":return'(s`F;aLZ;&iXNGq;m2>9JH"(!Me%X-v"t,`C*3b>
8t-dC58-"tg$)xsqemE(Fc@;n,II=U#0-eR>w/^Ks4?F`/4w6~Gmsgi8K8[Ht3&L5}j`m1l
a8Z#cD54`W`g6-2P"gy$DAnlnc`u`SIvGdFDsT`{J(m7l_R.O]s#_vY5cV`iBpmZ$co,0=m_H@+=35JJ6S(rry
k_x94GT+ja%yj`l7a:kK*]~@3f
V(NaNLg@$qiBbQ=.rbNhoHZ`.W45gI^^&En>.&xMop_Rk5n}9~IXaJ&*"o,[)9V&.V8F^R*|Mc2[RRHJ2N7j$-[,izC_-05hiZ,Z"Dsvuj
Bcb,K:U$7[#e{eH;z,la(N(44t$JV.v:aB1-9kDJ!VVN!Y#0",1x[Y:^{h)7/tfv<X?nl1ug$>z:GHP1o?-0(i!4r0tt4&2#D@#/}="eJl0r=PvlIE4i?ECU}Uz*sC`KdSc130#10_OVHH5osgI_X=v`2=Eb1;n!jlv(o%D,g"A%N#kX-o(sDkJCR*7fO#H6RZZ/%Q/PmC>E]S31w-mE;sVOO]yuQ$PAJvq])sN/bmT7#l.HW*BC]BuPvm`Pg,i:g#}3b]l)"D{$4+%13VBD9]GsO_)_sen"KE^f+)4:"T.x69-i22Q$;v7fyYe^Xv%uc<`#`lVVdV@A!YLjaN_1Qn?awX6v3m0g!gC2hfH[~WgN-je+)seN-e.:,&&"EexyB(pFAu!h,_S]PT>.juh=V*3M@#`ryB*+4_0![.cpwdgCxTX.!V)x/)h0X(3MC)G4[*fv1nM2*y4X/-LEbh(?N
;C5xM#&Sl]+SwacQFc?$`PJ#DeA[:w~Z/>)D;qH:UXt%!5#))Z[0GPu4+4ds50vG4(!3-D6qZ2vyT1|!]*p@3&Yms,LEvK-llpdR]t
9F+]+7e`GhC]7Y2DdftF&t_//W6~4if.b,FW
{^#Ew]&T%d[nu`.O1NFxCG3ud1Ku+6iVK][Qh:?Qx"a87([Oarp;&)9*%(slUqwun=n?Jw0Y3MnFc/yqi%woo-1P{tp%yVB!?.|n.(*,<W>ZXAV^YtK9t=5T=?ni-8)(<,Xm()P5#[GU<vudeK1sDh
3z>-LnAUR#%TsZ+<c4czydc+X^j&]Zp7J_0w5sfoaSwwr}C848-H@XZ6A)37[#*Cq^>_(^S!pUk
mrW=I
3Uw$Z6Hw`N7D[OyE?<pqO4`?6sYwBGUoMA/,B37zRc8%e`K#M}NfVtQ?gFGHS:m1T^j
#0:"NQr+V06g;>!YINi3($&6495Z7]-evOGe2enD:;bj>9A3!xSq#e6X7*6NS+H(fO@4ib^|R=pBn%5<6:-6U=uh)wC8$s.mYvbY!MsVeL8m(9;VOxq+0oDRG;s)pDoioYt92[cK;U_"g(5+7?NH#.$c@"1]^o_w0kMmUQmKJ?R8IVcHL}46%%"Y.k.|7eWD6)k`U=r^tFIHSB12yt8+M8_{]k-wNzx}al^?_ep7[S8yN@DiiTp1KBFDt+qrmUX*"QR}JCjME<g5*;3{:RQHq_r1Kb-popmA0B-l;MFAt`QClvHjnfp^MsO6
@[yG-?+3}WLa^K{06Ua>.Zjh$N*bZ%u4jnbV;q+jYwQ&H+C[Mn;"<ak>&JJs>eF&Jr3BS/mTf-U+22wZ1s-sI/6drmA>zq]<avIv:Z1Af/#okyz+5BV%.KC%3S#;H.6riL"LY-;LH/AZd/qRIm>_8(`7ea}d;Y>
&vsIdn?d;(D72Ss+7C+iK8hrO(UYTe[l{c5AaWob84R5z<~]:m]Q9vwG2,4(o#"wgkxHL_/:>P2EtKrR.=;Bmvo*G`f5SkNs~Kr*w/"dX$Ko|597{._lKHH)%GqEKok@tZ<Bk5{Ha1dOFD/Oh+SS7AMk|VY@TI>dNN|gU0f<&gz1rDJ:fy?9NX15t#*e%>~"Zd]fA$0,=Nu;[#xW&K9BGO5V#p3
I*xGOUZQ#(1:,aG$yM,@(<%AIi<${rZ[BgsND^D4>Y>a#AI<C.lqYAur*A8MS6sJq:%7</YMvLu+cWD&?EW6P.byi%e=t&Nu52ujX;s8h+z&#0Ujgm5j(6f>)(6ce@YFijA+UP+%GV=DY3t(jD-%1GAw%pvD}XA?$CY0=NLgN3=ndN+;qwO$?pTG`QhX}H}UrWh[*%K)(?4^;?ACE7*LCUxkNj!sXtSXP%
r:v06BwX

@N8)e~44c;XVSUjk3mmfmUsJ%_ylN~dM;;"0M,F[jA3[^TU<4_xFKF4.6~skSSTtqrUv:QAwE&9H#HxX`pp^?]_"F1&;1"JdpeNAk;E1Uzc:Q9Z_boU~pfkPNIN?c|.iLjbBE55-K`q>i`N/&F5Aj&7f>u^Ug7+iQ.u/@Iig5WQuB+?LHP8Xv(*,"dsL`|r0y(SUe(h27T9i6[JAs68qu}gz<?9XUpB10"C;=(@_T=eC_3
T8sP3yW+)D+g[qMeGpS<47-M/<QTYUBC{$+CUFQ]/NCWADOSx!#:C0=uxv<8XwoJQ&=iH1y.cZHNP7Rtb)jp2/OQ8SyTG/rjmiS(+a6u-Ash9k53!URmA$j*uj=&:9N&zXk-Rk##_)i;?Q"kQVHe}W%]A
gz$<2Z?H4G`J0Z5Q!&03Rx[#I1TYZ$;Ob@A4]7&r30I*atz`@+!Tb-<aCGQA%"Ki.E`09Z.Iq?omC^_y<+}nXZIw%SvW^KhcgKJi-&!^+^c]E=n41Q!wq]}tr20urh>GUf"qF.Z(M]"N]Qslh=pGvL1s+:E7eMD2^ZB,4C#^8s^B8XthOMinF';case"ka":return',s`F;h%WB&iq40n>[57d-gR)b.V?.u;G#NPX($r!jX,nXOlG@1VH/mOqCli/:6@3,+%"0T&l|Nk4^C~#E:>Eic$nHf[2#B<gMAaf,BdvI-SB$gzpuh-KFIvn8ddBdh`CRKt)(,-R]ROiikdFHpKnn&=HpuVVLwVoL6e@?Qzk4cvw/S
tA69*=N<yNb*^,Ue[%1Lh6j+YSDvUyX4)Y61+TP|c*RzSZ$c!N?;`TjEyl_9t_%NM3@<7@N=)rr8<l>9KkrMSJ?WL
u9nVA!+Sd7]-//((l)gNR9N0#7j.*2A!D[&
1;03/>(&Y5ExVI[nw#r5OG6!Ew6g8HFIY!Z(E(CTawM^IwP&E[xu:VqR+u_:c})ai?(1Ef!+12cW1
o"6Ws"a{;<h_R[W(lb5vMG+2vd")o,:f`JvyFVJ/nXkd2WqB+*%]M0[?bkj(nI6;cE3#MAuD`WH=Vy`ISc*iT`^&3"oKh"3,-Out-!H#EYv.25xf:Hwq%YfC:eKAHNE-xFW;kM(_m$G)>E+K7BG%-z!O,R(I7~mD$hry02GKYBM[?lgcLPqjbDTb1W59,cQ|GN)lZjp3I|D^9QY.b;D"Mr;G`wp)SgB?NmgKkT[A94.F&VCAEm]{I-TPV4w1o"&TI1p>94CL;3c6#*&>H2.jE@-fLD!)UTFY^v,%gSB|m+qj7KEm$7mu3y.}(S@
Smx?+YKh[<I:B3i?-wRgBYY{ls4#7^[6l#J*Xm?s.51[Ndt:vOw?DfyUu=/8_f;_kJ.A(n"!(pCgwe*m)s<V4::$jd:K(vP7vah?T2lrjs^ze$E"J!rR-)Y6@,reLMrTFE8f![=92@QaW`I{M`mjPz]4"W]AG:9P:+d#E<!X`:Vx^6>AGj6feYlQ&xm6g2ko)*K-&xxK!Kiri<ir-fZnr?_-9CiI>KVUyX<sT=;%u>va:/-Rqq+JFA>GUkB"J@IQhT?zgQIF7,9aa7Wt+mhZTMuE4~XYl_-Yc61xA"T#NwU_^GitQ>!!BQw/"TaI.=-`Y]1SEXn(P,m6uVaN>{t{$Ax8#aZwYy5~wICV^7i)!}9;nDV6JZO=4
]p*Nekyie^byT%9TeB+1rKV+%95^Q0E.cB-<JW#|%<ZH^87{dl8d`]_&0G,DxiMDoHN}&n[NM2-U3w"<:B(T)[#LFV>de[4P9+2?7U$<u^yIh]Sy^NZluSm~RzP9"7x}
.RHIa!`&bg&v!TGph
QPK^px#"0rRd!^RS6.7YWwj7SB@jn.JaY3RK]^L?Q2/[?$f3&=Sp{;:$LR%<,F^lQ1Rtiy<+mqS(5g/q(CLV2!rEmnoo$GJ$I9jl19)*fO08f:p.R<>26.0^3U]24I;$HP"2bsb(i:0Kr%h;Tqt.aki[<@_9M-}d*q,;:gyZ4C3&Fcdfb.9ZYqg#:;2I,9
l,]wBl/"[PCdf|!7"m6Of+2BlQV:N0&+H?-;J*v-QATmS^(KL8V.OGT|2!OFKu>hfN?@.RfX;psZAqItqkBAl8v=pvs.>XYrdz:/c6Bi_J%0WcVq5!>W<wdX)6<yI)"Jt(%~t.b_lLo{,*r%6<g-e`_1(29.S5Ep$0AcWV1C?p!NK}74r[aq"1J49B8E)ZqCCBY0c!+8HK%k
hD_T8-o=2Cm9/s1^m[B
*(6AQ:nJtlQBk5i(-$Wd.<BxUdSf)qyZk:A*#WVkxMs[ob;rZ93wgp]
_sBO])?1c4:1y?_5;Fl
,Cz&{8Z7e<(iCu?BwMX;oBJ*oeFuN9JsW^7
O%k3RZ[xE@H8|)A:L;sc$Rx1H!!R.A-7/]6FgP}kvI].:aRLd5hg=@)kNUi7D<UDfZz(@k?%-t-_%3}KoTo2bb%j=1Nj=Ft
4?1,>a{6Zd[??[w1T`JmsG>>yV~(HsO;`qS9CyooorW/c2">Y.m[HkN)RD`i3VfuUr921NW0dUm);[g2v:;fsgSe!6:GY_qR^yfWmYpQCU83=+~J*M-%z:jRX1hu<qS`.XK9dg1[{
-jg&f$1GS*KhN,+!2uxq^Jvgz!YB},*$b.dP(`W*<S2)eA?Psnn#QPXV`7GtIYzg=W{lGs}6)Vxy}OTH,ZApp;@G9qy
jZ.;01Jol7o8B0m])[;4
)=3H#i%{RO,gF8,s5Tgn(uToT?(F@Qw%hI?-ur?,mntGa01/dY8Q&%EBK30
<)c3"d!b[DaD7;
%m!TD$[XseA)nUWf4iaKy?sCX>uk?!<
,p&%3(Rg9&E=*;*(LBVn
rZX[`J(vJZ&8gwb]#l%k[X[)ZT6^>%-L;9f0;4l*NG^!7)e1-vtIvuF_6!&1,>.6&k((t9VuLZ7
`ET3P]+7AysN,lQ!.Ojc-UIJcGe/xb8"i3U8#;t]%Bw6g
2yPv1(>@vT[8i.Qh$(T]bIu@_%^NE}":D5eaU=VAK$!L:8(<,^*a3n@I,bDH>Ls8%43RfU1+usizp.hP>~ZH8s5"w9A3ZzeW&VY*,I*^AY(bv,pU$b8);AKVDcU=:>JPL3-]/iiGJxP&exp~&Y@,&24cp{dY@"o6t*]FJypQ*u4gl5StB
I7/&(BOH.n,*"NGQ@cT}1t;*:w_h`bg9vAZ]&w2i_gZo0Njh!$Aw(e,i#K(YYrC;"ui6<pA.]1YDfTyKRjW%0)DU^>"_<|$$jbT;Bm#]VQQU!-sddVK)`:*Epz3HDadY0uYeq8yX7Lh=;<.4.WpC*^2gOJ>!i>_zNxVm0umF6|PD/_3Am/Z}RkVo0:I1g,wcoxj@jy
L_HiJ%kvicr.t^AX6<|n~oM]5UIHH4L[QsMUG,;I#r/kk<CP:PU;==q/0
5<m;sF+w}#?FLJNI$j[Y#p9xd>$EpZaIyx3nd5NsTFpFr[4`TCKlDjckP

s[)c=<_$oFjab[tzt.;OJv3-Tr>+P<L!1#nQg2GteYnEC}aBU:/iUQuKlw33guWWK85Rr8rXAnN|X7Om"^^KLvq&wb+YUE9#+?O33.NS20FlY52*L6gUNSltJ{B9.96Y=#HycQJ#tNWv@^?:R"5q-y<h[PqJafv`H]u2=[aaD2fG6R<76ED>uRsLBusDIuyBGFYqGrcrphmx#`?F0>b%I#nxGaw/d^*.i(=r9g.v:2MG7:"}ty:K?b,>G@-fJm<c#j69QhMUg(%@@a(Ufthi_bChf!1gZSw%pYV8y
p!PB
>3R=GU,Fc[f-3:iGyjYaVTFrVJs85J|/b<5`OkN)S)l1~8MgcbVW4*YntO[,aZW$S&Q3ikktaCNZ&rz+MEfu#u6B$rTLL*-(ZZ=1KTJ1l^qox2&4HBLu?I~d-OCQHH_dr1A@Q3{IkWpFt5Ke>L<pwo)';case"ja":return'!Zu@af{Wr1*f8nkNIsFDTu4hh4VJ3[~+W!$j]q1Mk7}"wZr`J-g_!=gDr;^+CjD?Fe}Q1.UN`/}K{:#3("LN{
`vlr(Q$Q;"f.$Lw11D&RBw<YvTpr9Rx^MS;Lhu
i1
~Xr1N++N=J~nCC{m^h3E]n_Gx^#]3Gkj6D(GG!Du*:k`by.O[AK]mJR?U
j*~.Fa@BNu::04nrQfQ71vU=J6ke>d+`"`%_6Eya(:;svsnv~o;$#yk,/k]M_W=Eb#ZhY?!dxjZk(CY4{aHF8(!f3djobegc<tT)QhV@h&9>#AGa3oP?u0{_[iDqJjZrY:}9|sB`u$d(19{ifRMJ)lZRbHi`SIgmnLKw+
sR)5Qq[BH%SacP1a}o`Af[c,[J}l5]&N_b^!*t-c!@/oAcuh`[Lld4ULWBaxv!@j00qy$6o7w/zd]_C@+n}sQQZ^SA}3bm`e~!8?ZU6l~e:_qW0K*K818;F$_LaV,5*0Z!CcX`
K5E:rMZ?Zy*Ow8HUv*rMY$#:p<"eH)mz-&jkm<M;j6*;wIb%6--^>TQ;2Yh;`}e@wKmO+Ti~_yoM=4Caw0<rU}>gBMX*7h!]2*,6/DcBi*[/tMum:=J#f1ms714=i,(YQt%QO_ubJvBCJ)/X8[l!l;!{Kfb"v=Ha<3riK[[HZcIkrJLoBPq@
k]HO^C{pljY8w*~BVe,y6U7,lbH>MH)fr`O!9`:d~[)Cbgg(J4imehUj*B_F^VTR+Q6,tI.vdqUrL4x6-p+her<54<Cf0xp15rvswt]1q=bBb!,9sn+e8Qu;?ePaWF
k"v"UQeqC}t`x|D}NSP&L5xkN$tf7n@y2;&
*OG2-$d_EMM_as/Bs&XTLB2tMyRd5{OY1^-QjXB4w(gT=[R[&T)9"Rcg`}ryM`ahCQ5/+=rBr9v}SsA^PA0ZLy@9PlL/CE_=q"UA]zhZmM
JOk&0":tFf{vVqgx_`psidvn7[63nT85ZAY
>]|)YLEs}0g(=q_lQT99B2TMEvL2%2cAa93JpZq6F6=tO7`pg_Fj;iMdk9tanj#`O$At!A}6k;f0D8=
eOn(WeripEO2V9oi75J19T+G``4Uq[p%#$uAIqI>5K-2tP61M7e:)eXiF^VZ8a/=,6ADt^D%SVSle@ITT"lVK6}s=N#Nf2Nuj1TE!:>$Ihoxb<o,O^<HIppceP-v[z)ZcY0&w
5:6ELt)Qq&@XSSA`P+M^.lABbkw,4so,j)qU;?9Y(5uh*EQAt(%P7EYjhJW`+I
?`
r+b&4"5hN^n=>uAZd@hT{n">fL*=,!FF+[*JaF:;`F!w>LwcsqC0<Nv/IZ.J9B#nKMgP:Iz$3=wi,xX]/n[=|i(lf
-nhRowJsGnhaP)=j$1^IUF4oj8=_<,,[mOl8qWa2B)E$wnB>*AGoB@|5RDK5r-u?/&.@Z7L`z"@MT>j`xN
8iThW,yw*<g~3*#amM&k
F>9KWr:y)6cKNdD)z
S"{$ue2J&_+qk<_wR<857RE/hnIqrjioP3vdaQi,(5SKQ#861X+vOxEx%v[y=8BR[g=CYXnPvB3HqR$BFxHLNS6h!"7XEE`cf5Qh`r?kT)tQm@UNb.M7gP9-fYa8q&}]iJ
1h:|0LW/6$&]LP%c%hUvVCP:lL
@JWf;Q{?4O
XQD%)xZ19sM[k/k)iRu<K_mHysui1"oL](9H*&UEO}&PN..TD^$sh[ClQEg$mb()/y*4,z;S5o++uCmg
Iu;F6Fq:pV(CR*"F@"jek?+nl-y2Z8#Mf25ONz$GctOiTmDVt
z#5n~.OZ4lGb}
Pd<518uUOFwe8uj>3KKQ`;7"ubCLb+@Wf!]P(!d7Fu%IB3~f.l,;Pu@5mvV2A:;4[6r84Bv-^rH)Z5c4e`f
qUqD#s/0sA0yg_L;@)bwBqW`-oh9I_nl24L39b(vHUsH"5"fML4#/H]CGr}pZ9I"Isw)iV:
yW7>rZ2Qc4PShU=Ih46a)7{E%gDO=d!k|:gvYZ@^G2},%s1V9FT9~`M%50t
jgn5fgBxvT!`"3ILdb]DE-xCQA]F7HAN7rk@.GufM5EA~3i[?.
"tiC5adap:Ua=@I)!|>:Z7*$-h)*gIc_J0?fQPY#)*f(VJ_W@J9?dS!~r3-cQuO3DlRilv6N1KG6*2"cF`Q?#W.?Sacx9GK~)`o5300JK3.V^DUfLTYD,%Q(FRB}DWk~vmml/B%"(>_4[YRP(.a_lH,ijDoh<_VLVu;?/dpy/dThHz]!TPwKF/SLntt%hi!+SCa(bNK:cRl0/tOUuoM4L|%Zyl%_?9G65AflcQd!yVUI]/oJw<1?Vel@AZv.
ph1%.:bIh9ZBvhl*t3xf2-+g_$vp@fFMCXUV/I|<KUS.`UMwk16c
-_:rY9fl:ukSOItQX76`*GQ7_K0_<mir:~@6[>lZ6l-<YV?WpXdzA9HO+*4=IB!b7Z2"3HY
?S*jD*JZCw9ei^-0!"U[3(#1
2[u`qg)yvOWX87(f5Gl4BuM
lBy$v[
w,4
h6U!]$%VrfKT
Lm2#()fUE5.em)/;*"U(gVbch/jJS
2BU%Ss$X&*>p948Fom,`<7Q]]aK]abL?a7yH{_RVEqjt,k}aV@
CQfHu5/JkR(ehYspgZtw_"IBj!rV-!s$3i^I7gp+<mu_tE#8S~P.VI-qJto"23N*>L;q;g=8.)cjk9uf^$yAI}($XYJjY1kJ1
VcD0hn3E$ko`+#OkwFlWH::d*X(n_U;SPchFWlG?_d#bDle3%7uN-39+cLB5"&x2j3<.S/
z0>S5*M[/C[f|;}4/Y16ewmm$_(ATFrW!*^[RJUK^QN8QB7%!V[[T*hwW8F.h"*.`LPpa2U=0^4F_9[!2!R:WiYGJ/xDmFy$"#ieBEPpFnR,=oL+&
4iybtI+S4w7XxxFcODi(uD1%o]r"8`KhmFwr
wv([dOL
la4(kOre_e)h<`H}sQJ=9x]z3gYU&W]<hFh~5~h{pWS)o"wJK-<[6n-v]{D,r4WWV"c7KeoT_kkp4>1CSt]Et~@tv/q@-fRL1XT|>o&b/9sGq39O7Ys71VLB]ZXGxs4.SRLvRYO{co:.a[b#AwVL:^HZ6B/H9~-=eL@},ufL1WWu%h+E$nAm5YG#=?&4(4`Gaihqy8ea1z<!S^K_Uyy]PuD
vpA1u)j^+&s]2$ltJTP2tH%kiedVh)Hc^
^d*@O,tCu80HYhX]?<
_[|`H2!DBYIYa).;<P:d.+z9Fu%p{>Upy,;=JpRgd43rn7@l0".hL]RB_?TW{fl&Fb?+!:dl]>mFPyn$YWgC<b
SL&HJNXQS7tiu`xwyD4p&gVq3T?"D5^J5#PEMb"[Kcpo7$?aV6>N)+<
+`ot)|/.E`^nJhuU0nKL9|=j0!<Z6Wpacxcl,>A6(i[HI_gYt/0B0MaocT75j/1lDSa^QAm9
dNuA$`B5B*O<Jv5gSyP=mqQ`t$!H/Z)E_yg';case"zh":return')UF5h;"Wr1jkgkcqmN96En_G,lq(*>t.BdgY+HL%D_M7.R;`tqN=)qk"u<`Aeo#D_?.8zx|(mA4!9=0(o8VTN;6?0dd<.Q<6U$"az.EA;f9ro$2oHgz/v4W
K@.^r,vKYrm4FuXgo>R"&Y]qM!9n%%:]"B%]Jq&r_@6YUx_YT3431CLDWpgXJB7vLww
qO)IiI;bORC7_^4!6lZ]/UUG^y1OjFKKL9V>f+{U3NJmb+Zj<%embDT_(Cn.U^2R~3Y!Z<i++5I2qvdXq>SjaF&42bQqW:OWzAP"vZ;Dv^KF]#75wk0]&(zh+?V0{ARboMr1lnCXAaOB1mt
bwEEmH@;t*dIejXY&*b3pseV<H|+)P0H/752>@(cI)BhRPNRp4prVP.nD!8!
fGkSIW8hj<
6Myk?iEg5oU$fRFBYaj=#q7opZl^f5z$.=:+X6<GRi$R/YB9>?U#Vc##f5>!925tny^I|lVWdsh6_l"kh9AmVB4vBvwn!0tvikC,&FPfq[AtH<ZX*scF[p;^0HBBYffTk$1R)N`.8yd=.^GBXp:a3n`]TER12G{k][_M!-+W4mVB&5eafFN`q[vc*Eb`=#S[T!#=As:RW6L3a:uomS*P=s=v|$Wn3)INyZd7MuAWG6-b/9}l)
y<+j8E"la(ocs<#y]p$`/Q+[hePVp:UqV>fV)rWc+bcK11i79fHWUay>~u5RrfdD%XmM@`7_}X%6cF[A"jQ8WWwmG8L"5lYP<Ub<rc*_4OFO^&.l$E7FJ)amY%UO^a0j?YRX_df>h"hJWXgGf@D?2=LmwGNhY=I_I^j<Zx|+l!EpNfQ(coKl$f@0V>5U7FNsdi=l;BMkD;OGw;1Z24i16_uGr5r!&m%Pqwc$%:KYbi_D;DJ&]n2,q$i2bydlkuz"wtY-Y)+7Q[CUmFSFL;[?pBesr/Eq!>oXQEb[[>Lss_dG/K=t+H>6p(oLn%Zz)BMjz2:m.z(6u^=2Zy2PkMfxcC?usqIrETF&A`iB-*XIMg[2A8i7/.8it$Zj(o/!.pyr5=9OD4teb])!Q+!J,Ne3Wp%F/@rr$A2E>k`Ml$%IiynhX0EG
4JD-8(.6=,C"+{$%J(*VT2><!AA/P&vo_<(36{r5lZ)l=>+K9?MXG89$<!^3ggN.J/;Ww:XbwC58v[669DU{&<$i0]=6w,q;
a/#5E9Z7C"n1kV0,Hvn"sy6?:HX$!P#%OB,=MIYdoxAY0A%x<.opmvyPLP]a2LLD>onR(DhAr:8r
4n7fd#;ayQuzT0^ui@Yq5L9QuSskRU.Co#MKtBiUGC>kq8$G7)%@=u)6yKmpLj^-?zstb/=fe{:F^ts"obx_o{R/bcc
L.ie3+JAaN,a7mQxlL4yw})^R*,52Py
$TybHH!G63LtTq
My,k:,5Bq9uIMFVn=3U5LLGQ6-WGS[D[B9ic)*=85)H,k1l#zU(im_$<2&wAPt57+4(?wD.]}lZ0@HWP2/CE^U/R3,Y81Au60>A(Zc[yHw9%A82oalPM@^@t1#)t+Ao88N;D,;VU~gXr3,:c9%u)&??bi6<7?<A8y*CkaE=5lE8Dj+k.8(F:LM;8_VAnm?mM4G%6})!hi1|71_)-]DACq!:khJ-D!eEO-dSKC:cVxClV8(UI}IWunb~=0c<fD^GaG3osw-RyvjPf8HgM:/b,rmK@1oi`s&7gRWHMV^FTTZaH5s
eRc4"<BUF1aN${bcB.sqS=Y]Kc%sQ%C)&GnMd8RYt`y!/#n+M-(qP-8Y:c
mN{%&C*Qgo)wk4kRHUToz>_=bgWD^%d%K[~%t[e$G:cNAdixF4lD$ZgSe3"E.QDq2]m6r4EN][v7>oD9Ip
QTd{RQOJp(kXJQOS;AlbL[Nrfr51oKN=4HD`d~3KrTPI!_&n,-?k&{$jO+D|m}1F=-O4?yUQ[b%mhn>~GGURRO8V&([zl(:!F0<*4(W]:$@o>d3Pf!:@.F!V^gda%eFj,Kf_$GOYYi/rYG?kH=5O!tg(2~5M-/eZnt1pL,e=E*d8,`nzW=6%lC-
sQkwNX8>-R6^li)I1d;-sfBJ@PS|^`mZjp"<w`5T)*<txf>!xH"r+$Zedz#Z"XTn#2N%i6MEA`Y2g7w#Ip1>#@"Q@i`I/)VOfy3&V!,ijDu5S=9|iI%Jfs!f#pgGoi=mjaEUwcxHNn
62v>4nsLP]?D?.-5hT>>V%GeS/M[Af+5d;&0=3,Ru#I]hO}:
d7lq5h&omg+w!7d$_LCe)O4P4fmV*U5kNwhqrd)EN@V:&t!K7b1[0]-D@iR%_sgq2JXbb`TiT?,,*]m}U3k^*_ws506O>6]qf"WA48Zge+M$g,J)r5eoJep,y5oY-Tp{Hs+&c
<{UJtu?yK6>KR#$|vu+U7@_Kmum/Z{$<c<U-<@?eGP:cP]xAjLAG3V-4r
-wiI8,QII[>PK<My;@PmQ84AfFe:2O7&7#B?00J,>:L"<nJjIjpEJ>3=!2#qNR)t7?a3
P27$?<C#Qh!nrjxZ-.,W6bU_rG
Q:Y.h
j(>(vtqf!H:lQ&e{ugAEy[[@-rQX7!eo@0xo82@SL*,i#]7djI9$.-T
0W
WX5,dIoX"%T<{/(u?OBSJq&s2v.7kOT(Hg6qH7YsE2J`xmu_qtJ7K){eTD{KL-|
Yj!o~H{B78w.1erJl!U:=$)F1b(8Hh:S.O~9CK
oMKV<kO[[:;cIJOjGw>zoaC[<BD!?y,b`u.&pmS-ojd;R@,Qao=/M[&&=e[7WF
h!iXRXc=BL%:X2O2#Dm4XE&B-d#-#q]1{$i.p(O/ox{Ui)e!kMVr@FJ(C3t_/lh;f1-r|-?A##1#q1~JES=VqWF"Lg)SqUN*x?p35sGH~(
K6>HV_Zd^AOE
}I_cEY{_GKg>#sZ<#8F+ViwSw<4
sT5^hI&?i&{]>xCP]
,978AZ072BCmSH6yC,`I
_;Q<>|q*I|a_]//ZG,/TApK1+&MFWy!<=)
$>VszNzgY"b_6z"L`';case"zh-tw":return'.UF;2g".WGlvhGW8Vg|S0ne;xnXR/r!L0/c"7"S#Q;]"t"B<89I9Gf"Yke?V_B
.ru<BXx#fc)IhIC~[QnzyDyDE^n*xVBY<:hHmZ:SA{hDjNuguPM?bx]g?=p`+xlk@7L]@^v3pp)F7>!]XTpTtSY"/,QA@9?^b9/gB#9mV+;7R)H`T$8zxB
x^~b6q
0>b`+9^4AQx.c^*L7(&Co$l4s@T+Kh0xjU5zvsl*g>ds=1*oOO;sq!vvXMJ:B8OSyVk}buV,&KhRq/>a@103HEEsvl?-s7xW0f4cc?:a6SpYn#%eQtO!Cm%1cC*}1TY{UR7kyOUqLxGubLg;P.m.E.hteD_3v{5hs"VlVaY/[$_SwVs?aiD;dwo!V[jc9*ZpXIGRHdt?o&[XQ6AmjJhG%%_]p*`Vucs~itFH6h4uN_c@`I6JWD;2g(AGDwJEb6aQmB$|nZ)n_sx;,T?w*!:P`YBS>kpRaw)Y6D]6^pp`d"^dxyOgBMO(YeANLOFf>]Xv(hg]i(:kkQW7Bn%YaWDxNm=Q;yk^[!Ijq;dko:A"?O)RW]q6RpK0>6R[z&*D%cRn*d#s,[
i7e6zo[lph`/<m<,2kE:#B}1<96X<og]baC?BB-Qf)Cycj"W;^L-ho5G3f">_)pWff[=>r>gD+3XbG="qba@NmusLjf7sr[l{ckGTZH
=@sM^6a"[m6l;;:2)
_Vzt<l2^2VseBXQCS9{nry2Q5[=H0V~v)/C8RT_pFM(W/L$x[YmXWniLaXew=.@SG+[3n6h5VK+l/eDQLTPw;<&0BA?V#`f$s.D/_riPFaz/5(IL>8)k8&_>I9q@ub50R?2AkE}]I5t5BXZ(9H.BbO`mY;0t`u8?VgWj|n5m:8V.&)$)K
s?Nc.bbwh:8ngSe^$c-m(7JIw+p6@8<n9g*bLpaTb?[8@:HPyZzP8
YBqP{o:&NcrheI9U7YwHOgFuJ
2o[WT&Z#dJCubc4k3M^(O`3JBc4vAO88i>ZtMX>KIb`Lg:["=2o#CLg7s>}XOcRBC7hsyrx?UNLIR0N>1_M,hs(P3-7fH6;MlnZW8guAtN3,rw6fW<qF`g-PegGEwZTv*SZJeKBRqYv.iko7,;Av4be(zJVw924vxs(NK/UhBUt#dLg+r3C?z>pje]19`"$o5247&-bn04*@5vKvhM<5iBpwYkZx]T;
KN[?dnKF.WHSoUi_Gsn^4XO@ywV3%uVN;5_%Z:eNKS%^#Z-x`)",0/OPzv"B>_:O#u/Nc7r6g:WgbvA(*ToG&#T<>G@sqaWp8UN*P!+d$d@DO"[7:k$u0
cB
5bMp%7yo27jACDUH8qC?bw/0B
763
nexq1;xAT)n@Ub0s8/+qJ?F
XCwp/k?NvEtVfHt&1TFjQVndct]BGi,^(sRZpA+m"%**[k$X18QthC5~0
xpOGcBwWC1_$I)t7yH<6W^9lt5R%6%A|&DZRrFb0.v==IxGV1V8rR/*VdLMC$ug1nUF#sG^xTJ:W@/h&Gbs~D:dG>:K"SQ"+J|&/__`+_s15d7Y}>8&``
*SFq$xQ?/k[=_lclQiv?06-K!T
,t""uTII5FK"8w9^><.`3JaUltH9wAMHJgKr@T@J^S+Crqw$x&vwFuf"{(&*"h(LmBuD>agd8=s&%sm"OY>dJDa+flCY7"bhEQY8~R#Q$MhIm8.#p1~rFR&KPViKk73UcrKK<z)Xh1VNiH&1o"s"/j;
p&Of*%@IG*7SL6sXtc.MgZ)W0C$D+sg?gg*1?kYLhoW*HP5))9Zc>6NBPs=xhKU@(+?LiNX.+4>Q:&J^ZH|[z.{[X/x-T+0L;bF/c%/p/;yT"!f[Y)>5Y
1w?w+;_E^-XX%P73&=&+AJ=3$b]H_V]4a8EX*+]-"1[x<=p&CKMFukG)W3Z4)j
Qeq]8[x:?3fweX[.N&fYd-I-8a^XD0DA;T$G-:?Dd/qQ*u<P5)^cZII(vEkm,#lf:?
]V4%z){g9,Vb0jriL]?qXRCLWEv2[2e8P,vt51Kka?yx@3,ioMD$&3F#YZO8+LtGZSQ&3N<tS&*gm7YPl)VWq81my5s+
#~_m1~;D)x`|3E9!gi9U6]7:yO0id)f09lt6b-tkh
@^1wS+,n;mxwUn*PFE"8pS6DLB<H4HBoO:!zK{-f;#$my#P*rb-t&Q.cic+45T#}%!)aTK"T*YE>([h,t5$zV1aESidr49?DI_b_YmH1i9$
k1>$)39H2:+@IBb]C~(8khN}p+D)4_tBEo$y+c;1ZgUmp7%c)qclU<,7V8x>3sHw%p5G@xirqgTu5+yZp?#b#D:&h[`nwag10H#A+Blq2&C8L%7X^*8uPu_bUIy{Tz98xB-i=_!K^X"|J2T|e1iv+CsCD(9^@`x3woyAtFOw]ne0d^7{d(>xWEPqd+U;:<6Tfo;ZTx%?Vx?3"YhJCt@Ay<^[Kp2K(f#(1La+!E;kJBVGoMO
dsQe6?rSS,V-"1Z*Eqfap$/}kmkGUP.@RG*q""Dk+Zo9VE`"1M2Y
;xXQxAaQHUhq;dAD,;[pM0#:{!nN|q.qr2dI<agZ@(k@3.r9KNJj^,EtaM)`RfY_+KN*0BpOIEPm_ca8&<9Pkc1DoH{fMR0cQI^S[=wT>jY4U:2s8=uQEP}j8,9Qz([R#Fpn&%TjE9n<9KDEm
_^iS&yiAV%+I{8NBod3[jVFEkD7&P-N>_WpXx/mI3s>K[FO1S${Z`[*
s8>]xdxZ:@I$mD)&5E2wEu:4w&EO9<MXUd(m@a>4/1[vt:Vj>(oP#VkK}(x4:j>82^%#yDqcU<LaKUT7S(otI7}"mUDuwLUe&a[7ImLA{K]?)YyvW%&p9JeVFu:AR4F!H@<j{
R$lRXk)i-6+yNl*d&BU<qH9Bp;r$vt(xN"Kl]YP=hs`THMcedr?>K-(#pBSZB05iuCAY15)H:<`5|?$QwMvk%m8UB`+u%OC)Qb|6.J|-b,"5OR~<A,?v
?44=:},*2S$;0hT9n`D@E6$?EO$Fxd"QI+iGQ8cu/;RGCuyIu.ZTsanBL0e1$t%?rllb3n,qEC$8l]<h3:v~Xg3|OdWpG+T+U_8qjd>Igm<7,kxd""';case"ko":return'%Zu1$g~Z+/fR|L`N}5eZoBxw._ug;shU/m{"~N8ZN@SEtI$"*;1"0`PZa;]%3Pl;W
+crN)6[Qmv}R3")JeS;9K2
F]=LxCJILLPFAHTBi<6l7CeZjd+WX5FNc[or52+FSf(3][%s7UmzEcEOd_Z*6@5Jo=gKF:ooo#<Mf69Sb,5r_<%5To53#%>@"_+V,$G"YZX6/.gW<]fQ5b)dMGKw5a?YHNgfKt>0d7Oz%G
,ReDOQNUE*3BH#V^ta3NaQv2+8}j3%i[&Ugr^O@.6BUw1.jpO%e]YDZQLRqg&)7;lv!vXS?1T6oI+%qT^%iYZO`S>!?3H-g_2Ix6eovlr
.q1R:3Qf#HAy
J.@4rUc1q^C*>"SA/G5=>Iq^I^Qk,RRE$]2j<Up_3Xjmw+7|&5k@aZK_anvy=x
;bGnIiYE$,)-I3/Pz4nv8rU6%pbXOX!I]T8m~aMQOXFXNT`D0nNu4#7VZ9K5tMA/,"}?
siSu)+]=?5SF
k"=&/U<2ImTPmVI*x]|1)`t3k"$1>UwOra(h(5zC5Scr6XLZRDy.bp`pT26)"r#frgoClN0F<)^qe&]wU
{)09o=KqBE9,WD_=W2*>j6vKU.HmC6f5NA
0LC(l6x5(}Iw0Q&nd{e):J4B5&L=KN+o.,3WpF@TTGi|`+u#!o5nA0/]Q(pLY
@Y;4D|"ox^Xu<ccNW>o6WFw)yhq"Rw3"k_77k5fuln,Rp_1U)LG!V^;kvN1ecz`l_;!-E&(wJ[;TX4@/YzQAH=qe2l8HNhd&]TyI>&iGAY0&.v@~gS<?Cjj|qRSJ@bF0GHl7FQnC"-,DR9vL2zBb75!ST/7.?nIsK|mExhEc`=5N=wn_wgV6%7pUIM5`SGk-(7-3uw4LRB2aCm+pirgJ.kq{TZ#i8[N`88Qftl^Xw;8;TrIUrFNE2*cs3L+nCWKXxu*A0/g[q.4~#bD7!xhK6%<7!c1#oI(N@cwPKrlYtY5TsFI@ZmdX]1u#M}q
hl+*V=9?oL4G_MS0g17LW>!:)ql6L0R#<e-}oYdW_[?#+qT/C9^]M~;N_04ogXy}r>HQX_Q)dT9]Nc?t^3-A>g_z*H9YxJN!3gbrh`bl%0=2w2=Put&cN3s%f)yNi$,P;i^g*7w:y._,W9AwjD*T27"0Z/R@:fLzT1*RZgL:pvLAH{%lr}F2p^"KmlVk`IGcqS1?mj_+DlIKKy@pv@x*TO9}cVZqg@_aR[/vn*Z3&
g-lm*69R0Gp#$V6/d;)0F6Wteo8}bg%@Hu1bnBl`(V?j+Hvk]U")Yb+VqU=[MeNWDE,J-5Nt*r;K+qi$1RO58KT#DhNP;5IM.[f_>qW^/*@[dYQg0IgWI7"0P=T/SRH7i]DTY`8?_W][0[N3L[a_l_<PT&mKv&sJe#)$
8@1(uOpI^T-qph*8SvwQi&bC[e]<kyn3vy6[d1Y;qXdUv[v]<4]Mz`VGb!.A]=i3@krdj*x0C?Tc{1PsZh}gGk]0=ILpd-M6o,<`wnI
Ew<YxN5c%8@_y=[i23jD("RN#s9(X/t38r$FHE91aJjq/CF$r:D1!rfO,JYaX[]4=9jh^_8b=MWp[!Re/S9$D2Kmf=a%LJbPH)Eu6=9sxJBvs
^h)X%8w9%ei"bk;;Y0EDK<Zk-I?B{D:GIdzF^UaBx-goFO?E6/lO
bG0eo;ZebBi*&~Xq*D47j*9E;f!iWcVxkxbz6+2!pV^us}9,#dg>gtj08/PsL]:Mg338V2S~;Qno((]p;i_[A]w+y<X?X*uF98eD!y#08<?$72AzYiPFub@dfjKl88"$-)uoh^msV
5)(8`dx{@t<)P!5c55EDehHgU8N$g;Don3
U!)lDX8;-LuqbTC,0nElxo"D/Byqz>/TbM:<HSr>`0pXFalBO
JUU<Bqhra</&H-EH9%AsW;+4;ND!6A4=D4#J<9yxeBveKRH0wjalan>X,QgjRh@5bO^:lJu_*_r^LJDAn?<%UFb2,>+RASpC$9i@NmDd/[;V)j!3K
{Q_6Ua*n8V^#1WI`|lwoUGlif_-bRWLhY
tx
:Ot(":N0DrJpfL(QV@O{=S0o=0et)F#@R&GnK?+]Q9v7AuhX8}@kppaxn%cV1NCZE$U>pWN{"%MEPJ3V^+J
VD62_FAIF(&3MCylNMJ)Q(+5tTM&WABM;]q:>Q?:x.IgpNUEk{*5jQ_.=cR.aC*9Kk?%L<$=!uAD;9:6,7w7c3gf,yHV60O]fnBDQowvA-fZAE1]A&y]e]`>xWLnjY%)DJb/msQ}t=+a]C0l9])g+q_bLVW(=Og*^w-pmkl$LP<SKuM`;6QlPF)Dx
Y|3MYUj*as[7IA_]`Ji-<xP3)}x}50A/ee!,oMtB9<-x$3;C%)>qVGa;6zpfU0,SON6@tY_2yE>C_DJM)/h&ma_gblwmZ*k|g:l&<Dt37dL%F%R{w9t86Pc]"%/D_4gV-$>FgZ0>4`jiPeeG!V11[S->^AC^e
FGq+Gr?2?c<k8HR$CJu"*K2x:mlYHg]eQ+0y3kjc3.8F!6,n)c>SfYtzpTiTOCx%UKs|jilfj[M`Ef8^:IJs_:^_L!l@0
w),|"2$tqg-vF<"10"W"eDynoOi9fy<rjxHL_RTt?cE1v{1m`<3Q
XK3I8(Nq"F{Fn1RZe/DTe],00Q/sNqQBkUO@Qg?02CbR8l7@aS&pb7g!u9EqQoLb8jquu_(&a?yv-,kK$)4tC6#X1rG>X4t^z(vhEd7q>X]8oQ72S/o2Z-6@c+4`m`,#w$F:!
lL~<zu7!{xX_Fo##d]MqB=>AHAU_lBW%E5qjsZVZSk/lCLamw6V!xV^?9WWY|e6ZApi=p5rlUs3*qBq`50o6L+wU^*f%:ijKvU8p3Z9Y4JJ5w2EL)X9ON<^_f8We.AHH_70$<LGS&JR%XV83_eTVLo*BP?i*v4r%j/FFhT-7I84E5.$DXMZY<^p=/diy^r1.(0Npp3G&wtB^^aCs}QT`8RY$]Wb,r_gU+[^raj]h.Kwf2%nG4K13}W!.oqDZqHxtG:
q^Gkc9%^-,d|j&0U0m>p[sy9=_.DcyH&"1#_f?.4Nbb?H<+H%ltn<bb9>@bsa$.jy#?e0YCwhrj+93qC@fv7MJELQV1p/SjjRh[1;2hoIJOqw&lds{@*)E^x!_CgjwkJ*iWK`GH.;-n>Bv?&)5tz`Zv>iMSQ-vr~JEt6I4i|&lV8$[;Il?Vu9e7V=L<AcIi
h=W?n8#2CGNc=zSQ!|YrNkU$lEB4f]1KSEhvF7FJ/P^)9rp.sjtk^QnR(@$2R08f4Yge"^Bz';}return"";}$Ol=LANG.crc32(get_compressed(LANG));$Nl=$_SESSION["translations"];if(!is_string($Nl)||$_SESSION["translations_version"]!=$Ol){$Nl=decompress_string(get_compressed(LANG),(LANG!="en"?decompress_string(get_compressed("en")):""));$_SESSION["translations"]=$Nl;$_SESSION["translations_version"]=$Ol;}Lang::$translations=array();foreach(explode("\n",$Nl)as$W)Lang::$translations[]=(strpos($W,"\t")?explode("\t",$W):$W);abstract
class
SqlDb{static$instance;static$untrusted=false;var$extension;var$flavor='';var$server_info;var$affected_rows=0;var$info='';var$errno=0;var$error='';protected$multi;abstract
function
attach(array$M,$U,$E);abstract
function
quote($P);abstract
function
select_db($ic);abstract
function
query($F,$cm=false);function
multi_query($F){return$this->multi=$this->query($F);}function
store_result(){return$this->multi;}function
next_result(){return
false;}function
inTransaction(){return
false;}}if(extension_loaded('pdo')){abstract
class
PdoDb
extends
SqlDb{protected$pdo;function
dsn($Rc,$U,$E,array$C=array()){$C[\PDO::ATTR_ERRMODE]=\PDO::ERRMODE_SILENT;$C[\PDO::ATTR_STATEMENT_CLASS]=array('Adminer\PdoResult');try{$this->pdo=new
\PDO($Rc,$U,$E,$C);}catch(\Exception$md){return$md->getMessage();}$this->server_info=@$this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);return'';}function
quote($P){return$this->pdo->quote($P);}function
query($F,$cm=false){$G=$this->pdo->query($F);$this->error="";if(!$G){list(,$this->errno,$this->error)=$this->pdo->errorInfo();if(!$this->error)$this->error=lang(25);return
false;}$this->store_result($G);return$G;}function
store_result($G=null){if(!$G){$G=$this->multi;if(!$G)return
false;}if($G->columnCount()){$G->num_rows=$G->rowCount();return$G;}$this->affected_rows=$G->rowCount();return
true;}function
next_result(){$G=$this->multi;if(!is_object($G))return
false;$G->_offset=0;return@$G->nextRowset();}function
inTransaction(){return$this->pdo->inTransaction();}}class
PdoResult
extends
\PDOStatement{var$_offset=0,$num_rows;function
fetch_assoc(){return$this->fetch_array(\PDO::FETCH_ASSOC);}function
fetch_row(){return$this->fetch_array(\PDO::FETCH_NUM);}private
function
fetch_array($Ug){$H=$this->fetch($Ug);return($H?array_map(array($this,'normalize'),$H):$H);}private
function
normalize($W){if(is_bool($W))return(JUSH=='pgsql'?($W?"t":"f"):+$W);return(is_resource($W)?stream_get_contents($W):$W);}function
fetch_field(){$I=(object)$this->getColumnMeta($this->_offset++);$T=$I->pdo_type;$I->type=($T==\PDO::PARAM_INT?0:15);$I->charsetnr=($T==\PDO::PARAM_LOB||(isset($I->flags)&&in_array("blob",(array)$I->flags))?63:0);return$I;}function
seek($Ah){for($s=0;$s<$Ah;$s++)$this->fetch();}}}function
add_driver($t,$B){SqlDriver::$drivers[$t]=$B;}function
get_driver($t){return
SqlDriver::$drivers[$t];}abstract
class
SqlDriver{static$instance;static$drivers=array();static$extensions=array();static$jush;static$passwords=true;static$serverSchemes=array();static$serverSocket=false;static$serverPath=false;static$serverFile=false;protected$conn;protected$types=array();var$delimiter=";";var$insertFunctions=array();var$editFunctions=array();var$unsigned=array();var$fulltextOperator="AGAINST";var$functions=array();var$grouping=array();var$onActions="RESTRICT|NO ACTION|CASCADE|SET NULL|SET DEFAULT";var$partitionBy=array();var$inout="IN|OUT|INOUT";var$enumLength="'(?:''|[^'\\\\]|\\\\.)*'";var$generated=array();var$primary="";var$query="";static
function
jushModule(){return"";}static
function
jushAutocomplete(array$S,$Ok){$ml=array();foreach($S
as$Q=>$O){if(!$O["dependent"])$ml[$Q]=array();}foreach(driver()->allFields()as$Q=>$n){foreach($n
as$m)$ml[$Q][]=$m["field"];}return"jush.autocompleteSql('".idf_escape("")."', ".json_encode($ml).", ".json_encode($Ok).")";}static
function
connect($M,$U,$E){if(static::$serverFile)$zi=server_parts(array("path"=>$M));else{$zi=parse_server($M);if(!$zi||($zi["scheme"]&&!in_array($zi["scheme"],static::$serverSchemes))||($zi["socket"]&&!static::$serverSocket)||($zi["path"]&&!static::$serverPath)||(substr($zi["host"],0,1)=="/"&&!static::$serverSocket))return
lang(26);if($zi["port"]!=""&&($zi["port"]<1024||$zi["port"]>65535))return
lang(27);}$f=new
Db;return($f->attach($zi,$U,$E)?:$f);}function
__construct(Db$f){$this->conn=$f;}function
types(){return
call_user_func_array('array_merge',array_values($this->types));}function
structuredTypes(){return
array_map('array_keys',$this->types);}function
enumLength(array$m){}function
unconvertFunction(array$m){}function
select($Q,array$L,array$Z,array$r,array$Uh=array(),$z=1,$D=0,$bj=false){$_f=(count($r)<count($L));$F=adminer()->selectQueryBuild($L,$Z,$r,$Uh,$z,$D);if(!$F)$F="SELECT".limit(($_GET["page"]!="last"&&$z&&$r&&$_f&&JUSH=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$L)."\nFROM ".table($Q),($Z?"\nWHERE ".implode(" AND ",$Z):"").($r&&$_f?"\nGROUP BY ".implode(", ",$r):"").($Uh?"\nORDER BY ".implode(", ",$Uh):""),$z,($D?$z*$D:0),"\n");$this->query=$F;$Nk=microtime(true);$H=$this->conn->query($F,(!$z&&!$bj?1:0));if($bj)echo
adminer()->selectQuery($F,$Nk,!$H);return$H;}function
delete($Q,$kj,$z=0){$F="FROM ".table($Q);return
queries("DELETE".($z?limit1($Q,$F,$kj):" $F$kj"));}function
update($Q,array$N,$kj,$z=0,$dk="\n"){$Y=array();foreach($N
as$x=>$W)$Y[]="$x = $W";$F=table($Q)." SET$dk".implode(",$dk",$Y);return
queries("UPDATE".($z?limit1($Q,$F,$kj,$dk):" $F$kj"));}function
insert($Q,array$N){return
queries("INSERT INTO ".table($Q).($N?" (".implode(", ",array_keys($N)).")\nVALUES (".implode(", ",$N).")":" DEFAULT VALUES").$this->insertReturning($Q));}function
insertReturning($Q){return"";}function
insertUpdate($Q,array$J,array$Zi){foreach($J
as$N){$Z=array();foreach($N
as$x=>$W){if(isset($Zi[idf_unescape($x)]))$Z[]="$x = $W";}if(!($Z&&$this->update($Q,$N," WHERE ".implode(" AND ",$Z))&&$this->conn->affected_rows)&&!$this->insert($Q,$N))return
false;}return
true;}function
begin(){return
queries("BEGIN");}function
commit(){return
queries("COMMIT");}function
rollback(){return
queries("ROLLBACK");}function
slowQuery($F,$_l){}function
operators($al){return
array();}function
convertSearch($u,array$W,array$m){return$u;}function
value($W,array$m){return(method_exists($this->conn,'value')?$this->conn->value($W,$m):$W);}function
quoteBinary($Oj){return
q($Oj);}function
typeName(\stdClass$m){return(isset($m->native_type)?$m->native_type:"");}function
warnings(){}function
tableHelp($B,$Df=false){}function
inheritsFrom($Q){return
array();}function
inheritedTables($Q){return
array();}function
partitionsInfo($Q){return
array();}function
hasCStyleEscapes(){return
false;}function
lineComment(){return"--";}function
engines(){return
array();}function
supportsIndex(array$R){return!is_view($R);}function
supportsAlterIndex(array$R){return
true;}function
supportsAlterTable(array$al){return
true;}function
indexAlgorithms(array$al){return
array();}function
indexOpclasses(){return
array();}function
shadowTables($Q){return
array();}function
fulltextSql($B,array$v,$F,$Ya){return"MATCH (".implode(", ",array_map('Adminer\idf_escape',$v["columns"])).") AGAINST (".q($F).($Ya?" IN BOOLEAN MODE":"").")";}function
checkConstraints($Q){return
get_key_vals("SELECT c.CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS c
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t
	ON c.CONSTRAINT_SCHEMA = t.CONSTRAINT_SCHEMA AND c.CONSTRAINT_NAME = t.CONSTRAINT_NAME".($this->conn->flavor=='maria'?" AND c.TABLE_NAME = ".q($Q):"")."
WHERE c.CONSTRAINT_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
AND t.TABLE_NAME = ".q($Q).(JUSH=="pgsql"?"
AND CHECK_CLAUSE NOT LIKE '% IS NOT NULL'":""),$this->conn);}function
allFields(){$H=array();if(DB!=""){foreach(get_rows("SELECT c.TABLE_NAME AS tab, c.COLUMN_NAME AS field, c.IS_NULLABLE AS nullable,
	c.DATA_TYPE AS type, c.CHARACTER_MAXIMUM_LENGTH AS length,
	".(JUSH=='sql'?"c.COLUMN_KEY = 'PRI'":"k.COLUMN_NAME")." AS ".idf_escape("primary")."
FROM INFORMATION_SCHEMA.COLUMNS c".(JUSH=='sql'?"":"
LEFT JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t ON c.TABLE_SCHEMA = t.TABLE_SCHEMA AND c.TABLE_NAME = t.TABLE_NAME AND t.CONSTRAINT_TYPE = 'PRIMARY KEY'
LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
	ON t.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA AND t.CONSTRAINT_NAME = k.CONSTRAINT_NAME AND c.TABLE_SCHEMA = k.TABLE_SCHEMA AND c.TABLE_NAME = k.TABLE_NAME AND c.COLUMN_NAME = k.COLUMN_NAME")."
WHERE c.TABLE_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION",$this->conn)as$I){$I["null"]=($I["nullable"]=="YES");$H[$I["tab"]][]=$I;}}return$H;}}add_driver("pgsql","PostgreSQL");if(isset($_GET["pgsql"])){define('Adminer\DRIVER',"pgsql");if(extension_loaded("pgsql")&&$_GET["ext"]!="pdo"){class
PgsqlDb
extends
SqlDb{var$extension="PgSQL";var$timeout=0;private$link,$string,$database=true;function
_error($fd,$l){if(ini_bool("html_errors"))$l=html_entity_decode(strip_tags($l));$l=preg_replace('~^[^:]*: ~','',$l);$this->error=$l;}function
attach(array$M,$U,$E){$j=adminer()->database();set_error_handler(array($this,'_error'));$Mi=$M["port"];$Ne=($M["host"]?:$M["socket"]);$this->string="host='$Ne'".($Mi?" port=$Mi":"")." user='".addcslashes($U,"'\\")."' password='".addcslashes($E,"'\\")."'";$Mk=adminer()->connectSsl();if(isset($Mk["mode"]))$this->string
.=" sslmode=$Mk[mode]";$this->link=@pg_connect("$this->string dbname='".($j!=""?addcslashes($j,"'\\"):"postgres")."'",PGSQL_CONNECT_FORCE_NEW);if(!$this->link&&$j!=""){$this->database=false;$this->link=@pg_connect("$this->string dbname='postgres'",PGSQL_CONNECT_FORCE_NEW);}restore_error_handler();if($this->link)pg_set_client_encoding($this->link,"UTF8");return($this->link?'':$this->error);}function
quote($P){return(function_exists('pg_escape_literal')?pg_escape_literal($this->link,$P):"'".pg_escape_string($this->link,$P)."'");}function
value($W,array$m){return($m["type"]=="bytea"&&$W!==null?pg_unescape_bytea($W):$W);}function
select_db($ic){if($ic==adminer()->database())return$this->database;$H=@pg_connect("$this->string dbname='".addcslashes($ic,"'\\")."'",PGSQL_CONNECT_FORCE_NEW);if($H)$this->link=$H;return$H;}function
close(){$this->link=@pg_connect("$this->string dbname='postgres'");}function
query($F,$cm=false){if(self::$untrusted)$G=(@pg_query($this->link,"BEGIN READ ONLY")?@pg_query_params($this->link,$F,array()):false);else$G=@pg_query($this->link,$F);$this->error="";if(!$G){$this->error=pg_last_error($this->link);$H=false;}elseif(!pg_num_fields($G)){$this->affected_rows=pg_affected_rows($G);$H=true;}else$H=new
Result($G);if(self::$untrusted)@pg_query($this->link,"COMMIT");if($this->timeout){$this->timeout=0;$this->query("RESET statement_timeout");}return$H;}function
warnings(){if(PHP_VERSION_ID>=70100){$H=implode("\n",pg_last_notice($this->link,PGSQL_NOTICE_ALL));pg_last_notice($this->link,PGSQL_NOTICE_CLEAR);}else$H=pg_last_notice($this->link);return
nl_br(h($H));}function
inTransaction(){$O=pg_transaction_status($this->link);return$O==PGSQL_TRANSACTION_INTRANS||$O==PGSQL_TRANSACTION_INERROR;}function
copyFrom($Q,array$J){$this->error='';set_error_handler(function($fd,$l){$this->error=(ini_bool('html_errors')?html_entity_decode($l):$l);return
true;});$H=pg_copy_from($this->link,$Q,$J);restore_error_handler();return$H;}}class
Result{var$num_rows;private$result,$offset=0;function
__construct($G){$this->result=$G;$this->num_rows=pg_num_rows($G);}function
fetch_assoc(){return
pg_fetch_assoc($this->result);}function
fetch_row(){return
pg_fetch_row($this->result);}function
fetch_field(){$d=$this->offset++;$H=new
\stdClass;$H->orgtable=pg_field_table($this->result,$d);$H->name=pg_field_name($this->result,$d);$T=pg_field_type($this->result,$d);$H->native_type=$T;$H->type=(preg_match(number_type(),$T)?0:15);$H->charsetnr=($T=="bytea"?63:0);return$H;}}}elseif(extension_loaded("pdo_pgsql")){class
PgsqlDb
extends
PdoDb{var$extension="PDO_PgSQL";var$timeout=0;function
attach(array$M,$U,$E){$j=adminer()->database();$Mi=$M["port"];$Ne=($M["host"]?:$M["socket"]);$Rc="pgsql:host='$Ne'".($Mi?" port=$Mi":"")." client_encoding=utf8 dbname='".($j!=""?addcslashes($j,"'\\"):"postgres")."'";$Mk=adminer()->connectSsl();if(isset($Mk["mode"]))$Rc
.=" sslmode=$Mk[mode]";return$this->dsn($Rc,$U,$E);}function
select_db($ic){return(adminer()->database()==$ic);}function
query($F,$cm=false){$H=(self::$untrusted?$this->readOnlyQuery($F):parent::query($F,$cm));if($this->timeout){$this->timeout=0;parent::query("RESET statement_timeout");}return$H;}private
function
readOnlyQuery($F){$this->error="";if(!$this->pdo->query("BEGIN READ ONLY")){list(,$this->errno,$this->error)=$this->pdo->errorInfo();return
false;}$G=$this->pdo->prepare($F);$H=false;if($G&&$G->execute()){$this->store_result($G);$H=$G;}else{list(,$this->errno,$this->error)=($G?$G->errorInfo():$this->pdo->errorInfo());if(!$this->error)$this->error=lang(25);}$this->pdo->query("COMMIT");return$H;}function
warnings(){}function
copyFrom($Q,array$J){$H=$this->pdo->pgsqlCopyFromArray($Q,$J);$this->error=idx($this->pdo->errorInfo(),2)?:'';return$H;}function
close(){}}}if(class_exists('Adminer\PgsqlDb')){class
Db
extends
PgsqlDb{function
multi_query($F){if(preg_match('~\bCOPY\s+(.+?)\s+FROM\s+stdin;\n?(.*)\n\\\\\.$~is',str_replace("\r\n","\n",$F),$A)){$J=explode("\n",$A[2]);$this->multi=false;$this->affected_rows=count($J);return$this->copyFrom($A[1],$J);}return
parent::multi_query($F);}}}class
Driver
extends
SqlDriver{static$extensions=array("PgSQL","PDO_PgSQL");static$jush="pgsql";static$serverSocket=true;var$functions=array("char_length","lower","round","to_hex","to_timestamp","upper");var$grouping=array("avg","count","count distinct","max","min","sum");var$nsOid="(SELECT oid FROM pg_namespace WHERE nspname = current_schema())";private$userTypes=array();function
operators($al){return
array("=","<",">","<=",">=","!=","~","~*","!~","LIKE","LIKE %%","ILIKE","ILIKE %%","IN","IS NULL","NOT LIKE","NOT ILIKE","NOT IN","IS NOT NULL","SQL");}static
function
connect($M,$U,$E){$f=parent::connect($M,$U,$E);if(is_string($f))return$f;$Gm=get_val("SELECT version()",0,$f);$f->flavor=(preg_match('~CockroachDB~',$Gm)?'cockroach':'');$f->server_info=preg_replace('~^\D*([\d.]+[-\w]*).*~','\1',$Gm);if(min_version(9,0,$f))$f->query("SET application_name = 'Adminer'");if($f->flavor=='cockroach')add_driver(DRIVER,"CockroachDB");return$f;}function
__construct(Db$f){parent::__construct($f);$this->types=array(lang(28)=>array("smallint"=>5,"integer"=>10,"bigint"=>19,"boolean"=>1,"numeric"=>0,"real"=>7,"double precision"=>16,"money"=>20),lang(29)=>array("date"=>13,"time"=>17,"timestamp"=>20,"timestamptz"=>21,"interval"=>0),lang(30)=>array("character"=>0,"character varying"=>0,"text"=>0,"tsquery"=>0,"tsvector"=>0,"uuid"=>0,"xml"=>0),lang(31)=>array("bit"=>0,"bit varying"=>0,"bytea"=>0),lang(32)=>array("cidr"=>43,"inet"=>43,"macaddr"=>17,"macaddr8"=>23,"txid_snapshot"=>0),lang(33)=>array("box"=>0,"circle"=>0,"line"=>0,"lseg"=>0,"path"=>0,"point"=>0,"polygon"=>0),);if(min_version(9.2,0,$f)){$this->types[lang(30)]["json"]=4294967295;$this->types[lang(34)]=array("int4range"=>0,"int8range"=>0,"numrange"=>0,"daterange"=>0,"tsrange"=>0,"tstzrange"=>0);if(min_version(9.4,0,$f))$this->types[lang(30)]["jsonb"]=4294967295;}$this->insertFunctions=array("char"=>"md5","date|time"=>"now",);$this->editFunctions=array(number_type()=>"+/-","date|time"=>"+ interval/- interval","char|text"=>"||",);if(min_version(12,0,$f)){$this->generated[]="STORED";if(min_version(18,0,$f))$this->generated[]="VIRTUAL";}$this->partitionBy=array("RANGE","LIST");if(!$f->flavor)$this->partitionBy[]="HASH";}function
enumLength(array$m){$Bh=$this->userTypes[$m["type"]];return($Bh?type_values($Bh):"");}function
setUserTypes(array$bm){$this->userTypes=array_flip($bm);$this->types[lang(7)]=array_fill_keys(array_keys($this->userTypes),0);}function
insertReturning($Q){$Ia=array_filter(fields($Q),function($m){return$m['auto_increment'];});return(count($Ia)==1?" RETURNING ".idf_escape(key($Ia)):"");}function
insertUpdate($Q,array$J,array$Zi){$e=array_keys(reset($J));$Ib=array();$mm=array();foreach($e
as$x){if(isset($Zi[idf_unescape($x)]))$Ib[]=$x;else$mm[]="$x = EXCLUDED.$x";}if(!$Ib||!min_version(9.5)||count($Ib)!=count($Zi))return
parent::insertUpdate($Q,$J,$Zi);$Ui="INSERT INTO ".table($Q)." (".implode(", ",$e).") VALUES\n";$Uk="\nON CONFLICT (".implode(", ",$Ib).")".($mm?" DO UPDATE SET ".implode(", ",$mm):" DO NOTHING");$Y=array();$y=0;foreach($J
as$N){$X="(".implode(", ",$N).")";if($Y&&strlen($Ui)+$y+strlen($X)+strlen($Uk)>1e6){if(!queries($Ui.implode(",\n",$Y).$Uk))return
false;$Y=array();$y=0;}$Y[]=$X;$y+=strlen($X)+2;}return
queries($Ui.implode(",\n",$Y).$Uk);}function
slowQuery($F,$_l){$this->conn->query("SET statement_timeout = ".(1000*$_l));$this->conn->timeout=1000*$_l;return$F;}function
convertSearch($u,array$W,array$m){$Ei=preg_match('(LIKE|^!?~)',$W["op"]);$fh=preg_match('~^(character( varying)?|text|citext|bpchar|name)$~',$m["type"])||(!$Ei&&preg_match('~'.number_type().'|^(date|time|timetz|timestamp|timestamptz|boolean)$~',$m["type"]));return($fh&&!preg_match('~\[]$~',$m["full_type"])?$u:"CAST($u AS text)");}function
quoteBinary($Oj){return"'\\x".bin2hex($Oj)."'";}function
warnings(){return$this->conn->warnings();}function
tableHelp($B,$Df=false){$hg=array("information_schema"=>"infoschema","pg_catalog"=>($Df?"view":"catalog"),);$_=$hg[$_GET["ns"]];if($_)return"$_-".str_replace("_","-",$B).".html";}function
inheritsFrom($Q){return
get_rows("SELECT relname AS table, nspname AS ns FROM pg_class JOIN pg_inherits ON inhparent = oid JOIN pg_namespace ON relnamespace = pg_namespace.oid WHERE inhrelid = ".$this->tableOid($Q)." ORDER BY 2, 1");}function
inheritedTables($Q){return
get_rows("SELECT relname AS table, nspname AS ns FROM pg_inherits JOIN pg_class ON inhrelid = oid JOIN pg_namespace ON relnamespace = pg_namespace.oid WHERE inhparent = ".$this->tableOid($Q)." ORDER BY 2, 1");}function
partitionsInfo($Q){$I=(min_version(10)?$this->conn->query("SELECT * FROM pg_partitioned_table WHERE partrelid = ".$this->tableOid($Q))->fetch_assoc():null);if($I){$c=get_vals("SELECT attname FROM pg_attribute WHERE attrelid = $I[partrelid] AND attnum IN (".str_replace(" ",", ",$I["partattrs"]).")");$bb=array('h'=>'HASH','l'=>'LIST','r'=>'RANGE');return
array("partition_by"=>$bb[$I["partstrat"]],"partition"=>implode(", ",array_map('Adminer\idf_escape',$c)),);}return
array();}function
tableOid($Q){return"(SELECT oid FROM pg_class WHERE relnamespace = $this->nsOid AND relname = ".q($Q)." AND relkind IN ('r', 'm', 'v', 'f', 'p'))";}function
allFields(){$H=array();$J=get_rows("SELECT c.relname AS tab, a.attname AS field, a.attnotnull::int,
	format_type(a.atttypid, a.atttypmod) AS full_type, i.indrelid AS primary
FROM pg_class c
JOIN pg_attribute a ON a.attrelid = c.oid AND a.attnum > 0 AND NOT a.attisdropped
LEFT JOIN pg_index i ON i.indrelid = c.oid AND i.indisprimary AND a.attnum = ANY(i.indkey)
WHERE c.relnamespace = $this->nsOid
AND c.relkind IN ('r', 'm', 'v', 'f', 'p')".(min_version(10)?"
AND c.relispartition IS NOT TRUE":"")."
ORDER BY c.relname, a.attnum",$this->conn);foreach($J
as$I){parse_full_type($I);$I["null"]=!$I["attnotnull"];$H[$I["tab"]][]=$I;}return$H;}function
indexAlgorithms(array$al){static$H=array();if(!$H)$H=get_vals("SELECT amname FROM pg_am".(min_version(9.6)?" WHERE amtype = 'i'":"")." ORDER BY amname = '".($this->conn->flavor=='cockroach'?"prefix":"btree")."' DESC, amname");return$H;}function
indexOpclasses(){static$H=array();if(!$H&&$this->conn->flavor!='cockroach')$H=get_vals("SELECT DISTINCT opcname FROM pg_catalog.pg_opclass WHERE NOT opcdefault ORDER BY opcname");return$H;}function
supportsIndex(array$R){return$R["Engine"]!="view";}function
hasCStyleEscapes(){static$db;if($db===null)$db=(get_val("SHOW standard_conforming_strings",0,$this->conn)=="off");return$db;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($Sd){return
get_vals("SELECT datname FROM pg_database
WHERE datallowconn = TRUE AND has_database_privilege(datname, 'CONNECT')
ORDER BY datname");}function
limit($F,$Z,$z,$Ah=0,$dk=" "){return" $F$Z".($z?$dk."LIMIT $z".($Ah?" OFFSET $Ah":""):"");}function
limit1($Q,$F,$Z,$dk="\n"){return(preg_match('~^INTO~',$F)?limit($F,$Z,1,0,$dk):" $F".(is_view(table_status1($Q))?$Z:$dk."WHERE (tableoid, ctid) = (SELECT tableoid, ctid FROM ".table($Q).$Z.$dk."LIMIT 1)"));}function
db_collation($j,array$yb){return
get_val("SELECT datcollate FROM pg_database WHERE datname = ".q($j));}function
logged_user(){return
get_val("SELECT user");}function
tables_list(){$F="SELECT table_name, table_type FROM information_schema.tables WHERE table_schema = current_schema()";if(support("materializedview"))$F
.="
UNION ALL
SELECT matviewname, 'MATERIALIZED VIEW'
FROM pg_matviews
WHERE schemaname = current_schema()";$F
.="
ORDER BY 1";return
get_key_vals($F);}function
count_tables(array$i){$H=array();foreach($i
as$j){if(connection()->select_db($j))$H[$j]=count(tables_list());}return$H;}function
table_status($B="",$_d=false){static$Be;if($Be===null)$Be=get_val("SELECT 'pg_table_size'::regproc");$hk=(!$_d&&min_version(10));$H=array();foreach(get_rows("SELECT
	relname AS \"Name\",
	CASE relkind WHEN 'v' THEN 'view' WHEN 'm' THEN 'materialized view' ELSE 'table' END AS \"Engine\"".($Be?",
	pg_table_size(c.oid) AS \"Data_length\",
	pg_indexes_size(c.oid) AS \"Index_length\"":"").",
	obj_description(c.oid, 'pg_class') AS \"Comment\",
	".(min_version(12)?"''":"CASE WHEN relhasoids THEN 'oid' ELSE '' END")." AS \"Oid\",
	reltuples AS \"Rows\",
	".($hk?"seq.last_value":"NULL")." AS \"Auto_increment\",
	".(min_version(10)?"relispartition::int AS dependent,":"")."
	current_schema() AS nspname
FROM pg_class c
".($hk?"LEFT JOIN (
	SELECT d.refobjid, max(s.last_value) AS last_value
	FROM pg_depend d
	JOIN pg_class sc ON sc.oid = d.objid AND sc.relkind = 'S' AND sc.relnamespace = ".driver()->nsOid."
	JOIN pg_sequences s ON s.schemaname = current_schema() AND s.sequencename = sc.relname
	WHERE d.classid = 'pg_class'::regclass AND d.refclassid = 'pg_class'::regclass AND d.deptype IN ('a', 'i')
	".($B!=""?"AND d.refobjid = ".driver()->tableOid($B):"")."
	GROUP BY d.refobjid
) seq ON seq.refobjid = c.oid
":"")."WHERE relkind IN ('r', 'm', 'v', 'f', 'p')
AND relnamespace = ".driver()->nsOid."
".($B!=""?"AND relname = ".q($B):"ORDER BY relname"))as$I)$H[$I["Name"]]=$I;return$H;}function
is_view(array$R){return
in_array($R["Engine"],array("view","materialized view"));}function
fk_support(array$R){return
true;}function
parse_full_type(array&$I){static$wa=array('timestamp without time zone'=>'timestamp','timestamp with time zone'=>'timestamptz','time without time zone'=>'time','time with time zone'=>'timetz',);preg_match('~([^([]+)(\((.*)\))?([a-z ]+)?((\[[0-9]*])*)$~',$I["full_type"],$A);list(,$T,$y,$I["length"],$oa,$Da)=$A;$I["length"].=$Da;$mb=$T.$oa;if(isset($wa[$mb])){$I["type"]=$wa[$mb];$I["full_type"]=$I["type"].$y.$Da;}else{$I["type"]=$T;$I["full_type"]=$I["type"].$y.$oa.$Da;}}function
fields($Q){$H=array();foreach(get_rows("SELECT
	a.attname AS field,
	format_type(a.atttypid, a.atttypmod) AS full_type,
	pg_get_expr(d.adbin, d.adrelid) AS default,
	a.attnotnull::int,
	i.indrelid AS primary,
	t.typcategory,
	col_description(a.attrelid, a.attnum) AS comment".(min_version(10)?",
	a.attidentity".(min_version(12)?",
	a.attgenerated":""):"")."
FROM pg_attribute a
JOIN pg_type t ON t.oid = a.atttypid
LEFT JOIN pg_attrdef d ON a.attrelid = d.adrelid AND a.attnum = d.adnum
LEFT JOIN pg_index i ON a.attrelid = i.indrelid AND a.attnum = ANY(i.indkey) AND i.indisprimary
WHERE a.attrelid = ".driver()->tableOid($Q)."
AND NOT a.attisdropped
AND a.attnum > 0
ORDER BY a.attnum")as$I){parse_full_type($I);if(in_array($I['attidentity'],array('a','d')))$I['default']='GENERATED '.($I['attidentity']=='d'?'BY DEFAULT':'ALWAYS').' AS IDENTITY';$I["generated"]=idx(array("s"=>"STORED","v"=>"VIRTUAL"),$I["attgenerated"],"");$I["composite"]=($I["typcategory"]=="C");$I["null"]=!$I["attnotnull"];$I["auto_increment"]=$I['attidentity']||preg_match('~^nextval\(~i',$I["default"])||preg_match('~^unique_rowid\(~',$I["default"]);$I["privileges"]=array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1);if(!$I['generated']&&preg_match('~(.+)::[^,)]+(.*)~',$I["default"],$A))$I["default"]=($A[1]=="NULL"?null:idf_unescape($A[1]).$A[2]);$H[$I["field"]]=$I;}return$H;}function
indexes($Q,$g=null){$g=connection($g);$H=array();$fl=driver()->tableOid($Q);$e=get_key_vals("SELECT attnum, attname FROM pg_attribute WHERE attrelid = $fl AND attnum > 0",$g);foreach(get_rows("SELECT relname, indisunique::int, indisprimary::int, indkey, indoption, amname,
	pg_get_expr(indpred, indrelid, true) AS partial, pg_get_expr(indexprs, indrelid) AS indexpr".($g->flavor=='cockroach'?"":",
	(SELECT string_agg(CASE WHEN opcdefault THEN '' ELSE opcname END, ' ' ORDER BY s)
		FROM generate_subscripts(indclass, 1) AS s JOIN pg_catalog.pg_opclass ON pg_opclass.oid = indclass[s]) AS opclasses")."
FROM pg_index
JOIN pg_class ON indexrelid = oid
JOIN pg_am ON pg_am.oid = pg_class.relam
WHERE indrelid = $fl
ORDER BY indisprimary DESC, indisunique DESC",$g)as$I){$yj=$I["relname"];$H[$yj]["type"]=($I["indisprimary"]?"PRIMARY":($I["indisunique"]?"UNIQUE":"INDEX"));$H[$yj]["columns"]=array();$H[$yj]["descs"]=array();$H[$yj]["algorithm"]=$I["amname"];$H[$yj]["partial"]=$I["partial"];$gf=preg_split('~(?<=\)), (?=\()~',$I["indexpr"]);foreach(explode(" ",$I["indkey"])as$hf)$H[$yj]["columns"][]=($hf?$e[$hf]:array_shift($gf));foreach(explode(" ",$I["indoption"])as$if)$H[$yj]["descs"][]=(intval($if)&1?'1':null);$H[$yj]["opclasses"]=($I["opclasses"]!=""?explode(" ",$I["opclasses"]):array());$H[$yj]["lengths"]=array();}return$H;}function
foreign_keys($Q){$H=array();foreach(get_rows("SELECT conname, condeferrable::int AS deferrable, condeferred::int AS deferred, pg_get_constraintdef(oid) AS definition
FROM pg_constraint
WHERE conrelid = ".driver()->tableOid($Q)."
AND contype = 'f'::char
ORDER BY conkey, conname")as$I){$I['deferrable']=($I['deferrable']?'':'NOT ').'DEFERRABLE'.($I['deferred']?' INITIALLY DEFERRED':'');if(preg_match('~FOREIGN KEY\s*\((.+)\)\s*REFERENCES (.+)\((.+)\)(.*)$~iA',$I['definition'],$A)){$I['source']=array_map('Adminer\idf_unescape',array_map('trim',explode(',',$A[1])));if(preg_match('~^(("([^"]|"")+"|[^"]+)\.)?"?("([^"]|"")+"|[^"]+)$~',$A[2],$qg)){$I['ns']=idf_unescape($qg[2]);$I['table']=idf_unescape($qg[4]);}$I['target']=array_map('Adminer\idf_unescape',array_map('trim',explode(',',$A[3])));$I['on_delete']=(preg_match("~ON DELETE (".driver()->onActions.")~",$A[4],$qg)?$qg[1]:'NO ACTION');$I['on_update']=(preg_match("~ON UPDATE (".driver()->onActions.")~",$A[4],$qg)?$qg[1]:'NO ACTION');$H[$I['conname']]=$I;}}return$H;}function
view($B){return
array("select"=>trim(get_val("SELECT pg_get_viewdef(".driver()->tableOid($B).")")));}function
collations(){return
array();}function
information_schema($j,$K=""){return
in_array($K!=""?$K:get_schema(),array("information_schema","pg_catalog","pg_toast"));}function
error(){$H=h(connection()->error);if(preg_match('~^(.*\n)?([^\n]*)\n( *)\^(\n.*)?$~s',$H,$A))$H=$A[1].preg_replace('~((?:[^&]|&[^;]*;){'.strlen($A[3]).'})(.*)~','\1<b>\2</b>',$A[2]).$A[4];return
nl_br($H);}function
create_database($j,$xb){return
queries("CREATE DATABASE ".idf_escape($j).($xb?" ENCODING ".idf_escape($xb):""));}function
drop_databases(array$i){connection()->close();return
apply_queries("DROP DATABASE",$i,'Adminer\idf_escape');}function
rename_database($B,$xb){connection()->close();return!!queries("ALTER DATABASE ".idf_escape(DB)." RENAME TO ".idf_escape($B));}function
auto_increment(){return"";}function
alter_table($Q,$B,array$n,array$Ud,$Cb,$ad,$xb,$Ia,$wi){$b=array();$jj=array();if($Q!=""&&$Q!=$B)$jj[]="ALTER TABLE ".table($Q)." RENAME TO ".table($B);$ek="";foreach($n
as$m){$d=idf_escape($m[0]);$W=$m[1];if(!$W)$b[]="DROP $d";else{$Bm=$W[5];unset($W[5]);if($m[0]==""){if(isset($W[6]))$W[1]=($W[1]==" bigint"?" big":($W[1]==" smallint"?" small":" "))."serial";$b[]=($Q!=""?"ADD ":"  ").implode($W);if(isset($W[6]))$b[]=($Q!=""?"ADD":" ")." PRIMARY KEY ($W[0])";}else{if($d!=$W[0])$jj[]="ALTER TABLE ".table($B)." RENAME $d TO $W[0]";$b[]="ALTER $d TYPE$W[1]";$fk=$Q."_".idf_unescape($W[0])."_seq";$b[]="ALTER $d ".($W[3]?"SET".preg_replace('~GENERATED ALWAYS(.*) (STORED|VIRTUAL)~','EXPRESSION\1',$W[3]):(isset($W[6])?"SET DEFAULT nextval(".q($fk).")":"DROP DEFAULT"));if(isset($W[6]))$ek="CREATE SEQUENCE IF NOT EXISTS ".idf_escape($fk)." OWNED BY ".idf_escape($Q).".$W[0]";$b[]="ALTER $d ".($W[2]==" NULL"?"DROP NOT":"SET").$W[2];}if($m[0]!=""||$Bm!="")$jj[]="COMMENT ON COLUMN ".table($B).".$W[0] IS ".($Bm!=""?substr($Bm,9):"''");}}$b=array_merge($b,$Ud);if($Q==""){$O="";if($wi){$tb=(connection()->flavor=='cockroach');$O=" PARTITION BY $wi[partition_by]($wi[partition])";if($wi["partition_by"]=='HASH'){$xi=+$wi["partitions"];for($s=0;$s<$xi;$s++)$jj[]="CREATE TABLE ".idf_escape($B."_$s")." PARTITION OF ".idf_escape($B)." FOR VALUES WITH (MODULUS $xi, REMAINDER $s)";}else{$Wi="MINVALUE";foreach($wi["partition_names"]as$s=>$W){$X=$wi["partition_values"][$s];$si=" VALUES ".($wi["partition_by"]=='LIST'?"IN ($X)":"FROM ($Wi) TO ($X)");if($tb)$O
.=($s?",":" (")."\n  PARTITION ".(preg_match('~^DEFAULT$~i',$W)?$W:idf_escape($W))."$si";else$jj[]="CREATE TABLE ".idf_escape($B."_$W")." PARTITION OF ".idf_escape($B)." FOR$si";$Wi=$X;}$O
.=($tb?"\n)":"");}}array_unshift($jj,"CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)$O");}elseif($b)array_unshift($jj,"ALTER TABLE ".table($Q)."\n".implode(",\n",$b));if($ek)array_unshift($jj,$ek);if($Cb!==null)$jj[]="COMMENT ON TABLE ".table($B)." IS ".q($Cb);foreach($jj
as$F){if(!queries($F))return
false;}if($Ia!=""){foreach(fields($B)as$Cd=>$m){if($m["auto_increment"])return!!queries("SELECT setval(pg_get_serial_sequence(".q(table($B)).", ".q($Cd)."), $Ia)");}}return
true;}function
alter_indexes($Q,$b){$h=array();$Mc=array();$jj=array();foreach($b
as$W){if($W[0]!="INDEX")$h[]=($W[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($W[1]):"\nADD".($W[1]!=""?" CONSTRAINT ".idf_escape($W[1]):"")." $W[0] ".($W[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$W[2]).")");elseif($W[2]=="DROP")$Mc[]=idf_escape($W[1]);else$jj[]="CREATE INDEX ".idf_escape($W[1]!=""?$W[1]:uniqid($Q."_"))." ON ".table($Q).($W[3]?" USING $W[3]":"")." (".implode(", ",$W[2]).")".($W[4]?" WHERE $W[4]":"");}if($h)array_unshift($jj,"ALTER TABLE ".table($Q).implode(",",$h));if($Mc)array_unshift($jj,"DROP INDEX ".implode(", ",$Mc));foreach($jj
as$F){if(!queries($F))return
false;}return
true;}function
truncate_tables(array$S){return!!queries("TRUNCATE ".implode(", ",array_map('Adminer\table',$S)));}function
drop_kinds(array$S){$H=array("MATERIALIZED VIEW"=>array(),"VIEW"=>array(),"TABLE"=>array());foreach($S
as$B=>$R)$H[strtoupper($R["Engine"])][]=idf_escape($R["nspname"]).".".table($B);return
array_filter($H);}function
drop_views(array$Im){return
drop_tables($Im);}function
drop_tables(array$S){$Pk=array();foreach($S
as$Q)$Pk[$Q]=table_status1($Q);foreach(drop_kinds($Pk)as$Nf=>$eh){if(!queries("DROP $Nf ".implode(", ",$eh)))return
false;}return
true;}function
move_tables(array$S,array$Im,$ql){foreach(array_merge($S,$Im)as$Q){$O=table_status1($Q);if(!queries("ALTER ".strtoupper($O["Engine"])." ".table($Q)." SET SCHEMA ".idf_escape($ql)))return
false;}return
true;}function
trigger($B,$Q){if($B=="")return
array("Statement"=>"EXECUTE PROCEDURE ()");$e=array();$Z="WHERE trigger_schema = current_schema() AND event_object_table = ".q($Q)." AND trigger_name = ".q($B);foreach(get_rows("SELECT * FROM information_schema.triggered_update_columns $Z")as$I)$e[]=$I["event_object_column"];$H=array();foreach(get_rows('SELECT trigger_name AS "Trigger", action_timing AS "Timing", event_manipulation AS "Event", \'FOR EACH \' || action_orientation AS "Type", action_statement AS "Statement"
FROM information_schema.triggers'."
$Z
ORDER BY event_manipulation DESC")as$I){if($e&&$I["Event"]=="UPDATE")$I["Event"].=" OF";$I["Of"]=implode(", ",$e);if($H)$I["Event"].=" OR $H[Event]";$H=$I;}return$H;}function
triggers($Q){$H=array();foreach(get_rows("SELECT * FROM information_schema.triggers WHERE trigger_schema = current_schema() AND event_object_table = ".q($Q))as$I){$Rl=trigger($I["trigger_name"],$Q);$H[$Rl["Trigger"]]=array($Rl["Timing"],$Rl["Event"]);}return$H;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>array("INSERT","UPDATE","UPDATE OF","DELETE","INSERT OR UPDATE","INSERT OR UPDATE OF","DELETE OR INSERT","DELETE OR UPDATE","DELETE OR UPDATE OF","DELETE OR INSERT OR UPDATE","DELETE OR INSERT OR UPDATE OF",),"Type"=>array("FOR EACH ROW","FOR EACH STATEMENT"),);}function
routine($B,$T){$C=routine_options($T);$ak=array_intersect_key(array("VOLATILITY"=>"CASE p.provolatile WHEN 'i' THEN 'IMMUTABLE' WHEN 's' THEN 'STABLE' ELSE 'VOLATILE' END","NULL_INPUT"=>"CASE WHEN p.proisstrict THEN 'RETURNS NULL ON NULL INPUT' ELSE 'CALLED ON NULL INPUT' END","SECURITY"=>"CASE WHEN p.prosecdef THEN 'SECURITY DEFINER' ELSE 'SECURITY INVOKER' END","PARALLEL"=>"CASE p.proparallel WHEN 's' THEN 'PARALLEL SAFE' WHEN 'r' THEN 'PARALLEL RESTRICTED' ELSE 'PARALLEL UNSAFE' END",),$C);foreach($ak
as$x=>$L)$ak[$x]="$L AS \"$x\"";$J=get_rows('SELECT r.routine_definition AS definition, LOWER(r.external_language) AS language, '.($ak?implode(', ',$ak).', ':'').'r.*
FROM information_schema.routines r
LEFT JOIN pg_catalog.pg_proc p ON p.oid::text = substring(r.specific_name, \'[0-9]+$\')
WHERE r.routine_schema = current_schema() AND r.specific_name = '.q($B));$H=idx($J,0,array());$H["options"]=array_intersect_key($H,$C);$H["returns"]=array("type"=>preg_replace('~^_(.*)~','\1[]',"$H[type_udt_name]"));$H["fields"]=get_rows("SELECT COALESCE(parameter_name, ordinal_position::text) AS field,
	CASE data_type WHEN 'USER-DEFINED' THEN udt_name WHEN 'ARRAY' THEN substr(udt_name, 2) || '[]' ELSE data_type END AS type,
	character_maximum_length AS length, parameter_mode AS inout
FROM information_schema.parameters
WHERE specific_schema = current_schema() AND specific_name = ".q($B)."
ORDER BY ordinal_position");return$H;}function
routines(){return
get_rows('SELECT specific_name AS "SPECIFIC_NAME", routine_type AS "ROUTINE_TYPE", routine_name AS "ROUTINE_NAME", type_udt_name AS "DTD_IDENTIFIER"
FROM information_schema.routines
WHERE routine_schema = current_schema()'.(connection()->flavor=='cockroach'?'':"
AND substring(specific_name, '[0-9]+\$')::oid NOT IN (SELECT objid FROM pg_catalog.pg_depend WHERE classid = 'pg_proc'::regclass AND deptype = 'e')").'
ORDER BY SPECIFIC_NAME');}function
routine_languages(){$H=array();foreach(get_vals("SELECT LOWER(lanname) FROM pg_catalog.pg_language")as$Tf)$H[$Tf]=(preg_match('~sql$~',$Tf)?"pgsql":"txt");return$H;}function
routine_options($Ij){$tb=(connection()->flavor=='cockroach');$Vj=($tb?array():array("SECURITY"=>array("SECURITY INVOKER","SECURITY DEFINER")));if($Ij=="PROCEDURE")return$Vj;return
array("VOLATILITY"=>array("VOLATILE","STABLE","IMMUTABLE"),"NULL_INPUT"=>array("CALLED ON NULL INPUT","RETURNS NULL ON NULL INPUT"),)+$Vj+($tb?array():array("PARALLEL"=>array("PARALLEL UNSAFE","PARALLEL RESTRICTED","PARALLEL SAFE"),));}function
routine_id($B,array$I){$H=array();foreach($I["fields"]as$m){$y=$m["length"];$H[]=$m["type"].($y?"($y)":"");}return
idf_escape($B)."(".implode(", ",$H).")";}function
last_id($G){$I=(is_object($G)?$G->fetch_row():array());return($I?$I[0]:0);}function
explain(Db$f,$F){return$f->query("EXPLAIN $F");}function
found_rows(array$R,array$Z){if(preg_match("~ rows=([0-9]+)~",get_val("EXPLAIN SELECT * FROM ".idf_escape($R["Name"]).($Z?" WHERE ".implode(" AND ",$Z):"")),$xj))return$xj[1];}function
types($wd=false){$tb=connection()->flavor=='cockroach';$Of=($tb?"'e'":"'b','c','d','e'".(min_version(9.2)?",'r'":""));return
get_key_vals("SELECT t.oid, t.typname
FROM pg_type t
WHERE t.typnamespace = ".driver()->nsOid."
AND t.typtype IN ($Of)".($tb?"
AND t.typelem = 0":"
AND (t.typrelid = 0 OR (SELECT c.relkind FROM pg_class c WHERE c.oid = t.typrelid) = 'c')"."
AND NOT EXISTS (SELECT 1 FROM pg_type e WHERE e.typarray = t.oid)".($wd?'':"
AND t.oid NOT IN (SELECT objid FROM pg_catalog.pg_depend WHERE classid = 'pg_type'::regclass AND deptype = 'e')"))."
ORDER BY t.typname");}function
type_values($t){$ed=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $t ORDER BY enumsortorder");return($ed?"'".implode("', '",array_map('addslashes',$ed))."'":"");}function
collation_name($Bh){return(min_version(9.1)?"(SELECT collname FROM pg_collation WHERE oid = $Bh AND collname != 'default')":"NULL");}function
type_definition($t){$T=first(get_rows("SELECT typtype, typisdefined::int AS defined, typrelid FROM pg_type WHERE oid = $t"));$H=array("kind"=>($T?$T["typtype"]:""),"definition"=>"");if(!$T||!$T["defined"])return$H;switch($H["kind"]){case'e':$Y=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $t ORDER BY enumsortorder");$H["definition"]="AS ENUM (".implode(", ",array_map('Adminer\q',$Y)).")";break;case'c':$e=array();foreach(get_rows("SELECT attname, format_type(atttypid, atttypmod) AS full_type, ".collation_name("attcollation")." AS collation
FROM pg_attribute
WHERE attrelid = $T[typrelid] AND attnum > 0 AND NOT attisdropped
ORDER BY attnum")as$I)$e[]=idf_escape($I["attname"])." $I[full_type]".($I["collation"]?" COLLATE ".idf_escape($I["collation"]):"");$H["definition"]="AS (\n\t".implode(",\n\t",$e)."\n)";break;case'd':$Jc=first(get_rows("SELECT format_type(typbasetype, typtypmod) AS base, typnotnull::int AS notnull, typdefault, ".collation_name("typcollation")." AS collation
FROM pg_type WHERE oid = $t"));$H["definition"]="AS $Jc[base]".($Jc["collation"]?" COLLATE ".idf_escape($Jc["collation"]):"").($Jc["typdefault"]!=""?" DEFAULT $Jc[typdefault]":"").($Jc["notnull"]?" NOT NULL":"");foreach(get_rows("SELECT conname, pg_get_constraintdef(oid) AS definition FROM pg_constraint WHERE contypid = $t AND contype != 'n' ORDER BY conname")as$I)$H["definition"].=" CONSTRAINT ".idf_escape($I["conname"])." $I[definition]";break;case'r':$nj=first(get_rows("SELECT format_type(rngsubtype, NULL) AS subtype,
(SELECT opcname FROM pg_opclass WHERE oid = rngsubopc) AS subtype_opclass,
".collation_name("rngcollation")." AS collation,
NULLIF(rngcanonical, 0)::regproc::text AS canonical,
NULLIF(rngsubdiff, 0)::regproc::text AS subtype_diff".(min_version(14)?",
(SELECT typname FROM pg_type WHERE oid = rngmultitypid) AS multirange_type_name":"")."
FROM pg_range WHERE rngtypid = $t"));$C=array();foreach(array("subtype"=>0,"subtype_opclass"=>1,"collation"=>1,"canonical"=>0,"subtype_diff"=>0,"multirange_type_name"=>1)as$x=>$id){if($nj[$x]!="")$C[]=strtoupper($x)." = ".($id?idf_escape($nj[$x]):$nj[$x]);}$H["definition"]="AS RANGE (".implode(", ",$C).")";}return$H;}function
schemas(){return
get_vals("SELECT nspname FROM pg_namespace ORDER BY nspname");}function
get_schema(){return(string)get_val("SELECT current_schema()");}function
set_schema($K,$g=null){$H=connection($g)->query("SET search_path TO ".idf_escape($K));driver()->setUserTypes(types(true));return!!$H;}function
drop_sql(array$S){$H="";foreach(drop_kinds($S)as$Nf=>$eh)$H
.="DROP $Nf IF EXISTS ".implode(", ",$eh).";\n";return($H?"$H\n":"");}function
foreign_keys_sql($Q){$H="";$O=table_status1($Q);$rh=idf_escape($O['nspname']);$Qd=foreign_keys($Q);ksort($Qd);foreach($Qd
as$Pd=>$Od)$H
.="ALTER TABLE ONLY $rh.".idf_escape($O['Name'])." ADD CONSTRAINT ".idf_escape($Pd)." ".preg_replace('~( REFERENCES )([^(.]+\()~',"\\1$rh.\\2",$Od["definition"]).";\n";return($H?"$H\n":$H);}function
indexes_sql($Q,$Zi=""){$H="";$F="SELECT indexdef FROM pg_catalog.pg_indexes WHERE schemaname = current_schema() AND tablename = ".q($Q).($Zi!=""?" AND indexname != ".q($Zi):"");foreach(get_rows($F,null,"-- ")as$I)$H
.="\n\n$I[indexdef];";return$H;}function
create_sql($Q,$Ia,$Sk){$Dj=array();$hk=array();$ik=array();$gk=array();$O=table_status1($Q);$rh=idf_escape($O['nspname']);if(is_view($O)){$Hm=view($Q);$h="CREATE ".strtoupper($O["Engine"])." $rh.".idf_escape($Q)." AS ".rtrim($Hm["select"],";").";";return
rtrim($h.indexes_sql($Q),';');}$n=fields($Q);if(count($O)<2||empty($n))return"";$H="CREATE TABLE $rh.".idf_escape($O['Name'])." (\n    ";$dl=q("$rh.".idf_escape($O['Name']));foreach($n
as$m){$jk="";if($m['default']=="nextval('$O[Name]_$m[field]_seq')"){$jk="$rh.".idf_escape("$O[Name]_$m[field]_seq");$m['default']=null;$m['full_type']=preg_replace('~int(eger)?~','serial',$m['full_type']);}$qi=idf_escape($m['field']).' '.$m['full_type'].preg_replace('~(nextval\(\')([^.\']+\')~','\1'.str_replace("'","''",$O['nspname']).'.\2',default_value($m)).($m['null']?"":" NOT NULL");$Dj[]=$qi;if(preg_match('~nextval\(\'([^\']+)\'\)~',$m['default'],$rg)){$fk=$rg[1];$Fk=first(get_rows((min_version(10)?"SELECT *, cache_size AS cache_value FROM pg_sequences WHERE schemaname = current_schema() AND sequencename = ".q(idf_unescape($fk)):"SELECT * FROM $fk"),null,"-- "));$hk[]=($Sk=="DROP+CREATE"?"DROP SEQUENCE IF EXISTS $rh.$fk;\n":"")."CREATE SEQUENCE $rh.$fk INCREMENT $Fk[increment_by] MINVALUE $Fk[min_value] MAXVALUE $Fk[max_value]"." CACHE $Fk[cache_value];";if(get_val("SELECT pg_get_serial_sequence($dl, ".q($m['field']).")"))$ik[]="\n\nALTER SEQUENCE $rh.$fk OWNED BY $rh.".idf_escape($O['Name']).".".idf_escape($m['field']).";";if($Ia)$gk[]="$rh.$fk";}elseif($Ia&&$m['auto_increment'])$gk[]=($jk?:get_val("SELECT pg_get_serial_sequence($dl, ".q($m['field']).")"));}if(!empty($hk))$H=implode("\n\n",$hk)."\n\n$H";$Zi="";foreach(indexes($Q)as$ef=>$v){if($v['type']=='PRIMARY'){$Zi=$ef;$Dj[]="CONSTRAINT ".idf_escape($ef)." PRIMARY KEY (".implode(', ',array_map('Adminer\idf_escape',$v['columns'])).")";}}foreach(driver()->checkConstraints($Q)as$Kb=>$Mb)$Dj[]="CONSTRAINT ".idf_escape($Kb)." CHECK ($Mb)";$H
.=implode(",\n    ",$Dj)."\n)";$si=driver()->partitionsInfo($O['Name']);if($si)$H
.="\nPARTITION BY $si[partition_by]($si[partition])";$H
.="\nWITH (oids = ".($O['Oid']?'true':'false').");";$H
.=implode($ik);if($O['Comment'])$H
.="\n\nCOMMENT ON TABLE $rh.".idf_escape($O['Name'])." IS ".q($O['Comment']).";";foreach($n
as$Cd=>$m){if($m['comment'])$H
.="\n\nCOMMENT ON COLUMN $rh.".idf_escape($O['Name']).".".idf_escape($Cd)." IS ".q($m['comment']).";";}$H
.=indexes_sql($Q,$Zi);foreach(array_filter($gk)as$ek){$Fk=first(get_rows("SELECT last_value, is_called::int FROM $ek",null,"-- "));if($Fk['is_called'])$H
.="\n\nDO \$\$ BEGIN PERFORM setval(".q($ek).", $Fk[last_value]); END \$\$;";}return
rtrim($H,';');}function
truncate_sql($Q){return"TRUNCATE ".table($Q);}function
truncate_all_sql(array$S){return($S?"TRUNCATE ".implode(", ",array_map('Adminer\table',$S)).";\n\n":"");}function
trigger_sql($Q){$O=table_status1($Q);$H="";foreach(triggers($Q)as$Ql=>$Pl){$Rl=trigger($Ql,$O['Name']);$H
.="\nCREATE TRIGGER ".idf_escape($Rl['Trigger'])." $Rl[Timing] $Rl[Event] ON ".idf_escape($O["nspname"]).".".idf_escape($O['Name'])." $Rl[Type] $Rl[Statement];;\n";}return$H;}function
use_sql($ic,$Sk=""){$B=idf_escape($ic);$H="";if(preg_match('~CREATE~',$Sk)){if($Sk=="DROP+CREATE")$H="DROP DATABASE IF EXISTS $B;\n";$H
.="CREATE DATABASE $B;\n";}return"$H\\connect $B";}function
show_variables(){return
get_rows("SHOW ALL");}function
process_list(){return
get_rows("SELECT * FROM pg_stat_activity ORDER BY ".(min_version(9.2)?"pid":"procpid"));}function
convert_field(array$m){}function
unconvert_field(array$m,$H){return($m["composite"]?"$H::$m[type]":$H);}function
support($Ad){return
preg_match('~^(check|columns|comment|database|drop_col|dump|descidx|fast_status|indexes|kill|partial_indexes|routine|scheme|sequence|sql|table'.'|transaction_ddl|trigger|type|variables|view'.(min_version(9.3)?'|materializedview':'').(min_version(11)?'|procedure':'').(connection()->flavor=='cockroach'?'':'|deferrable').(connection()->flavor=='cockroach'?'':'|processlist').')$~',$Ad);}function
kill_process($t){return
queries("SELECT pg_terminate_backend(".number($t).")");}function
connection_id(){return"SELECT pg_backend_pid()";}function
max_connections(){return
get_val("SHOW max_connections");}}add_driver("sqlite","SQLite");if(isset($_GET["sqlite"])){define('Adminer\DRIVER',"sqlite");if(class_exists("SQLite3")&&$_GET["ext"]!="pdo"){abstract
class
SqliteDb
extends
SqlDb{var$extension="SQLite3";private$link;function
attach(array$M,$U,$E){$this->link=new
\SQLite3($M["path"]);$Gm=\SQLite3::version();$this->server_info=$Gm["versionString"];return'';}function
query($F,$cm=false){$G=@$this->link->query($F);$this->error="";if(!$G){$this->errno=$this->link->lastErrorCode();$this->error=$this->link->lastErrorMsg();return
false;}elseif($G->numColumns())return
new
Result($G);$this->affected_rows=$this->link->changes();return
true;}function
quote($P){return(is_utf8($P)?"'".$this->link->escapeString($P)."'":"x'".bin2hex($P)."'");}}class
Result{var$num_rows;private$result,$offset=0;function
__construct($G){$this->result=$G;}function
fetch_assoc(){return$this->result->fetchArray(SQLITE3_ASSOC);}function
fetch_row(){return$this->result->fetchArray(SQLITE3_NUM);}function
fetch_field(){$bm=array(1=>"integer","real","text","blob","null");$d=$this->offset++;$T=$this->result->columnType($d);return(object)array("name"=>$this->result->columnName($d),"type"=>($T==SQLITE3_TEXT?15:0),"native_type"=>$bm[$T],"charsetnr"=>($T==SQLITE3_BLOB?63:0),);}}}elseif(extension_loaded("pdo_sqlite")){abstract
class
SqliteDb
extends
PdoDb{var$extension="PDO_SQLite";function
attach(array$M,$U,$E){return$this->dsn(DRIVER.":".$M["path"],"","");}function
quote($P){return(is_utf8($P)?parent::quote($P):"x'".bin2hex($P)."'");}}}if(class_exists('Adminer\SqliteDb')){class
Db
extends
SqliteDb{function
attach(array$M,$U,$E){parent::attach($M,$U,$E);$this->query("PRAGMA foreign_keys = 1");$this->query("PRAGMA busy_timeout = 500");return'';}function
select_db($o){$F="ATTACH ".$this->quote(preg_match("~(^[/\\\\]|:)~",$o)?$o:dirname($_SERVER["SCRIPT_FILENAME"])."/$o")." AS a";if(is_readable($o)&&$this->query($F))return!self::attach(server_parts(array("path"=>$o)),'','');return
false;}}}class
Driver
extends
SqlDriver{static$extensions=array("SQLite3","PDO_SQLite");static$jush="sqlite";static$passwords=false;static$serverFile=true;protected$types=array(array("integer"=>0,"real"=>0,"numeric"=>0,"text"=>0,"blob"=>0));var$insertFunctions=array();var$editFunctions=array("integer|real|numeric"=>"+/-","text"=>"||",);var$fulltextOperator="MATCH";var$functions=array("hex","length","lower","round","unixepoch","upper");var$grouping=array("avg","count","count distinct","group_concat","max","min","sum");function
operators($al){$H=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL");if(preg_match('~^fts\d+$~i',(string)idx($al,"Engine")))$H[]="MATCH";$H[]="SQL";return$H;}static
function
connect($M,$U,$E){return
parent::connect(":memory:","","");}function
__construct(Db$f){parent::__construct($f);if(min_version(3.31,0,$f))$this->generated=array("STORED","VIRTUAL");if(min_version(3.37,0,$f))$this->types[0]["any"]=0;}function
structuredTypes(){return
array_keys($this->types[0]);}function
quoteBinary($Oj){return"x".q(bin2hex($Oj));}function
engines(){$H=array("table");if(min_version("3.8.2")){if(min_version(3.37)){$H[]="STRICT";$H[]="STRICT, WITHOUT ROWID";}$H[]="WITHOUT ROWID";}return$H;}private
function
isVirtual(array$R){$ad=$R["Engine"];return$ad!=""&&!in_array($ad,array_merge(array("view"),$this->engines()));}function
supportsIndex(array$R){return!is_view($R)&&!$this->isVirtual($R);}function
supportsAlterIndex(array$R){return$this->supportsIndex($R);}function
supportsAlterTable(array$al){return!$this->isVirtual($al);}function
shadowTables($Q){$H=array();if(min_version(3.37)){foreach(get_vals("SELECT name FROM pragma_table_list WHERE schema = 'main' AND type = 'shadow' ORDER BY name")as$B){if(preg_match('(^'.preg_quote($Q).'_[^_]*$)',$B))$H[]=array("table"=>$B,"ns"=>"");}}return$H;}function
fulltextSql($B,array$v,$F,$Ya){return
idf_escape($B)." MATCH ".q($F);}function
insertUpdate($Q,array$J,array$Zi){$Y=array();foreach($J
as$N)$Y[]="(".implode(", ",$N).")";return
queries("REPLACE INTO ".table($Q)." (".implode(", ",array_keys(reset($J))).") VALUES\n".implode(",\n",$Y));}function
tableHelp($B,$Df=false){if(preg_match('~^sqlite_(seq|stat.)~',$B,$A))return"fileformat2.html#$A[1]tab";if(preg_match('~^sqlite(_temp)?_(master|schema)$~',$B))return"schematab.html";}function
checkConstraints($Q){preg_match_all('~ CHECK *(\( *(((?>[^()]*[^() ])|(?1))*) *\))~',get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($Q),0,$this->conn),$rg);return
array_combine($rg[2],$rg[2]);}function
allFields(){$H=array();if(min_version(3.16)){$J=get_rows('SELECT m.name AS tab, p.name AS field, p.type, p."notnull", p.pk AS '.idf_escape("primary")."
FROM sqlite_master m, pragma_table_".(min_version(3.31)?"x":"")."info(m.name) p
WHERE m.type IN ('table', 'view')".(min_version(3.31)?"
AND p.hidden != 1":"").(min_version(3.37)?"
AND m.name NOT IN (SELECT name FROM pragma_table_list WHERE type = 'shadow')":"")."
ORDER BY (m.name LIKE 'sqlite_%'), m.name, p.cid",$this->conn);foreach($J
as$I){$I["type"]=type_affinity($I["type"]);$I["null"]=!$I["notnull"];$H[$I["tab"]][]=$I;}}else{foreach(tables_list()as$Q=>$T){foreach(fields($Q)as$m)$H[$Q][]=$m;}}return$H;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($Sd){return
array();}function
limit($F,$Z,$z,$Ah=0,$dk=" "){return" $F$Z".($z?$dk."LIMIT $z".($Ah?" OFFSET $Ah":""):"");}function
limit1($Q,$F,$Z,$dk="\n"){return(preg_match('~^INTO~',$F)||get_val("SELECT sqlite_compileoption_used('ENABLE_UPDATE_DELETE_LIMIT')")?limit($F,$Z,1,0,$dk):" $F WHERE rowid = (SELECT rowid FROM ".table($Q).$Z.$dk."LIMIT 1)");}function
db_collation($j,array$yb){return
get_val("PRAGMA encoding");}function
logged_user(){return
get_current_user();}function
virtual_module($Gk){return(preg_match('~^CREATE\s+VIRTUAL\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?(?:"[^"]*+"|`[^`]*+`|\[[^\]]*+\]|[^\s(]+)\s+USING\s+([a-z0-9_]+)~i',$Gk,$A)?$A[1]:"");}function
tables_list(){return
get_key_vals("SELECT name, type FROM sqlite_master WHERE type IN ('table', 'view')".(min_version(3.37)?" AND name NOT IN (SELECT name FROM pragma_table_list WHERE type = 'shadow')":"")."
ORDER BY (name LIKE 'sqlite_%'), name");}function
count_tables(array$i){return
array();}function
db_status(){$li=get_val("PRAGMA page_size");$ce=get_val("PRAGMA freelist_count")*$li;return
array("Data_length"=>get_val("PRAGMA page_count")*$li-$ce,"Index_length"=>0,"Data_free"=>$ce,);}function
table_status($B="",$_d=false){$H=array();$J=array();if(!$_d&&$B==""){connection()->query("PRAGMA optimize = 0x10002");$J=get_key_vals("SELECT tbl, MAX(CAST(stat AS integer)) FROM sqlite_stat1 GROUP BY tbl");}foreach(get_rows("SELECT name AS Name, type AS Engine, sql, 'rowid' AS Oid, '' AS Auto_increment".(min_version(3.37)?", name IN (SELECT name FROM pragma_table_list WHERE type = 'shadow') AS dependent":"")." FROM sqlite_master WHERE type IN ('table', 'view') ".($B!=""?"AND name = ".q($B):"ORDER BY (name LIKE 'sqlite_%'), name"))as$I){if($I["Engine"]=="table"){$Gk=preg_replace('~(?:\s|--[^\n]*|/\*.*?\*/)+$~s','',$I["sql"]);$Uk=preg_replace('~.*\)~s','',$Gk);$I["Engine"]=virtual_module($I["sql"])?:(implode(", ",array_filter(array((preg_match('~\bSTRICT\b~i',$Uk)?"STRICT":0),(preg_match('~\bWITHOUT\s+ROWID\b~i',$Uk)?"WITHOUT ROWID":0),)))?:"table");}unset($I["sql"]);$I["Rows"]=idx($J,$I["Name"],0);$H[$I["Name"]]=$I;}if(!$_d){foreach(get_rows("SELECT * FROM sqlite_sequence".($B!=""?" WHERE name = ".q($B):""),null,"")as$I)$H[$I["name"]]["Auto_increment"]=$I["seq"];}return$H;}function
is_view(array$R){return$R["Engine"]=="view";}function
fk_support(array$R){return!get_val("SELECT sqlite_compileoption_used('OMIT_FOREIGN_KEY')");}function
type_affinity($T){$T=strtolower($T);return(preg_match('~int~i',$T)?"integer":(preg_match('~char|clob|text~i',$T)?"text":(preg_match('~blob~i',$T)?"blob":(preg_match('~real|floa|doub~i',$T)?"real":(preg_match('~any~i',$T)?"any":"numeric")))));}function
fields($Q){$H=array();$Gk=get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($Q));$ej=array("select"=>1,"where"=>1,"order"=>1);if(!preg_match('~^sqlite(_temp)?_(master|schema)$~',$Q))$ej+=array("insert"=>1,"update"=>1);$ee=preg_match('~^fts\d+$~i',virtual_module($Gk));foreach(get_rows("PRAGMA table_".(min_version(3.31)?"x":"")."info(".table($Q).")")as$I){if($I["hidden"]==1)continue;$B=$I["name"];$T=strtolower($I["type"]);$k=$I["dflt_value"];$H[$B]=array("field"=>$B,"type"=>($ee?"text":type_affinity($T)),"full_type"=>$T,"default"=>(preg_match("~^'(.*)'$~",$k,$A)?str_replace("''","'",$A[1]):($k=="NULL"?null:$k)),"null"=>!$I["notnull"],"privileges"=>$ej,"primary"=>$I["pk"],);if($I["pk"]&&preg_match('~\bAUTOINCREMENT\b~i',$Gk))$H[$B]["auto_increment"]=true;}$u='[(,]\s*(("[^"]*+")+|[a-z0-9_]+)';$Bj='(?:[^,()\']|\'[^\']*+\'|\([^)]*+\))*?';preg_match_all('~'.$u.'\s+text\b'.$Bj.'COLLATE\s+(\'[^\']+\'|[a-z0-9_]+)~i',$Gk,$rg,PREG_SET_ORDER);foreach($rg
as$A){$B=str_replace('""','"',preg_replace('~^"|"$~','',$A[1]));if($H[$B])$H[$B]["collation"]=trim($A[3],"'");}preg_match_all('~'.$u.'\s'.$Bj.'GENERATED\s+ALWAYS\s+AS\s*\((.+?)\)\s+(STORED|VIRTUAL)~i',$Gk,$rg,PREG_SET_ORDER);foreach($rg
as$A){$B=str_replace('""','"',preg_replace('~^"|"$~','',$A[1]));if($H[$B]){$H[$B]["default"]=$A[3];$H[$B]["generated"]=strtoupper($A[4]);}}return$H;}function
indexes($Q,$g=null){$g=connection($g);$H=array();$Gk=get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($Q),0,$g);if(preg_match('~^fts\d+$~i',virtual_module($Gk)))return
array($Q=>array("type"=>"FULLTEXT","columns"=>array_keys(fields($Q)),"lengths"=>array(),"descs"=>array()));if(preg_match('~\bPRIMARY\s+KEY\s*\((([^)"]+|"[^"]*"|`[^`]*`)++)~i',$Gk,$A)){$H[""]=array("type"=>"PRIMARY","columns"=>array(),"lengths"=>array(),"descs"=>array());preg_match_all('~((("[^"]*+")+|(?:`[^`]*+`)+)|(\S+))(\s+(ASC|DESC))?(,\s*|$)~i',$A[1],$rg,PREG_SET_ORDER);foreach($rg
as$A){$H[""]["columns"][]=idf_unescape($A[2]).$A[4];$H[""]["descs"][]=(preg_match('~DESC~i',$A[5])?'1':null);}}if(!$H){foreach(fields($Q)as$B=>$m){if($m["primary"])$H[""]=array("type"=>"PRIMARY","columns"=>array($B),"lengths"=>array(),"descs"=>array(null));}}$Lk=get_key_vals("SELECT name, sql FROM sqlite_master WHERE type = 'index' AND tbl_name = ".q($Q),$g);foreach(get_rows("PRAGMA index_list(".table($Q).")",$g)as$I){$B=$I["name"];$v=array("type"=>($I["unique"]?"UNIQUE":"INDEX"));$v["lengths"]=array();$v["descs"]=array();foreach(get_rows("PRAGMA index_info(".idf_escape($B).")",$g)as$Nj){$v["columns"][]=$Nj["name"];$v["descs"][]=null;}if(preg_match('~^CREATE( UNIQUE)? INDEX '.preg_quote(idf_escape($B).' ON '.idf_escape($Q),'~').' \((.*)\)$~i',$Lk[$B],$xj)){preg_match_all('/("[^"]*+")+( DESC)?/',$xj[2],$rg);foreach($rg[2]as$x=>$W){if($W)$v["descs"][$x]='1';}}if(!$H[""]||$v["type"]!="UNIQUE"||$v["columns"]!=$H[""]["columns"]||$v["descs"]!=$H[""]["descs"]||!preg_match("~^sqlite_~",$B))$H[$B]=$v;}return$H;}function
foreign_keys($Q){$H=array();foreach(get_rows("PRAGMA foreign_key_list(".table($Q).")")as$I){$p=&$H[$I["id"]];if(!$p)$p=$I;$p["source"][]=$I["from"];$p["target"][]=$I["to"];}return$H;}function
view($B){return
array("select"=>preg_replace('~^(?:[^`"[]+|`[^`]*`|"[^"]*")* AS\s+~iU','',get_val("SELECT sql FROM sqlite_master WHERE type = 'view' AND name = ".q($B))));}function
collations(){return(isset($_GET["create"])?get_vals("PRAGMA collation_list",1):array());}function
information_schema($j,$K=""){return
false;}function
error(){return
h(connection()->error);}function
check_sqlite_name($B){$wd="db|sdb|sqlite";if(!preg_match("~^[^\\0]*\\.($wd)\$~",$B)){connection()->error=lang(35,str_replace("|",", ",$wd));return
false;}return
true;}function
create_database($j,$xb){if(file_exists($j)){connection()->error=lang(36);return
false;}if(!check_sqlite_name($j))return
false;try{$_=new
Db();$_->attach(server_parts(array("path"=>$j)),'','');}catch(\Exception$md){connection()->error=$md->getMessage();return
false;}$_->query('PRAGMA encoding = "UTF-8"');$_->query('CREATE TABLE adminer (i)');$_->query('DROP TABLE adminer');return
true;}function
drop_databases(array$i){connection()->attach(server_parts(array("path"=>":memory:")),'','');foreach($i
as$j){if(!check_sqlite_name($j))return
false;if(!@unlink($j)){connection()->error=lang(36);return
false;}}return
true;}function
rename_database($B,$xb){if(!check_sqlite_name($B))return
false;connection()->attach(server_parts(array("path"=>":memory:")),'','');connection()->error=lang(36);return@rename(DB,$B);}function
auto_increment(){return" PRIMARY KEY AUTOINCREMENT";}function
alter_table($Q,$B,array$n,array$Ud,$Cb,$ad,$xb,$Ia,$wi){$sm=($Q==""||$Ud||$ad);foreach($n
as$m){if($m[0]!=""||!$m[1]||$m[2]){$sm=true;break;}}$b=array();$fi=array();foreach($n
as$m){if($m[1]){$b[]=($sm?$m[1]:"ADD ".implode($m[1]));if($m[0]!="")$fi[$m[0]]=$m[1][0];}}if(!$sm){foreach($b
as$W){if(!queries("ALTER TABLE ".table($Q)." $W"))return
false;}if($Q!=$B&&!queries("ALTER TABLE ".table($Q)." RENAME TO ".table($B)))return
false;}elseif(!recreate_table($Q,$B,$b,$fi,$Ud,$Ia,array(),"","",$ad))return
false;if($Ia){queries("BEGIN");queries("UPDATE sqlite_sequence SET seq = $Ia WHERE name = ".q($B));if(!connection()->affected_rows)queries("INSERT INTO sqlite_sequence (name, seq) VALUES (".q($B).", $Ia)");queries("COMMIT");}return
true;}function
recreate_table($Q,$B,array$n,array$fi,array$Ud,$Ia="",$w=array(),$Nc="",$na="",$ad=""){if($Q!=""){if(!$n){foreach(fields($Q)as$x=>$m){if($w)$m["auto_increment"]=0;$n[]=process_field($m,$m);$fi[$x]=idf_escape($x);}}$aj=false;foreach($n
as$m){if($m[6])$aj=true;}$Pc=array();foreach($w
as$x=>$W){if($W[2]=="DROP"){$Pc[$W[1]]=true;unset($w[$x]);}}foreach(indexes($Q)as$Jf=>$v){$e=array();foreach($v["columns"]as$x=>$d){if(!$fi[$d])continue
2;$e[]=$fi[$d].($v["descs"][$x]?" DESC":"");}if(!$Pc[$Jf]){if($v["type"]!="PRIMARY"||!$aj)$w[]=array($v["type"],$Jf,$e);}}foreach($w
as$x=>$W){if($W[0]=="PRIMARY"){unset($w[$x]);$Ud[]="  PRIMARY KEY (".implode(", ",$W[2]).")";}}foreach(foreign_keys($Q)as$Jf=>$p){foreach($p["source"]as$x=>$d){if(!$fi[$d])continue
2;$p["source"][$x]=idf_unescape($fi[$d]);}if(!isset($Ud[" $Jf"]))$Ud[]=" ".format_foreign_key($p);}queries("BEGIN");}$gb=array();foreach($n
as$m){if(preg_match('~GENERATED~',$m[3]))unset($fi[array_search($m[0],$fi)]);$gb[]="  ".implode($m);}$gb=array_merge($gb,array_filter($Ud));foreach(driver()->checkConstraints($Q)as$kb){if($kb!=$Nc)$gb[]="  CHECK ($kb)";}if($na)$gb[]="  CHECK ($na)";$ul=($Q!=""&&$Q==$B?"adminer_$B":$B);if(!$ad&&$Q!="")$ad=idx(table_status1($Q),"Engine");if(!queries("CREATE TABLE ".table($ul)." (\n".implode(",\n",$gb)."\n)".($ad!="table"&&in_array($ad,driver()->engines())?" $ad":"")))return
false;if($Q!=""){if($fi&&!queries("INSERT INTO ".table($ul)." (".implode(", ",$fi).") SELECT ".implode(", ",array_map('Adminer\idf_escape',array_keys($fi)))." FROM ".table($Q)))return
false;$Vl=array();foreach(triggers($Q)as$Tl=>$Al){$Rl=trigger($Tl,$Q);$Vl[]="CREATE TRIGGER ".idf_escape($Tl)." ".implode(" ",$Al)." ON ".table($B)."\n$Rl[Statement]";}$Ia=$Ia?"":get_val("SELECT seq FROM sqlite_sequence WHERE name = ".q($Q));if(!queries("DROP TABLE ".table($Q))||($Q==$B&&!queries("ALTER TABLE ".table($ul)." RENAME TO ".table($B)))||!alter_indexes($B,$w))return
false;if($Ia)queries("UPDATE sqlite_sequence SET seq = $Ia WHERE name = ".q($B));foreach($Vl
as$Rl){if(!queries($Rl))return
false;}queries("COMMIT");}return
true;}function
index_sql($Q,$T,$B,$e){return"CREATE $T ".($T!="INDEX"?"INDEX ":"").idf_escape($B!=""?$B:uniqid($Q."_"))." ON ".table($Q)." $e";}function
alter_indexes($Q,$b){foreach($b
as$Zi){if($Zi[0]=="PRIMARY")return
recreate_table($Q,$Q,array(),array(),array(),"",$b);}foreach(array_reverse($b)as$W){if(!queries($W[2]=="DROP"?"DROP INDEX ".idf_escape($W[1]):index_sql($Q,$W[0],$W[1],"(".implode(", ",$W[2]).")")))return
false;}return
true;}function
truncate_tables(array$S){return
apply_queries("DELETE FROM",$S);}function
drop_views(array$Im){return
apply_queries("DROP VIEW",$Im);}function
drop_tables(array$S){return
apply_queries("DROP TABLE",$S);}function
move_tables(array$S,array$Im,$ql){return
false;}function
trigger($B,$Q){if($B=="")return
array("Statement"=>"BEGIN\n\t;\nEND");$u='(?:[^`"\s]+|`[^`]*`|"[^"]*")+';$Ul=trigger_options();preg_match("~^CREATE\\s+TRIGGER\\s*$u\\s*(".implode("|",$Ul["Timing"]).")\\s+([a-z]+)(?:\\s+OF\\s+($u))?\\s+ON\\s*$u\\s*(?:FOR\\s+EACH\\s+ROW\\s)?(.*)~is",get_val("SELECT sql FROM sqlite_master WHERE type = 'trigger' AND name = ".q($B)),$A);$xh=$A[3];return
array("Timing"=>strtoupper($A[1]),"Event"=>strtoupper($A[2]).($xh?" OF":""),"Of"=>idf_unescape($xh),"Trigger"=>$B,"Statement"=>$A[4],);}function
triggers($Q){$H=array();$Ul=trigger_options();foreach(get_rows("SELECT * FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($Q))as$I){preg_match('~^CREATE\s+TRIGGER\s*(?:[^`"\s]+|`[^`]*`|"[^"]*")+\s*('.implode("|",$Ul["Timing"]).')\s*(.*?)\s+ON\b~i',$I["sql"],$A);$H[$I["name"]]=array($A[1],$A[2]);}return$H;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","UPDATE OF","DELETE"),"Type"=>array("FOR EACH ROW"),);}function
last_id($G){return
get_val("SELECT LAST_INSERT_ROWID()");}function
explain(Db$f,$F){return$f->query("EXPLAIN QUERY PLAN $F");}function
found_rows(array$R,array$Z){}function
types($wd=false){return
array();}function
create_sql($Q,$Ia,$Sk){$H=get_val("SELECT sql FROM sqlite_master WHERE type IN ('table', 'view') AND name = ".q($Q));foreach(indexes($Q)as$B=>$v){if($B==''||$v['type']=='FULLTEXT')continue;$H
.=";\n\n".index_sql($Q,$v['type'],$B,"(".implode(", ",array_map('Adminer\idf_escape',$v['columns'])).")");}return$H;}function
truncate_sql($Q){return"DELETE FROM ".table($Q);}function
use_sql($ic,$Sk=""){return"";}function
trigger_sql($Q){return
implode(get_vals("SELECT sql || ';;\n' FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($Q)));}function
show_variables(){$H=array();foreach(get_rows("PRAGMA pragma_list")as$I){$B=$I["name"];if($B!="pragma_list"&&$B!="compile_options"){$H[$B]=array($B,'');foreach(get_rows("PRAGMA $B")as$I)$H[$B][1].=implode(", ",$I)."\n";}}return$H;}function
show_status(){$H=array();foreach(get_vals("PRAGMA compile_options")as$Rh)$H[]=explode("=",$Rh,2)+array('','');return$H;}function
convert_field(array$m){}function
unconvert_field(array$m,$H){return$H;}function
support($Ad){return
preg_match('~^(check|columns|database|drop_col|dump|fast_status|indexes|descidx|move_col|sql|status|table|transaction_ddl|trigger|variables|view|view_trigger)$~',$Ad);}}add_driver("mssql","MS SQL");if(isset($_GET["mssql"])){define('Adminer\DRIVER',"mssql");if(extension_loaded("sqlsrv")&&$_GET["ext"]!="pdo"){class
Db
extends
SqlDb{var$extension="sqlsrv";private$link,$result,$warnings;private
function
get_error(){$this->error="";foreach(sqlsrv_errors()as$l){$this->errno=$l["code"];$this->error
.="$l[message]\n";}$this->error=rtrim($this->error);}function
attach(array$M,$U,$E){sqlsrv_configure("WarningsReturnAsErrors",0);$Lb=array("UID"=>$U,"PWD"=>$E,"CharacterSet"=>"UTF-8");$Mk=adminer()->connectSsl();if(isset($Mk["Encrypt"]))$Lb["Encrypt"]=$Mk["Encrypt"];if(isset($Mk["TrustServerCertificate"]))$Lb["TrustServerCertificate"]=$Mk["TrustServerCertificate"];$j=adminer()->database();if($j!="")$Lb["Database"]=$j;$Mi=$M["port"];$this->link=@sqlsrv_connect($M["host"].($Mi?",$Mi":""),$Lb);if($this->link){$jf=sqlsrv_server_info($this->link);$this->server_info=$jf['SQLServerVersion'];}else$this->get_error();return($this->link?'':$this->error);}function
quote($P){$dm=strlen($P)!=strlen(utf8_decode($P));return($dm?"N":"")."'".str_replace("'","''",$P)."'";}function
select_db($ic){return$this->query(use_sql($ic));}function
query($F,$cm=false){$G=sqlsrv_query($this->link,$F);$this->error="";if(!$G){$this->get_error();return
false;}return$this->store_result($G);}function
multi_query($F){$this->result=sqlsrv_query($this->link,$F);$this->error="";if(!$this->result){$this->get_error();return
false;}return
true;}function
store_result($G=null){if(!$G)$G=$this->result;if(!$G)return
false;$this->warnings=sqlsrv_errors(SQLSRV_ERR_WARNINGS);if(sqlsrv_field_metadata($G))return
new
Result($G);$this->affected_rows=sqlsrv_rows_affected($G);return
true;}function
next_result(){if(!$this->result)return
false;$H=sqlsrv_next_result($this->result);if($H===false){$this->get_error();$this->result=null;return
true;}return!!$H;}function
warnings(){$H=array();foreach((array)$this->warnings
as$Lm)$H[]=$Lm["message"];return$H;}}class
Result{var$num_rows;private$result,$offset=0,$fields;function
__construct($G){$this->result=$G;}private
function
convert($I){foreach((array)$I
as$x=>$W){if(is_a($W,'DateTime'))$I[$x]=$W->format("Y-m-d H:i:s");}return$I;}function
fetch_assoc(){return$this->convert(sqlsrv_fetch_array($this->result,SQLSRV_FETCH_ASSOC));}function
fetch_row(){return$this->convert(sqlsrv_fetch_array($this->result,SQLSRV_FETCH_NUMERIC));}function
fetch_field(){if(!$this->fields)$this->fields=sqlsrv_field_metadata($this->result);$m=$this->fields[$this->offset++];$H=new
\stdClass;$H->name=$m["Name"];$H->type=($m["Type"]==1?254:15);$H->charsetnr=(in_array($m["Type"],array(-2,-3,-4))?63:0);return$H;}function
seek($Ah){for($s=0;$s<$Ah;$s++)sqlsrv_fetch($this->result);}}function
last_id($G){return(string)get_val("SELECT SCOPE_IDENTITY()");}function
explain(Db$f,$F){$f->query("SET SHOWPLAN_ALL ON");$H=$f->query($F);$f->query("SET SHOWPLAN_ALL OFF");return$H;}}else{abstract
class
MssqlDb
extends
PdoDb{function
select_db($ic){return$this->query(use_sql($ic));}function
lastInsertId(){return$this->pdo->lastInsertId();}function
warnings(){$G=$this->multi;if(!is_object($G))return
array();$l=$G->errorInfo();return
array((string)$l[2]);}}function
last_id($G){return
connection()->lastInsertId();}function
explain(Db$f,$F){}if(extension_loaded("pdo_sqlsrv")){class
Db
extends
MssqlDb{var$extension="PDO_SQLSRV";function
attach(array$M,$U,$E){$Mi=$M["port"];$Rc="sqlsrv:Server=$M[host]".($Mi?",$Mi":"");$Mk=adminer()->connectSsl();foreach(array("Encrypt","TrustServerCertificate")as$x){if(isset($Mk[$x]))$Rc
.=";$x=".($Mk[$x]?1:0);}return$this->dsn($Rc,$U,$E,array(\PDO::SQLSRV_ATTR_DIRECT_QUERY=>true));}}}elseif(extension_loaded("pdo_dblib")){class
Db
extends
MssqlDb{var$extension="PDO_DBLIB";function
attach(array$M,$U,$E){$Mi=$M["port"];$zk=$M["socket"];return$this->dsn("dblib:charset=utf8;host=$M[host]".($Mi!=""?";port=$Mi":($zk!=""?";unix_socket=$zk":"")),$U,$E);}}}}class
Driver
extends
SqlDriver{static$extensions=array("SQLSRV","PDO_SQLSRV","PDO_DBLIB");static$jush="mssql";static$serverSocket=true;var$insertFunctions=array("date|time"=>"getdate");var$editFunctions=array("int|decimal|real|float|money|datetime"=>"+/-","char|text"=>"+",);var$functions=array("len","lower","round","upper");var$grouping=array("avg","count","count distinct","max","min","sum");var$generated=array("PERSISTED","VIRTUAL");var$onActions="NO ACTION|CASCADE|SET NULL|SET DEFAULT";private$unknownTypes=array();function
operators($al){return
array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL");}static
function
connect($M,$U,$E){if($M=="")$M="localhost:1433";return
parent::connect($M,$U,$E);}function
__construct(Db$f){parent::__construct($f);$this->types=array(lang(28)=>array("tinyint"=>3,"smallint"=>5,"int"=>10,"bigint"=>20,"bit"=>1,"decimal"=>0,"numeric"=>0,"real"=>12,"float"=>53,"smallmoney"=>10,"money"=>20,"vector"=>0,),lang(29)=>array("date"=>10,"smalldatetime"=>19,"datetime"=>19,"datetime2"=>19,"time"=>8,"datetimeoffset"=>26),lang(30)=>array("char"=>8000,"varchar"=>8000,"text"=>2147483647,"nchar"=>4000,"nvarchar"=>4000,"ntext"=>1073741823,"uniqueidentifier"=>36,"xml"=>2147483647,"json"=>2147483647,"sql_variant"=>8000,"hierarchyid"=>892,),lang(31)=>array("binary"=>8000,"varbinary"=>8000,"image"=>2147483647),lang(33)=>array("geometry"=>0,"geography"=>0),);$bm=array_flip(get_vals("SELECT name FROM sys.types WHERE is_user_defined = 0 ORDER BY name"));if($bm){foreach($this->types
as$r=>$re){foreach($re
as$T=>$y){if(isset($bm[$T]))unset($bm[$T]);else
unset($this->types[$r][$T]);}if(!$this->types[$r])unset($this->types[$r]);}$this->unknownTypes=array_keys($bm);}}function
types(){return
parent::types()+array_fill_keys($this->unknownTypes,0);}function
structuredTypes(){return
array_merge(parent::structuredTypes(),$this->unknownTypes);}function
insertUpdate($Q,array$J,array$Zi){$n=fields($Q);$mm=array();$Z=array();$N=reset($J);$e="c".implode(", c",range(1,count($N)));$cb=0;$pf=array();foreach($N
as$x=>$W){$cb++;$B=idf_unescape($x);if(!$n[$B]["auto_increment"])$pf[$x]="c$cb";if(isset($Zi[$B]))$Z[]="$x = c$cb";else$mm[]="$x = c$cb";}$Y=array();foreach($J
as$N)$Y[]="(".implode(", ",$N).")";if($Z){$Se=queries("SET IDENTITY_INSERT ".table($Q)." ON");$H=queries("MERGE ".table($Q)." USING (VALUES\n\t".implode(",\n\t",$Y)."\n) AS source ($e) ON ".implode(" AND ",$Z).($mm?"\nWHEN MATCHED THEN UPDATE SET ".implode(", ",$mm):"")."\nWHEN NOT MATCHED THEN INSERT (".implode(", ",array_keys($Se?$N:$pf)).") VALUES (".($Se?$e:implode(", ",$pf)).");");if($Se)queries("SET IDENTITY_INSERT ".table($Q)." OFF");}else$H=queries("INSERT INTO ".table($Q)." (".implode(", ",array_keys($N)).") VALUES\n".implode(",\n",$Y));return$H;}function
begin(){return
queries("BEGIN TRANSACTION");}function
convertSearch($u,array$W,array$m){return(preg_match('~^(bit|n?text|xml|json|vector|uniqueidentifier|sql_variant|hierarchyid|geography|geometry)$~',$m["type"])?"CAST($u AS nvarchar(max))":$u);}function
quoteBinary($Oj){return"0x".bin2hex($Oj);}function
warnings(){$H=array();foreach($this->conn->warnings()as$Ig){$Ig=trim(preg_replace('~^(\[[^]]+])+~','',$Ig));if($Ig!="")$H[]=$Ig;}return
nl_br(h(implode("\n",$H)));}function
tableHelp($B,$Df=false){$hg=array("sys"=>"catalog-views/sys-","INFORMATION_SCHEMA"=>"information-schema-views/",);$_=$hg[get_schema()];if($_)return"relational-databases/system-$_".preg_replace('~_~','-',strtolower($B))."-transact-sql";}}function
idf_escape($u){return"[".str_replace("]","]]",$u)."]";}function
table($u){return($_GET["ns"]!=""?idf_escape($_GET["ns"]).".":"").idf_escape($u);}function
get_databases($Sd){return
get_vals("SELECT name FROM sys.databases WHERE name NOT IN ('master', 'tempdb', 'model', 'msdb')");}function
limit($F,$Z,$z,$Ah=0,$dk=" "){return($z?" TOP (".($z+$Ah).")":"")." $F$Z";}function
limit1($Q,$F,$Z,$dk="\n"){return
limit($F,$Z,1,0,$dk);}function
db_collation($j,array$yb){return
get_val("SELECT collation_name FROM sys.databases WHERE name = ".q($j));}function
logged_user(){return
get_val("SELECT SUSER_NAME()");}function
tables_list(){return
get_key_vals("SELECT name, type_desc FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ORDER BY name");}function
count_tables(array$i){$H=array();foreach($i
as$j){connection()->select_db($j);$H[$j]=get_val("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES");}return$H;}function
table_status($B="",$_d=false){$H=array();$xk=array();foreach(get_rows("SELECT object_id, SUM(CASE WHEN index_id < 2 THEN row_count ELSE 0 END) AS [Rows],
SUM(CASE WHEN index_id < 2 THEN used_page_count ELSE 0 END) * 8192 AS Data_length,
SUM(CASE WHEN index_id > 1 THEN used_page_count ELSE 0 END) * 8192 AS Index_length,
SUM(reserved_page_count - used_page_count) * 8192 AS Data_free
FROM sys.dm_db_partition_stats
GROUP BY object_id",null,"")as$I){$wh=$I["object_id"];unset($I["object_id"]);$xk[$wh]=$I;}foreach(get_rows("SELECT ao.object_id, ao.name AS Name, ao.type_desc AS Engine,
	(SELECT cast(value as varchar(max)) FROM fn_listextendedproperty(default, 'SCHEMA', schema_name(schema_id), 'TABLE', ao.name, null, null)) AS Comment
FROM sys.all_objects AS ao
WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ".($B!=""?"AND name = ".q($B):"ORDER BY name"))as$I){$wh=$I["object_id"];unset($I["object_id"]);$H[$I["Name"]]=$I+idx($xk,$wh,array());}return$H;}function
is_view(array$R){return$R["Engine"]=="VIEW";}function
fk_support(array$R){return
true;}function
fields($Q){$Eb=get_key_vals("SELECT objname, cast(value as varchar(max)) FROM fn_listextendedproperty('MS_DESCRIPTION', 'schema', ".q(get_schema()).", 'table', ".q($Q).", 'column', NULL)");$H=array();$bl=get_val("SELECT object_id FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') AND name = ".q($Q));foreach(get_rows("SELECT c.max_length, c.precision, c.scale, c.name, c.is_nullable, c.is_identity, c.collation_name,
	COALESCE(bt.name, t.name) type, d.definition [default], d.name default_constraint, i.is_primary_key
FROM sys.all_columns c
JOIN sys.types t ON c.user_type_id = t.user_type_id
LEFT JOIN sys.types bt ON t.system_type_id = bt.user_type_id AND t.is_user_defined = 1
LEFT JOIN sys.default_constraints d ON c.default_object_id = d.object_id
LEFT JOIN sys.index_columns ic ON c.object_id = ic.object_id AND c.column_id = ic.column_id
LEFT JOIN sys.indexes i ON ic.object_id = i.object_id AND ic.index_id = i.index_id
WHERE c.object_id = ".q($bl))as$I){$T=$I["type"];$y=(preg_match("~char|binary~",$T)?intval($I["max_length"])/($T[0]=='n'?2:1):($T=="decimal"?"$I[precision],$I[scale]":($T=="vector"?(intval($I["max_length"])-8)/4:"")));$H[$I["name"]]=array("field"=>$I["name"],"full_type"=>$T.($y?"($y)":""),"type"=>$T,"length"=>$y,"default"=>(preg_match("~^\('(.*)'\)$~",$I["default"],$A)?str_replace("''","'",$A[1]):$I["default"]),"default_constraint"=>$I["default_constraint"],"null"=>$I["is_nullable"],"auto_increment"=>$I["is_identity"],"collation"=>$I["collation_name"],"privileges"=>array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1),"primary"=>$I["is_primary_key"],"comment"=>$Eb[$I["name"]],);}foreach(get_rows("SELECT * FROM sys.computed_columns WHERE object_id = ".q($bl))as$I){$H[$I["name"]]["generated"]=($I["is_persisted"]?"PERSISTED":"VIRTUAL");$H[$I["name"]]["default"]=$I["definition"];}return$H;}function
indexes($Q,$g=null){$H=array();foreach(get_rows("SELECT i.name, key_ordinal, is_unique, is_primary_key, c.name AS column_name, is_descending_key
FROM sys.indexes i
INNER JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
INNER JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
WHERE OBJECT_NAME(i.object_id) = ".q($Q),$g)as$I){$B=$I["name"];$H[$B]["type"]=($I["is_primary_key"]?"PRIMARY":($I["is_unique"]?"UNIQUE":"INDEX"));$H[$B]["lengths"]=array();$H[$B]["columns"][$I["key_ordinal"]]=$I["column_name"];$H[$B]["descs"][$I["key_ordinal"]]=($I["is_descending_key"]?'1':null);}return$H;}function
view($B){return
array("select"=>preg_replace('~^(?:[^[]|\[[^]]*])*\s+AS\s+~isU','',get_val("SELECT VIEW_DEFINITION FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_SCHEMA = SCHEMA_NAME() AND TABLE_NAME = ".q($B))));}function
collations(){$H=array();foreach(get_vals("SELECT name FROM fn_helpcollations()")as$xb)$H[preg_replace('~_.*~','',$xb)][]=$xb;return$H;}function
information_schema($j,$K=""){return
in_array($K!=""?$K:get_schema(),array("INFORMATION_SCHEMA","sys"));}function
error(){return
nl_br(h(preg_replace('~^(\[[^]]*])+~m','',connection()->error)));}function
create_database($j,$xb){return
queries("CREATE DATABASE ".idf_escape($j).(preg_match('~^[a-z0-9_]+$~i',$xb)?" COLLATE $xb":""));}function
drop_databases(array$i){return!!queries("DROP DATABASE ".implode(", ",array_map('Adminer\idf_escape',$i)));}function
rename_database($B,$xb){if(preg_match('~^[a-z0-9_]+$~i',$xb))queries("ALTER DATABASE ".idf_escape(DB)." COLLATE $xb");queries("ALTER DATABASE ".idf_escape(DB)." MODIFY NAME = ".idf_escape($B));return
true;}function
auto_increment(){return" IDENTITY".($_POST["Auto_increment"]!=""?"(".number($_POST["Auto_increment"]).",1)":"")." PRIMARY KEY";}function
alter_table($Q,$B,array$n,array$Ud,$Cb,$ad,$xb,$Ia,$wi){$b=array();$Eb=array();$bi=fields($Q);foreach($n
as$m){$d=idf_escape($m[0]);$W=$m[1];if(!$W)$b["DROP"][]=" COLUMN $d";else{$W[1]=preg_replace("~( COLLATE )'(\\w+)'~",'\1\2',$W[1]);$Eb[$m[0]]=$W[5];unset($W[5]);if(preg_match('~ AS ~',$W[3]))unset($W[1],$W[2]);if($m[0]=="")$b["ADD"][]="\n  ".implode("",$W).($Q==""?substr($Ud[$W[0]],16+strlen($W[0])):"");else{$k=$W[3];unset($W[3]);unset($W[6]);if($d!=$W[0])queries("EXEC sp_rename ".q(table($Q).".$d").", ".q(idf_unescape($W[0])).", 'COLUMN'");$b["ALTER COLUMN ".implode("",$W)][]="";$ai=$bi[$m[0]];if(default_value($ai)!=$k){if($ai["default"]!==null)$b["DROP"][]=" ".idf_escape($ai["default_constraint"]);if($k)$b["ADD"][]="\n $k FOR $d";}}}}if($Q==""){$ma=(array)$b["ADD"];foreach($Ud
as$x=>$W){if(!is_string($x))$ma[]="\n$W";}return
queries("CREATE TABLE ".table($B)." (".implode(",",$ma)."\n)");}if($Q!=$B)queries("EXEC sp_rename ".q(table($Q)).", ".q($B));if($Ud)$b[""]=$Ud;foreach($b
as$x=>$W){if(!queries("ALTER TABLE ".table($B)." $x".implode(",",$W)))return
false;}foreach($Eb
as$x=>$W){$Cb=substr($W,9);queries("EXEC sp_dropextendedproperty @name = N'MS_Description', @level0type = N'Schema', @level0name = ".q(get_schema()).", @level1type = N'Table', @level1name = ".q($B).", @level2type = N'Column', @level2name = ".q($x));queries("EXEC sp_addextendedproperty
@name = N'MS_Description',
@value = $Cb,
@level0type = N'Schema',
@level0name = ".q(get_schema()).",
@level1type = N'Table',
@level1name = ".q($B).",
@level2type = N'Column',
@level2name = ".q($x));}return
true;}function
alter_indexes($Q,$b){$v=array();$Mc=array();foreach($b
as$W){if($W[2]=="DROP"){if($W[0]=="PRIMARY")$Mc[]=idf_escape($W[1]);else$v[]=idf_escape($W[1])." ON ".table($Q);}elseif(!queries(($W[0]!="PRIMARY"?"CREATE $W[0] ".($W[0]!="INDEX"?"INDEX ":"").idf_escape($W[1]!=""?$W[1]:uniqid($Q."_"))." ON ".table($Q):"ALTER TABLE ".table($Q)." ADD PRIMARY KEY")." (".implode(", ",$W[2]).")"))return
false;}return(!$v||queries("DROP INDEX ".implode(", ",$v)))&&(!$Mc||queries("ALTER TABLE ".table($Q)." DROP ".implode(", ",$Mc)));}function
found_rows(array$R,array$Z){}function
foreign_keys($Q){$H=array();$Kh=array("CASCADE","NO ACTION","SET NULL","SET DEFAULT");$K=get_schema();foreach(get_rows("EXEC sp_fkeys @fktable_name = ".q($Q).", @fktable_owner = ".q($K))as$I){$p=&$H[$I["FK_NAME"]];$p["db"]=($I["PKTABLE_QUALIFIER"]==DB?"":$I["PKTABLE_QUALIFIER"]);$p["ns"]=($I["PKTABLE_OWNER"]==$K?"":$I["PKTABLE_OWNER"]);$p["table"]=$I["PKTABLE_NAME"];$p["on_update"]=$Kh[$I["UPDATE_RULE"]];$p["on_delete"]=$Kh[$I["DELETE_RULE"]];$p["source"][]=$I["FKCOLUMN_NAME"];$p["target"][]=$I["PKCOLUMN_NAME"];}return$H;}function
truncate_tables(array$S){return
apply_queries("TRUNCATE TABLE",$S);}function
drop_views(array$Im){return
queries("DROP VIEW ".implode(", ",array_map('Adminer\table',$Im)));}function
drop_tables(array$S){return
queries("DROP TABLE ".implode(", ",array_map('Adminer\table',$S)));}function
move_tables(array$S,array$Im,$ql){return
apply_queries("ALTER SCHEMA ".idf_escape($ql)." TRANSFER",array_merge($S,$Im));}function
trigger($B,$Q){if($B=="")return
array();$J=get_rows("SELECT s.name [Trigger],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT'
	WHEN OBJECTPROPERTY(s.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE'
	WHEN OBJECTPROPERTY(s.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing],
c.text
FROM sysobjects s
JOIN syscomments c ON s.id = c.id
WHERE s.xtype = 'TR' AND s.name = ".q($B));$H=reset($J);if($H)$H["Statement"]=preg_replace('~^.+\s+AS\s+~isU','',$H["text"]);return$H;}function
triggers($Q){$H=array();foreach(get_rows("SELECT sys1.name,
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT'
	WHEN OBJECTPROPERTY(sys1.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE'
	WHEN OBJECTPROPERTY(sys1.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing]
FROM sysobjects sys1
JOIN sysobjects sys2 ON sys1.parent_obj = sys2.id
WHERE sys1.xtype = 'TR' AND sys2.name = ".q($Q))as$I)$H[$I["name"]]=array($I["Timing"],$I["Event"]);return$H;}function
trigger_options(){return
array("Timing"=>array("AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("AS"),);}function
schemas(){return
get_vals("SELECT name FROM sys.schemas");}function
get_schema(){if($_GET["ns"]!="")return$_GET["ns"];return
get_val("SELECT SCHEMA_NAME()");}function
set_schema($K,$g=null){$_GET["ns"]=$K;return
true;}function
create_sql($Q,$Ia,$Sk){if(is_view(table_status1($Q))){$Hm=view($Q);return"CREATE VIEW ".table($Q)." AS $Hm[select]";}$n=array();$Zi=false;foreach(fields($Q)as$B=>$m){$W=process_field($m,$m);if($W[6])$Zi=true;$n[]=implode("",$W);}foreach(indexes($Q)as$B=>$v){if(!$Zi||$v["type"]!="PRIMARY"){$e=array();foreach($v["columns"]as$x=>$W)$e[]=idf_escape($W).($v["descs"][$x]?" DESC":"");$B=idf_escape($B);$n[]=($v["type"]=="INDEX"?"INDEX $B":"CONSTRAINT $B ".($v["type"]=="UNIQUE"?"UNIQUE":"PRIMARY KEY"))." (".implode(", ",$e).")";}}foreach(driver()->checkConstraints($Q)as$B=>$kb)$n[]="CONSTRAINT ".idf_escape($B)." CHECK ($kb)";return"CREATE TABLE ".table($Q)." (\n\t".implode(",\n\t",$n)."\n)";}function
foreign_keys_sql($Q){$n=array();foreach(foreign_keys($Q)as$Ud)$n[]=ltrim(format_foreign_key($Ud));return($n?"ALTER TABLE ".table($Q)." ADD\n\t".implode(",\n\t",$n).";\n\n":"");}function
truncate_sql($Q){return"TRUNCATE TABLE ".table($Q);}function
use_sql($ic,$Sk=""){return"USE ".idf_escape($ic);}function
trigger_sql($Q){$H="";foreach(triggers($Q)as$B=>$Rl)$H
.=create_trigger(" ON ".table($Q),trigger($B,$Q)).";";return$H;}function
convert_field(array$m){}function
unconvert_field(array$m,$H){return$H;}function
support($Ad){return
preg_match('~^(check|comment|columns|database|drop_col|dump|fast_status|indexes|descidx|scheme|sql|table|transaction_ddl|trigger|view|view_trigger)$~',$Ad);}}add_driver("oracle","Oracle beta");if(isset($_GET["oracle"])){define('Adminer\DRIVER',"oracle");function
easy_connect(array$M){return
url_host($M["host"]).($M["port"]!=""?":$M[port]":"").$M["path"];}if(extension_loaded("oci8")&&$_GET["ext"]!="pdo"){class
Db
extends
SqlDb{var$extension="oci8";var$_current_db;private$link;function
_error($fd,$l){if(ini_bool("html_errors"))$l=html_entity_decode(strip_tags($l));$l=preg_replace('~^[^:]*: ~','',$l);$this->error=$l;}function
attach(array$M,$U,$E){$this->link=@oci_new_connect($U,$E,easy_connect($M),"AL32UTF8");if($this->link){$this->server_info=oci_server_version($this->link);return'';}$l=oci_error();return($l?$l["message"]:lang(25));}function
quote($P){return"'".str_replace("'","''",$P)."'";}function
select_db($ic){$this->_current_db=$ic;return
true;}function
query($F,$cm=false){$G=oci_parse($this->link,$F);$this->error="";if(!$G){$l=oci_error($this->link);$this->errno=$l["code"];$this->error=$l["message"];return
false;}set_error_handler(array($this,'_error'));$H=@oci_execute($G);restore_error_handler();if($H){if(oci_num_fields($G))return
new
Result($G);$this->affected_rows=oci_num_rows($G);oci_free_statement($G);}return$H;}function
timeout($Xg){return(function_exists('oci_set_call_timeout')?oci_set_call_timeout($this->link,$Xg):false);}}class
Result{var$num_rows;private$result,$offset=1;function
__construct($G){$this->result=$G;}private
function
convert($I){foreach((array)$I
as$x=>$W){if(is_a($W,'OCILob')||is_a($W,'OCI-Lob'))$I[$x]=$W->load();}return$I;}function
fetch_assoc(){return$this->convert(oci_fetch_assoc($this->result));}function
fetch_row(){return$this->convert(oci_fetch_row($this->result));}function
fetch_field(){$d=$this->offset++;$H=new
\stdClass;$H->name=oci_field_name($this->result,$d);$T=oci_field_type($this->result,$d);$H->native_type=$T;$H->type=$T;$H->charsetnr=(preg_match("~raw|blob|bfile~",$T)?63:0);return$H;}}}elseif(extension_loaded("pdo_oci")){class
Db
extends
PdoDb{var$extension="PDO_OCI";var$_current_db;function
attach(array$M,$U,$E){return$this->dsn("oci:dbname=//".easy_connect($M).";charset=AL32UTF8",$U,$E);}function
select_db($ic){$this->_current_db=$ic;return
true;}}}class
Driver
extends
SqlDriver{static$extensions=array("OCI8","PDO_OCI");static$jush="oracle";static$serverPath=true;var$insertFunctions=array("date"=>"current_date","timestamp"=>"current_timestamp",);var$editFunctions=array("number|float|double"=>"+/-","date|timestamp"=>"+ interval/- interval","char|clob"=>"||",);var$functions=array("length","lower","round","upper");var$grouping=array("avg","count","count distinct","max","min","sum");function
operators($al){return
array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL","SQL");}function
__construct(Db$f){parent::__construct($f);$this->types=array(lang(28)=>array("number"=>38,"binary_float"=>12,"binary_double"=>21),lang(29)=>array("date"=>10,"timestamp"=>29,"interval year"=>12,"interval day"=>28),lang(30)=>array("char"=>2000,"varchar2"=>4000,"nchar"=>2000,"nvarchar2"=>4000,"clob"=>4294967295,"nclob"=>4294967295),lang(31)=>array("raw"=>2000,"long raw"=>2147483648,"blob"=>4294967295,"bfile"=>4294967296),);}function
begin(){return
true;}function
convertSearch($u,array$W,array$m){$T=$m["type"];$Ei=strpos($W["op"],"LIKE")!==false;if($T=="xmltype")return"XMLSERIALIZE(CONTENT $u AS VARCHAR2(4000))";if($T=="json")return"JSON_SERIALIZE($u)";if(preg_match('~^(date|timestamp)~',$T))return"TO_CHAR($u, 'YYYY-MM-DD HH24:MI:SS')";if(preg_match('~char~',$T)||(preg_match('~clob~',$T)&&$Ei))return$u;return(!$Ei&&preg_match(number_type(),$T)?$u:"TO_CHAR($u)");}function
quoteBinary($Oj){return"HEXTORAW(".q(bin2hex($Oj)).")";}function
hasCStyleEscapes(){return
true;}function
allFields(){$H=array();$Hm=views_table("view_name");$J=get_rows('SELECT c.table_name "tab", c.column_name "field", c.data_type "type", c.nullable "nullable",
	c.data_precision "precision", c.data_scale "scale", c.char_col_decl_length "char_length"
FROM all_tab_columns c
WHERE c.table_name IN (
	SELECT table_name FROM all_tables WHERE tablespace_name = '.q(DB).where_owner(" AND ")."
	UNION SELECT view_name FROM $Hm
)".where_owner(" AND ","c.owner").'
ORDER BY c.table_name, c.column_id',$this->conn);foreach($J
as$I){$y="$I[precision],$I[scale]";$I["length"]=($y==","?$I["char_length"]:$y);$I["type"]=strtolower($I["type"]);$I["null"]=($I["nullable"]=="Y");$H[$I["tab"]][]=$I;}return$H;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($Sd){return
get_vals("SELECT DISTINCT tablespace_name FROM (
SELECT tablespace_name FROM user_tablespaces
UNION SELECT tablespace_name FROM all_tables WHERE tablespace_name IS NOT NULL
)
ORDER BY 1");}function
limit($F,$Z,$z,$Ah=0,$dk=" "){return($Ah?" * FROM (SELECT t.*, rownum AS rnum FROM (SELECT $F$Z) t WHERE rownum <= ".($z+$Ah).") WHERE rnum > $Ah":($z?" * FROM (SELECT $F$Z) WHERE rownum <= ".($z+$Ah):" $F$Z"));}function
limit1($Q,$F,$Z,$dk="\n"){return" $F$Z";}function
db_collation($j,array$yb){return
get_val("SELECT value FROM nls_database_parameters WHERE parameter = 'NLS_CHARACTERSET'");}function
logged_user(){return
get_val("SELECT USER FROM DUAL");}function
get_current_db(){$j=connection()->_current_db?:DB;connection()->_current_db=null;return$j;}function
where_owner($Ui,$ji="owner"){if(!$_GET["ns"])return'';return"$Ui$ji = sys_context('USERENV', 'CURRENT_SCHEMA')";}function
views_table($e){$ji=where_owner('');return"(SELECT $e FROM all_views WHERE ".($ji?:"rownum < 0").")";}function
tables_list(){$Hm=views_table("view_name");$ji=where_owner(" AND ");return
get_key_vals("SELECT table_name, 'table' FROM all_tables WHERE tablespace_name = ".q(DB)."$ji
UNION SELECT view_name, 'view' FROM $Hm
ORDER BY 1");}function
count_tables(array$i){$H=array();foreach($i
as$j)$H[$j]=get_val("SELECT COUNT(*) FROM all_tables WHERE tablespace_name = ".q($j));return$H;}function
table_status($B="",$_d=false){$H=array();$Tj=q($B);$j=get_current_db();$Hm=views_table("view_name");$ji=where_owner(" AND ","t.owner");foreach(get_rows('SELECT t.table_name "Name", \'table\' "Engine", s.bytes "Data_length", i.bytes "Index_length", t.num_rows "Rows"
FROM all_tables t
LEFT JOIN (SELECT segment_name, SUM(bytes) bytes FROM user_segments WHERE segment_type LIKE \'TABLE%\' GROUP BY segment_name) s ON s.segment_name = t.table_name
LEFT JOIN (SELECT i.table_name, SUM(s.bytes) bytes FROM user_indexes i
	JOIN user_segments s ON s.segment_name = i.index_name AND s.segment_type LIKE \'INDEX%\' GROUP BY i.table_name) i ON i.table_name = t.table_name
WHERE t.tablespace_name = '.q($j).$ji.($B!=""?" AND t.table_name = $Tj":"")."
UNION SELECT view_name, 'view', 0, 0, 0 FROM $Hm".($B!=""?" WHERE view_name = $Tj":"")."
ORDER BY 1")as$I)$H[$I["Name"]]=$I;return$H;}function
is_view(array$R){return$R["Engine"]=="view";}function
fk_support(array$R){return
true;}function
fields($Q){$H=array();$ji=where_owner(" AND ");foreach(get_rows("SELECT * FROM all_tab_columns WHERE table_name = ".q($Q)."$ji ORDER BY column_id")as$I){$T=$I["DATA_TYPE"];$y="$I[DATA_PRECISION],$I[DATA_SCALE]";if($y==",")$y=$I["CHAR_COL_DECL_LENGTH"];$ej=array("insert"=>1,"select"=>1,"update"=>1,"order"=>1);if($I["DATA_TYPE_OWNER"]==""||$T=="XMLTYPE")$ej["where"]=1;$H[$I["COLUMN_NAME"]]=array("field"=>$I["COLUMN_NAME"],"full_type"=>$T.($y?"($y)":""),"type"=>strtolower($T),"length"=>$y,"default"=>$I["DATA_DEFAULT"],"null"=>($I["NULLABLE"]=="Y"),"privileges"=>$ej,);}return$H;}function
indexes($Q,$g=null){$H=array();$ji=where_owner(" AND ","aic.table_owner");foreach(get_rows("SELECT aic.*, ac.constraint_type, atc.data_default
FROM all_ind_columns aic
LEFT JOIN all_constraints ac ON aic.index_name = ac.constraint_name AND aic.table_name = ac.table_name AND aic.index_owner = ac.owner
LEFT JOIN all_tab_cols atc ON aic.column_name = atc.column_name AND aic.table_name = atc.table_name AND aic.index_owner = atc.owner
WHERE aic.table_name = ".q($Q)."$ji
ORDER BY ac.constraint_type, aic.column_position",$g)as$I){$ef=$I["INDEX_NAME"];$Ab=$I["DATA_DEFAULT"];$Ab=($Ab?trim($Ab,'"'):$I["COLUMN_NAME"]);$H[$ef]["type"]=($I["CONSTRAINT_TYPE"]=="P"?"PRIMARY":($I["CONSTRAINT_TYPE"]=="U"?"UNIQUE":"INDEX"));$H[$ef]["columns"][]=$Ab;$H[$ef]["lengths"][]=($I["CHAR_LENGTH"]&&$I["CHAR_LENGTH"]!=$I["COLUMN_LENGTH"]?$I["CHAR_LENGTH"]:null);$H[$ef]["descs"][]=($I["DESCEND"]&&$I["DESCEND"]=="DESC"?'1':null);}return$H;}function
view($B){$Hm=views_table("view_name, text");$J=get_rows('SELECT text "select" FROM '.$Hm.' WHERE view_name = '.q($B));return
reset($J);}function
collations(){return
array();}function
information_schema($j,$K=""){return($K!=""?$K:get_schema())=="INFORMATION_SCHEMA";}function
error(){return
h(connection()->error);}function
explain(Db$f,$F){$f->query("EXPLAIN PLAN FOR $F");return$f->query("SELECT * FROM plan_table");}function
found_rows(array$R,array$Z){}function
auto_increment(){return"";}function
alter_table($Q,$B,array$n,array$Ud,$Cb,$ad,$xb,$Ia,$wi){$b=$Mc=array();$bi=($Q?fields($Q):array());foreach($n
as$m){$W=$m[1];if($W&&$m[0]!=""&&idf_escape($m[0])!=$W[0])queries("ALTER TABLE ".table($Q)." RENAME COLUMN ".idf_escape($m[0])." TO $W[0]");$ai=$bi[$m[0]];if($W&&$ai){$Ch=process_field($ai,$ai);if($W[2]==$Ch[2])$W[2]="";}if($W)$b[]=($Q!=""?($m[0]!=""?"MODIFY (":"ADD ("):"  ").implode($W).($Q!=""?")":"");else$Mc[]=idf_escape($m[0]);}if($Q=="")return
queries("CREATE TABLE ".table($B)." (\n".implode(",\n",array_merge($b,$Ud))."\n)");return(!$b||queries("ALTER TABLE ".table($Q)."\n".implode("\n",$b)))&&(!$Mc||queries("ALTER TABLE ".table($Q)." DROP (".implode(", ",$Mc).")"))&&($Q==$B||queries("ALTER TABLE ".table($Q)." RENAME TO ".table($B)));}function
alter_indexes($Q,$b){$Mc=array();$jj=array();foreach($b
as$W){if($W[0]!="INDEX"){$W[2]=preg_replace('~ DESC$~','',$W[2]);$h=($W[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($W[1]):"\nADD".($W[1]!=""?" CONSTRAINT ".idf_escape($W[1]):"")." $W[0] ".($W[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$W[2]).")");array_unshift($jj,"ALTER TABLE ".table($Q).$h);}elseif($W[2]=="DROP")$Mc[]=idf_escape($W[1]);else$jj[]="CREATE INDEX ".idf_escape($W[1]!=""?$W[1]:uniqid($Q."_"))." ON ".table($Q)." (".implode(", ",$W[2]).")";}if($Mc)array_unshift($jj,"DROP INDEX ".implode(", ",$Mc));foreach($jj
as$F){if(!queries($F))return
false;}return
true;}function
foreign_keys($Q){$H=array();$F="SELECT c_list.CONSTRAINT_NAME as NAME,
c_src.COLUMN_NAME as SRC_COLUMN,
c_dest.OWNER as DEST_DB,
c_dest.TABLE_NAME as DEST_TABLE,
c_dest.COLUMN_NAME as DEST_COLUMN,
c_list.DELETE_RULE as ON_DELETE
FROM ALL_CONSTRAINTS c_list, ALL_CONS_COLUMNS c_src, ALL_CONS_COLUMNS c_dest
WHERE c_list.CONSTRAINT_NAME = c_src.CONSTRAINT_NAME
AND c_list.R_CONSTRAINT_NAME = c_dest.CONSTRAINT_NAME
AND c_list.CONSTRAINT_TYPE = 'R'
AND c_src.TABLE_NAME = ".q($Q);foreach(get_rows($F)as$I)$H[$I['NAME']]=array("db"=>$I['DEST_DB'],"table"=>$I['DEST_TABLE'],"source"=>array($I['SRC_COLUMN']),"target"=>array($I['DEST_COLUMN']),"on_delete"=>$I['ON_DELETE'],"on_update"=>null,);return$H;}function
truncate_tables(array$S){return
apply_queries("TRUNCATE TABLE",$S);}function
drop_views(array$Im){return
apply_queries("DROP VIEW",$Im);}function
drop_tables(array$S){return
apply_queries("DROP TABLE",$S);}function
last_id($G){return"0";}function
schemas(){$H=get_vals("SELECT DISTINCT owner FROM dba_segments WHERE owner IN (SELECT username FROM dba_users WHERE default_tablespace NOT IN ('SYSTEM','SYSAUX')) ORDER BY 1");return($H?:get_vals("SELECT DISTINCT owner FROM all_tables WHERE tablespace_name = ".q(DB)." ORDER BY 1"));}function
get_schema(){return
get_val("SELECT sys_context('USERENV', 'SESSION_USER') FROM dual");}function
set_schema($K,$g=null){return!!connection($g)->query("ALTER SESSION SET CURRENT_SCHEMA = ".idf_escape($K));}function
show_variables(){return
get_rows('SELECT name, display_value FROM v$parameter');}function
show_status(){$H=array();$J=get_rows('SELECT * FROM v$instance');foreach(reset($J)as$x=>$W)$H[]=array($x,$W);return$H;}function
process_list(){return
get_rows('SELECT
	sess.process AS "process",
	sess.username AS "user",
	sess.schemaname AS "schema",
	sess.status AS "status",
	sess.wait_class AS "wait_class",
	sess.seconds_in_wait AS "seconds_in_wait",
	sql.sql_text AS "sql_text",
	sess.machine AS "machine",
	sess.port AS "port"
FROM v$session sess LEFT OUTER JOIN v$sql sql
ON sql.sql_id = sess.sql_id
WHERE sess.type = \'USER\'
ORDER BY PROCESS
');}function
convert_field(array$m){}function
unconvert_field(array$m,$H){return$H;}function
support($Ad){return
preg_match('~^(columns|database|drop_col|fast_status|indexes|descidx|processlist|scheme|sql|status|table|variables|view)$~',$Ad);}}class
Adminer{static$instance;var$error='';function
name(){return"<a href='https://www.adminer.org/'".target_blank()." id='h1'><img src='".h(preg_replace("~\\?.*~","",ME)."?file=logo.png&version=6.0.2")."' width='24' height='24' alt='' id='logo'>Adminer</a>";}function
credentials(){return
array(SERVER,$_GET["username"],get_password());}function
connectSsl(){}function
permanentLogin($h=false){return
password_file($h);}function
bruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
serverName($M){return
h($M);}function
database(){return
DB;}function
databases($Sd=true){return
get_databases($Sd);}function
pluginsLinks(){}function
operators($al=null){return
driver()->operators($al);}function
schemas(){$H=schemas();if($_GET["ns"]!=""&&!in_array($_GET["ns"],$H))array_unshift($H,$_GET["ns"]);return$H;}function
queryTimeout(){return
2;}function
afterConnect(){}function
headers(){}function
csp(array$Zb){return$Zb;}function
verifyVersion(){return
true;}function
serviceWorker(){service_worker();}function
head($ec=null){return
true;}function
bodyClass(){echo" adminer";}function
css(){$H=array();foreach(array("","-dark")as$Ug){$o="adminer$Ug.css";if(file_exists($o)){$Gd=file_get_contents($o);$H["$o?v=".crc32($Gd)]=($Ug?"dark":(preg_match('~prefers-color-scheme:\s*dark~',$Gd)?'':'light'));}}return$H;}function
loginForm(){echo"<table class='layout'>\n",adminer()->loginFormField('driver','<tr><th>'.lang(37).'<td>',html_select("auth[driver]",SqlDriver::$drivers,DRIVER,on('change','loginDriver'))),adminer()->loginFormField('server','<tr><th>'.lang(38).'<td>',"<input name='auth[server]' value='".h(SERVER)."' title='".lang(39)."' placeholder='localhost' autocapitalize='off'>"),adminer()->loginFormField('username','<tr><th>'.lang(40).'<td>','<input name="auth[username]" id="username" autofocus value="'.h($_GET["username"]).'" autocomplete="username" autocapitalize="off">'.script("fire(qs('#username').form['auth[driver]'], 'change');")),adminer()->loginFormField('password','<tr><th>'.lang(41).'<td>','<input type="password" name="auth[password]" autocomplete="current-password">'),adminer()->loginFormField('db','<tr><th>'.lang(42).'<td>','<input name="auth[db]" value="'.h($_GET["db"]).'" autocapitalize="off">'),"</table>\n","<p><input type='submit' value='".lang(43)."'>\n",checkbox("auth[permanent]",1,$_COOKIE["adminer_permanent"],lang(44))."\n";}function
loginFormField($B,$Ge,$X){return$Ge.$X."\n";}function
login($mg,$E){if($E=="")return
lang(45).require_password_link(null);if(!Driver::$passwords)return
lang(46).require_password_link($E);if(!password_required())return
lang(47).require_password_link($E);return
true;}function
tableName(array$al){return
h($al["Name"]);}function
fieldName(array$m,$Uh=0){$T=$m["full_type"].($m["null"]?" NULL":"");$Cb=$m["comment"];return'<span title="'.h($T.($Cb!=""?($T?": ":"").$Cb:'')).'">'.h($m["field"]).'</span>';}function
commentValue($T,$Cb){if($Cb==""||$T=='TABLE'||$T=='COLUMN')return
h($Cb);$Ti=function($Oj){return
preg_replace('~^~m','<tr>',preg_replace('~\|~','<td>',preg_replace('~\|$~m',"",rtrim($Oj))));};$Q='(\+--[-+]+\+\n)';$I='(\| .* \|\n)';return"<pre>\n".preg_replace_callback("~^$Q?$I$Q?($I*)$Q?~m",function($A)use($Ti){$Nd=$Ti($A[2]);return"<table>\n".($A[1]?"<thead>$Nd<tbody>\n":$Nd).$Ti($A[4])."\n</table>";},preg_replace('~(\n(    -|mysql)&gt; )(.+)~',"\\1<code class='jush-sql'>\\3</code>",preg_replace('~(.+)\n---+\n~',"<b>\\1</b>\n",h($Cb))))."</pre>\n";}function
commentInput($T,$c,$Cb){$X=h($Cb);return(preg_match('~\n~',$X)?"<textarea$c rows='2' cols='".($T=='TABLE'?20:30)."' style='vertical-align: bottom;'>\n$X</textarea>":"<input$c value='$X'>");}function
selectLinks(array$al,$N=""){$B=$al["Name"];echo'<p class="links">';$hg=array();if($B!="")$hg["select"]=lang(48);if(support("table")||support("indexes"))$hg["table"]=lang(49);$Df=false;if(support("table")){$Df=is_view($al);if($Df){if(support("view"))$hg["view"]=lang(50);}elseif(function_exists('Adminer\alter_table')&&$B!="")$hg["create"]=lang(51);}if($N!==null)$hg["edit"]=lang(52);foreach($hg
as$x=>$W)echo" <a href='".h(ME)."$x=".url_escape($B).($x=="edit"?$N:"")."'".bold(isset($_GET[$x])).">$W</a>";echo
doc_link(array(JUSH=>driver()->tableHelp($B,$Df)),"?"),"\n";}function
foreignKeys($Q){return
foreign_keys($Q);}function
backwardKeys($Q,$Zk){return
array();}function
backwardKeysPrint(array$Oa,array$I){}function
selectQuery($F,$Nk,$zd=false){$H="\n";if(!$zd&&($Mm=driver()->warnings())){$t="warnings";$H=", <a href='#$t' class='toggle'>".lang(53)."</a>"."$H<div id='$t' class='hidden'>\n$Mm</div>\n";}return"<p><code class='jush-".JUSH."'>".h(str_replace("\n"," ",$F))."</code> <span class='time'>(".format_time($Nk).")</span>".(support("sql")?" <a href='".h(ME)."sql=".url_escape($F)."' class='hover'>".lang(13)."</a>":"").$H;}function
sqlCommandQuery($F){return
shorten_utf8(trim($F),1000);}function
sqlPrintAfter(){}function
rowDescription($Q){return"";}function
rowDescriptions(array$J,array$Vd){return$J;}function
selectLink($W,array$m){}function
selectVal($W,$_,array$m,$ei){$H=($W===null?"<i>NULL</i>":(preg_match("~char|binary|boolean~",$m["type"])&&!preg_match("~var~",$m["type"])?"<code>$W</code>":(preg_match('~^jsonb?$~',$m["full_type"])?"<code class='jush-json'>$W</code>":$W)));if(is_blob($m)&&!is_utf8($W))$H="<i>".lang(54,strlen($ei))."</i>";return($_?"<a href='".h($_)."'".(is_url($_)?target_blank():"").">$H</a>":$H);}function
editVal($W,array$m){return$W;}function
config(){return
array();}function
tableStructurePrint(array$n,$al=null){echo"<div class='scrollable'>\n","<table class='nowrap odds'>\n","<thead><tr><th>".lang(55)."<td>".lang(56).(support("comment")?"<td>".lang(57):"")."<tbody>\n";$Rk=driver()->structuredTypes();foreach($n
as$m){echo"<tr><th>".h($m["field"]);$T=h($m["full_type"]);$xb=h($m["collation"]);echo"<td><span title='$xb'>".(in_array($T,(array)$Rk[lang(7)])?"<a href='".h(ME.'type='.url_escape($T))."'>$T</a>":$T.($xb&&isset($al["Collation"])&&$xb!=$al["Collation"]?" $xb":""))."</span>",($m["null"]?" <i>NULL</i>":""),($m["auto_increment"]?" <i>".lang(58)."</i>":""),(isset($m["default"])?" <span title='".lang(59)."'>[<b>".($m["generated"]?"<code class='jush-".JUSH."'>".shorten_utf8(preg_replace('~\s+~',' ',ltrim($m["default"])),80,"</code>"):h($m["default"]))."</b>]</span>":""),(support("comment")?"<td>".adminer()->commentValue('COLUMN',$m["comment"]):""),"\n";}echo"</table>\n","</div>\n";}function
tableIndexesPrint(array$w,array$al){$ri=false;foreach($w
as$B=>$v)$ri|=!!$v["partial"];echo"<table>\n";$nc=first(driver()->indexAlgorithms($al));foreach($w
as$B=>$v){ksort($v["columns"]);$bj=array();foreach($v["columns"]as$x=>$W)$bj[]="<i>".h($W)."</i>".($v["lengths"][$x]?"(".h($v["lengths"][$x]).")":"").($v["descs"][$x]?" DESC":"");echo"<tr title='".h($B)."'>","<th>".h($v["type"]).($nc&&$v['algorithm']!=$nc?" (".h($v['algorithm']).")":""),"<td>".implode(", ",$bj);if($ri)echo"<td>".($v['partial']?"<code class='jush-".JUSH."'>WHERE ".h($v['partial']):"");echo"\n";}echo"</table>\n";}function
selectColumnsPrint(array$L,array$e){print_fieldset("select",lang(60),$L);$s=0;$L[""]=array();foreach($L
as$x=>$W){$W=idx($_GET["columns"],$x,array());$d=select_input(" name='columns[$s][col]' data-default=''".on('change',($x!==""?'selectFieldChange':'selectAddRow')),$e,$W["col"]);echo"<div>".(driver()->functions||driver()->grouping?html_select("columns[$s][fun]",array(-1=>"")+array_filter(array(lang(61)=>driver()->functions,lang(62)=>driver()->grouping)),$W["fun"]," data-default=''".on('change',($x!==""?'helpClose':'selectFunAddRow')).on_help_value(' (.*)|$','($1)'))."($d)":$d)."</div>\n";$s++;}echo"</div></fieldset>\n";}function
selectSearchPrint(array$Z,array$e,array$w,$al=null){print_fieldset("search",lang(63),$Z);foreach($w
as$s=>$v){if($v["type"]=="FULLTEXT")echo"<div>(<i>".implode("</i>, <i>",array_map('Adminer\h',$v["columns"]))."</i>) ".h(driver()->fulltextOperator)," <input type='search' name='fulltext[$s]' value='".h(idx($_GET["fulltext"],$s))."' data-default=''".on('input','selectFieldChange').">",(JUSH=='sql'?checkbox("boolean[$s]",1,isset($_GET["boolean"][$s]),"BOOL"):''),"</div>\n";}$Ph=adminer()->operators($al);foreach(array_merge((array)$_GET["where"],array(array()))as$s=>$W){if(!$W||("$W[col]$W[val]"!=""&&in_array($W["op"],$Ph)))echo"<div>".select_input(" name='where[$s][col]' data-default=''".on('change',($W?'selectFieldChange':'selectAddRow')),$e,$W["col"],"(".lang(64).")"),html_select("where[$s][op]",$Ph,$W["op"]," data-default='".h(first($Ph))."'".on('change','selectFirstChange')),"<input type='search' name='where[$s][val]' value='".h($W["val"])."' data-default=''".on('input','selectFirstChange').on('keydown','selectSearchKeydown').on('search','selectSearchSearch').">","</div>\n";}echo"</div></fieldset>\n";}function
selectOrderPrint(array$Uh,array$e,array$w){print_fieldset("sort",lang(65),$Uh);$s=0;foreach((array)$_GET["order"]as$x=>$W){if($W!=""){echo"<div>".select_input(" name='order[$s]' data-default=''".on('change','selectFieldChange'),$e,$W),checkbox("desc[$s]",1,isset($_GET["desc"][$x]),lang(66))."</div>\n";$s++;}}echo"<div>".select_input(" name='order[$s]' data-default=''".on('change','selectAddRow'),$e),checkbox("desc[$s]",1,false,lang(66))."</div>\n","</div></fieldset>\n";}function
selectLimitPrint($z){echo"<fieldset><legend>".lang(67)."</legend><div>","<input type='number' name='limit' class='size' value='".h($z?:"")."' data-default='50'".on('input','selectFieldChange').">","</div></fieldset>\n";}function
selectLengthPrint($xl){echo"<fieldset><legend>".lang(68)."</legend><div>","<input type='number' name='text_length' class='size' value='".h($xl)."' data-default='100'>","</div></fieldset>\n";}function
selectActionPrint(array$w){echo"<fieldset><legend>".lang(69)."</legend><div>","<input type='submit' value='".lang(60)."'>"," <span id='noindex' title='".lang(70)."'></span>","<script".nonce().">\n","const indexColumns = ";$e=array();foreach($w
as$v){$dc=reset($v["columns"]);if($v["type"]!="FULLTEXT"&&$dc)$e[$dc]=1;}$e[""]=1;foreach($e
as$x=>$W)json_row($x);echo";\n","selectFieldChange.call(qs('#form')['select']);\n","</script>\n","</div></fieldset>\n";}function
selectCommandPrint(){return!information_schema(DB);}function
selectImportPrint(){return!information_schema(DB);}function
selectEmailPrint(array$Xc,array$e){}function
selectColumnsProcess(array$e,array$w){$L=array();$r=array();foreach((array)$_GET["columns"]as$x=>$W){if($W["fun"]=="count"||($W["col"]!=""&&(!$W["fun"]||in_array($W["fun"],driver()->functions)||in_array($W["fun"],driver()->grouping)))){$L[$x]=apply_sql_function($W["fun"],($W["col"]!=""?idf_escape($W["col"]):"*"));if(!in_array($W["fun"],driver()->grouping))$r[]=$L[$x];}}return
array($L,$r);}function
selectSearchProcess(array$n,array$w,$al=null){$H=array();foreach($w
as$s=>$v){if($v["type"]=="FULLTEXT"&&idx($_GET["fulltext"],$s)!="")$H[]=driver()->fulltextSql($s,$v,$_GET["fulltext"][$s],isset($_GET["boolean"][$s]));}$Ph=adminer()->operators($al);foreach((array)$_GET["where"]as$x=>$W){$W+=array("col"=>"","op"=>first($Ph),"val"=>"");$_GET["where"][$x]=$W;$vb=$W["col"];if("$vb$W[val]"!=""&&in_array($W["op"],$Ph)){if($W["op"]=="SQL"&&(!$_POST||!verify_token()))SqlDb::$untrusted=true;$Hb=array();foreach(($vb!=""?array($vb=>$n[$vb]):$n)as$B=>$m){$Ui="";$Gb=" $W[op]";if(preg_match('~IN$~',$W["op"]))$Gb
.=" ".($W["val"]!=""?process_in($W["val"]):"(NULL)");elseif($W["op"]=="SQL")$Gb=" $W[val]";elseif(preg_match('~^(I?LIKE) %%$~',$W["op"],$A))$Gb=" $A[1] ".q("%$W[val]%");elseif($W["op"]=="FIND_IN_SET"){$Ui="$W[op](".q($W["val"]).", ";$Gb=")";}elseif(!preg_match('~NULL$~',$W["op"]))$Gb
.=" ".q($W["val"]);if($vb!=""||is_searchable($m,$W))$Hb[]=$Ui.driver()->convertSearch(idf_escape($B),$W,$m).$Gb;}$H[]=(count($Hb)==1?$Hb[0]:($Hb?"(".implode(" OR ",$Hb).")":"1 = 0"));}}return$H;}function
selectOrderProcess(array$n,array$w){$H=array();foreach((array)$_GET["order"]as$x=>$W){if($W!="")$H[]=(preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~',$W)?$W:idf_escape($W)).(isset($_GET["desc"][$x])?" DESC".(JUSH=='pgsql'&&idx($n[$W],"null")?" NULLS LAST":""):"");}return$H;}function
selectLimitProcess(){return(isset($_GET["limit"])?intval($_GET["limit"]):50);}function
selectLengthProcess(){return(isset($_GET["text_length"])?"$_GET[text_length]":"100");}function
selectEmailProcess(array$Z,array$Vd){return
false;}function
selectQueryBuild(array$L,array$Z,array$r,array$Uh,$z,$D){return"";}function
messageQuery($F,$zl,$zd=false){restart_session();$Ke=&get_session("queries");if(!idx($Ke,$_GET["db"]))$Ke[$_GET["db"]]=array();if(strlen($F)>1e6)$F=preg_replace('~[\x80-\xFF]+$~','',substr($F,0,1e6))."\n…";$Ke[$_GET["db"]][]=array($F,time(),$zl);$Ik="sql-".count($Ke[$_GET["db"]]);$H="<a href='#$Ik' class='toggle'>".lang(71)."</a> ".copy_icon()."\n";if(!$zd&&($Mm=driver()->warnings())){$t="warnings-".count($Ke[$_GET["db"]]);$H="<a href='#$t' class='toggle'>".lang(53)."</a>, $H<div id='$t' class='hidden'>\n$Mm</div>\n";}return" <span class='time'>".@date("H:i:s")."</span>"." $H<div id='$Ik' class='hidden'><pre><code class='jush-".JUSH."'>".shorten_utf8($F,1e4)."</code></pre>".($zl?" <span class='time'>($zl)</span>":'').(support("sql")?'<p><a href="'.h(str_replace("db=".url_escape(DB),"db=".url_escape($_GET["db"]),ME).'sql=&history='.(count($Ke[$_GET["db"]])-1)).'">'.lang(13).'</a>':'').'</div>';}function
error(){return
error();}function
editRowPrint($Q,array$n,$I,$mm,$F='',$zl=''){echo($F!=""?"<p><code class='jush-".JUSH."'>".h(str_replace("\n"," ",$F))."</code> <span class='time'>($zl)</span>\n":"");}function
editFunctions(array$m){$H=($m["null"]?"NULL/":"");$Ce=isset($_GET["select"])||where($_GET);foreach(array(driver()->insertFunctions,driver()->editFunctions)as$x=>$je){if(!$x||(!isset($_GET["call"])&&$Ce)){foreach($je
as$Ei=>$W){if(!$Ei||preg_match("~$Ei~",$m["type"]))$H
.="/$W";}}if($x&&$je&&!preg_match('~set|bool~',$m["type"])&&!is_blob($m))$H
.="/SQL";}if($m["auto_increment"]&&!$Ce)$H=lang(58);return
explode("/",$H);}function
editInput($Q,array$m,$c,$X){if($m["type"]=="enum")return(isset($_GET["select"])?"<label><input type='radio'$c value='orig' checked><i>".lang(11)."</i></label> ":"").enum_input("radio",$c,$m,$X,"NULL");return"";}function
editHint($Q,array$m,$X){return"";}function
processInput(array$m,$X,$q=""){if($q=="SQL")return$X;$B=$m["field"];$H=q($X);if(preg_match('~^(now|getdate|uuid)$~',$q))$H="$q()";elseif(preg_match('~^current_(date|timestamp)$~',$q))$H=$q;elseif(preg_match('~^([+-]|\|\|)$~',$q))$H=idf_escape($B)." $q $H";elseif(preg_match('~^[+-] interval$~',$q))$H=idf_escape($B)." $q ".(preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i",$X)&&JUSH!="pgsql"?$X:$H);elseif(preg_match('~^(addtime|subtime|concat)$~',$q))$H="$q(".idf_escape($B).", $H)";elseif(preg_match('~^(md5|sha1|password|encrypt)$~',$q))$H="$q($H)";return
unconvert_field($m,$H);}function
dumpOutput(){$H=array('text'=>lang(72),'file'=>lang(73));if(function_exists('gzencode'))$H['gz']='gzip';return$H;}function
dumpFormat(){return(support("dump")?array('sql'=>'SQL'):array())+array('csv'=>'CSV,','csv;'=>'CSV;','tsv'=>'TSV');}function
dumpPrint(){}function
dumpDatabase($j){}function
dumpTable($Q,$Sk,$Df=0){if($_POST["format"]!="sql"){echo"\xef\xbb\xbf";if($Sk)dump_csv(array_keys(fields($Q)));}else{if($Df==2){$n=array();foreach(fields($Q)as$B=>$m)$n[]=idf_escape($B)." $m[full_type]";$h="CREATE TABLE ".table($Q)." (".implode(", ",$n).")";}else$h=create_sql($Q,$_POST["auto_increment"],$Sk);set_utf8mb4($h);if($Sk&&$h){if(($Sk=="DROP+CREATE"&&!function_exists('Adminer\drop_sql'))||$Df==1)echo"DROP ".($Df==2?"VIEW":"TABLE")." IF EXISTS ".table($Q).";\n";if($Df==1)$h=remove_definer($h);echo"$h;\n\n";}}}function
dumpData($Q,$Sk,$F,array$L=array(),array$Z=array(),array$r=array(),array$Uh=array()){if($Sk){$xg=(JUSH=="sqlite"?0:1048576);$n=array();$Te=false;if($_POST["format"]=="sql"){if($Sk=="TRUNCATE+INSERT"&&!function_exists('Adminer\truncate_all_sql'))echo
truncate_sql($Q).";\n";$n=fields($Q);if(JUSH=="mssql"){foreach($n
as$m){if($m["auto_increment"]){echo"SET IDENTITY_INSERT ".table($Q)." ON;\n";$Te=true;break;}}}}$G=($F!=""?connection()->query($F,1):driver()->select($Q,($L?:array("*")),$Z,$r,$Uh,0));if($G){$pf="";$ab="";$Kf=array();$ke=array();$Uk="";$Bd=($Q!=''?'fetch_assoc':'fetch_row');$Vb=0;while($I=$G->$Bd()){if(!$Kf){$Y=array();foreach($I
as$W){$m=$G->fetch_field();if(idx($n[$m->name],'generated')){$ke[$m->name]=true;continue;}$Kf[]=$m->name;$x=idf_escape($m->name);$Y[]="$x = VALUES($x)";}$Uk=($Sk=="INSERT+UPDATE"?"\nON DUPLICATE KEY UPDATE ".implode(", ",$Y):"").";\n";}if($_POST["format"]!="sql"){if($Sk=="table"){dump_csv($Kf);$Sk="INSERT";}dump_csv($I);}else{if(!$pf)$pf="INSERT INTO ".table($Q)." (".implode(", ",array_map('Adminer\idf_escape',$Kf)).") VALUES";foreach($I
as$x=>$W){if($ke[$x]){unset($I[$x]);continue;}$m=$n[$x];$I[$x]=($W===null?"NULL":($W===false?0:unconvert_field($m,preg_match(number_type(),$m["type"])&&!preg_match('~\[~',$m["full_type"])&&is_numeric($W)?$W:(!is_blob($m)||is_utf8($W)?q($W):driver()->quoteBinary($W)))));}$Oj=($xg?"\n":" ")."(".implode(",\t",$I).")";if(!$ab)$ab=$pf.$Oj;elseif(JUSH=='mssql'?$Vb%1000!=0:strlen($ab)+4+strlen($Oj)+strlen($Uk)<$xg)$ab
.=",$Oj";else{echo$ab.$Uk;$ab=$pf.$Oj;}}$Vb++;}if($ab)echo$ab.$Uk;}elseif($_POST["format"]=="sql")echo"-- ".str_replace("\n"," ",connection()->error)."\n";if($Te)echo"SET IDENTITY_INSERT ".table($Q)." OFF;\n";}}function
dumpFilename($Re){return
friendly_url($Re!=""?$Re:(SERVER?:"localhost"));}function
dumpHeaders($Re,$Zg=false){$ii=$_POST["output"];$ud=(preg_match('~sql~',$_POST["format"])?"sql":($Zg?"tar":"csv"));header("Content-Type: ".($ii=="gz"?"application/x-gzip":($ud=="tar"?"application/x-tar":($ud=="sql"||$ii!="file"?"text/plain":"text/csv")."; charset=utf-8")));if($ii=="gz"){ob_start(function($P){return
gzencode($P);},1e6);}return$ud;}function
dumpFooter(){if($_POST["format"]=="sql")echo"-- ".gmdate("Y-m-d H:i:s e")."\n";}function
importServerPath(){return"adminer.sql";}function
importPrint(){}function
importProcess(){return
false;}function
homepage(){echo'<p class="links">'.($_GET["ns"]==""&&support("database")?'<a href="'.h(ME).'database=">'.lang(74)."</a>\n":""),(support("scheme")?"<a href='".h(ME)."scheme='>".($_GET["ns"]!=""?lang(75):lang(76))."</a>\n":""),($_GET["ns"]!==""?'<a href="'.h(ME).'schema=">'.lang(77)."</a>\n":""),(support("privileges")?"<a href='".h(ME)."privileges='>".lang(78)."</a>\n":"");if($_GET["ns"]!=="")echo(support("routine")?"<a href='#routines'>".lang(79)."</a>\n":""),(support("sequence")?"<a href='#sequences'>".lang(80)."</a>\n":""),(support("type")?"<a href='#user-types'>".lang(7)."</a>\n":""),(support("event")?"<a href='#events'>".lang(81)."</a>\n":"");return
true;}function
navigation($Tg){echo"<h1>".adminer()->name()." <span class='version'>".VERSION;$oh=$_COOKIE["adminer_version"];echo" <a href='https://www.adminer.org/#download'".target_blank()." id='version'>".(version_compare(VERSION,$oh)<0?h($oh):"").version_iframe()."</a>","</span></h1>\n";switch_lang();if($Tg=="auth"){$ii="";foreach((array)$_SESSION["pwds"]as$Fm=>$pk){foreach($pk
as$M=>$zm){$B=h(get_setting("vendor-$Fm-$M")?:get_driver($Fm));foreach($zm
as$U=>$E){if($B&&$E!==null){$lc=$_SESSION["db"][$Fm][$M][$U];foreach(($lc?array_keys($lc):array(""))as$j)$ii
.="<li><a href='".h(auth_url($Fm,$M,$U,$j))."'>($B) ".h("$U@").($M!=""?adminer()->serverName($M):"").h($j!=""?" - $j":"")."</a>\n";}}}}if($ii)echo"<ul id='logins'".on('mouseover','menuOver').on('mouseout','menuOut').">\n$ii</ul>\n";}else{$S=array();if($_GET["ns"]!==""&&!$Tg&&DB!=""){connection()->select_db(DB);$S=table_status('',true);}adminer()->syntaxHighlighting($S);adminer()->databasesPrint($Tg);$la=array();if(DB==""||!$Tg){if(support("sql")){$la['sql']="<a href='".h(ME)."sql='".bold(isset($_GET["sql"])&&!isset($_GET["import"])).">".lang(71)."</a>";$la['import']="<a href='".h(ME)."import='".bold(isset($_GET["import"])).">".lang(82)."</a>";}$la['dump']="<a href='".h(ME)."dump=".url_escape(isset($_GET["table"])?$_GET["table"]:$_GET["select"])."' id='dump'".bold(isset($_GET["dump"])).">".lang(83)."</a>";}$Ye=$_GET["ns"]!==""&&!$Tg&&DB!="";if($Ye&&function_exists('Adminer\alter_table'))$la['create']='<a href="'.h(ME).'create="'.bold($_GET["create"]==="").">".lang(84)."</a>";$la=adminer()->menuActions($la,$Tg);echo($la?"<p class='links'>\n".implode("\n",$la)."\n":"");if($Ye){if($S)adminer()->tablesPrint($S);else
echo"<p class='message'>".lang(12)."</p>\n";}}}function
syntaxHighlighting(array$S){echo
script_src(preg_replace("~\\?.*~","",ME)."?file=jush.js&version=6.0.2",true);$Vg=preg_replace('~<(?=/script)~i','<\\',Driver::jushModule());echo($Vg?script("addEventListener('DOMContentLoaded', () => {\n$Vg\n});"):"");if(support("sql")){echo"<script".nonce().">\n";if($S){$hg=array();foreach($S
as$Q=>$T)$hg[]=js_escape_re($Q);echo"var jushLinks = { ".JUSH.":";json_row(js_escape(ME).(support("table")?"table":"select").'=$&','/\b(?<!\$)('.implode('|',$hg).')(?!\$)\b/g',false);$Kk=array("sql","check","event","procedure","trigger","view","type","table","processlist");if(support("routine")&&array_intersect_key($_GET,array_flip($Kk))){foreach(routines()as$I)json_row(js_escape(ME).'function='.url_escape($I["SPECIFIC_NAME"]).'&name=$&','/\b'.js_escape_re($I["ROUTINE_NAME"]).'(?=["`\]]?\()/g',false);}json_row('');echo"};\n";foreach(array("bac","bra","sqlite_quo","mssql_bra")as$W)echo"jushLinks.$W = jushLinks.".JUSH.";\n";if(array_intersect_key($_GET,array_flip(array("sql","check","event","procedure","trigger","view")))){$Ok=(isset($_GET["trigger"])?array('INSERT INTO','UPDATE','DELETE FROM'):(isset($_GET["check"])?array():(isset($_GET["view"])?array('SELECT'):null)));$Ka=Driver::jushAutocomplete($S,$Ok);echo($Ka?"addEventListener('DOMContentLoaded', () => { autocompleter = $Ka; });\n":"");}}echo"</script>\n";}echo
script("syntaxHighlighting('".doc_version()."', '".connection()->flavor."');");}function
databasesPrint($Tg){if(support("single_db"))return;$i=adminer()->databases();if(DB&&$i&&!in_array(DB,$i))array_unshift($i,DB);echo"<form action=''>\n<p id='dbs'>\n";hidden_fields_get();$jc=on('mousedown','dbMouseDown').on('change','dbChange');echo"<label title='".lang(42)."'>".lang(85).": ".($i?html_select("db",array(""=>"")+$i,DB,$jc):"<input name='db' value='".h(DB)."' autocapitalize='off' size='19'>\n")."</label>","<input type='submit' value='".lang(24)."'".($i?" class='hidden'":"").">\n";if(support("scheme")){if($Tg!="db"&&DB!=""&&connection()->select_db(DB)){echo"<br><label>".lang(86).": ".html_select("ns",array(""=>"")+adminer()->schemas(),$_GET["ns"],$jc)."</label>";if($_GET["ns"]!="")set_schema($_GET["ns"]);}}foreach(array("import","sql","schema","dump","privileges")as$W){if(isset($_GET[$W])){echo
input_hidden($W);break;}}echo"</p></form>\n";}function
menuActions(array$la,$Tg){return$la;}function
tablesPrint(array$S){echo"<ul id='tables'".on('mouseover','menuOver').on('mouseout','menuOut').">";foreach($S
as$Q=>$O){$Q="$Q";$B=adminer()->tableName($O);if($B!=""&&!$O["dependent"])echo'<li><a href="'.h(ME).'select='.url_escape($Q).'"'.bold($_GET["select"]==$Q||$_GET["edit"]==$Q,"select hover")." title='".lang(48)."'>".lang(87)."</a> ",(support("table")||support("indexes")?'<a href="'.h(ME).'table='.url_escape($Q).'"'.bold(in_array($Q,array($_GET["table"],$_GET["create"],$_GET["indexes"],$_GET["foreign"],$_GET["trigger"],$_GET["check"],$_GET["view"])),(is_view($O)?"view":"structure"))." title='".lang(49)."'>$B</a>":"<span>$B</span>")."\n";}echo"</ul>\n";}function
showVariables(){return
show_variables();}function
showStatus(){return
show_status();}function
processList(){return
process_list();}function
killProcess($t){return
kill_process($t);}}class
Plugins{private
static$append=array('dumpFormat'=>true,'dumpOutput'=>true,'editRowPrint'=>true,'editFunctions'=>true,'config'=>true);var$plugins;var$drivers=array();var$driverFiles=array();var$error='';private$hooks=array();function
__construct($Li){$Lc=SqlDriver::$drivers;$Ie=" href='https://www.adminer.org/plugins/#use'".target_blank();if($Li===null){$Li=array();$Sa="adminer-plugins";if(is_dir($Sa)){foreach(glob("$Sa/*.php")as$o){$Hd=SqlDriver::$drivers;$this->includeOnce($o);foreach(array_diff_key(SqlDriver::$drivers,$Hd)as$t=>$B)$this->driverFiles[$t]=$o;}}if(file_exists("$Sa.php")){$af=$this->includeOnce("$Sa.php");if(is_array($af)){foreach($af
as$x=>$Ii)$Li[is_object($Ii)?get_class($Ii):$x]=$Ii;}else$this->error
.=lang(88,"<b>$Sa.php</b>",$Ie)."<br>";}foreach(get_declared_classes()as$sb){if(!$Li[$sb]&&(preg_match('~^Adminer\w~i',$sb)||is_subclass_of($sb,'Adminer\Plugin'))){$uj=new
\ReflectionClass($sb);$Nb=$uj->getConstructor();if($Nb&&$Nb->getNumberOfRequiredParameters())$this->error
.=lang(89,$Ie,"<b>$sb</b>","<b>$Sa.php</b>")."<br>";else$Li[$sb]=new$sb;}}}$uf=array_filter($Li,function($Ii){return!is_object($Ii);});if($uf){$this->error
.=lang(90,$Ie)."<br>";$Li=array_diff_key($Li,$uf);}$this->drivers=array_diff_key(SqlDriver::$drivers,$Lc);$this->plugins=$Li;$qa=new
Adminer;$Li[]=$qa;$uj=new
\ReflectionObject($qa);foreach($uj->getMethods()as$Qg){foreach($Li
as$Ii){$B=$Qg->getName();if(method_exists($Ii,$B))$this->hooks[$B][]=$Ii;}}}function
includeOnce($o){return
include_once"./$o";}static
function
checksum($o){$Gd=str_replace("\r","",file_get_contents($o));$Gd=preg_replace('~\n\tprotected \$translations = array\(.*?\n\t\);~s','',$Gd);return
dechex(crc32($Gd));}function
checksums(){$Id=array_values($this->driverFiles);foreach($this->plugins
as$Ii){$uj=new
\ReflectionObject($Ii);$Id[]=$uj->getFileName();}$H=array();foreach($Id
as$o)$H[basename($o,'.php')]=self::checksum($o);return$H;}static
function
officialChecksums(){return
array('adminer.js'=>'a0599090','backward-keys'=>'ed1ef78f','before-unload'=>'2a613523','config'=>'722eb4af','dark-switcher'=>'3d490dea','database-hide'=>'e304a899','designs'=>'ed7e44e3','dump-alter'=>'896b579e','dump-bz2'=>'f0d0e336','dump-date'=>'adc7f1c7','dump-json'=>'767dd321','dump-xml'=>'4fc3cd60','dump-zip'=>'93817d96','edit-foreign'=>'72ad1562','edit-textarea'=>'a24c3cc','editor-setup'=>'a7dc3a37','editor-views'=>'5c12b185','enum-option'=>'1e24970e','file-upload'=>'10add0e8','foreign-system'=>'ebb4c654','frames'=>'b0e1d11a','highlight-codemirror'=>'c5716555','highlight-monaco'=>'edd1b0af','highlight-prism'=>'267948e5','import-csv'=>'d429c77','login-ip'=>'4d174fea','login-otp'=>'5b5a68af','login-passkey'=>'f69f2f06','login-password-less'=>'e150daac','login-reverse-proxy'=>'24558ea2','login-servers'=>'19c42e45','login-ssl'=>'6ed147bc','login-table'=>'811f8cef','menu-links'=>'c78461b3','remote-color'=>'ddeecc48','row-numbers'=>'eec8698c','select-email'=>'f84fbd2c','select-image'=>'f55c0231','slugify'=>'dec64713','sql-gemini'=>'c60ab309','sql-log'=>'8e435000','table-indexes-structure'=>'a90cc0c9','table-structure'=>'a8458e02','tables-filter'=>'ec2bcd6e','timeout'=>'97321caf','version-github'=>'627cadf9','version-noverify'=>'966937e9','clickhouse'=>'c66e1af6','elastic'=>'da03fb2a','firebird'=>'2f32108a','igdb'=>'ac7fbeff','imap'=>'c9dd2dd6','mongo'=>'f33a5c03','redis'=>'8603c834','simpledb'=>'1ef5b158',);}function
__call($B,array$oi){$Ca=array();foreach($oi
as$x=>$W)$Ca[]=&$oi[$x];$H=null;foreach($this->hooks[$B]as$Ii){$X=call_user_func_array(array($Ii,$B),$Ca);if($X!==null){if(!self::$append[$B])return$X;$H=$X+(array)$H;}}return$H;}}abstract
class
Plugin{protected$translations=array();function
description(){return$this->lang('');}function
screenshot(){return"";}protected
function
lang($u,$uh=null){$Ca=func_get_args();$Ca[0]=idx($this->translations[LANG],$u)?:$u;return
call_user_func_array('Adminer\lang_format',$Ca);}}class
Password{private$password_hash;private$password_matches=null;function
__construct($Ai){$this->password_hash=$Ai;}function
description(){return
lang(91);}function
credentials(){$E=get_password();return
array(SERVER,$_GET["username"],($this->passwordMatches($E)&&!password_required()?"":$E));}function
login($mg,$E){if($this->passwordMatches($E))return
true;}protected
function
passwordMatches($E){if($this->password_matches===null)$this->password_matches=(function_exists('password_verify')&&password_verify(strval($E),$this->password_hash));return$this->password_matches;}}Adminer::$instance=(function_exists('adminer_object')?adminer_object():(is_dir("adminer-plugins")||file_exists("adminer-plugins.php")?new
Plugins(null):new
Adminer));SqlDriver::$drivers=array("server"=>"MySQL / MariaDB")+SqlDriver::$drivers;if(!defined('Adminer\DRIVER')){define('Adminer\DRIVER',"server");if(extension_loaded("mysqli")&&$_GET["ext"]!="pdo"){class
Db
extends
\mysqli{static$instance;var$extension="MySQLi",$flavor='';function
__construct(){parent::init();}function
attach(array$M,$U,$E){mysqli_report(MYSQLI_REPORT_OFF);$Mi=$M["port"];$Zc=("$M[host]$Mi$M[socket]"=="");$Mk=adminer()->connectSsl();$vm=($Mk&&($Mk['key']||$Mk['cert']||$Mk['ca']||isset($Mk['verify'])));if($vm)$this->ssl_set($Mk['key'],$Mk['cert'],$Mk['ca'],'','');$H=@$this->real_connect((!$Zc?$M["host"]:ini_get("mysqli.default_host")),(!$Zc||$U!=""?$U:ini_get("mysqli.default_user")),(!$Zc||$U.$E!=""?$E:ini_get("mysqli.default_pw")),null,($Mi!=""?intval($Mi):ini_get("mysqli.default_port")),($Mi!=""?null:$M["socket"]),($vm?($Mk['verify']!==false?MYSQLI_CLIENT_SSL:64):0));$this->options(MYSQLI_OPT_LOCAL_INFILE,0);return($H?'':$this->error);}function
set_charset($ib){if(parent::set_charset($ib))return
true;parent::set_charset('utf8');return$this->query("SET NAMES $ib");}function
next_result(){return
self::more_results()&&parent::next_result();}function
quote($P){return"'".$this->escape_string($P)."'";}function
inTransaction(){return
false;}}}elseif(extension_loaded("mysql")&&!((ini_bool("sql.safe_mode")||ini_bool("mysql.allow_local_infile"))&&extension_loaded("pdo_mysql"))){class
Db
extends
SqlDb{private$link;function
attach(array$M,$U,$E){if(ini_bool("mysql.allow_local_infile"))return
lang(92,"'mysql.allow_local_infile'","MySQLi","PDO_MySQL");$Mi="$M[port]$M[socket]";$B=$M["host"].($Mi!=""?":$Mi":"");$this->link=@mysql_connect(($B!=""?$B:ini_get("mysql.default_host")),($B.$U!=""?$U:ini_get("mysql.default_user")),($B.$U.$E!=""?$E:ini_get("mysql.default_password")),true,131072);if(!$this->link)return
mysql_error();$this->server_info=mysql_get_server_info($this->link);return'';}function
set_charset($ib){return
mysql_set_charset($ib,$this->link)||mysql_set_charset('utf8',$this->link);}function
quote($P){return"'".mysql_real_escape_string($P,$this->link)."'";}function
select_db($ic){return
mysql_select_db($ic,$this->link);}function
query($F,$cm=false){$G=@($cm?mysql_unbuffered_query($F,$this->link):mysql_query($F,$this->link));$this->error="";if(!$G){$this->errno=mysql_errno($this->link);$this->error=mysql_error($this->link);return
false;}if($G===true){$this->affected_rows=mysql_affected_rows($this->link);$this->info=mysql_info($this->link);return
true;}return
new
Result($G);}}class
Result{var$num_rows;private$result;private$offset=0;function
__construct($G){$this->result=$G;$this->num_rows=mysql_num_rows($G);}function
fetch_assoc(){return
mysql_fetch_assoc($this->result);}function
fetch_row(){return
mysql_fetch_row($this->result);}function
fetch_field(){$H=mysql_fetch_field($this->result,$this->offset++);$H->orgtable=$H->table;$H->charsetnr=($H->blob?63:0);return$H;}}}elseif(extension_loaded("pdo_mysql")){class
Db
extends
PdoDb{var$extension="PDO_MySQL";function
attach(array$M,$U,$E){$C=array(\PDO::MYSQL_ATTR_LOCAL_INFILE=>false);if(isset($_GET["select"]))$C[\PDO::MYSQL_ATTR_MULTI_STATEMENTS]=false;$Mk=adminer()->connectSsl();if($Mk){if($Mk['key'])$C[\PDO::MYSQL_ATTR_SSL_KEY]=$Mk['key'];if($Mk['cert'])$C[\PDO::MYSQL_ATTR_SSL_CERT]=$Mk['cert'];if($Mk['ca'])$C[\PDO::MYSQL_ATTR_SSL_CA]=$Mk['ca'];if(isset($Mk['verify']))$C[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=$Mk['verify'];}$Ne=$M["host"];$Mi=$M["port"];$zk=$M["socket"];return$this->dsn("mysql:charset=utf8".($Ne!=""?";host=$Ne":'').($Mi!=""?";port=$Mi":($zk!=""?";unix_socket=$zk":"")),$U,$E,$C);}function
set_charset($ib){return$this->query("SET NAMES $ib");}function
select_db($ic){return$this->query("USE ".idf_escape($ic));}function
query($F,$cm=false){$this->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,!$cm);return
parent::query($F,$cm);}}}class
Driver
extends
SqlDriver{static$extensions=array("MySQLi","MySQL","PDO_MySQL");static$jush="sql";static$serverSocket=true;var$unsigned=array("unsigned","zerofill","unsigned zerofill");var$functions=array("char_length","date","from_unixtime","lower","round","floor","ceil","sec_to_time","time_to_sec","upper");var$grouping=array("avg","count","count distinct","group_concat","max","min","sum");var$partitionBy=array("HASH","LINEAR HASH","KEY","LINEAR KEY","RANGE","LIST");function
operators($al){return
array("=","<",">","<=",">=","!=","LIKE","LIKE %%","REGEXP","IN","FIND_IN_SET","IS NULL","NOT LIKE","NOT REGEXP","NOT IN","IS NOT NULL","SQL");}static
function
connect($M,$U,$E){$f=parent::connect($M,$U,$E);if(is_string($f)){if(function_exists('iconv')&&!is_utf8($f)&&strlen($Oj=iconv("windows-1252","utf-8//IGNORE",$f))>strlen($f))$f=$Oj;return$f;}$f->set_charset(charset($f));$f->query("SET sql_quote_show_create = 1, autocommit = 1");$f->flavor=(preg_match('~MariaDB~',$f->server_info)?'maria':'mysql');add_driver(DRIVER,($f->flavor=='maria'?"MariaDB":"MySQL"));return$f;}function
__construct(Db$f){parent::__construct($f);$this->types=array(lang(28)=>array("tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21),lang(29)=>array("date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4),lang(30)=>array("char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295),lang(93)=>array("enum"=>65535,"set"=>64),lang(31)=>array("bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295),lang(33)=>array("geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0),);$this->insertFunctions=array("char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",);$this->editFunctions=array(number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",);if(min_version('5.7.8',10.2,$f))$this->types[lang(30)]["json"]=4294967295;if(min_version('',10.7,$f)){$this->types[lang(30)]["uuid"]=128;$this->insertFunctions['uuid']='uuid';}if(min_version('',10.5,$f)){$this->types[lang(32)]["inet6"]=39;if(min_version('','10.10',$f))$this->types[lang(32)]["inet4"]=15;}if(min_version(9,11.7,$f))$this->types[lang(28)]["vector"]=16383;if(min_version(5.7,10.2,$f))$this->generated=array("STORED","VIRTUAL");}function
unconvertFunction(array$m){return(preg_match("~binary~",$m["type"])?"<code class='jush-sql'>UNHEX</code>":($m["type"]=="bit"?doc_link(array('sql'=>'bit-value-literals.html'),"<code>b''</code>"):($m["type"]=="vector"?"<code class='jush-sql'>".($this->conn->flavor=='maria'?"VEC_FromText":"STRING_TO_VECTOR")."</code>":(preg_match("~geom|point|linestring|polygon~",$m["type"])?"<code class='jush-sql'>GeomFromText</code>":""))));}function
insert($Q,array$N){return($N?parent::insert($Q,$N):queries("INSERT INTO ".table($Q)." ()\nVALUES ()"));}function
insertUpdate($Q,array$J,array$Zi){$e=array_keys(reset($J));$Ui="INSERT INTO ".table($Q)." (".implode(", ",$e).") VALUES\n";$Y=array();foreach($e
as$x)$Y[$x]="$x = VALUES($x)";$Uk="\nON DUPLICATE KEY UPDATE ".implode(", ",$Y);$Y=array();$y=0;foreach($J
as$N){$X="(".implode(", ",$N).")";if($Y&&(strlen($Ui)+$y+strlen($X)+strlen($Uk)>1e6)){if(!queries($Ui.implode(",\n",$Y).$Uk))return
false;$Y=array();$y=0;}$Y[]=$X;$y+=strlen($X)+2;}return
queries($Ui.implode(",\n",$Y).$Uk);}function
slowQuery($F,$_l){if(min_version('5.7.8','10.1.2')){if($this->conn->flavor=='maria')return"SET STATEMENT max_statement_time=$_l FOR $F";elseif(preg_match('~^(SELECT\b)(.+)~is',$F,$A))return"$A[1] /*+ MAX_EXECUTION_TIME(".($_l*1000).") */ $A[2]";}}function
convertColumn($u,array$m){if(preg_match("~binary~",$m["type"]))return"HEX($u)";if($m["type"]=="bit")return"BIN($u + 0)";if($m["type"]=="vector")return($this->conn->flavor=='maria'?"VEC_ToText":"VECTOR_TO_STRING")."($u)";if(preg_match("~geom|point|linestring|polygon~",$m["type"]))return(min_version(8)?"ST_":"")."AsWKT($u)";return"";}function
convertSearch($u,array$W,array$m){return($this->convertColumn($u,$m)?:(preg_match('~'.text_type().'~',$m["type"])&&!preg_match("~^utf8~",$m["collation"])&&preg_match('~[\x80-\xFF]~',$W['val'])?"CONVERT($u USING ".charset($this->conn).")":$u));}function
typeName(\stdClass$m){$bm=array("decimal","tinyint","smallint","int","float","double",7=>"timestamp","bigint","mediumint","date","time","datetime","year",15=>"varchar","bit",242=>"vector",245=>"json","decimal","enum","set","tinytext","mediumtext","longtext","text","varchar","char","geometry",);$H=idx($bm,$m->type,"");return
parent::typeName($m)?:($m->charsetnr==63?str_replace(array("text","varchar","char"),array("blob","varbinary","binary"),$H):$H);}function
quoteBinary($Oj){return"X".q(bin2hex($Oj));}function
warnings(){$G=$this->conn->query("SHOW WARNINGS");if($G&&$G->num_rows){ob_start();print_select_result($G);return
ob_get_clean();}}function
tableHelp($B,$Df=false){$og=($this->conn->flavor=='maria');if(information_schema(DB))return
strtolower(str_replace("_","-",DB)."-".($og?"$B-table/":str_replace("_","-",$B)."-table.html"));if(DB=="sys")return($og?"sys-schema/":strtolower("sys-".str_replace("_","-",preg_replace('~^x\$~','',$B)).".html"));if(DB=="mysql")return($og?"mysql$B-table/":"system-schema.html");}function
partitionsInfo($Q){$de="FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = ".q(DB)." AND TABLE_NAME = ".q($Q);$G=$this->conn->query("SELECT PARTITION_METHOD, PARTITION_EXPRESSION, PARTITION_ORDINAL_POSITION $de ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1");$I=($G?$G->fetch_row():null);if(!$I)return
array();$H=array();list($H["partition_by"],$H["partition"],$H["partitions"])=$I;$xi=get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $de AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");$H["partition_names"]=array_keys($xi);$H["partition_values"]=array_values($xi);return$H;}function
checkConstraints($Q){$H=parent::checkConstraints($Q);return($this->conn->flavor=='maria'?$H:array_map('stripslashes',$H));}function
hasCStyleEscapes(){static$db;if($db===null){$Jk=get_val("SHOW VARIABLES LIKE 'sql_mode'",1,$this->conn);$db=(strpos($Jk,'NO_BACKSLASH_ESCAPES')===false);}return$db;}function
lineComment(){return"#|-- ";}function
engines(){$H=array();foreach(get_rows("SHOW ENGINES")as$I){if(preg_match("~YES|DEFAULT~",$I["Support"]))$H[]=$I["Engine"];}return$H;}function
indexAlgorithms(array$al){return(preg_match('~^(MEMORY|NDB)$~',$al["Engine"])?array("HASH","BTREE"):array());}}function
idf_escape($u){return"`".str_replace("`","``",$u)."`";}function
table($u){return
idf_escape($u);}function
get_databases($Sd){$H=get_session("dbs");if($H===null){$F="SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME";$Nk=microtime(true);$H=($Sd?slow_query($F):get_vals($F));if(microtime(true)-$Nk>0.1){restart_session();set_session("dbs",$H);stop_session();}}return$H;}function
limit($F,$Z,$z,$Ah=0,$dk=" "){return" $F$Z".($z?$dk."LIMIT $z".($Ah?" OFFSET $Ah":""):"");}function
limit1($Q,$F,$Z,$dk="\n"){return
limit($F,$Z,1,0,$dk);}function
db_collation($j,array$yb){$H=null;$h=get_val("SHOW CREATE DATABASE ".idf_escape($j),1);if(preg_match('~ COLLATE ([^ ]+)~',$h,$A))$H=$A[1];elseif(preg_match('~ CHARACTER SET ([^ ]+)~',$h,$A))$H=$yb[$A[1]][-1];return$H;}function
logged_user(){return
get_val("SELECT USER()");}function
tables_list(){return
get_key_vals("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");}function
count_tables(array$i){$H=array();foreach($i
as$j)$H[$j]=count(get_vals("SHOW TABLES IN ".idf_escape($j)));return$H;}function
table_status($B="",$_d=false){$H=array();$F="SELECT ENGINE AS Engine, TABLE_NAME AS Name, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ".($B!=""?"AND TABLE_NAME = ".q($B):"ORDER BY Name");$K=array();foreach(($_d?array():get_rows($F))as$I)$K[$I["Name"]]=$I;$Yi=null;foreach(get_rows($_d?$F:"SHOW TABLE STATUS".($B!=""?" LIKE ".q(addcslashes($B,"%_\\")):""))as$I){$ei=idx($K,$I["Name"]);if($ei){if($I["Comment"]!==$ei["Comment"]&&$I["Comment"]!==$Yi)$I["Error"]=$I["Comment"];$Yi=$I["Comment"];$I["Comment"]=$ei["Comment"];$I["Engine"]=$ei["Engine"];}if($I["Engine"]=="InnoDB")$I["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\1',$I["Comment"]);if(!isset($I["Engine"]))$I["Comment"]="";if($B!="")$I["Name"]=$B;$H[$I["Name"]]=$I;}return$H;}function
is_view(array$R){return$R["Engine"]===null;}function
fk_support(array$R){return
preg_match('~InnoDB|IBMDB2I'.(min_version(5.6)?'|NDB':'').'~i',$R["Engine"]);}function
parse_type($ge){preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~',$ge,$A);return
array($A[1],$A[2],ltrim($A[3].$A[4]));}function
fields($Q){$og=(connection()->flavor=='maria');$H=array();foreach(get_rows("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ".q($Q)." ORDER BY ORDINAL_POSITION")as$I){$m=$I["COLUMN_NAME"];$T=$I["COLUMN_TYPE"];$le=$I["GENERATION_EXPRESSION"];$xd=$I["EXTRA"];preg_match('~^(VIRTUAL|PERSISTENT|STORED)~',$xd,$ke);list($am,$y,$km)=parse_type($T);$k=$I["COLUMN_DEFAULT"];if($k!=""){$Cf=preg_match('~text|json~',$am);if(!$og&&$Cf)$k=preg_replace("~^(_\w+)?('.*')$~",'\2',stripslashes($k));if($og||$Cf){$k=($k=="NULL"?null:preg_replace_callback("~^'(.*)'$~",function($A){return
stripslashes(str_replace("''","'",$A[1]));},$k));}if(!$og&&preg_match('~binary~',$am)&&preg_match('~^0x(\w*)$~',$k,$A))$k=pack("H*",$A[1]);}$H[$m]=array("field"=>$m,"full_type"=>$T,"type"=>$am,"length"=>$y,"unsigned"=>$km,"default"=>($ke?($og?$le:stripslashes($le)):$k),"null"=>($I["IS_NULLABLE"]=="YES"),"auto_increment"=>($xd=="auto_increment"),"on_update"=>(preg_match('~\bon update (\w+)~i',$xd,$A)?$A[1]:""),"collation"=>$I["COLLATION_NAME"],"privileges"=>array_flip(explode(",","$I[PRIVILEGES],where,order")),"comment"=>$I["COLUMN_COMMENT"],"primary"=>($I["COLUMN_KEY"]=="PRI"),"generated"=>($ke[1]=="PERSISTENT"?"STORED":$ke[1]),);}return$H;}function
indexes($Q,$g=null){$H=array();foreach(get_rows("SHOW INDEX FROM ".table($Q),$g)as$I){$B=$I["Key_name"];$H[$B]["type"]=($B=="PRIMARY"?"PRIMARY":($I["Index_type"]=="FULLTEXT"?"FULLTEXT":($I["Non_unique"]?(preg_match('~^(SPATIAL|VECTOR)$~',$I["Index_type"])?$I["Index_type"]:"INDEX"):"UNIQUE")));$H[$B]["columns"][]=$I["Column_name"];$H[$B]["lengths"][]=($I["Index_type"]=="SPATIAL"?null:$I["Sub_part"]);$H[$B]["descs"][]=null;$H[$B]["algorithm"]=$I["Index_type"];}return$H;}function
foreign_keys($Q){static$Ei='(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';$H=array();$Wb=get_val("SHOW CREATE TABLE ".table($Q),1);if($Wb){preg_match_all("~CONSTRAINT ($Ei) FOREIGN KEY ?\\(((?:$Ei,? ?)+)\\) REFERENCES ($Ei)(?:\\.($Ei))? \\(((?:$Ei,? ?)+)\\)(?: ON DELETE (".driver()->onActions."))?(?: ON UPDATE (".driver()->onActions."))?~",$Wb,$rg,PREG_SET_ORDER);foreach($rg
as$A){preg_match_all("~$Ei~",$A[2],$Ck);preg_match_all("~$Ei~",$A[5],$ql);$H[idf_unescape($A[1])]=array("db"=>idf_unescape($A[4]!=""?$A[3]:$A[4]),"table"=>idf_unescape($A[4]!=""?$A[4]:$A[3]),"source"=>array_map('Adminer\idf_unescape',$Ck[0]),"target"=>array_map('Adminer\idf_unescape',$ql[0]),"on_delete"=>($A[6]?:"RESTRICT"),"on_update"=>($A[7]?:"RESTRICT"),);}}return$H;}function
view($B){return
array("select"=>preg_replace('~^(?:[^`]|`[^`]*`)*\s+AS\s+~isU','',get_val("SHOW CREATE VIEW ".table($B),1)));}function
collations(){$H=array();foreach(get_rows("SHOW COLLATION")as$I){if($I["Default"])$H[$I["Charset"]][-1]=$I["Collation"];else$H[$I["Charset"]][]=$I["Collation"];}ksort($H);foreach($H
as$x=>$W)sort($H[$x]);return$H;}function
information_schema($j,$K=""){return($j=="information_schema")||(min_version(5.5)&&$j=="performance_schema");}function
error(){return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",connection()->error));}function
create_database($j,$xb){return
queries("CREATE DATABASE ".idf_escape($j).($xb?" COLLATE ".q($xb):""));}function
drop_databases(array$i){$H=apply_queries("DROP DATABASE",$i,'Adminer\idf_escape');restart_session();set_session("dbs",null);return$H;}function
rename_database($B,$xb){$H=false;if(create_database($B,$xb)){$S=array();$Im=array();foreach(tables_list()as$Q=>$T){if($T=='VIEW')$Im[]=$Q;else$S[]=$Q;}$H=(!$S&&!$Im)||move_tables($S,$Im,$B);drop_databases($H?array(DB):array());}return$H;}function
auto_increment(){$Ja=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$v){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$v["columns"],true)){$Ja="";break;}if($v["type"]=="PRIMARY")$Ja=" UNIQUE";}}return" AUTO_INCREMENT$Ja";}function
alter_table($Q,$B,array$n,array$Ud,$Cb,$ad,$xb,$Ia,$wi){$b=array();foreach($n
as$m){if($m[1]){$k=$m[1][3];if(preg_match('~ GENERATED~',$k)){$m[1][3]=(connection()->flavor=='maria'?"":$m[1][2]);$m[1][2]=$k;}$b[]=($Q!=""?($m[0]!=""?"CHANGE ".idf_escape($m[0]):"ADD"):" ")." ".implode($m[1]).($Q!=""?$m[2]:"");}else$b[]="DROP ".idf_escape($m[0]);}$b=array_merge($b,$Ud);$O=($Cb!==null?" COMMENT=".q($Cb):"").($ad?" ENGINE=".q($ad):"").($xb?" COLLATE ".q($xb):"").($Ia!=""?" AUTO_INCREMENT=$Ia":"");if($wi){$xi=array();if($wi["partition_by"]=='RANGE'||$wi["partition_by"]=='LIST'){foreach($wi["partition_names"]as$x=>$W){$X=$wi["partition_values"][$x];$xi[]="\n  PARTITION ".idf_escape($W)." VALUES ".($wi["partition_by"]=='RANGE'?"LESS THAN":"IN").($X!=""?" ($X)":" MAXVALUE");}}$O
.="\nPARTITION BY $wi[partition_by]($wi[partition])";if($xi)$O
.=" (".implode(",",$xi)."\n)";elseif($wi["partitions"])$O
.=" PARTITIONS ".(+$wi["partitions"]);}elseif($wi===null)$O
.="\nREMOVE PARTITIONING";if($Q=="")return
queries("CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)$O");if($Q!=$B)$b[]="RENAME TO ".table($B);if($O)$b[]=ltrim($O);return($b?queries("ALTER TABLE ".table($Q)."\n".implode(",\n",$b)):true);}function
alter_indexes($Q,$b){$gb=array();foreach($b
as$W)$gb[]=($W[2]=="DROP"?"\nDROP INDEX ".idf_escape($W[1]):"\nADD $W[0] ".($W[0]=="PRIMARY"?"KEY ":"").($W[1]!=""?idf_escape($W[1])." ":"")."(".implode(", ",$W[2]).")");return
queries("ALTER TABLE ".table($Q).implode(",",$gb));}function
truncate_tables(array$S){return
apply_queries("TRUNCATE TABLE",$S);}function
drop_views(array$Im){return
queries("DROP VIEW ".implode(", ",array_map('Adminer\table',$Im)));}function
drop_tables(array$S){return
queries("DROP TABLE ".implode(", ",array_map('Adminer\table',$S)));}function
move_tables(array$S,array$Im,$ql){$zj=array();foreach($S
as$Q)$zj[]=table($Q)." TO ".idf_escape($ql).".".table($Q);if(!$zj||queries("RENAME TABLE ".implode(", ",$zj))){$sc=array();foreach($Im
as$Q)$sc[table($Q)]=view($Q);connection()->select_db($ql);$j=idf_escape(DB);foreach($sc
as$B=>$Hm){if(!queries("CREATE VIEW $B AS ".str_replace(" $j."," ",$Hm["select"]))||!queries("DROP VIEW $j.$B"))return
false;}return
true;}return
false;}function
copy_tables(array$S,array$Im,$ql){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($S
as$Q){$B=($ql==DB?table("copy_$Q"):idf_escape($ql).".".table($Q));if(($_POST["overwrite"]&&!queries("\nDROP TABLE IF EXISTS $B"))||!queries("CREATE TABLE $B LIKE ".table($Q))||!queries("INSERT INTO $B SELECT * FROM ".table($Q)))return
false;foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($Q,"%_\\")))as$I){$Rl=$I["Trigger"];list($jd,$xh)=trigger_event($I);if(!queries("CREATE TRIGGER ".($ql==DB?idf_escape("copy_$Rl"):idf_escape($ql).".".idf_escape($Rl))." $I[Timing] $jd".($xh!=""?" $xh":"")." ON $B FOR EACH ROW\n$I[Statement];"))return
false;}}foreach($Im
as$Q){$B=($ql==DB?table("copy_$Q"):idf_escape($ql).".".table($Q));$Hm=view($Q);if(($_POST["overwrite"]&&!queries("DROP VIEW IF EXISTS $B"))||!queries("CREATE VIEW $B AS $Hm[select]"))return
false;}return
true;}function
trigger_event(array$I){$ld=explode(",",$I["Event"]);$H=array();foreach(array("DELETE","INSERT","UPDATE")as$jd){if(in_array($jd,$ld))$H[]=$jd;}$H=implode(" OR ",$H);if(in_array("UPDATE",$ld)&&min_version('','12.0.1')&&preg_match('~\s(?:BEFORE|AFTER)\s+(.+?)\s+ON\s~is',get_val("SHOW CREATE TRIGGER ".idf_escape($I["Trigger"]),2),$A)&&preg_match('~\bOF\s+(.+)~is',$A[1],$xh))return
array("$H OF",$xh[1]);return
array($H,"");}function
trigger($B,$Q){if($B=="")return
array();$J=get_rows("SHOW TRIGGERS WHERE `Trigger` = ".q($B));$H=reset($J);if($H)list($H["Event"],$H["Of"])=trigger_event($H);return$H;}function
triggers($Q){$H=array();foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($Q,"%_\\")))as$I){list($jd)=trigger_event($I);$H[$I["Trigger"]]=array($I["Timing"],$jd);}return$H;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>(min_version('','12.0.1')?array("INSERT","UPDATE","UPDATE OF","DELETE","INSERT OR UPDATE","INSERT OR UPDATE OF","DELETE OR INSERT","DELETE OR UPDATE","DELETE OR UPDATE OF","DELETE OR INSERT OR UPDATE","DELETE OR INSERT OR UPDATE OF",):array("INSERT","UPDATE","DELETE")),"Type"=>array("FOR EACH ROW"),);}function
routine($B,$T){$J=get_rows("SELECT PARAMETER_NAME, DTD_IDENTIFIER, PARAMETER_MODE, COLLATION_NAME
FROM information_schema.PARAMETERS
WHERE SPECIFIC_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$T' AND SPECIFIC_NAME = ".q($B)."
ORDER BY ORDINAL_POSITION");$n=array();foreach($J
as$I){$ge=$I["DTD_IDENTIFIER"];list($am,$y,$km)=parse_type($ge);$n[]=array("field"=>$I["PARAMETER_NAME"],"type"=>$am,"length"=>$y,"unsigned"=>$km,"null"=>true,"full_type"=>$ge,"inout"=>($T=="FUNCTION"?"":$I["PARAMETER_MODE"]),"collation"=>$I["COLLATION_NAME"],);}$H=(array)connection()->query("SELECT
	ROUTINE_COMMENT comment,
	ROUTINE_DEFINITION definition,
	LOWER(EXTERNAL_LANGUAGE) language,
	IF(DEFINER = CURRENT_USER(), '', DEFINER) definer,
	IF(IS_DETERMINISTIC = 'YES', 'DETERMINISTIC', 'NOT DETERMINISTIC') is_deterministic,
	SQL_DATA_ACCESS data_access,
	CONCAT('SQL SECURITY ', SECURITY_TYPE) security
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$T' AND ROUTINE_NAME = ".q($B))->fetch_assoc();$H['options']=array("DEFINER"=>$H['definer'],"DETERMINISTIC"=>$H['is_deterministic'],"SQL_DATA_ACCESS"=>$H['data_access'],"SQL_SECURITY"=>$H['security'],"COMMENT"=>$H['comment'],);if($n&&$n[0]['field']=='')$H['returns']=array_shift($n);$H['fields']=$n;return$H;}function
routines(){return
get_rows("SELECT SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()");}function
routine_languages(){return(min_version(9,99)?array("sql"=>"sql","javascript"=>"js"):array());}function
routine_options($Ij){return
array("DEFINER"=>array(),"DETERMINISTIC"=>array("NOT DETERMINISTIC","DETERMINISTIC"),"SQL_DATA_ACCESS"=>array("CONTAINS SQL","NO SQL","READS SQL DATA","MODIFIES SQL DATA"),"SQL_SECURITY"=>array("SQL SECURITY DEFINER","SQL SECURITY INVOKER"),"COMMENT"=>array(),);}function
routine_id($B,array$I){return
idf_escape($B);}function
last_id($G){return
get_val("SELECT LAST_INSERT_ID()");}function
explain(Db$f,$F){return$f->query("EXPLAIN ".(min_version(5.7)?"":"PARTITIONS ").$F);}function
found_rows(array$R,array$Z){return($Z||$R["Engine"]!="InnoDB"?null:$R["Rows"]);}function
create_sql($Q,$Ia,$Sk){$H=get_val("SHOW CREATE TABLE ".table($Q),1);if(!$Ia)$H=preg_replace('~(\n\)[^\n]*?) AUTO_INCREMENT=\d+~','\1',$H);return$H;}function
truncate_sql($Q){return"TRUNCATE ".table($Q);}function
use_sql($ic,$Sk=""){$B=idf_escape($ic);$H="";if(preg_match('~CREATE~',$Sk)&&($h=get_val("SHOW CREATE DATABASE $B",1))){set_utf8mb4($h);if($Sk=="DROP+CREATE")$H="DROP DATABASE IF EXISTS $B;\n";$H
.="$h;\n";}return$H."USE $B";}function
trigger_sql($Q){$H="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($Q,"%_\\")),null,"-- ")as$I){list($I["Event"],$I["Of"])=trigger_event($I);$H
.="\n".create_trigger(" ON ".table($I["Table"]),$I+array("Type"=>"FOR EACH ROW")).";\n";}return$H;}function
show_variables(){return
get_rows("SHOW VARIABLES");}function
show_status(){return
get_rows("SHOW STATUS");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
convert_field(array$m){return
driver()->convertColumn(idf_escape($m["field"]),$m);}function
unconvert_field(array$m,$H){if(preg_match("~binary~",$m["type"]))$H="UNHEX($H)";if($m["type"]=="bit")$H="CONVERT(b$H, UNSIGNED)";if($m["type"]=="vector")$H=(connection()->flavor=='maria'?"VEC_FromText":"STRING_TO_VECTOR")."($H)";if(preg_match("~geom|point|linestring|polygon~",$m["type"])){$Ui=(min_version(8)?"ST_":"");$H=$Ui."GeomFromText($H, $Ui"."SRID($m[field]))";}return$H;}function
support($Ad){return
preg_match('~^(comment|columns|copy|database|drop_col|dump|event|indexes|kill|privileges|move_col|procedure|processlist|routine|sql|status|table|trigger|variables|view'.(min_version(8)?'|descidx':'').(min_version('8.0.16','10.2.1')?'|check':'').(min_version(8,99)?'|fast_status':'').')$~',$Ad);}function
kill_process($t){return
queries("KILL ".number($t));}function
connection_id(){return"SELECT CONNECTION_ID()";}function
max_connections(){return
get_val("SELECT @@max_connections");}function
types($wd=false){return
array();}function
type_values($t){return"";}function
type_definition($t){return
array("kind"=>"","definition"=>"");}function
schemas(){return
array();}function
get_schema(){return"";}function
set_schema($K,$g=null){return
true;}}define('Adminer\JUSH',Driver::$jush);define('Adminer\SERVER',"".$_GET[DRIVER]);define('Adminer\DB',"$_GET[db]");define('Adminer\ME',preg_replace('~\?.*~','',relative_uri()).'?'.(sid()?SID.'&':'').($_GET["ext"]?"ext=".url_escape($_GET["ext"]).'&':'').(isset($_GET[DRIVER])?DRIVER."=".url_escape(SERVER).'&':'').(isset($_GET["username"])?"username=".url_escape($_GET["username"]).'&':'').(isset($_GET["db"])?'db='.url_escape(DB).'&'.(isset($_GET["ns"])?"ns=".url_escape($_GET["ns"])."&":""):''));function
page_header($Bl,$l="",$Za=array(),$Cl=""){page_headers();if(is_ajax()&&$l){page_messages($l);exit;}if(!ob_get_level())ob_start('ob_gzhandler',4096);$Dl=$Bl.($Cl!=""?": $Cl":"");$El=strip_tags($Dl.(SERVER!=""&&SERVER!="localhost"?h(" - ".SERVER):"")." - ".adminer()->name());echo'<!DOCTYPE html>
<html lang=\'',LANG,'\' dir=\'',lang(94),'\' class=\'',lang(94),' nojs\'>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>',$El,'</title>
<link rel="stylesheet" href="',h(preg_replace("~\\?.*~","",ME)."?file=default.css&version=6.0.2"),'">
';$ac=adminer()->css();if(is_int(key($ac)))$ac=array_fill_keys($ac,'light');$_e=in_array('light',$ac)||in_array('',$ac);$ye=in_array('dark',$ac)||in_array('',$ac);$ec=($_e?($ye?null:false):($ye?:null));$Fg=" media='(prefers-color-scheme: dark)'";if($ec!==false)echo"<link rel='stylesheet'".($ec?"":$Fg)." href='".h(preg_replace("~\\?.*~","",ME)."?file=dark.css&version=6.0.2")."'>\n";echo"<meta name='color-scheme' content='".($ec===null?"light dark":($ec?"dark":"light"))."'>\n",script_src(preg_replace("~\\?.*~","",ME)."?file=functions.js&version=6.0.2");if(adminer()->head($ec))echo"<link rel='icon' href='data:image/gif;base64,"."R0lGODlhEAAQAJEAAAQCBPz+/PwCBAROZCH5BAEAAAAALAAAAAAQABAAAAI2hI+pGO1rmghihiUdvUBnZ3XBQA7f05mOak1RWXrNq5nQWHMKvuoJ37BhVEEfYxQzHjWQ5qIAADs='>\n","<link rel='apple-touch-icon' href='".h(preg_replace("~\\?.*~","",ME)."?file=logo.png&version=6.0.2")."'>\n";foreach($ac
as$qm=>$Ug){$c=($Ug=='dark'&&!$ec?$Fg:($Ug=='light'&&$ye?" media='(prefers-color-scheme: light)'":""));echo"<link rel='stylesheet'$c href='".h($qm)."'>\n";}echo"\n<body class='";adminer()->bodyClass();echo"'>\n",script((isset($_COOKIE["adminer_version"])||!adminer()->verifyVersion()?"":"onload = partial(verifyVersion, '".VERSION."');\n")."
const offlineMessage = '".js_escape(lang(95))."';
const numberFormat = '".js_escape(lang(5))."';
const numberDigits = '".js_escape(lang(6))."';
const urlSeparators = '".js_escape(ini_get("arg_separator.input"))."';"),"<div id='help' class='jush-".JUSH." jsonly hidden'".on('mouseover','helpKeep').on('mouseout','helpMouseout')."></div>\n","<div id='content'>\n","<span id='menuopen' class='jsonly'".on('click','menuToggle')."><button title='".lang(96)."' class='icon icon-move' aria-expanded='false'></button></span>\n";if($Za!==null){$_=substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1);echo'<p id="breadcrumb"><a href="'.h($_?:".").'">'.get_driver(DRIVER).'</a> » ';$_=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);$M=adminer()->serverName(SERVER);$M=($M!=""?$M:lang(38));if($Za===false)echo"$M\n";else{echo"<a href='".h($_.(DB!=""&&support("single_db")?"&db=":""))."' accesskey='1' title='Alt+Shift+1'>$M</a> » ";if($_GET["ns"]!=""||(DB!=""&&is_array($Za)))echo'<a href="'.h($_."&db=".url_escape(DB).(support("scheme")?"&ns=":"").(support("single_table")?"&select=":"")).'">'.h(DB).'</a> » ';if(is_array($Za)){if($_GET["ns"]!="")echo'<a href="'.h(substr(ME,0,-1)).'">'.h($_GET["ns"]).'</a> » ';foreach($Za
as$x=>$W){$uc=(is_array($W)?$W[1]:h($W));if($uc!="")echo"<a href='".h(ME."$x=").url_escape(is_array($W)?$W[0]:$W)."'>$uc</a> » ";}}echo"$Bl\n";}}echo"<h2>$Dl</h2>\n","<div id='ajaxstatus' role='status' class='jsonly'></div>\n";restart_session();page_messages($l);adminer()->serviceWorker();$i=&get_session("dbs");if(DB!=""&&$i&&!in_array(DB,$i,true))$i=null;stop_session();define('Adminer\PAGE_HEADER',1);ob_flush();flush();}function
service_worker(){$ub=(has_passwords()?"navigator.serviceWorker.register('".js_escape(preg_replace('~\?.*~','',ME)."?file=worker.js&version=".VERSION)."', {scope: location.pathname}).catch(() => {});":"navigator.serviceWorker.getRegistration().then(registration => registration && registration.unregister());
	caches.keys().then(keys => keys.forEach(key => key.startsWith('adminer-') && caches.delete(key)));");echo
script("if (navigator.serviceWorker) {\n\t$ub\n}");}function
has_passwords(){foreach((array)$_SESSION["pwds"]as$pk){foreach($pk
as$zm){foreach($zm
as$E){if($E!==null)return
true;}}}return
false;}function
page_headers(){header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-Frame-Options: deny");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");foreach(adminer()->csp(csp())as$Zb){$Ee=array();foreach($Zb
as$x=>$W)$Ee[]="$x $W";header("Content-Security-Policy: ".implode("; ",$Ee));}adminer()->headers();}function
csp(){return
array(array("script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self' https://www.adminer.org","frame-src"=>"https://www.adminer.org","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",),);}function
design_checksums(){$wm=array();foreach(array_keys(adminer()->css())as$qm)$wm[preg_replace('~\?.*~','',$qm)]=true;$H=array();foreach(array("adminer.css","adminer-dark.css")as$o){if($wm[$o]&&file_exists($o)){preg_match('~^/\* Adminer design ([-\w]+) \*/~',file_get_contents($o),$A);$H[$o]=array((string)$A[1],Plugins::checksum($o));}}return$H;}function
official_design_checksums(){return
array('adminer-border/adminer.css'=>'ec757f3e','adminer-dark/adminer-dark.css'=>'a26bcd7b','brade/adminer.css'=>'be4161f0','bueltge/adminer.css'=>'1a8f00b4','cpanel/adminer.css'=>'59ce604e','dracula/adminer-dark.css'=>'cfaf61dd','esterka/adminer.css'=>'1f805f36','flat/adminer.css'=>'49a61af9','galkaev/adminer-dark.css'=>'16c46f94','haeckel/adminer.css'=>'147a3565','hever/adminer.css'=>'ef0e1948','konya/adminer.css'=>'2b409696','lavender-light/adminer.css'=>'bf03f5d7','lucas-sandery/adminer.css'=>'6596353','mancave/adminer-dark.css'=>'e1ac813d','mvt/adminer.css'=>'ebd3afdc','nette/adminer.css'=>'5ab360e7','ng9/adminer.css'=>'488583cf','nicu/adminer.css'=>'ecb9bd1e','pappu687/adminer.css'=>'b58d128c','paranoiq/adminer.css'=>'64d27e5','pepa-linha/adminer.css'=>'baf25f0','pokorny/adminer.css'=>'ee9eea6d','price/adminer.css'=>'81be9a85','rmsoft/adminer.css'=>'6cd4a237','rmsoft_blue-dark/adminer.css'=>'32102a8','rmsoft_blue/adminer.css'=>'7d8d5b18','win98/adminer.css'=>'e82d63c3',);}function
version_iframe(){return(isset($_COOKIE["adminer_version"])||!adminer()->verifyVersion()?"":"<noscript><iframe sandbox src='https://www.adminer.org/version/?current=".VERSION."&amp;noscript=1'></iframe></noscript>");}function
get_nonce(){static$qh;if(!$qh)$qh=base64_encode(rand_string());return$qh;}function
page_messages($l){$pm=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$Mg=idx($_SESSION["messages"],$pm);if($Mg){echo"<div class='message'>".implode("</div>\n<div class='message'>",$Mg)."</div>".script("messagesPrint();");unset($_SESSION["messages"][$pm]);}if($l)echo"<div class='error'>$l</div>\n";if(adminer()->error)echo"<div class='error'>".adminer()->error."</div>\n";}function
page_footer($Tg=""){echo"</div>\n\n<div id='foot' class='foot'>\n<div id='menu'>\n";adminer()->navigation($Tg);echo"</div>\n";if($Tg!="auth")echo'<form action="" method="post">
<p class="logout">
<span title="',lang(40),'">',h($_GET["username"])."\n",'</span>
<input type=\'submit\' name=\'logout\' value=\'',lang(97),'\' id=\'logout\'>
',input_token(),'</form>
';echo"</div>\n\n",script("setupSubmitHighlight(document);");}function
int32($bh){while($bh>=2147483648)$bh-=4294967296;while($bh<=-2147483649)$bh+=4294967296;return(int)$bh;}function
long2str(array$V,$Km){$Oj='';foreach($V
as$W)$Oj
.=pack('V',$W);if($Km)return
substr($Oj,0,end($V));return$Oj;}function
str2long($Oj,$Km){$V=array_values(unpack('V*',str_pad($Oj,4*ceil(strlen($Oj)/4),"\0")));if($Km)$V[]=strlen($Oj);return$V;}function
xxtea_mx($Vm,$Um,$Vk,$If){return
int32((($Vm>>5&0x7FFFFFF)^$Um<<2)+(($Um>>3&0x1FFFFFFF)^$Vm<<4))^int32(($Vk^$Um)+($If^$Vm));}function
encrypt_string($Qk,$x){if($Qk=="")return"";$x=array_values(unpack("V*",pack("H*",md5($x))));$V=str2long($Qk,true);$bh=count($V)-1;$Vm=$V[$bh];$Um=$V[0];$ij=floor(6+52/($bh+1));$Vk=0;while($ij-->0){$Vk=int32($Vk+0x9E3779B9);$Sc=$Vk>>2&3;for($ki=0;$ki<$bh;$ki++){$Um=$V[$ki+1];$ah=xxtea_mx($Vm,$Um,$Vk,$x[$ki&3^$Sc]);$Vm=int32($V[$ki]+$ah);$V[$ki]=$Vm;}$Um=$V[0];$ah=xxtea_mx($Vm,$Um,$Vk,$x[$ki&3^$Sc]);$Vm=int32($V[$bh]+$ah);$V[$bh]=$Vm;}return
long2str($V,false);}function
decrypt_string($Qk,$x){if($Qk=="")return"";if(!$x)return
false;$x=array_values(unpack("V*",pack("H*",md5($x))));$V=str2long($Qk,false);$bh=count($V)-1;$Vm=$V[$bh];$Um=$V[0];$ij=floor(6+52/($bh+1));$Vk=int32($ij*0x9E3779B9);while($Vk){$Sc=$Vk>>2&3;for($ki=$bh;$ki>0;$ki--){$Vm=$V[$ki-1];$ah=xxtea_mx($Vm,$Um,$Vk,$x[$ki&3^$Sc]);$Um=int32($V[$ki]-$ah);$V[$ki]=$Um;}$Vm=$V[$bh];$ah=xxtea_mx($Vm,$Um,$Vk,$x[$ki&3^$Sc]);$Um=int32($V[0]-$ah);$V[0]=$Um;$Vk=int32($Vk-0x9E3779B9);}return
long2str($V,true);}$Gi=array();if($_COOKIE["adminer_permanent"]){foreach(explode(" ",$_COOKIE["adminer_permanent"])as$W){list($x)=explode(":",$W);$Gi[$x]=$W;}}function
add_invalid_login(){$Qa=get_temp_dir()."/adminer-invalid";foreach(glob("$Qa*")?:array($Qa)as$o){$ae=file_open_lock($o);if($ae)break;}if(!$ae)$ae=file_open_lock("$Qa-".rand_string());if(!$ae)return;$wf=json_decode(stream_get_contents($ae),true);$zl=time();if($wf){foreach($wf
as$xf=>$W){if($W[0]<$zl)unset($wf[$xf]);}}$uf=&$wf[adminer()->bruteForceKey()];if(!$uf)$uf=array($zl+30*60,0);$uf[1]++;file_write_unlock($ae,json_encode($wf));}function
check_invalid_login(array&$Gi){$wf=array();foreach(glob(get_temp_dir()."/adminer-invalid*")as$o){$ae=file_open_lock($o);if($ae){$wf=json_decode(stream_get_contents($ae),true);file_unlock($ae);break;}}$x=adminer()->bruteForceKey();$uf=idx($wf,$x,array());$ph=($uf[1]>29?$uf[0]-time():0);if($ph>0){$l=lang(98,ceil($ph/60));if($_SERVER["HTTP_X_FORWARDED_FOR"]!=""&&$x==$_SERVER["REMOTE_ADDR"])$l
.='<br>'.lang(99,'<b>login-reverse-proxy</b>'," href='https://www.adminer.org/plugins/?version=".VERSION."'".target_blank());auth_error($l,$Gi,false);}}function
password_required(){static$H;if($H===null){$H=(bool)get_session("password_required");if(!$H){$Yb=adminer()->credentials();$H=!is_object(Driver::connect($Yb[0],$Yb[1],""));if($H)set_session("password_required",true);}}return$H;}function
require_password_link($E){$Wg="<a href='https://www.adminer.org/password/'".target_blank().">".lang(100)."</a>";if(!function_exists('password_hash'))return" $Wg";$Ji=($E!==null?$E:base64_encode(substr(pack("H*",rand_string()),0,12)));$De=password_hash($Ji,PASSWORD_DEFAULT);$o="adminer-plugins.php";$qd=file_exists("adminer-plugins.php");if($qd)$sf=($E!==null?lang(101,"<b>$o</b>"):lang(102,"<b>$o</b>","<b>$Ji</b>"));else{$o="<button name='password_less' value='".h($De)."' class='link'>$o</button>";$sf=($E!==null?lang(103,$o):lang(104,$o,"<b>$Ji</b>"));}$fg="\t<a>new</a> Adminer\\Password(<span class='jush-apo'>'".h($De)."'</span>),";$H="<p>$sf
<pre><code class='jush'>".($qd?$fg:"&lt;?php\n<a>return</a> <a>array</a>(\n$fg\n);")."</code></pre>
<p>$Wg
";return" <a href='#password-less' class='toggle'>".lang(105)."</a>
<div id='password-less' class='hidden'>".($qd?$H:"<form action='' method='post'>\n".$H.input_token()."</form>")."</div>";}if(preg_match('~^[-\w$./]+$~',$_POST["password_less"])&&verify_token()){header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=adminer-plugins.php");echo"<?php\nreturn array(\n\tnew Adminer\\Password('$_POST[password_less]'),\n);\n";exit;}$Ha=$_POST["auth"];if($Ha&&verify_token()){session_regenerate_id();$Fm=$Ha["driver"];$M=$Ha["server"];$U=$Ha["username"];$E=(string)$Ha["password"];$j=$Ha["db"];set_password($Fm,$M,$U,$E);$_SESSION["db"][$Fm][$M][$U][$j]=true;if($Ha["permanent"]){$x=implode("-",array_map('base64_encode',array($Fm,$M,$U,$j)));$cj=adminer()->permanentLogin(true);$Gi[$x]="$x:".base64_encode($cj?encrypt_string($E,$cj):"");cookie("adminer_permanent",implode(" ",$Gi));}if(!array_diff(array_keys($_POST),array("auth","token"))||$Fm!=DRIVER||$M!=SERVER||$U!==$_GET["username"]||$j!=DB)redirect(auth_url($Fm,$M,$U,$j));}elseif($_POST["logout"]&&(!$_SESSION["token"]||verify_token())){foreach(array("pwds","db","dbs","queries")as$x)set_session($x,null);unset_permanent($Gi);redirect(substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1),lang(106).' '.lang(107));}elseif($Gi&&!$_SESSION["pwds"]){session_regenerate_id();$cj=adminer()->permanentLogin();foreach($Gi
as$x=>$W){list(,$rb)=explode(":",$W);list($Fm,$M,$U,$j)=array_map('base64_decode',explode("-",$x));set_password($Fm,$M,$U,decrypt_string(base64_decode($rb),$cj));$_SESSION["db"][$Fm][$M][$U][$j]=true;}}function
unset_permanent(array&$Gi){foreach($Gi
as$x=>$W){list($Fm,$M,$U,$j)=array_map('base64_decode',explode("-",$x));if($Fm==DRIVER&&$M==SERVER&&$U==$_GET["username"]&&$j==DB)unset($Gi[$x]);}cookie("adminer_permanent",implode(" ",$Gi));}function
auth_error($l,array&$Gi,$vf=true){$qk=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$qk]||$_GET[$qk])&&!$_SESSION["token"])$l=lang(108);elseif($vf&&($E=get_password())!==null){restart_session();add_invalid_login();if($E===false)$l
.=($l?'<br>':'').lang(109,target_blank(),'<code>permanentLogin()</code>');set_password(DRIVER,SERVER,$_GET["username"],null);unset_permanent($Gi);}}if(!$_COOKIE[$qk]&&$_GET[$qk]&&ini_bool("session.use_only_cookies"))$l=lang(110);$oi=session_get_cookie_params();cookie("adminer_key",($_COOKIE["adminer_key"]?:rand_string()),$oi["lifetime"]);if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);page_header(lang(43),$l,null);echo"<form action='' method='post'>\n","<div>";if(hidden_fields($_POST,array("auth","token")))echo"<p class='message'>".lang(111)."\n";echo
input_token(),"</div>\n";adminer()->loginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!class_exists('Adminer\Db')){unset($_SESSION["pwds"][DRIVER]);unset_permanent($Gi);page_header(lang(112),lang(113,implode(", ",Driver::$extensions)),false);page_footer("auth");exit;}$f='';if(isset($_GET["username"])&&is_string(get_password())){check_invalid_login($Gi);$Yb=adminer()->credentials();$f=Driver::connect($Yb[0],$Yb[1],$Yb[2]);if(is_object($f)){Db::$instance=$f;Driver::$instance=new
Driver($f);if($f->flavor)save_settings(array("vendor-".DRIVER."-".SERVER=>get_driver(DRIVER)));}}$mg=null;if(!is_object($f)||($mg=adminer()->login($_GET["username"],get_password()))!==true){$l=(is_string($f)?nl_br(h($f)):(is_string($mg)?$mg:lang(114))).(preg_match('~^ | $~',get_password())?'<br>'.lang(115):'');auth_error($l,$Gi);}if($_POST["logout"]&&$_SESSION["token"]&&!verify_token()){page_header(lang(97),lang(116));page_footer("db");exit;}if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);stop_session(true);if($Ha&&$_POST["token"])$_POST["token"]=get_token();$l='';if($_POST){if(!verify_token()){header("HTTP/1.1 403 Forbidden");$l=lang(116).' '.lang(117);}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){header("HTTP/1.1 413 Content Too Large");$l=lang(118,"<b>post_max_size</b>");if(isset($_GET["sql"]))$l
.=' '.lang(119);}function
print_select_result($G,$g=null,array$Yh=array(),&$z=0){$hg=array();$w=array();$e=array();$Wa=array();$bm=array();$H=array();for($s=0;(!$z||$s<$z)&&($I=$G->fetch_row());$s++){if(!$s){echo"<div class='scrollable'>\n","<table class='nowrap odds'>\n","<thead><tr>";for($Ff=0;$Ff<count($I);$Ff++){$m=$G->fetch_field();$B=$m->name;$Xh=(isset($m->orgtable)?$m->orgtable:"");$Wh=(isset($m->orgname)?$m->orgname:$B);if($Yh&&JUSH=="sql")$hg[$Ff]=($B=="table"?"table=":($B=="possible_keys"?"indexes=":null));elseif($Xh!=""){if(isset($m->table))$H[$m->table]=$Xh;if(!isset($w[$Xh])){$w[$Xh]=array();foreach(indexes($Xh,$g)as$v){if($v["type"]=="PRIMARY"){$w[$Xh]=array_flip($v["columns"]);break;}}$e[$Xh]=$w[$Xh];}if(isset($e[$Xh][$Wh])){unset($e[$Xh][$Wh]);$w[$Xh][$Wh]=$Ff;$hg[$Ff]=$Xh;}}if($m->charsetnr==63)$Wa[$Ff]=true;$bm[$Ff]=$m->type;echo"<th title='".h(trim(($Xh!=""?"$Xh.$Wh":($m->name!=$Wh?$Wh:""))." ".driver()->typeName($m)))."'>".h($B).($Yh?doc_link(array('sql'=>"explain-output.html#explain_".strtolower($B),'mariadb'=>"explain/#the-columns-in-explain-select",)):"");}echo"<tbody>\n";}echo"<tr>";foreach($I
as$x=>$W){$_="";if(isset($hg[$x])&&!$e[$hg[$x]]){if($Yh&&JUSH=="sql"){$Q=$I[array_search("table=",$hg)];$_=ME.$hg[$x].url_escape($Yh[$Q]!=""?$Yh[$Q]:$Q);}else{$_=ME."edit=".url_escape($hg[$x]);foreach($w[$hg[$x]]as$vb=>$Ff){if($I[$Ff]===null){$_="";break;}$_
.="&where[".url_escape(bracket_escape($vb))."]=".url_escape($I[$Ff]);}}}$m=array('type'=>($Wa[$x]?'blob':($bm[$x]==254?'char':'')),);$W=select_value($W,$_,$m,null);echo"<td".($bm[$x]<=9||$bm[$x]==246?" class='number'":"").">$W";}}$z=$s;echo($s?"</table>\n</div>":"<p class='message'>".lang(15))."\n";return$H;}function
textarea($B,$X,$J=10,$zb=80,$Hf=JUSH){echo"<textarea name='".h($B)."' rows='$J' cols='$zb' class='sqlarea jush-".h($Hf)."' spellcheck='false' wrap='off'>";if(is_array($X)){foreach($X
as$W)echo
h($W[0])."\n\n\n";}else
echo
h($X);echo"</textarea>";}function
select_input($c,array$C,$X="",$Hi=""){if($C&&$X!=""&&!isset($C[$X]))$C=array($X=>$X)+$C;$pl=($C?"select":"input");return"<$pl$c".($C?"><option value=''>$Hi".optionlist($C,$X,true)."</select>":" size='10' value='".h($X)."' placeholder='$Hi'>");}function
json_row($x,$W=null,$id=true){static$Md=true;if($Md)echo"{";if($x!=""){echo($Md?"":",")."\n\t\"".addcslashes($x,"\r\n\t\"\\/").'": '.($W!==null?($id?'"'.addcslashes($W,"\r\n\"\\/").'"':$W):'null');$Md=false;}else{echo"\n}\n";$Md=true;}}function
flat_collations(){$yb=collations();return(is_array(reset($yb))?call_user_func_array('array_merge',array_values($yb)):$yb);}function
edit_type($x,array$m,array$yb,array$Wd=array(),array$yd=array()){$T=(string)$m["type"];echo"<td><select name='".h($x)."[type]' class='type' aria-labelledby='label-type'".on_help_value().">";if($T&&!array_key_exists($T,driver()->types())&&!isset($Wd[$T])&&!in_array($T,$yd))$yd[]=$T;$Rk=driver()->structuredTypes();if($Wd)$Rk[lang(120)]=$Wd;echo
optionlist(array_merge($yd,$Rk),$T),"</select><td>","<input name='".h($x)."[length]' value='".h($m["length"])."' size='3'".(!$m["length"]&&preg_match('~var(char|binary)$~',$T)?" class='required'":"")." aria-labelledby='label-length'>","<td class='options'>",($yb?"<input list='collations' name='".h($x)."[collation]'".option_types($T,'('.text_type().')$')." value='".h($m["collation"])."' placeholder='(".lang(121).")'>":''),(driver()->unsigned?"<select name='".h($x)."[unsigned]'".option_types($T,'^$|'.number_type()).'><option>'.optionlist(driver()->unsigned,$m["unsigned"]).'</select>':''),(isset($m['on_update'])?"<select name='".h($x)."[on_update]'".option_types($T,'timestamp|datetime').'>'.optionlist(array(""=>"(".lang(122).")","CURRENT_TIMESTAMP"),(preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?"CURRENT_TIMESTAMP":$m["on_update"])).'</select>':''),($Wd?"<select name='".h($x)."[on_delete]'".option_types($T,'`')."><option value=''>(".lang(123).")".optionlist(explode("|",driver()->onActions),$m["on_delete"])."</select> ":" ");}function
option_types($T,$bm){return" data-types='".h($bm)."'".(preg_match("~$bm~",$T)?"":" class='hidden'");}function
process_length($y){$dd=driver()->enumLength;return(preg_match("~^\\s*\\(?\\s*$dd(?:\\s*,\\s*$dd)*+\\s*\\)?\\s*\$~",$y)&&preg_match_all("~$dd~",$y,$rg)?"(".implode(",",$rg[0]).")":preg_replace('~^[0-9].*~','(\0)',preg_replace('~[^-0-9,+()[\]]~','',$y)));}function
process_in($W){$dd=driver()->enumLength;if(preg_match("~^\\s*\\(?\\s*$dd(?:\\s*,\\s*$dd)*+\\s*\\)?\\s*\$~",$W)&&preg_match_all("~$dd~",$W,$rg))return"(".implode(", ",$rg[0]).")";$H=array();foreach(explode(",",$W)as$Ef)$H[]=q(trim($Ef));return"(".implode(", ",$H).")";}function
process_type(array$m,$wb="COLLATE"){return" $m[type]".process_length($m["length"]).(preg_match(number_type(),$m["type"])&&in_array($m["unsigned"],driver()->unsigned)?" $m[unsigned]":"").(preg_match('~'.text_type().'~',$m["type"])&&$m["collation"]?" $wb ".(JUSH=="mssql"?$m["collation"]:q($m["collation"])):"");}function
process_field(array$m,array$Yl){if($m["on_update"])$m["on_update"]=str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",$m["on_update"]);return
array(idf_escape(trim($m["field"])),process_type($Yl),($m["null"]?" NULL":" NOT NULL"),default_value($m),(preg_match('~timestamp|datetime~',$m["type"])&&$m["on_update"]?" ON UPDATE $m[on_update]":""),(support("comment")&&$m["comment"]!=""?" COMMENT ".q($m["comment"]):""),($m["auto_increment"]?auto_increment():null),);}function
default_value(array$m){if($m["default"]===null)return"";$k=str_replace("\r","",$m["default"]);$ke=$m["generated"];return(in_array($ke,driver()->generated)?(JUSH=="mssql"?" AS ($k)".($ke=="VIRTUAL"?"":" $ke"):" GENERATED ALWAYS AS ($k) $ke"):(preg_match('~^GENERATED ~i',$k)?" $k":" DEFAULT ".(preg_match('~char|binary|text|json|enum|set|String~',$m["type"])||preg_match('~^(?![a-z])~i',$k)?(JUSH=="sql"&&preg_match('~text|json~',$m["type"])?"(".q($k).")":q($k)):str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",(JUSH=="sqlite"?"($k)":$k)))));}function
edit_fields(array$n,array$yb,$T="TABLE",array$Wd=array()){$n=array_values($n);$oc=(($_POST?$_POST["defaults"]:get_setting("defaults"))?"":" class='hidden'");$Db=(($_POST?$_POST["comments"]:get_setting("comments"))?"":" class='hidden'");echo"<thead><tr>\n",($T=="PROCEDURE"?"<td>":""),"<th id='label-name'>".($T=="TABLE"?lang(124):lang(125)),"<td id='label-type'>".lang(56)."<textarea id='enum-edit' rows='4' cols='12' wrap='off' hidden></textarea>".script("qs('#enum-edit').onblur = editingLengthBlur;"),"<td id='label-length'>".lang(126),"<td>".lang(127);if($T=="TABLE")echo"<td id='label-null'>NULL\n","<td><input type='radio' name='auto_increment_col' value=''><abbr id='label-ai' title='".lang(58)."'>AI</abbr>",doc_link(array('sql'=>"example-auto-increment.html",'mariadb'=>"auto_increment/",'sqlite'=>"autoinc.html",'pgsql'=>"datatype-numeric.html#DATATYPE-SERIAL",'mssql'=>"t-sql/statements/create-table-transact-sql-identity-property",)),"<td id='label-default'$oc>".lang(59),(support("comment")?"<td id='label-comment'$Db>".lang(57):"");$Vf=!support("move_col");echo"<td>".icon("plus","add[".($Vf?count($n):0)."]","+",lang(128),($Vf?on('click','editingAddLastRow'):"")),"<tbody".on('click','editingClick').on('input','editingInput').on('keydown','editingKeydown').">\n";foreach($n
as$s=>$m){$s++;$Zh=$m[($_POST?"orig":"field")];$Ac=(isset($_POST["add"][$s-1])||(isset($m["field"])&&!idx($_POST["drop_col"],$s)))&&(support("drop_col")||$Zh=="");echo"<tr".($Ac?"":" hidden").">\n",($T=="PROCEDURE"?"<td>".html_select("fields[$s][inout]",explode("|",driver()->inout),$m["inout"]):"")."<th>",(support("move_col")?icon("move","","↕",lang(129))." ":"");if($Ac)echo"<input name='fields[$s][field]' value='".h($m["field"])."' data-maxlength='64' autocapitalize='off' aria-labelledby='label-name'".(isset($_POST["add"][$s-1])?" autofocus":"").">";echo
input_hidden("fields[$s][orig]",$Zh);edit_type("fields[$s]",$m,$yb,$Wd);if($T=="TABLE"){echo"<td><label class='block'>".checkbox("fields[$s][null]",1,$m["null"],"","","","label-null")."</label>","<td><label class='block'><input type='radio' name='auto_increment_col' value='$s'".($m["auto_increment"]?" checked":"")." aria-labelledby='label-ai'></label>","<td$oc>".(driver()->generated?html_select("fields[$s][generated]",array_merge(array("","DEFAULT"),driver()->generated),$m["generated"])." ":checkbox("fields[$s][generated]",1,$m["generated"],"","","","label-default"));$c=" name='fields[$s][default]' aria-labelledby='label-default'";$X=h($m["default"]);echo(preg_match('~\n~',$m["default"])?"<textarea$c rows='2' cols='30' style='vertical-align: bottom;'>\n$X</textarea>":"<input$c value='$X'>");if(support("comment")){$c=" name='fields[$s][comment]' data-maxlength='".(min_version(5.5)?1024:255)."' aria-labelledby='label-comment'";echo"<td$Db>".adminer()->commentInput('COLUMN',$c,$m["comment"]);}}echo"<td>",(support("move_col")?icon("plus","add[$s]","+",lang(128))." ":""),($Zh==""||support("drop_col")?icon("cross","drop_col[$s]","x",lang(130)):"");}}function
process_fields(array&$n){if($_POST["add"]){$n=array_values($n);array_splice($n,key($_POST["add"]),0,array(array()));}return$_POST["add"]||$_POST["drop_col"];}function
drop_create($Mc,$h,$Oc,$vl,$Qc,$lg,$Lg,$Jg,$Kg,$Fh,$kh){if($_POST["drop"])query_redirect($Mc,$lg,$Lg);elseif($Fh=="")query_redirect($h,$lg,$Kg);elseif(support("transaction_ddl")){driver()->begin();queries_redirect($lg,$Jg,queries($Mc)&&queries($h)&&driver()->commit());driver()->rollback();}elseif($Fh!=$kh){$Xb=queries($h);queries_redirect($lg,$Jg,$Xb&&queries($Mc));if($Xb&&$Oc)queries($Oc);}else
queries_redirect($lg,$Jg,queries($vl)&&queries($Qc)&&queries($Mc)&&queries($h));}function
create_trigger($Ih,array$I){$Al=" $I[Timing] $I[Event]".(preg_match('~ OF~',$I["Event"])?" $I[Of]":"");return"CREATE TRIGGER ".idf_escape($I["Trigger"]).(JUSH=="mssql"?$Ih.$Al:$Al.$Ih).rtrim(" $I[Type]\n$I[Statement]",";").";";}function
q_dollar($P){$tc='$$';while(strpos($P.$tc,$tc)!=strlen($P))$tc='$_'.substr($tc,1);return$tc.$P.$tc;}function
routine_collate($xb){static$jb=array();if($xb&&!$jb){foreach(collations()as$ib=>$Dm){foreach((array)$Dm
as$W)$jb[$W]=$ib;}}return($jb[$xb]?"CHARACTER SET ".q($jb[$xb])." ":"")."COLLATE";}function
create_routine($Ij,array$I){$N=array();$n=(array)$I["fields"];ksort($n);foreach($n
as$m){if($m["field"]!="")$N[]="\n  ".(preg_match("~^(".driver()->inout.")\$~",$m["inout"])?"$m[inout] ":"").idf_escape($m["field"]).process_type($m,routine_collate($m["collation"]));}$qc="";$C=array();foreach(routine_options($Ij)as$x=>$Y){$X=idx((array)$I["options"],$x,"");if($x=="DEFINER")$qc=($X?" $x=".implode("@",array_map('Adminer\q',explode("@",$X,2))):"");elseif(!$Y){if($X!="")$C[]="$x ".q($X);}elseif($X!=reset($Y)&&in_array($X,$Y))$C[]=$X;}$Tf=$I["language"];$rc=rtrim($I["definition"],";");$Ic=(JUSH=="pgsql"||($Tf&&$Tf!="sql"));return"CREATE$qc $Ij ".idf_escape(trim($I["name"]))." (".($N?implode(",",$N)."\n":"").")".($Ij=="FUNCTION"?"\nRETURNS".process_type($I["returns"],routine_collate($I["returns"]["collation"])):"").($Tf?" LANGUAGE $Tf":"").($C?"\n".implode(" ",$C):"").($Ic?" AS ".q_dollar("\n".trim($rc)."\n"):"\n$rc;");}function
remove_definer($F){return
preg_replace('~^([A-Z =]+) DEFINER=`'.preg_replace('~@(.*)~','`@`(%|\1)',logged_user()).'`~','\1',$F);}function
format_foreign_key(array$p){$j=$p["db"];$rh=$p["ns"];return" FOREIGN KEY (".implode(", ",array_map('Adminer\idf_escape',$p["source"])).") REFERENCES ".($j!=""&&$j!=$_GET["db"]?idf_escape($j).".":"").($rh!=""&&$rh!=$_GET["ns"]?idf_escape($rh).".":"").idf_escape($p["table"])." (".implode(", ",array_map('Adminer\idf_escape',$p["target"])).")".(preg_match("~^(".driver()->onActions.")\$~",$p["on_delete"])?" ON DELETE $p[on_delete]":"").(preg_match("~^(".driver()->onActions.")\$~",$p["on_update"])?" ON UPDATE $p[on_update]":"").($p["deferrable"]?" $p[deferrable]":"");}function
tar_file($o,$Fl){$H=pack("a100a8a8a8a12a12",$o,644,0,0,decoct($Fl->size),decoct(time()));$pb=8*32;for($s=0;$s<strlen($H);$s++)$pb+=ord($H[$s]);$H
.=sprintf("%06o",$pb)."\0 ";echo$H,str_repeat("\0",512-strlen($H));$Fl->send();echo
str_repeat("\0",511-($Fl->size+511)%512);}function
doc_version(){$ok=connection()->server_info;if(JUSH=='oracle'){preg_match('~(?:.* |^)(\d+)\.\d+\.\d+\.\d+\.\d+~s',$ok,$A);return($A[1]>=18?$A[1]:"19");}$wj=(JUSH=='sql'?'~^\d+\.\d+~':'~^\d\.?\d~');$Gm=(preg_match($wj,$ok,$A)?$A[0]:"");if(JUSH=='mssql')return($Gm>=15?"sql-server-ver$Gm":($Gm==12?"azuresqldb-current":"sql-server-2017"));return$Gm;}function
doc_link(array$Di,$wl="<sup>?</sup>"){$Gm=doc_version();$rm=array('sql'=>"https://dev.mysql.com/doc/refman/$Gm/en/",'sqlite'=>"https://www.sqlite.org/",'pgsql'=>"https://www.postgresql.org/docs/".(connection()->flavor=='cockroach'?"current":$Gm)."/",'mssql'=>"https://learn.microsoft.com/en-us/sql/",'oracle'=>"https://docs.oracle.com/en/database/oracle/oracle-database/$Gm/",);if(connection()->flavor=='maria'){$rm['sql']="https://mariadb.com/kb/en/";$Di['sql']=(isset($Di['mariadb'])?$Di['mariadb']:str_replace(".html","/",$Di['sql']));}return($Di[JUSH]?"<a href='".h($rm[JUSH].$Di[JUSH].(JUSH=='mssql'?"?view=$Gm":""))."'".target_blank().">$wl</a>":"");}function
db_size($j){if(!connection()->select_db($j))return"?";$H=0;foreach(table_status()as$R)$H+=$R["Data_length"]+$R["Index_length"];return
format_number($H);}function
set_utf8mb4($h){static$N=false;if(!$N&&preg_match('~\butf8mb4~i',$h)){$N=true;echo"SET NAMES ".charset(connection()).";\n\n";}}if(isset($_GET["status"]))$_GET["variables"]=$_GET["status"];if(isset($_GET["import"]))$_GET["sql"]=$_GET["import"];if(DB==""&&isset($_GET["ns"]))redirect(remove_from_uri('ns'));if(!(DB!=""?connection()->select_db(DB):isset($_GET["sql"])||isset($_GET["dump"])||isset($_GET["database"])||isset($_GET["processlist"])||isset($_GET["privileges"])||isset($_GET["user"])||isset($_GET["variables"])||$_GET["script"]=="connect"||$_GET["script"]=="kill")){if(DB!=""||$_GET["refresh"]){restart_session();set_session("dbs",null);}if(DB!=""){header("HTTP/1.1 404 Not Found");page_header(lang(42).": ".h(DB),lang(131),true);}else{if(!isset($_GET["db"])&&support("single_db")){$i=adminer()->databases();if($i)redirect(ME."db=".url_escape($i[0]));}if($_POST["db"]&&!$l)queries_redirect(substr(ME,0,-1),lang(132),drop_databases($_POST["db"]));page_header(lang(133),$l,false);echo"<p class='links'>\n";foreach(array('database'=>lang(134),'privileges'=>lang(78),'processlist'=>lang(135),'variables'=>lang(136),'status'=>lang(137),)as$x=>$W){if(support($x))echo"<a href='".h(ME)."$x='>$W</a>\n";}echo"<p>".lang(138,get_driver(DRIVER),"<b>".h(connection()->server_info)."</b>","<b>".connection()->extension."</b>")."\n","<p>".lang(139,"<b>".h(logged_user())."</b>")."\n";$i=adminer()->databases();if($i){$Rj=support("scheme");$yb=collations();echo"<form action='' method='post'>\n","<table class='checkable odds'".on('click','tableClick').on('dblclick','tableClick').">\n","<thead><tr>".(support("database")?"<td class='hover'>":"")."<th".(JUSH!='mssql'?" aria-sort='ascending'":"").">".lang(42).(get_session("dbs")!==null?" - <a href='".h(ME)."refresh=1'>".lang(140)."</a>":"")."<td>".lang(141)."<td>".lang(142)."<td>".lang(143)." - <a href='".h(ME)."dbsize=1'".on('click','ajaxSetHtml',ME."script=connect").">".lang(144)."</a>"."<tbody>\n";$i=($_GET["dbsize"]?count_tables($i):array_flip($i));foreach($i
as$j=>$S){$Hj=h(preg_replace('~&db=[^&]*~','',ME))."db=".url_escape($j);$t=h("Db-".$j);echo"<tr>".(support("database")?"<td class='hover'>".checkbox("db[]",$j,in_array($j,(array)$_POST["db"]),"","","",$t):""),"<th><a href='$Hj' id='$t'>".h($j)."</a>";$xb=h(db_collation($j,$yb));echo"<td>".(support("database")?"<a href='$Hj".($Rj?"&amp;ns=":"")."&amp;database=' title='".lang(74)."'>$xb</a>":$xb),"<td align='right'><a href='$Hj&amp;schema=' id='tables-".h($j)."' title='".lang(77)."'>".($_GET["dbsize"]?format_number($S):"?")."</a>","<td align='right' id='size-".h($j)."'>".($_GET["dbsize"]?db_size($j):"?"),"\n";}echo"</table>\n",(support("database")?"<div class='footer'><div>\n"."<fieldset><legend>".lang(145)." <span id='selected'></span></legend><div>\n"."<input type='hidden' name='all' value=''".on('click','countDbs').">\n"."<input type='submit' name='drop' value='".lang(146)."'".confirm().">\n"."</div></fieldset>\n"."</div></div>\n":""),input_token(),"</form>\n",script("tableCheck();");}$qa=adminer();$Li=($qa
instanceof
Plugins?$qa->plugins:array());$Lc=($qa
instanceof
Plugins?$qa->drivers:array());$yc=design_checksums();if($Li||$Lc||$yc){$qb=($qa
instanceof
Plugins?$qa->checksums():array());$yh=Plugins::officialChecksums();$nm=function($qm){return" (<a href='$qm'".target_blank()." class='update'>".VERSION."</a>)";};$Ki=function($Gd)use($qb,$yh,$nm){return($qb[$Gd]&&$yh[$Gd]&&$qb[$Gd]!==$yh[$Gd]?$nm("https://www.adminer.org/plugins/?version=".VERSION):"");};echo"<div class='plugins'>\n","<h3>".lang(147)."</h3>\n<ul>\n";foreach($Li
as$Ii){$uj=new
\ReflectionObject($Ii);$vc=(method_exists($Ii,'description')?$Ii->description():"");if(!$vc){if(preg_match('~^/[\s*]+(.+)~',$uj->getDocComment(),$A))$vc=$A[1];}$Sj=(method_exists($Ii,'screenshot')?$Ii->screenshot():"");echo"<li><b>".get_class($Ii)."</b>".h($vc?": $vc":"").($Sj?" (<a href='".h($Sj)."'".target_blank().">".lang(148)."</a>)":"").$Ki(basename((string)$uj->getFileName(),'.php'))."\n";}foreach($Lc
as$t=>$B)echo"<li><b>".h($t)."</b>: ".h($B).$Ki(basename((string)$qa->driverFiles[$t],'.php'))."\n";if($yc){$_h=official_design_checksums();foreach($yc
as$o=>$xc){list($B,$pb)=$xc;$zh=$_h["$B/$o"];echo"<li><b>".h($o)."</b>".h($B?": $B":"").($zh&&$zh!==$pb?$nm("https://www.adminer.org/?version=".VERSION."#extras"):"")."\n";}}echo"</ul>\n";adminer()->pluginsLinks();echo"</div>\n";}}page_footer("db");exit;}if(support("scheme")){if(DB!=""&&$_GET["ns"]!==""){if(!isset($_GET["ns"]))redirect(preg_replace('~&db=[^&]+~','\0&ns='.url_escape(get_schema()),relative_uri()));if(!set_schema($_GET["ns"])){header("HTTP/1.1 404 Not Found");page_header(lang(86).h(": $_GET[ns]"),lang(149),true);page_footer("ns");exit;}}}adminer()->afterConnect();class
TmpFile{private$handler;var$size=0;function
__construct(){$this->handler=tmpfile();}function
write($Pb){$this->size+=strlen($Pb);fwrite($this->handler,$Pb);}function
send(){fseek($this->handler,0);fpassthru($this->handler);fclose($this->handler);}}if($_GET["select"]!=""&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["callf"]))$_GET["call"]=$_GET["callf"];if(isset($_GET["function"]))$_GET["procedure"]=$_GET["function"];if(isset($_GET["download"])){$a=$_GET["download"];$n=fields($a);header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$_GET["where"])).".".friendly_url($_GET["field"]));$L=array(idf_escape($_GET["field"]));$G=driver()->select($a,$L,array(where($_GET,$n)),$L);$I=($G?$G->fetch_row():array());echo
driver()->value($I[0],$n[$_GET["field"]]);exit;}elseif(isset($_GET["table"])){$a=$_GET["table"];$n=fields($a);if(!$n)$l=adminer()->error()?:lang(12);$R=table_status1($a);$B=adminer()->tableName($R);$l=$l?:h($R["Error"]);page_header(($n&&is_view($R)?$R['Engine']=='materialized view'?lang(150):lang(151):lang(152)).": ".($B!=""?$B:h($a)),$l);$Gj=array();foreach($n
as$x=>$m)$Gj+=$m["privileges"];adminer()->selectLinks($R,(isset($Gj["insert"])||!support("table")?"":null));$Cb=$R["Comment"];if($Cb!="")echo"<p class='nowrap'>".lang(57).": ".adminer()->commentValue('TABLE',$Cb)."\n";if($n)adminer()->tableStructurePrint($n,$R);function
tables_links(array$S){echo"<ul>\n";foreach($S
as$I){$_=preg_replace('~ns=[^&]*~',"ns=".url_escape($I["ns"]),ME);echo"<li><a href='".h($_."table=".url_escape($I["table"]))."'>".($I["ns"]!=$_GET["ns"]?"<b>".h($I["ns"])."</b>.":"").h($I["table"])."</a>";}echo"</ul>\n";}$lf=driver()->inheritsFrom($a);if($lf){echo"<h3>".lang(153)."</h3>\n";tables_links($lf);}if(support("indexes")&&driver()->supportsIndex($R)){echo"<div>\n","<h3 id='indexes'>".lang(154)."</h3>\n";$w=indexes($a);if($w)adminer()->tableIndexesPrint($w,$R);if(driver()->supportsAlterIndex($R))echo'<p class="links hover"><a href="'.h(ME).'indexes='.url_escape($a).'">'.lang(155)."</a>\n";echo"</div>\n";}if(!is_view($R)&&driver()->supportsAlterTable($R)){if(fk_support($R)){echo"<div>\n","<h3 id='foreign-keys'>".lang(120)."</h3>\n";$Wd=foreign_keys($a);if($Wd){echo"<table>\n","<thead><tr><th>".lang(156)."<td>".lang(157)."<td>".lang(123)."<td>".lang(122)."<td class='hover'><tbody>\n";foreach($Wd
as$B=>$p){echo"<tr title='".h($B)."'>","<th><i>".implode("</i>, <i>",array_map('Adminer\h',$p["source"]))."</i>";$_=($p["db"]!=""?preg_replace('~db=[^&]*~',"db=".url_escape($p["db"]),ME):($p["ns"]!=""?preg_replace('~ns=[^&]*~',"ns=".url_escape($p["ns"]),ME):ME));echo"<td><a href='".h($_."table=".url_escape($p["table"]))."'>".($p["db"]!=""&&$p["db"]!=DB?"<b>".h($p["db"])."</b>.":"").($p["ns"]!=""&&$p["ns"]!=$_GET["ns"]?"<b>".h($p["ns"])."</b>.":"").h($p["table"])."</a>","(<i>".implode("</i>, <i>",array_map('Adminer\h',$p["target"]))."</i>)","<td>".h($p["on_delete"]),"<td>".h($p["on_update"]),'<td class="hover"><a href="'.h(ME.'foreign='.url_escape($a).'&name='.url_escape($B)).'">'.lang(158).'</a>',"\n";}echo"</table>\n";}echo'<p class="links hover"><a href="'.h(ME).'foreign='.url_escape($a).'">'.lang(159)."</a>\n","</div>\n";}if(support("check")){echo"<div>\n","<h3 id='checks'>".lang(160)."</h3>\n";$lb=driver()->checkConstraints($a);if($lb){echo"<table>\n";foreach($lb
as$x=>$W)echo"<tr title='".h($x)."'>","<td><code class='jush-".JUSH."'>".shorten_utf8(preg_replace('~\s+~',' ',ltrim($W)),80,"</code>"),"<td class='hover'><a href='".h(ME.'check='.url_escape($a).'&name='.url_escape($x))."'>".lang(158)."</a>","\n";echo"</table>\n";}echo'<p class="links hover"><a href="'.h(ME).'check='.url_escape($a).'">'.lang(161)."</a>\n","</div>\n";}}if(support(is_view($R)?"view_trigger":"trigger")&&driver()->supportsAlterTable($R)){echo"<div>\n","<h3 id='triggers'>".lang(162)."</h3>\n";$Vl=triggers($a);if($Vl){echo"<table>\n";foreach($Vl
as$x=>$W)echo"<tr valign='top'><td>".h($W[0])."<td>".h($W[1])."<th>".h($x)."<td class='hover'><a href='".h(ME.'trigger='.url_escape($a).'&name='.url_escape($x))."'>".lang(158)."</a>\n";echo"</table>\n";}echo'<p class="links hover"><a href="'.h(ME).'trigger='.url_escape($a).'">'.lang(163)."</a>\n","</div>\n";}$tk=driver()->shadowTables($a);if($tk){echo"<h3 id='shadow-tables'>".lang(164)."</h3>\n";tables_links($tk);}$kf=driver()->inheritedTables($a);if($kf){echo"<h3 id='partitions'>".lang(165)."</h3>\n";$si=driver()->partitionsInfo($a);if($si)echo"<p><code class='jush-".JUSH."'>BY ".h("$si[partition_by]($si[partition])")."</code>\n";tables_links($kf);}}elseif(isset($_GET["schema"])){page_header(lang(77),"",array(),h(DB.($_GET["ns"]?".$_GET[ns]":"")));function
schema_column($Q,array$tj,array&$e){if(!isset($e[$Q])){$e[$Q]=0;foreach((array)idx($tj,$Q)as$B=>$vj){if($B!=$Q)$e[$Q]=max($e[$Q],schema_column($B,$tj,$e)+1);}}return$e[$Q];}function
type_class($T){foreach(array('char'=>'text','date'=>'time|year','binary'=>'blob','enum'=>'set',)as$x=>$W){if(preg_match("~$x|$W~",$T))return" class='$x'";}}$gl=array();$il=array();$hl=array();$Dd=array();$da=($_GET["schema"]?:$_COOKIE["adminer_schema-".str_replace(".","_",DB)]);preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~',$da,$rg,PREG_SET_ORDER);foreach($rg
as$s=>$A){$gl[$A[1]]=array((float)$A[2],(float)$A[3]);$il[]="\n\t'".js_escape($A[1])."': [ $A[2], $A[3] ]";}$K=array();$tj=array();$Wd=array();$xa=driver()->allFields();$Je=array();$jl=array();foreach(table_status('',true)as$Q=>$R){if(!is_view($R)){if(adminer()->tableName($R)!=""&&!$R["dependent"])$jl[$Q]=$R;else$Je[$Q]=true;}}foreach($jl
as$Q=>$R){$Ni=0;$K[$Q]["fields"]=array();foreach($xa[$Q]as$m){$Ni+=1.25;$Dd[$Q][$m["field"]]=$Ni;$K[$Q]["fields"][$m["field"]]=$m;}foreach(adminer()->foreignKeys($Q)as$W){if($W["db"]==""&&$W["ns"]==""&&!$Je[$W["table"]]){$Wd[$Q][]=$W;$tj[$W["table"]][$Q]=array();}}}$e=array();$oe=array();$Tm=array();$ue=array();foreach(array_keys($K)as$B)schema_column($B,$tj,$e);arsort($e);foreach($e
as$B=>$d){$Rg=null;foreach((array)idx($Wd,$B)as$W){if($W["table"]!=$B&&$K[$W["table"]])$Rg=($Rg===null?$e[$W["table"]]:min($Rg,$e[$W["table"]]));}$e[$B]=max($d,(int)$Rg-1);}foreach($K
as$B=>$Q){$d=$e[$B];$oe[$d][]=$B;$yl=.75*strlen($B);foreach($Q["fields"]as$m)$yl=max($yl,.65*strlen($m["field"]));$Tm[$d]=max(idx($Tm,$d,0),ceil($yl)+1);}foreach($Wd
as$B=>$Dm){foreach($Dm
as$W){$te=$e[$B]+(idx($e,$W["table"],$e[$B])>$e[$B]?1:0);$ue[$te]=idx($ue,$te,0)+1;}}ksort($oe);$He=0;$Sm=0;$_b=0;$Xi=null;$cl=array();$ll=array();foreach($oe
as$d=>$S){if($Xi!==null){$_b=round($_b+$Tm[$Xi]+1.7+idx($ue,$d,0)*.1,1);$Uh=array();foreach($S
as$B){$Vk=0;$Vb=0;$gh=array_keys((array)idx($tj,$B));foreach((array)idx($Wd,$B)as$W)$gh[]=$W["table"];foreach($gh
as$ch){if($K[$ch]&&$e[$ch]<$d){$Vk+=$K[$ch]["pos"][0];$Vb++;}}$Uh[$B]=($Vb?$Vk/$Vb:$He);}asort($Uh);$S=array_keys($Uh);}$Il=0;foreach($S
as$B){$Ni=1.25*count($K[$B]["fields"]);$K[$B]["pos"]=($gl[$B]?:array($Il,$_b));$cl[$B]=$K[$B]["pos"][1];$ll[$B]=$Tm[$d];$Il+=2.5+$Ni;$He=max($He,$K[$B]["pos"][0]+2.5+$Ni);$Sm=max($Sm,round($K[$B]["pos"][1]+$Tm[$d],1));if(!$gl[$B])$hl[]="\n\t'".js_escape($B)."': [ ".$K[$B]["pos"][0].", ".$K[$B]["pos"][1]." ]";}$Xi=$d;}$Zf=array();$Ra=array();foreach($Wd
as$B=>$Dm){foreach($Dm
as$W){$rl=idx($cl,$W["table"],$cl[$B]);$Dk=$cl[$B]+$ll[$B];$Fj=($rl-1>$Dk);$Xf=($Fj?$Dk+1:min($cl[$B],$rl)-1);$Qa=idx($Ra,(string)$Xf,0);$Ra[(string)$Xf]=$Qa+1;$Xf=round($Fj?min($Xf+$Qa*.1,$rl-1):$Xf-$Qa*.1,1);while($Zf[(string)$Xf])$Xf-=.0001;$K[$B]["references"][$W["table"]][(string)$Xf]=array($W["source"],$W["target"]);$tj[$W["table"]][$B][(string)$Xf]=$W["target"];$Zf[(string)$Xf]=true;}}echo'<div id="schema" style="height: ',$He,'em; width: ',$Sm,'em;">
<script',nonce(),'>
const tablePos = {',implode(",",$il)."\n",'};
const tablePosDefault = {',implode(",",$hl)."\n",'};
const em = qs(\'#schema\').offsetHeight / ',$He,';
document.onmousemove = schemaMousemove;
document.onmouseup = event => schemaMouseup(event, \'',js_escape(DB),'\');
</script>
';foreach($K
as$B=>$Q){echo"<div class='table'".on('mousedown','schemaMousedown')." style='top: ".$Q["pos"][0]."em; left: ".$Q["pos"][1]."em; width: ".$ll[$B]."em;'>",'<a href="'.h(ME).'table='.url_escape($B).'"><b>'.h($B)."</b></a>";foreach($Q["fields"]as$m){$W='<span'.type_class($m["type"]).' title="'.h($m["type"].($m["length"]?"($m[length])":"").($m["null"]?" NULL":'')).'">'.h($m["field"]).'</span>';echo"<br>".($m["primary"]?"<i>$W</i>":$W);}foreach((array)$Q["references"]as$sl=>$vj){foreach($vj
as$Xf=>$qj){$Yf=$Xf-$Q["pos"][1];$Sk=($Yf>0?"left: 100%; width: calc($Yf"."em - 100%)":"left: $Yf"."em");$Sm=($Yf>0?"100%":(-$Yf)."em");$s=0;foreach($qj[0]as$Ck)echo"\n<div class='references' title='".h($sl)."' id='refs$Xf-".($s++)."' style='$Sk"."; top: ".$Dd[$B][$Ck]."em; padding-top: .5em;'>"."<div style='border-top: 1px solid gray; width: $Sm;'></div></div>";}}foreach((array)$tj[$B]as$sl=>$vj){foreach($vj
as$Xf=>$tl){$Yf=$Xf-$Q["pos"][1];$s=0;foreach($tl
as$ql)echo"\n<div class='references arrow' title='".h($sl)."' id='refd$Xf-".($s++)."' style='left: $Yf"."em; top: ".$Dd[$B][$ql]."em;'>"."<div style='height: .5em; border-bottom: 1px solid gray; width: ".(-$Yf)."em;'></div>"."</div>";}}echo"\n</div>\n";}foreach($K
as$B=>$Q){foreach((array)$Q["references"]as$sl=>$vj){if($K[$sl]){foreach($vj
as$Xf=>$qj){$Sg=$He;$zg=-10;foreach($qj[0]as$x=>$Ck){$Oi=$Q["pos"][0]+$Dd[$B][$Ck];$Pi=$K[$sl]["pos"][0]+$Dd[$sl][$qj[1][$x]];$Sg=min($Sg,$Oi,$Pi);$zg=max($zg,$Oi,$Pi);}echo"<div class='references' id='refl$Xf' style='left: $Xf"."em; top: $Sg"."em; padding: .5em 0;'><div style='border-right: 1px solid gray; margin-top: 1px; height: ".($zg-$Sg)."em;'></div></div>\n";}}}}echo'</div>
<p class="links"><a href="',h(ME."schema=".url_escape($da)),'" id="schema-link">',lang(166),'</a>
';}elseif(isset($_GET["dump"])){$a=$_GET["dump"];if($_POST&&!$l){$k=array("auto_increment"=>'');foreach(array("type","routine","event","trigger")as$Xk){if(support($Xk))$k[$Xk."s"]='';}save_settings(array_intersect_key($_POST+$k,array_flip(array("output","format","db_style","table_style","data_style"))+$k),"adminer_export");$S=array_flip((array)$_POST["tables"])+array_flip((array)$_POST["data"]);$ud=dump_headers((count($S)==1?key($S):DB),(DB==""||$_GET["ns"]===""||count($S)>1));$Bf=preg_match('~sql~',$_POST["format"]);if($Bf){echo"-- Adminer ".VERSION." ".get_driver(DRIVER)." ".str_replace("\n"," ",connection()->server_info)." dump\n\n";if(JUSH=="sql"){echo"SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
".($_POST["data_style"]?"SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
":"")."
";connection()->query("SET time_zone = '+00:00'");connection()->query("SET sql_mode = ''");}}$Sk=$_POST["db_style"];$i=array(DB);if(DB==""){$i=$_POST["databases"];if(is_string($i))$i=explode("\n",rtrim(str_replace("\r","",$i),"\n"));}foreach((array)$i
as$j){adminer()->dumpDatabase($j);if(connection()->select_db($j)){if($Bf&&$Sk)echo
use_sql($j,$Sk).";\n\n";foreach(($_GET["ns"]===""?(array)$_POST["schemas"]:(DB!=""||!support("scheme")?array(""):adminer()->schemas()))as$K){if($K!=""){if(DB==""&&information_schema(DB,$K))continue;set_schema($K);}$Pk=($_POST["table_style"]||$_POST["data_style"]?table_status('',true):array());$td=array();$hc=array();foreach($Pk
as$B=>$R){if(DB==""||$_GET["ns"]===""||in_array($B,(array)$_POST["tables"]))$td[$B]=$R;if(DB==""||$_GET["ns"]===""||in_array($B,(array)$_POST["data"]))$hc[$B]=$R;}if($Bf){if($_POST["table_style"]=="DROP+CREATE"&&function_exists('Adminer\drop_sql'))echo
drop_sql($td);if($_POST["data_style"]=="TRUNCATE+INSERT"&&function_exists('Adminer\truncate_all_sql')){$Wl=array();foreach($hc
as$B=>$R){if(!is_view($R)&&!($_POST["table_style"]=="DROP+CREATE"&&isset($td[$B])))$Wl[]=$B;}echo
truncate_all_sql($Wl);}$hi="";if($_POST["types"]){foreach(types()as$t=>$T){$rc=type_definition($t);$vh=($rc["kind"]=='d'?"DOMAIN":"TYPE");if($rc["definition"])$hi
.=($Sk!='DROP+CREATE'?"DROP $vh IF EXISTS ".idf_escape($T).";;\n":"")."CREATE $vh ".idf_escape($T)." $rc[definition];\n\n";else$hi
.="-- Could not export type $T\n\n";}}if($_POST["routines"]){foreach(routines()as$I){$B=$I["ROUTINE_NAME"];$Ij=$I["ROUTINE_TYPE"];$h=create_routine($Ij,array("name"=>$B)+routine($I["SPECIFIC_NAME"],$Ij));set_utf8mb4($h);$hi
.=($Sk!='DROP+CREATE'?"DROP $Ij IF EXISTS ".idf_escape($B).";;\n":"")."$h;\n\n";}}if($_POST["events"]){foreach(get_rows("SHOW EVENTS",null,"-- ")as$I){$h=remove_definer(get_val("SHOW CREATE EVENT ".idf_escape($I["Name"]),3));set_utf8mb4($h);$hi
.=($Sk!='DROP+CREATE'?"DROP EVENT IF EXISTS ".idf_escape($I["Name"]).";;\n":"")."$h;;\n\n";}}echo($hi&&JUSH=='sql'?"DELIMITER ;;\n\n$hi"."DELIMITER ;\n\n":$hi);}if($_POST["table_style"]||$_POST["data_style"]){$Im=array();foreach($Pk
as$B=>$R){$Q=array_key_exists($B,$td);$fc=array_key_exists($B,$hc);if($Q||$fc){$Fl=null;if($ud=="tar"){$Fl=new
TmpFile;ob_start(array($Fl,'write'),1e5);}adminer()->dumpTable($B,($Q?$_POST["table_style"]:""),(is_view($R)?2:0));if(is_view($R))$Im[]=$B;elseif($fc){$n=fields($B);$L=array("*");$Sb=convert_fields($n,$n);if($Sb)$L[]=substr($Sb,2);adminer()->dumpData($B,$_POST["data_style"],"",$L);}if($Bf&&$_POST["triggers"]&&$Q&&($Vl=trigger_sql($B)))echo"\nDELIMITER ;;\n$Vl\nDELIMITER ;\n";if($ud=="tar"){ob_end_flush();tar_file((DB!=""?"":"$j/")."$B.csv",$Fl);}elseif($Bf)echo"\n";}}if($Bf&&$_POST["table_style"]&&function_exists('Adminer\foreign_keys_sql')){foreach($td
as$B=>$R){if(!is_view($R))echo
foreign_keys_sql($B);}}if($Bf){foreach($Im
as$Hm)adminer()->dumpTable($Hm,$_POST["table_style"],1);}if($ud=="tar")echo
pack("x1024");}}}}adminer()->dumpFooter();exit;}page_header(lang(83),$l,($_GET["export"]!=""?array("table"=>$_GET["export"]):array()),h(DB));echo'
<form action="" method="post">
<table class="layout">
';$kc=array('','USE','DROP+CREATE','CREATE');$kl=array('','DROP+CREATE','CREATE');$gc=array('','TRUNCATE+INSERT','INSERT');if(JUSH=="sql")$gc[]='INSERT+UPDATE';$I=get_settings("adminer_export");if(!$I)$I=array("output"=>"text","format"=>"sql","db_style"=>(DB!=""?"":"CREATE"),"table_style"=>"DROP+CREATE","data_style"=>"INSERT");echo"<tr><th>".lang(167)."<td>".html_radios("output",adminer()->dumpOutput(),$I["output"])."\n","<tr><th>".lang(168)."<td>".html_radios("format",adminer()->dumpFormat(),$I["format"])."\n",(JUSH=="sqlite"?"":"<tr><th>".lang(42)."<td>".html_select('db_style',$kc,$I["db_style"]).(support("type")?checkbox("types",1,$I["types"],lang(7)):"").(support("routine")?checkbox("routines",1,$I["routines"],lang(79)):"").(support("event")?checkbox("events",1,$I["events"],lang(81)):"")),"<tr><th>".lang(142)."<td>".html_select('table_style',$kl,$I["table_style"]).checkbox("auto_increment",1,$I["auto_increment"],lang(58)).(support("trigger")?checkbox("triggers",1,$I["triggers"],lang(162)):""),"<tr><th>".lang(169)."<td>".html_select('data_style',$gc,$I["data_style"]),'</table>
';adminer()->dumpPrint();echo'<p><input type=\'submit\' value=\'',lang(83),'\'>
',input_token(),'
<table',on('click','dumpClick'),'>
';$Vi=array();if($_GET["ns"]===""){echo"<thead><tr><th style='text-align: left;'>","<label class='block'><input type='checkbox' id='check-schemas' checked class='jsonly' title='".lang(170)."'".on('click','formCheck','^schemas\[').">".lang(86)."</label>","<tbody>\n";foreach(adminer()->schemas()as$K){if(!information_schema(DB,$K))echo"<tr><td>".checkbox("schemas[]",$K,true,$K,"","block")."\n";}}elseif(DB!=""){$nb=($a!=""?"":" checked");echo"<thead><tr>","<th style='text-align: left;'><label class='block'><input type='checkbox' id='check-tables'$nb class='jsonly' title='".lang(170)."'".on('click','formCheck','^tables\[').">".lang(152)."</label>","<th style='text-align: right;'><label class='block'>".lang(169)."<input type='checkbox' id='check-data'$nb class='jsonly' title='".lang(170)."'".on('click','formCheck','^data\[')."></label>","<tbody>\n";$Im="";$nl=tables_list();foreach($nl
as$B=>$T){$Ui=preg_replace('~_.*~','',$B);$nb=($a==""||$a==(substr($a,-1)=="%"?"$Ui%":$B));$bj="<tr><td>".checkbox("tables[]",$B,$nb,$B,"","block");if($T!==null&&!preg_match('~table~i',$T))$Im
.="$bj\n";else
echo"$bj<td align='right'><label class='block'><span id='Rows-".h($B)."'></span>".checkbox("data[]",$B,$nb)."</label>\n";$Vi[$Ui]++;}echo$Im;if($nl)echo
script("ajaxSetHtml('".js_escape(ME)."script=db');");}else{$i=adminer()->databases();echo"<thead><tr><th style='text-align: left;'>","<label class='block'>".($i?"<input type='checkbox' id='check-databases'".($a==""?" checked":"")." class='jsonly' title='".lang(170)."'".on('click','formCheck','^databases\[').">":"").lang(42)."</label>","<tbody>\n";if($i){foreach($i
as$j){if(!information_schema($j)){$Ui=preg_replace('~_.*~','',$j);echo"<tr><td>".checkbox("databases[]",$j,$a==""||$a=="$Ui%",$j,"","block")."\n";$Vi[$Ui]++;}}}else
echo"<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";}echo'</table>
</form>
';$Md=true;foreach($Vi
as$x=>$W){if($x!=""&&$W>1){echo($Md?"<p>":" ")."<a href='".h(ME)."dump=".url_escape("$x%")."'>".h($x)."</a>";$Md=false;}}}elseif(isset($_GET["privileges"])){page_header(lang(78));echo'<p class="links"><a href="'.h(ME).'user=">'.lang(171)."</a>";$G=connection()->query("SELECT User, Host FROM mysql.".(DB==""?"user":"db WHERE ".q(DB)." LIKE Db")." ORDER BY Host, User");$me=$G;if(!$G)$G=connection()->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");echo"<form action=''><p>\n";hidden_fields_get();echo
input_hidden("db",DB),($me?"":input_hidden("grant")),"<table class='odds'>\n","<thead><tr><th>".lang(40)."<th>".lang(38)."<td class='hover'><tbody>\n";while($I=$G->fetch_assoc())echo'<tr><td>'.h($I["User"]),"<td>".h($I["Host"]),'<td class="hover"><a href="'.h(ME.'user='.url_escape($I["User"]).'&host='.url_escape($I["Host"])).'">'.lang(13)."</a>\n";if(!$me||DB!="")echo"<tr><td><input name='user' autocapitalize='off'>","<td><input name='host' value='localhost' autocapitalize='off'>","<td class='hover'><input type='submit' value='".lang(13)."'>\n";echo"</table>\n","</form>\n";}elseif(isset($_GET["sql"])){if(!$l&&$_POST["export"]){save_settings(array("output"=>$_POST["output"],"format"=>$_POST["format"]),"adminer_import");dump_headers("sql");if($_POST["format"]=="sql")echo"$_POST[query]\n";else{adminer()->dumpTable("","");adminer()->dumpData("","table",$_POST["query"]);adminer()->dumpFooter();}exit;}restart_session();$Le=&get_session("queries");$Ke=&$Le[DB];if(!$l&&$_POST["clear"]){$Ke=array();redirect(remove_from_uri("history"));}stop_session();$ra=get_settings("adminer_import");if($_POST&&$ra)save_settings($ra,"adminer_import");page_header((isset($_GET["import"])?lang(82):lang(71)),$l);$gg=driver()->lineComment();if(!$l&&$_POST&&!(isset($_GET["import"])&&adminer()->importProcess())){$tc=driver()->delimiter;$ae=false;if(!isset($_GET["import"]))$F=$_POST["query"];elseif($_POST["webfile"]){$Hk=adminer()->importServerPath();$ae=@fopen((file_exists($Hk)?$Hk:"compress.zlib://$Hk.gz"),"rb");$F=($ae?fread($ae,1e6):false);}else$F=get_file("sql_file",true,$tc);if(is_string($F)){if(($Gg=ini_bytes("memory_limit"))!="-1")ini_set("memory_limit",max($Gg,strval(2*strlen($F)+memory_get_usage()+8e6)));if($F!=""&&strlen($F)<1e6){$ij=$F.(preg_match("~$tc\\s*\$~",$F)?"":$tc);if(!$Ke||first(end($Ke))!=$ij){restart_session();$Ke[]=array($ij,time());set_session("queries",$Le);stop_session();}}$Ek="(?:\\s|/\\*[\s\S]*?\\*/|(?:$gg)[^\n]*\n?|--\r?\n)";$Ah=0;$Zc=true;$Ub=false;$g=connect();if($g&&DB!=""){$g->select_db(DB);if($_GET["ns"]!="")set_schema($_GET["ns"],$g);}$Bb=0;$gd=array();$pi='[\'"'.(JUSH=="sql"?'`':(JUSH=="sqlite"?'`[':(JUSH=="mssql"?'[':''))).']|/\*|'.$gg.'|$'.(JUSH=="pgsql"?'|\$([a-zA-Z]\w*)?\$':'');$Jl=microtime(true);while($F!=""){if(!$Ah&&preg_match("~^$Ek*+DELIMITER\\s+(\\S+)~i",$F,$A)){$tc=preg_quote($A[1]);$F=substr($F,strlen($A[0]));}elseif(!$Ah&&JUSH=='pgsql'&&preg_match("~^($Ek*+COPY\\s+)[^;]+\\s+FROM\\s+stdin;~i",$F,$A)){$tc="\n\\\\\\.\r?\n";$Ub=true;$Ah=strlen($A[0]);}else{preg_match("($tc\\s*|$pi)",$F,$A,PREG_OFFSET_CAPTURE,$Ah);list($Yd,$Ni)=$A[0];if(!$Yd&&$ae&&!feof($ae))$F
.=fread($ae,1e5);else{if(!$Yd&&rtrim($F)=="")break;$Ah=$Ni+strlen($Yd);if($Yd&&!preg_match("(^$tc)",$Yd)){$eb=driver()->hasCStyleEscapes()||(JUSH=="pgsql"&&($Ni>0&&strtolower($F[$Ni-1])=="e"));$Ei=($Yd=='/*'?'\*/':($Yd=='['?']':(preg_match("~^(?:$gg)~",$Yd)?"\n":preg_quote($Yd).($eb?'|\\\\.':''))));while(preg_match("($Ei|\$)s",$F,$A,PREG_OFFSET_CAPTURE,$Ah)){$Oj=$A[0][0];if(!$Oj&&$ae&&!feof($ae))$F
.=fread($ae,1e5);else{$Ah=$A[0][1]+strlen($Oj);if(!$Oj||$Oj[0]!="\\")break;}}}else{$ij=substr($F,0,$Ni+($Ub?3:0));$F=substr($F,$Ah);$Ah=0;if($Ub){$tc=driver()->delimiter;$Ub=false;}$ub="<code class='jush-".JUSH."'>".adminer()->sqlCommandQuery($ij)."</code>";if(preg_match("~^$Ek*+\$~",$ij)&&!preg_match('~/\*M?!~',$ij)){echo($_POST["only_errors"]?"":"<pre>$ub</pre>\n");continue;}$Zc=false;$Bb++;$bj="<pre id='sql-$Bb'>$ub</pre>\n";if(JUSH=="sqlite"&&preg_match("~^$Ek*+(ATTACH|VACUUM\\b.*\\bINTO)\\b~is",$ij,$A)!==0){echo$bj,"<p class='error'>".lang(172,preg_match('~ATTACH~i',$A[1])?'ATTACH':'VACUUM INTO')."\n";$gd[]=" <a href='#sql-$Bb'>$Bb</a>";if($_POST["error_stops"])break;}else{if(!$_POST["only_errors"]){echo$bj;ob_flush();flush();}$Nk=microtime(true);if(connection()->multi_query($ij)&&$g&&preg_match("~^$Ek*+USE\\b~i",$ij))$g->query($ij);do{$G=connection()->store_result();if(connection()->error){echo($_POST["only_errors"]?$bj:""),"<p class='error'>".lang(173).(connection()->errno?" (".connection()->errno.")":"").": ".adminer()->error()."\n";$gd[]=" <a href='#sql-$Bb'>$Bb</a>";if($_POST["error_stops"])break
2;}else{$_=ME."sql=".url_escape(trim($ij));$zl=" <span class='time'>(".format_time($Nk).")</span>".(strlen($_)<1900?" <a href='".h($_)."'>".lang(13)."</a>":"");$ta=connection()->affected_rows;$Mm=($_POST["only_errors"]?"":driver()->warnings());$Nm="warnings-$Bb";if($Mm)$zl
.=", <a href='#$Nm' class='toggle'>".lang(53)."</a>";$rd=null;$Yh=null;$sd="explain-$Bb";if(is_object($G)){$z=$_POST["limit"];$th=$z;$Yh=print_select_result($G,$g,array(),$th);if(!$_POST["only_errors"]){echo"<form action='' method='post'>\n";$th=max($G->num_rows,$th);echo"<p class='sql-footer'>".($th?($z&&$th>$z?lang(174,$z):"").lang(175,$th):""),$zl;if($g&&preg_match("~^($Ek|\\()*+SELECT\\b~i",$ij)&&($rd=explain($g,$ij)))echo", <a href='#$sd' class='toggle'>Explain</a>";$t="export-$Bb";echo", <a href='#$t' class='toggle'>".lang(83)."</a><span id='$t' class='hidden'>: ".html_select("output",adminer()->dumpOutput(),$ra["output"])." ".html_select("format",adminer()->dumpFormat(),$ra["format"]).input_hidden("query",$ij)."<input type='submit' name='export' value='".lang(83)."'".($z?"":on('click','sqlExport')).">".input_token()."</span>\n"."</form>\n";}}else{if(preg_match("~^$Ek*+(CREATE|DROP|ALTER)$Ek++(DATABASE|SCHEMA)\\b~i",$ij)){restart_session();set_session("dbs",null);stop_session();}if(!$_POST["only_errors"])echo"<p class='message' title='".h(connection()->info)."'>".lang(176,$ta)."$zl\n";}echo($Mm?"<div id='$Nm' class='hidden'>\n$Mm</div>\n":"");if($rd){echo"<div id='$sd' class='hidden explain'>\n";print_select_result($rd,$g,$Yh);echo"</div>\n";}}$Nk=microtime(true);}while(connection()->next_result());}}}}}if($Zc)echo"<p class='message'>".lang(177)."\n";else{$Ze=connection()->inTransaction();driver()->rollback();if($Ze)echo"<pre><code class='jush-".JUSH."'>ROLLBACK -- Adminer</code></pre>\n";if($_POST["only_errors"])echo"<p class='message'>".lang(178,$Bb-count($gd))," <span class='time'>(".format_time($Jl).")</span>\n";elseif($gd&&$Bb>1)echo"<p class='error'>".lang(173).": ".implode("",$gd)."\n";}}else
echo"<p class='error'>".upload_error($F)."\n";}echo'
<form action="" method="post" enctype="multipart/form-data" id="form"';$om="";if(!isset($_GET["import"]))echo
on('submit','sqlSubmit',remove_from_uri("sql|limit|error_stops|only_errors|history"));else
echo
on_upload_progress($om);echo'>
';$od="<input type='submit' value='".lang(179)."' title='Ctrl+Enter'>";if(!isset($_GET["import"])){$ij=$_GET["sql"];if($_POST)$ij=$_POST["query"];elseif($_GET["history"]=="all")$ij=$Ke;elseif($_GET["history"]!="")$ij=idx($Ke[$_GET["history"]],0);echo"<p>";textarea("query",$ij,20);echo($_POST?"":script("qs('textarea').focus();")),"<p>";adminer()->sqlPrintAfter();echo"$od\n",lang(180).": <input type='number' name='limit' class='size' value='".h($_POST?$_POST["limit"]:$_GET["limit"])."'>\n";}else{$ve=(extension_loaded("zlib")?"[.gz]":"");echo"<fieldset><legend>".lang(181)."</legend><div>",($om?input_hidden(ini_get("session.upload_progress.name"),$om):""),"SQL$ve: ".file_input(" name='sql_file[]' multiple","\n$od"),($om?" <progress class='jsonly hidden' max='1' value='0'></progress>":""),"</div></fieldset>\n";$We=adminer()->importServerPath();if($We)echo"<fieldset><legend>".lang(182)."</legend><div>",lang(183,"<code>".h($We)."$ve</code>")," <input type='submit' name='webfile' value='".lang(184)."'>","</div></fieldset>\n";adminer()->importPrint();echo"<p>";}echo
checkbox("error_stops",1,($_POST?$_POST["error_stops"]:isset($_GET["import"])||$_GET["error_stops"]),lang(185))."\n",checkbox("only_errors",1,($_POST?$_POST["only_errors"]:isset($_GET["import"])||$_GET["only_errors"]),lang(186))."\n",input_token();if(!isset($_GET["import"])&&$Ke){print_fieldset("history",lang(187),$_GET["history"]!="");for($W=end($Ke);$W;$W=prev($Ke)){$x=key($Ke);list($ij,$zl,$Vc)=$W;echo'<div><a href="'.h(ME."sql=&history=$x").'" class="hover">'.lang(13)."</a>"." <span class='time' title='".@date('Y-m-d',$zl)."'>".@date("H:i:s",$zl)."</span>"." <code class='jush-".JUSH."'>".shorten_utf8(preg_replace('~\s+~',' ',ltrim(preg_replace("~^(?:$gg).*~m",'',$ij))),80,"</code>").($Vc?" <span class='time'>($Vc)</span>":"")."</div>\n";}echo"<input type='submit' name='clear' value='".lang(188)."'>\n","<a href='".h(ME."sql=&history=all")."'>".lang(189)."</a>\n","</div></fieldset>\n";}echo'</form>
';}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$n=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$n):""):where($_GET,$n));$mm=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($n
as$B=>$m){if((!$mm&&!isset($m["privileges"]["insert"]))||adminer()->fieldName($m)=="")unset($n[$B]);}if($_POST&&!$l&&!isset($_GET["select"])){$lg=relative_uri((string)$_POST["referer"]);if($_POST["insert"])$lg=($mm?null:relative_uri());elseif(!preg_match('~^.+&select=.+$~',$lg))$lg=ME."select=".url_escape($a);$w=indexes($a);$fm=unique_array($_GET["where"],$w);$lj="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($lg,lang(190),driver()->delete($a,$lj,$fm?0:1));else{$N=array();foreach($n
as$B=>$m){$W=process_input($m);if($W!==false&&$W!==null)$N[idf_escape($B)]=$W;}if($mm){if(!$N)redirect($lg);queries_redirect($lg,lang(191),driver()->update($a,$N,$lj,$fm?0:1));if(is_ajax()){page_headers();page_messages($l);exit;}}else{$G=driver()->insert($a,$N);$Wf=($G?last_id($G):0);queries_redirect($lg,lang(192,($Wf?" $Wf":"")),$G);}}}$I=null;$F="";$zl="";if($Z){$L=array();$Yj=array("*");foreach($n
as$B=>$m){if(isset($m["privileges"]["select"])){$Ea=($_POST["clone"]&&$m["auto_increment"]?"''":convert_field($m));$d=($Ea?"$Ea AS ":"").idf_escape($B);$L[]=$d;if($Ea)$Yj[]=$d;}}$I=array();if(!support("table")){$L=array("*");$Yj=$L;}if($L){$Nk=microtime(true);$G=driver()->select($a,$L,array($Z),$L,array(),(isset($_GET["select"])?2:1));$F=str_replace("SELECT ".implode(", ",$L),"SELECT ".implode(", ",$Yj),driver()->query);$zl=format_time($Nk);if(!$G)$l=adminer()->error();else{$I=$G->fetch_assoc();if(!$I)$I=false;}if(isset($_GET["select"])&&(!$I||$G->fetch_assoc()))$I=null;}}if(!$n&&driver()->primary!=""){if(!$Z){$G=driver()->select($a,array("*"),array(),array("*"));$I=($G?$G->fetch_assoc():false);if(!$I)$I=array(driver()->primary=>"");}if($I){foreach($I
as$x=>$W){if(!$Z)$I[$x]=null;$n[$x]=array("field"=>$x,"null"=>($x!=driver()->primary),"auto_increment"=>($x==driver()->primary));}}}if($_POST["save"]){$Qi=array();foreach((array)$_POST["fields"]as$x=>$W)$Qi[bracket_escape($x,true)]=$W;$I=$Qi+($I?$I:array());}edit_form($a,$n,$I,$mm,$l,$F,$zl);}elseif(isset($_GET["create"])){function
referencable_primary($bk){$H=array();foreach(table_status('',true)as$el=>$Q){if($el!=$bk&&!$Q["dependent"]&&fk_support($Q)){foreach(fields($el)as$m){if($m["primary"]){if($H[$el]){unset($H[$el]);break;}$H[$el]=$m;}}}}return$H;}$a=$_GET["create"];$ui=driver()->partitionBy;$yi=($ui&&$a!=""?driver()->partitionsInfo($a):array());$sj=referencable_primary($a);$Wd=array();foreach($sj
as$el=>$m)$Wd[str_replace("`","``",$el)."`".str_replace("`","``",$m["field"])]=$el;$bi=array();$R=array();if($a!=""){$bi=fields($a);$R=table_status1($a);if(count($R)<2)$l=lang(12);}$za=($a==""||driver()->supportsAlterTable($R));$I=$_POST;$I["fields"]=(array)$I["fields"];if($I["auto_increment_col"])$I["fields"][$I["auto_increment_col"]]["auto_increment"]=true;if($_POST&&!$l)save_settings(array("comments"=>$_POST["comments"],"defaults"=>$_POST["defaults"]));if($_POST&&!process_fields($I["fields"])&&!$l){if($_POST["drop"])queries_redirect(substr(ME,0,-1),lang(193),drop_tables(array($a)));else{$n=array();$xa=array();$sm=false;$Ud=array();$ai=reset($bi);$va=" FIRST";foreach($I["fields"]as$m){$p=$Wd[$m["type"]];$Yl=($p!==null?$sj[$p]:$m);if($m["field"]!=""){if(!$m["generated"])$m["default"]=null;$gj=process_field($m,$Yl);$xa[]=array($m["orig"],$gj,$va);if(!$ai||$gj!==process_field($ai,$ai)){$n[]=array($m["orig"],$gj,$va);if($m["orig"]!=""||$va)$sm=true;}if($p!==null)$Ud[idf_escape($m["field"])]=($a!=""&&JUSH!="sqlite"?"ADD":" ").format_foreign_key(array('table'=>$Wd[$m["type"]],'source'=>array($m["field"]),'target'=>array($Yl["field"]),'on_delete'=>$m["on_delete"],));$va=" AFTER ".idf_escape($m["field"]);}elseif($m["orig"]!=""){$sm=true;$n[]=array($m["orig"]);}if($m["orig"]!=""){$ai=next($bi);if(!$ai)$va="";}}$wi=array();if(in_array($I["partition_by"],$ui)){foreach($I
as$x=>$W){if(preg_match('~^partition~',$x))$wi[$x]=$W;}foreach($wi["partition_names"]as$x=>$B){if($B==""){unset($wi["partition_names"][$x]);unset($wi["partition_values"][$x]);}}$wi["partition_names"]=array_values($wi["partition_names"]);$wi["partition_values"]=array_values($wi["partition_values"]);if($wi==$yi)$wi=array();}elseif(preg_match("~partitioned~",$R["Create_options"]))$wi=null;$Ig=lang(194);if($a==""){cookie("adminer_engine",$I["Engine"]);$Ig=lang(195);}$B=trim($I["name"]);$lg=ME.(support("table")?"table=":"select=").url_escape($B);$G=alter_table($a,$B,(JUSH=="sqlite"&&($sm||$Ud)?$xa:$n),$Ud,($I["Comment"]!=$R["Comment"]?$I["Comment"]:null),($I["Engine"]&&$I["Engine"]!=$R["Engine"]?$I["Engine"]:""),($I["Collation"]&&$I["Collation"]!=$R["Collation"]?$I["Collation"]:""),($I["Auto_increment"]!=""?number($I["Auto_increment"]):""),$wi);if($G&&!Queries::$queries&&$a!=""&&!$n&&!$Ud)redirect($lg);queries_redirect($lg,$Ig,$G);}}page_header(($a!=""?lang(51):lang(84)),$l,array("table"=>$a),h($a));if(!$_POST){$bm=driver()->types();$I=array("Engine"=>$_COOKIE["adminer_engine"],"fields"=>array(array("field"=>"","type"=>(isset($bm["int"])?"int":(isset($bm["integer"])?"integer":"")),"on_update"=>"")),"partition_names"=>array(""),);if($a!=""){$I=$R;$I["name"]=$a;$I["fields"]=array();if(!$_GET["auto_increment"])$I["Auto_increment"]="";foreach($bi
as$m){if($m["generated"])$m["default"]=ltrim($m["default"]);$m["generated"]=$m["generated"]?:(isset($m["default"])?"DEFAULT":"");$I["fields"][]=$m;}if($ui){$I+=$yi;$I["partition_names"][]="";$I["partition_values"][]="";}}}$yb=flat_collations();$bd=driver()->engines();foreach($bd
as$ad){if(!strcasecmp($ad,$I["Engine"])){$I["Engine"]=$ad;break;}}$ug=max_input_vars(12,20);if($ug){$Je=(count($I["fields"])>$ug?"":" hidden");echo"<p".($Je?" id='max-fields' data-columns='$ug'":"")." class='error$Je'>".max_input_vars_error()."\n";}echo'
<form action="" method="post" id="form">
<p>
';if(support("columns")||$a==""){echo
lang(196).": <input name='name'".($a==""&&!$_POST?" autofocus":"")." data-maxlength='64' value='".h($I["name"])."' autocapitalize='off'>\n",(!$za?h($R["Engine"])."\n":($bd?html_select("Engine",array(""=>"(".lang(197).")")+$bd,$I["Engine"],on('change','helpClose').on_help_value())."\n":""));if($yb)echo"<datalist id='collations'>".optionlist($yb)."</datalist>\n",(preg_match("~sqlite|mssql~",JUSH)?"":"<input list='collations' name='Collation' value='".h($I["Collation"])."' placeholder='(".lang(121).")'>\n");echo"<input type='submit' value='".lang(17)."'>\n";}if(support("columns")&&$za){echo"<div class='scrollable'>\n","<table id='edit-fields' class='nowrap'>\n";edit_fields($I["fields"],$yb,"TABLE",$Wd);echo"</table>\n",script("editFields();"),"</div>\n<p>\n",lang(58).": <input type='number' name='Auto_increment' class='size' value='".h($I["Auto_increment"])."'>\n",checkbox("defaults",1,($_POST?$_POST["defaults"]:get_setting("defaults")),lang(198),on('click','columnShowClick',5),"jsonly");$Eb=($_POST?$_POST["comments"]:get_setting("comments"));if(support("comment")){echo
checkbox("comments",1,$Eb,lang(57),on('click','editingCommentsClick',true),"jsonly").' ';$c=" name='Comment' data-maxlength='".(min_version(5.5)?2048:60)."'".($Eb?"":" class='hidden'");echo
adminer()->commentInput('TABLE',$c,$I["Comment"]);}echo'<p>
<input type=\'submit\' value=\'',lang(17),'\'>
';}echo'
';if($a!="")echo'<input type=\'submit\' name=\'drop\' value=\'',lang(146),'\'',confirm(lang(199,$a)),'>
';if($ui&&(JUSH=='sql'||$a=="")){$vi=preg_match('~RANGE|LIST~',$I["partition_by"]);print_fieldset("partition",lang(200),$I["partition_by"]);echo"<p>".html_select("partition_by",array_merge(array(""),$ui),$I["partition_by"],on('change','partitionByChange').on_help_value('.','PARTITION BY $&'))."\n","(<input name='partition' value='".h($I["partition"])."'>)\n",lang(201).": <input type='number' name='partitions' class='size".($vi||!$I["partition_by"]?" hidden":"")."' value='".h($I["partitions"])."'>\n","<table id='partition-table'".($vi?"":" class='hidden'").">\n","<thead><tr><th>".lang(202)."<th>".lang(203)."<tbody>\n";foreach($I["partition_names"]as$x=>$W)echo'<tr>','<td><input name="partition_names[]" value="'.h($W).'" autocapitalize="off"'.($x==count($I["partition_names"])-1?on('input','partitionNameChange'):'').'>','<td><input name="partition_values[]" value="'.h(idx($I["partition_values"],$x)).'">';echo"</table>\n</div></fieldset>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["indexes"])){$a=$_GET["indexes"];$ff=array("PRIMARY","UNIQUE","INDEX");$R=table_status1($a,true);$cf=driver()->indexAlgorithms($R);if(preg_match('~MyISAM|M?aria'.(min_version(5.6,'10.0.5')?'|InnoDB':'').'~i',$R["Engine"]))$ff[]="FULLTEXT";if(preg_match('~MyISAM|M?aria'.(min_version(5.7,'10.2.2')?'|InnoDB':'').'~i',$R["Engine"]))$ff[]="SPATIAL";if(min_version('',11.7)&&preg_match('~MyISAM|InnoDB~i',$R["Engine"]))$ff[]="VECTOR";$w=indexes($a);$n=fields($a);$Zi=array();if(JUSH=="mongo"){$Zi=$w["_id_"];unset($ff[0]);unset($w["_id_"]);}$I=$_POST;if($I)save_settings(array("index_options"=>$I["options"]));if($_POST&&!$l&&!$_POST["add"]&&!$_POST["drop_col"]){$b=array();foreach($I["indexes"]as$v){$B=$v["name"];if(in_array($v["type"],$ff)){$e=array();$dg=array();$wc=array();$Nh=array();$df=(support("partial_indexes")?$v["partial"]:"");$bf=(in_array($v["algorithm"],$cf)?$v["algorithm"]:"");$N=array();ksort($v["columns"]);foreach($v["columns"]as$x=>$d){if($d!=""){$y=idx($v["lengths"],$x);$uc=idx($v["descs"],$x);$Mh=idx($v["opclasses"],$x);$N[]=($n[$d]?idf_escape($d):$d).($y?"(".(+$y).")":"").($Mh!=""?" ".idf_escape($Mh):"").($uc?" DESC":"");$e[]=$d;$dg[]=($y?:null);$wc[]=$uc;$Nh[]="$Mh";}}$pd=$w[$B];if($pd){ksort($pd["columns"]);ksort($pd["lengths"]);ksort($pd["descs"]);if($v["type"]==$pd["type"]&&array_values($pd["columns"])===$e&&(!$pd["lengths"]||array_values($pd["lengths"])===$dg)&&array_values($pd["descs"])===$wc&&(!$pd["opclasses"]||array_values($pd["opclasses"])===$Nh)&&$pd["partial"]==$df&&(!$cf||$pd["algorithm"]==$bf)){unset($w[$B]);continue;}}if($e)$b[]=array($v["type"],$B,$N,$bf,$df);}}foreach($w
as$B=>$pd)$b[]=array($pd["type"],$B,"DROP");if(!$b)redirect(ME."table=".url_escape($a));queries_redirect(ME."table=".url_escape($a),lang(204),alter_indexes($a,$b));}page_header(lang(154),$l,array("table"=>$a),h($a));$Fd=array_keys($n);if($_POST["add"]){foreach($I["indexes"]as$x=>$v){if($v["columns"][count($v["columns"])]!="")$I["indexes"][$x]["columns"][]="";}$v=end($I["indexes"]);if($v["type"]||array_filter($v["columns"],'strlen'))$I["indexes"][]=array("columns"=>array(1=>""));}if(!$I){foreach($w
as$x=>$v){$w[$x]["name"]=$x;$w[$x]["columns"][]="";}$w[]=array("columns"=>array(1=>""));$I["indexes"]=$w;}$dg=(JUSH=="sql"||JUSH=="mssql");$Nh=driver()->indexOpclasses();$uk=($_POST?$_POST["options"]:get_setting("index_options"));echo'
<form action="" method="post">
<div class="scrollable">
<table class="nowrap odds">
<thead><tr>
<th id="label-type">',lang(205);$Ue=" class='idxopts".($uk?"":" hidden")."'";if($cf)echo"<th id='label-algorithm'$Ue>".lang(206).doc_link(array('sql'=>'create-index.html#create-index-storage-engine-index-types','mariadb'=>'storage-engine-index-types/','pgsql'=>'indexes-types.html',));echo'<th><input type="submit" hidden>',lang(207).($dg?"<span$Ue> (".lang(208).")</span>":"");if($dg||support("descidx"))echo
checkbox("options",1,$uk,lang(127),on('click','indexOptionsShow'),"jsonly")."\n";echo'<th id="label-name">',lang(209);if(support("partial_indexes"))echo"<th id='label-condition'$Ue>".lang(210);echo'<th><noscript>',icon("plus","add[0]","+",lang(128)),'</noscript>
<tbody>
';if($Zi){echo"<tr><td>PRIMARY<td>";foreach($Zi["columns"]as$x=>$d)echo
select_input(" disabled",array_combine($Fd,$Fd),$d),"<label><input disabled type='checkbox'>".lang(66)."</label> ";echo"<td><td>\n";}$Ff=1;foreach($I["indexes"]as$v){if(!$_POST["drop_col"]||$Ff!=key($_POST["drop_col"])){echo"<tr><td>".html_select("indexes[$Ff][type]",array(-1=>"")+$ff,$v["type"],($Ff==count($I["indexes"])?on('change','indexesAddRow'):""),"label-type");if($cf)echo"<td$Ue>".html_select("indexes[$Ff][algorithm]",array_merge(array(""),$cf),$v['algorithm'],"","label-algorithm");echo"<td>";ksort($v["columns"]);$s=1;foreach($v["columns"]as$x=>$d){echo"<span>".select_input(" name='indexes[$Ff][columns][$s]' title='".lang(55)."'".on('change','indexesChangeColumn',(JUSH=="sql"?"":$_GET["indexes"]."_")),($n&&($d==""||$n[$d])?array_combine($Fd,$Fd):array()),$d)," <span$Ue>",($dg?"<input type='number' name='indexes[$Ff][lengths][$s]' class='size' value='".h(idx($v["lengths"],$x))."' title='".lang(126)."'>":"");if($Nh){$Mh=idx($v["opclasses"],$x);echo
html_select("indexes[$Ff][opclasses][$s]",array(""=>"(".lang(211).")")+array_combine($Nh,$Nh)+($Mh!=""?array($Mh=>$Mh):array()),$Mh),doc_link(array('pgsql'=>'indexes-opclass.html'));}echo(support("descidx")?checkbox("indexes[$Ff][descs][$s]",1,idx($v["descs"],$x),lang(66)):""),"<br>","</span></span>";$s++;}echo"<td><input name='indexes[$Ff][name]' value='".h($v["name"])."' autocapitalize='off' aria-labelledby='label-name'>\n";if(support("partial_indexes"))echo"<td$Ue><input name='indexes[$Ff][partial]' value='".h($v["partial"])."' autocapitalize='off' aria-labelledby='label-condition'>\n";echo"<td>".icon("cross","drop_col[$Ff]","x",lang(130),on('click','editingRemoveRow','indexes$1[type]'));}$Ff++;}echo'</table>
</div>
<p>
<input type=\'submit\' value=\'',lang(17),'\'>
',input_token(),'</form>
';}elseif(isset($_GET["database"])){$I=$_POST;if($_POST&&!$l&&!$_POST["add"]){$B=trim($I["name"]);if($_POST["drop"]){$_GET["db"]="";queries_redirect(remove_from_uri("db|database"),lang(212),drop_databases(array(DB)));}elseif($B!==DB){if(DB!=""){$_GET["db"]=$B;queries_redirect(preg_replace('~\bdb=[^&]*&~','',ME)."db=".url_escape($B),lang(213),rename_database($B,(string)$I["collation"]));}else{$i=explode("\n",str_replace("\r","",$B));$Tk=true;$Uf="";foreach($i
as$j){if(count($i)==1||$j!=""){if(!create_database($j,(string)$I["collation"]))$Tk=false;$Uf=$j;}}restart_session();set_session("dbs",null);queries_redirect(preg_replace('~&db=[^&]*~','',ME)."db=".url_escape($Uf),lang(214),$Tk);}}else{if(!$I["collation"])redirect(substr(ME,0,-1));query_redirect("ALTER DATABASE ".idf_escape($B).(preg_match('~^[a-z0-9_]+$~i',$I["collation"])?" COLLATE $I[collation]":""),substr(ME,0,-1),lang(215));}}page_header(DB!=""?lang(74):lang(134),$l,array(),h(DB));$yb=collations();$B=DB;if($_POST)$B=$I["name"];elseif(DB!="")$I["collation"]=db_collation(DB,$yb);elseif(JUSH=="sql"){foreach(get_vals("SHOW GRANTS")as$me){if(preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~',$me,$A)&&$A[1]){$B=stripcslashes(idf_unescape("`$A[2]`"));break;}}}echo'
<form action="" method="post">
<p>
',($_POST["add"]||strpos($B,"\n")?'<textarea autofocus name="name" rows="10" cols="40">'.h($B).'</textarea><br>':'<input name="name" autofocus value="'.h($B).'" data-maxlength="64" autocapitalize="off">')."\n",($yb?html_select("collation",array(""=>"(".lang(121).")")+$yb,$I["collation"]).doc_link(array('sql'=>"charset-charsets.html",'mariadb'=>"supported-character-sets-and-collations/",'mssql'=>"relational-databases/system-functions/sys-fn-helpcollations-transact-sql",)):"")."\n",'<input type=\'submit\' value=\'',lang(17),'\'>
';if(DB!="")echo"<input type='submit' name='drop' value='".lang(146)."'".confirm(lang(199,DB)).">\n";elseif(!$_POST["add"]&&$_GET["db"]=="")echo
icon("plus","add[0]","+",lang(128))."\n";echo
input_token(),'</form>
';}elseif(isset($_GET["scheme"])){$I=$_POST;if($_POST&&!$l){$_=preg_replace('~ns=[^&]*&~','',ME)."ns=";if($_POST["drop"])query_redirect("DROP SCHEMA ".idf_escape($_GET["ns"]),$_,lang(216));else{$B=trim($I["name"]);$_
.=url_escape($B);if($_GET["ns"]=="")query_redirect("CREATE SCHEMA ".idf_escape($B),$_,lang(217));elseif($_GET["ns"]!=$B)query_redirect("ALTER SCHEMA ".idf_escape($_GET["ns"])." RENAME TO ".idf_escape($B),$_,lang(218));else
redirect($_);}}page_header($_GET["ns"]!=""?lang(75):lang(76),$l);if(!$I)$I["name"]=$_GET["ns"];echo'
<form action="" method="post">
<p><input name="name" autofocus value="',h($I["name"]),'" autocapitalize="off">
<input type=\'submit\' value=\'',lang(17),'\'>
';if($_GET["ns"]!="")echo"<input type='submit' name='drop' value='".lang(146)."'".confirm(lang(199,$_GET["ns"])).">\n";echo
input_token(),'</form>
';}elseif(isset($_GET["call"])){$ca=($_GET["name"]?:$_GET["call"]);page_header(lang(219).": ".h($ca),$l);$Lj=(isset($_GET["callf"])?"FUNCTION":"PROCEDURE");$Ij=routine($_GET["call"],$Lj);$Xe=array();$hi=array();foreach($Ij["fields"]as$s=>$m){if(substr($m["inout"],-3)=="OUT"&&JUSH=='sql')$hi[$s]="@".idf_escape($m["field"])." AS ".idf_escape($m["field"]);if(!$m["inout"]||substr($m["inout"],0,2)=="IN")$Xe[]=$s;}if(!$l&&$_POST){$fb=array();foreach($Ij["fields"]as$x=>$m){$W="";if(in_array($x,$Xe)){$W=process_input($m);if($W===false)$W="''";if(isset($hi[$x]))connection()->query("SET @".idf_escape($m["field"])." = $W");}if(isset($hi[$x]))$fb[]="@".idf_escape($m["field"]);elseif(in_array($x,$Xe))$fb[]=$W;}$F=(isset($_GET["callf"])?"SELECT ":"CALL ").(idx($Ij["returns"],"type")=="record"?"* FROM ":"").table($ca)."(".implode(", ",$fb).")";$Nk=microtime(true);$G=connection()->multi_query($F);$ta=connection()->affected_rows;echo
adminer()->selectQuery($F,$Nk,!$G);if(!$G)echo"<p class='error'>".adminer()->error()."\n";else{$g=connect();if($g)$g->select_db(DB);do{$G=connection()->store_result();if(is_object($G))print_select_result($G,$g);else
echo"<p class='message'>".lang(220,$ta)." <span class='time'>".@date("H:i:s")."</span>\n";}while(connection()->next_result());if($hi)print_select_result(connection()->query("SELECT ".implode(", ",$hi)));}}echo'
<form action="" method="post">
';if($Xe){echo"<table class='layout'>\n";foreach($Xe
as$x){$m=$Ij["fields"][$x];$B=$m["field"];echo"<tr><th>".adminer()->fieldName($m);$X=idx($_POST["fields"],$B);if($X!=""){if($m["type"]=="set")$X=implode(",",$X);}input($m,$X,idx($_POST["function"],$B,""));echo"\n";}echo"</table>\n";}echo'<p>
<input type=\'submit\' value=\'',lang(219),'\'>
',input_token(),'</form>

',adminer()->commentValue($Lj,$Ij['comment']);}elseif(isset($_GET["foreign"])){$a=$_GET["foreign"];$B=$_GET["name"];$I=$_POST;if($_POST&&!$l&&!$_POST["add"]&&!$_POST["change"]&&!$_POST["change-js"]){if(!$_POST["drop"]){$I["source"]=array_filter($I["source"],'strlen');ksort($I["source"]);$ql=array();foreach($I["source"]as$x=>$W)$ql[$x]=$I["target"][$x];$I["target"]=$ql;}if(JUSH=="sqlite")$G=recreate_table($a,$a,array(),array(),array(" $B"=>($I["drop"]?"":" ".format_foreign_key($I))));else{$b="ALTER TABLE ".table($a);$G=($B==""||queries("$b DROP ".(JUSH=="sql"?"FOREIGN KEY ":"CONSTRAINT ").idf_escape($B)));if(!$I["drop"])$G=queries("$b ADD".format_foreign_key($I));}queries_redirect(ME."table=".url_escape($a),($I["drop"]?lang(221):($B!=""?lang(222):lang(223))),$G);if(!$I["drop"])$l=lang(224);}page_header(($B!=""?lang(225):lang(159)),$l,array("table"=>$a),h($B!=""?$B:$a));if($_POST){ksort($I["source"]);if($_POST["change"]||$_POST["change-js"])$I["target"]=array();else$I["source"][]="";}elseif($B!=""){$Wd=foreign_keys($a);$I=$Wd[$B];$I["source"][]="";}else{$I["table"]=$a;$I["source"]=array("");}echo'
<form action="" method="post">
';$Ck=array_keys(fields($a));if($I["db"]!="")connection()->select_db($I["db"]);if($I["ns"]!=""){$ci=get_schema();set_schema($I["ns"]);}$rj=array_keys(array_filter(table_status('',true),function(array$R){return!$R["dependent"]&&fk_support($R);}));$ql=array_keys(fields(in_array($I["table"],$rj)?$I["table"]:reset($rj)));$c=on('change','foreignChange');echo"<p><label>".lang(226).": ".html_select("table",$rj,$I["table"],$c)."</label>\n";if(support("scheme")){$Qj=array_filter(adminer()->schemas(),function($K){return!information_schema(DB,$K);});echo"<label>".lang(86).": ".html_select("ns",$Qj,$I["ns"]!=""?$I["ns"]:$_GET["ns"],$c)."</label>";if($I["ns"]!="")set_schema($ci);}elseif(JUSH!="sqlite"){$lc=array();foreach(adminer()->databases()as$j){if(!information_schema($j))$lc[]=$j;}echo"<label>".lang(85).": ".html_select("db",$lc,$I["db"]!=""?$I["db"]:$_GET["db"],$c)."</label>";}echo
input_hidden("change-js"),'<noscript><p><input type=\'submit\' name=\'change\' value=\'',lang(227),'\'></noscript>
<table>
<thead><tr><th id="label-source">',lang(156),'<th id="label-target">',lang(157),'<tbody>
';$Ff=0;foreach($I["source"]as$x=>$W){echo"<tr>","<td>".html_select("source[".(+$x)."]",array(-1=>"")+$Ck,$W,($Ff==count($I["source"])-1?on('change','foreignAddRow'):""),"label-source"),"<td>".html_select("target[".(+$x)."]",$ql,idx($I["target"],$x),"","label-target");$Ff++;}echo'</table>
<p>
<label>',lang(123),': ',html_select("on_delete",array(-1=>"")+explode("|",driver()->onActions),$I["on_delete"]),'</label>
<label>',lang(122),': ',html_select("on_update",array(-1=>"")+explode("|",driver()->onActions),$I["on_update"]),'</label>
',(support("deferrable")?html_select("deferrable",array('NOT DEFERRABLE','DEFERRABLE','DEFERRABLE INITIALLY DEFERRED'),$I["deferrable"]).' ':''),doc_link(array('sql'=>"innodb-foreign-key-constraints.html",'mariadb'=>"foreign-keys/",'pgsql'=>"sql-createtable.html#SQL-CREATETABLE-PARMS-REFERENCES",'mssql'=>"t-sql/statements/create-table-transact-sql",'oracle'=>"sqlrf/constraint.html",)),'<p>
<input type=\'submit\' value=\'',lang(17),'\'>
<noscript><p><input type=\'submit\' name=\'add\' value=\'',lang(228),'\'></noscript>
';if($B!="")echo'<input type=\'submit\' name=\'drop\' value=\'',lang(146),'\'',confirm(lang(199,$B)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["view"])){$a=$_GET["view"];$I=$_POST;$di="VIEW";if(JUSH=="pgsql"&&$a!=""){$O=table_status1($a);$di=strtoupper($O["Engine"]);}if($_POST&&!$l){$B=trim($I["name"]);$Ea=" AS\n$I[select]";$lg=ME."table=".url_escape($B);$Ig=lang(229);$T=($_POST["materialized"]?"MATERIALIZED VIEW":"VIEW");if(!$_POST["drop"]&&$a==$B&&JUSH!="sqlite"&&$T=="VIEW"&&$di=="VIEW")query_redirect((JUSH=="mssql"?"ALTER":"CREATE OR REPLACE")." VIEW ".table($B).$Ea,$lg,$Ig);else{$ul="adminer_".uniqid();drop_create("DROP $di ".table($a),"CREATE $T ".table($B).$Ea,"DROP $T ".table($B),"CREATE $T ".table($ul).$Ea,"DROP $T ".table($ul),($_POST["drop"]?substr(ME,0,-1):$lg),lang(230),$Ig,lang(231),$a,$B);}}if(!$_POST&&$a!=""){$I=view($a);$I["name"]=$a;$I["materialized"]=($di!="VIEW");if(!$l)$l=adminer()->error();}page_header(($a!=""?lang(50):lang(232)),$l,array("table"=>$a),h($a));echo'
<form action="" method="post">
<p>',lang(209),': <input name="name" value="',h($I["name"]),'" data-maxlength="64" autocapitalize="off">
',(support("materializedview")?" ".checkbox("materialized",1,$I["materialized"],lang(150)):""),'<p>';textarea("select",$I["select"]);echo'<p>
<input type=\'submit\' value=\'',lang(17),'\'>
';if($a!="")echo'<input type=\'submit\' name=\'drop\' value=\'',lang(146),'\'',confirm(lang(199,$a)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["event"])){$aa=$_GET["event"];$tf=array("YEAR","QUARTER","MONTH","DAY","HOUR","MINUTE","WEEK","SECOND","YEAR_MONTH","DAY_HOUR","DAY_MINUTE","DAY_SECOND","HOUR_MINUTE","HOUR_SECOND","MINUTE_SECOND");$Pk=array("ENABLED"=>"ENABLE","DISABLED"=>"DISABLE","SLAVESIDE_DISABLED"=>"DISABLE ON SLAVE");$I=$_POST;if($_POST&&!$l){if($_POST["drop"])query_redirect("DROP EVENT ".idf_escape($aa),substr(ME,0,-1),lang(233));elseif(in_array($I["INTERVAL_FIELD"],$tf)&&isset($Pk[$I["STATUS"]])){$Pj="\nON SCHEDULE ".($I["INTERVAL_VALUE"]?"EVERY ".q($I["INTERVAL_VALUE"])." $I[INTERVAL_FIELD]".($I["STARTS"]?" STARTS ".q($I["STARTS"]):"").($I["ENDS"]?" ENDS ".q($I["ENDS"]):""):"AT ".q($I["STARTS"]))." ON COMPLETION".($I["ON_COMPLETION"]?"":" NOT")." PRESERVE";queries_redirect(substr(ME,0,-1),($aa!=""?lang(234):lang(235)),queries(($aa!=""?"ALTER EVENT ".idf_escape($aa).$Pj.($aa!=$I["EVENT_NAME"]?"\nRENAME TO ".idf_escape($I["EVENT_NAME"]):""):"CREATE EVENT ".idf_escape($I["EVENT_NAME"]).$Pj)."\n".$Pk[$I["STATUS"]]." COMMENT ".q($I["EVENT_COMMENT"]).rtrim(" DO\n$I[EVENT_DEFINITION]",";").";"));}}page_header(($aa!=""?lang(236).": ".h($aa):lang(237)),$l);if(!$I&&$aa!=""){$J=get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = ".q(DB)." AND EVENT_NAME = ".q($aa));$I=reset($J);}echo'
<form action="" method="post">
<table class="layout">
<tr><th>',lang(209),'<td><input name="EVENT_NAME" value="',h($I["EVENT_NAME"]),'" data-maxlength="64" autocapitalize="off">
<tr><th title="datetime">',lang(238),'<td><input name="STARTS" value="',h("$I[EXECUTE_AT]$I[STARTS]"),'">
<tr><th title="datetime">',lang(239),'<td><input name="ENDS" value="',h($I["ENDS"]),'">
<tr><th>',lang(240),'<td><input type="number" name="INTERVAL_VALUE" value="',h($I["INTERVAL_VALUE"]),'" class="size"> ',html_select("INTERVAL_FIELD",$tf,$I["INTERVAL_FIELD"]),'<tr><th>',lang(137),'<td>',html_select("STATUS",$Pk,$I["STATUS"]),'<tr><th>',lang(57),'<td><input name="EVENT_COMMENT" value="',h($I["EVENT_COMMENT"]),'" data-maxlength="64">
<tr><th><td>',checkbox("ON_COMPLETION","PRESERVE",$I["ON_COMPLETION"]=="PRESERVE",lang(241)),'</table>
<p>';textarea("EVENT_DEFINITION",$I["EVENT_DEFINITION"]);echo'<p>
<input type=\'submit\' value=\'',lang(17),'\'>
';if($aa!="")echo'<input type=\'submit\' name=\'drop\' value=\'',lang(146),'\'',confirm(lang(199,$aa)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["procedure"])){$ca=($_GET["name"]?:$_GET["procedure"]);$Ij=(isset($_GET["function"])?"FUNCTION":"PROCEDURE");$I=$_POST;$I["fields"]=(array)$I["fields"];if($_POST&&!process_fields($I["fields"])&&!$l){foreach($I["fields"]as$x=>$m){if($m["field"]=="")unset($I["fields"][$x]);}$Eh=routine_id($ca,routine($_GET["procedure"],$Ij));$jh=routine_id($I["name"],$I);$h=create_routine($Ij,$I);$lg=substr(ME,0,-1);$Ig=lang(242);if(!$_POST["drop"]&&$Eh==$jh&&connection()->flavor!="mysql")query_redirect(substr_replace($h,' OR REPLACE',6,0),$lg,$Ig);else{$ul="adminer_".uniqid();drop_create("DROP $Ij $Eh",$h,"DROP $Ij $jh",create_routine($Ij,array("name"=>$ul)+$I),"DROP $Ij ".routine_id($ul,$I),$lg,lang(243),$Ig,lang(244),$ca,$I["name"]);}}page_header(($ca!=""?(isset($_GET["function"])?lang(245):lang(246)).": ".h($ca):(isset($_GET["function"])?lang(247):lang(248))),$l);if(!$_POST){if($ca=="")$I["language"]="sql";else{$I=routine($_GET["procedure"],$Ij);$I["name"]=$ca;}}$yb=(JUSH=="sql"?flat_collations():array());$Jj=routine_languages();echo($yb?"<datalist id='collations'>".optionlist($yb)."</datalist>":""),'
<form action="" method="post" id="form">
<p>',lang(209),': <input name="name" value="',h($I["name"]),'" data-maxlength="64" autocapitalize="off">
',($Jj?"<label>".lang(23).": ".html_select("language",array_keys($Jj),$I["language"],on('change','routineLanguage',$Jj))."</label>\n":""),'<input type=\'submit\' value=\'',lang(17),'\'>
',doc_link(array('sql'=>"create-procedure.html",'mariadb'=>($Ij=="FUNCTION"?"create-function/":"create-procedure/"),'pgsql'=>($Ij=="FUNCTION"?"sql-createfunction.html":"sql-createprocedure.html"),),"?"),'<div class="scrollable">
<table id="edit-fields" class="nowrap">
';edit_fields($I["fields"],$yb,$Ij);if(isset($_GET["function"])){echo"<tr><td>".lang(249);edit_type("returns",(array)$I["returns"],$yb,array(),(JUSH=="pgsql"?array("void","trigger"):array()));}echo'</table>
',script("editFields();"),'</div>
<p>';textarea("definition",$I["definition"],20,80,($Jj[$I["language"]]?:JUSH));echo'<p>
<input type=\'submit\' value=\'',lang(17),'\'>
';if($ca!="")echo'<input type=\'submit\' name=\'drop\' value=\'',lang(146),'\'',confirm(lang(199,$ca)),'>
';$Kj=routine_options($Ij);if($Kj){$I["options"]=(array)$I["options"];$Sh=false;foreach($Kj
as$x=>$Y){$k=($Y?reset($Y):"");$I["options"][$x]=idx($I["options"],$x,$k);if($I["options"][$x]!=$k)$Sh=true;}print_fieldset("options",lang(127),$Sh);echo"<table class='layout'>\n";foreach($Kj
as$x=>$Y){$Pf="label-option-$x";$Bl=str_replace("_"," ",$x);$L=array();foreach($Y
as$X)$L[$X]=(strpos($X,"$Bl ")===0?substr($X,strlen($Bl)+1):$X);echo"<tr><th id='$Pf'>$Bl<td>".($L?html_select("options[$x]",$L,$I["options"][$x],"",$Pf):"<input name='options[$x]' value='".h($I["options"][$x])."' aria-labelledby='$Pf' autocapitalize='off'>")."\n";}echo"</table>\n</div></fieldset>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["sequence"])){$ea=$_GET["sequence"];$I=$_POST;if($_POST&&!$l){$_=substr(ME,0,-1);$B=trim($I["name"]);if($_POST["drop"])query_redirect("DROP SEQUENCE ".idf_escape($ea),$_,lang(250));elseif($ea=="")query_redirect("CREATE SEQUENCE ".idf_escape($B),$_,lang(251));elseif($ea!=$B)query_redirect("ALTER SEQUENCE ".idf_escape($ea)." RENAME TO ".idf_escape($B),$_,lang(252));else
redirect($_);}page_header($ea!=""?lang(253).": ".h($ea):lang(254),$l);if(!$I)$I["name"]=$ea;echo'
<form action="" method="post">
<p><input name="name" value="',h($I["name"]),'" autocapitalize="off">
<input type=\'submit\' value=\'',lang(17),'\'>
';if($ea!="")echo"<input type='submit' name='drop' value='".lang(146)."'".confirm(lang(199,$ea)).">\n";echo
input_token(),'</form>
';}elseif(isset($_GET["type"])){function
enum_values($rc){$X="'(?:[^']|'')*'";if(!preg_match('~^AS\s+ENUM\s*\(\s*('.$X.'(?:\s*,\s*'.$X.')*)\s*\)$~i',$rc,$A))return
null;preg_match_all('~'.$X.'~',$A[1],$rg);return$rg[0];}function
add_enum_values($T,$Ch,$hh){$Hh=enum_values($Ch);$nh=enum_values($hh);if($Hh===null||$nh===null)return
null;$H=array();$s=0;foreach($nh
as$X){if($X===idx($Hh,$s))$s++;else$H[]="ALTER TYPE ".idf_escape($T)." ADD VALUE $X".($s<count($Hh)?" BEFORE ".$Hh[$s]:"");}return($s==count($Hh)?$H:null);}$fa=$_GET["type"];$I=$_POST;$T=($fa!=""?type_definition(+array_search($fa,types(true))):array());$vh=($T["kind"]=='d'?"DOMAIN":"TYPE");if($_POST&&!$l){$_=substr(ME,0,-1);$B=trim($I["name"]);$Ea=trim(str_replace("\r","",$I["as"]));$lh=(preg_match('~^AS\s+(?!ENUM\b|RANGE\b|\()~i',$Ea)?"DOMAIN":"TYPE");$Ig=lang(255);$b=(!$_POST["drop"]&&$fa!=""&&$lh==$vh?($Ea==$T["definition"]?array():add_enum_values($fa,$T["definition"],$Ea)):null);if($b!==null){if($fa!=$B)$b[]="ALTER $vh ".idf_escape($fa)." RENAME TO ".idf_escape($B);if(!$b)redirect($_);$zd=false;foreach($b
as$F){if(!queries($F)){$zd=true;break;}}queries_redirect($_,$Ig,!$zd);}else
drop_create("DROP $vh ".idf_escape($fa),"CREATE $lh ".idf_escape($B)." $Ea","","","",$_,lang(256),$Ig,lang(257),$fa,$B);}page_header($fa!=""?lang(258).": ".h($fa):lang(259),$l);if(!$I){$I["name"]=$fa;$I["as"]=($fa!=""?$T["definition"]:"AS ");}echo'
<form action="" method="post">
<p>
',lang(209).": <input name='name' value='".h($I['name'])."' autocapitalize='off'>\n",doc_link(array('pgsql'=>"sql-createtype.html",),"?");textarea("as",$I["as"]);echo"<p><input type='submit' value='".lang(17)."'>\n";if($fa!="")echo"<input type='submit' name='drop' value='".lang(146)."'".confirm(lang(199,$fa)).">\n";echo
input_token(),'</form>
';}elseif(isset($_GET["check"])){$a=$_GET["check"];$B="$_GET[name]";$I=$_POST;if($I&&!$l){$lg=ME."table=".url_escape($a);$Lg=lang(260);$Jg=lang(261);$Kg=lang(262);if(JUSH=="sqlite")queries_redirect($lg,($I["drop"]?$Lg:($B!=""?$Jg:$Kg)),recreate_table($a,$a,array(),array(),array(),"",array(),"$B",($I["drop"]?"":$I["clause"])));else{$b="ALTER TABLE ".table($a);$kb=" CHECK ($I[clause])";$ul="adminer_".uniqid();drop_create("$b DROP CONSTRAINT ".idf_escape($B),"$b ADD".($I["name"]!=""?" CONSTRAINT ".idf_escape($I["name"]):"").$kb,"$b DROP CONSTRAINT ".idf_escape($I["name"]),"$b ADD CONSTRAINT ".idf_escape($ul).$kb,"$b DROP CONSTRAINT ".idf_escape($ul),$lg,$Lg,$Jg,$Kg,$B,$I["name"]);}}page_header(($B!=""?lang(263):lang(161)),$l,array("table"=>$a),h($B!=""?$B:$a));if(!$I){$ob=driver()->checkConstraints($a);$I=array("name"=>$B,"clause"=>$ob[$B]);}echo'
<form action="" method="post">
<p>';if(JUSH!="sqlite")echo
lang(209).': <input name="name" value="'.h($I["name"]).'" data-maxlength="64" autocapitalize="off"> ';echo
doc_link(array('sql'=>"create-table-check-constraints.html",'mariadb'=>"constraint/",'pgsql'=>"ddl-constraints.html#DDL-CONSTRAINTS-CHECK-CONSTRAINTS",'mssql'=>"relational-databases/tables/create-check-constraints",'sqlite'=>"lang_createtable.html#check_constraints",),"?"),'<p>';textarea("clause",$I["clause"]);echo'<p><input type=\'submit\' value=\'',lang(17),'\'>
';if($B!="")echo'<input type=\'submit\' name=\'drop\' value=\'',lang(146),'\'',confirm(lang(199,$B)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["trigger"])){$a=$_GET["trigger"];$B="$_GET[name]";$Ul=trigger_options();$I=(array)trigger($B,$a)+array("Trigger"=>$a."_bi");if($_POST){if(!$l&&in_array($_POST["Timing"],$Ul["Timing"])&&in_array($_POST["Event"],$Ul["Event"])&&in_array($_POST["Type"],$Ul["Type"])){$Ih=" ON ".table($a);$Mc="DROP TRIGGER ".idf_escape($B).(JUSH=="pgsql"?$Ih:"");$lg=ME."table=".url_escape($a);if($_POST["drop"])query_redirect($Mc,$lg,lang(264));else{if($B!="")queries($Mc);queries_redirect($lg,($B!=""?lang(265):lang(266)),queries(create_trigger($Ih,$_POST)));if($B!="")queries(create_trigger($Ih,$I+array("Type"=>reset($Ul["Type"]))));}}$I=$_POST;}page_header(($B!=""?lang(267):lang(163)),$l,array("table"=>$a),h($B!=""?$B:$a));$Sl=on('change','triggerChange',"^".preg_quote($a,"/")."_[ba][iud]$",$a);echo'
<form action="" method="post" id="form">
<table class="layout">
<tr><th>',lang(268),'<td>',html_select("Timing",$Ul["Timing"],$I["Timing"],$Sl),'<tr><th>',lang(269),'<td>',html_select("Event",$Ul["Event"],$I["Event"],$Sl),(in_array("UPDATE OF",$Ul["Event"])?" <input name='Of' value='".h($I["Of"])."' class='hidden'>":""),'<tr><th>',lang(56),'<td>',html_select("Type",$Ul["Type"],$I["Type"]),'<tr><th>',lang(209),'<td><input name="Trigger" value="',h($I["Trigger"]),'" data-maxlength="64" autocapitalize="off">
</table>
',script("fire(qs('#form')['Timing'], 'change');"),'<p>';textarea("Statement",$I["Statement"]);echo'<p>
<input type=\'submit\' value=\'',lang(17),'\'>
';if($B!="")echo'<input type=\'submit\' name=\'drop\' value=\'',lang(146),'\'',confirm(lang(199,$B)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["user"])){function
grant($me,array$ej,$e,$Ih){if(!$ej)return
true;if($ej==array("ALL PRIVILEGES","GRANT OPTION"))return($me=="GRANT"?queries("$me ALL PRIVILEGES$Ih WITH GRANT OPTION"):queries("$me ALL PRIVILEGES$Ih")&&queries("$me GRANT OPTION$Ih"));return
queries("$me ".preg_replace('~(GRANT OPTION)\([^)]*\)~','\1',implode("$e, ",$ej).$e).$Ih);}$ga=$_GET["user"];$ej=array(""=>array("All privileges"=>""));foreach(get_rows("SHOW PRIVILEGES")as$I){foreach(explode(",",($I["Privilege"]=="Grant option"?"":$I["Context"]))as$Qb)$ej[$Qb=="File access on server"?"Server Admin":$Qb][$I["Privilege"]]=$I["Comment"];}unset($ej["Server Admin"]["Usage"]);foreach($ej["Tables"]as$x=>$W)unset($ej["Databases"][$x]);$ih=array();if($_POST){foreach($_POST["objects"]as$x=>$W)$ih[$W]=(array)$ih[$W]+idx($_POST["grants"],$x,array());}$ne=array();if(isset($_GET["host"])&&($G=connection()->query("SHOW GRANTS FOR ".q($ga)."@".q($_GET["host"])))){while($I=$G->fetch_row()){if(preg_match('~GRANT (.*) ON (.*) TO ~',$I[0],$A)&&preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~',$A[1],$rg,PREG_SET_ORDER)){foreach($rg
as$W){if($W[1]!="USAGE")$ne["$A[2]$W[2]"][$W[1]]=true;if(preg_match('~ WITH GRANT OPTION~',$I[0]))$ne["$A[2]$W[2]"]["GRANT OPTION"]=true;}}}}if($_POST&&!$l){$Gh=(isset($_GET["host"])?q($ga)."@".q($_GET["host"]):"''");if($_POST["drop"])query_redirect("DROP USER $Gh",ME."privileges=",lang(270));else{$mh=q($_POST["user"])."@".q($_POST["host"]);$_i=$_POST["pass"];$Xb=false;$G=true;if($Gh!=$mh){$Xb=queries("CREATE USER $mh IDENTIFIED BY ".($_POST["hashed"]?"PASSWORD ":"").q($_i));$G=$Xb;}elseif($_i!="")$G=queries("SET PASSWORD FOR $mh = ".(min_version(8,99)||$_POST["hashed"]?q($_i):"PASSWORD(".q($_i).")"));if($G){$Ej=array();foreach($ih
as$vh=>$me){if(isset($_GET["grant"]))$me=array_filter($me);$me=array_keys($me);if(isset($_GET["grant"]))$Ej=array_diff(array_keys(array_filter($ih[$vh],'strlen')),$me);elseif($Gh==$mh){$Dh=array_keys((array)$ne[$vh]);$Ej=array_diff($Dh,$me);$me=array_diff($me,$Dh);unset($ne[$vh]);}if(preg_match('~^(.+)\s*(\(.*\))?$~U',$vh,$A)&&(!grant("REVOKE",$Ej,$A[2]," ON $A[1] FROM $mh")||!grant("GRANT",$me,$A[2]," ON $A[1] TO $mh"))){$G=false;break;}}}if($G&&isset($_GET["host"])){if($Gh!=$mh)queries("DROP USER $Gh");elseif(!isset($_GET["grant"])){foreach($ne
as$vh=>$Ej){if(preg_match('~^(.+)(\(.*\))?$~U',$vh,$A))grant("REVOKE",array_keys($Ej),$A[2]," ON $A[1] FROM $mh");}}}if($G&&!Queries::$queries)redirect(ME."privileges=");queries_redirect(ME."privileges=",(isset($_GET["host"])?lang(271):lang(272)),$G);if($Xb)connection()->query("DROP USER $mh");}}page_header((isset($_GET["host"])?lang(40).": ".h("$ga@$_GET[host]"):lang(171)),$l,array("privileges"=>array('',lang(78))));$I=$_POST;if($I)$ne=$ih;else{$I=$_GET+array("host"=>get_val("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)"));$ne[(DB==""||$ne?"":idf_escape(addcslashes(DB,"%_\\"))).".*"]=array();}echo'<form action="" method="post">
<table class="layout">
<tr><th>',lang(38),'<td><input name="host" data-maxlength="60" value="',h($I["host"]),'" autocapitalize="off">
<tr><th>',lang(40),'<td><input name="user" data-maxlength="80" value="',h($I["user"]),'" autocapitalize="off">
<tr><th>',lang(41),'<td><input name="pass" id="pass" value="',h($I["pass"]),'" autocomplete="new-password">
',($I["hashed"]?"":script("typePassword(qs('#pass'));")),(min_version(8,99)?"":checkbox("hashed",1,$I["hashed"],lang(273),on('click','hashedClick'))),'</table>

',"<table class='odds'>\n","<thead><tr><th colspan='2'>".lang(78).doc_link(array('sql'=>"grant.html#priv_level"));$s=0;foreach($ne
as$vh=>$me){echo'<th>'.($vh!="*.*"?"<input name='objects[$s]' value='".h($vh)."' size='10' autocapitalize='off'>":input_hidden("objects[$s]","*.*")."*.*");$s++;}echo"<tbody>\n";foreach(array(""=>"","Server Admin"=>lang(38),"Databases"=>lang(42),"Tables"=>lang(152),"Procedures"=>lang(274),)as$Qb=>$uc){foreach((array)$ej[$Qb]as$dj=>$Cb){echo"<tr><td".($uc?">$uc<td":" colspan='2'").' lang="en" title="'.h($Cb).'">'.h($dj);$s=0;foreach($ne
as$vh=>$me){$B="'grants[$s][".h(strtoupper($dj))."]'";$X=$me[strtoupper($dj)];if($Qb=="Server Admin"&&$vh!=(isset($ne["*.*"])?"*.*":".*"))echo"<td>";elseif(isset($_GET["grant"]))echo"<td><select name=$B><option><option value='1'".($X?" selected":"").">".lang(275)."<option value='0'".($X=="0"?" selected":"").">".lang(276)."</select>";else
echo"<td align='center'><label class='block'>","<input type='checkbox' name=$B value='1'".($X?" checked":"").($dj=="All privileges"?" id='grants-$s-all'":($dj=="Grant option"?"":on('click','grantsClick',"grants-$s-all"))).">","</label>";$s++;}}}echo"</table>\n",'<p>
<input type=\'submit\' value=\'',lang(17),'\'>
';if(isset($_GET["host"]))echo'<input type=\'submit\' name=\'drop\' value=\'',lang(146),'\'',confirm(lang(199,"$ga@$_GET[host]")),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["processlist"])){if(support("kill")){if($_POST&&!$l){$Mf=0;foreach((array)$_POST["kill"]as$W){if(adminer()->killProcess($W))$Mf++;}queries_redirect(ME."processlist=",lang(277,$Mf),$Mf||!$_POST["kill"]);}}page_header(lang(135),$l);echo'
<form action="" method="post">
<div class="scrollable">
<table class="nowrap checkable odds"',on('click','tableClick').on('dblclick','tableClick'),'>
';$s=-1;foreach(adminer()->processList()as$s=>$I){if(!$s){echo"<thead><tr lang='en'>".(support("kill")?"<td class='hover'>":"");foreach($I
as$x=>$W)echo"<th>$x".doc_link(array('sql'=>"show-processlist.html#processlist_".strtolower($x),'pgsql'=>"monitoring-stats.html#PG-STAT-ACTIVITY-VIEW",'oracle'=>"refrn/V-SESSION.html",));echo"<tbody>\n";}echo"<tr>".(support("kill")?"<td class='hover'>".checkbox("kill[]",$I[JUSH=="sql"?"Id":"pid"],0):"");foreach($I
as$x=>$W)echo"<td>".($W!=""&&((JUSH=="sql"&&$x=="Info"&&preg_match("~Query|Killed~",$I["Command"]))||(JUSH=="pgsql"&&$x=="query")||(JUSH=="oracle"&&$x=="sql_text"))?"<code class='jush-".JUSH."' data-full='".h($W)."'>".shorten_utf8($W,100,"</code>").' <a href="'.h(($I["db"]!=""?preg_replace('~&db=[^&]*~','',ME)."db=".url_escape($I["db"])."&":ME)."sql=".url_escape($W)).'">'.lang(278).'</a>'.' '.copy_icon():h($W));echo"\n";}echo'</table>
</div>
<p>
',script("copyCode(qsl('table'));");if(support("kill"))echo
format_number($s+1)."/".lang(279,max_connections()),"<p><input type='submit' value='".lang(280)."'>\n";echo
input_token(),'</form>
',script("tableCheck();");}elseif($_GET["select"]!=""){$a=$_GET["select"];$R=table_status1($a);$w=indexes($a);$n=fields($a);$Wd=column_foreign_keys($a);$Bh=$R["Oid"];$Gj=array();$e=array();$Uj=array();$Vh=array();$xl=null;foreach($n
as$x=>$m){$B=adminer()->fieldName($m);$dh=html_entity_decode(strip_tags($B),ENT_QUOTES);if(isset($m["privileges"]["select"])&&$B!=""){$e[$x]=$dh;if(is_shortable($m))$xl=adminer()->selectLengthProcess();}if(isset($m["privileges"]["where"])&&$B!="")$Uj[$x]=$dh;if(isset($m["privileges"]["order"])&&$B!="")$Vh[$x]=$dh;$Gj+=$m["privileges"];}list($L,$r)=adminer()->selectColumnsProcess($e,$w);$L=array_unique($L);$r=array_unique($r);$_f=count($r)<count($L);$Z=adminer()->selectSearchProcess($n,$w,$R);$Uh=adminer()->selectOrderProcess($n,$w);$z=adminer()->selectLimitProcess();if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$gm=>$I){$Ea=convert_field($n[key($I)]);$L=array($Ea?:idf_escape(key($I)));$Z[]=where_check(bracket_escape($gm,true),$n);$H=driver()->select($a,$L,$Z,$L);if($H)echo
first($H->fetch_row());}exit;}$Zi=$jm=array();foreach($w
as$v){if($v["type"]=="PRIMARY"){$Zi=array_flip($v["columns"]);$jm=($L?$Zi:array());foreach($jm
as$x=>$W){if(in_array(idf_escape($x),$L))unset($jm[$x]);}break;}}if($Bh&&!$Zi){$Zi=$jm=array($Bh=>0);$w[]=array("type"=>"PRIMARY","columns"=>array($Bh));}if($_POST&&!$l){$Pm=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$ob=array();foreach($_POST["check"]as$kb)$ob[]=where_check($kb,$n);$Pm[]="((".implode(") OR (",$ob)."))";}$Rm=$Pm;$Pm=($Pm?"\nWHERE ".implode(" AND ",$Pm):"");if($_POST["export"]){save_settings(array("output"=>$_POST["output"],"format"=>$_POST["format"]),"adminer_import");dump_headers($a);adminer()->dumpTable($a,"");$Xj=($L?:array("*"));$Sb=convert_fields($e,$n,$L);if($Sb)$Xj[]=substr($Sb,2);$F="";if(is_array($_POST["check"])&&!$Zi){$de=implode(", ",$Xj)."\nFROM ".table($a);$qe=($r&&$_f?"\nGROUP BY ".implode(", ",$r):"").($Uh?"\nORDER BY ".implode(", ",$Uh):"");$em=array();foreach($_POST["check"]as$W)$em[]="(SELECT".limit($de,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($W,$n).$qe,1).")";$F=implode(" UNION ALL ",$em);}adminer()->dumpData($a,"table",$F,$Xj,$Rm,($_f?$r:array()),$Uh);adminer()->dumpFooter();exit;}if(!adminer()->selectEmailProcess($Z,$Wd)){if($_POST["save"]||$_POST["delete"]){$G=true;$ta=0;$Ta=false;$N=array();if(!$_POST["delete"]){foreach($n
as$B=>$W){$u=bracket_escape($B);if(isset($_POST["fields"][$u])||$_FILES["fields-$u"]){$W=process_input($n[$B]);if($W!==null&&($_POST["clone"]||$W!==false))$N[idf_escape($B)]=($W!==false?$W:idf_escape($B));}}}if($_POST["delete"]||$N){$F=($_POST["clone"]?"INTO ".table($a)." (".implode(", ",array_keys($N)).")\nSELECT ".implode(", ",$N)."\nFROM ".table($a):"");if($_POST["all"]||($Zi&&is_array($_POST["check"]))||$_f){$G=($_POST["delete"]?driver()->delete($a,$Pm):($_POST["clone"]?queries("INSERT $F$Pm".driver()->insertReturning($a)):driver()->update($a,$N,$Pm)));$ta=connection()->affected_rows;if(is_object($G))$ta+=$G->num_rows;}else{$Ta=count((array)$_POST["check"])>1&&driver()->begin();foreach((array)$_POST["check"]as$W){$Om="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($W,$n);$G=($_POST["delete"]?driver()->delete($a,$Om,1):($_POST["clone"]?queries("INSERT".limit1($a,$F,$Om)):driver()->update($a,$N,$Om,1)));if(!$G)break;$ta+=connection()->affected_rows;}if($Ta&&$G&&!driver()->commit())$G=false;}}$Ig=lang(281,$ta);if($_POST["clone"]&&$G&&$ta==1){$Wf=last_id($G);if($Wf)$Ig=lang(192," $Wf");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page|next":""),$Ig,$G);if($Ta)driver()->rollback();if(!$_POST["delete"]){$Qi=(array)$_POST["fields"];edit_form($a,array_intersect_key($n,$Qi),$Qi,!$_POST["clone"],$l);page_footer();exit;}}elseif(!$_POST["import"]){$G=true;$ta=0;$Ta=count((array)$_POST["val"])>1&&driver()->begin();foreach((array)$_POST["val"]as$gm=>$I){$N=array();foreach($I
as$x=>$W){$x=bracket_escape($x,true);$N[idf_escape($x)]=(preg_match('~char|text~',$n[$x]["type"])||$W!=""?adminer()->processInput($n[$x],$W):"NULL");}$G=driver()->update($a,$N," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check(bracket_escape($gm,true),$n),($_f||$Zi?0:1)," ");if(!$G)break;$ta+=connection()->affected_rows;}if($Ta)$G=$G&&driver()->commit();queries_redirect(remove_from_uri(),lang(281,$ta),$G);if($Ta)driver()->rollback();}else{save_settings(array("format"=>$_POST["separator"]),"adminer_import");$Gd=get_file("csv_file",true);if(!is_string($Gd))$l=upload_error($Gd);elseif(!preg_match('~~u',$Gd))$l=lang(282);else{$zb=array_keys($n);$dk=($_POST["separator"]=="csv"?",":($_POST["separator"]=="tsv"?"\t":";"));$bc=parse_csv($Gd,$dk);$ta=count($bc);driver()->begin();$J=array();foreach($bc
as$x=>$Y){if(!$x&&!array_diff($Y,$zb)){$zb=$Y;$ta--;}else{$N=array();foreach($Y
as$s=>$vb)$N[idf_escape($zb[$s])]=($vb==""&&$n[$zb[$s]]["null"]?"NULL":q(csv_value($vb)));$J[]=$N;}}$G=(!$J||driver()->insertUpdate($a,$J,$Zi));if($G)driver()->commit();queries_redirect(remove_from_uri("page|next"),lang(283,$ta),$G);driver()->rollback();}}}}$el=adminer()->tableName($R);if(is_ajax()){page_headers();ob_start();}else
page_header(lang(60).": $el",$l);$N=null;if(isset($Gj["insert"])||!support("table")){$N="";foreach((array)$_GET["where"]as$W){$X=$W["val"];if(is_array($X))$X=(count($X)==1&&preg_match('~^val-(.*)~s',reset($X),$A)?$A[1]:"");if($W["col"]!=""&&$X!=""&&($W["op"]=="="||(!$W["op"]&&(is_array($W["val"])||!preg_match('~[_%]~',$X)))))$N
.="&set[".url_escape(bracket_escape($W["col"]))."]=".url_escape($X);}}adminer()->selectLinks($R,$N);if(!$e&&support("table"))echo"<p class='error'>".lang(284).($n?".":": ".adminer()->error())."\n";else{echo"<form action='' id='form'>\n","<div hidden>";hidden_fields_get();echo(DB!=""?input_hidden("db",DB).(isset($_GET["ns"])?input_hidden("ns",$_GET["ns"]):""):""),input_hidden("select",$a),"</div>\n";adminer()->selectColumnsPrint($L,$e);adminer()->selectSearchPrint($Z,$Uj,$w,$R);adminer()->selectOrderPrint($Uh,$Vh,$w);adminer()->selectLimitPrint($z);if($xl!==null)adminer()->selectLengthPrint($xl);adminer()->selectActionPrint($w);echo"</form>\n";foreach((array)$_GET["where"]as$W){if($W["op"]=="SQL"&&!in_array($_SERVER["HTTP_SEC_FETCH_SITE"],array("","same-origin"))){echo"<p class='error'>".lang(116).' '.lang(117)."\n";page_footer();exit;}}$D=$_GET["page"];$Zd=null;if($D=="last"){$Zd=get_val(count_rows($a,$Z,$_f,$r));$D=floor(max(0,intval($Zd)-1)/$z);}$Wj=$L;$pe=$r;if(!$Wj){$Wj[]="*";$Sb=convert_fields($e,$n,$L);if($Sb)$Wj[]=substr($Sb,2);}foreach($L
as$x=>$W){$m=$n[idf_unescape($W)];if($m&&($Ea=convert_field($m)))$Wj[$x]="$Ea AS $W";}if(JUSH=="pgsql"||JUSH=="mssql"){foreach((array)$_GET["columns"]as$x=>$W){if(isset($Wj[$x])&&$W["fun"])$Wj[$x].=" AS ".idf_escape(apply_sql_function($W["fun"],($W["col"]!=""?$W["col"]:"*")));}}if(!$_f&&$jm){foreach($jm
as$x=>$W){$Wj[]=idf_escape($x);if($pe)$pe[]=idf_escape($x);}}$G=driver()->select($a,$Wj,$Z,$pe,$Uh,$z,$D,true);if(!is_object($G))echo"<p class='error'>".(adminer()->error()?:lang(25))."\n";else{if(JUSH=="mssql"&&$D)$G->seek($z*$D);$Yc=array();$J=array();while($I=$G->fetch_assoc()){if($D&&JUSH=="oracle")unset($I["RNUM"]);$J[]=$I;}$Ae=($z&&(support("cursor")?$_GET["next"]!="":count($J)>=$z));if(is_ajax()&&$Ae)header("X-Next-Page: ".pagination_href($D+1));if($_GET["modify"]&&$J){$_g=max_input_vars(count($J[0])+1,20);echo($_g&&count($J)>$_g?"<p class='error'>".max_input_vars_error()."\n":"");}echo"<form action='' method='post' enctype='multipart/form-data'".on_upload_progress($om).">\n";if($_GET["page"]!="last"&&$z&&$r&&$_f&&JUSH=="sql")$Zd=get_val(" SELECT FOUND_ROWS()");if(!$J)echo"<p class='message'>".lang(15)."\n";else{$Pa=adminer()->backwardKeys($a,$el);echo"<div class='scrollable'>","<table id='table' class='nowrap checkable odds'".on('click','tableClick').on('dblclick','tableClick').on('keydown','editingKeydown').">\n","<thead><tr>".(!$r&&$L?"":"<td class='hover check'><input type='checkbox' id='all-page' class='jsonly' title='".lang(285)."'".on('click','formCheck','^check').">");$eh=array();$je=array();reset($L);$oj=1;foreach($J[0]as$x=>$W){if(!isset($jm[$x])){$W=idx($_GET["columns"],key($L))?:array();$m=$n[$L?($W?$W["col"]:current($L)):$x];$B=($m?adminer()->fieldName($m,$oj):($W["fun"]?"*":h($x)));if($B!=""){$oj++;$eh[$x]=$B;$d=idf_escape($x);$Oe=remove_from_uri('(order|desc)[^=]*|page|next').'&order[0]='.url_escape($x);$uc="&desc[0]=1";$_k=preg_replace('~ DESC( NULLS LAST)?$~','',$Uh[0]);$Bk=($_k==$d||$_k==$x);echo"<th id='th[".h(bracket_escape($x))."]'".($Bk?" aria-sort='".($_k==$Uh[0]?"ascending":"descending")."'":"").">";$ie=apply_sql_function($W["fun"],$B);$Ak=isset($m["privileges"]["order"])||$ie!=$B;echo($Ak?"<a href='".h($Oe.($Bk&&$_k==$Uh[0]?$uc:''))."'>$ie</a>":$ie);$Hg=($Ak?"<a href='".h($Oe.$uc)."' title='".lang(66)."' class='text'> ↓</a>":'');if(!$W["fun"]&&isset($m["privileges"]["where"]))$Hg
.="<a href='#fieldset-search' title='".lang(63)."' class='text jsonly'".on('click','selectSearch',$x)."> =</a>";echo($Hg?"<span class='column'>$Hg</span>":"");}$je[$x]=$W["fun"];next($L);}}$dg=array();if($_GET["modify"]){foreach($J
as$I){foreach($I
as$x=>$W)$dg[$x]=max($dg[$x],min(40,strlen(utf8_decode($W))));}}echo($Pa?"<th>".lang(286):"")."<tbody>\n";if(is_ajax())ob_end_clean();foreach(adminer()->rowDescriptions($J,$Wd)as$bh=>$I){$fm=unique_array($J[$bh],$w);if(!$fm){$fm=array();reset($L);foreach($J[$bh]as$x=>$W){if(!preg_match('~^(COUNT|AVG|GROUP_CONCAT|MAX|MIN|SUM)\(~',current($L)))$fm[$x]=$W;next($L);}}$gm="";foreach($fm
as$x=>$W){$m=(array)$n[$x];$zf=is_blob($m);if((JUSH=="sql"||JUSH=="pgsql")&&($zf||preg_match('~'.text_type().'~',$m["type"]))&&strlen($W)>64){$x=(strpos($x,'(')?$x:idf_escape($x));$x="MD5(".($zf||JUSH!='sql'||preg_match("~^utf8~",$m["collation"])?$x:"CONVERT($x USING ".charset(connection()).")").")";$W=md5($zf?(string)driver()->value($W,$m):$W);}$gm
.="&".($W!==null?"where[".url_escape(bracket_escape($x))."]=".url_escape($W===false?"f":$W):"null[]=".url_escape($x));}echo"<tr>".(!$r&&$L?"":"<td class='hover check'>".($_f||information_schema(DB)?"":"<a href='".h(ME."edit=".url_escape($a).$gm)."' class='edit'>".lang(287)."</a> ").checkbox("check[]",substr($gm,1),in_array(substr($gm,1),(array)$_POST["check"])));reset($L);foreach($I
as$x=>$W){if(isset($eh[$x])){$d=current($L);$m=(array)$n[$x];if($W!=""&&(!isset($Yc[$x])||$Yc[$x]!=""))$Yc[$x]=(is_mail($W)?$eh[$x]:"");$_="";if(is_blob($m)&&$W!="")$_=ME.'download='.url_escape($a).'&field='.url_escape($x).$gm;if(!$_&&$W!==null){foreach((array)$Wd[$x]as$p){if(count($Wd[$x])==1||end($p["source"])==$x){$_="";foreach($p["source"]as$s=>$Ck)$_
.=where_link($s,$p["target"][$s],$J[$bh][$Ck]);$_=($p["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\1'.url_escape($p["db"]),ME):ME).'select='.url_escape($p["table"]).$_;if($p["ns"])$_=preg_replace('~([?&]ns=)[^&]+~','\1'.url_escape($p["ns"]),$_);if(count($p["source"])==1)break;}}}if($d=="COUNT(*)"){$_=ME."select=".url_escape($a);$s=0;foreach((array)$_GET["where"]as$V){if(!array_key_exists($V["col"],$fm))$_
.=where_link($s++,$V["col"],$V["val"],$V["op"]);}foreach($fm
as$If=>$V)$_
.=where_link($s++,$If,$V);}$Pe=select_value($W,$_,$m,$xl);$u=bracket_escape($gm);$t=h("val[$u][".bracket_escape($x)."]");$Si=idx(idx($_POST["val"],$u),bracket_escape($x));$mm=idx($m["privileges"],"update");$Uc=!is_array($I[$x])&&!is_blob($m)&&is_utf8($W)&&$J[$bh][$x]==$W&&!$je[$x]&&!$m["generated"]&&$mm;$T=(preg_match('~^(AVG|MIN|MAX)\((.+)\)~',$d,$A)?$n[idf_unescape($A[2])]["type"]:$m["type"]);$wl=preg_match('~text|json|lob~',$T);$Af=preg_match(number_type(),$T)||preg_match('~^(CHAR_LENGTH|ROUND|FLOOR|CEIL|TIME_TO_SEC|COUNT|SUM)\(~',$d);echo"<td id='$t'".($Af&&($W===null||is_numeric(strip_tags($Pe))||$T=="money")?" class='number'":"");if(($_GET["modify"]&&$Uc&&$W!==null)||$Si!==null){$we=h($Si!==null?$Si:$W);echo">".($wl?"<textarea name='$t' cols='30' rows='".(substr_count($W,"\n")+1)."'>$we</textarea>":"<input name='$t' value='$we' size='$dg[$x]'>");}else{$ng=strpos($Pe,"<i>…</i>");echo($mm?" data-text='".($ng?2:($wl?1:0))."'".($Uc?"":" data-warning='".lang(288)."'"):"").">$Pe";}}next($L);}if($Pa)echo"<td>";adminer()->backwardKeysPrint($Pa,$J[$bh]);echo"</tr>\n";}if(is_ajax())exit;echo"</table>\n","</div>\n";}if(!is_ajax()){$sa=get_settings("adminer_import");if($J||$D||$Ae){$nd=true;if($_GET["page"]!="last"){if(!$z||(count($J)<$z&&($J||!$D)))$Zd=($D?$D*$z:0)+count($J);elseif(JUSH!="sql"||!$_f){$Zd=($_f?false:found_rows($R,$Z));if(intval($Zd)<max(1e4,2*($D+1)*$z))$Zd=first(slow_query(count_rows($a,$Z,$_f,$r)));elseif(JUSH=='sql'||JUSH=='pgsql')$nd=false;}}if(!support("cursor"))$Ae=(($Zd===false?count($J)+1:$Zd-$D*$z)>$z);$mi=($z&&($Ae||$D));if($mi)echo($Ae?'<p><a href="'.h(pagination_href($D+1)).'" class="loadmore"'.on('click','selectLoadMore',lang(289)).'>'.lang(290).'</a>':''),"\n";echo"<div class='footer'><div>\n";if($mi){$yg=($Zd===false?$D+($J?(count($J)>=$z?2:1):0):floor(($Zd-1)/$z));echo"<fieldset><legend>".lang(291)."</legend>";if(!support("cursor")){echo
pagination(0,$D).($D>5?" …":"");for($s=max(1,$D-4);$s<min($yg,$D+5);$s++)echo
pagination($s,$D);if($yg>0)echo($D+5<$yg?" …":""),($nd&&$Zd!==false?pagination($yg,$D):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$yg'>".lang(292)."</a>");}else
echo
pagination(0,$D).($D>1?" …":""),($D?pagination($D,$D):""),($Ae?pagination($D+1,$D)." …":"");echo"</fieldset>\n";}echo"<fieldset>","<legend>".lang(293)."</legend>";$Bc=($nd?"":"~ ").$Zd;$Pf=($Zd!==false?($nd?"":"~ ").lang(175,$Zd):"");echo
checkbox("all",1,0,$Pf,on('click','countRows',$Bc))."\n","</fieldset>\n";if(adminer()->selectCommandPrint())echo'<fieldset',($_GET["modify"]?'':" title='".lang(294)."'"),'>
<legend><a href=\'',h($_GET["modify"]?remove_from_uri("modify"):relative_uri()."&modify=1"),'\'>',lang(295),'</a></legend><div>
<input type=\'submit\' id=\'save\' value=\'',lang(17),'\'',($_GET["modify"]?'':" class='jsonly' disabled"),'>
</div></fieldset>

<fieldset><legend>',lang(145),' <span id="selected"></span></legend><div>
<input type=\'submit\' name=\'edit\' value=\'',lang(13),'\'>
<input type=\'submit\' name=\'clone\' value=\'',lang(278),'\'>
<input type=\'submit\' name=\'delete\' value=\'',lang(21),'\'',confirm(),'>
</div></fieldset>
';$Xd=adminer()->dumpFormat();foreach((array)$_GET["columns"]as$d){if($d["fun"]){unset($Xd['sql']);break;}}if($Xd){print_fieldset("export",lang(83)." <span id='selected2'></span>");$ii=adminer()->dumpOutput();echo($ii?html_select("output",$ii,$sa["output"])." ":""),html_select("format",$Xd,$sa["format"])," <input type='submit' name='export' value='".lang(83)."'>\n","</div></fieldset>\n";}adminer()->selectEmailPrint(array_filter($Yc,'strlen'),$e);echo"</div></div>\n";}if(adminer()->selectImportPrint())echo"<p>","<a href='#import' class='toggle'>".lang(82)."</a>","<span id='import'".($_POST["import"]?"":" class='hidden'").">: ",($om?input_hidden(ini_get("session.upload_progress.name"),$om):""),file_input(" name='csv_file'"," ".html_select("separator",array("csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"),$sa["format"])." <input type='submit' name='import' value='".lang(82)."'>".($om?" <progress class='jsonly hidden' max='1' value='0'></progress>":"")),"</span>";echo
input_token(),"</form>\n",(!$r&&$L?"":script("tableCheck();"));}}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["variables"])){$O=isset($_GET["status"]);page_header($O?lang(137):lang(136));$Em=($O?adminer()->showStatus():adminer()->showVariables());if(!$Em)echo"<p class='message'>".lang(15)."\n";else{echo"<table>\n";foreach($Em
as$I){echo"<tr>";$x=array_shift($I);echo"<th><code class='jush-".JUSH.($O?"status":"set")."'>".h($x)."</code>";foreach($I
as$W)echo"<td>".nl_br(h($W));}echo"</table>\n";}}elseif(isset($_GET["script"])){header("Content-Type: application/json; charset=utf-8");if($_GET["script"]=="db"){$Wk=array("Data_length"=>0,"Index_length"=>0,"Data_free"=>0);foreach(table_status()as$B=>$R){json_row("Comment-$B",h($R["Comment"]).($R["Error"]?" <span class='error'>".h($R["Error"])."</span>":""));if(!is_view($R)||preg_match('~materialized~i',$R["Engine"])){foreach(array("Engine","Collation")as$x)json_row("$x-$B",h($R[$x]));foreach(array_keys($Wk+array("Auto_increment"=>0,"Rows"=>0))as$x){if(array_key_exists($x,$R))json_row("$x-$B",format_status($R,$x));if($R[$x]!=""&&isset($Wk[$x]))$Wk[$x]+=($R["Engine"]!="InnoDB"||$x!="Data_free"?$R[$x]:0);}}}if(function_exists('Adminer\db_status'))$Wk=db_status();foreach($Wk
as$x=>$W)json_row("sum-$x",format_number($W));json_row("");}elseif($_GET["script"]=="kill"){if(!$l)connection()->query("KILL ".number($_POST["kill"]));}else{foreach(count_tables(adminer()->databases(false))as$j=>$W){json_row("tables-$j",format_number($W));json_row("size-$j",db_size($j));}json_row("");}exit;}else{if(!isset($_GET["select"])&&support("single_table")){$S=tables_list();if($S)redirect(ME.(support("table")?"table=":"select=").url_escape(key($S)));}$Eg=ME.(isset($_GET["select"])?"select=&":"");$ol=array_merge((array)$_POST["tables"],(array)$_POST["views"]);if($ol&&!$l&&!$_POST["search"]){$G=true;$Ig="";if(JUSH=="sql"&&$_POST["tables"]&&count($_POST["tables"])>1&&($_POST["drop"]||$_POST["truncate"]||$_POST["copy"]))queries("SET foreign_key_checks = 0");if($_POST["truncate"]){if($_POST["tables"])$G=truncate_tables($_POST["tables"]);$Ig=lang(296);}elseif($_POST["move"]){$G=move_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$Ig=lang(297);}elseif($_POST["copy"]){$G=copy_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$Ig=lang(298);}elseif($_POST["drop"]){if($_POST["views"])$G=drop_views($_POST["views"]);if($G&&$_POST["tables"])$G=drop_tables($_POST["tables"]);$Ig=lang(299);}elseif(JUSH=="sqlite"&&$_POST["check"]){foreach((array)$_POST["tables"]as$Q){foreach(get_rows("PRAGMA integrity_check(".q($Q).")")as$I)$Ig
.="<b>".h($Q)."</b>: ".h($I["integrity_check"])."<br>";}}elseif(JUSH=="mssql"&&$_POST["check"]){foreach((array)$_POST["tables"]as$Q){foreach(get_rows("DBCC CHECKTABLE (".q(table($Q)).") WITH TABLERESULTS")as$I)$Ig
.="<b>".h($Q)."</b>: ".h($I["MessageText"])."<br>";}}elseif(JUSH!="sql"){$G=(JUSH=="sqlite"?queries("VACUUM"):apply_queries("VACUUM".($_POST["optimize"]?" ANALYZE":""),(array)$_POST["tables"]));$Ig=lang(300);}elseif(!$_POST["tables"])$Ig=lang(12);elseif($G=queries(($_POST["optimize"]?"OPTIMIZE":($_POST["check"]?"CHECK":($_POST["repair"]?"REPAIR":"ANALYZE")))." TABLE ".implode(", ",array_map('Adminer\idf_escape',$_POST["tables"])))){while($I=$G->fetch_assoc())$Ig
.="<b>".h($I["Table"])."</b>: ".h($I["Msg_text"])."<br>";}queries_redirect(relative_uri(),$Ig,$G);}page_header(($_GET["ns"]==""?lang(42).": ".h(DB):lang(86).": ".h($_GET["ns"])),$l,true);if(adminer()->homepage()){if($_GET["ns"]!==""){$Uh=$_GET["order"];$fe=($Uh||support("fast_status"));echo"<div>\n","<h3 id='tables-views'>".lang(301)."</h3>\n";$nl=($fe?table_status():tables_list());if(!$nl)echo"<p class='message'>".lang(12)."\n";else{echo"<form action='' method='post'>\n";if(support("table")){echo"<fieldset><legend>".lang(302)." <span id='selected2'></span></legend><div>",html_select("op",adminer()->operators(),idx($_POST,"op",JUSH=="elastic"?"should":"LIKE %%"))," <input type='search' name='query' value='".h($_POST["query"])."'".on('keydown','submitKeydown','search').">"," <input type='submit' name='search' value='".lang(63)."'>\n","</div></fieldset>\n";if(!$l&&$_POST["search"]&&$_POST["query"]!=""){$_GET["where"][0]["op"]=$_POST["op"];search_tables();}}echo"<div class='scrollable'>\n","<table class='nowrap checkable odds'".on('click','tableClick').on('dblclick','tableClick').">\n",'<thead><tr class="wrap">','<td class="hover"><input id="check-all" type="checkbox" class="jsonly" title="'.lang(170).'"'.on('click','formCheck','^(tables|views)\[').'>','<th'.(!$Uh&&JUSH!='sqlite'?" aria-sort='ascending'":'').'><a href="'.h(substr($Eg,0,-1)).'">'.lang(152).'</a>';$e=array("Engine"=>array(lang(303).doc_link(array('sql'=>'storage-engines.html'))));if(collations())$e["Collation"]=array(lang(141).doc_link(array('sql'=>'charset-charsets.html','mariadb'=>'supported-character-sets-and-collations/')));if(function_exists('Adminer\alter_table'))$e["Data_length"]=array(lang(304).doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT','oracle'=>'refrn/ALL_TABLES.html')),"create",lang(51),);if(support("indexes"))$e["Index_length"]=array(lang(305).doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT')),"indexes",lang(155),);$e["Data_free"]=array(lang(306).doc_link(array('sql'=>'show-table-status.html')),"edit",lang(52));if(function_exists('Adminer\alter_table'))$e["Auto_increment"]=array(lang(58).doc_link(array('sql'=>'example-auto-increment.html','mariadb'=>'auto_increment/')),"auto_increment=1&create",lang(51),);$e["Rows"]=array(lang(307).doc_link(array('sql'=>'show-table-status.html','pgsql'=>'catalog-pg-class.html#CATALOG-PG-CLASS','oracle'=>'refrn/ALL_TABLES.html')),"select",lang(48),);if(support("comment"))$e["Comment"]=array(lang(57).doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-info.html#FUNCTIONS-INFO-COMMENT-TABLE')));$Fa=array('Engine','Collation','Comment');foreach($e
as$x=>$d)echo"<th".($Uh==$x?" aria-sort='".(in_array($x,$Fa)?"ascending":"descending")."'":"")."><a href='".h($Eg)."order=$x'>$d[0]</a>";echo"<tbody>\n";if($Uh){uasort($nl,function($ja,$Ma)use($Uh,$Fa){$H=($ja[$Uh]<$Ma[$Uh]?-1:($ja[$Uh]>$Ma[$Uh]?1:0));return(in_array($Uh,$Fa)?$H:-$H);});}$S=0;$Wk=array("Data_length"=>0,"Index_length"=>0,"Data_free"=>0);foreach($nl
as$B=>$O){$Hm=($fe?is_view($O):$O!==null&&!preg_match('~table|sequence~i',$O));$O=($fe?$O:array('Engine'=>$O));$t=h("Table-".$B);echo'<tr><td class="hover">'.checkbox(($Hm?"views[]":"tables[]"),$B,in_array("$B",$ol,true),"","","",$t),'<th>'.(support("table")||support("indexes")?"<a href='".h(ME)."table=".url_escape($B)."' title='".lang(49)."' id='$t'>".h($B).'</a>':h($B));if($Hm&&!preg_match('~materialized~i',$O['Engine'])){$Bl=lang(151);echo'<td colspan="'.(count($e)-(support("comment")?2:1)).'">'.(support("view")?"<a href='".h(ME)."view=".url_escape($B)."' title='".lang(50)."'>$Bl</a>":$Bl),"<td align='right'><a href='".h(ME)."select=".url_escape($B)."' title='".lang(48)."'>?</a>";if(support("comment"))echo'<td>'.h($O['Comment']);}else{if($fe){foreach(array_keys($Wk)as$x)$Wk[$x]+=($O["Engine"]!="InnoDB"||$x!="Data_free"?idx($O,$x):0);}foreach($e
as$x=>$d){$t=" id='$x-".h($B)."'";echo($d[1]?"<td align='right'><a href='".h(ME."$d[1]=").url_escape($B)."'$t title='$d[2]'>".format_status($O,$x)."</a>":"<td$t>".h(idx($O,$x,'?')).($x=="Comment"&&$O["Error"]?" <span class='error'>".h($O["Error"])."</span>":""));}$S++;}echo"\n";}echo"<tr><td class='hover'><th>".lang(279,count($nl)),"<td>".h(JUSH=="sql"?get_val("SELECT @@default_storage_engine"):""),(collations()?"<td>".h(db_collation(DB,collations())):'');if($fe&&function_exists('Adminer\db_status'))$Wk=db_status();foreach($Wk
as$x=>$Vk)echo($e[$x]?"<td align='right' id='sum-$x'>".($fe?format_number($Vk):""):"");echo"\n","</table>\n",($fe?'':script("ajaxSetHtml('".js_escape(ME)."script=db');")),"</div>\n";if(!information_schema(DB)){$Am="<input type='submit' value='".lang(308)."'".on_help("VACUUM")."> ";$Qh="<input type='submit' name='optimize' value='".lang(309)."'".on_help(JUSH=="sql"?"OPTIMIZE TABLE":"VACUUM ANALYZE")."> ";$bj=(JUSH=="sqlite"?$Am."<input type='submit' name='check' value='".lang(310)."'".on_help("PRAGMA integrity_check")."> ":(JUSH=="pgsql"?$Am.$Qh:(JUSH=="mssql"?"<input type='submit' name='check' value='".lang(310)."'".on_help("DBCC CHECKTABLE")."> ":(JUSH=="sql"?"<input type='submit' value='".lang(311)."'".on_help("ANALYZE TABLE")."> ".$Qh."<input type='submit' name='check' value='".lang(310)."'".on_help("CHECK TABLE")."> "."<input type='submit' name='repair' value='".lang(312)."'".on_help("REPAIR TABLE")."> ":"")))).(function_exists('Adminer\truncate_tables')?"<input type='submit' name='truncate' value='".lang(313)."'".confirm().on_help(JUSH=="sqlite"?"DELETE":"TRUNCATE".(JUSH=="pgsql"?"":" TABLE"))."> ":"").(function_exists('Adminer\drop_tables')?"<input type='submit' name='drop' value='".lang(146)."'".confirm().on_help("DROP TABLE").">":"");echo($bj?"<div class='footer'><div>\n<fieldset><legend>".lang(145)." <span id='selected'></span></legend><div>$bj\n</div></fieldset>\n":"");$i=(support("scheme")?adminer()->schemas():adminer()->databases());if(count($i)!=1&&function_exists('Adminer\move_tables')){echo"<fieldset><legend>".lang(314)." <span id='selected3'></span></legend><div>";$j=(isset($_POST["target"])?$_POST["target"]:(support("scheme")?$_GET["ns"]:DB));echo($i?html_select("target",$i,$j):'<input name="target" value="'.h($j).'" autocapitalize="off">'),"</label> <input type='submit' name='move' value='".lang(129)."'>",(support("copy")?" <input type='submit' name='copy' value='".lang(22)."'> ".checkbox("overwrite",1,$_POST["overwrite"],lang(315)):""),"</div></fieldset>\n";}echo"<input type='hidden' name='all' value=''".on('click','countTables',$S).">\n",input_token(),"</div></div>\n";}echo"</form>\n",script("tableCheck();");}echo(function_exists('Adminer\alter_table')?"<p class='links hover'><a href='".h(ME)."create='>".lang(84)."</a>\n":''),(support("view")?"<a href='".h(ME)."view='>".lang(232)."</a>\n":""),"</div>\n";if(support("routine")){echo"<div>\n","<h3 id='routines'>".lang(79)."</h3>\n";$Mj=routines();if($Mj){echo"<table class='odds'>\n",'<thead><tr><th>'.lang(209).'<td>'.lang(56).'<td>'.lang(249)."<td class='hover'><tbody>\n";foreach($Mj
as$I){$B=($I["SPECIFIC_NAME"]==$I["ROUTINE_NAME"]?"":"&name=".url_escape($I["ROUTINE_NAME"]));echo'<tr>','<th><a href="'.h(ME.($I["ROUTINE_TYPE"]!="PROCEDURE"?'callf=':'call=').url_escape($I["SPECIFIC_NAME"]).$B).'" title="'.lang(219).'">'.h($I["ROUTINE_NAME"]).'</a>','<td>'.h($I["ROUTINE_TYPE"]),'<td>'.h($I["DTD_IDENTIFIER"]),'<td class="hover"><a href="'.h(ME.($I["ROUTINE_TYPE"]!="PROCEDURE"?'function=':'procedure=').url_escape($I["SPECIFIC_NAME"]).$B).'">'.lang(158)."</a>";}echo"</table>\n";}echo'<p class="links hover">'.(support("procedure")?'<a href="'.h(ME).'procedure=">'.lang(248).'</a>':'').'<a href="'.h(ME).'function=">'.lang(247)."</a>\n","</div>\n";}if(support("sequence")){echo"<div>\n","<h3 id='sequences'>".lang(80)."</h3>\n";$hk=get_vals("SELECT relname FROM pg_class WHERE relkind = 'S' AND relnamespace = ".driver()->nsOid." ORDER BY relname");if($hk){echo"<table class='odds'>\n","<thead><tr><th>".lang(209)."<tbody>\n";foreach($hk
as$W)echo"<tr><th><a href='".h(ME)."sequence=".url_escape($W)."'>".h($W)."</a>\n";echo"</table>\n";}echo"<p class='links hover'><a href='".h(ME)."sequence='>".lang(254)."</a>\n","</div>\n";}if(support("type")){echo"<div>\n","<h3 id='user-types'>".lang(7)."</h3>\n";$ym=types();if($ym){echo"<table class='odds'>\n","<thead><tr><th>".lang(209)."<tbody>\n";foreach($ym
as$W)echo"<tr><th><a href='".h(ME)."type=".url_escape($W)."'>".h($W)."</a>\n";echo"</table>\n";}echo"<p class='links hover'><a href='".h(ME)."type='>".lang(259)."</a>\n","</div>\n";}if(support("event")){echo"<div>\n","<h3 id='events'>".lang(81)."</h3>\n";$J=get_rows("SHOW EVENTS");if($J){echo"<table>\n","<thead><tr><th>".lang(209)."<td>".lang(316)."<td>".lang(238)."<td>".lang(239)."<td class='hover'><tbody>\n";foreach($J
as$I)echo"<tr>","<th>".h($I["Name"]),"<td>".($I["Execute at"]?lang(317)."<td>".h($I["Execute at"]):lang(240)." ".h($I["Interval value"])." ".h($I["Interval field"])."<td>".h($I["Starts"])),"<td>".h($I["Ends"]),'<td class="hover"><a href="'.h(ME).'event='.url_escape($I["Name"]).'">'.lang(158).'</a>';echo"</table>\n";$kd=get_val("SELECT @@event_scheduler");if($kd&&$kd!="ON")echo"<p class='error'><code class='jush-sqlset'>event_scheduler</code>: ".h($kd)."\n";}echo'<p class="links hover"><a href="'.h(ME).'event=">'.lang(237)."</a>\n","</div>\n";}}}}page_footer();