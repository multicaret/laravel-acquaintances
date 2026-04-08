<?php

return [
    /**
     * Load migrations from package migrations,
     * If you published the migration files, please set to `false`.
     */
    'migrations' => false,

    /*
     * Models Related.
     */
    'model_namespace' => (int) app()->version() <= 7 ? 'App' : 'App\Models',
    'models' => [
        /*
         * Model name of User model
         */
        'user' => 'User',
        /*
         * Model name of Interaction Relation model
         */
        'interaction_relation' => \Multicaret\Acquaintances\Models\InteractionRelation::class,
        /*
         * Model name of Interaction Relation model
         */
        'friendship' => \Multicaret\Acquaintances\Models\Friendship::class,
        /*
         * Model name of Interaction Relation model
         */
        'friendship_groups' => \Multicaret\Acquaintances\Models\FriendFriendshipGroups::class,
        /*
         * Model name of Interaction Relation model
         */
        'verification' => \Multicaret\Acquaintances\Models\Verification::class,
        /*
         * Model name of Interaction Relation model
         */
        'verification_groups' => \Multicaret\Acquaintances\Models\VerificationGroups::class,
    ],

    'tables' => [
        /*
         * Table name of interactions relations.
         */
        'interactions' => 'interactions',
        /*
         * user foreign key column name within interactions table.
         */
        'interactions_user_id_fk_column_name' => 'user_id',
        /*
         * user foreign key column type within interactions table.
         */
        'interactions_user_id_fk_column_type' => 'unsignedBigInteger',
        /*
         * Morph type for the interactions table subject columns.
         * Options: 'morphs', 'uuidMorphs', 'ulidMorphs'
         */
        'interactions_morph_type' => 'morphs',
        /*
         * Table name of friendships relations.
         */
        'friendships' => 'friendships',
        /*
         * Morph type for the friendships table sender/recipient columns.
         * Options: 'morphs', 'uuidMorphs', 'ulidMorphs'
         */
        'friendships_morph_type' => 'morphs',
        /*
         * Table name of friendship Groups relations.
         */
        'friendship_groups' => 'friendship_groups',
        /*
         * Morph type for the friendship groups table friend columns.
         * Options: 'morphs', 'uuidMorphs', 'ulidMorphs'
         */
        'friendship_groups_morph_type' => 'morphs',
        /*
         * Table name of verifications relations.
         */
        'verifications' => 'verifications',
        /*
         * Morph type for the verifications table sender/recipient columns.
         * Options: 'morphs', 'uuidMorphs', 'ulidMorphs'
         */
        'verifications_morph_type' => 'morphs',
        /*
         * Table name of verification Groups relations.
         */
        'verification_groups' => 'verification_groups',
        /*
         * Morph type for the verification groups table verifier columns.
         * Options: 'morphs', 'uuidMorphs', 'ulidMorphs'
         */
        'verification_groups_morph_type' => 'morphs',
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
             * to not hard code your rating types, hence, please use this simple array
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

    'verifications_groups' => [
        'text' => 0,
        'phone' => 1,
        'cam' => 2,
        'personally' => 3,
        'intimately' => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Enable caching for interaction checks (isLikedBy, isFollowing, etc.)
    | to reduce database queries. Uses cache tags when supported by the driver.
    |
    */
    'cache' => [
        /*
         * Enable or disable the caching layer.
         */
        'enabled' => false,

        /*
         * Cache store to use (null = default store).
         */
        'store' => null,

        /*
         * Time-to-live in seconds for cached interaction checks.
         */
        'ttl' => 3600,
    ],

];
