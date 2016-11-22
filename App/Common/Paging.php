<?php
// @formatter:off
function getPagingInfo($TotalCount, $NowPage, $DispCount = 30, $DispPage = 10)
{
	
	$Result = array();
	$TotalPage = ceil($TotalCount / $DispCount);
	$DispPage = $DispPage > $TotalPage ? $TotalPage : $DispPage;
	
	$NowPage = $NowPage > 0 ? $NowPage : 1;
	$NowPage = $NowPage < $TotalPage ? $NowPage : $TotalPage;
	$Result['next'] = $NowPage >= $TotalPage ? false : $NowPage + 1;
	$Result['pre'] = $NowPage > 1 ? $NowPage - 1 : false;
	$Result['now'] = $NowPage;
	$Result['totalcount'] = $TotalCount;
	$Result['totalpage'] = $TotalPage;
	$Result['disppage'] = $DispPage;
	
	$temp = $DispPage % 2 ? floor($DispPage / 2) : $DispPage / 2;
	if($NowPage <= ($DispPage - $temp))
	{ $Result['start'] = 1; }
	elseif($NowPage > ($TotalPage - $DispPage + $temp))
	{ $Result['start'] = $TotalPage - $DispPage + 1; }
	else { $Result['start'] = $NowPage - $temp; }
	return $Result;
}

// 新版
function getPagingInfo2($TotalCount, $NowPage, $DispCount = 30, $DispPage = 10, $BaseUrl, $ClassName = 'closed st')
{
	$TotalPage = ceil($TotalCount / $DispCount);
	$DispPage = $DispPage > $TotalPage ? $TotalPage : $DispPage;
	$NowPage = $NowPage > 0 ? $NowPage : 1;
	$NowPage = $NowPage < $TotalPage ? $NowPage : $TotalPage;
	
	// URL处理
	$urlPagePattern = "/-page-(\d*)/";
	$urlSuffixPattern = "/.(php|htm|html)$/";
	$suffixType = '';
	
	if (preg_match($urlPagePattern, $BaseUrl, $matches)){
		// print_r($matches);
		$BaseUrl = preg_replace($urlPagePattern, '', $BaseUrl);
	}
	
	if (preg_match($urlSuffixPattern, $BaseUrl, $matches)){
		$suffixType = $matches[0];
		$BaseUrl = preg_replace($urlSuffixPattern, '', $BaseUrl);
	}
	
	// 配置
	// 默认显示的前缀页数
	$DFT_PAGE_NUM = 10;
	$DFT_PAGE_NUM = $TotalPage <= $DFT_PAGE_NUM ? $TotalPage : $DFT_PAGE_NUM;
	$DFT_SHORT_PAGE = $TotalPage <= $DFT_PAGE_NUM ? 1 : 0;
	// 返回结果
	$Result = '';
	if ($DFT_SHORT_PAGE){
		for ($i=1;$i<=$DFT_PAGE_NUM;$i++){
			if ($i == $NowPage){
				$Result .= '<li><a href="'. $BaseUrl . '-page-' . $i . $suffixType . '" class="' . $ClassName . '">' . $i . '</a></li>';
			}else{
				$Result .= '<li><a href="'. $BaseUrl . '-page-' . $i . $suffixType . '">' . $i . '</a></li>';
			}
		}
	}else{
		// 间隔符
		$DFT_PAGE_SIGN = '<li>...</li>';
		// 下一页
		$NextPage = $NowPage + 1;
		$DFT_NEXT_PAGE = '<li><a href="'. $BaseUrl . '-page-' . $NextPage . $suffixType . '">下一页</a></li>';
		// 上一页
		$PrePage = $NowPage - 1;
		$DFT_PRE_PAGE = '<li><a href="'. $BaseUrl . '-page-' . $PrePage . $suffixType . '">上一页</a></li>';
		// 第一页
		$DFT_FIRST_PAGE = '<li><a href="'. $BaseUrl . '-page-1' . $suffixType . '">1</a></li>';
		// 最后一页
		$DFT_LAST_PAGE = '<li><a href="'. $BaseUrl . '-page-' . $TotalPage . $suffixType . '">' . $TotalPage . '</a></li>';
		// 1 2 3 4 5 ... 101 下一页
		// 上一页 1 ... 6 7 8 9 10 ... 101 下一页
		// 上一页 1 ... 97 98 99 100 101
		if ($NowPage < $DFT_PAGE_NUM){
			// 1 2 3 4 5 ... 101 下一页
			for ($i=1;$i<=$DFT_PAGE_NUM;$i++){
				if ($i == $NowPage){
					$Result .= '<li><a href="'. $BaseUrl . '-page-' . $i . $suffixType . '" class="' . $ClassName . '">' . $i . '</a></li>';
				}else{
					$Result .= '<li><a href="'. $BaseUrl . '-page-' . $i . $suffixType . '">' . $i . '</a></li>';
				}
			}
			$Result .= $DFT_PAGE_SIGN;
			$Result .= $DFT_LAST_PAGE;
			$Result .= $DFT_NEXT_PAGE;
		}elseif ($NowPage >= $TotalPage - $DFT_PAGE_NUM){
			// 上一页 1 ... 97 98 99 100 101
			$Result .= $DFT_PRE_PAGE;
			$Result .= $DFT_FIRST_PAGE;
			$Result .= $DFT_PAGE_SIGN; 
			for ($i=$TotalPage-$DFT_PAGE_NUM;$i<=$TotalPage;$i++){
				if ($i == $NowPage){
					$Result .= '<li><a href="'. $BaseUrl . '-page-' . $i . $suffixType . '" class="' . $ClassName . '">' . $i . '</a></li>';
				}else{
					$Result .= '<li><a href="'. $BaseUrl . '-page-' . $i . $suffixType . '">' . $i . '</a></li>';
				}
			}
		}else{
			// 上一页 1 ... 6 7 8 9 10 ... 101 下一页
			$limit_page = ($DFT_PAGE_NUM - 1) / 2;
			$Result .= $DFT_PRE_PAGE;
			$Result .= $DFT_FIRST_PAGE;
			$Result .= $DFT_PAGE_SIGN;
			for ($i=$NowPage-$limit_page;$i<=$NowPage+$limit_page;$i++){
				if ($i == $NowPage){
					$Result .= '<li><a href="'. $BaseUrl . '-page-' . $i . $suffixType . '" class="' . $ClassName . '">' . $i . '</a></li>';
				}else{
					$Result .= '<li><a href="'. $BaseUrl . '-page-' . $i . $suffixType . '">' . $i . '</a></li>';
				}
			}
			$Result .= $DFT_PAGE_SIGN;
			$Result .= $DFT_LAST_PAGE;
			$Result .= $DFT_NEXT_PAGE;
		}
	}
	return $Result;
}


// page的参数符号为斜线
function getPagingInfo2_yuan($TotalCount, $NowPage, $DispCount = 30, $DispPage = 10, $BaseUrl)
{
	$TotalPage = ceil($TotalCount / $DispCount);
	$DispPage = $DispPage > $TotalPage ? $TotalPage : $DispPage;
	$NowPage = $NowPage > 0 ? $NowPage : 1;
	$NowPage = $NowPage < $TotalPage ? $NowPage : $TotalPage;

	// 配置
	// 默认显示的前缀页数
	$DFT_PAGE_NUM = 5;
	$DFT_PAGE_NUM = $TotalPage <= $DFT_PAGE_NUM ? $TotalPage : $DFT_PAGE_NUM;
	$DFT_SHORT_PAGE = $TotalPage <= $DFT_PAGE_NUM ? 1 : 0;
	// 返回结果
	$Result = '';
	if ($DFT_SHORT_PAGE){
		for ($i=1;$i<=$DFT_PAGE_NUM;$i++){
			if ($i == $NowPage){
				$Result .= '<li><a href="'. $BaseUrl . '/page/' . $i . '" class="closed st">' . $i . '</a></li>';
			}else{
				$Result .= '<li><a href="'. $BaseUrl . '/page/' . $i . '">' . $i . '</a></li>';
			}
		}
	}else{
		// 间隔符
		$DFT_PAGE_SIGN = '<li>...</li>';
		// 下一页
		$NextPage = $NowPage + 1;
		$DFT_NEXT_PAGE = '<li><a href="'. $BaseUrl . '/page/' . $NextPage . '">下一页</a></li>';
		// 上一页
		$PrePage = $NowPage - 1;
		$DFT_PRE_PAGE = '<li><a href="'. $BaseUrl . '/page/' . $PrePage . '">上一页</a></li>';
		// 第一页
		$DFT_FIRST_PAGE = '<li><a href="'. $BaseUrl . '/page/1">1</a></li>';
		// 最后一页
		$DFT_LAST_PAGE = '<li><a href="'. $BaseUrl . '/page/' . $TotalPage . '">' . $TotalPage . '</a></li>';
		// 1 2 3 4 5 ... 101 下一页
		// 上一页 1 ... 6 7 8 9 10 ... 101 下一页
		// 上一页 1 ... 97 98 99 100 101
		if ($NowPage < $DFT_PAGE_NUM){
			// 1 2 3 4 5 ... 101 下一页
			for ($i=1;$i<=$DFT_PAGE_NUM;$i++){
				if ($i == $NowPage){
					$Result .= '<li><a href="'. $BaseUrl . '/page/' . $i . '" class="closed st">' . $i . '</a></li>';
				}else{
					$Result .= '<li><a href="'. $BaseUrl . '/page/' . $i . '">' . $i . '</a></li>';
				}
			}
			$Result .= $DFT_PAGE_SIGN;
			$Result .= $DFT_LAST_PAGE;
			$Result .= $DFT_NEXT_PAGE;
		}elseif ($NowPage >= $TotalPage - $DFT_PAGE_NUM){
			// 上一页 1 ... 97 98 99 100 101
			$Result .= $DFT_PRE_PAGE;
			$Result .= $DFT_FIRST_PAGE;
			$Result .= $DFT_PAGE_SIGN;
			for ($i=$TotalPage-$DFT_PAGE_NUM;$i<=$TotalPage;$i++){
				if ($i == $NowPage){
					$Result .= '<li><a href="'. $BaseUrl . '/page/' . $i . '" class="closed st">' . $i . '</a></li>';
				}else{
					$Result .= '<li><a href="'. $BaseUrl . '/page/' . $i . '">' . $i . '</a></li>';
				}
			}
		}else{
			// 上一页 1 ... 6 7 8 9 10 ... 101 下一页
			$limit_page = ($DFT_PAGE_NUM - 1) / 2;
			$Result .= $DFT_PRE_PAGE;
			$Result .= $DFT_FIRST_PAGE;
			$Result .= $DFT_PAGE_SIGN;
			for ($i=$NowPage-$limit_page;$i<=$NowPage+$limit_page;$i++){
				if ($i == $NowPage){
					$Result .= '<li><a href="'. $BaseUrl . '/page/' . $i . '" class="closed st">' . $i . '</a></li>';
				}else{
					$Result .= '<li><a href="'. $BaseUrl . '/page/' . $i . '">' . $i . '</a></li>';
				}
			}
			$Result .= $DFT_PAGE_SIGN;
			$Result .= $DFT_LAST_PAGE;
			$Result .= $DFT_NEXT_PAGE;
		}
	}
	return $Result;
}
?>