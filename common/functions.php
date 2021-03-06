<?php
/**
 * 批量文本替换
 * @param string $context 源文本
 * @param array $keyword 需要替换的文本（数组）
 * @param array $keyword_replace 替换后的内容（数组）
 * @return mixed
 */
function batch_str_replace($context, $keyword, $keyword_replace)
{
    if(sizeof($keyword) == 1 && sizeof($keyword_replace) == 1) {
        if(strpos($context, $keyword[0]) !== false) {
            $context = str_replace($keyword[0], $keyword_replace[0], $context);
        }else{
            $context = $keyword_replace[0];
        }
    }elseif(sizeof($keyword) > 1 && sizeof($keyword_replace) > 1 && sizeof($keyword) == sizeof($keyword_replace)) {
        foreach ($keyword as $key => $value) {
            if(strpos($context, $keyword[$key]) !== false) { $context = str_replace($keyword[$key], $keyword_replace[$key], $context);}
        }
    }
    return $context;
}

/**
 * 只替换一次字符串
 * @param string $needle 需要替换的文本
 * @param string $replace 替换后的内容
 * @param string $haystack 源字符串
 * @return mixed
 */
function str_replace_once($needle, $replace, $haystack) {
    $pos = strpos($haystack, $needle);
    if ($pos === false) return $haystack;
    return substr_replace($haystack, $replace, $pos, strlen($needle));
}

/**
 * 获取 IP地址、归属地
 * @param string $type IP 识别库
 * @return array
 */
function isIP($type = 'ip138')
{
    $unknown = 'unknown';
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    if (false !== strpos($ip, ',')) { $ip = reset(explode(',', $ip)); }
    if($ip == "::1") { $ip = "127.0.0.1"; }
    if($type == 'taobao') { // 淘宝 IP 地址库
        $JsonIp = json_decode(httpGet("http://ip.taobao.com/service/getIpInfo.php?ip=".$ip),true);
        if($JsonIp["data"]["region"] != ""){
            $arrayName = [ 'ip' => $ip, 'address' => $JsonIp["data"]["country"]."-".$JsonIp["data"]["area"]."地区 ".$JsonIp["data"]["region"].$JsonIp["data"]["city"]." ".$JsonIp["data"]["isp"] ];
        }else{
            $arrayName = [ 'ip' => $ip, 'address' => $JsonIp["data"]["country"] ];
        }
    }elseif($type == 'ip138') { // IP138 IP 地址库
        $JsonIp = httpsGet("https://sp0.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php?query=" . $ip . "&co=&resource_id=6006&t=1492224411362&ie=utf8&oe=gbk&cb=op_aladdin_callback&format=json&tn=baidu&cb=jQuery1102032941802880745374_1492224400641&_=1492224400643");
        $JsonIp = mb_convert_encoding($JsonIp, "utf-8", "gbk");
        $JsonIp = json_decode(getSubstr($JsonIp, "/**/jQuery1102032941802880745374_1492224400641(", ");"), true);
        $arrayName = [ 'ip' => isset($JsonIp["data"][0]["origip"]) ? $JsonIp["data"][0]["origip"] : "",
            'address' => isset($JsonIp["data"][0]["location"]) ? $JsonIp["data"][0]["location"] : "",
        ];
    }
    return $arrayName;
}

/**
 * curl http get 方式访问
 * @param string $url 网址
 * @return mixed 网页源代码
 */
function httpGet($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_REFERER, $url);
    $res = curl_exec($curl);
    curl_close($curl);
    return $res;
}

/**
 * curl http post 方式访问
 * @param string $url 网址
 * @param array $data 提交数据
 * @return mixed 网页源代码
 */
function httpPost($url, $data) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $res = curl_exec($curl);
    curl_close($curl);
    return $res;
}


/**
 * curl https get 方式访问
 * @param string $url 网址
 * @return mixed
 */
function httpsGet($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 500);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

/**
 * 取文本中间
 * @param string $str 文本内容
 * @param string $leftStr 左边文本
 * @param string $rightStr 右边文本
 * @return bool|string
 */
function getSubstr($str, $leftStr, $rightStr) {
    $left = strpos($str, $leftStr);
    $right = strpos($str, $rightStr, $left);
    if ($left < 0 or $right < $left) {
        return '';
    }
    return substr($str, $left + strlen($leftStr), $right - $left - strlen($leftStr));
}

/**
 * 对象转数组
 * @param object $object 对象
 * @return mixed 数组
 */
function object2Array($object) {
    return json_decode(json_encode($object, JSON_UNESCAPED_UNICODE), true);
}