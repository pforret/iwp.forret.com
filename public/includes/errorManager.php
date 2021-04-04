<?php

/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/

 class errorManager{
	
	public function getErrorCodeFromService($condition){
		$errorCodeList = getOption('commonRetryErrorCode');
		if(!empty($errorCodeList)){
			$serviceErrorList = unserialize($errorCodeList);
			$commonError = $serviceErrorList['commonError'];
			unset($serviceErrorList['commonError']);
			if(!empty($serviceErrorList) && !empty($serviceErrorList[$condition])){
				$errorCodeListString  = str_replace('commonError',$commonError,$serviceErrorList[$condition]);
				return explode(',',$errorCodeListString);
			}
		}
		return array();
	}

	 public function getDefaultErrorCodes($condition){
		$defaultErrorCodes = array('3', '6','7', '16', '18', '28', '35', '52', '56', '92', '100', '500', '502', '504','503', '521', '522', '508', '524', '525', '520', '526', '527', '400', '405', '408', 'timeoutClear', 'main_plugin_connection_error');
		switch($condition){
			case 'multicallArrayCheck':
			case 'multicallInitialCheck':
			case 'phoenixRetryCheck':
				return $defaultErrorCodes;
			case 'checkTriggerStatus':
				$checkTriggerStatus = array('processingResponseDied','dropbox_error');
				return array_merge($defaultErrorCodes,$checkTriggerStatus);
			case 'retryFailedTasksChecker':
				return array('6', '7','28','500', '501', '503', '504', '524');
			default:
				return array();			
		}
	}

	public function checkErrorExists($errorCode,$condition){
		return $this->getErrorCodeByCondition($errorCode,$condition);
	}

	public function getErrorCodeByCondition($errorCode,$condition){
		if($this->checkErrorFromServer($errorCode,$condition)){
			return true;
		}
		return $this->checkErrorFromPanel($errorCode,$condition);
	}

	public function checkErrorFromServer($errorCode,$condition){
		$serviceErrorCodes = $this->getErrorCodeFromService($condition);
		if(!empty($serviceErrorCodes) && (in_array($errorCode, $serviceErrorCodes))){
			return true;
		}
		return false;
	}

	public function checkErrorFromPanel($errorCode,$condition){
		$defaultErrorCodes = $this->getDefaultErrorCodes($condition);
		if(!empty($defaultErrorCodes) && (in_array($errorCode, $defaultErrorCodes))){
			if(in_array($errorCode, $defaultErrorCodes)){
				return true;
			}
		}
		return false;
	}
}
