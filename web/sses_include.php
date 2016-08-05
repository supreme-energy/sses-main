<?php
//	Written by:  C. Bergman with Tyler Hall
//	Copyright: 2012, Supreme Source Energy Services, Inc.
//	All rights reserved.
//	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.

session_start();

function IsLoggedIn() {
	$userid = $_SESSION['user_id'];
	if ($userid != '' && $userid != null)
		return true;
	else
		return false;
}

//	Validate an email address.
//	Provide email address (raw input)
//	Returns true if the email address has the email 
//	address format and the domain exists.

function validateLoginEmail($email) {
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex) {
      $isValid = false;
   } else {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64) {       
          $isValid = false;  	// local part length exceeded
      } else if ($domainLen < 1 || $domainLen > 255) {
          $isValid = false;	    // domain part length exceeded
      } else if ($local[0] == '.' || $local[$localLen-1] == '.') {
          $isValid = false;	    // local part starts or ends with '.'
      } else if (preg_match('/\\.\\./', $local)) {      
          $isValid = false;	 // local part has two consecutive dots
      } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
          $isValid = false;	 // character not valid in domain part
      } else if (preg_match('/\\.\\./', $domain)) {
          $isValid = false;	 // domain part has two consecutive dots
      } else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
         // character not valid in local part unless 
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) 
            $isValid = false;
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) 
           $isValid = false;	// domain not found in DNS
   }
   return $isValid;
}
