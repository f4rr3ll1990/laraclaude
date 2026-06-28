# Model: Claude code
# Prompt:
Translate all frontend text, labels, buttons, and static content in the F4X news website from their current language to Ukrainian (Українська мова). This should be a comprehensive translation of all user-facing text across all Vue components, templates, and pages while keeping the backend API responses unchanged (backend can remain in English or current language).

## Translation Requirements
- Target language: Ukrainian (Українська мова)
- Translate ALL static text in Vue components and Blade templates
- Maintain proper Ukrainian grammar, cases, and punctuation
- Keep proper nouns (like "F4X") unchanged
- Ensure natural-sounding Ukrainian translations (not word-for-word machine translation)
- Use formal Ukrainian (Ви) for addressing users where applicable

## Specific Components to Translate

### 1. AppHeader.vue (Navigation Bar)
- Navigation links:
  - "Home" → "Головна"
  - "About" → "Про нас"
  - "Contacts" → "Контакти"
- Mobile menu toggle button text/aria-labels
- Site title remains "F4X" (unchanged)
- Any search placeholder text (if exists): "Search..." → "Пошук..."

### 2. AppFooter.vue (Footer)
- Copyright text: "© {year} F4X. Всі права захищено."
- Footer section titles if they exist:
  - "Quick Links" → "Швидкі посилання"
  - "Follow Us" → "Слідкуйте за нами"
  - "Newsletter" → "Розсилка новин"
- Any footer description or tagline
- "Privacy Policy" → "Політика конфіденційності"
- "Terms of Service" → "Умови використання"

### 3. Homepage (NewsList.vue or similar component)
- Page heading (if exists): "Latest News" → "Останні новини"
- "Load More" button → "Завантажити ще"
- Loading state text: "Loading..." → "Завантаження..."
- "No more articles" message → "Більше немає новин"
- Empty state message: "No articles found" → "Новини не знайдено"
- Error state message: "Something went wrong" → "Щось пішло не так"
- "Try again" button/retry → "Спробувати знову"
- Date formatting: use Ukrainian locale for dates (e.g., "28 червня 2024")
- Author prefix: "By {author}" → "Автор: {author}" or just show the name
- "Read more" link/text on cards → "Читати далі"
- "Published" label → "Опубліковано"
- Image alt text: "News image" → "Зображення новини"

### 4. ArticleDetail.vue (Single Article Page)
- Back button/link: "Back to Home" → "Назад на головну"
- "Article not found" message (404) → "Новину не знайдено"
- "Back to homepage" link in 404 → "Повернутися на головну"
- Loading text: "Loading article..." → "Завантаження новини..."
- Error message: "Failed to load article" → "Не вдалося завантажити новину"
- "Retry" button → "Повторити спробу"
- Author label: "Author:" → "Автор:"
- Date label: "Published:" → "Опубліковано:"
- Related articles section heading: "Related Articles" → "Схожі новини"
- Social share buttons labels/aria-labels:
  - "Share on Twitter" → "Поділитися у Twitter"
  - "Share on Facebook" → "Поділитися у Facebook"
  - "Copy Link" → "Копіювати посилання"
- "Link copied!" confirmation → "Посилання скопійовано!"

### 5. About Page (/about route)
- Page heading: "About F4X" → "Про F4X"
- All paragraph text about the publication - translate to natural Ukrainian
- Section headings:
  - "Our Mission" → "Наша місія"
  - "Our Vision" → "Наше бачення"
  - "Our Team" → "Наша команда"
  - "Our History" → "Наша історія"
- Timeline labels if present
- Any statistics or achievements text

### 6. Contacts Page (/contacts route)
- Page heading: "Contact F4X" → "Контакти F4X" or "Зв'язатися з F4X"
- Form labels:
  - "Name" → "Ім'я"
  - "Email" → "Електронна пошта" or "Email"
  - "Subject" → "Тема"
  - "Message" → "Повідомлення"
- Form placeholders:
  - "Enter your name" → "Введіть ваше ім'я"
  - "Enter your email" → "Введіть вашу електронну пошту"
  - "Enter subject" → "Введіть тему"
  - "Enter your message" → "Введіть ваше повідомлення"
- Submit button: "Send" → "Надіслати"
- "Sending..." state → "Надсилання..."
- Success message: "Your message has been sent!" → "Ваше повідомлення надіслано!"
- Error message: "Failed to send message" → "Не вдалося надіслати повідомлення"
- Validation error messages:
  - "Name is required" → "Ім'я обов'язкове"
  - "Email is required" → "Електронна пошта обов'язкова"
  - "Invalid email format" → "Невірний формат електронної пошти"
  - "Subject is required" → "Тема обов'язкова"
  - "Message is required" → "Повідомлення обов'язкове"
  - "Message must be at least 10 characters" → "Повідомлення має містити щонайменше 10 символів"
- Contact information labels:
  - "Email:" → "Електронна пошта:"
  - "Phone:" → "Телефон:"
  - "Address:" → "Адреса:"

### 7. 404 Page (NotFound.vue or similar)
- Main heading: "Page Not Found" → "Сторінку не знайдено"
- Description: "The page you're looking for doesn't exist" → "Сторінка, яку ви шукаєте, не існує"
- Return link: "Back to Home" → "Повернутися на головну"
- Status code text if displayed: "404 Error" → "Помилка 404"

### 8. Browser Tab Titles (via Vue Router meta or document.title)
- Homepage: "F4X - Latest News" → "F4X - Останні новини"
- About: "About F4X" → "Про F4X"
- Contacts: "Contact F4X" → "Контакти F4X"
- Article detail: "{Article Title} - F4X" → "{Article Title} - F4X" (article titles may stay in original language, or translate if they are sample data)
- 404: "Page Not Found - F4X" → "Сторінку не знайдено - F4X"

### 9. Meta Tags
- Update meta description: translate to Ukrainian
- Open Graph tags: translate og:title, og:description content
- Keep URLs and technical attributes unchanged

## Localization Best Practices
- Consider creating a locale file (e.g., `resources/js/locales/uk.json`) for better maintainability instead of hardcoding
- Use a structured approach:
  ```json
  {
    "nav": {
      "home": "Головна",
      "about": "Про нас",
      "contacts": "Контакти"
    },
    "news": {
      "load_more": "Завантажити ще",
      "no_articles": "Більше немає новин"
    }
  }

If using a localization file, import and reference translations in components using keys

Set the HTML lang attribute to "uk" in the main layout: <html lang="uk">

Update date formatting to use Ukrainian locale (uk-UA) with month names in Ukrainian

Format dates like: "28 червня 2024, 14:30" (day month year, time)

Validation
After translation, please confirm:

No text remains in the old language (except proper nouns and F4X brand name)

Ukrainian text displays correctly (UTF-8 encoding)

All special Ukrainian characters render properly (і, ї, є, ґ, etc.)

No layout issues due to text length changes

All placeholders and aria-labels are translated

Form validation messages appear in Ukrainian
