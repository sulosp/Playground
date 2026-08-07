# YL Qualifier — WordPress theme integration (not a plugin)

Native WordPress form: PHP template + shortcode + AJAX mail. No Contact Form 7.

## 1. Copy into your child theme

Copy the entire **`yl-qualifier`** folder to:

```
wp-content/themes/your-child-theme/yl-qualifier/
```

You should have:

```
your-child-theme/
├── functions.php
└── yl-qualifier/
    ├── yl-qualifier.php
    ├── templates/form.php
    ├── assets/qualifier.css
    ├── assets/qualifier.js
    └── page-templates/qualifier-form.php
```

## 2. Load it from `functions.php`

Add **one line** to your child theme's `functions.php`:

```php
require get_stylesheet_directory() . '/yl-qualifier/yl-qualifier.php';
```

Use a **child theme** so updates to the parent theme do not remove this code.

## 3. Show the form on a page

Pick **either** method:

### Option A — Shortcode (flexible)

Create or edit a page, add a Shortcode block:

```
[yl_qualifier_form]
```

With custom mail / contact link:

```
[yl_qualifier_form to="sulosp1992@gmail.com" contact_email="sulosp1992@gmail.com"]
```

### Option B — Page template (dedicated form page)

1. Edit a page in WordPress
2. **Page → Template** (or Page Attributes → Template)
3. Choose **Qualifier Form**
4. Publish

The form renders on that page; CSS/JS load automatically.

## 4. Remove Contact Form 7

- Delete the old `[contact-form-7 ...]` shortcode from the page
- You can deactivate the CF7 plugin if nothing else uses it

## Customize notification email (optional)

In `functions.php`:

```php
add_filter( 'yl_qualifier_mail_to', function ( $email, $data ) {
    return 'team@yourfirm.com';
}, 10, 2 );
```

## Troubleshooting

| Issue | Fix |
|--------|-----|
| Shortcode shows as plain text | Missing `require` line in `functions.php`, or folder not in **child** theme |
| No styling | Hard-refresh; clear site cache; confirm `yl-qualifier/assets/` uploaded |
| Submit fails | Host may block `wp_mail()` — use WP Mail SMTP plugin |
| Template not in dropdown | Folder must be inside active theme; re-save permalinks |

## vs plugin

Same code path as `yl-qualifier-plugin/`, but lives in your theme and is loaded with `require` instead of activating a plugin.
