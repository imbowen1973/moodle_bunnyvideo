# 📦 **Bunny Video Moodle Module**

A Moodle module that integrates Bunny.net secure video delivery directly into your Moodle courses.

Created by **Mark Bowen** of **EBVS / Medicine Vet Referrals**.

---

## 🌟 **Features**

* Adds a **Bunny Video** activity module to Moodle courses.
* Enables embedding of Bunny.net-hosted videos with **signed URLs** for secure access.
* Configurable site-wide Bunny.net CDN base URL, secret key, and token validity duration.
* Provides easy management and playback of videos in Moodle’s course structure.

---

## 🛠 **Installation**

1. Copy the `bunnyvideo` folder into your Moodle site’s `mod` directory.
2. Log in to Moodle as an administrator.
3. Navigate to **Site Administration > Notifications** to trigger plugin installation.
4. Configure Bunny.net settings via **Site Administration > Plugins > Activity Modules > Bunny Video**.

---

## 🔐 **Configuration**

* **Bunny CDN URL**: The base URL of your Bunny.net zone (e.g., `https://yourzone.b-cdn.net`).
* **Secret Key**: Your Bunny.net token signing secret key.
* **Token Duration**: Optional; specifies how long a signed URL remains valid (default: 300 seconds).

---

## 📚 **Usage**

1. Go to a course and **add an activity or resource**.
2. Select **Bunny Video** from the list.
3. Enter the **video path** (relative to your Bunny.net CDN URL).
4. Save and display. Moodle will dynamically generate a signed URL for secure playback.

---

## 🧩 **Developer Notes**

* This plugin was built as a **bespoke solution** with the flexibility to extend features in the future.
* Credit: **Mark Bowen, EBVS / Medicine Vet Referrals**.

---

## 📝 **License**

This Moodle plugin is released under the **GNU General Public License (GPL) v3 or later**. See the `LICENSE` file for details.

