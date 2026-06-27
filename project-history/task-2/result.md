##Summary of changes
###Frontend components & views

AppHeader.vue — navbar brand → "F4X", added aria-label="F4X home", brand already links to /
AppFooter.vue — footer wordmark → "F4X"; copyright → "© {{ year }} F4X. All rights reserved." (year is dynamic via new Date().getFullYear())
About.vue — heading "About F4X", subtitle, intro paragraph, and founding-milestone text
Contacts.vue — email → hello@f4x.news
ArticleDetail.vue — appName fallback, hero placeholder text, and doc titles ({Title} - F4X, Article Not Found - F4X)
NewsCard.vue — card placeholder text → "F4X"
Routing & titles — router.js: per-route document titles exactly per spec — F4X - Latest News, About F4X, Contact F4X, Page Not Found - F4X.

###Browser/meta — app.blade.php: <title> → F4X - News, updated meta description, and added Open Graph + Twitter Card tags.

###Config — .env APP_NAME=F4X, .env.example, config/app.php default → 'F4X'. (MAIL_FROM_NAME/VITE_APP_NAME interpolate ${APP_NAME}, so they now resolve to F4X automatically.)

###istinctive brand styling — app.scss: the .brand-text wordmark now uses heavier weight (800), wider letter-spacing, and a cyan accent gradient clipped to the text (with a solid-color fallback), so "F4X" stands out in both header and footer.

###Documentation — README.md heading → # F4X plus description and page list; CLAUDE.md project description.

###One note: there were no custom error-page Blade templates, email templates, Artisan commands, or API response strings containing the old name — Laravel's default error pages render the config('app.name') value, which now resolves to "F4X".