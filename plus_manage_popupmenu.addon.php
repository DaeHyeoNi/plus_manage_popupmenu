<?php
	if(!defined('__XE__')) exit();
	
	/**
	* @file plus_manage_popupmenu.addon.php
	* @brief 팝업메뉴에 확장된 관리 메뉴를 넣습니다. https://www.xpressengine.com/tip/22925896 팁을 애드온화 한것입니다.
	**/

	$logged_info = Context::get('logged_info');
	if($addon_info->non_user_access == 'N'){
		if(isCrawler() || !$logged_info) return;
	}
	
	if($addon_info->user_access == 'N'){ //만약에 일반 유저에게 해당 확장 메뉴를 보여주지 않을것이라면
		if($logged_info->is_admin != 'Y') return; //관리자 제외 종료
	}
	
	if($called_position == 'before_module_proc'){
		$oMemberController = getController('member');
		$oPointModel = &getModel('point');
		$oModuleModel = &getModel('module');
		$config = $oModuleModel->getModuleConfig('point');
		
		$oMemberModel = &getModel('member');
		$member_srl = Context::get('target_srl'); // target_srl == member_srl
		$member_info = $oMemberModel->getMemberInfoByMemberSrl($member_srl);

		$point = $oPointModel->getPoint($member_srl);
		$level = $oPointModel->getLevel($point, $config->level_step);
		
		//포인트 이름 받아옴
		$point_name = $config->point_name;
		
		//색상 설정
		$color = $addon_info->color;
		if(!$color) $color = "#526bbe"; //default value
		
		//메뉴 가져오기
		if($addon_info->register_form) $reg = $addon_info->register_form;
		else $reg = "Y-m-d";
		foreach($member_info->group_list as $key => $val) {
			$stack .= "[".$val."]";
		}
		$str = '<div style="border-top:1px solid#dfdfdf; border-bottom:1px solid#dfdfdf;">
			<b><font color="'.$color.'">레벨</font> : '.$level.'
			<br /><font color="'.$color.'">'.$point_name.'</font> : '.$point.'
			<br /><font color="'.$color.'">가입</font> : '.zdate($member_info->regdate, $reg).'
			<br /><font color="'.$color.'">그룹</font> : '.$stack.'</b>
			</div>';
		
		$oMemberController->addMemberPopupMenu("javascript:;", $str, '', 'self');
		
		//회원 포인트 설정 (관리자 일때만)
		if($logged_info->is_admin == 'Y' && $addon_info->point == 'Y'){
		$url = getUrl('','module','admin','act','dispPointAdminPointList','search_target','user_id','search_keyword',$member_info->user_id);
		$str = '<b>'.$point_name.' 설정 바로가기</b>';
		$oMemberController->addMemberPopupMenu($url, $str,  '', 'blank');
		}
		
		//Conory님의 포인트 적립 내용 기록 모듈을 사용하는경우 해당 메뉴도 표시.
		//관리자 전용
		$filepath = _XE_PATH_ . 'modules/pointhistory/pointhistory.model.php';
		
		if(file_exists($filepath) && $logged_info->is_admin == 'Y' && $addon_info->pointhistory == 'Y') { //관리자라면 삭제도 가능한 히스토리 모듈의 적립 내역으로 이동
			$url = getUrl('','module','admin','act','dispPointhistoryAdminList','search_target','user_id','search_keyword',$member_info->user_id);
			$str = '<b>'.$point_name.' 적립내역 보기</b>';
			$oMemberController->addMemberPopupMenu($url, $str, '', 'blank');
		}
	}
?>
