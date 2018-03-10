<?php 
	function curl($url) {
		// Assigning cURL options to an array
		$options = Array(
			CURLOPT_RETURNTRANSFER => TRUE,  // Setting cURL's option to return the webpage data
			CURLOPT_FOLLOWLOCATION => TRUE,  // Setting cURL to follow 'location' HTTP headers
			CURLOPT_AUTOREFERER => TRUE, // Automatically set the referer where following 'location' HTTP headers
			CURLOPT_CONNECTTIMEOUT => 120,   // Setting the amount of time (in seconds) before the request times out
			CURLOPT_TIMEOUT => 120,  // Setting the maximum amount of time for cURL to execute queries
			CURLOPT_MAXREDIRS => 10, // Setting the maximum number of redirections to follow
			CURLOPT_USERAGENT => "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1a2pre) Gecko/2008073000 Shredder/3.0a2pre ThunderBrowse/3.2.1.8",  // Setting the useragent
			CURLOPT_URL => $url, // Setting cURL's URL option with the $url variable passed into the function
		);
		
		$ch = curl_init();  // Initialising cURL 
		curl_setopt_array($ch, $options);   // Setting cURL's options using the previously assigned array data in $options
		$data = curl_exec($ch); // Executing the cURL request and assigning the returned data to the $data variable
		curl_close($ch);    // Closing cURL 
		return $data;   // Returning the data from the function 
	}
?>

<?php //Basic Domain Class
	class ResultModel{
		public $lines = array();
	}

	class Line{
		public $id;
		public $routeName;
		public $stops = array();
	}

	class Stop{
		public $address;
		public $numbers;
		function __construct($arg1){
			$this->address = $arg1;
		}
	}
?>

<?php //Main 
	if ($_SERVER["REQUEST_METHOD"] != "GET")
		{
			header("HTTP/1.1 405 Method Not Allowed");
			return;
		}
	$paramNumber = $_GET["number"];
	header("number:".$paramNumber);
	if (empty($paramNumber))
	{
		header("HTTP/1.1 400 Bad Request");
		return;
	}
	
	$pageResult = curl("http://servicosbhtrans.pbh.gov.br/bhtrans/e-servicos/S02F02-itinerarioResultado.asp?linha=".$paramNumber."&sublinha=PRINCIPAL");
	
	$dom = new domDocument;
	@$dom->loadHTML($pageResult);
	$tables = $dom->getElementsByTagName("table");
	
	$result = new resultModel();
	$index = 0;
	
	foreach($tables as $table){
		$line = new Line();
		$line->id = $index;
		$index = $index + 1;
		
		$line->stops = array();
		
		if (strpos($table->nodeValue, "function googleTranslateElementInit")){
			continue;
		}
		$rows = $table->getElementsByTagName('tr');
		$validTable = false;
		
		foreach($rows as $row){
			$cols = $row->getElementsByTagName("td");
			
			$stop = new Stop($cols[0]->nodeValue);
			$stop->numbers = array();
			$numbers = $cols[1]->nodeValue;
			
			$numbers  = explode('|', $numbers);
			
			foreach($numbers as $number){
				if (preg_match('/\\d/', $number)){
					$stop->numbers[] = intval($number);
				}
			}
			$line->stops[] = $stop;
		}
		$result->lines[] = $line;
		
	}
	header('Content-type: application/json');
	$returnObject = json_encode($result);
  	echo $returnObject;
?>
