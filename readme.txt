=== Posterno - Listings Directory & Classifieds ===
Contributors: alessandro.tesoro, posterno
Tags: business directory, listings, classifieds, directory, listing, local business directory, listings directory, link directory, member directory, staff directory, real estate, job listing, googlemap, profile, profiles, yelp clone, tripadvisor clone, yellow pages clone, car listings, auto listings, members, profile, community, user profile, user registration
Requires at least: 4.9.6
Tested up to: 5.1
Requires PHP: 5.5+
Stable tag: 0.3.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Plugin URI: https://posterno.com

Posterno is a powerful, extendable directory plugin that helps you create any kind of listings & classifieds directory like Yelp or Tripadvisor.

== Description ==

Posterno is the #1 WordPress directory plugin that helps you create any kind of listings directory. Built to integrate seamlessly with WordPress, Posterno is designed to be easy to use, yet powerful enough to meet all your business needs as your online community grows.

With endless flexibility, Posterno allows you to create, manage and monetize a directory site for any purpose and showcase listings such as Cars, Hotels, Properties, Restaurants, Events, Travel Tours, etc. Your imagination is the only limit.

[Website](https://posterno.com) | [Full features list](https://posterno.com/features) | [Documentation](http://docs.posterno.com/) | [Support](https://posterno.com/support/)

**Please Note**: This is a beta testing version of this plugin. Therefore there may be some bugs that you come across. Please report any bugs through [the contact form here](https://posterno.com/contacts/).

**Please avoid reviewing the plugin until we've reached the stable version 1.0.0 :) thank you.**

= Fully featured =

Listings can have all the features you could ever wish - descriptions, photos, maps, video, categories, keywords, business hours and more. [Explore the powerful features](https://posterno.com/features/) that enable you to build the top-notch directory that you want.

= Unlimited custom fields =

Posterno provides you the flexibility to set an unlimited amount of custom fields for your listings, profiles and registration form. Your site can easily accommodate all the areas you’d like to advertise.

= Built to rank =

Built with SEO in mind to outperform your competitors in search engines thanks to the integration of JSON-LD for structured data (rich snippets). Rest assured your site will rank well in the SERPs with eye catching results.

= Membership functionalities =

Members can register on your site to create user profiles. A user dashboard is provided to each registered user and listing owner. Your members will never have to see the WordPress admin panel in order to, login, register, change password and customize their profile.

= Email notifications =

Posterno provides customizable email templates for each email that can be sent to users after a certain event/trigger happens. You also have access to merge tags for emails, they render bits of information about the website, the user or the listing.

**Read more about our features on [posterno.com](https://posterno.com/features/)**

= Built with developers in mind =

Developers have access to a large collection of actions and filters ready to be used to customize and extend Posterno. The integrated templating system that allows developers to easily customize all the templates of the plugin.

= Support =

Free support is provided only through the [support forum](https://wordpress.org/support/plugin/posterno). Join the [support forum](https://wordpress.org/support/plugin/posterno) to ask questions and get help regarding Posterno. Free support is provided exclusively for bugs and help using the plugin. Please read the support policy [https://posterno.com/support-policy/](https://posterno.com/support-policy/).

Support for premium addons is provided exclusively [through the premium support platform](https://posterno.com/support/).

Before opening a [support topic](https://wordpress.org/support/plugin/posterno) please read the [faqs](https://docs.posterno.com/category/445-faq-tutorials) and [documentation](http://docs.posterno.com/).

== Screenshots ==

1. Frontend members dashboard
2. Frontend listing submission form only some fields are displayed in the screenshot.
3. Frontend listing submission form only some fields are displayed in the screenshot.
4. Frontend members public profiles
5. Frontend registration form
6. Frontend login form
7. Frontend password recovery form

== Installation ==

= Minimum Requirements =

* PHP version 5.5 or greater (PHP 7.2 or greater is recommended)
* MySQL version 5.0 or greater (MySQL 5.6 or greater is recommended)

Visit the [Posterno server requirements documentation](https://docs.posterno.com/article/440-requirements) for a detailed list of server requirements.

= Automatic installation =

Visit the [Posterno automatic installation documentation](https://docs.posterno.com/article/435-automatic-installation) for detailed instructions.

= Manual installation =

The manual installation method involves downloading Posterno and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Frequently Asked Questions ==

= Where can I find the documentation and user guides? =

For help setting up and configuring Posterno please refer to our [documentation](https://docs.posterno.com/)

= Where can I get support? =

Free support is provided only through the [support forum](https://wordpress.org/support/plugin/posterno).

Support for premium addons is provided exclusively [through the premium support platform](https://posterno.com/support/).

= Will Posterno work with my theme? =

Posterno has been designed and coded to seamlessly integrate with any properly coded WordPress theme. Furthermore Posterno uses bootstrap 4 for styling all of the elements. However, each theme is coded differently and the quality of the code can vary. To avoid potential issues and conflicts, Posterno provides the ability to disable the built-in styling through the options panel. If your theme is properly coded, Posterno should adapt itself to your site layout. Although we cannot guarantee that the plugin works with all themes.

= Where can I request new features, themes and extensions? =

Get in touch with us via email at [posterno.com](https://posterno.com/contacts) or open a topic into the [support forum](https://wordpress.org/support/plugin/posterno).

= Is Posterno GDPR compliant? =

Please refer to the [official documentation for gdpr compliance](https://docs.posterno.com/article/536-is-posterno-gdpr-compliant).

Please note that using Posterno does NOT guarantee compliance to GDPR. Posterno gives you general information and tools, but is NOT meant to serve as complete compliance package. As the owner of your website, it is your responsibility to ensure that your site is compliant with the regulations. Please always contact an attorney for accurate information, we are not responsible for your website GDPR compliance and we can’t be held accountable for any legal issues.

== Screenshots ==

== Changelog ==

= 0.3.1 Open beta release =

- Tweak: uninstall schemas when deleting plugin.
- Tweak: When wp-login.php is locked, all privacy requests functionalities are now confirmed and handled on the fronted dashboard.
- Tweak: improved storage of the file field by pushing all attached files into a single meta value.
- Tweak: geocoding now triggers on frontend submission form too.
- Fix: listing file field displaying numeric value instead of file name/url.
- Fix: escaping of listings post statuses in classic editor window.
- Fix: trigger schema geocoding only when api keys are available.
- Fix: listing expiry date stored in wrong format when submitting listing on the frontend.

= 0.3.0 Open beta release =

- Added: structured data (schema) integration editor for listings.
- Added: automatically geocode listing's address when coordinates change.
- Added: (dev) Get the user's last name only, by id.
- Tweak: (dev) added new helper functions.
- Fix: (dev) pno_get_listing_author() function returning wrong value.
- Fix: deleted code no longer needed.
- Fix: detect editing permissions before loading the fields of the editing form.
- Fix: listing status set to published instead of publish.

The following documentation articles have been updated/created:

[Schema integration](https://docs.posterno.com/category/549-schema-integration)
[Geocoding documentation](https://docs.posterno.com/category/556-geocoding)

= 0.2.0 Open beta release =

- Added: honeypot antispam protection to login form.
- Added: listing status in dashboard is now highlighted.
- Added: button to mark listings as expired in the admin panel.
- Added: Ability to replace the comment author's URL with Posterno's public profile url.
- Added: count of pending listings is now displayed within the admin dashboard menu.
- Added: ability to limit the maximum amount of selectable tags during submission.
- Added: control whether expired listings are still visible and crawable by search engines.
- Added: when expired listings are public a notice will now be displayed informing the user that the listing is expired.
- Added: ability to hide the sidebar on the listing page when expired listings are public.
- Tweak: if tags association is disabled, the setting field is hidden from the taxonomy panel.
- Tweak: if categories association is disabled, the setting field is hidden from the taxonomy panel.
- Tweak: removed obsolete code from frontend javascript file.
- Tweak: display a warning message when a user attempts to edit an expired listing.
- Tweak: check permissions to edit a listing before even loading the form.
- Tweak: when a listing is expired, hide the edit action from the frontend dashboard.
- Fix: expired listings not visible in the frontend dashboard when "all listings" status is selected.
- Fix: uploaded file mime type verification would sometimes fail.

The following documentation articles have been updated/created:

[Listings expiry](https://docs.posterno.com/article/468-listings-expiry)
[How to make expired listings visible](https://docs.posterno.com/article/548-how-to-make-expired-listings-visible)

= 0.1.2 Open beta release =

- tweak: decode retrieved urls for email tags.
- tweak: add site's name and site's admin email to email options during plugin first installation.
- tweak: automatically enable expiry notification email during plugin first installation.
- tweak: listing title field should not have the "admin only" setting.
- tweak: the listing tags field should not have an options generator setting.
- fix: login fails when username is an email address and login method is set to "username only"
- fix: parameters missed when using the recent and featured shortcode from the shortcodes editor.
- fix: profile page not loading when username has spaces.

= 0.1.1 Open beta release =

- Fix: dist folder missing from deployment script.

= 0.1.0 Open beta release =
