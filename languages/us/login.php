<?php
/**
* 个人中心语言包.整理后的键值定义规则，页面title以t_开头，菜单以m_开头，标签以l_开头，js信息提示以j_开头，php信息提示以p_开头
* language: en
* @Author : yuandd
* @Last Modify : 2013-07-04
*/

//杂项
$lang['require_login'] = 'Illegal entry.<br />You can\'t finish the operation until login.'; //非法访问路径
$lang['require_tips'] = 'Required Fields';
$lang['l_back'] = 'Back';
$lang['l_save'] = 'Save';
$lang['l_edit'] = 'Edit';

$lang['warning']['remove_item']='Are you sure you want to remove it?';
$lang['confirm']['yes']='Yes';
$lang['confirm']['no']='No';

//登录注册页面
$lang['t_login_register'] = 'Customer login or create new customer account';
$lang['l_login'] = 'Registered Customers';
$lang['l_login_username'] = 'Email Address/Nickname';
$lang['l_login_psw'] = 'Password';
$lang['l_login_forgotpsw'] = 'Forgot Your Password?';

$lang['l_register'] = 'Personal Information';
$lang['l_register_nickname'] = 'Nickname';
$lang['l_register_email'] = 'Email Address';
$lang['l_register_psw'] = 'Password';
$lang['l_register_confim'] = 'Confirm Password';
$lang['l_verification_code'] = 'Verification code';
$lang['l_register_agree'] = 'I agree to ' . ucfirst( COMMON_DOMAIN );
$lang['l_register_agree_sub'] = 'Subscribe to save money with coupons on newsletter every week';
$lang['l_register_terms'] = 'Terms and Conditions.';
$lang['l_register_tips'] = 'After registering, you will receive our newsletters with information about sales, coupons, and special promotions. You can unsubscribe in My Account.';

$lang['p_username_please'] = 'please input the user name';
$lang['p_captcha_invalid'] = 'Invalid entry, please try again.';
$lang['p_login_failure'] = 'Invalid login or password.'; //登录失败
$lang['p_register_fail'] = 'Register failed,please try again.'; //注册失败
$lang['p_agreement'] = 'You do not agree with the agreement'; //用户协议未勾选
$lang['p_username_shorter'] = 'The nickname must have at least 3 characters.';
$lang['p_password_shorter'] = 'The password must have at least 6 characters.';
$lang['p_passwd_blank'] = 'The password entered can`t have blank.';
$lang['p_reset_password'] = 'You will receive an email with a link to reset your password.'; //邮箱找回密码信息发送提示
$lang['p_fail_send_password'] = 'Failure, please contact with administrator!'; //发送重设密码邮件失败
$lang['p_parm_error'] = 'Error, Please return!'; //重设密码时，错误的用户名或code
$lang['p_reset_psw_success'] = 'Reset password success.'; //重设密码成功

//注册页面和会员中心js语言包
$lang['username_exist'] = 'This customer Nickname already exists';
$lang['email_exist'] = 'There is already an account with this email address.';

$lang['required']='This is a required field.';
$lang['username_shorter'] = 'Please enter 3 or more characters. Leading or trailing spaces will be ignored.';
$lang['username_invalid'] = 'Nickname only can be composed of letters, figure and underline.';
$lang['password_shorter'] = 'Please enter 6 or more characters. Leading or trailing spaces will be ignored.';
$lang['email_invalid'] = 'Please enter a valid email address. For example johndoe@domain.com.';
$lang['confirm_password_invalid'] = 'Please make sure your passwords match.';
$lang['confirm_del_wl']='Are you sure you want to remove this product from your wishlist?';

//退出登录页面
$lang['t_logout'] = 'Customer logout';
$lang['l_logout_h1'] = 'You are now logged out';
$lang['l_logout_tips'] = 'You have logged out and will be redirected to our homepage in <span id="timer">3</span> seconds.';

$lang['l_order_h1'] = 'My Orders'; //My Order
$lang['l_order_empty'] = 'You have placed no orders.';
$lang['l_page_items'] = 'Items';
$lang['l_page_to'] = 'to';
$lang['l_page_of'] = 'of';
$lang['l_page_total'] = 'total';
$lang['l_page_show'] = 'Show';
$lang['l_page_per'] = 'per page';
$lang['l_page_page'] = 'Page';
$lang['l_page_next'] = 'Next';

$lang['l_paging_next'] = 'Next';
$lang['l_paging_previous'] = 'Previous';

$lang['l_page_records'] = 'records';

$lang['l_reviews_detail'] = 'View Details'; //My Product reviews

$lang['l_tags_tips'] = 'Click on a tag to view your corresponding products.'; //My Tags
$lang['l_no_tag'] = 'You have not tagged any products yet.';

$lang['l_news_gs'] = 'General Subscription'; //Newsletter Subscriptions

$lang['l_auction_h1'] = 'My Auction'; //My Gifts/Auctions
$lang['l_auction_th_pn'] = 'Product Name';
$lang['l_auction_th_rf'] = 'Retails for';
$lang['l_auction_th_yp'] = 'Your Price';
$lang['l_auction_th_ed'] = 'Expire date';
$lang['l_auction_th_state'] = 'State';
$lang['l_auction_status_win'] = 'WIN'; //竞拍当前状态
$lang['l_auction_status_lose'] = 'LOSE';
$lang['l_auction_page_goto'] = 'go to';

$lang['p_register_success'] = 'Thank you for registering with EachBuyer.';
$lang['p_user_edit_info_success'] = 'The account information has been saved.';
$lang['p_user_add_newsletter_success'] = 'The subscription has been saved.';
$lang['p_user_cancel_newsletter_success'] = 'The subscription has been removed.';
$lang['p_user_current_pwd_fail']          = 'Invalid current password.';
$lang['p_user_email_invalid']             = 'The email address is invalid.';
$lang['p_user_edit_address_success']      = 'The address has been saved.';
$lang['p_user_delete_address_success']    = 'The address has been deleted.';
$lang['psw_updated'] ='Your password has been updated.'; //密码修改成功
$lang['reset_psw_success']= 'reset psw success'; //修改密码成功
$lang['empty_reviews'] = 'You have submitted no reviews.'; //会员中心，没有评论信息
$lang['empty_wishlist'] = 'You have no items in your wishlist.'; //会员中心，没有收藏信息
$lang['empty_orders'] = 'You have placed no orders.';//会员中心，没有订单信息
$lang['update_wishlist_success']='Wishlist had saved.';//更新收藏夹成功
$lang['update_wishlist_fail']='Update wishlist failed.';//更新收藏夹失败
$lang['p_share_wl_success']='Your Wishlist has been shared.'; //分享收藏夹成功
$lang['p_share_wl_fail']='Share Wishlist failed,please try again.'; //分享收藏夹失败

//低价竞拍语言部分
$lang['l_forget_psw_tips']='If you have an account with us,please log in.';
$lang['l_signup_news']='Subscribe newsletter to get 10 more points & $3';
$lang['l_login']='LOGIN';
$lang['l_rigster']='REGISTER';
$lang['l_login_t']='Login';
$lang['l_rigster_t']='Register';
$lang['auction_title']='Spend Your Points At EachBuyer Auction';
$lang['auction_keywords']='eachbuyer points, auction, unique lowest auction, bid, redeem, game, win, spend no money, no risk';
$lang['auction_description']='Use your points to redeem, bidding auction, game. You don\'t need to spend your money. No risk.';
$lang['l_auction_home']='Home';
$lang['l_auction_livechart']= 'Live Chat';
$lang['l_auction_welcome']= 'Welcome to EachBuyer !';
$lang['l_auction_rules']='Rules';
$lang['l_auction_more']= 'more';
$lang['l_auction_copy']= ' <span>' . ucfirst( COMMON_DOMAIN ) . '</span>. All Rights Reserved';
$lang['l_auction_ongoing']= 'ONGOING';
$lang['l_auction_history']= 'HISTORY';
$lang['l_auction_upcoming']= 'UPCOMING';
$lang['l_auction_shopping']= 'SHOPPING TO GAIN POINTS';
$lang['l_auction_retails']= 'Retails for';
$lang['l_auction_page_first']= 'First';
$lang['l_auction_page_last']= 'Last';
$lang['l_auction_ended']= 'Ended';
$lang['l_auction_win']= 'Winning Bid';
$lang['l_auction_won']= 'won';
$lang['l_auction_totalbid']= 'Total Bidders';
$lang['l_auction_bid']='Bid';
$lang['l_auction_start']='start';
$lang['l_auction_end']='end';
$lang['l_auction_result']='Auction Results';
$lang['l_auction_wonitfor']='won it for';
$lang['l_auction_logout']='logout';
//auction详情页
$lang['l_auction_sku']='SKU';
$lang['l_auction_timeleft']='Time Left';
$lang['l_auction_lbp']='Last Bid Price';
$lang['l_auction_lb']='Last Bidder';
$lang['l_auction_yp']='Your Price';
$lang['l_auction_ys_left']='You have spent';
$lang['l_auction_ys_right']='points on this auction';
$lang['l_auction_ys_pd']='Product Description';
$lang['l_auction_ys_ybh']='Your Bid History';
$lang['l_auction_login']='Please login';
$lang['l_auction_invalid']='Invalid bid'; //非法竞拍
$lang['l_auction_over']='I\'m sorry, the auction has ended.'; //竞拍已结束
$lang['l_auction_min_unique']='Congratulations, this is the current lowest unique bid. As a convenience to you, we\'ll send you an email if someone else\'s bid matches yours making it no longer unique.'; //出价唯一且最低
$lang['l_auction_nomin_unique']='Congratulations, your bid is definitely unique, but just a little bit higher than the current winning bid.You\'re definitely close, so make another bid!';
$lang['l_auction_nomin_nounique']='Sorry, this bid is not unique. Please make another bid.';
$lang['l_auction_price_exist']='You have already bid this price. Please choose another bid.'; //已经出过价了
$lang['l_auction_min_price_out']='Nicely done! You\'ve just ruined someone\'s chance of winning by matching their current lowest, unique bid.Now make another bid to win the auction!'; //挤掉了之前的最低价格
$lang['l_auction_pi']='Your points is less than 10. Need more points? Purchase from our thousands of products. Write reviews of our products.
';  //积分不够了
$lang['l_auction_dpf']='Sorry,deduct points fail.';
$lang['l_auction_chart_title']='Bids Distribution Graph';//统计图
$lang['l_auction_chart_y']='Bids';
//竞拍详情历史页面
$lang['l_auction_wb']='Winning Bid';
$lang['l_auction_winer']='Winner';
$lang['l_auction_st']='Start Time';
$lang['l_auction_ct']='Close Time';
$lang['l_auction_cabd']='Check All Bid Details';
$lang['l_auction_bdg']='Bids Distribution Graph';
$lang['l_start_left_time']='hours until this auction begins.';
$lang['l_auction_search']='Search';
$lang['l_auction_search_tips']='Price interval search';
$lang['l_auction_show']='show';
$lang['l_auction_price']='Price';
$lang['l_auction_nickname']='Nicknames';
$lang['l_auction_loading']='loading...';
//竞拍介绍
$lang['l_auction_01_q']='01.What type of auction is this?';
$lang['l_auction_01_a']='Unlike traditional auctions where the highest bidder wins, this is called a lowest unique bid auction.';
$lang['l_auction_02_q']='02.How do I win?';
$lang['l_auction_02_a']='The bidder with the lowest unique bid wins. By lowest unique bid we mean you win if your bid is unique in that no one else bid the same price and that is the lowest price bid.<br>  And in the rare instances where there is no lowest unique bid price then we look for the lowest price with the fewest bids and the person who bid first at that price is the winner!';
$lang['l_auction_03_q']='03.How do I play?';
$lang['l_auction_03_a']='Simply log in to your account, enter a bid in the bid box and submit. Thereafter you have a chance to win the item offered.';
$lang['l_auction_04_q']='04.How much does it cost to bid?';
$lang['l_auction_04_a']='Each bid costs an affordable 10 reward points.';
$lang['l_auction_05_q']='05.How do I get reward points?';
$lang['l_auction_05_a']='For every dollar you spend on purchases at ' . ucfirst( COMMON_DOMAIN ) . ', you earn one reward point. For every review you write and approved by us, you earn five reward points.';
$lang['l_auction_06_q']='06.Can I bid more than once?';
$lang['l_auction_06_a']='You can put in as many bids as your reward points afford you.';
$lang['l_auction_07_q']='07.How do I know that your auction is not fixed?';
$lang['l_auction_07_a']='After the auction is over, we publish all the bids made by participants. Bids are listed with participant\'s nicknames. Therefore you\'re able to see what you and your friends bid.';
$lang['l_auction_08_q']='08.What\'s the point of bidding when I will probably lose?';
$lang['l_auction_08_a']='If your bidding is unsuccessful, you can still use your used reward points towards the purchase of the auction item at our normal everyday low prices. This benefit expires 72 hours after the auction is over.';
$lang['l_auction_09_q']='09.What else do I need to know about this auction?';
$lang['l_auction_09_a']='After you make a bid, you will be told whether your bid is currently the lowest, whether it is currently unique, whether it is both currently the lowest and unique, or whether it is neither the lowest nor unique. Thus bidding requires strategy.';
$lang['l_auction_10_q']='10.What happens after the auction ends?';
$lang['l_auction_10_a']='You\'ll get an email from us telling you whether you\'ve won or not. You also can find your auction in My Auctions/Gifts to check the status.';

$lang['t_meta_keywords']='{$goods_name} - Time Limited Special Offer | ' . ucfirst( COMMON_DOMAIN );
$lang['t_meta_desc']='Buy cheap and high quality {$goods_name} at ' . ucfirst( COMMON_DOMAIN ) . ', Time Limited Special Offer';

//邮件订阅模块语言包
$lang['news_title']='Newsletter Subscription';
$lang['news_sub_title']='Select the categories you like and make sure that you only receive the newsletter you are interested in:';
$lang['chg_news_title']='Edit Subscriptions';
$lang['chg_news_sub_title']='Change the categories to get different deals you like:';
$lang['fashions']='Fashions';
$lang['home_and_garden']='Home & Garden';
$lang['electronics']='Electronics';
$lang['sub_confirm']='Subscribe & Get $3 NOW';
$lang['info1']='Newsletters will go out <span>once or twice</span> a week, and all deals you subscribed will be included in one newsletter. ';
$lang['info2']='Get <span>$3</span> Credit on any orders';
$lang['info3']='<span>Huge discounts</span> only for subscribers';
$lang['info4']='Exclusive campaigns for subscribers to win <span>big prizes</span>';
$lang['info5']='Always be the first to know <span>the latest news</span> from ' . ucfirst( COMMON_DOMAIN );
$lang['info6']='<span>Products recommendation</span> to shorten your time of searching';
$lang['info7']= ucfirst( COMMON_DOMAIN ) . ' is committed to protecting your privacy. Your email address will never be sold to a third party for any reason. See our Privacy Policy.';

$lang['email_last_step_title']='Just One More Step';
$lang['sub_success_title']='Subscribe Successful';
$lang['sub_success_info']='<strong>Coupon Code: <span style="color:#f00;">%s</span></strong><br /><strong>
$3 off all orders over $30, valid until 31st of December.</strong><br />Coupon details have been sent to your mailbox.<br />Thank you for subscribing to ' . ucfirst( COMMON_DOMAIN ) . ' newsletter, <br />The discounts and deals about <span style="color:#0083d6">%s</span> will be notified periodically to you via :%s';
$lang['no_category']='Subscribe failed! NO category had been selected.';
$lang['back_home']='Go back to ' . ucfirst( COMMON_DOMAIN );

$lang['update_success']='Update Successful!';
$lang['update_success_info']='We will send you the related newsletters according to your new categories.';

$lang['unsub_title']='Enter your email address to unsubscribe if you don’t want to receive any updates from ' . ucfirst( COMMON_DOMAIN ) . ': ';
$lang['unsub_info']='You will no longer receive any coupons or exclusive deals only for subscribers with this option. We do hope you can consider that to change your categories to try different deals before unsubscribing.';
$lang['unsub_button']='Unsubscribe';

$lang['unsub_success']='You have unsubscribed EachBuyer newsletter!';
$lang['unsub_success_info']='We hope you can enjoy the shopping at ' . ucfirst( COMMON_DOMAIN ) . '. Thank you. ';

$lang['user_sub_title']='Categories you subscribed:';
$lang['sub_msg']='Please select at least one category you like';
$lang['sub_email_exist']='Thank you for your subscription, but your email address has already on our newsletter sendout list. <br />If you want to change your interested categories, please go to <a href="%s"  style="color: #09318B;">your account</a> to make change or edit your subscription <a href="%s"  style="color: #09318B;">here</a>.<br />Please keep your $3 coupon for newsletter subscription:<br /><br />';
$lang['check_mail_success']=' A confirmation mail has been sent to verify the request of subscription. Please go to your mailbox and follow the instructions in the email to finish the last step.';
$lang['sub_success_info_short']='<strong>Coupon Code: <span style="color:#f00;">%s</span></strong><br /><strong>
$3 off all orders over $30, valid until 31st of December.</strong>';
$lang['sub_auction_reg_info']='Please go to your mailbox to verify your subscription and get $3 right now!';

//注册成功页。
$lang['regOk']['regOk']='Successful Registration.';
$lang['regOk']['congra']='Congratulations!';
$lang['regOk']['sucMsg']='Successful Registration.';
$lang['regOk']['regOkMsg']='In <span><em id="time">6</em></span> seconds, you will be returned to the previous page…or <a href="/">visit home page now</a>.';

//facebook login
$lang['facebook']['title'] = 'Your Facebook email address already in use in EachBuyer.';
$lang['facebook']['input_pwd'] = 'Please enter your passward.';
$lang['facebook']['other_email'] = 'Login with another email address.';
$lang['facebook']['error_pwd'] = 'Sorry, either your email or your password is incorrect.';
$lang['facebook']['error_have_email'] = 'There is already an account with this email address.';
$lang['facebook']['error_email'] = 'Please enter a valid email address.';
$lang['facebook']['l_reset_psw_sub'] = 'Submit';
$lang['facebook']['l_reset_psw_email'] = 'Email Address';


$lang['bbs_error_email_address2'] = 'Please enter a valid email address. For example johndoe@domain.com.';