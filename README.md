# Keypic

This library provide composer ready library to use [KeyPic][keypic_site] web service to spam detection, by using PSR-7 compliant requests.


## How to use
To use keypic web service with any request that implements PSR-7:

```$keypicObj = new KeyPic\KeyPic($psrRequest);
   $keypicObj->setFormID('FORM_KEY_FROM_KEYPIC');
   $Token = $keypicObj->getToken('');

   $spam = $keypicObj->isSpam($Token, 'USER_EMAIL_VALUE', 'USER_NAME_VALUE');

  if($spam){
      echo "SPam detected";
  }
```

[keypic_site]: http://keypic.com