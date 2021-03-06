<?php
$banner = new Banner('127.0.0.1', 27016);
?>

    <div>
        <table>
            <tr>
                <?php echo
                    '<th colspan="2" style="text-align: center">
                <p>'.$banner->color_text($banner->sv_hostname).'<p>
				<p>'.$banner->mapname.'<p>
                <img style="display: block; margin-left: auto; margin-right: auto;" src="maps/'.$banner->mapname.'.png">
            </th>'?>
            </tr>
            <tr>
                <td>Game type</td>
                <?php echo '<td>'.$banner->g_gametype.'</td>' ?>
            </tr>
            <tr>
                <td>Hardcore</td>
                <?php echo '<td>'.$banner->g_hardcore.'</td>' ?>
            </tr>
            <tr>
                <td>Friendly fire</td>
                <?php echo '<td>'.$banner->scr_team_fftype.'</td>' ?>
            </tr>
            <tr>
                <td>Voice option</td>
                <?php echo '<td>'.$banner->sv_voice.'</td>' ?>
            </tr>
            <tr>
                <td>Location</td>
                <?php echo '<td>'.$banner->location.'</td>' ?>
            </tr>
            <tr>
                <td>Admin</td>
                <?php echo '<td>'.$banner->admin.'</td>' ?>
            </tr>
            <tr>
                <td>Clients</td>
                <?php echo '<td>'.count($banner->player_array_data).'/'.$banner->sv_maxclients.'</td>' ?>
            </tr>
        </table>

        <table>
            <tr>
                <th>Player name</th>
                <th>Score</th>
                <th>Ping</th>
            </tr>

            <?php
            foreach ($banner->player_array_data as $key => $item)
            {
                echo '<tr>';
                echo '<td>'.$key.'</td>';
                echo '<td>'.$item['score'].'</td>';
                echo '<td>'.$item['ping'].'</td>';
                echo '</tr>';
            }
            ?>
        </table>
    </div>

    <style>
        div {
            margin: 0;
        }

        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        td, th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #dddddd;
        }
    </style>

<?php

class Banner {

    private $ip;
    private $port;

    public $location;
    public $admin;

    private $send_string = 'LOOP0000';
    private $recive_string;

    public $player_str_data;

    public $player_array_data = array();

    public $g_gametype = 'Undefined';
    public $g_hardcore = 'Undefined';
    public $gamename = 'Undefined';
    public $mapname = 'Offline';
    public $scr_game_allowkillcam = 'Undefined';
    public $scr_team_fftype = 'Undefined';
    public $shortversion = 'Undefined';
    public $sv_allowClientConsole = 'Undefined';
    public $sv_hostname = 'Undefined';
    public $sv_maxclients = 'Undefined';
    public $sv_voice = 'Undefined';
    public $pswrd = 'Undefined';

    private $path_map = 'Offline';

    private $server_info_array = array();

    function __construct($ip, $port, $admin = 'Undefined', $location = 'Undefined') {
        $this->ip = $ip;
        $this->port = $port;

        $this->location = $admin;
        $this->admin = $location;

        $this->run_udp_connect();
    }

    private function run_udp_connect()
    {
        try {
            $udp_client = stream_socket_client('udp://'.$this->ip.':'.$this->port, $errno, $errstr, 5);

            if (!$udp_client)
                return;

            fwrite($udp_client, $this->send_string);
            $this->recive_string = fread($udp_client, 2048);

            if(!$this->recive_string)
                return;
            else
            {
                $this->recive_string = mb_convert_encoding($this->recive_string, 'utf-8', mb_detect_encoding($this->recive_string));
                $this->recive_string = stristr($this->recive_string, 'g_gametype', false);
                $this->server_info_dictionary(explode(chr(92), stristr($this->recive_string, '\mod', true)));
                $this->player_str_data = stristr($this->recive_string, '\mod', false);
                $this->player_str_data = substr($this->player_str_data, 7, mb_strlen($this->player_str_data));
                $this->server_players_data(explode(chr(10), $this->player_str_data));
            }
        }
        catch (Exception $e)
        {}

        // stream_socket_shutdown($udp_client);
    }

    private function server_players_data($array)
    {
        foreach($array as $item)
        {
            if($item)
            {
                $this->str_to_array_player_info($item);
            }
        }
    }

    private function str_to_array_player_info($str)
    {
        $max_space_count = 2;

        $score_and_ping = '';
        $player_name = '';

        for($curent_space = 0, $i = 0; $i < mb_strlen($str); $i++)
        {
            if(chr(32) == $str[$i] && $max_space_count != $curent_space)
                $curent_space++;
            if($max_space_count != $curent_space)
                $score_and_ping = $score_and_ping.$str[$i];
            else
                $player_name =  $player_name.($str[$i] == '"' ? '' : $str[$i]);
        }

        $arr = explode(chr(32), $score_and_ping);

        if(!$player_name)
            return;

        $this->player_array_data[substr($player_name, 1, mb_strlen($player_name))] = array('score' => $arr[0], 'ping' => $arr[1]);
    }

    private function server_info_dictionary($array)
    {
        for ($i = 0; $i <= 32; $i += 2)
            $this->server_info_array[$array[$i]] = $array[$i + 1];

        $this->scr_team_fftype();
        $this->sv_voice();
        $this->gamename();
        $this->shortversion();
        $this->scr_game_allowkillcam();
        $this->sv_allowClientConsole();
        $this->pswrd();
        $this->g_hardcore();
        $this->mapname();
        $this->g_gametype();
        $this->sv_hostname();
        $this->path_map();
        $this->sv_maxclients();
    }

    private function path_map()
    {
        $this->path_map = 'Banner/maps/'.$this->mapname.'.png';
    }

    private function sv_allowClientConsole()
    {
        $this->sv_allowClientConsole = $this->server_info_array['$sv_allowClientConsole'];
    }

    private function shortversion()
    {
        $this->shortversion = $this->server_info_array['shortversion'];
    }

    private function gamename()
    {
        switch($this->server_info_array['gamename'])
        {
            case 'IW5':
                $this->gamename = 'Call Of Duty MW3';
                break;
            case 'IW4':
                $this->gamename = 'Call Of Duty MW2';
                break;
        }
    }

    private function sv_hostname()
    {
        $this->sv_hostname = $this->server_info_array['sv_hostname'];
    }

    private function sv_voice()
    {
        $this->sv_voice =  $this->server_info_array['sv_voice'];
    }

    private function sv_maxclients()
    {
        $this->sv_maxclients =  $this->server_info_array['sv_maxclients'];
    }

    private function pswrd()
    {
        $this->pswrd =  $this->server_info_array['pswrd'];
    }

    private function g_hardcore()
    {
        $this->g_hardcore =  $this->server_info_array['g_hardcore'] == 1 ? 'On' : 'Off';
    }

    private function scr_game_allowkillcam()
    {
        $this->scr_game_allowkillcam =  $this->server_info_array['scr_game_allowkillcam'] == 1 ? 'On' : 'Off';
    }

    private function scr_team_fftype()
    {
        switch($this->server_info_array['scr_team_fftype'])
        {
            case 1:
                $this->scr_team_fftype = 'On';
                break;
            case 2:
                $this->scr_team_fftype = 'Reflect';
                break;
            case 3:
                $this->scr_team_fftype = 'Shared';
                break;
            default:
                $this->scr_team_fftype = 'Off';
                break;
        }
    }

    private function mapname()
    {
        switch($this->server_info_array['mapname'])
        {
            case 'mp_alpha':
                $this->mapname = 'Lockdown';
                break;
            case 'mp_bootleg':
                $this->mapname = 'Bootleg';
                break;
            case 'mp_bravo':
                $this->mapname = 'Mission';
                break;
            case 'mp_carbon':
                $this->mapname = 'Carbon';
                break;
            case 'mp_dome':
                $this->mapname = 'Dome';
                break;
            case 'mp_exchange':
                $this->mapname = 'Downturn';
                break;
            case 'mp_hardhat':
                $this->mapname = 'Hardhat';
                break;
            case 'mp_interchange':
                $this->mapname = 'Interchange';
                break;
            case 'mp_mogadishu':
                $this->mapname = 'Bakaara';
                break;
            case 'mp_paris':
                $this->mapname = 'Resistance';
                break;
            case 'mp_plaza2':
                $this->mapname = 'Arkaden';
                break;
            case 'mp_radar':
                $this->mapname = 'Outpost';
                break;
            case 'mp_seatown':
                $this->mapname = 'Seatown';
                break;
            case 'mp_underground':
                $this->mapname = 'Underground';
                break;
            case 'mp_village':
                $this->mapname = 'Village';
                break;
            case 'mp_lambeth':
                $this->mapname = 'Fallen';
                break;
            case 'mp_terminal_cls':
                $this->mapname = 'Terminal';
                break;
            case 'mp_overwatch':
                $this->mapname = 'Overwatch';
                break;
            case 'mp_park':
                $this->mapname = 'Liberation';
                break;
            case 'mp_italy':
                $this->mapname = 'Piazza';
                break;
            case 'mp_morningwood':
                $this->mapname = 'Black Box';
                break;
            case 'mp_meteora':
                $this->mapname = 'Sanctuary';
                break;
            case 'mp_cement':
                $this->mapname = 'Foundation';
                break;
            case 'mp_qadeem':
                $this->mapname = 'Oasis';
                break;
            case 'mp_aground_ss':
                $this->mapname = 'Aground';
                break;
            case 'mp_courtyard_ss':
                $this->mapname = 'Erosion';
                break;
            case 'mp_hillside_ss':
                $this->mapname = 'Getaway';
                break;
            case 'mp_restrepo_ss':
                $this->mapname = 'Lookout';
                break;
            case 'mp_burn_ss':
                $this->mapname = 'U-Turn';
                break;
            case 'mp_crosswalk_ss':
                $this->mapname = 'Intersection';
                break;
            case 'mp_six_ss':
                $this->mapname = 'Vortex';
                break;
            case 'mp_shipbreaker':
                $this->mapname = 'Decommission';
                break;
            case 'mp_roughneck':
                $this->mapname = 'OffShore';
                break;
            case 'mp_moab':
                $this->mapname = 'Gulch';
                break;
            case 'mp_boardwalk':
                $this->mapname = 'Boardwalk';
                break;
            case 'mp_nola':
                $this->mapname = 'Parish';
                break;
        }
    }

    private function g_gametype()
    {
        switch($this->server_info_array['g_gametype'])
        {
            case 'infect':
                $this->g_gametype = 'Infected';
                break;
            case 'war':
                $this->g_gametype = 'Team Death Match';
                break;
            case 'sd':
                $this->g_gametype = 'Search & Destroy';
                break;
            case 'dm':
                $this->g_gametype = 'Deathmatch';
                break;
            case 'koth':
                $this->g_gametype = 'Headquarters';
                break;
            case 'sab':
                $this->g_gametype = 'Sabotage';
                break;
            case 'ctf':
                $this->g_gametype = 'Capture The Flag';
                break;
            case 'gun':
                $this->g_gametype = 'Gun Game';
                break;
            case 'dd':
                $this->g_gametype = 'Demolition';
                break;
            case 'hlnd':
                $this->g_gametype = 'Stick And Stones';
                break;
            case 'oic':
                $this->g_gametype = 'One in the Chamber';
                break;
            case 'shrp':
                $this->g_gametype = 'Sharp Shooter';
                break;
            case 'tdef':
                $this->g_gametype = 'Team Defender';
                break;
            case 'jugg':
                $this->g_gametype = 'Juggernaut';
                break;
            case 'tjugg':
                $this->g_gametype = 'Team Juggernaut';
                break;
            case 'conf':
                $this->g_gametype = 'Kill Confirmed';
                break;
            case 'grnd':
                $this->g_gametype = 'Drop Zone';
                break;
            case 'dom':
                $this->g_gametype = 'Domination';
                break;
        }
    }

    function color_text($text)
    {
        $Colors = array("#000000","#FF0000","#00F100","#FFCC00","#0F04E8","#04E8E7","#F75AF6","#FFFFFF","#7E7E7E","#6E3C3C");

        $Find		 = array('/\^(\d)([^\^]*)/');
        $Replace	 = array('"<font color=\"".$Colors["$1"]."\">$2</font>"');
        $DataOut 	= preg_replace_callback($Find, function($m) use (&$Colors) {
            return '<font color="'.$Colors[$m[1]].'">'.$m[2].'</font>';
        }, $text);

        return $DataOut;
    }
}

?>