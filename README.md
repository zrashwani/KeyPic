# Keypic
=======================

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

$keypicObj = new \Zrashwani\KeyPic\KeyPic($psrRequest); //initiate and configure your keypic object
$keypicObj = $keypicObj->setFormID('YOUR_KEYPIC_FORM_ID') //set form ID
                       ->setDebug(true) // set debug mode
                       ->setTokenInputName("keypic_token"); //hidden input name
$token = $keypicObj->getToken("");

if($psrRequest->getMethod() == "POST"){
    $data         = $psrRequest->getParsedBody();    
    $email        = $data['EMAIL_INPUT'];
    $username     = $data['NAME_INPUT'];
    $message      = $data['MESSAGE_INPUT'];
    
    $spam = $keypicObj->isSpam($email, $username, $message);
    if($spam){
          echo "Keypic spam percentage = ".$spam;
    }else{
           echo "Spam value is empty";
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