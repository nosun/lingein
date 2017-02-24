<?php
require_once dirname(__FILE__) . '/lib/Stripe.php';

$stripe = array(
/*
  "secret_key"=> "sk_live_kxZTXDfvIxg10W0JPfEmKg78",
  "publishable_key" => "pk_live_BQJqxncorUZCrjWxA3bQJtKC"
  */
  "secret_key"=> "sk_test_BYBYAkZMiXTamqlJJdds7pSl",
  "publishable_key" => "pk_test_ID8u2MTiyF8uux439cNNlech"
);

Stripe::setApiKey($stripe['secret_key']);