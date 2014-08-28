<?php namespace CO;
require_once "idiorm.php";

$fetch_src = $_GET;
$in_buffer = Array();
$out_buffer = Array();
$autocommits = Array();

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

/* backends */
function on_response(){
	GLOBAL $out_buffer;

	$responses = Array();

	foreach($out_buffer as $key => $opts){
		$value = process_out_opts($key, $opts);
		$responses[$key] = $value;
	}

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

	$autocommit = true;
	$invalid_value = false;
	$glob_name = $key;
	$value = $fetch_src[$key];

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

			case "pk":
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

	$GLOBALS[$glob_name] = $value;

	if( $invalid_value )
		echo "invalid";
	//if( $autocommit )
//		$autocommits
}
function process_out_opts($key, $opts){
	$glob_name = $key;

	//echo var_dump($opts);
	foreach($opts as $opt){
		$params = $opt["params"];

		switch( $opt["opt"] ){
			case "required":
				break;
			case "optional":
				break;

			case "from":
				$glob_name = $params[0];
				break;

			case "timestamp":
				
				break;
		}
	}

	return $GLOBALS[$glob_name];
}

initialize();

in([
  "level" => "required pk[a,b] as[player_level] va-length[4]"
  ]);
out([
  "result" => "",
  "sex" => "from[buta]\"
  ]);

echo is_numeric("23f");

on_response();
?>
