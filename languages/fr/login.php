<?php
/**
* 个人中心语言包.整理后的键值定义规则，页面title以t_开头，菜单以m_开头，标签以l_开头，js信息提示以j_开头，php信息提示以p_开头
* language: fr
* @Author : yuandd
* @Last Modify : 2013-07-04
* ps:请翻译带有 “//translate”注释的语言内容，并将此注释删除掉
*/
//杂项
/* fujia 2013-07-09 */
$lang['require_login'] = 'L\'entrée illégale. Vous ne pouvez pas finir les opérations avant la connexion.'; //非法访问路径
$lang['require_tips'] = 'Champs obligatoires';
$lang['l_back'] = 'Retour';
$lang['l_save'] = 'Sauvegarder';
$lang['l_edit'] = 'Éditer';

$lang['warning']['remove_item']='Are you sure you want to remove it?';
$lang['confirm']['yes']='Oui';
$lang['confirm']['no']='Non';

//登录注册页面
$lang['t_login_register'] = 'Connexion ou créer un nouveau compte';
$lang['l_login'] = 'Clients enregistrés';
$lang['l_login_username'] = 'Adresse mail/ Pseudo';
$lang['l_login_psw'] = 'Mot de passe';
$lang['l_login_forgotpsw'] = 'Mot de passe oublié ?';

$lang['l_register'] = 'Informations personnelles';
$lang['l_register_nickname'] = 'Pseudo';
$lang['l_register_email'] = 'Adresse mail';
$lang['l_register_psw'] = 'Mot de passe';
$lang['l_register_confim'] = 'Confirmer le mot de passe';
$lang['l_verification_code'] = 'Code de vérification';
$lang['l_register_agree'] = 'Je suis d\'accord pour ' . ucfirst( COMMON_DOMAIN );
$lang['l_register_agree_sub'] = "Abonnez-vous à économiser de l'argent avec les coupons sur la newsletter chaque semaine";
$lang['l_register_terms'] = 'Termes et Conditions.';
$lang['l_register_tips'] = 'Après votre inscription, vous recevrez notre newsletters avec les informations sur des ventes,coupons et promotions spéciales. Vous pouvez vous désinscrire à Mon Compte.';
$lang['p_username_please'] = 'Entrez le nom d\'utilisateur, s\'il vous plaît ';

$lang['p_captcha_invalid'] = 'Entrée non valide, veuillez essayer de nouveau.';
$lang['p_login_failure'] = 'Invalide connexion ou mot de passe.'; //登录失败
$lang['p_register_fail'] = 'L\'inscription échouée, essayer à nouveau s\'il vous plaît.'; //注册失败
$lang['p_agreement'] = 'Vous n\'êtes pas d\'accord avec l\'accord.'; //用户协议未勾选
$lang['p_username_shorter'] = 'Le pseudo doit avoir au moins 3 caractères.';
$lang['p_password_shorter'] = 'Le mot de passe doit avoir au moins 6 caractères.';
$lang['p_passwd_blank'] = 'Le mot de passe entré ne doit pas être vide.';
$lang['p_reset_password'] = 'Vous recevrez un email avec un lien pour rétablir votre mot de passe.'; //邮箱找回密码信息发送提示
$lang['p_fail_send_password'] = 'L\'échec, contactez l\'administrateur s\'il vous plaît !'; //发送重设密码邮件失败
$lang['p_parm_error'] = 'Erreur, veuillez retourner !'; //重设密码时，错误的用户名或code
$lang['p_reset_psw_success'] = 'Le succès à rétablir le mot de passe.'; //重设密码成功

//注册页面和会员中心js语言包
$lang['username_exist'] = 'Ce pseudo est déjà appliqué.=';
$lang['email_exist'] = 'Il existe déjà un compte avec cette adresse email.';

$lang['required']='Ce champ est obligatoire.';
$lang['username_shorter'] = 'Entrez 3 ou plus caractères, s\'il vous plaît. Des espaces avant ou après seront ignorés.';
$lang['username_invalid'] = 'Un pseudo ne peut être composé de lettres, figure et soulignement.';
$lang['password_shorter'] = 'Entrez 6 ou plus caractères, s\'il vous plaît. Des espaces avant ou après seront ignorés.';
$lang['email_invalid'] = 'Entrez une adresse email valide, s\'il vous plaît. Par exemple johndoe@domain.com.';
$lang['confirm_password_invalid']	='Assurez-vous que vos mots de passe correspondent, s\'il vous plaît.';
$lang['confirm_password_invalid'] = 'Etes-vous sûr de supprimer ce produit de votre liste de souhaits ?';

//退出登录页面
$lang['t_logout'] = 'Déconnexion';
$lang['l_logout_h1'] = 'Vous êtes maintenant déconnecté.';
$lang['l_logout_tips'] = 'Vous êtes déconnecté et vous serez redirigé vers notre page d\'accueil dans <span id="timer">3</span> secondes.';

$lang['l_order_h1'] = 'Mes commandes'; //My Order
$lang['l_order_empty'] = 'Aucune commande n\'a été placée.';
$lang['l_page_items'] = 'Articles';
$lang['l_page_to'] = 'à';
$lang['l_page_of'] = 'sur';
$lang['l_page_total'] = 'un total';
$lang['l_page_show'] = 'Montrer';
$lang['l_page_per'] = 'par page';
$lang['l_page_page'] = 'Page';
$lang['l_page_next'] = 'Suivant ';

$lang['l_paging_next'] = 'Next';
$lang['l_paging_previous'] = 'Previous';

$lang['l_reviews_detail'] = 'Voyez les Détails'; //My Product reviews

$lang['l_tags_tips'] = 'Cliquez sur le mot clé pour voir les produits correspondants.'; //My Tags
$lang['l_no_tag'] = 'Vous n\'avez taggé aucun produit pour le moment.';

$lang['l_news_gs'] = 'Je m\'abonne'; //Newsletter Subscriptions

$lang['l_auction_h1'] = 'Mes enchères '; //My Gifts/Auctions
$lang['l_auction_th_pn'] = 'Nom de l\'article ';
$lang['l_auction_th_rf'] = 'Des détails pour';
$lang['l_auction_th_yp'] = 'Votre prix';
$lang['l_auction_th_ed'] = 'Date d\'expiration';
$lang['l_auction_th_state'] = 'État';
$lang['l_auction_status_win'] = 'GAGNER'; //竞拍当前状态
$lang['l_auction_status_lose'] = 'PERDRE';
$lang['l_auction_page_goto'] = 'aller à';

$lang['p_register_success'] = 'Merci de votre inscription sur EachBuyer.';
$lang['p_user_edit_info_success'] = 'Les informations de compte a été enregistré.';
$lang['p_user_add_newsletter_success'] = 'La souscription a été enregistré.';
$lang['p_user_cancel_newsletter_success'] = 'La souscription a été supprimé.';
$lang['p_user_current_pwd_fail']          = 'Mot de passe actuel non valide';
$lang['p_user_email_invalid']             = 'L\'adresse email n\'est pas valide.';
$lang['p_user_edit_address_success']      = 'L\'adresse a été sauvegardée.';
$lang['p_user_delete_address_success']    = 'L\'adresse a été supprimée.';
/*end*/
$lang['psw_updated'] ='Your password has been updated.'; //密码修改成功//translate
$lang['reset_psw_success']= 'reset psw success'; //修改密码成功//translate
/*fujia*/
$lang['empty_reviews'] = 'Vous n\'avez posté aucun commentaire'; //会员中心，没有评论信息
$lang['empty_wishlist'] = 'Vous n\'avez pas d\'article dans votre liste d\'envies.'; //会员中心，没有收藏信息
$lang['empty_orders'] = 'Vous n\'avez jamais commandé.';//会员中心，没有订单信息
$lang['empty_points'] = 'Vous n\'avez pas points enregistrés.';
$lang['update_wishlist_success']='Le liste de souhaits a été enregistrée.';//更新收藏夹成功
$lang['update_wishlist_fail']='La mise à jour du Liste de souhaits échoué.';//更新收藏夹失败
$lang['p_share_wl_success']='Votre Liste de souhaits a été partagé.'; //分享收藏夹成功
$lang['p_share_wl_fail']='Le partage du Liste de souhaits échoué, veuillez réessayer.'; //分享收藏夹失败

//低价竞拍语言部分
$lang['l_forget_psw_tips']='Si vous avez déjà un compte, veuillez vous identifier.';
$lang['l_signup_news']='Abonnez-vous à la newsletter pour obtenir 10 points & €2.31 de plus.';
$lang['l_login']='Connexion';
$lang['l_rigster']='S\'enregistrer';
$lang['l_login_t']='Connexion';
$lang['l_rigster_t']='S\'enregistrer';
$lang['auction_title']='Dépenser vos points pour une enchère à EachBuyer';
$lang['auction_keywords']='les points eachbuyer, vente aux enchères, vente aux enchères unique la plus basse, offre, échanger, jeu, gagner, ne pas dépenser de l\'argent, pas de risque';
$lang['auction_description']='Utilisez vos points pour échanger, offrir aux enchères, jeu. Vous n \'avez pas besoin de dépenser votre argent. Pas de risque.';
$lang['l_auction_home']='Accueil';
$lang['l_auction_livechart']= 'Chat en direct';
$lang['l_auction_welcome']= 'Bienvenue à eachbuyer!';
$lang['l_auction_rules']='Règles';
$lang['l_auction_more']= 'plus';
$lang['l_auction_copy']= '<span>' . ucfirst( COMMON_DOMAIN ) . '</span>. Tous droits réservés';
$lang['l_auction_ongoing']= 'En cours';
$lang['l_auction_history']= 'Historique';
$lang['l_auction_upcoming']= 'Avenir';
$lang['l_auction_shopping']= 'Gagner des points en faisant des achats';
$lang['l_auction_retails']= 'Se vend au';
$lang['l_auction_page_first']= 'Premier ';
$lang['l_auction_page_last']= 'Dernier';
$lang['l_auction_ended']= 'Terminé';
$lang['l_auction_win']= 'Le prix du gagnante';
$lang['l_auction_won']= 'gagnez';
$lang['l_auction_totalbid']= 'Total des Enchérisseurs';
$lang['l_auction_bid']='Offre';
$lang['l_auction_start']='débuter';
$lang['l_auction_end']='fin';
$lang['l_auction_result']='Resultants des Enchères';
$lang['l_auction_wonitfor']='gagnez-le pour';
$lang['l_auction_logout']='déconnexion';
//auction详情页
$lang['l_auction_sku']='Réf. ';
$lang['l_auction_timeleft']='Temps restant';
$lang['l_auction_lbp']='Dernière Offre';
$lang['l_auction_lb']='Dernier Offreur';
$lang['l_auction_yp']='Votre Prix';
$lang['l_auction_ys_left']='Vous avez dépensé';
$lang['l_auction_ys_right']='points sur cette enchère';
$lang['l_auction_ys_pd']='Description du produit';
$lang['l_auction_ys_ybh']='L’Historique Des Vos';
$lang['l_auction_login']='Connectez-vous, s\'il vous plaît';
$lang['l_auction_invalid']='L\'offre invalide'; //非法竞拍
$lang['l_auction_over']=' Désolé, ces enchères sont terminées.'; //竞拍已结束
$lang['l_auction_min_unique']='Congratulations, this is the current lowest unique bid. As a convenience to you, we\'ll send you an email if someone else\'s bid matches yours making it no longer unique.'; //出价唯一且最低 //translate
$lang['l_auction_min_price_out']='Nicely done! You\'ve just ruined someone\'s chance of winning by matching their current lowest, unique bid.Now make another bid to win the auction!'; //挤掉了之前的最低价格 //translate
$lang['l_auction_nomin_unique']='Congratulations, your bid is definitely unique, but just a little bit higher than the current winning bid.You\'re definitely close, so make another bid!'; //translate
$lang['l_auction_nomin_nounique']='Sorry, this bid is not unique. Please make another bid.'; //translate
$lang['l_auction_pi']='Your points is less than 10. Need more points? Purchase from our thousands of products. Write reviews of our products.
';  //积分不够了//translate
$lang['l_auction_price_exist']='You have already bid this price. Please choose another bid.'; //已经出过价了//translate
$lang['l_auction_dpf']='Désolé, échoué à déduire des points.'; //扣除积分失败//
$lang['l_auction_chart_title']='Distribution Graphique des Offres';//统计图//
$lang['l_auction_chart_y']='Enchères ';
//竞拍详情历史页面
$lang['l_auction_wb']='Offre Gagnante';
$lang['l_auction_winer']='Gagnant';
$lang['l_auction_st']='Heure De Début';
$lang['l_auction_ct']='Heure de Fin';
$lang['l_auction_cabd']='Afficher le Détail de toutes les Offres';
$lang['l_auction_bdg']='Distribution Graphique des Offres.';
$lang['l_start_left_time']='heures jusqu\'à ce que cette vente aux enchères commence.';
$lang['l_auction_search']='Chercher';
$lang['l_auction_search_tips']='Recherche du prix d\'intervalle';
$lang['l_auction_show']='montrer';
$lang['l_auction_price']='Prix';
$lang['l_auction_nickname']='Surnom';
$lang['l_auction_loading']='chargement en cours ...';
//竞拍介绍
$lang['l_auction_01_q']='01.De quel type de vente aux enchères s’agit-il ?';
$lang['l_auction_01_a']='Différente que les ventes aux enchères traditionnelles où le plus offrant gagne, c’est une enchère inversée à offre unique.';
$lang['l_auction_02_q']='02.Comment est-ce que je gagne ?';
$lang['l_auction_02_a']='La personne qui fait l’offre unique la plus basse gagne. Par l’offre unique la plus basse, nous voulons dire que personne d’autre n’a fait de meme offre et que votre offre est la plus basse de tous les participants.<br>  Et dans les rares cas où il n\'y a pas d’offre unique la plus basse nous recherchons le prix le plus bas qui a été offert par le moins de personnes et la personne qui a fait cette offre la première gagne !';
$lang['l_auction_03_q']='03.Comment est-ce que je joue ?';
$lang['l_auction_03_a']='Simplement en vous identifiant à votre compte, en écrivant le montant de votre offre dans la boîte d\'offre et en soumettant cette offre. Vous êtes alors en lice pour gagner l\'article mis en vente. ';
$lang['l_auction_04_q']='04.Combien ça coûte de faire une offre ?';
$lang['l_auction_04_a']='Chaque enchère coûte un prix abordable 10 points de fidélité.';
$lang['l_auction_05_q']='05.Comment est-ce que j\'obtiens les points de récompense ?';
$lang['l_auction_05_a']='Pour chaque dollar que vous dépensez sur les achats dans ' . ucfirst( COMMON_DOMAIN ) . ', vous gagnez un point de récompense. Pour chaque commentaire vous écrivez et approuvé par nous, vous gagnez points de récompense.';
$lang['l_auction_06_q']='06.Est-ce que je peux faire plusieurs offres ?';
$lang['l_auction_06_a']='Vous pouvez faire autant d\'offres que vous voulez tant que vous avez assez de points de récompense.';
$lang['l_auction_07_q']='07.Comment est-ce que je sais que votre vente aux enchères n\'est pas truquée ?';
$lang['l_auction_07_a']='Une fois la vente aux enchères est terminée, nous listons toutes les offres faites par les participants. Les offres sont énumérées avec les pseudos des participants. Par conséquent vous pouvez voir ce que vous et vos amis ont offert.';
$lang['l_auction_08_q']='08.Quel est l’intérêt de faire une offre que je perdrai probablement ?';
$lang['l_auction_08_a']='Si votre offre n’est pas gagnante, vous pouvez toujours utiliser les points de récompense dépensés pour l\'achat de l\'article mis en vente au bas prix normal. Cet avantage expire 72 heures après la fin de la vente aux enchères. ';
$lang['l_auction_09_q']='09.Qu’est-ce que je dois savoir de plus sur cette vente aux enchères ?';
$lang['l_auction_09_a']='Une fois que vous faites une offre, vous serez informé si votre offre est actuellement la plus basse, si elle est actuellement unique, si elle est à la fois la plus basse et unique, ou si elle n\'est ni la plus basse, ni unique. Comme cela, vous pouvez établir votre stratégie.';
$lang['l_auction_10_q']='10.Que se passe-t-il à la fin de la vente aux enchères ?';
$lang['l_auction_10_a']='Vous recevrez un email de notre part vous indiquant si vous avez gagné ou non. Vous pouvez aussi trouver votre enchère dans Mes enchères / Cadeaux pour vérifier l\'état.';

$lang['t_meta_keywords']='{$goods_name} - Offre limitée spéciale dans le temps | ' . ucfirst( COMMON_DOMAIN );
$lang['t_meta_desc']='Achetez pas cher et de haute qualité {$goods_name} sur ' . ucfirst( COMMON_DOMAIN ) . ', Offre limitée spéciale dans le temps';

//邮件订阅模块语言包
$lang['news_title']='Inscription de la newsletter';
$lang['news_sub_title']='Sélectionnez les catégories que vous aimez et assurez-vous que vous recevez la newsletter qui vous intéresse:';
$lang['chg_news_title']='Editer Abonnements';
$lang['chg_news_sub_title']='Changez les catégories pour obtenir les offres différents que vous aimez :';
$lang['fashions']='Mode';
$lang['home_and_garden']='Maison & Jardin';
$lang['electronics']='Electronique';
$lang['sub_confirm']='Abonnez-vous et recevez $ 3';
$lang['info1']='Les newsletters vont sortir une ou deux fois par semaine, et tous les bons plans vous êtes abonné seront inclus dans un mail.';
$lang['info2']='Obtenez <span>$3</span> de crédit sur toutes les commandes';
$lang['info3']='Grandes réductions uniquement pour les abonnés';
$lang['info4']='Des campagnes exclusives pour les abonnés de gagner de gros prix';
$lang['info5']='Toujours le premier à savoir les dernières nouvelles sur ' . ucfirst( COMMON_DOMAIN );
$lang['info6']='La recommandation de produit à raccourcir votre temps de recherche';
$lang['info7']= ucfirst( COMMON_DOMAIN ) . ' s\'engage à protéger votre vie privée. Votre adresse de courriel ne sera jamais vendue à un tiers pour toute raison. Voyez notre Politique de confidentialité.';

$lang['email_last_step_title']='Une étape de plus:';
$lang['sub_success_title']='Abonnement avec succès';
$lang['sub_success_info']='<strong>Coupon Code: <span style="color:#f00;">%s</span></strong><br /><strong>
3 $ les commandes de plus de 30 $, valable jusqu\'au 31 Décembre, 2013.</strong><br />Les détails du coupon ont été envoyées à votre boîte aux lettres.<br />Merci de votre inscription à la newsletter d’' . ucfirst( COMMON_DOMAIN ) . ' ! <br />The discounts and deals about <span style="color:#0083d6">%s</span> will be notified periodically to you via :%s';
$lang['no_category']='Subscribe failed! NO category had been selected.';
$lang['back_home']='Retour à l’' . ucfirst( COMMON_DOMAIN );

$lang['update_success']='Mise à jour avec succès !';
$lang['update_success_info']='Nous vous ferons parvenir les newsletters relatifs en fonction de vos nouvelles catégories.';

$lang['unsub_title']='Entrez votre adresse email pour vous désinscrire si vous ne voulez pas recevoir de les mises à jour à partir de ' . ucfirst( COMMON_DOMAIN ) . ' : ';
$lang['unsub_info']='Vous ne recevrez des coupons ou des offres exclusives uniquement pour les abonnés disposant de cette option. Nous espérons que vous pouvez le considérer à changer vos catégories d\'essayer différentes offres avant de vous désabonner.';
$lang['unsub_button']='Se désabonner';

$lang['unsub_success']='Vous êtes désabonné newsletter EachBuyer !';
$lang['unsub_success_info']='Nous espérons que vous pourrez profiter du shopping dans ' . ucfirst( COMMON_DOMAIN ) . '. Merci. ';

$lang['user_sub_title']='Catégories auxquelles vous êtes abonné :';
$lang['sub_msg']='Choisissez au moins une catégorie que vous aimez, s\'il vous plaît.';
$lang['sub_email_exist']='Nous vous remercions de votre abonnement, mais votre adresse e-mail est déjà sur notre liste de newsletter. Si vous souhaitez modifier vos catégories intéressées, allez à <a href="%s"  style="color: #09318B;">votre compte</a> de faire des changements ou modifier votre abonnement s\'il vous plaît <a href="%s"  style="color: #09318B;"> ici</a>.<br /> Garder votre coupon de $ 3 pour abonnement à la newsletter : <br /><br />';
$lang['check_mail_success']='Un e-mail de confirmation a été envoyé pour vérifier la demande de souscription. Allez à votre boîte aux lettres et suivez les instructions sur cet e-mail pour terminer la dernière étape, s\'il vous plaît.';
$lang['sub_success_info_short']='<strong>Coupon Code: <span style="color:#f00;">%s</span></strong><br /><strong>
3 $ les commandes de plus de 30 $, valable jusqu\'au 31 Décembre, 2013.</strong>';
$lang['sub_auction_reg_info']='S\'il vous plaît allez à votre boîte aux lettres pour vérifier votre abonnement et obtenir $ 3 dès maintenant !';

//注册成功页。
$lang['regOk']['regOk']='Enregistré avec succès.';
$lang['regOk']['congra']='Félicitations !';
$lang['regOk']['sucMsg']='Enregistré avec succès.';
$lang['regOk']['regOkMsg']='Au bout de <span><em id="time">6</em></span> secondes pouratteindresuffit de visiterla page...Ou <a href="/">visitezla page d\'accueil</a>.';

//facebook login
$lang['facebook']['title'] = 'Votre courriel de facebook est déjà enregistré à EachBuyer';
$lang['facebook']['input_pwd'] = 'Entrez la passe de pass du compte EachBuyer';
$lang['facebook']['other_email'] = 'Sélectionnez autre courriel pour la connexion';
$lang['facebook']['error_pwd'] =  'Le mot de passe que vous avez entré ne correspond pas à ce courriel.';
$lang['facebook']['error_have_email'] = 'Il existe déjà un compte associé à cette adresse e-mail.';
$lang['facebook']['error_email'] = "Entrez une adresse e-mail valide, s'il vous plaît.";
$lang['facebook']['l_reset_psw_sub'] = 'Valider';
$lang['facebook']['l_reset_psw_email'] = 'Adresse mail';


$lang['bbs_error_email_address2'] = 'Se il vous plaît entrer une adresse email valide. Par exemple johndoe@domain.com.';