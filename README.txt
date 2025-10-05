=== Viable Support for Zendesk ===
Contributors: viablecube, ahsangadit
Donate link: https://viablecube.com/docs/viable-support-for-zendesk/?utm_source=vsfz&utm_medium=donate-link
Tags: zendesk, support, helpdesk, customer support, zendesk support
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect your Zendesk Support account with WordPress — create tickets, sync custom fields, and automatically convert comments into Zendesk tickets.

== Description ==

[📘 Documentation](https://viablecube.com/docs/viable-support-for-zendesk/?utm_source=vsfz&utm_medium=readme-docs) | [💬 Support](mailto:ahsan@viablecube.com)

**Viable Support for Zendesk** integrates your Zendesk Support workspace directly into your WordPress site.

With just your Zendesk **subdomain**, **account email**, and **API token**, you can:

* Display Zendesk ticket fields as dynamic WordPress forms.
* Submit support tickets directly from your site.
* Automatically convert WordPress comments into Zendesk tickets.
* Include metadata, tags, and post details automatically in tickets.

Built by **ViableCube**, this plugin is perfect for businesses and developers who want a smooth, automated support experience between WordPress and Zendesk.

== Features ==

* **Secure Authorization** – Connect using your Zendesk subdomain, account email, and API token.
* **Dynamic Field Fetching** – Fetch all text-type custom fields from Zendesk and display them in your form.
* **Shortcode Support** – Use `[viasuzen_ticket_form]` anywhere on your site.
* **Ticket Submission** – Submit new tickets directly to Zendesk.
* **Comment-to-Ticket Automation** – Automatically convert new WordPress comments into Zendesk tickets.
* **Custom Metadata** – Add post type, author name, and ID as part of each ticket.
* **Tag Support** – Automatically add custom tags when creating tickets.
* **Custom Ticket Subjects** – Auto-generate ticket subjects with post or author details.
* **Lightweight & Developer-Friendly** – Optimized for performance and easy to extend.

== Shortcode Example ==

Use this shortcode to display the Zendesk-powered form anywhere:

`[viasuzen_ticket_form]`

== Installation ==

1. Upload the plugin to `/wp-content/plugins/` or install it directly from the WordPress Plugin Directory.  
2. Activate the plugin through the **Plugins** menu in WordPress.  
3. Navigate to **Settings → Viable Support for Zendesk**.  
4. Enter your **Zendesk subdomain**, **account email**, and **API token**.  
5. Use `[viasuzen_ticket_form]` shortcode to display the ticket form.  
6. (Optional) Enable comment-to-ticket automation to convert new comments into Zendesk tickets.

== Frequently Asked Questions ==

= What data does this plugin fetch from Zendesk? =  
It fetches all active text-type custom fields using the Zendesk API.

= Can I add tags or customize ticket subjects? =  
Yes. You can add tags and dynamically generate subjects using post and author data.

= Are my Zendesk credentials stored securely? =  
Yes. The plugin stores credentials securely and uses them only for server-side API requests.

= Can I submit tickets from WordPress? =  
Yes. You can submit new tickets directly from the form rendered by the shortcode.

= Does it modify existing comments? =  
No. It only converts **new comments** into Zendesk tickets if you enable that feature.

== Screenshots ==

1. Authorization screen with fields for Zendesk Subdomain, Email, and API Token.  
2. Connected account view showing Zendesk connection status and Web Widget visibility toggle.  
3. Form Settings page for configuring form display options.  
4. Popup modal to fetch and map Zendesk custom fields dynamically.  
5. Example of shortcode `[viasuzen_ticket_form]` added in the WordPress page editor.  
6. Frontend display of the Zendesk-powered support form.  
7. Zendesk Comment Settings section in WordPress discussing comment integration options.  
8. Convert Post Comment to Zendesk Ticket action button in the Comments area.  
9. Example of a WordPress comment successfully converted into a Zendesk ticket.

== Changelog ==

= 1.0 – Initial Release =  
* Added secure Zendesk API authentication  
* Added dynamic text field fetching  
* Added ticket submission functionality  
* Added comment-to-ticket automation  
* Added metadata and tag options  
* Initial stable release  

== Upgrade Notice ==

= 1.0 =  
First stable release of **Viable Support for Zendesk** connect, create, and automate tickets directly from WordPress.

== License ==

This plugin is licensed under the **GPLv2 or later**.  
You can view the full license text here: [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

== Support & Feedback ==

We’d love to hear your feedback and suggestions!  
📩 Email: [ahsan@viablecube.com](mailto:ahsan@viablecube.com)  
🌐 Website: [https://viablecube.com](https://viablecube.com)
