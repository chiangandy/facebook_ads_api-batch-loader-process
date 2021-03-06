<?php

use FacebookAds\Api;
use FacebookAds\Object\AdUser;
use FacebookAds\Object\Fields\AdAccountFields;
use FacebookAds\Object\Fields\ConnectionObjectFields;
use FacebookAds\Object\Values\ConnectionObjectTypes;

// Set your access token here:
//$access_token = 'CAAH0vZBILZCU0BALjBkEpFUqlSnsHQyhwzUoZBnooIAsbOl1DGvc19OxBV8WjQ0ZC725xntYfRvJClbZA4ZCIv6fB0uyPPYUwwkR7dZA2EpqlKjCNAnGo4HsAbLcP288Vx3ges8PMCQla7xbUjm9qHF4x4a9pNZB8Lq9atLY5nJOAPJfOaXq58XHDhuEHGX0RPHNXy4YmJ7HpX4krj5tKO83';
$access_token = 'CAAH0vZBILZCU0BANuvW9vZBAQCSOZBeN9g6H6zLvdgk16F8bkS3AGZA7jMe12fKkiliPbueeSz2MjmaZAKD5F2airkYrhuA9YZAQ2CBii9i9Qqbob0rGy3w640ZA0WtFqjP2s7Nl6inZBc2WNkdQy1izjASnTMeZCCDPidZCKwVaNydCZAaOh38jlgpThvSWRRnFOE0ff0sOT0Lh1HoTnZB3KEBIA';
$app_id = '550579945078093';
$app_secret = '405a05d14c52d130b170c734213fd52e';

if(is_null($access_token) || is_null($app_id) || is_null($app_secret)) {
  throw new \Exception(
    'You must set your access token, app id and app secret before executing'
  );
}

define('SDK_DIR', __DIR__ . '/..'); // Path to the SDK directory
$loader = include SDK_DIR.'/vendor/autoload.php';

Api::init($app_id, $app_secret, $access_token);

// Use the first account - Connection objects are not actually account-specific
// so the account ID doesn't matter
$user = new AdUser('me');
$accounts = $user->getAdAccounts([AdAccountFields::ID]);
$account = $accounts[0];

$connection_objects = $account->getConnectionObjects([
  ConnectionObjectFields::ID,
  ConnectionObjectFields::NAME,
  ConnectionObjectFields::OBJECT_STORE_URLS,
  ConnectionObjectFields::TYPE,
  ConnectionObjectFields::URL,
]);

// Group the connection objects based on type
$groups = [];

foreach ($connection_objects as $object) {
  if (!isset($groups[$object->type])) {
    $groups[$object->type] = [];
  }
  $groups[$object->type][] = $object;
}

foreach ($groups as $type => $type_objects) {
  $type_name = get_type_name($type);
  echo "\n", $type_name, "\n";
  echo str_repeat('=', strlen($type_name)), "\n";

  foreach ($type_objects as $object) {
    render_object($object);
  }
}

function get_type_name($type) {
  switch ($type) {
    case ConnectionObjectTypes::PAGE:
      return 'Page';
    case ConnectionObjectTypes::APPLICATION:
      return 'Application';
    case ConnectionObjectTypes::EVENT:
      return 'Event';
    case ConnectionObjectTypes::PLACE:
      return 'Place';
    case ConnectionObjectTypes::DOMAIN:
      return 'Domain';
    default:
      return $type;
  }
}

function render_object($object) {
  switch ($object->type) {
    case ConnectionObjectTypes::APPLICATION:
      echo ' - ', $object->id, ' - ', $object->name, "\n";
      foreach ($object->object_store_urls as $store_name => $store_url) {
        echo '   ', $store_name, ': ', $store_url, "\n";
      }
      return;

    default:
      echo ' - ', $object->id, ' - ', $object->name, ' - ', $object->url, "\n";
      return;
  }

}
