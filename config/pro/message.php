<?php  
 return array (
  'aomy_sms' => 
  array (
    'platform' => 'aliyun',
    'sms_code_expire' => '600',
    'sms_code_limit' => '60',
    'regional' => 
    array (
      'access_id' => '',
      'access_secret' => '',
      'sdk_app_id' => '',
      'region' => 'cn-shanghai',
      'endpoint_name' => 'cn-shanghai',
      'sign_name' => '',
    ),
    'global' => 
    array (
      'access_id' => '',
      'access_secret' => '',
      'sdk_app_id' => '',
      'region' => 'cn-hangzhou',
      'endpoint_name' => 'cn-hangzhou',
      'sign_name' => '',
    ),
  ),
  'aomy_private_letter' => 
  array (
    'private_letter_status' => '1',
    'private_ios_letter_status' => '0',
    'platform' => 'yunxin',
    'app_key' => 'b484a4fbe6871c01e4ca4e60d994296c',
    'app_secret' => '0c4baacbcf9f',
  ),
  'bxkj_push' => 
  array (
    'platform' => 'umeng',
    'android' => 
    array (
      'app_key' => '64d3580ee542cc4e3ceeb938',
      'message_secret' => '1d0f7aebe4425b68fa85dfa00fd7447f',
      'app_master_secret' => 'e3xec9wspgiwigofwmcpxr8bhwdeoagk',
      'default_activity' => '',
    ),
    'ios' => 
    array (
      'app_key' => '',
      'app_master_secret' => '',
    ),
    'push_delay_rate' => '1',
    'push_delay_range' => '900',
    'push_max_delay' => '3600',
    'push_section_length' => '300',
    'push_receipt_period' => '3600',
  ),
  'bxkj_customer_service' => 
  array (
    'type' => '1',
    'link' => 'www.baidu.com',
  ),
);