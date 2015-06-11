<?php
/**
* 个人中心语言包.整理后的键值定义规则，页面title以t_开头，菜单以m_开头，标签以l_开头，js信息提示以j_开头，php信息提示以p_开头
* language: es
* @Author : yuandd
* @Last Modify : 2013-07-04
* ps:请翻译带有 “//translate”注释的语言内容，并将此注释删除掉
*/

//杂项
/* fujia 2013-07-09 */
$lang['require_login'] = 'L\'entrée illégale. <br/>Vous ne pouvez pas finir les opérations avant la connexion.'; //非法访问路径
$lang['require_tips'] = 'Campos requeridos';
$lang['l_back'] = 'Volver atrás';
$lang['l_save'] = 'Guardar';
$lang['l_edit'] = 'Editar';

$lang['warning']['remove_item']='¿Seguro que deseas eliminarlo?';
$lang['confirm']['yes']='sí';
$lang['confirm']['no']='No';

//登录注册页面
$lang['t_login_register'] = 'Connexion ou créer un nouveau compte';
$lang['l_login'] = 'Clientes Registrados';
$lang['l_login_username'] = 'Dirección de correo electrónico/ Apodo';
$lang['l_login_psw'] = 'Contraseña';
$lang['l_login_forgotpsw'] = 'Olvidó su contraseña?';

$lang['l_register'] = 'Información personal';
$lang['l_register_nickname'] = 'Apodo';
$lang['l_register_email'] = 'Dirección de correo electrónico';
$lang['l_register_psw'] = 'Contraseña';
$lang['l_register_confim'] = 'Confirmar la contraseña';
$lang['l_verification_code'] = 'Código de verificación:';
$lang['l_register_agree'] = 'Je suis d\'accord pour ' . ucfirst( COMMON_DOMAIN );
$lang['l_register_agree_sub'] = 'Suscríbete a ahorrar dinero con cupones en boletín de noticias cada semana';
$lang['l_register_terms'] = 'Termes et Conditions.';
$lang['l_register_tips'] = 'Après votre inscription, vous recevrez notre newsletters avec les informations sur des ventes,coupons et promotions spéciales. Vous pouvez vous désinscrire à Mon Compte.';

$lang['p_captcha_invalid'] = 'Código incorrecto, por favor inténtalo de nuevo.';
$lang['p_login_failure'] = 'usuario o contraseña inválido'; //登录失败
$lang['p_register_fail'] = 'No inscribirse, por favor intente de nuevo.'; //注册失败
$lang['p_agreement'] = 'Usted no está de acuerdo con el acuerdo'; //用户协议未勾选
$lang['p_username_shorter'] = 'El apodo debe tener al menos 3 caracteres.';
$lang['p_password_shorter'] = 'La contraseña debe tener al menos 6 caracteres.';
$lang['p_passwd_blank'] = 'La contraseña introducida no puede tener en blanco.';
$lang['p_reset_password'] = 'Usted recibirá un correo electrónico con un enlace para restablecer tu contraseña.'; //邮箱找回密码信息发送提示
$lang['p_fail_send_password'] = 'Si no, por favor póngase en contacto con el administrador!'; //发送重设密码邮件失败
$lang['p_parm_error'] = 'Error, vuelva por favor!'; //重设密码时，错误的用户名或code
$lang['p_reset_psw_success'] = 'Restablecer contraseña de éxito.'; //重设密码成功

//注册页面和会员中心js语言包
$lang['username_exist'] = 'Ya existe este apodo al cliente';
$lang['email_exist'] = 'Ya existe una cuenta con esta dirección de correo electrónico.';

$lang['required']='Este es un campo obligatorio.';
$lang['username_shorter'] = 'Por favor, introduzca 3 o más caracteres. Espacios iniciales o finales serán ignoradas.';
$lang['username_invalid'] = 'Apodo sólo puede estar compuesta de letras, calcular y subrayado.';
$lang['password_shorter'] = 'Por favor, introduzca 6 o más caracteres. Espacios iniciales o finales serán ignoradas.';
$lang['email_invalid'] = 'Introduzca una dirección de correo electrónico válida. Por ejemplo johndoe@domain.com.';
$lang['confirm_password_invalid'] = 'Por favor, asegúrese de que sus contraseñas coinciden.';
$lang['confirm_del_wl'] ='¿Está seguro que desea eliminar este producto de la lista?';

//退出登录页面
$lang['t_logout'] = 'Déconnexion';
$lang['l_logout_h1'] = 'Vous êtes maintenant déconnecté';
$lang['l_logout_tips'] = 'Vous êtes déconnecté et vous serez redirigé vers notre page d\'accueil dans <span id="timer">3</span>  secondes.';

$lang['l_order_h1'] = 'Mis Pedidos'; //My Order
$lang['l_order_empty'] = 'Aucune commande n\'a été placée.';
$lang['l_page_items'] = 'Artículos';
$lang['l_page_to'] = 'a';
$lang['l_page_of'] = 'de';
$lang['l_page_total'] = 'en total';
$lang['l_page_show'] = 'Mostrar';
$lang['l_page_per'] = ' por página';
$lang['l_page_page'] = 'Página';
$lang['l_page_next'] = 'Suivant';

$lang['l_paging_next'] = 'Next';
$lang['l_paging_previous'] = 'Previous';

$lang['l_reviews_detail'] = 'Voyez les Détails'; //My Product reviews

$lang['l_tags_tips'] = 'Presione sobre una etiqueta para ver sus correspondientes productos.'; //My Tags
$lang['l_no_tag'] = 'No ha etiquetado ningún producto aún.';

$lang['l_news_gs'] = 'Suscripción general'; //Newsletter Subscriptions

$lang['l_auction_h1'] = 'Mes enchères'; //My Gifts/Auctions
$lang['l_auction_th_pn'] = 'Nom de l\'article';
$lang['l_auction_th_rf'] = 'Des détails pour';
$lang['l_auction_th_yp'] = 'Votre prix';
$lang['l_auction_th_ed'] = 'Date d\'expiration';
$lang['l_auction_th_state'] = 'État';
$lang['l_auction_status_win'] = 'ganar'; //竞拍当前状态
$lang['l_auction_status_lose'] = 'perder';
$lang['l_auction_page_goto'] = 'ir a';

$lang['p_username_please'] ='Por favor, introduzca el nombre de usuario';
$lang['p_register_success'] = 'Merci de votre inscription sur EachBuyer.';
$lang['p_user_edit_info_success'] = 'Les informations de compte a été enregistré.';
$lang['p_user_add_newsletter_success'] = 'La souscription a été enregistré.';
$lang['p_user_cancel_newsletter_success'] = 'La souscription a été supprimé.';
$lang['p_user_current_pwd_fail']          = 'Error al actualizar tu contraseña.';
$lang['p_user_email_invalid']             = 'L\'adresse email n\'est pas valide.';
$lang['p_user_edit_address_success']      = 'La dirección se ha guardado.';
$lang['p_user_delete_address_success']    = 'La dirección ha sido eliminada..';
/*end*/
$lang['psw_updated'] ='Your password has been updated.'; //密码修改成功
$lang['reset_psw_success']= 'reset psw success'; //修改密码成功
/*fujia*/
$lang['empty_reviews'] = 'Usted no ha enviado opiniones.'; //会员中心，没有评论信息
$lang['empty_wishlist'] = 'No tiene artículos en su lista de artículos de interés.'; //会员中心，没有收藏信息
$lang['empty_orders'] = 'Usted no ha realizado Pedidos';//会员中心，没有订单信息
$lang['empty_points'] = 'Usted no tiene ningún récord de puntos.';
$lang['update_wishlist_success']='La lista de deseos había salvado..';//更新收藏夹成功
$lang['update_wishlist_fail']='Error al actualizar la lista de deseos.';//更新收藏夹失败
$lang['p_share_wl_success']='Su lista de deseos se ha compartido.'; //分享收藏夹成功
$lang['p_share_wl_fail']='Compartir la lista de deseos Error,por favor, inténtelo de nuevo..'; //分享收藏夹失败

//低价竞拍语言部分
$lang['l_forget_psw_tips']='Si tiene una cuenta con nosotros, por favor acceda con sus datos.';
$lang['l_signup_news']='Suscribirse al boletín informativo de conseguir 10 puntos & €2.31 más';
$lang['l_login']='Iniciar sesión';
$lang['l_rigster']='Registrarse';
$lang['l_login_t']='Iniciar sesión';
$lang['l_rigster_t']='Registrarse';
$lang['auction_title']='History,Disfrute de sus Puntos de Subastas En EachBuyer';
$lang['auction_keywords']='puntos de eachbuyer, subastas, la oferta única más baja, oferta,canjear, juego, ganar, no gastar dinero, ni riesgo';
$lang['auction_description']='Utilice sus puntos para redimir, pujando subasta, juego.Usted no necesita gastar su dinero. No hay riesgo.';
$lang['l_auction_home']='Inicio';
$lang['l_auction_livechart']= 'Live Chat';//translate
$lang['l_auction_welcome']= 'Bienvenido a EachBuyer !';
$lang['l_auction_rules']='Reglas';
$lang['l_auction_more']= 'más';
$lang['l_auction_copy']= '<span> ' . ucfirst( COMMON_DOMAIN ) . ' </span>. Todos los derechos reservados';
$lang['l_auction_ongoing']= 'En marcha';
$lang['l_auction_history']= 'Historial';
$lang['l_auction_upcoming']= 'Próximas';
$lang['l_auction_shopping']= 'Compras para ganar puntos';
$lang['l_auction_retails']= 'Precio del ganador';
$lang['l_auction_page_first']= 'primero';
$lang['l_auction_page_last']= 'último';
$lang['l_auction_ended']= 'finalizado';
$lang['l_auction_win']= 'Precio del ganador';
$lang['l_auction_won']= 'Ganar';
$lang['l_auction_totalbid']= 'Oferentes totales';
$lang['l_auction_bid']='Oferta';
$lang['l_auction_start']='iniciar';
$lang['l_auction_end']='final';
$lang['l_auction_result']='Resultados de Subastas';
$lang['l_auction_wonitfor']='ganado por';
$lang['l_auction_logout']='logout';
//auction详情页
$lang['l_auction_sku']='SKU';
$lang['l_auction_timeleft']='Tiempo restante';
$lang['l_auction_lbp']='Precio de la última Oferta';
$lang['l_auction_lb']='Último postor';
$lang['l_auction_yp']='Su precio';
$lang['l_auction_ys_left']='Usted ha gastado los';
$lang['l_auction_ys_right']='puntos en esta subasta';
$lang['l_auction_ys_pd']='Descripción del producto';
$lang['l_auction_ys_ybh']='Su Historial de Oferta';
$lang['l_auction_login']='Por favor, inicie sesión';
$lang['l_auction_invalid']='oferta inválida'; //非法竞拍
$lang['l_auction_over']='Lo siento, la subasta ha terminado.'; //竞拍已结束
$lang['l_auction_min_unique']='Congratulations, this is the current lowest unique bid. As a convenience to you, we\'ll send you an email if someone else\'s bid matches yours making it no longer unique.'; //出价唯一且最低//translate
$lang['l_auction_min_price_out']='Nicely done! You\'ve just ruined someone\'s chance of winning by matching their current lowest, unique bid.Now make another bid to win the auction!'; //挤掉了之前的最低价格 //translate
$lang['l_auction_nomin_unique']='Congratulations, your bid is definitely unique, but just a little bit higher than the current winning bid.You\'re definitely close, so make another bid!';//translate
$lang['l_auction_nomin_nounique']='Sorry, this bid is not unique. Please make another bid.';//translate
$lang['l_auction_pi']='Your points is less than 10. Need more points? Purchase from our thousands of products. Write reviews of our products.
';  //积分不够了//translate
$lang['l_auction_price_exist']='You have already bid this price. Please choose another bid.'; //已经出过价了//translate
$lang['l_auction_dpf']='Lo siento, no deducir puntos.';
$lang['l_auction_chart_title']='Ofertas Distribución Gráfico';//统计图
$lang['l_auction_chart_y']='Ofertas';
//竞拍详情历史页面
$lang['l_auction_wb']='Precio del ganador';
$lang['l_auction_winer']='ganador';
$lang['l_auction_st']='Hora de inicio';
$lang['l_auction_ct']='Tiempo de Finalización';
$lang['l_auction_cabd']='Comprobar todos los detalles de la oferta';
$lang['l_auction_bdg']='Ofertas Distribución Gráfico';//translate
$lang['l_start_left_time']='horas hasta que comienza esta subasta.';//translate
$lang['l_auction_search']='Buscar';
$lang['l_auction_search_tips']='Búsqueda de precios de intervalo';//translate
$lang['l_auction_show']='mostrar';//translate
$lang['l_auction_price']=' Precio';
$lang['l_auction_nickname']=' Apodo';
$lang['l_auction_loading']='loading...';//translate
//竞拍介绍
$lang['l_auction_01_q']='01.¿Qué tipo de subasta es esta?';
$lang['l_auction_01_a']='A diferencia de las subastas tradicionales, donde el mejor postor gana, esta se llama subasta de oferta más baja única.';
$lang['l_auction_02_q']='02.¿Cómo puedo ganar?';
$lang['l_auction_02_a']='El postor con la oferta única más baja. Por oferta única más baja nos referimos a que usted gana si su oferta es única, ya que nadie más ha hecho una oferta al mismo precio y además es la oferta con el precio más bajo.<br>  Y en los raros casos en que no exista una oferta única de precio más bajo, entonces buscamos el precio más bajo con el menor número de ofertas y la persona que ofertó primero a ese precio es el ganador!';
$lang['l_auction_03_q']='03.¿Cómo participo?';
$lang['l_auction_03_a']='Basta con que acceda a su cuenta, introduzca una oferta en el cuadro de oferta y envíela. A partir de entonces usted tiene una oportunidad de ganar el artículo ofrecido.';
$lang['l_auction_04_q']='04.¿Cuánto cuesta la oferta?';
$lang['l_auction_04_a']='Cada oferta cuesta un precio asequible de 10 puntos de recompensa.';
$lang['l_auction_05_q']='05.¿Cómo obtengo los puntos de recompensa?';
$lang['l_auction_05_a']='Por cada dólar que gaste en compras en ' . ucfirst( COMMON_DOMAIN ) . '，usted gana un punto de recompensa. Para cada examen que usted escribe y aprobado por nosotros, usted gana cinco puntos de recompensa.';//translate
$lang['l_auction_06_q']='06.¿Puedo ofertar más de una vez?';
$lang['l_auction_06_a']='Usted puede colocar tantas ofertas como sus puntos de recompensa le permitan.';
$lang['l_auction_07_q']='07.¿Cómo estoy seguro de que su subasta no está arreglada?';
$lang['l_auction_07_a']='Después de que la subasta ha terminado, publicamos todas las ofertas presentadas por los participantes. Las ofertas se muestran con los apodos de los participantes. Por lo tanto, usted es capaz de ver lo que usted y sus amigos ofertaron.';
$lang['l_auction_08_q']='08.¿Cuál es el objetivo de ofertar si probablemente pierda?';
$lang['l_auction_08_a']='Si su oferta no tuvo éxito, puede seguir utilizando sus puntos de recompensa usados para la compra del artículo de la subasta en nuestros precios normalmente bajos todos los días. Este beneficio termina 72 horas después de que la subasta ha terminado.';
$lang['l_auction_09_q']='09.¿Qué más necesito saber sobre esta subasta?';
$lang['l_auction_09_a']='Después de realizar una oferta, se le informará si su oferta es actualmente la más baja, y también si es la única en la actualidad, ya sea tanto en la actualidad la más baja y única, o si no es ni la más baja ni la única. Así que ofertar requiere de estrategia.';
$lang['l_auction_10_q']='10.¿Qué sucede después de que la subasta termina?';
$lang['l_auction_10_a']='Recibirá un correo electrónico de nosotros indicándole si ha ganado o no.';

$lang['t_meta_keywords']='{$goods_name} - Oferta por Tiempo Limitado Especial | ' . ucfirst( COMMON_DOMAIN );
$lang['t_meta_desc']='Comprar barato y de alta calidad {$goods_name} en ' . ucfirst( COMMON_DOMAIN ) . ', Oferta por tiempo limitado especial!';

//邮件订阅模块语言包
$lang['news_title']='Boletín de Noticias';
$lang['news_sub_title']='Seleccione las categorías que te gusta y asegúrese de que sólo recibe el boletín que está interesado:';
$lang['chg_news_title']='Editar Suscripciones';
$lang['chg_news_sub_title']='Cambie las categorías para obtener diferentes ofertas que te gustan:';
$lang['fashions']='Moda';
$lang['home_and_garden']=' Hogar y Jardín';
$lang['electronics']='Electrónica';
$lang['sub_confirm']='Suscríbete y recibe $ 3';
$lang['info1']='Newsletters saldrán una o dos veces por semana, y todas las ofertas que está suscrito, serán incluidos en un boletín de noticias. ';
$lang['info2']='Consigue 3 dólares de crédito en cualquier orden';
$lang['info3']='Grandes descuentos sólo para los suscriptores';
$lang['info4']='Campa?as exclusivas para los suscriptores de ganar grandes premios';
$lang['info5']='Siempre será el primero en recibir las últimas noticias de ' . ucfirst( COMMON_DOMAIN );
$lang['info6']='Recomendación de productos para acortar el tiempo de búsqueda';
$lang['info7']= ucfirst( COMMON_DOMAIN ) . ' es utilizado para proteger su privacidad. Su dirección de correo electrónico nunca será vendida a un tercero, por cualquier razón. Vea nuestra Política de Privacidad.';

$lang['email_last_step_title']='Sólo un paso más:';
$lang['sub_success_title']='Suscríbete éxito';
$lang['sub_success_info']='<strong>Código promocional: <span style="color:#f00;">%s</span></strong><br /><strong>
$ 3 de descuento en pedidos de más de $ 30, válido hasta el 31 de diciembre 2013.</strong><br />Detalles de cupones han sido enviado a su buzón de correo.<br />Gracias por suscribirse a boletines de noticias ' . ucfirst( COMMON_DOMAIN ) . ' usted! <br />Los descuentos y ofertas sobre XXXX serán informados periódicamente a su correo :%s';
$lang['no_category']='Subscribe failed! NO category had been selected.';
$lang['back_home']='Volver a ' . ucfirst( COMMON_DOMAIN );

$lang['update_success']='Actualización de éxito!';
$lang['update_success_info']='Nosotros le enviaremos los boletines relacionados de acuerdo con sus nuevas categorías.';

$lang['unsub_title']='Ingrese su dirección de correo electrónico para darse de baja si no desea recibir las actualizaciones de ' . ucfirst( COMMON_DOMAIN ) . ': ';
$lang['unsub_info']='Usted dejará de recibir los cupones u ofertas exclusivas solo para suscriptores con esta opción.Nosotros esperamos que usted pueda tener en cuenta que para cambiar las categorías de probar diferentes ofertas antes de darse de baja.
';
$lang['unsub_button']='Darse de baja';

$lang['unsub_success']='Usted ha cancelado la suscripción newsletter EachBuyer!';
$lang['unsub_success_info']='Esperamos que puedan disfrutar de las compras en ' . ucfirst( COMMON_DOMAIN ) . '. Gracias. ';

$lang['user_sub_title']='Categorías que está suscrito:';
$lang['sub_msg']='Por favor, seleccione al menos una categoría en la que desea';
$lang['sub_email_exist']='Gracias por su suscripción, pero su dirección de correo electrónico tiene ya en nuestro boletín de noticias envíe lista. <br />Si desea cambiar sus categorías de interesados, por favor vaya a <a href="%s"  style="color: #09318B;">su cuenta</a> a <a href="%s"  style="color: #09318B;">here</a>.<br /> Por favor, tenga su cupón de $ 3 por suscripción al boletín:<br /><br />';
$lang['check_mail_success']='Un correo electrónico de confirmación ha sido enviado para verificar la solicitud de suscripción. Por favor, vaya a su buzón de correo y siga las instrucciones en el correo electrónico para finalizar el último paso.';
$lang['sub_success_info_short']='<strong>Código promocional: <span style="color:#f00;">%s</span></strong><br /><strong>
$ 3 de descuento en pedidos de más de $ 30, válido hasta el 31 de diciembre 2013.</strong>';
$lang['sub_auction_reg_info']='Por favor, vaya a su buzón de correo para verificar que su suscripción y obtener $ 3 ahora mismo!';

//注册成功页。
$lang['regOk']['regOk']='Registrado con éxito.';
$lang['regOk']['congra']='¡Enhorabuena!';
$lang['regOk']['sucMsg']='Registrado con éxito.';
$lang['regOk']['regOkMsg']='Después de <span><em id="time">6</em></span> segundos, sesaltará alapáginaVisitado recién ...O <a href="/">visitela página de inicio</a>.';

//facebook login
$lang['facebook']['title'] = 'Su buzón de facebook ya registrado en EachBuyer';
$lang['facebook']['input_pwd'] = 'Introduzca contraseña de ingresar de EachBuyer cuenta';
$lang['facebook']['other_email'] = 'Seleccione Otro Email ingresar';
$lang['facebook']['error_pwd'] =  'la contraseña y el correo electrónico que usted introduce no coincide  ';
$lang['facebook']['error_have_email'] = 'Ya existe una cuenta con esta dirección de correo electrónico';
$lang['facebook']['error_email'] = 'Por favor, introduce un e-mail válida';
$lang['facebook']['l_reset_psw_sub'] = 'Enviar';
$lang['facebook']['l_reset_psw_email'] = 'Dirección de correo electrónico';

$lang['bbs_error_email_address2'] = 'Introduzca una dirección de correo electrónico válida. Por ejemplo johndoe@domain.com.';