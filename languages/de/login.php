<?php
/**
* 个人中心语言包.整理后的键值定义规则，页面title以t_开头，菜单以m_开头，标签以l_开头，js信息提示以j_开头，php信息提示以p_开头
* language: de
* @Author : yuandd
* @Last Modify : 2013-07-04
* ps:请翻译带有 “//translate”注释的语言内容,并删除此标记
*/

//杂项
/* fujia 2013-07-05 */
$lang['require_login'] = 'Ungültige Zugriff. <br/>Sie können diese Operation nicht erledigen.Sie müssen einloggen. '; //非法访问路径
$lang['require_tips'] = 'Pflichtfelder';
$lang['l_back'] = 'Zurück';
$lang['l_save'] = 'Speichern';
$lang['l_edit'] = 'Bearbeiten';

$lang['warning']['remove_item']='Wollen Sie es sicher entfernen?';
$lang['confirm']['yes']='Ja';
$lang['confirm']['no']='Nicht';

//登录注册页面
$lang['t_login_register'] = 'Kunde anmelden oder neues Kundenkonto erstellen ';
$lang['l_login'] = 'Registrierte Kunden';
$lang['l_login_username'] = 'eMail-Adresse/ Nickname';
$lang['l_login_psw'] = 'Passwort';
$lang['l_login_forgotpsw'] = 'Passwort vergessen?';

$lang['l_register'] = 'Persönliche Informationen';
$lang['l_register_nickname'] = 'Nickname';
$lang['l_register_email'] = 'eMail-Adresse';
$lang['l_register_psw'] = 'Passwort';
$lang['l_register_confim'] = 'Passwort bestätigen';
$lang['l_verification_code'] = 'Prüfungskode';
$lang['l_register_agree'] = 'Ich stimme zu ' . ucfirst( COMMON_DOMAIN );
$lang['l_register_agree_sub'] = 'Abonnieren Sie auf unsere Newsletter um jede Wochen mit unseren Coupons Geld zu sparen';
$lang['l_register_terms'] = 'Allgemeine Geschäftsbedingungen';
$lang['l_register_tips'] = 'Nach der Registrierung werden Sie unsere Newsletter mit Informationen über Sales, Gutscheins und Sonderaktionen erhalten. Sie können in Mein Konto zurück abonnieren.';

$lang['p_captcha_invalid'] = 'Ungültige Eingabe, versuchen Sie noch bitte.';
$lang['p_username_please']='Geben bitte den Benutzername ein';
$lang['p_login_failure'] = 'Ungültiger Benutzername oder Passwort.'; //登录失败
$lang['p_register_fail'] = 'Registrieren fehlgeschlagen, bitte versuchen erneut.'; //注册失败
$lang['p_agreement'] = 'Sie sind mit der Zustimmung nicht einverstanden.'; //用户协议未勾选
$lang['p_username_shorter'] = 'Der Spitzname muss mindestens 3 Zeichen enthalten.';
$lang['p_password_shorter'] = 'Das Passwort muss mindestens 6 Zeichen enthalten.';
$lang['p_passwd_blank'] = 'Das eingegebene Passwort kann nicht leer sein.';
$lang['p_reset_password'] = 'Sie erhalten eine Email mit einem Link, um Ihre Passwort zu zurücksetzen.'; //邮箱找回密码信息发送提示
$lang['p_fail_send_password'] = 'Misserfolg, kontaktieren Sie bitte mit Administrator!'; //发送重设密码邮件失败
$lang['p_parm_error'] = 'Fehler, zurück bitte!'; //重设密码时，错误的用户名或code
$lang['p_reset_psw_success'] = 'Zurücksetzen Passwort erfolgreich.'; //重设密码成功

//注册页面和会员中心js语言包
$lang['username_exist'] = 'Dieser Kunde-Spitzname ist schon vorhanden.';
$lang['email_exist'] = 'Es gibt bereits ein Konto mit dieser Emailadresse.';

$lang['required']='Dies ist ein Pflichtfeld.';
$lang['username_shorter'] = 'Bitte geben Sie 3 oder mehr Zeichen ein. Führende oder nachfolgende Leerzeichen werden ignoriert.';
$lang['username_invalid'] = 'Spitzname kann nur aus Buchstaben, Figur und Unterstreichen bestehen.';
$lang['password_shorter'] = 'Bitte geben Sie 6 oder mehr Zeichen ein. Führende oder nachfolgende Leerzeichen werden ignoriert.';
$lang['email_invalid'] = 'Bitte geben Sie eine gültige Emailadresse ein. Zum Beispiel johndoe@domain.com.';
$lang['confirm_password_invalid'] = 'Bitte stellen Sie sicher, dass Ihre Passwörter übereinstimmen.';
$lang['confirm_del_wl']='Sind Sie sicher, dass Sie dieses Produkt von Ihrer Wunschliste löschen?';

//退出登录页面
$lang['t_logout'] = 'Kunde abmelden'; //translate
$lang['l_logout_h1'] = 'Sie haben jetzt abgemeldet. ';
$lang['l_logout_tips'] = 'Sie haben sich erfolgreich abgemeldet und werden in <span id="timer">3</span> Sekunden zur Startseite weitergeleitet.';

$lang['l_order_h1'] = 'Meine Bestellungen'; //My Order
$lang['l_order_empty'] = 'Sie haben keine Bestellungen getätigt.';
$lang['l_page_items'] = 'Artikel';
$lang['l_page_to'] = 'bis';
$lang['l_page_of'] = 'von';
$lang['l_page_total'] = 'gesamt';
$lang['l_page_show'] = 'Schau';
$lang['l_page_per'] = 'pro Seite';
$lang['l_page_page'] = 'Seite';
$lang['l_page_next'] = 'Nächste';

$lang['l_paging_next'] = 'Weiter';
$lang['l_paging_previous'] = 'Vorherige Seite';

$lang['l_reviews_detail'] = 'Details ansehen'; //My Product reviews

$lang['l_tags_tips'] = 'Klicken Sie auf ein Schlagwort, um übereinstimmende Artikel zu finden.'; //My Tags
$lang['l_no_tag'] = 'Sie haben bisher noch keine Artikel mit Schlagworten versehen.';

$lang['l_news_gs'] = 'Allgemeines Abonnement'; //Newsletter Subscriptions

$lang['l_auction_h1'] = 'Meine Auktion'; //My Gifts/Auctions
$lang['l_auction_th_pn'] = 'Produktsname';
$lang['l_auction_th_rf'] = 'Einzelhandel für';
$lang['l_auction_th_yp'] = 'Ihr Preis';
$lang['l_auction_th_ed'] = 'Ablaufdatum';
$lang['l_auction_th_state'] = 'Staat';
$lang['l_auction_status_win'] = 'Gewinnen'; //竞拍当前状态
$lang['l_auction_status_lose'] = 'Verlieren';
$lang['l_auction_page_goto'] = 'Gehen zu';

$lang['p_register_success'] = 'Vielen Dank für Ihre Registrierung bei EachBuyer.';
$lang['p_user_edit_info_success'] = 'Die Benutzerkonto Information wurde gespeichert.';
$lang['p_user_add_newsletter_success'] = 'Die Anmeldung wurde gespeichert.';
$lang['p_user_cancel_newsletter_success'] = 'Die Anmeldung wurde gelöscht.';
$lang['p_user_current_pwd_fail']          = 'Aktuelles Passwort ungültig';
$lang['p_user_email_invalid']             = 'Diese Emailadresse ist ungültig.';
$lang['p_user_edit_address_success']      = 'Diese Emailadresse ist gespeichert.';
$lang['p_user_delete_address_success']    = 'Die Adresse wurde gelöscht.';
$lang['psw_updated'] ='Your password has been updated.'; //密码修改成功
$lang['reset_psw_success']= 'reset psw success'; //修改密码成功
$lang['empty_reviews'] = 'Sie haben noch keine Kundenmeinungen abgegeben.'; //会员中心，没有评论信息
$lang['empty_wishlist'] = 'Sie haben keine Artikel auf Ihrem Wunschzettel.'; //会员中心，没有收藏信息
$lang['empty_orders'] = 'Sie haben keine Bestellungen getätigt.';//会员中心，没有订单信息
$lang['update_wishlist_success']='Wunschliste ist gespeichert.';//更新收藏夹成功
$lang['update_wishlist_fail']='Aktualisieren Wunschzettel fehlgeschlagen.';//更新收藏夹失败
$lang['p_share_wl_success']='Ihre Wunschliste ist geteilt.'; //分享收藏夹成功
$lang['p_share_wl_fail']='Teilen Wunschliste fehlgeschlagen. Probieren nochmal bitte.'; //分享收藏夹失败

//低价竞拍语言部分
$lang['l_forget_psw_tips']='Wenn Sie bei uns ein Benutzerkonto besitzen, melden Sie sich bitte an.';
$lang['l_signup_news']='Newsletter anmelden um 10 Punkte &  €2.31  zu erhalten';
$lang['l_login']='anmelden';
$lang['l_rigster']='Registrieren';
$lang['l_login_t']='anmelden';
$lang['l_rigster_t']='Registrieren';
$lang['auction_title']='Verbringen Sie Ihre Punkte für Auktion am EachBuyer';
$lang['auction_keywords']='Eachbuyer Punkte, Auktion, einzigartig niedrigsten Auktion, Gebot, EInlösen, Game, gewinnen, verbringen kein Geld, kein Risiko';
$lang['auction_description']='Nutzen Sie Ihre Punkte einzulösen, bieten Gebot, Game. Sie brauchen nicht Ihr Geld ausgeben. Kein Risiko.';
$lang['l_auction_home']='Home';//translate
$lang['l_auction_livechart']= 'Live Chat';//translate
$lang['l_auction_welcome']= 'Willkommen auf EachBuyer !';
$lang['l_auction_rules']='Regeln';
$lang['l_auction_more']= 'mehr';
$lang['l_auction_copy']= '<span>' . ucfirst( COMMON_DOMAIN ) . '</span>. Alle Rechte vorbehalten';
$lang['l_auction_ongoing']= 'Läuft noch';
$lang['l_auction_history']= 'Verlauf';
$lang['l_auction_upcoming']= 'Bevorstehend';
$lang['l_auction_shopping']= 'Einkaufen um Punkte zu verdienen';
$lang['l_auction_retails']= 'Ladenpreis';
$lang['l_auction_page_first']= 'Erste';
$lang['l_auction_page_last']= 'Letzte';
$lang['l_auction_ended']= 'Beendet';
$lang['l_auction_win']= 'Siegers Preis';
$lang['l_auction_won']= 'gewonnen';
$lang['l_auction_totalbid']= 'Bieter insgesamt';
$lang['l_auction_bid']='bieten';
$lang['l_auction_start']='starten';
$lang['l_auction_end']='Ende';
$lang['l_auction_result']='Auktionsergebnisse';
$lang['l_auction_wonitfor']='gewann es für';
$lang['l_auction_logout']='Abmeldung';
//auction详情页
$lang['l_auction_sku']='SKU';//translate
$lang['l_auction_timeleft']='Verbleibende zeit';
$lang['l_auction_lbp']='Letzter Gebotspreis';
$lang['l_auction_lb']='Letzter Bieter';
$lang['l_auction_yp']='Ihr Preis';
$lang['l_auction_ys_left']='Sie haben für diese Auktion';
$lang['l_auction_ys_right']='Punkte ausgegeben';
$lang['l_auction_ys_pd']='Artikelbeschreibung';
$lang['l_auction_ys_ybh']='Ihr Gebotsverlauf';
$lang['l_auction_login']='Bitte loggen Sie sich ein';
$lang['l_auction_invalid']='Ungültige Gebot'; //非法竞拍
$lang['l_auction_over']='Entschuldigung, die Auktion ist beendet.'; //竞拍已结束
$lang['l_auction_min_unique']='Congratulations, this is the current lowest unique bid. As a convenience to you, we\'ll send you an email if someone else\'s bid matches yours making it no longer unique.'; //出价唯一且最低//translate
$lang['l_auction_min_price_out']='Nicely done! You\'ve just ruined someone\'s chance of winning by matching their current lowest, unique bid.Now make another bid to win the auction!'; //挤掉了之前的最低价格 //translate
$lang['l_auction_nomin_unique']='Congratulations, your bid is definitely unique, but just a little bit higher than the current winning bid.You\'re definitely close, so make another bid!'; //translate
$lang['l_auction_nomin_nounique']='Sorry, this bid is not unique. Please make another bid.'; //translate
$lang['l_auction_price_exist']='You have already bid this price. Please choose another bid.'; //已经出过价了//translate
$lang['l_auction_pi']='Your points is less than 10. Need more points? Purchase from our thousands of products. Write reviews of our products.
';  //积分不够了//translate
$lang['l_auction_dpf']='Sorry,deduct points fail.';
$lang['l_auction_chart_title']='Bids Distribution Graph';//统计图
$lang['l_auction_chart_y']='Gebote';
//竞拍详情历史页面
$lang['l_auction_wb']='Siegerpreis';
$lang['l_auction_winer']='Gewinner';
$lang['l_auction_st']='Startzeit';
$lang['l_auction_ct']='Endzeit';
$lang['l_auction_cabd']='Alle Bietdetails prüfen';
$lang['l_auction_bdg']='Gebote Aufteilung Graph';
$lang['l_start_left_time']='Stunden bis diese Auktion beginnt.';//translate
$lang['l_auction_search']='Suche';
$lang['l_auction_search_tips']='Preis Intervall Suchen';
$lang['l_auction_show']='zeigen';
$lang['l_auction_price']='Preis';
$lang['l_auction_nickname']='Spitznamen';
$lang['l_auction_loading']='loading...';
//竞拍介绍
$lang['l_auction_01_q']='01.Um welche Art von Auktion handelt es sich?';
$lang['l_auction_01_a']='Im Gegensatz zu herkömmlichen, bei denen der H?chstbietende gewinnt, wird diese Auktion Niedrigste Einzelgebotsauktion genannt. ';
$lang['l_auction_02_q']='02.Wie kann ich gewinnen?';
$lang['l_auction_02_a']='Der Bieter mit dem niedrigsten Einzelgebot gewinnt. Mit niedrigstem Einzelgebot meinen wir, dass Sie dann gewinnen, wenn niemand sonst, den Betrag den Sie geboten haben bietet und dieses Gebot das niedrigste ist. <br>  Und in dem seltenen Fall, in dem es kein geringstes Einzelgebot gibt, schauen wir nach dem niedrigsten Gebot mit den wenigsten Bietern und die Person, die diesen Betrag als erstes geboten hat, ist der Gewinner!';
$lang['l_auction_03_q']='03.Wie kann ich mitmachen? ';
$lang['l_auction_03_a']='Loggen Sie sich einfach in Ihren Account ein, geben Sie ein Gebot in die Gebotsbox ein und best?tigen Sie es. Danach haben Sie die Chance, das angebotene Objekt zu gewinnen. ';
$lang['l_auction_04_q']='04.Wieviel kostet das Bieten?';
$lang['l_auction_04_a']='Jedes Gebot kostet nur 10 Verdienstpunkte.';
$lang['l_auction_05_q']='05.Wie bekomme ich Verdienstpunkte?';
$lang['l_auction_05_a']='Für jeden Dollar, den Sie auf ' . ucfirst( COMMON_DOMAIN ) . ' ausgeben, bekommen Sie einen Verdienstpunkt. Für jeden Beitrag, den Sie schreiben und der von uns genehmigt wird, erhalten Sie fünf Verdienstpunkte.';
$lang['l_auction_06_q']='06.Kann ich mehr als ein Gebot abgeben?';
$lang['l_auction_06_a']='Sie können soviele Gebote abgeben, wie Sie sich mit Ihren Verdienstpunkten leisten können. ';
$lang['l_auction_07_q']='07.Woher wei? ich, dass Ihre Auktionen nicht vorher abgekartet sind?';
$lang['l_auction_07_a']='Nach dem Ende der Auktion ver?ffentlichen wir alle abgegebenen Gebote der Teilnehmer. Die Gebote werden mit den Kontonamen der Teilnehmer ver?ffentlicht. Daher können Sie sehen, was Sie und Ihre Freunde geboten haben.';
$lang['l_auction_08_q']='08.Was bringt mir das Bieten, wenn ich wahrscheinlich eh verlieren werde?';
$lang['l_auction_08_a']='Wenn Ihr Gebot nicht gewinnt, können Sie immer noch Ihre Verdienstpunkte für den Auktionsgegenstand zu unseren t?glich niedrigen Preisen ausgeben. Dieser Vorteil besteht für 72 Stunden nach Ende der Auktion.';
$lang['l_auction_09_q']='09.Was sollte ich zu dieser Auktion sonst noch wissen?';
$lang['l_auction_09_a']='Wenn Sie ein Gebot geben, werden Sie wissen, ob Ihr Gebot derzeit das Niedrigste ist, ob es derzeit einzigartig ist, ob es derzeit das Niedrigste und einzigartig ist oder ob es weder das Niedrigste noch einzigartig ist. Daher erfordert das Bieten strategisches Geschick.';
$lang['l_auction_10_q']='10.Was passiert nach dem Ende der Auktion?';
$lang['l_auction_10_a']='Bekommen Sie eine E-Mail in der Sie benachrichtigt werden, ob Sie gewonnen haben oder nicht.';

$lang['t_meta_keywords']='{$goods_name} - Zeitbegrenzte Sonderangebote | ' . ucfirst( COMMON_DOMAIN );
$lang['t_meta_desc']='Kaufen Sie günstige Ware mit hohe Qualität {$goods_name} bei ' . ucfirst( COMMON_DOMAIN ) . ', Zeitbegrenzte Sonderangebote!';

//邮件订阅模块语言包
$lang['news_title']='Newsletter abonnieren';
$lang['news_sub_title']=' Wählen Sie die Kategorien aus, und stellen Sie sicher, dass Sie nur die Newsletter bekommen möchten, woran Sie Interesse haben:';
$lang['chg_news_title']='Bearbeiten Abonnement';
$lang['chg_news_sub_title']=' Ändern Sie die Kategorien, um verschiedene Angebote zu erhalten, die Sie gerne möchten:';
$lang['fashions']='Fashions';
$lang['home_and_garden']='Home & Garten';
$lang['electronics']='Elektronik';
$lang['sub_confirm']='Abonnieren und erhalten $3';
$lang['info1']='Newsletters werden sich einmal oder zweimal pro Woche senden. Und alle von Ihnen abonnierten Angebote werden in einem Newsletter enthalten ';
$lang['info2']='Holen Sie sich <span>$3</span> Kredit auf alle Bestellungen';
$lang['info3']=' Große Rabatte nur für Abonnenten ';
$lang['info4']=' Exklusive Kampagnen für Abonnenten, um große Preise zu gewinnen ';
$lang['info5']='Sein der erste Person, die neuesten Nachrichten aus ' . ucfirst( COMMON_DOMAIN ) . ' zu bekommen';
$lang['info6']='Produktempfehlung, um Ihre Zeit des Suchens zu verkürzen';
$lang['info7']= ucfirst( COMMON_DOMAIN ). '  verpflichtet sich zum Schutz Ihrer Privatsphäre. Ihre Emailadresse wird niemals an Dritte aus irgendeinem Grund verkauft. Sehen Sie unsere Datenschutzerklärung.';

$lang['email_last_step_title']='Nur noch ein Schritt:';
$lang['sub_success_title']='Abonnement erfolgreich';
$lang['sub_success_info']='<strong>Gutscheincode: <span style="color:#f00;">%s</span></strong><br /><strong>
$$3 für Bestellungen ab $30, gültig bis 31.12.2013.</strong><br /> Gutschein Details sind schon an Ihre Email gesendet.<br />Vielen Dank für Ihr Newsletter-Abonnement bei ' . ucfirst( COMMON_DOMAIN ) . '! <br />Die Rabatte und Deals über <span style="color:#0083d6">%s</span> werden Ihnen regelmäßig imformiert an :%s';
$lang['no_category']='Subscribe failed! NO category had been selected.';
$lang['back_home']='Zurück ' . ucfirst( COMMON_DOMAIN );

$lang['update_success']='Update erfolgreich! ';
$lang['update_success_info']='Wir werden Ihnen die entsprechenden Newsletter nach Ihren neuen Kategorien senden.';

$lang['unsub_title']=' Geben Sie einfach Ihre Eamiladresse ein, um den Newsletter abzubestellen, wenn Sie keine Updates von ' . ucfirst( COMMON_DOMAIN ) . ' erhalten möchten: ';
$lang['unsub_info']='Sie werden keine Gutscheine oder exklusive Angebote (nur für Abonnenten mit dieser Option) mehr erhalten. Wir hoffen, dass Sie Ihre Kategorien ändern können, um verschiedenen Deals zu probieren, bevor Sie abbestellen.';
$lang['unsub_button']='abbestellen';

$lang['unsub_success']='Sie haben EachBuyer Newsletter erfolgreich abbestellt!';
$lang['unsub_success_info']='Wir hoffen, Sie können das Einkaufen bei ' . ucfirst( COMMON_DOMAIN ) . ' genießen. Vielen Dank!';

$lang['user_sub_title']='Sie abonnieren Kategorien:';
$lang['sub_msg']=' Bitte wählen Sie mindesten eine Kategorie aus, die Ihnen gefällt.';
$lang['sub_email_exist']='Vielen Dank für Ihre Abonnement, aber Ihre Emailadresse ist bereits auf unserer Newsletter-Liste. <br />Wenn Sie Ihre interessierten Kategorien ändern möchten, melden Sie bitte an, um <a href="%s"  style="color: #09318B;">Ihrem Konto</a> zu ändern oder Ihr Abonnement zu bearbeiten. <a href="%s"  style="color: #09318B;">hier</a>.<br /> Bitte speichern Ihr $3 Gutschein für Newsletter-Abonnement:<br /><br />';
$lang['check_mail_success']='Eine Bestätigungsemail wurde abgeschickt, um die Anforderung des Abonnements zu verifizieren. Bitte gehen Sie auf Ihre Mailbox und folgen Sie den Anweisungen in der Email, um den letzten Schritt zu beenden.';
$lang['sub_success_info_short']='<strong>Gutscheincode: <span style="color:#f00;">%s</span></strong><br /><strong>
$$3 für Bestellungen ab $30, gültig bis 31.12.2013.</strong>';
$lang['sub_auction_reg_info']='Bitte gehen Sie zu Ihr Mailbox, um Abonnement zu verifizieren und $3 sofort zu bekommen!';

//注册成功页。
$lang['regOk']['regOk']='Sie haben sich erfolgreich registriert.';
$lang['regOk']['congra']='Glückwünsche!';
$lang['regOk']['sucMsg']='Sie haben sich erfolgreich registriert.';
$lang['regOk']['regOkMsg']='Nach <span><em id="time">6</em></span> Sekunden werden zu der kürzlich angezeigten Seite zurückkehren. . . Oder <a href="/">besuchen Sie die Startseite</a>.';

//facebook login
$lang['facebook']['title'] = 'Ihre  Facebook e-Mail ist bereits bei EachBuyer registriert';
$lang['facebook']['input_pwd'] = 'Geben Sie bitte Ihr Login-Passwort bei EachBuyer.';
$lang['facebook']['other_email'] = 'Wählen Sie eine andere e-Mail-login';
$lang['facebook']['error_pwd'] =  'Ihre Passwort und E-Mail Adresse stimmen nicht überein.';
$lang['facebook']['error_have_email'] = 'Es gibt bereits ein Konto mit dieser e-Mail-Adresse';
$lang['facebook']['error_email'] = 'Bitte geben Sie eine gültige Email Adresse';
$lang['facebook']['l_reset_psw_sub'] = 'Absenden';
$lang['facebook']['l_reset_psw_email'] = 'eMail-Adresse';

$lang['bbs_error_email_address2'] = 'Bitte geben Sie eine gültige E-Mail-Adresse ein. Zum Beispiel johndoe@domain.com.';