<?php
error_reporting(E_ALL ^ E_NOTICE);
include_once 'simplehtmldom_1_5/simple_html_dom.php';

define ("GEH_URL", "http://g.e-hentai.org/");
define ("CACHE_FOLDER", "temp/");
define ("PROXY_URL", "http://edw.ap01.aws.af.cm/proxy.php?url=");

function file_get_html_proxy($url){
    $html = file_get_html($url);
    if($html == null){
        $proxy = 'http://edw.ap01.aws.af.cm/proxy.php?url=';
        $html = file_get_html($proxy.$url);
    }
    return $html;
}

function image_url_list($url){
    $html = file_get_html_proxy($url);
    $i = 0;
    foreach($html->find('div[class=gdtm] div') as $ele){
        $style = $ele->style;
        $href = $ele->find('a', 0)->href;
        $list[$i++] = $href;
    }
    return $list;
}

function image_0_src_by_html($html){
    $divs = $html->find('div[class=gdtm] div');
    $style = $divs[0]->style;
    $pattern = '/http(.*)jpg/';
    preg_match($pattern, $style, $matches);
    return $matches[0];
}

function image_0_src($url){
    $html = file_get_html_proxy($url);
    return image_0_src_by_html($html);
}

function image_src($url){
    $html = file_get_html_proxy($url);
    $img = $html->find('img[id=img]', 0);
    return $img->src;
}

function check_cache($name){
    $filename = CACHE_FOLDER . $name. ".jpg";
    if(file_exists($filename)){
        return $filename;
    }
    return null;
}

function cache_image($name, $src){
    $filename = CACHE_FOLDER . $name. ".jpg";
    if(file_exists($filename)){
        return $filename;
    }

    @header('Content-type: image/jpg');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $src);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    file_put_contents($filename, $output);
    $size = filesize($filename);
    if($size != $info['size_download']){
        // $failed_file = "img/failed.jpg";
        echo 'cache failed...';
        return $src;
    }
    return $filename;
}

function show_image($filename){
    echo '<img src="', $filename , '" style="width:100%"/>';
}

function cache_gallery($id, $html){
    
}

function clear_cache($dir){
    $dh = opendir($dir);
    while($file = readdir($dh)){
        if($file!='.' && $file!='..'){
            $fullpath = $dir . '/' . $file;
            if(!is_dir($fullpath)){
                unlink($fullpath);
            }
        }
    }
}

function stringToHex($string) {
    $hexString = '';
    for ($i=0; $i < strlen($string); $i++) {
        $hexString .= '%' . bin2hex($string[$i]);
    }
    return $hexString;
}
function hexToString($hexString) {
    return pack("H*" , str_replace('%', '', $hexString));
}

function sibling_page($url, $i){
    $arr = parse_url($url);
    parse_str($arr['query'], $query);
    $query['page'] = $i;
    $url = $arr['scheme'] . '://' . $arr['host'] . $arr['path'] . 
            '?' . http_build_query($query);
    return $url;
}

function print_pages($url){
    echo '<div data-role="navbar" data-grid="d">','<ul>';
    for($i=0; $i<5; $i++){
        $url_i = sibling_page($url, $i);
        $url_i = stringToHex($url_i);
        echo '<li><a href="index.php?url=', $url_i, '">', ($i+1), 
            '</a></li>';
    }
    echo '</ul>','</div>';
}

function print_category(){
/*
 *  'http://g.e-hentai.org/?page=1&f_doujinshi=1&f_manga=0&f_artistcg=0&f_gamecg=0&f_western=0&f_non-h=0&f_imageset=0&f_cosplay=0&f_asianporn=0&f_misc=0&f_search=asuka&f_apply=Apply+Filter'    
 *  'http://g.e-hentai.org/?f_doujinshi=1&f_manga=0&f_artistcg=0&f_gamecg=0&f_western=0&f_non-h=0&f_imageset=0&f_cosplay=0&f_asianporn=0&f_misc=0&f_search=Search+Keywords&f_apply=Apply+Filter'
 */   
}

?>
