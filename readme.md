# App-Arena.com App Module: Twitter
Github: https://github.com/apparena/aa_app_mod_twitter
Docs: http://www.appalizr.com/index.php/twitter.html

This is a module of the [aa_app_template](https://github.com/apparena/aa_app_template)

## Module job
Handles all interactions with the Twitter API.
Login, Share, Friend Selector, Send, OpenGraph Posts

### Dependencies
* Nothing

### Important functions
* **share** - shares information from the model over an popup that calls the twitter API.
	* **elem** - Botton DOM element
	* **share_infos** - JSON element with additional share infos (title, desc, short_url)
* **login** - start facebook login process
    * **scope** - FB login scope
    * **callback** - callback function
* **follow** - follow button callback function definition
	* **callback** - callback function that will be fired after a click on the follow button
* **login** - open a popup with login information over the twitter API
* **getUserData** - get userdata from twitter after login (automatically called)

### Examples
#### initialize follow button without callback
```javascript
require(['modules/aa_app_mod_twitter/js/views/TwitterView'], function (Twitter) {
    var twitter = Twitter().init({init: true});
    twitter.libInit();
});
```
This calls the twitter API to activate the DOM part to change that into a real twitter follow button with fan information.

#### initialize follow button with callback function
```javascript
require([
    'modules/aa_app_mod_twitter/js/views/TwitterView'
], function (Twitter) {
    twitter = Twitter().init({init: true});

    twitter.libInit();
    twitter.follow(function (response) {
            that.saveAsFan({
                target: {
                    className: 'fangate_btn_twitter'
                }
            });
        }
    );
});
```
That will save the current user as fan in the local storage, that the fangate not open again on the next page refresh.

Needed DOM element:
```html
<div class="twitter_fan_btn clearfix">
    <a href="https://twitter.com/<%- _.c('share_twitter_id')%>" class="twitter-follow-button fangate_btn_twitter" data-show-count="true" data-lang="<%- _.t('share_lang') %>">@<%= _.c('share_twitter_id') %><%= _.t('follow') %></a>
</div>
```

#### login
```javascript
require([
    'modules/aa_app_mod_twitter/js/views/TwitterView',
    'modules/aa_app_mod_twitter/js/models/LoginModel'
], function (Twitter, LoginModel) {
    that.twitter = Twitter().init({init: true});
    that.twitterLoginModel = LoginModel().init();
    that.listenTo(that.twitterLoginModel, 'change:logintime', that.twLoginDone);
});
```
Add the css class **.twconnect** to your login button. THat activate a click listener to the lofin function.

#### share button
```javascript
var that = this;
require([
    'modules/aa_app_mod_twitter/js/views/TwitterView',
	'modules/aa_app_mod_share/js/models/ShareInfosModel'
], function (Twitter, ShareInfoModel) {
	that.shareInfos = ShareInfoModel().init({init: true});
    Twitter().init({init: true}).share($(.twshare), that.shareInfos.attributes);
});
```
Easier is to call the twShare function from the aa_app_model_share module. To do this, define an element with a twshare class and initialize the module.

#### App-Manager config values
| config | default | description |
|--------|--------|--------|
| tw_consumer_key | empty | your own twitter API consumer key |
| tw_consumer_secret | empty | your own twitter API secret |
| share_twitter_id | empty | your twitter account name |

#### App-Manager locale values
| locale | value example |
|--------|--------|
| share_lang | de |