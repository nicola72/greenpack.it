<?php

//ROUTES DEL CMS
Route::group(['prefix' => 'cms'], function ()
{
    /*
     * Per ogni route che non sia login chiamo il middleware "cms.isauth:cms"
     * cms.isauth è l'alias configurato per il middleware Cms\IsAuth nel Kernel.php
     * :cms corrisponde al tipo di guard che usiamo per l'autenitcazione in config/auth.php
     */
    Route::middleware('cms.isauth:cms')->group(function ()
    {
        Route::get('/', 'Cms\DashboardController@index')->name('cms.dashboard');

        Route::get('/settings', 'Cms\SettingsController@index')->name('cms.settings');
        Route::get('/settings/create_module', 'Cms\SettingsController@create_module')->name('cms.create.module');
        Route::get('/settings/switch_stato_module','Cms\SettingsController@switch_stato_module');
        Route::get('/settings/switch_boolean_config','Cms\SettingsController@switch_boolean_config');
        Route::get('/settings/config_module/{id}','Cms\SettingsController@config_module');
        Route::get('/settings/edit_module/{id}','Cms\SettingsController@edit_module');
        Route::get('/settings/edit_config_module/{id}','Cms\SettingsController@edit_config_module');
        Route::get('/settings/create_config_module/{id}','Cms\SettingsController@create_config_module');
        Route::get('/settings/destroy_config_module/{id}','Cms\SettingsController@destroy_config_module');
        Route::get('/settings/create_copy_config_module/{id}','Cms\SettingsController@create_copy_config_module');
        Route::post('/settings/update_module/{id}','Cms\SettingsController@update_module');
        Route::post('/settings/store_module','Cms\SettingsController@store_module');
        Route::post('/settings/store_config_module','Cms\SettingsController@store_config_module');
        Route::post('/settings/update_config_module/{id}','Cms\SettingsController@update_config_module');
        Route::post('/settings/store_copy_config_module','Cms\SettingsController@store_copy_config_module');
        Route::get('/settings/create_user_pannello','Cms\SettingsController@create_user_pannello');
        Route::get('/settings/create_admin_user_pannello','Cms\SettingsController@create_admin_user_pannello');

        Route::get('/sync', 'Cms\SyncController@index')->name('cms.sync');
        Route::get('/sync/sync_orders','Cms\SyncController@sync_orders');
        Route::get('/sync/sync_order_details','Cms\SyncController@sync_order_details');
        Route::get('/sync/sync_order_shippings','Cms\SyncController@sync_order_shippings');
        Route::get('/sync/sync_users','Cms\SyncController@sync_users');
        Route::get('/sync/sync_user_details','Cms\SyncController@sync_user_details');
        Route::get('/sync/sync_reviews','Cms\SyncController@sync_reviews');
        Route::get('/sync/sync_categorie','Cms\SyncController@sync_categorie');
        Route::get('/sync/sync_url_categorie','Cms\SyncController@sync_url_categorie');
        Route::get('/sync/sync_prodotti','Cms\SyncController@sync_prodotti');
        Route::get('/sync/sync_file_prodotti','Cms\SyncController@sync_file_prodotti');
        Route::get('/sync/sync_url_prodotti','Cms\SyncController@sync_url_prodotti');
        Route::get('/sync/sync_abbinamenti','Cms\SyncController@sync_abbinamenti');
        Route::get('/sync/sync_file_abbinamenti','Cms\SyncController@sync_file_abbinamenti');
        Route::get('/sync/sync_url_abbinamenti','Cms\SyncController@sync_url_abbinamenti');
        Route::get('/sync/create_thumbs/{page?}','Cms\SyncController@create_thumbs');
        Route::get('/sync/create_watermarks/{page?}','Cms\SyncController@create_watermarks');
        Route::get('/sync/create_watermarks_ital/{page?}','Cms\SyncController@create_watermarks_ital');
        Route::get('/sync/create_thumbs_abbinamenti/{page?}','Cms\SyncController@create_thumbs_abbinamenti');
        Route::get('/sync/create_watermarks_abbinamenti/{page?}','Cms\SyncController@create_watermarks_abbinamenti');
        Route::get('/sync/create_watermarks_ital_abbinamenti/{page?}','Cms\SyncController@create_watermarks_ital_abbinamenti');



        Route::get('/seo/switch_homepage','Cms\SeoController@switch_homepage');
        Route::get('/seo/associa_url/{id}','Cms\SeoController@associa_url');
        Route::get('/seo/associa_model/{id}','Cms\SeoController@associa_model');
        Route::get('/seo/delete_associazione_url/{id}','Cms\SeoController@delete_associazione_url');
        Route::get('/seo/delete_associazione_model/{id}','Cms\SeoController@delete_associazione_model');
        Route::get('/seo/get_urls_by_type','Cms\SeoController@get_urls_by_type');
        Route::post('/seo/store_associazione_url','Cms\SeoController@store_associazione_url');
        Route::post('/seo/store_associazione_model','Cms\SeoController@store_associazione_model');
        Route::resource('/seo','Cms\SeoController');
        Route::get('/seo/destroy/{id}', 'Cms\SeoController@destroy');
        Route::get('/seo', 'Cms\SeoController@index')->name('cms.seo');

        Route::get('/sliders/switch_visibility','Cms\SlidersController@switch_visibility');
        Route::post('/sliders/upload_images', 'Cms\SlidersController@upload_images');
        Route::get('/sliders/images/{id}', 'Cms\SlidersController@images');
        Route::get('/sliders', 'Cms\SlidersController@index')->name('cms.sliders');

        Route::get('/newsletter_subscribers/destroy/{id}', 'Cms\NewsletterSubscribersController@destroy');
        Route::resource('/newsletter_subscribers','Cms\NewsletterSubscribersController');
        Route::get('/newsletter_subscribers', 'Cms\NewsletterSubscribersController@index')->name('cms.iscritti_newsletter');

        Route::resource('/coupons','Cms\CouponsController');
        Route::get('/coupons/destroy/{id}', 'Cms\CouponsController@destroy');
        Route::get('/coupons', 'Cms\CouponsController@index')->name('cms.coupons');

        Route::get('/review/switch_visibility','Cms\ReviewController@switch_visibility');
        Route::resource('/review','Cms\ReviewController');
        Route::get('/review/destroy/{id}', 'Cms\ReviewController@destroy');
        Route::get('/review', 'Cms\ReviewController@index')->name('cms.recensioni');

        Route::get('/order','Cms\OrderController@index')->name('cms.ordini');
        Route::get('/order/order/{id}','Cms\OrderController@order');
        Route::get('/order/pdf/{id}','Cms\OrderController@pdf');
        Route::get('/order/order_print/{id}','Cms\OrderController@order_print');

        Route::get('/ital_order','Cms\ItalOrderController@index')->name('cms.italfama_ordini');
        Route::get('/ital_order/order/{id}','Cms\ItalOrderController@order');
        Route::get('/ital_order/pdf/{id}','Cms\ItalOrderController@pdf');

        Route::post('/catalog/upload_pdf', 'Cms\CatalogController@upload_pdf');
        Route::get('/catalog/pdf/{id}', 'Cms\CatalogController@pdf');
        Route::post('/catalog/upload_images', 'Cms\CatalogController@upload_images');
        Route::get('/catalog/images/{id}', 'Cms\CatalogController@images');
        Route::get('/catalog/switch_visibility','Cms\CatalogController@switch_visibility');
        Route::resource('/catalog','Cms\CatalogController');
        Route::get('/catalog/move_up/{id}', 'Cms\CatalogController@move_up');
        Route::get('/catalog/move_down/{id}', 'Cms\CatalogController@move_down');
        Route::get('/catalog/destroy/{id}', 'Cms\CatalogController@destroy');
        Route::get('/catalog', 'Cms\CatalogController@index')->name('cms.cataloghi');

        Route::get('/macrocategory/switch_stato','Cms\MacrocategoryController@switch_stato');
        Route::resource('/macrocategory','Cms\MacrocategoryController');
        Route::get('/macrocategory/move_up/{id}', 'Cms\MacrocategoryController@move_up');
        Route::get('/macrocategory/move_down/{id}', 'Cms\MacrocategoryController@move_down');
        Route::get('/macrocategory/destroy/{id}', 'Cms\MacrocategoryController@destroy');
        Route::get('/macrocategory', 'Cms\MacrocategoryController@index')->name('cms.macrocategorie');


        Route::get('/category/sync_prodotti', 'Cms\CategoryController@sync_prodotti');
        Route::get('/category/sync_file_prodotti', 'Cms\CategoryController@sync_file_prodotti');
        Route::get('/category/sync_abbinamenti', 'Cms\CategoryController@sync_abbinamenti');
        Route::get('/category/sync_file_abbinamenti', 'Cms\CategoryController@sync_file_abbinamenti');
        Route::get('/category/switch_stato','Cms\CategoryController@switch_stato');
        Route::resource('/category','Cms\CategoryController');
        Route::get('/category/move_up/{id}', 'Cms\CategoryController@move_up');
        Route::get('/category/move_down/{id}', 'Cms\CategoryController@move_down');
        Route::get('/category/destroy/{id}', 'Cms\CategoryController@destroy');
        Route::get('/category', 'Cms\CategoryController@index')->name('cms.categorie');

        Route::get('/material/switch_stato','Cms\MaterialController@switch_stato');
        Route::resource('/material','Cms\MaterialController');
        Route::get('/material/move_up/{id}', 'Cms\MaterialController@move_up');
        Route::get('/material/move_down/{id}', 'Cms\MaterialController@move_down');
        Route::get('/material/destroy/{id}', 'Cms\MaterialController@destroy');
        Route::get('/material/images/{id}', 'Cms\MaterialController@images');
        Route::get('/material', 'Cms\MaterialController@index')->name('cms.materiali');

        Route::get('/product/switch_visibility','Cms\ProductController@switch_visibility');
        Route::get('/product/switch_visibility_italfama','Cms\ProductController@switch_visibility_italfama');
        Route::get('/product/switch_offerta','Cms\ProductController@switch_offerta');
        Route::get('/product/switch_novita','Cms\ProductController@switch_novita');
        Route::resource('/product','Cms\ProductController');
        Route::post('/product/upload_images', 'Cms\ProductController@upload_images');
        Route::get('/product/images/{id}', 'Cms\ProductController@images');
        Route::get('/product/destroy/{id}', 'Cms\ProductController@destroy');
        Route::get('/product','Cms\ProductController@index')->name('cms.prodotti');

        Route::get('/pairing/switch_visibility','Cms\PairingController@switch_visibility');
        Route::get('/pairing/switch_visibility_italfama','Cms\PairingController@switch_visibility_italfama');
        Route::get('/pairing/switch_offerta','Cms\PairingController@switch_offerta');
        Route::resource('/pairing','Cms\PairingController');
        Route::post('/pairing/upload_images', 'Cms\PairingController@upload_images');
        Route::get('/pairing/images/{id}', 'Cms\PairingController@images');
        Route::get('/pairing/destroy/{id}', 'Cms\PairingController@destroy');
        Route::get('/pairing','Cms\PairingController@index')->name('cms.abbinamenti');

        Route::get('/italcustomers/switch_vede_p_fabbrica','Cms\ItalcustomersController@switch_vede_p_fabbrica');
        Route::get('/italcustomers/switch_vede_p_netto','Cms\ItalcustomersController@switch_vede_p_netto');
        Route::get('/italcustomers/switch_vede_p_vendita','Cms\ItalcustomersController@switch_vede_p_vendita');
        Route::get('/italcustomers/switch_vede_sconto_bonifico','Cms\ItalcustomersController@switch_vede_sconto_bonifico');
        Route::resource('/italcustomers','Cms\ItalcustomersController');
        Route::get('/italcustomers/destroy/{id}', 'Cms\ItalcustomersController@destroy');
        Route::get('/italcustomers', 'Cms\ItalcustomersController@index')->name('cms.italfama_customers');

        Route::get('/news/switch_visibility','Cms\NewsController@switch_visibility');
        Route::get('/news/switch_popup','Cms\NewsController@switch_popup');
        Route::get('/news/move_up/{id}', 'Cms\NewsController@move_up');
        Route::get('/news/move_down/{id}', 'Cms\NewsController@move_down');
        Route::post('/news/upload_images', 'Cms\NewsController@upload_images');
        Route::get('/news/images/{id}', 'Cms\NewsController@images');
        Route::get('/news/destroy/{id}', 'Cms\NewsController@destroy');
        Route::resource('/news','Cms\NewsController');
        Route::get('/news', 'Cms\NewsController@index')->name('cms.news');

        Route::resource('/offerte','Cms\OfferteController');
        Route::get('/offerte', 'Cms\OfferteController@index')->name('cms.offerte');

        Route::resource('/fotogallery','Cms\FotogalleryController');
        Route::get('/fotogallery', 'Cms\FotogalleryController@index')->name('cms.fotogallery');

        Route::resource('/eventi','Cms\EventiController');
        Route::get('/eventi', 'Cms\EventiController@index')->name('cms.eventi');

        Route::post('/file/sort_images', 'Cms\FileController@sort_images');
        Route::get('/file','Cms\FileController@index')->name('cms.file');
        Route::get('/file/destroy/{id}', 'Cms\FileController@destroy');

        Route::get('/website/domains', 'Cms\WebsiteController@domains')->name('cms.website.domains');
        Route::get('/website/create_domain', 'Cms\WebsiteController@create_domain');
        Route::get('/website/edit_domain/{id}', 'Cms\WebsiteController@edit_domain');
        Route::get('/website/destroy_domain/{id}', 'Cms\WebsiteController@destroy_domain');
        Route::post('/website/update_domain/{id}', 'Cms\WebsiteController@update_domain');
        Route::post('/website/store_domain','Cms\WebsiteController@store_domain');
        Route::get('/website/page_move_up/{id}', 'Cms\WebsiteController@page_move_up');
        Route::get('/website/page_move_down/{id}', 'Cms\WebsiteController@page_move_down');
        Route::get('/website/switch_menu_page','Cms\WebsiteController@switch_menu_page');
        Route::get('/website','Cms\WebsiteController@index')->name('cms.website');
        Route::get('/website/pages','Cms\WebsiteController@pages');
        Route::get('/website/create_page','Cms\WebsiteController@create_page');
        Route::get('/website/destroy_page/{id}','Cms\WebsiteController@destroy_page');
        Route::post('/website/store_page','Cms\WebsiteController@store_page');
        Route::get('/website/urls/{type?}','Cms\WebsiteController@urls');
        Route::get('/website/edit_url/{id}','Cms\WebsiteController@edit_url');
        Route::post('/website/update_url/{id}','Cms\WebsiteController@update_url');


    });

    Route::get('/login', 'Cms\Auth\LoginController@showLoginForm')->name('cms.login');
    Route::post('/login','Cms\Auth\LoginController@login')->name('cms.login');
    Route::get('/auto_login', 'Cms\Auth\LoginController@auto_login');
    Route::get('/logout', 'Cms\Auth\LoginController@logout')->name('cms.logout');
    Route::get('/register','Cms\Auth\RegisterController@showRegistrationForm')->name('cms.register');
    Route::post('/register','Cms\Auth\RegisterController@register');
    Route::get('/password/reset','Cms\Auth\ForgotPasswordController@showLinkRequestForm')->name('cms.password.request');

});

//ROUTES DEL WEBSITE
Route::get('/','Website\PageController@index')->name('website.home');

//ROTTE ITALIANE VECCHIE DA FARE REDIRECT 301
Route::get('scacchi_in_bronzo.php','Website\RedirectController@old_it_category');
Route::get('scacchi_in_ottone.php','Website\RedirectController@old_it_category');
Route::get('scacchiere.php','Website\RedirectController@old_it_category');
Route::get('tavoli_da_scacchi.php','Website\RedirectController@old_it_category');
Route::get('giochi_dama_firenze.php','Website\RedirectController@old_it_category');
Route::get('accessori.php','Website\RedirectController@old_it_category');
Route::get('scacchi_in_metallo.php','Website\RedirectController@old_it_category');
Route::get('scacchi_da_viaggio.php','Website\RedirectController@old_it_category');
Route::get('backgammon.php','Website\RedirectController@old_it_category');
Route::get('statue_per_scacchi.php','Website\RedirectController@old_it_category');
Route::get('domino.php','Website\RedirectController@old_it_category');
Route::get('scacchi_in_resina.php','Website\RedirectController@old_it_category');
Route::get('mappamondi.php','Website\RedirectController@old_it_category');

Route::get('dettaglio.php','Website\RedirectController@old_it_product');

//ROTTE INGLESI VECCHIE DA FARE REDIRECT 301
Route::group(['prefix' => 'eng'],function(){
    Route::get('production_brass.php','Website\RedirectController@old_en_category');
    Route::get('statue_resin_bronze.php','Website\RedirectController@old_en_category');
    Route::get('production_boards.php','Website\RedirectController@old_en_category');
    Route::get('production_tables.php','Website\RedirectController@old_en_category');
    Route::get('production_checkers.php','Website\RedirectController@old_en_category');
    Route::get('accessori.php','Website\RedirectController@old_en_category');
    Route::get('production_metal.php','Website\RedirectController@old_en_category');
    Route::get('travel_set.php','Website\RedirectController@old_en_category');
    Route::get('production-backgammon.php','Website\RedirectController@old_en_category');
    Route::get('production_pewter_statues.php','Website\RedirectController@old_en_category');
    Route::get('domino.php','Website\RedirectController@old_en_category');
    Route::get('production_statue_resin.php','Website\RedirectController@old_en_category');
    Route::get('mappamondi.php','Website\RedirectController@old_en_category');

    Route::get('details.php','Website\RedirectController@old_en_product');
});

Route::group(['prefix' => '{locale}','where' => ['locale' => '[a-zA-Z]{2}'],'middleware' => 'setlocale'],function(){


    //per L'autorizzazione
    Route::get('/login', 'Website\Auth\LoginController@showLoginAndRegisterForm')->name('website.login');
    Route::post('/login','Website\Auth\LoginController@login')->name('website.login');
    Route::get('/logout', 'Website\Auth\LoginController@logout')->name('website.logout');
    Route::post('/register','Website\Auth\RegisterController@register');
    Route::get('/retriew_password','Website\Auth\RegisterController@showRetriewPasswordForm');
    Route::post('/retriew_password','Website\Auth\RegisterController@retriew_password');
    Route::post('/change_account','Website\Auth\RegisterController@change_account');

    Route::get('/cart','Website\CartController@index')->name('website.cart');
    Route::get('/cart/redeem_coupon', 'Website\CartController@redeem_coupon');
    Route::get('/cart/addproduct/{id}','Website\CartController@addproduct');
    Route::get('/cart/addpairing/{id}','Website\CartController@addpairing');
    Route::get('/cart/update','Website\CartController@update');
    Route::get('/cart/destroy/{id}','Website\CartController@destroy');
    Route::post('/cart/resume','Website\CartController@resume')->name('riepilogo_ordine');
    Route::post('/cart/submit','Website\CartController@submit');
    Route::get('/cart/checkout_result/{id}','Website\CartController@checkout_result');
    Route::post('/cart/paypal_notify','Website\CartController@paypal_notify');
    Route::get('/cart/paypal_error/{id}','Website\CartController@paypal_error');

    Route::get('category/{id}','Website\PageController@category'); //url EMERGENZA (nel caso non venga trovata nel db) per categoria
    Route::get('details/{id}', 'Website\PageController@details'); //url EMERGENZA (nel caso non venga trovata nel db) per scheda prodotto
    Route::get('pairing-details/{id}', 'Website\PageController@pairing_details'); //url EMERGENZA (nel caso non venga trovata nel db) per scheda abbinamenti

    Route::post('/clear_cookies','Website\PageController@clear_cookies');
    Route::get('/cookies_policy','Website\PageController@cookies_policy');
    Route::get('/account','Website\PageController@account');
    Route::get('/orders','Website\PageController@orders');
    Route::post('/add_to_newsletter','Website\PageController@add_to_newsletter');
    Route::get('/wishlist_delete/{id}','Website\PageController@wishlist_delete');
    Route::get('/wishlist_addpairing/{id}','Website\PageController@wishlist_addpairing');
    Route::get('/wishlist_addproduct/{id}','Website\PageController@wishlist_addproduct');
    Route::post('/clear_cookies', 'Website\PageController@clear_cookies');
    Route::post('/invia_formcontatti','Website\PageController@invia_formcontatti')->name('invia_formcontatti');
    Route::get('/{slug}','Website\PageController@page');

});
