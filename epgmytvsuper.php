<?php
/**
 * 秋哥綜合 EPG 生成器 - 完整優化版
 * 
 * 功能：
 * 1. 從多個來源收集電視節目表數據
 * 2. 生成 XMLTV 格式的 EPG 文件
 * 3. 支持兩天的節目數據
 * 4. 自動處理時區轉換
 * 5. 錯誤處理和日誌記錄
 */

// ==================== 配置 ====================
header('Content-Type: text/plain; charset=UTF-8');
ini_set('max_execution_time', 300);
ini_set('date.timezone', 'Asia/Shanghai');
error_reporting(E_ALL & ~E_NOTICE);

define('OUTPUT_FILE', 'epgmytvsuper.xml');
define('VERSION', '2.0');
define('TIMEZONE', '+0800');
define('USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

// ==================== 核心類 ====================
class EPGGenerator {
    private $xml = '';
    private $dates = [];
    private $log = [];
    private $startTime;
    
    public function __construct() {
        $this->startTime = microtime(true);
        $this->initDates();
        $this->initXml();
        $this->log('EPG 生成器初始化完成');
    }
    
    private function initDates() {
        $now = time();
        $this->dates = [
            'dt1'  => date('Ymd', $now),           // 今天 YYYYMMDD
            'dt2'  => date('Ymd', $now + 86400),   // 明天 YYYYMMDD
            'dt11' => date('Y-m-d', $now),         // 今天 YYYY-MM-DD
            'dt12' => date('Y-m-d', $now + 86400), // 明天 YYYY-MM-DD
            'dt10' => date('Y-m-d', $now - 86400), // 昨天 YYYY-MM-DD
            'dt22' => date('Ymd', $now - 86400),   // 昨天 YYYYMMDD
            'dt21' => date('Ymd', $now + 172800),  // 後天 YYYYMMDD
        ];
    }
    
    private function initXml() {
        $this->xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $this->xml .= '<!DOCTYPE tv SYSTEM "http://api.torrent-tv.ru/xmltv.dtd">' . "\n";
        $this->xml .= '<tv generator-info-name="秋哥綜合" generator-info-url="https://www.tdm.com.mo/c_tv/?ch=Satellite">' . "\n";
    }
    
    // ==================== 工具方法 ====================
    private function log($message, $type = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[$timestamp] [$type] $message";
        $this->log[] = $entry;
        echo "$entry\n";
    }
    
    private function httpRequest($url, $options = []) {
        $defaults = [
            'headers' => [],
            'post' => false,
            'data' => null,
            'timeout' => 30,
            'encoding' => 'gzip, deflate',
            'retry' => 2,
        ];
        $options = array_merge($defaults, $options);
        
        for ($i = 0; $i <= $options['retry']; $i++) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $options['timeout'],
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_USERAGENT => USER_AGENT,
                CURLOPT_ENCODING => $options['encoding'],
            ]);
            
            if ($options['post']) {
                curl_setopt($ch, CURLOPT_POST, true);
                if ($options['data']) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $options['data']);
                }
            }
            
            if (!empty($options['headers'])) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $options['headers']);
            }
            
            $response = curl_exec($ch);
            $error = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($response && $httpCode == 200) {
                return $response;
            }
            
            if ($i < $options['retry']) {
                sleep(1);
                $this->log("請求失敗，重試中 ($i/2): $url", 'WARNING');
            }
        }
        
        $this->log("HTTP 請求失敗: $url (HTTP $httpCode: $error)", 'ERROR');
        return false;
    }
    
    private function cleanText($text) {
        if (!is_string($text)) return '';
        $text = htmlspecialchars($text, ENT_XML1, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }
    
    private function compressHtml($html) {
        return preg_replace('/\s+/', ' ', $html);
    }
    
    private function unicodeDecode($match) {
        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
    }
    
    private function addChannel($id, $name) {
        $id = $this->cleanText($id);
        $name = $this->cleanText($name);
        $this->xml .= "<channel id=\"$id\"><display-name lang=\"zh\">$name</display-name></channel>\n";
    }
    
    private function addProgram($channel, $title, $start, $stop, $desc = '') {
        $channel = $this->cleanText($channel);
        $title = $this->cleanText($title);
        $desc = $this->cleanText($desc);
        
        $this->xml .= "<programme start=\"$start\" stop=\"$stop\" channel=\"$channel\">\n";
        $this->xml .= "<title lang=\"zh\">$title</title>\n";
        $this->xml .= "<desc lang=\"zh\">$desc</desc>\n";
        $this->xml .= "</programme>\n";
    }
    
    // ==================== 數據源處理 ====================
    
    /**
     * 處理 TVB 頻道
     */
    private function processTVB() {
        $this->log('開始處理 TVB 頻道...');
        
        $channels = [
            ['195f7f1d-0eca-44f8-b277-15a2728dd102', 'TVB翡翠娛樂臺(TVBe)'],
            ['1d1ccf5f-952c-4563-b0f4-f1098eca3dd6', '翡翠一臺(TVB1)'],
            ['0a18ad87-8be1-4adf-ae3b-273c42ac9cec', 'TVB無綫新聞臺(TVB News)'],
            ['34f986d8-a74c-4cfa-8b0e-6643466d9463', 'TVB翡翠劇集臺(TVB Drama)'],
            ['e10f2a7f-ecf7-4c17-be6e-3fc59ef5da4e', 'TVB翡翠綜合臺(TVBJ1)'],
            ['be6d5f27-c9e4-4ff9-bdc4-14a42bbe86c6', 'TVB明珠劇集臺(TVB Pearl Drama)'],
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1], $channel[1]);
            
            $url = "https://tvbsvodassets.tv2zcdn2.com/epgv2/{$channel[0]}_{$this->dates['dt11']}.json";
            $data = $this->httpRequest($url);
            
            if (!$data) continue;
            
            $data = str_replace('&', '&amp;', $data);
            preg_match('/"programs":(.*?)"channel/i', $data, $matches);
            
            if (isset($matches[1])) {
                preg_match_all('/"start_date_utc": "(.*?)", /i', $matches[1], $startTimes);
                preg_match_all('/"end_date_utc": "(.*?)",/i', $matches[1], $endTimes);
                preg_match_all('/"title": "(.*?)",/i', $matches[1], $titles);
                
                $count = count($titles[1]);
                for ($i = 0; $i < $count; $i++) {
                    $start = str_replace([' ', '-', ':'], '', $startTimes[1][$i]) . ' +0000';
                    $stop = str_replace([' ', '-', ':'], '', $endTimes[1][$i]) . ' +0000';
                    $this->addProgram($channel[1], $titles[1][$i], $start, $stop);
                }
            }
        }
        
        $this->log('TVB 頻道處理完成');
    }
    
    /**
     * 處理 TBC 有線電視頻道
     */
    private function processTBC() {
        $this->log('開始處理 TBC 有線電視頻道...');
        
        $channels = [
            ['005','民視'], ['006','CNN'], ['914','桃園生活臺'],
            ['007','台視'], ['010','大愛電視台'], ['009','中視'],
            ['012','人間衛視'], ['011','華視'], ['013','公共電視'],
            ['014','公視台語台'], ['015','好消息頻道'], ['016','原住民族頻道'],
            ['017','客家電視台'], ['018','BBC EARTH'], ['019','Discovery'],
            ['021','TLC 旅遊生活頻道'], ['022','動物星球頻道'], ['008','Cartoon Network'],
            ['023','Nick Jr.(小尼克)'], ['024','MOMO親子台'], ['025','東森幼幼台'],
            ['026','緯來綜合台'], ['027','八大第一台'], ['028','八大綜合台'],
            ['029','三立台灣台'], ['030','三立都會台'], ['031','華藝中文台'],
            ['032','東森綜合台'], ['033','東森超視'], ['034','東森購物2台'],
            ['035','momo2台'], ['036','中天綜合台'], ['037','東風衛視'],
            ['038','年代MUCH TV'], ['039','中天娛樂台'], ['040','東森戲劇台'],
            ['041','八大戲劇台'], ['042','TVBS歡樂台'], ['043','緯來戲劇台'],
            ['044','高點電視台'], ['045','JET綜合台'], ['046','東森購物3台'],
            ['047','東森購物1台'], ['048','MOMO1臺'], ['049','壹電視新聞台'],
            ['050','年代新聞'], ['051','東森新聞台'], ['053','民視新聞台'],
            ['054','三立新聞台'], ['055','TVBS 新聞台'], ['056','TVBS'],
            ['057','東森財經新聞台'], ['058','非凡新聞台'], ['059','ViVa 1台'],
            ['060','東森購物5台'], ['061','CATCH PLAY電影台'], ['062','東森電影台'],
            ['063','緯來電影台'], ['064','LS TIME電影台'], ['065','HBO'],
            ['066','東森洋片台'], ['067','AXN'], ['068','好萊塢電影台'],
            ['069','AMC電影'], ['070','緯來育樂台'], ['071','CINEMAX有線'],
            ['072','緯來體育台'], ['073','DAZN 1'], ['074','DAZN 2'],
            ['075','MOMO綜合台'], ['077','緯來日本台'], ['078','國興衛視'],
            ['079','BBC LIFESTYLE'], ['081','靖天資訊台'], ['082','信吉電視台'],
            ['083','信大電視台'], ['084','中台灣生活網頻道'], ['085','TBC台中生活台'],
            ['086','鏡電視新聞台'], ['087','台灣藝術台'], ['088','樂視台'],
            ['089','非凡商業台'], ['090','三立財經新聞台'], ['091','冠軍電視台'],
            ['092','運通財經綜合台'], ['093','全球財經網頻道'], ['094','誠心電視台'],
            ['095','NHK'], ['096','MTV'], ['097','Animax'],
            ['098','霹靂台灣台'], ['099','海豚綜合台'], ['100','八大娛樂台'],
            ['101','十方法界電視台'], ['102','壹電視電影台'], ['103','華藏衛視'],
            ['104','壹電視資訊台'], ['105','佛衛電視慈悲台'], ['106','紅豆電視台'],
            ['107','全大電視台'], ['108','華藝台灣台'], ['109','正德電視台'],
            ['110','天良綜合台'], ['111','番薯衛星電視台'], ['112','富立電視台'],
            ['113','Z頻道'], ['114','冠軍夢想台'], ['115','新天地民俗台'],
            ['116','三聖電視台'], ['117','威達超舜生活台'], ['118','天美麗電視台'],
            ['119','大立電視台'], ['120','雙子衛視'], ['121','小公視'],
            ['122','華視教育體育文化台'], ['123','國會頻道1台'], ['124','國會頻道2台'],
            ['125','幸福空間居家台'], ['126','高點育樂台'], ['127','台灣綜合台'],
            ['128','大台中生活頻道台'], ['129','彰化生活台'], ['130','唯心電視台'],
            ['131','美麗人生購物台'], ['132','大愛二台'], ['133','靖天映畫'],
            ['134','靖洋戲劇台'], ['135','ROCK ACTION'], ['136','Global Trekker'],
            ['137','靖天綜合台'], ['138','寶島文化'], ['139','靖天日本台靖天'],
            ['149','TaiWan Plus'], ['150','BBC News'], ['151','民視第一台'],
            ['152','民視台灣台'], ['154','中視新聞台'], ['155','台視新聞台'],
            ['156','台視財經台'], ['157','台視綜合台'], ['162','Bloomberg Television'],
            ['162','TV5MONDE'], ['163','Channel News Asia'], ['164','韓國阿里郎'],
            ['202','DREAMWORKS'], ['203','Wa暖呢人TV'], ['204','靖天電影台'],
            ['205','Cinemalworld'], ['207','HBO HD'], ['208','HBO Signature 原創鉅獻'],
            ['209','HBO Hits 強檔鉅獻'], ['210','HBO Family'], ['212','靖天日本台'],
            ['213','EVE有線'], ['214','tvN有線'], ['215','靖天歡樂台'],
            ['216','HITS'], ['217','韓國娛樂台'], ['218','博靖天育樂台'],
            ['219','Lifeime'], ['220','罪案偵查頻道'], ['221','寵物頻道'],
            ['222','History 歷史頻道有線'], ['223','Discovery Asia'],
            ['224','Discovery 科學頻道'], ['225','DMAX有線'], ['227','環宇新聞台灣台'],
            ['229','亞洲旅遊台'], ['230','梅迪奇藝術台'], ['240','博斯運動二台有線'],
            ['241','博斯網球台有線'], ['242','博斯運動二台有線'], ['243','博斯高球一台有線'],
            ['244','博斯高球二台有線'], ['245','博斯魅力網有線'], ['246','博斯運動一台有線'],
            ['247','博斯無限二台有線'], ['249','Eurosnews'], ['250','Nickelodeon Asia尼克兒童頻道'],
            ['252','達文西頻道'], ['253','Cbeebies'], ['254','CARTOONITO'],
            ['257','靖洋卡通台'], ['258','靖天卡通'], ['260','LOVE NATURE 4K'],
            ['261','ROCK ENTERTAINMENT'], ['262','ROCK EXTREME'],
        ];
        
        $headers = [
            'Host: www.tbc.net.tw',
            'Connection: keep-alive',
            'User-Agent: ' . USER_AGENT,
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1], $channel[1]);
            
            $url = "https://www.tbc.net.tw/EPG/Epg/ChannelV2?channelId={$channel[0]}";
            $data = $this->httpRequest($url, ['headers' => $headers]);
            
            if (!$data) continue;
            
            $data = $this->compressHtml($data);
            $data = str_replace(['/', ':'], '', $data);
            $data = str_replace(['" time="', '~'], ['', ''], $data);
            
            preg_match('/ \<div class\="epg_con srl"> (.*)\<ul class\="list_program2" channelname="/i', $data, $matches);
            
            if (isset($matches[1])) {
                preg_match_all('/event" date\="(.*?)" desc/i', $matches[1], $dateMatches);
                preg_match_all('/desc="(.*?)"/i', $matches[1], $descMatches);
                preg_match_all('/title="(.*?)"\>/i', $matches[1], $titleMatches);
                
                $count = count($dateMatches[1]);
                for ($i = 0; $i < $count; $i++) {
                    if (!isset($dateMatches[1][$i])) continue;
                    
                    $dateStr = $dateMatches[1][$i];
                    $datePart = substr($dateStr, 0, 8);
                    $nextDate = $datePart + 1;
                    
                    $startTime = substr($dateStr, 8, 4);
                    $endTime = substr($dateStr, 12, 4);
                    
                    if ($datePart == $this->dates['dt1'] || $datePart == $this->dates['dt2'] || $datePart == $this->dates['dt22']) {
                        if ($startTime > $endTime) {
                            $start = $datePart . $startTime . '00 +0800';
                            $stop = $nextDate . $endTime . '00 +0800';
                        } else {
                            $start = $datePart . $startTime . '00 +0800';
                            $stop = $datePart . $endTime . '00 +0800';
                        }
                        
                        $title = isset($titleMatches[1][$i]) ? $titleMatches[1][$i] : '';
                        $desc = isset($descMatches[1][$i]) ? $descMatches[1][$i] : '';
                        $this->addProgram($channel[1], $title, $start, $stop, $desc);
                    }
                }
            }
        }
        
        $this->log('TBC 有線電視頻道處理完成');
    }
    
    /**
     * 處理 TBC 成人頻道
     */
    private function processTBCAdult() {
        $this->log('開始處理 TBC 成人頻道...');
        
        $channels = [
            ['404','彩虹頻道'], ['405','彩虹E頻道'], ['406','彩虹電影'],
            ['407','K頻道'], ['408','松視1頻道'], ['409','松視2頻道'],
            ['410','松視3頻道'], ['411','松視4頻道'], ['412','潘多拉完美台'],
            ['413','潘多拉粉紅台'], ['414','極限電源'], ['415','驚艷成人'],
            ['416','香蕉台'], ['417','樂活頻道'], ['418','玩家頻道'],
            ['419','HAPPY'], ['420','HOT'],
        ];
        
        $headers = [
            'Host: www.tbc.net.tw',
            'Connection: keep-alive',
            'User-Agent: ' . USER_AGENT,
            'Referer: https://www.tbc.net.tw/Epg/Epg/indexV2/0/1',
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1], $channel[1]);
            
            $url = "https://www.tbc.net.tw/EPG/Epg/ChannelV2?channelId={$channel[0]}";
            $data = $this->httpRequest($url, ['headers' => $headers]);
            
            if (!$data) continue;
            
            $data = $this->compressHtml($data);
            preg_match('/ \<div class\="epg_con srl"> (.*)\<ul class\="list_program2" channelname="/i', $data, $matches);
            
            if (isset($matches[1])) {
                preg_match_all('/<li class="[^"]*event[^"]*" channelid="\d+" date="(.*?)" time="(.*?)" desc="(.*?)"[^>]*data\.name="(.*?)"/i', $matches[1], $programs, PREG_SET_ORDER);
                
                foreach ($programs as $program) {
                    $dateStr = $program[1];
                    $timeStr = $program[2];
                    $desc = $program[3];
                    $title = $program[4];
                    
                    $datePart = str_replace('/', '', $dateStr);
                    list($startTime, $endTime) = explode('~', $timeStr);
                    $startTime = str_replace(':', '', $startTime);
                    $endTime = str_replace(':', '', $endTime);
                    
                    if ($datePart == $this->dates['dt1'] || $datePart == $this->dates['dt2']) {
                        if ($endTime > $startTime) {
                            $start = $datePart . $startTime . '00 +0800';
                            $stop = $datePart . $endTime . '00 +0800';
                        } else {
                            $nextDate = date('Ymd', strtotime($dateStr . ' +1 day'));
                            $start = $datePart . $startTime . '00 +0800';
                            $stop = $nextDate . $endTime . '00 +0800';
                        }
                        
                        $this->addProgram($channel[1], $title, $start, $stop, $desc);
                    }
                }
            }
        }
        
        $this->log('TBC 成人頻道處理完成');
    }
    
    /**
     * 處理天映頻道
     */
    private function processCelestial() {
        $this->log('開始處理天映頻道...');
        
        $channels = [
            ['cmclassic','tv','天映经典香港'],
            ['celestialmovies','com','天映频道马来西亚'],
            ['cmplus-tv','com','cmplus新加坡'],
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[2], $channel[2]);
            
            // 今天節目
            $url = "https://www.{$channel[0]}.{$channel[1]}/schedule.php?lang=tc&date/{$this->dates['dt11']}";
            $data = $this->httpRequest($url);
            
            if ($data) {
                $this->processCelestialData($channel[2], $data, $this->dates['dt1']);
            }
            
            // 明天節目
            $url = "https://www.{$channel[0]}.{$channel[1]}/schedule.php?lang=tc&date/{$this->dates['dt12']}";
            $data = $this->httpRequest($url);
            
            if ($data) {
                $this->processCelestialData($channel[2], $data, $this->dates['dt2']);
            }
        }
        
        $this->log('天映頻道處理完成');
    }
    
    private function processCelestialData($channel, $data, $date) {
        $data = str_replace('&', '&amp;', $data);
        $data = str_replace('<ul>', '', $data);
        $data = preg_replace('/\s+/', '', $data);
        
        preg_match_all('|<pclass="programme-title">(.*?)</p>|i', $data, $titles);
        preg_match_all('/schedule-time">(.*?)<\/div>/i', $data, $times);
        
        $count = count($titles[1]);
        for ($i = 1; $i <= $count - 1; $i++) {
            $startTime = date("Hi", strtotime($times[1][$i-1]));
            $endTime = date("Hi", strtotime($times[1][$i]));
            
            $title = str_replace('<spanclass="live-btn">播放中</span>', '', $titles[1][$i-1]);
            
            $start = $date . $startTime . '00 +0800';
            $stop = $date . $endTime . '00 +0800';
            
            $this->addProgram($channel, $title, $start, $stop);
        }
    }
    
    /**
     * 處理 myTV SUPER 頻道
     */
    private function processMyTVSuper() {
        $this->log('開始處理 myTV SUPER 頻道...');
        
        // 獲取頻道列表
        $url = 'https://content-api.mytvsuper.com/v1/channel/list?platform=web&country_code=TW&profile_class=general';
        $data = $this->httpRequest($url);
        
        if (!$data) {
            $this->log('無法獲取 myTV SUPER 頻道列表', 'ERROR');
            return;
        }
        
        $data = str_replace('&', '&amp;', $data);
        $channels = json_decode($data);
        
        if (!$channels || !isset($channels->channels)) {
            $this->log('myTV SUPER 頻道數據格式錯誤', 'ERROR');
            return;
        }
        
        foreach ($channels->channels as $channel) {
            $this->addChannel($channel->name_tc, $channel->name_tc);
            
            // 處理昨天、今天、明天的節目
            $dateKeys = ['dt22', 'dt1', 'dt2'];
            foreach ($dateKeys as $dateKey) {
                $date = $this->dates[$dateKey];
                $url = "https://content-api.mytvsuper.com/v1/epg?platform=web&country_code=TW&network_code={$channel->network_code}&from={$date}&to={$date}";
                $epgData = $this->httpRequest($url);
                
                if ($epgData) {
                    $this->processMyTVSuperData($channel->name_tc, $epgData);
                }
            }
        }
        
        $this->log('myTV SUPER 頻道處理完成');
    }
    
    private function processMyTVSuperData($channel, $data) {
        $data = $this->compressHtml($data);
        $data = str_replace('&', '&amp;', $data);
        $data = str_replace(['>', '<', '/', '.', '[', ']'], '', $data);
        
        preg_match_all('/"start_datetime":"(.*?)",/i', $data, $startTimes);
        preg_match_all('/programme_title_tc":"(.*?)",/i', $data, $titles);
        preg_match_all('/"episode_synopsis_tc":"(.*?)",/i', $data, $descs);
        
        $count = count($startTimes[1]);
        for ($i = 1; $i <= $count - 1; $i++) {
            $start = str_replace([' ', ':', '-'], '', $startTimes[1][$i-1]) . ' +0800';
            $stop = str_replace([' ', ':', '-'], '', $startTimes[1][$i]) . ' +0800';
            $title = isset($titles[1][$i-1]) ? trim($titles[1][$i-1]) : '';
            $desc = isset($descs[1][$i-1]) ? trim($descs[1][$i-1]) : '';
            
            $this->addProgram($channel, $title, $start, $stop, $desc);
        }
    }
    
    /**
     * 處理中天亞洲台
     */
    private function processCTIAsia() {
        $this->log('開始處理中天亞洲台...');
        
        $this->addChannel('中天亞洲台', '中天亞洲台');
        
        // 獲取今天節目
        $url = "https://asia-east1-ctitv-237901.cloudfunctions.net/ProgramList-Api2??chid=a2&start={$this->dates['dt1']}&end={$this->dates['dt1']}&_=";
        $data = $this->httpRequest($url);
        
        if ($data) {
            $this->processCTIData($data);
        }
        
        // 獲取明天節目
        $url = "https://asia-east1-ctitv-237901.cloudfunctions.net/ProgramList-Api2?chid=a2&start={$this->dates['dt1']}&end={$this->dates['dt2']}&_=";
        $data = $this->httpRequest($url);
        
        if ($data) {
            $this->processCTIData($data);
        }
        
        $this->log('中天亞洲台處理完成');
    }
    
    private function processCTIData($data) {
        $data = str_replace(['T', 'Z', '&'], ['', '', '&amp;'], $data);
        $programs = json_decode($data);
        
        if ($programs && is_array($programs)) {
            foreach ($programs as $program) {
                if (isset($program->start) && isset($program->end) && isset($program->title)) {
                    $start = str_replace([' ', ':', '-'], '', $program->start) . ' +0800';
                    $stop = str_replace([' ', ':', '-'], '', $program->end) . ' +0800';
                    $this->addProgram('中天亞洲台', $program->title, $start, $stop);
                }
            }
        }
    }
    
    /**
     * 處理龍華頻道
     */
    private function processLonghua() {
        $this->log('開始處理龍華頻道...');
        
        $channels = ['龍華卡通台', '龍華日韓台', '龍華偶像台OTT'];
        foreach ($channels as $channel) {
            $this->addChannel($channel, $channel);
        }
        
        $url = 'https://www.ltv.com.tw/wp-admin/admin-ajax.php';
        $headers = [
            'Host: www.ltv.com.tw',
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With: XMLHttpRequest',
            'Origin: https://www.ltv.com.tw',
            'Referer: https://www.ltv.com.tw/ott%e7%af%80%e7%9b%ae%e8%a1%a8/'
        ];
        
        // 處理今天節目
        $data = [
            'action' => 'timetable',
            'type' => 51,
            'play_date' => $this->dates['dt11']
        ];
        
        $response = $this->httpRequest($url, [
            'post' => true,
            'data' => http_build_query($data),
            'headers' => $headers
        ]);
        
        if ($response) {
            $this->processLonghuaData($response, $this->dates['dt1']);
        }
        
        // 處理明天節目
        $data['play_date'] = $this->dates['dt12'];
        $response = $this->httpRequest($url, [
            'post' => true,
            'data' => http_build_query($data),
            'headers' => $headers
        ]);
        
        if ($response) {
            $this->processLonghuaData($response, $this->dates['dt2']);
        }
        
        $this->log('龍華頻道處理完成');
    }
    
    private function processLonghuaData($data, $date) {
        $data = str_replace('&', '&amp;', $data);
        $data = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', [$this, 'unicodeDecode'], $data);
        $data = preg_replace('/\s+/', '', $data);
        
        // 卡通頻道
        if (preg_match('/class="timetable-column-header">卡通(.*)class="timetable-column-header">日韓/i', $data, $matches)) {
            $this->parseLonghuaSection($matches[1], '龍華卡通台', $date);
        }
        
        // 日韓頻道
        if (preg_match('/class="timetable-column-header">日韓(.*)class="timetable-column-header">知識/i', $data, $matches)) {
            $this->parseLonghuaSection($matches[1], '龍華日韓台', $date);
        }
        
        // 偶像頻道
        if (preg_match('/class="timetable-column-header">偶像(.*)class="timetable-column-header">電影/i', $data, $matches)) {
            $this->parseLonghuaSection($matches[1], '龍華偶像台OTT', $date);
        }
    }
    
    private function parseLonghuaSection($data, $channel, $date) {
        preg_match_all('|<divclass="timetable-name">(.*?)</div>|i', $data, $titles);
        preg_match_all('|<divclass="timetable-desc">(.*?)<br>|i', $data, $descs);
        preg_match_all('|<divclass="timetable-time">(.*?)</div>|i', $data, $times);
        
        $count = count($titles[1]);
        for ($i = 2; $i < $count - 1; $i++) {
            $timeStr = isset($times[1][($i-1)*2]) ? $times[1][($i-1)*2] : '';
            if (empty($timeStr)) continue;
            
            $timesArray = explode(' ', $timeStr);
            if (count($timesArray) < 2) continue;
            
            list($startTime, $endTime) = $timesArray;
            
            $start = $date . str_replace(':', '', $startTime) . '00 +0800';
            $stop = $date . str_replace(':', '', $endTime) . '00 +0800';
            $title = isset($titles[1][$i-1]) ? $titles[1][$i-1] : '';
            $desc = isset($descs[1][$i-1]) ? $descs[1][$i-1] : '';
            
            $this->addProgram($channel, $title, $start, $stop, $desc);
        }
    }
    
    /**
     * 處理 NOW Player 頻道
     */
    private function processNowPlayer() {
        $this->log('開始處理 NOW Player 頻道...');
        
        $channels = [
            ['096','viu6'], ['099','ViuTV'], ['102','Viu 頻道'],
            ['105','now 劇集'], ['106','video express rentnow'], ['108','nowjeli'],
            ['111','HBO Hits香港'], ['112','HBO Family香港'], ['113','CINEMAX香港'],
            ['114','HBO Signature香港'], ['115','HBO香港'], ['116','MOVIE MOVIE'],
            ['133','爆谷台'], ['138','Now爆谷星影台'], ['150','Animax香港'],
            ['155','tvN香港'], ['156','KBS World香港'], ['162','東森亞洲'],
            ['168','moov'], ['200','Panda TV'], ['208','Discovery Asia香港'],
            ['209','Discovery Channel香港'], ['210','動物星球頻道香港'],
            ['211','Discovery 科學頻道香港'], ['212','DMAX香港'],
            ['213','TLC旅遊生活頻道香港'], ['217','Love Nature香港'],
            ['220','BBC Earth香港'], ['221','戶外頻道香港'],
            ['222','罪案 + 偵緝香港'], ['223','HISTORY香港'],
            ['316','CNN 國際新聞網絡香港'], ['319','CNBC香港'],
            ['320','BBC News香港'], ['321','Bloomberg Television香港'],
            ['322','亞洲新聞台香港'], ['3231','Sky News香港'],
            ['324','DW (English)香港'], ['325','半島電視台英語頻道香港'],
            ['326','euronews香港'], ['327','France 24香港'],
            ['328','NHK WORLD-JAPA香港'], ['329','RT香港'],
            ['330','中國環球電視網香港'], ['331','now直播 '],
            ['332','now新聞'], ['333','now財經'], ['336','now報價'],
            ['366','鳳凰資訊'], ['367','鳳凰香港台'], ['400','智叻樂園'],
            ['548','鳳凰中文'], ['368','香港衛視'], ['371','東森亞洲新聞'],
            ['440','DreamWorks 頻道香港'], ['443','Cartoon Network香港'],
            ['444','Nickelodeon香港'], ['447','CBeebies香港'],
            ['448','Moonbug香港'], ['449','Nick Jr.香港'],
            ['460','Da Vinc香港'], ['502','BBC Lifestyle香港'],
            ['512','AXN香港'], ['517','ROCK Entertainment香港'],
            ['525','Lifetime香港'], ['526','Food Network香港'],
            ['527','亞洲美食台香港'], ['528','旅遊頻道香港'],
            ['529','居家樂活頻道香港'], ['535','Netflix香港'],
            ['540','深圳衛視香港'], ['541','CCTV-1香港'],
            ['542','CCTV-4香港'], ['543','大灣區衛視香港'],
            ['545','中央電視台新聞頻道香港'], ['548','鳳凰衛視中文台'],
            ['552','OneTV 綜合頻道'], ['553','三沙衛視香港'],
            ['555','浙江衛視香港'], ['561','ABC Australia香港'],
            ['600','now體育'], ['611','now體育4k'], ['612','now體育4k'],
            ['613','now體育4k'], ['620','Now Sports 英超TV'],
            ['621','Now Sports 英超TV1'], ['622','Now Sports 英超 TV2'],
            ['623','Now Sports 英超 TV3'], ['624','Now Sports 英超 TV4'],
            ['625','Now Sports 英超 TV5'], ['626','Now Sports 英超 TV6'],
            ['627','Now Sports 英超 TV7'], ['630','Now Sports Premier'],
            ['631','Now Sports 1'], ['632','Now Sports 2'],
            ['633','Now Sports 3'], ['634','Now Sports 4'],
            ['635','Now Sports 5'], ['636','Now Sports 6'],
            ['637','Now Sports 7'], ['638','beIN SPORTS 1'],
            ['639','beIN SPORTS 2'], ['640','MUTV'],
            ['641','Now Sports 641'], ['642','NBA'],
            ['643','beIN SPORTS 3'], ['644','beIN SPORTS 4'],
            ['645','beIN SPORTS 5'], ['646','beIN SPORTS 6'],
            ['650','beIN SPORTS RUGBY'], ['651','Now Sports 651'],
            ['652','Now Sports 652'], ['668','Now Sports 668'],
            ['670','SPOTV'], ['671','SPOTV2'], ['674','Astro Cricket'],
            ['679','Premier Sports'], ['680','Now Sports plus'],
            ['681','Now Sports 681'], ['683','Now 高爾夫2'],
            ['684','Now 高爾夫3'], ['688','Lucky 688'],
            ['711','NHK World Premium'], ['713','TV5MONDE Style'],
            ['714','TV5MONDE ASIE'], ['715','France 24 (French)'],
            ['720','GMA Pinoy TV'], ['721','GMA Life T'],
            ['725','TFC'], ['771','Sony TV (India)'],
            ['772','Sony MAX'], ['774','Sony SAB'],
            ['779','MTV India'], ['780','COLORS'],
            ['781','Zee Cinema International'], ['782','Zee TV'],
            ['785','Zee News'], ['793','Star Gold'],
            ['794','STAR PLUS'], ['797','Star Bharat'],
            ['900','成人節目資訊'], ['901','冰火頻道'],
            ['903','成人極品台'],
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1], $channel[1]);
            
            $url = "https://nowplayer.now.com/tvguide/channeldetail/{$channel[0]}/1?isfromchannel=false";
            $data = $this->httpRequest($url);
            
            if ($data) {
                $this->processNowPlayerData($channel[1], $data);
            }
        }
        
        $this->log('NOW Player 頻道處理完成');
    }
    
    private function processNowPlayerData($channel, $data) {
        $data = preg_replace('/\s+/', ' ', $data);
        $data = str_replace('&', '&amp;', $data);
        
        // 今天節目
        if (preg_match('/id="day1"(.*?)id="day2"/i', $data, $matches)) {
            $this->parseNowPlayerDay($channel, $matches[1], $this->dates['dt1']);
        }
        
        // 明天節目
        if (preg_match('/id="day2"(.*?)id="day3"/i', $data, $matches)) {
            $this->parseNowPlayerDay($channel, $matches[1], $this->dates['dt2']);
        }
    }
    
    private function parseNowPlayerDay($channel, $data, $date) {
        preg_match_all('/<div class="time">(.*?)<\/div>/i', $data, $times);
        preg_match_all('/<div class="prograam-name">(.*?)<\/div>/i', $data, $titles);
        
        $count = count($times[1]);
        for ($i = 1; $i <= $count - 1; $i++) {
            $startTime = date("Hi", strtotime($times[1][$i-1]));
            $endTime = date("Hi", strtotime($times[1][$i]));
            
            $start = $date . $startTime . '00 +0800';
            $stop = $date . $endTime . '00 +0800';
            $title = str_replace('<span class="live-btn">播放中</span>', '', $titles[1][$i-1]);
            
            $this->addProgram($channel, $title, $start, $stop);
        }
    }
    
    /**
     * 處理 meWatch 頻道（新加坡）
     */
    private function processMeWatch() {
        $this->log('開始處理 meWatch 頻道...');
        
        $channels = [
            ['97098','Channel 5'], ['97104','Channel 8'], ['97129','Channel U'],
            ['97084','Channel Suria'], ['97096','Channel Vasantham'], ['97072','CNA'],
            ['186574','oktolidays'], ['576059','SEA Games CH01'], ['576060','SEA Games CH02'],
            ['576061','SEA Games CH03'], ['580750','SEA Games CH04'], ['580751','SEA Games CH05'],
            ['580752','SEA Games CH06'], ['98200','SPL-CH01'], ['97073','meWATCH LIVE 1'],
            ['97078','meWATCH LIVE 2'], ['98202','meWATCH LIVE 5'], ['558241','River Monsters'],
            ['558273','FoodON'], ['557763','FIFA+'], ['558112',' W-Sport'],
            ['556888','TRACE Sport Stars'], ['556894','Action Hollywood Movies'],
            ['556893','Kartoon Channel!'], ['556877','TG Junior'], ['158965','NOW 80s'],
            ['158964','NOW 70s'], ['158963','NOW ROCK'], ['382872','CinemaWorld'],
            ['572361','ADITHYA TV'], ['569530','ANC'], ['571922','Animax HD'],
            ['572358','Asianet'], ['572359','Asianet Movies'], ['569790','Astro Sensasi HD'],
            ['569789','Astro Warna HD'], ['571915','AXN HD'], ['566407','BBC Earth HD'],
            ['570217','BBC Lifestyle HD'], ['569794','BBC News HD'], ['570192','Cartoon Network'],
            ['569516','CBeebies HD'], ['572051','天映經典台新加坡'], ['569797','CCTV-4'],
            ['572048','天映頻道新加坡'], ['566560','CGTN'], ['569534','Cinema One Global'],
            ['569781','Citra Entertainment'], ['571958','CNBC HD'], ['571959','CNN HD'],
            ['572356','COLORS'], ['572357','COLORS Tamil HD'], ['570193','Crime + Investigation HD'],
            ['571963','中天亞洲台新加坡'], ['570194','Discovery HD新加坡'],
            ['571966','東方衛視國際版'], ['569526','DreamWorks HD'], ['569803','東森亞洲臺新加坡'],
            ['570218','HGTV HD新加坡'], ['569527','HISTORY HD新加坡'], ['567120','HITS HD新加坡'],
            ['569535','HITS MOVIES HD新加坡'], ['569498','都會臺'], ['567123','Hub Premier 1'],
            ['572415','Hub Premier 2'], ['572419','Hub Premier 3'], ['572423','Hub Premier 4'],
            ['572420','Hub Premier 5'], ['572421','Hub Premier 6'], ['572417','Hub Premier 7'],
            ['572414','Hub Premier 8'], ['572411','Hub Premier 9'], ['572427','Hub Premier 10'],
            ['572408','Hub Premier 11'], ['571971','Hub Ruyi'], ['569506','Hub Sports 1 HD'],
            ['569510','Hub Sports 2 HD'], ['566562','Hub Sports 3 HD'], ['564507','Hub VV Drama HD'],
            ['572360','Kalaignar TV'], ['569788','Karisma'], ['569491','KBS World HD'],
            ['567111','KTV HD'], ['571921','Lifetime HD新加坡'], ['569519','Nick Jr. HD新加坡'],
            ['569522','Nickelodeon Asia HD新加坡'], ['569791','ONE (Malay)'], ['566561','ONE HD'],
            ['570229','ROCK Entertainment HD新加坡'], ['571936','Sky News HD新加坡'],
            ['572340','Sony Entertainment Televis新加坡'], ['572343','SONY MAX'],
            ['572338','Sun Music'], ['572335','Sun TV'], ['569532','The Filipino Channel HD'],
            ['570207','Travelxp HD'], ['572047','TVB星河新加坡'], ['569503','TVBS亞洲新加坡'],
            ['569802','TVB新聞新加坡'], ['572317','Vannathirai'], ['572316','Vijay TV HD'],
            ['572312','Zee Cinema HD'], ['572309','Zee Tamil HD'], ['572222','Zee Thirai'],
            ['572192','Zee TV HD'],
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1], $channel[1]);
            
            // 昨天節目
            $url = "https://cdn.mewatch.sg/api/schedules?channels={$channel[0]}&date={$this->dates['dt10']}&duration=24&ff=idp%2Cldp%2Crpt%2Ccd&hour=16&intersect=true&lang=en&segments=all";
            $data = $this->httpRequest($url);
            if ($data) $this->processMeWatchData($channel[1], $data);
            
            // 今天節目
            $url = "https://cdn.mewatch.sg/api/schedules?channels={$channel[0]}&date={$this->dates['dt11']}&duration=24&ff=idp%2Cldp%2Crpt%2Ccd&hour=16&intersect=true&lang=en&segments=all";
            $data = $this->httpRequest($url);
            if ($data) $this->processMeWatchData($channel[1], $data);
            
            // 明天節目
            $url = "https://cdn.mewatch.sg/api/schedules?channels={$channel[0]}&date={$this->dates['dt12']}&duration=24&ff=idp%2Cldp%2Crpt%2Ccd&hour=16&intersect=true&lang=en&segments=all";
            $data = $this->httpRequest($url);
            if ($data) $this->processMeWatchData($channel[1], $data);
        }
        
        $this->log('meWatch 頻道處理完成');
    }
    
    private function processMeWatchData($channel, $data) {
        $data = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', [$this, 'unicodeDecode'], $data);
        $data = str_replace('&', '&amp;', $data);
        
        $json = json_decode($data);
        if (!$json || !isset($json[0]) || !isset($json[0]->schedules)) return;
        
        $schedules = $json[0]->schedules;
        foreach ($schedules as $schedule) {
            if (!isset($schedule->startDate) || !isset($schedule->endDate) || !isset($schedule->item)) continue;
            
            $startDate = str_replace(['T', 'Z', '-', ':'], '', $schedule->startDate);
            $endDate = str_replace(['T', 'Z', '-', ':'], '', $schedule->endDate);
            $title = isset($schedule->item->secondaryLanguageTitle) ? $schedule->item->secondaryLanguageTitle : '';
            $title .= isset($schedule->item->title) ? $schedule->item->title : '';
            $desc = isset($schedule->item->description) ? $schedule->item->description : '';
            
            $start = $startDate . ' +0000';
            $stop = $endDate . ' +0000';
            
            $this->addProgram($channel, $title, $start, $stop, $desc);
        }
    }
    
    /**
     * 處理河南電視台
     */
    private function processHenan() {
        $this->log('開始處理河南電視台...');
        
        $channels = [
            ['145','河南卫视'], ['149','河南新闻'], ['141','河南都市'],
            ['146','河南民生'], ['147','河南法制'], ['151','河南公共'],
            ['152','河南乡村'], ['148','河南电视剧'], ['154','河南梨园戏曲'],
            ['155','河南文物宝库'], ['156','河南武术'], ['157','河南晴彩中原'],
            ['163','河南移动戏曲'], ['183','河南象世界'], ['150','河南欢腾购物'],
            ['194','国学时代界'],
        ];
        
        $timestamp = time();
        $sign = hash('sha256', '6ca114a836ac7d73' . $timestamp);
        
        $headers = [
            'Host: pubmod.hntv.tv',
            'User-Agent: ' . USER_AGENT,
            'sign: ' . $sign,
            'timestamp: ' . $timestamp,
            'Origin: https://static.hntv.tv',
            'Connection: keep-alive',
            'Referer: https://static.hntv.tv/',
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1], $channel[1]);
            
            $url = "https://pubmod.hntv.tv/program/getAuth/vod/originStream/program/{$channel[0]}/{$timestamp}";
            $data = $this->httpRequest($url, ['headers' => $headers]);
            
            if (!$data) continue;
            
            $data = str_replace('&', '&amp;', $data);
            $json = json_decode($data);
            
            if ($json && isset($json->programs)) {
                foreach ($json->programs as $program) {
                    if (!isset($program->beginTime) || !isset($program->endTime) || !isset($program->title)) continue;
                    
                    $start = str_replace([' ', '-', ':'], '', date('Y-m-d H:i:s', $program->beginTime)) . ' +0800';
                    $stop = str_replace([' ', '-', ':'], '', date('Y-m-d H:i:s', $program->endTime)) . ' +0800';
                    $this->addProgram($channel[1], $program->title, $start, $stop);
                }
            }
        }
        
        $this->log('河南電視台處理完成');
    }
    
    /**
     * 處理浙江電視台
     */
    private function processZhejiang() {
        $this->log('開始處理浙江電視台...');
        
        $channels = [
            ['101','浙江卫视'], ['102','浙江钱江都市'], ['103','浙江经济'],
            ['104','浙江科教'], ['106','浙江民生'], ['107','浙江新闻'],
            ['108','浙江少儿'], ['110','浙江国际'], ['111','浙江好易购'],
            ['112','浙江数码时代'],
        ];
        
        $headers = [
            'Host: p.cztv.com',
            'User-Agent: ' . USER_AGENT,
            'Origin: http://www.cztv.com',
            'Connection: keep-alive',
            'Referer: http://www.cztv.com/',
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1], $channel[1]);
            
            $url = "https://p.cztv.com/api/paas/program/{$channel[0]}/{$this->dates['dt1']}";
            $data = $this->httpRequest($url, ['headers' => $headers]);
            
            if (!$data) continue;
            
            $data = str_replace(['《', '》', '&'], ['', '', '&amp;'], $data);
            $json = json_decode($data);
            
            if ($json && isset($json->content) && isset($json->content->list[0]->list)) {
                $programs = $json->content->list[0]->list;
                foreach ($programs as $program) {
                    if (!isset($program->play_time) || !isset($program->duration) || !isset($program->program_title)) continue;
                    
                    $startTime = $program->play_time / 1000;
                    $endTime = ($program->play_time + $program->duration) / 1000;
                    
                    $start = str_replace([' ', '-', ':'], '', date('Y-m-d H:i:s', $startTime)) . ' +0800';
                    $stop = str_replace([' ', '-', ':'], '', date('Y-m-d H:i:s', $endTime)) . ' +0800';
                    $title = str_replace(':', '', $program->program_title);
                    
                    $this->addProgram($channel[1], $title, $start, $stop);
                }
            }
        }
        
        $this->log('浙江電視台處理完成');
    }
    
    /**
     * 處理廣東電視台
     */
    private function processGuangdong() {
        $this->log('開始處理廣東電視台...');
        
        $channels = [
            ['1','广东卫视'], ['2','广东珠江'], ['6','广东新闻'],
            ['4','广东民生'], ['14','广东大湾区卫视'], ['3','广东体育'],
            ['17','广东影视'], ['16','广东综艺'], ['18','广东少儿'],
            ['7','广东嘉佳卡通'], ['31','广东现代教育'], ['32','广东移动'],
            ['33','广东岭南戏曲'],
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1], $channel[1]);
            
            // 今天節目
            $url = "http://epg.gdtv.cn/f/{$channel[0]}/{$this->dates['dt11']}.xml";
            $data = $this->httpRequest($url);
            if ($data) $this->processGuangdongData($channel[1], $data);
            
            // 明天節目
            $url = "http://epg.gdtv.cn/f/{$channel[0]}/{$this->dates['dt12']}.xml";
            $data = $this->httpRequest($url);
            if ($data) $this->processGuangdongData($channel[1], $data);
        }
        
        $this->log('廣東電視台處理完成');
    }
    
    private function processGuangdongData($channel, $data) {
        $data = str_replace('&', '&amp;', $data);
        
        preg_match_all('/<content time1="(.*?)" time2=/i', $data, $startTimes);
        preg_match_all('/time2="(.*?)">/i', $data, $endTimes);
        preg_match_all('/<!\[CDATA\[(.*?)\]\]>/i', $data, $titles);
        
        $count = count($titles[1]);
        for ($i = 0; $i < $count; $i++) {
            if (!isset($startTimes[1][$i]) || !isset($endTimes[1][$i]) || !isset($titles[1][$i])) continue;
            
            $start = str_replace([' ', '-', ':'], '', date('Y-m-d H:i:s', $startTimes[1][$i])) . ' +0800';
            $stop = str_replace([' ', '-', ':'], '', date('Y-m-d H:i:s', $endTimes[1][$i])) . ' +0800';
            $this->addProgram($channel, $titles[1][$i], $start, $stop);
        }
    }
    
    /**
     * 處理陝西電視台
     */
    private function processShaanxi() {
        $this->log('開始處理陝西電視台...');
        
        $channels = [
            ['star','陕西卫视'], ['1','陕西新闻资讯'], ['2','陕西都市青春'],
            ['3','陕西银龄频道'], ['5','陕西秦腔频道'], ['6','陕西乐家购物'],
            ['7','陕西体育休闲'], ['nl','陕西农林'], ['11','陕西移动电视'],
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1], $channel[1]);
            
            $url = "https://qidian.sxtvs.com/api/v3/program/tv?channel={$channel[0]}";
            $data = $this->httpRequest($url);
            
            if (!$data) continue;
            
            $data = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', [$this, 'unicodeDecode'], $data);
            
            preg_match_all('/"start":"(.*?)",/i', $data, $startTimes);
            preg_match_all('/"end":"(.*?)",/i', $data, $endTimes);
            preg_match_all('/"name":"(.*?)",/i', $data, $titles);
            
            $count = count($titles[1]);
            for ($i = 0; $i < $count; $i++) {
                if (!isset($startTimes[1][$i]) || !isset($endTimes[1][$i]) || !isset($titles[1][$i])) continue;
                
                $start = $this->dates['dt1'] . str_replace(':', '', $startTimes[1][$i]) . '00 +0800';
                $stop = $this->dates['dt1'] . str_replace(':', '', $endTimes[1][$i]) . '00 +0800';
                $title = preg_replace('/\s+/', '', str_replace('</h4>', '', $titles[1][$i]));
                
                $this->addProgram($channel[1], $title, $start, $stop);
            }
        }
        
        $this->log('陝西電視台處理完成');
    }
    
    /**
     * 處理廣西電視台
     */
    private function processGuangxi() {
        $this->log('開始處理廣西電視台...');
        
        $channels = [
            ['广西卫视','广西卫视'], ['综艺旅游频道','广西综艺旅游频道'],
            ['都市频道','广西都市频道'], ['新闻频道','广西新闻频道'],
            ['影视频道','广西影视频道'], ['国际频道','广西国际频道'],
            ['乐思购频道','广西乐思购频道'],
        ];
        
        $url = 'https://api2019.gxtv.cn/memberApi/programList/selectListByChannelId';
        $headers = [
            'Host: api2019.gxtv.cn',
            'User-Agent: ' . USER_AGENT,
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'Origin: https://program.gxtv.cn',
            'Connection: keep-alive',
            'Referer: https://program.gxtv.cn/',
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1], $channel[1]);
            
            // 今天節目
            $data = [
                'channelName' => $channel[0],
                'dateStr' => $this->dates['dt11'],
                'programName' => '',
                'deptId' => '0a509685ba1a11e884e55cf3fc49331c',
                'platformId' => 'bd7d620a502d43c09b35469b3cd8c211',
            ];
            
            $response = $this->httpRequest($url, [
                'post' => true,
                'data' => http_build_query($data),
                'headers' => $headers
            ]);
            
            if ($response) {
                $this->processGuangxiData($channel[1], $response);
            }
            
            // 明天節目
            $data['dateStr'] = $this->dates['dt12'];
            $response = $this->httpRequest($url, [
                'post' => true,
                'data' => http_build_query($data),
                'headers' => $headers
            ]);
            
            if ($response) {
                $this->processGuangxiData($channel[1], $response);
            }
        }
        
        $this->log('廣西電視台處理完成');
    }
    
    private function processGuangxiData($channel, $data) {
        $data = str_replace('&', '&amp;', $data);
        
        preg_match_all('/"programName":"(.*?)",/i', $data, $titles);
        preg_match_all('/"programTime":"(.*?)"/i', $data, $times);
        
        $count = count($titles[1]);
        for ($i = 1; $i <= $count - 2; $i++) {
            if (!isset($times[1][$i-1]) || !isset($times[1][$i]) || !isset($titles[1][$i])) continue;
            
            $start = str_replace([' ', ':', '-'], '', $times[1][$i-1]) . ' +0800';
            $stop = str_replace([' ', ':', '-'], '', $times[1][$i]) . ' +0800';
            
            $this->addProgram($channel, $titles[1][$i], $start, $stop);
        }
    }
    
    /**
     * 處理廈門電視台
     */
    private function processXiamen() {
        $this->log('開始處理廈門電視台...');
        
        $channels = [
            ['84','厦门卫视'], ['16','厦门1'], ['17','厦门2'],
        ];
        
        $headers = [
            'Host: mapi1.kxm.xmtv.cn',
            'User-Agent: ' . USER_AGENT,
            'Origin: https://share1.kxm.xmtv.cn',
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1], $channel[1]);
            
            $url = "https://mapi1.kxm.xmtv.cn/api/v1/tvshow_share.php?channel_id={$channel[0]}&zone=";
            $data = $this->httpRequest($url, ['headers' => $headers]);
            
            if (!$data) continue;
            
            $data = str_replace('&', '&amp;', $data);
            $data = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', [$this, 'unicodeDecode'], $data);
            
            preg_match_all('/"start_time":(.*?),"date/i', $data, $startTimes);
            preg_match_all('/"end_time":(.*?),"m3u8/i', $data, $endTimes);
            preg_match_all('/"theme":"(.*?)"/i', $data, $titles);
            
            $count = count($titles[1]);
            for ($i = 0; $i < $count - 1; $i++) {
                if (!isset($startTimes[1][$i]) || !isset($endTimes[1][$i]) || !isset($titles[1][$i])) continue;
                
                $start = str_replace([' ', '-', ':'], '', date('Y-m-d H:i:s', $startTimes[1][$i])) . ' +0800';
                $stop = str_replace([' ', '-', ':'], '', date('Y-m-d H:i:s', $endTimes[1][$i])) . ' +0800';
                
                $this->addProgram($channel[1], $titles[1][$i], $start, $stop);
            }
        }
        
        $this->log('廈門電視台處理完成');
    }
    
    /**
     * 處理河北電視台
     */
    private function processHebei() {
        $this->log('開始處理河北電視台...');
        
        $channels = [
            ['462','河北卫视'], ['114','河北经济'], ['118','河北农民'],
            ['62','河北都市'], ['334','河北影视剧'], ['70','河北少儿科教'],
            ['338','河北公共'],
        ];
        
        $url = 'https://api.cmc.hebtv.com/spidercrms/api/live/liveShowSet/findNoPage';
        $headers = [
            'Connection: keep-alive',
            'tenantId: 0d91d6cfb98f5b206ac1e752757fc5a9',
            'DNT: 1',
            'Content-Type: application/json',
            'Origin: https://www.hebtv.com',
            'Referer: https://www.hebtv.com/',
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1], $channel[1]);
            
            // 今天節目
            $data = [
                "sourceId" => $channel[0],
                "tenantId" => "0d91d6cfb98f5b206ac1e752757fc5a9",
                "day" => $this->dates['dt11'],
                "dayEnd" => $this->dates['dt11'],
            ];
            
            $response = $this->httpRequest($url, [
                'post' => true,
                'data' => json_encode($data),
                'headers' => $headers
            ]);
            
            if ($response) {
                $this->processHebeiData($channel[1], $response);
            }
            
            // 明天節目
            $data['day'] = $this->dates['dt11'];
            $data['dayEnd'] = $this->dates['dt12'];
            
            $response = $this->httpRequest($url, [
                'post' => true,
                'data' => json_encode($data),
                'headers' => $headers
            ]);
            
            if ($response) {
                $this->processHebeiData($channel[1], $response);
            }
        }
        
        $this->log('河北電視台處理完成');
    }
    
    private function processHebeiData($channel, $data) {
        $data = str_replace('&', '&amp;', $data);
        $data = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', [$this, 'unicodeDecode'], $data);
        
        preg_match_all('/"startDateTime":"(.*?)",/i', $data, $startTimes);
        preg_match_all('/"endDateTime":"(.*?)",/i', $data, $endTimes);
        preg_match_all('/"name":"(.*?)",/i', $data, $titles);
        
        $count = count($titles[1]);
        for ($i = 0; $i < $count; $i++) {
            if (!isset($startTimes[1][$i]) || !isset($endTimes[1][$i]) || !isset($titles[1][$i])) continue;
            
            $start = str_replace([' ', ':', '-'], '', $startTimes[1][$i]) . ' +0800';
            $stop = str_replace([' ', ':', '-'], '', $endTimes[1][$i]) . ' +0800';
            
            $this->addProgram($channel, $titles[1][$i], $start, $stop);
        }
    }
    
    /**
     * 處理海南電視台
     */
    private function processHainan() {
        $this->log('開始處理海南電視台...');
        
        $channels = [
            ['7','三沙卫视'], ['3','海南卫视'], ['4','海南经济'],
            ['5','海南新闻'], ['6','海南公共'], ['8','海南文旅'],
            ['9','海南少儿'],
        ];
        
        $timestamp = time();
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1], $channel[1]);
            
            $url = "http://www.hnntv.cn/m2o/program_switch.php?channel_id={$channel[0]}&shownums=7&_={$timestamp}";
            $data = $this->httpRequest($url);
            
            if (!$data) continue;
            
            $data = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', [$this, 'unicodeDecode'], $data);
            
            preg_match_all('/<span class="time">(.*?)<\/span>/i', $data, $times);
            preg_match_all('/<\/span>(.*?)<\/li>/i', $data, $titles);
            
            $count = count($times[1]);
            for ($i = 1; $i <= $count - 2; $i++) {
                if (!isset($times[1][$i-1]) || !isset($times[1][$i]) || !isset($titles[1][$i+6])) continue;
                
                $start = $this->dates['dt1'] . str_replace(':', '', $times[1][$i-1]) . '00 +0800';
                $stop = $this->dates['dt1'] . str_replace(':', '', $times[1][$i]) . '00 +0800';
                
                $this->addProgram($channel[1], $titles[1][$i+6], $start, $stop);
            }
        }
        
        $this->log('海南電視台處理完成');
    }
    
    /**
     * 處理山東電視台
     */
    private function processShandong() {
        $this->log('開始處理山東電視台...');
        
        $channels = [
            ['24','山东卫视'], ['31','山东新闻'], ['25','山东齐鲁'],
            ['26','山东体育'], ['29','山东生活'], ['28','山东综艺'],
            ['30','山东农科'], ['27','山东文旅'], ['32','山东少儿'],
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1], $channel[1]);
            
            $url = "http://module.iqilu.com/media/apis/main/getprograms?channelID={$channel[0]}&date={$this->dates['dt11']}";
            $data = $this->httpRequest($url);
            
            if (!$data) continue;
            
            $data = str_replace('&', '&amp;', $data);
            $data = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', [$this, 'unicodeDecode'], $data);
            
            preg_match_all('/{"name":"(.*?)",/i', $data, $titles);
            preg_match_all('/"begintime":(.*?),"endtime/i', $data, $startTimes);
            preg_match_all('/"endtime":(.*?)},/i', $data, $endTimes);
            
            $count = count($titles[1]);
            for ($i = 0; $i < $count; $i++) {
                if (!isset($startTimes[1][$i]) || !isset($endTimes[1][$i]) || !isset($titles[1][$i])) continue;
                
                $start = str_replace([' ', '-', ':'], '', date('Y-m-d H:i:s', $startTimes[1][$i])) . ' +0800';
                $stop = str_replace([' ', '-', ':'], '', date('Y-m-d H:i:s', $endTimes[1][$i])) . ' +0800';
                $title = str_replace(['<', '>', '&'], ['&lt;', '&gt;', '&amp;'], $titles[1][$i]);
                
                $this->addProgram($channel[1], $title, $start, $stop);
            }
        }
        
        $this->log('山東電視台處理完成');
    }
    
    /**
     * 處理電影頻道 (1905)
     */
    private function processMovie1905() {
        $this->log('開始處理電影頻道...');
        
        $channels = [
            ['chcna','CMC 北美頻道'], ['cmchk','CMC 香港頻道'],
            ['chchome','CHC 家庭影院'], ['dypdepg','CCTV6 電影頻道'],
            ['xlepg','1905App 熱血·影院'], ['apptvepg','1905App 環球經典'],
        ];
        
        $headers = [
            'Host: www.1905.com',
            'Connection: keep-alive',
            'User-Agent: ' . USER_AGENT,
            'Referer: https://www.1905.com/cctv6/program/',
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: en-US,en;q=0.9,zh-CN;q=0.8,zh;q=0.7,en-GB;q=0.6',
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1], $channel[1]);
            
            $url = "https://www.1905.com/cctv6/program/{$channel[0]}/list/";
            $data = $this->httpRequest($url, ['headers' => $headers]);
            
            if (!$data) continue;
            
            $data = preg_replace('/\s+/', '', $data);
            $data = str_replace('&', '&amp;', $data);
            $data = str_replace(['<em>(00:00-12:00)</em>', '<em>(12:00-24:00)</em>'], '', $data);
            
            preg_match('/<p>節目單(.*?)<!--footer-->/i', $data, $matches);
            if (!isset($matches[1])) continue;
            
            preg_match_all('/<li data-id="(.*?)" data-caturl/i', $matches[1], $times);
            preg_match_all('/<em>(.*?)<\/em>/i', $matches[1], $titles);
            
            $count = count($titles[1]);
            for ($i = 1; $i <= $count - 1; $i++) {
                if (!isset($times[1][$i-1]) || !isset($times[1][$i]) || !isset($titles[1][$i-1])) continue;
                
                $start = date('YmdHis', $times[1][$i-1]) . ' +0800';
                $stop = date('YmdHis', $times[1][$i]) . ' +0800';
                $title = str_replace(['<', '>', ':'], ['&lt;', '&gt;', ''], $titles[1][$i-1]);
                
                $this->addProgram($channel[1], $title, $start, $stop);
            }
        }
        
        $this->log('電影頻道處理完成');
    }
    
    /**
     * 處理電視貓頻道
     */
    private function processTVCat() {
        $this->log('開始處理電視貓頻道...');
        
        $channels = [
            ['新视觉','新视觉'], ['劲爆体育','劲爆体育'],
            ['海峡卫视','海峡卫视'], ['深视都市频道','深视都市频道'],
            ['深视电视剧频道','深视电视剧频道'], ['深视财经生活频道','深视财经生活频道'],
            ['深视体育健康频道','深视体育健康频道'], ['深视少儿频道','深视少儿频道'],
            ['深视移动电视频道','深视移动电视频道'], ['福建电视台新闻频道','福建电视台新闻频道'],
            ['福建乡村振兴·公共','福建乡村振兴·公共'], ['福建电视剧频道','福建电视剧频道'],
            ['福建旅游频道','福建旅游频道'], ['福建经济频道','福建经济频道'],
            ['福建体育频道','福建体育频道'], ['福建少儿频道','福建少儿频道'],
            ['福建综合频道','福建综合频道'], ['江苏优漫卡通频道','江苏优漫卡通频道'],
            ['江苏国际频道','江苏国际频道'],
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1], $channel[1]);
            
            // 昨天節目
            $url = "https://sp1.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php?query={$channel[0]}&co=data[tabid=1]&resource_id=12520";
            $data = $this->httpRequest($url);
            if ($data) $this->processTVCatData($channel[1], $data);
            
            // 今天節目
            $url = "https://sp1.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php?query={$channel[0]}&co=data[tabid=2]&resource_id=12520";
            $data = $this->httpRequest($url);
            if ($data) $this->processTVCatData($channel[1], $data);
            
            // 明天節目
            $url = "https://sp1.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php?query={$channel[0]}&co=data[tabid=3]&resource_id=12520";
            $data = $this->httpRequest($url);
            if ($data) $this->processTVCatData($channel[1], $data);
        }
        
        $this->log('電視貓頻道處理完成');
    }
    
    private function processTVCatData($channel, $data) {
        $data = preg_replace('/\s+/', '', $data);
        $data = str_replace(['&', '/'], ['&amp;', ''], $data);
        $data = mb_convert_encoding($data, "UTF-8", "gb2312");
        
        preg_match_all('/,"title":"(.*?)","tvname/i', $data, $titles);
        preg_match_all('/"times":"(.*?)","title/i', $data, $times);
        
        $count = count($titles[1]);
        for ($i = 1; $i <= $count; $i++) {
            if (!isset($times[1][$i-1]) || !isset($times[1][$i]) || !isset($titles[1][$i-1])) continue;
            
            $start = str_replace([' ', ':', '-'], '', $times[1][$i-1]) . '00 +0800';
            $stop = str_replace([' ', ':', '-'], '', $times[1][$i]) . '00 +0800';
            
            $this->addProgram($channel, $titles[1][$i-1], $start, $stop);
        }
    }
    
    /**
     * 處理重溫經典頻道
     */
    private function processChongwen() {
        $this->log('開始處理重溫經典頻道...');
        
        $channels = [['cwjd', '重溫經典頻道']];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1], $channel[1]);
            
            $url = "http://timetv.cn/epg/{$channel[0]}.html";
            $data = $this->httpRequest($url);
            
            if (!$data) continue;
            
            // 清理 HTML
            $data = preg_replace('/\s+/', '', $data);
            $data = str_replace('&', '', $data);
            $data = str_replace([
                "<i style='color gray'>",
                "<span style='color: gray'>",
                "<span style='color: black'>",
                "<i style='color: darkgreen'>",
                "<i style='color: black'>",
                "<istyle='colordarkgreen;'>",
                "<spanstyle='color:darkgreen'>",
            ], '', $data);
            
            preg_match('/今日節目單(.*?)<\/tbody>/i', $data, $matches);
            if (!isset($matches[1])) continue;
            
            preg_match_all('/<tdclass=\'text\'>(.*?)</i', $matches[1], $titles);
            preg_match_all('/<tdclass=\'time\'>(.*?)</i', $matches[1], $times);
            
            $count = count($times[1]);
            for ($i = 0; $i <= $count - 2; $i++) {
                if (!isset($times[1][$i]) || !isset($times[1][$i+1]) || !isset($titles[1][$i])) continue;
                
                $start = $this->dates['dt1'] . str_replace(':', '', $times[1][$i]) . '00 +0800';
                $stop = $this->dates['dt1'] . str_replace(':', '', $times[1][$i+1]) . '00 +0800';
                $title = preg_replace('/<[^>]+>/', '', $titles[1][$i]);
                
                $this->addProgram($channel[1], $title, $start, $stop);
            }
        }
        
        $this->log('重溫經典頻道處理完成');
    }
    
    /**
     * 處理電視貓其他頻道
     */
    private function processTVMao() {
        $this->log('開始處理電視貓其他頻道...');
        
        $channels = [
            ['digital', 'SITV-YULE', '魅力足球', '/', '_'],
            ['CCTV', 'CCTVEUROPE', 'CCTV-4歐洲頻道', '-', '/'],
            ['CCTV', 'CCTVAMERICAS', 'CCTV-4美洲頻道', '-', '/'],
            ['digital', 'SITV-SPORTS', '勁爆體育', '/', '_'],
        ];
        
        $w1 = date("w");
        if ($w1 < '1') $w1 = 7;
        $w2 = $w1 + 1;
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[2], $channel[2]);
            
            // 今天節目
            $url = "https://www.tvmao.com/program{$channel[4]}{$channel[0]}{$channel[3]}{$channel[1]}-w{$w1}.html";
            $data = $this->httpRequest($url);
            
            if ($data) {
                $this->processTVMaoData($channel[2], $data, $this->dates['dt1']);
            }
            
            // 明天節目
            $url = "https://www.tvmao.com/program{$channel[4]}{$channel[0]}{$channel[3]}{$channel[1]}-w{$w2}.html";
            $data = $this->httpRequest($url);
            
            if ($data) {
                $this->processTVMaoData($channel[2], $data, $this->dates['dt2']);
            }
        }
        
        $this->log('電視貓其他頻道處理完成');
    }
    
    private function processTVMaoData($channel, $data, $date) {
        $data = preg_replace('/\s+/', '', $data);
        $data = str_replace('&', '&amp;', $data);
        $data = str_replace('<divclass="cur_player"><span>正在播出', '', $data);
        
        preg_match('/周日(.*)查看更多<\/a>/i', $data, $matches);
        if (!isset($matches[1])) return;
        
        $matches[1] = str_replace('</a>', '', $matches[1]);
        
        preg_match_all('/<spanclass="p_show">(.*?)<\/span>/i', $matches[1], $titles);
        preg_match_all('/<spanclass="am">(.*?)<\/span>/i', $matches[1], $amTimes);
        
        $count = count($amTimes[1]);
        for ($i = 0; $i <= $count - 1; $i++) {
            if (!isset($amTimes[1][$i]) || !isset($amTimes[1][$i+1]) || !isset($titles[1][$i])) continue;
            
            $start = $date . str_replace(':', '', $amTimes[1][$i]) . '00 +0800';
            $stop = $date . str_replace(':', '', $amTimes[1][$i+1]) . '00 +0800';
            $title = preg_replace('/<[^>]+>/', '', $titles[1][$i]);
            
            $this->addProgram($channel, $title, $start, $stop);
        }
    }
    
    /**
     * 主處理方法
     */
    public function processAll() {
        $this->log('開始生成 EPG 數據...');
        
        // 按順序處理所有數據源
        $methods = [
            'processTVB',
            'processTBC',
            'processTBCAdult',
            'processCelestial',
            'processMyTVSuper',
            'processCTIAsia',
            'processLonghua',
            'processNowPlayer',
            'processMeWatch',
            'processHenan',
            'processZhejiang',
            'processGuangdong',
            'processShaanxi',
            'processGuangxi',
            'processXiamen',
            'processHebei',
            'processHainan',
            'processShandong',
            'processMovie1905',
            'processTVCat',
            'processChongwen',
            'processTVMao',
        ];
        
        foreach ($methods as $method) {
            if (method_exists($this, $method)) {
                try {
                    $this->$method();
                } catch (Exception $e) {
                    $this->log("{$method} 處理失敗: " . $e->getMessage(), 'ERROR');
                }
            }
        }
        
        // 完成 XML
        $this->xml .= "</tv>\n";
        
        $endTime = microtime(true);
        $executionTime = round($endTime - $this->startTime, 2);
        
        $this->log("EPG 生成完成！總執行時間: {$executionTime} 秒");
    }
    
    public function saveToFile($filename = null) {
        $filename = $filename ?: OUTPUT_FILE;
        
        if (file_put_contents($filename, $this->xml)) {
            $this->log("EPG 已保存到: $filename (大小: " . number_format(strlen($this->xml)) . " 字節)");
            return true;
        } else {
            $this->log("保存文件失敗: $filename", 'ERROR');
            return false;
        }
    }
    
    public function output() {
        header('Content-Type: application/xml; charset=UTF-8');
        echo $this->xml;
    }
    
    public function getXml() {
        return $this->xml;
    }
    
    public function getLog() {
        return $this->log;
    }
}

// ==================== 執行入口 ====================
if (PHP_SAPI === 'cli') {
    // 命令行模式
    echo "秋哥綜合 EPG 生成器 v" . VERSION . "\n";
    echo "========================================\n";
    
    $generator = new EPGGenerator();
    $generator->processAll();
    
    $filename = OUTPUT_FILE;
    if ($generator->saveToFile($filename)) {
        echo "EPG 已生成: $filename\n";
    } else {
        echo "生成失敗！\n";
        exit(1);
    }
} else {
    // Web 模式
    $action = $_GET['action'] ?? 'generate';
    
    switch ($action) {
        case 'generate':
            $generator = new EPGGenerator();
            $generator->processAll();
            
            if (isset($_GET['download'])) {
                $generator->saveToFile();
                header('Content-Type: application/xml');
                header('Content-Disposition: attachment; filename="' . OUTPUT_FILE . '"');
                echo $generator->getXml();
            } elseif (isset($_GET['view'])) {
                $generator->output();
            } else {
                $generator->saveToFile();
                echo "<h1>EPG 生成完成！</h1>";
                echo "<p>文件: " . OUTPUT_FILE . "</p>";
                echo "<p>大小: " . number_format(strlen($generator->getXml())) . " 字節</p>";
                echo "<p><a href='?action=view'>查看 XML</a> | <a href='?action=generate&download=1'>下載 XML</a></p>";
                
                $log = $generator->getLog();
                echo "<h3>日誌:</h3><pre>";
                foreach ($log as $entry) {
                    echo htmlspecialchars($entry) . "\n";
                }
                echo "</pre>";
            }
            break;
            
        case 'view':
            if (file_exists(OUTPUT_FILE)) {
                header('Content-Type: application/xml; charset=UTF-8');
                readfile(OUTPUT_FILE);
            } else {
                header('Location: ?action=generate');
            }
            break;
            
        default:
            echo "<h1>秋哥綜合 EPG 生成器</h1>";
            echo "<p><a href='?action=generate'>生成 EPG</a></p>";
            if (file_exists(OUTPUT_FILE)) {
                $mtime = date('Y-m-d H:i:s', filemtime(OUTPUT_FILE));
                $size = number_format(filesize(OUTPUT_FILE));
                echo "<p>現有文件: " . OUTPUT_FILE . " ($size 字節, 更新於 $mtime)</p>";
                echo "<p><a href='?action=view'>查看</a> | <a href='" . OUTPUT_FILE . "' download>下載</a></p>";
            }
            break;
    }
}
