<?php

class magentoRewrites
{
    // main configuration storage object
	private $inputFileName; 
	private $colOld;
	private $colNew;
	private $storeId;
	private $homePageId;
		
	// class constructor
	public function __construct($sInput, $sOld, $sNew, $storeId=1, $homePageId) {
		$this->inputFileName=$sInput;
		$this->colOld=$sOld;
		$this->colNew=$sNew;
		$this->storeId=$storeId;
		$this->homePageId=$homePageId;
	}
	
	private function removeFirstSlash($str){
		return preg_replace('/^\/(.*)$/msi', '$1', $str);	
	}

	private function detectIdenticalURLs($old, $new){
		if($old==$new){
			return true;
		}
		elseif (str_replace($new, '', $old)=='/'	||	str_replace($new, '', $old)=='//'){		
			return true;	
		}
		elseif ((str_replace($old, '', $new)=='/')&&($new!='/')	||	str_replace($old, '', $new)=='//'){
			return true;	
		}
		return false;
	}

	private function isValidURL($url){
		if ($url==''){
			return false;
		}
		elseif($this->isComment($url)){
			return false;
		}
		elseif ($url=='/' || preg_match('/^\/.*$/si', $url) || preg_match('/[htps]{4,5}:\/\/[a-z0-9\.\-]+/si', $url)){
			return true;
		}
		return false;
	}

	private function isComment($url){
		if ($url=='#' || preg_match('/^#.*$/si', $url)){
			return true;
		}
		return false;
	}

	private function writeInsertOpen(){
		return 'INSERT INTO url_rewrite (entity_type,request_path,entity_id,target_path,redirect_type,store_id) VALUES ';
	}

	private function writeInsertClose(){
		return 'ON DUPLICATE KEY UPDATE store_id = '.$this->storeId.';';
	}

	private function writeInsertValue($old, $new){
		if ($new=='home'	|| $new=='home/'){
			// cms page rewrite rule for homepage
			return '("cms_page","'.$old.'","'.$this->homePageId.'", "cms/page/view/page_id/'.$this->homePageId.'","301","'.$this->storeId.'")';
		}
		else{
			// custom rules for all other pages
			return '("custom","'.$old.'","0", "'.$new.'","301","'.$this->storeId.'")';
		}
		
	}
	
	private function writeInsertReq($values){
		return $this->writeInsertOpen()."\n".implode(", \n", $values)."\n".$this->writeInsertClose()."\n";
	}	

	public function generate(){		
		$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($this->inputFileName);
		$sheetTout = $spreadsheet->getActiveSheet()->toArray(null,true,true,true);
		$validRules = array();

		foreach($sheetTout as $k => $row){
			// seulement les cases remplies
			if ($row[$this->colOld]!=null){	
				// si nouvelle url vide = homepage
				if ($row[$this->colNew]==''){
					$row[$this->colNew]='home/';
				}
				// seulement si si nouvelle url valide
				if ($this->isValidURL($row[$this->colNew])){											
					// ignorer les rewrites sur url identique
					if (!$this->detectIdenticalURLs($row[$this->colOld], $row[$this->colNew])){
						$validRules[$this->removeFirstSlash($row[$this->colOld])]=$this->removeFirstSlash($row[$this->colNew]);
					}					
				}
			}	
		}

		// rebuild list as id => value pair array
		$uniqueValidRules=array();
		foreach ($validRules as $key => $value) {
			$uniqueValidRules[]=array($key,$value);
		}

		// boucle par lot de 100
		for($i=0; $i<count($uniqueValidRules);$i=$i+100){
			$aRuleSetInsert=array();
			// boucle dans le lot
			for ($j=$i;$j<$i+100;$j++){
				if (isset($uniqueValidRules[$j])){
					$aRuleSetInsert[]=$this->writeInsertValue($uniqueValidRules[$j][0], $uniqueValidRules[$j][1]);
				}				
			}
			echo $this->writeInsertReq($aRuleSetInsert);
		}
	}
}

