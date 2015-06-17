<!--topbar start -->
<div class="topbar grey666" id="topbar">
    <div class="wrap">
        <!-- 语言设置 -->
        <div class="lan_cur">
            <div class="store_switch fl mr10 <?php echo isset($isOnlyHeader)?'hide':'' ?>">
                    <div class="select_block">
                        <div class="selected">
                            <a title="<?php echo lang('language') ?>" href="javascript:;"><?php echo id2name('current_language_title',$header) ?></a>
                            <i class="icon_select_arrow"></i>
                        </div>
                        <?php if(isset($header['language_list'])){ ?>
                        <div class="drop_box">
                            <ul class="drop_list drop_content">
                                <?php foreach($header['language_list'] as $record){ ?>
                                    <?php if($language_code != $record['code']){ ?>
                                        <?php if($this->router->class == 'buy' || $this->router->class == 'atoz'){ ?>
                                            <li> <a href="<?php echo $record['url'] ?>"><span><?php echo $record['title']?></span></a> </li>
                                        <?php }else{ ?>
                                            <li> <a href="<?php echo $record['url'].  HelpUrl::removeXSS( $_SERVER['REQUEST_URI'] ); ?>"><span><?php echo $record['title']?></span></a> </li>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            </ul>
                        </div>
                        <?php } ?>
                    </div>
            </div>

            <div class="currency_switch fl">
                    <div class="select_block <?php echo isset($isOnlyHeader)?'hide':'' ?>">
                        <div class="selected">
                            <a title="<?php echo lang('currency') ?>" href="javascript:;" rel="nofollow"><?php echo lang('currency') ?>: <em class="orange currency_name"><?php echo $currency ?></em></a>
                            <i class="icon_select_arrow"></i>
                        </div>
                        <?php if(isset($header['currency_list'])){ ?>
                        <div class="drop_box">
                            <div class="drop_content">
                                <div class="pl10 pr10">
                                    <input class="search_currency" type="text" id="searchCurrency" autocomplete="off"/>
                                    <input id="all_currency" type="hidden" value = "<?php echo implode(',',$header['currency_list']) ?>"/>
                                </div>
                                <div class="drop_list guoqi_list" id="guoqiList">
                                    <?php foreach($header['currency_list'] as $record){ ?>
                                    <a href="javascript:;" onclick="ec.currency('<?php echo $record ?>');" rel="nofollow" class="tab_<?php echo strtoupper($record) ?>"><i class="icon_guoqi tab_<?php echo $record ?>"></i><span><?php echo $record ?></span></a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
            </div>
        </div><!-- 语言设置 end -->

        <div class="login_minicart">
            <!-- 迷你购物车 -->
            <div class="mini_cart fr ml10 <?php echo isset($isOnlyHeader)?'hide':'' ?>">
                <div class="select_block" id="miniCart">
                    <div class="selected">
                        <a title="<?php echo lang('cart_name') ?>" href="<?php echo eb_gen_url('cart') ?>" rel="nofollow"><i class="icon_cart vam"></i><?php echo lang('cart_name') ?><span class="total">(<i id="cartTotal" class="red">0</i>)</span></a>
                        <i class="icon_select_arrow"></i>
                    </div>
                    <div class="drop_box">
                        <div class="drop_content p10">
                            <div>
                                <p class="min_cart_tips pt15 orange txtc" id="minCartTips" data-tip="<?php echo lang('no_items') ?>">loading...</p>
                                <div id="miniCartContent"></div>
                            </div>
                            <div class="min_cart_but pt15 txtr"><a href="<?php echo eb_gen_url('cart') ?>" class="btn btn_24 btn_bk shadow"><?php echo lang('view_cart') ?></a></div>
                        </div>
                    </div>
                </div>
            </div><!-- 迷你购物车 end -->
            <!-- 个人中心快速入口 -->
            <?php if(!isset($flg_header_account_disable) || $flg_header_account_disable !== true){ ?>
            <div class="my_account fr ml10">
                <div class="select_block">
                        <div class="selected lan_<?php echo $language_code?>">
                            <a title="<?php echo lang('my_account') ?>" href="<?php echo eb_gen_url('account') ?>" rel="nofollow"><?php echo lang('my_account') ?></a>
                            <i class="icon_select_arrow"></i>
                        </div>
                        <div class="drop_box">
                            <ul class="drop_content drop_list">
                                <li><a href="<?php echo eb_gen_url('account') ?>"><span><?php echo lang('account_dash') ?></span></a></li>
                                <li><a href="<?php echo eb_gen_url('wishlist') ?>"><span><?php echo lang('my_wishlist') ?></span></a></li>
                                <li><a href="<?php echo eb_gen_url('order_list') ?>"><span><?php echo lang('my_order') ?></span></a></li>
                                <li><a href="<?php echo eb_gen_url('review_list') ?>"><span><?php echo lang('my_review') ?></span></a></li>
                                <li><a href="<?php echo eb_gen_url('bbs_user') ?>"><span><?php echo lang('bbs_user') ?></span></a></li>
                            </ul>
                        </div>
                </div>
            </div><!-- 个人中心快速入口 end -->
            <?php } ?>
            <!-- 快速登录 -->
            <div class="login fr" id="ajaxLogin">
            </div><!-- 快速登录 end -->

        </div>
    </div>	
</div><!--topbar end-->

<!-- ajax登录模板 -->
<script type="text/html" id="isLoginTpl">
<div class="login_ok grey333">
    <span id="welcomeMsg" class="welcome_msg"><?php echo lang('welcome')?>, {{user_name}} !</span>
    <span id="headerLogout" class="logout">&nbsp;<a href="<?php echo eb_gen_url('common/logout') ?>" rel="nofollow"><?php echo lang('logout') ?></a></span>
</div>
</script>
<script type="text/html" id="unLoginTpl">
<div class="select_block">
    <div class="selected">
        <a href="<?php echo eb_gen_url('login') ?>" rel="nofollow"><?php echo lang('label_login') ?></a>
        <em><?php echo lang('or') ?></em>
        <a href="<?php echo eb_gen_url('login') ?>" rel="nofollow"><?php echo lang('label_regist') ?></a>
        <i class="icon_select_arrow"></i>
    </div>

    <div class="drop_box">
        <div class="fb_login drop_content p10 login_<?php echo $language_code?>">
            <table>
                <tr>
                    <td width="45%">
                        <form autocomplete="off" id="loginForm" name="formLogin" action="<?php echo eb_gen_url('login/authenticateApi') ?>" method="post" class="fb_login_form" onsubmit="return ec.login.ajaxLogin(this);">
                            <h5 class="red"><span><?php echo lang('label_login') ?></span></h5>
                            <div class="login_content">
                                <label for="mini-login" class="grey333"><?php echo lang('l_login_username') ?>:</label>
                                <input type="text" name="user_name" id="userName">
                                <label for="mini-password" class="grey333"><?php echo lang('l_login_psw') ?>:</label>
                                <input type="password" name="password" id="passWord">

                                <div class="fb_form_botton mt15">
                                    <button id="loginSubmitTop" class="btn btn_30 btn_bk shadow" type="submit"><?php echo lang('label_login') ?></button>
                                </div>
                                <div class="top_login_error">
                                    <span class="hide" id="topLoginError"></span>
                                </div>
                            </div>
                        </form>
                    </td>
                    <td class="login_new_customers">
                        <div class="login_text pb20">
                            <h5 class="red pb5"><?php echo lang('newCustomers') ?></h5>
                            <p><?php echo lang('member_desc') ?></p>
                        </div>
                        <p><a href="<?php echo eb_gen_url('login') ?>" class="btn btn_30 btn_bk shadow"><?php echo lang('label_CreateAnAccount') ?></a></p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="fb_login_bottom">
                        <div>
                            <a href="javascript:;" class="fb_login_1" onclick="ec.login.fbLogin();">
                                <i class="icon_fb vam"></i>
                                <span class="vam"><strong><?php echo lang('popbox_login')?></strong></span>
                            </a>
                            <span class="facebook_text pl15"><?php echo lang('facebook_login_text') ?></span>
                        </div>

                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
</script>
<!-- ajax登录模板 end -->

<!-- 购物车模板 -->
<script type="text/html" id="miniCartTpl">
    {{if (list && list.length > 0)}}
        <table class="mini_cart_list">
            {{each list}}
            <tr>
                <td class="cart_img"><a href="{{$value.url}}"><img alt="{{$value.goodsName}}" src="{{$value.image45}}"></a></td>
                <td class="cart_name"><a href="{{$value.url}}">{{$value.goodsName}}</a></td>
                <td class="cart_price">{{$value.finalPriceFormat}}</td>
                <td class="cart_num">{{$value.qty}}</td>
            </tr>
            {{/each}}
        </table>
    {{/if}}
</script>
<!-- 购物车模板 end -->