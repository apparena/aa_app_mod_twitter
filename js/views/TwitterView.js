require.config({
    paths: {
        'twitter': '//platform.twitter.com/widgets'
    },
    shim:  {
        'twitter': {
            exports: 'twttr'
        }
    }
});

define([
    'ViewExtend',
    'jquery',
    'underscore',
    'backbone',
    'modules/twitter/js/models/LoginModel'
], function (View, $, _, Backbone, LoginModel) {
    'use strict';

    return function () {
        View.namespace = 'twitter';

        View.code = Backbone.View.extend({
            el: $('body'),

            apiUrl: 'http://cdn.api.twitter.com/1/urls/count.json',

            tweetUrl: 'https://twitter.com/intent/tweet',

            events: {},

            initialize: function () {
                _.bindAll(this, 'libInit', 'share', 'follow', 'addClickEventListener', 'login', 'getUserData');

                // we must store the require config value into the global scope for build process
                this.twitter = 'twitter';
                this.model = LoginModel().init();
            },

            libInit: function () {
                //Once twttr is ready, bind callback functions to the tweet event
                require([this.twitter], function (twttr) {
                    twttr.ready(function (twttr) {
                        // reload twitter buttons to add functions
                        twttr.widgets.load();

                        // callback function on the follow button
                        twttr.events.bind('follow', function (event) {
                            //_.debug.log(event);
                        });
                    });
                });
                return this;
            },

            share: function (elem, share_infos) {
                var redirection,
                // Use current page URL as default link
                //url = encodeURIComponent(elem.attr('data-url') || _.aa.instance.share_url),
                    url = encodeURIComponent(share_infos.short_url),
                // Use page title as default tweet message
                //text = elem.attr('data-text') || document.title,
                    text = share_infos.title + ' - ' + share_infos.desc,
                    via = elem.attr('data-via') || '',
                    related = encodeURIComponent(elem.attr('data-related')) || '',
                //hashtags = encodeURIComponent(elem.attr('data-hashtag')) || '',
                    hashtags = '',
                    lang = elem.attr('data-lang') || 'en',
                    width = 575,
                    height = 400,
                    left = ($(window).width() - width) / 2,
                    top = ($(window).height() - height) / 2,
                    opts = 'status=1' +
                        ',width=' + width +
                        ',height=' + height +
                        ',top=' + top +
                        ',left=' + left;

                redirection = this.tweetUrl +
                    '?hashtags=' + hashtags +
                    '&original_referer=' + encodeURIComponent(document.location.href) +
                    '&related=' + related +
                    '&source=tweetbutton' +
                    '&text=' + text +
                    '&url=' + url +
                    '&lang=' + lang;
                if (via !== '') {
                    redirection += '&via=' + via;
                }
                window.open(redirection, 'twitter', opts);
            },

            follow: function (callback) {
                require([this.twitter], function (twttr) {
                    twttr.ready(function (twttr) {
                        // add callback function on the follow button
                        twttr.events.bind('follow', function (event) {
                            //_.debug.log(event);
                            callback.apply();
                        });
                    });
                });
            },

            addClickEventListener: function () {
                // add click event listener on FB login buttons
                var that = this,
                    connect = $('.twconnect');
                connect.off('click');
                connect.on('click', function () {
                    that.login();
                });
            },

            login: function () {
                var that = this,
                    width = 575,
                    height = 400,
                    left = ($(window).width() - width) / 2,
                    top = ($(window).height() - height) / 2,
                    opts = 'status=1' +
                        ',width=' + width +
                        ',height=' + height +
                        ',top=' + top +
                        ',left=' + left;

                this.ajax({
                    module: 'twitter',
                    action: 'auth'
                }, true, function (resp) {
                    if (resp.data.code === '203') {
                        that.twitter_window = window.open(resp.data.call, 'twitter_auth', opts);
                    } else {
                        _.debug.warn('twitter login response error', resp);
                    }
                });

                return this;
            },

            getUserData: function (response) {
                response = $.parseJSON(response);

                try {
                    this.twitter_window.close();
                } catch (e) {
                    _.debug.warn('error, can\'t close twitter popup window');
                    _.debug.warn(e);
                }

                if (response.code === '200') {
                    var data = this.model.toJSON(),
                        name;

                    response = response.user;

                    if (typeof( response.id ) !== 'undefined' && parseInt(response.id, 10) > 0) {
                        data.twid = parseInt(response.id, 10);
                    }
                    if (typeof( response.screen_name ) !== 'undefined' && response.screen_name.length > 0) {
                        data.screen_name = response.screen_name;
                        data.email = response.screen_name + '@twitter.com';
                    }
                    if (typeof( response.name ) !== 'undefined' && response.name.length > 0) {
                        name = response.name.split(' ');
                        if (_.isUndefined(name[0]) === false) {
                            data.firstname = name[0];
                        }
                        if (_.isUndefined(name[1]) === false) {
                            data.lastname = name[1];
                        }
                    }
                    if (typeof( response.location ) !== 'undefined' && response.location.length > 0) {
                        data.city = response.location;
                    }
                    if (typeof( response.profile_image_url_https ) !== 'undefined' && response.profile_image_url_https.length > 0) {
                        data.avatar = response.profile_image_url_https;
                    }
                    this.model.set(data);
                    this.model.save();
                } else {
                    _.debug.error('login error', response);
                }

                return this;
            }
        });

        return View;
    }
});