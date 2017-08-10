<?php
/*
Plugin Name: Guild Wars 2 World vs World
Plugin URI:  https://github.com/IronGhost63/wp-gw2-wvw
Description: WordPress Plugin for displaying Guild Wars 2 WvW Matchup information
Version:     1.0
Author:      Jirayu Yingthawornsuk
Author URI:  https://jirayu.in.th
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: gw2wvwinfo
*/

include("gw2wvw.class.php");

add_action("wp_enqueue_scripts", "mn_enqueue");
add_shortcode("gw2wvw", "gw2wvw_shortcode");

function mn_enqueue(){
	wp_register_style( "gw2wvwinfo", plugins_url("wp-gw-style.css", __FILE__));
	wp_enqueue_style( "gw2wvwinfo" );
}

function gw2wvw_shortcode($attr){
	extract(shortcode_atts(array(
		'world' => '1080',
	), $attr));

	$gw2 = new Gw2wvw(array('current_world' => $world));
	$worlds = $gw2->worlds;

	$vscores = array(
		$gw2->match_info['red']['v_scores'],
		$gw2->match_info['green']['v_scores'],
		$gw2->match_info['blue']['v_scores'],
	);
	$cscores = array(
		$gw2->match_info['red']['current_skirmish_scores'],
		$gw2->match_info['green']['current_skirmish_scores'],
		$gw2->match_info['blue']['current_skirmish_scores'],
	);

	rsort($vscores);
	rsort($cscores);

	$rows = "";
	foreach(array('red', 'green', 'blue') as $single){
		$server_name = '<span class="wvw-server-home">' . $worlds[$gw2->match_info[$single]['host']] . '</span>';

		if($gw2->home_world === $gw2->match_info[$single]['host']){
			$server_name .= ' <i class="wvw-home-icon"></i>';
		}

		if(!empty($gw2->linked[$single])){
			$server_name .= '<br><span class="wvw-server-linked">' . __("Linked:") . '</span> <span class="wvw-server-linked-name">' . $gw2->linked[$single] . '</span>';
		}

		$vscore_width = round( (100/$vscores[0])*$gw2->match_info[$single]['v_scores'], 0 );
		$cscore_width = round( (100/$cscores[0])*$gw2->match_info[$single]['current_skirmish_scores'], 0 );
		$kdr = round( ($gw2->match_info[$single]['kills']/$gw2->match_info[$single]['deaths']), 2);

		$rows .= <<<EOT
			<tr class="result-red">
				<td>{$server_name}</td>
				<td><p class="wvw-percent wvw-percent-{$single}" style="width: {$vscore_width}%;">{$gw2->match_info[$single]['v_scores']}</p></td>
				<td><p class="wvw-percent wvw-percent-{$single}" style="width: {$cscore_width}%;">{$gw2->match_info[$single]['current_skirmish_scores']}</p></td>
				<td class="column-center"><span class="wvw-kdr">{$kdr}</span></td>
			</tr>
EOT;
	}

	$txt = array(
		'worlds' => __("Worlds"),
		'vscore' => __("Victory Points"),
		'cscore' => __("Current Skirmish"),
		'kdr' => __("KDR"),
		'notice' => __("Update every 5 minutes")
	);

	$html = <<<EOT
	<table class="gw2wvw-info-table">
		<thead>
			<tr>
				<th width="50%">{$txt['worlds']}</th>
				<th width="20%" class="column-center">{$txt['vscore']}</th>
				<th width="20%" class="column-center">{$txt['cscore']}</th>
				<th width="10%" class="column-center">{$txt['kdr']}</th>
			</tr>
		</thead>
		<tbody>
			{$rows}
		</tbody>
	</table>
	<p class="gw2wvw-info-table-notice">{$txt['notice']}</p>
EOT;

	return $html;
}
?>