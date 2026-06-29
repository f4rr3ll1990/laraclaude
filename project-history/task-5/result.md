# What changed

config/services.php	Added a gemini block (key + image_model, default gemini-2.5-flash-image)

.env.example	Added GEMINI_API_KEY= placeholder

app/Services/GeminiImageService.php	New — calls generateContent, decodes the base64 inlineData part, returns bytes + MIME (or null)

app/Jobs/GenerateArticleImage.php	New — queued job: generates the image from title, stores it at storage/app/public/articles/{id}.{ext}, writes the public URL to image_url

app/Http/Controllers/Api/NewsController.php	store() unsets client image_url and dispatches the job after create

start.sh / stop.sh	Ensure storage:link exists; start/stop a queue:work worker alongside artisan serve
## Flow
POST /api/news → article saved immediately with image_url = null and returns 201 → GenerateArticleImage job runs on the database queue → Gemini 2.5 Flash Image generates from the title → file stored and image_url updated. On any Gemini failure it logs a warning and leaves image_url null (frontend renders its gradient placeholder).

## Verification
All PHP/shell files lint clean; config loads (gemini-2.5-flash-image, key detected).
storage:link created; jobs table present.
End-to-end: created an article (201, image_url null), job queued and processed cleanly, no failed_jobs.
⚠️ One thing you need to know: I couldn't confirm a real generated image, because the API call returns 429 — quota limit: 0 for this key. The model resolves to gemini-2.5-flash-preview-image, and Google's free tier allows zero image-generation requests — it requires a billed plan. The code is correct and the failure is handled gracefully; once the key has image quota (paid plan), the happy path will save the file and populate image_url with no further changes.

Note also: the worker runs --tries=3, which retries connection-level failures (timeout/DNS); HTTP error statuses like 429 take the graceful null path immediately rather than retrying.

Want me to switch the default model to a billable one, or add a small artisan command to test generation directly once the key has quota?