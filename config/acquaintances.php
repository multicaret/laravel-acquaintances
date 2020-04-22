<?php

return [
    /*
     * Models Namespace.
     */
    'model_namespace' => 'App',

    'tables' => [
        /*
         * Table name of interactions relations.
         */
        'interactions' => 'interactions',
        /*
         * `user_id` foreign key column type within interactions table.
         */
        'interactions_user_id_fk_column_type' => 'unsignedBigInteger',
        /*
         * Table name of friendships relations.
         */
        'friendships' => 'friendships',
        /*
         * Table name of friendship Groups relations.
         */
        'friendship_groups' => 'friendship_groups',
    ],

    'rating' => [
        'defaults' => [
            'amount' => 5,
            /*
            * Overall is meant to hold the normal type of rating to be stored, think of it
            * as normal, general, or essential rating, use it if your application is
            * using one rating only
            *
            */
            'type' => 'general',
        ],
        'types' => [
            'general',
            /* Add any other type that your website/application have, the followings are for demonstration purposes */
            'delivery-time',
            'quality',
            'communication',
            'commitment',
        ]
    ],

    'friendships_groups' => [
        'acquaintances' => 0,
        'close_friends' => 1,
        'family' => 2
    ],

];
