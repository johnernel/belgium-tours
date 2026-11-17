# Admin guide (plain English)

Open `/admin` on your site and log in. Everything you can change is in the left menu. Changes show on the website straight away.

## Dashboard
Shows enquiries this week, new ones nobody has dealt with, the latest enquiries and quick links. A yellow note appears if the GoHighLevel link is off or some leads failed to send.

## Enquiries
Every form on the site ends up here.
- Use the search box and the filters (status, source such as `google`, dates) to find people. **Export CSV** downloads what you filtered.
- Open an enquiry to see their details, quiz answers, where they came from and the **consent evidence** (the exact wording they agreed to, date, IP address). Keep this for disputes.
- Set a **status** (New, Contacted, Quoted, Won, Lost) and write **internal notes**. Only staff see notes.
- **Retry sync** re-sends one lead to GoHighLevel. **Delete** removes it for good (including the consent record).

## Packages
Add, edit, duplicate, publish or hide, feature, and reorder trips.
- **Published** trips show on the site. The home page shows up to six trips, featured ones first (tick **Featured**, and use the Order numbers).
- Leave **From price** empty to show "Price on request". Never guess a price.
- Description uses a simple editor. Highlights and Included/Not included are **one item per line**.
- Itinerary and trip questions: use **Add a day / Add a question**.
- Photos: the hero photo and every gallery photo need a short **description** (alt text) for accessibility and Google. Use photos you own or that are free for commercial use.
- "Page title for Google" and "Description for Google" control what searchers see. Tick "Hide from search" for private pages.
- **Route** (cities, e.g. `Bruges · Ghent`) and the first three **Highlights** appear on the home page journey cards. The card shows "N days" (nights + 1) and the From price, or "Price on request" when the price is blank.
- **Duplicate** makes a draft copy to adapt.

## Destinations
The six places shown in the home page accordion and as cards on the **Destinations** page (Bruges, Ghent, Brussels, Antwerp, Leuven, Ardennes). For each one set the name, a short description and a photo (describe the photo). **Photo focus** (optional, e.g. `30% 50%`) chooses which part of the photo stays visible in the narrow strips. The **tour count is automatic**: open a package and tick the destinations it visits. Edit the page banner (headline, short description, photo) in Admin > Pages > Destinations; put *asterisks* round the accent words of the headline.

## Tour types and Regions
Regions (for example "Belgium" or "Belgium and the Netherlands") are the country-level groups packages sit in, and are used by the Region filter on the Packages page. **Each tour type has its own page** (menu: Tour Types; also linked from the home page dropdown and cards). For each one set the **short tab label** (used on the home page tabs, e.g. Self-Drive), the **page headline**, the **page description** (shown beside the quiz), the Google title and description, and the **photo** (it is the background of that page). "Order" decides the sequence; untick "Show on the site" to hide one.

## Quiz
The questions in the home page "Start Your Adventure" box. Edit the wording, switch steps on or off, reorder, and add, rename or remove answers (and choose an icon). A new step starts switched off until you add answers and tick **On**. The contact form is always the last step. Answers are saved with the enquiry and sent to GoHighLevel.

## Pages
Edit the text of the home page sections, About, Contact, Terms and Privacy. About and Contact have a **banner**: change its short description and photo (describe the photo for accessibility). Text in [square brackets] is a placeholder to replace. Terms and Privacy are **starter templates**: have a professional review them. Type `[Company Name]` and it becomes your legal company name.

## Testimonials and FAQs
Only publish real reviews you have permission to use. Untick "placeholder" when a review is real. FAQs show on the home page and are sent to Google as FAQ data.

## Journal (blog)
Admin > Journal. Each article has a title, a category tag (shown on the card), a short excerpt, the article text, a photo with alt text, minutes to read and a publish date (blank = now; a future date hides it until then). The home page shows the three newest; /journal lists them all. Sample articles are marked "placeholder": they show a notice, are hidden from Google and left out of the sitemap. Replace them with real articles and untick "placeholder". Only use photos you are free to use.

## Package page images
On each package page the Highlights and every day of the Day by Day accordion show an image. Photos you upload in the package's Gallery (Admin > Packages) are used first, in order, with the description you gave them. Without gallery photos the site picks a photo of the places named in the text and the route (Bruges, Ghent and so on). The package's right-hand quote form was replaced by the main quiz form in the page's photo banner.

## Team and the About Us pages
Admin > Team holds the people on the Meet Our Team page (name, role, short introduction, optional photo with alt text, order, show/hide). Use only real people who agreed to be shown, and only photos you may use. The sample entries are placeholders: replace them and untick "placeholder". While every entry is a placeholder the page is hidden from Google.
Admin > Pages also lists Meet Our Team, Testimonials and FAQs: you can change the banner headline (put *asterisks* round the accent words), the short text under it and the banner photo. Testimonials come from Admin > Testimonials and the questions from Admin > FAQs.

## reCAPTCHA
Admin > reCAPTCHA: paste the Site key and the Secret key from google.com/recaptcha/admin (type reCAPTCHA v2, "I'm not a robot" checkbox, with this website's domain added), keep "Use reCAPTCHA on my forms" ticked and press Save keys. The secret is stored encrypted and shown only as its last 4 characters; leave the box blank to keep it. "Test secret key" asks Google whether the secret is valid (it cannot check the site key: open a form on the website, tick the box and send a test enquiry). Untick the switch only for testing: with it off, forms accept anyone and only the hidden honeypot and the rate limit stop spam. The status badge warns when Google's always-pass test keys are in use.

## Home page: The Journey Never Ends
The interactive Belgium map on the home page (below the FAQ). Its headline, text and button wording are in Admin > Pages > Home (the "Journey" boxes). The cities, photos and tour counts come from the site itself: a city's count is the number of published packages whose route or title names that city, and "View tours" opens the Packages page filtered to it (`/packages?place=Ghent`). To make a new trip count for a city, put the city name in the package's Route or title.

## Media
Upload photos once; they are resized and converted for fast loading. Describe every photo.

## Settings
Brand and **legal company name** (used in the consent wording), logo, favicon, phone (shown as tap-to-call), email, address, social links, footer text, notification emails, the quote-popup wording, Google Tag Manager ID, reCAPTCHA keys, default Google description and share image. Anything left blank is hidden on the site. GoHighLevel keys are **not** here: they live in the server's `.env` file for security.

## Admin users
Add people, reset passwords. You cannot delete yourself or the last admin. Passwords need 10+ characters.

## Habits that keep the site healthy
- Answer new enquiries quickly and set their status.
- Check the dashboard for failed GoHighLevel syncs.
- Change the first admin password when you launch.
