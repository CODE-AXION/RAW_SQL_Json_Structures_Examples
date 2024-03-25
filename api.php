<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/products', function (Request $request) {
    
    // COMMENT OUT THE SPECIFIC CODE SECTION TO RUN

    // (Many To Many) Product has many tags and tag has many products
    //Note: witout subquery and using group by 
    $sql = "SELECT products.*,
        JSON_ARRAYAGG(
            JSON_OBJECT(
                'id', tags.id,
                'name', tags.name
            )
        ) AS tags_json
    FROM products
    LEFT JOIN product_tag ON product_tag.product_id = products.id
    LEFT JOIN tags ON product_tag.tag_id = tags.id 
    GROUP BY products.id,products.name,products.created_at,products.updated_at";

       // -- -- OR -- -- //
    
    // (Many To Many) Product has many tags and tag has many products 
    $sql = "SELECT products.*,
    (
        SELECT 
            JSON_ARRAYAGG(
                JSON_OBJECT(
                    'id', tags.id,
                    'name', tags.name
                )
            )
        FROM product_tag
        LEFT JOIN tags ON product_tag.tag_id = tags.id
        WHERE product_tag.product_id = products.id

    ) AS tags
    FROM products";

    $results = \DB::select($sql);

    foreach ($results as &$result) {
        
        $result->tags = json_decode($result->tags);
    }
    
    return $results;



    // (ONE TO ONE) RELATIONSHIP (Product belongs to a User)

    $sql = "SELECT products.*,
    (
        SELECT 
            JSON_ARRAYAGG(
                JSON_OBJECT(
                    'id', users.id,
                    'name', users.name,
                    'email', users.email
                )
            )
        FROM users
        WHERE products.user_id = users.id

    ) AS user
    FROM products";

    $results = \DB::select($sql);

    foreach ($results as &$result) {
        
        $result->user = json_decode($result->user);
    }
    
    return $results;



    // (ONE TO MANY RELATIONSHIP) USER HAS MANY PRODUCTS AND PRODUCT BELONGS TO A USER
    $sql = "SELECT users.id, users.name,users.email,
        (
            SELECT 
                JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'id', products.id,
                        'name', products.name
                    )
                )
            FROM products
            WHERE products.user_id = users.id

        ) AS products
        FROM users";
        
    $results = \DB::select($sql);

    foreach ($results as &$result) {
        
        $result->products = json_decode($result->products);
    }
    
    return $results;



    // NESTED EXAMPLE    
    $sql = "SELECT users.id, users.name,users.email,
        (
            SELECT 
                JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'id', products.id,
                        'name', products.name,
                        'tags', (
                            SELECT 
                                JSON_ARRAYAGG(
                                    JSON_OBJECT(
                                        'id', tags.id,
                                        'name', tags.name
                                    )
                                ) 
                            FROM product_tag 
                            LEFT JOIN tags ON product_tag.tag_id = tags.id
                            WHERE product_tag.product_id = products.id
                        )
                    )
                )
            FROM products
            WHERE products.user_id = users.id

        ) AS products
        FROM users";



    $results = \DB::select($sql);

    
    foreach ($results as &$result) {
        
        $result->products = json_decode($result->products);
    }
    
    return $results;

});
