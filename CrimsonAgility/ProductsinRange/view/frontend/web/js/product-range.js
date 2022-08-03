define([
        'jquery',
        'uiComponent',
        'mage/validation',
        'mage/storage',
        'mage/translate',
        'ko'
    ], function ($, Component, validation, storage, $t, ko) {
        'use strict';

        var products= ko.observableArray([]);

        return Component.extend({
            defaults: {
                template: 'CrimsonAgility_ProductsinRange/product-range'
            },
            products: ko.observableArray([]),
            errorMessage: ko.observable(''),
            prodCount: ko.observable(0),
            low: ko.observable(0),
            high: ko.observable(1),

            initialize: function () {
                this._super();
            },
            fieldCheck: function(){
                let self = this;
                let maxTimes = 5;
                let max = 5;
                
                //High must never be lower than low
                if(Number(self.low()) > Number(self.high())){
                    //self.setError('Price high cannot be less than price low.');
                    self.high(Number(self.low()) + 1);
                }

                //High must never be beyond x times low
                if(Number(self.low()) > 0){
                    max = (Number(self.low()) * maxTimes);
                }
                if(Number(self.high()) > max){
                    self.setError('Price high cannot be greater than '+ maxTimes +'x than low price.');
                    self.high(max);
                }
            },
            save: function (saveForm) {
                var self = this;

                 var data = {},
                     formDataArray = $(saveForm).serializeArray();

                formDataArray.forEach(function (entry) {
                    data[entry.name] = entry.value;
                });

                if($(saveForm).validation()
                    && $(saveForm).validation('isValid')
                ) {
                    self.communication(data, products).always(function() {
                        console.log(products());
                    });
                }
            },
            communication: function (data, products) {
                var self = this;
                return storage.post(
                    'rest/V1/catalog/prange',
                    JSON.stringify(data),
                    false
                ).done(
                    function (response) {
                        if (response && !response['error']) {
                            products([]);
                            self.setError('');//clear error messages if any
                            $.each(response, function (i, v) {
                                products.push(v);
                            });
                            self.prodCount(response.length);
                        }
                        else{
                            self.prodCount(0);
                            self.setError(response['message']);
                        }
                    }
                ).fail(
                    function (response) {
                    }
                );
            },
            getProducts: function () {
                return products;
            },
            setError: function(message){
                this.errorMessage(message);
            }
        });
    }
);

