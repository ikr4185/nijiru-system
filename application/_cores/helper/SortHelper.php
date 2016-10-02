<?php
namespace Cores\Helper;

/**
 * Class SortHelper
 * \Cores\Helper\SortHelper::sort( $array, $sortKey, $is_desc=false )
 * @package Cores\Helper
 */
class SortHelper {

	/**
	 * 連想配列のソート
	 * @param $array
	 * @param $sortKey
	 * @param bool $is_desc
	 * @return mixed
	 */
	public static function sort( $array, $sortKey, $is_desc=false ) {

		foreach ( $array as $key => $value) {
			$sort[$key] = $value[$sortKey];
		}

		if ($is_desc) {
			array_multisort($sort, SORT_DESC, $array);
		}else{
			array_multisort($sort, SORT_ASC, $array);
		}

		return $array;
	}

}