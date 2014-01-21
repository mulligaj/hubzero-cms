<?php  
	//ini_set('display_errors', 1);
	//error_reporting(E_ALL);
	
	$username = "cccharle";
	$password = "5hw.hP4c5aa";
	$api_key = "d660bf27-84e6-49be-9f84-bb644b55d79c";
	$api_path = "https://api.constantcontact.com/ws/customers/";
	$url = $api_path . $username . "/contacts";
	
	//get the posted vars
	$subscribe = JRequest::get('post');
	
	//if we have submitted the form
	if($subscribe['submit']) {  
		//variable to hold errors
		$error = ""; 
		
		//var to hold contact details
		$contact = array();
		                         
		//get the entered email
		$email = $subscribe['emailaddress'];
	         
		//lists  
	    $lists = array("1");
		
		//get the honeypot
		$honey_pot = $subscribe['honey'];
		                  
		//regex for validating email.
		$email_validator = '/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/';   
   	            
		//check to make sure honeypot is empty
		if(!empty($honey_pot)) {
		   	$error = "Spam Bot, Go Away";
		}
		    
		//check to make sure we have a valid email
		if(!preg_match( $email_validator, $email )) {
			$error = "Email Address NOT Valid.";
		}
 		 
		if($error == "") {
			$list_xml = "";
			
			foreach($lists as $l) {
				$list_xml .= "<ContactList id=\"http://api.constantcontact.com/ws/customers/{$username}/lists/{$l}\" />";
			}
			
			$xml = '<entry xmlns="http://www.w3.org/2005/Atom">
				  		<title type="text"> </title>
				  		<updated>2008-07-23T14:21:06.407Z</updated>
				  		<author></author>
				  		<id>data:,none</id>
				  		<summary type="text">Contact</summary>
				  		<content type="application/vnd.ctct+xml">
							<Contact xmlns="http://ws.constantcontact.com/ns/1.0/">
					  			<EmailAddress>' . $email . '</EmailAddress>
					  			<OptInSource>ACTION_BY_CONTACT</OptInSource>
					  			<ContactLists>' . $list_xml . '</ContactLists>
							</Contact>
				  		</content>
					</entry>';       
					
			 // Ths is where we connect to the server and tell it what we need to do	
			 $session = curl_init($url);
			 $usrpsw = $api_key. '%' . $username .':'. $password;
			 curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			 curl_setopt($session, CURLOPT_USERPWD, $usrpsw);
			 curl_setopt($session, CURLOPT_FOLLOWLOCATION ,1);
			 curl_setopt($session, CURLOPT_POST, 1);
			 curl_setopt($session, CURLOPT_POSTFIELDS , $xml);
			 curl_setopt($session, CURLOPT_HTTPHEADER, Array("Content-Type:application/atom+xml"));
			 curl_setopt($session, CURLOPT_HEADER, 0);
			 curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
             
			 // this is now our response back from the server
			 $response = curl_exec($session);
             $info = curl_getinfo($session);      

			 // our curl
			 curl_close($session); 
             
			 // This echo is just to show you the results of your curl in case there is an error just uncomment
			 if($info['http_code'] == 200 || $info['http_code'] == 201) {
				echo "<p class=\"success\">You have successfully subscribed to the HABRI Central Mailing List.</p>";
			 } else {
				echo "<p class=\"error\">{$response}</p>"; 
			 }
					
		} else {
		   	echo "<p class=\"error\">{$error}</p>";
		}
   	}
?> 