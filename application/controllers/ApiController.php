<?php
namespace Controllers;
use Controllers\Commons\AbstractController;
//use Logics\ContactLogic;
use Inputs\BasicInput;


/**
 * Class ApiController
 * @package Controllers
 */
class ApiController extends AbstractController
{

    /**
     * @var ContactLogic
     */
    protected $logic;
    /**
     * @var BasicInput
     */
    protected $input;

    protected function getLogic() {
//        $this->logic = new ContactLogic();
    }

    protected function getInput() {
        $this->input = new BasicInput();
    }

    public function indexAction() {
        echo json_encode("error");
    }

    public function saveWhiteBoardAction()
    {
        $data = $this->input->getRequest("data");

        if (!empty($data)) {
            echo json_encode("ok {$data}");
        } else{
            echo json_encode("error {$data}");
            exit;
        }

        //ヘッダに「data:image/png;base64,」が付いているので、それは外す
        $canvas = preg_replace("/data:[^,]+,/i","",$data);

        //残りのデータはbase64エンコードされているので、デコードする
        $canvas = base64_decode($canvas);

        //まだ文字列の状態なので、画像リソース化
        $image = imagecreatefromstring($canvas);

        //画像として保存（ディレクトリは任意）
        imagesavealpha($image, TRUE); // 透明色の有効
        imagepng($image ,'/home/njr-sys/public_html/node_application/foundation_wb/test.png');

        exit;
    }

}