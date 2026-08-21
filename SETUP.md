# Salado Custom Carts - theme setup

Two zip files:

- `salado-custom-carts.zip` - the theme
- `salado-custom-carts-child.zip` - the empty child theme

## 1. Install

WordPress dashboard: **Appearance > Themes > Add New > Upload Theme**.

1. Upload `salado-custom-carts.zip`, click Install. Do **not** activate yet.
2. Upload `salado-custom-carts-child.zip`, click Install, then **Activate** this one.

Activating the child theme is the right move: any tweak you or I make later goes in
the child, so it survives updates to the main theme.

No plugins are required. The theme brings its own fonts, icons and cart fields.

## 2. Fill in your details

**Settings > Salado Details.** Phone, email, town, hours and the pickup line. These
appear in the top bar, the header, the footer, the mobile Call button and inside page
copy - change them here once and every page updates.

Inside page text you can also type `[scc_phone]` or `[scc_email]` and it will print
the current details.

## 3. Add your carts

A new **Carts** menu appears in the dashboard sidebar (the little car icon).

Each cart has: Status (Available / Sale Pending / Sold), Price, Year, Make, Model,
Battery, Seats, and a Features box (one per line). Only the fields you fill in are
shown, so a half-filled cart never looks broken.

- Set the **Featured image** - that is the photo on the card.
- Leave **Price** blank and the card shows "Call for price".
- Marking a cart **Sold** keeps it on the site with a Sold badge. That is deliberate -
  sold carts are the best proof of what you build. Available carts always sort first.

The public page is **/carts-for-sale/**.

## 4. Build the homepage

Create a page called Home, open the block inserter (the **+** button), go to the
**Patterns** tab, find **Salado Custom Carts > Full homepage**, and insert it.

Then **Settings > Reading > Your homepage displays > A static page > Home**.

The whole homepage is ordinary WordPress blocks after that. Click any headline,
paragraph, photo or button and edit it like normal text. Nothing is locked in a
template and there is no page builder to learn.

The "Carts for sale right now" strip is a shortcode block containing:

    [scc_carts count="3" status="available"]

Change `count` to show more or fewer. `status` accepts `available`, `pending`, `sold`
or `any`.

## 5. Menus

**Appearance > Menus.** Build a menu and assign it to **Primary menu**. If you skip
this, the theme falls back to a sensible default so the header is never empty.

> One thing to fix while you are in there: on the current live site, the menu items
> "Make Your Cart LOOK Good" and "Make Your Cart DRIVE Good" both point at old page
> addresses and return a 404 for your customers right now. The pages themselves are
> fine - they were renamed to Appearance Upgrades and Performance Upgrades. Re-pointing
> those two menu items fixes it.

## 6. About WooCommerce

The theme does not need WooCommerce. When you are happy the Carts section holds
everything, deactivate the WooCommerce plugin - do not delete it, so nothing is lost
and it can be switched back on if you ever change your mind.

While WooCommerce is still active, the theme already stops it loading its cart and
checkout scripts on every page, since nothing is sold online here.

## 7. Spam on the quote form

The form blocks the obvious junk on its own the moment the theme is active -
nothing to switch on, no keys needed. Two hidden traps, a check on how fast the
form was filled in, a limit per address, and a read of the wording.

If you want a visible check on top of that, there are two options and you only
need one of them. Both live at **Settings > Salado Details > Spam protection**.

**Option A - the built-in question (no account with anybody).**
Tick "Ask a simple question" and save. The form then asks something like *"What is
four plus three?"*. Customers can answer with digits or words, and stray spaces or
a full stop are fine. The question changes on every page load and the answer is
never written into the page, so it cannot simply be read out of the source.

This is the recommended option. It needs no third party, sends nothing anywhere,
and works on any host.

**Option B - Cloudflare Turnstile (optional, stronger).**
Only worth it if the junk keeps coming and it is clearly a bot rather than a
person being paid to fill forms in.

1. Go to dash.cloudflare.com and sign in (a free account is enough - your site
   does NOT have to be on Cloudflare).
2. Turnstile > Add site. Put `saladocustomcarts.com` in the domain box.
3. Copy the two keys it gives you.
4. Paste them into the same settings screen and save. It only switches on once
   BOTH boxes are filled.

If both are switched on, Turnstile is used and the question hides itself, so
nobody is ever asked twice.

Nothing is deleted behind your back. Anything the filter is unsure about is
saved as a **draft** under Quote Requests, marked with the reason, and simply not
emailed to you. If one turns out to be a real customer, open it and hit Publish.
Only a hidden-trap hit is thrown away outright, and no human being can trigger
one of those.

If you do use Turnstile and Cloudflare is ever unreachable, the form lets the
enquiry through and marks it, rather than turning your phone off for the length
of someone else's outage.

Honest limitation: the built-in question is weaker than Turnstile against a bot
written specifically for your site. Against the volume junk you are getting it is
plenty, because the hidden layers above do most of the work already.

## Notes

- Colours come from your real logo: navy `#002068`, red `#b80830`.
- Fonts (Inter and Barlow Condensed) ship inside the theme, so there is no request
  out to Google on every page load.
- Text contrast was measured on the rendered pages and meets WCAG AA.
- The header bar is white on purpose. Your logo has navy lettering with white knocked
  out inside it, so recolouring it to sit on a black bar destroys the inner letters -
  a light header keeps the logo exactly as it is.
