<?php
namespace Logics;
use Logics\Commons\AbstractLogic;

/**
 * Class ForumLogic
 * @package Logics
 */
class ForumLogic extends AbstractLogic {
	
	
	protected function getModel() {
	}
	
	
	/**
	 * RSS取得
	 * @return array
	 * @see http://community.wikidot.com/help:rss
	 */
	public function getRss($url)
	{

		$rss = simplexml_load_file( $url, 'SimpleXMLElement', LIBXML_NOCDATA );
		
		$forumItemArray = array();
		$i = 0;
		
		foreach ($rss->channel->item as $item) {
			$forumItemArray[$i]['title']	=	$item->title;
			$forumItemArray[$i]['date']		=	date("Y/n/j H:m:s", strtotime($item->pubDate));
			$forumItemArray[$i]['link']		=	$item->link;
			$forumItemArray[$i]['description']	=	mb_strimwidth(strip_tags($item->description), 0, 110, "…Read More", "utf-8");
			$forumItemArray[$i]['user']		=	$item->children('wikidot', true)->authorName;
			
			// contents整理
			$rawContents = $item->children('content', true)->encoded;
			$contents = $this->deleteTags( preg_replace('/([\s\S]*?)(\<br\/>)(フォーラムカテゴリ)([\s\S]*)/', "$1", $rawContents) );
			$category = $this->delAndDel( preg_replace('/([\s\S]*?)(\<br\/>)(フォーラムカテゴリ)([\s\S]*)/', "$3$4", $rawContents) );
			
			$forumItemArray[$i]['contents']	= $contents;
			$forumItemArray[$i]['category']	= $category;
			$i++;
		}
		
		// debug //////////////////////////////////////
//		var_dump($forumItemArray);
		// debug //////////////////////////////////////
		
		return $forumItemArray;
		
	}
	
	/**
	 * 削除＆整理
	 * @param $html
	 * @return mixed
	 */
	private function delAndAdj($html)
	{
		return $this->adjustmentBr( $this->deleteTags($html) );
	}
	
	/**
	 * 削除＆削除
	 * @param $html
	 * @return mixed
	 */
	private function delAndDel($html)
	{
		return $this->deleteBr( $this->deleteTags($html) );
	}
	
	/**
	 * htmlタグ除去
	 * @param $html
	 * @return string
	 */
	private function deleteTags($html){
		$html = strip_tags( $html,'<strong><span><em><a><ul><li><blockquote><table><tr><th><td><br><p><div>' );
		return $html;
	}
	
	/**
	 * <br>タグの整理
	 * @param $article
	 * @return mixed
	 */
	private function adjustmentBr($article)
	{
		
		// <table ~ </table>の間の<br>を削除する
		$article = preg_replace( '@(<table(.|\s)*?>)(<br>)*@', "$1", $article );
		$article = preg_replace( '@(<tr(.|\s)*?>)(<br>)*@', "$1", $article );
		$article = preg_replace( '@(<th(.|\s)*?>)(<br>)*@', "$1", $article );
		$article = preg_replace( '@(<td(.|\s)*?>)(<br>)@', "$1", $article );
		
		$article = preg_replace( '@(</table>)(<br>)*@', "$1".'', $article );
		$article = preg_replace( '@(</tr>)(<br>)*@', "$1", $article );
		$article = preg_replace( '@(</th>)(<br>)*@', "$1", $article );
		$article = preg_replace( '@(</td>)(<br>)@', "$1", $article );
		
		// <ul><ol>内の<br>を削除する
		$article = preg_replace( '@(<ul(.|\s)*?>)(<br>)*@', "$1", $article );
		$article = preg_replace( '@(<ol(.|\s)*?>)(<br>)*@', "$1", $article );
		$article = preg_replace( '@(<li(.|\s)*?>)(<br>)*@', "$1", $article );
		
		$article = preg_replace( '@(</ul>)(<br>)*@', "$1".'', $article );
		$article = preg_replace( '@(</ol>)(<br>)*@', "$1", $article );
		$article = preg_replace( '@(</li>)(<br>)*@', "$1", $article );
		
		// <a class="collapsible-block-link">前後の<br>を削除する
		$article = preg_replace( '@(<br>|\n)*(<a class="collapsible-block-link"(.|\s)*?>)(.*?)(</a>)(<br>|\n)*@', "<br>\n<br>\n<br>\n$2$4$5<br>\n<br>\n<br>\n", $article );
		
		// <blockquote>周辺の<br>を整理
		$article = preg_replace( '@(<br>|\n)*(<blockquote>)(<br>|\n)*@', "<br>\n<br>\n$2", $article );
		$article = preg_replace( '@(<br>|\n)*(</blockquote>)(<br>|\n)*@', "$2<br>\n", $article );
		
		// <br>タグの短縮
		$article = preg_replace( '/(<br>\s*){3,}/', "<br>\n<br>\n<br>\n", $article );
		
		return $article;
	}
	
	/**
	 * 不要<br>タグの削除
	 * @param $article
	 * @return mixed
	 */
	private function deleteBr($article)
	{
		// <br>タグの削除
		$article = preg_replace( '/(<br>)/', "\n", $article );
		return $article;
	}
	
}