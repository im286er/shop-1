<?php
class CreateUserAction extends Action
{	
	public $UserList = array(
		'UnexpladfinedTechnical' => '萨达哈鲁MK-I', 
		'Duo-gon_Evhoolution' => '万事屋de天然卷', 
		'animationsad' => 'JUST WE', 
		'single_frame_efdfsdfect' => 'HellCAT', 
		'Surfacessd' => '我觉得他动了我的骨头', 
		'Extrutsd' => '忧郁的糖果', 
		'RodmanddfdCraze' => '进击的哥斯拉', 
		'3weiWordsld' => '女王不打烊', 
		'occupationaldiseasdfde' => '神圣骑士查大锤', 
		'pleasestadsdy' => '血鸦之怒', 
		'DesperateSmilesd' => '孤独的WALL.E', 
		'Gladiatosdsr' => '天使在人间', 
		'deathcoastersds' => '队友杀手普莱斯', 
		'shockheartsdss' => '狂奔的BT5', 
		'swordfishdssdd' => '古斯塔夫列车炮',
		'Csfddfcs' => '看风景的咖啡机', 
		'Schwarzeneggedfsbgdfr' => '刀马旦', 
		'LastStansdsd' => '伊丽莎白', 
		'FightClsdsafhbgfub' => '小恶魔提利昂', 
		'violentdirgefgddsf' => '傲娇的超电磁炮', 
		'BeautifulHuntesdsdgr' => '推妹能手当麻君', 
		'ButterflyEffectfgs' => '一米六的兵长大人', 
		'comicdisasterdfdfh' => '克隆人士兵', 
		'OldGonzalezghgf' => '小嗲猫', 
		'Wantsdsed' => '左手油条右手豆浆', 
		'timeslossdft' => '旮旯里的猫', 
		'Dynamicssds' => 'Tieria?Erde', 
		'3Dagedfbgbn' => '城市豹', 
		'Gravitydfdgn' => '银八森塞', 
		'Csfddfcs' => 'TOP  GUN'
	);
	
	public function index(){
		
		echo "aaa";	
	}
	
	public function doCreate()
	{	
		
		//exit;
		$resArr = array();
		$conter = 100;
		foreach ($this->UserList as $Name => $NickName)
		{
			$conter++;
			$UserPost = $this->createUserPost($Name, $NickName);
			$IsValid = $this->validRegister($UserPost);
			if($IsValid === false) { $resArr[$Name] = $this->VRError; continue; }
			$resArr[$Name] = $this->add($UserPost, $conter);
			if($resArr[$Name] === false) { $resArr[$Name] = 'add'; continue; }
		}
		var_dump($resArr);
	}

	private function add($UserPost, $Count = 0)
	{
		$Users = D('Users');
		$Users->startTrans();
		$reArr = array(); // 保存数据操作的结果集
		$Users->create($UserPost);  
		$Users->u_avatar = 'pic1/' . $Count . '.jpg';
		$Users->u_pass = md5($Users->u_pass . $Users->u_salt);
		$uid = $Users->add();
		
		$reArr[] = $uid;
		
		$UP = D('UserProfile');
		$UP->u_id = $uid;
		$reArr[] = $UP->add();
		$lastSql = $UP->getLastSql();
		
		$UA = D('UserAccount');
		$UA->u_id = $uid;
		$reArr[] = $UA->add();
		$lastSql = $UA->getLastSql();
		
		if(in_array(false, $reArr))
		{
			$Users->rollback();
			return false;
		}else{
			$Users->commit();
			// 任务系统
			$HJ = new HookJobsModel();
			$HJ->run($uid, __METHOD__);
		}
		return $uid;
	}
	
	private $VRError = null;
	private function validRegister(array $req) {
		$this->VRError = null;
		$PVC = new PVC2();
		$PVC->setStrictMode(true)->setModeArray()->SourceArray = $req;
		$PVC->isString()->add('nickname');
		$PVC->isEMail()->add('email');
		$PVC->isString()->add('password');
		if(!$PVC->verifyAll()) { return false; }
		$Users = D('Users');
		$res = $Users->getByu_email($req['email']);
		if(is_array($res)) { $this->VRError = 'email registered'; return false; }
		return true;
	}
	
	private function createUserPost($Name, $NickName)
	{
		$UserPost = array(
				'email' => $Name . '@bitmap3d.com',
				'nickname' => $NickName,
				'password' => '1234567890gdicorp'
		);
		return $UserPost;
	}


}
?>