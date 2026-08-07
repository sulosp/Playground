# Lemonator Law WordPress Theme

Custom theme matching [lemonator.solution21-websites.com](https://lemonator.solution21-websites.com/) — no Elementor required.

## Install (FTP)

1. Upload the entire **`lemonator-law`** folder to:
   ```
   wp-content/themes/lemonator-law/
   ```

2. In WordPress admin → **Appearance → Themes**, activate **Lemonator Law**.

3. Go to **Settings → Reading** → set **Your homepage displays** to **A static page** and choose any page (or leave default — `front-page.php` renders automatically for the site front).

4. Ensure media exists at `wp-content/uploads/2026/08/` (same images as the live site). The theme references those paths.

## What's included

| Section | Template |
|---------|----------|
| Header + nav | `header.php` |
| Hero + qualifier form | `template-parts/hero.php` |
| Vehicle classes | `template-parts/vehicle-classes.php` |
| Claim process | `template-parts/claim-process.php` |
| Attorney | `template-parts/attorney.php` |
| Service areas | `template-parts/locations.php` |
| Contact form + office info | `template-parts/contact.php` |
| Footer | `footer.php` |

## Qualifier form + email

The theme bundles **`yl-qualifier/`** with the multi-step form and submit handler.

Edit notification email in:
```
yl-qualifier/config.local.php
```

Requires **WP Mail SMTP** (Google) configured on the site for email delivery.

## Contact form

Bottom contact form posts to `admin-post.php` and sends via `wp_mail()` to `sulosp1992@gmail.com` (edit in `functions.php` → `lemonator_handle_contact_form`).

## No Elementor needed

Deactivate Elementor / Hello Elementor after activating this theme to avoid conflicts.
