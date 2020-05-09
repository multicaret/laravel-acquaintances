<?php

return [
    /*
     * Models Related.
     */
    'model_namespace' => 'App',
    'user_model_class_name' => 'User',

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
             * Default type here is 'general', as longs as you have one criteria of rating a model
             * you can ignore this setting.
             * It will be the default type of rating of null is provided, if you wish to tweak this type name
             * use the value below as you wish.
             *
             */
            'type' => 'general',
        ],
        'types' => [
            /* Add any other type that your website/application have here,
             * the following added rating types are for demonstration purposes only.
             * There is no effect on deleting them nor adding to them, however, its a good practice
             * to not hard code your rating types, hence, pleas use this simple array
             */
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
