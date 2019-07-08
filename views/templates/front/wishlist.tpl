<div class="my-wishlist">
    <h1 class="page-heading">{l s='My wishlist' mod='mwishlist'}</h1>
    <div class="my-wishlist__actions"{if !$wishlist->productsNb} style="display: none"{/if}>
        <button class="btn btn-default wishlist-product__all-to-cart">
            <i class="fa fa-basket fa-fw"></i>
            {l s='Add all to cart' mod='mwishlist'}
        </button>
    </div>

    <div class="alert alert-danger  wishlist-alert" {if $wishlist->productsNb} style="display: none"{/if}>
        {l s='No products for this time.' mod='mwishlist'}
    </div>

    <div class="my-wishlist__wrapper">
        {foreach from=$wishlist->products item=product}
            <article class="wishlist-product row">
                <div class="wishlist-product__image col-md-3 col-xs-6">
                    <a class="wishlist-product__link" href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}">
                        <img class="img-responsive" src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'large_default')|escape:'html':'UTF-8'}" alt="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}"/>
                    </a>
                </div>
                <div class="wishlist-product__wrapper col-md-7 col-xs-5">
                    <a class="wishlist-product__link" href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}">
                        <h3 class="wishlist-product__name">{$product.name}</h3>
                    </a>
                    <div class="row">
                        {if (!$PS_CATALOG_MODE && ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
                        <div class="col-md-3 col-xs-3 wishlist-product__price">
                            <h5 class="wishlist-product__label">{l s='Price: ' mod='mwishlist'}</h5>
                            {if isset($product.show_price) && $product.show_price && !isset($restricted_country_mode)}
                                <span class="price {if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0} product-price-new{/if}">ab 
                                    {if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
                                </span>
                                {if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
                                    {hook h="displayProductPriceBlock" product=$product type="old_price"}
                                    <span class="price old-price">
                                        {displayWtPrice p=$product.price_without_reduction}
                                    </span>
                                    {hook h="displayProductPriceBlock" id_product=$product.id_product type="old_price"}
                                {/if}
                                {hook h="displayProductPriceBlock" product=$product type="price"}
                                {hook h="displayProductPriceBlock" product=$product type="unit_price"}
                            {/if}
                        </div>
                        {/if}
                        <div class="col-md-3 col-xs-3 wishlist-product__delivery">
                            <h5 class="wishlist-product__label">{l s='Delivery time: ' mod='mwishlist'}</h5>
                            {hook h='DisplayDeliveryTime' id_supplier=$product.id_supplier}
                        </div>

                    </div>

                    <button class="btn btn-primary btn-sm wishlist-product__remove" data-id-product="{$product.id_product}">
                        <i class="fa fa-times fa-fw"></i>
                        {l s='Remove from wishlist' mod='mwishlist'}
                    </button>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-default wishlist-product__add-to-cart" data-id-product="{$product.id_product}">
                        <i class="fa fa-basket fa-fw"></i>
                        {l s='Add to cart' mod='mwishlist'}
                    </button>
                </div>
            </article>
        {/foreach}
    </div>
    <div class="alert alert-warning my-wishlist__empty">
        {l s='No products at this time.' mod='mwishlist'}
    </div>
<div>