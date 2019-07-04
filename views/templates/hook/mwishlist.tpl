<div class="wishlist col-md-1 col-xs-3">
    <a class="wishlist__link" href="{$link->getModuleLink('mwishlist','wishlist')}">
        <i  class="fa fa-heart wishlist__icon"></i>
        <span class="wishlist__counter">{$wishlist->productsNb}</span>
    </a>
</div>

{addJsDef wishlist_url=$link->getModuleLink('mwishlist','wishlist')}