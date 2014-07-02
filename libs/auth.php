<?php
defined('_VALID_CALL') or die('Direct Access is not allowed.');
define('MODULE_PATH', 'modules' . DS . 'aa_app_mod_twitter');

require_once ROOT_PATH . DS . MODULE_PATH . DS . 'libs' . DS . 'codebird.php';

try
{
    \Codebird\Codebird::setConsumerKey(TW_CONSUMER_KEY, TW_CONSUMER_SECRET);

    $cb = \Codebird\Codebird::getInstance();

    $session = array();
    if (!empty($_SESSION['twitter']))
    {
        $session = $_SESSION['twitter'];
    }
    else
    {
        $_SESSION['twitter'] = array();
    }

    if (empty($session['oauth_verify']))
    {
        $session = array();
        unset($_SESSION['twitter']);
    }

    // we need this for callback
    $session['tw_consumer_key']    = __c('tw_consumer_key');
    $session['tw_consumer_secret'] = __c('tw_consumer_secret');

    if (!isset($session['oauth_token2']))
    {
        // get the request token
        $host = $_SERVER['HTTP_HOST'];
        $uri  = str_replace('ajax', MODULE_PATH . '/libs/auth_callback.php', $_SERVER['REQUEST_URI']);

        $reply = $cb->oauth_requestToken(array(
            'oauth_callback' => 'https://' . $host . $uri . '?i_id=' . \Apparena\App::$i_id
        ));

        if ($reply->httpstatus !== 401)
        {
            // store the token
            $cb->setToken($reply->oauth_token, $reply->oauth_token_secret);
            $session['oauth_token']        = $reply->oauth_token;
            $session['oauth_token_secret'] = $reply->oauth_token_secret;
            $session['oauth_verify']       = true;

            // redirect to auth website
            $auth_url = $cb->oauth_authorize();

            $return['code']   = '203';
            $return['call']   = $auth_url;
            $return['status'] = 'success';

            if (defined('ENV_MODE') && ENV_MODE === 'dev')
            {
                $return['debug_session'] = $session;
            }

            //$_SESSION['twitter'] = $session;
            $session['tw_consumer_key_config']    = __c('tw_consumer_key');
            $session['tw_consumer_secret_config'] = __c('tw_consumer_secret');
            setcookie('aa_twitter_auth_' . \Apparena\App::$i_id, serialize($session), 0, '/', $_SERVER['HTTP_HOST'], isset($_SERVER["HTTPS"]), true);
        }
        else
        {
            $return['message'] = $reply->message;
            $return['code']    = $reply->httpstatus;
        }
    }
    // assign access token on each page load
    //$cb->setToken($session['oauth_token'], $session['oauth_token_secret']);
    //$cb->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);
}
catch (Exception $e)
{
    // prepare return data
    $return['code']    = $e->getCode();
    $return['status']  = 'error';
    $return['message'] = $e->getMessage();
    $return['trace']   = $e->getTrace();
}