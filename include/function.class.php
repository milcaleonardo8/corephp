<?php
class Functions 
{
	 /**Local Database Detail **/
	protected $db_l_host = "localhost";	
	protected $db_l_user = "root";		//Database username
	protected $db_l_pass = "";			//Database password
	protected $db_l_name = ""; 	 		//Database name

	//Live database details
	protected $db_host = "";	
	protected $db_user = "";		//Database username
	protected $db_pass = "";			//Database password
	protected $db_name = ""; 	 		//Database name

	protected $con = false;
	public $myconn;
	
	function __construct() {
		global $myconn;
		if($_SERVER['HTTP_HOST'] == 'localhost' ){ 
			$myconn = @mysqli_connect($this->db_l_host,$this->db_l_user,$this->db_l_pass,$this->db_l_name);
		} else {
			$myconn = @mysqli_connect($this->db_host,$this->db_user,$this->db_pass,$this->db_name);
		}
		if (mysqli_connect_errno()){
			echo "Failed to connect to MySQL: " . mysqli_connect_error();die;
		}
	}
	/*
		*** Main Function <<<
			-> getData() 
				- return single and multi records
			-> getValue() 
				- return single records
			-> getTotalRecord()
				- return number of records
			-> getMaxVal()
				- return maximum value
			-> insertData()
				- insert record
			-> deleteData()
				- delete record
			-> updateData()
				- update record
			-> tableExists()
				- check whether table exist or not
			-> limitChar()
				- return trimed character string
			-> dupCheck()
				- check for duplicate record in table
			-> location()
				- redirect to given URL
			-> getDisplayOrder()
				- get next display order
			-> createSlug()
				- create alias of given string
			-> getTotalReview()
				- number of total review of product
			-> catData()
				- get cid/sid/ssid from slug
			-> clean()
				- prevent mysql injction
	*/
	public function getData($table, $rows = '*', $where = null, $order = null,$die=0) // Select Query, $die==1 will print query
	{
		$results = array();
		$q = 'SELECT '.$rows.' FROM '.$table;
		if($where != null)
			$q .= ' WHERE '.$where;
		if($order != null)
			$q .= ' ORDER BY '.$order;
		if($die==1){ echo $q;die; }
		if($this->tableExists($table))
		{
			
			if(@mysqli_num_rows(mysqli_query($GLOBALS['myconn'],$q))>0){
				$results = @mysqli_query($GLOBALS['myconn'],$q);
				return $results;
			}else{
				return false;
			}
		}
		else{
			return false;
		}
	}
	
	public function getValue($table, $row=null, $where=null,$die=0) // single records ref HB function
	{
		if($this->tableExists($table) && $row!=null && $where!=null)
		{
			$q = 'SELECT '.$row.' FROM '.$table.' WHERE '.$where;
			if($die==1){ echo $q;die; }
			if(@mysqli_num_rows(mysqli_query($GLOBALS['myconn'],$q))>0){
				$results = @mysqli_fetch_array(mysqli_query($GLOBALS['myconn'],$q));
				return $results[$row];
			}else{
				return false;
			}
		}
		else{
			return false;
		}
	}
	
	public function getMaxVal($table, $row=null, $where=null,$die=0)
	{
		if($this->tableExists($table) && $row!=null && $where!=null)
		{
			$q = 'SELECT MAX('.$row.') as '.$row.' FROM '.$table.' WHERE '.$where;
			if($die==1){
				echo $q;die;
			}
			if(mysqli_num_rows(mysqli_query($GLOBALS['myconn'],$q))>0){
				$results = @mysqli_fetch_array(mysqli_query($GLOBALS['myconn'],$q));
				return $results[$row];
			}else{
				return 0;
			}
		}
		else{
			return 0;
		}
	}

	public function getTotalRecord($table, $where = null,$die=0) // return number of records
	{
		$q = 'SELECT * FROM '.$table;
		if($where != null)
			$q .= ' WHERE '.$where;
		if($die==1){
			echo $q;die;
		}
		if($this->tableExists($table))
			return mysqli_num_rows(mysqli_query($GLOBALS['myconn'],$q))+0;
		else
			return 0;
	}
	
	public function insertData($table,$values,$rows = 0,$die=0) // insertData - Insert and Die Values
	{
		if($this->tableExists($table))
		{
			$insert = 'INSERT INTO '.$table;
			if(count($rows) > 0)
			{
				$insert .= ' ('.implode(",",$rows).')';
			}
 
			for($i = 0; $i < count($values); $i++)
			{
				if(is_string($values[$i]))
					$values[$i] = '"'.$values[$i].'"';
			}
			$values = implode(',',$values);
			$insert .= ' VALUES ('.$values.')';
			if($die==1){
				echo $insert;die;
			}
			$ins = @mysqli_query($GLOBALS['myconn'],$insert);           
			if($ins)
			{
				$last_id = mysqli_insert_id($GLOBALS['myconn']);
				return $last_id;
			}
			else
			{
				return false;
			}
		}
	}
	
	public function deleteData($table,$where = null,$die=0)
	{
		if($this->tableExists($table))
		{
			if($where != null)
			{
				$delete = 'DELETE FROM '.$table.' WHERE '.$where;
				if($die==1){
					echo $delete;die;
				}
				$del = @mysqli_query($GLOBALS['myconn'],$delete);
			}
			if($del)
			{
				return true;
			}
			else
			{
			   return false;
			}
		}
		else
		{
			return false;
		}
	}
	public function updateData($table,$rows,$where,$die=0) //update query 
	{
		if($this->tableExists($table))
		{
			// Parse the where values
			// even values (including 0) contain the where rows
			// odd values contain the clauses for the row
			//print_r($where);die;
			
			$update = 'UPDATE '.$table.' SET ';
			$keys = array_keys($rows);
			for($i = 0; $i < count($rows); $i++)
			{
				if(is_string($rows[$keys[$i]]))
				{
					$update .= $keys[$i].'="'.$rows[$keys[$i]].'"';
				}
				else
				{
					$update .= $keys[$i].'='.$rows[$keys[$i]];
				}
				 
				// Parse to add commas
				if($i != count($rows)-1)
				{
					$update .= ',';
				}
			}
			$update .= ' WHERE '.$where;
			if($die==1){
				echo $update;die;
			}
			//$update = trim($update," AND");
			$query = @mysqli_query($GLOBALS['myconn'],$update);
			if($query)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	
	public function tableExists($table)
	{
		return true;
	}
	
	public function limitChar($content,$limit,$url="javascript:void(0);",$txt="&hellip;")
	{
		if(strlen($content)<=$limit){
			return $content;
		}else{
			$ans = substr($content,0,$limit);
			if($url!=""){
				$ans .= "<a href='$url' class='desc'>$txt</a>";
			}else{
				$ans .= "&hellip;";
			}
			return $ans;
		}
	}

	public function limitCharNew($content,$limit)
	{
		if(strlen($content)<=$limit){
			return $content;
		}else{
			$ans = substr($content,0,$limit);
			if($url!=""){
				$ans .= $txt;
			}else{
				$ans .= "&hellip;";
			}
			return $ans;
		}
	}
	
	public function dupCheck($table, $where = null,$die=0) // Duplication Check 
	{
		$q = 'SELECT id FROM '.$table;
		if($where != null)
			$q .= ' WHERE '.$where;
		if($die==1){ echo $q;die; }
		if($this->tableExists($table))
		{
			$results = @mysqli_num_rows(mysqli_query($GLOBALS['myconn'],$q));
			if($results>0){
				return true;
			}else{
				return false;
			}
		}
		else
			return false;
	}
	
	public function location($redirectPageName=null) // Location 
	{
		if($redirectPageName==null){
			header("Location:".$this->SITEURL);
			exit;
		}else{
			header("Location:".$redirectPageName);
			exit;
		}
	}
	
	public function getDisplayOrder($table,$where=null,$die=0) // Display Order 
	{
		$q = 'SELECT MAX(display_order) as display_order FROM '.$table;
		if($where != null)
			$q .= ' WHERE '.$where;
		if($die==1){
			echo $q;die;
		}
		if($this->tableExists($table))
		{
			$results = @mysqli_query($GLOBALS['myconn'],$q);
			if(@mysqli_num_rows($results)>0){
				$disp_d = mysqli_fetch_array($results);
				return intval($disp_d['display_order'])+1;
			}else{
				return 1;
			}
		}
		else{
			return 1;
		}
	}
	
	public function createSlug($string)    // Slug  
	{   
		$slug = strtolower(trim(preg_replace('/-{2,}/','-',preg_replace('/[^a-zA-Z0-9-]/', '-', $string)),"-"));
		return $slug;
	}
	
	public function createProSlug($string) // Product Slug  
	{   
		$slug = strtolower(trim(preg_replace('/-{2,}/','-',preg_replace('/[^a-zA-Z0-9-.]/', '-', $string)),"-"));
		return $slug;
	}
	
	public function num($val,$deci="2",$sep=".",$thousand_sep=""){
		return number_format($val,$deci,$sep,$thousand_sep);
	}
	
	public function get_client_ip(){
		$ipaddress = '';
		if (getenv('HTTP_CLIENT_IP'))
			$ipaddress = getenv('HTTP_CLIENT_IP');
		else if(getenv('HTTP_X_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
		else if(getenv('HTTP_X_FORWARDED'))
			$ipaddress = getenv('HTTP_X_FORWARDED');
		else if(getenv('HTTP_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_FORWARDED_FOR');
		else if(getenv('HTTP_FORWARDED'))
			$ipaddress = getenv('HTTP_FORWARDED');
		else if(getenv('REMOTE_ADDR'))
			$ipaddress = getenv('REMOTE_ADDR');
		else
			$ipaddress = 'UNKNOWN';
		  
		return $ipaddress;
	}
	
	public function catData($cslug=null,$sslug=null,$ssslug=null){
		if($cslug!=null && $sslug==null && $ssslug==null){
			return $this->getData("category","*","slug='".$cslug."' AND isDelete=0");
		}else if($cslug!=null && $sslug!=null && $ssslug==null){
			$cid	= $this->getValue("category","id","slug='".$cslug."'");
			return $this->getData("subcategory","*","cid='".$cid."' AND slug='".$sslug."' AND isDelete=0");
		}else{
			return false;
		}
	}
	
	public function clean($string)
	{
		$string = trim($string);								// Trim empty space before and after
		if(get_magic_quotes_gpc()) {
			$string = stripslashes($string);					        // Stripslashes
		}
		$string = mysqli_real_escape_string($GLOBALS['myconn'],$string);			        // mysql_real_escape_string
		return $string;
	}
	public function checkLogin($url=""){
		if(!isset($_SESSION[SESS_PRE.'_SESS_USER_ID']) || $_SESSION[SESS_PRE.'_SESS_USER_ID']==""){
			$_SESSION[SESS_PRE.'_FAIL_LOG'] = "1";
			if($url==""){
				$_SESSION['backUrl'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
				$this->location(SITEURL.'login/');
			}else{
				$this->location($url);
			}
		}
	}
	
	public function checkAdminLogin($url=""){
		if(!isset($_SESSION[SESS_PRE.'_ADMIN_SESS_ID']) || $_SESSION[SESS_PRE.'_ADMIN_SESS_ID']==""){
			if($url==""){
				$_SESSION['adminbackUrl'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
				$this->location(ADMINURL);
			}else{
				$this->location($url);
			}
		}
	}
	
	public function printr($val,$isDie=1){
		echo "<pre>";
		print_r($val);
		if($isDie){die;}
	}
	public function CheckRemember(){
		if(isset($_COOKIE['SESS_COOKIE']) && $_COOKIE['SESS_COOKIE']>0){
			$_SESSION[SESS_PRE.'_SESS_USER_ID']=$_COOKIE['SESS_COOKIE'];
		}
	}
	public function DateFormat($date, $format="Y-m-d h:i:s"){
		return date_format(date_create($date),$format);
	}
	
	public function generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	public function RandomOTP($length = 6) {
		$characters = '0123456789';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
	
	public function getProductQty($pid)
	{
		$proQty = $this->getValue("product","qty","id='".$pid."'"); 
		return $proQty;
	}
	
	public function getStar($star)
	{
		if($star=='0.5')
		{
			return 'detail-rating-half';
		} else if($star=='1')
		{
			return 'detail-rating-1';
		} else if($star=='1.5')
		{
			return 'detail-rating-half-1';

		} else if($star=='2')
		{
			return 'detail-rating-2';

		} else if($star=='2.5')
		{
			return 'detail-rating-half-2';

		} else if($star=='3')
		{
			return 'detail-rating-3';

		} else if($star=='3.5')
		{
			return 'detail-rating-half-3';

		} else if($star=='4')
		{
			return 'detail-rating-4';

		} else if($star=='4.5')
		{
			return 'detail-rating-half-4';

		} else if($star=='5')
		{
			return 'detail-rating-5';

		}

	}
	public function base_encode($id){

		for ($i=0; $i < 3; $i++) { 
			$id = base64_encode($id);
		}
		return $id;
	}
	public function base_decode($id){

		for ($i=0; $i < 3; $i++) { 
			$id = base64_decode($id);
		}
		return $id;
	}
	
}
include("admin.class.php");
?>