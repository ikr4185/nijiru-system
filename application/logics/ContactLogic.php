<?php
namespace Logics;
use Logics\Commons\AbstractLogic;
use Logics\Commons\Mail;

/**
 * Class ContactLogic
 * @package Logics
 */
class ContactLogic extends AbstractLogic {

	/**
	 * @var Mail
	 */
	private $mail;
		
	protected function getModel() {
		$this->mail = new Mail("/home/njr-sys/public_html/application/views/mail_templates/contact.tpl");
	}
	
	/**
	 * メール送信
	 * @param $contact
	 */
	public function sendMail( $contact ) {
		
		// 現在時刻
		$now = date("Y-m-d H:i:s");
		
		// お問い合わせ番号取得
		$contact_id = file_get_contents("/home/njr-sys/public_html/log/Contact/contact_id.log");
		
		// 送信する
//		$this->mail->send('ikr_4185@njr-sys.net', array(
//			"user" => "育良 啓一郎",
//			"now" => $now,
//			"contactid" => $contact_id,
//			"name"  =>  $contact["name"],
//			"email"  =>  $contact["email"],
//			"subject"  =>  $contact["subject"],
//			"text"  =>  $contact["text"],
//		));

		$this->mail->send('ikr.4185@gmail.com', array(
			"user" => "育良 啓一郎",
			"now" => $now,
			"contactid" => $contact_id,
			"name"  =>  $contact["name"],
			"email"  =>  $contact["email"],
			"subject"  =>  $contact["subject"],
			"text"  =>  $contact["text"],
		));
		
		// お問い合わせ番号更新
		$new_contact_id = (int)$contact_id + 1;
		file_put_contents("/home/njr-sys/public_html/log/Contact/contact_id.log",(string)$new_contact_id);

		return $contact_id;
	}
	
	
	/**
	 * バリデーション
	 * @param $contact
	 * @return mixed
	 */
	public function validation( $contact ) {
		
		foreach ($contact as $key=>&$val) {

			if (empty($val)) {
				$this->setError( $key."が未入力です" );
				unset($val);
				return false;
			}

			$val = htmlspecialchars($val);
		}
		unset($val);
		return $contact;
	}

	
}