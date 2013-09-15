<?php
/* author: alice@muc.ccc.de
 */
if (!defined('DOKU_INC')) die();
class helper_plugin_podcast extends DokuWiki_Plugin {
  function get_headers_length($url) {
    $url_info=parse_url($url);
    $port = isset($url_info['port']) ? $url_info['port'] : 80;
    $fp=fsockopen($url_info['host'], $port, $errno, $errstr, 10 );
    if($fp) {
        $head = "GET ".@$url_info['path']."?".@$url_info['query']." HTTP/1.0\r\n";
        if (!empty($url_info['port'])) {
            $head .= "Host: ".@$url_info['host'].":".$url_info['port']."\r\n"; } 
	else {
            $head .= "Host: ".@$url_info['host']."\r\n"; }
        $head .= "Connection: Close\r\n";
        $head .= "Accept: */*\r\n";
        $head .= $refererline;
        $head .= $authline;
        $head .= "\r\n";
        fputs($fp, $head);       
        while( !feof( $fp ) or ( $eoh == true )) {
            if( $v = fgets( $fp, 1024 )) {
                if( $v == "\r\n" ) {
                    $eoh = true;
                    break;
                } else {
        	  if( preg_match( '/Content-Length: ([0-9]*)/', $v, $m )) {
		    $length = $m[1]; }
		  elseif( preg_match( '/Location: (http:[^ ]*)/', $v, $m )) {
		    $location = $m[1]; }
		  elseif( preg_match( '/HTTP\/1\.1 ([0-9]*)/', $v, $m )) {
		    $status = $m[1]; }}} }} 
    if( $status === '302' ) return $this->get_headers_length( trim( $location ));
    if( $status === '200' ) return $length;
    return false;
  }
  function gethumanfilesize( $bytes, $decimals = 2 ) {
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
  }
  function getfiles( $name ) {
    $name = $this->getConf( 'podcast_prefix' ).$name;
    $extensions = explode( ',', $this->getConf( 'podcast_extensions' ));
    $files = array( );
    foreach( $extensions as $ext ) {
      $f = "$name.$ext";
      $s = $this->get_headers_length( $f );
      if( !$s ) continue;
      $files[$ext] = array( 
          'url' => $f,
          'size' => $s,
          'hsize' => $this->gethumanfilesize( $s, 0 )); }
    return $files;
  }
}
