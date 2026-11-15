# GoHighLevel (GHL) integration

Every saved enquiry is pushed to GHL as a contact (API v2, `services.leadconnectorhq.com`) by a queued job. The website works fully without it: the integration is **off whenever `GHL_API_TOKEN` or `GHL_LOCATION_ID` is empty**, and a CRM failure never blocks or fails a visitor's enquiry.

## 1. Create the Private Integration Token (PIT)
1. In GHL open the **sub-account** you want leads in.
2. Settings > **Private Integrations** > **Create new Integration**. Name it "Website enquiries".
3. Tick these scopes (names as shown in GHL):
   - **contacts.readonly** and **contacts.write** (find, create, update, and delete the test contact)
   - **locations/customFields.readonly** and **locations/customFields.write** (list and create custom fields)
   - **locations.readonly** (the `ghl:test` connection check)
4. Create it and copy the token now (it is shown once).
5. The **location ID** is in Settings > Business Profile (or the sub-account URL after `/location/`).
6. Put them in `.env` only (never in code, git or Admin):
   ```
   GHL_API_TOKEN=pit-xxxxxxxx
   GHL_LOCATION_ID=xxxxxxxx
   ```
   Then `php artisan config:clear`.

## 2. Create the custom fields
```
php artisan ghl:setup
```
Lists the existing contact custom fields, creates only the missing ones, prints what it created, and is safe to re-run. Field ids are cached for an hour.

```
php artisan ghl:test          # checks token + location, creates a labelled TEST contact, then deletes it
php artisan ghl:test --keep   # keep the test contact
```

## 3. What is sent
Contact: first name, last name, email, phone in E.164, source `Website: <form>`, tags (brand, `website-enquiry`, `form: <form>`, `package: <name>`, `tour: <type>`, `source: <utm_source>`).

Custom fields (exact names, defined only in `app/Services/Ghl/GhlFields.php`):

| Group | Fields |
|---|---|
| Request | Preferred Travel Date, Second Quote Preference, Special Request, Package, Tour Type, Form Name |
| Quiz | Quiz: Destination, Quiz: Season, Quiz: Travel Style, Quiz: Group Size, Quiz: Nights, Quiz: Flights Booked, Quiz: Budget, Quiz: Likelihood, plus **Quiz Answers** (every answer as one text block) |
| Consent | Consent Given, Consent Timestamp, Consent Text Version |
| Tracking, first touch | UTM Source/Medium/Campaign/Term/Content (First), GCLID/GBRAID/WBRAID (First), Referrer (First), Landing Page (First) |
| Tracking, latest touch | the same set with "(Last)" |

UTM and ad ids are sent from **every** form (hero quiz, header modal, package quote, ask-a-question, contact). A returning contact gets the latest-touch fields updated; existing **(First)** values in GHL are never overwritten (the job reads them first and skips those fields).

## 4. Behaviour
- Runs in `App\Jobs\SyncEnquiryToGhl` with 4 tries and backoff (30 s, 2 min, 10 min). Needs a queue worker in production (see DEPLOYMENT.md); locally the queue is `sync`.
- Status is stored on each enquiry (`skipped`, `pending`, `synced`, `failed`) with the GHL contact id and last error (the token is never logged). Admin > Enquiries > open an enquiry > **Retry sync**.
- Tests never reach the live CRM: `phpunit.xml` blanks the GHL variables and all HTTP is faked.

## 5. Staff rules
- You **can** move custom fields into folders in GHL (the API cannot create folders).
- You **must not rename** the fields. Matching is by exact name. To rename one, change it in GHL **and** in `GhlFields.php` together, then clear the cache (`php artisan cache:clear`).
- A new quiz step: its answer is included in "Quiz Answers". To give it its own field, add a line to `GhlFields::quizFieldMap()` and re-run `ghl:setup`.
