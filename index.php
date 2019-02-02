<?php
// Include FB config file && User class
require_once 'facebook_config.php';
require_once 'class/class_user.php';

if (isset($accessToken)) {
    if (isset($_SESSION['facebook_access_token'])) {
        $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    } else {
        // Put short-lived access token in session
        $_SESSION['facebook_access_token'] = (string) $accessToken;
        
        // OAuth 2.0 client handler helps to manage access tokens
        $oAuth2Client = $fb->getOAuth2Client();
        
        // Exchanges a short-lived access token for a long-lived one
        $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
        $_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
        
        // Set default access token to be used in script
        $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    }
    
    // Redirect the user back to the same page if url has "code" parameter in query string
    if (isset($_GET['code'])) {
        header('Location: ./');
    }
    
    // Getting user facebook profile info
    try {
        $profileRequest = $fb->get('/me?fields=name,first_name,last_name,email,gender,locale,cover,picture.width(400).height(400)');
        $fbUserProfile = $profileRequest->getGraphNode()->asArray();
        $fbUserProfile['email'] = !empty($fbUserProfile['email'])?$fbUserProfile['email']:'';
        $fbUserProfile['link'] = !empty($fbUserProfile['link'])?$fbUserProfile['link']:'';
        $fbUserProfile['gender'] = !empty($fbUserProfile['gender'])?$fbUserProfile['gender']:'';
        $fbUserProfile['locale'] = !empty($fbUserProfile['locale'])?$fbUserProfile['locale']:'';
        $fbUserProfile['cover']['source'] = !empty($fbUserProfile['cover']['source'])?$fbUserProfile['cover']['source']:'';
    } catch (FacebookResponseException $e) {
        echo 'Graph returned an error: '.$e->getMessage();
        session_destroy();
        // Redirect user back to app login page
        header('Location: ./');
        exit;
    } catch (FacebookSDKException $e) {
        echo 'Facebook SDK returned an error: '.$e->getMessage();
        exit;
    }
    
    // Initialize User class
    $user = new User();
    
    // Insert or update user data to the database
    $fbUserData = array(
        'oauth_provider' => 'facebook',
        'oauth_uid' => $fbUserProfile['id'],
        'first_name' => $fbUserProfile['first_name'],
        'last_name' => $fbUserProfile['last_name'],
        'email'  => $fbUserProfile['email'],
        'gender' => $fbUserProfile['gender'],
        'locale' => $fbUserProfile['locale'],
        'cover' => $fbUserProfile['cover']['source'],
        'picture' => $fbUserProfile['picture']['url'],
        /**
         * Getting profile URL isn't work anymore since the abuse of that, see the link:
         * https://developers.facebook.com/blog/post/2018/04/19/facebook-login-changes-address-abuse/
         */
        'link' => $fbUserProfile['link']
    );
    $userData = $user->checkUser($fbUserData);
    
    // Put user data into session
    $_SESSION['userData'] = $userData;
    
    // Get logout url
    $logoutURL = $helper->getLogoutUrl($accessToken, $redirectURL.'logout.php');
    
    // Render facebook profile data
    if (!empty($userData)) {
        $output  = '<h2 style="color:#999999;">Facebook Profile Details</h2>';
        $output .= '<div style="position: relative;">';
        $output .= '<img src="'.$userData['cover'].'" />';
        $output .= '<img style="position: absolute; top: 90%; left: 25%;" src="'.$userData['picture'].'"/>';
        $output .= '</div>';
        $output .= '<br/>Facebook ID : '.$userData['oauth_uid'];
        $output .= '<br/>Name : '.$userData['first_name'].' '.$userData['last_name'];
        $output .= '<br/>Email : '.$userData['email'];
        $output .= '<br/>Gender : '.$userData['gender'];
        $output .= '<br/>Locale : '.$userData['locale'];
        $output .= '<br/>Logged in with : Facebook';
        $output .= '<br/>Profile Link : <a href="'.$userData['link'].'" target="_blank">Click to visit Facebook page</a>';
        //$output .= '<br/>Logout from <a href="'.$logoutURL.'">Facebook</a>';
        $output .= '<br/>Logout <a href="logout.php">Facebook</a>';
    } else {
        $output = '<h3 style="color:red">Some problem occurred, please try again.</h3>';
    }
    
} else {
    // Get login url
    $loginURL = $helper->getLoginUrl($redirectURL, $fbPermissions);
    
    // Render facebook login button
    $output = '<a href="'.htmlspecialchars($loginURL).'"><img src="image/sign-in-with-facebook.svg" alt="sign-in-with-facebook" /></a>';
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login with Facebook using PHP by carry0987</title>
</head>

<body>
    <div><?php echo $output; ?></div>
</body>
</html>