<?php //error_reporting(0);
//class for read mail from gmail 
set_time_limit(3000);
include_once('simple_html_dom.php');
Class emailReaderClass extends simple_html_dom_node{
	//body section store into message array
	public $message = array();
	//defing config data in constructor 
	public function __construct(){
		//define variable and common function 
		
		//database credential 
		$this->dbhostname = 'localhost';
		$this->dbusername = 'root';
		$this->dbpassword = '';
		$this->dbdatabase = 'irgca_newirg';
		$this->dbPrefix = 'irg_';
		$this->conn = $this->dbConnect();
	}
	/*
	 *  Database connection 
	*/
	private function dbConnect(){
		$this->db = mysql_connect($this->dbhostname,$this->dbusername,$this->dbpassword);
		if($this->db)
			mysql_select_db($this->dbdatabase,$this->db);
	}
	
	public function read_body($hostname,$username,$password,$subject){
		
		 $subject=trim($subject);
		//$emails = imap_search($inbox,'All');
		$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());
		
			$emails = imap_search($inbox, 'SEEN SUBJECT "'.$subject.'"');
			/* useful only if the above search is set to 'ALL' */
			$max_emails = 10;
			/* if any emails found, iterate through each email */
			if($emails) {
				$count = 1;
					/* put the newest emails on top */
				rsort($emails);
					/* for every email... */
				foreach($emails as $email_number) 
				{
					/* get information specific to this email */
					$overview = imap_fetch_overview($inbox,$email_number,0);
					/* get mail message into message array */
					$message[] = imap_fetchbody($inbox,$email_number,2);
					 
					if($count++ >= $max_emails) break;
				}
				return $message;
			}else{
				return "0";
			}
		
	
	}
	
	public function insert_data($mailbodyarray){
		foreach($mailbodyarray as $mailbodyvalue){
			$mailbody1=str_replace("<html><body style='padding: 12px; font-family: Helvetica, Arial, sans-serif; font-size: 14px;'>
			<h1 style='font-size: 21px;'>I Run Guns LLC Order Confirmation</h1><p style='font-size: 14px;'>Here are the details of Order Number 4074 from <a href='http://irunguns.com' style='color: #000; text-decoration: none;'><b>irunguns.com</b></a>.</p>
			","",$mailbodyvalue);

			$mailbody=str_replace("<div style='margin: 12px; text-align: center; font-size: 12px; color: #333;'><p>Shipping Charge: Dealer Default</p><p style='font-size: 10px; text-align: left;'>[ NFDN Dealer ID: 317, NFDN Order Record ID: 89617 ]</p></div>
			</body></html>
			","",$mailbody1);
			
			//echo $mailbody;
			$html = str_get_html($mailbody);
			$tblcunt = count($html->find("table"));
			$mailtoinsert = $e = $html->find("table", 0);

			//order detail table variables
			$orderdetailtable = $html->find("table", 1);

			$orderid1 = explode(':',strip_tags($orderdetailtable->find('td',0)));
			$orderid = $orderid1[1];
			/**
			* check that order id already exist or not
			* if order id exist in database table it means order already (mail)  readed
			* else read order or mail and proceed to insert data into tables 
			*/
			//get row using order id
			//echo "SELECT id FROM ".$this->dbPrefix."us_order_details WHERE order_id = '$orderid'";die;
			$selsql = mysql_query("SELECT id FROM ".$this->dbPrefix."us_order_details WHERE order_id = '$orderid'");
			$getrow = mysql_num_rows($selsql);
			$bodymessage = mysql_real_escape_string($mailtoinsert);
			
			if($getrow == 0){
				// insert mail to irg_us_mails table as it is showing in gmail 				
				$insertmail = mysql_query("INSERT INTO ".$this->dbPrefix."us_mails (id,order_id,mails) VALUES (NULL,'$orderid','$bodymessage')");
				$dateftd = $orderdetailtable->find('td',1);
				$date1 = explode(':',strip_tags($dateftd));
				$date = date('Y-m-d',strtotime($date1[1]));

				$cost1 = explode(':',strip_tags($orderdetailtable->find('td',2)));
				$cost = $cost1[1];

											
				//customer information table variables 
				$cname='';
				$cemail='';
				$cphone='';
				$cnote='';	
				$cardno='';						
				$customerinfo = $html->find("table", 2);
				//customer variables
				$customer =  explode('<br>',$customerinfo->find('td',0));
				$cname = $customer[1];
				$cemail = str_replace('Email:','',strip_tags($customer[2]));
				$cphone = str_replace('Phone:','',strip_tags($customer[3]));
				$cnote = str_replace('Note:','',strip_tags($customer[4]));
				//billing information variables
				$billinginfo = explode('Credit Card:',$customerinfo->find('td',1));
				$billing = str_replace('|#|','<br>',strip_tags(str_replace('<br>','|#|',str_replace('<b>Billing</b><br>','',$billinginfo[0]))));
				$cardno = strip_tags($billinginfo[1]);
				//shipping information variables 
				$shipping = str_replace('|#|','<br>',strip_tags(str_replace('<br>','|#|',str_replace('<b>Shipping</b><br>','',$customerinfo->find('td',2)))));
				//echo $shipping ;


				if($tblcunt==5){
				//dealer information table variable 
					$dealerinfo = $html->find("table", 3);
					$dealerinfo = strip_tags($dealerinfo->find('td',0).' '.$dealerinfo->find('td',1));
				}else{
					$dealerinfo = '';
				}

				//item detail or product detail 
				if($tblcunt==5){
					$productinfo = $html->find("table", 4);
				}else{
					$productinfo = $html->find("table", 3);
				}

				//echo $productinfo->find("tr",0).'<br/>';
				$maxcnt = count($productinfo->find("tr"));
				$pdtr1 = $productinfo->find("tr",$maxcnt-5);
				
				if(strpos($pdtr1,'Subtotal:')){
					$loopcountend = $maxcnt-5;
				}else{
					$loopcountend = $maxcnt-4;
				}
				
				//sub total, shipping and taxes variables
				$subtotal='';
				$shiptotal='';
				$tax='';
				$gtotal='';

				for($cont1=$loopcountend;$cont1<$maxcnt;$cont1++ ){
					//echo ($productinfo->find("tr",$cont1)).'<br/>';
					$pdtr = $productinfo->find("tr",$cont1);
					$subtotal1 = $pdtr->find("td",1);
					if($subtotal==''){
						$subtotal = $subtotal1;
					}else{
						$subtotal = $subtotal.'||'.$subtotal1;
					}
				}
				$totaalpricearray = explode('||',$subtotal);
				//sub total, shipping price, insurance, tax and grand total price
				if(strpos($pdtr1,'Subtotal:')){
					$subtotal = strip_tags($totaalpricearray[0]);
					$shiptotal = strip_tags($totaalpricearray[1]);
					$insurance = strip_tags($totaalpricearray[2]);
					$tax = strip_tags($totaalpricearray[3]);
					$gtotal = strip_tags($totaalpricearray[4]);
				}else{
					$subtotal = strip_tags($totaalpricearray[0]);
					$shiptotal = strip_tags($totaalpricearray[1]);
					$insurance = '';
					$tax = strip_tags($totaalpricearray[2]);
					$gtotal = strip_tags($totaalpricearray[3]);
				}		
			
				//insert data into us order detail table fields are (`id`, `order_id`, `order_date`, `order_total`, `customer_name`, `customer_email`, `customer_phone`, `note`, `billing_address`, `shipping_desc`, `creditcard_no`, `dealer_info`, `sub_total`, `shipping_total`, `insurance`, `tax`, `grand_total`, `current_TS`)
				$orderdetailsql = "INSERT INTO `".$this->dbPrefix."us_order_details`(`id`, `order_id`, `order_date`, `order_total`, `customer_name`, `customer_email`, `customer_phone`, `note`, `billing_address`, `shipping_desc`, `creditcard_no`, `dealer_info`, `sub_total`, `shipping_total`, `insurance`, `tax`, `grand_total`) VALUES (NULL,'$orderid','$date','$gtotal','$cname','$cemail','$cphone','$cnote','$billing','$shipping','$cardno','$dealerinfo','$subtotal','$shiptotal','$insurance','$tax','$gtotal')";
				//echo '<br>';
				mysql_query($orderdetailsql);
				$pdid = '';
				$pdqty = '';
				$pditem = '';
				$pdprice = '';
				for($cont=1;$cont<$loopcountend;$cont++ ){
					//echo ($productinfo->find("tr",$cont)).'<br/>';
					
					if($cont%2!=0){
						$pdtr = $productinfo->find("tr",$cont);
						$pdid1 = $pdtr->find("td",0);
						if($pdid==''){
							$pdid = $pdid1;
						}else{
							$pdid = $pdid.'||'.$pdid1;
						}
						
						$pditem1 = $pdtr->find("td",1);
						if($pditem==''){
							$pditem = $pditem1;
						}else{
							$pditem = $pditem.'||'.$pditem1;
						}	
						
						$pdqty1 = $pdtr->find("td",2);
						if($pdqty==''){
							$pdqty = $pdqty1;
						}else{
							$pdqty = $pdqty.'||'.$pdqty1;
						}
						
						$pdprice1 = $pdtr->find("td",3);
						if($pdprice==''){
							$pdprice = $pdprice1;
						}else{
							$pdprice = $pdprice.'||'.$pdprice1;
						}
					}else{
						$pdtr = $productinfo->find("tr",$cont);
						$pditem1 = $pdtr->find("td",1);	
							$pditem = $pditem.'<br/>'.$pditem1;
					}
				}
				
				$productidarray = explode('||',$pdid);
				$productitmarray = explode('||',$pditem);
				$productqtyarray = explode('||',$pdqty);
				$productpriarray = explode('||',$pdprice);
				
				//loop for insert product item in irg_us_order_item table
				for($i=0;$i<count($productidarray);$i++){
					
					$pid = strip_tags($productidarray[$i]);
					$pitem = strip_tags($productitmarray[$i]);
					$pqnt = strip_tags($productqtyarray[$i]);
					$ppric = strip_tags($productpriarray[$i]);
					
				//insert query for insert product item in to us order item table
				//fields are (`id`, `order_id`, `proid`, `p_item`, `p_cost`, `p_qnty`)
				$productinsertsql = "INSERT INTO `".$this->dbPrefix."us_order_item` (`id`, `order_id`, `proid`, `p_item`, `p_cost`, `p_qnty`) 
				VALUES (NULL,'$orderid','$pid','$pitem','$ppric','$pqnt')";
				//echo '<br>';
				mysql_query($productinsertsql);
				}
			}
			
		}
		echo 'values inserted !';
	
	}
	
	//select data from datatables to show on page
	public function dbLoadObjectList($tablename, $fields, $where ='',$order ='') {
		$sql = "SELECT $fields FROM ".$this->dbPrefix."$tablename $where $order";
		//echo "<br>";
		$query = mysql_query($sql) or die(mysql_error());		
		$records	=	array();		
		if (!$query) {
			return "Could not successfully run query ($sql) from DB: " . mysql_error();
			exit;
		}		
		//dbGetNumRows($query);
			
		if (mysql_num_rows($query) == 0) {
			return 0;
			//exit;
		}
		else {		
			while($this->_row	=	mysql_fetch_object($query)){
			
				$records[] = $this->_row;
			}		
			return $records;
		}

   }
}
?>
