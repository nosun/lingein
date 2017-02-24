<?php
$config = array();

$config['db'] = array(
  'host' => 'localhost',
  'user' => 'mdtrade',
  'passwd' => 'trade@mingDA123',
  'name' => 'lingerieinterest',
);

$config['inventorydb'] = array(
  'host' => 'localhost',
  'user' => 'root',
  'passwd' => '',
  'name' => 'mdbrandingconsole',
);

$config['routers'] = array(
  'index' => 'default/seotag',
  'paymentc' => 'product/paymentc',
  'user/panel' => 'user/home',
  'cheap-lingerie-all-cheap-corsets.html' => 'product/browse/cheap-corsets++++++1.html',
  'cheap-lingerie-all-sexy-club-dresses.html' => 'product/browse/sexy-club-dresses++++++1.html',
  'cheap-lingerie-all-babydoll-lingerie.html' => 'product/browse/babydoll-lingerie++++++1.html',
  'cheap-lingerie-all-sexy-costumes.html' => 'product/browse/sexy-costumes++++++1.html',
  'new-arrivals.html' => 'product/browse/++recommend-new-arrivals++++1.html',
  'recommended.html' => 'product/browse/++recommend-recommended++++1.html',
  'hot-products.html' => 'product/browse/++recommend-hot-products++++1.html',
  'discounted.html' => 'product/browse/++recommend-discounted++++1.html',
  'newslist' =>'article/list/news',
  'faqslist' =>'article/list/faq',
  'tag-a' => 'default/seotags/A',
  'tag-b' => 'default/seotags/B',
  'tag-c' => 'default/seotags/C',
  'tag-d' => 'default/seotags/D',
  'tag-e' => 'default/seotags/E',
  'tag-f' => 'default/seotags/F',
  'tag-g' => 'default/seotags/G',
  'tag-h' => 'default/seotags/H',
  'tag-i' => 'default/seotags/I',
  'tag-j' => 'default/seotags/J',
  'tag-k' => 'default/seotags/K',
  'tag-l' => 'default/seotags/L',
  'tag-m' => 'default/seotags/M',
  'tag-n' => 'default/seotags/N',
  'tag-o' => 'default/seotags/O',
  'tag-p' => 'default/seotags/P',
  'tag-q' => 'default/seotags/Q',
  'tag-r' => 'default/seotags/R',
  'tag-s' => 'default/seotags/S',
  'tag-t' => 'default/seotags/T',
  'tag-u' => 'default/seotags/U',
  'tag-v' => 'default/seotags/V',
  'tag-w' => 'default/seotags/W',
  'tag-x' => 'default/seotags/X',
  'tag-y' => 'default/seotags/Y',
  'tag-z' => 'default/seotags/Z',
  'tag-0-9' => 'default/seotags/0-9',
  'branding.html' =>'templates/lingeriemoment.com/branding.html',
);

$config['dynamicRouters'] = array(
  't/' => 'browse/++tag-',
  'browse/all/' => 'browse/all++++++',
);

$config['categoryRouter'] = array(
  'tag-' => 't/',
  '++tag-' => 't/',
  'all++++++' => 'browse/all/',
  'recommend-' => '',
);


$config['debug'] = false;
//$config['template'] = 'lingeriemoment.com';
//$config['sphinx.index'] = 'LINGERIEMOMENT';
$config['template'] = 'lingerieinterest.com';
$config['sphinx.index'] = 'INTEREST';
$config['compress'] = false;
