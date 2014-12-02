<?php


// Set your access token here:
include 'account.php';
include 'file_access.php';
list($campaign_array, $adset_array, $adgroup_array, $creative_array) = read_ad_index();

list($cmp_act_data) = read_append_ad_data($argv[1]);

if(is_null($access_token) || is_null($app_id) || is_null($app_secret)) {
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


/**
 * Step 1 Read the AdAccount (optional)
 */
use FacebookAds\Object\AdAccount;
use FacebookAds\Object\Fields\AdAccountFields;


$account = (new AdAccount($account_id))->read(array(
  AdAccountFields::ID,
  AdAccountFields::NAME,
  AdAccountFields::ACCOUNT_STATUS
));

echo "\nUsing this account: ";
echo $account->id."\n";

// Check the account is active
if($account->{AdAccountFields::ACCOUNT_STATUS} !== 1) {
  throw new \Exception(
    'This account is not active');
}

/**
 * Step 2 Create the AdCampaign
 */


use FacebookAds\Object\AdCampaign;
use FacebookAds\Object\Fields\AdCampaignFields;
use FacebookAds\Object\Values\AdObjectives;
use FacebookAds\Object\Values\AdBuyingTypes;

foreach($cmp_act_data as $cmp_data) {

  if ($cmp_data[0]=="A") {
    $campaign  = new AdCampaign(null, $account->id);
    $campaign->setData(array(
      AdCampaignFields::NAME => $cmp_data[1],
      AdCampaignFields::OBJECTIVE => AdObjectives::WEBSITE_CLICKS,
      AdCampaignFields::STATUS => $cmp_data[2],                  // AdCampaign::STATUS_PAUSED
    ));
    $campaign->create();
    echo "Campaign ID:" . $campaign->id . "\n";
    $campaign_array[$campaign->id] = array($cmp_data[1]);
  } elseif ($cmp_data[0]=="D") {
    $campaign  = new AdCampaign(null, $account->id);
    //echo $cmp_data[1];
    //print_r( $campaign_array);
      if (search_ad_array($campaign_array,0,$cmp_data[1])==false) {
         throw new \Exception(
            'no campaign item can be deleted');
      } else {
        $cmp_idx = search_ad_array($campaign_array,0,$cmp_data[1]);
        $cmp = new AdCampaign($cmp_idx);
        $cmp->delete();
        unset($campaign_array[$cmp_idx]);
      }    
  } else {
      // do nothing ....
  }

}


write_ad_index($campaign_array, $adset_array, $adgroup_array, $creative_array);
exit;
// ==================================================================================================


// ===================================================
/**
 * Step 6 Search Targeting
 */
/*
use FacebookAds\Object\TargetingSearch;
use FacebookAds\Object\Search\TargetingSearchTypes;

$results = TargetingSearch::search(
  $type = TargetingSearchTypes::INTEREST,
  $class = null,
  $query = 'facebook'
);
// we'll take the top result for now
$target = (count($results)) ? $results->getObjects()[0] : null;


echo "Using target: ".$target->name."\n";

$targeting = array(
  'geo_locations' => array(
    'countries' => array('US'),
  ),
  'interests' => array(
    array(
      'id' => $target->id,
      'name'=>$target->name
    )
  )
);
*/

/**
 * Step 3 Create the AdSet
 */
use FacebookAds\Object\AdSet;
use FacebookAds\Object\Fields\AdSetFields;

date_default_timezone_set('Asia/Taipei');

$adset = new AdSet(null, $account->id);

$data = array(
  AdSetFields::NAME => 'My AdSet',
  AdSetFields::BID_TYPE => 'CPC',
  AdSetFields::BID_INFO => array(
    'CLICKS' => 50,
  ),
  AdSetFields::CAMPAIGN_STATUS => AdSet::STATUS_PAUSED,
  AdSetFields::DAILY_BUDGET => 100,
  AdSetFields::CAMPAIGN_GROUP_ID => $campaign->id ,
  AdSetFields::TARGETING => array(
    'geo_locations' => array(
      'countries' => array(
        'US',
        'GB',
      ),
    ),
    'custom_audiences' => array(
        'id' => 6022751292518,
        'name' =>'125_S00171087_20141126_c02'),
  ),
);

echo '##Adset json='.json_encode($data)."\n";
$adset->create($data);
echo 'created AdSet  ID: '. $adset->id . "\n";

/**
 * Step 4 Create an AdImage
 */
use FacebookAds\Object\AdImage;
use FacebookAds\Object\Fields\AdImageFields;



$image = new AdImage(null, $account->id);
$image->filename = SDK_DIR.'/test/misc/abc.jpg';

$image->create();
echo 'created Image Hash: '.$image->hash . "\n";

/**
 * Step 5 Create an AdCreative
 */
use FacebookAds\Object\AdCreative;
use FacebookAds\Object\Fields\AdCreativeFields;

$creative = new AdCreative(null, $account->id);
$creative->setData(array(
  AdCreativeFields::NAME => 'Sample Creative1',
  AdCreativeFields::TITLE => '歡迎到安迪的店',
  AdCreativeFields::BODY => 'We\'ve got fun \'n\' games',
  AdCreativeFields::IMAGE_HASH => $image->hash,
  AdCreativeFields::OBJECT_URL => 'http://chiangandy.mooo.com/blog/',
));

$creative->create();
echo 'created Creative ID: '.$creative->id . "\n";

/**
 * Step 7 Create an AdGroup
 */
use FacebookAds\Object\AdGroup;
use FacebookAds\Object\Fields\AdGroupFields;
//use FacebookAds\Object\Fields\AdGroupBidInfoFields;
//use FacebookAds\Object\Values\BidTypes;

$adgroup = new AdGroup(null, $account->id);

/*
$goupdata = array(
  AdGroupFields::CREATIVE =>    array('creative_id' =>    $creative->id),
  AdGroupFields::NAME =>        'My AdGroup 1',
  AdGroupFields::BID_TYPE =>    BidTypes::BID_TYPE_CPM,
  AdGroupFields::BID_INFO =>    array(AdGroupBidInfoFields::IMPRESSIONS => '2'),
  AdGroupFields::CAMPAIGN_ID => $adset->id,
  AdGroupFields::TARGETING =>   $targeting,
);
*/

$fields = array(
  AdGroupFields::NAME => '我的群組1',
  AdGroupFields::CAMPAIGN_ID => $adset->id,
  AdGroupFields::ADGROUP_STATUS => 'PAUSED',
  AdGroupFields::CREATIVE => array(
    'creative_id' => $creative->id,
  ),
);
//$adgroup->setData();

echo '##Adgroup json='.json_encode($fields)."\n";
$adgroup->create($fields);
echo 'created AdGroup ID:' . $adgroup->id . "\n";
echo "finished!\n";


write_ad_index($campaign_array, $adset_array, $adgroup_array, $creative_array);
