<?php
//定义缩略图片
function hook_imagecachePresets()
{
  return array(
    '60x90' => array(
       'type' => 'scale',
       'width' => 60,
       'height' => 90,
    ),
    '67x67' => array(
       'type' => 'scale',
       'width' => 67,
       'height' => 67,
    ),
    '118x178' => array(
       'type' => 'scale',
       'width' => 118,
       'height' => 178,
    ),
    '160x246' => array(
    	'type' => 'scale',
    	'width' => 160,
    	'height' => 246,
    ),
    '180x270' => array(
       'type' => 'scale',
       'width' => 180,
       'height' => 270,
    ),
    '345x335' => array(
       'type' => 'scale',
       'width' => 345,
       'height' => 335,
    ),
    '186x280' => array(
       'type' => 'scale',
       'width' => 186,
       'height' => 280,
    ),
    '188x282' => array(
    	'type' => 'scale',
    	'width' => 188,
    	'height' => 282,
    ),
    '410x615' => array(
       'type' => 'scale',
       'width' => 410,
       'height' => 615,
    ),
    '500x750' => array(
       'type' => 'scale',
       'width' => 500,
       'height' => 750,
    ),
    '978x446' => array(
       'type' => 'scale',
       'width' => 978,
       'height' => 446,
    ),
    '210x316' => array(
       'type' => 'scale',
       'width' => 210,
       'height' => 316,
    ),
    '160x246' => array(
       'type' => 'scale',
       'width' => 160,
       'height' => 246,
    ),
  );
}

//重新设置当前位置
function hook_breadcrumb()
{
  $output = array();
  $breadcrumb = getBreadcrumb();
  foreach ($breadcrumb as $row) {
    $title = isset($row['html']) && $row['html'] ? $row['title'] : plain($row['title']);
    
    //url will translate the '&' already.
    $title = str_replace('&amp;', '&', $title);
    if (isset($row['path'])) {
      $output[] = '<a href="' . url($row['path']) . '">' . $title . '</a>';
    } else {
      $output[] = $title;
    }
  }
  $output = implode('&nbsp;>&nbsp;', $output);
  return $output;
}
//重定义category 分页。
function hook_category_pagination($urlPage, $pageCount, $page, $firstPath = null){
  $output = '';
  if ($page > 1){
    $previousUrl = categoryURL(str_replace('%d', ''.($page - 1), $urlPage));
    $output .= '<a title="page '.($page - 1).'" href="'.url($previousUrl).'"><span><img class="v-middle" alt="next" src="'.themeResourceURI('images/i_pager-prev.gif') .'"/></span></a>';
  }



  if($page - 1< 3){
    for($i=1; $i<$page; $i++){
      $pageUrl = categoryURL(str_replace('%d', ''.$i, $urlPage));
      $output .= '<a title="page '.$i.'" href="'.url($pageUrl). '">'. $i. '</a>';
    }
  }else{
    $firstPageUrl = categoryURL(str_replace('%d', '1', $urlPage));
    $output .= '<a title="page 1" href="'.url($firstPageUrl). '">1</a>';
    $output .= '<span>...</span>';
    for($i=$page-2; $i<$page; $i++){
      $pageUrl = categoryURL(str_replace('%d', ''.$i, $urlPage));
      $output .= '<a title="page '.$i.'" href="'.url($pageUrl). '">'. $i. '</a>';
    }
  }
  $output .= '<strong>'.$page.'</strong>';
  //echo after page.
  if($pageCount - $page < 3){
    for($i=$page + 1; $i <= $pageCount; $i++){
      $pageUrl = categoryURL(str_replace('%d', ''.$i, $urlPage));
      $output .= '<a title="page '.$i.'" href="'.url($pageUrl). '">'. $i. '</a>';
    }
  }else{
    for($i=$page + 1; $i<$page + 3; $i++){
      $pageUrl = categoryURL(str_replace('%d', ''.$i, $urlPage));
      $output .= '<a title="page '.$i.'" href="'.url($pageUrl). '">'. $i. '</a>';
    }
    $output .= '<span>...</span>';
    $lastPageUrl = categoryURL(str_replace('%d', ''.$pageCount, $urlPage));
    $output .= '<a title="page '.$pageCount.'" href="'.url($lastPageUrl). '">'.$pageCount.'</a>';
  }
  if ($page < $pageCount){
    $nextUrl = categoryURL(str_replace('%d', ''.($page + 1), $urlPage));
    $output .= '<a title="page '.($page + 1).'" href="'.url($nextUrl).'"></a>';
  }
  return $output;
}


//重定义category 分页。
function hook_common_pagination($urlPage, $pageCount, $page, $firstPath = null, $isCategory = false){
  $output = '';
  if ($page > 1){
    $previousUrl = str_replace('%d', ''.($page - 1), $urlPage);
    if($isCategory) $previousUrl = categoryURL($previousUrl);
    $output .= '<a title="page '.($page - 1).'" href="'
    .url($previousUrl).'">Previous</a>';
  }

  if($page - 1< 3){
    for($i=1; $i<$page; $i++){
      $pageUrl = str_replace('%d', ''.$i, $urlPage);
      if($isCategory) $pageUrl = categoryURL($pageUrl);
      $output .= '<a title="page '.$i.'" href="'.url($pageUrl). '">'. $i. '</a>';
    }
  }else{
    $firstPageUrl = str_replace('%d', '1', $urlPage);
    if($isCategory) $firstPageUrl = categoryURL($firstPageUrl);
    $output .= '<a title="page 1" href="'.url($firstPageUrl). '">1</a>';
    $output .= '<span>...</span>';
    for($i=$page-2; $i<$page; $i++){
      $pageUrl = str_replace('%d', ''.$i, $urlPage);
      if($isCategory) $pageUrl = categoryURL($pageUrl);
      $output .= '<a title="page '.$i.'" href="'.url($pageUrl). '">'. $i. '</a>';
    }
  }
  $output .= '<strong>'.$page.'</strong>';
  //echo after page.
  if($pageCount - $page < 3){
    for($i=$page + 1; $i <= $pageCount; $i++){
      $pageUrl = str_replace('%d', ''.$i, $urlPage);
      if($isCategory) $pageUrl = categoryURL($pageUrl);
      $output .= '<a title="page '.$i.'" href="'.url($pageUrl). '">'. $i. '</a>';
    }
  }else{
    for($i=$page + 1; $i<$page + 3; $i++){
      $pageUrl = str_replace('%d', ''.$i, $urlPage);
      if($isCategory) $pageUrl = categoryURL($pageUrl);
      $output .= '<a title="page '.$i.'" href="'.url($pageUrl). '">'. $i. '</a>';
    }
    $output .= '<span>...</span>';
    $lastPageUrl = str_replace('%d', ''.$pageCount, $urlPage);
    if($isCategory) $lastPageUrl = categoryURL($lastPageUrl);
    $output .= '<a title="page '.$pageCount.'" href="'.url($lastPageUrl). '">'.$pageCount.'</a>';
  }
  if ($page < $pageCount){

    $nextUrl = str_replace('%d', ''.($page + 1), $urlPage);
    if($isCategory) $nextUrl = categoryURL($nextUrl);
    $output .= '<a title="page '.($page + 1).'" href="'.url($nextUrl).'">Next</a>';
  }
  return $output;
}

 //重定义分页
function hook_pagination($path, $count, $each, $page, $firstPath = null)
{
$pages = ceil($count / $each);
  if (1 >= $pages) {
    return '';
  }
  $output = '';

      $output .= '<a class="independent" href="' . url(isset($firstPath) ? $firstPath : strtr($path, array('%d' => 1))) . '">&lt;Home</a>';

  if ($pages > 1) {
    if ($page == 1) {
      $output .= '<a class="independent">Previous</a>';
    } else {
      $output .= '<a class="independent" href="' . url($page == 2 && isset($firstPath) ? $firstPath : strtr($path, array('%d' => $page - 1))) . '">Previous</a>';
    }
  }
  $from = $page - 5;
  $end = $page + 5;
  if ($from < 1) {
    $end = min($end - $from + 1, $pages);
    $from = 1;
  }
  if ($end > $pages) {
    $from = max($from - $end + $pages, 1);
    $end = $pages;
  }
  for ($i = $from; $i <= $end; ++ $i) {
    if ($page == $i) {
      $output .=  '<a class="independent">'.$i.'</a>';
    } else {
      $output .= '<a class="independent" href="' . url($i == 1 && isset($firstPath) ? $firstPath : strtr($path, array('%d' => $i))) . '">' . $i . '</a>';
    }
  }
  if ($pages > 1) {
    if ($page == $pages) {
      $output .= '<a class="independent">Next</a>';
    } else {
      $output .= '<a  class="independent" href="' . url(strtr($path, array('%d' => $page + 1))) . '">Next</a>';
    }
  }

      $output .= '<a class="independent" href="' . url(strtr($path, array('%d' => $pages))) . '">Last&gt;</a>';
      $output .= '<a class="independent" href="' . url(strtr($path, array('%d' => $pages))) . '">  '.$pages.' pages </a>';
    return $output;
}