/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 define([
 	'jquery',
 	'mage/translate',
 	'jquery/ui',
 	'Magento_Catalog/js/catalog-add-to-cart',
  'Ves_All/lib/fancybox/jquery.fancybox.pack'
  ], function($, $t) {
   "use strict";
   $.widget('ves.catalogAddToCart', $.mage.catalogAddToCart,{
    options: {
      processStart: null,
      processStop: null,
      bindSubmit: true,
      minicartSelector: '[data-block="minicart"]',
      messagesSelector: '[data-placeholder="messages"]',
      productStatusSelector: '.stock.available',
      addToCartButtonSelector: '.action.add-to-cart',
      addToCartButtonDisabledClass: 'disabled',
      addToCartButtonTextWhileAdding: $t('Adding...'),
      addToCartButtonTextAdded: $t('Added'),
      addToCartButtonTextDefault: $t('Add to Cart')

    },
    _create: function() {
      if (this.options.bindSubmit) {
        this._bindSubmit();
      }
    },

    _bindSubmit: function() {
      var self = this;
      this.element.on('submit', function(e) {
        e.preventDefault();
        self.submitForm($(this));
      });
    },

    isLoaderEnabled: function() {
      return this.options.processStart && this.options.processStop;
    },

    submitForm: function(form) {
      var self = this;
      if (form.has('input[type="file"]').length && form.find('input[type="file"]').val() !== '') {
        self.element.off('submit');
        form.submit();
      } else {
        self.ajaxSubmit(form);
      }
    },
    ajaxSubmit: function(form) {
     var self = this;
     $(self.options.minicartSelector).trigger('contentLoading');
     self.disableAddToCartButton(form);
     var url = form.attr('action');
     $.ajax({
      url: url,
      data: form.serialize(),
      type: 'post',
      dataType: 'json',
      beforeSend: function() {
       if (self.isLoaderEnabled()) {
        $('body').trigger(self.options.processStart);
      }
      $('body').append("<div id='fancybox-loading'><div></div></div>");
      $('#ajax_loader').show();
    },
    success: function(res) {

     jQuery('#ajax_loader').hide();
     jQuery('body #fancybox-loading').remove();

     if(res.html){
      jQuery.fancybox({
       content: res.html,
       helpers: {
        overlay: {
         locked: false
       }
     }
   });
    }
    $(self.options.minicartSelector).trigger('contentUpdated');

    if (self.isLoaderEnabled()) {
      $('body').trigger(self.options.processStop);
    }

    if (res.backUrl) {
      window.location = res.backUrl;
      return;
    }
    if (res.messages) {
      $(self.options.messagesSelector).html(res.messages);
    }
    if (res.minicart) {
      $(self.options.minicartSelector).replaceWith(res.minicart);
      $(self.options.minicartSelector).trigger('contentUpdated');
    }
    if (res.product && res.product.statusText) {
      $(self.options.productStatusSelector)
      .removeClass('available')
      .addClass('unavailable')
      .find('span')
      .html(res.product.statusText);
    }
    self.enableAddToCartButton(form);
  }
});
   },
   disableAddToCartButton: function(form) {
    var addToCartButton = $(form).find(this.options.addToCartButtonSelector);
    addToCartButton.addClass(this.options.addToCartButtonDisabledClass);
    addToCartButton.attr('title', this.options.addToCartButtonTextWhileAdding);
    addToCartButton.find('span').text(this.options.addToCartButtonTextWhileAdding);
  },

  enableAddToCartButton: function(form) {
   var self = this,
   addToCartButton = $(form).find(this.options.addToCartButtonSelector);

   addToCartButton.find('span').text(this.options.addToCartButtonTextAdded);
   addToCartButton.attr('title', this.options.addToCartButtonTextAdded);

   setTimeout(function() {
     addToCartButton.removeClass(self.options.addToCartButtonDisabledClass);
     addToCartButton.find('span').text(self.options.addToCartButtonTextDefault);
     addToCartButton.attr('title', self.options.addToCartButtonTextDefault);
   }, 1000);
 }
})
   return $.ves.catalogAddToCart;
 });
