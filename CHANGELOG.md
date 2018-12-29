## v1.2.0
* adding rating system feature
* remote soft deletion and updated_at timestamp
* adding `numberToReadable()` function to as helper
* enhance logic by adding more columns
* adding `CanBeFavorited` trait now includes:
  * `favoriters_count` favoriters count
  * `favoritersCountReadable()` return favoriters count in readable format 
  * `favoriters_count_readable` favoriters count readable attribute added
* adding `CanBeFollowed` trait now includes:
  * `followers_count` followers count
  * `followersCountReadable()` return followers count in readable format 
  * `followers_count_readable` followers count readable attribute added
* adding `CanBeLiked` trait now includes:
  * `likers_count` likers count
  * `likersCountFormmated()` changed return type to readable number, and adding an alias to it `likersCountReadable()`
  * `likers_count_readable` likers count readable attribute added
* adding `CanBeSubscribed` trait now includes:
  * `subscribers_count` subscribers count
  * `subscribersCountReadable()` return subscribers count in readable format 
  * `subscribers_count_readable` subscribers count readable attribute added
* adding `CanBeSubscribed` trait now includes:
  * `voters_count` voters count
  * `votersCountReadable()` return voters count in readable format 
  * `voters_count_readable` voters count readable attribute added
* adding `CanBeSubscribed` trait now includes:
  * `upvoters_count` upvoters count
  * `upvotersCountReadable()` return upvoters count in readable format 
  * `upvoters_count_readable` upvoters count readable attribute added
* adding `CanBeSubscribed` trait now includes:
  * `downvoters_count` downvoters count
  * `downvotersCountReadable()` return downvoters count in readable format 
  * `downvoters_count_readable` downvoters count readable attribute added

## v1.1.0
* fix errors  
* enhance logic
* smart/less configurations 

## v1.0.1
* Minor change in the name of migrations files

## v1.0.0
* Initial Release
