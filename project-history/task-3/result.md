##What changed
###Navigation & chrome

AppHeader.vue — nav links (Головна / Про нас / Контакти), aria-labels translated; brand stays "F4X"
AppFooter.vue — tagline, nav links, "© {year} F4X. Всі права захищено."
###Pages

Home.vue — heading "Останні новини", load/empty/error states, "Завантажити ще", "Більше немає новин", retry button
ArticleDetail.vue — back button "← Назад", 404 "Новину не знайдено", error/retry, share aria-labels, "Схожі новини"
About.vue — full body text, "Наша місія" / "Наше бачення" / "Наш шлях", timeline milestones
Contacts.vue — headings, contact labels (Електронна пошта / Телефон / Адреса), "Як нас знайти"
ContactForm.vue — labels, placeholders, validation messages, submit/sending states, success message
NotFound.vue — "Сторінку не знайдено" + return link
###Locale & metadata

NewsCard.vue & ArticleDetail.vue — dates now use uk-UA locale with long month names (e.g. "28 червня 2026")
router.js — all browser-tab titles translated
app.blade.php — <html lang="uk">, meta description, OG/Twitter tags, <title> suffix → "Новини"
##Notes
Encoding/special chars: all files are UTF-8; Ukrainian glyphs (і, ї, є, ') render correctly — confirmed by a clean Vue compile.
One bug fixed proactively: the word "ім'я" contains an apostrophe that would have broken a single-quoted JS validation string in ContactForm.vue — I switched that string to double quotes.
Sample article content (titles/body) stays in English — that's backend seed data served via the API, which you asked to leave unchanged. If you'd like the seeded articles in Ukrainian too, I can update NewsSeeder.php and reseed.
I translated in-place rather than adding a uk.json locale file: the project has no i18n library and hardcodes all strings, so this matches existing conventions. Happy to refactor to vue-i18n if you want a multi-language setup later.