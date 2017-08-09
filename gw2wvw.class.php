<?php
function get_transient($name){
	return false;
}

class Gw2wvw {
	public $worlds;
	public $current_world;
	public $match_info;
	public $home_color;

	function __construct($options = array()){
		if(empty($option['current_world'])){
			$option['current_world'] = 1018;
		}

		$this->worlds = $this->get_worlds_list();
		$this->current_world = $option['current_world'];
		$this->get_match_info($option['current_world']);
	}

	public function get_gw_api($endpoint){
			$arrContextOptions=array(
				"ssl"=>array(
					"verify_peer"=>false,
					"verify_peer_name"=>false,
				),
			);

			return json_decode(file_get_contents($endpoint, false, stream_context_create($arrContextOptions)));
	}

	public function get_worlds_list(){
		if ( false === ( $gw2worlds = get_transient( 'gw2_worlds' ) ) ) {
			$gw2worlds = array();

			if( false != ( $server_name_raw = $this->get_gw_api("https://api.guildwars2.com/v2/worlds?ids=all") )){
				foreach($server_name_raw as $single){
					$gw2worlds[$single->id] = $single->name;
				}
			}
			//set_transient( 'gw2_worlds', $gw2worlds );
		}

		return $gw2worlds;
	}

	public function get_match_info($world_id = ""){
		if(empty($world_id)){
			$world_id = $this->current_world;
		}

		$data = $this->get_gw_api( "https://api.guildwars2.com/v2/wvw/matches?world=" . $world_id );

		switch($world_id){
			case $data->worlds->red :
				$this->home_color = "red";
				break;
			case $data->worlds->blue :
				$this->home_color = "blue";
				break;
			case $data->worlds->green :
				$this->home_color = "green";
				break;
		}
	}
}

$gw2wvw = new Gw2wvw();
var_dump($gw2wvw);
?>