#Model: Claude code.
#Prompt: 
Rename the entire news website from its current name to "F4X". This should be a comprehensive rebranding across all files, components, and documentation. The name change must be consistent throughout the entire project.

## Brand Name Change Requirements
- New site name: "F4X"
- Update every occurrence of the old site name to "F4X" across ALL project files
- This includes but is not limited to visible text, meta tags, configuration files, and documentation

## Specific Areas to Update

### 1. Header/Navigation (AppHeader.vue)
- Change the site logo/title text in the navbar to "F4X"
- Update any alt text or aria-labels containing the old name
- Ensure the navbar brand links to the homepage

### 2. Footer (AppFooter.vue)
- Update copyright text: "© 2024 F4X. All rights reserved." (use current year dynamically)
- Change any "About [Old Name]" or similar references to "About F4X"
- Update footer tagline or description if it contains the old name

### 3. About Page (/about route)
- Update all headings and text references from old site name to "F4X"
- Example changes:
  - "About Us" → "About F4X"
  - "Welcome to [Old Name]" → "Welcome to F4X"
  - "Our mission at [Old Name]" → "Our mission at F4X"
- Update any placeholder text that mentions the old name

### 4. Browser Tab & Meta Information
- Update the HTML `<title>` tag: change from old name to "F4X - News" or just "F4X"
- Update meta description tags if they contain the old site name
- Update any Open Graph or Twitter Card meta tags with the old name
- Modify the Laravel layout file or main app template where these are defined
- Update Vue Router's document.title for each route:
  - Homepage: "F4X - Latest News"
  - About: "About F4X"
  - Contacts: "Contact F4X"
  - Article detail: "{Article Title} - F4X"

### 5. Configuration Files
- Update `.env` or `.env.example` if they contain APP_NAME or similar variables with the old name: `APP_NAME=F4X`
- Update `config/app.php`: change 'name' value to 'F4X'
- Update any other configuration references

### 6. Documentation Files

#### CLAUDE.md
- Replace all instances of the old project name with "F4X"
- Update the project description/title
- Ensure setup instructions reference "F4X" where appropriate

#### README.md
- Change the main heading from old name to "# F4X"
- Update the project description paragraph
- Replace any old name references in:
  - Badges (if any)
  - Installation instructions
  - Usage examples
  - Screenshot descriptions
  - Features list
  - About section
- Update any links or URLs if they reference the old name (keep the actual URL structure, just change displayed text)

### 7. Other Files
- Search through ALL project files (controllers, views, components, configs, tests, etc.)
- Replace any comments mentioning the old project name
- Update email templates if they contain the old name
- Modify error pages (404, 500, etc.) to show "F4X" instead of old name
- Check and update any API response messages that might include the old name
- Update Artisan console commands if any reference the old name

### 8. Consistency Checks
- Ensure the name "F4X" is spelled exactly the same everywhere (uppercase "F", "4", uppercase "X")
- No trailing spaces or inconsistent formatting
- The brand name should stand out - consider using a slightly different font weight or color for "F4X" in the header to make it distinctive
- Add a subtle styling for the brand name "F4X" (optional: use a gradient text effect or letter-spacing to make it visually memorable)

## Verification
After making changes, please confirm:
- All visible user-facing text shows "F4X"
- All meta/invisible text shows "F4X"
- All documentation is updated
- No instances of the old name remain in the project

Perform a thorough search and replace across the entire codebase to ensure completeness.