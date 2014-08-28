<?php namespace CO;
require_once __DIR__."/idiorm.php";
require_once __DIR__."/chocoby_err.php";
require_once __DIR__."/chocoby_config.php";

$fetch_src = $_GET;
$in_buffer = Array();
$out_buffer = Array();
$autocommits = Array();
$errno = ERR\NONE;

/* interfaces */
function in($in_ary){
	GLOBAL $in_bufer;

	foreach($in_ary as $key => $value){
		$in_buffer[$key] = $value;

		$parsed = lex_opts($value);
		process_in_opts($key, $parsed);
	}
}
function out($out_ary){
	GLOBAL $out_buffer;

	foreach($out_ary as $key => $value){
		$parsed = lex_opts($value);
		$out_buffer[$key] = $parsed;
	}
}
function set_errno($no){
	GLOBAL $errno;
	$errno = $no;
}
function abort($no, $reason){
	set_errno($no);

	if( CONFIG\DEBUG ){
		$result = 
			[CONFIG\RESULT_KEY => $no,
			CONFIG\REASON_KEY => $reason];
	}
	else{
		$result = 
			[CONFIG\RESULT_KEY => $no];
	}

	exit(json_encode(
		$result));
}

/* backends */
function flush_db(){
	GLOBAL $autocommits;

	foreach($autocommits as $row){
		if( $row != null )
			$row->save();
	}
}
function on_response(){
	GLOBAL $out_buffer;
	GLOBAL $errno;

	$responses = Array();

	foreach($out_buffer as $key => $opts){
		$value = process_out_opts($key, $opts);
		$responses[$key] = $value;
	}

	flush_db();
	
	echo json_encode( $responses );
}
function initialize(){
	//register_shutdown_function("CO::on_response");
}

function lex_opts($opts){
	$parsed = Array();
	$tokens = split(" ", $opts);

	foreach($tokens as $token){
		preg_match_all("/^(?<opt>[^\[]+)(\[(?<params>.+)\])?/", $token, $result);
		
		$opt = null;
		$params = null;
		if( $result["opt"] != null)
			$opt = $result["opt"][0];
		if( $result["params"] != null )
			$params = split(",", $result["params"][0]);

		array_push($parsed,
			["opt" => $opt, "params" => $params]);
	}

	return $parsed;
}

function process_in_opts($key, $opts){
	GLOBAL $fetch_src;
	GLOBAL $autocommits;

	$autocommit = true;
	$invalid_value = false;
	$glob_name = $key;

	if( array_key_exists($key, $fetch_src) )
		$value = $fetch_src[$key];
	$dst_value = $value;

	foreach($opts as $opt){
		$params = $opt["params"];

		switch( $opt["opt"] ){
			case "required":
				if( $value == null )
					$invalid_value = true;
				break;
			case "optional":
				break;

			case "as":
				$glob_name = $params[0];
				break;

			/* pk [table,(column_name)] */
			case "pk":
				if( count($params) == 1 )
					$column_name = $key;
				else
					$column_name = $params[1];

				$table = \ORM::for_table($params[0]);
				$row = $table
					->where($column_name, $value)
					->find_one();
				$dst_value = $row;
				break;
			case "disable_autocommit":
				$autocommit = false;
				break;

			case "va-rex":
				if( preg_match($params[0], $value) != 1 )
					$invalid_value = true;
				break;
			case "va-range":
				if( is_numeric($value) != 1 )
					$invalid_value = true;
				else{
					if( $value < $params[0] ||
						$value > $params[1] )
						$invalid_value = true;
				}
				break;
			case "va-length":
				$param_count = count($params);
				$len = strlen($value);
				
				/* validate length == param[0] */
				if( $param_count == 1 ){
					if( $len != $params[0] )
						$invalid_value = true;
				}
				/* validate length in_range(param[0], param[1]) */
				else if( $param_count == 2 ){
					if( $len < $param[0] ||
						$len > $param[1] )
						$invalid_value = true;
				}
				break;
		}
	}

	$GLOBALS[$glob_name] = $dst_value;

	if( $invalid_value )
		abort(
			ERR\INVALID_PARAM,
			"invalid parameter - in::${key}");
	if( $autocommit )
		array_push($autocommits, $row);
}
function process_out_opts($key, $opts){
	$glob_name = $key;
	$required = false;

	foreach($opts as $opt){
		$params = $opt["params"];

		switch( $opt["opt"] ){
			case "required":
				$required = true;
				break;
			case "optional":
				break;

			case "from":
				$glob_name = $params[0];
				break;

			case "timestamp":
				return time();
		}
	}

	if( array_key_exists($glob_name, $GLOBALS) )
		return $GLOBALS[$glob_name];
	else if( $required )
		abort(
			ERR\INTERNAL_ERROR,
			"required field is null - out::${key}");
	else
		return null;
}

initialize();

\ORM::configure('mysql:host=' . "localhost" . ';dbname=' . "test");
\ORM::configure('username', "root");
\ORM::configure('password', "asdf1234");

in([
  "asdf" => "required pk[account,nickname] as[player_level] va-length[4] disable_autocommit"
  ]);
out([
  "result" => "required",
  "sex" => ""
  ]);

//echo $player_level;

$player_level->nickname = "qqqq";

on_response();
?>
