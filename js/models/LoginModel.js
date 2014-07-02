define([
    'ModelExtend',
    'underscore',
    'backbone',
    'localstorage'
], function (Model, _, Backbone) {
    'use strict';

    return function () {
        Model.namespace = 'twitterLogin';

        Model.code = Backbone.Model.extend({
            localStorage: new Backbone.LocalStorage('aa_app_mod_twitter_' + _.aa.instance.i_id + '_TwLoginData'),

            defaults: {
                'id':          1,
                'twid':        '',
                'screen_name': '',
                'email':       '',
                'firstname':   '',
                'lastname':    '',
                'city':        '',
                'avatar':      '',
                'login_type':  'twuser',
                'logintime':   ''
            }
        });

        return Model;
    }
});