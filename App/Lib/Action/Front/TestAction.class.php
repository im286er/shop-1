<?php
class TestAction extends CommonAction {

	public function encodepass(){
		$text = '123456789';
		$publickey = 'O4rDRqwshSBojonvTt4mar21Yv1Ehmqm';

		$encodeText = pub_encode_pass($text,$publickey,'encode');
		$decodeText = pub_encode_pass($encodeText,$publickey,'decode');

		$rand = rand();
		echo 'Random: ' . $rand;
		echo 'Text: ' . $text;
		echo 'Encode: ' . $encodeText;
		echo 'Encode Length: ' . strlen($encodeText);
		echo 'Decode: ' . $decodeText;
	}

	public function rushuser() {
		// 读取文件
		$conts = file_get_contents ( 'D:\Zend\WorkSpace\3DF\App\Common\Front\maillist.txt' );
		$arrConts = explode ( "\n", $conts );
		// 生成用户数
		$n = intval ( $_GET ['n'] );
		if ($n < 1) {
			$n = 1;
		}
		// 循环计数
		$i = 0;
		$p = 0;
		// try {
		do {
			$item = $arrConts [$i];
			$item_arr = explode ( '@', $item );
			$username = $item_arr [0];
			if (strlen ( $username ) < 5) {
				if (strlen ( $username ) < 4) {
					$username .= '_';
				}
				$rand_1 = mt_rand ( 1, 9 );
				$rand_2 = mt_rand ( 1, 9 );
				$rand = $rand_1 . $rand_2;
				$username = str_pad ( $username, 5, $rand, STR_PAD_RIGHT );
			}
			
			$data ['u_name'] = $username;
			$data ['u_realname'] = $username;
			$data ['u_pass'] = md5 ( $username );
			$data ['u_email'] = $item;
			$data ['u_dispname'] = $username;
			$data ['u_type'] = 9;
			$data ['u_title'] = L ( 'normal_user' );
			$data ['u_createdate'] = get_now ();
			$data ['u_status'] = 0;
			
			$u = D ( 'Users' );
			// 增加判断u_name和u_email
			$res_1 = $u->getByu_name ( $data ['u_name'] );
			$res_2 = $u->getByu_email ( $data ['u_email'] );
			if (is_array ( $res_1 ) || is_array ( $res_2 )) {
				// throw new Exception ( 'Username or Usermail have exist!' );
				$i += 1;
				echo 'Username or Usermail have exist!-->' . $data ['u_name'] . '<br><br>';
			} else {
				$res = $u->add ( $data );
				// $res = 1;
				if ($res) {
					$i += 1;
					$p += 1;
					echo 'Rush it!-->' . $data ['u_name'] . '-->' . $data ['u_email'] . '<br><br>';
				}
			}
		} while ( $p < $n );
		// } catch ( Exception $e ) {
		// echo $e->getMessage () . $data ['u_name'] . '<br><br>';
		// }
	}
	public function putPCT() {
		$size = 1000;
		$n = intval ( $_GET ['n'] );
		if ($n < 1) {
			$n = 1;
		}
		$offset = ($n - 1) * $size;
		$pf = D ( 'ProductFile' );
		$res = $pf->where ( '1=1' )->limit ( $offset, $size )->select ();
		
		foreach ( $res as $key => $val ) {
			$pct = D ( 'ProductCreateTool' );
			$pct->find ( $val ['pf_createtool'] );
			// 获取文件类型质数和ID
			$prime = $pct->pct_prime;
			$pid = $val ['p_id'];
			// 更新产品信息
			$p = D ( 'Product' );
			$p->find ( $pid );
			// 输出
			echo 'PID->' . $pid . '---CREATETOOL->' . $val ['pf_createtool'] . '---PRIME->' . $prime;
			echo '---BEF_CTPRIM-->' . $p->p_ctprime;
			if ($p->p_ctprime == 0) {
				$tmp_val = $prime;
				$p->p_ctprime = $tmp_val;
				$p->save ();
			} elseif ((($p->p_ctprime % $prime) == 0) && ($p->p_ctprime > 0)) {
				$tmp_val = 0;
			} else {
				$tmp_val = $p->p_ctprime * $prime;
				$p->p_ctprime = $tmp_val;
				$p->save ();
			}
			// 分行
			echo '---AFT_CTPRIM-->' . $tmp_val;
			echo '<br><br>';
		}
	}
	
	public function ckb()
	{
		$arr1 = array(1,2,3);
		$arr2 = array(1,2,3);
		var_dump(array_diff($arr1, $arr2));
	}
}
?>