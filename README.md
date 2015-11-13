# Keypic
--------------------------------

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/c27c4551-ad2b-4621-b32d-c84e4bee6489/mini.png)](https://insight.sensiolabs.com/projects/c27c4551-ad2b-4621-b32d-c84e4bee6489)

This library provides a wrapper to use [KeyPic][keypic_site] web service for spam detection, 
by using any [PSR-7][psr7_fig] compliant request (`RequestInterface` objects).

## How to Install
You can install this library with [Composer][composer]. Drop this into your `composer.json`
manifest file:

    {
        "require": {
            "zrashwani/key-pic": "dev-master"
        }
    }
	
Then run `composer install`.

## How to use
To use keypic web service with any request that implements [PSR-7][psr7_fig]:

```php
require 'vendor/autoload.php';
use Zrashwani\KeyPic\KeyPic;

$keypicObj = new KeyPic($psrRequest); //initiate and configure keypic object
$keypicObj = $keypicObj->setFormID('YOUR_KEYPIC_FORM_ID') //set form ID
                       ->setDebug(true) // set debug mode
                       ->setTokenInputName("keypic_token"); //hidden input name
$token = $keypicObj->getToken();

if($psrRequest->getMethod() == "POST"){
    $data         = $psrRequest->getParsedBody();    
    $email        = $data['EMAIL_INPUT'];
    $username     = $data['NAME_INPUT'];
    $message      = $data['MESSAGE_INPUT'];
    
    //Detect if entry is Spam? from 0% to 100%
    $spam = $keypicObj->isSpam($email, $username, $message);
    if($spam === false){
       echo "Cannot determine spam percentage.";
    }elseif($spam > 60){ //if spam percentage larger than certain number
        echo "user and/or submitted data seems spammy, spam percentage = ".$spam;
    }else{
        echo "Not Spam";
    }
}
```

In your form, place the call to `renderHtml()` method to render pixel image or javascript, along with keypic hidden token field as following:
```html
<form action="" method="post">
    <!-- Your form elements here -->
    <?php echo $keypicObj->renderHtml(); ?>
</form>
```
## How to Contribute

1. Fork this repository
2. Create a new branch for each feature or improvement
3. Send a pull request from each feature branch

It is important to separate new features or improvements into separate feature branches,
and to send a pull request for each branch.

All pull requests must adhere to the [PSR-2 standard][psr2].

## System Requirements

* PHP 5.4.0+


## License

MIT Public License


[keypic_site]: http://keypic.com
[psr2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[composer]: http://getcomposer.org/
[psr7_fig]: http://www.php-fig.org/psr/psr-7/
