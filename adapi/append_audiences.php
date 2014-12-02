<?php


include 'account.php';
include 'file_access.php';

$audinece_array = read_audience_index();

if (is_null($argv[1])) {
  throw new \Exception(
    'You must set data file name');
}
$audidence_data = read_append_audience_data($argv[1]);



if (is_null($access_token) || is_null($app_id) || is_null($app_secret)) {
  throw new \Exception(
    'You must set your access token, app id and app secret before executing'
  );
}

if (is_null($account_id)) {
  throw new \Exception(
    'You must set your account id before executing');
}

define('SDK_DIR', __DIR__ . '/..'); // Path to the SDK directory
$loader = include SDK_DIR.'/vendor/autoload.php';

use FacebookAds\Api;

Api::init($app_id, $app_secret, $access_token);

// use the namespace for Custom Audiences and Fields
use FacebookAds\Object\CustomAudience;
use FacebookAds\Object\Fields\CustomAudienceFields;
use FacebookAds\Object\Values\CustomAudienceTypes;

// Create a custom audience object, setting the parent to be the account id

//var_dump($audidence_data);
foreach ($audidence_data as $item_dt) {

    $url_key = explode("||", $item_dt[2] );
    //echo $url_key;
    $url_str = '{"and":[{"or":[
              ##url_data##
        ]}]}';
    $jsurl ='';
    $numItems = count($url_key);
    $cnt = 0;
    foreach ($url_key as $url) {
        $jsurl = $jsurl .'{"url":{"i_contains":"'.$url.'"}}';
        if (++$cnt < $numItems) {
            $jsurl = $jsurl . ",";
        }
    }    
    $url_str = str_replace('##url_data##',$jsurl,$url_str);
    echo "url_data".$url_str."\n";
    if ($item_dt[0]=="A") {
      # code...
      if (array_search($item_dt[1],$audinece_array)==false) {
        // perform insert
        $audience = new CustomAudience(null, $account_id);
        $adata = array(
          CustomAudienceFields::NAME => $item_dt[1],
          CustomAudienceFields::DESCRIPTION => $item_dt[1].'-從API 新增',
          CustomAudienceFields::SUBTYPE => 'WEBSITE',
          CustomAudienceFields::RETENTION_DAYS => $item_dt[3],
          CustomAudienceFields::RULE => $url_str,
        );      
        $audience->create($adata);
        $audinece_array[$audience->id] = $adata[CustomAudienceFields::NAME];
      } else {
        // perform update
        $aud_idx = array_search($item_dt[1],$audinece_array);
        $audience = new CustomAudience($aud_idx);
        $adata = array(
          CustomAudienceFields::NAME => $item_dt[1],
          CustomAudienceFields::DESCRIPTION => $item_dt[1].'-從API 新增',
//          CustomAudienceFields::SUBTYPE => 'WEBSITE',
          CustomAudienceFields::RETENTION_DAYS => $item_dt[3],
          CustomAudienceFields::RULE => $url_str,
        );  
        $audience->update($adata);
      }
    } elseif ($item_dt[0]=="D") {
      # perform delete audience process
      if (array_search($item_dt[1],$audinece_array)==false) {
         throw new \Exception(
            'no audience item can be deleted');
      } else {
        $aud_idx = array_search($item_dt[1],$audinece_array);
        $audience = new CustomAudience($aud_idx);
        $audience->delete();
        unset($audinece_array[$audience->id]);
      }
    } else {
        throw new \Exception(
          'invalid item data in data file');
    }
    
    echo "\n";

}
//var_dump($audidence_data);
write_audience_index($audinece_array);
exit;


$audience = new CustomAudience(null, $account_id);
$adata = array(
  CustomAudienceFields::NAME => '客製Audiece#2014_3',
  CustomAudienceFields::DESCRIPTION => '到訪過相關網站的受眾',
  CustomAudienceFields::SUBTYPE => 'WEBSITE',
  CustomAudienceFields::RETENTION_DAYS => 30,
  CustomAudienceFields::RULE => 
      '{"and":[{"or":[
              {"url":{"i_contains":"chiangandy.mooo.com"}},
              {"url":{"i_contains":"tw.yahoo.com"}}
        ]}]}',
// this part is still need to chck, we meet issue when we call the api and get different result
/*
  CustomAudienceFields::DATA_SOURCE => array (
    'type' =>  'EVENT_BASED',
    'subtype' => 'WEB_PIXEL_COMBINATION_EVENTS',
    'creation_params' => array(
      'combination_type' => 'website',
      'traffic_type' => '2',
      'prefill' => 'true',
    ),
  ),
*/
);

echo "==============\n";
echo '##Adset json='.json_encode($adata)."\n";
$audience->create($adata);
echo "Audience ID: " . $audience->id."\n";

$audinece_array[$audience->id] = $adata[CustomAudienceFields::NAME];
write_audience_index($audinece_array);

