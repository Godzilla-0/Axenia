<?php

class Axenia
{

    private $service;

    /**
     * Axenia constructor.
     * @param $service BotService
     */
    public function __construct($service)
    {
        $this->service = $service;
    }

    public function handleUpdate($update)
    {
        if (isset($update["message"]) || isset($update["inline_query"]) || isset($update["callback_query"])) {
            try {
                if (isset($update["message"])) {
                    $this->processMessage($update["message"]);
                } elseif (isset($update["inline_query"])) {
                    $this->processInline($update["inline_query"]);
                } elseif (isset($update["callback_query"])) {
                    $this->processCallback($update["callback_query"]);
                }
            } catch (Exception $e) {
                print_r("Boterror!");
                $this->service->handleException($e, $update);
            }
        }
    }

    /**
     * Check if is need to handle the message by bot
     * @param $message
     * @return bool
     */
    private function needToHandle($message)
    {
        if ($message['chat']['type'] != "channel") {
            if (isset($message['text'])) {
                return Util::startsWith($message['text'], ["/", "+", "-", '👍', '👎']);
            }
            if (isset($message['sticker'])) {
                return Util::startsWith($message['sticker']['emoji'], ['👍', '👎']);
            }
            if (isset($message['new_chat_member']) || isset($message['new_chat_title']) || isset($message['left_chat_member']) || isset($message['migrate_to_chat_id'])) {
                return true;
            }
        }

        return false;
    }

    public function processMessage($message)
    {
        if ($this->needToHandle($message)) {
            $message_id = $message['message_id'];
            $chat = $message['chat'];
            $from = $message['from'];

            $chat_id = $chat['id'];
            $from_id = $from['id'];

            $this->service->insertOrUpdateUser($from);
            $isNewChat = $this->service->initLang($chat);
            if ($isNewChat) {
                $this->service->rememberChat($chat, $from_id);
            }

            if (isset($message['text']) || isset($message['sticker'])) {
                $redis=new Redis();
                $redis->connect('127.0.0.1', 6379, 2.5);
                $isPrivate = $this->service->isPrivate($chat);
                $postfix = $isPrivate ? "" : ("@" . BOT_NAME);
                if (isset($message['sticker'])) {
                    $text = $message['sticker']['emoji'];
                } else {
                    $text = $message['text'];
                }
                switch (true) {
                    case Util::startsWith($text, ["+", "-", '👍', '👎']):
                        if ($isPrivate) {
                            Request::sendMessage($chat_id, Lang::message("bot.onlyPrivate"));
                        } else {
                            if (preg_match('/^(\+|\-|👍|👎) ?([\s\S]+)?/ui', $text, $matches)) {
                                if ($this->service->checkCoolDown($from_id, $chat)) {
                                    $isRise = Util::isInEnum("+,👍", $matches[1]);
                                    if (isset($message['reply_to_message'])) {
                                        $replyUser = $message['reply_to_message']['from'];
                                        if ($replyUser['username'] != BOT_NAME && !$this->service->isBot($replyUser['username'])) {
                                            $this->service->insertOrUpdateUser($replyUser);
                                            $this->doKarmaAction($isRise, $from_id, $replyUser['id'], $chat_id);
                                        }
                                    } else {
                                        if (preg_match('/@([\w]+)/ui', $matches[2], $user)) {
                                            if (BOT_NAME != $user[1] && !$this->service->isBot($user[1])) {
                                                $to = $this->service->getUserID($user[1]);
                                                if ($to) {
                                                    if(Request::isChatMember($to, $chat_id)){
                                                        $this->doKarmaAction($isRise, $from_id, $to, $chat_id);
                                                    } else {
                                                        Request::sendHtmlMessage($chat_id, Lang::message('karma.unknownUser.kicked'), ['reply_to_message_id' => $message_id]);
                                                    }
                                                } else {
                                                    Request::sendHtmlMessage($chat_id, Lang::message('karma.unknownUser'), ['reply_to_message_id' => $message_id]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    case (Util::startsWith($text, "/buy" . $postfix)):
                        Request::sendTyping($chat_id);
                        if ($isPrivate) {
                            Request::sendMessage($chat_id, Lang::message("bot.onlyPrivate"));
                        } else {
                            $this->sendStore($chat_id, $from);
                        }
                        break;
                    case (Util::startsWith($text, "/donate" . $postfix)):
                        if ($isPrivate) {
                            //Прикроем пока Дуров донат не активирует, в рот я ебал этот забагованный киви.
                            /*
                            $button_list=[];
                            foreach($this->service->getDonates() as $a){
                                array_push($button_list, ['text' => Lang::message('donate.price', ['k' => $a['nominal'], 'r' => $a['price']]), 'callback_data' => 'donate_'.$a['id']]);
                            }
                            $text=Lang::message("donate.title");
                            Request::sendHtmlMessage($from_id, $text, ["reply_markup" => ['inline_keyboard' => array_chunk($button_list,2)]]);
                            */
                        }
                        break;
                    case (Util::startsWith($text, "/settings" . $postfix)):
                        Request::sendTyping($chat_id);
                        $this->sendSettings($chat, NULL, NULL, $this->service->isAdminInChat($from_id, $chat));
                        break;
                    case (Util::startsWith($text, "/top" . $postfix)):
                        Request::sendTyping($chat_id);
                        if ($isPrivate) {
                            Request::sendMessage($chat_id, Lang::message("bot.onlyPrivate"));
                        } else {
                            $out = $this->service->getTop($chat_id, 10);
                            Request::sendHtmlMessage($chat_id, $out);
                        }
                        break;
                    case (Util::startsWith($text, "/my_stats" . $postfix)):
                        Request::sendTyping($chat_id);
                        $statsMessage = $this->service->getStats($from, $isPrivate ? NULL : $chat_id);
                        Request::sendHtmlMessage($chat_id, $statsMessage);
                        break;
                    case (Util::startsWith($text, "/start" . $postfix)):
                        if ($isPrivate) {
                            Request::sendTyping($chat_id);
                            Request::sendHtmlMessage($chat_id, Lang::message('chat.greetings'));
                            Request::sendHtmlMessage($chat_id, Lang::message('user.pickChat', ["botName"=> BOT_NAME]));
                        } else {
                            $this->service->rememberChat($chat, $from_id);
                            Request::sendHtmlMessage($chat_id, Lang::message('bot.start'));
                        }
                        break;
                    case (Util::startsWith($text, "/help" . $postfix)):
                        Request::sendHtmlMessage($chat_id, Lang::message('chat.help'));
                        break;
                    case Util::startsWith($text, ("/set @")):
                        if ($this->service->CheckRights($from_id,5)) {
                            if (preg_match('/^(\/set) @([\w]+) (-?\d+)/ui ', $text, $matches)) {
                                Request::sendMessage($from_id, $this->service->setLevelByUsername($matches[2], $chat_id, $matches[3]));
                            }
                        }
                        break;
                    case Util::startsWith($text, ("/test")):
                        if ($this->service->CheckRights($from_id,5)) {
                            $max=$redis->get("limit:zadolbali");
                            $text=file_get_contents("http://zadolba.li/story/".rand(1,$max));
                            $text=substr($text, strpos($text,"<div class='text'>"),-1);
                            $text=str_replace("<br>","\r\n",$text);
                            $text=html_entity_decode($text);
                            $text=strip_tags(substr($text, 0,strpos($text,"</div>")));
                            Request::sendMessage($chat_id,$text);
                        }
                        break;
                    case Util::startsWith($text, ("/Redis")):
                        if ($this->service->CheckRights($from_id,5)) {
                            if (preg_match('/^(\/RedisSet) ([\S]+) ([\S]+)/ui ', $text, $matches)) {
                                Request::sendMessage($chat_id, print_r($redis->set($matches[2],$matches[3]),true));
                            }
                            if (preg_match('/^(\/RedisGet) ([\S]+)/ui ', $text, $matches)) {
                                Request::sendMessage($chat_id, print_r($redis->get($matches[2]),true));
                            }
                            if (preg_match('/^(\/RedisKeys) ([\S]*)/ui ', $text, $matches)) {
                                Request::sendMessage($chat_id, print_r($redis->keys($matches[2]),true));
                            }
                            if (preg_match('/^(\/RedisDel) ([\S]*)/ui ', $text, $matches)) {
                                Request::sendMessage($chat_id, print_r($redis->del($matches[2]),true));
                            }
                        }
                        break;
                    case Util::startsWith($text, ("/lala")):
                        if (defined('TRASH_CHAT_ID')) {
                            Request::sendTyping($chat_id);
                            $ok = false;
                            do {
                                $message = Request::exec("forwardMessage", array('chat_id' => TRASH_CHAT_ID, "from_chat_id" => "@rgonewild", "disable_notification" => true, "message_id" => rand(1, 21363)));
                                if ($message !== false && isset($message['photo'])) {
                                    $array = $message['photo'];
                                    $file_id = $array[0]['file_id'];
                                    foreach ($array as $file) {
                                        $height = (int)$file['height'];
                                        if ($height > 600 && $height <= 1280) {
                                            $file_id = $file['file_id'];
                                        }
                                    }
                                    Request::sendPhoto($chat_id, $file_id);
                                    $ok = true;
                                }
                                sleep(1);
                            } while (!$ok);
                        }
                        break;
                }
                $redis->close();
            } elseif (isset($message['new_chat_member'])) {
                $newMember = $message['new_chat_member'];
                if (BOT_NAME == $newMember['username']) {
                    $isRemembered = $this->service->rememberChat($chat, $from_id);
                    $this->service->setBotPresentedInChat($chat_id, true);
                    if ($isRemembered !== false) {
                        if (defined('LOG_CHAT_ID')) {
                            Request::sendHtmlMessage(LOG_CHAT_ID, " 🌝 " . Request::getChatMembersCount($chat_id) . "|" . $this->service->getChatMembersCount($chat_id) . " (" . Util::getChatLink($chat) . ") by ". Util::getFullNameUser($from, false));
                        }
                        Request::sendMessage($chat_id, Lang::message('chat.greetings'), ["parse_mode" => "Markdown"]);
                    }
                }
                // убрал пока
                //else { $this->service->insertOrUpdateUser($newMember); }
            } elseif (isset($message['new_chat_title'])) {
                $this->service->rememberChat($chat, $from_id);
            } elseif (isset($message['left_chat_member'])) {
                $member = $message['left_chat_member'];
                if (BOT_NAME == $member['username']) {
                    //$isDeleted = $this->service->deleteChat($chat_id);
                    $this->service->setBotPresentedInChat($chat_id, false);
                    if (defined('LOG_CHAT_ID')) {
                        Request::sendHtmlMessage(LOG_CHAT_ID, " 🌚 -1|" . $this->service->getChatMembersCount($chat_id) . " (" . Util::getChatLink($chat) . ") by ". Util::getFullNameUser($from, false));
                    }
                }
            } elseif (isset($message['migrate_to_chat_id'])) {
                $rez = $this->service->migrateToNewChatId($message['migrate_to_chat_id'], $chat_id);
                if (defined('LOG_CHAT_ID')) {
                    Request::sendHtmlMessage(LOG_CHAT_ID, "Migration from " . $chat_id . " to " . $message['migrate_to_chat_id'] ." was " . ($rez? "successful" : "Unsuccessful") );
                }
            }
        }
    }

    public function doKarmaAction($isRise, $from_id, $user_id, $chat_id)
    {
        $out = $this->service->handleKarma($isRise, $from_id, $user_id, $chat_id);
        if (!$this->service->isSilentMode($chat_id)) {
            Request::sendHtmlMessage($chat_id, $out['msg']);
        }

        if ($out['good'] == true) {
            if ($out['newLevel'] != null) {
                $rewardMessages = $this->service->handleRewards($out['newLevel'], $chat_id, $user_id);
                if (count($rewardMessages) > 0) {
                    foreach ($rewardMessages as $msg) {
                        Request::sendHtmlMessage($chat_id, $msg);
                    }
                    if ($user = $this->service->getUser($user_id)) {
                        sleep(2);
                        $username = Util::getFullNameUser($user, false);
                        Request::sendHtmlMessage($chat_id, Lang::message('bot.rating', ['user' => $username, 'botName' => BOT_NAME]));
                    }
                }
            }
        }
    }

    public function processInline($inline)
    {
        $id = $inline['id'];
        $from = $inline['from'];
        $query = $inline['query'];

        if (isset($query) && $query !== "") {
            $users = $this->service->getUserList($query);

            if ($users) {
                Request::answerInlineQuery($id, $users);
            } else {
                Request::answerInlineQuery($id, [
                    [
                        "type" => "article",
                        "id" => "0",
                        "title" => Lang::message('chat.greetings'),
                        "message_text" => Lang::message('chat.greetings')
                    ]
                ]);
            }
        }
    }

    public function sendStore($chat_id, $from = NULL, $message = NULL, $text = NULL, $callback = NULL, $callback_id = NULL)
    {
        $message_id = $message['message_id'];
        $store=$this->service->getShowcase();
        $store=array_chunk($store,3);

        $button_list =[];
        foreach ($store as $value){
            if($value[2]=="0" || (Lang::isUncensored() && $value[2]=="1") ) array_push($button_list,
                    [
                        'text' => Lang::message('store.button.buy_'.$value[0],['price'=>$value[1]]),
                        'callback_data' => 'buy_'.$value[0] . '|' . $from['id'] . '|' .$value[1]
                    ]
                );
        }
        $inline_keyboard = array_chunk($button_list,2);
        $username = $this->service->getUserName($from['id']);
        $karma = $this->service->getUserLevel($from['id'], $chat_id);

        if ($message == NULL && $text == NULL) {
            $text = Util::insert(Lang::message('store.title'), ["user" => $username, "k" => $karma]);
            Request::sendHtmlMessage($chat_id, $text, ["reply_markup" => ['inline_keyboard' => $inline_keyboard]]);
        } else {
            $command = explode("|", $callback);
            $newKarma = $karma - (int)$command[2];
            if ($newKarma >= 0) {
                switch ($command[0]) {
                    case 'buy_gif':
                        Request::sendDocument($chat_id, $text, ['reply_to_message_id' => $message_id]);
                        break;
                    case 'buy_bashorg':
                    case 'buy_jokes':
                    case 'buy_jokes18':
                    case 'buy_zadolbali':
                    case 'buy_ideer':
                        Request::sendMessage($chat_id, $text, ['reply_to_message_id' => $message_id]);
                        break;
                    default:
                        if (Util::endsWith($text, "gif") == 1) {
                            Request::sendDocument($chat_id, $text, ['reply_to_message_id' => $message_id]);
                        } else {
                            Request::sendPhoto($chat_id, $text, ['reply_to_message_id' => $message_id]);
                        }
                }

                $newMessage = Util::insert(Lang::message('store.event.' . $command[0]), ["user" => $username, "k" => $newKarma]);
                $callbackMessage = Util::insert(Lang::message('store.callback'), ["buy" => Lang::message('store.button.' . $command[0], ["price" => $command[2]]), "k" => $newKarma]);
                $this->service->setLevel($from['id'], $chat_id, $newKarma);
            } else {
                $newMessage = Util::insert(Lang::message('store.event.cant_buy'), ["user" => $username, "k" => $karma, "buy" => Lang::message('store.button.' . $command[0], ["price" => $command[2]])]);
                $callbackMessage = Util::insert(Lang::message('store.callback.cant_buy'), ["buy" => Lang::message('store.button.' . $command[0])]);
            }
            Request::editMessageText($chat_id, $message_id, $newMessage, ["parse_mode" => "HTML"]);
            Request::answerCallbackQuery($callback_id, $callbackMessage);
        }
    }

    public function sendSettings($chat, $message = NULL, $type = NULL, $showButtons = true)
    {
        $chat_id = $chat['id'];
        $postfixSilentMode = "silent_mode" . ($this->service->isSilentMode($chat_id) ? "_on" : "_off");
        switch ($type) {
            case "set_cooldown":
                $minuteText = Lang::message('settings.minute');
                $button_list = [
                    [
                        ['text' => "0.1" . $minuteText, 'callback_data' => 'set_0'],
                        ['text' => "1" . $minuteText, 'callback_data' => 'set_1'],
                        ['text' => "2" . $minuteText, 'callback_data' => 'set_2']
                    ],
                    [['text' => Lang::message("settings.button.back"), 'callback_data' => "set_back"]]
                ];
                $text = Lang::message('settings.select.cooldown');
                break;
            case "set_lang":
                $ln = Lang::availableLangs();
                $keys = array_keys($ln);
                $values = array_values($ln);
                $button_list = [
                    [
                        ['text' => $values[0], 'callback_data' => $keys[0]],
                        ['text' => $values[1], 'callback_data' => $keys[1]]
                    ],
                    [['text' => $values[2], 'callback_data' => $keys[2]]],
                    [['text' => Lang::message("settings.button.back"), 'callback_data' => "set_back"]]
                ];
                $text = Lang::message('settings.select.lang');
                break;
            case "set_escapeFromGroup":
                $a=$this->service->getUserGroup($chat_id,false);
                $buttons=[];
                foreach($a as $item){
                    array_push($buttons,['text'=>explode(":",$item)[1],'callback_data'=>"escape_".explode(":",$item)[0]]);
                }

                $button_list=array_chunk($buttons,3);
                $text = Lang::message('settings.unfollow.title');
                break;
            default:
                $text = Lang::message('settings.title') . "\r\n";
                if ($this->service->isPrivate($chat)) {
                    $button_list = [
                        [
                            [
                                'text' => Lang::message('settings.button.lang'),
                                'callback_data' => 'set_lang'
                            ],
                            [
                                'text'  => Lang::message('settings.unfollow'),
                                'callback_data' =>  'set_escapeFromGroup'
                            ]
                        ]
                    ];
                    $text .= Lang::message("settings.title.lang", ["lang" => Lang::getCurrentLangDesc()]) . "\r\n";
                } else {
                    $button_list = [
                        [
                            ['text' => Lang::message("settings.button.toggle_silent_mode"),
                                'callback_data' => 'set_toggle_silent_mode'
                            ],
                            ['text' => Lang::message('settings.button.lang'),
                                'callback_data' => 'set_lang'
                            ]
                        ],
                        [['text' => Lang::message('settings.button.set_cooldown'),
                            'callback_data' => 'set_cooldown'
                        ]]
                    ];

                    $text .= Lang::message("settings.title." . $postfixSilentMode) . "\r\n";
                    $text .= Lang::message("settings.title.lang", ["lang" => Lang::getCurrentLangDesc()]) . "\r\n";
                    $text .= Lang::message('settings.title.cooldown', ["cooldown" => $this->service->getCooldown($chat_id)]);
                }

                break;
        }
        $inline_keyboard = $button_list;

        if ($message == NULL) {
            if ($showButtons) {
                Request::sendHtmlMessage($chat_id, $text, ["reply_markup" => ['inline_keyboard' => $inline_keyboard]]);
            } else {
                Request::sendHtmlMessage($chat_id, $text);
            }
        } else {
            Request::editMessageText($chat_id, $message['message_id'], $text, ["reply_markup" => ['inline_keyboard' => $inline_keyboard], "parse_mode" => "HTML"]);
        }
    }

    public function processCallback($callback)
    {
        $from = $callback['from'];
        $message = $callback['message'];
        $data = $callback['data'];
        $chat = $message['chat'];
        $chat_id = $chat['id'];
        $this->service->initLang($chat);
        $isAdminInChat = $this->service->isAdminInChat($from['id'], $chat);
        if (in_array($data, array_keys(Lang::availableLangs()))) {
            if ($isAdminInChat) {
                $qrez = $this->service->setLang($chat, $data);
                if ($qrez) {
                    Lang::init($data);
                }
                $this->sendSettings($chat, $message, NULL);
            } else {
                Request::answerCallbackQuery($callback['id'], Lang::message('settings.adminonly'));
            }
        } elseif (strpos($data, "buy_") !== false) {
            $data_array = explode('|', $data);
            if ($data_array[1] == $from['id']) {
                switch ($data_array[0]) {
                    case 'buy_tits':
                        $tits = json_decode(file_get_contents("http://api.oboobs.ru/boobs/1/1/random"), true);
                        $rez = "http://media.oboobs.ru/boobs/" . sprintf("%05d", $tits[0]['id']) . ".jpg";
                        break;
                    case 'buy_butts':
                        $butts = json_decode(file_get_contents("http://api.obutts.ru/butts/1/1/random"), true);
                        $rez = "http://media.obutts.ru/butts/" . sprintf("%05d", $butts[0]['id']) . ".jpg";
                        break;
                    case 'buy_bashorg':
                        $rez = str_ireplace("' + '","",file_get_contents("http://bash.im/forweb/?u"));
                        $rez=substr($rez, strpos($rez,"<div id=\"b_q_t\""),-1);
                        $rez=str_replace("<br>","\r\n",$rez);
                        $rez=html_entity_decode($rez);
                        $rez=strip_tags(substr($rez, 0,strpos($rez,"<small>")));
                        break;
                    case 'buy_jokes':
                        $json=iconv("CP1251", "UTF-8",file_get_contents ("http://rzhunemogu.ru/RandJSON.aspx?CType=1"));
                        $rez=substr($json,12,-2);
                        break;
                    case 'buy_jokes18':
                        $json=iconv("CP1251", "UTF-8",file_get_contents ("http://rzhunemogu.ru/RandJSON.aspx?CType=11"));
                        $rez=substr($json,12,-2);
                        break;
                    case 'buy_cats':
                        $cat = json_decode(file_get_contents("http://random.cat/meow"), true);
                        $rez = $cat["file"];
                        break;
                    case 'buy_gif':
                        if (defined('TRASH_CHAT_ID')) {
                            Request::sendTyping($chat_id);
                            $ok = false;
                            do {
                                $tmp = Request::exec("forwardMessage", array('chat_id' => TRASH_CHAT_ID, "from_chat_id" => "@GIFsChannel", "disable_notification" => true, "message_id" => rand(1, 1920)));
                                if ($tmp !== false && isset($tmp['document']) && !isset($tmp['text'])) {
                                    $array = $tmp['document'];
                                    $rez = $array['file_id'];
                                    $ok = true;
                                }
                                sleep(1);
                            } while (!$ok);
                        }
                        break;
                    case 'buy_zadolbali':
                        $redis=new Redis();
                        $redis->connect('127.0.0.1', 6379, 2.5);
                        $max=$redis->get("limit:zadolbali");
                        $text=file_get_contents("http://zadolba.li/story/".rand(1,$max));
                        $text=substr($text, strpos($text,"<div class='text'>"),-1);
                        $text=str_replace("<br>","\r\n",$text);
                        $text=html_entity_decode($text);
                        $rez=strip_tags(substr($text, 0,strpos($text,"</div>")));
                        $redis->close();
                        break;
                    case 'buy_ideer':
                        $redis=new Redis();
                        $redis->connect('127.0.0.1', 6379, 2.5);
                        $max=$redis->get("limit:ideer");
                        $text=file_get_contents("https://ideer.ru/".rand(1,$max));
                        $text=substr($text, strpos($text,"<div class=\"shortContent\">"),-1);
                        $text=str_replace("<br>","\r\n",$text);
                        $text=html_entity_decode($text);
                        $rez=strip_tags(substr($text, 0,strpos($text,"</div>")));
                        $redis->close();
                        break;
                    default:
                        $rez = $data;
                }
                $this->sendStore($chat_id, $from, $message, $rez, $data, $callback['id']);
            } else {
                Request::answerCallbackQuery($callback['id'], Lang::message('store.wrongPick', array('user' => $data_array[1])));
            }
        } elseif (strpos($data, "set_") !== false) {
            if ($isAdminInChat) {
                switch ($data) {
                    case 'set_toggle_silent_mode':
                        $this->service->toggleSilentMode($chat_id);
                        break;
                    case 'set_0':
                        $this->service->setCooldown($chat_id, 0.1);
                        break;
                    case 'set_1':
                        $this->service->setCooldown($chat_id, 1);
                        break;
                    case 'set_2':
                        $this->service->setCooldown($chat_id, 2);
                        break;
                    case 'set_back':
                        $data = NULL;
                        break;
                }
                $this->sendSettings($chat, $message, $data);
            } else {
                Request::answerCallbackQuery($callback['id'], Lang::message('settings.title'));
            }
        } elseif (strpos($data, "donate_") !== false) {

            $donates = $this->service->getDonates();
            foreach ($donates as $k => $a) if ($a['id'] == explode("_", $data)[1]) $key = $k;

            $text = "https://bill.qiwi.com/order/external/create.action";
            //$txn_id = substr(hash_hmac('sha256',  rand(1, 99999999), Date('d-m-Yhh-mm-ss')),1,16);

            $txn_id = md5(Date('dmYhhmmss') . $chat_id);
            $params = [
                "from" => QIWI_API_ID,
                "summ" => $donates[$key]['price'],
                "currency" => "RUB",
                "comm" => "Получение " . $donates[$key]['nominal'] . " печенек",
                "txn_id" => $txn_id,
                "iframe" => "true",
                "successUrl" => 'http://' . $_SERVER['SERVER_NAME'] . '/success.php',
                "lifetime" => date('Y-m-d', strtotime(date('Y-m-d') . " + 1 DAY")) . "T00:00:00",
                "target" => "iframe"
            ];
            $url = $text . "?" . http_build_query($params);
            $googer = new GoogleURLAPI(GOOGLE_API_KEY);
            $shortDWName = $googer->shorten($url);
            $text = Lang::message('donate.bill', ['nom' => $donates[$key]['nominal'], 'url' => $shortDWName]);
            Request::editMessageText($chat_id, $message['message_id'], $text, ["parse_mode" => "HTML", "reply_markup" => ['inline_keyboard' => [[["text" => Lang::message("donate.pay"), "url" => $shortDWName]]]]]);
            $this->service->insertBill($txn_id, $donates[$key]['id'], $chat_id);
        } elseif (strpos($data, "escape_") !== false) {
            $escape_chat_id=explode("_",$data)[1];
            $escape_chat=$this->service->getGroupName($escape_chat_id);
            if(strpos($data, "accept") !== false){
                $this->service->deleteUserDataInChat($chat_id,$escape_chat_id);
                $text = Lang::message('settings.unfollow.success',['chat_id' =>$escape_chat_id,'chat'=>$escape_chat]);
                Request::editMessageText($chat_id, $message['message_id'], $text, ["parse_mode" => "HTML"]);
            }elseif(strpos($data, "reject") !== false){
                $text = Lang::message('settings.unfollow.cancel',['chat_id' =>$escape_chat_id,'chat'=>$escape_chat]);
                Request::editMessageText($chat_id, $message['message_id'], $text, ["parse_mode" => "HTML"]);
            }else{
                $text = Lang::message('settings.unfollow.confirm',['chat_id' =>$escape_chat_id,'chat'=>$escape_chat]);
                Request::editMessageText($chat_id, $message['message_id'], $text, ["parse_mode" => "HTML", "reply_markup" => ['inline_keyboard' => [[["text" => "✔️".Lang::message("confirm.yes"), "callback_data" => $data."_accept"],["text" => "❌".Lang::message("confirm.no"), "callback_data" => $data."_reject"]]]]]);
            }
        }
    }

}

?>