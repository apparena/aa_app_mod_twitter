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
            localStorage: new Backbone.LocalStorage('AppArenaAdventskalenderApp_' + _.aa.instance.aa_inst_id + '_TwLoginData'),

            defaults: {
                'id':          1,
                'twid':        '',
                'screen_name': '',
                'email':       '',
                'firstname':   '',
                'lastname':    '',
                'city':        '',
                'avatar':      '',
                'login_type':  'twuser'
            }
        });

        return Model;
    }
})
;