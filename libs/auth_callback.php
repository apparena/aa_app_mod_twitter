<?php
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', '..' . DS . '..' . DS . '..' . DS);
define('MODULE_PATH', 'modules' . DS . 'aa_app_mod_twitter');

require_once ROOT . 'init.php';

// check twitter sesion
//session_start();
$session = array();
if (!empty($_SESSION['twitter']))
{
    $session = $_SESSION['twitter'];
}
global_escape();

// create default return statement
$return = array(
    'code'    => 0,
    'status'  => 'error',
    'message' => ''
);

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
        )
    );

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

//$_SESSION['twitter'] = $session;

//echo '<script>window.opener.aa.auth.twitter_popup_callback("' . escape(json_encode($return)) . '");</script>';
echo '<script>window.opener._.singleton.view.twitter.getUserData("' . addslashes(json_encode($return)) . '");</script>';