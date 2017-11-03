<?php
namespace Controllers;

use Controllers\Commons\WebController;
use Logics\RandScpLogic;

/**
 * Class RandScpController
 * @package Controllers
 */
class RandScpController extends WebController
{
    /**
     * @var RandScpLogic
     */
    protected $logic;
    
    protected function getLogic()
    {
        parent::getLogic();
        $this->logic = new RandScpLogic();
    }
    
    /**
     * ランダムURL取得のループ処理
     * @param $callbackName string コールバック関数のなまえ
     * @param int $loop int ループ回数
     * @return string 空文字列かURL
     */
    protected function getRandMulti($callbackName, $loop = 10)
    {
        $url = "";
        
        // $loopの回数まで繰り返し実行
        for ($i = 0; $i < $loop; $i = $i + 1) {
            
            // logicのURL取得関数を実行
            $url = call_user_func(array($this->logic, $callbackName));
            
            // 値が入っていればbreak
            if ($url) {
                break;
            }
            $i++;
            
            // Wikidotサーバ負荷軽減(0.2秒)
            usleep(200000);
        }
        return $url;
    }
    
    public function indexAction()
    {
        // ランダムURL取得
        $url = $this->getRandMulti("getRandScp");
        
        // 失敗時の処理
        if (!$url) {
            echo '検索に失敗しました。<br><a href="">リトライ</a>';
            exit;
        }
        
        // リダイレクト
        $this->redirectTo($url);
    }
    
    public function jpAction()
    {
        // ランダムURL取得
        $url = $this->getRandMulti("getRandJp");
        
        // 失敗時の処理
        if (!$url) {
            echo '検索に失敗しました。<br><a href="">リトライ</a>';
            exit;
        }
        
        // リダイレクト
        $this->redirectTo($url);
    }
}