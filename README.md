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

That's all, I think they don’t throw rotten eggs at me :stuck_out_tongue_winking_eye:

# TODO

![ServerStatus DROPDOWN](https://i.ibb.co/sChN2gh/Server-Offline-Example.png)

### Однако, WireShark хорошая программа, с помощью неё я смог сделать это..., а, ведь, когда-то я узнавал статус севрера иначе:
#### 1 - На сервере был скрипт, которые посылал POST запрос php обработчику на сайте, запрос содержал в себе информацию о сервере.
#### 2 - Обработчик записывал/создавал/обнавлял данные в JSON файле на сервере (где обработчик).
#### 3 - Когда клиент хотел узнать статус сервера, то он обращался к этому обработчику и тот извлекал нужные данные из JSON файла и приводил их нормальный вид (Короче был нормальный баннер, а то что здесь - тупо извлекает строки и всё)

Так же есть скрипт который создает TcpServer и к нему можно  подцепляться из обработчика php, но как сделать так же я понятия не имею, так как сервер тупо виснет при создании TCP сервера (конечно, я знаю в чем проблема).
