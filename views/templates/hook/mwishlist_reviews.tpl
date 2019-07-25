
{if $fromProductPage}

<button class="wishlist__button{if $isLiked} wishlist__button--liked{/if} wishlist__from-product" data-id-product="{$id_product}" data-toggle="tooltip" title="{l s='Add to wishlist' mod='mwishlist'}">
    <i  class="fa fa-heart"></i>
</button>

{else}

<div class="wishlist__section">
    <button class="wishlist__button{if $isLiked} wishlist__button--liked{/if}" data-id-product="{$id_product}" data-toggle="tooltip" title="{l s='Add to wishlist' mod='mwishlist'}">
        <i  class="fa fa-heart"></i>
    </button>
    {* <span class="wishlist__info" id="wishlist_info_{$id_product}">
        {l s="Likes count: %d" mod='mwishlist' sprintf=$likesCount}
    </span> *}
</div>
{/if}