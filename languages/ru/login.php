<?php
/**
* 个人中心语言包.整理后的键值定义规则，页面title以t_开头，菜单以m_开头，标签以l_开头，js信息提示以j_开头，php信息提示以p_开头
* language: ru
* @Author : yuandd
* @Last Modify : 2013-07-04
* ps:请翻译带有 “//translate”注释的语言内容，并将此注释删除掉
*/

//杂项
/* fujia 2013-07-09 */
$lang['require_login'] = 'Ошибка входа. <br/> Вы не можете продолжить без регистрации'; //非法访问路径
$lang['require_tips'] = 'Обязательные поля';
$lang['l_back'] = 'Вернуться';
$lang['l_save'] = 'Сохранить';
$lang['l_edit'] = 'Редактировать';

$lang['warning']['remove_item']='Вы уверены, что хотите удалить?';
$lang['confirm']['yes']='Да';
$lang['confirm']['no']='нет';

//登录注册页面
$lang['t_login_register'] = 'Войти или зарегистрироваться';
$lang['l_login'] = 'Зарегистрированный пользователь';
$lang['l_login_username'] = 'Email адрес/ Псевдоним пользователя';
$lang['l_login_psw'] = 'Пароль';
$lang['l_login_forgotpsw'] = 'Забыли пароль?';

$lang['l_register'] = 'Личная информация';
$lang['l_register_nickname'] = 'Псевдоним пользователя';
$lang['l_register_email'] = 'Email адрес';
$lang['l_register_psw'] = 'Пароль';
$lang['l_register_confim'] = 'Подтвердите пароль';
$lang['l_verification_code'] = 'Проверочный код';
$lang['l_register_agree'] = 'Я принимаю Общие положения и условия ' . ucfirst( COMMON_DOMAIN );
$lang['l_register_agree_sub'] = 'Подпишитесь, чтобы сэкономить с помощью купонов каждую неделю';
$lang['l_register_terms'] = 'Условия пользования.';
$lang['l_register_tips'] = 'После регистрации, Вы будете получать рассылку с информацией о продажах, купонах и специальных акциях. Отменить подписку можно в Личном кабинете.';

$lang['p_captcha_invalid']='Неправильный ввод, пожалуйста, попробуйте еще раз';
$lang['p_login_failure'] = 'Неверный Логин или пароль.'; //登录失败
$lang['p_register_fail'] = 'Register failed,please try again.'; //注册失败
$lang['p_agreement'] = 'You do not agree with the agreement'; //用户协议未勾选
$lang['p_username_shorter'] = 'Прозвище должно быть не менее 3 символов.';
$lang['p_password_shorter'] = 'Пароль должен содержать не менее 6 символов.';
$lang['p_passwd_blank'] = 'The password entered can`t have blank.';
$lang['p_reset_password'] = 'You will receive an email with a link to reset your password.'; //邮箱找回密码信息发送提示
$lang['p_fail_send_password'] = 'Failure, please contact with administrator!'; //发送重设密码邮件失败
$lang['p_parm_error'] = 'Error, Please return!'; //重设密码时，错误的用户名或code
$lang['p_reset_psw_success'] = 'Reset password success.'; //重设密码成功

//注册页面和会员中心js语言包
$lang['username_exist'] = 'This customer Nickname already exists';
$lang['email_exist'] = 'Существует уже учетная запись с таким адресом электронной почты.';

$lang['required']='This is a required field.';
$lang['username_shorter'] = 'Please enter 3 or more characters. Leading or trailing spaces will be ignored.';
$lang['username_invalid'] = 'Nickname only can be composed of letters, figure and underline.';
$lang['password_shorter'] = 'Please enter 6 or more characters. Leading or trailing spaces will be ignored.';
$lang['email_invalid'] = 'Пожалуйста, введите верный адрес. Например johndoe@domain.com.';
$lang['confirm_password_invalid'] = 'Пожалуйста, убедитесь, что ваши пароли совпадают.';

//退出登录页面
$lang['t_logout'] = 'Выйти';
$lang['l_logout_h1'] = 'Вы вышли';
$lang['l_logout_tips'] = 'Вы вышли из системы и будете перенаправлены на главную страницу в течение <span id="timer">3</span> секунд.';

$lang['l_order_h1'] = 'Мой заказ'; //My Order
$lang['l_order_empty'] = 'Вы не разместили ни одного заказа.';
$lang['l_page_items'] = 'Позиции с';
$lang['l_page_to'] = 'no';
$lang['l_page_of'] = 'из';
$lang['l_page_total'] = '';
$lang['l_page_show'] = 'Показать';
$lang['l_page_per'] = 'на странице';
$lang['l_page_page'] = 'Страница';
$lang['l_page_next'] = 'Далее';

$lang['l_paging_next'] = 'Вперед';
$lang['l_paging_previous'] = 'Предыдущая страница';

$lang['l_reviews_detail'] = 'Просмотреть подробности'; //My Product reviews

$lang['l_tags_tips'] = 'Нажмите на метку для просмотра помеченных товаров.'; //My Tags
$lang['l_no_tag'] = 'Вы еще не добавили позиции.';

$lang['l_news_gs'] = 'Общая подписка'; //Newsletter Subscriptions

$lang['l_auction_h1'] = 'Мой аукцион'; //My Gifts/Auctions
$lang['l_auction_th_pn'] = 'Название продукта';
$lang['l_auction_th_rf'] = 'Розничные продажи для';
$lang['l_auction_th_yp'] = 'Ваша цена';
$lang['l_auction_th_ed'] = 'Дата истечения срока действия';
$lang['l_auction_th_state'] = 'Статус';
$lang['l_auction_status_win'] = 'WIN'; //竞拍当前状态 //translate
$lang['l_auction_status_lose'] = 'LOSE'; //translate
$lang['l_auction_page_goto'] = 'go to'; //translate

$lang['p_register_success'] = 'Спасибо за регистрацию с EachBuyer.';
$lang['p_user_edit_info_success'] = 'Информация о счете была сохранена.';
$lang['p_user_add_newsletter_success'] = 'Подписка была сохранена.';
$lang['p_user_cancel_newsletter_success'] = 'Подписка была удалена.';
$lang['p_user_current_pwd_fail']          = 'Неправильный текущий пароль';
$lang['p_user_email_invalid']             = 'Адрес электронной почты недействителен.';
$lang['p_user_edit_address_success']      = 'Адрес успешно сохранен.';
$lang['p_user_delete_address_success']    = 'Адрес был удален.';
/*end*/
$lang['psw_updated'] ='Your password has been updated.'; //密码修改成功
$lang['reset_psw_success']= 'reset psw success'; //修改密码成功
/*fujia*/
$lang['empty_reviews'] = 'Вы не оставляли отзывы о товарах на этом сайте.'; //会员中心，没有评论信息
$lang['empty_wishlist'] = 'В вашем листе пожеланий нет товаров.'; //会员中心，没有收藏信息
$lang['empty_orders'] = 'У вас пока нет оформленных заказов.';//会员中心，没有订单信息
$lang['empty_points'] = 'У Вас пока нет покупочных очков.';
$lang['update_wishlist_success']='Список желаний сохранен.';//更新收藏夹成功
$lang['update_wishlist_fail']='Не удалось обновить список желаний';//更新收藏夹失败
$lang['p_share_wl_success']='Вы успешно поделились списком желаний'; //分享收藏夹成功
$lang['p_share_wl_fail']='Поделиться Списком желаний не удалось, попробуйте еще ​​раз.'; //分享收藏夹失败

//低价竞拍语言部分
$lang['l_forget_psw_tips']='Если у Вас уже есть аккаунт, войдите';
$lang['l_signup_news']='Подпишитесь на рассылку, чтобы получить больше 10 баллов и 98.5 рублей';
$lang['l_login']='Войти';
$lang['l_rigster']='Регистрация';
$lang['l_login_t']='Войти';
$lang['l_rigster_t']='Регистрация';
$lang['auction_title']='Spend Your Points At EachBuyer Auction';//translate
$lang['auction_keywords']='eachbuyer points, auction, unique lowest auction, bid, redeem, game, win, spend no money, no risk';//translate
$lang['auction_description']='Use your points to redeem, bidding auction, game. You don\'t need to spend your money. No risk.';//translate
$lang['l_auction_home']='Главная';
$lang['l_auction_livechart']= 'Live Chat';//translate
$lang['l_auction_welcome']= 'Welcome to EachBuyer !';//translate
$lang['l_auction_rules']='Правила';
$lang['l_auction_more']= 'more';//translate
$lang['l_auction_copy']= ' <span>' . ucfirst( COMMON_DOMAIN ) . '</span>. All Rights Reserved';//translate
$lang['l_auction_ongoing']= 'Популярные';
$lang['l_auction_history']= 'История';
$lang['l_auction_upcoming']= 'Ожидаемые';
$lang['l_auction_shopping']= 'Купить на заработанные очки';
$lang['l_auction_retails']= 'Retails for';//translate
$lang['l_auction_page_first']= 'First';//translate
$lang['l_auction_page_last']= 'Last';//translate
$lang['l_auction_ended']= 'Ended';//translate
$lang['l_auction_win']= 'Winner’s Price';
$lang['l_auction_won']= 'won';//translate
$lang['l_auction_totalbid']= 'Total Bidders';//translate
$lang['l_auction_bid']='Bid';//translate
$lang['l_auction_start']='start';//translate
$lang['l_auction_end']='end';//translate
$lang['l_auction_result']='Результаты аукциона';
$lang['l_auction_wonitfor']='won it for';//translate
$lang['l_auction_logout']='logout';//translate
//auction详情页
$lang['l_auction_sku']='Артикул';
$lang['l_auction_timeleft']='Time Left';//translate
$lang['l_auction_lbp']='Last Bid Price';//translate
$lang['l_auction_lb']='Last Bidder';//translate
$lang['l_auction_yp']='Your Price';//translate
$lang['l_auction_ys_left']='You have spent';//translate
$lang['l_auction_ys_right']='points on this auction';//translate
$lang['l_auction_ys_pd']='Описание товара';
$lang['l_auction_ys_ybh']='Your Bid History';//translate
$lang['l_auction_login']='Please login';//translate
$lang['l_auction_invalid']='Invalid bid'; //非法竞拍//translate
$lang['l_auction_over']='I\'m sorry, the auction has ended.'; //竞拍已结束//translate
$lang['l_auction_min_unique']='Congratulations, this is the current lowest unique bid. As a convenience to you, we\'ll send you an email if someone else\'s bid matches yours making it no longer unique.'; //出价唯一且最低//translate
$lang['l_auction_min_price_out']='Nicely done! You\'ve just ruined someone\'s chance of winning by matching their current lowest, unique bid.Now make another bid to win the auction!'; //挤掉了之前的最低价格 //translate
$lang['l_auction_nomin_unique']='Congratulations, your bid is definitely unique, but just a little bit higher than the current winning bid.You\'re definitely close, so make another bid!'; //translate
$lang['l_auction_nomin_nounique']='Sorry, this bid is not unique. Please make another bid.'; //translate
$lang['l_auction_pi']='Your points is less than 10. Need more points? Purchase from our thousands of products. Write reviews of our products.
';  //积分不够了//translate
$lang['l_auction_price_exist']='You have already bid this price. Please choose another bid.'; //已经出过价了//translate
$lang['l_auction_dpf']='Sorry,deduct points fail.';//translate
$lang['l_auction_chart_title']='Bids Distribution Graph';//统计图//translate
$lang['l_auction_chart_y']='Bids';//translate
//竞拍详情历史页面
$lang['l_auction_wb']='Winning Bid';//translate
$lang['l_auction_winer']='Winner';//translate
$lang['l_auction_st']='Start Time';//translate
$lang['l_auction_ct']='Close Time';//translate
$lang['l_auction_cabd']='Check All Bid Details';//translate
$lang['l_auction_bdg']='Bids Distribution Graph';//translate
$lang['l_start_left_time']='hours until this auction begins.';//translate
$lang['l_auction_search']='Поиск';
$lang['l_auction_search_tips']='Price interval search';//translate
$lang['l_auction_show']='show';//translate
$lang['l_auction_price']=' Цена';
$lang['l_auction_nickname']='Nicknames';//translate
$lang['l_auction_loading']='loading...';//translate
//竞拍介绍
$lang['l_auction_01_q']='01.Какой вариант акциона Вы проводите?';
$lang['l_auction_01_a']='Не традицонный аукцион, где товар с самой высокой ставкой выигрывает. У нас все наоборот!';
$lang['l_auction_02_q']='02.Как я могу выиграть?';
$lang['l_auction_02_a']='Самая низкая ставка побеждает. Под самой низкой ставкой мы имеем ввиду, что Ваша предложенная цена уникальная и самая низкая.<br>  And in the rare instances where there is no lowest unique bid price then we look for the lowest price with the fewest bids and the person who bid first at that price is the winner!';//translate
$lang['l_auction_03_q']='03.Как происходит оплата товара?';
$lang['l_auction_03_a']='Войдите под своим именем, введите свою ставку в поле и подтвердите. После этого товар со ставкой может участвовать в аукционе.';
$lang['l_auction_04_q']='04.Стоимость предложения ставки?';
$lang['l_auction_04_a']='Each bid costs an affordable 10 reward points.';//translate
$lang['l_auction_05_q']='05.Как я могу получит призовые баллы?';
$lang['l_auction_05_a']='За каждый доллар, потраченный на ' . ucfirst( COMMON_DOMAIN ) . ' Вы можете получить призовые бонусы. За каждую рецензию, подтвержденную нами, Вы получаете 5 баллов.';
$lang['l_auction_06_q']='06.Как часто я могу участвовать в аукционе?';
$lang['l_auction_06_a']='Количество не ограничено.';
$lang['l_auction_07_q']='07.Как я узнаю не поддельный ли аукцион и ставки?';
$lang['l_auction_07_a']='После того, как аукцион закончился, мы выставляем имена наших пользователей и предложенные ими ставки на сайте.';
$lang['l_auction_08_q']='08.Какой смысл участвовать, если ты возможно проиграешь?';
$lang['l_auction_08_a']='Если Ваша ставка не выиграла, Вы все еще можете воспользоваться выигрышными очками во время аукциона, для совершения обычных покупок на сайте. Время действия выигрышных баллов 72 часа.';
$lang['l_auction_09_q']='09.Что еще я должен знать об акции?';
$lang['l_auction_09_a']='Когда Вы предлагаете ставку за товар, будет указано, самая ли низкая или же уникальная это цена.';
$lang['l_auction_10_q']='10.Что будет после того, как акция закончилась?';
$lang['l_auction_10_a']='Вам на почту будет отправлено письмо, с указанием выиграли Вы или нет. Также Вы можете проверить это, пройдя по «Мои аукцион/Подарки».';

$lang['t_meta_keywords']='{$goods_name} - Time Limited Special Offer | ' . ucfirst( COMMON_DOMAIN );
$lang['t_meta_desc']='Buy cheap and high quality {$goods_name} at ' . ucfirst( COMMON_DOMAIN ) . ', Time Limited Special Offer';

//邮件订阅模块语言包
$lang['news_title']='Подписка на новостную рассылку';
$lang['news_sub_title']='Выберите ту категорию, которая Вас интересует и Вы будете получать письма только по интересующей Вас теме:';
$lang['chg_news_title']='Редактировать Подписку';
$lang['chg_news_sub_title']='Выберите интересующую Вас категорию:';
$lang['fashions']='Мода';
$lang['home_and_garden']='Электроника';
$lang['electronics']=' Дом и Сад';
$lang['sub_confirm']='Подпишитесь и получите $3';
$lang['info1']='Рассылка будет выходить один или два раза в неделю, а все специальные предложения будут включены в один бюллетень. ';
$lang['info2']='Получите <span>$3</span> на любые заказы';
$lang['info3']='Большие скидки только для абонентов';
$lang['info4']='Эксклюзивные кампаний для абонентов с выигрышом призов';
$lang['info5']='Будьте всегда в курсе последних новостей ' . ucfirst( COMMON_DOMAIN );
$lang['info6']='Сократится время поиска товаров';
$lang['info7']= ucfirst( COMMON_DOMAIN ) . ' гарантирует Вам защиту Ваших личных данных. Ваш адрес электронной почты никогда не будет продан третьему лицу. См. нашу политику конфиденциальности.';

$lang['email_last_step_title']='Еще один шаг:';
$lang['sub_success_title']='Вы успешно подписались на нашу рассылку.';
$lang['sub_success_info']='<strong>Скидочный купон: <span style="color:#f00;">%s</span></strong><br /><strong>
Скидка 3$ на заказ свыше $30, действителен до 31 Декабря 2013.</strong><br />Информация о скидочном купоне была отправлена на Ваш адрес электронной почты.<br />Спасибо, что остаетесь с ' . ucfirst( COMMON_DOMAIN ) . '! <br />Письма со скидками <span style="color:#0083d6">%s</span> будут приходить на Ваш адрес электронной почты :%s';
$lang['no_category']='Subscribe failed! NO category had been selected.';
$lang['back_home']='Вернуться на  ' . ucfirst( COMMON_DOMAIN );

$lang['update_success']='Обновление готово!';
$lang['update_success_info']='Вы будете получать рассылку по интересующим Вас категориям.';

$lang['unsub_title']='Введите адрес электронной почты, чтобы отписаться, если Вы не хотите получать новости от ' . ucfirst( COMMON_DOMAIN ) . ': ';
$lang['unsub_info']='Вы больше не будете получать новостную рассылку от ' . ucfirst( COMMON_DOMAIN ) . '. Вы можете также выбрать только интересующую Вас категорию.';
$lang['unsub_button']='Отказаться';

$lang['unsub_success']='Вы отписались от новостной рассылки EachBuyer!';
$lang['unsub_success_info']='Покупайте с удовольствием на ' . ucfirst( COMMON_DOMAIN ) . '. Спасибо за поддержку. ';

$lang['user_sub_title']='Категории, на которые Вы подписаны:';
$lang['sub_msg']='Пожалуйста, выберите минимум одну категорию.';
$lang['sub_email_exist']='Спасибо за Вашу подписку, но Ваш адрес электронной почты уже есть в наших списках подписчиков. Если Вы хотите изменить категорию подписки, проидите <a href="%s"  style="color: #09318B;">в свой аккаунт</a> по ссылке <a href="%s"  style="color: #09318B;"> здесь</a>.<br /> А также Ваш купон на $3 за подписку :<br /><br />';
$lang['check_mail_success']='Письмо с подтверждением было отправлено на Ваш адрес. Проверьте Ваш почтовый ящик и завершите подписку.';
$lang['sub_success_info_short']='<strong>Скидочный купон: <span style="color:#f00;">%s</span></strong><br /><strong>
Скидка 3$ на заказ свыше $30, действителен до 31 Декабря 2013.</strong>';
$lang['sub_auction_reg_info']='Пожалуйста, зайдите в почту и проверьте подписку, а также получите $3!';

//注册成功页。
$lang['regOk']['regOk']='Регистрация завершена!';
$lang['regOk']['congra']='Поздравления!';
$lang['regOk']['sucMsg']='Регистрация завершена!';
$lang['regOk']['regOkMsg']='After<span> <em id="time">6</em></span> seconds will jump to just visit the page..or <a href="/">Visit Home</a>';
$lang['regOk']['regOkMsg']='Через <span><em id="time">6</em></span> секунд вы вернетесь на недавно просмотренную страницу...или <a href="/">на главную страницу</a>.';

//facebook login
$lang['facebook']['title'] = 'Вашаэлектроннаяпочта Facebook  ужезарегистрированана EachBuyer';
$lang['facebook']['input_pwd'] = 'ВведитепарольЛогин EachBuyer';
$lang['facebook']['other_email'] = 'Выберитедругойадресэлектроннойпочты';
$lang['facebook']['error_pwd'] =  'Вашпочтовыйящикипарольнесовпадают';
$lang['facebook']['error_have_email'] = 'Адресаэлектроннойпочтыужесуществуетвнашейучетнойзаписи';
$lang['facebook']['error_email'] = " Пожалуйста, введитедействительныйадресэлектроннойпочты";
$lang['facebook']['l_reset_psw_sub'] = 'Подтвердить';
$lang['facebook']['l_reset_psw_email'] = 'Email адрес';

$lang['bbs_error_email_address2'] = 'Пожалуйста, введите верный адрес. Например johndoe@domain.com.';