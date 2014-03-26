<?php

/*
This class use to parser the certain branch of html, regular expression is hard to parser nested html
$pattern = "/<div[^>]*>(.*?)<\/div>/im";
The above pattern using none greedy only support not nested html


$text = '<div class="main"> content1 <div class="d1"> content2 </div> ...</div>';
$ob = new HtmlBranchParser();
$ob->init($text, '<div class="main">', '</div>', 'div');
$ob->execute();
$arrResult = $ob->getResult();
print_r($arrResult);
*/
class HtmlBranchParser {

	public $strStart;		// start string
	public $arrStrStart;	// start string array
	public $intStartLength;	// number of letters in the start string

	public $strClose;		// close string
	public $arrStrClose;	// close string array
	public $intCloseLength;	// number of letters in the end string

	public $strSource;			// original source string
	public $intSourceLength;	// The source string length

	public $strTag;			// This is the same tag with start string
	public $arrStrTag;		// This is the tag string array;
	public $intTagLength;	// number of letters in the tag string

	//public $blnMatchOpen = false;	//
	//public $blnTagOpen = false;		// This flag set the child tag open
	public $intTagOpen = 0;			// This flag set how many child tags open

	public $arrResult = array();	// store how many result, each row store one result
	public $blnStartRecord = false;	// start record the inner html
	public $strTempResult = '';		// this temp var to store record string

	/**
	 * This is the initial function to set up all necessary parameter
	 *
	 * @param	$htmlSource	The original html source
	 * @param	$startText	The starting text   (<div class="myclass">)
	 * @param	$closeText	The closeing text   (</div>)
	 * @param	$tag		The html tag		('div', don't put any '<>' for the tag here)
	 */
	public function init($htmlSource, $startText, $closeText, $tag=null) {
		$this->strSource = $htmlSource;
		$this->strStart = $startText;
		$this->strClose = $closeText;

		$this->arrStrStart = str_split($this->strStart);
		$this->intStartLength = count($this->arrStrStart);
		$this->arrStrClose = str_split($this->strClose);
		$this->intCloseLength = count($this->arrStrClose);

		$this->intSourceLength = strlen($this->strSource);
		if($tag==null){
			$tempTag = $closeText;
			$tempTag = str_replace('/', '', $tempTag);
			$tempTag = str_replace('>', '', $tempTag);
			$this->strTag = $tempTag;
			$this->arrStrTag = str_split($this->strTag);
			$this->intTagLength = count($this->arrStrTag);
		}else{
			$this->strTag = $tag;
			$this->arrStrTag = str_split($this->strTag);
			$this->intTagLength = count($this->arrStrTag);
		}
	}

	public function clean(){
		$this->intTagOpen = 0;			// This flag set how many child tags open
		$this->arrResult = array();	// store how many result, each row store one result
		$this->blnStartRecord = false;	// start record the inner html
		$this->strTempResult = '';		// this temp var to store record string
	}

	public function getResult() {
		return $this->arrResult;
	}

	public function execute() {

		if(empty($this->strSource) || empty($this->strStart) || empty($this->strClose)){
			echo "Please use init() function to set all the parameter first\n";
			exit;
		}

		$blnStartHtmlTag = false;	// This is start with html tag, any '<>'

		for($i=0; $i<$this->intSourceLength; $i++){
			$str = substr($this->strSource, $i, 1);

			//if($i>50) exit;

			if($str == '<'){
				// start with '<'
				$blnStartHtmlTag = true;
				$j = 0;	// set the $j var, and ready to loop throught start string
				if($this->blnStartRecord){
					$blnStartParseTag = true;	// assume start for the pasering tag, only when recording start
				}else{
					$blnStartParseTag = false;
				}
				$blnStartRecordTag = true;		// assume start for recording tag
				$blnStartCloseTag = true;		// assume start for the closing tag, it might be child tag

			}

			// indicated one html tag start, will only process any letter within '<>';
			if($blnStartHtmlTag){

				// check if closing tag
				if(isset($this->arrStrClose[$j])){
					if($str != $this->arrStrClose[$j]){
						//echo "close tag: " . $this->arrStrClose[$j] . " | j: $j | str: $str\n";
						$blnStartCloseTag = false;
					}
				}

				// check to see if similar child tag start
				if(isset($this->arrStrTag[$j])){
					//echo "arrStrTag: " . $this->arrStrTag[$j] . "\n";
					if($str != $this->arrStrTag[$j]){
						//echo "blnStartParseTag = false | $str \n";
						$blnStartParseTag = false;
					}
				}

				if($str == '>'){
					//echo "close tag\n";
					$blnStartHtmlTag = false;	// mark html tag stop
					// if find the child tag start, add 1 to intTagOpen
					if($blnStartParseTag){
						//echo "intTagOpen++\n";
						$this->intTagOpen++;
					}

					// check closing tag, if child tag open, then intTagOpen-1, otherwise means stop recording
					if($blnStartCloseTag){
						if($this->intTagOpen > 0){
							//echo "close once\n";
							$this->intTagOpen--;
						}else{
							$this->blnStartRecord = false;

							// need to remove the last closing tag '</div'
							if($this->strTempResult != ''){
								$this->strTempResult = substr($this->strTempResult, 0, -($this->intCloseLength-1));
								$this->arrResult[] = $this->strTempResult;
								$this->strTempResult = '';
							}
						}
					}
				}


				// check if the recording tag start
				if($str == $this->arrStrStart[$j]){
					//echo "here:$str | $j | " . $this->intStartLength . " | $blnStartRecordTag\n";
					if($j == ($this->intStartLength-1) && $blnStartRecordTag){
						//echo "start record\n";
						$this->blnStartRecord = true;
						$j++;
						continue;	// don't record the '>'
					}
				}else{
					//echo "==false blnStartRecordTag\n";
					$blnStartRecordTag = false;
				}

				$j++;

				if(!$this->blnStartRecord){
					continue;
				}

			}

			// store this letter into record var
			if($this->blnStartRecord){
				$this->strTempResult .= $str;
			}

		}

	}


}




