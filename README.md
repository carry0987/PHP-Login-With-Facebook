# PHP-Login-With-Facebook
Login with Facebook account via PHP &amp; Mysqli
![Login-With-Facebook-By-carry0987](./image/sign-in-with-facebook.svg)

## Usage
If you don't have `Facebook API Client`, just go here to generate yours:
https://developers.facebook.com/apps/

Then enter the `AppID` &amp; `AppSecret` [here](https://github.com/carry0987/PHP-Login-With-Facebook/blob/master/facebook_config.php#L16-L18), and also set your `Redirect-URL`:
```php
$appId = 'InsertAppID';
$appSecret = 'InsertAppSecret';
$redirectURL = 'https://localhost/facebook_login_with_php/';