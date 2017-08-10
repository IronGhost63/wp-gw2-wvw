<?php
class Gw2wvw {
	public $worlds;
	public $current_world;
	public $match_info;
	public $home_world;
	public $home_color;
	public $linked;

	function __construct($option = array()){
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
			set_transient( 'gw2_worlds', $gw2worlds );
		}

		return $gw2worlds;
	}

	//public function get_linked_server

	public function get_match_info($world_id = ""){
		if(empty($world_id)){
			$world_id = $this->current_world;
		}

		if(false === ( $score = get_transient( 'gw2_wvw_' . $this->current_world ) ) ){
			$score = $this->get_gw_api( "https://api.guildwars2.com/v2/wvw/matches?world=" . $world_id );
			set_transient( 'gw2_wvw_' . $this->current_world, $score, 300);
		}

		$score = $this->get_gw_api( "https://api.guildwars2.com/v2/wvw/matches?world=" . $world_id );

		$team = array(
			'red' => array(
				'host' => $score->worlds->red,
				'guests' => array_diff($score->all_worlds->red, array($score->worlds->red)),
				'scores' => $score->scores->red,
				'current_skirmish_scores' => end($score->skirmishes)->scores->red,
				'kills' => $score->kills->red,
				'deaths' => $score->deaths->red,
				'v_scores' => $score->victory_points->red
			),
			'blue' => array(
				'host' => $score->worlds->blue,
				'guests' => array_diff($score->all_worlds->blue, array($score->worlds->blue)),
				'scores' => $score->scores->blue,
				'current_skirmish_scores' => end($score->skirmishes)->scores->blue,
				'kills' => $score->kills->blue,
				'deaths' => $score->deaths->blue,
				'v_scores' => $score->victory_points->blue
			),
			'green' => array(
				'host' => $score->worlds->green,
				'guests' => array_diff($score->all_worlds->green, array($score->worlds->green)),
				'scores' => $score->scores->green,
				'current_skirmish_scores' => end($score->skirmishes)->scores->green,
				'kills' => $score->kills->green,
				'deaths' => $score->deaths->green,
				'v_scores' => $score->victory_points->green
			)
		);

		$guest_name = array(
			'red' => '',
			'green' => '',
			'blue' => ''
		);
		$color = array('red', 'blue', 'green');
		foreach($color as $single){
			if(count($team[$single]['guests']) > 0){
				foreach($team[$single]['guests'] as $team_guest){
					$guest[$single][] = $this->worlds[$team_guest];
				}
				//$guest_name[$single] = "(".implode(", ", $guest[$single]).")";
				$guest_name[$single] = implode(", ", $guest[$single]);
			}
			unset($guest);
		}

		if(in_array($world_id, $score->all_worlds->red)){
			$this->home_world = $score->worlds->red;
			$this->home_color = "red";
		}elseif(in_array($world_id, $score->all_worlds->green)){
			$this->home_world = $score->worlds->green;
			$this->home_color = "green";
		}elseif(in_array($world_id, $score->all_worlds->blue)){
			$this->home_world = $score->worlds->blue;
			$this->home_color = "blue";
		}

		$this->match_info = $team;
		$this->linked = $guest_name;
	}
}
?>