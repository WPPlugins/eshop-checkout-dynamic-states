=== Plugin Name ===
Contributors: useStrict
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=VLQU2MMXKB6S2
Tags: eShop, Checkout Form, Dynamic State County Province
Requires at least: 3.0
Tested up to: 4.0
Stable tag: 1.3.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Dynamically load correct State/County/Province in Checkout forms according to selected country. 

== Description ==

Improve your customer's experience by showing the appropriate State/County/Province when the Country field is changed. 

If the selected country does not have any regions in the database, replace the dropdown with a text field.

Comes out of the box with US and Brazilian States, and Canadian Provinces. Additional countries/regions can be found [here](http://usestrict.net/2012/09/eshop-dynamic-checkout-statecountyprovince-region-packs/).

For best results, use with [eShop Shipping Extension](http://wordpress.org/extend/plugins/eshop-shipping-extension/). 

== Installation ==

1. Upload eshop-checkout-dynamic-states.zip to your blog's wp-content/plugins directory;
1. Activate the plugin in your Plugin Admin interface;

== Frequently Asked Questions ==

= Where can I get additional countries/regions? =

* Additional countries/regions can be found [here](http://usestrict.net/2012/09/eshop-dynamic-checkout-statecountyprovince-region-packs/).


== Screenshots ==

1. Alternate State field is removed, as it is no longer necessary. If a user selects a country for which there are States/Counties/Provinces in the database, then a drop-down is presented.
2. If no data exists in the database, then a text input field is presented to the user instead.


== Changelog ==
= 1.3.4 =
* Catching up with jQuery >= 1.8

= 1.3.3 =
* Fixed typo in previous commit which messed up shipping states.
* Making sure that the session is started.

= 1.3.2 =
* Saving state/shipstate state with $.totalStorage. Clicking the back button no longer forces the user to re-select the state.

= 1.3.1 =
* Using state ID for dropdown values instead of code;
* Improved state/altstate field logic to play nicely with eShop;

= 1.3 =
* Now using eShop's country/state tables in order to play nicely with taxes. Users will have to manually set the zones that they want for any new countries/states

= 1.2.1 =
* Removed FK name of states table due to possible naming conflicts.
* Replaced dbDelta with $wpdb->query

= 1.2 =
* Modified primary key of states table as it was failing to add a few states.

= 1.1 =
* Maintaining state when a user submits the checkout form with missing required fields.

= 1.0.1 =
* Fixed a typo setting state_code field size

= 1.0 =
* Initial release

== Upgrade Notice ==
No need to upgrade at this time
