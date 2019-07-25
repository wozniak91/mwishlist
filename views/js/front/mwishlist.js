var ajaxWishlist;

$(document).ready(function() {
    ajaxWishlist = new Wishlist('wishlist__icon', 'wishlist__counter');
});

$(document).on('click touchstart', '.wishlist__button', function() {
    var id_product = $(this).attr('data-id-product');
    ajaxWishlist.toggle(id_product, $(this));
});

$(document).on('click touchstart', '.wishlist-product__remove', function() {
    var id_product = $(this).attr('data-id-product');
    ajaxWishlist.remove(id_product, $(this));
});

$(document).on('click touchstart', '.wishlist-product__add-to-cart', function() {
    var id_product = $(this).attr('data-id-product');
    ajaxWishlist.addToCart(id_product);
});

$(document).on('click touchstart', '.wishlist-product__all-to-cart', function() {
    $('.wishlist-product__add-to-cart').each(function() {
        var id_product = $(this).attr('data-id-product');
        ajaxWishlist.addToCart(id_product, true);
    });
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
                if(el) {
                    $('#wishlist_product_' + id_product).fadeOut();
                }

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
                    $('#wishlist_info_' + id_product).text(json.msg);
                }
            }
        }
      });
}

Wishlist.prototype.addToCart = function(id_product, reload = false){

    var _this = this;

    $.ajax({
        type: 'POST',
        headers: { "cache-control": "no-cache" },
        url: baseUri + '?rand=' + new Date().getTime(),
        async: true,
        cache: false,
        dataType : "json",
        data: 'controller=cart&add=1&ajax=true&qty=1&id_product=' + id_product + '&token=' + static_token,
        success: function(jsonData)
        {   
            if(!reload) {
                if (!jsonData.hasError) {
                    ajaxCart.updateCartInformation(jsonData, false);
    
                    $(jsonData.products).each(function(){
                        if (this.id != undefined && this.id == parseInt(id_product))
                            ajaxCart.updateLayer(this);			
                    });
                    _this.remove(id_product, $('.wishlist-product__remove[data-id-product="' + id_product + '"]'));
                } else {
                    ajaxCart.updateCart(jsonData);
                }
            } else {
                _this.remove(id_product, $('.wishlist-product__remove[data-id-product="' + id_product + '"]'));
                window.location = window.location.origin + "?controller=cart";
            }

        }
    });
}

Wishlist.prototype.refresh = function() {
    if(!this.productsNb) {
        $('.wishlist-product__all-to-cart').fadeOut();
        $('.wishlist-alert').fadeIn();
    } else {
        $('.wishlist-product__all-to-cart').fadeIn();
        $('.wishlist-alert').fadeOut();
    }
    this.counter.text(this.productsNb);

}