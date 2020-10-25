## v3.4.0
* minor fix. Replaced `str_plural` with `Str::plural`.

## v3.3.1
* Added `ratings()` & `ratingsPure()` to CanBeRated model
* Added user model name to configs `user_model_class_name`
* Added user relation to InteractionRelation model

## v3.3.0
* Fixed the logic of allTypes post-fixed functions.

## v3.2.0
* Removed avoiding querying ratings when the type is set to *overall* 
* Fixed the value of `userSumRatingReadable()` function in CanBeRated trait.
* Fixed `userSumRatingReadable()` function in `CanBeRated` trait.
* Add new functions to `CanBeRated` trait:
    * averageRatingAllTypes()
    * sumRatingAllTypes()
    * sumRatingAllTypesReadable()
    * userAverageRatingAllTypes()
    * userSumRatingAllTypes()
    * userSumRatingAllTypesReadable()
    * ratingPercentAllTypes()
    * getAverageRatingAllTypesAttribute()
    * getSumRatingAllTypesAttribute()
    * getUserAverageRatingAllTypesAttribute()
    * getUserSumRatingAllTypesAttribute()

* Added a proper param type hint for the $target param in the following traits
    * CanBeFavorited
    * CanBeFollowed
    * CanBeLiked
    * CanBeRated
    * CanBeSubscribed
    * CanBeVoted
    * CanFavorite
    * CanFollow
    * CanLike
    * CanRate
    * CanSubscribe
    * CanVote

## v3.1.0
* Made the `user_id` FK type dynamic and part of configurations

## v3.0.0
* Changed the package's company - since I renamed my brand

## v2.0.0
* PHP 7.1 is the minimum requirement now
* Supporting Laravel 5.8 by replacing `Event::fire` with `Event::dispatch`
* Fixing an issue with foreign key constraint when running migrations

## v1.2.0
* Adding rating system feature
* Remote soft deletion and updated_at timestamp
* Adding `numberToReadable()` function to as helper
* Enhance logic by adding more columns
* Adding `CanBeFavorited` trait now includes:
  * `favoriters_count` favoriters count
  * `favoritersCountReadable()` return favoriters count in readable format 
  * `favoriters_count_readable` favoriters count readable attribute added
* Adding `CanBeFollowed` trait now includes:
  * `followers_count` followers count
  * `followersCountReadable()` return followers count in readable format 
  * `followers_count_readable` followers count readable attribute added
* Adding `CanBeLiked` trait now includes:
  * `likers_count` likers count
  * `likersCountFormmated()` changed return type to readable number, and adding an alias to it `likersCountReadable()`
  * `likers_count_readable` likers count readable attribute added
* Adding `CanBeSubscribed` trait now includes:
  * `subscribers_count` subscribers count
  * `subscribersCountReadable()` return subscribers count in readable format 
  * `subscribers_count_readable` subscribers count readable attribute added
* Adding `CanBeSubscribed` trait now includes:
  * `voters_count` voters count
  * `votersCountReadable()` return voters count in readable format 
  * `voters_count_readable` voters count readable attribute added
* Adding `CanBeSubscribed` trait now includes:
  * `upvoters_count` upvoters count
  * `upvotersCountReadable()` return upvoters count in readable format 
  * `upvoters_count_readable` upvoters count readable attribute added
* Adding `CanBeSubscribed` trait now includes:
  * `downvoters_count` downvoters count
  * `downvotersCountReadable()` return downvoters count in readable format 
  * `downvoters_count_readable` downvoters count readable attribute added

## v1.1.0
* Fix errors  
* Enhance logic
* Smart/less configurations 

## v1.0.1
* Minor change in the name of migrations files

## v1.0.0
* Initial Release
