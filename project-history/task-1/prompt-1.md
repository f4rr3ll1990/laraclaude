#Model: Claude code.
#Comand: /compact keep track of the current architecture
#Prompt:
Add a single news article detail page to the existing dark-themed minimalist news website. When users click on a news card on the homepage, they should be navigated to a dedicated page showing the full article.

## New Route
- Add Vue Router route: "/news/{slug}" 
- This should render a new ArticleDetail.vue component

## ArticleDetail.vue Component
Create a new Vue component that displays the full news article with the following requirements:

### Layout & Content
- Fetch article data from the existing API endpoint: GET /api/news/{slug}
- Display:
  - Hero section with the article's image (full-width banner style, with a dark overlay gradient for readability)
  - Article title (large, bold heading)
  - Author name and publication date below the title
  - Full article content (rendered as HTML, ensure proper sanitization)
  - Back button/link to return to homepage
- If the article doesn't exist (404), show a "Article not found" message with a link back to home
- Show loading skeleton/spinner while fetching the article data

### Design Specifications
- Maintain the existing dark theme and minimalist style
- Use proper typography hierarchy: large title, smaller meta info, comfortable reading font size for content (18-20px)
- Maximum content width of ~800px centered for optimal readability
- Add subtle fade-in animation when the page loads
- Include social sharing buttons (visual only, no actual functionality needed): Twitter/X, Facebook, Copy Link
- The image banner should have a height of ~400px on desktop, ~250px on mobile

## Homepage Updates (App.vue or NewsList component)
- Make each NewsCard component fully clickable
- On click, navigate to "/news/{article.slug}" using Vue Router
- Add cursor:pointer style and a subtle visual indicator that cards are clickable (e.g., slightly more pronounced hover effect)
- Ensure the click event works on the entire card area, not just the title

## Additional Details
- Add smooth page transitions between homepage and article detail (optional fade or slide)
- Update the browser document title to show the article title using document.title or a meta tag update
- Handle errors gracefully: if the API call fails, show an error message with a retry button
- The back button should use Vue Router's router.back() or link to "/"
- Ensure all existing functionality (Load More, other pages) continues to work correctly
- Add a "Related Articles" section at the bottom of the article detail page showing 3 random articles (fetch from GET /api/news?page=1&per_page=3&exclude={current_slug} or similar logic)

## API Update (if needed)
- If the backend doesn't already have it, add a GET /api/news/{slug} endpoint that returns a single article by slug
- Include proper 404 response if slug doesn't exist
- If you need to update the paginated endpoint to exclude a slug for "Related Articles", add an optional exclude_slug query parameter

Please implement all changes while maintaining the existing dark minimalist design aesthetic and ensuring responsive behavior across all device sizes.