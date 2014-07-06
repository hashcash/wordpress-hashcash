=== Hashcash ===
Contributors: pkaroukin
Tags: hashcash, spam, security
Requires at least: 3.0.0
Tested up to: 3.9.1
Stable tag: 1.0.3
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Integrates Hashcash.IO proof-of-work widget with login/registration/comment forms.

== Description ==

This plugin will integrate jQuery plugin Hashcash.IO (https://github.com/hashcash/jquery.hashcash.io) to be used in combination with https://hashcash.io/ service.

Active development happens in GitHub repository - https://github.com/hashcash/wordpress-hashcash

Please report all issues at https://github.com/hashcash/wordpress-hashcash/issues

Because of the way plugins work, problem might arrise from specific combination of WP version, plugins, themes.
Therefore for the best result everytime you submit a bug report or just seeking an advice please include following:

* Version of Wordpress you are using
* Brand and version of Browser you are using to test it
* List of third-party plugins and their versions activated on your website
* What theme and version you are using

### Protect Against Web Spam

Typically various "Internet SEO Companies" try to leverage poor forum software protection against mass submission and create many worthless posts with links to a website they are promoting.

Some forum and blog software implement various CAPTCHA solutions but these have two negative aspects:

* They annoy your visitors.
* They provide a fake sense of security.
* Today it is possible to buy access to API which solves any kind of CAPTCHA for just $0.70 per 1000 CAPTCHA images solved by a real human being. And do you really think your customer will be happy to try to solve one of these ridiculous CAPTCHAs?

### Secure Against Brute Force Attacks

Many modern applications are susceptible to brute force attacks. Take a typical login form, for example. Hackers can compromise account security by trying every possible password combination. They can also leverage a large network of proxy servers to paralelize this attack. Forcing their browser to work hard makes it too expensive and slow for hackers to perform a brute force attack.

### Based On Open Technologies

We leverage the following features:

* Asm.js
* HTML5
* Web Workers

Browsers supported:

* Google Chrome 28+
* Mozilla Firefox 22+
* Internet Explorer 10+
* Opera 18+

### Fully translatable

All strings are available for translation

### Fully accessible

We follow both common sense and accessibility guidelines to make this
widget accessible to people with limited abilities. We make it focusable
and actionable via Tab-Enter keys, as well as we have WAI-ARIA live region
which updates blind user via screen reader about progress.

== Installation ==

1. Download plugin folder into /wp-content/plugins/ on your server
2. Activate the plugin through the 'Plugins' menu in WordPress
3. On Hashcash plugin settings page generate new keys and save settings

== Screenshots ==

1. Submit button disabled. User need to unlock it before proceeding.
2. Widget updates both visually and via WAI-ARIA to inform user about progress.
3. Submit button unlocked.
4. If user tries to click on submit button before unlocking, information popup will show up.
