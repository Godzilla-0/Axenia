<?php return [
"bot.name" => "Axenia",
"bot.start" => "So it begins.",
"bot.error" => "Ops! Something broken.. 🤷‍♀️",
"bot.onlyPrivate" => "Sorry, but this I can do only in groups.",
"user.pickChat" => "Please, <a href='t.me/:botName?startgroup=0'>pick а group</a> where I'll handle the karma points and more. ✌😊",
"chat.greetings" => "Hello! My name is Axenia and I can count the karma points.\r\nUse /help to see more about and /settings for switch language.",
"chat.help" => "<b>💬 What I can?</b>\r\n\r\n<b>Raising/reducing karma</b>
Type 'plus' (+ or 👍) for raising or 'minus' (- or 👎) for reducing karma points to the user on which message it will be replying.
Also I understand messages in format like \"+ @username\" and etc.\r\n
<b>Commands</b>
/top - show top of users in current group.
/my_stats - show your global statistics.
/settings - open dialog to change the language and to toggle silent-mode which mute bot comments and cooldown time between user's messages about decrease/increase karma points (admin only)
/buy - Also you may buy some content (kitty, gif etc) on your karma points.\r\n
<i>Statistics are available on</i> axeniabot.ru.",
"user.stat.inline" => "Statistic of the 👤 :user",
"user.stat.title" => " ℹ️ <b>The user's statistic</b>",
"user.stat.name" => "👤 Name: ",
"user.stat.inchat" => "📍 Karma's rate in current group: ",
"user.stat.sum" => "🔮 Global karma's rate: ",
"user.stat.place" => "📊 Rating: ",
"user.stat.membership" => "👥 Has karma points in: ",
"user.stat.cookies" => "🍪 Cookies: ",
"user.stat.rewards" => "🏅 Rewards: ",
"karma.top.title" => "<b>🏆Top list of Karma owners in the «:chatName»:</b>\r\n\r\n",
"karma.top.row" => ":username (:karma)\r\n",
"karma.top.firstrow" => "⭐:username (:karma)\r\n",
"karma.top.footer" => "<a href=':pathToSite?chat_id=:chatId'>Read more.</a> <i>(on Russian)</i>",
"karma.top.private" => "The Karma's top is available only in groups",
"karma.plus" => "<b>:from (:k1)</b> has given some karma points to <b>:to (:k2)</b>",
"karma.minus" => "<b>:from (:k1)</b> has taken some karma points from <b>:to (:k2)</b>",
"karma.yourself" => "Do not do it again, please!",
"karma.tooSmallKarma" => "You <b>can't</b> vote with the negative count of karma points",
"karma.tooFast" => "Not so fast. The group has a limit for user's messages.",
"karma.unknownUser" => "I don't know this user 🤷‍♀️ <i>(please, try to write '👍' via Reply)</i>",
"karma.unknownUser.kicked" => "This user is not in the group now 🤷‍♀️",
"karma.manualSet" => "User ':0' (id=:1) in group with id=:2 has got new karma points :3",
"reward.list" => "Rewards list of <b>:user</b>:\r\n:list\r\n",
"reward.listInGroup" => "Rewards list of <b>:user</b> in group «:chatName»:\r\n:list\r\n",
"reward.noRewards" => "You don't have rewards yet.",
"reward.new" => "👏 User <b>:user</b> has been awarded the achievement called «<a href=':path?user_id=:user_id'>:title</a>»",
"reward.type.karma.desc" => "The karma points in the group :0 exceeded :1",
"reward.type.karma1" => "Karmachanic",
"reward.type.karma2" => "Karmaniac",
"reward.type.karma3" => "Karmonster",
"store.title" => "<b>🛍Content store</b>\r\n<i>Here you can buy a bit of content using your karma points</i>\r\n\r\n<b>:user(:k)</b>, what do you want to buy?",
"store.switchoff" => "<b>🛍Content store</b> is disabled by group admin 🤷‍♂️",
"store.button.buy_tits" => "*CENSORED*",
"store.button.buy_butts" => "*CENSORED*",
"store.button.buy_cats" => "Kitty :price",
"store.button.buy_sharks" => "Sharks :price",
"store.button.buy_pandas" => "Panda :price",
"store.button.buy_koalas" => "Koala :price",
"store.button.buy_dogs" => "Doge :price",
"store.button.buy_meme" => "Meme :price",
"store.button.buy_bashorg" => "Bash.Im :price",
"store.button.buy_gif" => "GIF :price",
"store.button.buy_jokes" => "Joke :price",
"store.button.buy_jokes18" => "Joke (18+) :price",
"store.button.buy_zadolbali" => "Zadolba.li :price",
"store.button.buy_ideer" => "Podslushano :price",
"store.event.buy_tits" => "*CENSORED*",
"store.event.buy_butts" => "*CENSORED*",
"store.event.buy_cats" => "<b>:user(:k)</b> has bought a kitty. Isn't it cute? :)",
"store.event.buy_sharks" => "<b>:user(:k)</b> has bought a shark. Isn't it cute? :)",
"store.event.buy_pandas" => "<b>:user(:k)</b> has bought a panda. Isn't it cute? :)",
"store.event.buy_koalas" => "<b>:user(:k)</b> has bought a koala. Isn't it cute? :)",
"store.event.buy_dogs" => "<b>:user(:k)</b> has bought a doge. Isn't it cute? :)",
"store.event.buy_meme" => "<b>:user(:k)</b> has bought a meme. Isn't it funny? :)",
"store.event.buy_gif" => "<b>:user(:k)</b> has bought a GIF. Isn't it funny? :)",
"store.event.buy_bashorg" => "<b>:user(:k)</b> has bought quote from Bash.Im. Isn't it funny? :)",
"store.event.buy_jokes" => "<b>:user(:k)</b> has bought joke. Isn't it funny? :)",
"store.event.buy_jokes18" => "<b>:user(:k)</b> has bought joke (18+). Isn't it funny? :)",
"store.event.buy_zadolbali" => "<b>:user(:k)</b> has bought story from Zadolbali. Isn't it funny? :)",
"store.event.buy_ideer" => "<b>:user(:k)</b> has bought story from Podslushano. Isn't it funny? :)",
"store.callback" => "You have bought ':buy'. You have :k karma points.",
"store.event.cant_buy" => "<b>:user(:k)</b> hasn't enough karma points to buy <i>':buy'</i>",
"store.callback.cant_buy" => "You have not enough karma points to buy ':buy'",
"store.wrongPick" => "You can't pick this. This is for user ':user'",
"settings.titleGroup" => "<b>🛠Settings</b> \r\n<i>Access to changing is only for admins.</i>\r\n",
"settings.titlePrivate" => "<b>🛠Settings</b> \r\nAttention please! Some operations for groups enabled only for administrators. Also some functions enabled for cookies🍪. \r\nYou can add cookies with /donate command.\r\n",
"settings.adminonly" => "Access to changing is only for admins.",
"settings.title.cooldown" => "⏱Cooldown time: <b>:cooldown min.</b>",
"settings.title.lang" => "🗣Language is <b>:lang</b>",
"settings.title.growth" => "📈Growth is <b>:type</b>",
"settings.title.access" => "👥Karma available for <b>:type</b>",
"settings.title.silent_mode" => "🔔Silent-mode is <b>:status</b>",
"settings.title.showcase" => "🛍Content store is <b>:status</b>",
"settings.enabled" => "enabled",
"settings.disabled" => "disabled",
"settings.growth.ariphmetic" => "Arithmetic",
"settings.growth.geometric" => "Geometric",
"settings.access.for_admin" => "admin only",
"settings.access.for_everyone" => "everyone",
"settings.button.toggle_silent_mode" => "🔔Silent-mode",
"settings.button.set_another_growth" => "📈Switch karma's growth on \":type\"",
"settings.button.set_another_access" => "👥Set access for :type",
"settings.button.set_showcase" => "🛍:type content store",
"settings.button.lang" => "🗣Language",
"settings.button.set_cooldown" => "⏱Cooldown time",
"settings.enable" => "Enable",
"settings.disable" => "Disable",
"settings.minute" => " min.",
"settings.select.cooldown" => "<b>⏱Cooldown settings</b>\r\n\r\nSelect the cooldown time between user's votes ",
"settings.select.lang" => "<b>🗣Language settings</b>\r\n\r\nSelect the language of Axenia",
"settings.button.back" => "⬅️Back",
"settings.unfollow" => "🙅‍♂️ Erase own karma from group",
"settings.erase" => "💀 Erase all karma from group",
"settings.price" => " (:price🍪)",
"settings.erase.title" => "Choose group where you want erase data for all members. This operation just for group admins.\r\n\r\n <b>❗️Attention! This operation can't be canceled.</b>",
"settings.erase.notallow" => "You are not administrator in groups",
"settings.erase.confirm" => "‼️Are you sure that want erase karma in <b>\":chat\"</b>(:chat_id)?",
"settings.erase.success" => "Karma of members in <b>\":chat\"</b>(:chat_id) erased",
"settings.erase.cancel" => "Erased in <b>\":chat\"</b>(:chat_id) canceled",
"settings.groups.adminonly" => "<b>This operation just for group admins.</b>",
"settings.unfollow.confirm" => "‼️Are you sure that want erase karma from <b>\":chat\"</b>(:chat_id)?",
"settings.unfollow.success" => "Your karma from <b>\":chat\"</b>(:chat_id) erased",
"settings.unfollow.cancel" => "Erased from <b>\":chat\"</b>(:chat_id) canceled",
"settings.unfollow.title" => "Choose group where you want erase data about karma.\r\n\r\n <b>❗️Attention! This operation can't be canceled.</b>",
"settings.hidden.turnon" => "👻 Don't show membership",
"settings.hidden.turnoff" => "👻 Show membership",
"donate.title" => "Please donate for my development. In return, I will thank you with a special currency - a Cookie 🍪",
"donate.price" => ":k🍪 :r₽",
"donate.attention" => "By clicking on the payment button you agree with the terms and conditions available by the command /terms",
"donate.bill" => "Excellent choice😉  As a token of gratitude for the help in the project we will give you :nom cookies🍪",
"donate.pay" => "Pay bill",
"donate.check" => "Check bill",
"donate.notEnough" => "Not enough :count cookies 🍪. Press /donate for get it.",
"confirm.yes" => "Yes",
"confirm.no" => "No",
];