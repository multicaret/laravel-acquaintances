# Events

The package dispatches events for key actions. Common names in tests and docs include:

- friendships.sent, friendships.accepted, friendships.denied, friendships.blocked, friendships.unblocked, friendships.cancelled
- verifications.sent, verifications.accepted, verifications.denied, verifications.blocked, verifications.unblocked, verifications.cancelled
- ratings.rate, ratings.unrate
- vote.up, vote.down, vote.cancel
- likes.like, likes.unlike
- followships.follow, followships.unfollow
- favorites.favorite, favorites.unfavorite
- reports.report, reports.unreport
- subscriptions.subscribe, subscriptions.unsubscribe
- views.view, views.unview

You can listen to these via the Event facade or listeners in your app.
