<?php
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', '..' . DS . '..' . DS . '..' . DS);
define('MODULE_PATH', 'modules' . DS . 'aa_app_mod_twitter');

$session = unserialize($_COOKIE['aa_twitter_auth_' . $_GET['i_id']]);
setcookie('aa_twitter_auth_' . $_GET['i_id'], null, time() - 3600, '/', $_SERVER['HTTP_HOST'], isset($_SERVER["HTTPS"]), true);
unset($_COOKIE['aa_twitter_auth_' . $_GET['i_id']]);

define('TW_CONSUMER_KEY', $session['tw_consumer_key_config']);
define('TW_CONSUMER_SECRET', $session['tw_consumer_secret_config']);

// create default return statement
$return = array(
    'code'    => 0,
    'status'  => 'error',
    'message' => ''
);

try
{
// load codebird library and create an instance
    require_once 'codebird.php';
    \Codebird\Codebird::setConsumerKey(TW_CONSUMER_KEY, TW_CONSUMER_SECRET);
    $cb = \Codebird\Codebird::getInstance();

//if (isset($_GET['oauth_verifier']) && isset($session['oauth_verify']))
    if (isset($_GET['oauth_token']) && isset($_GET['oauth_verifier']))
    {
        // the user accepted the app auth dialog and we just returned from twitter...
        $session['oauth_token']    = $_GET['oauth_token'];
        $session['oauth_verifier'] = $_GET['oauth_verifier'];

        $cb->setToken($session['oauth_token'], $session['oauth_token_secret']);

        $user = $cb->oauth_accessToken(array(
            'oauth_verifier' => $session['oauth_verifier']
        ));

        if (is_object($user))
        {
            $username = $user->screen_name;
            $cb->setToken($user->oauth_token, $user->oauth_token_secret);
            $reply = $cb->users_show(array('screen_name' => $username));

            $return['user']   = $reply;
            $return['status'] = 'success';
            $return['code']   = '200';
        }
    }
}
catch (Exception $e)
{
    echo '<pre>';
    print_r($e->getMessage());
    echo '</pre>';
    exit();
}

echo '<script>window.opener._.twitterReturn.getUserData("' . addslashes(json_encode($return)) . '");</script>';