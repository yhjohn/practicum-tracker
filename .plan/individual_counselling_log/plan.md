# Goal
Change the way individual couselling logging. 

# Reference
Please follow 100% the Template of individual counselling.docx

# Model
CounsellingSession.php

# Scope
I need to perhaps separate the counselling session of group vs individual for now we do individual. I need to capture all that is there in the docx from the Filament PHP

I don't mind having another model for individual counselling and have it nested under the Counselling Session in admin bar now
and the Counselling Session perhaps we can turn into gian buttons that navigate users to respective place.

Make sure the headers like client name date time, place all is captured too.

---

# Fact-finding (current state)

- `CounsellingSession` (`counselling_sessions` table) already handles scheduling for both types: `type` (Individual/Group via `SessionType` enum), `sessionable` morph to `Client` or `Group`, `session_date`, `week`, `start_time`, `end_time`, `duration_minutes`, a single `session_notes` RichEditor, `attachments`.
- `CounsellingSessionResource` (list/create/edit/view) is the only place sessions are managed today. The form (`CounsellingSessionForm`) branches on `type` to show `client_id` (individual) vs `group_id` + attendee checklist (group), then the same `session_notes` field for both.
- The docx template ("Counseling Session Report") is a 1-per-session narrative report with a header (Client Name/Code, Date, Session #, Time, Place/Organization) and 4 parts, each a free-text write-up guided by bullet prompts, not a discrete Q&A form like the Intake:
  1. Issues/Problems Presented (Subjective + Objective)
  2. Assessment of Issues/Problems (clinical conceptualization)
  3. Treatment Planning and Action (goals, approach, interventions, homework)
  4. Evaluation of Counseling Session (progress, next-session plan)
  - Footer: Counselor name, signature, date/time.
- No existing model/table covers this — `session_notes` is the closest, but it's one plain field shared by both session types.
- Precedent for a 1:1 nested record editable from a resource's own page: none exactly, but `Groups/RelationManagers/ClientsRelationManager` shows the RelationManager pattern used elsewhere in this app (many-to-many, not 1:1).

# Decisions locked in

## New model, not new columns on `CounsellingSession`
Per your note you're fine with a separate model. Going with it rather than adding 4 more nullable columns to `counselling_sessions`, because it's individual-only (group sessions don't get this), it's optional/fills in after the session happens, and it keeps `CounsellingSessionForm` from growing a second unrelated form inside it.

- `App\Models\IndividualCounsellingLog`, table `individual_counselling_logs`.
- `belongsTo(CounsellingSession)`, one row per session (`counselling_session_id` unique FK, cascade delete).
- `CounsellingSession` gets `individualLog(): HasOne`.

## Fields
Header fields already on `CounsellingSession` are reused, not duplicated: client (via `sessionable`), date (`session_date`), time (`start_time`). Two header fields from the docx aren't captured anywhere yet, so they go on the new table:
- `place` (string, nullable) — "Place / Organization"
- `session_number` (unsigned tinyint, nullable) — the docx's "Session: 1/2/3/4/5"; free integer input, not locked to 1–5

Body: one long-text field per Part, matching the docx exactly (it's a narrative report, not a checklist — breaking each bullet under a Part into its own DB column would invent structure the source document doesn't have):
- `part1_subjective_objective`
- `part2_assessment`
- `part3_treatment_plan`
- `part4_evaluation`

Each rendered as a `RichEditor` (same component `session_notes` already uses) with the docx's bullet prompts as `->helperText()`, so the counselor sees the prompts while writing without us inventing fake structured fields for them.

Footer: `counselor_name` (string, nullable, defaulted to `auth()->user()->name`), `signed_at` (datetime, nullable, defaulted to now on save), `signature` (longText, nullable). Correcting my earlier call to skip the signature image — the docx has an explicit Signature line, and `saade/filament-autograph` is already an installed dependency used the same way on `ConsentForm` (`SignaturePad::make('signature')`, stored as `longText`). Reusing that instead of inventing something new or skipping a field the source doc explicitly asks for.

## Where it lives in Filament — "nested under Counselling Session"
Not a new top-level resource (a log only ever exists attached to one session — a separate index list would just be a worse way to find the same record). Not a RelationManager either — those are built for hasMany/belongsToMany, and this is 1:1.

Instead: a custom Filament page registered on `CounsellingSessionResource` itself, e.g. `getPages()['individual-log'] => ManageIndividualCounsellingLog::route('/{record}/individual-log')`. This keeps the URL nested under `/counselling-sessions/{id}/...` like you asked, without a second nav item.

"Giant button" = a prominent header action on `ViewCounsellingSession` (and a matching row action in `CounsellingSessionsTable`), labelled "Individual Counselling Report", visible only when `$record->isIndividual()` (`SessionType::Individual`), linking to that nested page. Group sessions won't show it — matches "for now we do individual."

Exact Filament v5 API for a resource-nested custom page (base class, how it loads/saves a *different* model than the resource's) needs a `search-docs` check at build time before writing it, per Boost rules — not guessing it here.

## Autosave
Requested: `filament-autosave` (or similar) so long-form typing isn't lost. This is a **new composer dependency** — `composer show --direct` confirms it's not currently installed, and CLAUDE.md requires approval before adding one. Flagging rather than adding silently: please confirm you want it added, and I'll check it actually supports Filament v5 (this app is on `filament/filament: ^5.0`; a lot of community Filament plugins lag a major version behind) before running `composer require`. If it turns out not to support v5, the fallback is a small `wire:poll`/`debounce` autosave on the page itself instead of a plugin — cheaper anyway for one form.

## PDF export
Docx implies this'll eventually want a printable report, same as Intake/Consent. `barryvdh/laravel-dompdf` is already installed and already used for Intake/Consent PDFs, so this is just a new blade view + download action, no new dependency. Still not building it in this pass since it wasn't asked for — flagging as a likely next step once the form itself is confirmed.

## DOCX export
Requested. Unlike the PDF, there's no docx-writing package installed today (`composer show --direct` has no phpoffice/phpword or similar) — this is a **new composer dependency** too, same approval-needed flag as autosave. Recommend `phpoffice/phpword` (the standard choice, MIT-licensed) if you confirm you want this built. Given PDF is already free (dompdf's installed) and DOCX would be new plumbing, worth confirming DOCX is actually needed alongside PDF rather than instead of it — two export formats to keep in sync is real ongoing cost for two exports of the same 6 fields.

# Table shape
`individual_counselling_logs`: `id, counselling_session_id (unique, fk cascade), place, session_number, part1_subjective_objective, part2_assessment, part3_treatment_plan, part4_evaluation, counselor_name, signature (longText), signed_at, timestamps`.

# Build order
1. Migration + `IndividualCounsellingLog` model + `CounsellingSession::individualLog()`.
2. `IndividualCounsellingLogForm` schema (4 RichEditors w/ docx prompts as helper text, `place`, `session_number`, `SignaturePad` for `signature`).
3. `search-docs` for the correct Filament v5 pattern for a resource-nested custom page over a different model, then `ManageIndividualCounsellingLog` page + route registration.
4. Header action on `ViewCounsellingSession` + row action on `CounsellingSessionsTable`, both gated on `isIndividual()`.
5. Feature test covering: action only visible for individual sessions, page creates/updates the log, counselor_name/signed_at default correctly.
6. Once confirmed: PDF download action (reusing dompdf) — no new dependency.
7. Only if approved: autosave dependency + DOCX export dependency (`composer require`), each its own step after the base form works.

# Confirmed
- One RichEditor per Part (no per-bullet fields) — locked in.

# Status (built)
- Steps 1–5: done. Migration, model, relation, form, nested page (`/counselling-sessions/{id}/individual-log`), header/row actions gated on `isIndividual()`, 5 passing feature tests.
- PDF export: done. `resources/views/pdf/individual-counselling-log.blade.php` (same layout convention as `pdf/consent.blade.php`), `downloadIndividualReportPdf` action on `ViewCounsellingSession`, gated on `signed_at` being set (mirrors the Consent/Intake "Submitted" gating). 3 more tests covering visibility + rendering.
- Autosave: done. Installed `yousefaman/filament-autosave` (confirmed Filament v5 support before installing), registered `AutosavePlugin` on `AdminPanelProvider`, added `HasAutosave`/`HasAutosaveForCreate` to `ManageIndividualCounsellingLog` only (the long form this was requested for — not added to every Edit page in the app, that's unrequested scope).
- DOCX export: left out per instruction, not built.

# Direction change (2026-09-18)
The nested-under-`CounsellingSession` design ("too complicated" — you have to create a `CounsellingSession` first, then navigate into a sub-page to actually log anything) is out. `IndividualCounsellingLog` is now a fully standalone top-level resource, no relationship to `CounsellingSession` at all. `CounsellingSession`/`CounsellingSessionResource` are back to their exact original state (verified zero diff) — group counselling will get its own separate thing later, per your note.

**What changed:**
- `individual_counselling_logs` migration: dropped `counselling_session_id`, added `client_id` (FK to clients, not nullable), `session_date`, `start_time` directly on the table — it now carries its own scheduling info instead of borrowing it from a session row.
- `IndividualCounsellingLog` model: `belongsTo(Client)` instead of `belongsTo(CounsellingSession)`.
- New resource `App\Filament\Resources\IndividualCounsellingLogs\*` (scaffolded via `make:filament-resource`, standard List/Create/Edit/View — no custom page plumbing needed anymore since the record IS the resource's own model now, which is simpler than the old override-heavy nested-page approach).
- Removed: `ManageIndividualCounsellingLog` page, the old `IndividualCounsellingLogForm` under `CounsellingSessions/Schemas`, `CounsellingSession::individualLog()`, the header/row actions on `ViewCounsellingSession`/`CounsellingSessionsTable`.
- PDF/autosave moved onto the new resource's own pages (`ViewIndividualCounsellingLog`'s `downloadPdf` action, `HasAutosave`/`HasAutosaveForCreate` on Edit/Create).
- One real test row you'd created while trying out the old flow got dropped along with the table rebuild (you said this was fine, still dev).
- Tests rewritten against the new Create/Edit/View pages — 7 passing.