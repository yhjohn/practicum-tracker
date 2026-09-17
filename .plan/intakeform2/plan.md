# Instruction

1. You are not allowed to delete any database table/entries.

# Files

These are reference that I need you to look at

app/Models/Intake.php
app/Filament/Schemas/intakes/IntakeWizard.php
resources/views/livewire/public-client-portal.blade.php
ClientInfolist.php


# Description

Currently I already a have an Intake form, your job is to look through the Intake and look at what the Intake is doing.

# Scope

I want to change the intake form question, however the project is already running and old data should remain as is and not be modified. but new clients will have to basically be onboarded with another different Intake.

There might be other instance of Intake question so the best I would say is probably different Intake model per.

The question that should be on this intake will be the following.

Section 1 -
Refer to the current Intake Model, but minus DOB

Section 2 and onwards, please use the docx in the same folder as this plan.

I will need to change the public client portal to link to the new intake form

As for the filament section this is a tough one i need to think of something
basically for those that arleady fill up i need to use the old download pdf and the new one download pdf.

---

# Expanded plan (decisions locked in)

## Naming
New model: `App\Models\IntakeV2`, table `intake_v2s`.
Supporting classes mirror the old naming with a `V2` suffix:
- `App\Filament\Schemas\Intakes\IntakeWizardV2`
- `App\Filament\Schemas\Intakes\IntakeSummaryV2`
- `App\Livewire\PublicIntakeFormV2`
- `resources/views/livewire/public-intake-form-v2.blade.php`
- `resources/views/pdf/intake-v2.blade.php`
- `database/factories/IntakeV2Factory.php`
- Route name `intake-v2.show`, URI `/intake-v2/{intake}`
- Reuse `App\Enums\IntakeStatus` as-is (Pending/Submitted has no version-specific behavior).

## Which intake does a client get?
Explicit flag column, not inferred from existing rows (inferring from "does an Intake row exist" breaks for existing clients who were added but never sent a portal link yet).

- Migration adds `clients.intake_version` (string, nullable).
- In the same migration, backfill every existing row: `DB::table('clients')->whereNull('intake_version')->update(['intake_version' => 'v1'])`.
- Add `App\Enums\IntakeVersion` (`V1 = 'v1'`, `V2 = 'v2'`), cast on `Client`.
- `Client::booted()`'s `creating` callback sets `intake_version` to `IntakeVersion::V2` when not already set (mirrors how it already defaults `public_access_token`/`public_access_code`/`public_access_expires_at`). This means the migration is the only place `v1` is ever assigned — every `Client` created after this ships defaults to `v2` automatically, no manual step needed at deploy time.
- `Client` gets a new `intakeV2(): HasOne` relation alongside the untouched `intake(): HasOne`.

This does **not** touch the `intakes` table, `Intake` model, or any existing row — satisfies "old data should remain as is."

## Branching logic (where old vs new is chosen)
Two call sites currently lazily create a Pending `Intake` for a client: `PublicClientPortal::render()` and `ViewClient::pendingIntakeFor()`. Both need to branch on `$client->intake_version`:
- `v1` → existing behavior, unchanged (`$client->intake` / create `Intake`).
- `v2` → `$client->intakeV2` / create `IntakeV2` (prefilled from `name`/`email`/`phone` only — no `dob`, since Section 1 drops it).

Recommend extracting the "resolve or create this client's current intake" logic into one shared helper (e.g. a method on `Client`) so `PublicClientPortal` and `ViewClient` don't duplicate the branch twice each.

## Routes
Add a second closure route mirroring the existing one:
```
Route::get('/intake-v2/{intake}', function (IntakeV2 $intake) { ... })
    ->middleware('signed')->name('intake-v2.show');
```
Same signed-URL + session-verification pattern as today's `/intake/{intake}`, calling `PublicIntakeFormV2::class.'@__invoke'`.

## Public client portal
`resources/views/livewire/public-client-portal.blade.php` currently renders one "Intake form" card unconditionally. It needs to build `$intakeUrl` against whichever relation/route applies for `$client->intake_version`, but otherwise render the same card — a client only ever sees **one** intake link, whichever version they're on.

## Filament (staff) side — two PDFs
`ViewClient` gets a second, independent action:
- `downloadIntakePdf` (existing, untouched) — visible when `$record->intake?->status === Submitted`, renders `pdf.intake`.
- `downloadIntakeV2Pdf` (new) — visible when `$record->intakeV2?->status === Submitted`, renders `pdf.intake-v2`.

Only one will ever be visible per client in practice (since a client is only ever on one version), but both exist independently so nothing breaks if that ever changes.

`ClientInfolist` gets a second collapsible "Intake forms (new)" section using `IntakeSummaryV2::sections('intakeV2.')`, alongside the existing one — same pattern as today, just doubled.

## Section 1 (from current model, minus DOB, marital status kept as its own step)
Personal Information step — same fields as today except `dob` removed:
`name, age, phone, email, occupation, sex, emergency_name, emergency_relationship, emergency_phone`.

Marital / Relationship Status — kept as its own step (per decision), but using the docx's option set instead of today's, and **dropping** `relationship_length` and `marriages` (the docx never asks for these):
`marital`: Single, Married, In a relationship, Separated / divorced, Widowed, Other, Prefer not to say (single-select).

## Sections 2–4 (from the docx, field-by-field)
The docx uses a checkbox glyph (☐) for both single-answer and multi-answer questions. Treated as `Radio` (single-select) unless the question says "(Select all that apply.)", in which case it's `CheckboxList`. Every option list that includes "Other" gets a paired `_other` text column/input (shown conditionally when "Other" is selected), following the same pattern the current schema already uses for e.g. `trauma`/`trauma_details`.

**Section 2 — Education, Career & Personal Strengths**
- `education_level` (Radio + other): Primary education / Secondary education (SPM or equivalent) / Certificate or diploma / Vocational or technical training / Bachelor's degree / Postgraduate degree (Master's or PhD) / Other / Prefer not to say
- `job_satisfaction` (Radio): 1 – Very dissatisfied … 5 – Very satisfied / Not currently employed
- `enjoyable_activities` (CheckboxList + other): Sports/physical activities, Arts/creative activities, Music/singing/dancing, Reading/learning, Cooking/baking, Time with family or friends, Helping others, Work/academic activities, Hobbies, Other
- `personal_strengths` (CheckboxList + other): Kindness, Patience, Creativity, Confidence, Responsibility, Determination, Honesty, Communication, Problem-solving, Adaptability, Independence, Not sure, Other
- `coping_resources` (CheckboxList + other): Loved ones, Exercise/sports, Music, Hobbies, Rest/alone time, Prayer/meditation, Nature, Talking about feelings, Other
- `strengths_to_build` (Textarea, freeform)

**Section 3 — Family, Social & Personal Background**
- `family_relationships` (CheckboxList + other): Close and supportive, Generally positive, Sometimes difficult/stressful, Frequent conflicts/misunderstandings, Distant/not connected, Complicated or mixed, Prefer not to say, Other
- `childhood_experiences` (CheckboxList + other): Positive experiences, Family conflicts, Changes in family structure/living arrangements, Loss of a loved one, Bullying or rejection, Neglect/lack of support, Difficult or stressful experiences, No not at the moment, Prefer not to say, Other
- `family_support_received` (Radio): Always, Most of the time, Sometimes, Rarely, Never, Not sure, Prefer not to say
- `beliefs` (CheckboxList + other): Buddhism, Christianity, Hinduism, Islam, Taoism, Personal beliefs not linked to a religion, No particular beliefs, Prefer not to say, Other
- `spiritual_upbringing_role` (Radio): Significant role, Some role, No role, Unsure, Prefer not to say
- `substances_used` (CheckboxList + other): Alcohol, Tobacco/cigarettes, Drugs, Other, None, Prefer not to say
- `substance_use_frequency` (Radio, shown only if `substances_used` isn't empty/None): Daily, Several times a week, Weekly, A few times a month, Rarely/occasionally, Other
- `background_notes` (Textarea, freeform) — "anything else about background, family, beliefs, or support system"

**Section 4 — Mental Health, Current Concerns & Counselling Goals**
- `previous_counselling` (Radio): Yes, No, Prefer not to say
- `previous_counselling_when` (Radio, shown only if Yes): Within past 6 months, 6 months–1 year ago, More than 1 year ago (the docx only collects a timeframe here, not a separate free-text reason — there is no reason field drawn on the form)
- `mental_health_diagnosis` (Radio): Yes, No, Unsure, Prefer not to say
- `mental_health_diagnosis_details` (Textarea, shown only if Yes) — "you may specify the diagnosis"
- `psychiatric_services` (CheckboxList): Psychiatric consultation, Hospitalisation, Medication, Currently receiving support, None, Prefer not to say
- `previous_support_feedback` (Textarea, freeform) — "what did you find helpful or unhelpful"
- `mood_recent` (CheckboxList + other): Happy/content, Calm/relaxed, Worried/anxious, Sad/low, Stressed/overwhelmed, Frustrated/irritable, Lonely/disconnected, Emotionally exhausted, Mood changes frequently, Other, Not sure, Prefer not to say
- `harm_thoughts` (Radio): No, Yes myself, Yes someone else, Yes both, Unsure, Prefer not to say
- `harm_thoughts_response` (Textarea, optional, shown unless "No") — "you may share what support you need here..."
- `current_stressors` (CheckboxList + other): Academic/exam stress, Work/career, Family, Relationship/friendship, Financial, Health/wellbeing, Life changes, Grief/loss, Self-esteem/confidence, Other, None at present, Prefer not to say
- `current_stressors_impact` (Radio): Not at all, Slightly, Moderately, Significantly, Severely, Not sure
- `main_concern` (Textarea, freeform) — the main concern being sought counselling for
- `counselling_goals` (CheckboxList + other): Understand feelings/thoughts, Manage stress/emotions, Improve relationships, Build confidence/self-esteem, Develop coping strategies, Explore solutions, Improve overall wellbeing, Not sure yet, Other
- `anything_else` (Textarea, freeform, final catch-all)

## Explicitly dropped vs. the old model (per decision — match docx exactly)
No equivalent carried forward for: `legal_issues`, `legal_impact`, `memory` (concentration concerns), `family_concern` (what family/friends expressed), `first_occurred` (when concern first noticed), `relationship_length`, `marriages`. These simply don't exist on `IntakeV2`.

## Table shape
`intake_v2s`: `id, client_id (unique, fk), status, access_code`, all Section 1 + marital fields above, all Section 2–4 fields above (including `_other` companion columns), `signature_date` (nullable date, set on submit like today — the `signature` image column is skipped since nothing in the current wizard actually captures a signature either; it's dead weight in the old table), `submitted_at`, timestamps.

## Tests
New `tests/Feature/IntakeV2FormTest.php` mirroring `IntakeFormTest.php`'s coverage (access code flow, rate limiting, save progress, submit, backfill, PDF/infolist visibility) against the new model/route/component — existing test file stays untouched, per the "don't remove/modify tests without approval" rule.

## Build order
1. Migration: `clients.intake_version` + backfill + `IntakeVersion` enum + `Client` relation/casts.
2. Migration + model + factory for `intake_v2s` / `IntakeV2`.
3. `IntakeWizardV2` + `IntakeSummaryV2`.
4. `PublicIntakeFormV2` + view + route.
5. Wire `PublicClientPortal` + `ViewClient` branching helper.
6. `pdf/intake-v2.blade.php` + `ViewClient` second download action + `ClientInfolist` second section.
7. Feature tests.
