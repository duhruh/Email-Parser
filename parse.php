<html>
<header>
</header>
<body>
<pre>
<?php
/**
* Used to parse an email file and sort into relevant data.
* @global Boundary_String holds the value of the string that separates the bodys
* @global Known_Headers holds an array of commonly used headers that can later be expanded if nessary
*/
$Boundary_String;
$Known_Headers = array (
	'Received',
	'Date',
	'Delivered-To',
	'Message-ID',
	'Subject',
	'From',
	'To',
	'References',
	'X-Attachment-Id',
	'MIME-Version',
	'In-Reply-To',
	'Authentication-Results',
	'Received-SPF',
	'Return-Path'

);
/**
* Holds all functions nessary to parse a raw email.
* @author David Rivera
* @version PHP5
* @copyright Copyright (c) 2011, David Rivera
*/
class Email {
	/**
	* Initalize the email object by getting its file contents.
	* @method
	*/
	function __construct($email) {
		$this->email = file_get_contents($email);
		 } 
	/**
	* Takes in the header text and breaks it into single lines, if one of the lines begins with a space it signifies it is part of the last header and appends it.
	* @return array
	*/
	function single_line_headers($header_text) {
		$header_lines = array();
		$lines = explode("\n", $header_text);
		$index = 0;
		foreach($lines as $line) {
			if (! preg_match('/^\s/', $line) ) { // if the line does not begin with whitespace
				$header_lines[$index] = $line;
				++$index;
			} else { // else, append the line to the last header line
				if(isset($last_header[$index-1])){
					$last_header[$index-1] .= preg_replace('/^\s+/', '', $line);
					++$index;
				}else{++$index;}
			}
		}

		return $header_lines;
	}
	/**
	* Takes in a single header line and breaks it apart into its header and its information.
	* @return array
	*/
	function parse_header($header_line) {
		return explode(': ', $header_line,2);
	}
	/**
	* This function when called breaks the raw email into its header and body.
	* @global Known_Headers
	* @global Boundary_String
	* @return array (the parsed headers)
	*/
	function parse_headers(){
		GLOBAL $Known_Headers;
		GLOBAL $Boundary_String;
		list($header, $body) = explode("\n\n", $this->email, 2);// Seperates the headder from the body
		$parsed_headers = array();

		$header_lines = $this->single_line_headers($header);
	
	
		foreach($header_lines as $line){
			list($header_name, $header_value) = $this->parse_header($line);
			if(in_array($header_name, $Known_Headers)) {
				$parsed_headers[$header_name][] = $header_value;
			} else if($header_name == 'Content-Type'){
				list($header_value, $Boundary_String) = $this->extract_boundary($header_value);
				$parsed_headers['Content-Type'][] = $header_value;
				}else {
				$parsed_headers['UNMATCHED'][] = $line;
			}
		}
		return $parsed_headers;
	}
	/**
	* Extracts the boundary string and returns it as the second value in the array.
	* @return array
	*/
	function extract_boundary($bound_line){
		return explode('=',$bound_line, 2);
	}
	/**
	* Parses the body of the email.
	* @return array
	*/
	function parse_body(){
		list($header, $body) = explode("\n\n", $this->email, 2);
		$parsed_body = $this->strip_body($body);
		return $parsed_body;
	}
	/**
	* strips info not relevant to the body of the email e.g.(--, boundary string)
	* @return array
	* @global Boundary_String
	*/
	function strip_body($body){
		GLOBAL $Boundary_String;
		$parsed_body = explode('--'.$Boundary_String,$body);
		unset($parsed_body[count($parsed_body)-count($parsed_body)]);
		unset($parsed_body[count($parsed_body)]);
		return $parsed_body;
	}
}
/**
* Test run of the a given file we'll call it mail.
*
*/
$mail = "mail.txt";
$obj = new Email($mail);
print_r($obj->parse_headers());
print_r($obj->parse_body());

?>
</pre>
</body>
</html>