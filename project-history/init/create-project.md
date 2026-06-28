# Model: Claude Code.
# Prompt: 
Create a dark-themed minimalist news website using Laravel (backend) + Vue.js 3 (frontend) + Bootstrap 5 (dark mode). The website should have a clean, modern, and minimalist design with a dark color scheme throughout.

## Technical Stack
- Backend: Laravel 10+ with PHP 8.1+
- Frontend: Vue.js 3 with Composition API
- Styling: Bootstrap 5 with dark theme (data-bs-theme="dark")
- Database: MySQL with migrations and seeders (host: "localhost"; login: "root"; password: "")
- Build tool: Vite (default with Laravel)

## Database Structure
Create migration and model for:
- News articles table: id, title, slug, excerpt, content, image_url, author, published_at, created_at, updated_at
- Include a seeder that generates 50+ sample news articles with realistic content

## API Endpoints
Create RESTful API routes:
- GET /api/news - Returns paginated news articles (10 per page), ordered by published_at DESC
- GET /api/news/{slug} - Returns single article details
- Accept ?page=X query parameter for pagination

## Page Structure & Requirements

### 1. Homepage ("/" route)
- Display the latest 10 news articles in a clean card-based grid layout (2 columns on desktop, 1 on mobile)
- Each article card should show: image (placeholder if none), title, excerpt (max 150 chars), author name, publication date
- Cards should have subtle hover effects (slight scale/border glow)
- At the bottom, include a "Load More" button that fetches and appends the next 10 articles via the API without page reload
- The button should show a loading state while fetching
- If no more articles exist, hide the button and show "No more articles" text
- Include a simple header with navigation and a footer

### 2. About Page ("/about" route)
- Simple page with a heading "About Us"
- Include 2-3 paragraphs of placeholder text about the news publication
- Clean typography, plenty of whitespace
- Possible timeline or mission/vision sections

### 3. Contacts Page ("/contacts" route)
- Heading "Contact Us"
- Contact form with fields: Name, Email, Subject, Message
- Form validation (client-side with Vue)
- Show success message on submission (no actual backend needed, just simulate)
- Display contact information: Email address, Phone number, Physical address
- Embed a simple location section (text-based, no actual map needed)

## Design Requirements
- Color scheme: Dark background (#1a1a2e or similar dark blue/charcoal), light text (#e0e0e0), accent color (#00d4ff or cyan/teal)
- Use Bootstrap's built-in dark mode (data-bs-theme="dark")
- Minimalist typography - use system fonts or a clean sans-serif
- Subtle gradients and shadows for depth
- Consistent spacing and alignment throughout
- Mobile-responsive design
- Navigation bar: Sticky top, dark, with links to Home, About, Contacts
- Footer: Simple, with copyright and quick links

## Vue.js Implementation
- Use Vue Router for page navigation
- Create reusable components:
  - NewsCard.vue - Individual news article card
  - AppHeader.vue - Navigation header
  - AppFooter.vue - Site footer
  - ContactForm.vue - Contact form with validation
- Use the Composition API (setup script syntax)
- Handle loading states and empty states gracefully

## Additional Details
- Add smooth transitions between pages
- Implement proper error handling for API calls
- Add a 404 page with link back to home
- The "Load More" functionality should track the current page and append results
- All API calls should show loading indicators
- Use environment variables for API base URL configuration

Please create the complete project structure with all necessary files, including:
- Laravel project setup with required packages
- Database migrations and seeders
- All Vue components
- Routes configuration (both Laravel web routes and Vueboth Laravel web routes and Vue Router Router)
- Styling
- READ
- README with setup instructions
- CLAUDE.md with project info
