# Testing Checklist

## Manual testing checklist:

- [x] Plugin activates without notices
- [x] "Series" available in Gutenberg sidebar
- [x] "Series" saves to post
- [x] "Series" shows on frontend content
  - [x] Functional list of posts
  - [x] Toggle works on click
  - [x] Descriptions are shown correctly
  - [x] Future posts shown with publish date
- [x] Post Series Block
  - [x] Can be inserted
  - [x] Shows current series
  - [x] Shows chosen series
  - [x] Saves/loads correctly
  - [x] Has preview
- [x] Compatibility
  - [x] "Series" available in classic editor meta box
  - [x] "Legacy" template file doesn't cause errors
  - [x] Runs on WP 5.5
  - [x] Runs on PHP 5.6
  - [x] Appearance acceptable across default themes:
    - [x] Twenty Ten
    - [x] Twenty Eleven
    - [x] Twenty Twelve
    - [x] Twenty Thirteen
    - [x] Twenty Fourteen
    - [x] Twenty Fifteen
    - [x] Twenty Sixteen
    - [x] Twenty Seventeen
    - [x] Twenty Nineteen
    - [x] Twenty Twenty

## Post deployment checklist

- [ ] Stable tag is up to date on wordpress.org
- [ ] Tag exists on wordpress.org
- [ ] Plugin contains the /build/ directory
- [ ] Plugin contains the /vendor/ directory and autoloader
