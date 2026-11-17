<?php

$router->map(
    method: 'GET',
    route: '/wishlists',
    target: 'App\controller\members\GiftWishlistController@index',
    name: 'family_wishlists_index'
);

$router->map(
    method: 'POST',
    route: '/member/wishlists',
    target: 'App\controller\members\GiftWishlistController@store',
    name: 'family_wishlists_store'
);

$router->map(
    method: 'POST',
    route: '/member/wishlists/items/[i:itemId]/claim',
    target: 'App\controller\members\GiftWishlistController@claimItem',
    name: 'family_wishlists_claim_item'
);

$router->map(
    method: 'POST',
    route: '/member/wishlists/[i:id]/delete',
    target: 'App\controller\members\GiftWishlistController@delete',
    name: 'family_wishlists_delete'
);
