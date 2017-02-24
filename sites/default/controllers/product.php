<?php
class Product_Controller extends Bl_Controller
{
	private $_productInstance;

	public static function __router($paths)
	{
		if (!isset($paths[0])) {
			goto404(t('Argument 0 is invalid'));
		}
		if (preg_match('/^([\w-]+)\.html$/i', $paths[0], $matches)) {
			$paths[0] = $matches[1];
			return array(
        'action' => 'pathalias',
        'arguments' => $paths,
			);
		} else if (preg_match('/^\d+$/', $paths[0])) {
			return array(
        'action' => 'view',
        'arguments' => $paths,
			);
		}
	}

	public function init()
	{
		$this->_productInstance = Product_Model::getInstance();
		$this->_displayInfo = Bl_Config::get('display', array());
	}

	public function indexAction()
	{
		gotourl('product/list');
	}

	public function termAction($tid, $path){
		$path = basename($path, '.html');
		$productInfo = $this->_productInstance->getProductInfoByPathAlias($path);
		$this->_view($productInfo);
	}

	//added for seo optimization.
	public function seoAction($path){
		$path = basename($path, '.html');
		$productInfo = $this->_productInstance->getProductInfoByPathAlias($path);
		$this->_view($productInfo);
	}

	public function viewAction($pid)
	{
		if (!$product = $this->_productInstance->getProductInfo($pid)) {
			goto404(t('Product ID'). '<em>' . $pid . '</em>' . t('not found.'));
		}
		$this->_view($product);
	}

	public function pathaliasAction($path)
	{
		if (!$product = $this->_productInstance->getProductInfoByPathAlias($path)) {
			goto404(t('Product path alias') . '<em>' . $path . '.html</em>' . t('not found.'));
		}
		$this->_view($product);
	}

	private function _view($product)
	{
		$config = Bl_Config::get('undercarriageShow');
		if (!$config && !$product->status) {
			goto404(t('Product path alias') . '<em>' . $path . '.html</em>' . t('not found.'));
		}
		$pid = $product->pid;
		$this->_productInstance->addProductVisit($pid);

		$product->summary = strtr($product->summary, array('&nbsp;' => ' '));
		$product->description = strtr($product->description, array('&nbsp;' => ' '));

		$product->files = array_values($this->_productInstance->getProductFilesList($pid));

		$type = $product->type;
		if ($this->_productInstance->checkTypeExist($type)) {
			$product->type = $this->_productInstance->getTypeInfo($type);
			$product->fields = $this->_productInstance->getTypeFieldsList($type);
		} else {
			$product->type = null;
			$product->fields = array();
		}

		$breadcrumb = array();

		$breadcrumb[] = array(
      'title' => key_exists('productListHomeName', $this->_displayInfo) ? $this->_displayInfo['productListHomeName'] : 'Home',
      'path' => '',
		);
		$taxonomyInstance = Taxonomy_Model::getInstance();
		if ($termInfo = $taxonomyInstance->getTermInfo($product->directory_tid)) {

			if (!$termInfo->ptid1) {
				$product->directorytid[] = $termInfo->tid;
			} else if (!$termInfo->ptid2) {
				$product->directorytid[] = $termInfo->ptid1;
				$product->directorytid[] = $termInfo->tid;
			} else if (!$termInfo->ptid3) {
				$product->directorytid[] = $termInfo->ptid1;
				$product->directorytid[] = $termInfo->ptid2;
				$product->directorytid[] = $termInfo->tid;
			} else {
				$product->directorytid[] = $termInfo->ptid1;
				$product->directorytid[] = $termInfo->ptid2;
				$product->directorytid[] = $termInfo->ptid3;
				$product->directorytid[] = $termInfo->tid;
			}
			if ($product->directorytid) {
				$termParents = $product->directorytid ? $product->directorytid : array();
				foreach ($termParents as $tid) {
					$term = $taxonomyInstance->getTermInfo($tid);
					$product->directory[] = $term;
					$breadcrumb[] = array(
            'title' => $term->name,
            'path' => $term->path_alias . '.html',
					);
				}
				$product->directory = array_reverse($product->directory);
			}
		} else {
			$product->directory = null;
		}
		
		$pageInstance = PageVariable_Model::getInstance();
		
		if ($brand = $taxonomyInstance->getTermInfo($product->brand_tid)) {
			/*
		    $brand_pv = $pageInstance->selectPageVariables($brand->pvid, null, $brand);
			if(isset($brand_pv->var4)){
				$brand->size_chart = $brand_pv->var4;
			}else{
				$brand->size_chart = null;
			}*/
		    $brand->size_chart = SizeChart_Model::getInstance()->getSizeChartByBrand($brand->tid);
			$product->brand = $brand;
		} else {
			$product->brand = null;
		}

		$breadcrumb[] = array(
      'title' => $product->name,
		);
		setBreadcrumb($breadcrumb);

		//
		$product->related = $this->_productInstance->listProductRelated($pid, 1, 5);

		//获取同类商品
		$product->similar = $this->_productInstance->getProductsList(array('tids' => $product->directory_tid, 'orderby'=> 'rand()'), 1, 4);
		
		if (isset($product->pvid)) {
			$pageInfo = $pageInstance->selectPageVariables($product->pvid, 'product', $product);
		}
		if (isset($pageInfo) && $pageInfo) {
			$this->view->setTitle($pageInfo->title, $pageInfo->meta_keywords, $pageInfo->meta_description , $pageInfo->var1, $pageInfo->var2, $pageInfo->var3, $pageInfo->var4, $pageInfo->var5, $pageInfo->var6);
		}
		if($product->fid){
			$product->bigfilepath = $product->filepath;}
			else {
				$product->bigfilepath = isset($product->files[0]->filepath) ? $product->files[0]->filepath : '';
			}

			$getrandomfilter = array(
      'tid' => $product->directory_tid,
      'keyword' => $product->tname
			);
			callFunction('getrandom', 'product', $this,  $getrandomfilter);
			
			callFunction('check_inventory', $this, $product);

			$templateFile = isset($product->template) && $product->template != '' ? $product->template : 'product.phtml';
			$this->view->render($templateFile, array(
      'product' => $product,
      'termParents' => isset($termParents) ? $termParents : array(),
			));
	}

	public function listAction($page = 1)
	{
		$listMode = isset($_SESSION['browseListConfig']['listMode']) ? $_SESSION['browseListConfig']['listMode'] : 'photo';
		$orderby = isset($_SESSION['browseListConfig']['orderby']) ? $_SESSION['browseListConfig']['orderby'] : null;
		$pageRows = isset($_SESSION['browseListConfig']['pageRows']) ? $_SESSION['browseListConfig']['pageRows'] : (key_exists('goodsListNum', $this->_displayInfo) ? $this->_displayInfo['goodsListNum'] : 60);
		$productCount = $this->_productInstance->getProductsCount(array());
		 
		$pageCount = 0;
		$eachPage = 0;

		if($pageRows == 'all' || $pageRows <= 0){
			$pageCount = 1;
			$eachPage = $productCount;
		}
		else {
			$eachPage = $pageRows;
			$pageCount = ceil($productCount/$pageRows);
		}

		$productList = $this->_productInstance->getProductsList(array('orderby' => $orderby), $page, $eachPage);

		 
		foreach ($productList as $product){
			//added by pzzhang to support direct purchase.
			$type = $product->type;
			if ($this->_productInstance->checkTypeExist($type)) {
				$product->type = $this->_productInstance->getTypeInfo($type);
				$product->fields = $this->_productInstance->getTypeFieldsList($type);
			} else {
				$product->type = null;
				$product->fields = array();
			}
		}

		$breadcrumb = array();
		$breadcrumb[] = array(
      'title' => key_exists('productListHomeName', $this->_displayInfo) ? $this->_displayInfo['productListHomeName'] : 'Home',
      'path' => '',
		);
		$breadcrumb[] = array(
      'title' => 'allgoods',
		);
		setBreadcrumb($breadcrumb);
		if ($page == 1) {
			$_SESSION['FirstPath']['productList'] = trim($_SERVER['REQUEST_URI'], '/');
		}
		$firstPath = isset($_SESSION['FirstPath']['productList']) ? $_SESSION['FirstPath']['productList'] : null;
		
		if(!isset($product->price)){
			$product->price = $product->sell_price;
		}
		
		$this->view->render('productlist.phtml', array(
      'productList' => isset($productList) ? $productList : null,
  	  'listMode' => $listMode,
  	  'page' => $page,
  	  'pageRows' => $pageRows,
      'orderby' => $orderby,
  	  'pageCount' => $pageCount,
  	  'productCount' => $productCount,
		//be notice, here the second parameter $pageCount is different from the other pagination functions.
      'pagination' => callFunction('common_pagination', 'product/list/%d/', $pageCount, $page, $firstPath), 
		));
	}

	public function browseAction($url = '' , $page = 1, $clear = null)
	{
		if ($url == '') {
			gotoUrl('browse/all.html');
		}
		if (!preg_match ("/.html$/i", $url)) {
			goto404(t('no page'));
		}
		$taxonomyInstance = Taxonomy_Model::getInstance();
		$url = basename($url, '.html');
		list($directory_path_alias, $brand_path_alias, $tid_path_alias, $type, $fields, $prices, $page) = $urlArray = array_pad(explode('+', $url), 7, '');
		$urlPageFirst = $urlPage = $directory_path_alias . '+' . $brand_path_alias . '+' . $tid_path_alias . '+' . $type . '+' . $fields . '+' . $prices;
		$pvtitle = '';
		$relateName = array();
		if (isset($directory_path_alias) && $directory_path_alias && $directory_path_alias != 'all' && $directory_path_alias != 'allgoods') {
			$termInfo = $taxonomyInstance->getTermInfoByPathAlias($directory_path_alias);
			if (isset($termInfo) && $termInfo) {
				$directory_tid = isset($termInfo->tid) ? $termInfo->tid : null;

				$parent = $taxonomyInstance->getTermParents($termInfo->tid);
				$termInfo->parent = $parent;
				$termGrade = 1;
				if (isset($parent[0]) && $parent[0]) {
					$termGrade = 2;
				}
				if (isset($parent[1]) && $parent[1]) {
					$termGrade = 3;
				}
				$this->view->assign('termGrade', $termGrade);

				$pvid = $termInfo->pvid;
				$pvtitle .= str_replace('-', ' ', $directory_path_alias);
				$relateName[] =  $termInfo->name;
			} else {
				goto404(t('Can not found this Page'));
			}
		}

		if (isset($brand_path_alias) && $brand_path_alias) {
			$termInfo = $taxonomyInstance->getTermInfoByPathAlias($brand_path_alias);
			if (isset($termInfo) && $termInfo) {
				$brand_tid = isset($termInfo->tid) ? $termInfo->tid : null;
				$pvid = $termInfo->pvid;
				$pvtitle .= str_replace('-', ' ', $brand_path_alias);
				$relateName[] =  $termInfo->name;
			} else {
				goto404(t('Can not found this Page'));
			}
		}

		$tids = array();
		if (isset($tid_path_alias) && $tid_path_alias) {
			$tids_path_alias = explode('_', $tid_path_alias);
			foreach ($tids_path_alias as $path_alias) {
				$termInfo = $taxonomyInstance->getTermInfoByPathAlias($path_alias);
				if (isset($termInfo) && $termInfo) {
					$tids[] = $termInfo->tid;
					$pvid = $termInfo->pvid;
					$pvtitle .= str_replace('-', ' ', $path_alias);
					$relateName[] =  $termInfo->name;
				} else {
					goto404(t('Can not found this Page'));
				}
			}
		}

		$pageInstance = PageVariable_Model::getInstance();
		if(isset($pvid)){
			$pageInfo = $pageInstance->selectPageVariables($pvid, 'term', $termInfo);
		}else{
			$pageInfo = $pageInstance->getPageVariableByKey('allgoods');
		}
		if (isset($pageInfo) && $pageInfo) {
			$this->view->setTitle($pageInfo->title, $pageInfo->meta_keywords, $pageInfo->meta_description, $pageInfo->var1, $pageInfo->var2, $pageInfo->var3, $pageInfo->var4, $pageInfo->var5, $pageInfo->var6);
		}

		$getrandomfilter = array(
      'tid' => isset($directory_tid) ? $directory_tid : null,
      'keyword' => isset($termInfo->name) ? $termInfo->name : null
		);
		callFunction('getrandom', 'browse', $this,  $getrandomfilter);

		if (isset($type) && $type) {
			$pvtitle .= ' ' . $type;
		}

		$fieldsarr = array();
		if (isset($fields) && $fields) {
			$fieldsarr1 = explode('_', $fields);
			foreach ($fieldsarr1 as $field) {
				$field = explode('=', $field);
				$pvtitle .= ' ' . $field[1];
				if (is_array($field) && $field && isset($field[0]) && isset($field[1])) {
					$filedsarr[$field[0]] = $field[1];
				}
			}
		}
		$pricesarr = array();
		if (isset($prices) && $prices) {
			$pricesarr = explode('_', $prices);
		}

		$page = isset($page) && $page ? $page : 1;
		$listMode = isset($_SESSION['browseListConfig']['listMode']) ? $_SESSION['browseListConfig']['listMode'] : 'photo';
		$orderby = isset($_SESSION['browseListConfig']['orderby']) ? $_SESSION['browseListConfig']['orderby'] : null;
		$filter = array(
      'directory_tid' => isset($directory_tid) ? $directory_tid : null,
      'brand_tid' => isset($brand_tid) ? $brand_tid : null,
      'tids' => isset($tids) ? $tids : null,
      'type' => isset($type) ? $type : null,
      'filedsarr' => isset($filedsarr) ? $filedsarr : null,
      'pricesarr' => isset($pricesarr) ? $pricesarr : null,
      'orderby' => isset($orderby) ? $orderby : null,
		);
		$productCount = $this->_productInstance->searchProductCount($filter);
		$pageRows = isset($_SESSION['browseListConfig']['pageRows']) ? $_SESSION['browseListConfig']['pageRows'] : (key_exists('goodsListNum', $this->_displayInfo) ? $this->_displayInfo['goodsListNum'] : 60);

		$pageCount = 0;
		$eachPage = 0;
		if($pageRows == 'all' || $pageRows <= 0){
			$pageCount = 1;
			$eachPage = $productCount;
		}
		else{
			$eachPage = $pageRows;
			$pageCount = ceil($productCount/$pageRows);
		}
		$productList = $this->_productInstance->searchProductList($filter, $page, $eachPage);

		//get most commented products.
		$termPathAlias = isset($termInfo->path_alias) && $termInfo->path_alias !== '' ? $termInfo->path_alias : 'product';
		$commentInstance = Comment_Model::getInstance();
		$configure = Bl_Config::get('widgetFiveStartsStatus');
		$mostCommentedProducts = $this->_productInstance->getPopularCommentedProducts($termInfo->tid, 10);
		foreach($mostCommentedProducts as $k=>$v){
			
			$v->url = ($v->path_alias !== '' ? $v->path_alias : $v->pid).'-p'.$v->sn . '.html';
			//$v->url = $termPathAlias . '/' . ($v->path_alias !== '' ? $v->path_alias : $v->pid) . '.html';
			if($configure === '1'){
				$commentsByGrade = widgetCallFunction('fivestars', 'getcommentsbygrade', $v->pid, null, 1);
				if($commentsByGrade && count($commentsByGrade) > 0){
					$v->popularComment = $commentsByGrade[0];
				}else{
					//get the comment generated recently
					$commentList = $commentInstance->getCommentsListByProductId($v->pid, $filter = array('status' => 1, 'orderby'=> 'created DESC '));
					if($commentList && count($commentList > 0)){
						$v->popularComment = $commentList[0];
						$v->popularComment->grade = 0;
					}
				}
			}
		}

		$productInfo = array('list' => $productList, 'count' => $productCount);
		$productInfo = (object) $productInfo;

		$lfilter['tids'] = $filter['directory_tid'];
		callFunction('listproduct', $lfilter, $productInfo);

		$productList = $productInfo->list;
		$productCount = $productInfo->count;

		if (isset($productList) &&$productList) {
			$pids = array_keys($productList);
		}

		//取搜索条件相关信息
		$condition = array(
      'directory_tid' => isset($filter['directory_tid']) ? $filter['directory_tid'] : null,
      'brand_tid' => isset($filter['brand_tid']) ? $filter['brand_tid'] : null,
      'tids' => isset($filter['tids']) ? $filter['tids'] : null,
		);

		//取品牌
		if ($vocabularyInfo = $taxonomyInstance->getVocabularyInfoByVid(Taxonomy_Model::TYPE_BRAND)) {
			$brandsList = $taxonomyInstance->getBrandListByProduct($vocabularyInfo->vid, $condition['directory_tid']);
			$this->view->assign('brandsList', isset($brandsList) ? $brandsList : null);
		}

		//取最大商品价，最小商品价格
		$priceLimits = $this->_productInstance->getHighAndLowPrice($condition);
		$this->view->assign('priceLimits', isset($priceLimits) ? $priceLimits : null);

		//get the price range to high light the item of the template
		$this->view->assign('pricesarr', isset($pricesarr) ? $pricesarr:null);


		//取商品类型
		if ($typesList = $this->_productInstance->getTypeListByProduct($condition)) {
			$this->view->assign('typesList', isset($typesList) ? $typesList : null);
		}

		//取商品属性
		if (isset($filter['type']) && $filter['type']) {
			$fieldsList = $this->_productInstance->getProductFieldsIndex($filter['type']);
			$this->view->assign('fieldsList', isset($fieldsList) ? $fieldsList : null);
		}
		$urlPage = $urlPage .'+%d';


		if (isset($prices) && $prices) {
			$pvtitle .= ' ' . $prices;
		}

		$breadcrumb_tid = (isset($directory_tid) && $directory_tid) ? $directory_tid :
		((isset($brand_tid) && $brand_tid) ? $brand_tid :
		((isset($tids[0]) && $tids[0]) ? $tids[0] : 0));

		$breadcrumb = array();
		$breadcrumb[] = array(
      'title' => key_exists('productListHomeName',$this->_displayInfo) ? $this->_displayInfo['productListHomeName'] : 'Home',
      'path' => '',
		);

		if ($termInfo = $taxonomyInstance->getTermInfo($breadcrumb_tid)) {

			if (!$termInfo->ptid1) {
				$termParents['directory_tid1'] = $termInfo->tid;
			} else if (!$termInfo->ptid2) {
				$termParents['directory_tid1'] = $termInfo->ptid1;
				$termParents['directory_tid2'] = $termInfo->tid;
			} else if (!$termInfo->ptid3) {
				$termParents['directory_tid1'] = $termInfo->ptid1;
				$termParents['directory_tid2'] = $termInfo->ptid2;
				$termParents['directory_tid3'] = $termInfo->tid;
			} else {
				$termParents['directory_tid1'] = $termInfo->ptid1;
				$termParents['directory_tid2'] = $termInfo->ptid2;
				$termParents['directory_tid3'] = $termInfo->ptid3;
				$termParents['directory_tid4'] = $termInfo->tid;
			}
			if ($termParents) {
				foreach ($termParents as $tid) {
					$term = $taxonomyInstance->getTermInfo($tid);
					$product->directory[] = $term;
					if($term->vid == 4){
						$breadpath = '++';
					}elseif($term->vid == 3){
						$breadpath = '';
					}elseif($term->vid == 2){
						$breadpath = '+';
					}
					$breadcrumb[] = array(
	            'title' => t($term->name),
	            'path' => isset($breadpath) ? ($breadpath. $term->path_alias . '.html') : categoryURL($term->path_alias),
					);
				}
			}
		}

		if ($directory_path_alias == 'all') {
			$breadcrumb[] = array(
        'title' => 'all',
			);
		}

		setBreadcrumb($breadcrumb);

		if ($page == 1) {
			$_SESSION['FirstPath']['browse'] = trim($_SERVER['REQUEST_URI'], '/');
		}
		$firstPath = isset($_SESSION['FirstPath']['browse']) ? $_SESSION['FirstPath']['browse'] : null;

		if (isset($clear) && $clear) {
			unset($_SESSION['browseListConfig']);
		}
		callFunction('getProductList', $this, $productList, $termInfo);



		//get category sibling.
		$termChildren = array();
		$termSiblings = array();
		if(isset($termInfo) && $termInfo){
			$taxonomyInstance = Taxonomy_Model::getInstance();
			$termChildrenIds = $taxonomyInstance->getTermChildsByParent($termInfo->tid);

			foreach($termChildrenIds as $k=> $v){
				$termChildren[$v] = $taxonomyInstance->getTermInfo($v);
			}

			$explodeSelf = true;
			if(count($termChildren) == 0){
				$explodeSelf = false;
			}
			$termSiblingIds = $taxonomyInstance->getTermSibling($termInfo->tid, $explodeSelf);

			foreach($termSiblingIds as $k=> $v){
				$termSiblings[$v] = $taxonomyInstance->getTermInfo($v);
			}
		}

		$templateFile = (isset($termInfo->template) && $termInfo->template) ? $termInfo->template : 'productlist.phtml';
		
		$this->view->assign('pvtitle', isset($pvtitle) ? $pvtitle : null);
		$this->view->render($templateFile, array(
      'termInfo' => isset($termInfo) ? $termInfo : null,
      'urlArray' => isset($urlArray) ? $urlArray : null,
      'filter' => isset($filter) ? $filter : null,
      'productList' => isset($productList) ? $productList : null,
      'listMode' => $listMode,
      'page' => $page,
      'pageRows' => $pageRows,
      'orderby' => $orderby,
      'pageCount' => $pageCount,
      'productCount' => $productCount,
      'urlPage' => $urlPage,
      'termChildren' => $termChildren,
      'termSibling' => $termSiblings,
      'mostCommentedProducts' => $mostCommentedProducts,
      'showSeeAllLink' => true,
		//be notice, here the second parameter $pageCount is different from the other pagination functions.
      'pagination' => callFunction('common_pagination', $urlPage, $pageCount, $page, $firstPath, true), 
		));
	}

	public function skipAction()
	{
		if ($this->isPost()) {
			$post = $_POST;
			if (!$post) {
				gotoUrl('browse/allgoods.html');
			} else {

				$reffer_url = ltrim($_SERVER["HTTP_REFERER"], "/");

				$uri = Bl_Core::getUri();
				$siteInfo = Bl_Config::get('siteInfo');
				

				if(endsWith($reffer_url, '.html')){
					//if not ends with .html, then we will not use
					Bl_Config::get();
					if($_SERVER['HTTP_HOST'] == 'localhost'){
						$base_uri = 'http://localhost/mdtradebase/httpdocs/';
					}else{
						$base_uri = Bl_Config::get('server.baseuri', $siteInfo['siteurl'].'/');
					}
					$paths = explode($base_uri, $reffer_url);

					if(key_exists(1, $paths)){
						$reffer_url = $paths[1];
					}
					
					Bl_Core::dynamicRouter($reffer_url);

					$url = basename($reffer_url, '.html');
					$url = $url . '.html';
					if ($url == '++++++1.html' || $url == 'all++++++1.html'){
						$url = 'all.html';
					}

					$routers = Bl_Config::get('routers');
					if (isset($routers[$url]) && $routers[$url]) {
						$url = $routers[$url];
					} else {
						$url = basename($url, '.html');
					}

					$urls = explode('/', $url);
					reset($urls);
					$url = end($urls);
				}else{
					$url = '';
				}

				list($directory_path_alias, $brand_path_alias, $tid_path_alias, $type, $fields, $prices, $filters) = $urlArray = array_pad(explode('+', $url), 7, '');
				$directory_tid = isset($post['directory_path_alias']) ? $post['directory_path_alias'] : $directory_path_alias;
				$brandid = isset($post['brand_path_alias']) ? $post['brand_path_alias'] : $brand_path_alias;
				$tids = isset($post['tid_path_alias']) ? $post['tid_path_alias'] : $tid_path_alias;
				$type = isset($post['type']) ? $post['type'] : $type;
				$fileds = isset($post['fileds']) ? implode('_', $post['fileds']) : $fields;
				$prices = (isset($post['lowprice']) ? $post['lowprice'] : '') . (isset($post['highprice']) ?  '_'  . $post['highprice'] : $prices);
				$page = isset($post['page']) && $post['page'] ? $post['page'] : 1;
				/*$_SESSION['browseListConfig']['pageRows'] = (isset($post['pageRows']) && $post['pageRows']) ? $post['pageRows'] : (
				 ((isset($_SESSION['browseListConfig']['pageRows']) && $_SESSION['browseListConfig']['pageRows']) ? $_SESSION['browseListConfig']['pageRows'] : ($this->_displayInfo['goodsListNum'] ? $this->_displayInfo['goodsListNum'] : 60))
				 );*/
				$_SESSION['browseListConfig']['listMode'] = (isset($post['listMode']) && $post['listMode']) ? $post['listMode'] : (
				((isset($_SESSION['browseListConfig']['listMode']) && $_SESSION['browseListConfig']['listMode']) ? $_SESSION['browseListConfig']['listMode'] : 'photo')
				);
				$_SESSION['browseListConfig']['orderby'] = (isset($post['orderby']) && $post['orderby']) ? $post['orderby'] : (
				((isset($_SESSION['browseListConfig']['orderby']) && $_SESSION['browseListConfig']['orderby']) ? $_SESSION['browseListConfig']['orderby'] : null)
				);
				$param = $directory_tid. '+'. $brandid . '+' . $tids . '+' . $type . '+' . $fileds . '+' . $prices . '+' . $page;

				$skipUrl = categoryURL($param);
				gotoUrl($skipUrl);

				//gotoUrl('browse/' . $param . '.html');
			}
		} else {
			gotoUrl('');
		}
	}


	public function ajaxshowproductsAction(){
		if ($this->isPost()) {
			$post = $_POST;
			$reffer_url = ltrim($_SERVER["HTTP_REFERER"], "/");
			$uri = Bl_Core::getUri();
			if(endsWith($reffer_url, '.html')){
				//if not ends with .html, then we will not use
				Bl_Core::dynamicRouter($reffer_url);
				$url = basename($reffer_url, '.html');
				$url = $url . '.html';
				if ($url == '++++++1.html' || $url == 'all++++++1.html'){
					$url = 'all.html';
				}
				$routers = Bl_Config::get('routers');
				if (isset($routers[$url]) && $routers[$url]) {
					$url = $routers[$url];
				} else {
					$url = basename($url, '.html');
				}
				$urls = explode('/', $url);
				reset($urls);
				$url = end($urls);
			}else{
				$url = '';
			}
			list($directory_path_alias, $brand_path_alias, $tid_path_alias, $type, $fields, $prices, $page) = $urlArray = array_pad(explode('+', $url), 7, '');
			$urlPageFirst = $directory_path_alias . '+' . $brand_path_alias . '+' . $tid_path_alias . '+' . $type . '+' . $fields . '+' . $prices;
			$urlPage = $urlPageFirst + '%d';
			$taxonomyInstance = Taxonomy_Model::getInstance();
			if (isset($directory_path_alias) && $directory_path_alias && $directory_path_alias != 'all' && $directory_path_alias != 'allgoods') {
				$termInfo = $taxonomyInstance->getTermInfoByPathAlias($directory_path_alias);
				if (isset($termInfo) && $termInfo) {
					$directory_tid = isset($termInfo->tid) ? $termInfo->tid : null;
				}
			}
			if (isset($brand_path_alias) && $brand_path_alias) {
				$termInfo = $taxonomyInstance->getTermInfoByPathAlias($brand_path_alias);
				if (isset($termInfo) && $termInfo) {
					$brand_tid = isset($termInfo->tid) ? $termInfo->tid : null;
				}
			}
			$tids = array();
			if (isset($tid_path_alias) && $tid_path_alias) {
				$tids_path_alias = explode('_', $tid_path_alias);
				foreach ($tids_path_alias as $path_alias) {
					$termInfo = $taxonomyInstance->getTermInfoByPathAlias($path_alias);
					if (isset($termInfo) && $termInfo) {
						$tids[] = $termInfo->tid;
					}
				}
			}

			$type = isset($post['type']) ? $post['type'] : $type;
			$fileds = isset($post['fileds']) ? implode('_', $post['fileds']) : $fields;
			$prices = (isset($post['lowprice']) ? $post['lowprice'] : '') . (isset($post['highprice']) ?  '_'  . $post['highprice'] : $prices);
			$pageRows = isset($post['pageRows']) && $post['pageRows'] ? $post['pageRows'] : 60;
			$orderby = isset($_SESSION['browseListConfig']['orderby']) ? $_SESSION['browseListConfig']['orderby'] : null;
			 
			$firstPath = isset($_SESSION['FirstPath']['browse']) ? $_SESSION['FirstPath']['browse'] : null;
			 
			$page = isset($page) && $page ? $page : 1;
			$filter = array(
		      'directory_tid' => isset($directory_tid) ? $directory_tid : null,
		      'brand_tid' => isset($brand_tid) ? $brand_tid : null,
		      'tids' => isset($tids) ? $tids : null,
		      'type' => isset($type) ? $type : null,
		      'filedsarr' => isset($fileds) ? $fileds : null,
		      'pricesarr' => isset($prices) ? $prices : null,
		      'orderby' => isset($orderby) ? $orderby : null,
			);

			$productCount = $this->_productInstance->searchProductCount($filter);
			$pageCount = 0;
			$eachPage = 0;
			if($pageRows == 'all' || $pageRows <= 0){
				$pageCount = 1;
				$eachPage = $productCount;
				$page = 1;
			}
			else{
				$eachPage = $pageRows;
				$pageCount = ceil($productCount/$pageRows);
			}
			$productList = $this->_productInstance->searchProductList($filter, $page, $eachPage);
			callFunction('getProductList', $this, $productList, $termInfo);

			$this->view->render('ajax/ajaxproductlist.phtml', array(
		      'productList' => isset($productList) ? $productList : null,
		  	  'page' => $page,
		  	  'pageRows' => $pageRows,
		      'orderby' => $orderby,
			  'filter'=>$filter,
		  	  'pageCount' => $pageCount,
		  	  'productCount' => $productCount,
		      'pagination' => callFunction('common_pagination', $urlPage, $pageCount, $page, $firstPath, true), 
			));
			 
		}
	}

	public function reviewAction($url = '' , $page = 1, $clear = null)
	{
		if ($url == '') {
			gotoUrl('review/all.html');
		}
		if (!preg_match ("/.html$/i", $url)) {
			goto404(t('no page'));
		}
		$taxonomyInstance = Taxonomy_Model::getInstance();
		$url = basename($url, '.html');
		list($directory_path_alias, $brand_path_alias, $tid_path_alias, $type, $fields, $prices, $page) = $urlArray = array_pad(explode('+', $url), 7, '');
		$urlPageFirst = $urlPage = $directory_path_alias . '+' . $brand_path_alias . '+' . $tid_path_alias . '+' . $type . '+' . $fields . '+' . $prices;
		$pvtitle = '';
		$relateName = array();
		if (isset($directory_path_alias) && $directory_path_alias && $directory_path_alias != 'all' && $directory_path_alias != 'allgoods') {
			$termInfo = $taxonomyInstance->getTermInfoByPathAlias($directory_path_alias);
			if (isset($termInfo) && $termInfo) {
				$directory_tid = isset($termInfo->tid) ? $termInfo->tid : null;

				$parent = $taxonomyInstance->getTermParents($termInfo->tid);
				$termInfo->parent = $parent;
				$termGrade = 1;
				if (isset($parent[0]) && $parent[0]) {
					$termGrade = 2;
				}
				if (isset($parent[1]) && $parent[1]) {
					$termGrade = 3;
				}
				$this->view->assign('termGrade', $termGrade);

				$pvid = $termInfo->pvid;
				$pvtitle .= str_replace('-', ' ', $directory_path_alias);
				$relateName[] =  $termInfo->name;
			} else {
				goto404(t('Can not found this Page'));
			}
		}

		if (isset($brand_path_alias) && $brand_path_alias) {
			$termInfo = $taxonomyInstance->getTermInfoByPathAlias($brand_path_alias);
			if (isset($termInfo) && $termInfo) {
				$brand_tid = isset($termInfo->tid) ? $termInfo->tid : null;
				$pvid = $termInfo->pvid;
				$pvtitle .= str_replace('-', ' ', $brand_path_alias);
				$relateName[] =  $termInfo->name;
			} else {
				goto404(t('Can not found this Page'));
			}
		}

		$tids = array();
		if (isset($tid_path_alias) && $tid_path_alias) {
			$tids_path_alias = explode('_', $tid_path_alias);
			foreach ($tids_path_alias as $path_alias) {
				$termInfo = $taxonomyInstance->getTermInfoByPathAlias($path_alias);
				if (isset($termInfo) && $termInfo) {
					$tids[] = $termInfo->tid;
					$pvid = $termInfo->pvid;
					$pvtitle .= str_replace('-', ' ', $path_alias);
					$relateName[] =  $termInfo->name;
				} else {
					goto404(t('Can not found this Page'));
				}
			}
		}

		$pageInstance = PageVariable_Model::getInstance();
		if(isset($pvid)){
			$pageInfo = $pageInstance->selectPageVariables($pvid, 'term', $termInfo);
		}else{
			$pageInfo = $pageInstance->getPageVariableByKey('allgoods');
		}
		if (isset($pageInfo) && $pageInfo) {
			$this->view->setTitle($pageInfo->title, $pageInfo->meta_keywords, $pageInfo->meta_description, $pageInfo->var1, $pageInfo->var2, $pageInfo->var3, $pageInfo->var4, $pageInfo->var5, $pageInfo->var6);
		}

		//get most commented products.
		$termPathAlias = isset($termInfo->path_alias) && $termInfo->path_alias !== '' ? $termInfo->path_alias : 'product';
		$commentInstance = Comment_Model::getInstance();
		$configure = Bl_Config::get('widgetFiveStartsStatus');
		$mostCommentedProducts = $this->_productInstance->getPopularCommentedProducts($termInfo->tid);
		foreach($mostCommentedProducts as $k=>$v){
			$v->url = $termPathAlias . '/' . ($v->path_alias !== '' ? $v->path_alias : $v->pid) . '.html';
			if($configure === '1'){
				$commentsByGrade = widgetCallFunction('fivestars', 'getcommentsbygrade', $v->pid, 5, 1);
				if($commentsByGrade && count($commentsByGrade) > 0){
					$v->popularComment = $commentsByGrade[0];
				}
			}else{
				//get the comment that have the longest length.
				$commentList = $commentInstance->getCommentsListByProductId($v->pid, $filter = array('status' => 1, 'orderby'=> 'LENGTH(comment) DESC '));
				if($commentList && count($commentList > 0)){
					$v->popularComment = $commentList[0];
				}
			}
		}

		$breadcrumb_tid = (isset($directory_tid) && $directory_tid) ? $directory_tid :
		((isset($brand_tid) && $brand_tid) ? $brand_tid :
		((isset($tids[0]) && $tids[0]) ? $tids[0] : 0));

		$breadcrumb = array();
		$breadcrumb[] = array(
      'title' => key_exists('productListHomeName',$this->_displayInfo) ? $this->_displayInfo['productListHomeName'] : 'Home',
      'path' => '',
		);

		$breadcrumb[] = array(
      'title' => 'Reviews',
		);

		if ($termInfo = $taxonomyInstance->getTermInfo($breadcrumb_tid)) {
			if (!$termInfo->ptid1) {
				$termParents['directory_tid1'] = $termInfo->tid;
			} else if (!$termInfo->ptid2) {
				$termParents['directory_tid1'] = $termInfo->ptid1;
				$termParents['directory_tid2'] = $termInfo->tid;
			} else if (!$termInfo->ptid3) {
				$termParents['directory_tid1'] = $termInfo->ptid1;
				$termParents['directory_tid2'] = $termInfo->ptid2;
				$termParents['directory_tid3'] = $termInfo->tid;
			} else {
				$termParents['directory_tid1'] = $termInfo->ptid1;
				$termParents['directory_tid2'] = $termInfo->ptid2;
				$termParents['directory_tid3'] = $termInfo->ptid3;
				$termParents['directory_tid4'] = $termInfo->tid;
			}
			if ($termParents) {
				foreach ($termParents as $tid) {
					$term = $taxonomyInstance->getTermInfo($tid);
					$product->directory[] = $term;
					if($term->vid == 4){
						$breadpath = 'review/++';
					}elseif($term->vid == 3){
						$breadpath = 'review/';
					}elseif($term->vid == 2){
						$breadpath = 'review/+';
					}
					$breadcrumb[] = array(
	            		'title' => t($term->name),
	            		'path' => isset($breadpath) ? ($breadpath. $term->path_alias . '.html') : categoryURL($term->path_alias),
					);
				}
			}
		}
		setBreadcrumb($breadcrumb);

		$templateFile = (isset($termInfo->template) && $termInfo->template) ? $termInfo->template : 'reviewlist.phtml';

		$this->view->assign('pvtitle', isset($pvtitle) ? $pvtitle : null);
		$this->view->render($templateFile, array(
      'termInfo' => isset($termInfo) ? $termInfo : null,
      'mostCommentedProducts' => $mostCommentedProducts,
      'showSeeAllLink' => false,
		));
	}


}
