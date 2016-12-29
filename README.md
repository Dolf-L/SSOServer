SSO Server
===

### Installation
Require this package in your composer.json and run composer update.
```
    "require": {
        "dolf/ssoserver": "dev"
    }
    "repositories": [
        {
            "type": "package",
            "package": {
                "name": "dolf/sso",
                "version":"dev",
                "dist": {
                    "url": "https://github.com/Dolf-L/packages/tree/master/dolf/ssoserver",
                }
            }
        }
    ]
```
###Configuration
Add to .env
```
    API_KEY = ...
    SERVER_URL = ...
    BROKER_URL = ...
```

###Usage
Look at examples