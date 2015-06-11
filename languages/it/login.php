<?php
/**
* 个人中心语言包.整理后的键值定义规则，页面title以t_开头，菜单以m_开头，标签以l_开头，js信息提示以j_开头，php信息提示以p_开头
* language: it
* @Author : yuandd
* @Last Modify : 2013-07-04
* ps:请翻译带有 “//translate”注释的语言内容，并将此注释删除掉
*/

//杂项
/* fujia 2013-07-09 */
$lang['require_login'] = 'Entrata rifiutata.<br/> Non si può continuare l\'operazione fino a accedersi.'; //非法访问路径//
$lang['require_tips'] = 'Campo obbligatorio';
$lang['l_back'] = 'Indietro';
$lang['l_save'] = 'Salva';
$lang['l_edit'] = 'Modifica';

$lang['warning']['remove_item']='Sei sicuro di rimuoverlo?';
$lang['confirm']['yes']='Si\'';
$lang['confirm']['no']='No';

//登录注册页面
$lang['t_login_register'] = 'Accedi o crea un nuovo conto';
$lang['l_login'] = 'Clienti registrati';
$lang['l_login_username'] = 'Indirizzo email/ Nickname';
$lang['l_login_psw'] = 'Password';
$lang['l_login_forgotpsw'] = 'Password dimenticata?';

$lang['l_register'] = 'Informazioni personali';
$lang['l_register_nickname'] = 'Nickname';
$lang['l_register_email'] = 'Indirizzo email';
$lang['l_register_psw'] = 'Password';
$lang['l_register_confim'] = 'Conferma password';
$lang['l_verification_code'] = 'Codice di verifica';
$lang['l_register_agree'] = 'Accetti a ' . ucfirst( COMMON_DOMAIN );
$lang['l_register_agree_sub'] = 'Abbonarsi per risparmiare i soldi con i couponsu Newsletter di ogni settimana';
$lang['l_register_terms'] = 'Termini e condizioni';
$lang['l_register_tips'] = 'Dopo aver iscritto, riceverà le newsletter riguardo alle informazioni delle offerte, coupon e promozioni speciali. Può anche cancellarlo nel mio account.';

$lang['p_captcha_invalid'] = 'Ingresso valido, si prega di riprovare.';
$lang['p_login_failure'] = 'Invalida login o password.'; //登录失败
$lang['p_register_fail'] = 'Registrato fallito, riprovi per favore'; //注册失败
$lang['p_agreement'] = 'Non si accetta il contratto'; //用户协议未勾选
$lang['p_username_shorter'] = 'Il soprannome deve avere almeno 3 carateristiche.';
$lang['p_password_shorter'] = 'Il password deve avere almeno 6 carateristiche';
$lang['p_passwd_blank'] = 'Il password inserito non può essere vuoto.';
$lang['p_reset_password'] = 'Riceverà una mail con un link per reimpostare il password.'; //邮箱找回密码信息发送提示
$lang['p_fail_send_password'] = 'Fallimento, si prega di contattare l\'amministratore'; //发送重设密码邮件失败
$lang['p_parm_error'] = 'Errore, ritornare per favore!'; //重设密码时，错误的用户名或code
$lang['p_reset_psw_success'] = 'Il password è risettato.'; //重设密码成功

//注册页面和会员中心js语言包
$lang['username_exist'] = 'Questo soprannome di utente esiste già';
$lang['email_exist'] = 'C\'è già un account con questo indirizzo di email.';

$lang['required']='Questo è un campo obbligatorio.';
$lang['username_shorter'] = 'Si prega di inserire almeno 3 carateristiche. Spazi iniziali o finali vengono ignorati.';
$lang['username_invalid'] = 'Nickname solo può essere composta da lettere , figure e sottolinea.';
$lang['password_shorter'] = 'Si prega di inserire almeno 6 carateristiche. Spazi iniziali o finali vengono ignorati.';
$lang['email_invalid'] = 'Inserisca un indirizzo valido. Per esempio johndoe@domain.com.';
$lang['confirm_password_invalid'] = 'Si assicuri che il password corrisponde per favore.';
$lang['confirm_del_wl']='E\' sicuro di voler rimuovere il prodotto dalla tua lista dei desideri ?';
//退出登录页面
$lang['t_logout'] = 'Logout';
$lang['l_logout_h1'] = 'Sei ora scollegato';
$lang['l_logout_tips'] = 'Già logout e tornerai alla pagina iniziale in <span id="timer">3</span> secondi.';

$lang['l_order_h1'] = 'I miei ordini'; //My Order
$lang['l_order_empty'] = 'Non hai effettuato un ordine.';
$lang['l_page_items'] = 'Oggetti';
$lang['l_page_to'] = 'a';
$lang['l_page_of'] = 'di';
$lang['l_page_total'] = 'totali';
$lang['l_page_show'] = 'Mostrare';
$lang['l_page_per'] = 'per pagina';
$lang['l_page_page'] = 'Pagina';
$lang['l_page_next'] = 'Prossimo';

$lang['l_paging_next'] = 'Avanti';
$lang['l_paging_previous'] = 'Precedente';

$lang['l_reviews_detail'] = 'Vedi i Dettagli'; //My Product reviews

$lang['l_tags_tips'] = 'Clicca sul tag per vedere i prodotti corrispondeti'; //My Tags
$lang['l_no_tag'] = 'Non ha ancora tag alcun prodotto.';//translate

$lang['l_news_gs'] = 'Generale iscrizione'; //Newsletter Subscriptions

$lang['l_auction_h1'] = 'La Mia Asta'; //My Gifts/Auctions
$lang['l_auction_th_pn'] = 'Nome di Prodotto';
$lang['l_auction_th_rf'] = 'Venduto al detagglio';
$lang['l_auction_th_yp'] = 'I Tuoi Prezzi';
$lang['l_auction_th_ed'] = 'Giorno di scadenza';
$lang['l_auction_th_state'] = 'Stato';
$lang['l_auction_status_win'] = 'vince'; //竞拍当前状态
$lang['l_auction_status_lose'] = 'PERDERE';
$lang['l_auction_page_goto'] = 'andare';

$lang['p_register_success'] = 'Grazie mille per averti iscritto a EachBuyer.';
$lang['p_user_edit_info_success'] = 'Le informazioni del conto sono state conservate. ';
$lang['p_user_add_newsletter_success'] = 'L\'abbonamento è stato conservato.';
$lang['p_user_cancel_newsletter_success'] = 'L\'abbonamento è stato rimosso.';
$lang['p_user_current_pwd_fail']          = 'Password corrente non valida';
$lang['p_user_email_invalid']             = 'L\'indirizzo email non è valido.';
$lang['p_user_edit_address_success']      = 'L\'indirizzo è stato conservato.';
$lang['p_user_delete_address_success']    = 'L\'indirizzo è stato cancellato.';

$lang['psw_updated'] ='Your password has been updated.'; //密码修改成功
$lang['reset_psw_success']= 'reset psw success'; //修改密码成功

$lang['empty_reviews'] = 'Non ha inviato nessuna recensione.'; //会员中心，没有评论信息
$lang['empty_wishlist'] = 'Non ha ancora niente oggetti nella lista desideri.'; //会员中心，没有收藏信息
$lang['empty_orders'] = 'Non hai importato nessun ordine.';//会员中心，没有订单信息
$lang['empty_points'] = 'Non hai ancora nessuna storia punti.';
$lang['update_wishlist_success']='La lista degli articoli desideri è stata conservata. ';//更新收藏夹成功
$lang['update_wishlist_fail']='L\'aggiornamento della lista degli articoli desideri non ha successo.';//更新收藏夹失败
$lang['p_share_wl_success']='La tua lista degli articoli desideri è stata condivisa. '; //分享收藏夹成功
$lang['p_share_wl_fail']='Non riesce di condividere la lista degli articoli desideri. Si prega di riprovare. '; //分享收藏夹失败

//低价竞拍语言部分
$lang['l_forget_psw_tips']='Se ha un account con noi, accedi';
$lang['l_signup_news']=' Iscriversi alla newsletter per ottenere 10 punti & €2.31.';
$lang['l_login']='Accedi';
$lang['l_rigster']='Registrati';
$lang['l_login_t']='Accedi';
$lang['l_rigster_t']='Registrati';
$lang['auction_title']='Spendere i tuoi punti per il d\'asta A EachBuyer';
$lang['auction_keywords']='punti eachbuyer, asta, aste più bassa unica, offerta, riscattare, gioco, vincere, spendono senza soldi, senza rischi';
$lang['auction_description']='Usa i tuoi punti per riscattare, le offerte all\'asta, gioco. Non c\'è bisogno di spendere il vostro denaro. Nessun rischio.';
$lang['l_auction_home']='Home';
$lang['l_auction_livechart']= 'Live Chat';
$lang['l_auction_welcome']= 'Benvenuti a Eachbuyer!';
$lang['l_auction_rules']='Regole';
$lang['l_auction_more']= 'di più';
$lang['l_auction_copy']= '<span>' . ucfirst( COMMON_DOMAIN ) . '</span>.Tutti diritti riservati';
$lang['l_auction_ongoing']= 'In corso';
$lang['l_auction_history']= 'Storico';
$lang['l_auction_upcoming']= 'Prossimo';
$lang['l_auction_shopping']= 'Shopping per guadagnare punti';
$lang['l_auction_retails']= 'Prezzo al Dettaglio';
$lang['l_auction_page_first']= 'Il primo';
$lang['l_auction_page_last']= 'L\'ultimo';
$lang['l_auction_ended']= 'finito';
$lang['l_auction_win']= 'Prezzo del Vincitore';
$lang['l_auction_won']= 'vinta';
$lang['l_auction_totalbid']= 'Partecipanti Totali';
$lang['l_auction_bid']='Offerta';
$lang['l_auction_start']='inizio';
$lang['l_auction_end']='fine';
$lang['l_auction_result']='Risultati Asta';
$lang['l_auction_wonitfor']='l\'ha vinto per';
$lang['l_auction_logout']='il logout';
//auction详情页
$lang['l_auction_sku']='SKU';//translate
$lang['l_auction_timeleft']='tempo rimasto';
$lang['l_auction_lbp']='Ultimo Prezzo Offerto';
$lang['l_auction_lb']='Ultimo Offerente';
$lang['l_auction_yp']='Il Tuo Prezzo';
$lang['l_auction_ys_left']='Hai speso';
$lang['l_auction_ys_right']='punti su quest’asta';
$lang['l_auction_ys_pd']='Descrizione del prodotto';
$lang['l_auction_ys_ybh']='Il Tuo Storico Offerte';
$lang['l_auction_login']='login per favore';
$lang['l_auction_invalid']='offerta invalida'; //非法竞拍
$lang['l_auction_over']='Mi scusi, l\'asta è finita'; //竞拍已结束
$lang['l_auction_min_unique']='Congratulations, this is the current lowest unique bid. As a convenience to you, we\'ll send you an email if someone else\'s bid matches yours making it no longer unique.'; //出价唯一且最低//translate
$lang['l_auction_min_price_out']='Nicely done! You\'ve just ruined someone\'s chance of winning by matching their current lowest, unique bid.Now make another bid to win the auction!'; //挤掉了之前的最低价格 //translate
$lang['l_auction_nomin_unique']='Congratulations, your bid is definitely unique, but just a little bit higher than the current winning bid.You\'re definitely close, so make another bid!';//translate
$lang['l_auction_nomin_nounique']='Sorry, this bid is not unique. Please make another bid.';//translate
$lang['l_auction_pi']='Your points is less than 10. Need more points? Purchase from our thousands of products. Write reviews of our products.
';  //积分不够了//translate
$lang['l_auction_price_exist']='You have already bid this price. Please choose another bid.'; //已经出过价了//translate
$lang['l_auction_dpf']='Mi scusi, detrarre punti falliscono.';
$lang['l_auction_chart_title']='Grafico di distribuzione di offerta';//统计图
$lang['l_auction_chart_y']='offerte';
//竞拍详情历史页面
$lang['l_auction_wb']='Prezzo del Vincitore';
$lang['l_auction_winer']='Vincitore';
$lang['l_auction_st']='Ora di Inizio';
$lang['l_auction_ct']='Ora di Conclusione';
$lang['l_auction_cabd']='Guarda Tutti i Dettagli delle Offerte';
$lang['l_auction_bdg']='Grafico di distribuzione di offerta';
$lang['l_start_left_time']='ore fino a quando inizia questa asta.';
$lang['l_auction_search']='Ricerca';
$lang['l_auction_search_tips']='Prezzo ricerca intervallo';
$lang['l_auction_show']='mostra';
$lang['l_auction_price']='Prezzo';
$lang['l_auction_nickname']='sopranno';
$lang['l_auction_loading']='loading...';
//竞拍介绍
$lang['l_auction_01_q']='01.Di che tipo di asta si tratta?';
$lang['l_auction_01_a']='A differenza delle tradizionali dove l’offerta più alta vince, questa si chiama asta ad offerta unica più bassa. ';
$lang['l_auction_02_q']='02.Come si vince? ';
$lang['l_auction_02_a']='Il partecipante con l’offerta unica più bassa vince. Con offerta unica più bassa intendiamo dire che vincete se la vostra offerta è unica nel senso che nessun altro offre lo stesso prezzo e che tale prezzo è il più basso offerto.<br>  E nei rari casi in cui non esista un pezzo unico di offerta più basso allora cerchiamo il prezzo più basso con il minor numero di offerenti e la persona che ha offerto tale prezzo per prima diventa automaticamente il vincitore!';
$lang['l_auction_03_q']='03.Come si gioca?';
$lang['l_auction_03_a']='Effettua semplicemente il login sul tuo account, inserisci un’offerta nel box offerta ed invia. Dopodichè avrai la possibilità di aggiudicarti l’oggetto in offerta. ';
$lang['l_auction_04_q']='04.Quanto costa piazzare un’offerta?';
$lang['l_auction_04_a']='Ogni offerta costa la ragionevole cifra di 10 punti ricompensa. ';
$lang['l_auction_05_q']='05.Come ottengo punti ricompensa?';
$lang['l_auction_05_a']='Per ogni Euro speso in acquisti su ' . ucfirst( COMMON_DOMAIN ) . ', si guadagna un punto ricompensa. Per ogni recensione che si scrive e approvato da noi, si guadagna cinque punti premio.';
$lang['l_auction_06_q']='06.Posso piazzare più di una sola offerta?';
$lang['l_auction_06_a']='Potete fare tante offerte quante ve ne consentano i vostri punti ricompensa.';
$lang['l_auction_07_q']='07.Come faccio a sapere che la vostra asta non è truccata?';
$lang['l_auction_07_a']='Dopo La conclusione dell’asta, pubblichiamo tutte le offerte effettuate dai partecipanti. Le offerte vengono elencate assieme al nickname dei partecipanti. Perciò sarete in grado di vedere le offerte vostre e quelle dei vostri amici.';
$lang['l_auction_08_q']='08.Che senso ha puntare se tanto probabilmente perderò? ';
$lang['l_auction_08_a']='Se la vostra puntata non avrà successo, potrete sempre utilizzare i punti ricompensa impiegati nell’acquisto dell’oggetto in asta secondo i nostri normali e bassi prezzi di tutti giorni. Questa possibilità scadrà a 72 ore dopo la conclusione dell’asta. ';
$lang['l_auction_09_q']='09.Che altro devo sapere riguardo a quest’asta?';
$lang['l_auction_09_a']='Dopo aver effettuato un\'offerta, vi verrà ditto se la vostra offerta sia al momento la più bassa, se sia al momento unica, se sia al momento sia la più bassa che l’unica, o se l’offerta non è nessuna delle due cose. Perciò piazzare offerte richiede una certa strategia.';
$lang['l_auction_10_q']='10.Cosa succede dopo la conclusione dell’asta?';
$lang['l_auction_10_a']='Riceverete una e-mail da noi che vi indicherà se avete vinto o no.';

$lang['t_meta_keywords']='{$goods_name} - Offerta per Tempo Limitato | ' . ucfirst( COMMON_DOMAIN );
$lang['t_meta_desc']='Compra economico e di alta qualità {$goods_name} su ' . ucfirst( COMMON_DOMAIN ) . ', Offerta per Tempo Limitato';

//邮件订阅模块语言包
$lang['news_title']='Iscrizione alla Newsletter';
$lang['news_sub_title']='Scegli le categorie ti piaciono e fa sicuro che riceverai solo le newsletter che ti interessa:';
$lang['chg_news_title']='Modifica l’abbonamento';
$lang['chg_news_sub_title']='Cambia le categorie per ricevere le offerte diverse che ti piaciono:';
$lang['fashions']='Moda';
$lang['home_and_garden']='Casa&Giardino';
$lang['electronics']='Elettronica';
$lang['sub_confirm']='Iscriviti e ricevi $3';
$lang['info1']='Le newsletter saranno inviato una o due volte alla settimana, tutte le offerte che hai iscritto saranno incluse in una newsletter.';
$lang['info2']='Ottieni $3 di credito su tutti gli ordini.';
$lang['info3']='Grandi sconti solo per gli partecipanti.';
$lang['info4']='Esclusiva attività per i partecipanti di vincere i grandi premi.';
$lang['info5']='Sempre il primo a sapere le ultime news di ' . ucfirst( COMMON_DOMAIN );
$lang['info6']='Prodotto consigliato per abbreviare il tuo tempo di ricerca';
$lang['info7']= ucfirst( COMMON_DOMAIN ) .' si impegna a proteggere la tua privacy. Il tuo indirizzo e-mail non saranno ceduti a terzi per alcun motivo. Vedere la nostra Politica sulla Privacy';

$lang['email_last_step_title']='Solo un altro passo:';
$lang['sub_success_title']='Iscritto con successo';
$lang['sub_success_info']='<strong>Codici sconto: <span style="color:#f00;">%s</span></strong><br /><strong>
$3 di sconto per gli ordini superiore a $30, valido fino al 31 dicembre, 2013.</strong><br />Il dettaglio del coupon è stato inviato al suo e-mail.<br />Grazie per iscrivere alla newsletter di ' . ucfirst( COMMON_DOMAIN ) . '! <br />Gli sconti e offerte su <span style="color:#0083d6">%s</span> saranno informati periodicamente a te via :%s';
$lang['no_category']='Subscribe failed! NO category had been selected.';
$lang['back_home']='Torna a ' . ucfirst( COMMON_DOMAIN );

$lang['update_success']='Aggiornato con successo!';
$lang['update_success_info']='Ti invieremo la relativa newsletter secondo le tue nuove categorie.';

$lang['unsub_title']='Inserisci il tuo indirizzo e-mail per annullare l\'iscrizione se non si desidera ricevere gli aggiornamenti da ' . ucfirst( COMMON_DOMAIN ) . ': ';
$lang['unsub_info']='Non sarà più possibile ricevere un coupon o offerte esclusiva solo per gli abbonati con questa opzione. Speriamo che si possa considerare di cambiare le categorie di provare diverse trattative prima della cancellazione.';
$lang['unsub_button']='Cancella';

$lang['unsub_success']='Hai cancellato la newsletter di  ' . ucfirst( COMMON_DOMAIN ) . '!';
$lang['unsub_success_info']='Speriamo che puoi godere lo shopping su ' . ucfirst( COMMON_DOMAIN ) . '! ';

$lang['user_sub_title']='Categorie di iscrizione:';
$lang['sub_msg']='Si prega di scegliere almeno una categoria che a lei piace.';
$lang['sub_email_exist']='Grazie per il suo abbonamento, però il suo indirizzo email è già nella nostra lista di newsletter.
Se vuole modificare le sue categorie interessate, si prega di andare al <a href="%s"  style="color: #09318B;">suo account</a> per cambiare il suo abbonamento o modificarla <a href="%s"  style="color: #09318B;">qui</a>.<br />Si prega di mantenere il suo $3 di coupon per iscrivere alla newsletter:<br /><br />';
$lang['check_mail_success']='Una email di conferma è stata inviata per verificare la richiesta di abbonamento. Vai alla casella di posta e seguire le istruzioni contenute nell\'e-mail per completare l\'ultimo passo.';
$lang['sub_success_info_short']='<strong>Coupon Code: <span style="color:#f00;">%s</span></strong><br /><strong>
3 $ les commandes de plus de 30 $, valable jusqu\'au 31 Décembre, 2013.</strong>';
$lang['sub_auction_reg_info']='Si prega di andare alla cassetta postale per verificare il suo abbonamento e ottenere $3 adesso!';

//注册成功页。
$lang['regOk']['regOk']='Iscritto con successo.';
$lang['regOk']['congra']='Complimenti!';
$lang['regOk']['sucMsg']='Iscritto con successo.';
$lang['regOk']['regOkMsg']='Dopo <span><em id="time">6</em></span> secondi torna alla pagina scorsa…O <a href="/">visita la pagina iniziale</a>.';

//facebook login
$lang['facebook']['title'] = 'La sua email di Facebook è già iscritto su Eachbuyer.';
$lang['facebook']['input_pwd'] = 'Inserisca la password dell’account di Eachbuyer';
$lang['facebook']['other_email'] = 'Accedi con un’altra email.';
$lang['facebook']['error_pwd'] =  'La password inserita non corrisponde alla e-mail.';
$lang['facebook']['error_have_email'] = 'C’è già un account con questo indirizzo email.';
$lang['facebook']['error_email'] = "Si prega di inserire una E-mail valida.";
$lang['facebook']['l_reset_psw_sub'] = 'Invia';
$lang['facebook']['l_reset_psw_email'] = 'Indirizzo email';


$lang['bbs_error_email_address2'] = 'Si prega di inserire un indirizzo email valido. Per esempio johndoe@domain.com.';