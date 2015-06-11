<?php
/**
* 个人中心语言包.整理后的键值定义规则，页面title以t_开头，菜单以m_开头，标签以l_开头，js信息提示以j_开头，php信息提示以p_开头
* language: br
* @Author : yuandd
* @Last Modify : 2013-07-04
* ps:请翻译带有 “//translate”注释的语言内容，并将此注释删除掉
*/

//杂项
/* fujia 2013-07-09 */
$lang['require_login'] = 'Entrada ilegal.<br/> Você não pode concluir a operação até fazer o registro.'; //非法访问路径//translate
$lang['require_tips'] = 'Campos obrigatórios';
$lang['l_back'] = 'Voltar';
$lang['l_save'] = 'Salvar';
$lang['l_edit'] = 'Editar';

$lang['warning']['remove_item']='Are you sure you want to remove it?';
$lang['confirm']['yes']='Sim';
$lang['confirm']['no']='Não';

//登录注册页面
$lang['t_login_register'] = 'Cliente, faça o login ou crie uma nova conta de cliente';
$lang['l_login'] = 'clientes cadastrados';
$lang['l_login_username'] = 'Endereço de e-mail/ Apelido';
$lang['l_login_psw'] = 'Senha';
$lang['l_login_forgotpsw'] = 'Esqueceu sua senha?';

$lang['l_register'] = 'Informações pessoais';
$lang['l_register_nickname'] = 'Apelido';
$lang['l_register_email'] = 'Endereço de e-mail';
$lang['l_register_psw'] = 'Senha';
$lang['l_register_confim'] = 'Confirmar senha';
$lang['l_verification_code'] = 'Código de verificação';
$lang['l_register_agree'] = 'Concordo com os termos da ' . ucfirst( COMMON_DOMAIN );
$lang['l_register_agree_sub'] = 'Subscreva para poupar dinheiro com os cupons da Newsletter semanal';
$lang['l_register_terms'] = 'Termos e Condições.';
$lang['l_register_tips'] = 'Após o cadastro, você receberá as nossas newsletters com informações sobre produtos, cupons e promoções especiais. Você pode cancelar sua inscrição em minha conta.
';

$lang['p_captcha_invalid'] = 'Entrada inválida, por favor tente novamente.';
$lang['p_login_failure'] = 'login ou senha são inválidos.'; //登录失败
$lang['p_register_fail'] = 'O registre falhou, por favor, tente novamente.'; //注册失败
$lang['p_agreement'] = 'Você não concorda com as condições de serviço.'; //用户协议未勾选
$lang['p_username_shorter'] = 'O apelido deve conter pelo menos 3 caracteres.';
$lang['p_password_shorter'] = 'A senha deve ter pelo menos 6 caracteres.';
$lang['p_passwd_blank'] = 'A senha digitada não pode possuir espaço em branco.';
$lang['p_reset_password'] = 'Você receberá um e-mail com um link para redefinir sua senha.'; //邮箱找回密码信息发送提示
$lang['p_fail_send_password'] = 'Falha, por favor, entre em contato com o administrador!'; //发送重设密码邮件失败
$lang['p_parm_error'] = 'Erro, Por favor, retorne!'; //重设密码时，错误的用户名或code
$lang['p_reset_psw_success'] = 'Senha alterada com sucesso.'; //重设密码成功

//注册页面和会员中心js语言包
$lang['username_exist'] = 'Esse apelido de cliente já existe.';
$lang['email_exist'] = 'Já existe uma conta com este endereço de e-mail.';

$lang['required']='Ops, este é um campo obrigatório.';
$lang['username_shorter'] = 'Por favor, digite três ou mais caracteres. Espaços antes ou depois serão ignorados.';
$lang['username_invalid'] = 'O apelido só pode ser composto de letras, simbolos e sublinhado.';
$lang['password_shorter'] = 'Por favor insira seis ou mais caracteres. Espaços antes ou depois serão ignorados.';
$lang['email_invalid'] = 'Por favor insira um endereço de e-mail válido. Por exemplo johndoe@domain.com.';
$lang['confirm_password_invalid'] = 'Por favor, certifique-se de que suas senhas coincidem.';
$lang['confirm_del_wl'] ='Você tem certeza que deseja remover o produto da sua lista de desejos?';

//退出登录页面
$lang['t_logout'] = 'Sair ';
$lang['l_logout_h1'] = 'Você agora está desconectado';
$lang['l_logout_tips'] = 'Você saiu e será redirecionado para nossa página inicial em  <span id="timer">3</span> segundos.';

$lang['l_order_h1'] = 'Meus pedidos'; //My Order
$lang['l_order_empty'] = 'Você não tem nenhum pedido.';
$lang['l_page_items'] = 'Itens';
$lang['l_page_to'] = 'para';
$lang['l_page_of'] = 'de';
$lang['l_page_total'] = 'total';
$lang['l_page_show'] = 'Mostrar';
$lang['l_page_per'] = 'por página';
$lang['l_page_page'] = 'Página';
$lang['l_page_next'] = 'próximo';

$lang['l_paging_next'] = 'Próximo';
$lang['l_paging_previous'] = 'Anterior';

$lang['l_reviews_detail'] = 'Ver detalhes'; //My Product reviews

$lang['l_tags_tips'] = 'Clique na tag para visualizar seus produtos correspondentes.'; //My Tags
$lang['l_no_tag'] = 'You have not tagged any products yet.';//translate

$lang['l_news_gs'] = 'Assinatura comum'; //Newsletter Subscriptions

$lang['l_auction_h1'] = 'Meu Leilão'; //My Gift
$lang['l_auction_th_pn'] = 'Nome do Produto';
$lang['l_auction_th_rf'] = 'vendido por';
$lang['l_auction_th_yp'] = 'O seu preço';
$lang['l_auction_th_ed'] = 'data de vencimento';
$lang['l_auction_th_state'] = 'Estado';
$lang['l_auction_status_win'] = 'VENCEU'; //竞拍当前状态
$lang['l_auction_status_lose'] = 'PERDEU';
$lang['l_auction_page_goto'] = 'ir a';
$lang['p_username_please']='por favor coloque o nome de usuário';
$lang['p_register_success'] = 'Obrigado por se registar na EachBuyer.';
$lang['p_user_edit_info_success'] = 'As informações da conta foram salvas.';
$lang['p_user_add_newsletter_success'] = 'A assinatura foi salva.';
$lang['p_user_cancel_newsletter_success'] = 'A assinatura foi removida.';
$lang['p_user_current_pwd_fail']          = 'Senha atual inválida';
$lang['p_user_email_invalid']             = 'O endereço de email é inválido.';
$lang['p_user_edit_address_success']      = 'O endereço foi salvo.';
$lang['p_user_delete_address_success']    = 'O endereço foi apagado.';
/*end*/
$lang['psw_updated'] ='Your password has been updated.'; //密码修改成功
$lang['reset_psw_success']= 'reset psw success'; //修改密码成功
/*fujia*/
$lang['empty_reviews'] = 'Você não enviou opiniões.'; //会员中心，没有评论信息
$lang['empty_wishlist'] = 'Não há itens na sua lista de desejos.'; //会员中心，没有收藏信息
$lang['empty_orders'] = 'Você não tem nenhum pedido.';//会员中心，没有订单信息
$lang['empty_points'] = 'Você não tem nenhum ponto registrado.';
$lang['update_wishlist_success']='Lista de desejos foi salva.';//更新收藏夹成功
$lang['update_wishlist_fail']='A atualuzação da lista de desejos falhou.';//更新收藏夹失败
$lang['p_share_wl_success']='Sua Lista de Desejos foi compartilhada.'; //分享收藏夹成功
$lang['p_share_wl_fail']='O Compartilhamento da Lista de desejos falhou, por favor, tente novamente.'; //分享收藏夹失败

//低价竞拍语言部分
$lang['l_forget_psw_tips']='Se você tem uma conta conosco, efetue o login.';
$lang['l_signup_news']='Subscreva a newsletter para receber mais 10 pontos & R$6';
$lang['l_login']='Entrar';
$lang['l_rigster']='Registrar';
$lang['l_login_t']='Entrar';
$lang['l_rigster_t']='Registrar';
$lang['auction_title']='Gastar seus pontos para leilão a EachBuyer';
$lang['auction_keywords']='pontos eachbuyer, leilão, leilões menor único, oferta, resgatar, jogo, ganhar, gastar nenhum dinheiro, nenhum risco';
$lang['auction_description']='Use os seus pontos para resgatar, o lance do leilão, game. Você não precisa gastar o seu dinheiro. Não há risco.';
$lang['l_auction_home']='Principal';
$lang['l_auction_livechart']= 'Chat ao vivo';
$lang['l_auction_welcome']= 'Bem-vindo ao EachBuyer!';
$lang['l_auction_rules']='Regras';
$lang['l_auction_more']= 'veja mais';
$lang['l_auction_copy']= '<span>' . ucfirst( COMMON_DOMAIN ) . '</span>. Todos os direitos reservados';
$lang['l_auction_ongoing']= 'Agora';
$lang['l_auction_history']= 'Histórico';
$lang['l_auction_upcoming']= 'Próximos';
$lang['l_auction_shopping']= 'Compras para ganhar pontos';
$lang['l_auction_retails']= 'No varejo';
$lang['l_auction_page_first']= 'primeiro';
$lang['l_auction_page_last']= 'último';
$lang['l_auction_ended']= 'Finalizado';
$lang['l_auction_win']= 'Valor vencedor';
$lang['l_auction_won']= 'Ganhador';
$lang['l_auction_totalbid']= 'Total de apostadores';
$lang['l_auction_bid']='Apostar';
$lang['l_auction_start']='começar';
$lang['l_auction_end']='final';
$lang['l_auction_result']='Auction Results';//translate
$lang['l_auction_wonitfor']='levou por';
$lang['l_auction_logout']='sair';
//auction详情页
$lang['l_auction_sku']='ID do produto';
$lang['l_auction_timeleft']='tempo restante';
$lang['l_auction_lbp']='Valor do último lance';
$lang['l_auction_lb']='Último apostador';
$lang['l_auction_yp']='Seu preço';
$lang['l_auction_ys_left']='Você gastou';
$lang['l_auction_ys_right']='pontos neste leilão';
$lang['l_auction_ys_pd']='Descrição do produto';
$lang['l_auction_ys_ybh']='Seu Historico de ';
$lang['l_auction_login']='Conecte-se';
$lang['l_auction_invalid']='lance inválido'; //非法竞拍
$lang['l_auction_over']='Desculpe, mas o leilão terminou.'; //竞拍已结束
$lang['l_auction_min_unique']='Congratulations, this is the current lowest unique bid. As a convenience to you, we\'ll send you an email if someone else\'s bid matches yours making it no longer unique.'; //出价唯一且最低//translate
$lang['l_auction_min_price_out']='Nicely done! You\'ve just ruined someone\'s chance of winning by matching their current lowest, unique bid.Now make another bid to win the auction!'; //挤掉了之前的最低价格 //translate
$lang['l_auction_nomin_unique']='Congratulations, your bid is definitely unique, but just a little bit higher than the current winning bid.You\'re definitely close, so make another bid!'; //translate
$lang['l_auction_nomin_nounique']='Sorry, this bid is not unique. Please make another bid.'; //translate
$lang['l_auction_pi']='Your points is less than 10. Need more points? Purchase from our thousands of products. Write reviews of our products.
';  //积分不够了//translate
$lang['l_auction_price_exist']='You have already bid this price. Please choose another bid.'; //已经出过价了//translate
$lang['l_auction_dpf']='Desculpe, o desconto dos pontos falhou.';
$lang['l_auction_chart_title']='Gráfico da distribuição das apostas';//统计图
$lang['l_auction_chart_y']='Apostas';
//竞拍详情历史页面
$lang['l_auction_wb']='Valor vencedor';
$lang['l_auction_winer']='Vencedor';
$lang['l_auction_st']='Tempo de ínicio';
$lang['l_auction_ct']='Tempo de término';
$lang['l_auction_cabd']='Confira todos os detalhes do lance';
$lang['l_auction_bdg']='Gráfico Distribuição de Apostas';
$lang['l_start_left_time']='horas até este leilão começar.';
$lang['l_auction_search']='Pesquisar';
$lang['l_auction_search_tips']='Pesquisa do intervalo de preço';
$lang['l_auction_show']='mostrar';
$lang['l_auction_price']='Preço';
$lang['l_auction_nickname']='Apelido';
$lang['l_auction_loading']='Carregando ...';
//竞拍介绍
$lang['l_auction_01_q']='01.Que tipo de leilão é isso?';
$lang['l_auction_01_a']='Ao contrário dos leilões tradicionais, onde o maior lance ganha, isso é chamado de um leilão de menor lance único.';
$lang['l_auction_02_q']='02.E como faço para ganhar?';
$lang['l_auction_02_a']='O participante com o menor lance único vence a oferta. Por menor lance único entende se que você ganha se o lance é único no qual ninguém apostou o mesmo valor que o seu <br>  E nos casos raros em que não existe um preço com o menor lance único então pesquisamos entre o menor preço com o menor número de apostadores. O primeiro participante mediante a esse critério é considerado o vencedor!';
$lang['l_auction_03_q']='03.Como faço para jogar?';
$lang['l_auction_03_a']='Basta fazer login na sua conta, digite um lance na caixa de compra e pressione enviar. A partir daí você tem uma chance de ganhar o item ofertado.';
$lang['l_auction_04_q']='04.Quanto custa para concorrer?';
$lang['l_auction_04_a']='Cada lance custa um preço equivalente a 10 pontos de recompensa.';
$lang['l_auction_05_q']='05.Como posso obter pontos de recompensa?';
$lang['l_auction_05_a']='Para cada dólar que você gastar em compras na ' . ucfirst( COMMON_DOMAIN ) .', você ganha um ponto de recompensa. Para cada avaliação que você escrever,  e aprovado por nós, você ganha cinco pontos de recompensa.';
$lang['l_auction_06_q']='06.Posso apostar mais de uma vez?';
$lang['l_auction_06_a']='Você pode fazer tantas apostas quanto o seus pontos de recompensa lhe permitir.';
$lang['l_auction_07_q']='07.Como eu sei que a oferta vencedora não foi fixada antes?';
$lang['l_auction_07_a']='Após o termino do leilão, vamos publicar todas as propostas feitas pelos participantes.  As ofertas são listados bem como os nicknames dos participante. Portanto, você é capaz de ver o que você e seus colegas lançaram.';
$lang['l_auction_08_q']='08.Qual é a vantagem desse tipo de aposta quando eu provavelmente sei que posso perder?';
$lang['l_auction_08_a']='Se o seu lance não for bem sucedido, você ainda pode usar seus pontos de recompensa utilizados para a compra do item de leilão com nossos preços mais baixos diariamente exposto em nosso site. Esse benefício expira 72 horas após o termino do leilão.';
$lang['l_auction_09_q']='09.O que mais eu preciso saber sobre esse leilão?';
$lang['l_auction_09_a']='Depois de fazer uma oferta, você será informado se o seu lance é atualmente o mais baixo, se é atualmente único, também se é atualmente o menor e único, ou seja, não é nem o mais baixo nem exclusivo. Assim, a aposta requer estratégia.';
$lang['l_auction_10_q']='10.O que acontece após o término do leilão?';
$lang['l_auction_10_a']='Você receberá um e-mail informando se você ganhou ou não. Você também pode encontrar o seu leilão em Meus Leilões / Presentes para verificar o status.';

$lang['t_meta_keywords']='{$goods_name} - Oferta Por Tempo Limitado | ' . ucfirst( COMMON_DOMAIN );
$lang['t_meta_desc']='Preco baixo e alta qualidade {$goods_name} somente na achBuyer.com , Oferta por tempo limitado!';

//邮件订阅模块语言包
$lang['news_title']=' subscrição da newsletter';
$lang['news_sub_title']='Selecione as categorias que você gosta para ter certeza de que você só recebera a newsletter que lhe interessa:';
$lang['chg_news_title']='Editar Assinatura';
$lang['chg_news_sub_title']='Alterar as categorias de obter diferentes ofertas que você gosta:';
$lang['fashions']='Moda';
$lang['home_and_garden']='Home & Garden';
$lang['electronics']=' Eletrônicos ';
$lang['sub_confirm']='Subscrive e ganhe $3';
$lang['info1']=' Newsletters sera inviado uma ou duas vezes por semana e todas as promoções tambem serao incluída em um boletim. ';
$lang['info2']='Receba <span>$3</span> credito em qualquer pedido';
$lang['info3']='Grandes descontos apenas para assinantes';
$lang['info4']='Campanhas exclusivas para os assinantes de ganhar grandes prêmios';
$lang['info5']='Seja sempre o primeiro a saber as últimas notícias do ' . ucfirst( COMMON_DOMAIN );
$lang['info6']=' Recomendação de produtos para encurtar seu tempo de busca ';
$lang['info7']= ucfirst( COMMON_DOMAIN ) . ' está empenhado em proteger a sua privacidade. Seu endereço de email nunca sera vendido a um terceiro, por qualquer motivo. Veja nossa Política de Privacidade.';

$lang['email_last_step_title']='So mais um passo:';
$lang['sub_success_title']='Assinado com sucesso';
$lang['sub_success_info']='<strong>Codigo do cupon: <span style="color:#f00;">%s</span></strong><br /><strong>
$ 3 OFF ordens mais de $ 30, com validade até 31 de dezembro de 2013.</strong><br /> Detalhes do cupon foram inviados para o seu email.<br />Obrigado por subscrever a newsletter ' . ucfirst( COMMON_DOMAIN ) . '! <br /> Os descontos e promoções sobre <span style="color:#0083d6">%s</span> será notificado periodicamente para você via :%s';
$lang['no_category']='Subscribe failed! NO category had been selected.';
$lang['back_home']='Volte ao ' . ucfirst( COMMON_DOMAIN );

$lang['update_success']='Update feito com Sucesso!';
$lang['update_success_info']='Iremos enviar-lhe os boletins relacionados de acordo com suas novas categorias.';

$lang['unsub_title']=' Digite seu endereço de e-mail para cancelar a inscrição, se você não deseja receber nenhuma atualização de ' . ucfirst( COMMON_DOMAIN ) . ': ';
$lang['unsub_info']='Você deixará de receber todos os cupons ou promoções exclusivos somente para assinantes com esta opção. Nós esperamos que você pode considerar que, para mudar suas categorias de experimentar diferentes ofertas antes de cancelar';
$lang['unsub_button']='Anular';

$lang['unsub_success']=' Você não subscritas boletim EachBuyer!';
$lang['unsub_success_info']='Esperamos que você possa desfrutar de compras em ' . ucfirst( COMMON_DOMAIN ) . '. Obrigado. ';

$lang['user_sub_title']='Categorias subscritos:';
$lang['sub_msg']=' Favor selecionar pelo menos uma categoria de sua preferencia ';
$lang['sub_email_exist']='Se voce quiser trocar a categoria que voce se inscreveu favor va
<a href="%s"  style="color: #09318B;"> editar o seu perfil</a>';
$lang['check_mail_success']='Um e-mail de confirmação foi enviado para verificar o pedido de inscrição. Por favor, vá para sua caixa postal e siga as instruções do e-mail para concluir a última etapa.';
$lang['sub_success_info_short']='<strong>Codigo do cupon: <span style="color:#f00;">%s</span></strong><br /><strong>
$ 3 OFF ordens mais de $ 30, com validade até 31 de dezembro de 2013.</strong>';
$lang['sub_auction_reg_info']='Please go to your mailbox to verify your subscription and get $3 right now!';

//注册成功页。
$lang['regOk']['regOk']='Registrado com sucesso.';
$lang['regOk']['congra']='Parabéns!';
$lang['regOk']['sucMsg']='Registrado com sucesso.';
$lang['regOk']['regOkMsg']='After<span> <em id="time">6</em></span> seconds will jump to just visit the page..or <a href="/">Visit Home</a>';
$lang['regOk']['regOkMsg']='Depois de <span><em id="time">6</em></span> segundos, ele irá saltar para a página visitada recentemente. . . Ou <a href="/">visite a página inicial</a>.';

//facebook login
$lang['facebook']['title'] = 'Seu e-mail de Facebook já está registrado Eachbuyer.';
$lang['facebook']['input_pwd'] = 'Digite seu passward.';
$lang['facebook']['other_email'] = 'Entrar com outro endereço de e-mail.';
$lang['facebook']['error_pwd'] =  'A senha que você digitou não corresponde ao e-mail.';
$lang['facebook']['error_have_email'] = 'Já existe uma conta com este endereço de e-mail.';
$lang['facebook']['error_email'] = "Por favor insira um endereço de e-mail válido.";
$lang['facebook']['l_reset_psw_sub'] = 'Enviar';
$lang['facebook']['l_reset_psw_email'] = 'Endereço de e-mail';


$lang['bbs_error_email_address2'] = 'Si prega di inserire un indirizzo email valido. Per esempio johndoe@domain.com.';