<div class="my-wishlist">

    <h1 class="page-heading">{l s='My wishlist' mod='mwishlist'}</h1>
    <div class="box">
        {foreach from=$wishlist->products item=product}
            {$product.id_product}
        {/foreach}
    </div>
<div>