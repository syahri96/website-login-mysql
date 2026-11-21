<?php
require_once dirname(__DIR__) . '/src/bootstrap.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Facebook;

$providerName = $_GET['provider'] ?? '';
if (!in_array($providerName, ['google','facebook'], true)) { http_response_code(400); exit('Provider tidak dikenal.'); }
$settings = $config['oauth'][$providerName];
if (empty($settings['client_id']) || empty($settings['client_secret'])) {
    flash('error', 'Login ' . ucfirst($providerName) . ' belum dikonfigurasi oleh administrator.');
    redirect('login.php');
}
$options = ['clientId'=>$settings['client_id'],'clientSecret'=>$settings['client_secret'],'redirectUri'=>$settings['redirect_uri']];
$provider = $providerName === 'google' ? new Google($options) : new Facebook($options);
$provider->setScopes($providerName === 'google' ? ['openid','email','profile'] : ['email']);
$authUrl = $provider->getAuthorizationUrl();
$_SESSION['oauth_state'] = $provider->getState();
$_SESSION['oauth_provider'] = $providerName;
redirect($authUrl);
