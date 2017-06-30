<?php
/**
 * APP支付访问接口集合
 */



defined('In33hao') or exit('Access Invalid!');
header("Access-Control-Allow-Origin:*");

class mobilepaymentApiControl extends BaseGoodsControl {
	public function __construct() {
		$this->appid = "wx1cec702c7ee56a77";
		$this->mch_id = "1388299902";
		$this->notify_url = SHOP_SITE_URL.'/api/payment/wxpay/notify_appurl.php';
		$this->notify2_url = SHOP_SITE_URL.'/api/payment/wxpay/notify_water_appurl.php';
		$this->notify3_url = SHOP_SITE_URL.'/api/payment/wxpay/notify_saoma_appurl.php';
		$this->key = "kdhvergrgjxiymi11jeq4d3521rl0xvm";

		parent::__construct ();
	}


	/*************************************************微信支付**********************************************************/

	/**
	 * [getWeChatPayInfoOp 移动端获取微信支付参数]
	 * @return [type] [description]
	 */
	public function getWeChatPayInfoOp(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'2000','msg'=>'无效用户'));
			exit;
		}

		$payInfo = Model('payment') -> getPaymentOpenInfo(array('payment_code'=>'wxpay'));
		if (!empty($payInfo)) {
			$payInfo = unserialize($payInfo['payment_config']);
			exit(json_encode(array('code'=>'1000','msg'=>'有效支付方式','data'=>$payInfo)));
		}else{
			exit(json_encode(array('code'=>'2000','msg'=>'无效支付方式')));
		}
	}

	/**
	 * [wxpaystep1Op APP微信支付ORDER] 
	 * @return [type] [description]
	 */
	public function wxpaystep1Op(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'2000','msg'=>'无效用户'));
			exit;
		}

		$params['body'] = '向水来了订货平台支付';                       //商品描述
		$params['out_trade_no'] = $_GET['pay_sn'];    //自定义的订单号
		$params['total_fee'] = $_GET['pay_fee']*100;                       //订单金额 只能为整数 单位为分
		$params['trade_type'] = 'APP';                      //交易类型 JSAPI | NATIVE | APP | WAP 
		$result = $this->unifiedOrder( $params );

		if ($result['return_code'] == 'FAIL') {
			exit(json_encode(array('code'=>'2000','msg'=>$result['return_msg'])));
		}else{	
			if ($result['result_code'] == 'FAIL') {
				exit(json_encode(array('code'=>'2000','msg'=>$result['err_code_des'])));
			}else{
				$result['timestamp'] = time();
				$result['key'] = $this->key;
				exit(json_encode(array('code'=>'1000','msg'=>'获取成功','data'=>$result)));
			}	
		}
	}

	/**
	 * 统一下单方法
	 * @param   $params 下单参数
	 */
	private function unifiedOrder( $params ){
		$this->body = $params['body'];
		$this->out_trade_no = $params['out_trade_no'];
		$this->total_fee = $params['total_fee'];
		$this->trade_type = $params['trade_type'];
		$this->nonce_str = $this->genRandomString();
		$this->spbill_create_ip = $_SERVER['REMOTE_ADDR'];
		$this->params['appid'] = $this->appid;
		$this->params['mch_id'] = $this->mch_id;
		$this->params['nonce_str'] = $this->nonce_str;
		$this->params['body'] = $this->body;
		$this->params['out_trade_no'] = $this->out_trade_no;
		$this->params['total_fee'] = $this->total_fee;
		$this->params['spbill_create_ip'] = $this->spbill_create_ip;
		$this->params['notify_url'] = $this->notify_url;
		$this->params['trade_type'] = $this->trade_type;
		$this->params['attach'] = "appwxpay";
		//获取签名数据
		$this->sign = $this->MakeSign( $this->params );
		$this->params['sign'] = $this->sign;

		$xml = $this->data_to_xml($this->params);
		$response = $this->postXmlCurl($xml, "https://api.mch.weixin.qq.com/pay/unifiedorder");
		
		if( !$response ){
			return false;
		}
		$result = $this->xml_to_data( $response );
		
		if( !empty($result['result_code']) && !empty($result['err_code']) ){
			$result['err_msg'] = $this->error_code( $result['err_code'] );
		}
		return $result;
	}


	/**
	 * [wxpaystep2Op APP微信支付水币充值]
	 * @return [type] [description]
	 */
	public function wxpaystep2Op(){
		$token = $_GET['token'];
		$mobile = $_GET['mobile']; //用户ID
		$Verify = $this -> VerifyUser($mobile,$token);
		if ($Verify['state'] == '1') {
			echo json_encode(array('code'=>'2000','msg'=>'无效用户'));
			exit;
		}

		$params['body'] = '向水来了订货平台充值水币';                       //商品描述
		$params['out_trade_no'] = $_GET['pdr_sn'];    //自定义的订单号
		$params['total_fee'] = $_GET['pdr_fee']*100;                       //订单金额 只能为整数 单位为分
		$params['trade_type'] = 'APP';                      //交易类型 JSAPI | NATIVE | APP | WAP 
		$result = $this->unifiedPdr( $params );

		if ($result['return_code'] == 'FAIL') {
			exit(json_encode(array('code'=>'2000','msg'=>$result['return_msg'])));
		}else{	
			if ($result['result_code'] == 'FAIL') {
				exit(json_encode(array('code'=>'2000','msg'=>$result['err_code_des'])));
			}else{
				$result['timestamp'] = time();
				$result['key'] = $this->key;
				exit(json_encode(array('code'=>'1000','msg'=>'获取成功','data'=>$result)));
			}	
		}
	}

	/**
	 * 统一下单方法
	 * @param   $params 下单参数
	 */
	private function unifiedPdr( $params ){
		$this->body = $params['body'];
		$this->out_trade_no = $params['out_trade_no'];
		$this->total_fee = $params['total_fee'];
		$this->trade_type = $params['trade_type'];
		$this->nonce_str = $this->genRandomString();
		$this->spbill_create_ip = $_SERVER['REMOTE_ADDR'];
		$this->params['appid'] = $this->appid;
		$this->params['mch_id'] = $this->mch_id;
		$this->params['nonce_str'] = $this->nonce_str;
		$this->params['body'] = $this->body;
		$this->params['out_trade_no'] = $this->out_trade_no;
		$this->params['total_fee'] = $this->total_fee;
		$this->params['spbill_create_ip'] = $this->spbill_create_ip;
		$this->params['notify_url'] = $this->notify2_url;
		$this->params['trade_type'] = $this->trade_type;
		$this->params['attach'] = "appwxpay";
		//获取签名数据
		$this->sign = $this->MakeSign( $this->params );
		$this->params['sign'] = $this->sign;

		$xml = $this->data_to_xml($this->params);
		$response = $this->postXmlCurl($xml, "https://api.mch.weixin.qq.com/pay/unifiedorder");
		
		if( !$response ){
			return false;
		}
		$result = $this->xml_to_data( $response );
		
		if( !empty($result['result_code']) && !empty($result['err_code']) ){
			$result['err_msg'] = $this->error_code( $result['err_code'] );
		}
		return $result;
	}

	/**
	 * [wxpaystep3Op 微信扫码支付]
	 * @return [type] [description]
	 */
	public function wxpaystep3Op(){
		// $token = $_GET['token'];
		// $mobile = $_GET['mobile']; //用户ID
		// $Verify = $this -> VerifyUser($mobile,$token);
		// if ($Verify['state'] == '1') {
		// 	echo json_encode(array('code'=>'2000','msg'=>'无效用户'));
		// 	exit;
		// }

		$params['body'] = '向水来了订货平台充值水币';                       //商品描述
		$params['out_trade_no'] = $_GET['pdr_sn'];    //自定义的订单号
		$params['total_fee'] = $_GET['pdr_fee']*100;                       //订单金额 只能为整数 单位为分
		$params['trade_type'] = 'NATIVE';                      //交易类型 JSAPI | NATIVE | APP | WAP 
		$result = $this->unifiedPdr( $params );

		if ($result['return_code'] == 'FAIL') {
			exit(json_encode(array('code'=>'2000','msg'=>$result['return_msg'])));
		}else{	
			if ($result['result_code'] == 'FAIL') {
				exit(json_encode(array('code'=>'2000','msg'=>$result['err_code_des'])));
			}else{
				exit(json_encode(array('code'=>'1000','msg'=>'获取成功','data'=>$result)));
				// return $result['code_url'];
			}	
		}
	}


	/**
	 * 统一下单方法
	 * @param   $params 下单参数
	 */
	private function unifiedSaoma( $params ){
		$this->body = $params['body'];
		$this->out_trade_no = $params['out_trade_no'];
		$this->total_fee = $params['total_fee'];
		$this->trade_type = $params['trade_type'];
		$this->nonce_str = $this->genRandomString();
		$this->spbill_create_ip = $_SERVER['REMOTE_ADDR'];
		$this->params['appid'] = $this->appid;
		$this->params['mch_id'] = $this->mch_id;
		$this->params['nonce_str'] = $this->nonce_str;
		$this->params['body'] = $this->body;
		$this->params['out_trade_no'] = $this->out_trade_no;
		$this->params['total_fee'] = $this->total_fee;
		$this->params['spbill_create_ip'] = $this->spbill_create_ip;
		$this->params['notify_url'] = $this->notify2_url;
		$this->params['trade_type'] = $this->trade_type;
		$this->params['attach'] = "appwxpay";
		//获取签名数据
		$this->sign = $this->MakeSign( $this->params );
		$this->params['sign'] = $this->sign;

		$xml = $this->data_to_xml($this->params);
		$response = $this->postXmlCurl($xml, "https://api.mch.weixin.qq.com/pay/unifiedorder");
		
		if( !$response ){
			return false;
		}
		$result = $this->xml_to_data( $response );
		
		if( !empty($result['result_code']) && !empty($result['err_code']) ){
			$result['err_msg'] = $this->error_code( $result['err_code'] );
		}
		return $result;
	}


	/**
	 * 产生一个指定长度的随机字符串,并返回给用户 
	 * @param type $len 产生字符串的长度
	 * @return string 随机字符串
	 */
	private function genRandomString($len = 32) {
		$chars = array(
			"a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
			"l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
			"w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
			"H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
			"S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
			"3", "4", "5", "6", "7", "8", "9"
		);
		$charsLen = count($chars) - 1;
		// 将数组打乱 
		shuffle($chars);
		$output = "";
		for ($i = 0; $i < $len; $i++) {
			$output .= $chars[mt_rand(0, $charsLen)];
		}
		return $output;
	}


	/**
	 * 生成签名
	 *  @return 签名
	 */
	public function MakeSign( $params ){
		//签名步骤一：按字典序排序数组参数
		ksort($params);
		$string = $this->ToUrlParams($params);
		//签名步骤二：在string后加入KEY
		$string = $string . "&key=".$this->key;
		//签名步骤三：MD5加密
		$string = md5($string);
		//签名步骤四：所有字符转为大写
		$result = strtoupper($string);
		return $result;
	}

	/**
	 * 将参数拼接为url: key=value&key=value
	 * @param   $params
	 * @return  string
	 */
	public function ToUrlParams( $params ){
		$string = '';
		if( !empty($params) ){
			$array = array();
			foreach( $params as $key => $value ){
				$array[] = $key.'='.$value;
			}
			$string = implode("&",$array);
		}
		return $string;
	}

	/**
	 * 输出xml字符
	 * @param   $params     参数名称
	 * return   string      返回组装的xml
	 **/
	public function data_to_xml( $params ){
		if(!is_array($params)|| count($params) <= 0)
		{
			return false;
		}
		$xml = "<xml>";
		foreach ($params as $key=>$val)
		{
			if (is_numeric($val)){
				$xml.="<".$key.">".$val."</".$key.">";
			}else{
				$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
			}
		}
		$xml.="</xml>";
		return $xml; 
	}

	/**
	 * 以post方式提交xml到对应的接口url
	 * 
	 * @param string $xml  需要post的xml数据
	 * @param string $url  url
	 * @param bool $useCert 是否需要证书，默认不需要
	 * @param int $second   url执行超时时间，默认30s
	 * @throws WxPayException
	 */
	private function postXmlCurl($xml, $url, $useCert = false, $second = 30){       
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		if($useCert == true){
			//设置证书
			//使用证书：cert 与 key 分别属于两个.pem文件
			curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
			//curl_setopt($ch,CURLOPT_SSLCERT, WxPayConfig::SSLCERT_PATH);
			curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
			//curl_setopt($ch,CURLOPT_SSLKEY, WxPayConfig::SSLKEY_PATH);
		}
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
		$data = curl_exec($ch);
		//返回结果
		if($data){
			curl_close($ch);
			return $data;
		} else { 
			$error = curl_errno($ch);
			curl_close($ch);
			return false;
		}
	}

	/**
	 * 将xml转为array
	 * @param string $xml
	 * return array
	 */
	public function xml_to_data($xml){  
		if(!$xml){
			return false;
		}
		//将XML转为array
		//禁止引用外部xml实体
		libxml_disable_entity_loader(true);
		$data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);        
		return $data;
	}


	/**
	* 错误代码
	* @param  $code       服务器输出的错误代码
	* return string
	*/
	public function error_code( $code ){
		$errList = array(
			'NOAUTH'                =>  '商户未开通此接口权限',
			'NOTENOUGH'             =>  '用户帐号余额不足',
			'ORDERNOTEXIST'         =>  '订单号不存在',
			'ORDERPAID'             =>  '商户订单已支付，无需重复操作',
			'ORDERCLOSED'           =>  '当前订单已关闭，无法支付',
			'SYSTEMERROR'           =>  '系统错误!系统超时',
			'APPID_NOT_EXIST'       =>  '参数中缺少APPID',
			'MCHID_NOT_EXIST'       =>  '参数中缺少MCHID',
			'APPID_MCHID_NOT_MATCH' =>  'appid和mch_id不匹配',
			'LACK_PARAMS'           =>  '缺少必要的请求参数',
			'OUT_TRADE_NO_USED'     =>  '同一笔交易不能多次提交',
			'SIGNERROR'             =>  '参数签名结果不正确',
			'XML_FORMAT_ERROR'      =>  'XML格式错误',
			'REQUIRE_POST_METHOD'   =>  '未使用post传递参数 ',
			'POST_DATA_EMPTY'       =>  'post数据不能为空',
			'NOT_UTF8'              =>  '未使用指定编码格式',
		); 
		if( array_key_exists( $code , $errList ) ){
			return $errList[$code];
		}
	}

	/**
	 * [VerifyUser 用户验证]
	 * @param [type] $mobile [用户手机号码]
	 * @param [type] $token  [description]
	 */
	public function VerifyUser($mobile,$token){
		$find_user['member_mobile'] = $mobile;
		$userInfo = Model('member') -> getMemberInfo($find_user);
		$now = time();

		$result =array();
		if (empty($userInfo)) {
			$result['state'] = '1';
		}else{	
			$FindToken['token'] = $token;
			$FindToken['member_id'] = $userInfo['member_id'];
			$token_info = Model('mb_user_token') -> getMbUserTokenInfo($FindToken);

			if (!empty($token_info)) {
				if ($now - $userInfo['member_login_time'] > 259200) {
					$result['state'] = '1';
				}else{
					if ($now - $token_info['login_time'] > 259200) {
						$result['state'] = '1';
					}else{
						$result['state'] = '2';
						$result['info'] = $userInfo;
					}
				}
			}else{
				$result['state'] = '1';
			}
		}
		return $result;
	}


	

}