<?php
header( 'Content-Type: text/plain; charset=UTF-8');
//header( 'Content-Type:text /html; charset=UTF-8');
ini_set("max_execution_time", "3000000");
//htaccess php_value max_execution_time 0;
ini_set('date.timezone','Asia/Shanghai');
$fp="epgmytvsuper.xml";//压缩版本的扩展名后加.gz
$dt1=date('Ymd');//獲取當前日期
$dt2=date('Ymd',time()+24*3600);//第二天日期
$dt21=date('Ymd',time()+48*3600);//第三天日期
$dt22=date('Ymd',time()-24*3600);//前天日期
$dt3=date('Ymd',time()+7*24*3600);
$dt4=date("Y/n/j");//獲取當前日期
$dt5=date('Y/n/j',time()+24*3600);//第二天日期
$dt7=date('Y');//獲取當前日期
$dt6=date('YmdHi',time());
$dt11=date('Y-m-d');
$time111=strtotime(date('Y-m-d',time()))*1000;
$dt12=date('Y-m-d',time()+24*3600);
$dt10=date('Y-m-d',time()-24*3600);
$w1=date("w");//當前第幾周
if ($w1<'1') {$w1=7;}
$w2=$w1+1;
function match_string($matches)
{
    return  iconv('UCS-2', 'UTF-8', pack('H4', $matches[1]));
    //return  iconv('UCS-2BE', 'UTF-8', pack('H4', $matches[1]));
    //return  iconv('UCS-2LE', 'UTF-8', pack('H4', $matches[1]));
}


function compress_html($string) {
    $string = str_replace("\r", '', $string); //清除换行符
    $string = str_replace("\n", '', $string); //清除换行符
    $string = str_replace("\t", '', $string); //清除制表符
    return $string;
}

function escape($str) 
{ 
preg_match_all("/[\x80-\xff].|[\x01-\x7f]+/",$str,$r); 
$ar = $r[0]; 
foreach($ar as $k=>$v) 
{ 
if(ord($v[0]) < 128) 
$ar[$k] = rawurlencode($v); 
else 
$ar[$k] = "%u".bin2hex(iconv("UTF-8","UCS-2",$v)); 
} 
return join("",$ar); 
} 




//適合php7以上
function replace_unicode_escape_sequence($match)
{       
		return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');     
}          



$dt1=date('Ymd');//獲取當前日期
$dt2=date('Ymd',time()+24*3600);//第二天日期
$w1=date("w");//當前第幾周

$chn="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<!DOCTYPE tv SYSTEM \"http://api.torrent-tv.ru/xmltv.dtd\">\n<tv generator-info-name=\"秋哥綜合\" generator-info-url=\"https://www.tdm.com.mo/c_tv/?ch=Satellite\">\n";
$idn6=700110;
$cid6=array(

array('195f7f1d-0eca-44f8-b277-15a2728dd102','TVB翡翠娛樂臺(TVBe)'),

array('1d1ccf5f-952c-4563-b0f4-f1098eca3dd6','翡翠一臺(TVB1)'),
array('0a18ad87-8be1-4adf-ae3b-273c42ac9cec','TVB無綫新聞臺(TVB News)'),
array('34f986d8-a74c-4cfa-8b0e-6643466d9463','TVB翡翠劇集臺(TVB Drama)'),

array('e10f2a7f-ecf7-4c17-be6e-3fc59ef5da4e','TVB翡翠綜合臺(TVBJ1)'),
array('be6d5f27-c9e4-4ff9-bdc4-14a42bbe86c6','TVB明珠劇集臺(TVB Pearl Drama)'),

 );

$nid6=sizeof($cid6);

for ($idm6 = 1; $idm6 <= $nid6; $idm6++){
 $idd6=$idn6+$idm6;
   $chn.="<channel id=\"".$cid6[$idm6-1][1]."\"><display-name lang=\"zh\">".$cid6[$idm6-1][1]."</display-name></channel>\n";
                                        }
for ($id6 = 1; $id6 <= $nid6; $id6++){
 $url6='https://tvbsvodassets.tv2zcdn2.com/epgv2/'.$cid6[$id6-1][0].'_'.$dt11.'.json';

//https://tvbsvodassets.tv2zcdn2.com/epgv2/34f986d8-a74c-4cfa-8b0e-6643466d9463_2025-10-05.json

$idd6=$id6+$idn6;
    $ch6 = curl_init();
    curl_setopt($ch6, CURLOPT_URL, $url6);
    curl_setopt($ch6, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch6, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch6, CURLOPT_SSL_VERIFYHOST, FALSE);
     curl_setopt($ch6,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $rk6 = curl_exec($ch6);
  $rk6=str_replace('&','&amp;',$rk6);
    curl_close($ch6);
preg_match('/"programs":(.*?)"channel/i',$rk6,$re6);
//print $re6[1];
preg_match_all('|"start_date_utc": "(.*?)", |i',$re6[1],$us6,PREG_SET_ORDER);//播放開始時間
preg_match_all('|"end_date_utc": "(.*?)",|i',$re6[1],$ue6,PREG_SET_ORDER);//播放結束時間
preg_match_all('|"title": "(.*?)",|i',$re6[1],$ut6,PREG_SET_ORDER);//播放节目
$ryut6=count($ut6);
//print_r($us6);
//print_r($ue6);
//print_r($ut6);
for ( $i6=0 ; $i6<=$ryut6-1 ; $i6++ ) {
$chn.="<programme start=\"".str_replace(' ','',str_replace('-','',str_replace(':','',$us6[$i6][1]))).' +0000'."\" stop=\"".str_replace(' ','',str_replace('-','',str_replace(':','',$ue6[$i6][1]))).' +0000'."\" channel=\"".$cid6[$id6-1][1]."\">\n<title lang=\"zh\">". $ut6[$i6][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";

}

}


//tbc有線
$idn11=400074;//起始节目编号
$cid11=[
    ['005','民視'],
     ['006','CNN'],
 ['914','桃園生活臺'],

            ['007','台視'],
            ['010','大愛電視台'],
            ['009','中視'],
            ['012','人間衛視'],
            ['011','華視'],
            ['013','公共電視'],
            ['014','公視台語台'],
            ['015','好消息頻道'],
            ['016','原住民族頻道'],
            ['017','客家電視台'],
            ['018','BBC EARTH'],
            ['019','Discovery'],
            ['021','TLC 旅遊生活頻道'],
            ['022','動物星球頻道'],
            ['008','Cartoon Network'],
            ['023','Nick Jr.(小尼克)'],
            ['024','MOMO親子台'],
            ['025','東森幼幼台'],
            ['026','緯來綜合台'],
            ['027','八大第一台'],
            ['028','八大綜合台'],
            ['029','三立台灣台'],
            ['030','三立都會台'],
            ['031','華藝中文台'],
            ['032','東森綜合台'],
            ['033','東森超視'],
            ['034','東森購物2台'],
            ['035','momo2台'],
            ['036','中天綜合台'],
            ['037','東風衛視'],
            ['038','年代MUCH TV'],
            ['039','中天娛樂台'],
            ['040','東森戲劇台'],
            ['041','八大戲劇台'],
            ['042','TVBS歡樂台'],
            ['043','緯來戲劇台'],
            ['044','高點電視台'],
            ['045','JET綜合台'],
            ['046','東森購物3台'],
            ['047','東森購物1台'],
            ['048','MOMO1臺'],
            ['049','壹電視新聞台'],
            ['050','年代新聞'],
            ['051','東森新聞台'],
           // ['52','華視新聞資訊台'],
            ['053','民視新聞台'],
            ['054','三立新聞台'],
            ['055','TVBS 新聞台'],
            ['056','TVBS'],
            ['057','東森財經新聞台'],
            ['058','非凡新聞台'],
            ['059','ViVa 1台'],
            ['060','東森購物5台'],
            ['061','CATCH PLAY電影台'],
            ['062','東森電影台'],
            ['063','緯來電影台'],
            ['064','LS TIME電影台'],
            ['065','HBO'],
            ['066','東森洋片台'],
            ['067','AXN'],
            ['068','好萊塢電影台'],
            ['069','AMC電影'],
            ['070','緯來育樂台'],
            ['071','CINEMAX有線'],
         
            ['072','緯來體育台'],
            ['073','DAZN 1'],
            ['074','DAZN 2'],
            ['075','MOMO綜合台'],
          ['077','緯來日本台'],
            ['078','國興衛視'],
            ['079','BBC LIFESTYLE'],
         //   ['79','MTV綜合電視台'],
           // ['80','靖天購物一台有線'],
            ['081','靖天資訊台'],
            ['082','信吉電視台'],
            ['083','信大電視台'],
            ['084','中台灣生活網頻道'],
            ['085','TBC台中生活台'],
            ['086','鏡電視新聞台'],
            ['087','台灣藝術台'],
            ['088','樂視台'],
            ['089','非凡商業台'],
            ['090','三立財經新聞台'],
            ['091','冠軍電視台'],
            ['092','運通財經綜合台'],
            ['093','全球財經網頻道'],
            ['094','誠心電視台'],
            ['095','NHK'],
            ['096','MTV'],
            ['097','Animax'],
            ['098','霹靂台灣台'],
            ['099','海豚綜合台'],
            ['100','八大娛樂台'],
            ['101','十方法界電視台'],
            ['102','壹電視電影台'],
            ['103','華藏衛視'],
            ['104','壹電視資訊台'],
            ['105','佛衛電視慈悲台'],
            ['106','紅豆電視台'],
            ['107','全大電視台'],
            ['108','華藝台灣台'],
            ['109','正德電視台'],
            ['110','天良綜合台'],
            ['111','番薯衛星電視台'],
            ['112','富立電視台'],
            ['113','Z頻道'],
            ['114','冠軍夢想台'],
            ['115','新天地民俗台'],
            ['116','三聖電視台'],
            ['117','威達超舜生活台'],
            ['118','天美麗電視台'],
            ['119','大立電視台'],
            ['120','雙子衛視'],
            ['121','小公視'],
            ['122','華視教育體育文化台'],
            ['123','國會頻道1台'],
            ['124','國會頻道2台'],
            ['125','幸福空間居家台'],
            ['126','高點育樂台'],
            ['127','台灣綜合台'],
            ['128','大台中生活頻道台'],
            ['129','彰化生活台'],
            ['130','唯心電視台'],
            ['131','美麗人生購物台'],
            ['132','大愛二台'],
            ['133','靖天映畫'],
            ['134','靖洋戲劇台'],
            ['135','ROCK ACTION'],
            ['136','Global Trekker'],
            ['137','靖天綜合台'],
            ['138','寶島文化'],
            ['139','靖天日本台靖天'],
            ['149','TaiWan Plus'],
            ['150','BBC News'],
            ['151','民視第一台'],
            ['152','民視台灣台'],
           // ['153','中視菁采台'],
            ['154','中視新聞台'],
            ['155','台視新聞台'],
            ['156','台視財經台'],
            ['157','台視綜合台'],
            ['162','Bloomberg Television'],
           // ['161','BBC World News'],
            ['162','TV5MONDE'],
            ['163','Channel News Asia'],
            ['164','韓國阿里郎'],
            ['202','DREAMWORKS'],
            ['203','Wa暖呢人TV'],
            ['204','靖天電影台'],
            ['205','Cinemalworld'],
            ['207','HBO HD'],
            ['208','HBO Signature 原創鉅獻'],
            ['209','HBO Hits 強檔鉅獻'],
            ['210','HBO Family'],
            ['212','靖天日本台'],
            ['213','EVE有線'],
            ['214','tvN有線'],
            ['215','靖天歡樂台'],
            ['216','HITS'],
            ['217','韓國娛樂台'],
            ['218','博靖天育樂台'],   
            ['219','Lifeime'],
            ['220','罪案偵查頻道'],  
            ['221','寵物頻道'],
            ['222','History 歷史頻道有線'],
            ['223','Discovery Asia'], 
           ['224','Discovery 科學頻道'],  
            ['225','DMAX有線'],
            ['227','環宇新聞台灣台'],
            ['229','亞洲旅遊台'],
            ['230','梅迪奇藝術台'],
             ['240','博斯運動二台有線'], 
           ['241','博斯網球台有線'],
            ['242','博斯運動二台有線'],
            ['243','博斯高球一台有線'],
            ['244','博斯高球二台有線'],
            ['245','博斯魅力網有線'],
            ['246','博斯運動一台有線'], 
          //  ['247','博斯無限台有線'],
            ['247','博斯無限二台有線'],
            ['249','Eurosnews'],
            ['250','Nickelodeon Asia尼克兒童頻道'],
            ['252','達文西頻道'],
           //  ['250','Nickelodeon Asia尼克兒童頻道'],
            ['253','Cbeebies'],
            ['254','CARTOONITO'],
            ['257','靖洋卡通台'],
            ['258','靖天卡通'],  
            ['260','LOVE NATURE 4K'],  
            ['261','ROCK ENTERTAINMENT'],
            ['262','ROCK EXTREME'],



];
$nid11 = sizeof($cid11);

for ($idm11 = 1; $idm11 <= $nid11; $idm11++) {
    $idd11 = $idn11 + $idm11;
    $chn .= "<channel id=\"" . $cid11[$idm11 - 1][1] . "\"><display-name lang=\"zh\">" . $cid11[$idm11 - 1][1] . "</display-name></channel>\n";
}

for ($idm11 = 1; $idm11 <= $nid11; $idm11++) {
    $url11 = 'https://www.tbc.net.tw/EPG/Epg/ChannelV2?channelId=' . $cid11[$idm11 - 1][0];
    $idd11 = $idn11 + $idm11;
    $ch11 = curl_init();
    curl_setopt($ch11, CURLOPT_URL, $url11);
    curl_setopt($ch11, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch11, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch11, CURLOPT_SSL_VERIFYHOST, FALSE);

    $hea11 = [
        'Host: www.tbc.net.tw',
        'Connection: keep-alive',
        'sec-ch-ua: "Chromium";v="142", "Microsoft Edge";v="142", "Not_A Brand";v="99"',
        'sec-ch-ua-mobile: ?0',
        'sec-ch-ua-platform: "Windows"',
        'Upgrade-Insecure-Requests: 1',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
        'Sec-Fetch-Site: none',
        'Sec-Fetch-Mode: navigate',
        'Sec-Fetch-User: ?1',
        'Sec-Fetch-Dest: document',
        'Cookie: ASP.NET_SessionId=i25dijsp5ss2iwmcs3wxgige; COMPANY_INFO=1; __RequestVerificationToken_L0VQRw2=m4vYiV46wDL3cJ6DKYH3RIYbP9jjv2_9fXqemnZwg9fqWOVcrZE3eNMokm80TGqdDBctAO6yHjEqKIachxC4CrHJMFfh8eCC_V3ET3r913E1',
    ];
    curl_setopt($ch11, CURLOPT_HTTPHEADER, $hea11);
    curl_setopt($ch11, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch11, CURLOPT_FOLLOWLOCATION, true);

    $re11 = curl_exec($ch11);
    curl_close($ch11);

    $re11 = compress_html($re11);
    
   $re11=str_replace('/','',str_replace(':','',$re11));
   $re11=str_replace('" time="','',str_replace('~','',$re11));
    
    preg_match('/ \<div class\="epg_con srl"> (.*)\<ul class\="list_program2" channelname="/i', $re11, $uk11);
    
  //  if (isset($uk11[1])) {
      // $uk11_content = str_replace('~', '', str_replace('" time="', '', $uk11[1]));
        preg_match_all('/event" date\="(.*?)" desc/i', $uk11[1], $um11, PREG_SET_ORDER);
        preg_match_all('/desc="(.*?)"/i', $uk11[1], $un11, PREG_SET_ORDER);
        preg_match_all('/title="(.*?)"\>/i', $uk11[1], $ui11, PREG_SET_ORDER);
       // print_r($um11);
      //  print_r($un11);
        $trm11 = count($um11);

        for ($k11 = 0; $k11 <= $trm11; $k11++) {
            if (isset($um11[$k11][1])) {
                $date_str = $um11[$k11][1];
                
                // 假設格式為 YYYYMMDDHHMMHHMM
                // 例如: 2024122518001900 表示 2024-12-25 18:00 到 19:00
                $date_part = substr($date_str, 0, 8);
                $next_date = $date_part +1 ;
                
                $start_time_part = substr($date_str, 8, 4);
                $end_time_part = substr($date_str, 12, 4);

                if ($date_part == $dt1 || $date_part == $dt2 ||$date_part == $dt22 ) {
                    // 檢查是否跨天（開始時間大於結束時間）
                    if ($start_time_part > $end_time_part) {
                        // 跨天情況：結束時間+1天
                        
                        $chn .= "<programme start=\"" . $date_part . $start_time_part . "00 +0800\" stop=\"" . $next_date . $end_time_part . "00 +0800\" channel=\"" .$cid11[$idm11-1][1]."\">\n";
                    } else{
                    
                   
                        // 同一天情況
                        $chn .= "<programme start=\"" . $date_part . $start_time_part . "00 +0800\" stop=\"" . $date_part . $end_time_part . "00 +0800\" channel=\"".$cid11[$idm11-1][1]."\">\n";
                    }
                    
                    $chn .= "<title lang=\"zh\">" . (isset($ui11[$k11][1]) ? htmlspecialchars($ui11[$k11][1]) : '') . "</title>\n";
                    $chn .= "<desc lang=\"zh\">" . (isset($un11[$k11][1]) ? htmlspecialchars($un11[$k11][1]) : '') . "</desc>\n";
                    $chn .= "</programme>\n";
                        }
            }
        }
        }

  
    /*
    
    if (isset($uk11[1])) {
        $uk11_content = str_replace('~', '', str_replace('" time="', '', $uk11[1]));
        preg_match_all('/event" date\="(.*?)" desc/i', $uk11_content, $um11, PREG_SET_ORDER);
        preg_match_all('/desc="(.*?)"/i', $uk11[1], $un11, PREG_SET_ORDER);
        preg_match_all('/title="(.*?)"\>/i', $uk11[1], $ui11, PREG_SET_ORDER);

        $trm11 = count($um11);

        for ($k11 = 0; $k11 = $trm11; $k11++) {
            if (isset($um11[$k11][1])) {
                $date_str = $um11[$k11][1];
                
                // 假設格式為 YYYYMMDDHHMMHHMM
                // 例如: 2024122518001900 表示 2024-12-25 18:00 到 19:00
                $date_part = substr($date_str, 0, 8);
                $start_time_part = substr($date_str, 8, 4);
                $end_time_part = substr($date_str, 12, 4);

                if ($date_part ==$dt1||$date_part == $dt2
                ){
                    // 檢查是否跨天（開始時間大於結束時間）
                    
                    if ($start_time_part > $end_time_part) {
                        // 跨天情況：結束時間+1天
                        $next_date = date('Ymd', strtotime($date_part . ' +1 day'));
                        $chn .= "<programme start=\"" . $date_part . $start_time_part . "00 +0800\" stop=\"" . $next_date . $end_time_part . "00 +0800\" channel=\"" . $cid11[$idm11 - 1][1] . "\">\n";
                        
                    } else{
                        // 同一天情況
                        $chn .= "<programme start=\"" . $date_part . $start_time_part . "00 +0800\" stop=\"" . $date_part . $end_time_part . "00 +0800\" channel=\"" . $cid11[$idm11 - 1][1] . "\">\n";
                    }
                    $chn .= "<title lang=\"zh\">" . (isset($ui11[$k11][1]) ? htmlspecialchars($ui11[$k11][1]) : '') . "</title>\n";
                    $chn .= "<desc lang=\"zh\">" . (isset($un11[$k11][1]) ? htmlspecialchars($un11[$k11][1]) : '') . "</desc>\n";
                    $chn .= "</programme>\n";
                }
            }
        }
    }
}
    }
 
    

//print $uk11[1];

    if (isset($uk11[1])) {
        $uk11_content = str_replace('~', '', str_replace('" time="', '', $uk11[1]));
       // preg_match_all('/\<li class\=" event" date\="(.*?)" desc/i', $uk11_content, $um11, PREG_SET_ORDER);
 preg_match_all('/event" date\="(.*?)" desc/i', $uk11_content, $um11, PREG_SET_ORDER);

        preg_match_all('/desc="(.*?)"/i', $uk11[1], $un11, PREG_SET_ORDER);
        preg_match_all('/title="(.*?)"\>/i', $uk11[1], $ui11, PREG_SET_ORDER);

        $trm11 = count($um11);


//print_r( $um11);
        for ($k11 = 0; $k11 <= $trm11; $k11++){
           
           if (isset($um11[$k11][0])) {
                $date_str= $um11[$k11][1];
                $date_part= substr($date_str[$k11], 0, 8);
                

                $start_time_par = substr($date_str, 8, 4);
                $end_time_part= substr($date_str, -4);

                if ($date_part == $dt1 || $date_part== $dt2) {
                   
                   if ($start_time_part > $end_time_part) {
                        // 跨天情况：结束时间+1天
                        $next_date[$k11] = substr($date_str[$k11], 0, 8)+1;
                        $chn.= "<programme start=\"" . $date_part[$k11] . $start_time_part[$k11] . "00 +0800\" stop=\"" . $next_date[$k11] . $end_time_part[$k11] . "00 +0800\" channel=\"" . $cid11[$idm11 - 1][1] . "\">\n";
                        $chn.= "<title lang=\"zh\">" . (isset($ui11[$k11][1]) ? htmlspecialchars($ui11[$k11][1]) : '') . "</title>\n";
                        $chn .= "<desc lang=\"zh\">" . (isset($un11[$k11][1]) ? htmlspecialchars($un11[$k11][1]) : '') . "</desc>\n";
                        $chn.= "</programme>\n";
                     
                    } else{
                        // 同一天情况
                        $chn.= "<programme start=\"" . $date_part. $start_time_part . "00 +0800\" stop=\"" . $date_part. $end_time_part . "00 +0800\" channel=\"" . $cid11[$idm11 - 1][1] . "\">\n";
                        $chn .= "<title lang=\"zh\">" . (isset($ui11[$k11][1]) ? htmlspecialchars($ui11[$k11][1]) : '') . "</title>\n";
                        $chn .= "<desc lang=\"zh\">" . (isset($un11[$k11][1]) ? htmlspecialchars($un11[$k11][1]) : '') . "</desc>\n";
                        $chn.= "</programme>\n";
                      //$k11=$k11+1;
                    }
                }
            }
        }
    }  
}
*/
    




 $idn111=400274;//起始节目编号
$cid111=array(
    array('404','彩虹頻道'),
    array('405','彩虹E頻道'),
    array('406','彩虹電影'),
    array('407','K頻道'),
    array('408','松視1頻道'),
    array('409','松視2頻道'),
    array('410','松視3頻道'),
    array('411','松視4頻道'),
    array('412','潘多拉完美台'),
    array('413','潘多拉粉紅台'),
    array('414','極限電源'),
    array('415','驚艷成人'),
    array('416','香蕉台'),
    array('417','樂活頻道'),
    array('418','玩家頻道'),
    array('419','HAPPY'),
    array('420','HOT'),
  
);

$nid111=sizeof($cid111);

 for ($idm111 = 1; $idm111 <= $nid111; $idm111++){
    $idd111=$idn111+$idm111;//節目編號 
    $chn.="<channel id=\"".$cid111[$idm111-1][1]."\"><display-name lang=\"zh\">".$cid111[$idm111-1][1]."</display-name></channel>\n";
}

  for ($idm111 = 1; $idm111 <= $nid111; $idm111++){
    $url111='https://www.tbc.net.tw/EPG/Epg/ChannelV2?channelId='.$cid111[$idm111-1][0];
    $idd111=$idn111+$idm111;//節目編號 
    $ch111 = curl_init();
    curl_setopt($ch111, CURLOPT_URL, $url111);
    curl_setopt($ch111, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch111, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch111, CURLOPT_SSL_VERIFYHOST, FALSE);

$hea111= [
            'Host: www.tbc.net.tw',
            'Connection: keep-alive',
            'sec-ch-ua: "Chromium";v="142", "Microsoft Edge";v="142", "Not_A Brand";v="99"',
            'sec-ch-ua-mobile: ?0',
            'sec-ch-ua-platform: "Windows"',
            'Upgrade-Insecure-Requests: 1',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0',
            'Referer: https://www.tbc.net.tw/Epg/Epg/indexV2/0/1',
            'Cookie: COMPANY_INFO=1; ASP.NET_SessionId=u250gvpsuer1zmlmfmrkty5x; __RequestVerificationToken_L0VQRw2=cjCytJ5gSrjgIxPM6UKISYiKK8I1rTDYKimuKy8q8utnVq7lmSSj6NHiHva4skStCNwHpC7XQmCDftUyOA_C2jcmlZ86XzT7NB-8dW8Q41g1',
            'Priority: u=0, i',
            'Sec-Fetch-Site: none',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-User: ?1',
            'Sec-Fetch-Dest: document',
           // 'Cookie: ASP.NET_SessionId=i25dijsp5ss2iwmcs3wxgige; COMPANY_INFO=1; __RequestVerificationToken_L0VQRw2=m4vYiV46wDL3cJ6DKYH3RIYbP9jjv2_9fXqemnZwg9fqWOVcrZE3eNMokm80TGqdDBctAO6yHjEqKIachxC4CrHJMFfh8eCC_V3ET3r913E1',
        ];
     curl_setopt($ch111, CURLOPT_HTTPHEADER, $hea111);
     curl_setopt($ch111, CURLOPT_TIMEOUT, 15);
     curl_setopt($ch111, CURLOPT_FOLLOWLOCATION, true);
    $re111 = curl_exec($ch111);
    curl_close($ch111);
    $re111=compress_html($re111);
    //print $re111;
    preg_match('/ \<div class\="epg_con srl"> (.*)\<ul class\="list_program2" channelname="/i',$re111,$uk111);
    //print $uk111[1];
if(isset($uk111[1])) {
    // 使用正确的正则表达式匹配节目信息
    preg_match_all('/<li class="[^"]*event[^"]*" channelid="\d+" date="(.*?)" time="(.*?)" desc="(.*?)"[^>]*data\.name="(.*?)"/i', $uk111[1], $matches, PREG_SET_ORDER);
    
    $trm111 = count($matches);
    for ($k111 = 0; $k111 < $trm111; $k111++) {   
        // 提取日期和时间部分
        $date_str = $matches[$k111][1];  // 日期：2025/111/22
        $time_str = $matches[$k111][2];  // 时间：00:00~02:40
        $desc = $matches[$k111][3];      // 节目描述
        $title = $matches[$k111][4];     // 节目名称
        
        // 格式化日期
        $date_part = str_replace('/', '', $date_str);  // 202511122
        
        // 解析时间范围
        list($start_time, $end_time) = explode('~', $time_str);
        $start_time_clean = str_replace(':', '', $start_time);  // 0000
        $end_time_clean = str_replace(':', '', $end_time);      // 0240
        
        // 只保留今天和明天的节目
        if($date_part == $dt1 || $date_part == $dt2) {
            if($end_time_clean > $start_time_clean) {
                // 同一天的情况
                $chn .= "<programme start=\"".$date_part.$start_time_clean."00 +0800\" stop=\"".$date_part.$end_time_clean."00 +0800\" channel=\"".$cid111[$idm111-1][1]."\">\n<title lang=\"zh\">".$title."</title>\n<desc lang=\"zh\">".$desc."</desc>\n</programme>\n";
            } else {
                // 跨天的情况（结束时间小于开始时间）
                $prev_date = date('Ymd', strtotime($date_str . ' +1 day'));
                $chn .= "<programme start=\"".$date_part.$start_time_clean."00 +0800\" stop=\"".$prev_date.$end_time_clean."00 +0800\" channel=\"".$cid111[$idm111-1][1]."\">\n<title lang=\"zh\">".$title."</title>\n<desc lang=\"zh\">".$desc."</desc>\n</programme>\n";
            }
        }
    }
}

}


/*
$id655=655081;//起始节目编号
$cid655=array(
   array('ofiii13','公視金獎臺'),
array('ofiii16','空中英語教室HD頻道'),
array('ofiii22','哆啦Ａ夢臺'),
array('ofiii23','新哆啦A夢(中文版)'),
array('ofiii24','新哆啦A夢(中文版)2臺'),
array('ofiii31','TVBS食尚玩家'),
array('ofiii32','東森娛樂臺'),
array('ofiii36','中天亞洲精采臺'),
array('ofiii38','經典劇場'),
array('ofiii39','短劇馬拉松'),
array('ofiii47','Yes娛樂'),
array('ofiii1048','Focus風采戲劇臺'),
array('ofiii50','掏掏新聞'),
array('nnews-zh','倪珍播新聞'),
array('iNEWS','三立新聞iNEWS'),
array('ofiii55','國際大小事'),
array('ofiii64','第1財經'),
array('ofiii70','Golden 強片臺'),
array('ofiii73','周星馳臺'),
array('ofiii74','歐飛電影臺'),
array('ofiii75','歐飛動作電影臺'),
array('ofiii76','Golden影迷臺'),
array('ofiii81','全民星攻略 知識開箱'),
array('ofiii82','Focus探索新知臺'),
array('ofiii83','鄉民大學問'),
array('ofiii85','社會NOW什麼'),
array('ofiii88','啦啦隊獨家專訪'),
array('ofiii89','泰可愛旋風'),
array('ofiii91','演唱會直擊'),
array('ofiii92','健康問良醫'),
array('ofiii94','ASMR行車紀錄'),
array('ofiii95','Freeman @臺灣'),
array('ofiii96','饕客揪愛吃'),
array('ofiii97','人氣動漫預告'),
array('ofiii99','九九敬老頻道'),
array('ofiii100','長安十二時辰'),
array('ofiii101','韶華若錦'),
array('ofiii102','白髮'),
array('ofiii103','三國'),
array('ofiii104','武媚娘傳奇'),
array('ofiii105','長樂曲'),
array('ofiii106','軍師聯盟'),
array('ofiii107','軍師聯盟2 虎嘯龍吟'),
array('ofiii108','國色芳華'),
array('ofiii109','落花時節又逢君'),
array('ofiii110','楚喬傳'),
array('ofiii111','倚天屠龍記 2019'),
array('ofiii112','鳳囚凰'),
array('ofiii113','步步驚心'),
array('ofiii114','九重紫'),
array('ofiii115','私藏浪漫'),
array('ofiii116','去有風的地方'),
array('ofiii117','佔有姜西'),
array('ofiii118','向風而行'),
array('ofiii119','以家人之名'),
array('ofiii120','有你的時光裡'),
array('ofiii121','我的差評女友'),
array('ofiii122','請叫我總監'),
array('ofiii123','三十而已'),
array('ofiii124','月嫂先生'),
array('ofiii125','綜藝大集合'),
array('ofiii126','豬哥壹級棒'),
array('ofiii127','鬼話連篇'),
array('ofiii128','醫師好辣'),
array('ofiii129','WTO姐妹會'),
array('ofiii131','全民星攻略'),
array('ofiii132','健康2.0'),
array('ofiii133','臺灣啟示錄'),
array('ofiii134','法眼黑與白'),
array('ofiii135','天才衝衝衝'),
array('ofiii136','一步一腳印 發現新臺灣'),
array('ofiii137','女人我最大'),
array('ofiii139','威廉沈歡樂送'),
array('ofiii140','木曜4超玩 一日系列'),
array('ofiii141','月曜1起玩'),
array('ofiii142','一字千金'),
array('ofiii143','11點熱吵店'),
array('ofiii144','麥卡貝網路電視'),
array('ofiii145','回到20歲'),
array('ofiii146','白雪公主非死不可'),
array('ofiii147','好搭檔'),
array('ofiii148','月升之江'),
array('ofiii150','臺灣靈異事件'),
array('ofiii151','包青天 1993版'),
array('ofiii152','我的婆婆怎麼那麼可愛'),
array('ofiii153','村裡來了個暴走女外科'),
array('ofiii154','一把青'),
array('ofiii155','茶金'),
array('ofiii156','苦力'),
array('ofiii157','俗女養成記'),
array('ofiii158','麻醉風暴'),
array('ofiii159','雖然等級只有1級但固有技能是最強的'),
array('ofiii160','擁有超常技能的異世界流浪美食家'),
array('ofiii161','離開A級隊伍的我，和從前的弟子往迷宮深處邁進'),
array('ofiii162','在異世界獲得超強能力的我，在現實世界照樣無敵～等級提升改變人生命運～'),
array('ofiii163','第一神拳'),
array('ofiii164','刀劍神域(中文版)'),
array('ofiii165','蠟筆小新(中文版)'),
array('ofiii166','我們這一家'),
array('ofiii167','獵人(中文版)'),
array('ofiii168','中華一番(中文版)'),
array('ofiii169','隊長小翼(中文版)'),
array('ofiii170','SPY X FAMILY 間諜家家酒'),
array('ofiii171','SPY X FAMILY 間諜家家酒(中文版)'),
array('ofiii172','無職轉生，到了異世界就拿出真本事'),
array('ofiii173','關於我轉生變成史萊姆這檔事(國)'),
array('ofiii174','夏目友人帳'),
array('ofiii175','怪醫黑傑克TV(中文版)'),
array('ofiii177','鋼之鍊金術師'),
array('ofiii178','JOJO 的奇妙冒險'),
array('ofiii179','葬送的芙莉蓮'),
array('ofiii180','史上最強弟子兼一(中文版)'),
array('ofiii182','忍者亂太郎(中文版)'),
array('ofiii183','膽大黨'),
array('ofiii184','新忍者哈特利'),
array('ofiii185','凡爾賽玫瑰'),
array('ofiii186','城市獵人'),
array('ofiii187','超人力霸王臺'),
array('ofiii192','生命的贏家'),
array('daystar','DayStar'),
array('ofiii195','海派甜心'),
array('ofiii196','不良笑花'),
array('ofiii198','王子看見二公主'),
array('ofiii200','劣人傳之詭計'),
array('ofiii201','大新聞大爆卦'),
array('ofiii202','新聞大白話'),
array('ofiii203','文茜的世界周報'),
array('ofiii204','寰宇全視界'),
array('ofiii205','文茜的世界財經周報'),
array('ofiii206','環球大戰線'),
array('ofiii207','少康戰情室'),
array('ofiii208','國民大會'),
array('ofiii209','臺灣最前線'),
array('ofiii210','臺灣向前行'),
array('ofiii211','民視異言堂'),
array('ofiii212','新聞觀測站'),
array('ofiii215','電動車時代'),
array('ofiii216','SiCAR愛車酷'),
array('ofiii217','狂人日誌'),
array('ofiii218','脖子解說 Mr. Neck'),
array('ofiii225','食尚玩家-Hello腹餓代'),
array('ofiii226','食尚玩家-天菜就醬吃'),
array('ofiii227','食尚玩家-2天1夜go'),
array('ofiii228','食尚玩家-熱血48小時'),
array('ofiii234','非凡大探索'),
array('ofiii235','臺灣1001個故事'),
array('ofiii236','詹姆士出走料理'),
array('ofiii237','進擊的臺灣'),
array('ofiii238','世界第一等'),
array('ofiii239','臺灣第一等'),
array('ofiii240','溢遊未盡'),
array('ofiii241','臺灣真善美'),
array('ofiii242','請問今晚住誰家'),
array('ofiii243','早餐中國'),
array('ofiii244','溢起趣打卡'),
array('ofiii245','臺灣壹百種味道'),
array('ofiii246','中國美食大探索'),
array('ofiii247','中國旅遊大探索'),
array('ofiii248','秘境不思溢'),
array('ofiii250','紓壓雷雨聲'),
array('ofiii251','療癒下雨聲'),
array('ofiii252','放鬆的爵士午後'),
array('ofiii254','夏日陽光海浪聲'),
array('ofiii255','大自然流水聲'),

);

$nid655=sizeof($cid655);
for ($idm655=1; $idm655 <= $nid655; $idm655++){
 $idd655=$id655+$idm655;
   $chn.="<channel id=\"".$cid655[$idm655-1][1]."\"><display-name lang=\"zh\">".$cid655[$idm655-1][1]."</display-name></channel>\n";
}
for ($idm655=1; $idm655 <= $nid655; $idm655++){

  // $url655='https://www.ofiii.com/_next/data/'.$yuu651[1].'/channel/watch/'.$cid655[$idm655-1][0].'.json?contentId='. 
//$url655='https://www.ofiii.com/_next/data/'.$yuu651[1].'/channel/watch/'.$cid655[$idm655-1][0].'.json?contentId='.$cid655
$url655= "https://www.ofiii.com/_next/data/Qi1G4-x6f7ycEL1ZDdUMG/channel/watch/".$cid655[$idm655-1][0].".json?contentId=".$cid655[$idm655-1][0];
 $idd655=$id655+$idm655;
//print $url655;
 $ch655=curl_init();
curl_setopt($ch655,CURLOPT_URL,$url655);
curl_setopt($ch655,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch655,CURLOPT_SSL_VERIFYHOST,false);
curl_setopt($ch655,CURLOPT_RETURNTRANSFER,1);
$headers655=[
'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 Edg/114.0.1823.86',
//'Referer: Referer: https://www.ebs.co.kr/schedule?channelCd='.$cid655[$idm655-1][0].'&onor='.$cid655[$idm655-1][0],
//'Cookie: WHATAP=zq1p9og4vtfru; XTVID=A230717100728270943; PCID=16895596558411605420033; ONAIR_MODE=DEFAULT; SESSION=502b96e9-e81f-4235-b8db-49a23eb3d60e; ONAIR_RATING=1689661221687:257c4cda

];
curl_setopt($ch655, CURLOPT_HTTPHEADER, $headers655);
$re655=curl_exec($ch655);
$re655=str_replace('&','&amp;',$re655);
//$re655=str_replace('>','',$re655);
//$re655=str_replace('<','&lt;',$re655);
//$re655=$re600 = preg_replace('/\s(?=)/', '',$re655);
$re655= preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re655);// 適合php7
curl_close($ch655);
//print $re655 ;

$data655 = json_decode($re655);
$programs = $data655->pageProps->channel->vod_channel_schedule->programs;


$count = count($programs);

for ($i655 = 0; $i655 <= $count - 2; $i655++) {
    $AirDateTime[$i655] = $programs[$i655]->p_start;
    $endDateTime[$i655] = $programs[$i655 + 1]->p_start;
    $Title655[$i655] = $programs[$i655]->title ?? '';
    $subtitle655[$i655] = $programs[$i655]->subtitle ?? '';
    $abtr655[$i655] = $programs[$i655]->vod_channel_description ?? '';

    // 避免特殊符號錯誤並組成 XML 字串
    $titleClean = str_replace(['<', '>'], '', $Title655[$i655] . $subtitle655[$i655]);
    $descClean = str_replace(['<', '>'], '', $abtr655[$i655]);

    $chn .= "<programme start=\"" . convertMillisToDateTime($AirDateTime[$i655]) . " +0800\" ";
    $chn .= "stop=\"" . convertMillisToDateTime($endDateTime[$i655]) . " +0800\" ";
    $chn .= "channel=\"" . $cid655[$idm655 - 1][1] . "\">\n";
    $chn .= "<title lang=\"zh\">" . $titleClean . "</title>\n";
    $chn .= "<desc lang=\"zh\">" . $descClean . "</desc>\n";
    $chn .= "</programme>\n";
}



$tuu655=json_decode($re655)->pageProps->channel ->vod_channel_schedule->programs;
//print count($tuu655);


for ( $i655=0 ; $i655<=count($tuu655)-2 ; $i655++ ) {
$AirDateTime[$i655]=json_decode($re655)->pageProps->channel ->vod_channel_schedule->programs[$i655]->p_start;//2025-04-24T07:30:00Z
$endDateTime[$i655]=json_decode($re655)->pageProps->channel ->vod_channel_schedule->programs[$i655+1]->p_start;//2025-04-24T07:30:00Z
$Title655[$i655]=json_decode($re655)->pageProps->channel ->vod_channel_schedule->programs[$i655]->title;
$subtitle655[$i655]=json_decode($re655)->pageProps->channel ->vod_channel_schedule->programs[$i655]->subtitle;
$abtr655[$i655]=json_decode($re655)->pageProps->channel ->vod_channel_schedule->programs[$i655]->vod_channel_description;
//$chn.="<programme start=\"".str_replace('-','',str_replace(':','',str_replace('T','', str_replace('Z','', convertMillisToDateTime($AirDateTime[$i655]))))).' +0000'."\" stop=\"".str_replace('-','',str_replace(':','',str_replace('T','', str_replace('Z','', convertMillisToDateTime($endDateTime[$i655]))))).' +0000'."\" channel=\"".$cid655[$idm655-1][1]."\">\n<title lang=\"zh\">".str_replace('<','&lt;',str_replace('>','',$Title655[$i655]))."</title>\n<desc lang=\"zh\">".str_replace('<','&lt;',str_replace('>','',$abtr655[$i655]))."</desc>\n</programme>\n";

$chn.="<programme start=\"".str_replace('-','',str_replace(':','',str_replace('T','', str_replace('Z','', date("YmdHis",$AirDateTime[$i655]/1000))))).' +0000'."\" stop=\"".str_replace('-','',str_replace(':','',str_replace('T','', str_replace('Z','', date("YmdHis",$endDateTime[$i655]/1000))))).' +0000'."\" channel=\"".$cid655[$idm655-1][1]."\">\n<title lang=\"zh\">".str_replace('<','&lt;',str_replace('>','',$Title655[$i655]))."</title>\n<desc lang=\"zh\">".str_replace('<','&lt;',str_replace('>','',$abtr655[$i655]))."</desc>\n</programme>\n";
}

}
*/
$id4=100200;
$cid4=array(
array('cmclassic','tv','天映经典香港'),

array('celestialmovies','com','天映频道马来西亚'),
array('cmplus-tv','com','cmplus新加坡'),
);
$nid4=sizeof($cid4);

for ($idm4 = 1; $idm4 <= $nid4; $idm4++){
 $idd4=$id4+$idm4;
   $chn.="<channel id=\"".$cid4[$idm4-1][2]."\"><display-name lang=\"zh\">".$cid4[$idm4-1][2]."</display-name></channel>\n";
}

for ($idm4 = 1; $idm4 <= $nid4; $idm4++){
$idd4=$id4+$idm4;



    $url4='https://www.'.$cid4[$idm4-1][0].'.'.$cid4[$idm4-1][1].'/schedule.php?lang=tc&date/'.$dt11;
     $ch4 = curl_init ();
curl_setopt ( $ch4, CURLOPT_URL, $url4  );
curl_setopt($ch4,CURLOPT_USERAGENT,'Mozilla/5.0 (Linux; Android 8.1.0; JKM-AL00b) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Mobile Safari/537.36):');
curl_setopt($ch4,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch4,CURLOPT_SSL_VERIFYHOST,false);
curl_setopt ( $ch4, CURLOPT_RETURNTRANSFER, 1 );
$hea4=array(
'Host: www.'.$cid4[$idm4-1][0].'.'.$cid4[$idm4-1][0],
'Connection: keep-alive',
'Upgrade-Insecure-Requests: 1',
'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36 Edg/130.0.0.0',
'Referer: '.  $url4,
//'Accept-Encoding: gzip, deflate, br, zstd',
//'Accept-Language: en-US,en;q=0.9,zh-CN;q=0.8,zh;q=0.7,en-GB;q=0.6',
);
curl_setopt ( $ch4, CURLOPT_HEADER, $hea4 );
//curl_setopt($ch4, CURLOPT_COOKIE, $cookie4);
curl_setopt($ch4,CURLOPT_ENCODING,'Vary: Accept-Encoding');
//curl_setopt($ch4,CURLOPT_USERAGENT, " user-agent:Mozilla/5.0 (Windows NT 6.1; rv:62.0) Gecko/20100101 Firefox/62.0");//浏览器头信息
$re4 = curl_exec ( $ch4 );
$re4=str_replace('&','&amp;',$re4);
$re4=str_replace('<ul>','',$re4);
//$re4=str_replace('00:00','24:00',$re4);
curl_close ( $ch4 );
$re4 = preg_replace('/\s(?=)/', '',$re4);
//$re4 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re4);// 適合php7
preg_match_all('|<pclass="programme-title">(.*?)</p>|i',$re4,$un4,PREG_SET_ORDER);//播放節目名称
preg_match_all('/schedule-time">(.*?)<\/div>/i',$re4,$um4,PREG_SET_ORDER);//播放時間
preg_match_all('/schedule-description">(.*?)<\/div>/i',$re4,$uk4,PREG_SET_ORDER);//播放節目介绍
$trm4=count($un4);
 for ($k4 = 1; $k4 <= $trm4-1; $k4++) { 
    // $chn.="<programme start=\"".$dt1.str_replace(':','',DateTime::createFromFormat('h:i A', $um4[$k4-1][1])->format('H:i')).'00 +0700'."\" stop=\"".$dt1.str_replace(':','',DateTime::createFromFormat('h:i A', $um4[$k4][1])->format('H:i')).'00 +0700'."\" channel=\"".$cid4[$idm4-1][2]."\">\n<title lang=\"zh\">".str_replace('<spanclass="live-btn">播放中</span>','',$un4[$k4-1][1])."</title>\n<desc lang=\"zh\">".$uk4[$k4-1][1]." </desc>\n</programme>\n";
       $chn.="<programme start=\"".$dt1.date("Hi", strtotime($um4[$k4-1][1])).'00 +0800'."\" stop=\"".$dt1.date("Hi", strtotime($um4[$k4][1])).'00 +0800'."\" channel=\"".$cid4[$idm4-1][2]."\">\n<title lang=\"zh\">".str_replace('<spanclass="live-btn">播放中</span>','',$un4[$k4-1][1])."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
       

                                                                                                                                       }

 $url41='https://www.'.$cid4[$idm4-1][0].'.'.$cid4[$idm4-1][1].'/schedule.php?lang=tc&date/'.$dt12;
     $ch41 = curl_init ();
curl_setopt ( $ch41, CURLOPT_URL, $url41  );
curl_setopt($ch41,CURLOPT_USERAGENT,'Mozilla/5.0 (Linux; Android 8.1.0; JKM-AL00b) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Mobile Safari/537.36):');
curl_setopt($ch41,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch41,CURLOPT_SSL_VERIFYHOST,false);
curl_setopt ( $ch41, CURLOPT_RETURNTRANSFER, 1 );
$hea41=array(
'Host: www.'.$cid4[$idm4-1][0].'.'.$cid4[$idm4-1][0],
'Connection: keep-alive',
'Upgrade-Insecure-Requests: 1',
'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36 Edg/130.0.0.0',
'Referer: '.  $url41,
//'Accept-Encoding: gzip, deflate, br, zstd',
//'Accept-Language: en-US,en;q=0.9,zh-CN;q=0.8,zh;q=0.7,en-GB;q=0.6',
);
curl_setopt ( $ch41, CURLOPT_HEADER, $hea41 );
//curl_setopt($ch4, CURLOPT_COOKIE, $cookie4);
curl_setopt($ch41,CURLOPT_ENCODING,'Vary: Accept-Encoding');
//curl_setopt($ch41,CURLOPT_USERAGENT, " user-agent:Mozilla/5.0 (Windows NT 6.1; rv:62.0) Gecko/20100101 Firefox/62.0");//浏览器头信息
$re41 = curl_exec ( $ch41 );
//$re41=str_replace('00:00','24:00',$re4);
$re41=str_replace('&','&amp;',$re41);
$re41=str_replace('<ul>','',$re41);
curl_close ( $ch41 );
$re41 = preg_replace('/\s(?=)/', '',$re41);
//$re41 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re41);// 適合php7
preg_match_all('|<pclass="programme-title">(.*?)</p>|i',$re41,$un41,PREG_SET_ORDER);//播放節目名称
preg_match_all('/schedule-time">(.*?)<\/div>/i',$re41,$um41,PREG_SET_ORDER);//播放時間
preg_match_all('/schedule-description">(.*?)<\/div>/i',$re41,$uk41,PREG_SET_ORDER);//播放節目介绍
$trm41=count($un41);
//$chn.="<programme start=\"".$dt1.str_replace(':','',DateTime::createFromFormat('h:i A', $um4[$trm4-1][1])->format('H:i')).'00 +0700'."\" stop=\"".$dt2.str_replace(':','',DateTime::createFromFormat('h:i A', $um41[0][1])->format('H:i')).'00 +0700'."\" channel=\"".$cid4[$idm4-1][2]."\">\n<title lang=\"zh\">".str_replace('<spanclass="live-btn">播放中</span>','',$un4[$trm4-1][1])."</title>\n<desc lang=\"zh\">".$uk4[$k4-1][1]." </desc>\n</programme>\n";
$chn.="<programme start=\"".$dt1.date("Hi", strtotime($um4[$trm41-1][1])).'00 +0800'."\" stop=\"".$dt2.date("Hi", strtotime($um41[0][1])).'00 +0800'."\" channel=\"".$cid4[$idm4-1][2]."\">\n<title lang=\"zh\">".str_replace('<spanclass="live-btn">播放中</span>','',$un4[$trm41-1][1])."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
       


 for ($k41 = 1; $k41 <= $trm41-1; $k41++) { 
    // $chn.="<programme start=\"".$dt2.str_replace(':','',DateTime::createFromFormat('h:i A', $um41[$k41-1][1])->format('H:i')).'00 +0700'."\" stop=\"".$dt2.str_replace(':','',DateTime::createFromFormat('h:i A', $um41[$k41][1])->format('H:i')).'00 +0700'."\" channel=\"".$cid4[$idm4-1][2]."\">\n<title lang=\"zh\">".str_replace('<spanclass="live-btn">播放中</span>','',$un41[$k41-1][1])."</title>\n<desc lang=\"zh\">".$uk41[$k41-1][1]." </desc>\n</programme>\n";
       $chn.="<programme start=\"".$dt2.date("Hi", strtotime($um41[$k41-1][1])).'00 +0800'."\" stop=\"".$dt2.date("Hi", strtotime($um41[$k41][1])).'00 +0800'."\" channel=\"".$cid4[$idm4-1][2]."\">\n<title lang=\"zh\">".str_replace('<spanclass="live-btn">播放中</span>','',$un41[$k41-1][1])."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
       

                                                                                                                                       }

//$chn.="<programme start=\"".$dt2.str_replace(':','',DateTime::createFromFormat('h:i A', $um41[$trm41-1][1])->format('H:i')).'00 +0700'."\" stop=\"".$dt2.'235900 +0700'."\" channel=\"".$cid4[$idm4-1][2]."\">\n<title lang=\"zh\">".str_replace('<spanclass="live-btn">播放中</span>','',$un41[$trm41-1][1])."</title>\n<desc lang=\"zh\">".$uk41[$k41-1][1]." </desc>\n</programme>\n";
$chn.="<programme start=\"".$dt2.date("Hi", strtotime($um41[$trm41-1][1])).'00 +0800'."\" stop=\"".$dt2.'235900 +0800'."\" channel=\"".$cid4[$idm4-1][2]."\">\n<title lang=\"zh\">".str_replace('<spanclass="live-btn">播放中</span>','',$un4[$k4-1][1])."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
       

}



/*
$idn5=69999;
$cid5=array(

array('CWIN', 'SUPER FREE (免費)'),
array('SVAR', 'SUPER獎門人 (免費)'),
array('SEYT', 'SUPER EYT (免費)'),
array('SFOO', 'SUPER識食 (免費)'),
array('STRA', 'SUPER識嘆 (免費)'),
array('SMUS', 'SUPER Music (免費)'),
array('SGOL', 'SUPER金曲 (免費)'),
array('SSIT', 'SUPER煲劇 (免費)'),
array('STVM', 'SUPER劇場 (免費)'),
array('SDOC', 'SUPER話當年 (免費)'),
array('SSPT', 'SUPER Sports (免費)'),
array('C18', 'myTV SUPER 18台'),
array('C28', '28AI智慧賽馬 (免費)'),
array('EV11', '2025香港小姐競選---我最喜愛佳麗投票頻道'),

array('TVG', '黃金翡翠台 (免費)'),
array('JUHD', '翡翠台(超高清)'),
array('J', '翡翠台 (免費)'),
array('B', 'TVB Plus (免費)'),
array('C', '無綫新聞台 (免費)'),
array('P', '明珠台 (免費)'),
array('CTVC', '千禧經典台'),
array('CTVS', '亞洲劇台'),
array('CDR3', '華語劇台'),
array('TVO', '黃金華劇台'),
array('CTVE', '娛樂新聞台 (免費)'),
array('CCOC', '戲曲台'),
array('KID', 'SUPER Kids Channel'),
array('ZOO', 'ZooMoo'),
array('CNIKO', 'Nickelodeon'),
array('CNIJR', 'Nick Jr'),
array('CCLM', '粵語片台'),
array('CMAM', '美亞電影台'),
array('CTHR', 'Thrill'),
array('CCCM', '天映經典頻道'),
array('CMC', '中國電影頻道'),
array('CRTX', 'ROCK Action'),
array('POPC', 'PopC'),
array('ACTM', 'Action Hollywood Movies (免費)'),
array('RCM', 'Rialto Classic Movies (RCM) (免費)'),
array('CKIX', 'KIX'),
array('TRSP', 'TRACE Sport Stars (免費)'),
array('LNH', 'Love Nature HD'),
array('LN4', 'Love Nature 4K'),
array('SMS', 'Global Trekker'),
array('PETC', 'Pet Club TV (免費)'),
array('GLBT', 'Globetrotter (免費)'),
array('DOCV', 'DocsVille (免費)'),
array('PULS', 'Pulse Documentaries (免費)'),
array('CRTE', 'ROCK綜藝娛樂'),
array('CAXN', 'AXN'),
array('CANI', 'Animax'),
array('CJTV', 'tvN'),
array('CTS1', '無線衛星亞洲台'),
array('CRE', '創世電視 (免費)'),
array('FBX', 'FashionBox'),
array('CMEZ', 'Mezzo Live HD'),
array('CC1', '中央電視台綜合頻道 (港澳版) (免費)'),
array('CGD', 'CGTN (中國環球電視網)記錄頻道 (免費)'),
array('CGE', 'CGTN (中國環球電視網)英語頻道 (免費)'),
array('DTV', '東方衛視國際頻道 (免費)'),
array('PCC', '鳳凰衛視中文台 (免費)'),
array('PIN', '鳳凰衛視資訊台 (免費)'),
array('PHK', '鳳凰衛視香港台 (免費)'),
array('CC4', '中國中央電視台中文國際頻道 (免費)'),
array('CCE', '中國中央電視台娛樂頻道 (免費)'),
array('CCO', '中國中央電視台戲曲頻道 (免費)'),
array('YNTV', '雲南瀾湄國際衛視 (免費)'),
array('AHTV', '安徽廣播電視台國際頻道 (免費)'),
array('BJTV', '北京電視台國際頻道 (免費)'),
array('GXTV', '廣西電視台國際頻道 (免費)'),
array('FJTV', '福建海峽衛視國際頻道 (免費)'),
array('HNTV', '湖南電視台國際頻道 (免費)'),
array('JSTV', '江蘇電視台國際頻道 (免費)'),
array('GBTV', '廣東廣播電視台大灣區衛視頻道 (免費)'),
array('ZJTV', '浙江電視台國際頻道 (免費)'),
array('SZTV', '深圳衛視國際頻道 (免費)'),
array('NOW7', 'NOW 70s (免費)'),
array('NOW8', 'NOW 80s (免費)'),
array('NOWR', 'NOW ROCK (免費)'),
array('NOW9', 'NOW 90s00s (免費)'),
array('CONC', 'Concerto (免費)'),
array('TRUR', 'TRACE Urban (免費)'),
array('CTSN', '無線衛星新聞台'),
array('CCNA', '亞洲新聞台'),
array('CJAZ', '半島電視台英語頻道'),
array('CF24', 'France 24'),
array('CDW1', 'DW'),
array('CNHK', 'NHK World-Japan'),
array('CARI', 'Arirang TV'),
array('NSWD', 'NewsWorld (免費)'),
array('EVT2', 'myTV SUPER直播足球2台'),
array('EVT3', 'myTV SUPER直播足球3台'),
array('EVT4', 'myTV SUPER直播足球4台'),
array('EVT5', 'myTV SUPER直播足球5台'),
array('EVT6', 'myTV SUPER直播足球6台'),
array('EVT7', 'myTV SUPER直播足球7台'),
//array('EVT8', 'myTV SUPER直播足球8台'),
//array('EVT9', 'myTV SUPER直播足球9台'),
array('EVT8', '二零二五香港公開羽毛球錦標賽-世界羽聯世界巡迴賽超級500'),
array('OL00', '全運800台'),
array('OL01', '全運801台'),
array('OL02', '全運802台'),
array('OL03', '全運803台'),
array('OL04', '全運804台'),
array('OL05', '全運805台'),
array('OL06', '全運806台'),
array('TEST', '測試頻道'),

 );

$nid5=sizeof($cid5);

for ($idm5 = 1; $idm5 <= $nid5; $idm5++){
 $idd5=$idn5+$idm5;
   $chn.="<channel id=\"".$cid5[$idm5-1][1]."\"><display-name lang=\"zh\">".$cid5[$idm5-1][1]."</display-name></channel>\n";

                                         }
for ($idm5 = 1; $idm5 <= $nid5; $idm5++){
 
$idd5=$idn5+$idm5;


$url52='https://content-api.mytvsuper.com/v1/epg?platform=web&country_code=TW&network_code='.$cid5[$idm5-1][0].'&from='.$dt22.'&to='.$dt22;


    $ch52 = curl_init();
    curl_setopt($ch52, CURLOPT_URL, $url52);
    curl_setopt($ch52, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch52, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch52, CURLOPT_SSL_VERIFYHOST, FALSE);
     curl_setopt($ch52,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $re52 = curl_exec($ch52);
   $re52=compress_html($re52);
  $re52=str_replace('&','&amp;',$re52);
    $re52=str_replace('>','',$re52);
 $re52=str_replace('<','&lt;',$re52);
$re52=str_replace('/','',$re52);
$re52=stripslashes($re52);
$re52=str_replace('.','',$re52);
 curl_close($ch52);
//print  $re51;
$re52=str_replace('[','',$re52);
$re52=str_replace(']','',$re52);



preg_match_all('/"start_datetime":"(.*?)",/i',$re52,$um52,PREG_SET_ORDER);//播放時間
preg_match_all('/programme_title_tc":"(.*?)",/i',$re52,$un52,PREG_SET_ORDER);//播放節目
preg_match_all('/"episode_synopsis_tc":"(.*?)",/i',$re52,$uk52,PREG_SET_ORDER);//播放節目介绍



  $url5='https://content-api.mytvsuper.com/v1/epg?platform=web&country_code=TW&network_code='.$cid5[$idm5-1][0].'&from='.$dt1.'&to='.$dt1;
 $ch5 = curl_init();
    curl_setopt($ch5, CURLOPT_URL, $url5);
    curl_setopt($ch5, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch5, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch5, CURLOPT_SSL_VERIFYHOST, FALSE);
     curl_setopt($ch5,CURLOPT_ENCODING,'Vary: Accept-Encoding');
   
 $re5 = curl_exec($ch5);
   $re5=compress_html($re5);
  $re5=str_replace('&','&amp;',$re5);
 $re5=str_replace('>','',$re5);
 $re5=str_replace('<','&lt;',$re5);
 $re5=str_replace('/','',$re5);
$re5=stripslashes($re5);
$re5=str_replace('.','',$re5);
$re5=str_replace('[','',$re5);
$re5=str_replace(']','',$re5);




    curl_close($ch5);
//print $re5;

preg_match_all('/"start_datetime":"(.*?)",/i',$re5,$um5,PREG_SET_ORDER);//播放時間
preg_match_all('/programme_title_tc":"(.*?)",/i',$re5,$un5,PREG_SET_ORDER);//播放節目
preg_match_all('/"episode_synopsis_tc":"(.*?)",/i',$re5,$uk5,PREG_SET_ORDER);//播放節目介绍
 $url51='https://content-api.mytvsuper.com/v1/epg?platform=web&country_code=TW&network_code='.$cid5[$idm5-1][0].'&from='.$dt2.'&to='.$dt2;


    $ch51 = curl_init();
    curl_setopt($ch51, CURLOPT_URL, $url51);
    curl_setopt($ch51, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch51, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch51, CURLOPT_SSL_VERIFYHOST, FALSE);
     curl_setopt($ch51,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $re51 = curl_exec($ch51);
   $re51=compress_html($re51);
  $re51=str_replace('&','&amp;',$re51);
    $re51=str_replace('>','',$re51);
 $re51=str_replace('<','&lt;',$re51);
$re51=str_replace('/','',$re51);
$re51=stripslashes($re51);
$re51=str_replace('.','',$re51);

$re51=str_replace('[','',$re51);
$re51=str_replace(']','',$re51);



 curl_close($ch51);
//print  $re51;

preg_match_all('/"start_datetime":"(.*?)",/i',$re51,$um51,PREG_SET_ORDER);//播放時間
preg_match_all('/programme_title_tc":"(.*?)",/i',$re51,$un51,PREG_SET_ORDER);//播放節目
preg_match_all('/"episode_synopsis_tc":"(.*?)",/i',$re51,$uk51,PREG_SET_ORDER);//播放節目介绍

//print_r($um51);
//print_r($un51);
//print_r($uk51);


//$re5 = preg_replace('/\s(?=)/', '',$re5);
//preg_match('/divclass="epg-list(.*?)epg-list"/i',$re5,$u5);//


//print_r($um5);
//print_r($un5);
//print_r($uk5);
$trm52=count($um52);
for ($k52 = 1; $k52 <= $trm52-1 ; $k52++) {  


     $chn.="<programme start=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$um52[$k52-1][1]))).' +0800'."\" stop=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$um52[$k52][1]))).' +0800'."\" channel=\"".$cid5[$idm5-1][1]."\">\n<title lang=\"zh\">".trim($un52[$k52-1][1])."</title>\n<desc lang=\"zh\">".trim($uk52[$k52-1][1])." </desc>\n</programme>\n";
 
                                                                                                                            

   }                                                                                                                                                                                                                       

 
   $chn.="<programme start=\"".str_replace(':','',str_replace('-','',str_replace(' ','',$um52[$trm52-1][1]))).' +0800'."\" stop=\"".str_replace(':','',str_replace('-','',str_replace(' ','',$um5[0][1]))).' +0800'."\" channel=\"".$cid5[$idm5-1][1]."\">\n<title lang=\"zh\">".trim($un52[$trm52-1][1])."</title>\n<desc lang=\"zh\">".trim($uk52[$trm52-1][1])." </desc>\n</programme>\n";                                                                                                                                        





$trm5=count($um5);
for ($k5 = 1; $k5 <= $trm5-1 ; $k5++) {  


     $chn.="<programme start=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$um5[$k5-1][1]))).' +0800'."\" stop=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$um5[$k5][1]))).' +0800'."\" channel=\"".$cid5[$idm5-1][1]."\">\n<title lang=\"zh\">".trim($un5[$k5-1][1])."</title>\n<desc lang=\"zh\">".trim($uk5[$k5-1][1])." </desc>\n</programme>\n";
  //     $chn.="<programme start=\"".$dt2.date("Hi", strtotime($um81[$k81-1][1])).'00 +0800'."\" stop=\"".$dt2.date("Hi", strtotime($um81[$k81][1])).'00 +0800'."\" channel=\"".$cid8[$idm8-1][1]."\">\n<title lang=\"zh\">".str_replace('<spanclass="live-btn">播放中</span>','',$un81[$k81-1][1])."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";

                                                                                                                            

   }                                                                                                                                                                                                                       

 
   $chn.="<programme start=\"".str_replace(':','',str_replace('-','',str_replace(' ','',$um5[$trm5-1][1]))).' +0800'."\" stop=\"".str_replace(':','',str_replace('-','',str_replace(' ','',$um51[0][1]))).' +0800'."\" channel=\"".$cid5[$idm5-1][1]."\">\n<title lang=\"zh\">".trim($un5[$trm5-1][1])."</title>\n<desc lang=\"zh\">".trim($uk5[$trm5-1][1])." </desc>\n</programme>\n";                                                                                                                                        








$trm51=count($um51);
for ($k51 = 1; $k51 <= $trm51-1 ; $k51++) {  
                                     
 $chn.="<programme start=\"".str_replace(':','',str_replace('-','',str_replace(' ','',$um51[$k51-1][1]))).' +0800'."\" stop=\"".str_replace(':','',str_replace('-','',str_replace(' ','',$um51[$k51][1]))).' +0800'."\" channel=\"".$cid5[$idm5-1][1]."\">\n<title lang=\"zh\">".trim($un51[$k51-1][1])."</title>\n<desc lang=\"zh\">".trim($uk51[$k51-1][1])."</desc>\n</programme>\n";
                                                                                           }                                                                                                                                                                                                                       
 
   $chn.="<programme start=\"".str_replace(':','',str_replace('-','',str_replace(' ','',$um51[$trm51-1][1]))).' +0800'."\" stop=\"".$dt21.'060000 +0800'."\" channel=\"".$cid5[$idm5-1][1]."\">\n<title lang=\"zh\">".trim($un51[$trm51-1][1])."</title>\n<desc lang=\"zh\">".trim($uk51[$trm51-1][1])." </desc>\n</programme>\n";                                                                                                     
}
*/

$url5='https://content-api.mytvsuper.com/v1/channel/list?platform=web&country_code=TW&profile_class=general';
$ch5=curl_init();
curl_setopt($ch5,CURLOPT_URL,$url5);
curl_setopt($ch5,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch5,CURLOPT_SSL_VERIFYHOST,false);
curl_setopt($ch5,CURLOPT_RETURNTRANSFER,1);
$re5=curl_exec($ch5);
  $re5=str_replace('&','&amp;',$re5);
curl_close($ch5);
 $data5=json_decode($re5)->channels;
$tuu5=count($data5);

for ( $i5=0 ; $i5<=$tuu5-1 ; $i5++ ) {
$name_tc=json_decode($re5)->channels[$i5]->name_tc;
$network_code=json_decode($re5)->channels[$i5]->network_code;
//$fnID=json_decode($re5)->Data[$i5]->fnID;
  $chn.="<channel id=\"".$name_tc."\"><display-name lang=\"zh\">".$name_tc."</display-name></channel>\n";//用於xml的channel輸出
                                  }
for ( $i5=0 ; $i5<=$tuu5-1 ; $i5++ ) {
$name_tc=json_decode($re5)->channels[$i5]->name_tc;
$network_code=json_decode($re5)->channels[$i5]->network_code;
//前天数据

$urk52='https://content-api.mytvsuper.com/v1/epg?platform=web&country_code=TW&network_code='.$network_code.'&from='.$dt22.'&to='.$dt22;
 $ch52 = curl_init();
    curl_setopt($ch52, CURLOPT_URL, $urk52);
    curl_setopt($ch52, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch52, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch52, CURLOPT_SSL_VERIFYHOST, FALSE);
     curl_setopt($ch52,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $re52 = curl_exec($ch52);
   $re52=compress_html($re52);
  $re52=str_replace('&','&amp;',$re52);
 $re52=str_replace('>','',$re52);
 $re52=str_replace('<','&lt;',$re52);
 $re52=str_replace('/','',$re52);
$re52=stripslashes($re52);
$re52=str_replace('.','',$re52);
$re52=str_replace('[','',$re52);
$re52=str_replace(']','',$re52);
    curl_close($ch52);


preg_match_all('/"start_datetime":"(.*?)",/i',$re52,$um52,PREG_SET_ORDER);//播放時間
preg_match_all('/programme_title_tc":"(.*?)",/i',$re52,$un52,PREG_SET_ORDER);//播放節目
preg_match_all('/"episode_synopsis_tc":"(.*?)",/i',$re52,$uk52,PREG_SET_ORDER);//播放節目介绍


//当天数据

  $url53='https://content-api.mytvsuper.com/v1/epg?platform=web&country_code=TW&network_code='.$network_code.'&from='.$dt1.'&to='.$dt1;
 $ch53 = curl_init();
    curl_setopt($ch53, CURLOPT_URL, $url53);
    curl_setopt($ch53, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch53, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch53, CURLOPT_SSL_VERIFYHOST, FALSE);
     curl_setopt($ch53,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $re53 = curl_exec($ch53);
   $re53=compress_html($re53);
  $re53=str_replace('&','&amp;',$re53);
 $re53=str_replace('>','',$re53);
 $re53=str_replace('<','&lt;',$re53);
 $re53=str_replace('/','',$re53);
$re53=stripslashes($re53);
$re53=str_replace('.','',$re53);
$re53=str_replace('[','',$re53);
$re53=str_replace(']','',$re53);
    curl_close($ch53);
//print $re53;
preg_match_all('/"start_datetime":"(.*?)",/i',$re53,$um53,PREG_SET_ORDER);//播放時間
preg_match_all('/programme_title_tc":"(.*?)",/i',$re53,$un53,PREG_SET_ORDER);//播放節目
preg_match_all('/"episode_synopsis_tc":"(.*?)",/i',$re53,$uk53,PREG_SET_ORDER);//播放節目介绍

//第二天数据

 $url51='https://content-api.mytvsuper.com/v1/epg?platform=web&country_code=TW&network_code='.$network_code.'&from='.$dt2.'&to='.$dt2;
    $ch51 = curl_init();
    curl_setopt($ch51, CURLOPT_URL, $url51);
    curl_setopt($ch51, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch51, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch51, CURLOPT_SSL_VERIFYHOST, FALSE);
     curl_setopt($ch51,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $re51 = curl_exec($ch51);
   $re51=compress_html($re51);
  $re51=str_replace('&','&amp;',$re51);
    $re51=str_replace('>','',$re51);
 $re51=str_replace('<','&lt;',$re51);
$re51=str_replace('/','',$re51);
$re51=stripslashes($re51);
$re51=str_replace('.','',$re51);
$re51=str_replace('[','',$re51);
$re51=str_replace(']','',$re51);
 curl_close($ch51);
//print  $re51;
preg_match_all('/"start_datetime":"(.*?)",/i',$re51,$um51,PREG_SET_ORDER);//播放時間
preg_match_all('/programme_title_tc":"(.*?)",/i',$re51,$un51,PREG_SET_ORDER);//播放節目
preg_match_all('/"episode_synopsis_tc":"(.*?)",/i',$re51,$uk51,PREG_SET_ORDER);//播放節目介绍

$trm52=count($um52);
for ($k52 = 1; $k52 <= $trm52-1 ; $k52++) {  
     $chn.="<programme start=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$um52[$k52-1][1]))).' +0800'."\" stop=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$um52[$k52][1]))).' +0800'."\" channel=\"".$name_tc."\">\n<title lang=\"zh\">".trim($un52[$k52-1][1])."</title>\n<desc lang=\"zh\">".trim($uk52[$k52-1][1])." </desc>\n</programme>\n";
   }                                                                                                                                                                                                                       
   $chn.="<programme start=\"".str_replace(':','',str_replace('-','',str_replace(' ','',$um52[$trm52-1][1]))).' +0800'."\" stop=\"".str_replace(':','',str_replace('-','',str_replace(' ','',$um53[0][1]))).' +0800'."\" channel=\"".$name_tc."\">\n<title lang=\"zh\">".trim($un52[$trm52-1][1])."</title>\n<desc lang=\"zh\">".trim($uk52[$trm52-1][1])." </desc>\n</programme>\n";                                                                                                                                        

$trm53=count($um53);
for ($k53 = 1; $k53 <= $trm53-1 ; $k53++) {  
     $chn.="<programme start=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$um53[$k53-1][1]))).' +0800'."\" stop=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$um53[$k53][1]))).' +0800'."\" channel=\"".$name_tc."\">\n<title lang=\"zh\">".trim($un53[$k53-1][1])."</title>\n<desc lang=\"zh\">".trim($uk53[$k53-1][1])." </desc>\n</programme>\n";
   }                                                                                                                                                                                                                       
   $chn.="<programme start=\"".str_replace(':','',str_replace('-','',str_replace(' ','',$um53[$trm53-1][1]))).' +0800'."\" stop=\"".str_replace(':','',str_replace('-','',str_replace(' ','',$um51[0][1]))).' +0800'."\" channel=\"".$name_tc."\">\n<title lang=\"zh\">".trim($un53[$trm53-1][1])."</title>\n<desc lang=\"zh\">".trim($uk53[$trm53-1][1])." </desc>\n</programme>\n";                                                                                                                                        


$trm51=count($um51);
for ($k51 = 1; $k51 <= $trm51-1 ; $k51++) {  
 $chn.="<programme start=\"".str_replace(':','',str_replace('-','',str_replace(' ','',$um51[$k51-1][1]))).' +0800'."\" stop=\"".str_replace(':','',str_replace('-','',str_replace(' ','',$um51[$k51][1]))).' +0800'."\" channel=\"".$name_tc."\">\n<title lang=\"zh\">".trim($un51[$k51-1][1])."</title>\n<desc lang=\"zh\">".trim($uk51[$k51-1][1])."</desc>\n</programme>\n";
                                                                                           }                                                                                                                                                                                 
   $chn.="<programme start=\"".str_replace(':','',str_replace('-','',str_replace(' ','',$um51[$trm51-1][1]))).' +0800'."\" stop=\"".$dt21.'060000 +0800'."\" channel=\"".$name_tc."\">\n<title lang=\"zh\">".trim($un51[$trm51-1][1])."</title>\n<desc lang=\"zh\">".trim($uk51[$trm51-1][1])." </desc>\n</programme>\n";                                                           
}

  $chn.="<channel id=\"中天亞洲台\"><display-name lang=\"zh\">中天亞洲台</display-name></channel>\n";
$url79='https://asia-east1-ctitv-237901.cloudfunctions.net/ProgramList-Api2??chid=a2&start='.$dt1.'&end='.$dt1.'&_=';
$ch79= curl_init ();
curl_setopt ( $ch79, CURLOPT_URL, $url79 );
curl_setopt ( $ch79, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt($ch79,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch79,CURLOPT_SSL_VERIFYHOST,false);
curl_setopt($ch79,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:131.0) Gecko/20100101 Firefox/131.0');
//curl_setopt($ch79,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $re79 = curl_exec($ch79);
   $re79=str_replace('T','',$re79);
   $re79=str_replace('Z','',$re79);
$re79=str_replace('&','&amp;',$re79);




   curl_close($ch79);

//print $re79;

//$data79=json_decode($re79)->title;

$ryut79=count(json_decode($re79));
//print $ryut;

for ($k79 =0; $k79 < $ryut79; $k79++){



$title79=json_decode($re79)[$k79]->title;
$start79=json_decode($re79)[$k79]->start;
$end79=json_decode($re79)[$k79]->end;
   $chn.="<programme start=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$start79))).' +0800'."\" stop=\"".str_replace(' ','',str_replace(':','',str_replace('-','', $end79))).' +0800'."\" channel=\"中天亞洲台\">\n<title lang=\"zh\">".$title79."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";



}

$url791='https://asia-east1-ctitv-237901.cloudfunctions.net/ProgramList-Api2?chid=a2&start='.$dt1.'&end='.$dt2.'&_=';

$ch791= curl_init ();
curl_setopt ( $ch791, CURLOPT_URL, $url791 );
curl_setopt ( $ch791, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt($ch791,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch791,CURLOPT_SSL_VERIFYHOST,false);
curl_setopt($ch791,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:131.0) Gecko/20100101 Firefox/131.0');
//curl_setopt($ch791,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $re791 = curl_exec($ch791);
$re791=str_replace('T','',$re791);
   $re791=str_replace('Z','',$re791);
    $re791=str_replace('&','&amp;',$re791);



   curl_close($ch791);
$ryut791=count(json_decode($re791));


for ($k791 =0; $k791 < $ryut791; $k791++){



$title791=json_decode($re791)[$k791]->title;
$start791=json_decode($re791)[$k791]->start;
$end791=json_decode($re791)[$k791]->end;
   $chn.="<programme start=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$start791))).' +0800'."\" stop=\"".str_replace(' ','',str_replace(':','',str_replace('-','', $end791))).' +0800'."\" channel=\"中天亞洲台\">\n<title lang=\"zh\">".$title791."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";



}

  $chn.="<channel id=\"龍華卡通台\"><display-name lang=\"zh\">龍華卡通台</display-name></channel>\n";//用於xml的channel輸出
 $chn.="<channel id=\"龍華日韓台\"><display-name lang=\"zh\">龍華日韓台</display-name></channel>\n";//用於xml的channel輸出
 $chn.="<channel id=\"龍華偶像台OTT\"><display-name lang=\"zh\">龍華偶像台OTT</display-name></channel>\n";//用於xml的channel輸出
$url40 = 'https://www.ltv.com.tw/wp-admin/admin-ajax.php';

$headers40 = [
    'Host: www.ltv.com.tw',
    'sec-ch-ua: "Not)A;Brand";v="24", "Chromium";v="116"',
    'DNT: 1',
    'sec-ch-ua-mobile: ?0',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.5845.97 Safari/537.36 SE 2.X MetaSr 1.0',
    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
    'X-Requested-With: XMLHttpRequest',
    'sec-ch-ua-platform: "Windows"',
    'Origin: https://www.ltv.com.tw',
    'Referer: https://www.ltv.com.tw/ott%e7%af%80%e7%9b%ae%e8%a1%a8/'
];

$data40 = [
    'action' => 'timetable',
    'type' => 51,
    'play_date' => $dt11
];

$ch40 = curl_init();
curl_setopt($ch40, CURLOPT_URL, $url40);
curl_setopt($ch40, CURLOPT_POST, true);
curl_setopt($ch40, CURLOPT_HTTPHEADER, $headers40);
curl_setopt($ch40, CURLOPT_POSTFIELDS, http_build_query($data40));
curl_setopt($ch40, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch40, CURLOPT_SSL_VERIFYPEER, false); // 忽略SSL验证（测试用）
$response40 = curl_exec($ch40);
$response40 = str_replace('&','&amp;',$response40);
//$response40 = STR_REPLACE('>','',$response40);
//$response40 = str_replace('<','&lt;',$response40);


curl_close($ch40);
$response40 = str_replace(array("\r\n", "\r", "\n"), "", $response40);
$response40 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $response40); // 适合php7
$response40 = preg_replace('/\s+/', '', $response40);
//print $response ;
// Corrected regex pattern to properly escape characters:


// 使用正则表达式匹配目标内容
//preg_match("/偶像(.*)卡通/i", $response40,$matches40);
preg_match('/class="timetable-column-header">卡通(.*)class="timetable-column-header">日韓/i', $response40,$matches40);
// 输出提取的内容
//print $matches[1];
preg_match_all('|<divclass="timetable-name">(.*?)</div>|i',$matches40[1],$ulk40,PREG_SET_ORDER);//播放節目名稱
preg_match_all('|<divclass="timetable-desc">(.*?)<br>|i',$matches40[1],$umk40,PREG_SET_ORDER);//播放節目介紹
preg_match_all('|<divclass="timetable-time">(.*?)</div>|i',$matches40[1],$unk40,PREG_SET_ORDER);//播放時間
 $trmk40=sizeof($ulk40);
//print_r($unk10);
//print_r($ulk10);
//print_r($umk10);

for ($kk40 =2; $kk40 < $trmk40-1; $kk40++) { 


    $chn.="<programme start=\"".$dt1.str_replace(':','',substr("".$unk40[($kk40-1)*2][1]."",0,5)).'00 +0800'."\"  stop=\"".$dt1.str_replace(':','',substr("".$unk40[($kk40-1)*2][1]."",-5)).'00 +0800'."\" channel=\"龍華卡通台\">\n<title lang=\"zh\">".$ulk40[$kk40-1][1]."</title>\n<desc lang=\"zh\">".$umk40[$kk40-1][1]." </desc>\n</programme>\n";
                 }


preg_match('/class="timetable-column-header">日韓(.*)class="timetable-column-header">知識/i', $response40,$matches401);
// 输出提取的内容
//print $matches1[1];
preg_match_all('|<divclass="timetable-name">(.*?)</div>|i',$matches401[1],$uqk4010,PREG_SET_ORDER);//播放節目名稱
preg_match_all('|<divclass="timetable-desc">(.*?)<br>|i',$matches401[1],$uok4010,PREG_SET_ORDER);//播放節目介紹
preg_match_all('|<divclass="timetable-time">(.*?)</div>|i',$matches401[1],$upk4010,PREG_SET_ORDER);//播放時間
 $trmk401=sizeof($uqk4010);
//print_r($uqk10);
//print_r($uok10);
//print_r($upk10);

for ($kk401 =2; $kk401 < $trmk401-1; $kk401++) { 


    $chn.="<programme start=\"".$dt1.str_replace(':','',substr("".$upk4010[($kk401-1)*2][1]."",0,5)).'00 +0800'."\"  stop=\"".$dt1.str_replace(':','',substr("".$upk4010[($kk401-1)*2][1]."",-5)).'00 +0800'."\" channel=\"龍華日韓台\">\n<title lang=\"zh\">".$uqk4010[$kk401-1][1]."</title>\n<desc lang=\"zh\">".$uok4010[$kk401-1][1]." </desc>\n</programme>\n";
                 }


preg_match('/class="timetable-column-header">偶像(.*)class="timetable-column-header">電影/i', $response40,$matches402);
// 输出提取的内容
//print $matches1[1];
preg_match_all('|<divclass="timetable-name">(.*?)</div>|i',$matches402[1],$uqk4020,PREG_SET_ORDER);//播放節目名稱
preg_match_all('|<divclass="timetable-desc">(.*?)<br>|i',$matches402[1],$uok4020,PREG_SET_ORDER);//播放節目介紹
preg_match_all('|<divclass="timetable-time">(.*?)</div>|i',$matches402[1],$upk4020,PREG_SET_ORDER);//播放時間
 $trmk402=sizeof($uqk4020);
//print_r($uqk10);
//print_r($uok10);
//print_r($upk10);

for ($kk402 =2; $kk402 < $trmk402-1; $kk402++) { 


    $chn.="<programme start=\"".$dt1.str_replace(':','',substr("".$upk4020[($kk402-1)*2][1]."",0,5)).'00 +0800'."\"  stop=\"".$dt1.str_replace(':','',substr("".$upk4020[($kk402-1)*2][1]."",-5)).'00 +0800'."\" channel=\"龍華偶像台OTT\">\n<title lang=\"zh\">".$uqk4020[$kk402-1][1]."</title>\n<desc lang=\"zh\">".$uok4020[$kk402-1][1]." </desc>\n</programme>\n";
                 }




$data42 = [
    'action' => 'timetable',
    'type' => 51,
    'play_date' => $dt12
];

$ch42 = curl_init();
curl_setopt($ch42, CURLOPT_URL, $url40);
curl_setopt($ch42, CURLOPT_POST, true);
curl_setopt($ch42, CURLOPT_HTTPHEADER, $headers40);
curl_setopt($ch42, CURLOPT_POSTFIELDS, http_build_query($data42));
curl_setopt($ch42, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch42, CURLOPT_SSL_VERIFYPEER, false); // 忽略SSL验证（测试用）
$response42 = curl_exec($ch42);

$response42 = str_replace('&','&amp;',$response42);
//$response42 = STR_REPLACE('>','',$response42);
//$response42= str_replace('<','&lt;',$response42);

curl_close($ch42);
$response42 = str_replace(array("\r\n", "\r", "\n"), "", $response42);
$response42 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $response42); // 适合php7
$response42 = preg_replace('/\s+/', '', $response42);
//print $response42 ;
// Corrected regex pattern to properly escape characters:


// 使用正则表达式匹配目标内容
//preg_match("/偶像(.*)卡通/i", $response42,$matches4211);
preg_match('/class="timetable-column-header">卡通(.*)class="timetable-column-header">日韓/i', $response42,$matches4211);
// 输出提取的内容
//print $matches[1];
preg_match_all('|<divclass="timetable-name">(.*?)</div>|i',$matches4211[1],$ulk4011,PREG_SET_ORDER);//播放節目名稱
preg_match_all('|<divclass="timetable-desc">(.*?)<br>|i',$matches4211[1],$umk4011,PREG_SET_ORDER);//播放節目介紹
preg_match_all('|<divclass="timetable-time">(.*?)</div>|i',$matches4211[1],$unk4011,PREG_SET_ORDER);//播放時間
 $trmk4011=sizeof($ulk4011);
//print_r($unk10);
//print_r($ulk10);
//print_r($umk10);

for ($kk4011 =2; $kk4011 < $trmk4011-1; $kk4011++) { 


    $chn.="<programme start=\"".$dt2.str_replace(':','',substr("".$unk4011[($kk4011-1)*2][1]."",0,5)).'00 +0800'."\"  stop=\"".$dt2.str_replace(':','',substr("".$unk4011[($kk4011-1)*2][1]."",-5)).'00 +0800'."\" channel=\"龍華卡通台\">\n<title lang=\"zh\">".$ulk4011[$kk4011-1][1]."</title>\n<desc lang=\"zh\">".$umk4011[$kk4011-1][1]." </desc>\n</programme>\n";
                 }

preg_match('/class="timetable-column-header">日韓(.*)class="timetable-column-header">知識/i', $response42,$matches14212);
// 输出提取的内容
//print $matches14212[1];

preg_match_all('|<divclass="timetable-name">(.*?)</div>|i',$matches14212[1],$uqk4012,PREG_SET_ORDER);//播放節目名稱
preg_match_all('|<divclass="timetable-desc">(.*?)<br>|i',$matches14212[1],$uok4012,PREG_SET_ORDER);//播放節目介紹
preg_match_all('|<divclass="timetable-time">(.*?)</div>|i',$matches14212[1],$upk4012,PREG_SET_ORDER);//播放時間
 $trmk4012=sizeof($uqk4012);
//print_r($uqk4012);
//print_r($uok4012);
//print_r($upk4012);

for ($kk4012 =2; $kk4012 <  $trmk4012-1; $kk4012++) { 


    $chn.="<programme start=\"".$dt2.str_replace(':','',substr("".$upk4012[($kk4012-1)*2][1]."",0,5)).'00 +0800'."\"  stop=\"".$dt2.str_replace(':','',substr("".$upk4012[($kk4012-1)*2][1]."",-5)).'00 +0800'."\" channel=\"龍華日韓台\">\n<title lang=\"zh\">".$uqk4012[$kk4012-1][1]."</title>\n<desc lang=\"zh\">".$uok4012[$kk4012-1][1]." </desc>\n</programme>\n";
                 }


preg_match('/class="timetable-column-header">偶像(.*)class="timetable-column-header">電影/i', $response42,$matches14213);
// 输出提取的内容
//print $matches14212[1];

preg_match_all('|<divclass="timetable-name">(.*?)</div>|i',$matches14213[1],$uqk4013,PREG_SET_ORDER);//播放節目名稱
preg_match_all('|<divclass="timetable-desc">(.*?)<br>|i',$matches14213[1],$uok4013,PREG_SET_ORDER);//播放節目介紹
preg_match_all('|<divclass="timetable-time">(.*?)</div>|i',$matches14213[1],$upk4013,PREG_SET_ORDER);//播放時間
 $trmk4013=sizeof($uqk4013);
//print_r($uqk4012);
//print_r($uok4012);
//print_r($upk4012);

for ($kk4013 =2; $kk4013 <  $trmk4013-1; $kk4013++) { 


    $chn.="<programme start=\"".$dt2.str_replace(':','',substr("".$upk4013[($kk4013-1)*2][1]."",0,5)).'00 +0800'."\"  stop=\"".$dt2.str_replace(':','',substr("".$upk4013[($kk4013-1)*2][1]."",-5)).'00 +0800'."\" channel=\"龍華偶像台OTT\">\n<title lang=\"zh\">".$uqk4013[$kk4013-1][1]."</title>\n<desc lang=\"zh\">".$uok4013[$kk4013-1][1]." </desc>\n</programme>\n";
                 }





//8inow
$id80=100579;//起始节目编号
$cid8=array(
array('096','viu6'),

array('099','ViuTV'),  
array('102','Viu 頻道'),  
 array('105','now 劇集'),
 array('106','video express rentnow'),
 array('108','nowjeli'),
 array('111','HBO Hits香港'),
 array('112','HBO Family香港'),
 array('113','CINEMAX香港'),
 array('114','HBO Signature香港'),
 array('115','HBO香港'),
 array('116','MOVIE MOVIE'),
 array('133','爆谷台'),
 array('138','Now爆谷星影台'),
 array('150','Animax香港'),
 array('155','tvN香港'),
 array('156','KBS World香港'),
 array('162','東森亞洲'),
  array('168','moov'),
  array('200','Panda TV'),
 array('208','Discovery Asia香港'),
 array('209','Discovery Channel香港'),
 array('210','動物星球頻道香港'),
 array('211','Discovery 科學頻道香港'),
 array('212','DMAX香港'),
 array('213','TLC旅遊生活頻道香港'),
 array('217','Love Nature香港'),
 array('220','BBC Earth香港'),
 array('221','戶外頻道香港'),
 array('222','罪案 + 偵緝香港'),
 array('223','HISTORY香港'),
 array('316','CNN 國際新聞網絡香港'),
array('319','CNBC香港'),
array('320','BBC News香港'),
array('321','Bloomberg Television香港'),
array('322','亞洲新聞台香港'),
array('3231','Sky News香港'),
array('324','DW (English)香港'),
array('325','半島電視台英語頻道香港'),
array('326','euronews香港'),
array('327','France 24香港'),
array('328','NHK WORLD-JAPA香港'),
array('329','RT香港'),
array('330','中國環球電視網香港'),
array('331','now直播 '),
array('332','now新聞'),
array('333','now財經'),
array('336','now報價'),
//array('338','第一財經'),
array('366','鳳凰資訊'),
array('367','鳳凰香港台'),
array('400','智叻樂園'),
array('548','鳳凰中文'),
array('368','香港衛視'),
array('371','東森亞洲新聞'),
array('440','DreamWorks 頻道香港'),
array('443','Cartoon Network香港'),
array('444','Nickelodeon香港'),
array('447','CBeebies香港'),
array('448','Moonbug香港'),
array('449','Nick Jr.香港'),
array('460','Da Vinc香港'),
array('502','BBC Lifestyle香港'),
array('512','AXN香港'),
array('517','ROCK Entertainment香港'),
array('525','Lifetime香港'),
array('526','Food Network香港'),
array('527','亞洲美食台香港'),
array('528','旅遊頻道香港'),
array('529','居家樂活頻道香港'),
array('535','Netflix香港'),
//array('538','中天亞洲台'),
array('540','深圳衛視香港'),
array('541','CCTV-1香港'),
array('542','CCTV-4香港'),
array('543','大灣區衛視香港'),
array('545','中央電視台新聞頻道香港'),
array('548','鳳凰衛視中文台'),
array('552','OneTV 綜合頻道'),
array('553','三沙衛視香港'),
array('555','浙江衛視香港'),
array('561','ABC Australia香港'),
array('600','now體育'),
array('611','now體育4k'),
array('612','now體育4k'),
array('613','now體育4k'),
array('620','Now Sports 英超TV'),
array('621','Now Sports 英超TV1'),
array('622','Now Sports 英超 TV2'),
array('623','Now Sports 英超 TV3'),
array('624','Now Sports 英超 TV4'),
array('625','Now Sports 英超 TV5'),
array('626','Now Sports 英超 TV6'),
array('627','Now Sports 英超 TV7'),
array('630','Now Sports Premier'),
array('631','Now Sports 1'),
array('632','Now Sports 2'),
array('633','Now Sports 3'),
array('634','Now Sports 4'),
array('635','Now Sports 5'),
array('636','Now Sports 6'),
array('637','Now Sports 7'),
array('638','beIN SPORTS 1'),
array('639','beIN SPORTS 2'),
array('640','MUTV'),
array('641','Now Sports 641'),
array('642','NBA'),
array('643','beIN SPORTS 3'),
array('644','beIN SPORTS 4'),
array('645','beIN SPORTS 5'),
array('646','beIN SPORTS 6'),
array('650','beIN SPORTS RUGBY'),
array('651','Now Sports 651'),
array('652','Now Sports 652'),
array('668','Now Sports 668'),
array('670','SPOTV'),
array('671','SPOTV2'),
array('674','Astro Cricket'),
array('679','Premier Sports'),
array('680','Now Sports plus'),
array('681','Now Sports 681'),
array('683','Now 高爾夫2'),
array('684','Now 高爾夫3'),
array('688','Lucky 688'),
array('711','NHK World Premium'),
array('713','TV5MONDE Style'),
array('714','TV5MONDE ASIE'),
array('715','France 24 (French)'),
array('720','GMA Pinoy TV'),
array('721','GMA Life T'),
array('725','TFC'),
array('771','Sony TV (India)'),
array('772','Sony MAX'),
array('774','Sony SAB'),
array('779','MTV India'),
array('780','COLORS'),
array('781','Zee Cinema International'),
array('782','Zee TV'),
array('785','Zee News'),
array('793','Star Gold'),
array('794','STAR PLUS'),
array('797','Star Bharat'),
array('900','成人節目資訊'),
array('901','冰火頻道'),
array('903','成人極品台'),

);


$nid8=sizeof($cid8);

for ($idm8 = 1; $idm8 <= $nid8; $idm8++){
 $idd8=$id80+$idm8;
   $chn.="<channel id=\"".$cid8[$idm8-1][1]."\"><display-name lang=\"zh\">".$cid8[$idm8-1][1]."</display-name></channel>\n";
                                         }

for ($idm8 = 1; $idm8 <= $nid8; $idm8++){
$cookie8='Cookie: __eoi=ID=dfc31ab44d7042c7:T=1720171157:RT=1720416942:S=AA-AfjZu3rh8SzbgMl-5j4p1JBCI; NOWSESSIONID=; NOW_SESSION=b0c4fc0487e5b349296f55d969de215610470549-NOWSESSIONID=&mupType=NORMAL&nowDollarBalance=0&isBinded=false&isMobileId=false&mobile=&isOTTMode=N&firstMupUser=false&is4K=false&isLogin=false&isMobileGuest=false&fsaType=&mupShow=login&lang=zh; LANG=zh';
$url8='https://nowplayer.now.com/tvguide/channeldetail/'.$cid8[$idm8-1][0].'/1?isfromchannel=false';
 
 $idd8=$id80+$idm8;

    $ch8 = curl_init();
    curl_setopt($ch8, CURLOPT_URL, $url8);
    curl_setopt($ch8, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch8, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch8, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch8, CURLOPT_TIMEOUT, 30); 
$hea8=array(
'Host: nowplayer.now.com',
'Connection: keep-alive',
'Cache-Control: max-age=0Cache-Control: max-age=0',
'Upgrade-Insecure-Requests: 1',
'DNT: 1',

//'Referer: https://nowplayer.now.com/tvguide?filterType=all',
'Referer: '.$url8,
);
curl_setopt ( $ch8, CURLOPT_HEADER, $hea8 );
curl_setopt($ch8,CURLOPT_USERAGENT,'Mozilla/5.0');
curl_setopt($ch8, CURLOPT_COOKIE, $cookie8);
curl_setopt($ch8,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $re8 = curl_exec($ch8);
    curl_close($ch8);

 $re8 = preg_replace('/\s(?=)/', '',$re8);
 $re8=str_replace('&','&amp;',$re8);
$re8=str_replace('<ul>','',$re8);
$re8=str_replace('[','',$re8);
$re8=str_replace(']','',$re8);



$re8=str_replace('<spanclass="live-btn">播放中</span>','',$re8);
//$re8 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re8);// 適合php7



preg_match('/id\="day1"(.*)id\="day2"/i',$re8,$uk8);
//print $uk8[1];

preg_match_all('|<divclass="time">(.*?)</div>|i',$uk8[1],$um8,PREG_SET_ORDER);//播放時間
preg_match_all('|<divclass="prograam-name">(.*?)</div>|i',$uk8[1],$un8,PREG_SET_ORDER);//播放節目
$trm8=count($um8);


preg_match('/id\="day2"(.*)id\="day3"/i',$re8,$uk81);
//print $uk81[1];

preg_match_all('|<divclass="time">(.*?)</div>|i',$uk81[1],$um81,PREG_SET_ORDER);//播放時間
preg_match_all('|<divclass="prograam-name">(.*?)</div>|i',$uk81[1],$un81,PREG_SET_ORDER);//播放節目
$trm81=count($um81);

//$um8[][1]='12:00PM';

//$um81[][1]='12:00PM';
//輸入節目開始上午
//print_r($um8);
//print_r($um81);

for ($k8 =1; $k8 <= $trm8-1; $k8++) {
     
   
      $chn.="<programme start=\"".$dt1.date("Hi", strtotime($um8[$k8-1][1])).'00 +0800'."\" stop=\"".$dt1.date("Hi", strtotime($um8[$k8][1])).'00 +0800'."\" channel=\"".$cid8[$idm8-1][1]."\">\n<title lang=\"zh\">".str_replace('<spanclass="live-btn">播放中</span>','',$un8[$k8-1][1])."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
       
                                                                                                                                       }
                                                              
  //$chn.="<programme start=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$um52[$k52-1][1]))).' +0800'."\" stop=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$um52[$k52][1]))).' +0800'."\" channel=\"".$cid5[$idm5-1][1]."\">\n<title lang=\"zh\">".trim($un52[$k52-1][1])."</title>\n<desc lang=\"zh\">".trim($uk52[$k52-1][1])." </desc>\n</programme>\n";
 

$chn.="<programme start=\"".$dt1.date("Hi", strtotime($um8[$trm8-1][1])).'00 +0800'."\" stop=\"".$dt2.date("Hi", strtotime( $um81[0][1])).'00 +0800'."\" channel=\"".$cid8[$idm8-1][1]."\">\n<title lang=\"zh\">".str_replace('<spanclass="live-btn">播放中</span>','',$un8[$trm8-1][1])."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";     

//$chn.="<programme start=\"".$dt1.str_replace(':','',DateTime::createFromFormat('h:i A', $um8[$trm8][1])->format('H:i')).'00 +0800'."\" stop=\"".$dt2.str_replace(':','',DateTime::createFromFormat('h:i A', $um81[0][1])->format('H:i')).'00 +0800'."\" channel=\"".$cid8[$idm8-1][1]."\">\n<title lang=\"zh\">".str_replace('<spanclass="live-btn">播放中</span>','',$un8[$trm8-1][1])."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";     


for ($k81 =1; $k81 <= $trm81-1; $k81++) {
        $chn.="<programme start=\"".$dt2.date("Hi", strtotime($um81[$k81-1][1])).'00 +0800'."\" stop=\"".$dt2.date("Hi", strtotime($um81[$k81][1])).'00 +0800'."\" channel=\"".$cid8[$idm8-1][1]."\">\n<title lang=\"zh\">".str_replace('<spanclass="live-btn">播放中</span>','',$un81[$k81-1][1])."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
       
                                                                                                                                       }
                                                              

$chn.="<programme start=\"".$dt2.date("Hi", strtotime($um81[$trm81-1][1])).'00 +0800'."\" stop=\"".$dt2.'235900 +0800'."\" channel=\"".$cid8[$idm8-1][1]."\">\n<title lang=\"zh\">".str_replace('<spanclass="live-btn">播放中</span>','',$un81[$trm81-1][1])."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";     
    
                            
}
//新加坡mewatch
//https://www.mewatch.sg/channels/CCTV-4-569797
$id100=600000;//起始节目编号
$cid100=array(
 // array('186574',' Russia Today'),
  array('97098','Channel 5'),
 array('97104','Channel 8'),
array('97129','Channel U'),
 array('97084','Channel Suria'),
 array('97096','Channel Vasantham'),
 array('97072','CNA'),
 array('186574','oktolidays'),
  // array('20695','EGG'),

array('576059','SEA Games CH01'),
array('576060','SEA Games CH02'),
array('576061','SEA Games CH03'),
array('580750','SEA Games CH04'),
array('580751','SEA Games CH05'),
array('580752','SEA Games CH06'),
array('98200','SPL-CH01'),

array('97073','meWATCH LIVE 1'),
array('97078','meWATCH LIVE 2'),
array('98202','meWATCH LIVE 5'),
array('558241','River Monsters'),
array('558273','FoodON'),
array('557763','FIFA+'),
array('558112',' W-Sport'),
array('556888','TRACE Sport Stars'),
array('556894','Action Hollywood Movies'),
array('556893','Kartoon Channel!'),
array('556877','TG Junior'),
array('158965','NOW 80s'),
array('158964','NOW 70s'),
array('158963','NOW ROCK'),
array('382872','CinemaWorld'),
array('572361','ADITHYA TV'),
array('569530','ANC'),
array('571922','Animax HD'),
array('572358','Asianet'),
array('572359','Asianet Movies'),
array('569790','Astro Sensasi HD'),
array('569789','Astro Warna HD'),
array('571915','AXN HD'),
array('566407','BBC Earth HD'),
array('570217','BBC Lifestyle HD'),
array('569794','BBC News HD'),
array('570192','Cartoon Network'),
array('569516','CBeebies HD'),
array('572051','天映經典台新加坡'),
array('569797','CCTV-4'),
array('572048','天映頻道新加坡'),
array('566560','CGTN'),
array('569534','Cinema One Global'),
array('569781','Citra Entertainment'),
array('571958','CNBC HD'),
array('571959','CNN HD'),
array('572356','COLORS'),
array('572357','COLORS Tamil HD'),
array('570193','Crime + Investigation HD'),
array('571963','中天亞洲台新加坡'),
array('570194','Discovery HD新加坡'),
array('571966','東方衛視國際版'),
array('569526','DreamWorks HD'),
array('569803','東森亞洲臺新加坡'),
//array('571923','Euronews HD'),
//array('','Fox News Channel HD'),
array('570218','HGTV HD新加坡'),
array('569527','HISTORY HD新加坡'),
array('567120','HITS HD新加坡'),
array('569535','HITS MOVIES HD新加坡'),
array('569498','都會臺'),
array('567123','Hub Premier 1'),
array('572415','Hub Premier 2'),
array('572419','Hub Premier 3'),
array('572423','Hub Premier 4'),
array('572420','Hub Premier 5'),
array('572421','Hub Premier 6'),
array('572417','Hub Premier 7'),
array('572414','Hub Premier 8'),
array('572411','Hub Premier 9'),
array('572427','Hub Premier 10'),
array('572408','Hub Premier 11'),
array('571971','Hub Ruyi'),
array('569506','Hub Sports 1 HD'),
array('569510','Hub Sports 2 HD'),
array('566562','Hub Sports 3 HD'),
array('564507','Hub VV Drama HD'),
array('572360','Kalaignar TV'),
array('569788','Karisma'),
array('569491','KBS World HD'),
array('567111','KTV HD'),
array('571921','Lifetime HD新加坡'),
array('569519','Nick Jr. HD新加坡'),
array('569522','Nickelodeon Asia HD新加坡'),
array('569791','ONE (Malay)'),
array('566561','ONE HD'),
//array('569800','鳳凰中文新加坡'),
//array('569801','鳳凰資訊新加坡'),
array('570229','ROCK Entertainment HD新加坡'),
array('571936','Sky News HD新加坡'),
array('572340','Sony Entertainment Televis新加坡'),
array('572343','SONY MAX'),
array('572338','Sun Music'),
array('572335','Sun TV'),
array('569532','The Filipino Channel HD'),
array('570207','Travelxp HD'),
array('572047','TVB星河新加坡'),
array('569503','TVBS亞洲新加坡'),
array('569802','TVB新聞新加坡'),
array('572317','Vannathirai'),
array('572316','Vijay TV HD'),
array('572312','Zee Cinema HD'),
array('572309','Zee Tamil HD'),
array('572222','Zee Thirai'),
array('572192','Zee TV HD'),
//array('158962','trace urban'),
//array('242036','GEM'),
//array('98200','SPL01'),
//array('98201','SPL02'),
//array('15896','SPOTV Stadia'),

);

$nid100=sizeof($cid100);


for ($idm100= 1; $idm100 <= $nid100; $idm100++){
 $idd100=$id100+$idm100;
   $chn.="<channel id=\"".$cid100[$idm100-1][1]."\"><display-name lang=\"zh\">".$cid100[$idm100-1][1]."</display-name></channel>\n";
}



for ($idm100= 1; $idm100 <= $nid100; $idm100++){

$url98='https://cdn.mewatch.sg/api/schedules?channels='.$cid100[$idm100-1][0].'&date='.$dt10.'&duration=24&ff=idp%2Cldp%2Crpt%2Ccd&hour=16&intersect=true&lang=en&segments=all';
//https://cdn.mewatch.sg/api/schedules?channels=158965&date=2024-07-12&duration=24&ff=idp%2Cldp%2Crpt%2Ccd&hour=16&intersect=true&lang=en&segments=all

 $idd100=$id100+$idm100;
$ch98=curl_init();
curl_setopt($ch98,CURLOPT_URL,$url98);
curl_setopt($ch98,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch98,CURLOPT_SSL_VERIFYHOST,false);
curl_setopt($ch98,CURLOPT_RETURNTRANSFER,1);
$re98=curl_exec($ch98);
curl_close($ch98);

 


$re98 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re98);// 適合php7

 $re98=str_replace('&','&amp;',$re98);
 $re98=str_replace('>','',$re98);
 $re98=str_replace('<','&lt;',$re98);
 $re98=str_replace('/','',$re98);


 $data98=json_decode($re98)[0]->schedules;
$tuu98=count($data98);

for ( $i98=0 ; $i98<=$tuu98-1 ; $i98++ ) {
$startDate98=json_decode($re98)[0]->schedules[$i98]->startDate;
$endDate98=json_decode($re98)[0]->schedules[$i98]->endDate;
$title98=json_decode($re98)[0]->schedules[$i98]->item->title;

$secondaryLanguageTitle98=json_decode($re98)[0]->schedules[$i98]->item->secondaryLanguageTitle;
$description98=json_decode($re98)[0]->schedules[$i98]->item->description;
$description98=str_replace('>','',$description98);
 $description98=str_replace('<','&lt;',$description98);
  $description98=str_replace('&','&amp;', $description98);
$description98=str_replace('/','',$description98);
$description98=str_replace('<','&lt;',$description98);

$seasonNumber98=json_decode($re98)[0]->schedules[$i98]->classification->seasonNumber;
$episodeNumber98=json_decode($re98)[0]->schedules[$i98]->classification->episodeNumber;
$startDate98=str_replace('Z','',$startDate98);
$startDate98=str_replace('T','',$startDate98);
$startDate98=str_replace('-','',$startDate98);
$startDate98=str_replace(':','',$startDate98);
$endDate98=str_replace('T','',$endDate98);
$endDate98=str_replace('Z','',$endDate98);
$endDate98=str_replace(':','',$endDate98);
$endDate98=str_replace('-','',$endDate98);
$chn.="<programme start=\"".$startDate98.' +0000'."\"  stop=\"".$endDate98.' +0000'."\"  channel=\"".$cid100[$idm100-1][1]."\">\n<title lang=\"zh\">".$secondaryLanguageTitle98.$title98.$seasonNumber98.$episodeNumber98."</title>\n<desc lang=\"zh\">".str_replace('<','&lt;',$description98)."</desc>\n</programme>\n";

}

$url99='https://cdn.mewatch.sg/api/schedules?channels='.$cid100[$idm100-1][0].'&date='.$dt11.'&duration=24&ff=idp%2Cldp%2Crpt%2Ccd&hour=16&intersect=true&lang=en&segments=all';
 $idd100=$id100+$idm100;
$ch99=curl_init();
curl_setopt($ch99,CURLOPT_URL,$url99);
curl_setopt($ch99,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch99,CURLOPT_SSL_VERIFYHOST,false);
curl_setopt($ch99,CURLOPT_RETURNTRANSFER,1);
$re99=curl_exec($ch99);
curl_close($ch99);
$re99 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re99);// 適合php7
//print $re ;
//$re99 = preg_replace('/\s(?=)/', '',$re);
 $re99=str_replace('&','&amp;',$re99);

$re99=str_replace('&','&amp;',$re99);
 $re99=str_replace('&','&amp;',$re99);
 $re99=str_replace('>','',$re99);
 $re99=str_replace('<','&lt;',$re99);
 $re99=str_replace('/','',$re99);

 $data99=json_decode($re99)[0]->schedules;
$tuu99=count($data99);

for ( $i99=0 ; $i99<=$tuu99-1 ; $i99++ ) {
$startDate99=json_decode($re99)[0]->schedules[$i99]->startDate;
$endDate99=json_decode($re99)[0]->schedules[$i99]->endDate;
$title99=json_decode($re99)[0]->schedules[$i99]->item->title;
$secondaryLanguageTitle99=json_decode($re99)[0]->schedules[$i99]->item->secondaryLanguageTitle;
$description99=json_decode($re99)[0]->schedules[$i99]->item->description;
$description99=str_replace('>','',$description99);
 $description99=str_replace('<','&lt;',$description99);
  $description99=str_replace('&','&amp;', $description99);
$description99=str_replace('/','',$description99);
  
                 $description99=str_replace('<','&lt;',$description99);

$seasonNumber99=json_decode($re99)[0]->schedules[$i99]->classification->seasonNumber;
$episodeNumber99=json_decode($re99)[0]->schedules[$i99]->classification->episodeNumber;
$startDate99=str_replace('Z','',$startDate99);
$startDate99=str_replace('T','',$startDate99);
$startDate99=str_replace('-','',$startDate99);
$startDate99=str_replace(':','',$startDate99);
$endDate99=str_replace('T','',$endDate99);
$endDate99=str_replace('Z','',$endDate99);
$endDate99=str_replace(':','',$endDate99);
$endDate99=str_replace('-','',$endDate99);


$chn.="<programme start=\"".$startDate99.' +0000'."\"  stop=\"".$endDate99.' +0000'."\"  channel=\"".$cid100[$idm100-1][1]."\">\n<title lang=\"zh\">".$secondaryLanguageTitle99.$title99.$seasonNumber99.$episodeNumber99."</title>\n<desc lang=\"zh\">".str_replace('<','&lt;',$description99)."</desc>\n</programme>\n";

}


$url100='https://cdn.mewatch.sg/api/schedules?channels='.$cid100[$idm100-1][0].'&date='.$dt12.'&duration=24&ff=idp%2Cldp%2Crpt%2Ccd&hour=16&intersect=true&lang=en&segments=all';
 $idd100=$id100+$idm100;
$ch100=curl_init();
curl_setopt($ch100,CURLOPT_URL,$url100);
curl_setopt($ch100,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch100,CURLOPT_SSL_VERIFYHOST,false);
curl_setopt($ch100,CURLOPT_RETURNTRANSFER,1);
$re100=curl_exec($ch100);
curl_close($ch100);


 $re100=str_replace('&','&amp;',$re100);
 $re100=str_replace('>','&gt;',$re100);
 $re100=str_replace('<','&lt;',$re100);
 $re100=str_replace('/','',$re100);


//$re100 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re100);// 適合php7
//print $re ;
//$re99 = preg_replace('/\s(?=)/', '',$re);
 $re100=str_replace('&','&amp;',$re100);



 $data100=json_decode($re100)[0]->schedules;
$tuu100=count($data100);

for ( $i100=0 ; $i100<=$tuu100-1 ; $i100++ ) {
$startDate100=json_decode($re100)[0]->schedules[$i100]->startDate;
$endDate100=json_decode($re100)[0]->schedules[$i100]->endDate;
$title100=json_decode($re100)[0]->schedules[$i100]->item->title;

$secondaryLanguageTitle100=json_decode($re100)[0]->schedules[$i100]->item->secondaryLanguageTitle;
$description100=json_decode($re100)[0]->schedules[$i100]->item->description;
$description100=str_replace('>','&gt;',$description100);
 $description100=str_replace('<','&lt;',$description100);
  $description100=str_replace('&','&amp;', $description100);
$description100=str_replace('/','',$description100);



$seasonNumber100=json_decode($re100)[0]->schedules[$i00]->classification->seasonNumber;
$episodeNumber100=json_decode($re100)[0]->schedules[$i100]->classification->episodeNumber;
$startDate100=str_replace('Z','',$startDate100);
$startDate100=str_replace('T','',$startDate100);
$startDate100=str_replace('-','',$startDate100);
$startDate100=str_replace(':','',$startDate100);
$endDate100=str_replace('T','',$endDate100);
$endDate100=str_replace('Z','',$endDate100);
$endDate100=str_replace(':','',$endDate100);
$endDate100=str_replace('-','',$endDate100);
$chn.="<programme start=\"".$startDate100.' +0000'."\"  stop=\"".$endDate100.' +0000'."\"  channel=\"".$cid100[$idm100-1][1]."\">\n<title lang=\"zh\">".$secondaryLanguageTitle100.$seasonNumber100.$title100.$episodeNumber100."</title>\n<desc lang=\"zh\">".str_replace('<','&lt;',$description100)."</desc>\n</programme>\n";

}

}



/*
//央视频
$id79=799999;//起始节目编号
$cid79=array(
   
    array('600001859','cctv1'),

    array('600001800','cctv2'),
    array('600001801','cctv3'),
   array('600001814','cctv4亚洲'),
 array('600001818','cctv5'),
   array('600001817','cctv5+'),
   array('600001802','cctv6'),
   array('600004092','cctv7'),
array('600001803','cctv8'),
   array('600004078','cctv9'),
array('600001805','cctv10'),
array('600001806','cctv11'),
array('600001807','cctv12'),
array('600001811','cctv13'),
array('600001809','cctv14'),
array('600001815','cctv15'),
array('600099502','cctv16'),
array('600001810','cctv17'),
array('600002264','cctv4k'),
array('600156816','cctv8k'),
array('600099655','cctv第一剧场'),
array('600099658','cctv风云剧场'),
array('600099620','cctv怀旧剧场'),
array('600099637','cctv世界地理'),
array('600099660','cctv风云音乐'),
array('600099649','cctv兵器科技'),
array('600099636','cctv风云足球'),
array('600099659','cctv高尔夫'),
array('600099650','cctv女性时尚'),
array('600099653','cctv文化精品'),
array('600099652','cctv台球'),
array('600099656','cctv电视指南'),
array('600099651','cctv卫生健康'),
array('600014550','cgtn'),
array('600084704','cgtn法语'),
array('600084758','cgtn俄语'),
array('600084782','cgtn阿拉伯语'),
array('600084744','cgtn西班牙语'),
array('600084781','cgtn英文记录片'),
array('600002309','北京卫视'),
array('600002483','东方卫视'),
array('600002521','江苏卫视'),
array('600002520','浙江卫视'),
array('600002475','湖南卫视'),
array('600002508','湖北卫视'),
array('600002485','广东卫视'),
array('600002509','广西卫视'),
array('600002498','黑龙江卫视'),
array('600002506','海南卫视'),
array('600002531','重庆卫视'),
array('600002481','深圳卫视'),
array('600002516','四川卫视'),
array('600002525','河南卫视'),
array('600002484','东南卫视'),
array('600002490','贵州卫视'),
array('600002503','江西卫视'),
array('600002505','辽宁卫视'),
array('600002532','安徽卫视'),
array('600002493','河北卫视'),
array('600002513','山东卫视'),
array('600152137','天津卫视'),
array('600190405','吉林卫视'),
array('600190400','陕西卫视'),
array('600190408','甘肃卫视'),
array('600190737','宁夏卫视'),
array('600190401','内蒙古卫视'),
array('600190402','云南卫视'),
array('600190407','山西卫视'),
array('600190406','青海卫视'),
array('600190403','西藏卫视'),
array('600171827','中国教育电视台-1'),
array('600152138','新疆卫视'),
array('600170344','兵团卫视'),



);

$nid79=sizeof($cid79);
for ($idm79 = 1; $idm79 <= $nid79; $idm79++){
 $idd79=$id79+$idm79;
   $chn.="<channel id=\"".$cid79[$idm79-1][1]."\"><display-name lang=\"zh\">".$cid79[$idm79-1][1]."</display-name></channel>\n";
         
}

for ($idm79 = 1; $idm79 <= $nid79; $idm79++){

        // https://capi.yangshipin.cn/api/yspepg/program/600001818/20241025
//$url79='https://h5access.yangshipin.cn/web/tv_program?targetId=1&vappid=59306155&vsecret=b42702bf7309a179d102f3d51b1add2fda0bc7ada64cb801&raw=1&type=by_day&pid='.$cid79[$idm79-1][0].'&day='.$dt11;


$url79='https://capi.yangshipin.cn/api/yspepg/program/'.$cid79[$idm79-1][0].'/'.$dt1;


$idd79=$id79+$idm79;
$ch79= curl_init ();
curl_setopt ( $ch79, CURLOPT_URL, $url79 );
curl_setopt ( $ch79, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt($ch79,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch79,CURLOPT_SSL_VERIFYHOST,false);

$hea79=[

'Host: capi.yangshipin.cn',
'Connection: keep-alive',

'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36 Edg/130.0.0.0',


'Origin: https://www.yangshipin.cn',

'Referer: https://www.yangshipin.cn/',
'Accept-Encoding: gzip',
//'Accept-Language: en-US,en;q=0.9,zh-CN;q=0.8,zh;q=0.7,en-GB;q=0.6',


];
curl_setopt( $ch79, CURLOPT_HTTPHEADER, $hea79);

curl_setopt($ch79,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:131.0) Gecko/20100101 Firefox/131.0');
//curl_setopt($ch79,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $re79 = curl_exec($ch79);
   // $re79=str_replace('&','&amp;',$re79);
   curl_close($ch79);
//$re79 =preg_replace('/[^\p{L}\p{N}\s\-:]+/u', '', $re79);
//$re79=utf8_
//print $re79;
$pattern = '/\x08(\d{8})\x12(.*?)\x18.*?\x05(\d{2}:\d{3})\x05(\d{2}:\d{3})/';

// 使用 preg_match_all 提取数据
preg_match_all($pattern,$re79, $matches, PREG_SET_ORDER);

// 输出提取的信息
foreach ($matches as $match) {
    $id = $match[1];         // ID
    $title = $match[2];      // 标题
    $startTime = $match[3];  // 开始时间
    $endTime = $match[4];    // 结束时间

$chn .= "<programme start=\"" .$dt1.str_replace(' ', '', str_replace(':', '', substr($startTime, 0, strlen($startTime) - 1))) . '00 +0800" stop="' .$dt1.str_replace(' ', '', str_replace(':', '', substr($endTime, 0, strlen($endTime) - 1))) . '00 +0800" channel="' . $cid79[$idm79-1][1] . "\">\n<title lang=\"zh\">" . substr($title, 1) . "</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
    //echo "标题: $title, 开始时间: $startTime, 结束时间: $endTime\n";
}
$url791='https://capi.yangshipin.cn/api/yspepg/program/'.$cid79[$idm79-1][0].'/'.$dt2;
$idd79=$id79+$idm79;
$ch791= curl_init ();
curl_setopt ( $ch791, CURLOPT_URL, $url791 );
curl_setopt ( $ch791, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt($ch791,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch791,CURLOPT_SSL_VERIFYHOST,false);
curl_setopt( $ch791, CURLOPT_HTTPHEADER, $hea79);
curl_setopt($ch791,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:131.0) Gecko/20100101 Firefox/131.0');
//curl_setopt($ch791,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $re791 = curl_exec($ch791);
   // $re79=str_replace('&','&amp;',$re79);
   curl_close($ch791);


// 使用 preg_match_all 提取数据
preg_match_all($pattern,$re791, $matches1, PREG_SET_ORDER);


// 输出提取的信息
foreach ($matches1 as $match1) {
    $id1 = $match1[1];         // ID
    $title1 = $match1[2];      // 标题
    $startTime1 = $match1[3];  // 开始时间
    $endTime1 = $match1[4];    // 结束时间

$chn .= "<programme start=\"" .$dt2.str_replace(' ', '', str_replace(':', '', substr($startTime1, 0, strlen($startTime1) - 1))) . '00 +0800" stop="' .$dt2.str_replace(' ', '', str_replace(':', '', substr($endTime1, 0, strlen($endTime1) - 1))) . '00 +0800" channel="' . $cid79[$idm79-1][1] . "\">\n<title lang=\"zh\">" . str_replace(':','',substr($title1, 1)) . "</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
    //echo "标题: $title, 开始时间: $startTime, 结束时间: $endTime\n";
}

}

//看看新闻官网直播
$chn.="<channel id=\"看看新聞直播\"><display-name lang=\"zh\">看看新聞直播</display-name></channel>\n";

$urlk13="https://live.kankanews.com/";
$ch13=curl_init();
curl_setopt($ch13,CURLOPT_URL,$urlk13);
curl_setopt($ch13,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch13,CURLOPT_SSL_VERIFYHOST,false);
curl_setopt($ch13,CURLOPT_RETURNTRANSFER,1);
$hea13=[
'Host: live.kankanews.com',
'Connection: keep-alive',
'sec-ch-ua: "Not_A Brand";v="99", "Microsoft Edge";v="109", "Chromium";v="109"',
'sec-ch-ua-mobile: ?0',
'sec-ch-ua-platform: "Windows"',
'Upgrade-Insecure-Requests: 1',
'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36 Edg/109.0.1518.78',

];

curl_setopt($ch13,CURLOPT_HTTPHEADER,$hea13);
curl_setopt($ch13,CURLOPT_ENCODING,'Vary: Accept-Encoding');
$rek13=curl_exec($ch13);
curl_close($ch13);
//$rk13=stripslashes($rek13);
$rek13 = str_replace(array("\r\n", "\r", "\n"), "", $rek13);
//$rek13 = preg_replace("/(s*?r?ns*?)+/","n",$rek13);
//$rek13 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $rek13);// 適合php7
//print $rek13;

preg_match('/\<div class\="main\-box" data\-v\-(.*?)\>\<div/i', $rek13,$yy);// 適合php7
//print $yy[1];


 preg_match('/div class\="info" data\-v\-(.*?)div class\="duration" data\-v/i',$rek13,$date13);//
//preg_match('/div class\="video\-info" style\="display:none;" data\-v\-(.*?)div class\="duration" data\-v/i',$rek13,$date13);//
//print $date13[1];

$date13[1]=str_replace('</a>','',$date13[1]);

preg_match_all('/target\="_blank" data-v-'.$yy[1].'>(.*?)<\/div>/i',$date13[1],$um13,PREG_SET_ORDER);//播放标题
preg_match_all('/img src\=\"https:\/\/skin.kankanews.com\/kknews\/img\/icon_time.svg" data-v-'.$yy[1].'>(.*?)\<\/span>/i',$date13[1],$un13,PREG_SET_ORDER);//播放时间

$trm13=count($un13);
for ($k13 =1; $k13 <= $trm13 ; $k13++) {  
$chn.="<programme start=\"2024".str_replace('月','',str_replace(' ','',str_replace('日','',str_replace('AM','',str_replace('PM','',str_replace(':','',$un13[$k13-1][1])))))).'00 +0800'."\" stop=\"2024".(str_replace('月','',str_replace(' ','',str_replace('日','',str_replace('AM','',str_replace('PM','',str_replace(':','',$un13[$k13-1][1]))))))+100).'00 +0800'."\" channel=\"看看新聞直播\">\n<title lang=\"zh\">".$um13[($k13-1)*2][1]."</title>\n<desc lang=\"zh\">".$um13[($k13-1)*2][1]." </desc>\n</programme>\n";
}                                                                                                   
*/


$id590=94000;
$cid59=array(
array( 'cwjd' ,'重温经典频道'),

);

$nid59=sizeof($cid59);
 for ($id59 = 1; $id59 <= $nid59; $id59++){
    $idd59=$id59+$id590;
    $chn.="<channel id=\"".$cid59[$id59-1][1]."\"><display-name lang=\"zh\">".$cid59[$id59-1][1]."</display-name></channel>\n";
 }

for ($id59 = 1; $id59<= $nid59; $id59++){

$url59='http://timetv.cn/epg/'.$cid59[$id59-1][0].'.html';
//print $url59;
$idd59=$id59+$id590;

$ch59 = curl_init();
curl_setopt($ch59, CURLOPT_URL, $url59);
 //curl_setopt($ch59, CURLOPT_SSL_VERIFYPEER, FALSE);
//curl_setopt($ch59, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch59, CURLOPT_RETURNTRANSFER, 1);
$hea59=[

'Host: timetv.cn',
'Connection: keep-alive',
'Cache-Control: max-age=0',
'DNT: 1',
'Upgrade-Insecure-Requests: 1',
'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0',
'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
'Accept-Encoding: gzip, deflate',
'Accept-Language: en-US,en;q=0.9,zh-CN;q=0.8,zh;q=0.7,en-GB;q=0.6',
'sec-gpc: 1',
];
  curl_setopt($ch59, CURLOPT_HTTPHEADER, $hea59);
  curl_setopt($ch59,CURLOPT_ENCODING,'Vary: Accept-Encoding');
$re59=curl_exec($ch59);
$re59 = str_replace("<i style='color gray'>", "", $re59);
$re59 = str_replace("<span style='color: gray'>", "", $re59);
$re59 = str_replace("<span style='color: black'>",  "", $re59);
    $re59 = str_replace("<i style='color: darkgreen'>","", $re59);
    $re59 = str_replace("<i style='color: black'>","", $re59);
    $re59 = str_replace("<td class='time'><i style='color: rebeccapurple;border: 1px solid rebeccapurple;'>广 告</i></td><td class='text' title='光传播专注于新闻发布、自媒体发布、微信公众号发布、微博发布等媒体稿件投放服务，致力于打造全媒体自助新闻营销平台。'><span style='color: rebeccapurple'>光传播新闻发稿营销平台</span></td><td class='other'><span class='marking' style='background-color: rebeccapurple'><a href='http://guangchuanbo.com/Template/Media/?from=ad12138' target='_blank'>了解详情</a></span></td>", "", $re59);


$re59 = str_replace("<spanstyle='color:darkgreen'>访问时光电视官网：http://timetv.cn</span></td><tdclass='other'><spanclass='marking'style='background-color:darkgreen'><ahref='http://timetv.cn'target='_blank'>访问官网</a></span></td></tr>", "", $re59);

     
 $re59 = str_replace("<i style='color: gray'>","", $re59);
$re59 = str_replace("<istyle='colordarkgreen;'>","", $re59);

    $re59 = str_replace('&', '', $re59);
 $re59 = str_replace("'color:darkgreen'", "", $re59);

// $re59 = str_replace('<spanstyle='color:darkgreen'>', '', $re59);


curl_close($ch59);
$re59 = preg_replace('/\s(?=)/', '',$re59);
//print $re59 ;

$re59 = str_replace("<istyle='color:darkgreen;'>","", $re59);
   //preg_match("|<div class="pageTitle"><label>今日节目单</label>(.*?)</tbody>|i", $re59,$rk59);
preg_match('/今日节目单(.*?)<\/tbody>/i', $re59,$rk59);

//print $rk59[1];
$rk59[1]=str_replace("<istyle='colordarkgreen;'>","",$rk59[1]);
$rk59[1]=str_replace("<spanstyle='color:darkgreen'>","",$rk59[1]);
  preg_match_all("|<tdclass='text'>(.*?)<|i",$rk59[1], $un59, PREG_SET_ORDER);
    preg_match_all("|<tdclass='time'>(.*?)<|i", $rk59[1], $um59, PREG_SET_ORDER);
//print_r($un59);
//print_r($um59);




for ($k59 = 0; $k59 <=count($um59)-2; $k59++){
$chn.="<programme start=\"".$dt1.str_replace(':','',$um59[$k59][1]).'00 +0800'."\" stop=\"".$dt1.str_replace(':','',$um59[$k59+1][1]).'00 +0800'."\" channel=\"".$cid59[$id59-1][1]."\">\n<title lang=\"zh\">". preg_replace('/<[^>]+>/', '',$un59[$k59][1])."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
                                                               }

//$chn.="<programme start=\"".$dt1.str_replace(':','',$um59[count($um59)-1][1]).'00 +0800'."\" stop=\"".$dt1.'235900 +0800'."\" channel=\"".$cid59[$id59-1][1]."\">\n<title lang=\"zh\">". preg_replace('/<[^>]+>/', '',$un59[count($um59)-1][1])."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";

    }


//电视猫



$idn10=100642;//起始节目编号
$cid10=array(
array('新视觉','新视觉'),
array('劲爆体育','劲爆体育'),
array('海峡卫视','海峡卫视'),
array('深视都市频道','深视都市频道'),
array('深视电视剧频道','深视电视剧频道'),

array('深视财经生活频道','深视财经生活频道'),

array('深视体育健康频道','深视体育健康频道'),

array('深视少儿频道','深视少儿频道'),

array('深视移动电视频道','深视移动电视频道'),
array('福建电视台新闻频道','福建电视台新闻频道'),
array('福建乡村振兴·公共','福建乡村振兴·公共'), 
array('福建电视剧频道','福建电视剧频道'),
array('福建旅游频道','福建旅游频道'), 
array('福建经济频道','福建经济频道'), 
array('福建体育频道','福建体育频道'), 
array('福建少儿频道','福建少儿频道'),
array('福建综合频道','福建综合频道'),
array('劲爆体育','劲爆体育'),
/*
array('江苏城市频道','江苏城市频道'),
array('江苏综艺频道','江苏综艺频道'),
array('江苏影视频道','江苏影视频道'),
array('江苏新闻频道','江苏新闻频道'),
array('江苏教育频道','江苏教育频道'),
array('江苏体育休闲频道','江苏体育休闲频道'),
*/
array('江苏优漫卡通频道','江苏优漫卡通频道'),
array('江苏国际频道','江苏国际频道'),
//array('重温经典频道','重温经典频道'),
);
$nid10=sizeof($cid10);
for ($idm10 = 1; $idm10 <= $nid10; $idm10++){
 $idd10=$idn10+$idm10;
   $chn.="<channel id=\"".$cid10[$idm10-1][1]."\"><display-name lang=\"zh\">".$cid10[$idm10-1][1]."</display-name></channel>\n";
}


for ($idm10 = 1; $idm10 <= $nid10; $idm10++){

 $idd10=$idn10+$idm10;
     $uu10=$dt1-20241010;

$url102="https://sp1.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php?query=".$cid10[$idm10-1][1]."&co=data[tabid=1]&resource_id=12520";

    $ch102 = curl_init();
    curl_setopt($ch102, CURLOPT_URL, $url102);
    curl_setopt($ch102, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch102, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch102, CURLOPT_SSL_VERIFYHOST, FALSE);
   curl_setopt($ch102,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $re102 = curl_exec($ch102);
$re102=str_replace('&','&amp;',$re102);
    curl_close($ch102);
$re102 = preg_replace('/\s(?=)/', '',$re102);
$re102 =str_replace('/', '', $re102); 

$re102=mb_convert_encoding($re102,"UTF-8" ,"gb2312" ); 
preg_match_all('/,"title":"(.*?)","tvname/i', $re102,$um102,PREG_SET_ORDER);//播放節目
preg_match_all('/"times":"(.*?)","title/i', $re102,$un102,PREG_SET_ORDER);//播放時間
$trm102=sizeof($um102);
 for ($k102 = 1; $k102 <= $trm102; $k102++) {
   $chn.="<programme start=\"".str_replace(' ','',str_replace(':','',str_replace(' ','',$un102[$k102-1][1]))).'00 +0800'."\" stop=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$un102[$k102][1]))).'00 +0800'."\" channel=\"".$cid10[$idm10-1][1]."\">\n<title lang=\"zh\">".$um102[$k102-1][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
}




$url10="https://sp1.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php?query=".$cid10[$idm10-1][1]."&co=data[tabid=2]&resource_id=12520";

    $ch10 = curl_init();
    curl_setopt($ch10, CURLOPT_URL, $url10);
    curl_setopt($ch10, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch10, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch10, CURLOPT_SSL_VERIFYHOST, FALSE);
   curl_setopt($ch10,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $re10 = curl_exec($ch10);
$re10=str_replace('&','&amp;',$re10);
    curl_close($ch10);
$re10 = preg_replace('/\s(?=)/', '',$re10);
$re10 =str_replace('/', '', $re10); 

$re10=mb_convert_encoding($re10,"UTF-8" ,"gb2312" ); 
preg_match_all('/,"title":"(.*?)","tvname/i', $re10,$um10,PREG_SET_ORDER);//播放節目
preg_match_all('/"times":"(.*?)","title/i', $re10,$un10,PREG_SET_ORDER);//播放時間
$trm10=sizeof($um10);
 for ($k10 = 1; $k10 <= $trm10; $k10++) {
   $chn.="<programme start=\"".str_replace(' ','',str_replace(':','',str_replace(' ','',$un10[$k10-1][1]))).'00 +0800'."\" stop=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$un10[$k10][1]))).'00 +0800'."\" channel=\"".$cid10[$idm10-1][1]."\">\n<title lang=\"zh\">".$um10[$k10-1][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
}



$url101="https://sp1.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php?query=".$cid10[$idm10-1][1]."&co=data[tabid=3]&resource_id=12520";

//$url101="https://sp1.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php?query=".$cid10[$idm10-1][1]."&co=data[tabid=($dt2-20241011)]&resource_id=12520";


    $ch101 = curl_init();
    curl_setopt($ch101, CURLOPT_URL, $url101);
    curl_setopt($ch101, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch101, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch101, CURLOPT_SSL_VERIFYHOST, FALSE);
   curl_setopt($ch101,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $re101 = curl_exec($ch101);
$re101=str_replace('&','&amp;',$re101);
    curl_close($ch101);
$re101 = preg_replace('/\s(?=)/', '',$re101);
$re101 =str_replace('/', '', $re101); 
$re101=mb_convert_encoding($re101,"UTF-8" ,"gb2312" ); 
//$re101 =iconv('GB2312', 'UTF-8', $re101); 
//print $re101;

preg_match_all('/,"title":"(.*?)","tvname/i', $re101,$um101,PREG_SET_ORDER);//播放節目
preg_match_all('/"times":"(.*?)","title/i', $re101,$un101,PREG_SET_ORDER);//播放時間
$trm101=sizeof($um101);

  $chn.="<programme start=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$un10[$trm10-1][1]))).'00 +0800'."\" stop=\"".str_replace(' ','',str_replace(':','',str_replace(' ','',$un101[0][1]))).'00 +0800'."\" channel=\"".$cid10[$idm10-1][1]."\">\n<title lang=\"zh\">". $um10[$trm10-1][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";



 for ($k101 = 1; $k101 <= $trm101; $k101++) {
   $chn.="<programme start=\"".str_replace(' ','',str_replace(':','',str_replace(' ','',$un101[$k101-1][1]))).'00 +0800'."\" stop=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$un101[$k101][1]))).'00 +0800'."\" channel=\"".$cid10[$idm10-1][1]."\">\n<title lang=\"zh\">".$um101[$k101-1][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
}
  $chn.="<programme start=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$un101[$trm101-1][1]))).'00 +0800'."\" stop=\"".$dt2.'235900 +0800'."\" channel=\"".$cid10[$idm10-1][1]."\">\n<title lang=\"zh\">". $um101[$trm101-1][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";

}



$id290=90000;
$cid29=array(
/*
array( 'SHIJIAZHUANG' ,'SHIJIAZHUANG1' ,'石家庄新闻','-','/'),

array( 'SHIJIAZHUANG' ,'SHIJIAZHUANG2' ,'石家庄城市','-','/'),

array( 'SHIJIAZHUANG' ,'SHIJIAZHUANG3' ,'石家庄娱乐','-','/'),
array( 'SHIJIAZHUANG' ,'SHIJIAZHUANG4' ,'石家庄都市','-','/'),
*/
array( 'digital' ,'SITV-YULE' ,'魅力足球','/','_'),
array( 'CCTV' ,'CCTVEUROPE' ,'CCTV-4欧洲频道','-','/'),
array( 'CCTV' ,'CCTVAMERICAS' ,'CCTV-4美洲频道','-','/'),
array( 'digital' ,'SITV-SPORTS' ,'劲爆体育','/','_'),
//array( 'digital' ,'CWJINGDIAN' ,'重温经典频道','/','_'),
//https://mtmadm.tvmao.com/program_digital/CWJINGDIAN-w4.html
//https://mtmadm.tvmao.com/program/SITV-SITV-SPORTS-w5.html


);

$nid29=sizeof($cid29);
 for ($id29 = 1; $id29 <= $nid29; $id29++){
    $idd29=$id29+$id290;
    $chn.="<channel id=\"".$cid29[$id29-1][2]."\"><display-name lang=\"zh\">".$cid29[$id29-1][2]."</display-name></channel>\n";
 }

for ($id29 = 1; $id29<= $nid29; $id29++){

$idd29=$id29+$id290;

$url29='https://www.tvmao.com/program'.$cid29[$id29-1][4].$cid29[$id29-1][0].$cid29[$id29-1][3].$cid29[$id29-1][1].'-w'.$w1.'.html';
$ch29 = curl_init();
curl_setopt($ch29, CURLOPT_URL, $url29);
 curl_setopt($ch29, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch29, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch29, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch29,CURLOPT_ENCODING,'Vary: Accept-Encoding');
$re29=curl_exec($ch29);
$re29=str_replace('&','&amp;',$re29);
curl_close($ch29);
$re29 = preg_replace('/\s(?=)/', '',$re29);
//$re29 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re29);// 適合php7
$re29 =str_replace('<divclass="cur_player"><span>正在播出','',$re29);
preg_match('/周日(.*)查看更多<\/a>/i',$re29,$uk29);
$uk29[1]=str_replace('</a>','',$uk29[1]);
preg_match_all('|<spanclass="p_show">(.*?)</span>|i',$uk29[1],$un29,PREG_SET_ORDER);//播放節目內容
preg_match_all('|<spanclass="am">(.*?)</span>|i',$uk29[1],$um29,PREG_SET_ORDER);//播放節目时间
for ($k29 = 0; $k29 <=count($um29)-1; $k29++){
$chn.="<programme start=\"".$dt1.str_replace(':','',$um29[$k29][1]).'00 +0800'."\" stop=\"".$dt1.str_replace(':','',$um29[$k29+1][1]).'00 +0800'."\" channel=\"".$cid29[$id29-1][2]."\">\n<title lang=\"zh\">". preg_replace('/<[^>]+>/', '',$un29[$k29][1])."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
                                                               }


$url292='https://www.tvmao.com/servlet/accessToken?p=channelEpg';
$ch292 = curl_init();
curl_setopt($ch292, CURLOPT_URL, $url292);
 curl_setopt($ch292, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch292, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch292, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch292, CURLOPT_HTTPHEADER, array(
'Host: www.tvmao.com',
'Connection: keep-alive',
'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36 Edg/129.0.0.0',
'Referer: '.$url29,
//'Cookie: xsuid=e7508145-b5a5-4b2a-a1e2-8092fd347670; xsuid_time=2024-10-11; tvm_channel_province=BTV@台湾; FAD=1',
));
$re292=curl_exec($ch292);
$re292=str_replace('&','&amp;',$re292);
curl_close($ch292);
$token29=json_decode($re292)[1];
//print $token29;

$postfield291='tc='.$cid29[$id29-1][0].'&cc='.$cid29[$id29-1][1].'&w='.$w1.'&token='.$token29;
$url291='https://www.tvmao.com/servlet/channelEpg';
$ch291 = curl_init();
curl_setopt($ch291, CURLOPT_URL, $url291);
 curl_setopt($ch291, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch291, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch291, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch291, CURLOPT_HTTPHEADER, array(
'Host: www.tvmao.com',
'Connection: keep-alive',
//'Content-Length: 251',
'X-Requested-With: XMLHttpRequest',
'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36 Edg/129.0.0.0',
'Content-Type: application/x-www-form-urlencoded',
'Origin: https://www.tvmao.com',
'Referer: '.$url29,
));
curl_setopt ( $ch291, CURLOPT_POST, 1 );
curl_setopt ( $ch291, CURLOPT_POSTFIELDS, $postfield291);
//curl_setopt($ch291, CURLOPT_COOKIE, $cookie291);
curl_setopt($ch291,CURLOPT_ENCODING,'Vary: Accept-Encoding');
$re291=curl_exec($ch291);
$re291=str_replace('&','&amp;',$re291);
curl_close($ch291);
$re291 = preg_replace('/\s(?=)/', '',$re291);
//$re291 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re291);// 適合php7
$re291=stripslashes($re291);
//print $re291;


$re291=str_replace('</a>','',$re291);
$re291 =str_replace('<divclass="cur_player"><span>正在播出','',$re291);
preg_match_all('|<spanclass="pm">(.*?)</span>|i',$re291,$um291,PREG_SET_ORDER);//播放时间
preg_match_all('|<spanclass=\"p_show\">(.*?)</span>|i',$re291,$un291,PREG_SET_ORDER);//播放節目節目內容
//print_r($un291);
//print_r($um291);
$chn.="<programme start=\"".$dt1.str_replace(':','',$um29[count($um29)-1][1]).'00 +0800'."\" stop=\"".$dt1.str_replace(':','',$um291[0][1]).'00 +0800'."\" channel=\"".$cid29[$id29-1][2]."\">\n<title lang=\"zh\">". preg_replace('/<[^>]+>/', '',$un29[count($um29)-1][1])."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";


//$chn.="<programme start=\"".$dt1.'120000 +0800'."\" stop=\"".$dt1.str_replace(':','',$um291[0][1]).'00 +0800'."\" channel=\"".$cid29[$id29-1][2]."\">\n<title lang=\"zh\">". preg_replace('/<[^>]+>/', '',$un291[0][1])."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
for ($k291 = 0; $k291 <=count($um291)-1; $k291++){
$chn.="<programme start=\"".$dt1.str_replace(':','',$um291[$k291][1]).'00 +0800'."\" stop=\"".$dt1.str_replace(':','',$um291[$k291+1][1]).'00 +0800'."\" channel=\"".$cid29[$id29-1][2]."\">\n<title lang=\"zh\">". preg_replace('/<[^>]+>/', '',$un291[$k291][1])."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
                                                               }
$chn.="<programme start=\"".$dt1.str_replace(':','',$um291[count($um291)-1][1]).'00 +0800'."\" stop=\"".$dt1.'235900 +0800'."\" channel=\"".$cid29[$id29-1][2]."\">\n<title lang=\"zh\">". preg_replace('/<[^>]+>/', '',$un291[count($um291)-1][1])."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";


$url2911='https://www.tvmao.com/program'.$cid29[$id29-1][4].$cid29[$id29-1][0].$cid29[$id29-1][3].$cid29[$id29-1][1].'-w'.$w2.'.html';
//print $url29;

$ch2911 = curl_init();
curl_setopt($ch2911, CURLOPT_URL, $url2911);
 curl_setopt($ch2911, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch2911, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch2911, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch2911,CURLOPT_ENCODING,'Vary: Accept-Encoding');
$re2911=curl_exec($ch2911);
$re2911=str_replace('&','&amp;',$re2911);
curl_close($ch2911);
$re2911 = preg_replace('/\s(?=)/', '',$re2911);
$re2911 =str_replace('<divclass="cur_player"><span>正在播出','',$re2911);
//$re2911 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re2911);// 適合php7
preg_match('/周日(.*)查看更多<\/a>/i',$re2911,$uk2911);
$uk2911[1]=str_replace('</a>','',$uk2911[1]);

preg_match_all('|<spanclass="p_show">(.*?)</span>|i',$uk2911[1],$un2911,PREG_SET_ORDER);//播放節目內容
preg_match_all('|<spanclass="am">(.*?)</span>|i',$uk2911[1],$um2911,PREG_SET_ORDER);//播放節目时间
//print_r($un29);
//print_r($um29);
for ($k2911 = 1; $k2911 <=count($um2911)-2; $k2911++){
$chn.="<programme start=\"".$dt2.str_replace(':','',$um2911[$k2911-1][1]).'00 +0800'."\" stop=\"".$dt2.str_replace(':','',$um2911[$k2911][1]).'00 +0800'."\" channel=\"".$cid29[$id29-1][2]."\">\n<title lang=\"zh\">". preg_replace('/<[^>]+>/', '',$un2911[$k2911-1][1])."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
                                                               }

$url29112='https://www.tvmao.com/servlet/accessToken?p=channelEpg';
$ch29112 = curl_init();
curl_setopt($ch29112, CURLOPT_URL, $url29112);
 curl_setopt($ch29112, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch29112, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch29112, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch29112, CURLOPT_HTTPHEADER, array(
'Host: www.tvmao.com',
'Connection: keep-alive',
'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36 Edg/129.0.0.0',
'Referer: '.$url2911,
//'Cookie: xsuid=e7508145-b5a5-4b2a-a1e2-8092fd347670; xsuid_time=2024-10-11; tvm_channel_province=BTV@台湾; FAD=1',
));
$re29112=curl_exec($ch29112);
$re29112=str_replace('&','&amp;',$re29112);
curl_close($ch29112);
$token2911=json_decode($re29112)[1];
$postfield29111='tc='.$cid29[$id29-1][0].'&cc='.$cid29[$id29-1][1].'&w='.$w2.'&token='.$token2911;

$url29111='https://www.tvmao.com/servlet/channelEpg';
$ch29111 = curl_init();
curl_setopt($ch29111, CURLOPT_URL, $url29111);
 curl_setopt($ch29111, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch29111, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch29111, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch29111, CURLOPT_HTTPHEADER, array(
'Host: www.tvmao.com',
'Connection: keep-alive',
//'Content-Length: 251',
'X-Requested-With: XMLHttpRequest',
'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36 Edg/129.0.0.0',
//'Content-Type: application/x-www-form-urlencoded',
'Origin: https://www.tvmao.com',
'Referer: '.$url2911,
));
curl_setopt ( $ch29111, CURLOPT_POST, 1 );
curl_setopt ( $ch29111, CURLOPT_POSTFIELDS, $postfield29111);
//curl_setopt($ch29111, CURLOPT_COOKIE, $cookie29111);
curl_setopt($ch29111,CURLOPT_ENCODING,'Vary: Accept-Encoding');
$re29111=curl_exec($ch29111);
$re29111=str_replace('&','&amp;',$re29111);
curl_close($ch29111);
$re29111 = preg_replace('/\s(?=)/', '',$re29111);
//$re29111 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re29111);// 適合php7
$re29111=stripslashes($re29111);
//print $re291;

$re29111 =str_replace('<divclass="cur_player"><span>正在播出','',$re29111);
$re29111=str_replace('</a>','',$re29111);
preg_match_all('|<spanclass="pm">(.*?)</span>|i',$re29111,$um29111,PREG_SET_ORDER);//播放时间
preg_match_all('|<spanclass=\"p_show\">(.*?)</span>|i',$re29111,$un29111,PREG_SET_ORDER);//播放節目節目內容


$chn.="<programme start=\"".$dt2.str_replace(':','',$um2911[count($um2911)-1][1]).'00 +0800'."\" stop=\"".$dt2.str_replace(':','',$um29111[0][1]).'00 +0800'."\" channel=\"".$cid29[$id29-1][2]."\">\n<title lang=\"zh\">". preg_replace('/<[^>]+>/', '',$un2911[count($um2911)-2][1])."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";


for ($k29111 = 0; $k29111 <=count($um29111)-1; $k29111++){
$chn.="<programme start=\"".$dt2.str_replace(':','',$um29111[$k29111][1]).'00 +0800'."\" stop=\"".$dt2.str_replace(':','',$um29111[$k29111+1][1]).'00 +0800'."\" channel=\"".$cid29[$id29-1][2]."\">\n<title lang=\"zh\">". preg_replace('/<[^>]+>/', '',$un29111[$k29111][1])."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
                                                               }
$chn.="<programme start=\"".$dt2.str_replace(':','',$um29111[count($um29111)-1][1]).'00 +0800'."\" stop=\"".$dt2.'235900 +0800'."\" channel=\"".$cid29[$id29-1][2]."\">\n<title lang=\"zh\">". preg_replace('/<[^>]+>/', '',$un29111[count($um29111)-1][1])."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";

}     



$idn11=100652;//起始节目编号
$cid11=array(
array('chcna','CMC 北美频道'),
array('cmchk','CMC 香港频道'),
array('chchome','CHC 家庭影院'),
array('dypdepg','CCTV6 电影频道'),
array('xlepg','1905App 热血·影院'),
array('apptvepg','1905App 环球经典'),
);
$nid11=sizeof($cid11);


for ($idm11 = 1; $idm11 <= $nid11; $idm11++){


 $idd11=$idn11+$idm11;
   $chn.="<channel id=\"".$cid11[$idm11-1][1]."\"><display-name lang=\"zh\">".$cid11[$idm11-1][1]."</display-name></channel>\n";
}


for ($idm11 = 1; $idm11 <= $nid11; $idm11++){

 $idd11=$idn11+$idm11;

$url11="https://www.1905.com/cctv6/program/".$cid11[$idm11-1][0]."/list/";
//print $url10;

    $ch11 = curl_init();
    curl_setopt($ch11, CURLOPT_URL, $url11);
    curl_setopt($ch11, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch11, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch11, CURLOPT_SSL_VERIFYHOST, FALSE);
$headers11=[
'Host: www.1905.com',
'Connection: keep-alive',
'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36 Edg/129.0.0.0',

'Referer: '.$url11,
'Accept-Encoding: gzip, deflate, br, zstd',
'Accept-Language: en-US,en;q=0.9,zh-CN;q=0.8,zh;q=0.7,en-GB;q=0.6',

];
curl_setopt($ch11, CURLOPT_HTTPHEADER, $headers11);
    curl_setopt($ch11,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $re11 = curl_exec($ch11);
$re11=str_replace('&','&amp;',$re11);
    curl_close($ch11);
$re11 = preg_replace('/\s(?=)/', '',$re11);

$re11=str_replace('<em>(00:00-12:00)</em>','',$re11);
$re11=str_replace('<em>(12:00-24:00)</em>','',$re11);

preg_match('|<p>节目单(.*?)<!--footer-->|i',$re11,$rk11);
//print $re10;

//print $rk10[1];



preg_match_all('/<lidata-id="(.*?)"data-caturl/i',$rk11[1],$un11,PREG_SET_ORDER);//播放時間
preg_match_all('|<em>(.*?)</em>|i',$rk11[1],$ul11,PREG_SET_ORDER);//播放節目



$trm11=sizeof($ul11);
 

for ($k11 = 1; $k11 <= $trm11-1; $k11++) {
   $chn.="<programme start=\"".date("YmdHis",$un11[$k11-1][1]).' +0800'."\" stop=\"".date("YmdHis", $un11[$k11][1]).' +0800'."\" channel=\"" . $cid11[$idm11-1][1] . "\">\n<title lang=\"zh\">".str_replace('<','&lt;',str_replace('>','&gt;',str_replace(':','', $ul11[$k11-1][1])))."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";

}

$chn.="<programme start=\"".date("YmdHis",$un11[$trm11-1][1]).' +0800'."\" stop=\"".$dt1.'235900 +0800'."\" channel=\"" . $cid11[$idm11-1][1] . "\">\n<title lang=\"zh\">". str_replace('<','&lt;',str_replace('>','&gt;',str_replace(':','',$ul11[$k11-1][1])))."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";


}

 //22河南电视台
//$time22=time();
$id22=101035;//起始节目编号
$cid22=array(
array('145','河南卫视'),
array('149','河南新闻'),
array('141','河南都市'),
array('146','河南民生'),
array('147','河南法制'),
array('151','河南公共'),
array('152','河南乡村'),
array('148','河南电视剧'),
array('154','河南梨园戏曲'),
array('155','河南文物宝库'),
array('156','河南武术'),
array('157','河南晴彩中原'),
array('163','河南移动戏曲'),
array('183','河南象世界'),
array('150','河南欢腾购物'),
array('194','国学时代界'),






);
$nid22=sizeof($cid22);
for ($idm22 = 1; $idm22 <= $nid22; $idm22++){
 $idd22=$id22+$idm22;
   $chn.="<channel id=\"".$cid22[$idm22-1][1]."\"><display-name lang=\"zh\">".$cid22[$idm22-1][1]."</display-name></channel>\n";       
}

for ($idm22 = 1; $idm22 <= $nid22; $idm22++){
$ts = time();
$sign = hash('sha256', '6ca114a836ac7d73'.$ts);

   $url22='https://pubmod.hntv.tv/program/getAuth/vod/originStream/program/'.$cid22[$idm22-1][0].'/'.$ts;
 $idd22=$id22+$idm22;
    $ch22= curl_init();
    curl_setopt($ch22, CURLOPT_URL,$url22);
    curl_setopt($ch22, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch22, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch22, CURLOPT_SSL_VERIFYHOST, FALSE);
$headers22=[
'Host: pubmod.hntv.tv',
'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36 Edg/129.0.0.0',
'sign: '.$sign,
'timestamp: '.$ts,
'Origin: https://static.hntv.tv',

'Connection: keep-alive',
'Referer: https://static.hntv.tv/',
];

//curl_setopt($ch, CURLOPT_HTTPHEADER, array('timestamp: '.$ts,'sign: '.$sign));
//curl_setopt($ch, CURLOPT_USERAGENT, 'okhttp/3.12.0');
curl_setopt($ch22, CURLOPT_HTTPHEADER, $headers22);


curl_setopt($ch22,CURLOPT_ENCODING,'Vary: Accept-Encoding');
$re22 = curl_exec($ch22);
$re22=str_replace('&','&amp;',$re22);
curl_close($ch22);

//print $re21;

$programs22=json_decode($re22)->programs;
$tum22=count($programs22);


for ( $i22=1 ; $i22<=$tum22 ; $i22++ ) {
$title22=json_decode($re22)->programs[$i22-1]->title;//节目名称
$beginTime22=json_decode($re22)->programs[$i22-1]->beginTime;//节目开始时间
$endTime22=json_decode($re22)->programs[$i22-1]->endTime;//节目结束时间



$chn.="<programme start=\"".str_replace(' ','',str_replace('-','',str_replace(':','',date('Y-m-d H:i:s',$beginTime22)))).' +0800'."\" stop=\"".str_replace(' ','',str_replace('-','',str_replace(':','',date('Y-m-d H:i:s', $endTime22)))).' +0800'."\" channel=\"".$cid22[$idm22-1][1]."\">\n<title lang=\"zh\">". $title22."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";


}
}

 //19浙江電視台
$id19=100999;//起始节目编号
$cid19=array(
array('101','浙江卫视'),
array('102','浙江钱江都市'),
array('103','浙江经济'),
array('104','浙江科教'),
array('106','浙江民生'),
array('107','浙江新闻'),
array('108','浙江少儿'),
array('110','浙江国际'),
array('111','浙江好易购'),
array('112','浙江数码时代'),

);
$nid19=sizeof($cid19);


for ($idm19 = 1; $idm19 <= $nid19; $idm19++){
 $idd19=$id19+$idm19;
   $chn.="<channel id=\"".$cid19[$idm19-1][1]."\"><display-name lang=\"zh\">".$cid19[$idm19-1][1]."</display-name></channel>\n";
       
}

for ($idm19 = 1; $idm19 <= $nid19; $idm19++){
 $idd19=$id19+$idm19;
$url19='https://p.cztv.com/api/paas/program/'.$cid19[$idm19-1][0].'/'.$dt1;
    $ch19 = curl_init();
    curl_setopt($ch19, CURLOPT_URL, $url19);
    curl_setopt($ch19, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch19, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch19, CURLOPT_SSL_VERIFYHOST, FALSE);

$headers19=[

'Host: p.cztv.com',
'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:91.0) Gecko/20100101 Firefox/91.0',

'Origin: http://www.cztv.com',
'Connection: keep-alive',
'Referer: http://www.cztv.com/',

];
curl_setopt($ch19, CURLOPT_HTTPHEADER, $headers19);

    curl_setopt($ch19,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $re19 = curl_exec($ch19);
    curl_close($ch19);
 $re19 = str_replace('《','',$re19);
 $re19 = str_replace('》','',$re19);
$re19=str_replace('&','&amp;',$re19);
//print $re19;
$list19=json_decode($re19)->content->list[0]->list;
$ryut19=count($list19);

for ( $i19=0 ; $i19<=$ryut19-1 ; $i19++ ) {
//$date = date('d-m-Y H:i:s', $play_time);
$program_title=json_decode($re19)->content ->list[0] ->list[$i19]->program_title;
$play_time=json_decode($re19)->content ->list[0] ->list[$i19]->play_time;
$duration=json_decode($re19)->content ->list[0] ->list[$i19]->duration;


//$date = str_replace(' ','',str_replace('-','',str_replace(':','',date('Y-m-d H:i:s', 1565600000))));
                                                                                                             
//print $program_title;
//print $play_time;

$chn.="<programme start=\"".str_replace(' ','',str_replace('-','',str_replace(':','',date('Y-m-d H:i:s', ($play_time/1000))))).' +0800'."\" stop=\"".str_replace(' ','',str_replace('-','',str_replace(':','',date('Y-m-d H:i:s', (($play_time+$duration)/1000))))) .' +0800'."\" channel=\"".$cid19[$idm19-1][1]."\">\n<title lang=\"zh\">". str_replace(':','',$program_title)."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";




}
}


//广东电视台
$id20=101009;//起始节目编号
$cid20=array(
array('1','广东卫视'),
array('2','广东珠江'),
array('6','广东新闻'),
array('4','广东民生'),
array('14','广东大湾区卫视'),
//array('8','广东大湾区卫视海外版'),
array('3','广东体育'),
//array('13','广东经济科教'),
array('17','广东影视'),
array('16','广东综艺'),
//array('5','广东珠江境外'),
array('18','广东少儿'),
array('7','广东嘉佳卡通'),
array('31','广东现代教育'),
array('32','广东移动'),
array('33','广东岭南戏曲'),

);
$nid20=sizeof($cid20);
for ($idm20 = 1; $idm20 <= $nid20; $idm20++){
 $idd20=$id20+$idm20;
   $chn.="<channel id=\"".$cid20[$idm20-1][1]."\"><display-name lang=\"zh\">".$cid20[$idm20-1][1]."</display-name></channel>\n";       
}

for ($idm20 = 1; $idm20 <= $nid20; $idm20++){

$url20='http://epg.gdtv.cn/f/'.$cid20[$idm20-1][0].'/'.$dt11.'.xml';
 $idd20=$id20+$idm20;

    $ch20= curl_init();
    curl_setopt($ch20, CURLOPT_URL,$url20);
    curl_setopt($ch20, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch20, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch20, CURLOPT_SSL_VERIFYHOST, FALSE);

curl_setopt($ch20,CURLOPT_ENCODING,'Vary: Accept-Encoding');
$re20 = curl_exec($ch20);
curl_close($ch20);

$re20=str_replace('&','&amp;',$re20);




preg_match_all('|<content time1="(.*?)" time2=|i',$re20,$us20,PREG_SET_ORDER);//播放開始時間
preg_match_all('|time2="(.*?)">|i',$re20,$ue20,PREG_SET_ORDER);//播放結束時間
preg_match_all('|CDATA\[(.*?)\]\]>|i',$re20,$un20,PREG_SET_ORDER);//播放時間

//print_r($us20);
//print_r($ue20);
//print_r($un20);
$ryut20=count($un20);
for ( $i20=0 ; $i20<=$ryut20-1 ; $i20++ ) {

$chn.="<programme start=\"".str_replace(' ','',str_replace('-','',str_replace(':','',date('Y-m-d H:i:s', $us20[$i20][1])))).' +0800'."\" stop=\"".str_replace(' ','',str_replace('-','',str_replace(':','',date('Y-m-d H:i:s', $ue20[$i20][1])))).' +0800'."\" channel=\"".$cid20[$idm20-1][1]."\">\n<title lang=\"zh\">". $un20[$i20][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";






}


$url201='http://epg.gdtv.cn/f/'.$cid20[$idm20-1][0].'/'.$dt12.'.xml';

 $idd20=$id20+$idm20;
    $ch201= curl_init();
    curl_setopt($ch201, CURLOPT_URL,$url201);
    curl_setopt($ch201, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch201, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch201, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch201,CURLOPT_ENCODING,'Vary: Accept-Encoding');
$re201 = curl_exec($ch201);
curl_close($ch201);
$re201=str_replace('&','&amp;',$re201);
//print $re20;





preg_match_all('|<content time1="(.*?)" time2=|i',$re201,$us201,PREG_SET_ORDER);//播放開始時間
preg_match_all('|time2="(.*?)">|i',$re201,$ue201,PREG_SET_ORDER);//播放結束時間
preg_match_all('|CDATA\[(.*?)\]\]>|i',$re201,$un201,PREG_SET_ORDER);//播放時間


$ryut201=count($un201);
for ( $i201=0 ; $i201<=$ryut201-1 ; $i201++ ) {

$chn.="<programme start=\"".str_replace(' ','',str_replace('-','',str_replace(':','',date('Y-m-d H:i:s', $us201[$i201][1])))).' +0800'."\" stop=\"".str_replace(' ','',str_replace('-','',str_replace(':','',date('Y-m-d H:i:s', $ue201[$i201][1])))).' +0800'."\" channel=\"".$cid20[$idm20-1][1]."\">\n<title lang=\"zh\">". $un201[$i201][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";


}



}

//陝西

$id23=101051;
$cid23=array(

array('star','陝西卫视'),

array('1','陝西新闻资讯'),

array('2','陝西都市青春'),
array('3','陝西银龄频道'),
//array('4','陝西影视'),
array('5','陝西秦腔频道'),
array('6','陝西乐家购物'),
array('7','陝西体育休闲'),
array('nl','陝西农林'),
array('11','陝西移动电视'),

    );

 
$nid23=sizeof($cid23);
for ($idm23 = 1; $idm23 <= $nid23; $idm23++){
 $idd23=$id23+$idm23;
   $chn.="<channel id=\"".$cid23[$idm23-1][1]."\"><display-name lang=\"zh\">".$cid23[$idm23-1][1]."</display-name></channel>\n";
}
for ($idm23 = 1; $idm23 <= $nid23; $idm23++){

$url23="https://qidian.sxtvs.com/api/v3/program/tv?channel=".$cid23[$idm23-1][0];

 $idd23=$id23+$idm23;
    $ch23 = curl_init();
    curl_setopt($ch23, CURLOPT_URL, $url23);
    curl_setopt($ch23, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch23, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch23, CURLOPT_SSL_VERIFYHOST, FALSE);

/*
$headers23=[

'Host: qidian.sxtvs.com',
'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0',

'Accept-Encoding: gzip, deflate, br, zstd',
'Connection: keep-alive',
'Referer: http://live.snrtv.com/',
'Cookie: acw_tc=3daa4d2317647412879011377e843e8a5be0ed85fc36ca09f5abf8adc0; cdn_sec_tc=3daa4d2317647412879011377e843e8a5be0ed85fc36ca09f5abf8adc0',



];
curl_setopt($ch23, CURLOPT_HTTPHEADER, $headers23);
*/
    curl_setopt($ch23,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $re23 = curl_exec($ch23);
  //$re31=str_replace('&','&amp;',$re31);
    curl_close($ch23);
   // $re31=compress_html($re31);


$re23 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re23);// 適合php7
//print $re23;
preg_match_all('|"start":"(.*?)",|i',$re23,$us23,PREG_SET_ORDER);//獲取時間
preg_match_all('|"end":"(.*?)",|i',$re23,$ue23,PREG_SET_ORDER);//獲取時間
preg_match_all('|"name":"(.*?)",|i',$re23,$uk23,PREG_SET_ORDER);//獲取節目
//print_r($ue23);

$trm23=count($uk23);
  for ($k23 = 1; $k23 <=$trm23; $k23++) {
//if(str_replace(':','',$us23[$k23-1][1])<str_replace(':','',$us23[$k23][1])){
        $chn.="<programme start=\"".$dt1.str_replace(':','',$us23[$k23-1][1]).'00 +0800'."\" stop=\"".$dt1.str_replace(':','',$ue23[$k23-1][1]).'00 +0800'."\" channel=\"".$cid23[$idm23-1][1]."\">\n<title lang=\"zh\">".preg_replace('/\s(?=)/','',str_replace('</h4>','',$uk23[$k23-1][1]))."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
                                                                 

   }
/*
if(str_replace(':','',$us23[$k23][1])>str_replace(':','',$us23[$k23+1][1])){
        $chn.="<programme start=\"".$dt1.str_replace(':','',$us23[$k23][1]).'00 +0800'."\" stop=\"".($dt1+1).str_replace(':','',$ue23[$k23][1]).'00 +0800'."\" channel=\"".$cid23[$idm23-1][1]."\">\n<title lang=\"zh\">".preg_replace('/\s(?=)/','',str_replace('</h4>','',$uk23[$k23][1]))."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
 

                                                                                                       }

}
*/
}

//25安徽卫视
$id25=101060;//起始节目编号
$cid25=array(
       array('11','安徽卫视'),
    array('12','安徽经济'),
 array('16','安徽公共'),
 array('14','安徽影视'),
 array('13','安徽农业科教'),
 array('17','安徽综艺体育'),
 array('19','安徽人物'),
 array('18','安徽国际'),
);

$nid25=sizeof($cid25);
for ($idm25 = 1; $idm25 <= $nid25; $idm25++){
 $idd25=$id25+$idm25;
   $chn.="<channel id=\"".$cid25[$idm25-1][1]."\"><display-name lang=\"zh\">".$cid25[$idm25-1][1]."</display-name></channel>\n";
         
}

for ($idm25 = 1; $idm25 <= $nid25; $idm25++){

          
$url25='https://tvzb-hw.ahtv.cn/tvradio/Tvprogram/tvProgramList?index=1&page_size=99999&tv_id='.$cid25[$idm25-1][0];
$idd25=$id25+$idm25;
$ch25 = curl_init ();
curl_setopt ( $ch25, CURLOPT_URL, $url25 );
//curl_setopt ( $ch25, CURLOPT_HEADER, $hea );
curl_setopt($ch25,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch25,CURLOPT_SSL_VERIFYHOST,false);
curl_setopt ( $ch25, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt($ch25,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $re25 = curl_exec($ch25);
    $re25=str_replace('&','&amp;',$re25);
   curl_close($ch25);
$re25=str_replace('&','&amp;',$re25);
//print $re1;

preg_match_all('|"tv_program_name":"(.*?)","start_time|i',$re25,$um25,PREG_SET_ORDER);//播放節目

preg_match_all('|"start_time":(.*?),"replay_url|i',$re25,$un25,PREG_SET_ORDER);//播放時間

//print_r($um1);
//print_r($un1);


$trm25=count($um25);
  for ($k25 = 0; $k25 <=$trm25-2 ; $k25++) {  

 $chn.="<programme start=\"".date('YmdHis', $un25[$k25][1]).' +0800'."\" stop=\"".date('YmdHis', $un25[$k25+1][1]).' +0800'."\" channel=\"".$cid25[$idm25-1][1]."\">\n<title lang=\"zh\">".str_replace('<','&lt;',str_replace('&','&amp;',str_replace('>',' &gt;',$um25[$k25][1])))."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
 

               
}
}

$id26=101070;//起始节目编号
$cid26=array(
 
 array('广西卫视','广西卫视'),

 array('综艺旅游频道','广西综艺旅游频道'),
 array('都市频道','广西都市频道'),
 array('新闻频道','广西新闻频道'),
 array('影视频道','广西影视频道'),
 array('国际频道','广西国际频道'),
 array('乐思购频道','广西乐思购频道'),



);

$nid26=sizeof($cid26);
for ($idm26 = 1; $idm26 <= $nid26; $idm26++){
 $idd26=$id26+$idm26;
   $chn.="<channel id=\"".$cid26[$idm26-1][1]."\"><display-name lang=\"zh\">".$cid26[$idm26-1][1]."</display-name></channel>\n";
         
}

for ($idm26 = 1; $idm26 <= $nid26; $idm26++){

          
$url26='https://api2019.gxtv.cn/memberApi/programList/selectListByChannelId';
$postfile26='channelName='.$cid26[$idm26-1][0].'&dateStr='.$dt11.'&programName=&deptId=0a509685ba1a11e884e55cf3fc49331c&platformId=bd7d620a502d43c09b35469b3cd8c211';
$idd26=$id26+$idm26;

$ch26 = curl_init ();
curl_setopt ( $ch26, CURLOPT_URL, $url26 );
//curl_setopt ( $ch26, CURLOPT_HEADER, $hea );
curl_setopt($ch26,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch26,CURLOPT_SSL_VERIFYHOST,false);
curl_setopt ( $ch26, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt ( $ch26, CURLOPT_POST, 1 );
curl_setopt ( $ch26, CURLOPT_POSTFIELDS, $postfile26 );

curl_setopt($ch26, CURLOPT_HTTPHEADER, array(
'Host: api2019.gxtv.cn',
'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0',

//'Accept-Language: zh-TW,zh;q=0.8,en-US;q=0.5,en;q=0.3',
//'Accept-Encoding: gzip, deflate, br, zstd',
'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
'Authorization: ',
//Content-Length: 164
'Origin: https://program.gxtv.cn',
'Connection: keep-alive',
'Referer: https://program.gxtv.cn/',

'Priority: u=0',
)
);
curl_setopt($ch26,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $re26 = curl_exec($ch26);
    $re26=str_replace('&','&amp;',$re26);
   curl_close($ch26);

//print $re26;
//}

preg_match_all('|"programName":"(.*?)",|i',$re26,$um26,PREG_SET_ORDER);//播放節目

preg_match_all('|"programTime":"(.*?)"|i',$re26,$un26,PREG_SET_ORDER);//播放時間

$trm26=count($um26);
  for ($k26 = 1; $k26 <=$trm26-2 ; $k26++) {  

   $chn.="<programme start=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$un26[$k26-1][1]))).' +0800'."\" stop=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$un26[$k26][1]))).' +0800'."\" channel=\"".$cid26[$idm26-1][1]."\">\n<title lang=\"zh\">".$um26[$k26][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
                 
}
 

   $chn.="<programme start=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$un26[$trm26-1][1]))).' +0800'."\" stop=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$un26[$k26][1]))).' +0800'."\" channel=\"".$cid26[$idm26-1][1]."\">\n<title lang=\"zh\">".$um26[$k26][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";




$postfile261='channelName='.$cid26[$idm26-1][0].'&dateStr='.$dt12.'&programName=&deptId=0a509685ba1a11e884e55cf3fc49331c&platformId=bd7d620a502d43c09b35469b3cd8c211';
$idd26=$id26+$idm26;

$ch261 = curl_init ();
curl_setopt ( $ch261, CURLOPT_URL, $url26 );
//curl_setopt ( $ch261, CURLOPT_HEADER, $hea );
curl_setopt($ch261,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch261,CURLOPT_SSL_VERIFYHOST,false);
curl_setopt ( $ch261, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt ( $ch261, CURLOPT_POST, 1 );
curl_setopt ( $ch261, CURLOPT_POSTFIELDS, $postfile261 );

curl_setopt($ch261, CURLOPT_HTTPHEADER, array(
'Host: api2019.gxtv.cn',
'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0',

//'Accept-Language: zh-TW,zh;q=0.8,en-US;q=0.5,en;q=0.3',
//'Accept-Encoding: gzip, deflate, br, zstd',
'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
'Authorization: ',
//Content-Length: 164
'Origin: https://program.gxtv.cn',
'Connection: keep-alive',
'Referer: https://program.gxtv.cn/',

'Priority: u=0',
)
);
curl_setopt($ch261,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $re261 = curl_exec($ch261);
    $re261=str_replace('&','&amp;',$re261);
   curl_close($ch261);

//print $re26;
//}

preg_match_all('|"programName":"(.*?)",|i',$re261,$um261,PREG_SET_ORDER);//播放節目

preg_match_all('|"programTime":"(.*?)"|i',$re261,$un261,PREG_SET_ORDER);//播放時間

//print_r($um26);
//print_r($un26);


$trm261=count($um261);


$chn.="<programme start=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$un26[$trm26-1][1]))).' +0800'."\" stop=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$un261[0][1]))).' +0800'."\" channel=\"".$cid26[$idm26-1][1]."\">\n<title lang=\"zh\">".$um26[$trm26-1][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";

  for ($k261 = 1; $k261 <=$trm261-2 ; $k261++) {  

   $chn.="<programme start=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$un261[$k261-1][1]))).' +0800'."\" stop=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$un261[$k261][1]))).' +0800'."\" channel=\"".$cid26[$idm26-1][1]."\">\n<title lang=\"zh\">".$um261[$k261-1][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
                 
}

  $chn.="<programme start=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$un261[$trm261-1][1]))).' +0800'."\" stop=\"".$dt2.'235900 +0800'."\" channel=\"".$cid26[$idm26-1][1]."\">\n<title lang=\"zh\">".$um261[$k261][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";

}



/*
$idm30=100647;
$cid30=array(
array('4','福建综合'),

array('5','东南卫视'),
array('6','福建公共'),
array('7','福建电视剧'),

array('13','福建新闻'),
array('8','福建旅游'),
array('9','福建经济'),
array('10','福建体育'),
array('2','福建少儿'),
array('3','海峡卫视'),

);


$nid30=sizeof($cid30);

for ($id30 = 1; $id30 <= $nid30; $id30++){

    $idd30=$id30+$idm30;
    $chn.="<channel id=\"".$cid30[$id30-1][1]."\"><display-name lang=\"zh\">".$cid30[$id30-1][1]."</display-name></channel>\n";
 }


for ($id30 = 1; $id30 <= $nid30; $id30++){
 $t=time();
$tr=md5("877a9ba7a98f75b90a9d49f53f15a858&YmJjMjllMjJkODc2OGViZTUwYzRjYjAyYzBhZDg3YmU=&1.0.0&".$t);
$url30='https://live.fjtv.net/m2o/program_switch.php?channel_id='.$cid30[$id30-1][0].'&shownums=7&_='.$t;
   $idd30=$id30+$idm30;



$header30=array(
"Host: live.fjtv.net",
"User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:84.0) Gecko/20100101 Firefox/84.0",
"X-API-TIMESTAMP: ".$t,
"X-API-KEY: 877a9ba7a98f75b90a9d49f53f15a858",
"X-AUTH-TYPE: md5",
"X-API-VERSION: 1.0.0",
"X-API-SIGNATURE: ".$tr,
"Referer: http://live.fjtv.net/",
);
      $ch30 = curl_init();
    curl_setopt($ch30, CURLOPT_URL, $url30);
curl_setopt($ch30, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch30, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch30, CURLOPT_RETURNTRANSFER, TRUE);
    //curl_setopt($ch30, CURLOPT_COOKIEJAR, $cookie_jar);
curl_setopt($ch30, CURLOPT_TIMEOUT, 60); // CURLOPT_TIMEOUT_MS
    curl_setopt($ch30,CURLOPT_HTTPHEADER,$header30);
    $re30 = curl_exec($ch30);
   // $re=stripslashes($re);
    curl_close($ch30);

   preg_match_all('|<span class="time">(.*?)</span>|',$re30,$time30);
preg_match_all('|</span>(.*?)</li>|',$re30,$title30);
//print_r($time30);
//print_r($title30);

$trm30=count($time30[1]);
//print $trm30;


  for ($k30 =1; $k30 <=$trm30-2 ; $k30++) {  
        $chn.="<programme start=\"".$dt1.str_replace(':','',$time30[1][$k30-1]).' +0800'."\" stop=\"".$dt1.str_replace(':','',$time30[1][$k30]).' +0800'."\" channel=\"".$cid30[$id30-1][1]."\">\n<title lang=\"zh\">".$title30[1][$k30+6]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
                                                            
 

    }

$chn.="<programme start=\"".$dt1.str_replace(':','',$time30[1][$trm30-1]).' +0800'."\" stop=\"".$dt1.'235900 +0800'."\" channel=\"".$cid30[$id30-1][1]."\">\n<title lang=\"zh\">".$title30[1][$trm30+6]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
}

*/
//廈門電視
$id33=100669;
$cid33=array(

array('84','厦门卫视'),

array('16','厦门1'),

array('17','厦门2'),

//array('52','厦门移动'),


    );

$nid33=sizeof($cid33);
for ($idm33 = 1; $idm33 <= $nid33; $idm33++){
 $idd33=$id33+$idm33;
   $chn.="<channel id=\"".$cid33[$idm33-1][1]."\"><display-name lang=\"zh\">".$cid33[$idm33-1][1]."</display-name></channel>\n";
}
for ($idm33 = 1; $idm33 <= $nid33; $idm33++){
//$url23="http://m.snrtv.com/index.php?m=playlist_tv&channel=".$cid23[$idm23-1][0];
$url33="https://mapi1.kxm.xmtv.cn/api/v1/tvshow_share.php?channel_id=".$cid33[$idm33-1][0]."&zone=";
 $idd33=$id33+$idm33;
    $ch33 = curl_init();
    curl_setopt($ch33, CURLOPT_URL, $url33);
    curl_setopt($ch33, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch33, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch33, CURLOPT_SSL_VERIFYHOST, FALSE);
$headers33=[
'Host: mapi1.kxm.xmtv.cn',
'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:96.0) Gecko/20100101 Firefox/96.0',
'Origin: https://share1.kxm.xmtv.cn',
//Cookie: Hm_lvt_22ac8379032eba86b4501cf27e79465c=1629937088; Hm_lpvt_22ac8379032eba86b4501cf27e79465c=1629937088
];
curl_setopt($ch33, CURLOPT_HTTPHEADER, $headers33);
    curl_setopt($ch33,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $re33 = curl_exec($ch33);
    curl_close($ch33);
   // $re31=compress_html($re31);

$re33=str_replace('&','&amp;',$re33);

$re33 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re33);// 適合php7



preg_match_all('|"start_time":(.*?),"date|i',$re33,$us33,PREG_SET_ORDER);//獲取時間
preg_match_all('|"end_time":(.*?),"m3u8|i',$re33,$ue33,PREG_SET_ORDER);//獲取時間
preg_match_all('|"theme":"(.*?)"|i',$re33,$uk33,PREG_SET_ORDER);//獲取節目

$trm33=count($uk33);

 for ($k33 = 0; $k33 < $trm33-1; $k33++) {
// $chn.="<programme start=\"".$dt1.str_replace(':','',date("H:i", $us33[$k33][1])).' +0800'."\" stop=\"".$dt1.str_replace(':','',date("H:i", $ue33[$k33][1])).' +0800'."\" channel=\"".$cid33[$idm33-1][1]."\">\n<title lang=\"zh\">".$uk33[$k33][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
                                                            
   $chn.="<programme start=\"".str_replace(' ','',str_replace('-','',str_replace(':','', date('Y-m-d H:i:s', $us33[$k33][1])))).' +0800'."\" stop=\"".str_replace(' ','',str_replace('-','',str_replace(':','', date('Y-m-d H:i:s', $ue33[$k33][1])))).' +0800'."\" channel=\"".$cid33[$idm33-1][1]."\">\n<title lang=\"zh\">".$uk33[$k33][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";                               




}
              

   }

$idm31=100658;
$cid31=array(
array('462','河北卫视'),

array('114','河北经济'),
array('118','河北农民'),
array('62','河北都市'),
array('334','河北影视剧'),
array('70','河北少儿科教'),
array('338','河北公共'),

);


$nid31=sizeof($cid31);

for ($id31 = 1; $id31 <= $nid31; $id31++){

    $idd31=$id31+$idm31;
    $chn.="<channel id=\"".$cid31[$id31-1][1]."\"><display-name lang=\"zh\">".$cid31[$id31-1][1]."</display-name></channel>\n";
 }

for ($id31 = 1; $id31 <= $nid31; $id31++){
   $idd31=$id31+$idm31;

$ch311 = curl_init();
curl_setopt_array($ch311, array(
CURLOPT_URL => 'https://api.cmc.hebtv.com/spidercrms/api/live/liveShowSet/findNoPage',
CURLOPT_CUSTOMREQUEST => 'OPTIONS',
CURLOPT_RETURNTRANSFER => true,
CURLOPT_HEADER => true,
CURLOPT_NOBODY => true,
CURLOPT_VERBOSE => true,
CURLOPT_SSL_VERIFYPEER=>false,
CURLOPT_SSL_VERIFYHOST =>false,
));

$r = curl_exec($ch311);

//echo PHP_EOL.'Response Headers:'.PHP_EOL;

//print_r($r);

curl_close($ch311);



$url31 = 'https://api.cmc.hebtv.com/spidercrms/api/live/liveShowSet/findNoPage';

$data1 = array(
    "sourceId" => $cid31[$id31 - 1][0],
    "tenantId" => "0d91d6cfb98f5b206ac1e752757fc5a9",
    "day" => "$dt11",
    "dayEnd" => "$dt11",
);

$encryptString = json_encode($data1, true);

$ch31 = curl_init();
curl_setopt($ch31, CURLOPT_URL, $url31);
curl_setopt($ch31, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch31, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch31, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch31, CURLOPT_POST, 1);
curl_setopt($ch31, CURLOPT_POSTFIELDS, $encryptString); // 使用 JSON 编码后的字符串

curl_setopt($ch31, CURLOPT_HTTPHEADER, array(
    'Connection: keep-alive',
    'Content-Length: ' . strlen($encryptString),
    'tenantId: 0d91d6cfb98f5b206ac1e752757fc5a9',
    'DNT: 1',
    'Content-Type: application/json',
    'Origin: https://www.hebtv.com',
    'Referer: https://www.hebtv.com/',
));

$re31 = curl_exec($ch31);
$re31=str_replace('&','&amp;',$re31);
curl_close($ch31);

// 处理返回的 JSON

    $re31 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re31);
 // print $re31;


preg_match_all('/"startDateTime":"(.*?)",/i',$re31,$us31,PREG_SET_ORDER);//播放開始時間
preg_match_all('/"endDateTime":"(.*?)",/',$re31,$ue31,PREG_SET_ORDER);//播放結束時間
preg_match_all('/name":"(.*?)",/',$re31,$un31,PREG_SET_ORDER);//播放時間
//print_r($us31);
//print_r($ue31);
//print_r($un31);

$trm31=count($us31);
for ($k31 = 0; $k31 <= $trm31-1 ; $k31++) {  


   $chn.="<programme start=\"".str_replace(' ','',str_replace(':','',str_replace('-','',
$us31[$k31][1]))).' +0800'."\" stop=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$ue31[$k31][1]))).' +0800'."\" channel=\"".$cid31[$id31-1][1]."\">\n<title lang=\"zh\">". $un31[$k31][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
}



$url31 = 'https://api.cmc.hebtv.com/spidercrms/api/live/liveShowSet/findNoPage';

$data11 = array(
    "sourceId" => $cid31[$id31 - 1][0],
    "tenantId" => "0d91d6cfb98f5b206ac1e752757fc5a9",
    "day" => "$dt11",
    "dayEnd" => "$dt12",
);

$encryptString1 = json_encode($data11, true);

$ch311 = curl_init();
curl_setopt($ch311, CURLOPT_URL, $url31);
curl_setopt($ch311, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch311, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch311, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch311, CURLOPT_POST, 1);
curl_setopt($ch311, CURLOPT_POSTFIELDS, $encryptString1); // 使用 JSON 编码后的字符串

curl_setopt($ch311, CURLOPT_HTTPHEADER, array(
    'Connection: keep-alive',
    'Content-Length: ' . strlen($encryptString),
    'tenantId: 0d91d6cfb98f5b206ac1e752757fc5a9',
    'DNT: 1',
    'Content-Type: application/json',
    'Origin: https://www.hebtv.com',
    'Referer: https://www.hebtv.com/',
));

$re311 = curl_exec($ch311);
$re311=str_replace('&','&amp;',$re311);
curl_close($ch311);

// 处理返回的 JSON

    $re311 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re311);
 // print $re31;


preg_match_all('/"startDateTime":"(.*?)",/i',$re311,$us311,PREG_SET_ORDER);//播放開始時間
preg_match_all('/"endDateTime":"(.*?)",/',$re311,$ue311,PREG_SET_ORDER);//播放結束時間
preg_match_all('/name":"(.*?)",/',$re311,$un311,PREG_SET_ORDER);//播放時間
//print_r($us31);
//print_r($ue31);
//print_r($un31);

$trm311=count($us311);
for ($k311 = 0; $k311 <= $trm311-1 ; $k311++) {  


   $chn.="<programme start=\"".str_replace(' ','',str_replace(':','',str_replace('-','',
$us311[$k311][1]))).' +0800'."\" stop=\"".str_replace(' ','',str_replace(':','',str_replace('-','',$ue311[$k311][1]))).' +0800'."\" channel=\"".$cid31[$id31-1][1]."\">\n<title lang=\"zh\">". $un311[$k311][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
}





}


/*
//河北电视台

$idm31=100658;
$cid31=array(
array('462','河北卫视'),

array('114','河北经济'),
array('118','河北农民'),
array('62','河北都市'),
array('334','河北影视剧'),
array('70','河北少儿科教'),
array('338','河北公共'),

);


$nid31=sizeof($cid31);

for ($id31 = 1; $id31 <= $nid31; $id31++){

    $idd31=$id31+$idm31;
    $chn.="<channel id=\"".$cid31[$id31-1][1]."\"><display-name lang=\"zh\">".$cid31[$id31-1][1]."</display-name></channel>\n";
 }

for ($id31 = 1; $id31 <= $nid31; $id31++){
   $idd31=$id31+$idm31;

$ch311 = curl_init();
curl_setopt_array($ch311, array(
CURLOPT_URL => 'https://api.cmc.hebtv.com/appapi/api/content/get-live-info',
CURLOPT_CUSTOMREQUEST => 'OPTIONS',
CURLOPT_RETURNTRANSFER => true,
CURLOPT_HEADER => true,
CURLOPT_NOBODY => true,
CURLOPT_VERBOSE => true,
CURLOPT_SSL_VERIFYPEER=>false,
CURLOPT_SSL_VERIFYHOST =>false,
));

$r = curl_exec($ch311);

//echo PHP_EOL.'Response Headers:'.PHP_EOL;

//print_r($r);

curl_close($ch311);



$url31='https://api.cmc.hebtv.com/appapi/api/content/get-live-info';


$data1 = array(
 "sourceId" =>$cid31[$id31-1][0],
"tenantId" => "0d91d6cfb98f5b206ac1e752757fc5a9",
"tenantid" => "0d91d6cfb98f5b206ac1e752757fc5a9",
"api_version" => "3.7.0",
"client" => "android",
"cms_app_id" => "19",
"app_id" => 2,
"app_version" => "1.0.39",
"no_cache" => "yes",

);


$encryptString = json_encode($data1,true);
//echo $encryptString;
$url31='https://api.cmc.hebtv.com/appapi/api/content/get-live-info';
    $ch31 = curl_init();
    curl_setopt($ch31, CURLOPT_URL, $url31);
  curl_setopt($ch31, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch31, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch31, CURLOPT_SSL_VERIFYHOST, false);
//curl_setopt($ch31, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
//curl_setopt($ch31, CURLOPT_CUSTOMREQUEST, NULL);
    curl_setopt ( $ch31, CURLOPT_POST, 1 );
    curl_setopt ( $ch31, CURLOPT_POSTFIELDS, $data1 );
      //curl_setopt($ch31, CURLOPT_COOKIEJAR, $cookie_jar)
//curl_setopt($ch31, CURLOPT_TIMEOUT, 28); // CURLOPT_TIMEOUT_MS
   $re31 = curl_exec($ch31);



    curl_close($ch31);
$re31 = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re31);// 適合php7
//print  $re31;

$uuu=json_decode($re31);
$tuuu31=$uuu->result->data->$dt11;//http://www.bejson.com/jsonviewernew/进行结果转化
$tyu31=count($tuuu31);
//print_r($tuuu31);





for ( $i31=0 ; $i31<=$tyu31-1 ; $i31++ ) {
$endDateTime31=$tuuu31[$i31]->endDateTime;
$name31=$tuuu31[$i31]->name;
$startDateTime31=$tuuu31[$i31]->startDateTime;
//$endDateTime31=json_decode($re31)->result->data->$dt11[$i31]->endDateTime;
//$name31=json_decode($re31)->result->data->$dt11[$i31]->name;


$startDateTime31=str_replace(' ','',$startDateTime31);

$startDateTime31=str_replace('-','',$startDateTime31);
$startDateTime31=str_replace(':','',$startDateTime31);

$endDateTime31=str_replace(' ','',$endDateTime31);
$endDateTime31=str_replace(':','',$endDateTime31);
$endDateTime31=str_replace('-','',$endDateTime31);

$chn.="<programme start=\"".$startDateTime31.' +0800'."\" stop=\"".$endDateTime31.' +0800'."\" channel=\"".$cid31[$id31-1][1]."\">\n<title lang=\"zh\">".$name31."</title>\n<desc lang=\"zh\"></desc>\n</programme>\n";

}


$tuuu32=$uuu->result->data->$dt12;//http://www.bejson.com/jsonviewernew/进行结果转化

$tyu32=count($tuuu32);

for ( $i32=0 ; $i32<=$tyu32-1 ; $i32++ ) {
$startDateTime32=$tuuu32[$i32]->startDateTime;
$endDateTime32=$tuuu32[$i32]->endDateTime;
$name32=$tuuu32[$i32]->name;


$startDateTime32=str_replace(' ','',$startDateTime32);

$startDateTime32=str_replace('-','',$startDateTime32);
$startDateTime32=str_replace(':','',$startDateTime32);

$endDateTime32=str_replace(' ','',$endDateTime32);
$endDateTime32=str_replace(':','',$endDateTime32);
$endDateTime32=str_replace('-','',$endDateTime32);

$chn.="<programme start=\"".$startDateTime32.' +0800'."\" stop=\"".$endDateTime32.' +0800'."\" channel=\"".$cid31[$id31-1][1]."\">\n<title lang=\"zh\">".$name32."</title>\n<desc lang=\"zh\"></desc>\n</programme>\n";

}




}


//海南電視台
$time=time();
    
$id35=100672;
$cid35=array(
array('7','三沙卫视'),
array('3','海南卫视'),
array('4','海南经济'),
array('5','海南新闻'),
array('6','海南公共'),
 array('8','海南文旅'),
 array('9','海南少儿'),
//id=7三沙卫视,19海南卫视高清,3卫视标清,4经济频道,5新闻频道,6公共频道,8海南文旅,,9海南少儿,11新闻广播
);

$nid35=sizeof($cid35);
for ($idm35 = 1; $idm35 <= $nid35; $idm35++){
 $idd35=$id35+$idm35;
   $chn.="<channel id=\"".$cid35[$idm35-1][1]."\"><display-name lang=\"zh\">".$cid35[$idm35-1][1]."</display-name></channel>\n";
         
}

for ($idm35 = 1; $idm35 <= $nid35; $idm35++){

 $idd35=$id35+$idm35;

//$url28="http://module.iqilu.com/media/apis/main/getprograms?channelID=".$cid28[$idm28-1][0]."&date=".$dt11;
$url35="http://www.hnntv.cn/m2o/program_switch.php?channel_id=".$cid35[$idm35-1][0]."&shownums=7&_=".$time;

$ch35=curl_init();
curl_setopt($ch35,CURLOPT_URL,$url35);;
curl_setopt($ch35,CURLOPT_RETURNTRANSFER,1);
 //curl_setopt($ch25, CURLOPT_SSL_VERIFYPEER, FALSE);
//curl_setopt($ch25, CURLOPT_SSL_VERIFYHOST, FALSE);
//curl_setopt($ch35,CURLOPT_ENCODING,'Vary: Accept-Encoding');
$re35=curl_exec($ch35);
curl_close($ch35);  
//print $re35;

$re35= preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re35);// 適合php7

preg_match_all('|</span>(.*?)</li>|i',$re35,$tile35,PREG_SET_ORDER);//播放節目
preg_match_all('|<span class="time">(.*?)</span>|i',$re35,$st35,PREG_SET_ORDER);//播放節目時間


$tuuy35=count($st35);
//print_r($tile35);
//print_r($st35);


for ($k35 = 0; $k35<= $tuuy35-2; $k35++) { 
   
                 
 $chn.="<programme start=\"".$dt1.str_replace(' ','',str_replace('-','',str_replace(':','',$st35[$k35][1]))).'00 +0800'."\" stop=\"".$dt1.str_replace(' ','',str_replace('-','',str_replace(':','',$st35[$k35+1][1]))).'00 +0800'."\" channel=\"".$cid35[$idm35-1][1]."\">\n<title lang=\"zh\">".$tile35[$k35+7][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
}




 $chn.="<programme start=\"".$dt1.str_replace(' ','',str_replace('-','',str_replace(':','',$st35[$tuuy35-1][1]))).'00 +0800'."\" stop=\"".$dt1.'235900 +0800'."\" channel=\"".$cid35[$idm35-1][1]."\">\n<title lang=\"zh\">".$tile35[$tuuy35+6][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";

}
*/
//山東電視台
$id28=100680;
$cid28=array(
array('24','山东卫视'),
array('31','山东新闻'),
array('25','山东齐鲁'),
array('26','山东体育'),
array('29','山东生活'),
array('28','山东综艺'),
array('30','山东农科'),
array('27','山东文旅'),
  array('32','山东少儿'),

);

$nid28=sizeof($cid28);
for ($idm28 = 1; $idm28 <= $nid28; $idm28++){
 $idd28=$id28+$idm28;
   $chn.="<channel id=\"".$cid28[$idm28-1][1]."\"><display-name lang=\"zh\">".$cid28[$idm28-1][1]."</display-name></channel>\n";
         
}

for ($idm28 = 1; $idm28 <= $nid28; $idm28++){

 $idd28=$id28+$idm28;

$url28="http://module.iqilu.com/media/apis/main/getprograms?channelID=".$cid28[$idm28-1][0]."&date=".$dt11;


$ch28=curl_init();
curl_setopt($ch28,CURLOPT_URL,$url28);;
curl_setopt($ch28,CURLOPT_RETURNTRANSFER,1);
 //curl_setopt($ch25, CURLOPT_SSL_VERIFYPEER, FALSE);
//curl_setopt($ch25, CURLOPT_SSL_VERIFYHOST, FALSE);
//curl_setopt($ch25,CURLOPT_ENCODING,'Vary: Accept-Encoding');
$re28=curl_exec($ch28);
$re28=str_replace('&','&amp;',$re28);
curl_close($ch28);  
//print $re28;

$re28= preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re28);// 適合php7

preg_match_all('|{"name":"(.*?)",|i',$re28,$tile28,PREG_SET_ORDER);//播放節目
preg_match_all('/"begintime":(.*?),"endtime/i',$re28,$st28,PREG_SET_ORDER);//播放節目開始
preg_match_all('|"endtime":(.*?)},|i',$re28,$et28,PREG_SET_ORDER);//播放節目結束


$tuuy28=count($et28);
//print_r($tile28);
//print_r($st28);
//print_r($et28);



for ($k28 = 0; $k28<= $tuuy28-1; $k28++) { 
    $chn.="<programme start=\"".str_replace(' ','',str_replace('-','',str_replace(':','',date('Y-m-d H:i:s', $st28[$k28][1])))).' +0800'."\" stop=\"".str_replace(' ','',str_replace('-','',str_replace(':','',date('Y-m-d H:i:s', $et28[$k28][1])))).' +0800'."\" channel=\"".$cid28[$idm28-1][1]."\">\n<title lang=\"zh\">".str_replace('<','&lt;',str_replace('&','&amp;',str_replace('>',' &gt;',$tile28[$k28][1])))."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
                 

}
}
$id29=100951;
$cid29=array(

array('1','新疆卫视HD'),
array('3','新疆维吾尔语新闻综合频道'),
array('4','新疆哈萨克语新闻综合频道'),
array('16','新疆汉语综艺频道'),
array('17','新疆维吾尔语影视频道'),
array('18','新疆汉语经济生活频道'),
array('19','新疆哈萨克语综艺频道'),
array('20','新疆维吾尔语经济生活频道'),
array('21','新疆汉语体育健康频道'),
array('22','新疆汉语信息服务频道'),
array('23','新疆少儿频道'),
    );
$nid29=sizeof($cid29);
for ($idm29 = 1; $idm29 <= $nid29; $idm29++){
 $idd29=$id29+$idm29;
   $chn.="<channel id=\"".$cid29[$idm29-1][1]."\"><display-name lang=\"zh\">".$cid29[$idm29-1][1]."</display-name></channel>\n";
}
for ($idm29 = 1; $idm29 <= $nid29; $idm29++){
$url29="https://slstapi.xjtvs.com.cn/api/TVLiveV100/TVGuideList?tvChannelId=".$cid29[$idm29-1][0]."&date=".$dt11."+00:00:00&json=true";
 $idd29=$id29+$idm29;
    $ch29 = curl_init();
    curl_setopt($ch29, CURLOPT_URL, $url29);
    curl_setopt($ch29, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch29, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch29, CURLOPT_SSL_VERIFYHOST, FALSE);
$headersch=[
'Host: slstapi.xjtvs.com.cn',
'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:91.0) Gecko/20100101 Firefox/91.0',
//Accept-Language: zh-TW,zh;q=0.8,en-US;q=0.5,en;q=0.3
//Accept-Encoding: gzip, deflate
'Connection: keep-alive',
'Referer: https://www.xjtvs.com.cn/',
//Cookie: Hm_lvt_22ac8379032eba86b4501cf27e79465c=1629937088; Hm_lpvt_22ac8379032eba86b4501cf27e79465c=1629937088

];
curl_setopt($ch29, CURLOPT_HTTPHEADER, $headers29);
  //  curl_setopt($ch29,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $re29 = curl_exec($ch29);
$re29=str_replace('&','&amp;',$re29);
    curl_close($ch29);
preg_match_all('|"StartTime":"(.*?)",|i',$re29,$us29,PREG_SET_ORDER);//獲取時間
preg_match_all('|"EndTime":"(.*?)"|i',$re29,$ue29,PREG_SET_ORDER);//獲取時間
preg_match_all('|"Name":"(.*?)",|i',$re29,$uk29,PREG_SET_ORDER);//獲取節目
$trm29=count($uk29);
  for ($k29 = 0; $k29 < $trm29-1; $k29++) {
        $chn.="<programme start=\"".$dt1.str_replace(':','',$us29[$k29][1]).'00 +0800'."\" stop=\"".($dt1).str_replace(':','',$ue29[$k29][1]).'00 +0800'."\" channel=\"".$cid29[$idm29-1][1]."\">\n<title lang=\"zh\">".preg_replace('/\s(?=)/','',str_replace('</h4>','',$uk29[$k29][1]))."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
                                          }
$url291="https://slstapi.xjtvs.com.cn/api/TVLiveV100/TVGuideList?tvChannelId=".$cid29[$idm29-1][0]."&date=".$dt12."+00:00:00&json=true";
 $idd291=$id29+$idm291;
    $ch291 = curl_init();
    curl_setopt($ch291, CURLOPT_URL, $url291);
    curl_setopt($ch291, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch291, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch291, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch291, CURLOPT_HTTPHEADER, $headers29);
  //  curl_setopt($id291,CURLOPT_ENCODING,'Vary: Accept-Encoding');
    $re291 = curl_exec($ch291);
$re291=str_replace('&','&amp;',$re291);
    curl_close($ch291);
preg_match_all('|"StartTime":"(.*?)",|i',$re291,$us291,PREG_SET_ORDER);//獲取時間
preg_match_all('|"EndTime":"(.*?)"|i',$re291,$ue291,PREG_SET_ORDER);//獲取時間
preg_match_all('|"Name":"(.*?)",|i',$re291,$uk291,PREG_SET_ORDER);//獲取節目
$trm291=count($uk291);
  for ($k291 = 0; $k291 < $trm291-1; $k291++) {
        $chn.="<programme start=\"".$dt2.str_replace(':','',$us291[$k291][1]).'00 +0800'."\" stop=\"".($dt2).str_replace(':','',$ue291[$k291][1]).'00 +0800'."\" channel=\"".$cid29[$idm29-1][1]."\">\n<title lang=\"zh\">".preg_replace('/\s(?=)/','',str_replace('</h4>','',$uk291[$k291][1]))."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
                                          }

}
/*
深圳电视台

$id34=100662;
$cid34=array(


array("AxeFRth","深圳卫视"),

 array("ZwxzUXr","深圳都市"),
 array("4azbkoY","深圳电视剧"),
 array("2q76Sw2","深圳公共"),
 array("3vlcoxP","深圳财经"),
 array("1q4iPng","深圳娱乐"),
 array("1SIQj6s","深圳少儿"),
 array("wDF6KJ3","深圳移动电视"),
 array("BJ5u5k2","深圳购物"),
 array("xO1xQFv","深圳DV生活"),
 array("sztvgjpd","深圳"),
);
$nid34=sizeof($cid34);
for ($idm34 = 1; $idm34<= $nid34; $idm34++){
 $idd34=$id34+$idm34;
   $chn.="<channel id=\"".$cid34[$idm34-1][1]."\"><display-name lang=\"zh\">".$cid34[$idm34-1][1]."</display-name></channel>\n";
    
}

for ($idm34 = 1; $idm34 <= $nid34; $idm34++){
 $idd34=$id34+$idm34;
$url34="http://cls2.cutv.com/api/getEpgs?channelId=".$cid34[$idm34-1][0]."&daytime=".$time111;
$ch34=curl_init();
curl_setopt($ch34,CURLOPT_URL,$url34);;
curl_setopt($ch34,CURLOPT_RETURNTRANSFER,1);
 //curl_setopt($ch25, CURLOPT_SSL_VERIFYPEER, FALSE);
//curl_setopt($ch25, CURLOPT_SSL_VERIFYHOST, FALSE);
//curl_setopt($ch25,CURLOPT_ENCODING,'Vary: Accept-Encoding');
$re34=curl_exec($ch34);
curl_close($ch34);  
$re34= preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re34);// 適合php7
$list=json_decode($re34)->list;
$yyy=count($list);
$daytime=json_decode($re34)->list[$yyy-1]->daytime; 
$programme=json_decode($re34)->list[$yyy-1]->programme; 
$tuuy34= count($programme);
for ($k34 = 0; $k34<= $tuuy34-1; $k34++) { 
$s=$programme[$k34]->s; 
$e=$programme[$k34+1]->s; 
$t=$programme[$k34]->t; 
   $chn.="<programme start=\"".str_replace(' ','',str_replace('-','',str_replace(':','',date('Y-m-d H:i:s', ($s+$daytime)/1000)))).' +0800'."\" stop=\"".str_replace(' ','',str_replace('-','',str_replace(':','',date('Y-m-d H:i:s',  ($e+$daytime)/1000)))).' +0800'."\" channel=\"".$cid34[$idm34-1][1]."\">\n<title lang=\"zh\">".str_replace('<','&lt;',str_replace('&','&amp;',str_replace('>',' &gt;',$t)))."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
                 

}



}



// 获取当前时间戳
$currentTime = time();

// 创建今天中午12点的时间戳
$noonToday = strtotime('today 12:00:00');

// 判断并输出结果
//if ($currentTime > $noonToday) {
if (date('w')==1) {


  $url661='https://www.dubaione.ae/content/dubaione/en-ae/schedule.3.html';
$url662='https://www.dubaione.ae/content/dubaione/en-ae/schedule.4.html';
    // 这里添加情况1的代码逻辑
} 

if (date('w')==2) {



  $url661='https://www.dubaione.ae/content/dubaione/en-ae/schedule.2.html';
$url662='https://www.dubaione.ae/content/dubaione/en-ae/schedule.3.html';
    // 这里添加情况1的代码逻辑
} 

if (date('w')==4) {



  $url661='https://www.dubaione.ae/content/dubaione/en-ae/schedule.2.html';
$url662='https://www.dubaione.ae/content/dubaione/en-ae/schedule.3.html';
    // 这里添加情况1的代码逻辑
} 

if (date('w')==5) {


  $url661='https://www.dubaione.ae/content/dubaione/en-ae/schedule.3.html';
$url662='https://www.dubaione.ae/content/dubaione/en-ae/schedule.4.html';
    // 这里添加情况1的代码逻辑
} 

if (date('w')==6) {


  $url661='https://www.dubaione.ae/content/dubaione/en-ae/schedule.4.html';
//$url662='https://www.dubaione.ae/content/dubaione/en-ae/schedule.4.html';
    // 这里添加情况1的代码逻辑
} 
if (date('w')==0) {


  $url661='https://www.dubaione.ae/content/dubaione/en-ae/schedule.2.html';
$url662='https://www.dubaione.ae/content/dubaione/en-ae/schedule.3.html';
    // 这里添加情况1的代码逻辑
} 




if(date('w')==3) {



// 创建今天中午12点的时间戳


// 判断并输出结果
if ($currentTime > $noonToday) {



  $url661='https://www.dubaione.ae/content/dubaione/en-ae/schedule.2.html';
$url662='https://www.dubaione.ae/content/dubaione/en-ae/schedule.3.html';
}else{

 $url661='https://www.dubaione.ae/content/dubaione/en-ae/schedule.html';
$url662='https://www.dubaione.ae/content/dubaione/en-ae/schedule.2.html';
    // 这里添加情况1的代码逻辑
}
}


$headers661=[
'Host: www.dubaione.ae',
'Connection: keep-alive',

'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0',

'Referer: https://www.dubaione.ae/content/dubaione/en-ae/schedule.html',

'Cookie: cookieWarn.accepted=true; twtr_pixel_opt_in=N; eu_cn=1',

];



//$url661='https://www.dubaione.ae/content/dubaione/en-ae/schedule.'.$we.'.html';
//print $url661;
 $ch661=curl_init();
curl_setopt($ch661,CURLOPT_URL,$url661);
curl_setopt($ch661,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch661,CURLOPT_SSL_VERIFYHOST,false);
curl_setopt($ch661,CURLOPT_RETURNTRANSFER,1);


curl_setopt($ch661, CURLOPT_HTTPHEADER, $headers661);

$re661=curl_exec($ch661);
$re661=str_replace('&','&amp;',$re661);
curl_close($ch661);
$re661 = preg_replace('/\s(?=)/', '',$re661);
$re661= preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re661);// 適合php7
//print $re661;
//$re661 = compress_html($re661);

preg_match('/SCHEDULELISTSTART(.*)SCHEDULELISTEND/i',$re661,$yuu661);
//print $yuu661[1];




preg_match_all('|<ulclass="th-postmatadata"><li>(.*?)</li>|i',$yuu661[1],$un661,PREG_SET_ORDER);//播放時間
preg_match_all('|<h5>(.*?)</h5>|i',$yuu661[1],$ul661,PREG_SET_ORDER);//播放節目介紹
//print_r($un661); 
// print_r($ul661); 

$chn.="<channel id=\"dubai one\"><display-name lang=\"zh\">dubai one</display-name></channel>\n";


$trm661=sizeof($ul661);
for ($k661 = 0; $k661 < $trm661-2; $k661++) { 

    $chn.="<programme start=\"".$dt1.str_replace(':','',substr("".$un661[$k661][1]."",-5,5)).'00 +0400'."\" stop=\"".$dt1.str_replace(':','',substr("".$un661[$k661+1][1]."",-5,5)).'00 +0400'."\" channel=\"dubai one\">\n<title lang=\"zh\">".$ul661[$k661][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
                 }


    $chn.="<programme start=\"".$dt1.str_replace(':','',substr("".$un661[$trm661-1][1]."",-5,5)).'00 +0400'."\" stop=\"".$dt1.'235900 +0400'."\" channel=\"dubai one\">\n<title lang=\"zh\">".$ul661[$trm661-1][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";


 $ch662=curl_init();
curl_setopt($ch662,CURLOPT_URL,$url662);
curl_setopt($ch662,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch662,CURLOPT_SSL_VERIFYHOST,false);
curl_setopt($ch662,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch662, CURLOPT_HTTPHEADER, $headers661);
$re662=curl_exec($ch662);
$re662=str_replace('&','&amp;',$re662);
curl_close($ch661);
$re662 = preg_replace('/\s(?=)/', '',$re662);
$re662= preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $re662);// 適合php7
//print $re662;
//$re661 = compress_html($re661);


preg_match('/SCHEDULELISTSTART(.*)SCHEDULELISTEND/i',$re662,$yuu662);
//print $yuu661[1];





preg_match_all('|<ulclass="th-postmatadata"><li>(.*?)</li>|i',$yuu662[1],$un662,PREG_SET_ORDER);//播放時間
preg_match_all('|<h5>(.*?)</h5>|i',$yuu662[1],$ul662,PREG_SET_ORDER);//播放節目介紹
//print_r($un661); 
// print_r($ul661); 


$trm662=sizeof($ul662);
for ($k662 = 0; $k662 < $trm662-2; $k662++) { 

    $chn.="<programme start=\"".$dt2.str_replace(':','',substr("".$un662[$k662][1]."",-5,5)).'00 +0400'."\" stop=\"".$dt2.str_replace(':','',substr("".$un662[$k662+1][1]."",-5,5)).'00 +0400'."\" channel=\"dubai one\">\n<title lang=\"zh\">".$ul662[$k662][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";
                 }

    $chn.="<programme start=\"".$dt2.str_replace(':','',substr("".$un662[$trm662-1][1]."",-5,5)).'00 +0400'."\" stop=\"".$dt2.'235900 +0400'."\" channel=\"dubai one\">\n<title lang=\"zh\">".$ul662[$trm662-1][1]."</title>\n<desc lang=\"zh\"> </desc>\n</programme>\n";



*/
 $chn.="</tv>\n";
//print  $chn;

file_put_contents($fp, $chn);

?>

?>
