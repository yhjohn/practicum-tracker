# Goal
Replace the current “two separate public links + separate intake access code” flow with a single client-facing portal that is protected by a short-lived passcode, then gates the intake and consent pages behind a browser session created after verification.

# Models
1. app/Models/Client.php
   - Keep the client as the single public entry point.
   - Add the portal metadata needed for the one-page client access flow:
     - `public_access_code` (hashed)
     - `public_access_expires_at` (or equivalent timestamp)
     - optionally `public_portal_token` if we want a signed portal URL that is not just the client id.
   - No need for a separate table unless we later want per-portal audit/history.
2. app/Models/Intake.php
   - Keep the intake record as-is.
   - Remove the idea of a passcode being required directly on the intake form route. The intake page should be protected by the browser session, not by an intake-specific password.
3. app/Models/Consent.php
   - Keep the consent record as-is.
   - Same as above: no per-form passcode on the public consent page.

# View / UX
This should be built around the existing Filament client record views and the public Livewire pages that already exist.

- Existing view to consider:
  - app/Filament/Resources/Clients/Pages/ViewClient.php
  - app/Filament/Resources/Clients/Schemas/ClientInfolist.php
  - app/Filament/Resources/Clients/ClientResource.php
- New public-facing portal page should be a simple status dashboard for a single client.
- Page content:
  - “Client access portal” title
  - status chips for each form
  - intake: complete / pending / not started
  - consent: signed / pending
  - clickable links to public forms only when the client is verified for that browser session
  - a passcode entry before access is granted

Recommended UI pattern:
- portal page shows:
  - Intake form: check mark if submitted, exclamation if still pending, with updated date
  - Consent form: check mark if submitted, exclamation if still pending, with updated date
  - link to each form is shown conditionally based on verified session

# Proposed access flow
## 1) Client portal entry
- Create a single signed temporary route, e.g. `/client-portal/{client}` or `/portal/{client}`.
- The URL should not show any client id,name and it should be random string
- Route is time-limited and tamper-proof via Laravel signed URLs.
- The page loads and immediately asks for a passcode if the client has not yet been verified in this browser session.
- Passcode is generated once from the Filament client page and displayed in a modal (similar to the current intake access code action).

## 2) Passcode verification
- Generate the passcode in the existing client “generate link” action.
- Store a hash on the Client model (`public_access_code`), not plain text.
- When the client enters the code on the portal page:
  - check against the stored hash
  - if valid, set a Laravel session value keyed to the client, e.g. `public_portal.{client_id}`
  - session expiry = 30 minutes (or a flexible period)
  - clear the temporary code input and show the portal dashboard

## 3) Forms are session-gated, not password-gated
- After passcode validation, allow access to the intake and consent forms within the same browser session.
- Remove direct passcode checks from the form components.
- Instead, gate by browser session in the route or component lifecycle.

Pattern:
- `session(["public_portal.{$client->id}" => now()->addDays(2)])`
- when opening the intake or consent URL, check session exists for that client
- if missing or expired, redirect back to the portal with a message like “Please re-enter your access code to continue.”

This is intentionally simple and aligns with the current app flow:
- the portal is the thing with the passcode
- the individual forms are only accessible in the same browser that was verified
- if someone sends the form link to another browser, they cannot access it because the session is not present there

# Why this is enough
This avoids over-engineering while still matching the requirement.

- Signed URL = expiry + tamper protection
- Browser session = “same browser only” protection
- Passcode only on the entry page = much simpler for client-facing UX
- No need for a custom token table or complex auth layer for this small workflow

# Technical implementation plan
## Phase 1: Data model / migration
- Update Client model and add access metadata fields for the portal.
- Keep `Intake` and `Consent` fields unchanged unless we want a helper flag for current status.
- Keep hash logic in Laravel using `Hash::make()` and `Hash::check()`.

## Phase 2: Replace dual link generation action
- Update ViewClient action logic so it creates a single client portal link instead of generating two independent links.
- Client portal link should be auto generated upon client model creation.
- Remove the “Generate intake link” and “Generate consent link” actions.
- Add a generated passcode field alongside the portal URL in the modal.

## Phase 3: Public portal page
- Create a new Livewire or Blade page for the portal, roughly matching the current public layout.
- Example behavior:
  - if session is valid: show form status and links
  - if session is invalid or expired: show passcode form
- This page should live outside the admin panel but use the public layout.

## Phase 4: Access control for form pages
- Update the intake and consent public routes/pages to enforce session validity for the client.
- Example check:
  - confirm the client id in the signed route
  - confirm session contains a valid portal key for that client
  - if not, redirect to portal route.
- Keep the forms themselves simple and avoid repeating additional passcode input.

## Phase 5: UX polish
- Show status indicators using check mark / warning icon.
- Use `IntakeStatus` and `ConsentStatus` to decide the state label.
- If the form is already submitted, show “Completed” without allowing re-entry (unless that is a valid business rule).

## Phase 6: Tests
Add focused feature/livewire tests for:
1. generating portal link + passcode
2. valid passcode creates portal session
3. invalid passcode is rejected
4. form links redirect when no valid session exists
5. verified browser can access both forms in same session
6. session expiry denies access after timeout
7. submitted intake or consent state is shown as completed

# Recommended final shape
- Public entry route: `/portal/{client}` (signed + time limited)
- Passcode entry page: same portal route, before session grant
- Form routes: `/intake/{intake}` and `/consent/{consent}` remain, but are session-guarded
- Session logic: same-browser protection via Laravel cookie-based session
- Data storage: only hashed passcode + expiry metadata on Client, no extra form-level passwording

# Implementation note
This matches the requirement without making the flow complicated.
The app already has most of the structure needed:
- public Livewire forms
- signed URLs
- `ViewClient` action generation
- session-based verification in `PublicIntakeForm`
The main work is consolidating these into a single portal flow and reusing the same browser session rule for both forms.