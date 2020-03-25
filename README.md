# ServerStatus
#### Shows the status of the COD MW3 server using UDP protocol

To get information about the server, you need to turn to the Banner class.

```php
  require_once BANNER_PATH.'banner.php';
  
  $banner = new Banner('127.0.0.1', 27015);
  //127.0.0.1 - Server IP
  //27015 - MasterPort(QueryPort)
  //or you can specify additional information
  //new Banner('127.0.0.1', 27015, 'admin', 'server_location');
```

I do not like the front end, so I think that you yourself can make the banner design to your taste.

```php
  echo $banner->field;
  // Fields
  //player_array_data - contains array with players on server
  //g_gametype - game type
  //g_hardcore = hardcore status
  //gamename = game name (IW5 - COD MW3, IW4 - COD MW2)
  //mapname = map name (already converted: mp_dome = Dome)
  //scr_game_allowkillcam = killcam status
  //scr_team_fftype = friendly fire status
  //shortversion = game version (Tekno MW3 - 1.4; Steam - 1.9)
  //sv_allowClientConsole = allow RCON console for clinet
  //sv_hostname = host name (if hostname has symbols ^(1-9), then use function $banner->color_text($banner->sv_hostname))
  //sv_maxclients = max clients for server
  //sv_voice = voice chat type
  //pswrd = server has password
```

# Demo
![ServerStatus](https://i.ibb.co/hBLZNNR/Example.png)
