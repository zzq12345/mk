<?php
header('Content-Type: text/plain; charset=UTF-8');
ini_set("max_execution_time", "900");
ini_set('date.timezone','Asia/Shanghai');
ini_set('memory_limit', '512M');

class EPGGenerator {
    private $fp = "epg4gtv2.xml";
    private $chn = "";
    private $currentDate;
    private $nextDate;
    private $dt1;
    private $dt2;
    private $dt21;
    private $dt22;
    private $dt7;
    private $cacheEnabled = false;
    private $cacheDir = './cache/';
    private $cacheTime = 3600;
    
    public function __construct() {
        $this->currentDate = date('Y-m-d');
        $this->nextDate = date('Y-m-d', time() + 24 * 3600);
        $this->dt1 = date('Ymd');
        $this->dt2 = date('Ymd', time() + 24 * 3600);
        $this->dt21 = date('Ymd', time() + 48 * 3600);
        $this->dt22 = date('Ymd', time() - 24 * 3600);
        $this->dt7 = date('Y');  // 当前年份
        
        // 确保缓存目录存在
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
        
        $this->initializeXML();
    }
        
    private function initializeXML() {
        $this->chn = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $this->chn .= "<!DOCTYPE tv SYSTEM \"http://api.torrent-tv.ru/xmltv.dtd\">\n";
        $this->chn .= "<tv generator-info-name=\"秋哥綜合\" generator-info-url=\"https://www.tdm.com.mo/c_tv/?ch=Satellite\">\n";
    }
    
    public function generate() {
        // 按数据源模块化处理
        $this->processMacauTV();
        $this->processMOD();
        $this->processBBTV();
        $this->processBBTV1();
        $this->processBBTV2();
        $this->processKbro();
        $this->processHami(); // 整合的Hami处理
        $this->process4GTV();
        $this->processOfiii();
        $this->processMiguSports();
        $this->processOlympic();
        
        // 使用修复后的 KBS 处理方法
        $this->processKBS();
        $this->processSBS();
        $this->processMBC();
        $this->processEBS(); // 取消注释，启用EBS处理
        $this->processKatyusha();
        
        $this->chn .= "</tv>\n";
        file_put_contents($this->fp, $this->chn);
        return "EPG生成完成: " . $this->fp;
    }
  
    // ================ 以下是原有方法 ================
    
    // 1. 澳門電視台
    private function processMacauTV() {
        $channels = [
            ['1','澳視澳門'],
            ['2','澳門葡萄牙'],
            ['5','澳門資訊'],
            ['6','澳門體育'],
            ['7','澳門綜藝'],
            ['8','澳門衛星'],
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1]);
            $this->fetchMacauPrograms($channel[0], $channel[1]);
        }
    }
    
    private function fetchMacauPrograms($channelId, $channelName) {
        $dates = [$this->currentDate, $this->nextDate];
        
        foreach ($dates as $date) {
            $url = "https://www.tdm.com.mo/api/v1.0/program-list/{$date}?type=tv&channelId={$channelId}&date={$date}";
            $data = $this->curlGet($url);
            
            if ($data) {
                $data = str_replace('&', '', $data);
                preg_match_all('|"title":"(.*?)","isLive|i', $data, $titles, PREG_SET_ORDER);
                preg_match_all('|"date":"(.*?)","title|i', $data, $times, PREG_SET_ORDER);
                
                $count = count($titles);
                for ($i = 1; $i <= $count - 1; $i++) {  
                    $start = str_replace([' ', ':', '-'], '', $times[$i-1][1]) . ' +0800';
                    $stop = str_replace([' ', ':', '-'], '', $times[$i][1]) . ' +0800';
                    $this->addProgram($channelName, $start, $stop, $titles[$i-1][1]);
                }
            }
        }
    }
    
    // 2. 中華電信MOD
    private function processMOD() {
        $url = 'https://mod.cht.com.tw/channel/contentList.do';
        $data = "menuId=323&categoryId=";
        
        $response = $this->curlPost($url, $data, [], 'Mozilla/5.0 (Linux; Android 8.1.0; JKM-AL00b) AppleWebKit/537.36');
        
        if (!$response) return;
        
        $channelsData = json_decode($response);
        if (!$channelsData) return;
        
        $channelCount = count($channelsData);
        
        for ($i = 1; $i <= $channelCount - 1; $i++) {
            $channel = $channelsData[$i];
            $this->addChannel($channel->title);
        }
        
        for ($i = 1; $i <= $channelCount - 1; $i++) {
            $channel = $channelsData[$i];
            $this->fetchMODChannelPrograms($channel->link, $channel->title);
        }
    }
    
    private function fetchMODChannelPrograms($link, $channelName) {
        $dates = [$this->currentDate, $this->nextDate];
        $url = 'https://mod.cht.com.tw/channel/epg.do';
        
        $headers = [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
            "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
            "Referer: https://mod.cht.com.tw/channel/{$link}.do",
        ];
        
        foreach ($dates as $date) {
            $cacheKey = "mod_{$link}_{$date}";
            $data = $this->getCachedData($cacheKey, function() use ($url, $link, $date, $headers) {
                $postData = "contentPk={$link}&date={$date}";
                return $this->curlPost($url, $postData, $headers);
            });
            
            if ($data) {
                $data = str_replace('&', '&amp;', $data);
                preg_match_all('/programName":"(.*?)",/i', $data, $titles, PREG_SET_ORDER);
                preg_match_all('/startTime":"(.*?)",/i', $data, $startTimes, PREG_SET_ORDER);
                preg_match_all('/endTime":"(.*?)",/i', $data, $endTimes, PREG_SET_ORDER);
                
                $count = count($titles);
                for ($j = 0; $j < $count; $j++) {
                    $start = date("YmdHis", $startTimes[$j][1]) . ' +0800';
                    $stop = date("YmdHis", $endTimes[$j][1]) . ' +0800';
                    $this->addProgram($channelName, $start, $stop, $titles[$j][1]);
                }
            }
        }
    }

    // 3.1 bb寬頻
    private function processBBTV() {
        $channels = [
            ['2','bb快報'],
            ['3','公用頻道'],
            ['4','高雄都會台'],
            ['5','CNN International'],
            ['6','民視無線台'],
            ['7','人間衛視'],
            ['8','台灣電視台'],
            ['9','大愛電視台'],
            ['10','中視數位台'],
            ['11','霹靂電視台'],
            ['12','中華電視台'],
            ['13','公共電視台'],
            ['14','公視台語台'],
            ['15','好消息衛星電視台'],
            ['16','原住民族電視台'],
            ['17','客家電視台'],
            ['18','BBC EARTH'],
            ['19','Discovery'],
            ['20','TLC 旅遊生活頻道'],
            ['21','動物星球頻道'],
            ['22','Nick Jr.(小尼克)'],
            ['23','Cartoon Network'],
            ['24','MOMO親子台'],
            ['25','東森幼幼台'],
            ['26','緯來綜合台'],
            ['27','八大第一台'],
            ['28','八大綜合台'],
            ['29','三立台灣台'],
            ['30','三立都會台'],
            ['31','韓國娛樂KMTV'],
            ['32','東森綜合台'],
            ['33','超視'],
            ['34','東森購物2台'],
            ['35','momo2台'],
            ['36','中天綜合台'],
            ['37','東風衛視台'],
            ['38','MUCH TV'],
            ['39','中天娛樂台'],
            ['40','東森戲劇台'],
            ['41','八大戲劇台'],
            ['42','TVBS歡樂台'],
            ['43','緯來戲劇台'],
            ['44','高點電視台'],
            ['45','東森購物3台'],
            ['46','東森購物1台'],
            ['47','momo 1台'],
            ['48','三立財經新聞台'],
            ['49','壹電視新聞台'],
            ['50','era news 年代新聞'],
            ['51','東森新聞台'],
            ['52','華視新聞資訊台'],
            ['53','民視新聞台'],
            ['54','三立新聞台'],
            ['55','TVBS 新聞台'],
            ['56','TVBS'],
            ['57','東森財經新聞台'],
            ['58','非凡新聞台'],
            ['59','ViVa 1台'],
            ['60','東森購物5台'],
            ['61','CATCH PLAY電影台'],
            ['62','東森電影台'],
            ['63','緯來電影台'],
            ['64','LS TIME電影台'],
            ['65','HBO'],
            ['66','東森洋片台'],
            ['67','AXN'],
            ['68','好萊塢電影台'],
            ['69','AMC電影'],
            ['70','CINEMAX有線'],
            ['71','緯來育樂台'],
            ['72','緯來體育台'],
            ['73','ELEVEN SPORTS 1'],
            ['74','ELEVEN SPORTS 2'],
            ['75','MOMO綜合台'],
            ['76','緯來日本台'],
            ['77','國興衛視'],
            ['78','BBC LIFESTYLE'],
            ['79','MTV綜合電視台'],
            ['80','靖天購物一台有線'],
            ['84','ANIMAX'],
            ['82','信吉電視台'],
            ['85','寰宇新聞台'],
            ['86','鏡電視新聞台'],
            ['87','冠軍電視台'],
            ['88','JET TV'],
            ['89','非凡商業台'],
            ['90','中台灣生活網頻道'],
            ['91','八大娛樂台'],
            ['92','運通財經綜合台'],
            ['93','全球財經網頻道'],
            ['94','誠心電視台'],
            ['95','信大電視台'],
            ['96','Z頻道'],
            ['97','台灣綜合台'],
            ['98','海豚綜合台'],
            ['99','威達超舜生活台'],
            ['100','台灣藝術台'],
            ['101','華藏衛星電視台'],
            ['102','十方法界電視台'],
            ['103','世界衛星電視台'],
            ['104','佛衛電視慈悲台'],
            ['105','信大電視台'],
            ['106','NHK WORLD PREMIUM'],
            ['107','全大電視台'],
            ['108','美麗人生購物台'],
            ['109','正德電視台'],
            ['110','天良綜合台'],
            ['111','番薯衛星電視台'],
            ['112','富立電視台'],
            ['113','華藝台灣台'],
            ['114','冠軍夢想台'],
            ['131','好消息二台'],
            ['136','高點育樂台'],
            ['137','靖天綜合台靖天'],
            ['138','靖天資訊台靖天'],
            ['139','靖天日本台靖天'],
            ['140','唯心電視台'],
            ['145','GLOBLTREKKER探索世界'],
            ['146','ROCK ACTION 搖滾電影'],
            ['147','trace sport star運動明星'],
            ['148','美食星球'],
            ['149','環宇財經'],
            ['150','寰宇新聞台灣台'],
            ['151','民視第一台'],
            ['152','民視台灣台'],
            ['153','中視菁采台'],
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1]);
            $this->fetchBBTVPrograms($channel[0], $channel[1]);
        }
    }
    
    private function fetchBBTVPrograms($channelId, $channelName) {
        $url = 'https://www.homeplus.net.tw/cable/Product_introduce/digital_tv/get_channel_content';
        $postData = "so=720&channelid={$channelId}";

        $data = $this->curlPost($url, $postData, ['Referer: https://www.homeplus.net.tw/cable/product-introduce/digital-tv/digital-program-cont/210-$channelId'], 'Mozilla/5.0 (Linux; Android 8.1.0; JKM-AL00b) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Mobile Safari/537.36');
        
        if ($data) {
            $data = str_replace('&', '', $data);
            $jsonData = json_decode($data);
            
            if ($jsonData && isset($jsonData->date_program)) {
                $this->processBBTVDatePrograms($jsonData->date_program->{$this->currentDate}, $channelName, $this->dt1, $this->dt2, $this->dt22);
                $this->processBBTVDatePrograms($jsonData->date_program->{$this->nextDate}, $channelName, $this->dt2, $this->dt21, $this->dt1);
            }
        }
    }
    
    private function processBBTVDatePrograms($dateProgram, $channelName, $currentDate, $nextDate, $prevDate) {
        if (!$dateProgram) return;
        
        // 处理第一部分数据
        if (isset($dateProgram[0])) {
            foreach ($dateProgram[0] as $item) {
                $beginTime = str_replace([':', '-', ' ', '.0'], '', $item->beginTime) . '00';
                $endTime = str_replace([':', '-', ' ', '.0'], '', $item->endTime) . '00';
                
                if (str_replace(':', '', $item->beginTime) < str_replace(':', '', $item->endTime)) {
                    $start = $currentDate . $beginTime . ' +0800';
                    $stop = $currentDate . $endTime . ' +0800';
                } else {
                    $start = $prevDate . $beginTime . ' +0800';
                    $stop = $currentDate . $endTime . ' +0800';
                }
                
                $this->addProgram($channelName, $start, $stop, $item->name, $item->description);
            }
        }
        
        // 处理第二部分数据
        if (isset($dateProgram[1])) {
            foreach ($dateProgram[1] as $item) {
                $beginTime = str_replace([':', '-', ' ', '.0'], '', $item->beginTime) . '00';
                $endTime = str_replace([':', '-', ' ', '.0'], '', $item->endTime) . '00';
                
                if (str_replace(':', '', $item->beginTime) < str_replace(':', '', $item->endTime)) {
                    $start = $currentDate . $beginTime . ' +0800';
                    $stop = $currentDate . $endTime . ' +0800';
                } else {
                    $start = $currentDate . $beginTime . ' +0800';
                    $stop = $nextDate . $endTime . ' +0800';
                }
                
                $this->addProgram($channelName, $start, $stop, $item->name, $item->description);
            }
        }
    }
 
    // 3.2 bb寬頻
    private function processBBTV1() {
        $channels = [
            ['105','彰化生活台'],
            ['115','新天地民俗台'],
            ['116','三聖電視台'],
            ['117','紅豆2台'],
            ['118','天美麗電視台'],
            ['119','大立電視台'],
            ['120','雙子衛視'],
            ['121','小公視'],
            ['122','華視教育體育文化台'],
            ['123','國會頻道1台'],
            ['124','國會頻道2台'],
            ['125','幸福空間居家台'],
            ['126','亞洲旅遊台有線'],
            ['129','智林體育台有線'],
            ['130','大愛二台'],
            ['154','中視新聞台'],
            ['155','台視新聞台'],
            ['156','台視財經台'],
            ['157','台視綜合台'],
            ['160','Bloomberg Television'],
            ['161','BBC World News'],
            ['162','TV5MONDE'],
            ['163','Channel News Asia'],
            ['164','TaiwanPlus有線'],
            ['168','LOVE NATURE有線'],
            ['200','CI罪案偵查頻道有線'],
            ['201','lifetime娛樂頻道有線'],
            ['202','Asian Food Network 亞洲美食頻道'],
            ['203','梅迪奇藝術頻道有線'],
            ['204','Discovery Asia有線'],
            ['205','Discovery科學頻道有線'],
            ['206','DMAX有線'],
            ['207','EVE有線'],
            ['209','博斯運動一台有線'],
            ['211','HITS有線'],
            ['212','History 歷史頻道有線'],
            ['213','PET CLUB TV寵物頻道有線'],
            ['214','滾動力 rollor有線'],
            ['215','cinemalworld世界電影頻道有線'],
            ['216','My Cinema Europe HD 我的歐洲電影有線'],
            ['217','HBO HD'],
            ['218','HBO Signature 原創鉅獻'],
            ['219','HBO Hits 強檔鉅獻'],
            ['220','HBO Family'],
            ['221','tvN有線'],
            ['222','華藝MBC綜合台有線'],
            ['223','靖天映畫有線'],
            ['224','靖天電影有線'],
            ['225','ROCK ENTERTAINMENT'],
            ['226','ROCK EXTREME'],
            ['227','CMusic'],
            ['229','Warner TV'],
            ['230','MTV Live音樂頻道'],
            ['234','靖洋卡通有線'],
            ['235','Nickelodeon Asia尼克兒童頻道'],
            ['236','CARTOONITO'],
            ['237','Cbeebies'],
            ['239','Eurosport'],
            ['242','博斯運動二台有線'],
            ['243','博斯高球一台有線'],
            ['244','博斯高球二台有線'],
            ['245','博斯魅力網有線'],
            ['246','博斯網球台有線'],
            ['247','博斯無限台有線'],
            ['248','博斯無限二台有線'],
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1]);
            $this->fetchBBTVPrograms1($channel[0], $channel[1]);
        }
    }
    
    private function fetchBBTVPrograms1($channelId, $channelName) {
        $url = 'https://www.homeplus.net.tw/cable/Product_introduce/digital_tv/get_channel_content';
        $postData = "so=209&channelid={$channelId}";
        
        $data = $this->curlPost($url, $postData, ['Content-Type: application/x-www-form-urlencoded; charset=UTF-8', 'Referer: https://www.homeplus.net.tw/cable/product-introduce/digital-tv/digital-program-cont/210-$channelId'], 'Mozilla/5.0 (Linux; Android 8.1.0; JKM-AL00b) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Mobile Safari/537.36');
        
        if ($data) {
            $data = str_replace('&', '', $data);
            $jsonData = json_decode($data);
            
            if ($jsonData && isset($jsonData->date_program)) {
                $this->processBBTVDatePrograms1($jsonData->date_program->{$this->currentDate}, $channelName, $this->dt1, $this->dt2, $this->dt22);
                $this->processBBTVDatePrograms1($jsonData->date_program->{$this->nextDate}, $channelName, $this->dt2, $this->dt21, $this->dt1);
            }
        }
    }
    
    private function processBBTVDatePrograms1($dateProgram, $channelName, $currentDate, $nextDate, $prevDate) {
        if (!$dateProgram) return;
        
        // 处理第一部分数据
        if (isset($dateProgram[0])) {
            foreach ($dateProgram[0] as $item) {
                $beginTime = str_replace([':', '-', ' ', '.0'], '', $item->beginTime) . '00';
                $endTime = str_replace([':', '-', ' ', '.0'], '', $item->endTime) . '00';
                
                if (str_replace(':', '', $item->beginTime) < str_replace(':', '', $item->endTime)) {
                    $start = $currentDate . $beginTime . ' +0800';
                    $stop = $currentDate . $endTime . ' +0800';
                } else {
                    $start = $prevDate . $beginTime . ' +0800';
                    $stop = $currentDate . $endTime . ' +0800';
                }
                
                $this->addProgram($channelName, $start, $stop, $item->name, $item->description);
            }
        }
        
        // 处理第二部分数据
        if (isset($dateProgram[1])) {
            foreach ($dateProgram[1] as $item) {
                $beginTime = str_replace([':', '-', ' ', '.0'], '', $item->beginTime) . '00';
                $endTime = str_replace([':', '-', ' ', '.0'], '', $item->endTime) . '00';
                
                if (str_replace(':', '', $item->beginTime) < str_replace(':', '', $item->endTime)) {
                    $start = $currentDate . $beginTime . ' +0800';
                    $stop = $currentDate . $endTime . ' +0800';
                } else {
                    $start = $currentDate . $beginTime . ' +0800';
                    $stop = $nextDate . $endTime . ' +0800';
                }
                
                $this->addProgram($channelName, $start, $stop, $item->name, $item->description);
            }
        }
    }

    // 3.3 bb寬頻
    private function processBBTV2() {
        $channels = [
            ['301','彩虹頻道'],
            ['302','松視4台'],
            ['303','Pandora潘朵啦高畫質玩美台'],
            ['304','Pandora潘朵啦高畫質粉紅台'],
            ['305','松視1台'],
            ['306','松視2台'],
            ['307','松視3台'],
            ['308','彩虹E台'],
            ['309','彩虹Movie台'],
            ['310','K頻道'],
            ['311','HOT頻道'],
            ['312','HAPPY頻道'],
            ['313','玩家頻道'],
            ['314','驚豔成人電影台'],
            ['315','香蕉台'],
            ['316','樂活頻道'],
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1]);
            $this->fetchBBTVPrograms2($channel[0], $channel[1]);
        }
    }
    
    private function fetchBBTVPrograms2($channelId, $channelName) {
        $url = 'https://www.homeplus.net.tw/cable/Product_introduce/digital_tv/get_channel_content';
        $postData = "so=250&channelid={$channelId}";
        
        $data = $this->curlPost($url, $postData, ['Content-Type: application/x-www-form-urlencoded; charset=UTF-8', 'Referer: https://www.homeplus.net.tw/cable/product-introduce/digital-tv/digital-program-cont/250-$channelId'], 'Mozilla/5.0 (Linux; Android 8.1.0; JKM-AL00b) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Mobile Safari/537.36');
        
        if ($data) {
            $data = str_replace('&', '', $data);
            $jsonData = json_decode($data);
            
            if ($jsonData && isset($jsonData->date_program)) {
                $this->processBBTVDatePrograms2($jsonData->date_program->{$this->currentDate}, $channelName, $this->dt1, $this->dt2, $this->dt22);
                $this->processBBTVDatePrograms2($jsonData->date_program->{$this->nextDate}, $channelName, $this->dt2, $this->dt21, $this->dt1);
            }
        }
    }
    
    private function processBBTVDatePrograms2($dateProgram, $channelName, $currentDate, $nextDate, $prevDate) {
        if (!$dateProgram) return;
        
        // 处理第一部分数据
        if (isset($dateProgram[0])) {
            foreach ($dateProgram[0] as $item) {
                $beginTime = str_replace([':', '-', ' ', '.0'], '', $item->beginTime) . '00';
                $endTime = str_replace([':', '-', ' ', '.0'], '', $item->endTime) . '00';
                
                if (str_replace(':', '', $item->beginTime) < str_replace(':', '', $item->endTime)) {
                    $start = $currentDate . $beginTime . ' +0800';
                    $stop = $currentDate . $endTime . ' +0800';
                } else {
                    $start = $prevDate . $beginTime . ' +0800';
                    $stop = $currentDate . $endTime . ' +0800';
                }
                
                $this->addProgram($channelName, $start, $stop, $item->name, $item->description);
            }
        }
        
        // 处理第二部分数据
        if (isset($dateProgram[1])) {
            foreach ($dateProgram[1] as $item) {
                $beginTime = str_replace([':', '-', ' ', '.0'], '', $item->beginTime) . '00';
                $endTime = str_replace([':', '-', ' ', '.0'], '', $item->endTime) . '00';
                
                if (str_replace(':', '', $item->beginTime) < str_replace(':', '', $item->endTime)) {
                    $start = $currentDate . $beginTime . ' +0800';
                    $stop = $currentDate . $endTime . ' +0800';
                } else {
                    $start = $currentDate . $beginTime . ' +0800';
                    $stop = $nextDate . $endTime . ' +0800';
                }
                
                $this->addProgram($channelName, $start, $stop, $item->name, $item->description);
            }
        }
    }

    // 4. 凱擰有線
    private function processKbro() {
        $channels = [
            ['912','JStar極限台電影頻道'],
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1]);
            $this->fetchKbroPrograms($channel[0], $channel[1]);
        }
    }
    
    private function fetchKbroPrograms($channelId, $channelName) {
        $dates = [$this->dt1, $this->dt2];
        
        foreach ($dates as $date) {
            $url = "https://www.kbro.com.tw/do/getpage_catvtable.php?callback=result&action=get_channelprogram&channelid={$channelId}&showtime={$date}";
            $data = $this->curlGet($url);
            
            if ($data) {
                $data = str_replace('&', '&amp;', $data);
                $data = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', [$this, 'replace_unicode_escape_sequence'], $data);
                
                preg_match_all('|"programname":"(.*?)","playdate|i', $data, $titles, PREG_SET_ORDER);
                preg_match_all('|"starttime":"(.*?)","endtime|i', $data, $startTimes, PREG_SET_ORDER);
                preg_match_all('|"endtime":"(.*?)","eventid|i', $data, $endTimes, PREG_SET_ORDER);
                
                $count = count($titles);
                for ($i = 0; $i < $count; $i++) {
                    $start = str_replace([' ', '-', ':'], '', $startTimes[$i][1]) . ' +0800';
                    $stop = str_replace([' ', '-', ':'], '', $endTimes[$i][1]) . ' +0800';
                    $title = str_replace('<spanclass="live-btn">播放中</span>', '', $titles[$i][1]);
                    $this->addProgram($channelName, $start, $stop, $title);
                }
            }
        }
    }
    
    //5.0 Hami Video
    private function processHami() {
        $url = 'https://hamivideo.hinet.net/%E9%9B%BB%E8%A6%96%E9%81%8B%E5%8B%95%E9%A4%A8/%E5%85%A8%E9%83%A8.do';
        $data = $this->curlGet($url, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        ]);
        
        if (!$data) {
            error_log("获取Hami频道列表失败");
            return;
        }
        
        // 清理HTML
        $data = preg_replace('/\s(?=)/', '', $data);
        
        // 解析频道数据
        preg_match('/\<h2\>頻道一覽\<\/h2\>(.*?)\<script\>/', $data, $rk);
        if (!isset($rk[1])) {
            error_log("无法解析Hami频道列表");
            return;
        }
        
        preg_match_all('/<ahref\="\/channel\/OTT_LIVE_000000(.*?).do"onclick/i', $rk[1], $channelIds, PREG_SET_ORDER);
        preg_match_all('/alt\="(.*?)"><\/a>/i', $rk[1], $channelNames, PREG_SET_ORDER);
        
        $channelCount = count($channelNames);
        
        // 添加频道到XML
        for ($i = 0; $i < $channelCount; $i++) {
            $this->addChannel($channelNames[$i][1]);
        }
        
        // 获取每个频道的节目表
        for ($i = 0; $i < $channelCount; $i++) {
            $this->fetchHamiChannelPrograms($channelIds[$i][1], $channelNames[$i][1]);
        }
    }
    
    private function fetchHamiChannelPrograms($channelId, $channelName) {
        $url = 'https://hamivideo.hinet.net/channel/epg.do';
        
        // 获取当天节目
        $postData1 = "contentPk=OTT_LIVE_000000{$channelId}&date={$this->currentDate}";
        $data1 = $this->curlPost($url, $postData1, [], 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36');
        
        if ($data1) {
            $data1 = str_replace('&', '&amp;', $data1);
            preg_match_all('/programName":"(.*?)","startTime/i', $data1, $titles1, PREG_SET_ORDER);
            preg_match_all('/startTime":"(.*?)","endTime/i', $data1, $startTimes1, PREG_SET_ORDER);
            preg_match_all('/endTime":"(.*?)","tsId/i', $data1, $endTimes1, PREG_SET_ORDER);
            
            $count1 = count($titles1);
            for ($j = 0; $j < $count1; $j++) {
                $start = str_replace([' ', ':', '-'], '', date("Y-m-d H:i:s", $startTimes1[$j][1])) . ' +0800';
                $stop = str_replace([' ', ':', '-'], '', date("Y-m-d H:i:s", $endTimes1[$j][1])) . ' +0800';
                $this->addProgram($channelName, $start, $stop, $titles1[$j][1]);
            }
        }
        
        // 获取次日节目
        $postData2 = "contentPk=OTT_LIVE_000000{$channelId}&date={$this->nextDate}";
        $data2 = $this->curlPost($url, $postData2, [], 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36');
        
        if ($data2) {
            $data2 = str_replace('&', '&amp;', $data2);
            preg_match_all('/programName":"(.*?)","startTime/i', $data2, $titles2, PREG_SET_ORDER);
            preg_match_all('/startTime":"(.*?)","endTime/i', $data2, $startTimes2, PREG_SET_ORDER);
            preg_match_all('/endTime":"(.*?)","tsId/i', $data2, $endTimes2, PREG_SET_ORDER);
            
            $count2 = count($titles2);
            for ($j = 0; $j < $count2; $j++) {
                $start = str_replace([' ', ':', '-'], '', date("Y-m-d H:i:s", $startTimes2[$j][1])) . ' +0800';
                $stop = str_replace([' ', ':', '-'], '', date("Y-m-d H:i:s", $endTimes2[$j][1])) . ' +0800';
                $this->addProgram($channelName, $start, $stop, $titles2[$j][1]);
            }
        }
    }

    // 6. 4GTV
    private function process4GTV() {
        $url = 'https://api2.4gtv.tv/Channel/GetChannelBySetId/1/pc/L';
        $data = $this->curlGet($url);
        
        if ($data) {
            $data = str_replace('&', '', $data);
            $jsonData = json_decode($data);
            
            if ($jsonData && isset($jsonData->Data)) {
                foreach ($jsonData->Data as $channel) {
                    $this->addChannel($channel->fsNAME);
                    $this->fetch4GTVPrograms($channel->fs4GTV_ID, $channel->fsNAME);
                }
            }
        }
    }
    
    private function fetch4GTVPrograms($channelId, $channelName) {
        $url = "https://www.4gtv.tv/proglist/{$channelId}.txt";
        $data = $this->curlGet($url);
        
        if ($data) {
            $data = str_replace('&', '', $data);
            $programs = json_decode($data, true);
            
            if ($programs) {
                foreach ($programs as $program) {
                    $start = str_replace(['-', ':'], '', $program['sdate']) . str_replace(':', '', $program['stime']) . ' +0800';
                    $stop = str_replace(['-', ':'], '', $program['edate']) . str_replace(':', '', $program['etime']) . ' +0800';
                    $this->addProgram($channelName, $start, $stop, $program['title']);
                }
            }
        }
    }
    
    // 7. Ofiii
    private function processOfiii() {
        // 获取buildId
        $buildId = $this->getOfiiiBuildId();
        if (!$buildId) return;
        
        $channels = [
            ['4gtv-4gtv040', '中視'],
            ['4gtv-4gtv041', '華視'],
            ['litv-ftv17', '好消息2台'],
            ['litv-ftv16', '好消息'],
            ['litv-longturn20', 'ELTV生活英语台'],
            ['litv-longturn01', '龍華卡通台'],
            ['4gtv-4gtv076', '亞洲旅遊台'],
            ['litv-longturn19', 'Smart知識台'],
            ['litv-longturn18', '龍華戲劇台'],
            ['litv-longturn22', '台湾戲劇台'],
            ['4gtv-4gtv102', '東森购物1台'],
            ['litv-longturn12', '龍華偶像台'],
            ['litv-longturn11', '龍華日韩台'],
            ['4gtv-4gtv103', '東森购物2台'],
            ['4gtv-4gtv052', '華视新闻'],
            ['nnews-zh', '倪珍播新聞'],
            ['iNEWS', '三立新闻iNEWS'],
            ['4gtv-4gtv158', '寰宇財經台'],
            ['4gtv-4gtv074', '中視新聞'],
            ['4gtv-4gtv009', '中天新聞台'],
            ['4gtv-4gtv156', '寰宇新聞台'],
            ['4gtv-longturn14', '寰宇新聞台'],
            ['4gtv-4gtv104', '第1商業台'],
            ['4gtv-4gtv084', '國會頻道1台'],
            ['4gtv-4gtv085', '國會頻道2台'],
            ['litv-longturn21', '龍華經典台'],
            ['litv-longturn03', '龍華電影院'],
            ['litv-longturn02', '龍華洋片台OTT'],
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1]);
            $this->fetchOfiiiPrograms($channel[0], $channel[1], $buildId);
        }
    }
    
    private function getOfiiiBuildId() {
        $url = 'https://www.ofiii.com/channel/watch/4gtv-4gtv156';
        $data = $this->curlGet($url, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 Edg/114.0.1823.86'
        ]);
        
        if ($data && preg_match('/"buildId":"(.*)",/i', $data, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    private function fetchOfiiiPrograms($channelId, $channelName, $buildId) {
        $url = "https://www.ofiii.com/_next/data/{$buildId}/channel/watch/{$channelId}.json?contentId={$channelId}";
        $data = $this->curlGet($url, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 Edg/114.0.1823.86'
        ]);
        
        if ($data) {
            $data = str_replace('&', '', $data);
            $data = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', [$this, 'replace_unicode_escape_sequence'], $data);
            
            $jsonData = json_decode($data);
            if ($jsonData && isset($jsonData->pageProps->channel->Schedule)) {
                $schedule = $jsonData->pageProps->channel->Schedule;
                $count = count($schedule);
                
                for ($i = 0; $i < $count - 1; $i++) {
                    $start = str_replace(['-', ':', 'T', 'Z'], '', $schedule[$i]->AirDateTime) . ' +0000';
                    $stop = str_replace(['-', ':', 'T', 'Z'], '', $schedule[$i+1]->AirDateTime) . ' +0000';
                    $title = str_replace(['<', '>'], '', $schedule[$i]->program->Title);
                    $desc = str_replace(['<', '>'], '', $schedule[$i]->program->Description);
                    $this->addProgram($channelName, $start, $stop, $title, $desc);
                }
            }
        }
    }
    
    // 8. 咪咕体育
    private function processMiguSports() {
        $this->addChannel('咪咕体育');
        $url = 'https://vms-sc.miguvideo.com/vms-match/v6/staticcache/basic/match-list/normal-match-list/0/all/default/1/miguvideo';
        $data = $this->curlGet($url, ['User-Agent: Mozilla/5.0']);
        
        if ($data) {
            $data = str_replace('&', '&amp;', $data);
            $jsonData = json_decode($data);
            
            if ($jsonData && isset($jsonData->body->matchList)) {
                $this->processMiguMatchList($jsonData->body->matchList->{$this->dt1}, $this->dt1);
                $this->processMiguMatchList($jsonData->body->matchList->{$this->dt2}, $this->dt2);
            }
        }
    }
    
    private function processMiguMatchList($matchList, $date) {
        if (!$matchList) return;
        
        foreach ($matchList as $match) {
            $start = date("YmdHis", $match->startTime / 1000) . ' +0800';
            $stop = date("YmdHis", $match->endTime / 1000) . ' +0800';
            $title = $match->title . $match->pkInfoTitle;
            $this->addProgram('咪咕体育', $start, $stop, $title, '/');
        }
    }
    
    // 9. 奥林匹克官网直播
    private function processOlympic() {
        $this->addChannel('奥林匹克官网直播');
        $this->fetchOlympicPrograms($this->currentDate);
        $this->fetchOlympicPrograms($this->nextDate);
    }
    
    private function fetchOlympicPrograms($date) {
        $url = "https://www.olympics.com/zh/api/v1/live/video/{$date}/octv/epglist?channelid=OCTV";
        $data = $this->curlGet($url, [
            'Host: www.olympics.com',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0',
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'Referer: https://www.olympics.com/zh/live/',
        ]);
        
        if ($data) {
            $data = str_replace('&', '&amp;', $data);
            preg_match_all('|"startTime":"(.*?)"|i', $data, $startTimes, PREG_SET_ORDER);
            preg_match_all('|"endTime":"(.*?)"|i', $data, $endTimes, PREG_SET_ORDER);
            preg_match_all('|"title":"(.*?)",|i', $data, $titles, PREG_SET_ORDER);
            
            $count = count($startTimes);
            for ($i = 1; $i <= $count - 1; $i++) {
                $start = str_replace(['T', '-', ':'], '', $startTimes[$i-1][1]) . ' +0000';
                $stop = str_replace(['T', '-', ':'], '', $startTimes[$i][1]) . ' +0000';
                $title = str_replace(['|', '-'], '', $titles[($i-1)*2][1]);
                $this->addProgram('奥林匹克官网直播', $start, $stop, $title);
            }
            
            if ($count > 0) {
                $start = str_replace(['T', '-', ':'], '', $startTimes[$count-1][1]) . ' +0000';
                $stop = str_replace(['T', '-', ':'], '', $endTimes[$count-1][1]) . ' +0000';
                $title = str_replace(['|', '-'], '', $titles[($count-1)*2][1]);
                $this->addProgram('奥林匹克官网直播', $start, $stop, $title);
            }
        }
    }
    
    // 10. 喀秋莎
    private function processKatyusha() {
        $this->addChannel('喀秋莎');
        $this->fetchKatyushaPrograms();
        $this->fetchKatyushaPrograms($this->nextDate);
    }
    
    private function fetchKatyushaPrograms($date = null) {
        $url = $date ? "https://www.katyusha.tv/zh-hans/grid?date={$date}" : "https://www.katyusha.tv/zh-hans/grid";
        $data = $this->curlGet($url, [
            'Host: www.katyusha.tv',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.114 Safari/537.36 Edg/103.0.1264.49',
            'Referer: https://www.katyusha.tv/zh-hans/grid',
        ]);
        
        if ($data) {
            $data = str_replace('&', '&amp;', $data);
            $data = preg_replace('/\s(?=)/', '', $data);
            $data = str_replace(':', '', $data);
            
            preg_match_all('|<divclass="broadcast-media_time">(.*?)</div>|i', $data, $startTimes, PREG_SET_ORDER);
            preg_match_all('|<h4class="mt-0mb-2broadcast-media_title">(.*?)</h4>|i', $data, $titles, PREG_SET_ORDER);
            preg_match_all('|<pclass="broadcast-media_text">(.*?)</p>|i', $data, $descriptions, PREG_SET_ORDER);
            
            $count = count($startTimes);
            for ($i = 0; $i < $count - 1; $i++) {
                $currentTime = $startTimes[$i][1];
                $nextTime = $startTimes[$i+1][1];
                
                if ($currentTime < 600 && $nextTime > 0) {
                    $startDate = $this->dt2;
                    $stopDate = $this->dt2;
                } elseif ($currentTime >= 600 && $currentTime < 1000) {
                    $startDate = $this->dt1;
                    $stopDate = $this->dt1;
                } elseif ($nextTime >= 1000) {
                    if ($currentTime < 1000) {
                        $startDate = $this->dt1;
                        $stopDate = $this->dt1;
                    } else {
                        $startDate = $this->dt1;
                        $stopDate = ($currentTime < $nextTime) ? $this->dt1 : $this->dt2;
                    }
                } else {
                    continue;
                }
                
                $start = $startDate . sprintf("%04d", $currentTime) . '00 +0800';
                $stop = $stopDate . sprintf("%04d", $nextTime) . '00 +0800';
                $this->addProgram('喀秋莎', $start, $stop, $titles[$i][1], $descriptions[$i][1]);
            }
            
            // 最后一个节目
            if ($count > 0) {
                $lastTime = $startTimes[$count-1][1];
                $start = $this->dt2 . sprintf("%04d", $lastTime) . '00 +0800';
                $stop = $this->dt2 . '062000 +0800';
                $this->addProgram('喀秋莎', $start, $stop, $titles[$count-1][1], $descriptions[$count-1][1]);
            }
        }
    }
 
    //11 ================ KBS 处理函数 ================
    private function processKBS() {
        $channels = [
            // 主频道
            ['11','KBS 1'],
            ['12','KBS 2'],
            ['14','KBS WORLD'],
            ['81','KBS NEWS'],
            
            // 子频道
            ['N91','KBS DRAMA'],
            ['N92','KBS JOY'],
            ['N94','KBS STORY'],
            ['N93','KBS LIFE'],
            ['N96','KBS KIDS'],
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1]);
            $this->fetchKBSPrograms($channel[0], $channel[1]);
        }
    }
    
    private function fetchKBSPrograms($channelId, $channelName) {
        $dates = [$this->dt1, $this->dt2];
        
        foreach ($dates as $date) {
            // 使用API URL
            $url = "https://static.api.kbs.co.kr/mediafactory/v1/schedule/weekly?rtype=jsonp&local_station_code=00&channel_code={$channelId}&program_planned_date_from={$date}&program_planned_date_to={$date}&callback=weekly_schedule";
            
            $data = $this->curlGet($url);
            
            if ($data) {
                // 移除JSONP包装器
                $data = preg_replace('/^weekly_schedule\(/', '', $data);
                $data = preg_replace('/\);?$/', '', $data);
                
                // 解码JSON数据
                $jsonData = json_decode($data, true);
                
                if ($jsonData && is_array($jsonData)) {
                    foreach ($jsonData as $dayData) {
                        if (isset($dayData['schedules']) && is_array($dayData['schedules'])) {
                            foreach ($dayData['schedules'] as $schedule) {
                                $this->processKBSProgram($schedule, $channelName);
                            }
                        }
                    }
                } else {
                    // 备用方案：尝试正则匹配
                    $this->parseKBSDataWithRegex($data, $channelName, $date);
                }
            }
        }
    }
    
    private function processKBSProgram($schedule, $channelName) {
        // 提取节目信息
        $title = isset($schedule['program_title']) ? $schedule['program_title'] : '未知节目';
        $serviceDate = isset($schedule['service_date']) ? $schedule['service_date'] : '';
        $startTime = isset($schedule['service_start_time']) ? $schedule['service_start_time'] : '';
        $endTime = isset($schedule['service_end_time']) ? $schedule['service_end_time'] : '';
        
        if (!$serviceDate || !$startTime || !$endTime) {
            return;
        }
        
        // 处理时间格式（HHmmssff -> HHmmss）
        $startTimeFormatted = substr($startTime, 0, 6);
        $endTimeFormatted = substr($endTime, 0, 6);
        
        // 检查是否跨天
        $endDate = $serviceDate;
        if ($endTimeFormatted < $startTimeFormatted) {
            // 结束时间小于开始时间，说明跨天了
            $endDate = date('Ymd', strtotime($serviceDate . ' +1 day'));
        }
        
        $start = $serviceDate . $startTimeFormatted . ' +0900';
        $stop = $endDate . $endTimeFormatted . ' +0900';
        
        $this->addProgram($channelName, $start, $stop, $title);
    }
    
    private function parseKBSDataWithRegex($data, $channelName, $date) {
        // 使用正则表达式提取数据（备用方法）
        preg_match_all('/"program_title":"([^"]*)",.*?"service_date":"([^"]*)",.*?"service_start_time":"([^"]*)",.*?"service_end_time":"([^"]*)"/', $data, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $title = $match[1];
            $serviceDate = $match[2];
            $startTime = $match[3];
            $endTime = $match[4];
            
            // 处理时间格式
            $startTimeFormatted = substr($startTime, 0, 6);
            $endTimeFormatted = substr($endTime, 0, 6);
            
            // 检查是否跨天
            $endDate = $serviceDate;
            if ($endTimeFormatted < $startTimeFormatted) {
                $endDate = date('Ymd', strtotime($serviceDate . ' +1 day'));
            }
            
            $start = $serviceDate . $startTimeFormatted . ' +0900';
            $stop = $endDate . $endTimeFormatted . ' +0900';
            
            $this->addProgram($channelName, $start, $stop, $title);
        }
    }
    
    //12 ================ MBC 处理函数 ================
    private function processMBC() {
        $channels = [
            ['MBC', 'MBC'],
            ['MBC every1', 'MBC every1'],
            ['MBC drama', 'MBC drama'],
            ['MBC music', 'MBC music'],
            ['MBC on', 'MBC on'],
            ['MBC net', 'MBC net'],
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[0]);
            $this->fetchMBCPrograms($channel[0], $channel[1]);
        }
    }
    
    private function fetchMBCPrograms($channelName, $channelType) {
        $dates = [$this->dt1, $this->dt2];
        
        foreach ($dates as $date) {
            if ($channelName === 'MBC') {
                $url = "https://control.imbc.com/Schedule/TV?callback=Schedule_TV_{$date}&sDate={$date}&sType=ALL";
            } else {
                // 其他MBC频道
                $typeMap = [
                    'MBC every1' => 'P_everyone',
                    'MBC drama' => 'P_drama',
                    'MBC music' => 'P_music',
                    'MBC on' => 'P_on',
                    'MBC net' => 'MBCNET',
                ];
                
                $sType = isset($typeMap[$channelName]) ? $typeMap[$channelName] : 'ALL';
                $url = "https://control.imbc.com/Schedule/MBCPlus?callback=MBCPlus{$date}_{$sType}&sDate={$date}&sType={$sType}";
            }
            
            $data = $this->curlGet($url);
            
            if ($data) {
                $data = str_replace(['&', '<', '>'], ['&amp;', '', ''], $data);
                
                preg_match_all('/"StartTime": "(.*?)",/', $data, $startTimes, PREG_SET_ORDER);
                preg_match_all('/"EndTime": "(.*?)",/', $data, $endTimes, PREG_SET_ORDER);
                
                if ($channelName === 'MBC') {
                    preg_match_all('/"Title": "(.*?)",/', $data, $titles, PREG_SET_ORDER);
                } else {
                    preg_match_all('/"ProgramTitle": "(.*?)",/', $data, $titles, PREG_SET_ORDER);
                }
                
                $count = count($startTimes);
                for ($i = 0; $i < $count; $i++) {
                    $startTime = $this->adjustTimeFormat($startTimes[$i][1]);
                    $endTime = $this->adjustTimeFormat($endTimes[$i][1]);
                    
                    // 解析时间
                    $startDateTime = $this->parseTimeToDateTime($date, $startTime);
                    $endDateTime = $this->parseTimeToDateTime($date, $endTime);
                    
                    // 确保结束时间在开始时间之后
                    if ($endDateTime <= $startDateTime) {
                        $endDateTime->modify('+1 day');
                    }
                    
                    $this->addProgram($channelName, 
                        $startDateTime->format('YmdHis') . ' +0900',
                        $endDateTime->format('YmdHis') . ' +0900',
                        $titles[$i][1]
                    );
                }
            }
        }
    }
    
    //13================ SBS 处理函数 ================
    private function processSBS() {
        $channels = [
            ['SBS', 'SBS'],
            ['SBS plus', 'SBS plus'],
            ['SBS funE', 'SBS fune'],
            ['SBS life', 'SBS Fil'],
            ['SBS sport', 'SBS sport'],
            ['SBS Golf', 'SBS Golf'],
            ['SBS Golf2', 'SBS Golf2'],
            ['SBS Biz', 'SBS Biz'],
        ];
        
        $channelMap = [
            'SBS' => 'SBS',
            'SBS plus' => 'Plus',
            'SBS funE' => 'ETV',
            'SBS life' => 'Fil',
            'SBS sport' => 'ESPN',
            'SBS Golf' => 'Golf',
            'SBS Golf2' => 'Golf2',
            'SBS Biz' => 'CNBC',
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[0]);
            $channelCode = $channelMap[$channel[0]];
            //https://static.cloud.sbs.co.kr/schedule/2025/12/10/Fil.json?_=1765268876492
            // 第一天
            $url1 = "https://static.cloud.sbs.co.kr/schedule/" . date("Y/n/j") . "/{$channelCode}.json?_=";
            $data1 = $this->curlGet($url1);
            
            if ($data1) {
                preg_match_all('/"start_time":"(.*?)",/', $data1, $startTimes1, PREG_SET_ORDER);
                preg_match_all('/"title":"(.*?)",/', $data1, $titles1, PREG_SET_ORDER);
                
                $count1 = count($startTimes1);
                for ($i = 0; $i < $count1 - 1; $i++) {
                    $startTime = $this->normalizeTime($this->dt1, $startTimes1[$i][1]);
                    $stopTime = $this->normalizeTime($this->dt1, $startTimes1[$i+1][1]);
                    
                    $this->addProgram($channel[0], $startTime . ' +0900', $stopTime . ' +0900', $titles1[$i][1]);
                }
            }
            
            // 第二天
            $url2 = "https://static.cloud.sbs.co.kr/schedule/" . date("Y/n/j", time() + 24 * 3600) . "/{$channelCode}.json?_=";
            $data2 = $this->curlGet($url2);
            
            if ($data2) {
                preg_match_all('/"start_time":"(.*?)",/', $data2, $startTimes2, PREG_SET_ORDER);
                preg_match_all('/"title":"(.*?)",/', $data2, $titles2, PREG_SET_ORDER);
                
                $count2 = count($startTimes2);
                for ($i = 0; $i < $count2 - 1; $i++) {
                    $startTime = $this->normalizeTime($this->dt2, $startTimes2[$i][1]);
                    $stopTime = $this->normalizeTime($this->dt2, $startTimes2[$i+1][1]);
                    
                    $this->addProgram($channel[0], $startTime . ' +0900', $stopTime . ' +0900', $titles2[$i][1]);
                }
            }
        }
    }

    //14 ================ EBS 处理函数 (从epgebs.php整合) ================
    private function processEBS() {
        $channels = [
            ['tv', 'EBS1'],
            // 如果需要其他EBS频道，可以取消注释下面的行
         
            ['tv2', 'EBS2'],
            ['EBSU', 'EBS CHILD'],
            ['PLUS1', 'EBS PLUS1'],
            ['PLUS2', 'EBS PLUS2'],
            ['EBSE', 'EBS EDUCATION'],
          
        ];
        
        foreach ($channels as $channel) {
            $this->addChannel($channel[1]);
            $this->fetchEBSPrograms($channel[0], $channel[1]);
        }
    }
    
    private function fetchEBSPrograms($channelCode, $channelName) {
        // 获取当天节目
        $day1Data = $this->fetchEBSDayData($channelCode, $this->dt1);
        // 获取第二天节目
        $day2Data = $this->fetchEBSDayData($channelCode, $this->dt2);
        
        // 处理当天节目（除了最后一个）
        $count1 = count($day1Data['startTimes']);
        for ($i = 0; $i <= $count1 - 2; $i++) {
            $startFull = $this->formatEBSDateTime($this->dt7 . preg_replace('/[^0-9]/', '', $day1Data['startTimes'][$i][1]) . '00');
            $stopFull = $this->formatEBSDateTime($this->dt7 . preg_replace('/[^0-9]/', '', $day1Data['startTimes'][$i+1][1]) . '00');
            
            $this->addProgram($channelName, $startFull . ' +0900', $stopFull . ' +0900', 
                $day1Data['titles'][$i][1], $day1Data['descs'][$i][1]);
        }
        
        // 衔接节目：当天最后一个到第二天第一个
        if ($count1 > 0) {
            $lastStartFull = $this->formatEBSDateTime($this->dt7 . preg_replace('/[^0-9]/', '', $day1Data['startTimes'][$count1 - 1][1]) . '00');
            $firstStopFull = $this->formatEBSDateTime($this->dt7 . preg_replace('/[^0-9]/', '', $day2Data['startTimes'][0][1]) . '00');
            
            $this->addProgram($channelName, $lastStartFull . ' +0900', $firstStopFull . ' +0900',
                $day1Data['titles'][$count1 - 1][1], $day1Data['descs'][$count1 - 1][1]);
        }
        
        // 处理第二天节目（除了最后一个）
        $count2 = count($day2Data['startTimes']);
        for ($i = 0; $i <= $count2 - 2; $i++) {
            $startFull = $this->formatEBSDateTime($this->dt7 . preg_replace('/[^0-9]/', '', $day2Data['startTimes'][$i][1]) . '00');
            $stopFull = $this->formatEBSDateTime($this->dt7 . preg_replace('/[^0-9]/', '', $day2Data['startTimes'][$i+1][1]) . '00');
            
            $this->addProgram($channelName, $startFull . ' +0900', $stopFull . ' +0900',
                $day2Data['titles'][$i][1], $day2Data['descs'][$i][1]);
        }
    }
    
    private function fetchEBSDayData($channelCode, $date) {
        $url = "https://www.ebs.co.kr/schedule?channelCd={$channelCode}&date={$date}";
        
        $data = $this->curlGet($url, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 Edg/114.0.1823.86',
            "Referer: https://www.ebs.co.kr/schedule?channelCd={$channelCode}&onor={$channelCode}",
        ]);
        
        if (!$data) {
            return ['startTimes' => [], 'titles' => [], 'descs' => []];
        }
        
        // 清理数据 - 移除韩语星期字符
        $data = str_replace('&', '', $data);
        $data = str_replace(['월','목','금','토'], '', $data); // 移除韩语星期字符
        $data = preg_replace('/\s(?=)/', '', $data);
        
        // 解析数据
        preg_match_all('|<pclass="date">(.*?)</p>|i', $data, $startTimes, PREG_SET_ORDER);
        preg_match_all('|<h4>(.*?)</h4>|i', $data, $titles, PREG_SET_ORDER);
        preg_match_all('|<pclass="tit"><ahref="#">(.*?)</a></p>|i', $data, $descs, PREG_SET_ORDER);
        
        return [
            'startTimes' => $startTimes,
            'titles' => $titles,
            'descs' => $descs
        ];
    }
    
    private function formatEBSDateTime($dateStr) {
        // 处理时间超过24小时的情况
        $datePart = substr($dateStr, 0, 8);  // Ymd
        $hourPart = substr($dateStr, 8, 2);  // HH
        $restPart = substr($dateStr, 10, 4); // MMSS
        
        $hour = intval($hourPart);
        
        // 如果小时小于24，直接返回
        if ($hour < 24) {
            return $dateStr;
        }
        
        // 如果小时大于等于24，需要调整
        $newHour = $hour - 24;
        $newDate = date('Ymd', strtotime($datePart . ' +1 day'));
        
        return $newDate . sprintf("%02d", $newHour) . $restPart;
    }

    // ================ 通用辅助方法 ================
    private function getCachedData($key, $callback) {
        if (!$this->cacheEnabled) {
            return $callback();
        }
        
        $cacheFile = $this->cacheDir . md5($key) . '.cache';
        
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $this->cacheTime) {
            return file_get_contents($cacheFile);
        }
        
        $data = $callback();
        if ($data) {
            file_put_contents($cacheFile, $data);
        }
        
        return $data;
    }
    
    // 辅助方法
    private function addChannel($channelId, $displayName = null) {
        $displayName = $displayName ?: $channelId;
        $this->chn .= "<channel id=\"{$channelId}\"><display-name lang=\"zh\">{$displayName}</display-name></channel>\n";
    }
    
    private function addProgram($channel, $start, $stop, $title, $desc = " ") {
        // 更严格的 XML 字符处理
        $title = htmlspecialchars($title, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $desc = htmlspecialchars($desc, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        
        // 移除控制字符
        $title = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $title);
        $desc = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $desc);
        
        $this->chn .= "<programme start=\"{$start}\" stop=\"{$stop}\" channel=\"{$channel}\">\n";
        $this->chn .= "<title lang=\"zh\">{$title}</title>\n";
        $this->chn .= "<desc lang=\"zh\">{$desc}</desc>\n";
        $this->chn .= "</programme>\n";
    }
    
    private function curlGet($url, $headers = []) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FAILONERROR => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);
        
        $result = curl_exec($ch);
        if (curl_error($ch)) {
            error_log("CURL Error for $url: " . curl_error($ch));
            curl_close($ch);
            return false;
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ($httpCode === 200) ? $result : false;
    }
    
    private function curlPost($url, $postData, $headers = [], $userAgent = '') {
        $ch = curl_init();
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FAILONERROR => true
        ];
        
        if ($userAgent) {
            $options[CURLOPT_USERAGENT] = $userAgent;
        }
        
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        
        if (curl_error($ch)) {
            error_log("CURL Error for $url: " . curl_error($ch));
            curl_close($ch);
            return false;
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ($httpCode === 200) ? $result : false;
    }
    
    private function adjustTimeFormat($timeStr) {
        // 将时间格式从HHMM转换为HHMMSS
        if (strlen($timeStr) == 4) {
            return $timeStr . '00';
        }
        return $timeStr;
    }
    
    private function parseTimeToDateTime($dateStr, $timeStr) {
        // 解析时间字符串，处理超过24小时的情况
        $timeStr = $this->adjustTimeFormat($timeStr);
        
        // 分离小时、分钟、秒
        $hour = substr($timeStr, 0, 2);
        $minute = substr($timeStr, 2, 2);
        $second = substr($timeStr, 4, 2);
        
        // 处理小时超过24的情况
        $extraDays = 0;
        if ($hour >= 24) {
            $extraDays = floor($hour / 24);
            $hour = $hour % 24;
        }
        
        // 创建DateTime对象
        $dateTime = DateTime::createFromFormat('Ymd His', $dateStr . ' ' . sprintf('%02d%02d%02d', $hour, $minute, $second), new DateTimeZone('+0900'));
        
        // 如果有额外的天数，增加天数
        if ($extraDays > 0) {
            $dateTime->modify('+' . $extraDays . ' days');
        }
        
        return $dateTime;
    }
    
    private function normalizeTime($date, $timeStr) {
        // $date 格式: Ymd (如: 20251204)
        // $timeStr 格式: HH:MM (如: 25:10)
        
        $timeParts = explode(':', $timeStr);
        $hour = intval($timeParts[0]);
        $minute = $timeParts[1];
        
        // 如果小时数小于24，直接返回
        if ($hour < 24) {
            return $date . sprintf("%02d%02d00", $hour, $minute);
        }
        
        // 如果小时数大于等于24，需要进位到第二天
        $newHour = $hour - 24;
        // 日期加一天
        $newDate = date('Ymd', strtotime($date . ' +1 day'));
        
        return $newDate . sprintf("%02d%02d00", $newHour, $minute);
    }
    
    public function replace_unicode_escape_sequence($match) {
        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
    }
}

// 执行EPG生成
try {
    $epg = new EPGGenerator();
    $result = $epg->generate();
    echo $result;
} catch (Exception $e) {
    echo "错误: " . $e->getMessage();
}
?>