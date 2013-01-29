## Guzzlecal TODO

* Make sure that we properly handle the application not having the access token or refresh (because the user didn't authorize the account anyways, or because the application lost it)
* Move Mutators to separate namespace and class(es).
* Determine whether or not to use a new class for PATCH operations or just allow array based manipulation
* Consider implementations on other platforms (Codeigniter?)
  * <del>Would a symlink from `libraries` to the vendors directory make sense?</del> Just make sure it's autoloaded properly, no lib needed.
