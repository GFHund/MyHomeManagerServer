<?php
/**
 * Routes configuration.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * It's loaded within the context of `Application::routes()` method which
 * receives a `RouteBuilder` instance `$routes` as method argument.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use App\Middleware\AuthenticationMiddleware;

/*
 * The default class to use for all routes
 *
 * The following route classes are supplied with CakePHP and are appropriate
 * to set as the default:
 *
 * - Route
 * - InflectedRoute
 * - DashedRoute
 *
 * If no call is made to `Router::defaultRouteClass()`, the class used is
 * `Route` (`Cake\Routing\Route\Route`)
 *
 * Note that `Route` does not do any inflections on URLs which will result in
 * inconsistently cased URLs when used with `:plugin`, `:controller` and
 * `:action` markers.
 */
/** @var \Cake\Routing\RouteBuilder $routes */
$routes->setRouteClass(DashedRoute::class);

//$routes->scope('/', function (RouteBuilder $builder) {
    /*
     * Here, we are connecting '/' (base path) to a controller called 'Pages',
     * its action called 'display', and we pass a param to select the view file
     * to use (in this case, templates/Pages/home.php)...
     */
    //$builder->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);

    /*
     * ...and connect the rest of 'Pages' controller's URLs.
     */
    //$builder->connect('/pages/*', 'Pages::display');

    /*
     * Connect catchall routes for all controllers.
     *
     * The `fallbacks` method is a shortcut for
     *
     * ```
     * $builder->connect('/:controller', ['action' => 'index']);
     * $builder->connect('/:controller/:action/*', []);
     * ```
     *
     * You can remove these routes once you've connected the
     * routes you want in your application.
     */
    //$builder->fallbacks();
//});

/*
 * If you need a different set of middleware or none at all,
 * open new scope and define routes there.
 *
 * ```
 * $routes->scope('/api', function (RouteBuilder $builder) {
 *     // No $builder->applyMiddleware() here.
 *     
 *     // Parse specified extensions from URLs
 *     // $builder->setExtensions(['json', 'xml']);
 *     
 *     // Connect API actions here.
 * });
 * ```
 */


$routes->registerMiddleware('authorisation',new AuthenticationMiddleware());

$routes->scope('/api/v1/auth/',['controller' => 'Users'],function (RouteBuilder $builder) {
    $builder->setExtensions(['json']);
    //$builder->resources('Auth');
    $builder->connect('/login',['action'=> 'loginUser'] );//,'_method' => 'POST'
});
$routes->scope('/api/v1/',['controller' => 'Products'],function (RouteBuilder $builder){
    $builder->applyMiddleware('authorisation');
    $builder->setExtensions(['json']);
    $builder->connect('/product',['action' => 'productAction']);
    $builder->connect('/product/{id}',['action' => 'productDetailAction']);
});
$routes->scope('/api/v1/',['controller' => 'ShoppingLists'],function (RouteBuilder $builder){
    $builder->applyMiddleware('authorisation');
    $builder->setExtensions(['json']);
    $builder->options('/shoppingList',['action' => 'optionRequest']);
    $builder->get('/shoppingList',['action' => 'getShoppingLists']);
    $builder->post('/shoppingList',['action' => 'createShoppingList']);
    $builder->get('/shoppingList/{id}',['action' => 'getShoppingList']);
    $builder->put('/shoppingList/{id}',['action' => 'updateShoppingList']);
    $builder->get('/shoppingList/{id}/product',['action' => 'getShoppingListProducts']);
    $builder->options('/shoppingList/{id}/product/{productId}',['action' => 'optionRequest']);
    $builder->post('/shoppingList/{id}/product/{productId}',['action' => 'createShoppingListProduct']);
    $builder->get('/shoppingList/{id}/mapping',['action' => 'getShoppingListMapping']);
});
$routes->scope('/api/v1/',['controller' => 'Recipes'],function (RouteBuilder $builder){
    $builder->applyMiddleware('authorisation');
    $builder->options('/recipe',['action' => 'createRecipeOptionRequest']);
    $builder->post('/recipe',['action' => 'createRecipe']);
    $builder->get('/recipe',['action' => 'getRecipeList']);
    $builder->get('/recipe/{id}',['action' => 'getRecipe']);
    $builder->options('/recipe/{id}/incredient/{productId}',['action' => 'createRecipeOptionRequest']);
    $builder->post('/recipe/{id}/incredient/{productId}',['action' => 'createRecipeIncredient']);
    $builder->get('/recipe/{id}/incredient',['action' => 'getRecipeIncredients']);
    $builder->options('/recipe/{id}/step',['action' => 'createRecipeOptionRequest']);
    $builder->post('/recipe/{id}/step',['action' => 'createRecipeStep']);
    $builder->get('/recipe/{id}/step',['action' => 'getRecipeSteps']);
    $builder->options('/recipe/{id}/step/{stepId}',['action' => 'createRecipeOptionRequest']);
    $builder->put('/recipe/{id}/step/{stepId}',['action' => 'updateRecipeStep']);
});
$routes->scope('/api/v1/',['controller' => 'magazines'],function (RouteBuilder $builder){
    $builder->applyMiddleware('authorisation');
    $builder->get('magazines/list',['action' => 'getMagazineList']);
    $builder->options('magazines/list',['action' => 'optionsRequest']);
    $builder->put('magazines/{id}',['action' => 'updateMagazineData']);
    
});
$routes->scope('/api/v1/',['controller' => 'wikiPages'],function (RouteBuilder $builder){
    $builder->applyMiddleware('authorisation');
    $builder->options('wiki/page',['action' => 'optionsRequest']);
    $builder->post('wiki/page',['action' => 'createWikiPage']);
    $builder->delete('wiki/page/{id}',['action' => 'deleteWikiPage']);
    $builder->get('wiki/page/{id}',['action' => 'getWikiPage']);
    $builder->get('wiki/page',['action' => 'getWikiPages']);
    $builder->put('wiki/page/{id}',['action' => 'updateWikiPage']);
    $builder->options('wiki/page/{id}',['action' => 'optionsRequest']);
});
$routes->scope('/api/v1/',['controller' => 'settings'],function (RouteBuilder $builder){
    $builder->applyMiddleware('authorisation');
    $builder->get('/setting',['action' => 'getSettings']);
    $builder->options('/setting',['action' => 'optionsRequest']);
    $builder->put('/setting',['action' => 'updateSettings']);
});