# 💬 Viable Support for Zendesk

**Version:** 1.0  
**Requires WordPress:** 5.0 or higher  
**Tested up to:** 6.8  
**Requires PHP:** 7.2 or higher  
**License:** GPLv2 or later  
**Author:** [ViableCube](https://viablecube.com)  
**Tags:** zendesk, helpdesk, customer support, ticketing, wordpress comments, support form, zendesk integration, zendesk support, zendesk tickets, automation

---

Connect your **Zendesk Support** account with **WordPress** — create tickets, sync custom fields, and automatically convert comments into Zendesk tickets for a smoother customer support experience.

[📘 Documentation](https://viablecube.com/docs/viable-support-for-zendesk/?utm_source=vsfz&utm_medium=readme-docs) | [💬 Support](mailto:ahsan@viablecube.com)

---

## ✨ Features

- 🔐 **Secure Authorization** — Connect using your Zendesk Subdomain, Account Email, and API Token.  
- 🧩 **Dynamic Custom Fields** — Fetch all text-type custom fields directly from Zendesk.  
- 🧾 **Shortcode Integration** — Display a dynamic Zendesk-powered support form anywhere with `[viasuzen_ticket_form]`.  
- 💬 **Comment to Ticket Automation** — Automatically convert new WordPress comments into Zendesk tickets.  
- 🧠 **Custom Metadata** — Include post type, author name, and post ID in Zendesk tickets.  
- 🏷️ **Tag Management** — Automatically apply Zendesk tags when creating tickets from comments.  
- ⚙️ **Lightweight & Extendable** — Developer-friendly structure for custom workflows and integrations.

---

## 🧾 Shortcode Example

Use this shortcode in any post, page, or template:

```php
[viasuzen_ticket_form]
````

This renders a Zendesk-powered form that dynamically displays text-type custom fields fetched from your connected account.

---

## ⚙️ Installation

1. Upload the plugin files to the `/wp-content/plugins/` directory or install directly from the WordPress Plugin Directory.
2. Activate **Viable Support for Zendesk** through the **Plugins → Installed Plugins** menu.
3. Navigate to **Settings → Viable Support for Zendesk**.
4. Enter your **Zendesk Subdomain**, **Account Email**, and **API Token**.
5. Use the `[viasuzen_ticket_form]` shortcode on any page or post to display your ticket form.
6. (Optional) Enable **Comment to Ticket** to automatically create Zendesk tickets from WordPress comments.

---

## ❓ Frequently Asked Questions

### 🔹 What data does this plugin fetch from Zendesk?

It retrieves all **active text-type custom fields** from your Zendesk account using secure API calls.

### 🔹 Can I customize ticket subjects or tags?

Yes. You can configure ticket subjects to include post type, author name, and ID. You can also define tags for better organization.

### 🔹 Are credentials stored securely?

Absolutely. All Zendesk credentials (subdomain, email, token) are securely stored in the WordPress database and used only for server-side API requests.

### 🔹 Does it affect existing comments?

No. It only converts **new comments** into Zendesk tickets when automation is enabled.

---

## 🖼️ Screenshots

1. Authorization screen with fields for Zendesk Subdomain, Email, and API Token.
2. Connected account view showing Zendesk connection status and Web Widget visibility toggle.
3. Form Settings page for configuring form display options.
4. Popup modal to fetch and map Zendesk custom fields dynamically.
5. Example of shortcode `[viasuzen_ticket_form]` added in the WordPress page editor.
6. Frontend display of the Zendesk-powered support form.
7. Zendesk Comment Settings section in WordPress discussing comment integration options.
8. Convert Post Comment to Zendesk Ticket action button in the Comments area.
9. Example of a WordPress comment successfully converted into a Zendesk ticket.

---

## 🧩 Changelog

### v1.0 – Initial Release

* Secure Zendesk API authorization
* Dynamic field fetching from Zendesk
* Shortcode support for ticket form `[viasuzen_ticket_form]`
* Comment-to-ticket automation
* Custom metadata (post type, author name, post ID)
* Automatic tag assignment on ticket creation
* Initial stable release

---

## 🚀 Upcoming Features (v1.1 Roadmap)

* File attachment support in ticket forms
* Dropdown and checkbox field type support
* Admin dashboard to view synced tickets
* Multi-user Zendesk connection management
* Real-time ticket status sync

---

## 📜 License

This plugin is licensed under the **GPLv2 or later**.
You can read the full license text at [GNU General Public License](https://www.gnu.org/licenses/gpl-2.0.html).

---

## 💡 Support & Feedback

For support, suggestions, or bug reports:
📩 **Email:** [ahsan@viablecube.com](mailto:ahsan@viablecube.com)
🌐 **Website:** [https://viablecube.com](https://viablecube.com)

---

> **Developed with ❤️ by [ViableCube](https://viablecube.com)**
> Smart WordPress + Zendesk integrations for modern support teams.
