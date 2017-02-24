<?php
class Sphinx_Model extends Bl_Model
{
	/**
	 * @var SphinxClient
	 */
	private $_sphinx;

	/**
	 * @return Sphinx_Model
	 */
	public static function getInstance()
	{
		Bl_Core::loadLibrary('sphinxapi');
		return parent::getInstance(__CLASS__);
	}

	/**
	 * 初始化全文检索服务器
	 * @param string $server 服务器地址
	 * @param int $port 端口号
	 */
	public function init($server, $port)
	{
		$this->_sphinx = new SphinxClient();
		$this->_sphinx->SetServer($server, $port);
	}

	/**
	 * 检索商品
	 * @param string $index 索引名称
	 * @param string $keyword 关键字
	 * @param int $page 分页数
	 * @param int $pageRows 分页行数
	 * @param array $directoryTid 商品目录分类词ID数组
	 * @param array $brandTid 品牌分类词ID数组
	 * @return array
	 */
	public function searchProducts($index, $keyword, $page = 1, $pageRows = 60, $orderby = null, $directoryTid = null, $brandTid = null)
	{
		$offset = $pageRows * ($page - 1);

		if ($offset < 0) {
			$offset = 0;
		}
		$this->_sphinx->SetFieldWeights(array('number' => 10, 'name' => 10, 'sphinx_key' => 5));
		if (isset($directoryTid)) {
  		if (isset($directoryTid) && $directoryTid) {
        $taxonomyInstance = Taxonomy_Model::getInstance();
        $termInfo = $taxonomyInstance->getTermInfo($directoryTid);
        if (isset($termInfo) && $termInfo) {
          if (!$termInfo->ptid1) {
            $this->_sphinx->SetFilter('directory_tid1', $termInfo->tid);
          } else if (!$termInfo->ptid2) {
            $this->_sphinx->SetFilter('directory_tid1', $termInfo->ptid1);
            $this->_sphinx->SetFilter('directory_tid2', $termInfo->tid);
          } else if (!$termInfo->ptid3) {
            $this->_sphinx->SetFilter('directory_tid1', $termInfo->ptid1);
            $this->_sphinx->SetFilter('directory_tid2', $termInfo->ptid2);
            $this->_sphinx->SetFilter('directory_tid3', $termInfo->tid);
          } else {
            $this->_sphinx->SetFilter('directory_tid1', $termInfo->ptid1);
            $this->_sphinx->SetFilter('directory_tid2', $termInfo->ptid2);
            $this->_sphinx->SetFilter('directory_tid3', $termInfo->ptid3);
            $this->_sphinx->SetFilter('directory_tid4', $termInfo->tid);
          }
        }
      }
		}
		if (isset($brandTid)) {
			if (!is_array($brandTid)) {
				$brandTid = array($brandTid);
			}
			$this->_sphinx->SetFilter('brand_tid', $brandTid);
		}
		
		$this->_sphinx->SetLimits($offset, $pageRows);

		//-----------------------------------------------
  	$cacheId = 'sphinx-keyword-'.md5($keyword);
  	$cache = cache::get($cacheId);
		if ($cache && isset($cache->data)) {
		  $queryKeyword = $cache->data;
		  if($orderby &&!($orderby == 'pid DESC') && (strpos($orderby,' ')!= -1)){
		    $orderArray = split(' ', $orderby);
    		$sort_field = '';
    		$sort_order = SPH_SORT_ATTR_DESC;
    		if('updated' == $orderArray[0]){
    		  $sort_field = 'updated';
    		}else if('sell_price' == $orderArray[0]){
    		  $sort_field = 'sell_price';
    		}
    		if('ASC' == $orderArray[1]){
    		  $sort_order = SPH_SORT_ATTR_ASC;
    		}
		  	$this->_sphinx->SetSortMode($sort_order, $sort_field);
		  	//$this->_sphinx->SetSortMode(SPH_SORT_EXPR, $orderby);
    	  }else{
    		//$this->_sphinx->SetSortMode(SPH_SORT_RELEVANCE, '');
    		$this->_sphinx->SetSortMode(SPH_SORT_EXTENDED, "@relevance DESC");
    	  }

		  $result = $this->_sphinx->Query($queryKeyword, $index);
		  if (isset($result['matches']) && !empty($result['matches'])) return $result;
		}
		
		$arr_new = array();
		$input_arr = array_unique(explode(' ', str_replace('-', ' ', $keyword)));
		foreach ($input_arr as $key => $value) {
			$value = trim($value);
			if ($value == '') continue;
			$arr_new[] = $value;
		}
		$input_arr = array_unique($arr_new);
		if (count($input_arr) == 0) return null;
		unset($arr_new);
		$n_arr = array();
		if (count($input_arr) > 8) {
			$n_arr[] = count($input_arr);
			$n_arr[] = 1;
		} else if (count($input_arr) > 5) {
			$n_arr[] = count($input_arr);
			$n_arr[] = 3;
			$n_arr[] = 2;
			$n_arr[] = 1;
		} else {
			for ($i = count($input_arr); $i >= 1; $i--) {
				$n_arr[] = $i;
			}
		}
		
		$keywordsArray = $this->getMultiComb($input_arr, ' ', $n_arr);
		
		foreach ($keywordsArray as $key => $queryKeyword) {
			$result = $this->_sphinx->Query($queryKeyword, $index);
			if (isset($result['matches']) && !empty($result['matches'])) {
				cache::save($cacheId, $queryKeyword, 3600);
				return $result;
			}
			if ($key > 15) {
				break;
			}
		}
		//-----------------------------------------------
		$this->_sphinx->SetMatchMode(1);
		
		if($orderby && (strpos($orderby,' ')!= -1)){
		  $orderArray = split(' ', $orderby);
		  $sort_field = '';
		  $sort_order = SPH_SORT_ATTR_DESC;
		  if('updated' == $orderArray[0]){
		    $sort_field = 'updated';
		  }else if('sell_price' == $orderArray[0]){
		    $sort_field = 'sell_price';
		  }
		  if('ASC' == $orderArray[1]){
		    $sort_order = SPH_SORT_ATTR_ASC;
		  }
		  $this->_sphinx->SetSortMode($sort_order, $sort_field);
		  //$this->_sphinx->SetSortMode(SPH_SORT_EXPR, $orderby);
		}else{
		  //$this->_sphinx->SetSortMode(SPH_SORT_RELEVANCE, '');
    		$this->_sphinx->SetSortMode(SPH_SORT_EXTENDED, "@relevance DESC");
		}

		return $this->_sphinx->Query(str_replace('-', ' ', $keyword), $index);
	}


	/**
	 *描述:把$input_arr进行组合操作，并返回一个包含所有组合的数组
	 *参数:$input_arr 操作对象,$delimit 组合的分割符号,$n 每个组合的数组单元数
	 **/
	private function getSingleComb($input_arr, $delimit, $n)
	{
		if ($n==1)
		{
			return $input_arr;
		}
		$c_num = $this->getCNum($n, count($input_arr));
		$new_arr = array();
		while (1)
		{
			$r_arr = array_rand($input_arr, $n);
			asort($r_arr);
			foreach ($r_arr as $value)
			{
				$tmp_str[] = $input_arr[$value];
			}
			$new_str = implode($delimit, $tmp_str);
			unset($tmp_str);
			if (!in_array($new_str, $new_arr))
			{
				$new_arr[] = $new_str;
			}
			if (count($new_arr) >= $c_num || count($new_arr) > 15)
			{
				break;
			}
		}
		return $new_arr;

	}

	/**
	 *描述:获得组合个数
	 *参数:$m 组合的单元数,$n 单元总数
	 **/
	private function getCNum($m, $n)
	{
		$store_m = $m;
		$store_n = $n;
		$dividend = 1;
		for ($m; $m>1; $m--)
		{
			$dividend *= $m;
		}
		$divisor = 1;
		for ($n; $n >= ($store_n - $store_m + 1); $n--)
		{
			$divisor *= $n;
		}
		return $divisor/$dividend;
	}


	/**
	 *描述:把$input_arr进行组合操作，并返回一个包含所有组合的数组
	 *参数:$input_arr 操作对象,$delimit 组合的分割符号,$n_arr 为一个数组，其值范围必须在从1到count($input_arr)之间
	 **/
	private function getMultiComb($input_arr, $delimit, $n_arr)
	{
		$return_arr = array();
		foreach ($n_arr as $value)
		{
			$return_arr = array_merge($return_arr, $this->getSingleComb($input_arr, $delimit, $value));
		}
		return $return_arr;
	}




}
