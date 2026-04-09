<?php

use Multicaret\Acquaintances\Models\FriendFriendshipGroups;
use Multicaret\Acquaintances\Models\Friendship;
use Multicaret\Acquaintances\Models\InteractionRelation;
use Multicaret\Acquaintances\Models\Verification;
use Multicaret\Acquaintances\Models\VerificationGroups;

return [
    /**
     * Load migrations from package migrations,
     * If you published the migration files, please set to `false`.
     */
    'migrations' => false,

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Control which features of the package are enabled. When a feature is
    | disabled, its migrations will not be loaded or published, keeping your
    | database lean. By default all features are enabled.
    |
    | Available features: 'interactions', 'friendships', 'verifications'
    |
    */
    'features' => [
        'interactions' => true,
        'friendships' => true,
        'verifications' => true,
    ],

    /*
     * Models Related.
     */

    /*
     * The fully qualified class name of the User model.
     * Set this if your User model lives in a custom namespace
     * (e.g., 'App\Models\Core\User').
     * When null, the package resolves the model using
     * model_namespace + models.user (e.g., 'App\Models' + 'User').
     */
    'user_model_class_name' => null,

    'model_namespace' => (int) app()->version() <= 7 ? 'App' : 'App\Models',
    'models' => [
        /*
         * Short class name of User model (used when user_model_class_name is null).
         * You may also set a fully qualified class name here.
         */
        'user' => 'User',
        /*
         * Model name of Interaction Relation model
         */
        'interaction_relation' => InteractionRelation::class,
        /*
         * Model name of Interaction Relation model
         */
        'friendship' => Friendship::class,
        /*
         * Model name of Interaction Relation model
         */
        'friendship_groups' => FriendFriendshipGroups::class,
        /*
         * Model name of Interaction Relation model
         */
        'verification' => Verification::class,
        /*
         * Model name of Interaction Relation model
         */
        'verification_groups' => VerificationGroups::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Morph Map Enforcement
    |--------------------------------------------------------------------------
    |
    | When enabled, the package will call Relation::enforceMorphMap() during boot.
    | This prevents fully qualified class names from being stored in *_type columns,
    | reducing storage and avoiding issues when namespaces change.
    | Define your morph map in the 'morph_map' array below.
    |
    */
    'enforce_morph_map' => false,

    'morph_map' => [
        // 'user' => \App\Models\User::class,
        // 'post' => \App\Models\Post::class,
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
        ],
    ],

    'friendships_groups' => [
        'acquaintances' => 0,
        'close_friends' => 1,
        'family' => 2,
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
