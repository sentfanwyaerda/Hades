<?php
class Hades {
	var $notification_log = array();
	var $modules = array();
	function Hades(){
		$start = microtime();
		$this->notification_log[] = json_encode(array('%when'=>$start,'%code'=>__CLASS__.'.start'));
		global $hades;
		$hades = $this;
		#Hades::time_status(NULL, $start);
		#Hades::time_status();
		Hades::notify(__CLASS__.'.initiated');
	}
	public function &get_instance(){
		global $hades;
		if(!is_object($hades)){ $hades = new Hades(); }
		return $hades;
	}

	public function get_root($sub=NULL){
		return dirname(dirname(dirname(__FILE__))).($sub == NULL ? NULL : DIRECTORY_SEPARATOR.$sub.DIRECTORY_SEPARATOR);
	}
	public function load_module($name=FALSE){
		if(!is_array($name) && class_exists($name)){
			Hades::notify(__METHOD__.'.already', $name);
			return TRUE;
		}
	
		$ds = DIRECTORY_SEPARATOR;
		if(is_array($name)){
			Hades::notify(__METHOD__.'.multiple', implode(', ', $name));
			$b = TRUE;
			foreach($name as $i=>$sub){
				$task = Hades::load_module($sub);
				$b = ($b && $task );
			}
			return $b;
		}
		elseif($name === TRUE || $name === NULL){ /*load all modules*/ }
		else{
			if(file_exists(Hades::get_root("module").$name) && is_dir(Hades::get_root("module").$name) && file_exists(Hades::get_root("module").$name.$ds.$name.".php") ){
				require_once(Hades::get_root("module").$name.$ds.$name.".php");
			}
			elseif(file_exists(Hades::get_root("module").$name.".php")){
				require_once(Hades::get_root("module").$name.".php");
			}
			else{ Hades::notify(__METHOD__.'.fail', array("module"=>$name)); return FALSE; }
						
			if(class_exists($name)){
				$hades =& Hades::get_instance();
				$hades->modules[] = $name;
				Hades::notify(__METHOD__, array("module"=>$name));
			}
			else{
				Hades::notify(__METHOD__.'.fail.class', array("module"=>$name));
			}
			return TRUE;
		}
	}
	
	public function notify($code, $vars=array(), $line=NULL){
		if(!is_array($vars)){ $vars = array("message" => $vars); }
		if($line !== NULL){ $vars = array_merge(array("%line" => $line), $vars); }
		$hades =& Hades::get_instance();
		$vars = array_merge(array("%when" => microtime(), "%code" => $code), $vars);
		if(is_array($code)){ foreach($code as $i=>$sub){
			$vars['%code'] = $sub;
			$hades->notification_log[] = json_encode($vars);
		}}
		else{
			$hades->notification_log[] = json_encode($vars);
		}
	}

	public function run($request=NULL, $print=FALSE){
		if($request == NULL){ $request = Hades::find_request(); }

		if(preg_match("#.(md|txt|html)$#i", $request) ){
			$run = Morpheus::basic_parse_template(Hades::get_root("content/text").$request);
			$str = $run;
		} else {
			Hades::notify(415);
			$str = Morpheus::basic_parse_template(Hades::get_root("content/text").'415-unsupported-media-type.md');
		}
		/*debug*/ $str = '``'.$request."``\n".$str;

		Hades::notify(__METHOD__);
		if($print == FALSE){
			return $str;
		} else {
			print $str;
		}
	}
	public function find_request(){
		$request = $_SERVER["QUERY_STRING"];
		
		if($request == NULL){ $request = 'welcome.md'; }
		Hades::notify(__METHOD__, array("URI"=>$request));
		return $request;
	}
	
	public function time_status($finish=NULL, $start=NULL){
		#ignore notification log:
		if($finish === FALSE){ $finish = microtime(); }
		if($start === FALSE){ $start = microtime(); }
		#use notification log:
		if($finish == NULL || $start == NULL) $hades =& Hades::get_instance();
		if($start == NULL){
			$first = json_decode($hades->notification_log[0]);
			$start = $first->{"%when"};
		}
		if($finish == NULL){
			$last = json_decode(end($hades->notification_log));
			$finish = $last->{"%when"};
		}
		#calculate:
		$a = explode(' ', $start);
			$x = $a[0]+$a[1];
		$b = explode(' ', $finish);
			$y = $b[0]+$b[1];
		$length = round($y-$x, 8);
				
		Hades::notify(__METHOD__, array(/*"start"=>$start, "finish"=>$finish,*/ "length"=>$length));
		return $length;
	}
}
/*debug*/ header("Content-type: text/plain;");

$hades = new Hades();
#Hades::time_status();

#Hades::load_module(array("Heracles", "UAPI", "Hermes", "Morpheus", "FSnode"));
Hades::load_module("Morpheus");
#Hades::time_status();

Hades::run(Hades::find_request(), TRUE);

/*debug*/ print "\n".Hades::time_status()."s\n";

/*debug*/ print "\n\n<pre>"; print_r(Hades::get_instance()); print '</pre>';
?>