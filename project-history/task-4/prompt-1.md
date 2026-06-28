# Model: Claude code
# Prompt:
Add a complete news article creation API to the F4X news website backend (Laravel). This API should allow authenticated users to create new news articles through protected endpoints with proper validation, error handling, and security measures.

## API Endpoints

### 1. POST /api/news
Create a new news article

### 2. Authentication Requirement
All article creation endpoints must be protected by authentication using Laravel Sanctum (token-based API authentication)

## Authentication Setup

### Install and Configure Laravel Sanctum
- Install Laravel Sanctum if not already present
- Publish Sanctum configuration
- Add Sanctum middleware to the api middleware group
- Create an `api_token` field in the users table migration (or use Sanctum's personal access tokens)
- Add HasApiTokens trait to the User model

### Authentication Endpoints
- POST /api/login - Login and receive API token
  - Request: { "email": "user@example.com", "password": "password" }
  - Response: { "token": "...", "user": {...} }
- POST /api/logout - Revoke current token (authenticated)
- POST /api/register - Register new user (optional, can be useful for admin creation)
  - Request: { "name": "...", "email": "...", "password": "...", "password_confirmation": "..." }

## Create News Article Endpoint Details

### Request
- Method: POST
- URL: /api/news
- Headers: 
  - Authorization: Bearer {token}
  - Accept: application/json
  - Content-Type: application/json

### Request Body (JSON)
```json
{
  "title": "Заголовок новини",
  "excerpt": "Короткий опис новини",
  "content": "Повний текст новини з HTML розміткою",
  "image_url": "https://example.com/image.jpg",
  "author": "Ім'я Автора",
  "published_at": "2024-06-28T14:30:00"
}
