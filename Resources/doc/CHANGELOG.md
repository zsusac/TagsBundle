Netgen Tags Bundle changelog
============================

2.0.8 (05.11.2015)
------------------

* Parametrize pagelayout used in tag view page

2.0.7 (28.10.2015)
------------------

* Add `sudo` method to Tags service, used by tags router to match the tag since at that point user is still anonymous
* Add setting to enable or disable tag router, useful for legacy siteaccesses

2.0.6 (08.10.2015)
------------------

* Fix tag router causing config resolver to be created too early (thanks @wizhippo)

2.0.5 (25.09.2015)
------------------

* Also support `UserReference` argument in `TagLimitationType` methods

2.0.4 (10.09.2015)
------------------

* Replace `ez_trans_prop` usage with `netgen_tags_tag_keyword` function, to be able to get keyword by tag ID also

2.0.3 (10.09.2015)
------------------

* Do not use removed criterion property on Query object

2.0.2 (08.09.2015)
------------------

* Use `ez_trans_prop` to render tag keywords in most prioritized languages in field template and tag view template
* Fix generating tag URLs when fallback to internal route occurs
* Switch coding standards to PSR2

2.0.1 (16.07.2015)
------------------

* Fix calling `empty()` with expressions

2.0 (16.07.2015)
----------------

* Support multilanguage tags!
* Use Symfony router and generator to match and generate tag view URLs
* Path to tag view page changed from `/tag/{tagUrl}` to `/tags/view/{tagUrl}` to be compatible with legacy
* You can now use a container parameter to change the path prefix used to generate tag URLs
* You can now use a container parameter to select which template will `/tags/view` controller use
* Various bug fixes and optimizations

1.2.5 (05.11.2015)
------------------

* Parametrize pagelayout used in tag view page

1.2.4 (05.11.2015)
------------------

* Parametrize template used for tag view pages

1.2.3 (16.07.2015)
------------------

* Require `ezsystems/eztags-ls` 1.x

1.2.2 (13.05.2015)
------------------

* Support new namespaces for search implementation in eZ Publish kernel

1.2.1 (07.05.2015)
------------------

* Mark `eztags` field type as unindexable for now

1.2 (20.04.2015)
----------------

* Add support for specifying field definition settings in process of creating it
* Add support for setting the priority of tags stored in field, load tags from field by priority
* Switch loading field type view template to PrependExtensionInterface method
* Fixed a bug with removing a tag from content through Public API
* Fixed support for pgsql databases
* Fixed creating tags with parent ID 0


1.1.3 (19.08.2014)
------------------

* Fix recursion in signal slot methods `loadTagSynonyms` & `getTagSynonymCount`
* Fix signal slot methods `loadTagsByKeyword` and `getTagsByKeywordCount` not returning value
* Add unit tests for signal slot service


1.1.2 (27.06.2014)
------------------

* Enable reverse proxy caching of tag view page and clearing the cache via `X-Tag-Id` header
* Switched usage of `eztags.tag_view.related_content_list.limit` parameter in tag view controller to config resolver


1.1.1 (20.06.2014)
------------------

* Add loading tags and tags count by keyword in `TagsService`
* Add implementation of `Tag` limitation for use in `tags/add` policy


1.1 (27.05.2014)
----------------

* Add `TagId` content & location search criterion
* Add `TagKeyword` content & location search criterion
* Allow loading tags and tag count from root level (by making `$tag` parameter in `TagsService::loadTagChildren` and `TagsService::getTagChildrenCount` optional)
* Implement loading a tag by its URL (for example `ez+publish/extensions/eztags`)
* Add a controller to render `/tag/{tagId}` and `/tag/{tagUrl}` pages (includes pagination)
* Add `eztags_tag_url` Twig function to be able to link to `/tag/{tagUrl}` page properly
* Add links to `/tag/{tagUrl}` page for each tag in `eztags` content field template
* Reconfigure unit tests to allow running from repo root instead of eZ Publish 5 root


1.0 (08.07.2013)
----------------

* Update Tags field type to new version of field type API
* Fix bug with not handling -1 as limit in `TagsService::getRelatedContent`


0.9 (19.06.2013)
----------------

* Initial release
