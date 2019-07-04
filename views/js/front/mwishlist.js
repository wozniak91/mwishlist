var ajaxWishlist;

$(document).ready(function() {
    ajaxWishlist = new Wishlist('wishlist__icon', 'wishlist__counter');
});

$(document).on('click', '.wishlist__button', function() {
    var id_product = $(this).attr('data-id-product');
    ajaxWishlist.toggle(id_product, $(this));
});

$(document).on('click', '.wishlist-product__remove', function() {
    var id_product = $(this).attr('data-id-product');
    ajaxWishlist.remove(id_product, $(this));
});

var Wishlist = function(iconClass, counterClass) {
    this.productsNb = 0;
    this.counter = $('.' + counterClass);
    this.icon = $('.' + iconClass);
};

Wishlist.prototype.add = function(id_product) {
    
    var _this = this;

    $.ajax({
        type: 'POST',
        url: wishlist_url,
        data: {
            'ajax': 1,
            'id_product': id_product,
            'action': 'addproduct'
        },
        dataType: 'json',
        success: function(json) {
            if(!json.hasError) {
                _this.productsNb = json.wishlist.productsNb;
                _this.refresh();
            }
        }
      });
}

Wishlist.prototype.remove = function(id_product, el = false) {
    var _this = this;
    $.ajax({
        type: 'POST',
        url: wishlist_url,
        data: {
            'ajax': 1,
            'id_product': id_product,
            'action': 'removeproduct'
        },
        dataType: 'json',
        success: function(json) {
            if(!json.hasError) {
                _this.productsNb = json.wishlist.productsNb;
                _this.refresh();
                el.parent().parent().fadeOut().delay(500).remove();

            }
        }
      });
}

Wishlist.prototype.toggle = function(id_product, el = false) {
    var _this = this;
    $.ajax({
        type: 'POST',
        url: wishlist_url,
        data: {
            'ajax': 1,
            'id_product': id_product,
            'action': 'toggleproduct'
        },
        dataType: 'json',
        success: function(json) {
            if(!json.hasError) {
                _this.productsNb = json.wishlist.productsNb;
                _this.refresh();
                if(el) {
                    el.toggleClass('wishlist__button--liked');
                }
            }
        }
      });
}

Wishlist.prototype.refresh = function() {
    this.counter.text(this.productsNb);
}